<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/2/2019
 * Time: 2:15 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Database\Container;

/**
 * Class PlayerData
 * Provides a consistent object for use with commands
 * @package ARTulloss\ModerationPM\Database
 */
class PlayerData extends DataContainer{
    /** @var int $id */
    private $id;
    /** @var string $name */
    private $name;
    /** @var string $xuid */
    private $xuid;
    /** @var string $deviceID */
    private $deviceID;
    /** @var string $ip */
    private $ip;
    /**
     * PlayerData constructor.
     * @param int $id
     * @param string $name
     * @param string $xuid
     * @param string $device_id
     * @param string $ip
     */
    private function __construct(int $id, string $name, string $xuid, string $device_id, string $ip) {
        $this->id = $id;
        $this->name = $name;
        $this->xuid = $xuid;
        $this->deviceID = $device_id;
        $this->ip = $ip;
    }
    /**
     * @return string
     */
    public function getName(): string{
        return $this->name;
    }
    /**
     * @return string
     */
    public function getXUID(): string{
        return $this->xuid;
    }
    /**
     * @return string
     */
    public function getDeviceID(): string{
        return $this->deviceID;
    }
    /**
     * @return string
     */
    public function getHashedIP(): string{
        return $this->ip;
    }
    /**
     * @return int
     */
    public function getID(): int{
        return $this->id;
    }
    /**
     * @param array $data
     * @param int $key
     * @return PlayerData|null
     */
    public static function fromDatabaseQuery(array $data, $key = 0): ?DataContainer{
        if(self::hasNecessary($data, $key, ['id', 'name', 'xuid', 'device_id', 'ip']))
            return new PlayerData($data['id'], $data['name'], $data['xuid'], $data['device_id'], $data['ip']);
        return null;
    }
}