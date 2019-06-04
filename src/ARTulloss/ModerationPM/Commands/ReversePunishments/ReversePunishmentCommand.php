<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/3/2019
 * Time: 2:19 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\ReversePunishments;

use ARTulloss\ModerationPM\Commands\ModerationCommand;
use ARTulloss\ModerationPM\Database\Container\PlayerData;
use ARTulloss\ModerationPM\Discord\Colors;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use function str_replace;

abstract class ReversePunishmentCommand extends ModerationCommand{
    protected const TYPE = 0;
    protected const MESSAGE_SUCCESS = 'Success';
    protected const MESSAGE_SUCCESS_ONLINE = 'Success';
    protected const MESSAGE_FAIL = 'Fail';
    protected const COLOR = Colors::GREEN;
    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if(isset($args['name'])) {
            $this->passPlayerData($args['name'], function (?PlayerData $data) use ($sender): void{
                if($data !== null) {
                    $name = $data->getName();
                    $this->provider->asyncRemovePunishment($name, static::TYPE, function (int $rows) use ($sender, $name): void{
                        if($rows === 0) {
                            $sender->sendMessage(str_replace('{player}', $name, static::MESSAGE_FAIL));
                            return;
                        }
                        $sender->sendMessage(str_replace('{player}', $name, static::MESSAGE_SUCCESS));
                        $content = $this->plugin->getCommandConfig()->getAll()['Discord']['Content-Unpunish'];
                        $logger = $this->plugin->getDiscordLogger();
                        if($logger !== null) {
                            foreach ($content as $key => $line)
                                $content[$key] = str_replace(['{player}', '{staff}'], [$logger->getXblLinkMarkdown($name), $logger->getXblLinkMarkdown($sender->getName())], $line);
                            $logger->logGeneric('Un' . $this->provider->typeToString(static::TYPE, false),
                                $content, static::COLOR);
                            $player = $sender->getServer()->getPlayer($name);
                            if($player !== null)
                                $this->onlineUnpunish($player, str_replace('{player}', $player->getName(), static::MESSAGE_SUCCESS_ONLINE));
                        }
                    });
                }
            });
        }
    }

    /**
     * @param Player $player
     * @param string $message
     */
    public function onlineUnpunish(Player $player, string $message): void{}
}