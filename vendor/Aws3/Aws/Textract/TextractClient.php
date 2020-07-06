<?php
namespace Aws\Textract;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Textract** service.
 * @method \Aws\Result analyzeDocument(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise analyzeDocumentAsync(array $args = [])
 * @method \Aws\Result detectDocumentText(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise detectDocumentTextAsync(array $args = [])
 * @method \Aws\Result getDocumentAnalysis(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getDocumentAnalysisAsync(array $args = [])
 * @method \Aws\Result getDocumentTextDetection(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getDocumentTextDetectionAsync(array $args = [])
 * @method \Aws\Result startDocumentAnalysis(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startDocumentAnalysisAsync(array $args = [])
 * @method \Aws\Result startDocumentTextDetection(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startDocumentTextDetectionAsync(array $args = [])
 */
class TextractClient extends AwsClient {}
