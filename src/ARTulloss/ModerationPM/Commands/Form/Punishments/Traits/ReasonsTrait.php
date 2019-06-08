<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/8/2019
 * Time: 1:58 PM
 */

namespace ARTulloss\ModerationPM\Commands\Form\Punishments\Traits;

trait ReasonsTrait{
    /** @var string[] $reasons */
    protected $reasons;
    /**
     * @param string[] $reasons
     */
    protected function setReasons(array $reasons): void{
        $this->reasons = $reasons;
    }
}