<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/2/2019
 * Time: 6:28 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Database\Container;

abstract class DataContainer{

    public const NO_KEY = -1;

    /**
     * @param array $data
     * @param int $key
     * @return DataContainer
     */
    abstract static public function fromDatabaseQuery(array $data, $key = 0): ?self;
    /**
     * @param array $data
     * @param int $key
     * @param array $checkForKeys
     * @return bool
     */
    protected static function hasNecessary(array &$data, int $key, array $checkForKeys): bool{
        if(is_array($data) && $data !== []) {
            if(isset($data[$key]) && $key !== self::NO_KEY)
                $data = $data[$key];
            foreach ($checkForKeys as $value) {
                if(!isset($data[$value]))
                    return false;
            }
            return true;
        }
        return false;
    }

}