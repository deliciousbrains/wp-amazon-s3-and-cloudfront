<?php
namespace Aws\Detective;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Detective** service.
 * @method \Aws\Result acceptInvitation(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise acceptInvitationAsync(array $args = [])
 * @method \Aws\Result createGraph(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createGraphAsync(array $args = [])
 * @method \Aws\Result createMembers(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createMembersAsync(array $args = [])
 * @method \Aws\Result deleteGraph(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteGraphAsync(array $args = [])
 * @method \Aws\Result deleteMembers(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteMembersAsync(array $args = [])
 * @method \Aws\Result disassociateMembership(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise disassociateMembershipAsync(array $args = [])
 * @method \Aws\Result getMembers(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getMembersAsync(array $args = [])
 * @method \Aws\Result listGraphs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listGraphsAsync(array $args = [])
 * @method \Aws\Result listInvitations(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listInvitationsAsync(array $args = [])
 * @method \Aws\Result listMembers(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listMembersAsync(array $args = [])
 * @method \Aws\Result rejectInvitation(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise rejectInvitationAsync(array $args = [])
 * @method \Aws\Result startMonitoringMember(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startMonitoringMemberAsync(array $args = [])
 */
class DetectiveClient extends AwsClient {}
