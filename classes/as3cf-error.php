<?php
/**
 * Error
 *
 * @package     amazon-s3-and-cloudfront
 * @subpackage  Classes/Error
 * @copyright   Copyright (c) 2015, Delicious Brains
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.9.12
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AS3CF_Error Class
 *
 * This class handles error logging
 *
 * @since 0.9.12
 */
class AS3CF_Error {

	/**
	 * Wrapper for error logging a message with plugin prefix
	 *
	 * phpcs:disable WordPress.PHP.DevelopmentFunctions
	 *
	 * @param mixed  $message
	 * @param string $plugin_prefix
	 */
	public static function log( $message, $plugin_prefix = '' ) {
		$prefix = 'AS3CF';
		if ( '' !== $plugin_prefix ) {
			$prefix .= '_' . $plugin_prefix;
		}

		$prefix .= ': ';

		$message = apply_filters( 'as3cf_error_log_message', $message, $prefix );
		if ( empty( $message ) ) {
			return;
		}

		if ( is_array( $message ) || is_object( $message ) ) {
			error_log( $prefix . print_r( $message, true ) );
		} else {
			error_log( $prefix . $message );
		}
	}
}
