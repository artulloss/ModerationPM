<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/4/2019
 * Time: 7:54 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands;

use pocketmine\utils\TextFormat;

interface CommandConstants{

    public const PLAYER_ONLY = TextFormat::RED . 'You must be a player to use this command';

}