<?php

namespace DeliciousBrains\WP_Offload_S3\Providers;

use AS3CF_Plugin_Base;
use AS3CF_Utils;
use Exception;

abstract class Provider {

	/**
	 * @var AS3CF_Plugin_Base
	 */
	private $as3cf;

	/**
	 * @var
	 */
	private $client;

	/**
	 * @var string
	 */
	protected $provider_short_name = '';

	/**
	 * @var string
	 */
	protected $service_short_name = '';

	/**
	 * @var string
	 */
	protected $access_key_id_setting_name = '';

	/**
	 * @var string
	 */
	protected $secret_access_key_setting_name = '';

	/**
	 * @var array
	 */
	protected static $access_key_id_constants = array();

	/**
	 * @var array
	 */
	protected static $secret_access_key_constants = array();

	/**
	 * @var array
	 */
	protected static $use_server_roles_constants = array();

	/**
	 * @var array
	 */
	protected $regions = array();

	/**
	 * @var string
	 */
	protected $default_region = '';

	/**
	 * Provider constructor.
	 *
	 * @param AS3CF_Plugin_Base $as3cf
	 */
	public function __construct( \AS3CF_Plugin_Base $as3cf ) {
		$this->as3cf = $as3cf;
	}

	/**
	 * Whether or not access keys are needed.
	 *
	 * Keys are needed if we are not using server roles or not defined/set yet.
	 *
	 * @return bool
	 */
	public function needs_access_keys() {
		if ( $this->use_server_roles() ) {
			return false;
		}

		return ! $this->are_access_keys_set();
	}

	/**
	 * Check if both access key id & secret are present.
	 *
	 * @return bool
	 */
	function are_access_keys_set() {
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
		if ( $this->is_any_access_key_constant_defined() ) {
			$constant = $this->access_key_id_constant();

			return $constant ? constant( $constant ) : '';
		}

		return $this->as3cf->get_core_setting( $this->access_key_id_setting_name );
	}

	/**
	 * Get the secret access key from a constant or the settings.
	 *
	 * Falls back to settings only if neither constant is defined.
	 *
	 * @return string
	 */
	public function get_secret_access_key() {
		if ( $this->is_any_access_key_constant_defined() ) {
			$constant = $this->secret_access_key_constant();

			return $constant ? constant( $constant ) : '';
		}

		return $this->as3cf->get_core_setting( $this->secret_access_key_setting_name );
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
	public function use_server_roles() {
		$constant = $this->use_server_role_constant();

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
	public function get_default_region() {
		return $this->default_region;
	}


	/**
	 * Returns an array of valid region slugs and names.
	 *
	 * @return array Keys are region slug, values their name
	 */
	public function get_regions() {
		$regions = apply_filters( $this->provider_short_name . '_get_regions', $this->regions ); // Backwards compatibility, e.g. 'aws_get_regions'.

		return apply_filters( 'as3cf_' . $this->provider_short_name . '_get_regions', $regions );
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
			if ( ! $this->use_server_roles() ) {
				$args = array_merge( array(
					'credentials' => array(
						'key'    => $this->get_access_key_id(),
						'secret' => $this->get_secret_access_key(),
					),
				), $args );
			}

			// Add credentials and given args to default client args and then let user override.
			$args = array_merge( $this->default_client_args(), $args );
			$args = apply_filters( $this->provider_short_name . '_get_client_args', $args ); // Backwards compatibility, e.g. 'aws_get_client_args'.
			$args = apply_filters( 'as3cf_' . $this->provider_short_name . '_init_client_args', $args );

			$this->client = $this->init_client( $args );
		}
	}

	/**
	 * Get the service client instance.
	 *
	 * @param array $args Options for required region/endpoint
	 *
	 * @return Provider
	 * @throws Exception
	 */
	public function get_client( Array $args ) {
		$this->_init_client( $args );

		$args = apply_filters( 'as3cf_' . $this->provider_short_name . '_' . $this->service_short_name . '_client_args', $args );

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
				// TODO: Rename element when going multi-provider.
				$region_keys = $region['s3client']->list_keys( $region['locations'] );
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
	 * Returns default args array for the client.
	 *
	 * @return array
	 */
	abstract protected function default_client_args();

	/**
	 * Instantiate a new client for the provider's SDK.
	 *
	 * @param array $args
	 */
	abstract protected function init_client( Array $args );

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
}
