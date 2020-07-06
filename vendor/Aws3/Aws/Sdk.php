<?php
namespace DeliciousBrains\WP_Offload_Media\Aws3\Aws;

/**
 * Builds AWS clients based on configuration settings.
 *
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ACMPCA\ACMPCAClient createACMPCA(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionACMPCA(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\AccessAnalyzer\AccessAnalyzerClient createAccessAnalyzer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionAccessAnalyzer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Acm\AcmClient createAcm(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionAcm(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\AlexaForBusiness\AlexaForBusinessClient createAlexaForBusiness(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionAlexaForBusiness(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Amplify\AmplifyClient createAmplify(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionAmplify(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ApiGateway\ApiGatewayClient createApiGateway(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionApiGateway(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ApiGatewayManagementApi\ApiGatewayManagementApiClient createApiGatewayManagementApi(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionApiGatewayManagementApi(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ApiGatewayV2\ApiGatewayV2Client createApiGatewayV2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionApiGatewayV2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\AppConfig\AppConfigClient createAppConfig(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionAppConfig(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\AppMesh\AppMeshClient createAppMesh(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionAppMesh(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\AppSync\AppSyncClient createAppSync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionAppSync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ApplicationAutoScaling\ApplicationAutoScalingClient createApplicationAutoScaling(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionApplicationAutoScaling(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ApplicationDiscoveryService\ApplicationDiscoveryServiceClient createApplicationDiscoveryService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionApplicationDiscoveryService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ApplicationInsights\ApplicationInsightsClient createApplicationInsights(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionApplicationInsights(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Appstream\AppstreamClient createAppstream(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionAppstream(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Athena\AthenaClient createAthena(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionAthena(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\AugmentedAIRuntime\AugmentedAIRuntimeClient createAugmentedAIRuntime(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionAugmentedAIRuntime(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\AutoScaling\AutoScalingClient createAutoScaling(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionAutoScaling(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\AutoScalingPlans\AutoScalingPlansClient createAutoScalingPlans(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionAutoScalingPlans(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Backup\BackupClient createBackup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionBackup(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Batch\BatchClient createBatch(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionBatch(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Budgets\BudgetsClient createBudgets(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionBudgets(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Chime\ChimeClient createChime(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionChime(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Cloud9\Cloud9Client createCloud9(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCloud9(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CloudDirectory\CloudDirectoryClient createCloudDirectory(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCloudDirectory(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CloudFormation\CloudFormationClient createCloudFormation(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCloudFormation(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CloudFront\CloudFrontClient createCloudFront(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCloudFront(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CloudHSMV2\CloudHSMV2Client createCloudHSMV2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCloudHSMV2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CloudHsm\CloudHsmClient createCloudHsm(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCloudHsm(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CloudSearch\CloudSearchClient createCloudSearch(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCloudSearch(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CloudSearchDomain\CloudSearchDomainClient createCloudSearchDomain(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCloudSearchDomain(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CloudTrail\CloudTrailClient createCloudTrail(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCloudTrail(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CloudWatch\CloudWatchClient createCloudWatch(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCloudWatch(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CloudWatchEvents\CloudWatchEventsClient createCloudWatchEvents(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCloudWatchEvents(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CloudWatchLogs\CloudWatchLogsClient createCloudWatchLogs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCloudWatchLogs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CodeArtifact\CodeArtifactClient createCodeArtifact(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCodeArtifact(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CodeBuild\CodeBuildClient createCodeBuild(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCodeBuild(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CodeCommit\CodeCommitClient createCodeCommit(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCodeCommit(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CodeDeploy\CodeDeployClient createCodeDeploy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCodeDeploy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CodeGuruProfiler\CodeGuruProfilerClient createCodeGuruProfiler(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCodeGuruProfiler(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CodeGuruReviewer\CodeGuruReviewerClient createCodeGuruReviewer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCodeGuruReviewer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CodePipeline\CodePipelineClient createCodePipeline(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCodePipeline(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CodeStar\CodeStarClient createCodeStar(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCodeStar(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CodeStarNotifications\CodeStarNotificationsClient createCodeStarNotifications(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCodeStarNotifications(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CodeStarconnections\CodeStarconnectionsClient createCodeStarconnections(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCodeStarconnections(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CognitoIdentity\CognitoIdentityClient createCognitoIdentity(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCognitoIdentity(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CognitoIdentityProvider\CognitoIdentityProviderClient createCognitoIdentityProvider(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCognitoIdentityProvider(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CognitoSync\CognitoSyncClient createCognitoSync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCognitoSync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Comprehend\ComprehendClient createComprehend(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionComprehend(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ComprehendMedical\ComprehendMedicalClient createComprehendMedical(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionComprehendMedical(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ComputeOptimizer\ComputeOptimizerClient createComputeOptimizer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionComputeOptimizer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ConfigService\ConfigServiceClient createConfigService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionConfigService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Connect\ConnectClient createConnect(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionConnect(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ConnectParticipant\ConnectParticipantClient createConnectParticipant(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionConnectParticipant(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CostExplorer\CostExplorerClient createCostExplorer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCostExplorer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\CostandUsageReportService\CostandUsageReportServiceClient createCostandUsageReportService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionCostandUsageReportService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\DAX\DAXClient createDAX(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionDAX(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\DLM\DLMClient createDLM(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionDLM(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\DataExchange\DataExchangeClient createDataExchange(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionDataExchange(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\DataPipeline\DataPipelineClient createDataPipeline(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionDataPipeline(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\DataSync\DataSyncClient createDataSync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionDataSync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\DatabaseMigrationService\DatabaseMigrationServiceClient createDatabaseMigrationService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionDatabaseMigrationService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Detective\DetectiveClient createDetective(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionDetective(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\DeviceFarm\DeviceFarmClient createDeviceFarm(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionDeviceFarm(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\DirectConnect\DirectConnectClient createDirectConnect(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionDirectConnect(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\DirectoryService\DirectoryServiceClient createDirectoryService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionDirectoryService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\DocDB\DocDBClient createDocDB(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionDocDB(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\DynamoDb\DynamoDbClient createDynamoDb(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionDynamoDb(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\DynamoDbStreams\DynamoDbStreamsClient createDynamoDbStreams(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionDynamoDbStreams(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\EBS\EBSClient createEBS(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionEBS(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\EC2InstanceConnect\EC2InstanceConnectClient createEC2InstanceConnect(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionEC2InstanceConnect(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\EKS\EKSClient createEKS(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionEKS(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Ec2\Ec2Client createEc2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionEc2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Ecr\EcrClient createEcr(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionEcr(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Ecs\EcsClient createEcs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionEcs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Efs\EfsClient createEfs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionEfs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ElastiCache\ElastiCacheClient createElastiCache(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionElastiCache(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ElasticBeanstalk\ElasticBeanstalkClient createElasticBeanstalk(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionElasticBeanstalk(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ElasticInference\ElasticInferenceClient createElasticInference(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionElasticInference(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ElasticLoadBalancing\ElasticLoadBalancingClient createElasticLoadBalancing(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionElasticLoadBalancing(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ElasticLoadBalancingV2\ElasticLoadBalancingV2Client createElasticLoadBalancingV2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionElasticLoadBalancingV2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ElasticTranscoder\ElasticTranscoderClient createElasticTranscoder(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionElasticTranscoder(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ElasticsearchService\ElasticsearchServiceClient createElasticsearchService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionElasticsearchService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Emr\EmrClient createEmr(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionEmr(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\EventBridge\EventBridgeClient createEventBridge(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionEventBridge(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\FMS\FMSClient createFMS(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionFMS(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\FSx\FSxClient createFSx(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionFSx(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Firehose\FirehoseClient createFirehose(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionFirehose(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ForecastQueryService\ForecastQueryServiceClient createForecastQueryService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionForecastQueryService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ForecastService\ForecastServiceClient createForecastService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionForecastService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\FraudDetector\FraudDetectorClient createFraudDetector(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionFraudDetector(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\GameLift\GameLiftClient createGameLift(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionGameLift(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Glacier\GlacierClient createGlacier(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionGlacier(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\GlobalAccelerator\GlobalAcceleratorClient createGlobalAccelerator(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionGlobalAccelerator(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Glue\GlueClient createGlue(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionGlue(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Greengrass\GreengrassClient createGreengrass(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionGreengrass(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\GroundStation\GroundStationClient createGroundStation(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionGroundStation(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\GuardDuty\GuardDutyClient createGuardDuty(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionGuardDuty(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Health\HealthClient createHealth(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionHealth(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Honeycode\HoneycodeClient createHoneycode(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionHoneycode(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Iam\IamClient createIam(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionIam(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ImportExport\ImportExportClient createImportExport(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionImportExport(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Inspector\InspectorClient createInspector(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionInspector(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\IoT1ClickDevicesService\IoT1ClickDevicesServiceClient createIoT1ClickDevicesService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionIoT1ClickDevicesService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\IoT1ClickProjects\IoT1ClickProjectsClient createIoT1ClickProjects(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionIoT1ClickProjects(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\IoTAnalytics\IoTAnalyticsClient createIoTAnalytics(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionIoTAnalytics(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\IoTEvents\IoTEventsClient createIoTEvents(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionIoTEvents(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\IoTEventsData\IoTEventsDataClient createIoTEventsData(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionIoTEventsData(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\IoTJobsDataPlane\IoTJobsDataPlaneClient createIoTJobsDataPlane(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionIoTJobsDataPlane(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\IoTSecureTunneling\IoTSecureTunnelingClient createIoTSecureTunneling(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionIoTSecureTunneling(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\IoTSiteWise\IoTSiteWiseClient createIoTSiteWise(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionIoTSiteWise(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\IoTThingsGraph\IoTThingsGraphClient createIoTThingsGraph(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionIoTThingsGraph(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Iot\IotClient createIot(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionIot(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\IotDataPlane\IotDataPlaneClient createIotDataPlane(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionIotDataPlane(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Kafka\KafkaClient createKafka(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionKafka(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Kinesis\KinesisClient createKinesis(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionKinesis(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\KinesisAnalytics\KinesisAnalyticsClient createKinesisAnalytics(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionKinesisAnalytics(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\KinesisAnalyticsV2\KinesisAnalyticsV2Client createKinesisAnalyticsV2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionKinesisAnalyticsV2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\KinesisVideo\KinesisVideoClient createKinesisVideo(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionKinesisVideo(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\KinesisVideoArchivedMedia\KinesisVideoArchivedMediaClient createKinesisVideoArchivedMedia(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionKinesisVideoArchivedMedia(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\KinesisVideoMedia\KinesisVideoMediaClient createKinesisVideoMedia(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionKinesisVideoMedia(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\KinesisVideoSignalingChannels\KinesisVideoSignalingChannelsClient createKinesisVideoSignalingChannels(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionKinesisVideoSignalingChannels(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Kms\KmsClient createKms(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionKms(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\LakeFormation\LakeFormationClient createLakeFormation(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionLakeFormation(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Lambda\LambdaClient createLambda(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionLambda(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\LexModelBuildingService\LexModelBuildingServiceClient createLexModelBuildingService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionLexModelBuildingService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\LexRuntimeService\LexRuntimeServiceClient createLexRuntimeService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionLexRuntimeService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\LicenseManager\LicenseManagerClient createLicenseManager(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionLicenseManager(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Lightsail\LightsailClient createLightsail(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionLightsail(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MQ\MQClient createMQ(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMQ(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MTurk\MTurkClient createMTurk(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMTurk(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MachineLearning\MachineLearningClient createMachineLearning(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMachineLearning(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Macie\MacieClient createMacie(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMacie(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Macie2\Macie2Client createMacie2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMacie2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ManagedBlockchain\ManagedBlockchainClient createManagedBlockchain(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionManagedBlockchain(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MarketplaceCatalog\MarketplaceCatalogClient createMarketplaceCatalog(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMarketplaceCatalog(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MarketplaceCommerceAnalytics\MarketplaceCommerceAnalyticsClient createMarketplaceCommerceAnalytics(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMarketplaceCommerceAnalytics(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MarketplaceEntitlementService\MarketplaceEntitlementServiceClient createMarketplaceEntitlementService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMarketplaceEntitlementService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MarketplaceMetering\MarketplaceMeteringClient createMarketplaceMetering(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMarketplaceMetering(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MediaConnect\MediaConnectClient createMediaConnect(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMediaConnect(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MediaConvert\MediaConvertClient createMediaConvert(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMediaConvert(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MediaLive\MediaLiveClient createMediaLive(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMediaLive(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MediaPackage\MediaPackageClient createMediaPackage(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMediaPackage(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MediaPackageVod\MediaPackageVodClient createMediaPackageVod(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMediaPackageVod(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MediaStore\MediaStoreClient createMediaStore(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMediaStore(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MediaStoreData\MediaStoreDataClient createMediaStoreData(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMediaStoreData(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MediaTailor\MediaTailorClient createMediaTailor(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMediaTailor(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MigrationHub\MigrationHubClient createMigrationHub(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMigrationHub(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MigrationHubConfig\MigrationHubConfigClient createMigrationHubConfig(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMigrationHubConfig(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Mobile\MobileClient createMobile(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionMobile(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Neptune\NeptuneClient createNeptune(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionNeptune(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\NetworkManager\NetworkManagerClient createNetworkManager(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionNetworkManager(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\OpsWorks\OpsWorksClient createOpsWorks(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionOpsWorks(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\OpsWorksCM\OpsWorksCMClient createOpsWorksCM(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionOpsWorksCM(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Organizations\OrganizationsClient createOrganizations(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionOrganizations(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Outposts\OutpostsClient createOutposts(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionOutposts(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\PI\PIClient createPI(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionPI(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Personalize\PersonalizeClient createPersonalize(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionPersonalize(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\PersonalizeEvents\PersonalizeEventsClient createPersonalizeEvents(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionPersonalizeEvents(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\PersonalizeRuntime\PersonalizeRuntimeClient createPersonalizeRuntime(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionPersonalizeRuntime(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Pinpoint\PinpointClient createPinpoint(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionPinpoint(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\PinpointEmail\PinpointEmailClient createPinpointEmail(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionPinpointEmail(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\PinpointSMSVoice\PinpointSMSVoiceClient createPinpointSMSVoice(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionPinpointSMSVoice(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Polly\PollyClient createPolly(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionPolly(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Pricing\PricingClient createPricing(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionPricing(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\QLDB\QLDBClient createQLDB(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionQLDB(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\QLDBSession\QLDBSessionClient createQLDBSession(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionQLDBSession(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\QuickSight\QuickSightClient createQuickSight(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionQuickSight(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\RAM\RAMClient createRAM(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionRAM(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\RDSDataService\RDSDataServiceClient createRDSDataService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionRDSDataService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Rds\RdsClient createRds(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionRds(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Redshift\RedshiftClient createRedshift(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionRedshift(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Rekognition\RekognitionClient createRekognition(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionRekognition(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ResourceGroups\ResourceGroupsClient createResourceGroups(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionResourceGroups(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ResourceGroupsTaggingAPI\ResourceGroupsTaggingAPIClient createResourceGroupsTaggingAPI(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionResourceGroupsTaggingAPI(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\RoboMaker\RoboMakerClient createRoboMaker(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionRoboMaker(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Route53\Route53Client createRoute53(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionRoute53(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Route53Domains\Route53DomainsClient createRoute53Domains(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionRoute53Domains(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Route53Resolver\Route53ResolverClient createRoute53Resolver(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionRoute53Resolver(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\S3\S3Client createS3(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\S3\S3MultiRegionClient createMultiRegionS3(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\S3Control\S3ControlClient createS3Control(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionS3Control(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\SSO\SSOClient createSSO(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSSO(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\SSOOIDC\SSOOIDCClient createSSOOIDC(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSSOOIDC(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\SageMaker\SageMakerClient createSageMaker(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSageMaker(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\SageMakerRuntime\SageMakerRuntimeClient createSageMakerRuntime(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSageMakerRuntime(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\SavingsPlans\SavingsPlansClient createSavingsPlans(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSavingsPlans(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Schemas\SchemasClient createSchemas(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSchemas(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\SecretsManager\SecretsManagerClient createSecretsManager(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSecretsManager(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\SecurityHub\SecurityHubClient createSecurityHub(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSecurityHub(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ServerlessApplicationRepository\ServerlessApplicationRepositoryClient createServerlessApplicationRepository(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionServerlessApplicationRepository(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ServiceCatalog\ServiceCatalogClient createServiceCatalog(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionServiceCatalog(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ServiceDiscovery\ServiceDiscoveryClient createServiceDiscovery(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionServiceDiscovery(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\ServiceQuotas\ServiceQuotasClient createServiceQuotas(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionServiceQuotas(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Ses\SesClient createSes(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSes(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\SesV2\SesV2Client createSesV2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSesV2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Sfn\SfnClient createSfn(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSfn(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Shield\ShieldClient createShield(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionShield(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Sms\SmsClient createSms(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSms(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\SnowBall\SnowBallClient createSnowBall(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSnowBall(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Sns\SnsClient createSns(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSns(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Sqs\SqsClient createSqs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSqs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Ssm\SsmClient createSsm(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSsm(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\StorageGateway\StorageGatewayClient createStorageGateway(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionStorageGateway(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Sts\StsClient createSts(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSts(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Support\SupportClient createSupport(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSupport(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Swf\SwfClient createSwf(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSwf(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Synthetics\SyntheticsClient createSynthetics(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionSynthetics(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Textract\TextractClient createTextract(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionTextract(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\TranscribeService\TranscribeServiceClient createTranscribeService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionTranscribeService(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Transfer\TransferClient createTransfer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionTransfer(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Translate\TranslateClient createTranslate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionTranslate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\WAFV2\WAFV2Client createWAFV2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionWAFV2(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Waf\WafClient createWaf(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionWaf(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\WafRegional\WafRegionalClient createWafRegional(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionWafRegional(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\WorkDocs\WorkDocsClient createWorkDocs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionWorkDocs(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\WorkLink\WorkLinkClient createWorkLink(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionWorkLink(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\WorkMail\WorkMailClient createWorkMail(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionWorkMail(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\WorkMailMessageFlow\WorkMailMessageFlowClient createWorkMailMessageFlow(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionWorkMailMessageFlow(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\WorkSpaces\WorkSpacesClient createWorkSpaces(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionWorkSpaces(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\XRay\XRayClient createXRay(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionXRay(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\imagebuilder\imagebuilderClient createimagebuilder(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionimagebuilder(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\kendra\kendraClient createkendra(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionkendra(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\signer\signerClient createsigner(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\MultiRegionClient createMultiRegionsigner(array $args = [])
 */
