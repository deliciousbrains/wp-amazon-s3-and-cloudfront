<?php
namespace Aws\Translate;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Translate** service.
 * @method \Aws\Result deleteTerminology(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteTerminologyAsync(array $args = [])
 * @method \Aws\Result describeTextTranslationJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeTextTranslationJobAsync(array $args = [])
 * @method \Aws\Result getTerminology(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getTerminologyAsync(array $args = [])
 * @method \Aws\Result importTerminology(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise importTerminologyAsync(array $args = [])
 * @method \Aws\Result listTerminologies(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listTerminologiesAsync(array $args = [])
 * @method \Aws\Result listTextTranslationJobs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listTextTranslationJobsAsync(array $args = [])
 * @method \Aws\Result startTextTranslationJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startTextTranslationJobAsync(array $args = [])
 * @method \Aws\Result stopTextTranslationJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise stopTextTranslationJobAsync(array $args = [])
 * @method \Aws\Result translateText(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise translateTextAsync(array $args = [])
 */
class TranslateClient extends AwsClient {}
