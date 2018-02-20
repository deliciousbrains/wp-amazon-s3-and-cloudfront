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
namespace DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Command;

use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\OperationCommand;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Resource\Model;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Event;
/**
 * Adds functionality to Amazon S3 commands:
 * - Adds the PutObject URL to a response
 * - Allows creating a Pre-signed URL from any command
 */
class S3Command extends \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\OperationCommand
{
    /**
     * Create a pre-signed URL for the operation
     *
     * @param int|string $expires The Unix timestamp to expire at or a string that can be evaluated by strtotime
     *
     * @return string
     */
    public function createPresignedUrl($expires)
    {
        return $this->client->createPresignedUrl($this->prepare(), $expires);
    }
    /**
     * {@inheritdoc}
     */
    protected function process()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();
        // Dispatch an error if a 301 redirect occurred
        if ($response->getStatusCode() == 301) {
            $this->getClient()->getEventDispatcher()->dispatch('request.error', new \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Common\Event(array('request' => $this->getRequest(), 'response' => $response)));
        }
        parent::process();
        // Set the GetObject URL if using the PutObject operation
        if ($this->result instanceof Model && $this->getName() == 'PutObject') {
            $this->result->set('ObjectURL', $request->getUrl());
        }
    }
}
