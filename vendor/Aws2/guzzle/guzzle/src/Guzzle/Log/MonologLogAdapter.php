<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log;

use DeliciousBrains\WP_Offload_S3\Aws2\Monolog\Logger;
/**
 * @deprecated
 * @codeCoverageIgnore
 */
class MonologLogAdapter extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log\AbstractLogAdapter
{
    /**
     * syslog to Monolog mappings
     */
    private static $mapping = array(LOG_DEBUG => \DeliciousBrains\WP_Offload_S3\Aws2\Monolog\Logger::DEBUG, LOG_INFO => \DeliciousBrains\WP_Offload_S3\Aws2\Monolog\Logger::INFO, LOG_WARNING => \DeliciousBrains\WP_Offload_S3\Aws2\Monolog\Logger::WARNING, LOG_ERR => \DeliciousBrains\WP_Offload_S3\Aws2\Monolog\Logger::ERROR, LOG_CRIT => \DeliciousBrains\WP_Offload_S3\Aws2\Monolog\Logger::CRITICAL, LOG_ALERT => \DeliciousBrains\WP_Offload_S3\Aws2\Monolog\Logger::ALERT);
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws2\Monolog\Logger $logObject)
    {
        $this->log = $logObject;
    }
    public function log($message, $priority = LOG_INFO, $extras = array())
    {
        $this->log->addRecord(self::$mapping[$priority], $message, $extras);
    }
}
