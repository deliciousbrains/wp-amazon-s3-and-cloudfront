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
namespace DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Model;

use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Client\AwsClientInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\OverflowException;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\UaString as Ua;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Exception\InvalidArgumentException;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Exception\DeleteMultipleObjectsException;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Batch\BatchTransferInterface;
use DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface;
/**
 * Transfer logic for deleting multiple objects from an Amazon S3 bucket in a
 * single request
 */
class DeleteObjectsTransfer implements \DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Batch\BatchTransferInterface
{
    /**
     * @var AwsClientInterface The Amazon S3 client for doing transfers
     */
    protected $client;
    /**
     * @var string Bucket from which to delete the objects
     */
    protected $bucket;
    /**
     * @var string MFA token to apply to the request
     */
    protected $mfa;
    /**
     * Constructs a transfer using the injected client
     *
     * @param AwsClientInterface $client Client used to transfer the requests
     * @param string             $bucket Name of the bucket that stores the objects
     * @param string             $mfa    MFA token used when contacting the Amazon S3 API
     */
    public function __construct(\DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Client\AwsClientInterface $client, $bucket, $mfa = null)
    {
        $this->client = $client;
        $this->bucket = $bucket;
        $this->mfa = $mfa;
    }
    /**
     * Set a new MFA token value
     *
     * @param string $token MFA token
     *
     * @return $this
     */
    public function setMfa($token)
    {
        $this->mfa = $token;
        return $this;
    }
    /**
     * {@inheritdoc}
     * @throws OverflowException        if a batch has more than 1000 items
     * @throws InvalidArgumentException when an invalid batch item is encountered
     */
    public function transfer(array $batch)
    {
        if (empty($batch)) {
            return;
        }
        if (count($batch) > 1000) {
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Exception\OverflowException('Batches should be divided into chunks of no larger than 1000 keys');
        }
        $del = array();
        $command = $this->client->getCommand('DeleteObjects', array('Bucket' => $this->bucket, \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\UaString::OPTION => \DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Enum\UaString::BATCH));
        if ($this->mfa) {
            $command->getRequestHeaders()->set('x-amz-mfa', $this->mfa);
        }
        foreach ($batch as $object) {
            // Ensure that the batch item is valid
            if (!is_array($object) || !isset($object['Key'])) {
                throw new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Exception\InvalidArgumentException('Invalid batch item encountered: ' . var_export($batch, true));
            }
            $del[] = array('Key' => $object['Key'], 'VersionId' => isset($object['VersionId']) ? $object['VersionId'] : null);
        }
        $command['Objects'] = $del;
        $command->execute();
        $this->processResponse($command);
    }
    /**
     * Process the response of the DeleteMultipleObjects request
     *
     * @paramCommandInterface $command Command executed
     */
    protected function processResponse(\DeliciousBrains\WP_Offload_S3\Aws2\Guzzle\Service\Command\CommandInterface $command)
    {
        $result = $command->getResult();
        // Ensure that the objects were deleted successfully
        if (!empty($result['Errors'])) {
            $errors = $result['Errors'];
            throw new \DeliciousBrains\WP_Offload_S3\Aws2\Aws\S3\Exception\DeleteMultipleObjectsException($errors);
        }
    }
}
