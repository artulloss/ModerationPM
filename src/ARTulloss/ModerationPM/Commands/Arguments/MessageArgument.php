<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/4/2019
 * Time: 9:20 AM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Arguments;

use CortexPE\Commando\args\TextArgument;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use function strlen;

class MessageArgument extends TextArgument{

    public function canParse(string $testString, CommandSender $sender): bool{
        return strlen($testString) <= 32; // Max in varchar reason
    }

    public function getNetworkType(): int{
        return AvailableCommandsPacket::ARG_TYPE_MESSAGE;
    }
}