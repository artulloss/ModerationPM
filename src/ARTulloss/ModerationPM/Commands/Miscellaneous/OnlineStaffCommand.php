<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 4/14/2020
 * Time: 6:33 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Miscellaneous;

use ARTulloss\ModerationPM\Commands\ModerationCommand;
use ARTulloss\ModerationPM\Main;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use function count;
use function implode;

class OnlineStaffCommand extends ModerationCommand{
    protected function prepare(): void{
        $this->setPermission(Main::PERMISSION_PREFIX . 'onlinestaff');
    }
    /**
     * @param CommandSender $sender
     * @param string $aliasUsed
     * @param array $args
     */
    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if($this->testPermission($sender)) {
            $staff = [];
            foreach ($sender->getServer()->getOnlinePlayers() as $player)
                if($player->hasPermission($this->getPermission()))
                    $staff[] = $player->getName();
            $sender->sendMessage('There are ' . TextFormat::BLUE . count($staff) . TextFormat::WHITE . ' staff online!');
            if(count($staff) !== 0)
                $sender->sendMessage('The staff are: ' . implode(', ', $staff));
        }
    }
}