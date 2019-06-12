<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/4/2019
 * Time: 8:00 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Database\Container;

use pocketmine\Player;

class IntContainer{
    /** @var bool $cache */
    protected $cache;
    /**
     * @param Player $player
     * @param int $value
     */
    public function action(Player $player, int $value): void{
        $this->cache[$player->getName()] = $value;
    }
    /**
     * @param Player $player
     */
    public function reverseAction(Player $player): void{
        unset($this->cache[$player->getName()]);
    }
    /**
     * @param Player $player
     * @return int
     */
    public function checkState(Player $player): ?int{
        return $this->checkStateByName($player->getName());
    }
    /**
     * @param string $name
     * @return int|null
     */
    public function checkStateByName(string $name): ?int{
        return $this->cache[$name] ?? null;
    }
}