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
	private $cron_interval_in_minutes;
	private $error_threshold;

	/**
	 * Start it up
	 *
	 * @param $as3cf - the instance of the as3cf class
	 */
	function __construct( $as3cf ) {
		$this->as3cf = $as3cf;

		$this->cron_interval_in_minutes = $this->sanitize_integer( 'as3cf_upgrade_cron_interval', 10 );
		$this->error_threshold = $this->sanitize_integer( 'as3cf_upgrade_error_threshold', 20 );

		add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );
		add_action( $this->cron_hook, array( $this, 'process_cron_job' ) );

		add_action( 'as3cf_pre_settings_render', array( $this, 'upgrade_notices' ) );
		add_action( 'admin_init', array( $this, 'restart_job' ) );

		$this->plugin_upgrades();
	}

	/**
	 * Process any migrations or data changes needed after a plugin update
	 */
	function plugin_upgrades() {
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		// check if there are any jobs queued to be restarted
		$restart = $this->as3cf->get_setting( 'restart_jobs' );

		$current_version = $this->as3cf->get_setting( 'version' );
		if ( ! $restart && version_compare( $this->as3cf->get_plugin_version(), $current_version, '==' ) ) {
			return;
		}

		// 0.6.2 - update s3 meta with bucket region where missing
		$this->update_meta_with_region();

		$this->as3cf->set_setting( 'version', $this->as3cf->get_plugin_version() );
		$this->as3cf->save_settings();
	}

	/**
	 * Adds notices about issues with upgrades allowing user to restart them
	 */
	function upgrade_notices() {
		if ( 2 == $this->as3cf->get_setting( 'post_meta_version' ) ) {
			$msg = __( 'There were a number of errors in our upgrade routine to retrieve the bucket region for uploaded images.', 'as3cf' );
			$msg .= ' <a href="' . self_admin_url( 'admin.php?page=' . $this->as3cf->get_plugin_slug() . '&job=post_meta_version' ) . '">' . __( 'Run again', 'as3cf' ) . '</a>';

			$this->as3cf->render_view( 'error', array( 'error_message' => $msg ) );
		}
	}

	/**
	 * Generic method to trigger a job to be restarted
	 */
	function restart_job() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == $this->as3cf->get_plugin_slug() && isset( $_GET['job'] ) ) {
			// reset specific job indicator
			$this->as3cf->set_setting( $_GET['job'], 0 );
			// add the job to the array of restart jobs to get passed the version check for upgrade
			$this->add_job_to_restart_queue( $_GET['job'] );
			$this->as3cf->save_settings();

			wp_redirect( self_admin_url( 'admin.php?page=' . $this->as3cf->get_plugin_slug() ) );
		}
	}

	/**
	 * Add a job to the saved queue of jobs to be restarted
	 *
	 * @param $job
	 */
	function add_job_to_restart_queue( $job ) {
		$jobs         = $this->as3cf->get_setting( 'restart_jobs', array() );
		$jobs[ $job ] = 1;
		$this->as3cf->set_setting( 'restart_jobs', $jobs );
	}

	/**
	 * Removes a job from the saved queue of jobs to be restarted
	 *
	 * @param $job
	 */
	function remove_job_from_restart_queue( $job ) {
		$jobs = $this->as3cf->get_setting( 'restart_jobs', array() );
		if ( isset( $jobs[ $job ] ) ) {
			unset( $jobs[ $job ] );
			if ( count( $jobs ) > 0 ) {
				$this->as3cf->set_setting( 'restart_jobs', $jobs );
			} else {
				$this->as3cf->remove_setting( 'restart_jobs' );
			}
		}
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
		$schedules['as3cf_upgrade_interval'] = array(
			'interval' => $this->cron_interval_in_minutes * 60,
			'display'  => __( 'Every ' . $this->cron_interval_in_minutes . ' Minutes', 'as3cf' )
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
	function check_setting_version( $setting, $compare_version, $default_value = '', $operator = '<' ) {
		$setting_version = $this->as3cf->get_setting( $setting, $default_value );

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
	 *
	 * 'post_meta_version' 0 = not completed, 1 = completed, 2 = failed with errors
	 */
	function update_meta_with_region() {
		// only run update of region if post_meta_version is 0
		if ( $this->check_setting_version( 'post_meta_version', 0, 0, '!=' ) ) {
			return;
		}
		// spawn the cron job to batch update s3 meta with bucket region
		$this->schedule_event( 'cron_update_meta_with_region', 'as3cf_upgrade_interval' );
	}

	/**
	 * Cron jon to update the region of the bucket in s3 metadata
	 */
	function cron_update_meta_with_region() {
		$job      = __FUNCTION__;
		$meta_key = 'post_meta_version';

		// check if the cron should even be running
		if ( ! $this->check_setting_version( $meta_key, 1 ) ) {
			$this->clear_scheduled_event( $job );
			return;
		}

		$this->remove_job_from_restart_queue( $meta_key );

		global $wpdb;
		$prefix = $wpdb->prefix;

		// set the batch size limit for the query
		$limit     = $this->sanitize_integer( 'as3cf_update_meta_with_region_batch_size', 500 );
		$all_limit = $limit;

		// query all attachments with amazons3_info without region key in meta
		$table_prefixes = array();
		$job_meta       = $this->as3cf->get_setting( $job, array() );
		// find the blog IDs that have been processed so we can skip them
		$processed_blog_ids = isset( $job_meta['processed_blog_ids'] ) ? $job_meta['processed_blog_ids'] : array();
		$errors             = isset( $job_meta['errors'] ) ? $job_meta['errors'] : 0;

		if ( ! in_array( 1, $processed_blog_ids ) ) {
			$table_prefixes[1] = $prefix;
		}
		if ( is_multisite() ) {
			$blog_ids = $this->as3cf->get_blog_ids();
			foreach ( $blog_ids as $blog_id ) {
				if ( in_array( $blog_id, $processed_blog_ids ) ) {
					continue;
				}
				$table_prefixes[ $blog_id ] = $prefix . $blog_id . '_';
			}
		}

		$all_attachments = array();
		$all_count       = 0;

		foreach ( $table_prefixes as $blog_id => $table_prefix ) {
			$attachments = $this->get_attachments_without_region( $table_prefix, $limit );
			$count       = count( $attachments );

			if ( 0 == $count ) {
				// no more attachments, record the blog ID to skip next time
				$processed_blog_ids[] = $blog_id;
			} else {
				$all_count += $count;
				$all_attachments[ $blog_id ] = $attachments;
			}

			if ( $all_count >= $all_limit ) {
				break;
			}

			$limit = $limit - $count;
		}

		if ( 0 == $all_count ) {
			$this->as3cf->set_setting( $meta_key, 1 ); // 1 = upgrade finished
			$this->abort_upgrade_job( $job );
			return;
		}

		// only process the loop for a certain amount of time
		$minutes = ( $this->cron_interval_in_minutes * 60 ) * 0.8; // smaller time limit so won't run into another instance of cron
		$finish  = time() + $minutes;

		// loop through and update s3 meta with region
		foreach ( $all_attachments as $blog_id => $attachments ) {
			if ( 1 != $blog_id && is_multisite() ) {
				switch_to_blog( $blog_id );
			}
			foreach ( $attachments as $attachment ) {
				if ( time() >= $finish ) {
					break;
				}
				$s3object = unserialize( $attachment->s3object );
				// retrieve region and update the attachment metadata
				$region = $this->as3cf->get_s3object_region( $s3object, $attachment->ID );
				if ( is_wp_error( $region ) ) {
					$errors ++;
					if ( $errors >= $this->error_threshold ) {
						// abort upgrade process
						$this->as3cf->set_setting( $meta_key, 2 ); // 2 = aborted with errors
						$this->abort_upgrade_job( $job );

						return;
					}
				}
			}
			if ( 1 != $blog_id && is_multisite() ) {
				restore_current_blog();
			}
		}

		$job_meta['processed_blog_ids'] = $processed_blog_ids;
		$job_meta['errors']             = $errors;
		$this->as3cf->set_setting( $job, $job_meta );
		$this->as3cf->save_settings();
	}

	/**
	 * Abort a scheduled job and remove temporary job related setting
	 *
	 * @param       $job
	 */
	function abort_upgrade_job( $job ) {
		$this->as3cf->remove_setting( $job );
		$this->as3cf->save_settings();
		$this->clear_scheduled_event( $job );
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
