<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/7/2019
 * Time: 10:41 AM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Miscellaneous;

use ARTulloss\ModerationPM\Commands\Arguments\MessageArgument;
use ARTulloss\ModerationPM\Commands\CommandConstants;
use ARTulloss\ModerationPM\Commands\ModerationCommand;
use ARTulloss\ModerationPM\Main;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class StaffChatCommand extends ModerationCommand {

    protected function prepare(): void{
        $this->registerArgument(0, new MessageArgument('message', true));
        $this->setPermission(Main::PERMISSION_PREFIX . 'staff_chat');
    }
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if($sender instanceof Player) {
            if($this->testPermission($sender)) {

                $staffChat = $this->plugin->getStaffChat();

                if(!$staffChat->isInStaffChat($sender)) {
                    $staffChat->addToStaffChat($sender);
                }

                if(isset($args['message'])) {
                    $staffChat->sendMessage($sender, $args['message']);
                    return;
                }

                $staffChatToggled = $this->plugin->getStaffChatToggled();

                if($staffChatToggled->checkState($sender)) {
                    $staffChatToggled->reverseAction($sender);
                    $sender->sendMessage(TextFormat::GREEN . 'Staff chat disabled!');
                    return;
                }
                $staffChatToggled->action($sender);
                $sender->sendMessage(TextFormat::GREEN . 'Staff chat enabled!');
            }
        } elseif(isset($args['message'])) {
            $staffChat = $this->plugin->getStaffChat();
            if(!$staffChat->isInStaffChat($sender))
                $staffChat->addToStaffChat($sender);
            $staffChat->sendMessage($sender, $args['message']);
        } else
            $this->sendUsage();
    }
}