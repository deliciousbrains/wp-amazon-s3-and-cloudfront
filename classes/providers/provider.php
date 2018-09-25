<?php

namespace DeliciousBrains\WP_Offload_Media\Providers;

use AS3CF_Plugin_Base;
use AS3CF_Error;
use AS3CF_Utils;
use Exception;

abstract class Provider {

	/**
	 * @var \Amazon_S3_And_CloudFront|\Amazon_S3_And_CloudFront_Pro
	 */
	private $as3cf;

	/**
	 * @var
	 */
	private $client;

	/**
	 * @var string
	 */
	protected static $provider_name = '';

	/**
	 * @var string
	 */
	protected static $provider_short_name = '';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $provider_key_name = '';

	/**
	 * @var string
	 */
	protected static $service_name = '';

	/**
	 * @var string
	 */
	protected static $service_short_name = '';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $service_key_name = '';

	/**
	 * Optional override of "Provider Name" + "Service Name" for friendly name for service.
	 *
	 * @var string
	 */
	protected static $provider_service_name = '';

	/**
	 * @var string
	 */
	protected static $access_key_id_setting_name = 'access-key-id';

	/**
	 * @var string
	 */
	protected static $secret_access_key_setting_name = 'secret-access-key';

	/**
	 * @var array
	 */
	protected static $access_key_id_constants = array();

	/**
	 * @var array
	 */
	protected static $secret_access_key_constants = array();

	/**
	 * If left empty, server roles not allowed.
	 *
	 * @var array
	 */
	protected static $use_server_roles_constants = array();

	/**
	 * @var array
	 */
	protected $regions = array();

	/**
	 * @var bool
	 */
	protected $region_required = false;

	/**
	 * @var string
	 */
	protected $default_region = '';

	/**
	 * @var string
	 */
	protected $default_domain = '';

	/**
	 * @var string
	 */
	protected $console_url = '';

	/**
	 * @var string
	 */
	protected $console_url_param = '';

	/**
	 * Provider constructor.
	 *
	 * @param AS3CF_Plugin_Base $as3cf
	 */
	public function __construct( \AS3CF_Plugin_Base $as3cf ) {
		$this->as3cf = $as3cf;
	}

	/**
	 * Returns the full name for the provider.
	 *
	 * @return string
	 */
	public static function get_provider_name() {
		return static::$provider_name;
	}

	/**
	 * Returns the key friendly name for the provider.
	 *
	 * @return string
	 */
	public static function get_provider_key_name() {
		return static::$provider_key_name;
	}

	/**
	 * Returns the key friendly name for the service.
	 *
	 * @return string
	 */
	public static function get_service_key_name() {
		return static::$provider_key_name;
	}

	/**
	 * Returns the full name for the provider and service for display.
	 *
	 * @return string
	 */
	public static function get_provider_service_name() {
		if ( ! empty( static::$provider_service_name ) ) {
			return static::$provider_service_name;
		}

		return static::$provider_name . ' ' . static::$service_name;
	}

	/**
	 * Whether or not access keys are needed.
	 *
	 * Keys are needed if we are not using server roles or not defined/set yet.
	 *
	 * @return bool
	 */
	public function needs_access_keys() {
		if ( static::use_server_roles() ) {
			return false;
		}

		return ! $this->are_access_keys_set();
	}

	/**
	 * Check if both access key id & secret are present.
	 *
	 * @return bool
	 */
	public function are_access_keys_set() {
		return $this->get_access_key_id() && $this->get_secret_access_key();
	}

	/**
	 * Get the access key from a constant or the settings.
	 *
	 * Falls back to settings only if neither constant is defined.
	 *
	 * @return string
	 */
	public function get_access_key_id() {
		if ( static::is_any_access_key_constant_defined() ) {
			$constant = static::access_key_id_constant();

			return $constant ? constant( $constant ) : '';
		}

		return $this->as3cf->get_core_setting( static::$access_key_id_setting_name );
	}

	/**
	 * Get the secret access key from a constant or the settings.
	 *
	 * Falls back to settings only if neither constant is defined.
	 *
	 * @return string
	 */
	public function get_secret_access_key() {
		if ( static::is_any_access_key_constant_defined() ) {
			$constant = static::secret_access_key_constant();

			return $constant ? constant( $constant ) : '';
		}

		return $this->as3cf->get_core_setting( static::$secret_access_key_setting_name );
	}

