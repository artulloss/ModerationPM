<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/3/2019
 * Time: 5:11 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Discord;

use ARTulloss\ModerationPM\Main;
use CortexPE\DiscordWebhookAPI\Embed;
use CortexPE\DiscordWebhookAPI\Message;
use CortexPE\DiscordWebhookAPI\Webhook;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;
use DateTime;
use function str_replace;
use function implode;
use function strtr;


class DiscordLogger
{
    /** @var Main $plugin */
    private $plugin;
    /** @var array $webhookData */
    private $webhookData;
    /**
     * DiscordLogger constructor.
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin) {
        $this->plugin = $plugin;
        $this->webhookData = $this->plugin->getCommandConfig()->getAll()['Discord'];
    }
    /**
     * @param string $player
     * @param string $staff
     * @param int $type
     * @param string $reason
     * @param int $until
     * @param int $color
     * @throws \Exception
     */
    public function logPunish(string $player, string $staff, int $type, string $reason, int $until, int $color): void{
        $data = $this->webhookData;
        $hook = new Webhook($data['Webhook']);
        $message = new Message();
        $embed = new Embed();
        $embed->setColor($color);
        $dateTime = new DateTime();
        $dateTime->setTimestamp($until);
        $length = $dateTime->diff(new DateTime());
        $lengthFormat = $until !== 0 ? $length->format('%Y-%m-%d %H:%i:%s') : 'Forever';
        $dateTimeFormat = $until !== 0 ? $dateTime->format('Y-m-d H:i:s') : 'Forever';
        $embed->setDescription(str_replace(
            ['{player}', '{staff}', '{reason}', '{length}', '{until}'], 
            [$this->getXblLinkMarkdown($player), $this->getXblLinkMarkdown($staff), $reason, $lengthFormat, $dateTimeFormat],
            implode(TextFormat::EOL, $data['Content-Punish'])));
        $embed->setTitle(str_replace('{type}', $this->plugin->getProvider()->typeToString($type), $data['Title']));
        $embed->setFooter($data['Footer'], $data['Image']);
        $embed->setTimestamp(new DateTime());
        $message->addEmbed($embed);
        $hook->send($message);
    }
    /**
     * @param string $title
     * @param array $content
     * @param int $color
     * @param string|null $customHook
     * @throws \Exception
     */
    public function logGeneric(string $title, array $content, int $color, string $customHook = null): void{
        $data = $this->webhookData;
        $hook = new Webhook($customHook ?? $data['Webhook']);
        $message = new Message();
        $embed = new Embed();
        $embed->setColor($color);
        $embed->setTitle($title);
        $embed->setFooter($data['Footer'], $data['Image']);
        $embed->setTimestamp(new DateTime());
        $embed->setDescription(implode(TextFormat::EOL, $content));
        $message->addEmbed($embed);
        $hook->send($message);
    }
    /**
     * @param string $gamerTag
     * @return string
     */
    public function getXblLinkMarkdown(string $gamerTag): string{
        $webGamerTag = strtr($gamerTag, [' ' => '+']);
        return "[$gamerTag](https://account.xbox.com/en-us/profile?gamertag=$webGamerTag)";
    }
}