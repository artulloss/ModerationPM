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
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class UnmuteCommand extends ReversePunishmentCommand{

    protected const TYPE = Punishment::TYPE_MUTE;
    protected const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully unmuted {player}!';
    protected const MESSAGE_SUCCESS_ONLINE = TextFormat::GREEN . 'You were unmuted!';
    protected const MESSAGE_FAIL = TextFormat::RED . 'Player was not muted!';
    protected const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was unmuted by {staff}';

    public function onlineUnpunish(Player $player, string $message): void{
        $this->plugin->getMuted()->reverseAction($player);
        $player->sendMessage($message);
    }
}