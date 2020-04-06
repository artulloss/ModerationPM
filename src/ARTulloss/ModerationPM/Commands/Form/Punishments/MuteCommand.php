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

class MuteCommand extends FormPunishmentModerationCommand{

    protected const TITLE = 'Mute {player}';
    public const TYPE = Punishment::TYPE_MUTE;
    public const COLOR = Colors::GRAY;
    public const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully muted {player}!';
    public const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was muted by {staff}';

    /**
     * @param Player $player
     * @param string $message
     */
    public function onlinePunish(Player $player, string $message): void{
        $player->sendMessage($message);
        $this->plugin->getMuted()->action($player);
    }
}