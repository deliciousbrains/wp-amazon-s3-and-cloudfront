<?php
namespace Aws\LakeFormation;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Lake Formation** service.
 * @method \Aws\Result batchGrantPermissions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise batchGrantPermissionsAsync(array $args = [])
 * @method \Aws\Result batchRevokePermissions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise batchRevokePermissionsAsync(array $args = [])
 * @method \Aws\Result deregisterResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deregisterResourceAsync(array $args = [])
 * @method \Aws\Result describeResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeResourceAsync(array $args = [])
 * @method \Aws\Result getDataLakeSettings(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getDataLakeSettingsAsync(array $args = [])
 * @method \Aws\Result getEffectivePermissionsForPath(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getEffectivePermissionsForPathAsync(array $args = [])
 * @method \Aws\Result grantPermissions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise grantPermissionsAsync(array $args = [])
 * @method \Aws\Result listPermissions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listPermissionsAsync(array $args = [])
 * @method \Aws\Result listResources(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listResourcesAsync(array $args = [])
 * @method \Aws\Result putDataLakeSettings(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putDataLakeSettingsAsync(array $args = [])
 * @method \Aws\Result registerResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise registerResourceAsync(array $args = [])
 * @method \Aws\Result revokePermissions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise revokePermissionsAsync(array $args = [])
 * @method \Aws\Result updateResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateResourceAsync(array $args = [])
 */
class LakeFormationClient extends AwsClient {}
