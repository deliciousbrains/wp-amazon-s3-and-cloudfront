<?php

namespace DeliciousBrains\WP_Offload_Media\Providers;

class Linode_Provider extends AWS_Provider {

	/**
	 * @var string
	 */
	protected static $provider_name = 'Linode';

	/**
	 * @var string
	 */
	protected static $provider_short_name = 'Linode';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $provider_key_name = 'linode';

	/**
	 * @var string
	 */
	protected static $service_name = 'Object Storage';

	/**
	 * @var string
	 */
	protected static $service_short_name = 'Object Storage';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $service_key_name = 'objectstorage';

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
	protected static $provider_service_quick_start_slug = 'Linode-Object-Storage-quick-start-guide';

	/**
	 * @var array
	 */
	protected static $access_key_id_constants = array(
		'AS3CF_LINODE_ACCESS_KEY_ID',
	);

	/**
	 * @var array
	 */
	protected static $secret_access_key_constants = array(
		'AS3CF_LINODE_SECRET_ACCESS_KEY',
	);

	/**
	 * @var array
	 */
	protected static $use_server_roles_constants = array();

	/**
	 * @var array
	 */
	protected $regions = array(
		'us-east-1' => 'Newark',
	);

	/**
	 * @var bool
	 */
	protected $region_required = true;

	/**
	 * @var string
	 */
	protected $default_region = 'us-east-1';

	/**
	 * @var string
	 */
	protected $default_domain = 'linodeobjects.com';

	/**
	 * @var string
	 */
	protected $console_url = 'https://cloud.linode.com/';

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
	 * Create bucket.
	 *
	 * @param array $args
	 *
	 * @throws \Exception
	 */
	public function create_bucket( Array $args ) {
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
         $location = $this->s3_client->getBucketLocation( $args );
         $region   = empty( $location['LocationConstraint'] ) ? '' : $location['LocationConstraint'];

         return $region;
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
