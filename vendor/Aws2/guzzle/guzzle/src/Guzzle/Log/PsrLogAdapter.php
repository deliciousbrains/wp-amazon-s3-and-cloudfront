<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log;

use DeliciousBrains\WP_Offload_S3\Aws2\Psr\Log\LogLevel;
use DeliciousBrains\WP_Offload_S3\Aws2\Psr\Log\LoggerInterface;
/**
 * PSR-3 log adapter
 *
 * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 */
class PsrLogAdapter extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log\AbstractLogAdapter
{
    /**
     * syslog to PSR-3 mappings
     */
    private static $mapping = array(LOG_DEBUG => \DeliciousBrains\WP_Offload_S3\Aws2\Psr\Log\LogLevel::DEBUG, LOG_INFO => \DeliciousBrains\WP_Offload_S3\Aws2\Psr\Log\LogLevel::INFO, LOG_WARNING => \DeliciousBrains\WP_Offload_S3\Aws2\Psr\Log\LogLevel::WARNING, LOG_ERR => \DeliciousBrains\WP_Offload_S3\Aws2\Psr\Log\LogLevel::ERROR, LOG_CRIT => \DeliciousBrains\WP_Offload_S3\Aws2\Psr\Log\LogLevel::CRITICAL, LOG_ALERT => \DeliciousBrains\WP_Offload_S3\Aws2\Psr\Log\LogLevel::ALERT);
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws2\Psr\Log\LoggerInterface $logObject)
    {
        $this->log = $logObject;
    }
    public function log($message, $priority = LOG_INFO, $extras = array())
    {
        $this->log->log(self::$mapping[$priority], $message, $extras);
    }
}
