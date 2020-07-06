<?php
namespace DeliciousBrains\WP_Offload_Media\Aws3\Aws\Rds;

use DeliciousBrains\WP_Offload_Media\Aws3\Aws\AwsClient;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\Service;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\DocModel;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\Api\ApiProvider;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\PresignUrlMiddleware;

/**
 * This client is used to interact with the **Amazon Relational Database Service (Amazon RDS)**.
 *
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result addSourceIdentifierToSubscription(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise addSourceIdentifierToSubscriptionAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result addTagsToResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise addTagsToResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result authorizeDBSecurityGroupIngress(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise authorizeDBSecurityGroupIngressAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result copyDBParameterGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise copyDBParameterGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result copyDBSnapshot(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise copyDBSnapshotAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result copyOptionGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise copyOptionGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createDBInstance(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createDBInstanceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createDBInstanceReadReplica(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createDBInstanceReadReplicaAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createDBParameterGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createDBParameterGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createDBSecurityGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createDBSecurityGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createDBSnapshot(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createDBSnapshotAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createDBSubnetGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createDBSubnetGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createEventSubscription(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createEventSubscriptionAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createOptionGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createOptionGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteDBInstance(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteDBInstanceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteDBParameterGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteDBParameterGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteDBSecurityGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteDBSecurityGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteDBSnapshot(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteDBSnapshotAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteDBSubnetGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteDBSubnetGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteEventSubscription(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteEventSubscriptionAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteOptionGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteOptionGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBEngineVersions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBEngineVersionsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBInstances(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBInstancesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBLogFiles(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBLogFilesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBParameterGroups(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBParameterGroupsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBParameters(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBParametersAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBSecurityGroups(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBSecurityGroupsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBSnapshots(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBSnapshotsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBSubnetGroups(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBSubnetGroupsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeEngineDefaultParameters(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeEngineDefaultParametersAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeEventCategories(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeEventCategoriesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeEventSubscriptions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeEventSubscriptionsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeEvents(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeEventsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeOptionGroupOptions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeOptionGroupOptionsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeOptionGroups(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeOptionGroupsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeOrderableDBInstanceOptions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeOrderableDBInstanceOptionsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeReservedDBInstances(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeReservedDBInstancesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeReservedDBInstancesOfferings(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeReservedDBInstancesOfferingsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result downloadDBLogFilePortion(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise downloadDBLogFilePortionAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listTagsForResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listTagsForResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyDBInstance(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyDBInstanceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyDBParameterGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyDBParameterGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyDBSubnetGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyDBSubnetGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyEventSubscription(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyEventSubscriptionAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyOptionGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyOptionGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result promoteReadReplica(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise promoteReadReplicaAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result purchaseReservedDBInstancesOffering(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise purchaseReservedDBInstancesOfferingAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result rebootDBInstance(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise rebootDBInstanceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result removeSourceIdentifierFromSubscription(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise removeSourceIdentifierFromSubscriptionAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result removeTagsFromResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise removeTagsFromResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result resetDBParameterGroup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise resetDBParameterGroupAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result restoreDBInstanceFromDBSnapshot(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise restoreDBInstanceFromDBSnapshotAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result restoreDBInstanceToPointInTime(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise restoreDBInstanceToPointInTimeAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result revokeDBSecurityGroupIngress(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise revokeDBSecurityGroupIngressAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result addRoleToDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise addRoleToDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result addRoleToDBInstance(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise addRoleToDBInstanceAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result applyPendingMaintenanceAction(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise applyPendingMaintenanceActionAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result backtrackDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise backtrackDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result cancelExportTask(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise cancelExportTaskAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result copyDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise copyDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result copyDBClusterSnapshot(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise copyDBClusterSnapshotAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createCustomAvailabilityZone(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createCustomAvailabilityZoneAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createDBClusterEndpoint(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createDBClusterEndpointAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createDBClusterSnapshot(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createDBClusterSnapshotAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createDBProxy(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createDBProxyAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createGlobalCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createGlobalClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteCustomAvailabilityZone(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteCustomAvailabilityZoneAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteDBClusterEndpoint(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteDBClusterEndpointAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteDBClusterSnapshot(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteDBClusterSnapshotAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteDBInstanceAutomatedBackup(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteDBInstanceAutomatedBackupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteDBProxy(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteDBProxyAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteGlobalCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteGlobalClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteInstallationMedia(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteInstallationMediaAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deregisterDBProxyTargets(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deregisterDBProxyTargetsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeAccountAttributes(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeAccountAttributesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeCertificates(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeCertificatesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeCustomAvailabilityZones(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeCustomAvailabilityZonesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBClusterBacktracks(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBClusterBacktracksAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBClusterEndpoints(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBClusterEndpointsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBClusterParameterGroups(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBClusterParameterGroupsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBClusterParameters(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBClusterParametersAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBClusterSnapshotAttributes(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBClusterSnapshotAttributesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBClusterSnapshots(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBClusterSnapshotsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBClusters(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBClustersAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBInstanceAutomatedBackups(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBInstanceAutomatedBackupsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBProxies(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBProxiesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBProxyTargetGroups(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBProxyTargetGroupsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBProxyTargets(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBProxyTargetsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeDBSnapshotAttributes(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDBSnapshotAttributesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeEngineDefaultClusterParameters(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeEngineDefaultClusterParametersAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeExportTasks(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeExportTasksAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeGlobalClusters(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeGlobalClustersAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeInstallationMedia(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeInstallationMediaAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describePendingMaintenanceActions(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describePendingMaintenanceActionsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeSourceRegions(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeSourceRegionsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeValidDBInstanceModifications(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeValidDBInstanceModificationsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result failoverDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise failoverDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result importInstallationMedia(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise importInstallationMediaAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyCertificates(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyCertificatesAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyCurrentDBClusterCapacity(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyCurrentDBClusterCapacityAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyDBClusterEndpoint(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyDBClusterEndpointAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyDBClusterSnapshotAttribute(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyDBClusterSnapshotAttributeAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyDBProxy(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyDBProxyAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyDBProxyTargetGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyDBProxyTargetGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyDBSnapshot(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyDBSnapshotAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyDBSnapshotAttribute(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyDBSnapshotAttributeAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result modifyGlobalCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise modifyGlobalClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result promoteReadReplicaDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise promoteReadReplicaDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result registerDBProxyTargets(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise registerDBProxyTargetsAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result removeFromGlobalCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise removeFromGlobalClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result removeRoleFromDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise removeRoleFromDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result removeRoleFromDBInstance(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise removeRoleFromDBInstanceAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result resetDBClusterParameterGroup(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise resetDBClusterParameterGroupAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result restoreDBClusterFromS3(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise restoreDBClusterFromS3Async(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result restoreDBClusterFromSnapshot(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise restoreDBClusterFromSnapshotAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result restoreDBClusterToPointInTime(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise restoreDBClusterToPointInTimeAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result restoreDBInstanceFromS3(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise restoreDBInstanceFromS3Async(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result startActivityStream(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startActivityStreamAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result startDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result startDBInstance(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startDBInstanceAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result startExportTask(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startExportTaskAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result stopActivityStream(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise stopActivityStreamAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result stopDBCluster(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise stopDBClusterAsync(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result stopDBInstance(array $args = []) (supported in versions 2014-10-31)
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise stopDBInstanceAsync(array $args = []) (supported in versions 2014-10-31)
 */
