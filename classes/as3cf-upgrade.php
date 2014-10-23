<?php
/**
 * Upgrade
 *
 * @package     amazon-s3-and-cloudfront
 * @subpackage  Classes/Upgrade
 * @copyright   Copyright (c) 2014, Delicious Brains
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.6.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AS3CF_Upgrade Class
 *
 * This class handles data updates and other migrations after a plugin update
 *
 * @since 0.6.2
 */
class AS3CF_Upgrade {

	private $as3cf;
	private $cron_hook = 'as3cf_schedule_cron_job';
	private $ten_minutes;

	/**
	 * Start it up
	 *
	 * @param $as3cf - the instance of the as3cf class
	 */
	function __construct( $as3cf ) {
		$this->as3cf = $as3cf;

		$this->ten_minutes = $this->sanitize_integer( 'as3cf_upgrade_ten_minutes', 10 ); // filtered for testing

		add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );
		add_action( $this->cron_hook, array( $this, 'process_cron_job' ) );

		$this->plugin_upgrades();
	}

	/**
	 * Process any migrations or data changes needed after a plugin update
	 */
	function plugin_upgrades() {
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		$current_version = $this->as3cf->get_setting( 'version' );
		if ( version_compare( $this->as3cf->get_plugin_version(), $current_version, '==' ) ) {
			return;
		}

		if ( version_compare( $current_version, '0.6.2', '<' ) ) {
			// update s3 meta with bucket region where missing
			$this->update_meta_with_region();
		}

		$this->as3cf->set_setting( 'version', $this->as3cf->get_plugin_version() );
		$this->as3cf->save_settings();
	}

	/**
	 * Add custom cron interval schedules
	 *
	 * @param array $schedules
	 *
	 * @return array
	 */
	function cron_schedules( $schedules ) {
		// Adds every 10 minutes to the existing schedules.
		$schedules['as3cf_minutes_10'] = array(
			'interval' => $this->ten_minutes * 60,
			'display'  => __( 'Every ' . $this->ten_minutes . ' Minutes', 'as3cf' )
		);

		return $schedules;
	}

	/**
	 * Helper to compare the version stored in a setting
	 *
	 * @param $setting         - name of the setting key
	 * @param $compare_version - version to compare against
	 * @param $operator
	 *
	 * @return bool
	 */
	function check_setting_version( $setting, $compare_version, $operator = '<' ) {
		$setting_version = $this->as3cf->get_setting( $setting );

		return version_compare( $setting_version, $compare_version, $operator );
	}

	/**
	 * Wrapper for scheduling a cron for a specific job
	 *
	 * @param        $job      - callback
	 * @param string $schedule - schedule interval
	 */
	function schedule_event( $job, $schedule = 'hourly' ) {
		if ( ! wp_next_scheduled( $this->cron_hook ) ) {
			wp_schedule_event( current_time( 'timestamp' ), $schedule, $this->cron_hook, array( 'job' => $job ) );
		}
	}

	/**
	 * Wrapper for clearing scheduled events for a specific cron job
	 *
	 * @param $job - callback
	 */
	function clear_scheduled_event( $job ) {
		$timestamp = wp_next_scheduled( $this->cron_hook, array( 'job' => $job ) );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, $this->cron_hook, array( 'job' => $job ) );
		}
	}

	/**
	 * Main cron job to run various jobs, hooked into the cron action
	 *
	 * @param       $job  - method callback
	 * @param array $args - additional args passed to the callback
	 */
	function process_cron_job( $job, $args = array() ) {
		if ( method_exists( 'AS3CF_Upgrade', $job ) ) {
			call_user_func( array( $this, $job ), $args );
		}
	}

	/**
	 * Wrapper for the cron job to update the region of the bucket in s3 metadata
	 */
	function update_meta_with_region() {
		// only run update of region if post_meta_version is less than 1
		if ( ! $this->check_setting_version( 'post_meta_version', 1 ) ) {
			return;
		}
		// spawn the cron job to batch update s3 meta with bucket region
		$this->schedule_event( 'cron_update_meta_with_region', 'as3cf_minutes_10' );
	}

	/**
	 * Cron jon to update the region of the bucket in s3 metadata
	 */
	function cron_update_meta_with_region() {
		// check if the cron should even be running
		if ( ! $this->check_setting_version( 'post_meta_version', 1 ) ) {
			// remove schedule
			$this->clear_scheduled_event( 'cron_update_meta_with_region' );

			return;
		}

		global $wpdb;
		$prefix = $wpdb->prefix;

		// set the batch size limit for the query
		$limit = $this->sanitize_integer( 'as3cf_update_meta_with_region_batch_size', 500 );

		// query all attachments with amazons3_info without region key in meta
		$table_prefixes[1] = $prefix;
		if ( is_multisite() ) {
			$blog_ids = $this->as3cf->get_blog_ids();
			foreach ( $blog_ids as $blog_id ) {
				$table_prefixes[ $blog_id ] = $prefix . $blog_id . '_';
			}
		}

		$all_attachments = array();
		$all_count = 0;

		foreach ( $table_prefixes as $blog_id => $table_prefix ) {
			$attachments = $this->get_attachments_without_region( $table_prefix, $limit );
			$count = count( $attachments );
			$all_count += $count;
			$all_attachments[ $blog_id ] = $attachments;

			if ( $all_count >= $limit ) {
				break;
			}

			$limit = $limit - $count;
		}

		if ( 0 == $all_count ) {
			// update post_meta_version
			$this->as3cf->set_setting( 'post_meta_version', 1 );
			$this->as3cf->save_settings();
			// remove schedule
			$this->clear_scheduled_event( 'cron_update_meta_with_region' );

			return;
		}

		// only process the loop for a certain amount of time
		$minutes = ( $this->ten_minutes * 60 ) * 0.8; // smaller time limit so won't run into another instance of cron
		$finish  = time() + $minutes;

		// loop through and update s3 meta with region
		foreach ( $all_attachments as $blog_id => $attachments ) {
			if ( 1 != $blog_id && is_multisite() ) {
				switch_to_blog( $blog_id );
			}
			foreach( $attachments as $attachment ) {
				if ( time() >= $finish ) {
					break;
				}
				$s3object = unserialize( $attachment->s3object );
				// retrieve region and update the attachment metadata
				$this->as3cf->get_s3object_region( $s3object, $attachment->ID );
			}
			if ( 1 != $blog_id && is_multisite() ) {
				restore_current_blog();
			}
		}
	}

	/**
	 * Get all attachments that don't have region in their S3 meta
	 *
	 * @param $prefix
	 * @param $limit
	 *
	 * @return mixed
	 */
	function get_attachments_without_region( $prefix, $limit ) {
		global $wpdb;
		$sql = $wpdb->prepare(
			"SELECT `post_id` as `ID`, `meta_value` AS 's3object'
    			FROM `{$prefix}postmeta`
   				WHERE `meta_key` = 'amazonS3_info'
    			AND `meta_value` NOT LIKE '%%\"region\"%%'
				LIMIT %d",
			$limit
		);

		return $wpdb->get_results( $sql, OBJECT );
	}

	/**
	 * Sanitize an integer passed through a filter
	 *
	 * @param $filter - filter tag
	 * @param $default
	 *
	 * @return float
	 */
	function sanitize_integer( $filter, $default ) {
		$number = apply_filters( $filter, $default );

		return is_numeric( $number ) ? round( $number ) : $default;
	}
}
