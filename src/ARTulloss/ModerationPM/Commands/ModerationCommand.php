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
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

abstract class ModerationCommand extends BaseCommand{

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
            $sender->sendMessage(TextFormat::RED . "That player is offline or doesn't exist!");
        return $player;
    }
    /**
     * @param string $playerName
     * @param callable $callback
     */
    public function passPlayerData(string $playerName, callable $callback): void{
        $this->provider->asyncGetPlayer($playerName, function (array $result) use ($callback): void{
            $callback(PlayerData::fromDatabaseQuery($result));
        });
    }
}