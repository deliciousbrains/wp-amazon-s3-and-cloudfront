<?php

namespace DeliciousBrains\WP_Offload_Media\Providers\Delivery;

class DigitalOcean_Spaces_CDN extends Delivery_Provider {

	/**
	 * Which storage providers does the delivery provider support, empty means all.
	 *
	 * @var array
	 */
	protected static $supported_storage_providers = array(
		'do',
	);

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
	protected static $service_name = 'Spaces CDN';

	/**
	 * @var string
	 */
	protected static $service_short_name = 'Spaces CDN';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $service_key_name = 'spaces-cdn';

	/**
	 * Optional override of "Provider Name" + "Service Name" for friendly name for service.
	 *
	 * @var string
	 */
	protected static $provider_service_name = 'DigitalOcean Spaces CDN';

	/**
	 * The slug for the service's quick start guide doc.
	 *
	 * @var string
	 */
	protected static $provider_service_quick_start_slug = 'digitalocean-spaces-cdn-setup';

	/**
	 * @var string
	 */
	protected $default_domain = 'cdn.digitaloceanspaces.com';

	/**
	 * @var string
	 */
	protected $console_url = 'https://cloud.digitalocean.com/spaces/';

	/**
	 * @var string
	 */
	protected $console_url_prefix_param = '?path=';

	/**
	 * @inheritDoc
	 */
	public function features_description() {
		return __( 'Fast, No Private Media', 'amazon-s3-and-cloudfront' );
	}
}
