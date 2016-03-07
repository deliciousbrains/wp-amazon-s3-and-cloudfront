<?php
/**
 * Plugin Utilities
 *
 * @package     amazon-s3-and-cloudfront
 * @subpackage  Classes/Utils
 * @copyright   Copyright (c) 2015, Delicious Brains
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AS3CF_Utils' ) ) {

	/**
	 * AS3CF_Utils Class
	 *
	 * This class contains utility functions that need to be available
	 * across the Pro plugin codebase
	 *
	 */
	class AS3CF_Utils {

		/**
		 * Checks if another version of WP Offload S3 (Lite) is active and deactivates it.
		 * To be hooked on `activated_plugin` so other plugin is deactivated when current plugin is activated.
		 *
		 * @param string $plugin
		 *
		 * @return bool
		 */
		public static function deactivate_other_instances( $plugin ) {
			if ( ! in_array( basename( $plugin ), array( 'amazon-s3-and-cloudfront-pro.php', 'wordpress-s3.php' ) ) ) {
				return false;
			}

			$plugin_to_deactivate  = 'wordpress-s3.php';
			$deactivated_notice_id = '1';
			if ( basename( $plugin ) === $plugin_to_deactivate ) {
				$plugin_to_deactivate  = 'amazon-s3-and-cloudfront-pro.php';
				$deactivated_notice_id = '2';
			}

			if ( is_multisite() ) {
				$active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
				$active_plugins = array_keys( $active_plugins );
			} else {
				$active_plugins = (array) get_option( 'active_plugins', array() );
			}

			foreach ( $active_plugins as $basename ) {
				if ( false !== strpos( $basename, $plugin_to_deactivate ) ) {
					set_transient( 'as3cf_deactivated_notice_id', $deactivated_notice_id, HOUR_IN_SECONDS );
					deactivate_plugins( $basename );

					return true;
				}
			}

			return false;
		}
	}
}