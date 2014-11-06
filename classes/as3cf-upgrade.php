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
	private $cron_interval_in_minutes;
	private $error_threshold;

	const CRON_HOOK = 'as3cf_cron_update_meta_with_region';
	const CRON_SCHEDULE_KEY = 'as3cf_update_meta_with_region_interval';

	const STATUS_RUNNING = 1;
	const STATUS_ERROR = 2;

	/**
	 * Start it up
	 *
	 * @param $as3cf - the instance of the as3cf class
	 */
	function __construct( $as3cf ) {
		$this->as3cf = $as3cf;

		$this->cron_interval_in_minutes = apply_filters( 'as3cf_update_meta_with_region_interval', 10 );
		$this->error_threshold = apply_filters( 'as3cf_update_meta_with_region_error_threshold', 20 );

		add_filter( 'cron_schedules', array( $this, 'cron_schedules' ) );
		add_action( self::CRON_HOOK, array( $this, 'cron_update_meta_with_region' ) );

		add_action( 'as3cf_pre_settings_render', array( $this, 'maybe_display_notices' ) );
		add_action( 'admin_init', array( $this, 'maybe_restart_upgrade' ) );

		$this->maybe_init_upgrade();
	}

	/**
	 * Maybe initialize the upgrade
	 */
	function maybe_init_upgrade() {
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		// Have we completed the upgrade yet?
		if ( $this->as3cf->get_setting( 'post_meta_version', 0 ) > 0 ) {
			return;
		}

		// If the upgrade status is already set, then we've already initialized the upgrade
		if ( $this->get_upgrade_status() ) {
			return;
		}

		// Initialize the upgrade
		$this->save_session( array( 'status' => self::STATUS_RUNNING ) );

		if ( ! wp_next_scheduled( self::CRON_HOOK ) ) {
			wp_schedule_event( current_time( 'timestamp' ), self::CRON_SCHEDULE_KEY, self::CRON_HOOK );
		}
	}

	/**
	 * Adds notices about issues with upgrades allowing user to restart them
	 */
	function maybe_display_notices() {
		switch ( $this->get_upgrade_status() ) {
			case self::STATUS_ERROR :
				$msg = __( 'In our attempt to update the meta data for all your Media Library items that have been uploaded to S3, we ran into a snag.', 'as3cf' );
				$msg .= ' <a href="' . self_admin_url( 'admin.php?page=' . $this->as3cf->get_plugin_slug() . '&action=restart_update_meta_with_region' ) . '">' . __( 'Try run it again', 'as3cf' ) . '</a>';
				$this->as3cf->render_view( 'error', array( 'error_message' => $msg ) );
				break;
		}
	}

	/**
	 * Generic method to trigger a job to be restarted
	 */
	function maybe_restart_upgrade() {
		if ( isset( $_GET['page'] ) && $_GET['page'] == $this->as3cf->get_plugin_slug() && isset( $_GET['action'] ) && 'restart_update_meta_with_region' == $_GET['action'] ) {
			// Turn it back on
			$session = $this->get_session();
			$session['status'] = self::STATUS_RUNNING;
			$this->save_session( $session );

			wp_redirect( self_admin_url( 'admin.php?page=' . $this->as3cf->get_plugin_slug() ) );
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
		$schedules[ self::CRON_SCHEDULE_KEY ] = array(
			'interval' => $this->cron_interval_in_minutes * 60,
			'display'  => __( 'Every ' . $this->cron_interval_in_minutes . ' Minutes', 'as3cf' )
		);

		return $schedules;
	}

	/**
	 * Wrapper for clearing scheduled events for a specific cron job
	 */
	function clear_scheduled_event() {
		$timestamp = wp_next_scheduled( self::CRON_HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::CRON_HOOK );
		}
	}

	/** COMMENTING THIS OUT INSTEAD OF REMOVING SO THE DIFF IS CLEARER
	 * Wrapper for the cron job to update the region of the bucket in s3 metadata
	 *
	 * 'post_meta_version' 0 = not completed, 1 = completed, 2 = failed with errors
	 *
	function update_meta_with_region() {
		// only run update of region if post_meta_version is 0
		if ( $this->check_setting_version( 'post_meta_version', 0, 0, '!=' ) ) {
			return;
		}
		// spawn the cron job to batch update s3 meta with bucket region
		$this->schedule_event( 'cron_update_meta_with_region', 'as3cf_upgrade_interval' );
	}*/

	/**
	 * Cron jon to update the region of the bucket in s3 metadata
	 */
	function cron_update_meta_with_region() {
		// Check if the cron should even be running
		if ( $this->as3cf->get_setting( 'post_meta_version', 0 ) > 0 || $this->get_upgrade_status() != self::STATUS_RUNNING ) {
			$this->clear_scheduled_event();
			return;
		}

		global $wpdb;
		$prefix = $wpdb->prefix;

		// set the batch size limit for the query
		$limit     = apply_filters( 'as3cf_update_meta_with_region_batch_size', 500 );
		$all_limit = $limit;

		$table_prefixes = array();
		$session 		= $this->get_session();

		// find the blog IDs that have been processed so we can skip them
		$processed_blog_ids = isset( $session['processed_blog_ids'] ) ? $session['processed_blog_ids'] : array();
		$error_count		= isset( $session['error_count'] ) ? $session['error_count'] : 0;

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
			$this->as3cf->set_setting( 'post_meta_version', 1 );
			$this->as3cf->remove_setting( 'update_meta_with_region_session' );
			$this->as3cf->save_settings();
			$this->clear_scheduled_event();
			return;
		}

		// only process the loop for a certain amount of time
		$minutes = $this->cron_interval_in_minutes * 60;

		// smaller time limit so won't run into another instance of cron
		$minutes = $minutes * 0.8;

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
					$error_count++;
					if ( $error_count >= $this->error_threshold ) {
						$session['status'] = self::STATUS_ERROR;
						$this->save_session( $session );
						$this->clear_scheduled_event();
						return;
					}
				}
			}
			if ( 1 != $blog_id && is_multisite() ) {
				restore_current_blog();
			}
		}

		$session['processed_blog_ids'] = $processed_blog_ids;
		$session['error_count']        = $error_count;

		$this->save_session( $session );
	}

	/** COMMENTING THIS OUT INSTEAD OF REMOVING SO THE DIFF IS CLEARER
	 * Abort a scheduled job and remove temporary job related setting
	 *
	 * @param       $job
	 *
	function abort_upgrade_job( $job ) {
		$this->as3cf->remove_setting( $job );
		$this->as3cf->save_settings();
		$this->clear_scheduled_event( $job );
	}*/

	/*
	 * Get the current status of the upgrade
	 * See STATUS_* constants in the class declaration above.
	 */
	function get_upgrade_status() {
		$session = $this->get_session();

		if ( ! isset( $session['status'] ) ) {
			return '';
		}

		return $session['status'];
	}

	/*
	 * Retrieve session data from plugin settings
	 *
	 * @return array
	 */
	function get_session() {
		return $this->as3cf->get_setting( 'update_meta_with_region_session', array() );
	}

	/*
	 * Store data to be used between requests in plugin settings
	 *
	 * @param $session array of session data to store
	 */
	function save_session( $session ) {
		$this->as3cf->set_setting( 'update_meta_with_region_session', $session );
		$this->as3cf->save_settings();
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