class RdsClient extends AwsClient
{
    public function __construct(array $args)
    {
        $args['with_resolved'] = function (array $args) {
            $this->getHandlerList()->appendInit(
                PresignUrlMiddleware::wrap(
                    $this,
                    $args['endpoint_provider'],
                    [
                        'operations' => [
                            'CopyDBSnapshot',
                            'CreateDBInstanceReadReplica',
                            'CopyDBClusterSnapshot',
                            'CreateDBCluster'
                        ],
                        'service' => 'rds',
                        'presign_param' => 'PreSignedUrl',
                        'require_different_region' => true,
                    ]
                ),
                'rds.presigner'
            );
        };

        parent::__construct($args);
    }

    /**
     * @internal
     * @codeCoverageIgnore
     */
    public static function applyDocFilters(array $api, array $docs)
    {
        // Add the SourceRegion parameter
        $docs['shapes']['SourceRegion']['base'] = 'A required parameter that indicates '
            . 'the region that the DB snapshot will be copied from.';
        $api['shapes']['SourceRegion'] = ['type' => 'string'];
        $api['shapes']['CopyDBSnapshotMessage']['members']['SourceRegion'] = ['shape' => 'SourceRegion'];
        $api['shapes']['CreateDBInstanceReadReplicaMessage']['members']['SourceRegion'] = ['shape' => 'SourceRegion'];

        // Add the DestinationRegion parameter
        $docs['shapes']['DestinationRegion']['base']
            = '<div class="alert alert-info">The SDK will populate this '
            . 'parameter on your behalf using the configured region value of '
            . 'the client.</div>';
        $api['shapes']['DestinationRegion'] = ['type' => 'string'];
        $api['shapes']['CopyDBSnapshotMessage']['members']['DestinationRegion'] = ['shape' => 'DestinationRegion'];
        $api['shapes']['CreateDBInstanceReadReplicaMessage']['members']['DestinationRegion'] = ['shape' => 'DestinationRegion'];

        // Several parameters in presign APIs are optional.
        $docs['shapes']['String']['refs']['CopyDBSnapshotMessage$PreSignedUrl']
            = '<div class="alert alert-info">The SDK will compute this value '
            . 'for you on your behalf.</div>';
        $docs['shapes']['String']['refs']['CopyDBSnapshotMessage$DestinationRegion']
            = '<div class="alert alert-info">The SDK will populate this '
            . 'parameter on your behalf using the configured region value of '
            . 'the client.</div>';

        // Several parameters in presign APIs are optional.
        $docs['shapes']['String']['refs']['CreateDBInstanceReadReplicaMessage$PreSignedUrl']
            = '<div class="alert alert-info">The SDK will compute this value '
            . 'for you on your behalf.</div>';
        $docs['shapes']['String']['refs']['CreateDBInstanceReadReplicaMessage$DestinationRegion']
            = '<div class="alert alert-info">The SDK will populate this '
            . 'parameter on your behalf using the configured region value of '
            . 'the client.</div>';

        if ($api['metadata']['apiVersion'] != '2014-09-01') {
            $api['shapes']['CopyDBClusterSnapshotMessage']['members']['SourceRegion'] = ['shape' => 'SourceRegion'];
            $api['shapes']['CreateDBClusterMessage']['members']['SourceRegion'] = ['shape' => 'SourceRegion'];

            $api['shapes']['CopyDBClusterSnapshotMessage']['members']['DestinationRegion'] = ['shape' => 'DestinationRegion'];
            $api['shapes']['CreateDBClusterMessage']['members']['DestinationRegion'] = ['shape' => 'DestinationRegion'];

            // Several parameters in presign APIs are optional.
            $docs['shapes']['String']['refs']['CopyDBClusterSnapshotMessage$PreSignedUrl']
                = '<div class="alert alert-info">The SDK will compute this value '
                . 'for you on your behalf.</div>';
            $docs['shapes']['String']['refs']['CopyDBClusterSnapshotMessage$DestinationRegion']
                = '<div class="alert alert-info">The SDK will populate this '
                . 'parameter on your behalf using the configured region value of '
                . 'the client.</div>';

            // Several parameters in presign APIs are optional.
            $docs['shapes']['String']['refs']['CreateDBClusterMessage$PreSignedUrl']
                = '<div class="alert alert-info">The SDK will compute this value '
                . 'for you on your behalf.</div>';
            $docs['shapes']['String']['refs']['CreateDBClusterMessage$DestinationRegion']
                = '<div class="alert alert-info">The SDK will populate this '
                . 'parameter on your behalf using the configured region value of '
                . 'the client.</div>';
        }

        return [
            new Service($api, ApiProvider::defaultProvider()),
            new DocModel($docs)
        ];
    }
}
