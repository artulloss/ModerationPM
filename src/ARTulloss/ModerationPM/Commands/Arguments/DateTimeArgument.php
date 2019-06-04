<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/4/2019
 * Time: 9:10 AM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Arguments;

use ARTulloss\ModerationPM\Utilities\Utilities;
use CortexPE\Commando\args\RawStringArgument;
use pocketmine\command\CommandSender;
use function preg_match;

class DateTimeArgument extends RawStringArgument{

    public function canParse(string $testString, CommandSender $sender): bool{
        return (bool) preg_match(Utilities::DATE_TIME_REGEX, $testString);
    }

    public function getSpanLength(): int{
        return 2;
    }
}