	/**
	 * Check if any access key (id or secret, prefixed or not) is defined.
	 *
	 * @return bool
	 */
	public static function is_any_access_key_constant_defined() {
		return static::access_key_id_constant() || static::secret_access_key_constant();
	}

	/**
	 * Allows the provider's client factory to use server roles instead of key/secret for credentials.
	 * http://docs.aws.amazon.com/aws-sdk-php/guide/latest/credentials.html#instance-profile-credentials
	 *
	 * @return bool
	 */
	public static function use_server_roles() {
		if ( ! static::use_server_roles_allowed() ) {
			return false;
		}

		$constant = static::use_server_role_constant();

		return $constant && constant( $constant );
	}

	/**
	 * Get the constant used to define the access key id.
	 *
	 * @return string|false Constant name if defined, otherwise false
	 */
	public static function access_key_id_constant() {
		return AS3CF_Utils::get_first_defined_constant( static::$access_key_id_constants );
	}

	/**
	 * Get the constant used to define the aws secret access key.
	 *
	 * @return string|false Constant name if defined, otherwise false
	 */
	public static function secret_access_key_constant() {
		return AS3CF_Utils::get_first_defined_constant( static::$secret_access_key_constants );
	}

	/**
	 * Is the provider able to use server roles?
	 *
	 * @return bool
	 */
	public static function use_server_roles_allowed() {
		return ! empty( static::$use_server_roles_constants );
	}

	/**
	 * Get the constant used to enable the use of EC2 IAM roles.
	 *
	 * @return string|false Constant name if defined, otherwise false
	 */
	public static function use_server_role_constant() {
		return AS3CF_Utils::get_first_defined_constant( static::$use_server_roles_constants );
	}

	/**
	 * Returns the Provider's default region slug.
	 *
	 * @return string
	 */
	public function region_required() {
		return $this->region_required;
	}

	/**
	 * Returns the Provider's default region slug.
	 *
	 * @return string
	 */
	public function get_default_region() {
		return $this->default_region;
	}

	/**
	 * Returns the Provider's base domain.
	 *
	 * Does not include region prefix or bucket path etc.
	 *
	 * @return string
	 */
	public function get_domain() {
		return apply_filters( 'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_domain', $this->default_domain );
	}

	/**
	 * Returns an array of valid region slugs and names.
	 *
	 * @return array Keys are region slug, values their name
	 */
	public function get_regions() {
		$regions = apply_filters( static::$provider_key_name . '_get_regions', $this->regions ); // Backwards compatibility, e.g. 'aws_get_regions'.

		return apply_filters( 'as3cf_' . static::$provider_key_name . '_get_regions', $regions );
	}

	/**
	 * Returns readable region name.
	 *
	 * @param string $region
	 * @param bool   $with_key
	 *
	 * @return string
	 */
	public function get_region_name( $region = '', $with_key = false ) {
		if ( empty( $region ) && ! $this->region_required() ) {
			$region = $this->get_default_region();
		}

		$regions = $this->get_regions();

		$region_name = empty( $regions[ $region ] ) ? '' : $regions[ $region ];

		if ( $with_key ) {
			$region_name .= empty( $region_name ) ? $region : ' (' . $region . ')';
		}

		return $region_name;
	}

	/**
	 * Instantiate a new service client for the provider.
	 *
	 * @param array $args Options for required region/endpoint
	 *
	 * @throws Exception
	 */
	private function _init_client( Array $args ) {
		if ( $this->needs_access_keys() ) {
			throw new Exception( sprintf( __( 'You must first <a href="%s">set your access keys</a>.', 'amazon-s3-and-cloudfront' ), $this->as3cf->get_plugin_page_url() . '#settings' ) );
		}

		if ( is_null( $this->client ) ) {
			if ( ! static::use_server_roles() ) {
				$args = array_merge( array(
					'credentials' => array(
						'key'    => $this->get_access_key_id(),
						'secret' => $this->get_secret_access_key(),
					),
				), $args );
			}

			// Add credentials and given args to default client args and then let user override.
			$args = array_merge( $this->default_client_args(), $args );
			$args = apply_filters( 'as3cf_' . static::$provider_key_name . '_init_client_args', $this->init_client_args( $args ) );
			$args = apply_filters( static::$provider_key_name . '_get_client_args', $args ); // Backwards compatibility, e.g. 'aws_get_client_args'.

			$this->client = $this->init_client( $args );
		}
	}

