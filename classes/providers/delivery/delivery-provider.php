<?php

namespace DeliciousBrains\WP_Offload_Media\Providers\Delivery;

use AS3CF_Utils;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Providers\Provider;

abstract class Delivery_Provider extends Provider {

	/**
	 * @var string
	 */
	protected static $provider_service_name_setting_name = 'delivery-provider-service-name';

	/**
	 * @var bool
	 */
	protected static $delivery_domain_allowed = true;

	/**
	 * Which storage providers does the delivery provider support, empty means all.
	 *
	 * @var array
	 */
	protected static $supported_storage_providers = array();

	/**
	 * @var string
	 */
	protected static $signed_urls_key_id_setting_name = 'signed-urls-key-id';

	/**
	 * @var string
	 */
	protected static $signed_urls_key_file_path_setting_name = 'signed-urls-key-file-path';

	/**
	 * @var string
	 */
	protected static $signed_urls_object_prefix_setting_name = 'signed-urls-object-prefix';

	/**
	 * If left empty, private signing key file not allowed.
	 *
	 * @var array
	 */
	protected static $signed_urls_key_id_constants = array();

	/**
	 * If left empty, private signing key file not allowed.
	 *
	 * @var array
	 */
	protected static $signed_urls_key_file_path_constants = array();

	/**
	 * If left empty, private signing key file not allowed.
	 *
	 * @var array
	 */
	protected static $signed_urls_object_prefix_constants = array();

	/**
	 * Does the delivery provider allow for setting a custom delivery domain?
	 *
	 * @return bool
	 */
	public static function delivery_domain_allowed() {
		return static::$delivery_domain_allowed;
	}

