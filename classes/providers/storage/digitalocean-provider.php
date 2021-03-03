<?php

namespace DeliciousBrains\WP_Offload_Media\Providers\Storage;

class DigitalOcean_Provider extends AWS_Provider {

	/**
	 * @var string
	 */
	protected static $provider_name = 'DigitalOcean';

	/**
	 * @var string
	 */
	protected static $provider_short_name = 'DigitalOcean';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $provider_key_name = 'do';

	/**
	 * @var string
	 */
	protected static $service_name = 'Spaces';

	/**
	 * @var string
	 */
	protected static $service_short_name = 'Spaces';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $service_key_name = 'spaces';

	/**
	 * Optional override of "Provider Name" + "Service Name" for friendly name for service.
	 *
	 * @var string
	 */
	protected static $provider_service_name = '';

	/**
	 * The slug for the service's quick start guide doc.
	 *
	 * @var string
	 */
	protected static $provider_service_quick_start_slug = 'digitalocean-spaces-quick-start-guide';

	/**
	 * @var array
	 */
	protected static $access_key_id_constants = array(
		'AS3CF_DO_ACCESS_KEY_ID',
	);

	/**
	 * @var array
	 */
	protected static $secret_access_key_constants = array(
		'AS3CF_DO_SECRET_ACCESS_KEY',
	);

	/**
	 * @var array
	 */
	protected static $use_server_roles_constants = array();

	/**
	 * @var bool
	 */
	protected static $block_public_access_allowed = false;

	/**
	 * @var array
	 */
	protected $regions = array(
		'nyc3' => 'New York',
		'ams3' => 'Amsterdam',
		'sgp1' => 'Singapore',
		'sfo2' => 'San Francisco 2',
		'sfo3' => 'San Francisco 3',
		'fra1' => 'Frankfurt',
	);

	/**
	 * @var bool
	 */
	protected $region_required = true;

	/**
	 * @var string
	 */
	protected $default_region = 'nyc3';

	/**
	 * @var string
	 */
	protected $default_domain = 'digitaloceanspaces.com';

	/**
	 * @var string
	 */
	protected $console_url = 'https://cloud.digitalocean.com/spaces/';

	/**
	 * @var string
	 */
	protected $console_url_prefix_param = '?path=';

	/**
	 * @var array
	 */
	private $client_args = array();

	/**
	 * Process the args before instantiating a new client for the provider's SDK.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	protected function init_client_args( Array $args ) {
		if ( empty( $args['endpoint'] ) ) {
			// DigitalOcean endpoints always require a region.
			$args['region'] = empty( $args['region'] ) ? $this->get_default_region() : $args['region'];

			$args['endpoint'] = 'https://' . $args['region'] . '.' . $this->get_domain();
		}

		$this->client_args = $args;

		return $this->client_args;
	}

	/**
	 * Process the args before instantiating a new service specific client.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	protected function init_service_client_args( Array $args ) {
		return $args;
	}

	/**
	 * Update the block public access setting for the given bucket.
	 *
	 * @param string $bucket
	 * @param bool   $block
	 */
	public function block_public_access( $bucket, $block ) {
		// DigitalOcean doesn't support this, so do nothing.
		return;
	}

	/**
	 * Create bucket.
	 *
	 * @param array $args
	 *
	 * @throws \Exception
	 */
	public function create_bucket( Array $args ) {
		// DigitalOcean is happy with the standard V4 signature, unless doing a "CreateBucket"!
		if ( ! empty( $this->client_args['signature_version'] ) && 'v4-unsigned-body' === $this->client_args['signature_version'] ) {
			parent::create_bucket( $args );
		} else {
			$client_args                      = $this->client_args;
			$client_args['signature_version'] = 'v4-unsigned-body';
			$this->get_client( $client_args, true )->create_bucket( $args );
		}
	}

	/**
	 * Returns region for bucket.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function get_bucket_location( Array $args ) {
		// For some reason DigitalOcean Spaces returns an XML LocationConstraint segment prepended to the region key.
		return strip_tags( parent::get_bucket_location( $args ) );
	}

	/**
	 * Get the region specific prefix for raw URL
	 *
	 * @param string   $region
	 * @param null|int $expires
	 *
	 * @return string
	 */
	protected function url_prefix( $region = '', $expires = null ) {
		return $region;
	}
}
