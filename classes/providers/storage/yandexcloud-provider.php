<?php

namespace DeliciousBrains\WP_Offload_Media\Providers\Storage;

class YandexCloud_Provider extends AWS_Provider {

	/**
	 * @var string
	 */
	protected static $provider_name = 'Yandex Cloud';

	/**
	 * @var string
	 */
	protected static $provider_short_name = 'YC';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $provider_key_name = 'yc';

	/**
	 * @var string
	 */
	protected static $service_name = 'Object Storage';

	/**
	 * @var string
	 */
	protected static $service_short_name = 'Storage';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $service_key_name = 'storage';

	/**
	 * Optional override of "Provider Name" + "Service Name" for friendly name for service.
	 *
	 * @var string
	 */
	protected static $provider_service_name = 'Yandex Cloud Object Storage';

	/**
	 * The slug for the service's quick start guide doc.
	 *
	 * @var string
	 */
	protected static $provider_service_quick_start_slug = 'amazon-s3-quick-start-guide';

	/**
	 * @var array
	 */
	protected static $access_key_id_constants = array(
		'AS3CF_YC_ACCESS_KEY_ID',
	);

	/**
	 * @var array
	 */
	protected static $secret_access_key_constants = array(
		'AS3CF_YC_SECRET_ACCESS_KEY',
	);

	/**
	 * @var array
	 */
	protected static $use_server_roles_constants = array();

	/**
	 * @var bool
	 */
	protected static $block_public_access_allowed = true;

	/**
	 * @var array
	 */
	protected $regions = array(
		'ru-central1'    => 'RU Central',
	);

	/**
	 * @var string
	 */
	protected $default_region = 'ru-central1';

	/**
	 * @var string
	 */
	protected $default_domain = 'storage.yandexcloud.net';

	/**
	 * @var string
	 */
	protected $console_url = 'https://console.cloud.yandex.ru/';

	/**
	 * @var string
	 */
	protected $console_url_prefix_param = '/';

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
			// YandexCloud endpoints does not contain a region.
			$args['endpoint'] = 'https://' . $this->get_domain();
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
	 * Get the region specific prefix for raw URL
	 *
	 * @param string   $region
	 * @param null|int $expires
	 *
	 * @return string
	 */
	protected function url_prefix( $region = '', $expires = null ) {
		// YandexCloud endpoints does not contain any prefix.
		$prefix = '';
		return $prefix;
	}

}
