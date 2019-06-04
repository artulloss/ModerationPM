<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/1/2019
 * Time: 9:13 AM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Database;

interface Queries
{
    public const MODERATION_INIT_PLAYERS = 'moderation.init.players';
    public const MODERATION_INIT_BANS = 'moderation.init.bans';
    public const MODERATION_INIT_IP_BANS = 'moderation.init.ip_bans';
    public const MODERATION_INIT_MUTES = 'moderation.init.mutes';
    public const MODERATION_INIT_FREEZES = 'moderation.init.freezes';

    public const MODERATION_UPSERT_PLAYERS = 'moderation.upsert.players';
    public const MODERATION_UPSERT_BANS = 'moderation.upsert.bans';
    public const MODERATION_UPSERT_IP_BANS = 'moderation.upsert.ip_bans';
    public const MODERATION_UPSERT_MUTES = 'moderation.upsert.mutes';
    public const MODERATION_UPSERT_FREEZES = 'moderation.upsert.freezes';

    public const MODERATION_GET_PLAYERS_ALL = 'moderation.get.players.all';
    public const MODERATION_GET_BANS_ALL = 'moderation.get.bans.all';
    public const MODERATION_GET_IP_BANS_ALL = 'moderation.get.ip_bans.all';
    public const MODERATION_GET_MUTES_ALL = 'moderation.get.mutes.all';
    public const MODERATION_GET_FREEZES_ALL = 'moderation.get.freezes.all';

    public const MODERATION_GET_PLAYERS_PLAYER = 'moderation.get.players.player';
    public const MODERATION_GET_BANS_PLAYER = 'moderation.get.bans.player';
    public const MODERATION_GET_IP_BANS_PLAYER = 'moderation.get.ip_bans.player';
    public const MODERATION_GET_MUTES_PLAYER = 'moderation.get.mutes.player';
    public const MODERATION_GET_FREEZES_PLAYER = 'moderation.get.freezes.player';

    public const MODERATION_DELETE_BANS = 'moderation.delete.bans';
    public const MODERATION_DELETE_IP_BANS = 'moderation.delete.ip_bans';
    public const MODERATION_DELETE_MUTES = 'moderation.delete.mutes';
    public const MODERATION_DELETE_FREEZES = 'moderation.delete.freezes';
}