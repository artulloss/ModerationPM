<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/7/2020
 * Time: 5:20 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Miscellaneous;

use ARTulloss\ModerationPM\Commands\ModerationCommand;
use ARTulloss\ModerationPM\Database\Container\PlayerData;
use ARTulloss\ModerationPM\Utilities\Utilities;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\utils\TextFormat;
use function str_replace;
use function strtolower;

class AliasCommand extends ModerationCommand {
    public const MESSAGE_INITIAL = 'Alias for {player}';
    public const MESSAGE_MATCHES = '{player} with ' . TextFormat::RED . '{matches}' . TextFormat::WHITE  . ' matches';
    private $aliases = [];
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if(!isset($args['name']))
            throw new InvalidCommandSyntaxException();
        $player = Utilities::getPlayer($args['name']);
        $xuid = null;
        if($player !== null) {
            $args['name'] = $player->getName();
            $xuid = $player->getXuid();
        }
        $this->passPlayerData($args['name'], $xuid, null, true, function (?array $playerDataArray) use ($sender, $args): void{
            if($playerDataArray !== null) {
                /** @var PlayerData $playerData */
                foreach ($playerDataArray as $playerData) {
                    $this->passPlayerData($playerData->getName(), $playerData->getXUID(), $playerData->getDeviceID(), true, function (?array $playerDataArray) use ($sender, $args): void{
                        /** @var PlayerData $playerData */
                        foreach ($playerDataArray as $playerData) {
                            $name = $playerData->getName();
                            if(!isset($this->aliases[$name])) {
                                $this->aliases[$name] = 1;
                            } else
                                $this->aliases[$name]++;
                        }
                    });
                }
                $sender->sendMessage(str_replace('{player}', $args['name'], self::MESSAGE_INITIAL));
                foreach ($this->aliases as $name => $matches) {
                    if(strtolower($name) !== strtolower($args['name']))
                        $sender->sendMessage(str_replace(['{player}', '{matches}'], [$name, $matches], self::MESSAGE_MATCHES));
                }
                $this->aliases = [];
            }
        });
    }
}