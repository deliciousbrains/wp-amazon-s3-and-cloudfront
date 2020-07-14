<?php

namespace DeliciousBrains\WP_Offload_Media\Providers;

use AS3CF_Plugin_Base;
use DeliciousBrains\WP_Offload_Media\Items\Item;

abstract class Provider {

	/**
	 * @var \Amazon_S3_And_CloudFront|\Amazon_S3_And_CloudFront_Pro
	 */
	protected $as3cf;

	/**
	 * Can the displayed provider service name be overridden by the user?
	 *
	 * @var bool
	 */
	protected static $provider_service_name_override_allowed = false;

	/**
	 * @var string
	 */
	protected static $provider_service_name_setting_name = 'provider-service-name';

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
	 * The slug for the service's quick start guide doc.
	 *
	 * @var string
	 */
	protected static $provider_service_quick_start_slug = 'quick-start-guide';

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
	 * Provider constructor.
	 *
	 * @param AS3CF_Plugin_Base $as3cf
	 */
	public function __construct( AS3CF_Plugin_Base $as3cf ) {
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
	 * Returns the full name for the service.
	 *
	 * @return string
	 */
	public static function get_service_name() {
		return static::$service_name;
	}

	/**
	 * Returns the key friendly name for the service.
	 *
	 * @return string
	 */
	public static function get_service_key_name() {
		return static::$service_key_name;
	}

	/**
	 * Returns the full name for the provider and service for display.
	 *
	 * @param bool $override_allowed Use override if available? Defaults to true.
	 *
	 * @return string
	 */
	public static function get_provider_service_name( $override_allowed = true ) {
		if ( ! empty( static::$provider_service_name ) ) {
			$result = static::$provider_service_name;
		} else {
			$result = static::$provider_name . ' ' . static::$service_name;
		}

		if ( false === $override_allowed || false === static::provider_service_name_override_allowed() ) {
			return $result;
		} else {
			/** @var \Amazon_S3_And_CloudFront $as3cf */
			global $as3cf;

			$override = stripslashes( $as3cf->get_setting( static::$provider_service_name_setting_name ) );

			if ( empty( $override ) ) {
				return $result;
			} else {
				return $override;
			}
		}
	}

	/**
	 * Can the displayed provider service name be overridden by the user?
	 *
	 * @return bool
	 */
	public static function provider_service_name_override_allowed() {
		return static::$provider_service_name_override_allowed;
	}

	/**
	 * Returns the slug for the service's quick start guide doc.
	 *
	 * @return string
	 */
	public static function get_provider_service_quick_start_slug() {
		return static::$provider_service_quick_start_slug;
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
}
