<?php
namespace Aws\CodeArtifact;

use Aws\AwsClient;

/**
 * This client is used to interact with the **CodeArtifact** service.
 * @method \Aws\Result associateExternalConnection(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise associateExternalConnectionAsync(array $args = [])
 * @method \Aws\Result copyPackageVersions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise copyPackageVersionsAsync(array $args = [])
 * @method \Aws\Result createDomain(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createDomainAsync(array $args = [])
 * @method \Aws\Result createRepository(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createRepositoryAsync(array $args = [])
 * @method \Aws\Result deleteDomain(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteDomainAsync(array $args = [])
 * @method \Aws\Result deleteDomainPermissionsPolicy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteDomainPermissionsPolicyAsync(array $args = [])
 * @method \Aws\Result deletePackageVersions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deletePackageVersionsAsync(array $args = [])
 * @method \Aws\Result deleteRepository(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteRepositoryAsync(array $args = [])
 * @method \Aws\Result deleteRepositoryPermissionsPolicy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteRepositoryPermissionsPolicyAsync(array $args = [])
 * @method \Aws\Result describeDomain(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeDomainAsync(array $args = [])
 * @method \Aws\Result describePackageVersion(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describePackageVersionAsync(array $args = [])
 * @method \Aws\Result describeRepository(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeRepositoryAsync(array $args = [])
 * @method \Aws\Result disassociateExternalConnection(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise disassociateExternalConnectionAsync(array $args = [])
 * @method \Aws\Result disposePackageVersions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise disposePackageVersionsAsync(array $args = [])
 * @method \Aws\Result getAuthorizationToken(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getAuthorizationTokenAsync(array $args = [])
 * @method \Aws\Result getDomainPermissionsPolicy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getDomainPermissionsPolicyAsync(array $args = [])
 * @method \Aws\Result getPackageVersionAsset(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getPackageVersionAssetAsync(array $args = [])
 * @method \Aws\Result getPackageVersionReadme(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getPackageVersionReadmeAsync(array $args = [])
 * @method \Aws\Result getRepositoryEndpoint(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getRepositoryEndpointAsync(array $args = [])
 * @method \Aws\Result getRepositoryPermissionsPolicy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getRepositoryPermissionsPolicyAsync(array $args = [])
 * @method \Aws\Result listDomains(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listDomainsAsync(array $args = [])
 * @method \Aws\Result listPackageVersionAssets(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listPackageVersionAssetsAsync(array $args = [])
 * @method \Aws\Result listPackageVersionDependencies(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listPackageVersionDependenciesAsync(array $args = [])
 * @method \Aws\Result listPackageVersions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listPackageVersionsAsync(array $args = [])
 * @method \Aws\Result listPackages(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listPackagesAsync(array $args = [])
 * @method \Aws\Result listRepositories(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listRepositoriesAsync(array $args = [])
 * @method \Aws\Result listRepositoriesInDomain(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listRepositoriesInDomainAsync(array $args = [])
 * @method \Aws\Result putDomainPermissionsPolicy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putDomainPermissionsPolicyAsync(array $args = [])
 * @method \Aws\Result putRepositoryPermissionsPolicy(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise putRepositoryPermissionsPolicyAsync(array $args = [])
 * @method \Aws\Result updatePackageVersionsStatus(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updatePackageVersionsStatusAsync(array $args = [])
 * @method \Aws\Result updateRepository(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateRepositoryAsync(array $args = [])
 */
class CodeArtifactClient extends AwsClient {}
