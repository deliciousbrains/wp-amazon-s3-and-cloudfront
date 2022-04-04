<?php
/**
 * Upgrade extra info in custom objects table.
 *
 * @package     amazon-s3-and-cloudfront
 * @subpackage  Classes/Upgrades/Upgrade_Item_Extra_Data
 * @copyright   Copyright (c) 2021, Delicious Brains
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6.0
 */

namespace DeliciousBrains\WP_Offload_Media\Upgrades;

use AS3CF_Error;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;
use stdClass;

/**
 * Upgrade_Item_Extra_Data Class
 *
 * This class handles updating extra info in the custom objects table.
 *
 * @since 2.6.0
 */
class Upgrade_Item_Extra_Data extends Upgrade {

	/**
	 * @var int
	 */
	protected $upgrade_id = 10;

	/**
	 * @var string
	 */
	protected $upgrade_name = 'item_extra_data';

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
		return __( 'and updating metadata about offloaded items to new format.', 'amazon-s3-and-cloudfront' );
	}

	/**
	 * Update extra_info in items table
	 *
	 * @param stdClass $item
	 *
	 * @return bool
	 */
	protected function upgrade_item( $item ) {
		Item::disable_cache();
		$as3cf_item = Media_Library_Item::get_by_source_id( $item->source_id );
		Item::enable_cache();

		if ( ! $as3cf_item ) {
			AS3CF_Error::log( 'Could not construct item for attachment with ID ' . $item->source_id . '.' );
			$this->error_count++;

			return false;
		}

		$result = $as3cf_item->save();

		if ( is_wp_error( $result ) ) {
			AS3CF_Error::log( 'Error saving item: ' . $result->get_error_message() );
			$this->error_count++;

			return false;
		}

		return true;
	}

	/**
	 * Get a count of all items to be processed.
	 * for the whole site
	 *
	 * @return int
	 */
	protected function count_items_to_process() {
		return $this->count_items_with_old_extra_info( $this->blog_prefix );
	}

	/**
	 * Get all items to be processed.
	 *
	 * @param string     $prefix Table prefix for blog.
	 * @param int        $limit
	 * @param bool|mixed $offset
	 *
	 * @return array
	 */
	protected function get_items_to_process( $prefix, $limit, $offset = false ) {
		$attachments = $this->get_items_with_old_extra_info( $prefix, false, $limit );

		return $attachments;
	}

	/**
	 * Get a count of items that have legacy extra info.
	 *
	 * @param string $prefix Table prefix for blog.
	 *
	 * @return int
	 */
	protected function count_items_with_old_extra_info( $prefix ) {
		$count = $this->get_items_with_old_extra_info( $prefix, true );

		return $count;
	}

	/**
	 * Wrapper for database call to get items with legacy extra info.
	 *
	 * @param string   $prefix Table prefix for blog.
	 * @param bool     $count  return count of attachments
	 * @param null|int $limit
	 *
	 * @return mixed
	 */
	protected function get_items_with_old_extra_info( $prefix, $count = false, $limit = null ) {
		global $wpdb;

		$table = Item::ITEMS_TABLE;

		$sql = "
			FROM {$prefix}{$table}
			WHERE extra_info NOT LIKE '%s:7:\"objects\"%' AND source_type='media-library'
		";

		if ( $count ) {
			$sql = 'SELECT COUNT(source_id)' . $sql;

			return $wpdb->get_var( $sql );
		}

		$sql = 'SELECT source_id' . $sql;
		$sql .= ' ORDER BY id';

		if ( $limit && $limit > 0 ) {
			$sql .= sprintf( ' LIMIT %d', (int) $limit );
		}

		return $wpdb->get_results( $sql, OBJECT );
	}
}
