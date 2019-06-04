<?php

declare(strict_types=1);

namespace ARTulloss\ModerationPM;

use ARTulloss\ModerationPM\Commands\Form\Punishments\BanCommand;
use ARTulloss\ModerationPM\Commands\Form\Punishments\BanIPCommand;
use ARTulloss\ModerationPM\Commands\Form\Punishments\FreezeCommand;
use ARTulloss\ModerationPM\Commands\Form\Punishments\MuteCommand;
use ARTulloss\ModerationPM\Commands\ReversePunishments\UnbanCommand;
use ARTulloss\ModerationPM\Commands\ReversePunishments\UnfreezeCommand;
use ARTulloss\ModerationPM\Commands\ReversePunishments\UnBanIPCommand;
use ARTulloss\ModerationPM\Commands\ReversePunishments\UnmuteCommand;
use ARTulloss\ModerationPM\Database\Cache\Cache;
use ARTulloss\ModerationPM\Database\Container\Punishment;
use ARTulloss\ModerationPM\Database\MySqlProvider;
use ARTulloss\ModerationPM\Database\Provider;
use ARTulloss\ModerationPM\Discord\DiscordLogger;
use ARTulloss\ModerationPM\Events\Listener;
use ARTulloss\ModerationPM\Utilities\Utilities;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\PacketHooker;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use poggit\libasynql\CallbackTask;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;
use DateTime;
use Exception;
use function strtr;
use function implode;

class Main extends PluginBase{

    /** @var DataConnector $database */
    private $database;
    /** @var Provider $provider */
    private $provider;
    /** @var Config $commandConfig */
    private $commandConfig;
    /** @var DiscordLogger $discordLogger */
    private $discordLogger;
    /** @var Cache $muted */
    private $muted;
    /** @var Cache $frozen */
    private $frozen;
    /**
     * @throws \CortexPE\Commando\exception\HookAlreadyRegistered
     */
	public function onEnable(): void{
	    $this->initConfigs();
	    $this->registerDatabase(); // May disable the plugin
	    if($this->isEnabled()) {
            $this->registerPacketHook();
            $this->registerCommands();
            $this->getServer()->getPluginManager()->registerEvents(new Listener($this), $this);
            if($this->getCommandConfig()->getNested('Discord.Enable'))
                $this->discordLogger = new DiscordLogger($this);
            $this->registerCache();
        }
	}
	public function initConfigs(): void{
	    $this->saveDefaultConfig();
	    $this->saveResource('commands.yml');
    }
    /**
     * @throws \CortexPE\Commando\exception\HookAlreadyRegistered
     */
	public function registerPacketHook(): void{
        if(!PacketHooker::isRegistered())
            PacketHooker::register($this);
    }
    public function registerCommands(): void{
	    $correct = false;
	    $this->commandConfig = new Config($this->getDataFolder() . 'commands.yml', Config::DETECT, [], $correct);
	    $config = $this->commandConfig;
	    $map = $this->getServer()->getCommandMap();
	    if($correct) {
	        // Fallbacks in case issue
	        $error = [TextFormat::RED . 'Error' . TextFormat::RESET];
	        $forever = ['Forever'];
            $commands = [
                new BanCommand($this, 'ban', 'Ban a player!', $config->getNested('Ban.Lengths') ?? $forever, $config->getNested('Ban.Reasons') ?? $error),
                new BanIPCommand($this, 'ban-ip', 'IP Ban a player!', $config->getNested('Ip Ban.Lengths') ?? $forever, $config->getNested('Ip Ban.Reasons') ?? $error),
                new MuteCommand($this,'mute', 'Mute a player!', $config->getNested('Mute.Lengths') ?? $forever, $config->getNested('Mute.Reasons') ?? $error),
                new FreezeCommand($this,'freeze', 'Freeze a player', $config->getNested('Freeze.Lengths') ?? $forever, $config->getNested('Freeze.Reasons') ?? $error),
                new UnbanCommand($this, 'unban', 'Unban a player!'),
                new UnBanIPCommand($this, 'unban-ip', "Unban a player's IP!"),
                new UnmuteCommand($this, 'unmute', 'Unmute a player!'),
                new UnfreezeCommand($this, 'unfreeze', 'Unfreeze a player!', ['thaw'])
            ];
            /**
             * @var BaseCommand[] $commands
             */
            foreach ($commands as $command) {
                if(($oldCmd = $map->getCommand($command->getName())) && $oldCmd !== null) // Unregister previous commands
                    $map->unregister($oldCmd);
                $map->register($this->getName(), $command);
            }
        }
    }
    public function registerDatabase(): void{
	    $config = $this->getConfig();

        $this->database = libasynql::create($this, $config->get('database'), [
            'mysql' => 'mysql.sql'
        ]);
        $this->provider = new MySqlProvider($this);
    }
    public function registerCache(): void{
        $this->muted = new Cache($this, Punishment::TYPE_MUTE);
        $this->frozen = new Cache($this, Punishment::TYPE_FREEZE);
        $task = new CallbackTask(function (): void{
            $this->muted->refresh();
            $this->frozen->refresh();
        });
        $minutes = $this->getConfig()->getNested('database.cache');
        $this->getScheduler()->scheduleDelayedRepeatingTask($task, 1200 * $minutes, 1200 * $minutes);
    }
    /**
     * @return Config
     */
    public function getCommandConfig(): Config{
	    return $this->commandConfig;
    }
    /**
     * @return DataConnector
     */
    public function getDatabase(): DataConnector{
	    return $this->database;
    }
    /**
     * @return Provider
     */
    public function getProvider(): Provider{
        return $this->provider;
    }
    /**
     * @return Cache
     */
    public function getMuted(): Cache{
        return $this->muted;
    }
    /**
     * @return Cache
     */
    public function getFrozen(): Cache{
        return $this->frozen;
    }
    /**
     * @param int $type
     * @param string $reason
     * @param int $time
     * @return string
     * @throws Exception
     */
    public function resolvePunishmentMessage(int $type, string $reason, int $time): string{

        $format = $this->commandConfig->getNested($this->provider->typeToString($type) . '.Message') ?? ['Error'];
        $format = implode($format, TextFormat::EOL);
        $until = $time !== 0 ? ((new DateTime())->setTimestamp($time))->format('Y-m-d H:i:s') : 'Forever';
        return strtr($format, [
            '{reason}' => $reason,
            '{until}' => $until
        ]);
    }
    /**
     * @return DiscordLogger|null
     */
    public function getDiscordLogger(): ?DiscordLogger{
        return $this->discordLogger;
    }
}
