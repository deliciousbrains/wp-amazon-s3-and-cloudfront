<?php
namespace Aws\Outposts;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Outposts** service.
 * @method \Aws\Result createOutpost(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createOutpostAsync(array $args = [])
 * @method \Aws\Result deleteOutpost(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteOutpostAsync(array $args = [])
 * @method \Aws\Result deleteSite(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteSiteAsync(array $args = [])
 * @method \Aws\Result getOutpost(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getOutpostAsync(array $args = [])
 * @method \Aws\Result getOutpostInstanceTypes(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getOutpostInstanceTypesAsync(array $args = [])
 * @method \Aws\Result listOutposts(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listOutpostsAsync(array $args = [])
 * @method \Aws\Result listSites(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listSitesAsync(array $args = [])
 */
class OutpostsClient extends AwsClient {}
