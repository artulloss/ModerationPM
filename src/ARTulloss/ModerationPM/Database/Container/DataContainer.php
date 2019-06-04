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
    //    var_dump($data);
        if(is_array($data) && $data !== [] && isset($data[$key])) {
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