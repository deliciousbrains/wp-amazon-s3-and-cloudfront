<?php
namespace DeliciousBrains\WP_Offload_Media\Aws3\Aws\PI;

use DeliciousBrains\WP_Offload_Media\Aws3\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Performance Insights** service.
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDimensionKeys(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDimensionKeysAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getResourceMetrics(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getResourceMetricsAsync(array $args = [])
 */
class PIClient extends AwsClient {}
