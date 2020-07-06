<?php
namespace Aws\IoTEvents;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS IoT Events** service.
 * @method \Aws\Result createDetectorModel(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createDetectorModelAsync(array $args = [])
 * @method \Aws\Result createInput(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createInputAsync(array $args = [])
 * @method \Aws\Result deleteDetectorModel(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteDetectorModelAsync(array $args = [])
 * @method \Aws\Result deleteInput(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteInputAsync(array $args = [])
 * @method \Aws\Result describeDetectorModel(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDetectorModelAsync(array $args = [])
 * @method \Aws\Result describeInput(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeInputAsync(array $args = [])
 * @method \Aws\Result describeLoggingOptions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeLoggingOptionsAsync(array $args = [])
 * @method \Aws\Result listDetectorModelVersions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listDetectorModelVersionsAsync(array $args = [])
 * @method \Aws\Result listDetectorModels(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listDetectorModelsAsync(array $args = [])
 * @method \Aws\Result listInputs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listInputsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result putLoggingOptions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putLoggingOptionsAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateDetectorModel(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateDetectorModelAsync(array $args = [])
 * @method \Aws\Result updateInput(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateInputAsync(array $args = [])
 */
class IoTEventsClient extends AwsClient {}
