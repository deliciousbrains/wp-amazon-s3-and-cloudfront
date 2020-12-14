<?php

namespace DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Promise;

final class Is
{
    /**
     * Returns true if a promise is pending.
     *
     * @return bool
     */
    public static function pending(\DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Promise\PromiseInterface::PENDING;
    }
    /**
     * Returns true if a promise is fulfilled or rejected.
     *
     * @return bool
     */
    public static function settled(\DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() !== \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Promise\PromiseInterface::PENDING;
    }
    /**
     * Returns true if a promise is fulfilled.
     *
     * @return bool
     */
    public static function fulfilled(\DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Promise\PromiseInterface::FULFILLED;
    }
    /**
     * Returns true if a promise is rejected.
     *
     * @return bool
     */
    public static function rejected(\DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Promise\PromiseInterface::REJECTED;
    }
}
