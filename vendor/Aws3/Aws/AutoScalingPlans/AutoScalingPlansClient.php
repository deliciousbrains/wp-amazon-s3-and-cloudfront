<?php
namespace Aws\AutoScalingPlans;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Auto Scaling Plans** service.
 * @method \Aws\Result createScalingPlan(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createScalingPlanAsync(array $args = [])
 * @method \Aws\Result deleteScalingPlan(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteScalingPlanAsync(array $args = [])
 * @method \Aws\Result describeScalingPlanResources(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeScalingPlanResourcesAsync(array $args = [])
 * @method \Aws\Result describeScalingPlans(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeScalingPlansAsync(array $args = [])
 * @method \Aws\Result getScalingPlanResourceForecastData(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getScalingPlanResourceForecastDataAsync(array $args = [])
 * @method \Aws\Result updateScalingPlan(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateScalingPlanAsync(array $args = [])
 */
class AutoScalingPlansClient extends AwsClient {}
