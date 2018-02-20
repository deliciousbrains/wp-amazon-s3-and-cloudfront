<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\QueryAggregator;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\QueryString;
/**
 * Aggregates nested query string variables using commas
 */
class CommaAggregator implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\QueryAggregator\QueryAggregatorInterface
{
    public function aggregate($key, $value, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\QueryString $query)
    {
        if ($query->isUrlEncoding()) {
            return array($query->encodeValue($key) => implode(',', array_map(array($query, 'encodeValue'), $value)));
        } else {
            return array($key => implode(',', $value));
        }
    }
}