	/**
	 * Get the service client instance.
	 *
	 * @param array $args Options for required region/endpoint
	 * @param bool  $force
	 *
	 * @return Provider
	 * @throws Exception
	 */
	public function get_client( Array $args, $force = false ) {
		if ( true === $force ) {
			$this->client = null;
		}

		$this->_init_client( $args );

		$args = apply_filters( 'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_client_args', $this->init_service_client_args( $args ) );

		$this->client = $this->init_service_client( $args );

		return $this;
	}

	/**
	 * Get object keys from multiple clients.
	 *
	 * @param array $regions
	 *
	 * @return array
	 */
	public static function get_keys_from_regions( Array $regions ) {
		$keys = array();

		foreach ( $regions as $region ) {
			try {
				/* @var $client Provider */
				$client      = $region['provider_client'];
				$region_keys = $client->list_keys( $region['locations'] );
			} catch ( \Exception $e ) {
				AS3CF_Error::log( get_class( $e ) . ' exception caught when executing list_keys: ' . $e->getMessage() );
				continue;
			}

			if ( ! empty( $region_keys ) ) {
				foreach ( $region_keys as $attachment_id => $found_keys ) {
					$keys[ $attachment_id ] = AS3CF_Utils::validate_attachment_keys( $attachment_id, $found_keys );
				}
			}
		}

		return $keys;
	}

	/**
	 * Generate a stream wrapper compatible URL
	 *
	 * @param string $region
	 * @param string $bucket
	 * @param string $key
	 *
	 * @return string
	 */
	public function prepare_stream_wrapper_file( $region, $bucket, $key ) {
		$protocol = $this->get_stream_wrapper_protocol( $region );

		return $protocol . '://' . $bucket . '/' . $key;
	}

	/**
	 * Get the region specific prefix for URL
	 *
	 * @param string   $region
	 * @param null|int $expires
	 *
	 * @return string
	 */
	public function get_url_prefix( $region = '', $expires = null ) {
		/**
		 * Region specific prefix for raw URL
		 *
		 * @param string   $prefix
		 * @param string   $region
		 * @param null|int $expires
		 *
		 * @return string
		 */
		return apply_filters( 'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_url_prefix', $this->url_prefix( $region, $expires ), $region, $expires );
	}

	/**
	 * Get the url domain for the files
	 *
	 * @param string $bucket
	 * @param string $region
	 * @param int    $expires
	 * @param array  $args    Allows you to specify custom URL settings
	 * @param bool   $preview When generating the URL preview sanitize certain output
	 *
	 * @return string
	 */
	public function get_url_domain( $bucket, $region = '', $expires = null, $args = array(), $preview = false ) {
		if ( ! isset( $args['cloudfront'] ) ) {
			$args['cloudfront'] = $this->as3cf->get_setting( 'cloudfront' );
		}

		if ( ! isset( $args['domain'] ) ) {
			$args['domain'] = $this->as3cf->get_setting( 'domain' );
		}

		if ( ! isset( $args['force-https'] ) ) {
			$args['force-https'] = $this->as3cf->use_ssl( $this->as3cf->get_setting( 'force-https' ) );
		}

		$prefix = $this->url_prefix( $region, $expires );
		$domain = $this->get_domain();
		$domain = empty( $prefix ) ? $domain : $prefix . '.' . $domain;

		return apply_filters(
			'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_url_domain',
			$this->url_domain( $domain, $bucket, $region, $expires, $args, $preview ),
			$bucket,
			$region,
			$expires,
			$args,
			$preview
		);
	}

	/**
	 * Get the link to the bucket on the provider's console.
	 *
	 * @param string $bucket
	 * @param string $prefix
	 *
	 * @return string
	 */
	public function get_console_url( $bucket = '', $prefix = '' ) {
		if ( '' !== $prefix ) {
			$prefix = $this->get_console_url_param() . urlencode( apply_filters( 'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_console_url_prefix_value', $prefix ) );
		}

		return apply_filters( 'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_console_url', $this->console_url ) . $bucket . $prefix;
	}

	/**
	 * Get the prefix param to append to the link to the bucket on the provider's console.
	 *
	 * @return string
	 */
	public function get_console_url_param() {
		return apply_filters( 'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_console_url_prefix_param', $this->console_url_param );
	}

	/**
	 * Returns default args array for the client.
	 *
	 * @return array
	 */
	abstract protected function default_client_args();

	/**
	 * Process the args before instantiating a new client for the provider's SDK.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	abstract protected function init_client_args( Array $args );

	/**
	 * Instantiate a new client for the provider's SDK.
	 *
	 * @param array $args
	 */
	abstract protected function init_client( Array $args );

