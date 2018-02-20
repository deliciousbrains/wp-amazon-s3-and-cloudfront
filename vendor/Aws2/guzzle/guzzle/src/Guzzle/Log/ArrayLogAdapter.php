<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log;

/**
 * Stores all log messages in an array
 */
class ArrayLogAdapter implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Log\LogAdapterInterface
{
    protected $logs = array();
    public function log($message, $priority = LOG_INFO, $extras = array())
    {
        $this->logs[] = array('message' => $message, 'priority' => $priority, 'extras' => $extras);
    }
    /**
     * Get logged entries
     *
     * @return array
     */
    public function getLogs()
    {
        return $this->logs;
    }
    /**
     * Clears logged entries
     */
    public function clearLogs()
    {
        $this->logs = array();
    }
}
