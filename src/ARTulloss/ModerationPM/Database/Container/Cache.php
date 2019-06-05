<?php
/**
 * Created by PhpStorm.
 * User: Adam
 * Date: 6/3/2019
 * Time: 3:47 PM
 */
declare(strict_types=1);

namespace ARTulloss\ModerationPM\Database\Container;

use ARTulloss\ModerationPM\Database\Provider;
use ARTulloss\ModerationPM\Utilities\Utilities;
use pocketmine\plugin\Plugin;

/**
 * Class Cache
 * Useful for storing data that we don't want to fetch repeatedly
 * @package ARTulloss\ModerationPM\Database\Cache
 */
class Cache extends BoolContainer{
    /** @var int $type */
    private $type;
    /**
     * Cache constructor.
     * @param Plugin $plugin
     * @param int $type
     */
    public function __construct(Plugin $plugin, int $type) {
        parent::__construct($plugin);
        $this->type = $type;
    }
    public function refresh(): void{
        /** @var Provider $provider */
        $provider = $this->plugin->getProvider();
        $provider->asyncGetPunishments($this->type, function (array $result) use ($provider): void{
            $cache = [];
            foreach ($result as $punishment) {
                $name = $punishment['name'];
                $stillPunished = Utilities::isStillPunished($punishment['until']);
                if($stillPunished) {
                    $cache[$name] = true;
                } else {
                    $provider->asyncRemovePunishment($name, $this->type, function (int $rows) use ($provider, $name): void{
                        if($rows !== 0) {
                            $expiredMsg = $provider->typeToString($this->type, false) . ' expired!';
                            $this->plugin->getLogger()->info("$name's " . $expiredMsg);
                        }
                    });
                }
            }
            $this->cache = $cache;
            $this->plugin->getLogger()->info('Refreshed cache for ' . $provider->typeToString($this->type) . 's.');
        });
    }
}