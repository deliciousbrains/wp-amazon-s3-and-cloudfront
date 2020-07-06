<?php
namespace Aws\GameLift;

use Aws\AwsClient;

/**
 * This client is used to interact with the **Amazon GameLift** service.
 *
 * @method \Aws\Result acceptMatch(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise acceptMatchAsync(array $args = [])
 * @method \Aws\Result claimGameServer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise claimGameServerAsync(array $args = [])
 * @method \Aws\Result createAlias(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createAliasAsync(array $args = [])
 * @method \Aws\Result createBuild(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createBuildAsync(array $args = [])
 * @method \Aws\Result createFleet(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createFleetAsync(array $args = [])
 * @method \Aws\Result createGameServerGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createGameServerGroupAsync(array $args = [])
 * @method \Aws\Result createGameSession(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createGameSessionAsync(array $args = [])
 * @method \Aws\Result createGameSessionQueue(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createGameSessionQueueAsync(array $args = [])
 * @method \Aws\Result createMatchmakingConfiguration(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createMatchmakingConfigurationAsync(array $args = [])
 * @method \Aws\Result createMatchmakingRuleSet(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createMatchmakingRuleSetAsync(array $args = [])
 * @method \Aws\Result createPlayerSession(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createPlayerSessionAsync(array $args = [])
 * @method \Aws\Result createPlayerSessions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createPlayerSessionsAsync(array $args = [])
 * @method \Aws\Result createScript(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createScriptAsync(array $args = [])
 * @method \Aws\Result createVpcPeeringAuthorization(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createVpcPeeringAuthorizationAsync(array $args = [])
 * @method \Aws\Result createVpcPeeringConnection(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createVpcPeeringConnectionAsync(array $args = [])
 * @method \Aws\Result deleteAlias(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteAliasAsync(array $args = [])
 * @method \Aws\Result deleteBuild(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteBuildAsync(array $args = [])
 * @method \Aws\Result deleteFleet(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteFleetAsync(array $args = [])
 * @method \Aws\Result deleteGameServerGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteGameServerGroupAsync(array $args = [])
 * @method \Aws\Result deleteGameSessionQueue(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteGameSessionQueueAsync(array $args = [])
 * @method \Aws\Result deleteMatchmakingConfiguration(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteMatchmakingConfigurationAsync(array $args = [])
 * @method \Aws\Result deleteMatchmakingRuleSet(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteMatchmakingRuleSetAsync(array $args = [])
 * @method \Aws\Result deleteScalingPolicy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteScalingPolicyAsync(array $args = [])
 * @method \Aws\Result deleteScript(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteScriptAsync(array $args = [])
 * @method \Aws\Result deleteVpcPeeringAuthorization(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteVpcPeeringAuthorizationAsync(array $args = [])
 * @method \Aws\Result deleteVpcPeeringConnection(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteVpcPeeringConnectionAsync(array $args = [])
 * @method \Aws\Result deregisterGameServer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deregisterGameServerAsync(array $args = [])
 * @method \Aws\Result describeAlias(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeAliasAsync(array $args = [])
 * @method \Aws\Result describeBuild(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeBuildAsync(array $args = [])
 * @method \Aws\Result describeEC2InstanceLimits(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeEC2InstanceLimitsAsync(array $args = [])
 * @method \Aws\Result describeFleetAttributes(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeFleetAttributesAsync(array $args = [])
 * @method \Aws\Result describeFleetCapacity(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeFleetCapacityAsync(array $args = [])
 * @method \Aws\Result describeFleetEvents(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeFleetEventsAsync(array $args = [])
 * @method \Aws\Result describeFleetPortSettings(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeFleetPortSettingsAsync(array $args = [])
 * @method \Aws\Result describeFleetUtilization(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeFleetUtilizationAsync(array $args = [])
 * @method \Aws\Result describeGameServer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeGameServerAsync(array $args = [])
 * @method \Aws\Result describeGameServerGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeGameServerGroupAsync(array $args = [])
 * @method \Aws\Result describeGameSessionDetails(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeGameSessionDetailsAsync(array $args = [])
 * @method \Aws\Result describeGameSessionPlacement(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeGameSessionPlacementAsync(array $args = [])
 * @method \Aws\Result describeGameSessionQueues(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeGameSessionQueuesAsync(array $args = [])
 * @method \Aws\Result describeGameSessions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeGameSessionsAsync(array $args = [])
 * @method \Aws\Result describeInstances(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeInstancesAsync(array $args = [])
 * @method \Aws\Result describeMatchmaking(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeMatchmakingAsync(array $args = [])
 * @method \Aws\Result describeMatchmakingConfigurations(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeMatchmakingConfigurationsAsync(array $args = [])
 * @method \Aws\Result describeMatchmakingRuleSets(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeMatchmakingRuleSetsAsync(array $args = [])
 * @method \Aws\Result describePlayerSessions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describePlayerSessionsAsync(array $args = [])
 * @method \Aws\Result describeRuntimeConfiguration(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeRuntimeConfigurationAsync(array $args = [])
 * @method \Aws\Result describeScalingPolicies(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeScalingPoliciesAsync(array $args = [])
 * @method \Aws\Result describeScript(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeScriptAsync(array $args = [])
 * @method \Aws\Result describeVpcPeeringAuthorizations(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeVpcPeeringAuthorizationsAsync(array $args = [])
 * @method \Aws\Result describeVpcPeeringConnections(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeVpcPeeringConnectionsAsync(array $args = [])
 * @method \Aws\Result getGameSessionLogUrl(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getGameSessionLogUrlAsync(array $args = [])
 * @method \Aws\Result getInstanceAccess(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getInstanceAccessAsync(array $args = [])
 * @method \Aws\Result listAliases(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listAliasesAsync(array $args = [])
 * @method \Aws\Result listBuilds(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listBuildsAsync(array $args = [])
 * @method \Aws\Result listFleets(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listFleetsAsync(array $args = [])
 * @method \Aws\Result listGameServerGroups(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listGameServerGroupsAsync(array $args = [])
 * @method \Aws\Result listGameServers(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listGameServersAsync(array $args = [])
 * @method \Aws\Result listScripts(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listScriptsAsync(array $args = [])
 * @method \Aws\Result listTagsForResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \Aws\Result putScalingPolicy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putScalingPolicyAsync(array $args = [])
 * @method \Aws\Result registerGameServer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise registerGameServerAsync(array $args = [])
 * @method \Aws\Result requestUploadCredentials(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise requestUploadCredentialsAsync(array $args = [])
 * @method \Aws\Result resolveAlias(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise resolveAliasAsync(array $args = [])
 * @method \Aws\Result resumeGameServerGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise resumeGameServerGroupAsync(array $args = [])
 * @method \Aws\Result searchGameSessions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise searchGameSessionsAsync(array $args = [])
 * @method \Aws\Result startFleetActions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startFleetActionsAsync(array $args = [])
 * @method \Aws\Result startGameSessionPlacement(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startGameSessionPlacementAsync(array $args = [])
 * @method \Aws\Result startMatchBackfill(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startMatchBackfillAsync(array $args = [])
 * @method \Aws\Result startMatchmaking(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startMatchmakingAsync(array $args = [])
 * @method \Aws\Result stopFleetActions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise stopFleetActionsAsync(array $args = [])
 * @method \Aws\Result stopGameSessionPlacement(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise stopGameSessionPlacementAsync(array $args = [])
 * @method \Aws\Result stopMatchmaking(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise stopMatchmakingAsync(array $args = [])
 * @method \Aws\Result suspendGameServerGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise suspendGameServerGroupAsync(array $args = [])
 * @method \Aws\Result tagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \Aws\Result untagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \Aws\Result updateAlias(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateAliasAsync(array $args = [])
 * @method \Aws\Result updateBuild(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateBuildAsync(array $args = [])
 * @method \Aws\Result updateFleetAttributes(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateFleetAttributesAsync(array $args = [])
 * @method \Aws\Result updateFleetCapacity(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateFleetCapacityAsync(array $args = [])
 * @method \Aws\Result updateFleetPortSettings(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateFleetPortSettingsAsync(array $args = [])
 * @method \Aws\Result updateGameServer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateGameServerAsync(array $args = [])
 * @method \Aws\Result updateGameServerGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateGameServerGroupAsync(array $args = [])
 * @method \Aws\Result updateGameSession(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateGameSessionAsync(array $args = [])
 * @method \Aws\Result updateGameSessionQueue(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateGameSessionQueueAsync(array $args = [])
 * @method \Aws\Result updateMatchmakingConfiguration(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateMatchmakingConfigurationAsync(array $args = [])
 * @method \Aws\Result updateRuntimeConfiguration(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateRuntimeConfigurationAsync(array $args = [])
 * @method \Aws\Result updateScript(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateScriptAsync(array $args = [])
 * @method \Aws\Result validateMatchmakingRuleSet(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise validateMatchmakingRuleSetAsync(array $args = [])
 */
class GameLiftClient extends AwsClient {}
