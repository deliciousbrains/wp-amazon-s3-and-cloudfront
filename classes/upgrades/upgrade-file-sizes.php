<?php
/**
 * Update File Sizes
 *
 * @package     amazon-s3-and-cloudfront
 * @subpackage  Classes/Upgrades/File-Sizes
 * @copyright   Copyright (c) 2015, Delicious Brains
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.9.3
 */

namespace DeliciousBrains\WP_Offload_S3\Upgrades;

use AS3CF_Error;
use Exception;

/**
 * Upgrade_File_Sizes Class
 *
 * This class handles updating the file sizes in the meta data
 * for attachments that have been removed from the local server
 *
 * @since 0.9.3
 */
class Upgrade_File_Sizes extends Upgrade {

	/**
	 * @var int
	 */
	protected $upgrade_id = 2;

	/**
	 * @var string
	 */
	protected $upgrade_name = 'file_sizes';

	/**
	 * @var string 'metadata', 'attachment'
	 */
	protected $upgrade_type = 'attachments';

	/**
	 * Get running update text.
	 *
	 * @return string
	 */
	protected function get_running_update_text() {
		return __( 'and updating the metadata with the sizes of files that have been removed from the server. This will allow us to serve the correct size for media items and the total space used in Multisite subsites.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Get the total file sizes for an attachment and associated files.
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

		$region = $this->as3cf->get_s3object_region( $s3object );
		if ( is_wp_error( $region ) ) {
			AS3CF_Error::log( 'Failed to get the region for the bucket of the attachment ' . $attachment->ID );
			$this->error_count++;

			return false;
		}

		$s3client  = $this->as3cf->get_s3client( $region, true );
		$main_file = $s3object['key'];

		$ext    = pathinfo( $main_file, PATHINFO_EXTENSION );
		$prefix = trailingslashit( dirname( $s3object['key'] ) );

		// Used to search S3 for all files related to an attachment
		$search_prefix = $prefix . wp_basename( $main_file, ".$ext" );

		$args = array(
			'Bucket' => $s3object['bucket'],
			'Prefix' => $search_prefix,
		);

		try {
			// List objects for the attachment
			$result = $s3client->list_objects( $args );
		} catch ( Exception $e ) {
			AS3CF_Error::log( 'Error listing objects of prefix ' . $search_prefix . ' for attachment ' . $attachment->ID . ' from S3: ' . $e->getMessage() );
			$this->error_count++;

			return false;
		}

		$file_size_total = 0;
		$main_file_size  = 0;

		if ( ! empty( $result['Contents'] ) ) {
			foreach ( $result['Contents'] as $object ) {
				if ( ! isset( $object['Size'] ) ) {
					continue;
				}

				$size = $object['Size'];

				// Increment the total size of files for the attachment
				$file_size_total += $size;

				if ( $object['Key'] === $main_file ) {
					// Record the size of the main file
					$main_file_size = $size;
				}
			}
		}

		if ( 0 === $file_size_total ) {
			AS3CF_Error::log( 'Total file size for the attachment is 0: ' . $attachment->ID );
			$this->error_count++;

			return false;
		}

		// Update the main file size for the attachment
		$meta             = get_post_meta( $attachment->ID, '_wp_attachment_metadata', true );
		$meta['filesize'] = $main_file_size;
		update_post_meta( $attachment->ID, '_wp_attachment_metadata', $meta );

		// Add the total file size for all image sizes
		update_post_meta( $attachment->ID, 'wpos3_filesize_total', $file_size_total );

		return true;
	}

	/**
	 * Get all attachments removed from the server.
	 *
	 * @param string     $prefix
	 * @param int        $limit
	 * @param bool|mixed $offset
	 *
	 * @return array
	 */
	protected function get_items_to_process( $prefix, $limit, $offset = false ) {
		$all_attachments = $this->get_s3_attachments( $prefix, $limit );
		$attachments     = array();

		foreach ( $all_attachments as $attachment ) {
			if ( ! file_exists( get_attached_file( $attachment->ID, true ) ) ) {
				$attachments[] = $attachment;
			}
		}

		return $attachments;
	}

	/**
	 * Wrapper for database call to get attachments uploaded to S3,
	 * that don't have the file size meta added already
	 *
	 * @param string   $prefix
	 * @param null|int $limit
	 *
	 * @return mixed
	 */
	protected function get_s3_attachments( $prefix, $limit = null ) {
		global $wpdb;

		$sql = "SELECT pm1.`post_id` as `ID`, pm1.`meta_value` AS 's3object'
				FROM `{$prefix}postmeta` pm1
					LEFT OUTER JOIN `{$prefix}postmeta` pm2
					ON pm1.`post_id` = pm2.`post_id`
					AND pm2.`meta_key` = 'wpos3_filesize_total'
				WHERE pm1.`meta_key` = 'amazonS3_info'
				AND pm2.`post_id` is null";

		if ( $limit && $limit > 0 ) {
			$sql .= sprintf( ' LIMIT %d', (int) $limit );
		}

		return $wpdb->get_results( $sql, OBJECT );
	}
}