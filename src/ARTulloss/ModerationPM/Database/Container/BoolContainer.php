<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/4/2019
 * Time: 7:47 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Database\Container;

use pocketmine\Player;
use pocketmine\plugin\Plugin;

class BoolContainer{
    /** @var Plugin $plugin */
    protected $plugin;
    /** @var bool[] $cache */
    protected $cache;
    /**
     * Cache constructor.
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;
    }
    /**
     * @param Player $player
     */
    public function action(Player $player): void{
        $this->cache[$player->getName()] = true;
    }
    /**
     * @param Player $player
     */
    public function reverseAction(Player $player): void{
        unset($this->cache[$player->getName()]);
    }
    /**
     * @param Player $player
     * @return bool
     */
    public function checkState(Player $player): bool{
        return $this->cache[$player->getName()] ?? false;
    }
}