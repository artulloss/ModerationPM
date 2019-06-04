<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/4/2019
 * Time: 2:21 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Arguments;

use pocketmine\command\CommandSender;
use function strtolower;

class ForeverArgument extends DateTimeArgument{

    public function canParse(string $testString, CommandSender $sender): bool{
        return strtolower($testString) === 'forever';
    }
    public function getSpanLength(): int{
        return 1;
    }
}