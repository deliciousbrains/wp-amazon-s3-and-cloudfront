<?php

if ( ! function_exists( 'as3cf_check_for_upgrades' ) ) {
	/**
	 * Initialize the checking for plugin updates.
	 */
	function as3cf_check_for_upgrades() {
		$properties = array(
			'plugin_slug'     => 'amazon-s3-and-cloudfront',
			'plugin_basename' => plugin_basename( AS3CF_FILE ),
		);

		require_once AS3CF_PATH . 'ext/as3cf-plugin-updater.php';
		new DeliciousBrains\WP_Offload_Media\AS3CF_Plugin_Updater( $properties );
	}

	add_action( 'admin_init', 'as3cf_check_for_upgrades' );
}
