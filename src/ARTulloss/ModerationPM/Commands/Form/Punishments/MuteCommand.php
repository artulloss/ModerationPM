<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 5/29/2019
 * Time: 10:25 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Form\Punishments;

use ARTulloss\ModerationPM\Database\Container\Punishment;
use ARTulloss\ModerationPM\Discord\Colors;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class MuteCommand extends FormPunishmentModerationCommandOnline{

    protected const TITLE = 'Mute {player}';
    protected const TYPE = Punishment::TYPE_MUTE;
    protected const COLOR = Colors::GRAY;
    protected const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully muted {player}!';

    /**
     * @param Player $player
     * @param string $message
     */
    public function onlinePunish(Player $player, string $message): void{
        $player->sendMessage($message);
        $this->plugin->getMuted()->action($player);
    }
}