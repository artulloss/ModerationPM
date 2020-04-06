<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/8/2019
 * Time: 2:11 PM
 */

namespace ARTulloss\ModerationPM\Commands\Form\Punishments;

use ARTulloss\ModerationPM\Database\Container\Punishment;
use ARTulloss\ModerationPM\Discord\Colors;
use pocketmine\utils\TextFormat;

interface PunishmentCommand{
    public const TYPE = Punishment::TYPE_BAN;
    public const COLOR = Colors::RED;
    public const MESSAGE_SUCCESS = TextFormat::GREEN . 'Success!';
    public const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was {action} by {staff}';
}