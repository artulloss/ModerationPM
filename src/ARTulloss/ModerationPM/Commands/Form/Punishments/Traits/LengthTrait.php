<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/8/2019
 * Time: 1:56 PM
 */

namespace ARTulloss\ModerationPM\Commands\Form\Punishments\Traits;

use ARTulloss\ModerationPM\Utilities\Utilities;
use http\Exception\InvalidArgumentException;

trait LengthTrait{
    /** @var string[] $lengths */
    protected $lengths;

    /**
     * @param string[] $lengths
     */
    protected function setLengths(array $lengths): void{
        foreach ($lengths as $length)
            if(preg_match(Utilities::DATE_TIME_REGEX, $length) === 0 && strtolower($length) !== 'forever')
                throw new InvalidArgumentException(str_replace('{length}', $length, Utilities::DATE_TIME_REGEX_FAILED));
        $this->lengths = $lengths;
    }
}