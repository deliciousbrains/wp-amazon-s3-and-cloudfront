<?php
namespace Aws\IoTEventsData;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Events Data** service.
 * @method \Aws\Result batchPutMessage(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise batchPutMessageAsync(array $args = [])
 * @method \Aws\Result batchUpdateDetector(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise batchUpdateDetectorAsync(array $args = [])
 * @method \Aws\Result describeDetector(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDetectorAsync(array $args = [])
 * @method \Aws\Result listDetectors(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listDetectorsAsync(array $args = [])
 */
class IoTEventsDataClient extends AwsClient {}
