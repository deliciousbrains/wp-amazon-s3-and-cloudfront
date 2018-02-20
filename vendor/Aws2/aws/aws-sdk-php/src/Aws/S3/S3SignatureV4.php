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

use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\SignatureV4;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Credentials\CredentialsInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\EntityEnclosingRequestInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface;
/**
 * Amazon S3 signature version 4 overrides.
 */
class S3SignatureV4 extends \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Signature\SignatureV4 implements \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\S3SignatureInterface
{
    /**
     * Always add a x-amz-content-sha-256 for data integrity.
     */
    public function signRequest(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request, \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Credentials\CredentialsInterface $credentials)
    {
        if (!$request->hasHeader('x-amz-content-sha256')) {
            $request->setHeader('x-amz-content-sha256', $this->getPayload($request));
        }
        parent::signRequest($request, $credentials);
    }
    /**
     * Override used to allow pre-signed URLs to be created for an
     * in-determinate request payload.
     */
    protected function getPresignedPayload(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request)
    {
        return 'UNSIGNED-PAYLOAD';
    }
    /**
     * Amazon S3 does not double-encode the path component in the canonical req
     */
    protected function createCanonicalizedPath(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Http\Message\RequestInterface $request)
    {
        return '/' . ltrim($request->getPath(), '/');
    }
}
