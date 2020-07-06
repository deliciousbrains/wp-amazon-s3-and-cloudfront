<?php
namespace Aws\AccessAnalyzer;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Access Analyzer** service.
 * @method \Aws\Result createAnalyzer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createAnalyzerAsync(array $args = [])
 * @method \Aws\Result createArchiveRule(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createArchiveRuleAsync(array $args = [])
 * @method \Aws\Result deleteAnalyzer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteAnalyzerAsync(array $args = [])
 * @method \Aws\Result deleteArchiveRule(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteArchiveRuleAsync(array $args = [])
 * @method \Aws\Result getAnalyzedResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getAnalyzedResourceAsync(array $args = [])
 * @method \Aws\Result getAnalyzer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getAnalyzerAsync(array $args = [])
 * @method \Aws\Result getArchiveRule(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getArchiveRuleAsync(array $args = [])
 * @method \Aws\Result getFinding(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getFindingAsync(array $args = [])
 * @method \Aws\Result listAnalyzedResources(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listAnalyzedResourcesAsync(array $args = [])
 * @method \Aws\Result listAnalyzers(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listAnalyzersAsync(array $args = [])
 * @method \Aws\Result listArchiveRules(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listArchiveRulesAsync(array $args = [])
 * @method \Aws\Result listFindings(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listFindingsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result startResourceScan(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startResourceScanAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateArchiveRule(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateArchiveRuleAsync(array $args = [])
 * @method \Aws\Result updateFindings(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateFindingsAsync(array $args = [])
 */
class AccessAnalyzerClient extends AwsClient {}