	/**
	 * Does provider support given storage provider?
	 *
	 * @param string $storage_provider_key
	 *
	 * @return bool
	 */
	public function supports_storage( $storage_provider_key ) {
		if ( empty( static::$supported_storage_providers ) || in_array( $storage_provider_key, static::$supported_storage_providers ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Title used in various places for the Signed URLs Key ID.
	 *
	 * @return string
	 */
	public static function signed_urls_key_id_name() {
		return __( 'Signing Key ID', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Description used in various places for the Signed URLs Key ID.
	 *
	 * @return string
	 */
	public static function signed_urls_key_id_description() {
		return '';
	}

	/**
	 * Title used in various places for the Signed URLs Key File Path.
	 *
	 * @return string
	 */
	public static function signed_urls_key_file_path_name() {
		return __( 'Signing Key File Path', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Description used in various places for the Signed URLs Key File Path.
	 *
	 * @return string
	 */
	public static function signed_urls_key_file_path_description() {
		return '';
	}

	/**
	 * Title used in various places for the Signed URLs Private Object Prefix.
	 *
	 * @return string
	 */
	public static function signed_urls_object_prefix_name() {
		return __( 'Private Path', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Description used in various places for the Signed URLs Private Object Prefix.
	 *
	 * @return string
	 */
	public static function signed_urls_object_prefix_description() {
		return '';
	}

	/**
	 * Is the provider able to use a private signing key file?
	 *
	 * @return bool
	 */
	public static function use_signed_urls_key_file_allowed() {
		return ! empty( static::$signed_urls_key_id_constants ) && ! empty( static::$signed_urls_key_file_path_constants ) && ! empty( static::$signed_urls_object_prefix_constants );
	}

	/**
	 * If private signing key file allowed, returns first (preferred) key id constant that should be defined, otherwise blank.
	 *
	 * @return string
	 */
	public static function preferred_signed_urls_key_id_constant() {
		if ( static::use_signed_urls_key_file_allowed() ) {
			return static::$signed_urls_key_id_constants[0];
		} else {
			return '';
		}
	}

	/**
	 * If private signing key file allowed, returns first (preferred) key file path constant that should be defined, otherwise blank.
	 *
	 * @return string
	 */
	public static function preferred_signed_urls_key_file_path_constant() {
		if ( static::use_signed_urls_key_file_allowed() ) {
			return static::$signed_urls_key_file_path_constants[0];
		} else {
			return '';
		}
	}

	/**
	 * If private signing key file allowed, returns first (preferred) object prefix constant that should be defined, otherwise blank.
	 *
	 * @return string
	 */
	public static function preferred_signed_urls_object_prefix_constant() {
		if ( static::use_signed_urls_key_file_allowed() ) {
			return static::$signed_urls_object_prefix_constants[0];
		} else {
			return '';
		}
	}

	/**
	 * Check if private signing key id and file path set.
	 *
	 * @return bool
	 */
	public function use_signed_urls_key_file() {
		if ( ! static::use_signed_urls_key_file_allowed() ) {
			return false;
		}

		return $this->get_signed_urls_key_id() && $this->get_signed_urls_key_file_path() && $this->get_signed_urls_object_prefix();
	}

	/**
	 * Get the private signing key id from a constant or the settings.
	 *
	 * Falls back to settings if constant is not defined.
	 *
	 * @return string
	 */
	public function get_signed_urls_key_id() {
		if ( static::is_signed_urls_key_id_constant_defined() ) {
			$constant = static::signed_urls_key_id_constant();

			return $constant ? constant( $constant ) : '';
		}

		return $this->as3cf->get_core_setting( static::$signed_urls_key_id_setting_name );
	}

	/**
	 * Get the private signing key file path from a constant or the settings.
	 *
	 * Falls back to settings if constant is not defined.
	 *
	 * @return string|bool
	 */
	public function get_signed_urls_key_file_path() {
		if ( static::is_signed_urls_key_file_path_constant_defined() ) {
			$constant = static::signed_urls_key_file_path_constant();

			if ( $constant ) {
				return $this->validate_signed_urls_key_file_path( constant( $constant ) );
			} else {
				// Constant defined but value is not a non-empty string.
				return false;
			}
		}

		return $this->validate_signed_urls_key_file_path( $this->as3cf->get_core_setting( static::$signed_urls_key_file_path_setting_name, false ) );
	}

	/**
	 * Get the private signing object prefix from a constant or the settings.
	 *
	 * Falls back to settings if constant is not defined.
	 *
	 * @return string
	 */
	public function get_signed_urls_object_prefix() {
		if ( static::is_signed_urls_object_prefix_constant_defined() ) {
			$constant = static::signed_urls_object_prefix_constant();

			return $constant ? constant( $constant ) : '';
		}

		return $this->as3cf->get_core_setting( static::$signed_urls_object_prefix_setting_name );
	}

	/**
	 * Validate a private signing key file path to ensure it exists, is readable, and contains something.
	 *
	 * @param string $signed_urls_key_file_path
	 *
	 * @return bool|string
	 */
	public function validate_signed_urls_key_file_path( $signed_urls_key_file_path ) {
		$notice_id = 'validate-signed-urls-key-file-path';
		$this->as3cf->notices->remove_notice_by_id( $notice_id );

		if ( empty( $signed_urls_key_file_path ) ) {
			return false;
		}

		if ( ! file_exists( $signed_urls_key_file_path ) ) {
			$this->as3cf->notices->add_notice( __( 'Given Signing Key File Path is invalid or could not be accessed.', 'amazon-s3-and-cloudfront' ), array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media', 'custom_id' => $notice_id ) );

			return false;
		}

		try {
			$value = file_get_contents( $signed_urls_key_file_path );

			// An exception isn't always thrown, so check value instead.
			if ( empty( $value ) ) {
				$this->as3cf->notices->add_notice( __( 'Could not read Signing Key File Path\'s contents.', 'amazon-s3-and-cloudfront' ), array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media', 'custom_id' => $notice_id ) );

				return false;
			}
		} catch ( \Exception $e ) {
			$this->as3cf->notices->add_notice( __( 'Could not read Signing Key File Path\'s contents.', 'amazon-s3-and-cloudfront' ), array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media', 'custom_id' => $notice_id ) );

			return false;
		}

		// File exists and has contents.
		return $signed_urls_key_file_path;
	}

	/**
	 * Check if private signing key id constant is defined.
	 *
	 * @return bool
	 */
	public static function is_signed_urls_key_id_constant_defined() {
		$constant = static::signed_urls_key_id_constant();

		return $constant && constant( $constant );
	}

	/**
	 * Check if private signing key file path constant is defined, for speed, does not check validity of file path.
	 *
	 * @return bool
	 */
	public static function is_signed_urls_key_file_path_constant_defined() {
		$constant = static::signed_urls_key_file_path_constant();

		return $constant && constant( $constant );
	}

	/**
	 * Check if private signing object prefix constant is defined.
	 *
	 * @return bool
	 */
	public static function is_signed_urls_object_prefix_constant_defined() {
		$constant = static::signed_urls_object_prefix_constant();

		return $constant && constant( $constant );
	}

	/**
	 * Get the constant used to define the private signing key id.
	 *
	 * @return string|false Constant name if defined, otherwise false
	 */
	public static function signed_urls_key_id_constant() {
		return AS3CF_Utils::get_first_defined_constant( static::$signed_urls_key_id_constants );
	}

	/**
	 * Get the constant used to define the private signing key file path.
	 *
	 * @return string|false Constant name if defined, otherwise false
	 */
	public static function signed_urls_key_file_path_constant() {
		return AS3CF_Utils::get_first_defined_constant( static::$signed_urls_key_file_path_constants );
	}

	/**
	 * Get the constant used to define the private signing object prefix.
	 *
	 * @return string|false Constant name if defined, otherwise false
	 */
	public static function signed_urls_object_prefix_constant() {
		return AS3CF_Utils::get_first_defined_constant( static::$signed_urls_object_prefix_constants );
	}

	/**
	 * Get a site specific file path example for a signed URL key file.
	 *
	 * @return string
	 */
	public static function signed_urls_key_file_path_placeholder() {
		$filename = 'pk-12345678ABCDE.pem';

		return dirname( ABSPATH ) . DIRECTORY_SEPARATOR . $filename;
	}

	/**
	 * Return a fully formed and potentially expiring signed URL for the given Item.
	 *
	 * @param Item       $as3cf_item
	 * @param string     $path    The required bucket path, may differ from Item's path if image subsize etc.
	 * @param string     $domain  The domain to use for the URL if at all possible.
	 * @param string     $scheme  The scheme to be used if possible.
	 * @param array|null $headers Optional array of headers to be passed along to underlying requests.
	 *
	 * @return string
	 */
	public function get_url( Item $as3cf_item, $path, $domain, $scheme, $headers = array() ) {
		$item_path = $this->as3cf->maybe_update_delivery_path( $path, $domain );
		$item_path = AS3CF_Utils::encode_filename_in_path( $item_path );

		return $scheme . '://' . $domain . '/' . $item_path;
	}

	/**
	 * Return a fully formed and expiring signed URL for the given Item.
	 *
	 * @param Item       $as3cf_item
	 * @param string     $path      The required bucket path, may differ from Item's path if image subsize etc.
	 * @param string     $domain    The domain to use for the URL if at all possible.
	 * @param string     $scheme    The scheme to be used if possible.
	 * @param int        $timestamp URL expires at the given time.
	 * @param array|null $headers   Optional array of headers to be passed along to underlying requests.
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function get_signed_url( Item $as3cf_item, $path, $domain, $scheme, $timestamp, $headers = array() ) {
		/**
		 * This default implementation defers to the storage provider's signed URLs.
		 * Therefore we need to use a storage provider client instance for the item's region.
		 */
		if ( ! empty( $as3cf_item->region() ) && ( $this->as3cf->get_storage_provider()->region_required() || $this->as3cf->get_storage_provider()->get_default_region() !== $as3cf_item->region() ) ) {
			$region = $this->as3cf->get_storage_provider()->sanitize_region( $as3cf_item->region() );
		} else {
			$region = '';
		}

		// Storage Provider may support signing custom domain, e.g. GCP.
		if ( $this->as3cf->get_storage_provider()->get_domain() !== $domain ) {
			$headers['BaseURL'] = $scheme . '://' . $domain;
		}

		return $this->as3cf->get_provider_client( $region )->get_object_url( $as3cf_item->bucket(), $path, $timestamp, $headers );
	}

	/**
	 * A short description of what features the delivery provider enables.
	 *
	 * TODO: Use properties to specify features list, allowing some to be derived from storage provider (e.g. GCP can be fast by default).
	 *
	 * @return string
	 */
	abstract public function features_description();
}
