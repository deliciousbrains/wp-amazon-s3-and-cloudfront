<?php

namespace DeliciousBrains\WP_Offload_Media\Providers\Delivery;

use DeliciousBrains\WP_Offload_Media\Items\Item;

class Storage extends Delivery_Provider {

	/**
	 * @var bool
	 */
	protected static $delivery_domain_allowed = false;

	/**
	 * @var string
	 */
	protected static $provider_name = 'Storage Provider';

	/**
	 * @var string
	 */
	protected static $provider_short_name = 'Storage';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $provider_key_name = 'storage';

	/**
	 * @var string
	 */
	protected static $service_name = 'Bucket';

	/**
	 * @var string
	 */
	protected static $service_short_name = 'Bucket';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $service_key_name = 'bucket';

	/**
	 * Returns the full name for the provider.
	 *
	 * @return string
	 */
	public static function get_provider_name() {
		/** @var \Amazon_S3_And_CloudFront $as3cf */
		global $as3cf;

		return $as3cf->get_storage_provider()->get_provider_name();
	}

	/**
	 * Returns the full name for the service.
	 *
	 * @return string
	 */
	public static function get_service_name() {
		/** @var \Amazon_S3_And_CloudFront $as3cf */
		global $as3cf;

		return $as3cf->get_storage_provider()->get_service_name();
	}

	/**
	 * Returns the full name for the provider and service for display.
	 *
	 * @param bool $override_allowed Not used.
	 *
	 * @return string
	 */
	public static function get_provider_service_name( $override_allowed = true ) {
		/** @var \Amazon_S3_And_CloudFront $as3cf */
		global $as3cf;

		return $as3cf->get_storage_provider()->get_provider_service_name();
	}

	/**
	 * A short description of what features the delivery provider enables.
	 *
	 * @return string
	 */
	public function features_description() {
		return __( 'Slow, Private Media Supported', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * @inheritDoc
	 */
	public function get_url( Item $as3cf_item, $path, $domain, $scheme, $headers = array() ) {
		$domain = $this->as3cf->get_storage_provider()->get_url_domain( $as3cf_item->bucket(), $as3cf_item->region() );

		return parent::get_url( $as3cf_item, $path, $domain, $scheme, $headers );
	}
}
