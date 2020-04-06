<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 5/29/2019
 * Time: 12:35 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Form\Punishments;

use ARTulloss\ModerationPM\Database\Container\Punishment;
use ARTulloss\ModerationPM\Discord\Colors;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class BanCommand extends FormPunishmentModerationCommand{

    protected const TITLE = 'Ban {player}';
    public const TYPE = Punishment::TYPE_BAN;
    public const COLOR = Colors::RED;
    public const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully banned {player}!';
    public const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was banned by {staff}';

    public function onlinePunish(Player $player, string $message): void{
        $player->kick($message, false);
    }
}