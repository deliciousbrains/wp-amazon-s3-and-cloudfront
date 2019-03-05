<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Handler;

use DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Formatter\NormalizerFormatter;
use DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger;
/**
 * Handler sending logs to Zend Monitor
 *
 * @author  Christian Bergau <cbergau86@gmail.com>
 */
class ZendMonitorHandler extends \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Handler\AbstractProcessingHandler
{
    /**
     * Monolog level / ZendMonitor Custom Event priority map
     *
     * @var array
     */
    protected $levelMap = array(\DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::DEBUG => 1, \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::INFO => 2, \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::NOTICE => 3, \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::WARNING => 4, \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::ERROR => 5, \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::CRITICAL => 6, \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::ALERT => 7, \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::EMERGENCY => 0);
    /**
     * Construct
     *
     * @param  int                       $level
     * @param  bool                      $bubble
     * @throws MissingExtensionException
     */
    public function __construct($level = \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Logger::DEBUG, $bubble = true)
    {
        if (!function_exists('zend_monitor_custom_event')) {
            throw new \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Handler\MissingExtensionException('You must have Zend Server installed in order to use this handler');
        }
        parent::__construct($level, $bubble);
    }
    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $this->writeZendMonitorCustomEvent($this->levelMap[$record['level']], $record['message'], $record['formatted']);
    }
    /**
     * Write a record to Zend Monitor
     *
     * @param int    $level
     * @param string $message
     * @param array  $formatted
     */
    protected function writeZendMonitorCustomEvent($level, $message, $formatted)
    {
        zend_monitor_custom_event($level, $message, $formatted);
    }
    /**
     * {@inheritdoc}
     */
    public function getDefaultFormatter()
    {
        return new \DeliciousBrains\WP_Offload_Media\Gcp\Monolog\Formatter\NormalizerFormatter();
    }
    /**
     * Get the level map
     *
     * @return array
     */
    public function getLevelMap()
    {
        return $this->levelMap;
    }
}
