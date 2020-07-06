<?php
namespace DeliciousBrains\WP_Offload_Media\Aws3\Aws\Sts;

use DeliciousBrains\WP_Offload_Media\Aws3\Aws\AwsClient;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\CacheInterface;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\Credentials\Credentials;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result;
use DeliciousBrains\WP_Offload_Media\Aws3\Aws\Sts\RegionalEndpoints\ConfigurationProvider;

/**
 * This client is used to interact with the **AWS Security Token Service (AWS STS)**.
 *
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result assumeRole(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise assumeRoleAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result assumeRoleWithSAML(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise assumeRoleWithSAMLAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result assumeRoleWithWebIdentity(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise assumeRoleWithWebIdentityAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result decodeAuthorizationMessage(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise decodeAuthorizationMessageAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getAccessKeyInfo(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getAccessKeyInfoAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getCallerIdentity(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getCallerIdentityAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getFederationToken(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getFederationTokenAsync(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\Aws\Result getSessionToken(array $args = [])
 * @method \DeliciousBrains\WP_Offload_Media\Aws3\GuzzleHttp\Promise\Promise getSessionTokenAsync(array $args = [])
 */
class StsClient extends AwsClient
{

    /**
     * {@inheritdoc}
     *
     * In addition to the options available to
     * {@see \DeliciousBrains\WP_Offload_Media\Aws3\Aws\AwsClient::__construct}, StsClient accepts the following
     * options:
     *
     * - sts_regional_endpoints:
     *   (DeliciousBrains\WP_Offload_Media\Aws3\Aws\Sts\RegionalEndpoints\ConfigurationInterface|DeliciousBrains\WP_Offload_Media\Aws3\Aws\CacheInterface\|callable|string|array)
     *   Specifies whether to use regional or legacy endpoints for legacy regions.
     *   Provide an DeliciousBrains\WP_Offload_Media\Aws3\Aws\Sts\RegionalEndpoints\ConfigurationInterface object, an
     *   instance of DeliciousBrains\WP_Offload_Media\Aws3\Aws\CacheInterface, a callable configuration provider used
     *   to create endpoint configuration, a string value of `legacy` or
     *   `regional`, or an associative array with the following keys:
     *   endpoint_types (string)  Set to `legacy` or `regional`, defaults to
     *   `legacy`
     *
     * @param array $args
     */
    public function __construct(array $args)
    {
        if (!isset($args['sts_regional_endpoints'])) {
            $args['sts_regional_endpoints'] = ConfigurationProvider::defaultProvider();
        } elseif ($args['sts_regional_endpoints'] instanceof CacheInterface) {
            $args['sts_regional_endpoints'] = ConfigurationProvider::defaultProvider($args);
        }
        parent::__construct($args);
    }

    /**
     * Creates credentials from the result of an STS operations
     *
     * @param Result $result Result of an STS operation
     *
     * @return Credentials
     * @throws \InvalidArgumentException if the result contains no credentials
     */
    public function createCredentials(Result $result)
    {
        if (!$result->hasKey('Credentials')) {
            throw new \InvalidArgumentException('Result contains no credentials');
        }

        $c = $result['Credentials'];

        return new Credentials(
            $c['AccessKeyId'],
            $c['SecretAccessKey'],
            isset($c['SessionToken']) ? $c['SessionToken'] : null,
            isset($c['Expiration']) && $c['Expiration'] instanceof \DateTimeInterface
                ? (int) $c['Expiration']->format('U')
                : null
        );
    }
}
