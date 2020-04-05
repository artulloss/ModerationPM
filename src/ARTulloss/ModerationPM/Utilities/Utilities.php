<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 5/29/2019
 * Time: 12:09 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Utilities;

use ARTulloss\ModerationPM\Database\Container\Punishment;
use pocketmine\Player;
use pocketmine\Server;
use DateTime;

class Utilities{
    public const DATE_TIME_REGEX = '/^(?:[0-9]+ )(?:seconds?|minutes?|hours?|days?|weeks?|months?|years?)$/i';
    public const DATE_TIME_REGEX_FAILED = "{length} violates length parameters! Must be a valid date time string";
    /**
     * Hybrid between the below
     * @param string $name
     * @return Player|null
     */
    public static function getPlayer(string $name): ?Player{
        return self::getPlayerExact($name) ?? self::getPlayerLoose($name);
    }
    /**
     * @param string $name
     *
     * @return Player|null
     */
    public static function getPlayerLoose(string $name): ?Player{
        $found = null;
        $name = strtolower($name);
        $delta = PHP_INT_MAX;
        foreach(Server::getInstance()->getOnlinePlayers() as $player){
            if(stripos($player->getDisplayName(), $name) === 0){
                $curDelta = strlen($player->getName()) - strlen($name);
                if($curDelta < $delta){
                    $found = $player;
                    $delta = $curDelta;
                }
                if($curDelta === 0){
                    break;
                }
            }
        }
        return $found;
    }
    /**
     * @param string $name
     *
     * @return Player|null
     */
    public static function getPlayerExact(string $name): ?Player{
        $name = strtolower($name);
        foreach (Server::getInstance()->getOnlinePlayers() as $player) {
            if (strtolower($player->getDisplayName()) === $name) {
                return $player;
            }
        }
        return null;
    }
    /**
     * @param int $until
     * @return bool
     * @throws \Exception
     */
    public static function isStillPunished(int $until): bool{
        $remaining = $until - (new DateTime())->getTimestamp();
        if($until === Punishment::FOREVER || $remaining > 0)
            return true;
        return false;
    }
    /**
     * @param string $string
     * @return string
     */
    public static function hash(string $string): string{
        $front = '1O"iQWl<';
        $back = '^83M3an6';
        return hash('sha256', $front . $string . $back);
    }

    /**
     * Debug function
     * @param $dump
     * @return mixed
     */
    public static function dumpReturn($dump) {
        return $dump;
    }
}