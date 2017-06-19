<?php
/**
 * Upgrade Region in Meta
 *
 * @package     amazon-s3-and-cloudfront
 * @subpackage  Classes/Upgrades/Region-Meta
 * @copyright   Copyright (c) 2014, Delicious Brains
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.6.2
 */

namespace DeliciousBrains\WP_Offload_S3\Upgrades;

use AS3CF_Error;

/**
 * Upgrade_Region_Meta Class
 *
 * This class handles updating the region of the attachment's bucket in the meta data
 *
 * @since 0.6.2
 */
class Upgrade_Region_Meta extends Upgrade {

	/**
	 * @var int
	 */
	protected $upgrade_id = 1;

	/**
	 * @var string
	 */
	protected $upgrade_name = 'meta_with_region';

	/**
	 * @var string 'metadata', 'attachment'
	 */
	protected $upgrade_type = 'metadata';

	/**
	 * Get running update text.
	 *
	 * @return string
	 */
	protected function get_running_update_text() {
		return __( 'and updating the metadata with the bucket region it is served from. This will allow us to serve your files from the proper S3 region subdomain <span style="white-space:nowrap;">(e.g. s3-us-west-2.amazonaws.com)</span>.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get the region for the bucket where an attachment is located, update the S3 meta.
	 *
	 * @param mixed $attachment
	 *
	 * @return bool
	 */
	protected function upgrade_item( $attachment ) {
		$s3object = unserialize( $attachment->s3object );
		if ( false === $s3object ) {
			AS3CF_Error::log( 'Failed to unserialize S3 meta for attachment ' . $attachment->ID . ': ' . $attachment->s3object );
			$this->error_count++;

			return false;
		}
		// retrieve region and update the attachment metadata
		$region = $this->as3cf->get_s3object_region( $s3object, $attachment->ID );
		if ( is_wp_error( $region ) ) {
			AS3CF_Error::log( 'Error updating region: ' . $region->get_error_message() );
			$this->error_count++;

			return false;
		}

		return true;
	}

	/**
	 * Get a count of all attachments without region in their S3 metadata
	 * for the whole site
	 *
	 * @return int
	 */
	protected function count_items_to_process() {
		return $this->count_attachments_without_region( $this->blog_prefix );
	}

	/**
	 * Get all attachments that don't have region in their S3 meta data for a blog
	 *
	 * @param string     $prefix
	 * @param int        $limit
	 * @param bool|mixed $offset
	 *
	 * @return array
	 */
	protected function get_items_to_process( $prefix, $limit, $offset = false ) {
		$attachments = $this->get_attachments_without_region_results( $prefix, false, $limit );

		return $attachments;
	}

	/**
	 * Get a count of attachments that don't have region in their S3 meta data for a blog
	 *
	 * @param $prefix
	 *
	 * @return int
	 */
	protected function count_attachments_without_region( $prefix ) {
		$count = $this->get_attachments_without_region_results( $prefix, true );

		return $count;
	}

	/**
	 * Wrapper for database call to get attachments without region
	 *
	 * @param string   $prefix
	 * @param bool     $count return count of attachments
	 * @param null|int $limit
	 *
	 * @return mixed
	 */
	protected function get_attachments_without_region_results( $prefix, $count = false, $limit = null ) {
		global $wpdb;

		$sql = " FROM `{$prefix}postmeta`
				WHERE `meta_key` = 'amazonS3_info'
				AND `meta_value` NOT LIKE '%%\"region\"%%'";

		if ( $count ) {
			$sql = 'SELECT COUNT(*)' . $sql;

			return $wpdb->get_var( $sql );
		}

		$sql = "SELECT `post_id` as `ID`, `meta_value` AS 's3object'" . $sql;

		if ( $limit && $limit > 0 ) {
			$sql .= sprintf( ' LIMIT %d', (int) $limit );
		}

		return $wpdb->get_results( $sql, OBJECT );
	}
}