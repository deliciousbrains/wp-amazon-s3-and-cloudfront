<?php
namespace DeliciousBrains\WP_Offload_Media\Aws3\Aws\Backup;

use DeliciousBrains\WP_Offload_Media\Aws3\Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Backup** service.
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createBackupPlan(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createBackupPlanAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createBackupSelection(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createBackupSelectionAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result createBackupVault(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createBackupVaultAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteBackupPlan(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteBackupPlanAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteBackupSelection(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteBackupSelectionAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteBackupVault(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteBackupVaultAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteBackupVaultAccessPolicy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteBackupVaultAccessPolicyAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteBackupVaultNotifications(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteBackupVaultNotificationsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result deleteRecoveryPoint(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteRecoveryPointAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeBackupJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeBackupJobAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeBackupVault(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeBackupVaultAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeCopyJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeCopyJobAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeProtectedResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeProtectedResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeRecoveryPoint(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeRecoveryPointAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeRegionSettings(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeRegionSettingsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result describeRestoreJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeRestoreJobAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result exportBackupPlanTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise exportBackupPlanTemplateAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getBackupPlan(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getBackupPlanAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getBackupPlanFromJSON(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getBackupPlanFromJSONAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getBackupPlanFromTemplate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getBackupPlanFromTemplateAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getBackupSelection(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getBackupSelectionAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getBackupVaultAccessPolicy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getBackupVaultAccessPolicyAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getBackupVaultNotifications(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getBackupVaultNotificationsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getRecoveryPointRestoreMetadata(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getRecoveryPointRestoreMetadataAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getSupportedResourceTypes(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getSupportedResourceTypesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listBackupJobs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listBackupJobsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listBackupPlanTemplates(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listBackupPlanTemplatesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listBackupPlanVersions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listBackupPlanVersionsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listBackupPlans(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listBackupPlansAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listBackupSelections(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listBackupSelectionsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listBackupVaults(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listBackupVaultsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listCopyJobs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listCopyJobsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listProtectedResources(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listProtectedResourcesAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listRecoveryPointsByBackupVault(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listRecoveryPointsByBackupVaultAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listRecoveryPointsByResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listRecoveryPointsByResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listRestoreJobs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listRestoreJobsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result listTags(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listTagsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result putBackupVaultAccessPolicy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putBackupVaultAccessPolicyAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result putBackupVaultNotifications(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putBackupVaultNotificationsAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result startBackupJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startBackupJobAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result startCopyJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startCopyJobAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result startRestoreJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise startRestoreJobAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result stopBackupJob(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise stopBackupJobAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result tagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise tagResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result untagResource(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise untagResourceAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateBackupPlan(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateBackupPlanAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateRecoveryPointLifecycle(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateRecoveryPointLifecycleAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result updateRegionSettings(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateRegionSettingsAsync(array $args = [])
 */
class BackupClient extends AwsClient {}
