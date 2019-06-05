<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 5/30/2019
 * Time: 11:04 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Database;

use ARTulloss\ModerationPM\Main;
use ARTulloss\ModerationPM\Utilities\Utilities;
use pocketmine\utils\Utils;
use poggit\libasynql\SqlError;
use SOFe\AwaitGenerator\Await;
use Generator;
use Throwable;
use Closure;

abstract class SqlProvider extends Provider implements Queries{

    /** @var Main $plugin */
    protected $plugin;

    public function init(): void{
        Await::f2c(function (): Generator{

            $actions = [
                self::MODERATION_INIT_PLAYERS,
                self::MODERATION_INIT_BANS,
                self::MODERATION_INIT_IP_BANS,
                self::MODERATION_INIT_MUTES,
                self::MODERATION_INIT_FREEZES
            ];

            foreach ($actions as $action) {
                yield $this->asyncGenericQuery($action);
            }

        }, function () {
            $this->onInitializationSuccess();
        }, function (SqlError $error) {
            $this->plugin->getServer()->getLogger()->logException($error);
            $this->onInitializationFail();
        });
    }
    public function asyncRegisterPlayer(string $name, string $xuid, string $deviceID, string $ip, callable $onComplete = null): void{
        Await::f2c(function () use ($name, $xuid, $deviceID, $ip): Generator{
            yield $this->asyncInsert(Queries::MODERATION_UPSERT_PLAYERS, [
                'player_name' => $name,
                'xuid' => $xuid,
                'device_id' => $deviceID,
                'ip' => Utilities::hash($ip)
            ]);
        }, $onComplete, $this->getOnError());
    }
    public function asyncGetPlayer(string $name, callable $callback): void{
        Utils::validateCallableSignature(function (array $result): void{}, $callback);
        Await::f2c(function () use ($callback, $name): Generator{
            $select = yield $this->asyncSelect(Queries::MODERATION_GET_PLAYERS_PLAYER, [
                'player_name' => $name
            ]);
            $callback($select);
        }, null, $this->getOnError());
    }
    public function asyncPunishPlayer(string $name, int $type, string $staffName, string $reason, int $until, callable $onComplete = null): void{
        Await::f2c(function () use ($name, $type, $staffName, $reason, $until): Generator{
            $query = $this->resolveType($type, Queries::MODERATION_UPSERT_BANS, Queries::MODERATION_UPSERT_IP_BANS, Queries::MODERATION_UPSERT_MUTES, Queries::MODERATION_UPSERT_FREEZES);
            yield $this->asyncInsert($query, [
                'player_name' => $name,
                'staff_name' => $staffName,
                'reason' => $reason,
                'until' => $until
            ]);
        }, $onComplete, $this->getOnError());
    }
    public function asyncGetPunishments(int $type, callable $callback): void{
        Utils::validateCallableSignature(function (array $result): void{}, $callback);
        $query = $this->resolveType($type, self::MODERATION_GET_BANS_ALL, self::MODERATION_GET_IP_BANS_ALL, self::MODERATION_GET_MUTES_ALL, self::MODERATION_GET_FREEZES_ALL);
        Await::f2c(function () use ($query, $callback): Generator{
            $select = yield $this->asyncSelect($query);
            $callback($select);
        }, null, $this->getOnError());
    }
    public function asyncCheckPunished(string $name, int $type, callable $callback): void{
        Utils::validateCallableSignature(function (array $rows): void{}, $callback);
        $query = $this->resolveType($type, self::MODERATION_GET_BANS_PLAYER, self::MODERATION_GET_IP_BANS_PLAYER, self::MODERATION_GET_MUTES_PLAYER, self::MODERATION_GET_FREEZES_PLAYER);
        Await::f2c(function () use ($query, $name, $callback): Generator{
            $select = yield $this->asyncSelect($query, [
                'player_name' => $name
            ]);
            $callback($select);
        }, null, $this->getOnError());
    }
    public function asyncRemovePunishment(string $name, int $type, callable $callback = null): void{
        $query = $this->resolveType($type, self::MODERATION_DELETE_BANS, self::MODERATION_DELETE_IP_BANS, self::MODERATION_DELETE_MUTES, self::MODERATION_DELETE_FREEZES);
        Await::f2c(function () use ($query, $name, $callback) {
            $result = yield $this->asyncChange($query, [
                'player_name' => $name
            ]);
            if($callback !== null) {
                Utils::validateCallableSignature(function (int $rows): void{}, $callback);
                $callback($result);
            }
        }, null, $this->getOnError());

    }
    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncGenericQuery(string $query, array $args = []): Generator{
        $this->plugin->getDatabase()->executeGeneric($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }
    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncSelect(string $query, array $args = []): Generator{
        $this->plugin->getDatabase()->executeSelect($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }
    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncInsert(string $query, array $args = []): Generator{
        $this->plugin->getDatabase()->executeInsert($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }
    /**
     * @param string $query
     * @param array $args
     * @return Generator
     */
    protected function asyncChange(string $query, array $args = []): Generator{
        $this->plugin->getDatabase()->executeChange($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }
    /**
     * @return Closure
     */
    public function getOnError(): Closure{
        return function (Throwable $error): void{
            $this->plugin->getServer()->getLogger()->logException($error);
        };
    }
}