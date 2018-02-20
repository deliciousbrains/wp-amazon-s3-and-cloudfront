<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common;

/**
 * An object that can be represented as an array
 */
interface ToArrayInterface
{
    /**
     * Get the array representation of an object
     *
     * @return array
     */
    public function toArray();
}
