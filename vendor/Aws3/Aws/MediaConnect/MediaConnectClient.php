<?php
namespace DeliciousBrains\WP_Offload_Media\Aws3\Aws\MediaConnect;

use DeliciousBrains\WP_Offload_Media\Aws3\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS MediaConnect** service.
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result addFlowOutputs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise addFlowOutputsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result addFlowSources(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise addFlowSourcesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result addFlowVpcInterfaces(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise addFlowVpcInterfacesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createFlow(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createFlowAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteFlow(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteFlowAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeFlow(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeFlowAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result grantFlowEntitlements(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise grantFlowEntitlementsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listEntitlements(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listEntitlementsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listFlows(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listFlowsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listTagsForResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result removeFlowOutput(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise removeFlowOutputAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result removeFlowSource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise removeFlowSourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result removeFlowVpcInterface(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise removeFlowVpcInterfaceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result revokeFlowEntitlement(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise revokeFlowEntitlementAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result startFlow(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startFlowAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result stopFlow(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise stopFlowAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result tagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result untagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateFlow(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateFlowAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateFlowEntitlement(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateFlowEntitlementAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateFlowOutput(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateFlowOutputAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateFlowSource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateFlowSourceAsync(array $args = [])
 */
class MediaConnectClient extends AwsClient {}
