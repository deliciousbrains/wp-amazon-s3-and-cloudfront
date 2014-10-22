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

	/**
	 * Start it up
	 *
	 * @param $as3cf - the instance of the as3cf class
	 */
	function __construct( $as3cf ) {
		$this->as3cf = $as3cf;

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
			'interval' => 600,
			'display'  => __( 'Every 10 Minutes', 'as3cf' )
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
		$limit = apply_filters( 'as3cf_update_meta_with_region_batch_size', 500 );
		$limit = is_numeric( $limit ) ? round( $limit ) : 500;

		// query all attachment posts with amazons3_info without region key in meta
		$all_attachments    = array();
		$attachments        = $this->get_attachments_without_region( $prefix, $limit );
		$count              = count( $attachments );
		$all_attachments[1] = $attachments;

		if ( is_multisite() ) {
			$blogs = $this->as3cf->get_blogs();
			foreach ( $blogs as $blog ) {
				if ( $count >= $limit ) {
					break;
				}
				$blog_prefix      = $prefix . $blog . '_';
				$blog_attachments = $this->get_attachments_without_region( $blog_prefix, $limit );
				$count += count( $blog_attachments );
				$all_attachments[ $blog ] = $blog_attachments;
			}
		}

		if ( 0 == $count ) {
			// update post_meta_version
			$this->as3cf->set_setting( 'post_meta_version', 1 );
			$this->as3cf->save_settings();
			// remove schedule
			$this->clear_scheduled_event( 'cron_update_meta_with_region' );

			return;
		}

		// only process the loop for a certain amount of time
		$finish = time() + 480; // 8 minutes so won't run into another instance of cron

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
}
