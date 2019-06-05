<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/4/2019
 * Time: 7:18 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\TouchPunish;

use ARTulloss\ModerationPM\Commands\CommandConstants;
use ARTulloss\ModerationPM\Commands\ModerationCommand;
use ARTulloss\ModerationPM\Database\Container\Punishment;
use ARTulloss\ModerationPM\Main;
use CortexPE\Commando\args\RawStringArgument;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class TouchPunish extends ModerationCommand implements CommandConstants{

    public function __construct(Main $main, string $name, string $description = "", array $aliases = []) {
        parent::__construct($main, $name, $description, $aliases);
        $this->setPermission(Main::PERMISSION_PREFIX . 'touch_punish');
    }

    protected function prepare(): void{
        $this->registerArgument(0, new RawStringArgument('type'));
    }
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if($sender instanceof Player) {
            if($this->testPermission($sender)) {
                if(isset($args['type'])) {
                    switch ($args['type']) {
                        case 'ban':
                            $type = Punishment::TYPE_BAN;
                            break;
                        case 'ip_ban':
                        case 'ipban':
                            $type = Punishment::TYPE_IP_BAN;
                            break;
                        case 'mute':
                            $type = Punishment::TYPE_MUTE;
                            break;
                        case 'freeze':
                            $type = Punishment::TYPE_FREEZE;
                            break;
                        default:
                            $this->sendError(self::ERR_INVALID_ARG_VALUE, ['value' => $args['type'], 'position' => 0]);
                            return;
                    }
                } else {
                    $this->sendUsage();
                    return;
                }
                $sender->sendMessage(TextFormat::GREEN . "You're in touch punish mode!");
                $this->plugin->getTapPunishUsers()->action($sender, $type);
            }
        } else
            $sender->sendMessage(self::PLAYER_ONLY);
    }
}