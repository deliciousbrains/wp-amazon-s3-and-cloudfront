<?php

namespace DeliciousBrains\WP_Offload_Media\Providers\Delivery;

class AWS_CloudFront extends Delivery_Provider {

	/**
	 * Which storage providers does the delivery provider support, empty means all.
	 *
	 * @var array
	 */
	protected static $supported_storage_providers = array(
		'aws',
	);

	/**
	 * @var string
	 */
	protected static $provider_name = 'Amazon Web Services';

	/**
	 * @var string
	 */
	protected static $provider_short_name = 'AWS';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $provider_key_name = 'aws';

	/**
	 * @var string
	 */
	protected static $service_name = 'CloudFront';

	/**
	 * @var string
	 */
	protected static $service_short_name = 'CloudFront';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $service_key_name = 'cloudfront';

	/**
	 * Optional override of "Provider Name" + "Service Name" for friendly name for service.
	 *
	 * @var string
	 */
	protected static $provider_service_name = 'Amazon CloudFront';

	/**
	 * The slug for the service's quick start guide doc.
	 *
	 * @var string
	 */
	protected static $provider_service_quick_start_slug = 'amazon-cloudfront-quick-start-guide';

	/**
	 * @var string
	 */
	protected $default_domain = 'cloudfront.net';

	/**
	 * @var string
	 */
	protected $console_url = 'https://console.aws.amazon.com/cloudfront/home';

	/**
	 * AWS_CloudFront constructor.
	 *
	 * @param \AS3CF_Plugin_Base $as3cf
	 */
	public function __construct( \AS3CF_Plugin_Base $as3cf ) {
		parent::__construct( $as3cf );

		// Autoloader.
		require_once $as3cf->get_plugin_sdks_dir_path() . '/Aws3/aws-autoloader.php';
	}

	/**
	 * A short description of what features the delivery provider enables.
	 *
	 * @return string
	 */
	public function features_description() {
		return sprintf(
			__( 'Fast, Private Media Supported with <a href="%s" target="_blank">upgrade</a>', 'amazon-s3-and-cloudfront' ),
			$this->as3cf->dbrains_url( '/wp-offload-media/upgrade/', array(
				'utm_campaign' => 'WP+Offload+S3',
			) )
		);
	}
}
