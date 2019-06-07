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
use ARTulloss\ModerationPM\Commands\ModerationCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class StaffChatCommand extends ModerationCommand{

    protected function prepare(): void{
        $this->registerArgument(0, new MessageArgument('message', true));
    }
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if($sender instanceof Player) {
            if(isset($args['message'])) {
                $this->plugin->getStaffChat()->sendMessage($sender, $args['message']);
                return;
            }

            $staffChatToggled = $this->plugin->getStaffChatToggled();

            if($staffChatToggled->checkState($sender)) {
                $staffChatToggled->reverseAction($sender);
                return;
            }
            $staffChatToggled->action($sender);
        }

    }
}