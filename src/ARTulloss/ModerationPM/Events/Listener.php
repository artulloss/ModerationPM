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
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\Player;
use function strtr;

class Listener implements PMListener{
    /** @var Main $plugin */
    private $plugin;
    /**
     * Listener constructor.
     * @param Main $plugin
     */
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onLogin(DataPacketReceiveEvent $event): void{
        $pk = $event->getPacket();
        if($pk instanceof LoginPacket) {
            $player = $event->getPlayer();
            $name = $pk->username;
            $xuid = $pk->xuid;
            $deviceID = $pk->clientData['DeviceId'];
            $provider = $this->plugin->getProvider();
            $provider->asyncGetPlayer($name, function (array $result) use ($provider, $player, $name, $xuid, $deviceID): void{
                if(PlayerData::fromDatabaseQuery($result) === null)
                    $provider->asyncRegisterPlayer($name, $xuid, $deviceID, $player->getAddress());
                $provider->asyncCheckPunished($name, Punishment::TYPE_BAN, function (array $result) use ($provider, $player, $name): void{
                    /** @var Punishment $punishment */
                    $punishment = Punishment::fromDatabaseQuery($result, 0, Punishment::TYPE_BAN);
                    if($punishment !== null) {
                        $until = $punishment->getUntil();
                        if(Utilities::isStillPunished($until)) {
                            $player->kick($this->plugin->resolvePunishmentMessage(Punishment::TYPE_BAN, $punishment->getReason(), $until), false);
                            return;
                        }
                        $this->plugin->getProvider()->asyncRemovePunishment($name, Punishment::TYPE_BAN, $this->getOnDelete($name, Punishment::TYPE_BAN));
                    }
                    $provider->asyncCheckPunished($name, Punishment::TYPE_IP_BAN, function (array $result) use ($player, $name): void{
                        $punishment = null;
                        foreach ($result as $key => $entry) {
                            $resultClone = $result;
                            /** @var Punishment $potentialPunishment
                             * @var Punishment|null $punishment */
                            $potentialPunishment = Punishment::fromDatabaseQuery($resultClone, $key, Punishment::TYPE_IP_BAN);
                            if ($potentialPunishment !== null and
                                ($potentialPunishment->getUntil() === Punishment::FOREVER) || ($punishment === null || $potentialPunishment->getUntil() > $punishment->getUntil()))
                                $punishment = $potentialPunishment;
                        }
                        if($punishment !== null) {
                            $until = $punishment->getUntil();
                            if(Utilities::isStillPunished($until)) {
                                $player->kick($this->plugin->resolvePunishmentMessage(Punishment::TYPE_IP_BAN, $punishment->getReason(), $until), false);
                                return;
                            }
                            $this->plugin->getProvider()->asyncRemovePunishment($name, Punishment::TYPE_IP_BAN, $this->getOnDelete($name, Punishment::TYPE_IP_BAN));
                        }
                    });
                    $provider->asyncCheckPunished($name, Punishment::TYPE_FREEZE, function (array $result) use ($player, $name): void{
                        /** @var Punishment $punishment */
                        $punishment = Punishment::fromDatabaseQuery($result, 0, Punishment::TYPE_FREEZE);
                        if($punishment !== null) {
                            $until = $punishment->getUntil();
                            if(Utilities::isStillPunished($until)) {
                                $player->sendMessage($this->plugin->resolvePunishmentMessage(Punishment::TYPE_FREEZE, $punishment->getReason(), $until));
                                $this->plugin->getFrozen()->action($player);
                            } else
                                $this->getOnDelete($name, Punishment::TYPE_FREEZE);
                        }
                    });
                    $provider->asyncCheckPunished($name, Punishment::TYPE_MUTE, function (array $result) use ($player, $name): void{
                        /** @var Punishment $punishment */
                        $punishment = Punishment::fromDatabaseQuery($result, 0, Punishment::TYPE_MUTE);
                        if($punishment !== null) {
                            $until = $punishment->getUntil();
                            if(Utilities::isStillPunished($until)) {
                                $this->plugin->getMuted()->action($player);
                                return;
                            }
                            $this->plugin->getProvider()->asyncRemovePunishment($name, Punishment::TYPE_MUTE, $this->getOnDelete($name, Punishment::TYPE_MUTE));
                            $this->plugin->getMuted()->reverseAction($player);
                        }
                    });
                });
            });
        }
    }
    /**
     * @param PlayerChatEvent $event
     */
    public function onTalk(PlayerChatEvent $event): void{
        $player = $event->getPlayer();
        if($this->plugin->getMuted()->checkState($player))
            $event->setCancelled();
    }
    public function onMove(PlayerMoveEvent $event): void{
        $player = $event->getPlayer();
        if($this->plugin->getFrozen()->checkState($player))
            $player->setImmobile();
        elseif($player->isImmobile())
            $player->setImmobile(false);
    }
    /**
     * @param EntityDamageEvent $event
     */
    public function onTap(EntityDamageEvent $event): void{
        if($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            $player = $event->getEntity();
            $tapPunish = $this->plugin->getTapPunishUsers();
            if($damager instanceof Player && $player instanceof Player && $tapPunish->checkState($damager) !== null) {
                $event->setCancelled();
                switch ($this->plugin->getTapPunishUsers()->checkState($damager)) {
                    case Punishment::TYPE_BAN;
                        $command = 'ban {player}';
                        break;
                    case Punishment::TYPE_IP_BAN;
                        $command = 'ban-ip {player}';
                        break;
                    case Punishment::TYPE_MUTE;
                        $command = 'mute {player}';
                        break;
                    case Punishment::TYPE_FREEZE;
                        $command = 'freeze {player}';
                        break;
                    default:
                        return;
                }
                $damager->getServer()->dispatchCommand($damager, strtr($command, ['{player}' => $player->getName()]));
                $tapPunish->reverseAction($damager);
            }
        }
    }
    /**
     * @param $name
     * @param $type
     * @return callable
     */
    private function getOnDelete($name, $type): callable {
        return function (int $rows) use ($name, $type): void {
            if ($rows !== 0) {
                $expiredMsg = $this->plugin->getProvider()->typeToString($type, false) . ' expired!';
                $this->plugin->getLogger()->info("$name's " . $expiredMsg);
            }
        };
    }
}