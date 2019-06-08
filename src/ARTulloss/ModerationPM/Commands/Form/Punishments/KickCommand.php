<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/8/2019
 * Time: 1:50 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Form\Punishments;

use ARTulloss\ModerationPM\Database\Container\Punishment;
use ARTulloss\ModerationPM\Discord\Colors;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class KickCommand extends FormNotStoredNotStoredPunishmentModerationCommand{

    protected const TITLE = 'Kick {player}';
    public const TYPE = Punishment::TYPE_KICK;
    public const COLOR = Colors::YELLOW;
    public const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully kicked {player}!';

    /**
     * @param CommandSender $sender
     * @param Player $player
     * @param array $result
     * @throws \Exception
     */
    protected function callback(CommandSender $sender, Player $player, array $result): void{
        $reason = $result['reason'];
        $this->logKick($sender, $player, $reason);
        $player->kick($this->plugin->resolvePunishmentMessage(Punishment::TYPE_KICK, $reason), false);
    }
}