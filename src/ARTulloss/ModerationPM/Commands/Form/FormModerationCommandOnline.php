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
        if(($player = $this->resolveOnlinePlayer($sender, $args['name'])) && $player !== null) {
            if ($sender instanceof Player) {
                $this->runAsPlayer($sender, $player);
            } elseif ($sender instanceof ConsoleCommandSender) {
                $this->runAsConsole($sender, $player);
            }
        }
    }
    /**
     * @param Player $sender
     * @param Player $player
     */
    abstract public function runAsPlayer(Player $sender, Player $player): void;
    /**
     * @param ConsoleCommandSender $sender
     * @param Player $player
     */
    abstract public function runAsConsole(ConsoleCommandSender $sender, Player $player): void;
}