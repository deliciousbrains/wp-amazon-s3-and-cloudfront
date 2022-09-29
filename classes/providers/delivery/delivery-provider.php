<?php

namespace DeliciousBrains\WP_Offload_Media\Providers\Delivery;

use AS3CF_Utils;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Providers\Provider;
use Exception;

abstract class Delivery_Provider extends Provider {

	/**
	 * @var string
	 */
	protected static $provider_service_name_setting_name = 'delivery-provider-service-name';

	/**
	 * @var string
	 */
	protected $console_url_prefix_param = '/';

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
	 * A description for the Rewrite Media URLs setting.
	 *
	 * @return string
	 */
	public static function get_rewrite_media_urls_desc(): string {
		global $as3cf;

		$mesg = sprintf(
			_x( 'Serves offloaded media files by rewriting local URLs so that they point to %s.', 'Setting description', 'amazon-s3-and-cloudfront' ),
			static::get_provider_service_name()
		);
		$mesg .= ' ' . $as3cf::settings_more_info_link( 'serve-from-s3', 'How URL rewriting works' );

		return $mesg;
	}

	/**
	 * Does the delivery provider allow for setting a custom delivery domain?
	 *
	 * @return bool
	 */
	public static function delivery_domain_allowed() {
		return static::$delivery_domain_allowed;
	}

	/**
	 * A description for the delivery domain settings.
	 *
	 * @return string
	 */
	public static function get_delivery_domain_desc(): string {
		return sprintf(
			__( 'Serves media from a custom domain that has been pointed to %1$s. <a href="%2$s" target="_blank">How to set a custom domain name</a>', 'amazon-s3-and-cloudfront' ),
			static::get_provider_service_name(),
			static::get_provider_service_quick_start_url() . '#create-cname'
		);
	}

	/**
	 * Returns array of supported storage provider key names, empty array means all supported.
	 *
	 * @return array
	 */
	public static function get_supported_storage_providers() {
		return static::$supported_storage_providers;
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
	 * Title used in various places for enabling Signed URLs.
	 *
	 * @return string
	 */
	public static function signed_urls_option_name() {
		return __( 'Serve Private Media from Delivery Provider', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Description used in various places for enabling Signed URLs.
	 *
	 * @return string
	 */
	public static function signed_urls_option_description() {
		return '';
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
		return __( 'Private Bucket Path', 'amazon-s3-and-cloudfront' );
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
		} catch ( Exception $e ) {
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
	 * @throws Exception
	 */
	public function get_signed_url( Item $as3cf_item, $path, $domain, $scheme, $timestamp, $headers = array() ) {
		/**
		 * This default implementation defers to the storage provider's signed URLs.
		 * Therefore, we need to use a storage provider client instance for the item's region.
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
	 * A short description of whether delivery is fast (distributed) or not.
	 *
	 * @return string
	 */
	public static function edge_server_support_desc() {
		return __( 'Fast', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * A short description of whether signed URLs for private media is supported or not.
	 *
	 * @return string
	 */
	public static function signed_urls_support_desc() {
		return __( 'No Private Media', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Description for when Block All Public Access is enabled and Delivery Provider does not support it.
	 *
	 * @return string
	 */
	public static function get_block_public_access_enabled_unsupported_desc() {
		return sprintf(
			__( 'You need to disable Block All Public Access so that %1$s can access your bucket for delivery.', 'amazon-s3-and-cloudfront' ),
			static::get_provider_name()
		);
	}

	/**
	 * Prompt text to confirm that everything is in place to enable Block All Public Access without issues for Delivery Provider.
	 *
	 * @return string
	 */
	public static function get_block_public_access_confirm_setup_prompt() {
		return '';
	}

	/**
	 * Description for when Object Ownership is enforced and Delivery Provider does not support it.
	 *
	 * @return string
	 */
	public static function get_object_ownership_enforced_unsupported_desc(): string {
		global $as3cf;

		$object_ownership_doc = $as3cf::dbrains_url(
			'/wp-offload-media/doc/amazon-s3-bucket-object-ownership/',
			array( 'utm_campaign' => 'support+docs', 'utm_content' => 'change+bucket+access' )
		);

		return sprintf(
			__( 'You need to edit the bucket\'s Object Ownership setting and <a href="%1$s">enable ACLs</a> so that %2$s can access your bucket for delivery.', 'amazon-s3-and-cloudfront' ),
			$object_ownership_doc,
			static::get_provider_name()
		);
	}

	/**
	 * Prompt text to confirm that everything is in place to enforce Object Ownership without issues for Delivery Provider.
	 *
	 * @return string
	 */
	public static function get_object_ownership_confirm_setup_prompt(): string {
		// Using the same text as for the Block Public Access prompt.
		return static::get_block_public_access_confirm_setup_prompt();
	}

	/**
	 * Get the link to the provider's console.
	 *
	 * @param string $bucket
	 * @param string $prefix
	 * @param string $region
	 *
	 * @return string
	 *
	 * NOTE: By default delivery providers append the suffix to the base console URL only.
	 *       The usual bucket, prefix and region params are still processed and the suffix
	 *       may end up being either a path, params or both that get deeper into the console.
	 */
	public function get_console_url( string $bucket = '', string $prefix = '', string $region = '' ): string {
		if ( '' !== $prefix ) {
			$prefix = $this->get_console_url_prefix_param() . apply_filters( 'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_console_url_prefix_value', $prefix );
		}

		$suffix = apply_filters( 'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_console_url_suffix_param', $this->get_console_url_suffix_param( $bucket, $prefix, $region ) );

		return apply_filters( 'as3cf_' . static::$provider_key_name . '_' . static::$service_key_name . '_console_url', $this->console_url ) . $suffix;
	}

	/**
	 * Get the suffix param to append to the link to the provider's console.
	 *
	 * @param string $bucket
	 * @param string $prefix
	 * @param string $region
	 *
	 * @return string
	 */
	protected function get_console_url_suffix_param( string $bucket = '', string $prefix = '', string $region = '' ): string {
		return '';
	}
}
