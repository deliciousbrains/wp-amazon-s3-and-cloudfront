<?php
namespace Aws\CodePipeline;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CodePipeline** service.
 *
 * @method \Aws\Result acknowledgeJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise acknowledgeJobAsync(array $args = [])
 * @method \Aws\Result acknowledgeThirdPartyJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise acknowledgeThirdPartyJobAsync(array $args = [])
 * @method \Aws\Result createCustomActionType(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createCustomActionTypeAsync(array $args = [])
 * @method \Aws\Result createPipeline(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createPipelineAsync(array $args = [])
 * @method \Aws\Result deleteCustomActionType(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteCustomActionTypeAsync(array $args = [])
 * @method \Aws\Result deletePipeline(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deletePipelineAsync(array $args = [])
 * @method \Aws\Result deleteWebhook(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteWebhookAsync(array $args = [])
 * @method \Aws\Result deregisterWebhookWithThirdParty(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deregisterWebhookWithThirdPartyAsync(array $args = [])
 * @method \Aws\Result disableStageTransition(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise disableStageTransitionAsync(array $args = [])
 * @method \Aws\Result enableStageTransition(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise enableStageTransitionAsync(array $args = [])
 * @method \Aws\Result getJobDetails(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getJobDetailsAsync(array $args = [])
 * @method \Aws\Result getPipeline(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getPipelineAsync(array $args = [])
 * @method \Aws\Result getPipelineExecution(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getPipelineExecutionAsync(array $args = [])
 * @method \Aws\Result getPipelineState(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getPipelineStateAsync(array $args = [])
 * @method \Aws\Result getThirdPartyJobDetails(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getThirdPartyJobDetailsAsync(array $args = [])
 * @method \Aws\Result listActionExecutions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listActionExecutionsAsync(array $args = [])
 * @method \Aws\Result listActionTypes(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listActionTypesAsync(array $args = [])
 * @method \Aws\Result listPipelineExecutions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listPipelineExecutionsAsync(array $args = [])
 * @method \Aws\Result listPipelines(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listPipelinesAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result listWebhooks(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listWebhooksAsync(array $args = [])
 * @method \Aws\Result pollForJobs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise pollForJobsAsync(array $args = [])
 * @method \Aws\Result pollForThirdPartyJobs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise pollForThirdPartyJobsAsync(array $args = [])
 * @method \Aws\Result putActionRevision(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putActionRevisionAsync(array $args = [])
 * @method \Aws\Result putApprovalResult(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putApprovalResultAsync(array $args = [])
 * @method \Aws\Result putJobFailureResult(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putJobFailureResultAsync(array $args = [])
 * @method \Aws\Result putJobSuccessResult(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putJobSuccessResultAsync(array $args = [])
 * @method \Aws\Result putThirdPartyJobFailureResult(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putThirdPartyJobFailureResultAsync(array $args = [])
 * @method \Aws\Result putThirdPartyJobSuccessResult(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putThirdPartyJobSuccessResultAsync(array $args = [])
 * @method \Aws\Result putWebhook(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putWebhookAsync(array $args = [])
 * @method \Aws\Result registerWebhookWithThirdParty(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise registerWebhookWithThirdPartyAsync(array $args = [])
 * @method \Aws\Result retryStageExecution(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise retryStageExecutionAsync(array $args = [])
 * @method \Aws\Result startPipelineExecution(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startPipelineExecutionAsync(array $args = [])
 * @method \Aws\Result stopPipelineExecution(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise stopPipelineExecutionAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updatePipeline(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updatePipelineAsync(array $args = [])
 */
class CodePipelineClient extends AwsClient {}