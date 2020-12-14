<?php

namespace DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Handler;

use DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\RequestInterface;
interface CurlFactoryInterface
{
    /**
     * Creates a cURL handle resource.
     *
     * @param RequestInterface $request Request
     * @param array            $options Transfer options
     *
     * @throws \RuntimeException when an option cannot be applied
     */
    public function create(\DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\RequestInterface $request, array $options) : EasyHandle;
    /**
     * Release an easy handle, allowing it to be reused or closed.
     *
     * This function must call unset on the easy handle's "handle" property.
     */
    public function release(\DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Handler\EasyHandle $easy) : void;
}
