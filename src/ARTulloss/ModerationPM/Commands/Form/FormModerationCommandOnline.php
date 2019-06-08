<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 5/29/2019
 * Time: 2:39 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Form;

use ARTulloss\ModerationPM\Commands\ModerationCommand;
use ARTulloss\ModerationPM\Utilities\Utilities;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;

/**
 * Class FormModerationCommandOnline
 * For moderating online players, gives instances of Player to work with
 * @package ARTulloss\ModerationPM\Commands\Form
 */
abstract class FormModerationCommandOnline extends ModerationCommand{
    /**
     * Splits the command into the run as player and run as console
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
    final public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        Utilities::dumpReturn($this->getPermission());
        if(isset($args['name'])) {
            if(($player = $this->resolveOnlinePlayer($sender, $args['name'])) && $player !== null) {
                if ($sender instanceof Player) {
                    $this->runAsPlayer($sender, $player, $args);
                } elseif ($sender instanceof ConsoleCommandSender) {
                    $this->runAsConsole($sender, $player, $args);
                }
            }
        } else
            $this->sendUsage();
    }
    /**
     * @param Player $sender
     * @param Player $player
     * @param array $args
     */
    abstract public function runAsPlayer(Player $sender, Player $player, array $args): void;

    /**
     * @param CommandSender $sender
     * @param Player $player
     * @param array $args
     */
    abstract public function runAsConsole(CommandSender $sender, Player $player, array $args): void;
}