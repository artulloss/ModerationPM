<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/4/2019
 * Time: 11:43 AM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\ReversePunishments;

use ARTulloss\ModerationPM\Database\Container\Punishment;
use pocketmine\utils\TextFormat;

class UnBanIPCommand extends ReversePunishmentCommand{

    protected const TYPE = Punishment::TYPE_IP_BAN;
    protected const MESSAGE_SUCCESS = TextFormat::GREEN . "Successfully unbanned {player}'s IP!";
    protected const MESSAGE_FAIL = TextFormat::RED . "Player's IP was not banned!";

}