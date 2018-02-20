<?php

namespace DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Cache;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception\BadResponseException;
/**
 * Default revalidation strategy
 */
class DefaultRevalidation implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Cache\RevalidationInterface
{
    /** @var CacheStorageInterface Cache object storing cache data */
    protected $storage;
    /** @var CanCacheStrategyInterface */
    protected $canCache;
    /**
     * @param CacheStorageInterface     $cache    Cache storage
     * @param CanCacheStrategyInterface $canCache Determines if a message can be cached
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Cache\CacheStorageInterface $cache, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Cache\CanCacheStrategyInterface $canCache = null)
    {
        $this->storage = $cache;
        $this->canCache = $canCache ?: new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Cache\DefaultCanCacheStrategy();
    }
    public function revalidate(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response)
    {
        try {
            $revalidate = $this->createRevalidationRequest($request, $response);
            $validateResponse = $revalidate->send();
            if ($validateResponse->getStatusCode() == 200) {
                return $this->handle200Response($request, $validateResponse);
            } elseif ($validateResponse->getStatusCode() == 304) {
                return $this->handle304Response($request, $validateResponse, $response);
            }
        } catch (BadResponseException $e) {
            $this->handleBadResponse($e);
        }
        // Other exceptions encountered in the revalidation request are ignored
        // in hopes that sending a request to the origin server will fix it
        return false;
    }
    public function shouldRevalidate(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response)
    {
        if ($request->getMethod() != \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface::GET) {
            return false;
        }
        $reqCache = $request->getHeader('Cache-Control');
        $resCache = $response->getHeader('Cache-Control');
        $revalidate = $request->getHeader('Pragma') == 'no-cache' || $reqCache && ($reqCache->hasDirective('no-cache') || $reqCache->hasDirective('must-revalidate')) || $resCache && ($resCache->hasDirective('no-cache') || $resCache->hasDirective('must-revalidate'));
        // Use the strong ETag validator if available and the response contains no Cache-Control directive
        if (!$revalidate && !$resCache && $response->hasHeader('ETag')) {
            $revalidate = true;
        }
        return $revalidate;
    }
    /**
     * Handles a bad response when attempting to revalidate
     *
     * @param BadResponseException $e Exception encountered
     *
     * @throws BadResponseException
     */
    protected function handleBadResponse(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception\BadResponseException $e)
    {
        // 404 errors mean the resource no longer exists, so remove from
        // cache, and prevent an additional request by throwing the exception
        if ($e->getResponse()->getStatusCode() == 404) {
            $this->storage->delete($e->getRequest());
            throw $e;
        }
    }
    /**
     * Creates a request to use for revalidation
     *
     * @param RequestInterface $request  Request
     * @param Response         $response Response to revalidate
     *
     * @return RequestInterface returns a revalidation request
     */
    protected function createRevalidationRequest(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response)
    {
        $revalidate = clone $request;
        $revalidate->removeHeader('Pragma')->removeHeader('Cache-Control');
        if ($response->getLastModified()) {
            $revalidate->setHeader('If-Modified-Since', $response->getLastModified());
        }
        if ($response->getEtag()) {
            $revalidate->setHeader('If-None-Match', $response->getEtag());
        }
        // Remove any cache plugins that might be on the request to prevent infinite recursive revalidations
        $dispatcher = $revalidate->getEventDispatcher();
        foreach ($dispatcher->getListeners() as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                if (is_array($listener) && $listener[0] instanceof CachePlugin) {
                    $dispatcher->removeListener($eventName, $listener);
                }
            }
        }
        return $revalidate;
    }
    /**
     * Handles a 200 response response from revalidating. The server does not support validation, so use this response.
     *
     * @param RequestInterface $request          Request that was sent
     * @param Response         $validateResponse Response received
     *
     * @return bool Returns true if valid, false if invalid
     */
    protected function handle200Response(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $validateResponse)
    {
        $request->setResponse($validateResponse);
        if ($this->canCache->canCacheResponse($validateResponse)) {
            $this->storage->cache($request, $validateResponse);
        }
        return false;
    }
    /**
     * Handle a 304 response and ensure that it is still valid
     *
     * @param RequestInterface $request          Request that was sent
     * @param Response         $validateResponse Response received
     * @param Response         $response         Original cached response
     *
     * @return bool Returns true if valid, false if invalid
     */
    protected function handle304Response(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $validateResponse, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response)
    {
        static $replaceHeaders = array('Date', 'Expires', 'Cache-Control', 'ETag', 'Last-Modified');
        // Make sure that this response has the same ETag
        if ($validateResponse->getEtag() != $response->getEtag()) {
            return false;
        }
        // Replace cached headers with any of these headers from the
        // origin server that might be more up to date
        $modified = false;
        foreach ($replaceHeaders as $name) {
            if ($validateResponse->hasHeader($name)) {
                $modified = true;
                $response->setHeader($name, $validateResponse->getHeader($name));
            }
        }
        // Store the updated response in cache
        if ($modified && $this->canCache->canCacheResponse($response)) {
            $this->storage->cache($request, $response);
        }
        return true;
    }
}