	/**
	 * Process the args before instantiating a new service specific client.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	abstract protected function init_service_client_args( Array $args );

	/**
	 * Instantiate a new service specific client.
	 * Depending on SDK, may simply return client instantiated with `init_client`.
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	abstract protected function init_service_client( Array $args );

	/**
	 * Make sure region "slug" fits expected format.
	 *
	 * @param string $region
	 *
	 * @return string
	 */
	abstract public function sanitize_region( $region );

	/**
	 * Create bucket.
	 *
	 * @param array $args
	 */
	abstract public function create_bucket( Array $args );

	/**
	 * Check whether bucket exists.
	 *
	 * @param $bucket
	 *
	 * @return bool
	 */
	abstract public function does_bucket_exist( $bucket );

	/**
	 * Returns region for bucket.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	abstract public function get_bucket_location( Array $args );

	/**
	 * List buckets.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	abstract public function list_buckets( Array $args = array() );

	/**
	 * Check whether key exists in bucket.
	 *
	 * @param       $bucket
	 * @param       $key
	 * @param array $options
	 *
	 * @return bool
	 */
	abstract public function does_object_exist( $bucket, $key, Array $options = array() );

	/**
	 * Get default "canned" ACL string.
	 *
	 * @return string
	 */
	abstract public function get_default_acl();

	/**
	 * Get private "canned" ACL string.
	 *
	 * @return string
	 */
	abstract public function get_private_acl();

	/**
	 * Download object, destination specified in args.
	 *
	 * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#getobject
	 *
	 * @param array $args
	 */
	abstract public function get_object( Array $args );

	/**
	 * Get object's URL.
	 *
	 * @param       $bucket
	 * @param       $key
	 * @param       $expires
	 * @param array $args
	 *
	 * @return string
	 */
	abstract public function get_object_url( $bucket, $key, $expires, Array $args = array() );

	/**
	 * List objects.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	abstract public function list_objects( Array $args = array() );

	/**
	 * Update the ACL for an object.
	 *
	 * @param array $args
	 */
	abstract public function update_object_acl( Array $args );

	/**
	 * Upload file to bucket.
	 *
	 * @param array $args
	 */
	abstract public function upload_object( Array $args );

	/**
	 * Delete object from bucket.
	 *
	 * @param array $args
	 */
	abstract public function delete_object( Array $args );

	/**
	 * Delete multiple objects from bucket.
	 *
	 * @param array $args
	 */
	abstract public function delete_objects( Array $args );

	/**
	 * Returns arrays of found keys for given bucket and prefix locations, retaining given array's integer based index.
	 *
	 * @param array $locations Array with attachment ID as key and Bucket and Prefix in an associative array as values.
	 *
	 * @return array
	 */
	abstract public function list_keys( Array $locations );

	/**
	 * Copies objects into current bucket from another bucket hosted with provider.
	 *
	 * @param array $items
	 *
	 * @return array Failures with elements Key and Message
	 */
	abstract public function copy_objects( Array $items );

	/**
	 * Generate the stream wrapper protocol
	 *
	 * @param string $region
	 *
	 * @return string
	 */
	abstract protected function get_stream_wrapper_protocol( $region );

	/**
	 * Register a stream wrapper for specific region.
	 *
	 * @param string $region
	 *
	 * @return bool
	 */
	abstract public function register_stream_wrapper( $region );

	/**
	 * Check that a bucket and key can be written to.
	 *
	 * @param string $bucket
	 * @param string $key
	 * @param string $file_contents
	 *
	 * @return bool|string Error message on unexpected exception
	 */
	abstract public function can_write( $bucket, $key, $file_contents );

	/**
	 * Get the region specific prefix for raw URL
	 *
	 * @param string   $region
	 * @param null|int $expires
	 *
	 * @return string
	 */
	abstract protected function url_prefix( $region = '', $expires = null );

	/**
	 * Get the url domain for the files
	 *
	 * @param string $domain  Likely prefixed with region
	 * @param string $bucket
	 * @param string $region
	 * @param int    $expires
	 * @param array  $args    Allows you to specify custom URL settings
	 * @param bool   $preview When generating the URL preview sanitize certain output
	 *
	 * @return string
	 */
	abstract protected function url_domain( $domain, $bucket, $region = '', $expires = null, $args = array(), $preview = false );
}
