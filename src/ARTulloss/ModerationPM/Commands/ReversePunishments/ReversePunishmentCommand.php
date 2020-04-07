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
use ARTulloss\ModerationPM\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\Player;
use function str_replace;
use function strtolower;
use function var_dump;

abstract class ReversePunishmentCommand extends ModerationCommand{
    protected const TYPE = 0;
    protected const MESSAGE_SUCCESS = 'Success';
    protected const MESSAGE_SUCCESS_ONLINE = 'Success';
    protected const MESSAGE_FAIL = 'Fail';
    protected const MESSAGE_BROADCAST = '{player} was {action} by {staff}';
    protected const COLOR = Colors::GREEN;

    public function __construct(Main $main, string $name, string $description = "", array $aliases = []) {
        parent::__construct($main, $name, $description, $aliases);
        $this->setPermission(Main::PERMISSION_PREFIX . 'un' . $this->provider->typeToString(static::TYPE, false));
    }
    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if($this->testPermission($sender)) {
            if(!isset($args['name']))
                throw new InvalidCommandSyntaxException();
            $this->passPlayerData($args['name'], null, null, true, function (?array $dataArray) use($sender, $args): void{
                if($dataArray === null)
                    return;
                /** @var PlayerData $playerData */
                foreach ($dataArray as $playerData) {
                    $xuid = $playerData->getXUID();
                    $device_id = $playerData->getDeviceID();
                    $this->passPlayerData($args['name'], $xuid, $device_id, true, function (?array $dataArray) use ($sender, $args): void{
                        if($dataArray !== null) {
                            $lowerCaseName = strtolower($args['name']);
                            /** @var PlayerData $data */
                            foreach ($dataArray as $data) {
                                $name = $data->getName();
                                $id = $data->getID();
                                $this->provider->asyncRemovePunishment($id, static::TYPE, function (int $rows) use ($sender, $lowerCaseName, $name): void{
                                    $player = $sender->getServer()->getPlayer($name); // Unpunish the player if they're or alts are online
                                    if($player !== null)
                                        $this->onlineUnpunish($player, str_replace('{player}', $player->getName(), static::MESSAGE_SUCCESS_ONLINE));
                                    if($rows === 0) {
                                        $sender->sendMessage(str_replace('{player}', $name, static::MESSAGE_FAIL));
                                        return;
                                    }
                                    $sender->sendMessage(str_replace('{player}', $name, static::MESSAGE_SUCCESS));
                                    $sender->getServer()->broadcastMessage(str_replace(['{player}', '{staff}'], [$name, $sender->getName()], static::MESSAGE_BROADCAST));
                                    $content = $this->plugin->getCommandConfig()->getAll()['Discord']['Content-Unpunish'];
                                    $logger = $this->plugin->getDiscordLogger();
                                    if($logger !== null) {
                                        foreach ($content as $key => $line)
                                            $content[$key] = str_replace(['{player}', '{staff}'], [$logger->getXblLinkMarkdown($name), $logger->getXblLinkMarkdown($sender->getName())], $line);
                                        $logger->logGeneric('Un' . $this->provider->typeToString(static::TYPE, false),
                                            $content, static::COLOR);
                                    }
                                });
                            }
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