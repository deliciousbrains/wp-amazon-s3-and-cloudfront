<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log;

use DeliciousBrains\WP_Offload_S3\Aws2\Zend\Log\Logger;
/**
 * Adapts a Zend Framework 2 logger object
 */
class Zf2LogAdapter extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log\AbstractLogAdapter
{
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws2\Zend\Log\Logger $logObject)
    {
        $this->log = $logObject;
    }
    public function log($message, $priority = LOG_INFO, $extras = array())
    {
        $this->log->log($priority, $message, $extras);
    }
}
