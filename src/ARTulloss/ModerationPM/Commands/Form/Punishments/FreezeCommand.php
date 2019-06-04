<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 5/29/2019
 * Time: 11:19 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Form\Punishments;

use ARTulloss\ModerationPM\Database\Container\Punishment;
use ARTulloss\ModerationPM\Discord\Colors;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class FreezeCommand extends FormPunishmentModerationCommandOnline{

    protected const TITLE = 'Freeze {player}';
    protected const TYPE = Punishment::TYPE_FREEZE;
    protected const COLOR = Colors::BLUE;

    protected const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully froze {player}!';

    public function onlinePunish(Player $player, string $message): void{
        $player->sendMessage($message);
        $player->setImmobile();
    }
}