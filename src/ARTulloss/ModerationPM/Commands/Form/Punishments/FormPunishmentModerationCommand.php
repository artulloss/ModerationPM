<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 5/29/2019
 * Time: 3:01 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Form\Punishments;

use ARTulloss\ModerationPM\Commands\Arguments\DateTimeArgument;
use ARTulloss\ModerationPM\Commands\Arguments\ForeverArgument;
use ARTulloss\ModerationPM\Commands\Arguments\MessageArgument;
use ARTulloss\ModerationPM\Commands\Form\FormModerationCommand;
use ARTulloss\ModerationPM\Commands\Form\Punishments\Traits\LengthTrait;
use ARTulloss\ModerationPM\Commands\Form\Punishments\Traits\ReasonsTrait;
use ARTulloss\ModerationPM\Database\Container\PlayerData;
use ARTulloss\ModerationPM\Database\Container\Punishment;
use ARTulloss\ModerationPM\Main;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Dropdown;
use dktapps\pmforms\element\StepSlider;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use Exception;
use DateTime;
use function str_replace;
use function strtolower;
use function strtr;

abstract class FormPunishmentModerationCommand extends FormModerationCommand implements PunishmentCommand{
    /** @var string[] $lengths */
    protected $lengths;
    /** @var string[] $reasons */
    protected $reasons;

    use LengthTrait, ReasonsTrait;

    protected function prepare(): void{
        parent::prepare();
        $this->registerArgument(1, new DateTimeArgument('length', true));
        $this->registerArgument(1, new ForeverArgument('length', true));
        $this->registerArgument(2, new MessageArgument('reason', true));
    }

    /**
     * FormPunishmentModerationCommandOnline constructor.
     * @param Main $main
     * @param string $name
     * @param string $description
     * @param string[] $lengths
     * @param string[] $reasons
     * @param array $aliases
     */
    public function __construct(Main $main, string $name, string $description, array $lengths, array $reasons, array $aliases = []) {
        parent::__construct($main, $name, $description, $aliases);
        $this->setPermission(Main::PERMISSION_PREFIX . $this->provider->typeToString(static::TYPE, false));
        $this->setLengths($lengths);
        $this->setReasons($reasons);
    }
    /**
     * @param Player $sender
     * @param PlayerData $data
     * @param array $args
     */
    public function runAsPlayer(Player $sender, PlayerData $data, array $args): void{
        $form = new CustomForm(strtr(static::TITLE, ['{player}' => $data->getName()]), [
            new StepSlider('length', 'Length', $this->lengths, 0),
            new Dropdown('reason', 'Reason', $this->reasons)
        ], function (Player $sender, CustomFormResponse $response) use ($data): void{
            $response = $response->getAll();
            $this->callback($sender, $data, $this->lengths[$response['length']], $this->reasons[$response['reason']]); // Forward this so the callback can be overwritten
        });
        $sender->sendForm($form);
    }
    /**
     * @param CommandSender $sender
     * @param PlayerData $data
     * @param array $args
     * @throws Exception
     */
    public function runAsConsole(CommandSender $sender, PlayerData $data, array $args): void{
        if(!($args['length'] === '' || $args['reason'] === ''))
            $this->callback($sender, $data, $args['length'], $args['reason']);
        else
            $this->sendError(self::ERR_INSUFFICIENT_ARGUMENTS);
    }
    /**
     * @param CommandSender $sender
     * @param PlayerData $data
     * @param string $until
     * @param string $reason
     * @throws Exception
     */
    public function callback(CommandSender $sender, PlayerData $data, string $until, string $reason): void{
        if(strtolower($until) === 'forever') {
            $until = Punishment::FOREVER;
        } else
            $until = (new DateTime("now + $until"))->getTimestamp();
        $this->provider->asyncPunishPlayer($data->getName(), static::TYPE, $sender->getName(), $reason, $until);
        $player = $sender->getServer()->getPlayerExact($data->getName());
        if($player !== null)
            $this->onlinePunish($player, $this->plugin->resolvePunishmentMessage(static::TYPE, $reason, $until));
        $sender->sendMessage(str_replace('{player}', $data->getName(), static::MESSAGE_SUCCESS));
        $logger = $this->plugin->getDiscordLogger();
        if($logger !== null)
            $logger->logPunish($data->getName(), $sender->getName(), static::TYPE, $reason, $until, static::COLOR);
    }
    /**
     * @param Player $player
     * @param string $message
     */
    abstract public function onlinePunish(Player $player, string $message): void;
}