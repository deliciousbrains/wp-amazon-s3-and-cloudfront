<?php

/**
 * Copyright 2010-2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 * http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception\HttpException;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\EntityEnclosingRequestInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\BackoffStrategyInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\AbstractBackoffStrategy;
/**
 * Custom S3 exponential backoff checking use to retry 400 responses containing the following reason phrase:
 * "Your socket connection to the server was not read from or written to within the timeout period.".
 * This error has been reported as intermittent/random, and in most cases, seems to occur during the middle of a
 * transfer. This plugin will attempt to retry these failed requests, and if using a local file, will clear the
 * stat cache of the file and set a new content-length header on the upload.
 */
class SocketTimeoutChecker extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\AbstractBackoffStrategy
{
    const ERR = 'Your socket connection to the server was not read from or written to within the timeout period';
    /**
     * {@inheridoc}
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Plugin\Backoff\BackoffStrategyInterface $next = null)
    {
        if ($next) {
            $this->setNext($next);
        }
    }
    /**
     * {@inheridoc}
     */
    public function makesDecision()
    {
        return true;
    }
    /**
     * {@inheritdoc}
     */
    protected function getDelay($retries, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\Response $response = null, \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Exception\HttpException $e = null)
    {
        if ($response && $response->getStatusCode() == 400 && strpos($response->getBody(), self::ERR)) {
            return true;
        }
    }
}
