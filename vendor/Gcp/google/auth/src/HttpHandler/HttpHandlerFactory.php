<?php

/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace DeliciousBrains\WP_Offload_Media\Gcp\Google\Auth\HttpHandler;

use DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Client;
use DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\ClientInterface;
class HttpHandlerFactory
{
    /**
     * Builds out a default http handler for the installed version of guzzle.
     *
     * @param ClientInterface $client
     *
     * @return Guzzle5HttpHandler|Guzzle6HttpHandler
     *
     * @throws \Exception
     */
    public static function build(\DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\ClientInterface $client = null)
    {
        $version = \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\ClientInterface::VERSION;
        $client = $client ?: new \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Client();
        switch ($version[0]) {
            case '5':
                return new \DeliciousBrains\WP_Offload_Media\Gcp\Google\Auth\HttpHandler\Guzzle5HttpHandler($client);
            case '6':
                return new \DeliciousBrains\WP_Offload_Media\Gcp\Google\Auth\HttpHandler\Guzzle6HttpHandler($client);
            default:
                throw new \Exception('Version not supported');
        }
    }
}
