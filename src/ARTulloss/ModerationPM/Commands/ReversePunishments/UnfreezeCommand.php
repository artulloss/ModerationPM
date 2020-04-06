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

class UnfreezeCommand extends ReversePunishmentCommand{

    protected const TYPE = Punishment::TYPE_FREEZE;
    protected const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully thawed {player}!';
    protected const MESSAGE_SUCCESS_ONLINE = TextFormat::GREEN . 'You were thawed!';
    protected const MESSAGE_FAIL = TextFormat::RED . 'Player was not frozen!';
    protected const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was thawed by {staff}';

    public function onlineUnpunish(Player $player, string $message): void{
        $this->plugin->getFrozen()->reverseAction($player);
        $player->setImmobile(false);
        $player->sendMessage($message);
    }
}