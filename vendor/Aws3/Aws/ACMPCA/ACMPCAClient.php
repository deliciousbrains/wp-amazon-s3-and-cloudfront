<?php
namespace Aws\ACMPCA;

use Aws\AwsClient;

/**
 * This client is used to interact with the **AWS Certificate Manager Private Certificate Authority** service.
 * @method \Aws\Result createCertificateAuthority(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createCertificateAuthorityAsync(array $args = [])
 * @method \Aws\Result createCertificateAuthorityAuditReport(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createCertificateAuthorityAuditReportAsync(array $args = [])
 * @method \Aws\Result createPermission(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise createPermissionAsync(array $args = [])
 * @method \Aws\Result deleteCertificateAuthority(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deleteCertificateAuthorityAsync(array $args = [])
 * @method \Aws\Result deletePermission(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise deletePermissionAsync(array $args = [])
 * @method \Aws\Result describeCertificateAuthority(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeCertificateAuthorityAsync(array $args = [])
 * @method \Aws\Result describeCertificateAuthorityAuditReport(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise describeCertificateAuthorityAuditReportAsync(array $args = [])
 * @method \Aws\Result getCertificate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getCertificateAsync(array $args = [])
 * @method \Aws\Result getCertificateAuthorityCertificate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getCertificateAuthorityCertificateAsync(array $args = [])
 * @method \Aws\Result getCertificateAuthorityCsr(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getCertificateAuthorityCsrAsync(array $args = [])
 * @method \Aws\Result importCertificateAuthorityCertificate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise importCertificateAuthorityCertificateAsync(array $args = [])
 * @method \Aws\Result issueCertificate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise issueCertificateAsync(array $args = [])
 * @method \Aws\Result listCertificateAuthorities(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listCertificateAuthoritiesAsync(array $args = [])
 * @method \Aws\Result listPermissions(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listPermissionsAsync(array $args = [])
 * @method \Aws\Result listTags(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise listTagsAsync(array $args = [])
 * @method \Aws\Result restoreCertificateAuthority(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise restoreCertificateAuthorityAsync(array $args = [])
 * @method \Aws\Result revokeCertificate(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise revokeCertificateAsync(array $args = [])
 * @method \Aws\Result tagCertificateAuthority(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise tagCertificateAuthorityAsync(array $args = [])
 * @method \Aws\Result untagCertificateAuthority(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise untagCertificateAuthorityAsync(array $args = [])
 * @method \Aws\Result updateCertificateAuthority(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise updateCertificateAuthorityAsync(array $args = [])
 */
class ACMPCAClient extends AwsClient {}
