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

class FreezeCommand extends FormPunishmentModerationCommand{

    protected const TITLE = 'Freeze {player}';
    public const TYPE = Punishment::TYPE_FREEZE;
    public const COLOR = Colors::BLUE;
    public const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully froze {player}!';
    public const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was frozen by {staff}';

    public function onlinePunish(Player $player, string $message): void{
        $player->sendMessage($message);
        $this->plugin->getFrozen()->action($player);
        $player->setImmobile();
    }
}