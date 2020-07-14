<?php

namespace DeliciousBrains\WP_Offload_Media\Providers\Delivery;

class Another_CDN extends Delivery_Provider {

	/**
	 * @var string
	 */
	protected static $provider_name = 'Another';

	/**
	 * @var string
	 */
	protected static $provider_short_name = 'Another';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $provider_key_name = 'another';

	/**
	 * @var string
	 */
	protected static $service_name = 'CDN';

	/**
	 * @var string
	 */
	protected static $service_short_name = 'CDN';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $service_key_name = 'cdn';

	/**
	 * A short description of what features the delivery provider enables.
	 *
	 * @return string
	 */
	public function features_description() {
		return __( 'Fast, No Private Media', 'amazon-s3-and-cloudfront' );
	}
}
