<?php
namespace DeliciousBrains\WP_Offload_Media\Aws3\Aws\Connect;

use DeliciousBrains\WP_Offload_Media\Aws3\Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon Connect Service** service.
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createUser(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createUserAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteUser(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteUserAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeUser(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeUserAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeUserHierarchyGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeUserHierarchyGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeUserHierarchyStructure(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeUserHierarchyStructureAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getContactAttributes(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getContactAttributesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getCurrentMetricData(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getCurrentMetricDataAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getFederationToken(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getFederationTokenAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getMetricData(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getMetricDataAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listContactFlows(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listContactFlowsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listHoursOfOperations(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listHoursOfOperationsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listPhoneNumbers(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listPhoneNumbersAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listQueues(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listQueuesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listRoutingProfiles(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listRoutingProfilesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listSecurityProfiles(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listSecurityProfilesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listTagsForResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listUserHierarchyGroups(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listUserHierarchyGroupsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listUsers(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listUsersAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result startChatContact(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startChatContactAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result startOutboundVoiceContact(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startOutboundVoiceContactAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result stopContact(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise stopContactAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result tagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result untagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateContactAttributes(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateContactAttributesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateUserHierarchy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateUserHierarchyAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateUserIdentityInfo(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateUserIdentityInfoAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateUserPhoneConfig(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateUserPhoneConfigAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateUserRoutingProfile(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateUserRoutingProfileAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateUserSecurityProfiles(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateUserSecurityProfilesAsync(array $args = [])
 */
class ConnectClient extends AwsClient {}
