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
    protected const TYPE = Punishment::TYPE_IP_BAN;
    protected const COLOR = Colors::RED;

    protected const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully IP banned {player}!';
}