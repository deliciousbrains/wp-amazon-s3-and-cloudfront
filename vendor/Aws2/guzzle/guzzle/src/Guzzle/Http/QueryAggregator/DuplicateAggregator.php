<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\QueryAggregator;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\QueryString;
/**
 * Does not aggregate nested query string values and allows duplicates in the resulting array
 *
 * Example: http://test.com?q=1&q=2
 */
class DuplicateAggregator implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\QueryAggregator\QueryAggregatorInterface
{
    public function aggregate($key, $value, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\QueryString $query)
    {
        if ($query->isUrlEncoding()) {
            return array($query->encodeValue($key) => array_map(array($query, 'encodeValue'), $value));
        } else {
            return array($key => $value);
        }
    }
}
