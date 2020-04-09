<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/5/2019
 * Time: 12:29 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Form\PunishmentsList;

use function array_unique;
use ARTulloss\ModerationPM\Commands\CommandConstants;
use ARTulloss\ModerationPM\Commands\ModerationCommand;
use ARTulloss\ModerationPM\Database\Container\Punishment;
use ARTulloss\ModerationPM\Main;
use function count;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use function strtr;
use function implode;

class ListPunishmentsCommand extends ModerationCommand implements CommandConstants{
    /** @var int $type */
    private $type;
    /* Max amount of buttons on the form before switching to text */
    public const MAX_FORM = 100;
    /**
     * ListPunishmentsCommand constructor.
     * @param Main $main
     * @param int $type
     * @param string $name
     * @param string $description
     * @param array $aliases
     */
    public function __construct(Main $main, int $type, string $name, string $description = "", array $aliases = []) {
        parent::__construct($main, $name, $description, $aliases);
        $this->setPermission(Main::PERMISSION_PREFIX . 'list');
        $this->type = $type;
    }

    protected function prepare(): void{}

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void{
        if($sender instanceof Player) {
            if($this->testPermission($sender)) {
                $commandConfig = $this->plugin->getCommandConfig();
                $this->asyncGetPunishments(function (?array $punishments) use ($sender, $commandConfig): void{
                    if($punishments !== null) {
                        $punishmentNames = [];
                        /** @var Punishment $punishment */
                        foreach ($punishments as $punishment) {
                            $punishmentNames[] = $punishment->getPlayerName();
                        }
                        if(count(array_unique($punishmentNames)) > self::MAX_FORM) {
                            $this->runAsConsole($sender);
                            return;
                        }
                        $format = $commandConfig->getNested('List.Format');
                        $punishmentsAlreadyListed = [];
                        /** @var Punishment $punishment */
                        foreach ($punishments as $punishment) {
                            $name = $punishment->getPlayerName();
                            if(!isset($punishmentsAlreadyListed[$name])) {
                                $menuText = $this->replaceStrings(implode(TextFormat::EOL, $format), $this->provider->typeToString($this->type), $name, $punishment->getStaffName(), $punishment->getReason());
                                $entries[] = new MenuOption($menuText);
                                $punishmentsAlreadyListed[$name] = true;
                            }
                        }
                        if($entries ?? null !== null) {
                            $name = $this->provider->typeToString($this->type);
                            $sender->sendForm(new MenuForm($name . 'list', $name, $entries, function (Player $player, int $selectedOption) use ($punishments, $commandConfig): void{
                                /** @var Punishment $punishment */
                                $punishment = $punishments[$selectedOption];
                                $type = $this->provider->resolveType($this->type, 'ban', 'ban-ip', 'mute', 'freeze');
                                $player->getServer()->dispatchCommand($player, $this->replaceStrings(
                                    $commandConfig->getNested('List.Command'), $type, $punishment->getPlayerName(), $punishment->getStaffName(), $punishment->getReason())
                                );
                            }));
                        }
                    } else
                        $sender->sendMessage(TextFormat::RED . 'No one is ' . $this->provider->resolveType($this->type, 'banned', 'IP banned', 'muted', 'frozen'));
                });
            }
        } else
            $this->runAsConsole($sender);
    }
    public function runAsConsole(CommandSender $sender): void{
        $this->asyncGetPunishments(function (?array $punishments) use ($sender): void{
            if($punishments !== null) {
                $punishmentsNames = [];
                /** @var Punishment $punishment */
                foreach ($punishments as $punishment) {
                    $punishmentsNames[] = $punishment->getPlayerName();
                }
                $punishmentString = implode(', ', array_unique($punishmentsNames));
                $sender->sendMessage($punishmentString);
            } else
                $sender->sendMessage(TextFormat::RED . 'No one is ' . $this->provider->resolveType($this->type, 'banned', 'IP banned', 'muted', 'frozen'));
        });
    }
    /**
     * @param string $string
     * @param string $type
     * @param string $player
     * @param string $staff
     * @param string $reason
     * @return string
     */
    private function replaceStrings(string $string, string $type, string $player, string $staff, string $reason): string{
        return strtr($string, [
            '{type}' => $type,
            '{player}' => $player,
            '{staff}' => $staff,
            '{reason}' => $reason
        ]);
    }
    /**
     * @param callable $callback
     */
    public function asyncGetPunishments(callable $callback): void{
        Utils::validateCallableSignature(function (?array $punishments): void{}, $callback);
        $this->provider->asyncGetPunishments($this->type, function (array $result) use ($callback): void{
            foreach ($result as $punishmentValue) {
                /** @var Punishment $punishment */
                $punishment = Punishment::fromDatabaseQuery($punishmentValue, Punishment::NO_KEY, $this->type);
                if($punishment !== null)
                    $punishments[] = $punishment;
            }
            $callback($punishments ?? null);
        });
    }
}