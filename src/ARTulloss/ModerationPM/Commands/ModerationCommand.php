<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 5/29/2019
 * Time: 12:05 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands;

use ARTulloss\ModerationPM\Database\Container\PlayerData;
use ARTulloss\ModerationPM\Database\Provider;
use ARTulloss\ModerationPM\Main;
use ARTulloss\ModerationPM\Utilities\Utilities;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\Player;

abstract class ModerationCommand extends BaseCommand implements CommandConstants{

    protected const TITLE = 'Moderation';
    /** @var Provider $provider */
    protected $provider;
    /** @var Main $plugin */
    protected $plugin;
    /**
     * ModerationCommand constructor.
     * @param Main $main
     * @param string $name
     * @param string $description
     * @param array $aliases
     */
    public function __construct(Main $main, string $name, string $description = "", array $aliases = []) {
        parent::__construct($name, $description, $aliases);
        $this->plugin = $main;
        $this->provider = $main->getProvider();
    }
    /**
     * @throws ArgumentOrderException
     * @throws \CortexPE\Commando\exception\ArgumentOrderException
     */
    protected function prepare(): void{
        $this->registerArgument(0, new RawStringArgument('name'));
    }
    /**
     * @param CommandSender $sender
     * @param string $name
     * @param bool $silent
     * @return Player|null
     */
    public function resolveOnlinePlayer(CommandSender $sender, string $name, bool $silent = false): ?Player{
        $player = Utilities::getPlayer($name);
        if ($player === null && !$silent)
            $sender->sendMessage(self::PLAYER_OFFLINE);
        return $player;
    }
    /**
     * @param string $playerName
     * @param string|null $xuid
     * @param string|null $device_id
     * @param bool $inclusive
     * @param callable $callback
     */
    public function passPlayerData(string $playerName, ?string $xuid, ?string $device_id, bool $inclusive, callable $callback): void{
        $this->provider->asyncGetPlayer($playerName, $xuid, $device_id, $inclusive, function (array $result) use ($callback): void {
            foreach ($result as $player) {
                $data = PlayerData::fromDatabaseQuery($player, PlayerData::NO_KEY);
                if($data !== null)
                    $dataArray[] = $data;
            }
        $callback($dataArray ?? null);
        });
    }
}