class Sdk
{
    const VERSION = '3.145.0';

    /** @var array Arguments for creating clients */
    private $args;

    /**
     * Constructs a new SDK object with an associative array of default
     * client settings.
     *
     * @param array $args
     *
     * @throws \InvalidArgumentException
     * @see Aws\AwsClient::__construct for a list of available options.
     */
    public function __construct(array $args = [])
    {
        $this->args = $args;

        if (!isset($args['handler']) && !isset($args['http_handler'])) {
            $this->args['http_handler'] = default_http_handler();
        }
    }

    public function __call($name, array $args)
    {
        $args = isset($args[0]) ? $args[0] : [];
        if (strpos($name, 'createMultiRegion') === 0) {
            return $this->createMultiRegionClient(substr($name, 17), $args);
        }

        if (strpos($name, 'create') === 0) {
            return $this->createClient(substr($name, 6), $args);
        }

        throw new \BadMethodCallException("Unknown method: {$name}.");
    }

    /**
     * Get a client by name using an array of constructor options.
     *
     * @param string $name Service name or namespace (e.g., DynamoDb, s3).
     * @param array  $args Arguments to configure the client.
     *
     * @return AwsClientInterface
     * @throws \InvalidArgumentException if any required options are missing or
     *                                   the service is not supported.
     * @see Aws\AwsClient::__construct for a list of available options for args.
     */
    public function createClient($name, array $args = [])
    {
        // Get information about the service from the manifest file.
        $service = manifest($name);
        $namespace = $service['namespace'];

        // Instantiate the client class.
        $client = "DeliciousBrains\WP_Offload_Media\Aws3\Aws\\{$namespace}\\{$namespace}Client";
        return new $client($this->mergeArgs($namespace, $service, $args));
    }

