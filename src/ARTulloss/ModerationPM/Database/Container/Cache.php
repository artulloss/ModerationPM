<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/3/2019
 * Time: 3:47 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Database\Container;

use ARTulloss\ModerationPM\Database\Provider;
use ARTulloss\ModerationPM\Main;
use ARTulloss\ModerationPM\Utilities\Utilities;
use pocketmine\plugin\Plugin;

/**
 * Class Cache
 * Useful for storing data that we don't want to fetch repeatedly
 * @package ARTulloss\ModerationPM\Database\Cache
 */
class Cache extends BoolContainer{
    /** @var int $type */
    private $type;
    /**
     * Cache constructor.
     * @param Plugin $plugin
     * @param int $type
     */
    public function __construct(Plugin $plugin, int $type) {
        parent::__construct($plugin);
        $this->type = $type;
    }
    public function refresh(): void{
        /** @var Main $plugin */
        $plugin = $this->plugin;
        /** @var Provider $provider */
        $provider = $plugin->getProvider();
        $provider->asyncGetPunishments($this->type, function (array $result) use ($plugin, $provider): void{

            $logger = $this->plugin->getLogger();

            $type = $provider->typeToString($this->type);

            $logger->info("Refreshing cache for {$type}s...");

            foreach ($result as $punishment) {
                $punishments[$punishment['name']] = Punishment::fromDatabaseQuery($punishment);
            }

            if(!isset($punishments)) {
                $logger->info("No {$type}s found");
                return;
            }

            $server = $this->plugin->getServer();

            $playerData = $plugin->getPlayerData();

            foreach ($server->getOnlinePlayers() as $player) {
                $name = $player->getName();
                $data = $playerData->get($name);
                if($data === null) // Impossible...
                    continue;
                /** @var Punishment[] $punishments */
                if(isset($punishments[$name])) {
                    $this->handlePunishment($punishments[$name], $data);
                } else {
                    $this->handleAlts($provider, $data, $punishments);
                }
            }
        });
    }
    /**
     * @param Provider $provider
     * @param PlayerData $data
     * @param array $punishments
     */
    private function handleAlts(Provider $provider, PlayerData $data, array $punishments): void{
        $provider->asyncGetPlayer($data->getName(), $data->getXUID(), $data->getDeviceID(), true, function (array $result) use ($provider, $punishments, $data): void{
            /** @var Punishment|null $punishment */
            $punishment = null;
            foreach ($result as $p) {
                $playerData = PlayerData::fromDatabaseQuery($p);
                if($playerData !== null) {
                    $name = $playerData->getName();
                    if(isset($punishments[$name])) {
                        if($punishment === null || (($until = $punishment->getUntil()) && $until < $punishments[$name]))
                            $punishment = $punishments[$name];
                    }
                }
                if($punishment !== null) {
                    $this->handlePunishment($punishment, $data); // Data is original PlayerData object
                }
            }
        });
    }
    /**
     * @param Punishment $punishment
     * @param PlayerData $data
     * @throws \Exception
     */
    private function handlePunishment(Punishment $punishment, PlayerData $data): void{
        /** @var Main $plugin */
        $plugin = $this->plugin;
        $provider = $plugin->getProvider();
        if(Utilities::isStillPunished($punishment->getUntil())) {
            $this->cache[$data->getName()] = true;
        } else {
            $server = $plugin->getServer();
            $name = $punishment->getPlayerName();
            $provider->asyncRemovePunishment($data->getID(), $this->type, function (int $rows) use ($plugin, $provider, $server, $name): void{
                if($rows !== 0) {
                    $expiredMsg = $provider->typeToString($this->type, false) . ' expired!';
                    $plugin->getLogger()->info("$name's " . $expiredMsg);
                    $player = $server->getPlayerExact($name);
                    if($player !== null) {
                        $plugin->getFrozen()->reverseAction($player);
                        $player->setImmobile(false);
                    }
                }
            });
        }
    }
}