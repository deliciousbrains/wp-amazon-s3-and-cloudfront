<?php
namespace Aws\Batch;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Batch** service.
 * @method \Aws\Result cancelJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise cancelJobAsync(array $args = [])
 * @method \Aws\Result createComputeEnvironment(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createComputeEnvironmentAsync(array $args = [])
 * @method \Aws\Result createJobQueue(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createJobQueueAsync(array $args = [])
 * @method \Aws\Result deleteComputeEnvironment(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteComputeEnvironmentAsync(array $args = [])
 * @method \Aws\Result deleteJobQueue(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteJobQueueAsync(array $args = [])
 * @method \Aws\Result deregisterJobDefinition(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deregisterJobDefinitionAsync(array $args = [])
 * @method \Aws\Result describeComputeEnvironments(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeComputeEnvironmentsAsync(array $args = [])
 * @method \Aws\Result describeJobDefinitions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeJobDefinitionsAsync(array $args = [])
 * @method \Aws\Result describeJobQueues(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeJobQueuesAsync(array $args = [])
 * @method \Aws\Result describeJobs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeJobsAsync(array $args = [])
 * @method \Aws\Result listJobs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listJobsAsync(array $args = [])
 * @method \Aws\Result registerJobDefinition(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise registerJobDefinitionAsync(array $args = [])
 * @method \Aws\Result submitJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise submitJobAsync(array $args = [])
 * @method \Aws\Result terminateJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise terminateJobAsync(array $args = [])
 * @method \Aws\Result updateComputeEnvironment(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateComputeEnvironmentAsync(array $args = [])
 * @method \Aws\Result updateJobQueue(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateJobQueueAsync(array $args = [])
 */
class BatchClient extends AwsClient {}
