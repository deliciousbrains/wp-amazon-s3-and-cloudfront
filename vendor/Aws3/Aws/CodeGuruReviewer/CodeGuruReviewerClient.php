<?php
namespace Aws\CodeGuruReviewer;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon CodeGuru Reviewer** service.
 * @method \Aws\Result associateRepository(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise associateRepositoryAsync(array $args = [])
 * @method \Aws\Result describeCodeReview(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeCodeReviewAsync(array $args = [])
 * @method \Aws\Result describeRecommendationFeedback(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeRecommendationFeedbackAsync(array $args = [])
 * @method \Aws\Result describeRepositoryAssociation(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeRepositoryAssociationAsync(array $args = [])
 * @method \Aws\Result disassociateRepository(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise disassociateRepositoryAsync(array $args = [])
 * @method \Aws\Result listCodeReviews(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listCodeReviewsAsync(array $args = [])
 * @method \Aws\Result listRecommendationFeedback(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listRecommendationFeedbackAsync(array $args = [])
 * @method \Aws\Result listRecommendations(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listRecommendationsAsync(array $args = [])
 * @method \Aws\Result listRepositoryAssociations(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listRepositoryAssociationsAsync(array $args = [])
 * @method \Aws\Result putRecommendationFeedback(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putRecommendationFeedbackAsync(array $args = [])
 */
class CodeGuruReviewerClient extends AwsClient {}
