<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Version;
/**
 * Adapts a Zend Framework 1 logger object
 * @deprecated
 * @codeCoverageIgnore
 */
class Zf1LogAdapter extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log\AbstractLogAdapter
{
    public function __construct(\Zend_Log $logObject)
    {
        $this->log = $logObject;
        \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Version::warn(__CLASS__ . ' is deprecated');
    }
    public function log($message, $priority = LOG_INFO, $extras = array())
    {
        $this->log->log($message, $priority, $extras);
    }
}