    public function createMultiRegionClient($name, array $args = [])
    {
        // Get information about the service from the manifest file.
        $service = manifest($name);
        $namespace = $service['namespace'];

        $klass = "DeliciousBrains\WP_Offload_Media\Aws3\Aws\\{$namespace}\\{$namespace}MultiRegionClient";
        $klass = class_exists($klass) ? $klass : 'Aws\\MultiRegionClient';

        return new $klass($this->mergeArgs($namespace, $service, $args));
    }

    /**
     * Clone existing SDK instance with ability to pass an associative array
     * of extra client settings.
     *
     * @param array $args
     *
     * @return self
     */
    public function copy(array $args = [])
    {
        return new self($args + $this->args);
    }

    private function mergeArgs($namespace, array $manifest, array $args = [])
    {
        // Merge provided args with stored, service-specific args.
        if (isset($this->args[$namespace])) {
            $args += $this->args[$namespace];
        }

        // Provide the endpoint prefix in the args.
        if (!isset($args['service'])) {
            $args['service'] = $manifest['endpoint'];
        }

        return $args + $this->args;
    }

    /**
     * Determine the endpoint prefix from a client namespace.
     *
     * @param string $name Namespace name
     *
     * @return string
     * @internal
     * @deprecated Use the `\DeliciousBrains\WP_Offload_Media\Aws3\Aws\manifest()` function instead.
     */
    public static function getEndpointPrefix($name)
    {
        return manifest($name)['endpoint'];
    }
}
