<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 5/29/2019
 * Time: 3:01 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Commands\Form\Punishments;

use function array_values;
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
use dktapps\pmforms\element\Input;
use dktapps\pmforms\element\StepSlider;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use Exception;
use DateTime;
use pocketmine\utils\TextFormat;
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
     * @param array $dataArray
     * @param array $args
     */
    public function runAsPlayer(Player $sender, array $dataArray, array $args): void{
        /** @var PlayerData $data */
        $data = array_values($dataArray)[0];
        $form = new CustomForm(strtr(static::TITLE, ['{player}' => $data->getName()]), [
            new StepSlider('length', 'Length', $this->lengths, 0),
            new Dropdown('reason', 'Reason', $this->reasons),
            new Input('custom_reason', 'Custom Reason', 'Reason')
        ], function (Player $sender, CustomFormResponse $response) use ($dataArray): void{
            $response = $response->getAll();
            $reason = $response['custom_reason'] === '' ? $this->reasons[$response['reason']] : $response['custom_reason'];
            $this->callback($sender, $dataArray, $this->lengths[$response['length']], $reason); // Forward this so the callback can be overwritten
        });
        $sender->sendForm($form);
    }
    /**
     * @param CommandSender $sender
     * @param array $data
     * @param array $args
     * @throws Exception
     */
    public function runAsConsole(CommandSender $sender, array $data, array $args): void{
        if(!($args['length'] === '' || $args['reason'] === ''))
            $this->callback($sender, $data, $args['length'], $args['reason']);
        else
            $this->sendError(self::ERR_INSUFFICIENT_ARGUMENTS);
    }
    /**
     * @param CommandSender $sender
     * @param array $data
     * @param string $until
     * @param string $reason
     * @throws Exception
     */
    public function callback(CommandSender $sender, $data, string $until, string $reason): void{
        if(strtolower($until) === 'forever') {
            $until = Punishment::FOREVER;
        } else
            $until = (new DateTime("now + $until"))->getTimestamp();
        foreach ($data as $playerData) {
            // Punish each player data matching the players name
            $this->provider->asyncPunishPlayer($playerData->getID(), static::TYPE, $sender->getName(), $reason, $until);
            $player = $sender->getServer()->getPlayerExact($playerData->getName());
        }
        if(isset($player) && $player !== null)
            $this->onlinePunish($player, $this->plugin->resolvePunishmentMessage(static::TYPE, $reason, $until));
        if(isset($playerData)) {
            $sender->getServer()->broadcastMessage(str_replace(['{player}', '{staff}'], [$playerData->getName(), $sender->getName()], static::MESSAGE_BROADCAST));
            $logger = $this->plugin->getDiscordLogger();
            if ($logger !== null)
                $logger->logPunish($playerData->getName(), $sender->getName(), static::TYPE, $reason, $until, static::COLOR);
        }
    }
    /**
     * @param Player $player
     * @param string $message
     */
    abstract public function onlinePunish(Player $player, string $message): void;
}