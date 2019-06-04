<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/1/2019
 * Time: 11:18 AM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Database\Container;

use DateTime;

class Punishment extends DataContainer{

    public const TYPE_BAN = 1;
    public const TYPE_IP_BAN = 2;
    public const TYPE_MUTE = 3;
    public const TYPE_FREEZE = 4;

    public const FOREVER = 0;

    /** @var string $playerName */
    private $playerName;
    /** @var string $staffName */
    private $staffName;
    /** @var string $reason */
    private $reason;
    /** @var int $type */
    private $type;
    /** @var int $until */
    private $until;
    /**
     * Punishment constructor.
     * @param string $playerName
     * @param string $staffName
     * @param int $type
     * @param string $reason
     * @param int $until
     */
    private function __construct(string $playerName, string $staffName, int $type, string $reason, int $until) {
        $this->playerName = $playerName;
        $this->staffName = $staffName;
        $this->reason = $reason;
        $this->type = $type;
        $this->until = $until;
    }
    /**
     * @return string
     */
    public function getPlayerName(): string{
        return $this->playerName;
    }
    /**
     * @return string
     */
    public function getStaffName(): string{
        return $this->staffName;
    }
    /**
     * @return string
     */
    public function getReason(): string{
        return $this->reason;
    }
    /**
     * @return int
     */
    public function getType(): int{
        return $this->type;
    }
    /**
     * Returns a unix timestamp
     * @return int|null
     */
    public function getUntil(): ?int{
        return $this->until;
    }
    /**
     * @return DateTime|null
     * @throws \Exception
     */
    public function getUntilDateTime(): ?DateTime{
        $return = null;
        $until = $this->getUntil();
        if($until !== null) {
            $return = new DateTime();
            $return->setTimestamp($until);
        }
        return $return;
    }
    /**
     * @param array $data
     * @param int $key
     * @param int $type
     * @return DataContainer|null
     */
    public static function fromDatabaseQuery(array $data, $key = 0, int $type = self::TYPE_BAN): ?DataContainer{
        if (self::hasNecessary($data, $key, ['name', 'staff_name', 'reason', 'until']))
            return new Punishment($data['name'], $data['staff_name'], $type, $data['reason'], $data['until']);
        return null;
    }
}