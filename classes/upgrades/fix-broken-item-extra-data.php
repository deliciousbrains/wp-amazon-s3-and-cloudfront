<?php
/**
 * Upgrade extra info in custom objects table.
 *
 * @package     amazon-s3-and-cloudfront
 * @subpackage  Classes/Upgrades/Upgrade_Item_Extra_Data
 * @copyright   Copyright (c) 2022, Delicious Brains
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6.2
 */

namespace DeliciousBrains\WP_Offload_Media\Upgrades;

use DeliciousBrains\WP_Offload_Media\Items\Item;

/**
 * Fix_Broken_Item_Extra_Data Class
 *
 * This class handles updating extra info in the custom objects table.
 *
 * @since 2.6.2
 */
class Fix_Broken_Item_Extra_Data extends Upgrade_Item_Extra_Data {

	/**
	 * @var int
	 */
	protected $upgrade_id = 12;

	/**
	 * @var string
	 */
	protected $upgrade_name = 'fix_broken_item_extra_data';

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

		/**
		 * Find items with empty object array or that still have the private_sizes array element in
		 * extra_info
		 */
		$sql = "
			FROM {$prefix}{$table}
			WHERE (extra_info LIKE '%s:7:\"objects\";a:0%' OR extra_info LIKE '%s:13:\"private_sizes\";a%' OR extra_info LIKE 's:%')
			AND source_type='media-library'
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
