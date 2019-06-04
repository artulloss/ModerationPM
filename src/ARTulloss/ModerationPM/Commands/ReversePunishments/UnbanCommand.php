<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/3/2019
 * Time: 2:17 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\ReversePunishments;

use ARTulloss\ModerationPM\Database\Container\Punishment;
use pocketmine\utils\TextFormat;

class UnbanCommand extends ReversePunishmentCommand{

    protected const TYPE = Punishment::TYPE_BAN;
    protected const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully unbanned {player}!';
    protected const MESSAGE_FAIL = TextFormat::RED . 'Player was not banned!';

}