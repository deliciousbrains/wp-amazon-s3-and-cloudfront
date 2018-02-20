<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Header;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Header;
/**
 * Default header factory implementation
 */
class HeaderFactory implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Header\HeaderFactoryInterface
{
    /** @var array */
    protected $mapping = array('cache-control' => 'DeliciousBrains\\WP_Offload_S3\\Aws2\\Guzzle\\Http\\Message\\Header\\CacheControl', 'link' => 'DeliciousBrains\\WP_Offload_S3\\Aws2\\Guzzle\\Http\\Message\\Header\\Link');
    public function createHeader($header, $value = null)
    {
        $lowercase = strtolower($header);
        return isset($this->mapping[$lowercase]) ? new $this->mapping[$lowercase]($header, $value) : new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Header($header, $value);
    }
}
