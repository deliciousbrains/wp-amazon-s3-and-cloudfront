<?php

namespace DeliciousBrains\WP_Offload_Media\Gcp\Google\Auth\HttpHandler;

use DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\ClientInterface;
use DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\RequestInterface;
use DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\ResponseInterface;
class Guzzle6HttpHandler
{
    /**
     * @var ClientInterface
     */
    private $client;
    /**
     * @param ClientInterface $client
     */
    public function __construct(\DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\ClientInterface $client)
    {
        $this->client = $client;
    }
    /**
     * Accepts a PSR-7 request and an array of options and returns a PSR-7 response.
     *
     * @param RequestInterface $request
     * @param array $options
     *
     * @return ResponseInterface
     */
    public function __invoke(\DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\RequestInterface $request, array $options = [])
    {
        return $this->client->send($request, $options);
    }
    /**
     * Accepts a PSR-7 request and an array of options and returns a PromiseInterface
     *
     * @param RequestInterface $request
     * @param array $options
     *
     * @return \GuzzleHttp\Promise\Promise
     */
    public function async(\DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\RequestInterface $request, array $options = [])
    {
        return $this->client->sendAsync($request, $options);
    }
}
