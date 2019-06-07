<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/7/2019
 * Time: 9:55 AM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\StaffChat;

use pocketmine\Player;
use function str_replace;

class StaffChat{
    /** @var Player[] $staff */
    private $staff;
    /** @var string $format */
    private $format;
    /**
     * StaffChat constructor.
     * @param string $format
     */
    public function __constrvauct(string $format) {
        $this->format = $format;
    }
    /**
     * @param Player $player
     */
    public function addToStaffChat(Player $player): void{
        $this->staff[$player->getName()] = $player;
    }

    public function isInStaffChat(Player $player): bool{
        return isset($this->staff[$player->getName()]);
    }
    /**
     * @param Player $player
     */
    public function removeFromStaffChat(Player $player): void{
        unset($this->staff[$player->getName()]);
    }
    /**
     * @param Player $player
     * @param string $msg
     */
    public function sendMessage(Player $player, string $msg): void{
        $msg = str_replace(['{player}', '{msg}'], [$player->getName(), $msg], $this->format);
        foreach ($this->staff as $player)
            $player->sendMessage($msg);
    }
}