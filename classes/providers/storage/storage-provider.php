<?php

namespace DeliciousBrains\WP_Offload_Media\Providers\Storage;

use Amazon_S3_And_CloudFront;
use AS3CF_Error;
use AS3CF_Utils;
use DeliciousBrains\WP_Offload_Media\Providers\Provider;
use Exception;

abstract class Storage_Provider extends Provider {

	/**
	 * @var
	 */
	private $client;

	/**
	 * @var string
	 */
	protected static $access_key_id_setting_name = 'access-key-id';

	/**
	 * @var string
	 */
	protected static $secret_access_key_setting_name = 'secret-access-key';

	/**
	 * @var string
	 */
	protected static $use_server_roles_setting_name = 'use-server-roles';

	/**
	 * @var string
	 */
	protected static $key_file_setting_name = 'key-file';

	/**
	 * @var string
	 */
	protected static $key_file_path_setting_name = 'key-file-path';

	/**
	 * If left empty, access keys not allowed.
	 *
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
	 * If left empty, key file not allowed.
	 *
	 * @var array
	 */
	protected static $key_file_path_constants = array();

	/**
	 * @var bool
	 */
	protected static $block_public_access_allowed = false;

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
	protected $console_url_prefix_param = '';

	/**
	 * Is the provider able to use access keys?
	 *
	 * @return bool
	 */
	public static function use_access_keys_allowed() {
		return ! empty( static::$access_key_id_constants );
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

		if ( static::use_key_file() ) {
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
	 * Get the constant used to define the access key id.
	 *
	 * @return string|false Constant name if defined, otherwise false
	 */
	public static function access_key_id_constant() {
		return AS3CF_Utils::get_first_defined_constant( static::$access_key_id_constants );
	}

	/**
	 * Get the constant used to define the secret access key.
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
	 * If server roles allowed, returns first (preferred) constant that should be defined, otherwise blank.
	 *
	 * @return string
	 */
	public static function preferred_use_server_roles_constant() {
		if ( static::use_server_roles_allowed() ) {
			return static::$use_server_roles_constants[0];
		} else {
			return '';
		}
	}

	/**
	 * Allows the provider's client factory to use server roles instead of key/secret for credentials.
	 *
	 * @see http://docs.aws.amazon.com/aws-sdk-php/guide/latest/credentials.html#instance-profile-credentials
	 *
	 * @return bool
	 */
	public function use_server_roles() {
		if ( ! static::use_server_roles_allowed() ) {
			return false;
		}

		if ( static::use_server_roles_constant() ) {
			$constant = static::use_server_roles_constant();

			return $constant ? constant( $constant ) : false;
		}

		return $this->as3cf->get_core_setting( static::$use_server_roles_setting_name, false );
	}

	/**
	 * Get the constant used to enable the use of IAM roles.
	 *
	 * @return string|false Constant name if defined, otherwise false
	 */
	public static function use_server_roles_constant() {
		return AS3CF_Utils::get_first_defined_constant( static::$use_server_roles_constants );
	}

	/**
	 * Is the provider able to use a key file?
	 *
	 * @return bool
	 */
	public static function use_key_file_allowed() {
		return ! empty( static::$key_file_path_constants );
	}

	/**
	 * If key file allowed, returns first (preferred) constant that should be defined, otherwise blank.
	 *
	 * @return string
	 */
	public static function preferred_key_file_path_constant() {
		if ( static::use_key_file_allowed() ) {
			return static::$key_file_path_constants[0];
		} else {
			return '';
		}
	}

	/**
	 * Check if either key file path or key file set.
	 *
	 * @return bool
	 */
	public function use_key_file() {
		if ( ! static::use_key_file_allowed() ) {
			return false;
		}

		return $this->get_key_file_path() || $this->get_key_file();
	}

	/**
	 * Get the key file contents from settings.
	 *
	 * @return array|bool
	 */
	public function get_key_file() {
		$key_file = $this->as3cf->get_core_setting( static::$key_file_setting_name, false );

		return $key_file;
	}

	/**
	 * Get the key file path from a constant or the settings.
	 *
	 * Falls back to settings if constant is not defined.
	 *
	 * @return string|bool
	 */
	public function get_key_file_path() {
		if ( static::is_key_file_path_constant_defined() ) {
			$constant = static::key_file_path_constant();

			if ( $constant ) {
				return $this->validate_key_file_path( constant( $constant ) );
			} else {
				// Constant defined but value is not a non-empty string.
				return false;
			}
		}

		return $this->validate_key_file_path( $this->as3cf->get_core_setting( static::$key_file_path_setting_name, false ) );
	}

	/**
	 * Validate a key file path to ensure it exists, is readable, and contains JSON.
	 *
	 * @param string $key_file_path
	 *
	 * @return bool|string
	 */
	public function validate_key_file_path( $key_file_path ) {
		$notice_id = 'validate-key-file-path';
		$this->as3cf->notices->remove_notice_by_id( $notice_id );

		if ( empty( $key_file_path ) ) {
			return false;
		}

		if ( ! file_exists( $key_file_path ) ) {
			$this->as3cf->notices->add_notice( __( 'Given Key File Path is invalid or could not be accessed.', 'amazon-s3-and-cloudfront' ), array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media', 'custom_id' => $notice_id ) );

			return false;
		}

		try {
			$value = file_get_contents( $key_file_path );

			// An exception isn't always thrown, so check value instead.
			if ( empty( $value ) ) {
				$this->as3cf->notices->add_notice( __( 'Could not read Key File Path\'s contents.', 'amazon-s3-and-cloudfront' ), array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media', 'custom_id' => $notice_id ) );

				return false;
			}
		} catch ( Exception $e ) {
			$this->as3cf->notices->add_notice( __( 'Could not read Key File Path\'s contents.', 'amazon-s3-and-cloudfront' ), array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media', 'custom_id' => $notice_id ) );

			return false;
		}

		$value = json_decode( $value, true );

		if ( empty( $value ) ) {
			$this->as3cf->notices->add_notice( __( 'Given Key File Path does not contain valid JSON.', 'amazon-s3-and-cloudfront' ), array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media', 'custom_id' => $notice_id ) );

			return false;
		}

		// File exists and looks like JSON.
		return $key_file_path;
	}

	/**
	 * Check if key file path constant is defined, for speed, does not check validity of file path.
	 *
	 * @return bool
	 */
	public static function is_key_file_path_constant_defined() {
		$constant = static::key_file_path_constant();

		return $constant && constant( $constant );
	}

	/**
	 * Get the constant used to define the key file path.
	 *
	 * @return string|false Constant name if defined, otherwise false
	 */
	public static function key_file_path_constant() {
		return AS3CF_Utils::get_first_defined_constant( static::$key_file_path_constants );
	}

	/**
	 * Get default "canned" ACL string.
	 *
	 * @return string|null
	 */
	public function get_default_acl() {
		return apply_filters( 'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_default_acl', $this->get_public_acl() );
	}

	/**
	 * Does provider have functionality to block direct public access to bucket?
	 *
	 * @return bool
	 */
	public static function block_public_access_allowed() {
		return static::$block_public_access_allowed;
	}

	/**
	 * Is public access to the given bucket prohibited?
	 *
	 * @param string $bucket
	 *
	 * @return bool|null
	 */
	public function public_access_blocked( $bucket ) {
		if ( static::block_public_access_allowed() ) {
			return null; // Unknown until sub class overrides this function.
		} else {
			return false;
		}
	}

	/**
	 * Update the block public access setting for the given bucket.
	 *
	 * @param string $bucket
	 * @param bool   $block
	 *
	 * Note: Should be overridden and implemented as required.
	 */
	public function block_public_access( $bucket, $block ) {
		return;
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
		if ( is_wp_error( $region ) ) {
			return '';
		}
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
	 * Is given region key valid for provider?
	 *
	 * @param string $region
	 *
	 * @return bool
	 */
	public function is_region_valid( $region ) {
		return in_array( trim( $region ), array_keys( $this->get_regions() ) );
	}

	/**
	 * Instantiate a new service client for the provider.
	 *
	 * @param array $args Options for required region/endpoint
	 *
	 * @throws Exception
	 */
	private function _init_client( array $args ) {
		if ( $this->needs_access_keys() ) {
			throw new Exception( sprintf( __( 'You must first <a href="%s">set your access keys</a>.', 'amazon-s3-and-cloudfront' ), $this->as3cf->get_plugin_page_url() . '#settings' ) );
		}

		if ( is_null( $this->client ) ) {
			// There's no extra client authentication config required when using server roles.
			if ( ! $this->use_server_roles() ) {
				// Some providers can supply Key File contents or Key File Path.
				if ( static::use_key_file() ) {
					// Key File contents take precedence over Key File Path.
					if ( static::get_key_file() ) {
						$args = array_merge( array(
							'keyFile' => static::get_key_file(),
						), $args );
					} else {
						$args = array_merge( array(
							'keyFilePath' => static::get_key_file_path(),
						), $args );
					}
				} else {
					// Fall back is Access Keys.
					$args = array_merge( array(
						'credentials' => array(
							'key'    => $this->get_access_key_id(),
							'secret' => $this->get_secret_access_key(),
						),
					), $args );
				}
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
	 * @return Storage_Provider
	 * @throws Exception
	 */
	public function get_client( array $args, $force = false ) {
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
	 * @param array  $regions
	 * @param string $source_type
	 *
	 * @return array
	 */
	public static function get_keys_from_regions( array $regions, $source_type ) {
		$keys = array();

		foreach ( $regions as $region ) {
			try {
				/* @var $client Storage_Provider */
				$client      = $region['provider_client'];
				$region_keys = $client->list_keys( $region['locations'] );
			} catch ( Exception $e ) {
				AS3CF_Error::log( get_class( $e ) . ' exception caught when executing list_keys: ' . $e->getMessage() );
				continue;
			}

			if ( ! empty( $region_keys ) ) {
				foreach ( $region_keys as $attachment_id => $found_keys ) {
					$keys[ $attachment_id ] = AS3CF_Utils::validate_attachment_keys( $attachment_id, $found_keys, $source_type );
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
	 *
	 * @return string
	 */
	public function get_url_domain( $bucket, $region = '', $expires = null ) {
		$args = array(
			'delivery-provider'      => $this->as3cf->get_setting( 'delivery-provider' ),
			'enable-delivery-domain' => $this->as3cf->get_setting( 'enable-delivery-domain' ),
			'delivery-domain'        => $this->as3cf->get_setting( 'delivery-domain' ),
			'force-https'            => $this->as3cf->use_ssl( $this->as3cf->get_setting( 'force-https' ) ),
		);

		// Backwards Compat.
		$args['domain']     = $args['enable-delivery-domain'] && Amazon_S3_And_CloudFront::get_default_delivery_provider() !== $args['delivery-provider'] ? 'cloudfront' : 'path';
		$args['cloudfront'] = $args['delivery-domain'];

		$prefix = $this->url_prefix( $region, $expires );
		$domain = $this->get_domain();
		$domain = empty( $prefix ) ? $domain : $prefix . '.' . $domain;

		return apply_filters(
			'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_url_domain',
			$this->url_domain( $domain, $bucket, $region, $expires, $args ),
			$bucket,
			$region,
			$expires,
			$args
		);
	}

	/**
	 * Get the link to the bucket on the provider's console.
	 *
	 * @param string $bucket
	 * @param string $prefix
	 * @param string $region
	 *
	 * @return string
	 */
	public function get_console_url( $bucket = '', $prefix = '', $region = '' ) {
		if ( '' !== $prefix ) {
			$prefix = $this->get_console_url_prefix_param() . apply_filters( 'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_console_url_prefix_value', $prefix );
		}

		$suffix = apply_filters( 'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_console_url_suffix_param', $this->get_console_url_suffix_param( $bucket, $prefix, $region ) );

		return apply_filters( 'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_console_url', $this->console_url ) . $bucket . $prefix . $suffix;
	}

	/**
	 * Get the prefix param to append to the link to the bucket on the provider's console.
	 *
	 * @return string
	 */
	public function get_console_url_prefix_param() {
		return apply_filters( 'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_console_url_prefix_param', $this->console_url_prefix_param );
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
	abstract protected function init_client_args( array $args );

	/**
	 * Instantiate a new client for the provider's SDK.
	 *
	 * @param array $args
	 */
	abstract protected function init_client( array $args );

	/**
	 * Process the args before instantiating a new service specific client.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	abstract protected function init_service_client_args( array $args );

	/**
	 * Instantiate a new service specific client.
	 * Depending on SDK, may simply return client instantiated with `init_client`.
	 *
	 * @param array $args
	 *
	 * @return mixed
	 */
	abstract protected function init_service_client( array $args );

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
	abstract public function create_bucket( array $args );

	/**
	 * Check whether bucket exists.
	 *
	 * @param string $bucket
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
	abstract public function get_bucket_location( array $args );

	/**
	 * List buckets.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	abstract public function list_buckets( array $args = array() );

	/**
	 * Check whether key exists in bucket.
	 *
	 * @param string $bucket
	 * @param string $key
	 * @param array  $options
	 *
	 * @return bool
	 */
	abstract public function does_object_exist( $bucket, $key, array $options = array() );

	/**
	 * Get public "canned" ACL string.
	 *
	 * @return string
	 */
	abstract public function get_public_acl();

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
	abstract public function get_object( array $args );

	/**
	 * Get object's URL.
	 *
	 * @param string $bucket
	 * @param string $key
	 * @param int    $timestamp
	 * @param array  $args
	 *
	 * @return string
	 */
	abstract public function get_object_url( $bucket, $key, $timestamp, array $args = array() );

	/**
	 * List objects.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	abstract public function list_objects( array $args = array() );

	/**
	 * Update the ACL for an object.
	 *
	 * @param array $args
	 */
	abstract public function update_object_acl( array $args );

	/**
	 * Update the ACL for multiple objects.
	 *
	 * @param array $items
	 *
	 * @return array Failures with elements Key and Message
	 */
	abstract public function update_object_acls( array $items );

	/**
	 * Upload file to bucket.
	 *
	 * @param array $args
	 */
	abstract public function upload_object( array $args );

	/**
	 * Delete object from bucket.
	 *
	 * @param array $args
	 */
	abstract public function delete_object( array $args );

	/**
	 * Delete multiple objects from bucket.
	 *
	 * @param array $args
	 */
	abstract public function delete_objects( array $args );

	/**
	 * Returns arrays of found keys for given bucket and prefix locations, retaining given array's integer based index.
	 *
	 * @param array $locations Array with attachment ID as key and Bucket and Prefix in an associative array as values.
	 *
	 * @return array
	 */
	abstract public function list_keys( array $locations );

	/**
	 * Copies objects into current bucket from another bucket hosted with provider.
	 *
	 * @param array $items
	 *
	 * @return array Failures with elements Key and Message
	 */
	abstract public function copy_objects( array $items );

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
	 * @param string $domain Likely prefixed with region
	 * @param string $bucket
	 * @param string $region
	 * @param int    $expires
	 * @param array  $args   Allows you to specify custom URL settings
	 *
	 * @return string
	 */
	abstract protected function url_domain( $domain, $bucket, $region = '', $expires = null, $args = array() );

	/**
	 * Get the suffix param to append to the link to the bucket on the provider's console.
	 *
	 * @param string $bucket
	 * @param string $prefix
	 * @param string $region
	 *
	 * @return string
	 */
	abstract protected function get_console_url_suffix_param( $bucket = '', $prefix = '', $region = '' );
}
