<?php
namespace Aws\RAM;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Resource Access Manager** service.
 * @method \Aws\Result acceptResourceShareInvitation(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise acceptResourceShareInvitationAsync(array $args = [])
 * @method \Aws\Result associateResourceShare(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise associateResourceShareAsync(array $args = [])
 * @method \Aws\Result associateResourceSharePermission(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise associateResourceSharePermissionAsync(array $args = [])
 * @method \Aws\Result createResourceShare(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createResourceShareAsync(array $args = [])
 * @method \Aws\Result deleteResourceShare(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteResourceShareAsync(array $args = [])
 * @method \Aws\Result disassociateResourceShare(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise disassociateResourceShareAsync(array $args = [])
 * @method \Aws\Result disassociateResourceSharePermission(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise disassociateResourceSharePermissionAsync(array $args = [])
 * @method \Aws\Result enableSharingWithAwsOrganization(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise enableSharingWithAwsOrganizationAsync(array $args = [])
 * @method \Aws\Result getPermission(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getPermissionAsync(array $args = [])
 * @method \Aws\Result getResourcePolicies(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getResourcePoliciesAsync(array $args = [])
 * @method \Aws\Result getResourceShareAssociations(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getResourceShareAssociationsAsync(array $args = [])
 * @method \Aws\Result getResourceShareInvitations(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getResourceShareInvitationsAsync(array $args = [])
 * @method \Aws\Result getResourceShares(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getResourceSharesAsync(array $args = [])
 * @method \Aws\Result listPendingInvitationResources(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listPendingInvitationResourcesAsync(array $args = [])
 * @method \Aws\Result listPermissions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listPermissionsAsync(array $args = [])
 * @method \Aws\Result listPrincipals(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listPrincipalsAsync(array $args = [])
 * @method \Aws\Result listResourceSharePermissions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listResourceSharePermissionsAsync(array $args = [])
 * @method \Aws\Result listResourceTypes(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listResourceTypesAsync(array $args = [])
 * @method \Aws\Result listResources(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listResourcesAsync(array $args = [])
 * @method \Aws\Result promoteResourceShareCreatedFromPolicy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise promoteResourceShareCreatedFromPolicyAsync(array $args = [])
 * @method \Aws\Result rejectResourceShareInvitation(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise rejectResourceShareInvitationAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateResourceShare(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateResourceShareAsync(array $args = [])
 */
class RAMClient extends AwsClient {}
