<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/4/2019
 * Time: 11:41 AM
 */
declare(strict_types=1);


namespace ARTulloss\ModerationPM\Commands\Form\Punishments;

use ARTulloss\ModerationPM\Database\Container\Punishment;
use ARTulloss\ModerationPM\Discord\Colors;
use pocketmine\utils\TextFormat;

class BanIPCommand extends BanCommand
{
    protected const TITLE = 'IP Ban {player}';
    public const TYPE = Punishment::TYPE_IP_BAN;
    public const COLOR = Colors::RED;
    public const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully IP banned {player}!';
    public const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was IP banned by {staff}';
}