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
use function var_dump;

class TouchPunish extends ModerationCommand implements CommandConstants{

    public function __construct(Main $main, string $name, string $description = "", array $aliases = []) {
        parent::__construct($main, $name, $description, $aliases);
        $this->setPermission(Main::PERMISSION_PREFIX . 'touch_punish');
    }

    protected function prepare(): void{
        $this->registerArgument(0, new RawStringArgument('type', true));
    }
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if($sender instanceof Player) {
            if($this->testPermission($sender)) {

                $tapPunish = $this->plugin->getTapPunishUsers();

                // Toggle it off

                if($tapPunish->checkState($sender) !== null) {
                    $tapPunish->reverseAction($sender);
                    $sender->sendMessage(TextFormat::GREEN . 'Touch punish was toggled off!');
                    return;
                }

                if(isset($args['type'])) {
                    $type = $this->provider->stringToType($args['type']);
                } else {
                    $this->sendUsage();
                    return;
                }
                $sender->sendMessage(TextFormat::GREEN . "You're in touch punish mode! Type the command again to toggle it off!");

                if($type === null)
                    $this->sendError(self::ERR_INVALID_ARG_VALUE, ['value' => $args['type'], 'position' => 0]);
                else
                    $tapPunish->action($sender, $type);
            }
        } else
            $sender->sendMessage(self::PLAYER_ONLY);
    }
}