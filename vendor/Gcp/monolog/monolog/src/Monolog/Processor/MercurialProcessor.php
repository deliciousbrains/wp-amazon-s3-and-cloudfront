<?php

declare (strict_types=1);
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Processor;

use DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger;
/**
 * Injects Hg branch and Hg revision number in all records
 *
 * @author Jonathan A. Schweder <jonathanschweder@gmail.com>
 */
class MercurialProcessor implements \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Processor\ProcessorInterface
{
    private $level;
    private static $cache;
    /**
     * @param string|int $level The minimum logging level at which this Processor will be triggered
     */
    public function __construct($level = \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::DEBUG)
    {
        $this->level = \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::toMonologLevel($level);
    }
    public function __invoke(array $record) : array
    {
        // return if the level is not high enough
        if ($record['level'] < $this->level) {
            return $record;
        }
        $record['extra']['hg'] = self::getMercurialInfo();
        return $record;
    }
    private static function getMercurialInfo() : array
    {
        if (self::$cache) {
            return self::$cache;
        }
        $result = explode(' ', trim(`hg id -nb`));
        if (count($result) >= 3) {
            return self::$cache = ['branch' => $result[1], 'revision' => $result[2]];
        }
        return self::$cache = [];
    }
}
