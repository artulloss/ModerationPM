<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 5/30/2019
 * Time: 8:51 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Database;

use ARTulloss\ModerationPM\Database\Container\Punishment;
use pocketmine\plugin\Plugin;
use http\Exception\InvalidArgumentException;
use function ucwords;

abstract class Provider{
    /** @var Plugin $plugin */
    protected $plugin;
    /** @var Punishment[] $punishments */
    protected $punishments;
    /**
     * Provider constructor.
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;
        if(!$this->isInitialized())
            $this->init();
    }
    /**
     * Create files or tables
     */
    abstract public function init(): void;
    /**
     * Has the initialization occurred?
     * @return bool
     */
    public function isInitialized(): bool {
        return $this->plugin->getConfig()->get('initialized', false);
    }
    public function onInitializationSuccess(): void{
        $this->plugin->getLogger()->info('Successfully initialized!');
        $config = $this->plugin->getConfig();
        $config->set('initialized');
        $config->save();
    }
    public function onInitializationFail(): void{
        $this->plugin->getLogger()->emergency('Something went wrong with the initialization of the database, disabling!');
        $this->plugin->getServer()->getPluginManager()->disablePlugin($this->plugin);
    }

    /**
     * Add a player to the data
     * @param string $name
     * @param string $xuid
     * @param string $deviceID
     * @param string $ip
     * @param callable|null $onComplete
     */
    abstract public function asyncRegisterPlayer(string $name, string $xuid, string $deviceID, string $ip, callable $onComplete = null): void;
    /**
     * @param string $name
     * @param callable $callback
     */
    abstract public function asyncGetPlayer(string $name, callable $callback): void;
    /**
     * @param string $name
     * @param int $type
     * @param string $staffName
     * @param string $reason
     * @param int|null $until
     * @param callable|null $onComplete
     */
    abstract public function asyncPunishPlayer(string $name, int $type, string $staffName, string $reason, int $until, callable $onComplete = null): void;
    /**
     * @param string $name
     * @param int $type
     * @param callable $callback
     */
    abstract public function asyncCheckPunished(string $name, int $type, callable $callback): void;
    /**
     * Pass punishments to callback
     * @param int $type
     * @param callable $callback
     */
    abstract public function asyncGetPunishments(int $type, callable $callback): void;
    /**
     * @param string $name
     * @param int $type
     * @param callable|null $callback
     */
    abstract public function asyncRemovePunishment(string $name, int $type, callable $callback = null): void;
    /**
     * @param int $type
     * @param bool $caps
     * @return string
     */
    public function typeToString(int $type, bool $caps = true): string{
        switch ($type) {
            case Punishment::TYPE_BAN:
                $return = 'ban';
                break;
            case Punishment::TYPE_IP_BAN:
                $return = 'ip ban';
                break;
            case Punishment::TYPE_MUTE:
                $return = 'mute';
                break;
            case Punishment::TYPE_FREEZE:
                $return = 'freeze';
        }
        if(isset($return))
            return $caps ? ucwords($return) : $return;
        throw new InvalidArgumentException('Invalid type, please use the constants provided');
    }

    public function stringToType(string $string): ?int{
        switch ($string) {
            case 'ban':
                return Punishment::TYPE_BAN;
                break;
            case 'ip_ban':
            case 'ipban':
                return Punishment::TYPE_IP_BAN;
            case 'mute':
                return Punishment::TYPE_MUTE;
            case 'freeze':
                return Punishment::TYPE_FREEZE;
            default:
                return null;
        }
    }
    /**
     * @param int $type
     * @param string $ban
     * @param string $ipBan
     * @param string $mute
     * @param string $freeze
     * @param bool $throwError
     * @return string|null
     */
    public function resolveType(int $type, string $ban, string $ipBan, string $mute, string $freeze, $throwError = true): ?string{
        switch ($type) {
            case Punishment::TYPE_BAN:
                return $ban;
            case Punishment::TYPE_IP_BAN:
                return $ipBan;
            case Punishment::TYPE_MUTE:
                return $mute;
            case Punishment::TYPE_FREEZE:
                return $freeze;
            default:
                if($throwError)
                    throw new InvalidArgumentException('Invalid type, please use the constants provided');
                return null;
        }
    }
}