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
use pocketmine\plugin\Plugin;

class IntContainer
{
    /** @var Plugin $plugin */
    protected $plugin;
    /** @var bool $cache */
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
        return $this->cache[$player->getName()] ?? null;
    }
}