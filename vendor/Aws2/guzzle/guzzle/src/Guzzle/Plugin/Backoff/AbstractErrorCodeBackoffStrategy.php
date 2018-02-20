<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff;

/**
 * Strategy used to retry when certain error codes are encountered
 */
abstract class AbstractErrorCodeBackoffStrategy extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\AbstractBackoffStrategy
{
    /** @var array Default cURL errors to retry */
    protected static $defaultErrorCodes = array();
    /** @var array Error codes that can be retried */
    protected $errorCodes;
    /**
     * @param array                    $codes Array of codes that should be retried
     * @param BackoffStrategyInterface $next  The optional next strategy
     */
    public function __construct(array $codes = null, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\BackoffStrategyInterface $next = null)
    {
        $this->errorCodes = array_fill_keys($codes ?: static::$defaultErrorCodes, 1);
        $this->next = $next;
    }
    /**
     * Get the default failure codes to retry
     *
     * @return array
     */
    public static function getDefaultFailureCodes()
    {
        return static::$defaultErrorCodes;
    }
    public function makesDecision()
    {
        return true;
    }
}
