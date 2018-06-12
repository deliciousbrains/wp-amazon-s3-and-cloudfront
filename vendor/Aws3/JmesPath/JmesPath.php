<?php

namespace DeliciousBrains\WP_Offload_S3\Aws3\JmesPath;

/**
 * Returns data from the input array that matches a JMESPath expression.
 *
 * @param string $expression Expression to search.
 * @param mixed $data Data to search.
 *
 * @return mixed|null
 */
if (!function_exists(__NAMESPACE__ . '\\search')) {
    function search($expression, $data)
    {
        return \DeliciousBrains\WP_Offload_S3\Aws3\JmesPath\Env::search($expression, $data);
    }
}
