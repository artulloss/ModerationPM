<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/2/2019
 * Time: 1:03 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Form;

use function array_values;
use ARTulloss\ModerationPM\Commands\ModerationCommand;
use ARTulloss\ModerationPM\Database\Container\PlayerData;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use function strtolower;

/**
 * Class FormModerationCommand
 * For moderating players that are online + offline or stored in data
 * @package ARTulloss\ModerationPM\Commands\Form
 */
abstract class FormModerationCommand extends ModerationCommand{
    /**
     * Splits the command into the run as player and run as console
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
    final public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if(isset($args['name'])) {
            $name = $args['name'];
            $player = $this->resolveOnlinePlayer($sender, $args['name'], true);
            if($player !== null) {
                $name = $player->getName();
            }
            $data = $this->plugin->getPlayerData()->get($name);
            if($data !== null) {
                $name = $data->getName();
            }
            $this->passPlayerData($name, null, null, true, function (?array $playerDataArray) use ($sender, $name, $args): void{
                if($playerDataArray !== null) {
                    $lowerCaseName = strtolower($name);
                    /** @var PlayerData $playerData */
                    $playerData = array_values($playerDataArray)[0];
                    if(strtolower($playerData->getName()) === $lowerCaseName) {
                        if ($sender instanceof ConsoleCommandSender || (isset($args['length']) && isset($args['reason']) && $args['reason'] !== '' && ($good = true))) {
                            if(isset($good) || (isset($args['length']) && isset($args['reason']))) {
                                $this->runAsConsole($sender, $playerDataArray, $args);
                                return;
                            } else {
                                $this->sendUsage();
                                return;
                            }
                        } elseif ($sender instanceof Player) {
                            $this->runAsPlayer($sender, $playerDataArray, $args);
                            return;
                        } else {
                            $this->sendUsage();
                            return;
                        }
                    }
                } else
                    $sender->sendMessage(TextFormat::RED . 'Player does not exist!');
            });
        } else
            $this->sendUsage();
    }
    /**
     * @param Player $sender
     * @param array $data
     * @param array $args
     */
    abstract public function runAsPlayer(Player $sender, array $data, array $args): void;
    /**
     * @param CommandSender $sender
     * @param array $data
     * @param array $args
     */
    abstract public function runAsConsole(CommandSender $sender, array $data, array $args): void;
}