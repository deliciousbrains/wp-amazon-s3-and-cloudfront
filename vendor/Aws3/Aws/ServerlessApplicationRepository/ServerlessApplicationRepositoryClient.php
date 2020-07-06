<?php
namespace Aws\ServerlessApplicationRepository;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWSServerlessApplicationRepository** service.
 * @method \Aws\Result createApplication(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createApplicationAsync(array $args = [])
 * @method \Aws\Result createApplicationVersion(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createApplicationVersionAsync(array $args = [])
 * @method \Aws\Result createCloudFormationChangeSet(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createCloudFormationChangeSetAsync(array $args = [])
 * @method \Aws\Result createCloudFormationTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createCloudFormationTemplateAsync(array $args = [])
 * @method \Aws\Result deleteApplication(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteApplicationAsync(array $args = [])
 * @method \Aws\Result getApplication(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getApplicationAsync(array $args = [])
 * @method \Aws\Result getApplicationPolicy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getApplicationPolicyAsync(array $args = [])
 * @method \Aws\Result getCloudFormationTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getCloudFormationTemplateAsync(array $args = [])
 * @method \Aws\Result listApplicationDependencies(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listApplicationDependenciesAsync(array $args = [])
 * @method \Aws\Result listApplicationVersions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listApplicationVersionsAsync(array $args = [])
 * @method \Aws\Result listApplications(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listApplicationsAsync(array $args = [])
 * @method \Aws\Result putApplicationPolicy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putApplicationPolicyAsync(array $args = [])
 * @method \Aws\Result unshareApplication(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise unshareApplicationAsync(array $args = [])
 * @method \Aws\Result updateApplication(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateApplicationAsync(array $args = [])
 */
class ServerlessApplicationRepositoryClient extends AwsClient {}
