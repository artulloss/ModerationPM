<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/2/2019
 * Time: 1:03 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Form;

use ARTulloss\ModerationPM\Commands\Arguments\DateTimeArgument;
use ARTulloss\ModerationPM\Commands\Arguments\ForeverArgument;
use ARTulloss\ModerationPM\Commands\Arguments\MessageArgument;
use ARTulloss\ModerationPM\Commands\ModerationCommand;
use ARTulloss\ModerationPM\Database\Container\PlayerData;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

/**
 * Class FormModerationCommand
 * For moderating players that are offline or stored in data
 * @package ARTulloss\ModerationPM\Commands\Form
 */
abstract class FormModerationCommand extends ModerationCommand{

    protected function prepare(): void{
        parent::prepare();
        $this->registerArgument(1, new DateTimeArgument('length', true));
        $this->registerArgument(1, new ForeverArgument('length', true));
        $this->registerArgument(2, new MessageArgument('reason', true));
    }

    /**
     * Splits the command into the run as player and run as console
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
    final public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if(isset($args['name'])) {
            $name = $args['name'];
            $onlinePlayer = $this->resolveOnlinePlayer($sender, $name, true);
            if($onlinePlayer !== null)
                $name = $onlinePlayer->getName();
            $this->passPlayerData($name, function (?PlayerData $playerData) use ($sender, $args): void{
                if($playerData !== null) {
                     if ($sender instanceof ConsoleCommandSender || (isset($args['length']) && isset($args['reason']) && $args['reason'] !== '' && ($good = true))) {
                         if(isset($good) || (isset($args['length']) && isset($args['reason'])))
                             $this->runAsConsole($sender, $playerData, $args);
                         else {
                             $this->sendUsage();
                             return;
                         }
                     } elseif ($sender instanceof Player) {
                         $this->runAsPlayer($sender, $playerData, $args);
                     } else {
                         $this->sendUsage();
                         return;
                     }
                } else
                    $sender->sendMessage(TextFormat::RED . 'Player does not exist!');
            });
        } else
            $this->sendUsage();
    }
    /**
     * @param Player $sender
     * @param PlayerData $data
     * @param array $args
     */
    abstract public function runAsPlayer(Player $sender, PlayerData $data, array $args): void;
    /**
     * @param CommandSender $sender
     * @param PlayerData $data
     * @param array $args
     */
    abstract public function runAsConsole(CommandSender $sender, PlayerData $data, array $args): void;
}