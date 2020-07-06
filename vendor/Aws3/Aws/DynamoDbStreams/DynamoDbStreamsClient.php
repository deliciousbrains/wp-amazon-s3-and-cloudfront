<?php
namespace DeliciousBrains\WP_Offload_Media\Aws3\Aws\DynamoDbStreams;

use DeliciousBrains\WP_Offload_Media\Aws3\Aws\AwsClient;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\DynamoDb\DynamoDbClient;

/**
 * This client is used to interact with the **Amazon DynamoDb Streams** service.
 *
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeStream(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeStreamAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getRecords(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getRecordsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getShardIterator(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getShardIteratorAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listStreams(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listStreamsAsync(array $args = [])
 */
class DynamoDbStreamsClient extends AwsClient
{
    public static function getArguments()
    {
        $args = parent::getArguments();
        $args['retries']['default'] = 11;
        $args['retries']['fn'] = [DynamoDbClient::class, '_applyRetryConfig'];

        return $args;
    }
}