<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/1/2019
 * Time: 10:11 AM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Events;

use ARTulloss\ModerationPM\Database\Container\PlayerData;
use ARTulloss\ModerationPM\Database\Container\Punishment;
use ARTulloss\ModerationPM\Main;
use ARTulloss\ModerationPM\Utilities\Utilities;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener as PMListener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Player;
use function strtr;
use function substr;

class Listener implements PMListener{
    /** @var Main $plugin */
    private $plugin;
    /** @var string $staffChatChar */
    private $staffChatChar;
    /** @var string[] $deviceIDs */
    private $deviceIDs;
    /**
     * Listener constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->staffChatChar = $plugin->getCommandConfig()->getNested('Staff Chat.Inverse Character');
    }
    /**
     * @param DataPacketReceiveEvent $event
     */
    public function onLogin(DataPacketReceiveEvent $event): void{
        $pk = $event->getPacket();
        if($pk instanceof LoginPacket) {
            $player = $event->getPlayer();
            $name = $pk->username;
            $xuid = $pk->xuid;
            if($xuid === '')
                return;
            $deviceID = $pk->clientData['DeviceId'];
            $this->deviceIDs[$name] = $deviceID;
            $provider = $this->plugin->getProvider();
            $provider->asyncGetPlayer($name, $xuid, $deviceID, false, function (array $result) use ($provider, $player, $name, $xuid, $deviceID, $event): void{
                $playerData = PlayerData::fromDatabaseQuery($result);
                if ($playerData === null) {
                    $provider->asyncRegisterPlayer($name, $xuid, $deviceID, $player->getAddress(), function () use ($event): void {
                        $this->onLogin($event);
                    });
                    return;
                }
            });
            $provider->asyncGetPlayer($name, $xuid, $deviceID, true, function (array $result) use ($player): void{
               foreach ($result as $playerData) {
                   $playerData = PlayerData::fromDatabaseQuery($playerData, PlayerData::NO_KEY);
                   if($playerData !== null) {
                       $this->checkAllPunishments($player, $playerData);
                   }
               }
            });
        }
    }
    /**
     * @param Player $player
     * @param PlayerData $playerData
     */
    public function checkAllPunishments(Player $player, PlayerData $playerData): void{
        $provider = $this->plugin->getProvider();
        $id = $playerData->getID();
        $name = $playerData->getName();
        $this->plugin->getPlayerData()->set($playerData); // Assign player and id together in RAM
        $provider->asyncCheckPunished($id, Punishment::TYPE_BAN, function (array $result) use ($provider, $player, $id, $name): void {
            /** @var Punishment $punishment */
            $punishment = Punishment::fromDatabaseQuery($result, 0, Punishment::TYPE_BAN);
            if ($punishment !== null) {
                $until = $punishment->getUntil();
                if (Utilities::isStillPunished($until)) {
                    $player->kick($this->plugin->resolvePunishmentMessage(Punishment::TYPE_BAN, $punishment->getReason(), $until), false);
                    return;
                }
                $this->plugin->getProvider()->asyncRemovePunishment($id, Punishment::TYPE_BAN, $this->getOnDelete($name, Punishment::TYPE_BAN));
            }
            $provider->asyncCheckPunished($id, Punishment::TYPE_IP_BAN, function (array $result) use ($player, $id, $name): void{
                $punishment = null;
                foreach ($result as $key => $entry) {
                    $resultClone = $result;
                    /** @var Punishment $potentialPunishment
                     * @var Punishment|null $punishment
                     */
                    $potentialPunishment = Punishment::fromDatabaseQuery($resultClone, $key, Punishment::TYPE_IP_BAN);
                    if ($potentialPunishment !== null and
                        ($potentialPunishment->getUntil() === Punishment::FOREVER) || ($punishment === null || $potentialPunishment->getUntil() > $punishment->getUntil()))
                        $punishment = $potentialPunishment;
                }
                if ($punishment !== null) {
                    $until = $punishment->getUntil();
                    if (Utilities::isStillPunished($until)) {
                        $player->kick($this->plugin->resolvePunishmentMessage(Punishment::TYPE_IP_BAN, $punishment->getReason(), $until), false);
                        return;
                    }
                    $this->plugin->getProvider()->asyncRemovePunishment($id, Punishment::TYPE_IP_BAN, $this->getOnDelete($name, Punishment::TYPE_IP_BAN));
                }
            });
            $provider->asyncCheckPunished($id, Punishment::TYPE_FREEZE, function (array $result) use ($player, $id, $name): void {
                /** @var Punishment $punishment */
                $punishment = Punishment::fromDatabaseQuery($result, 0, Punishment::TYPE_FREEZE);
                if ($punishment !== null) {
                    $until = $punishment->getUntil();
                    if (Utilities::isStillPunished($until)) {
                        $this->plugin->getFrozen()->action($player);
                        if($player->loggedIn)
                            $player->sendMessage($this->plugin->resolvePunishmentMessage(Punishment::TYPE_FREEZE, $punishment->getReason(), $until));
                    } else
                        $this->plugin->getProvider()->asyncRemovePunishment($id, Punishment::TYPE_IP_BAN, $this->getOnDelete($name, Punishment::TYPE_FREEZE));
                }
            });
            $provider->asyncCheckPunished($id, Punishment::TYPE_MUTE, function (array $result) use ($player, $id, $name): void {
                /** @var Punishment $punishment */
                $punishment = Punishment::fromDatabaseQuery($result, 0, Punishment::TYPE_MUTE);
                if ($punishment !== null) {
                    $until = $punishment->getUntil();
                    if (Utilities::isStillPunished($until)) {
                        $this->plugin->getMuted()->action($player);
                        if($player->loggedIn)
                            $player->sendMessage($this->plugin->resolvePunishmentMessage(Punishment::TYPE_MUTE, $punishment->getReason(), $until));
                        return;
                    }
                    $this->plugin->getProvider()->asyncRemovePunishment($id, Punishment::TYPE_MUTE, $this->getOnDelete($name, Punishment::TYPE_MUTE));
                }
            });
        });
    }
    /**
     * @param PlayerQuitEvent $event
     */
    public function onQuit(PlayerQuitEvent $event): void{
        $name = $event->getPlayer()->getName();
        $this->plugin->getPlayerData()->unset($name);
        unset($this->deviceIDs[$name]);
    }
    /**
     * @param PlayerChatEvent $event
     */
    public function onTalk(PlayerChatEvent $event): void{
        $player = $event->getPlayer();

        if($this->plugin->getMuted()->checkState($player)) {
            $event->setCancelled();
            return;
        }

        $msg = $event->getMessage();

        if($this->plugin->getConfig()->getNested('Staff Chat.Enabled')) {
            $toggledStaffChat = $this->plugin->getStaffChatToggled();
            $staffChat = $this->plugin->getStaffChat();

            if($player->hasPermission('moderation.staff_chat'))
                $staffChat->addToStaffChat($player);
            else
                $staffChat->removeFromStaffChat($player);

            if($staffChat->isInStaffChat($player)) {
                if($msg[0] === $this->staffChatChar) {
                    $msg = substr($msg, 1);
                    if($toggledStaffChat->checkState($player))
                        $event->setMessage($msg);
                    else {
                        $staffChat->sendMessage($player, $msg);
                        $event->setCancelled();
                    }
                } elseif($toggledStaffChat->checkState($player)) {
                    $staffChat->sendMessage($player, $msg);
                    $event->setCancelled();
                }
            }
        }
    }
    /**
     * @param PlayerMoveEvent $event
     */
    public function onMove(PlayerMoveEvent $event): void{
        $player = $event->getPlayer();
        if($this->plugin->getFrozen()->checkState($player))
            $player->setImmobile();
    }
    /**
     * @param EntityDamageEvent $event
     */
    public function onTap(EntityDamageEvent $event): void
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            $player = $event->getEntity();
            $tapPunish = $this->plugin->getTapPunishUsers();
            if ($damager instanceof Player && $player instanceof Player && $tapPunish->checkState($damager) !== null) {
                $type = $this->plugin->getTapPunishUsers()->checkState($damager);
                $command = $this->plugin->getProvider()
                    ->resolveType($type, 'ban {player}', 'ban-ip {player}', 'mute {player}', 'freeze {player}', 'kick {player}', false);
                if($command !== null) {
                    $event->setCancelled();
                    $damager->getServer()->dispatchCommand($damager, strtr($command, ['{player}' => $player->getName()]));
                    $tapPunish->reverseAction($damager);
                }
            }
        }
    }
    /**
     * @param $name
     * @param $type
     * @return callable
     */
    private function getOnDelete($name, $type): callable{
        return function (int $rows) use ($name, $type): void{
            if ($rows !== 0) {
                $expiredMsg = $this->plugin->getProvider()->typeToString($type, false) . ' expired!';
                $this->plugin->getLogger()->info("$name's " . $expiredMsg);
            }
        };
    }
    /**
     * @return array
     */
    public function getDeviceIDs(): array{
        return $this->deviceIDs;
    }
}