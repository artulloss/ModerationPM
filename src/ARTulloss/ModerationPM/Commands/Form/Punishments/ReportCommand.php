<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/7/2020
 * Time: 6:34 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Form\Punishments;

use ARTulloss\ModerationPM\Database\Container\Punishment;
use ARTulloss\ModerationPM\Discord\Colors;
use ARTulloss\ModerationPM\Main;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use function str_replace;

class ReportCommand extends FormNotStoredPunishmentModerationCommand {
    protected const TITLE = 'Report {player}';
    public const TYPE = Punishment::TYPE_REPORT;
    public const COLOR = Colors::ORANGE;
    public const MESSAGE_SUCCESS = TextFormat::GREEN . 'Successfully reported {player}!';
    public const MESSAGE_BROADCAST = TextFormat::GREEN . '{player} was reported by {staff}';
    public const REPORT_FORMAT = 'Report on {reported} from {player} with reason: {reason}';
    /**
     * @param CommandSender $sender
     * @param Player $player
     * @param array $result
     * @throws \Exception
     */
    protected function callback(CommandSender $sender, Player $player, array $result): void{
        $sender->sendMessage(str_replace('{player}', $player->getName(), self::MESSAGE_SUCCESS));
        $this->logReport($sender, $player, $result['reason']);
        foreach ($sender->getServer()->getOnlinePlayers() as $player) {
            if($player->hasPermission(Main::PERMISSION_PREFIX . 'reports')) {
                $player->sendMessage(str_replace(['{reported}', '{player}', '{reason}'], [$result['name'], $sender->getName(), $result['reason']], self::REPORT_FORMAT));
            }
        }
    }
}