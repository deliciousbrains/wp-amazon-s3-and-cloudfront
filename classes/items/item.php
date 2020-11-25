<?php

namespace DeliciousBrains\WP_Offload_Media\Items;

use Amazon_S3_And_CloudFront;
use AS3CF_Utils;
use WP_Error;

abstract class Item {
	const ITEMS_TABLE = 'as3cf_items';
	const ORIGINATORS = array(
		'standard'      => 0,
		'metadata-tool' => 1,
	);

	protected static $source_type = 'media-library';
	protected static $source_table = 'posts';
	protected static $source_fk = 'id';

	private static $checked_table_exists = array();

	protected static $items_cache_by_id = array();
	protected static $items_cache_by_source_id = array();
	protected static $items_cache_by_path = array();
	protected static $items_cache_by_source_path = array();

	/**
	 * @var array Keys with array of fields that can be used for cache lookups.
	 */
	protected static $cache_keys = array(
		'id'          => array( 'id' ),
		'source_id'   => array( 'source_id' ),
		'path'        => array( 'path', 'original_path' ),
		'source_path' => array( 'source_path', 'original_source_path' ),
	);

	private $id;
	private $provider;
	private $region;
	private $bucket;
	private $path;
	private $original_path;
	private $is_private;
	private $source_id;
	private $source_path;
	private $original_source_path;
	private $extra_info;
	private $originator;
	private $is_verified;

	/**
	 * Item constructor.
	 *
	 * @param string $provider          Storage provider key name, e.g. "aws".
	 * @param string $region            Region for item's bucket.
	 * @param string $bucket            Bucket for item.
	 * @param string $path              Key path for item (full sized if type has thumbnails etc).
	 * @param bool   $is_private        Is the object private in the bucket.
	 * @param int    $source_id         ID that source has.
	 * @param string $source_path       Path that source uses, could be relative or absolute depending on source.
	 * @param string $original_filename An optional filename with no path that was previously used for the item.
	 * @param array  $extra_info        An optional array of extra data specific to the source type.
	 * @param int    $id                Optional Item record ID.
	 * @param int    $originator        Optional originator of record from ORIGINATORS const.
	 * @param bool   $is_verified       Optional flag as to whether Item's objects are known to exist.
	 */
	public function __construct(
		$provider,
		$region,
		$bucket,
		$path,
		$is_private,
		$source_id,
		$source_path,
		$original_filename = null,
		$extra_info = array(),
		$id = null,
		$originator = 0,
		$is_verified = true
	) {
		$this->provider    = $provider;
		$this->region      = $region;
		$this->bucket      = $bucket;
		$this->path        = $path;
		$this->is_private  = $is_private;
		$this->source_id   = $source_id;
		$this->source_path = $source_path;
		$this->extra_info  = serialize( $extra_info );
		$this->originator  = $originator;
		$this->is_verified = $is_verified;

		if ( empty( $original_filename ) ) {
			$this->original_path        = $path;
			$this->original_source_path = $source_path;
		} else {
			$this->original_path        = str_replace( wp_basename( $path ), $original_filename, $path );
			$this->original_source_path = str_replace( wp_basename( $source_path ), $original_filename, $source_path );
		}

		if ( ! empty( $id ) ) {
			$this->id = $id;
		}

		static::add_to_items_cache( $this );
	}

	/**
	 * Returns the string used to group all keys in the object cache by.
	 *
	 * @return string
	 */
	protected static function get_object_cache_group() {
		static $group;

		if ( empty( $group ) ) {
			/** @var Amazon_S3_And_CloudFront $as3cf */
			global $as3cf;
			$group = trim( '' . apply_filters( 'as3cf_object_cache_group', $as3cf->get_plugin_prefix() ) );
		}

		return $group;
	}

	/**
	 * Get base string for all of current blog's object cache keys.
	 *
	 * @return string
	 */
	protected static function get_object_cache_base_key() {
		$blog_id = get_current_blog_id();

		return static::items_table() . '-' . $blog_id . '-' . static::$source_type;
	}

	/**
	 * Get full object cache key.
	 *
	 * @param string $base_key
	 * @param string $key
	 * @param string $field
	 *
	 * @return string
	 */
	protected static function get_object_cache_full_key( $base_key, $key, $field ) {
		return sanitize_text_field( $base_key . '-' . $key . '-' . $field );
	}

	/**
	 * Add the given item to the object cache.
	 *
	 * @param Item $item
	 */
	protected static function add_to_object_cache( $item ) {
		if ( empty( $item ) || empty( static::$cache_keys ) ) {
			return;
		}

		$base_key = static::get_object_cache_base_key();
		$group    = static::get_object_cache_group();

		$keys = array();

		foreach ( static::$cache_keys as $key => $fields ) {
			foreach ( $fields as $field ) {
				$full_key = static::get_object_cache_full_key( $base_key, $key, $item->{$field}() );

				if ( in_array( $full_key, $keys ) ) {
					continue;
				}

				wp_cache_set( $full_key, $item, $group );

				$keys[] = $full_key;
			}
		}
	}

	/**
	 * Delete the given item from the object cache.
	 *
	 * @param Item $item
	 */
	protected static function remove_from_object_cache( $item ) {
		if ( empty( $item ) || empty( static::$cache_keys ) ) {
			return;
		}

		$base_key = static::get_object_cache_base_key();
		$group    = static::get_object_cache_group();

		$keys = array();

		foreach ( static::$cache_keys as $key => $fields ) {
			foreach ( $fields as $field ) {
				$full_key = static::get_object_cache_full_key( $base_key, $key, $item->{$field}() );

				if ( in_array( $full_key, $keys ) ) {
					continue;
				}

				wp_cache_delete( $full_key, $group );

				$keys[] = $full_key;
			}
		}
	}

	/**
	 * Try and get Item from object cache by known key and value.
	 *
	 * Note: Actual lookup is scoped by blog and item's source_type, so example key may be 'source_id'.
	 *
	 * @param string $key   The base of the key that makes up the lookup, e.g. field for given value.
	 * @param mixed  $value Will be coerced to string for lookup.
	 *
	 * @return bool|Item
	 */
	protected static function get_from_object_cache( $key, $value ) {
		if ( ! array_key_exists( $key, static::$cache_keys ) ) {
			return false;
		}

		$base_key = static::get_object_cache_base_key();
		$full_key = static::get_object_cache_full_key( $base_key, $key, $value );
		$group    = static::get_object_cache_group();
		$force    = false;
		$found    = false;
		$result   = wp_cache_get( $full_key, $group, $force, $found );

		if ( $found ) {
			return $result;
		}

		return false;
	}

	/**
	 * (Re)initialize the static cache used for speeding up queries.
	 */
	public static function init_cache() {
		self::$checked_table_exists = array();

		static::$items_cache_by_id          = array();
		static::$items_cache_by_source_id   = array();
		static::$items_cache_by_path        = array();
		static::$items_cache_by_source_path = array();
	}

	/**
	 * Add an item to the static cache to allow fast retrieval via get_from_items_cache_by_* functions.
	 *
	 * @param Item $item
	 */
	protected static function add_to_items_cache( $item ) {
		$blog_id = get_current_blog_id();

		if ( ! empty( $item->id() ) ) {
			static::$items_cache_by_id[ $blog_id ][ $item->id() ] = $item;
		}

		if ( ! empty( $item->source_id() ) ) {
			static::$items_cache_by_source_id[ $blog_id ][ static::$source_type ][ $item->source_id() ] = $item;
		}

		if ( ! empty( $item->path() ) ) {
			static::$items_cache_by_path[ $blog_id ][ static::$source_type ][ $item->original_path() ] = $item;
			static::$items_cache_by_path[ $blog_id ][ static::$source_type ][ $item->path() ]          = $item;
		}

		if ( ! empty( $item->source_path() ) ) {
			static::$items_cache_by_source_path[ $blog_id ][ static::$source_type ][ $item->original_source_path() ] = $item;
			static::$items_cache_by_source_path[ $blog_id ][ static::$source_type ][ $item->source_path() ]          = $item;
		}
	}

	/**
	 * Remove an item from the static cache that allows fast retrieval via get_from_items_cache_by_* functions.
	 *
	 * @param Item $item
	 */
	protected static function remove_from_items_cache( $item ) {
		$blog_id = get_current_blog_id();

		if ( ! empty( $item->id() ) ) {
			unset( static::$items_cache_by_id[ $blog_id ][ $item->id() ] );
		}

		if ( ! empty( $item->source_id() ) ) {
			unset( static::$items_cache_by_source_id[ $blog_id ][ static::$source_type ][ $item->source_id() ] );
		}

		if ( ! empty( $item->path() ) ) {
			unset( static::$items_cache_by_path[ $blog_id ][ static::$source_type ][ $item->original_path() ] );
			unset( static::$items_cache_by_path[ $blog_id ][ static::$source_type ][ $item->path() ] );
		}

		if ( ! empty( $item->source_path() ) ) {
			unset( static::$items_cache_by_source_path[ $blog_id ][ static::$source_type ][ $item->original_source_path() ] );
			unset( static::$items_cache_by_source_path[ $blog_id ][ static::$source_type ][ $item->source_path() ] );
		}
	}

	/**
	 * Try and get Item from cache by known id.
	 *
	 * @param int $id
	 *
	 * @return bool|Item
	 */
	private static function get_from_items_cache_by_id( $id ) {
		$blog_id = get_current_blog_id();

		if ( ! empty( static::$items_cache_by_id[ $blog_id ][ $id ] ) ) {
			return static::$items_cache_by_id[ $blog_id ][ $id ];
		}

		$item = static::get_from_object_cache( 'id', $id );

		if ( $item ) {
			static::add_to_items_cache( $item );

			return $item;
		}

		return false;
	}

	/**
	 * Try and get Item from cache by known source_id.
	 *
	 * @param int $source_id
	 *
	 * @return bool|Item
	 */
	private static function get_from_items_cache_by_source_id( $source_id ) {
		$blog_id = get_current_blog_id();

		if ( ! empty( static::$items_cache_by_source_id[ $blog_id ][ static::$source_type ][ $source_id ] ) ) {
			return static::$items_cache_by_source_id[ $blog_id ][ static::$source_type ][ $source_id ];
		}

		$item = static::get_from_object_cache( 'source_id', $source_id );

		if ( $item ) {
			static::add_to_items_cache( $item );

			return $item;
		}

		return false;
	}

	/**
	 * Try and get Item from cache by known bucket and path.
	 *
	 * @param string $bucket
	 * @param string $path
	 *
	 * @return bool|Item
	 */
	private static function get_from_items_cache_by_bucket_and_path( $bucket, $path ) {
		$blog_id = get_current_blog_id();

		if ( ! empty( static::$items_cache_by_path[ $blog_id ][ static::$source_type ][ $path ] ) ) {
			/** @var Item $item */
			$item = static::$items_cache_by_path[ $blog_id ][ static::$source_type ][ $path ];

			if ( $item->bucket() === $bucket ) {
				return $item;
			}
		}

		return false;
	}

	/**
	 * The full items table name for current blog.
	 *
	 * @return string
	 */
	protected static function items_table() {
		global $wpdb;

		/* @var Amazon_S3_And_CloudFront $as3cf */
		global $as3cf;

		$table_name = $wpdb->get_blog_prefix() . static::ITEMS_TABLE;

		if ( empty( self::$checked_table_exists[ $table_name ] ) ) {
			self::$checked_table_exists[ $table_name ] = true;

			$schema_version = get_option( $as3cf->get_plugin_prefix() . '_schema_version', '0.0.0' );

			if ( version_compare( $schema_version, $as3cf->get_plugin_version(), '<' ) ) {
				self::install_table( $table_name );

				update_option( $as3cf->get_plugin_prefix() . '_schema_version', $as3cf->get_plugin_version() );
			}
		}

		return $table_name;
	}

	/**
	 * Create the table needed by this class with given name (for current site).
	 *
	 * @param string $table_name
	 */
	private static function install_table( $table_name ) {
		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$wpdb->hide_errors();

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
				CREATE TABLE {$table_name} (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				provider VARCHAR(18) NOT NULL,
				region VARCHAR(255) NOT NULL,
				bucket VARCHAR(255) NOT NULL,
				path VARCHAR(1024) NOT NULL,
				original_path VARCHAR(1024) NOT NULL,
				is_private BOOLEAN NOT NULL DEFAULT 0,
				source_type VARCHAR(18) NOT NULL,
				source_id BIGINT(20) UNSIGNED NOT NULL,
				source_path VARCHAR(1024) NOT NULL,
				original_source_path VARCHAR(1024) NOT NULL,
				extra_info LONGTEXT,
				originator TINYINT UNSIGNED NOT NULL DEFAULT 0,
				is_verified BOOLEAN NOT NULL DEFAULT 1,
				PRIMARY KEY  (id),
				UNIQUE KEY uidx_path (path(190), id),
				UNIQUE KEY uidx_original_path (original_path(190), id),
				UNIQUE KEY uidx_source_path (source_path(190), id),
				UNIQUE KEY uidx_original_source_path (original_source_path(190), id),
				UNIQUE KEY uidx_source (source_type, source_id),
				UNIQUE KEY uidx_provider_bucket (provider, bucket(190), id),
				UNIQUE KEY uidx_is_verified_originator (is_verified, originator, id)
				) $charset_collate;
				";
		dbDelta( $sql );
	}

	/**
	 * Get item's data as an array, optionally with id if available.
	 *
	 * @param bool $include_id Default false.
	 *
	 * @return array
	 */
	public function key_values( $include_id = false ) {
		$key_values = array(
			'provider'             => $this->provider,
			'region'               => $this->region,
			'bucket'               => $this->bucket,
			'path'                 => $this->path,
			'original_path'        => $this->original_path,
			'is_private'           => $this->is_private,
			'source_type'          => static::$source_type,
			'source_id'            => $this->source_id,
			'source_path'          => $this->source_path,
			'original_source_path' => $this->original_source_path,
			'extra_info'           => $this->extra_info,
			'originator'           => $this->originator,
			'is_verified'          => $this->is_verified,
		);

		if ( $include_id && ! empty( $this->id ) ) {
			$key_values['id'] = $this->id;
		}

		ksort( $key_values );

		return $key_values;
	}

	/**
	 * All the item's property names in an array, optionally with id if available.
	 *
	 * @param bool $include_id Default false.
	 *
	 * @return array
	 */
	private function keys( $include_id = false ) {
		return array_keys( $this->key_values( $include_id ) );
	}

	/**
	 * All the item's property values in an array, optionally with id if available.
	 *
	 * @param bool $include_id Default false.
	 *
	 * @return array
	 */
	private function values( $include_id = false ) {
		return array_values( $this->key_values( $include_id ) );
	}

	/**
	 * Get item's column formats as an associative array, optionally with id if available.
	 *
	 * @param bool $include_id Default false.
	 *
	 * @return array
	 */
	private function key_formats( $include_id = false ) {
		$key_values = array(
			'provider'             => '%s',
			'region'               => '%s',
			'bucket'               => '%s',
			'path'                 => '%s',
			'original_path'        => '%s',
			'is_private'           => '%d',
			'source_type'          => '%s',
			'source_id'            => '%d',
			'source_path'          => '%s',
			'original_source_path' => '%s',
			'extra_info'           => '%s',
			'originator'           => '%d',
			'is_verified'          => '%d',
		);

		if ( $include_id && ! empty( $this->id ) ) {
			$key_values['id'] = '%d';
		}

		ksort( $key_values );

		return $key_values;
	}

	/**
	 * All the item's column formats in an indexed array, optionally with id if available.
	 *
	 * @param bool $include_id Default false.
	 *
	 * @return array
	 */
	private function formats( $include_id = false ) {
		return array_values( $this->key_formats( $include_id ) );
	}

	/**
	 * Save the item's current data.
	 *
	 * @return int|WP_Error
	 */
	public function save() {
		global $wpdb;

		if ( empty( $this->id ) ) {
			$result = $wpdb->insert( static::items_table(), $this->key_values(), $this->formats() );

			if ( $result ) {
				$this->id = $wpdb->insert_id;

				// Now that the item has an ID it should be (re)cached.
				static::add_to_items_cache( $this );
			}
		} else {
			// Make sure object cache does not have stale items.
			$old_item = static::get_from_object_cache( 'id', $this->id() );
			static::remove_from_object_cache( $old_item );
			unset( $old_item );

			$result = $wpdb->update( static::items_table(), $this->key_values(), array( 'id' => $this->id ), $this->formats(), array( '%d' ) );
		}

		if ( false !== $result ) {
			// Now that the item has an ID it should be (re)cached.
			static::add_to_object_cache( $this );
		} else {
			static::remove_from_items_cache( $this );

			return new WP_Error( 'item_save', 'Error saving item:- ' . $wpdb->last_error );
		}

		return $this->id;
	}

	/**
	 * Delete the current item.
	 *
	 * @return bool|WP_Error
	 */
	public function delete() {
		global $wpdb;

		static::remove_from_items_cache( $this );
		static::remove_from_object_cache( $this );

		if ( empty( $this->id ) ) {
			return new WP_Error( 'item_delete', 'Error trying to delete item with no id.' );
		} else {
			$result = $wpdb->delete( static::items_table(), array( 'id' => $this->id ), array( '%d' ) );
		}

		if ( ! $result ) {
			return new WP_Error( 'item_delete', 'Error deleting item:- ' . $wpdb->last_error );
		}

		return true;
	}

	/**
	 * Creates an item based on object from database.
	 *
	 * @param object $object
	 * @param bool   $add_to_object_cache Should this object be added to the object cache too?
	 *
	 * @return Item
	 */
	protected static function create( $object, $add_to_object_cache = false ) {
		$extra_info = array();

		if ( ! empty( $object->extra_info ) ) {
			$extra_info = unserialize( $object->extra_info );
		}

		$item = new static(
			$object->provider,
			$object->region,
			$object->bucket,
			$object->path,
			$object->is_private,
			$object->source_id,
			$object->source_path,
			wp_basename( $object->original_source_path ),
			$extra_info,
			$object->id,
			$object->originator,
			$object->is_verified
		);

		if ( $add_to_object_cache ) {
			static::add_to_object_cache( $item );
		}

		return $item;
	}

	/**
	 * Get an item by its id.
	 *
	 * @param integer $id
	 *
	 * @return bool|Item
	 */
	public static function get_by_id( $id ) {
		global $wpdb;

		if ( empty( $id ) ) {
			return false;
		}

		$item = static::get_from_items_cache_by_id( $id );

		if ( ! empty( $item ) ) {
			return $item;
		}

		$sql = $wpdb->prepare( "SELECT * FROM " . static::items_table() . " WHERE source_type = %s AND id = %d", static::$source_type, $id );

		$object = $wpdb->get_row( $sql );

		if ( empty( $object ) ) {
			return false;
		}

		return static::create( $object, true );
	}

	/**
	 * Get an item by its source id.
	 *
	 * While source id isn't strictly unique, it is by source type, which is always used in queries based on called class.
	 *
	 * @param integer $source_id
	 *
	 * @return bool|Item
	 */
	public static function get_by_source_id( $source_id ) {
		global $wpdb;

		if ( ! is_numeric( $source_id ) ) {
			return false;
		}

		$source_id = (int) $source_id;

		if ( empty( $source_id ) ) {
			return false;
		}

		$item = static::get_from_items_cache_by_source_id( $source_id );

		if ( ! empty( $item ) ) {
			return $item;
		}

		$sql = $wpdb->prepare( "SELECT * FROM " . static::items_table() . " WHERE source_id = %d AND source_type = %s", $source_id, static::$source_type );

		$object = $wpdb->get_row( $sql );

		if ( empty( $object ) ) {
			return false;
		}

		return static::create( $object, true );
	}

	/**
	 * Getter for item's id value.
	 *
	 * @return integer
	 */
	public function id() {
		return $this->id;
	}

	/**
	 * Getter for item's provider value.
	 *
	 * @return string
	 */
	public function provider() {
		return $this->provider;
	}

	/**
	 * Getter for item's region value.
	 *
	 * @return string
	 */
	public function region() {
		return $this->region;
	}

	/**
	 * Getter for item's bucket value.
	 *
	 * @return string
	 */
	public function bucket() {
		return $this->bucket;
	}

	/**
	 * Getter for item's path value.
	 *
	 * @return string
	 */
	public function path() {
		return $this->path;
	}

	/**
	 * Getter for item's original_path value.
	 *
	 * @return string
	 */
	public function original_path() {
		return $this->original_path;
	}

	/**
	 * Getter for item's is_private value.
	 *
	 * @return bool
	 */
	public function is_private() {
		return (bool) $this->is_private;
	}

	/**
	 * Setter for item's is_private value
	 *
	 * @param bool $private
	 */
	public function set_is_private( $private ) {
		$this->is_private = (bool) $private;
	}

	/**
	 * Getter for item's source_id value.
	 *
	 * @return integer
	 */
	public function source_id() {
		return $this->source_id;
	}

	/**
	 * Getter for item's source_path value.
	 *
	 * @return string
	 */
	public function source_path() {
		return $this->source_path;
	}

	/**
	 * Getter for item's original_source_path value.
	 *
	 * @return string
	 */
	public function original_source_path() {
		return $this->original_source_path;
	}

	/**
	 * Getter for item's extra_info value.
	 *
	 * @return array
	 */
	public function extra_info() {
		return unserialize( $this->extra_info );
	}

	/**
	 * Setter for extra_info value
	 *
	 * @param array $extra_info
	 */
	protected function set_extra_info( $extra_info ) {
		$this->extra_info = serialize( $extra_info );
	}

	/**
	 * Getter for item's originator value.
	 *
	 * @return integer
	 */
	public function originator() {
		return $this->originator;
	}

	/**
	 * Getter for item's is_verified value.
	 *
	 * @return bool
	 */
	public function is_verified() {
		return (bool) $this->is_verified;
	}

	/**
	 * Setter for item's is_verified value
	 *
	 * @param bool $is_verified
	 */
	public function set_is_verified( $is_verified ) {
		$this->is_verified = (bool) $is_verified;
	}

	/**
	 * Get normalized object path dir.
	 *
	 * @return string
	 */
	public function normalized_path_dir() {
		$directory = dirname( $this->path );

		return ( '.' === $directory ) ? '' : AS3CF_Utils::trailingslash_prefix( $directory );
	}

	/**
	 * Get the first source id for a bucket and path.
	 *
	 * @param string $bucket
	 * @param string $path
	 *
	 * @return int|bool
	 */
	public static function get_source_id_by_bucket_and_path( $bucket, $path ) {
		global $wpdb;

		if ( empty( $bucket ) || empty( $path ) ) {
			return false;
		}

		$item = static::get_from_items_cache_by_bucket_and_path( $bucket, $path );

		if ( ! empty( $item ) ) {
			return $item->source_id();
		}

		$sql = $wpdb->prepare(
			"
				SELECT source_id FROM " . static::items_table() . "
				WHERE source_type = %s
				AND bucket = %s
				AND (path = %s OR original_path = %s)
				ORDER BY source_id LIMIT 1
			",
			static::$source_type,
			$bucket,
			$path,
			$path
		);

		$result = $wpdb->get_var( $sql );

		return empty( $result ) ? false : (int) $result;
	}

	/**
	 * Get the source id for a given remote URL.
	 *
	 * @param string $url
	 *
	 * @return int|bool
	 */
	public static function get_source_id_by_remote_url( $url ) {
		global $wpdb;

		/**
		 * @var Amazon_S3_And_CloudFront|\Amazon_S3_And_CloudFront_Pro $as3cf
		 */
		global $as3cf;

		$parts = AS3CF_Utils::parse_url( $url );
		$path  = AS3CF_Utils::decode_filename_in_path( ltrim( $parts['path'], '/' ) );

		// Remove the first directory to cater for bucket in path domain settings.
		if ( false !== strpos( $path, '/' ) ) {
			$path = explode( '/', $path );
			array_shift( $path );

			// If private prefix enabled, check if first segment and remove it as path/original_path do not include it.
			// We can't check every possible private prefix as each item may have a unique private prefix.
			// The only way to do that is with some fancy SQL, but that's not feasible as this particular
			// SQL query is already troublesome on some sites with badly behaved themes/plugins.
			if ( count( $path ) && $as3cf->get_delivery_provider()->use_signed_urls_key_file() ) {
				// We have to be able to handle multi-segment private prefixes such as "private/downloads/".
				$private_prefixes = explode( '/', untrailingslashit( $as3cf->get_setting( 'signed-urls-object-prefix' ) ) );

				foreach ( $private_prefixes as $private_prefix ) {
					if ( $private_prefix === $path[0] ) {
						array_shift( $path );
					} else {
						// As soon as we don't have a match stop looking.
						break;
					}
				}
			}

			$path = implode( '/', $path );
		}

		$sql = $wpdb->prepare(
			"SELECT * FROM " . static::items_table() . " WHERE source_type = %s AND (path LIKE %s OR original_path LIKE %s);"
			, static::$source_type
			, '%' . $path
			, '%' . $path
		);

		$results = $wpdb->get_results( $sql );

		// Nothing found, shortcut out.
		if ( 0 === count( $results ) ) {
			// TODO: If upgrade in progress, fallback to 'amazonS3_info' in Media_Library_Item override of this function.
			return false;
		}

		// Regardless of whether 1 or many items found, must validate match.
		$path = AS3CF_Utils::decode_filename_in_path( ltrim( $parts['path'], '/' ) );

		foreach ( $results as $result ) {
			$as3cf_item = static::create( $result );

			// If item's bucket matches first segment of URL path, remove it from URL path before checking match.
			if ( 0 === strpos( $path, trailingslashit( $as3cf_item->bucket() ) ) ) {
				$match_path = ltrim( substr_replace( $path, '', 0, strlen( $as3cf_item->bucket() ) ), '/' );
			} else {
				$match_path = $path;
			}

			// If item's private prefix matches first segment of URL path, remove it from URL path before checking match.
			if ( ! empty( $as3cf_item->private_prefix() ) && 0 === strpos( $match_path, $as3cf_item->private_prefix() ) ) {
				$match_path = ltrim( substr_replace( $match_path, '', 0, strlen( $as3cf_item->private_prefix() ) ), '/' );
			}

			// Exact match, return ID.
			if ( $as3cf_item->path() === $match_path || $as3cf_item->original_path() === $match_path ) {
				return $as3cf_item->source_id();
			}
		}

		return false;
	}

	/**
	 * Get an array of managed source_ids in descending order.
	 *
	 * While source id isn't strictly unique, it is by source type, which is always used in queries based on called class.
	 *
	 * @param integer $upper_bound Returned source_ids should be lower than this, use null/0 for no upper bound.
	 * @param integer $limit       Maximum number of source_ids to return. Required if not counting.
	 * @param bool    $count       Just return a count of matching source_ids? Negates $limit, default false.
	 * @param int     $originator  Optionally restrict to only records with given originator type from ORIGINATORS const.
	 * @param bool    $is_verified Optionally restrict to only records that either are or are not verified.
	 *
	 * @return array|int
	 */
	public static function get_source_ids( $upper_bound, $limit, $count = false, $originator = null, $is_verified = null ) {
		global $wpdb;

		$args = array( static::$source_type );

		if ( $count ) {
			$sql = 'SELECT COUNT(DISTINCT source_id)';
		} else {
			$sql = 'SELECT DISTINCT source_id';
		}

		$sql .= ' FROM ' . static::items_table() . ' WHERE source_type = %s';

		if ( ! empty( $upper_bound ) ) {
			$sql    .= ' AND source_id < %d';
			$args[] = $upper_bound;
		}

		// If an originator type given, check that it is valid before continuing and using.
		if ( null !== $originator ) {
			if ( is_int( $originator ) && in_array( $originator, self::ORIGINATORS ) ) {
				$sql    .= ' AND originator = %d';
				$args[] = $originator;
			} else {
				\AS3CF_Error::log( __METHOD__ . ' called with invalid originator: ' . $originator );

				return $count ? 0 : array();
			}
		}

		// If an is_verified value given, check that it is valid before continuing and using.
		if ( null !== $is_verified ) {
			if ( is_bool( $is_verified ) ) {
				$sql    .= ' AND is_verified = %d';
				$args[] = (int) $is_verified;
			} else {
				\AS3CF_Error::log( __METHOD__ . ' called with invalid is_verified: ' . $is_verified );

				return $count ? 0 : array();
			}
		}

		if ( ! $count ) {
			$sql    .= ' ORDER BY source_id DESC LIMIT %d';
			$args[] = $limit;
		}

		$sql = $wpdb->prepare( $sql, $args );

		if ( $count ) {
			return $wpdb->get_var( $sql );
		} else {
			return array_map( 'intval', $wpdb->get_col( $sql ) );
		}
	}

	/**
	 * Get an array of un-managed source_ids in descending order.
	 *
	 * While source id isn't strictly unique, it is by source type, which is always used in queries based on called class.
	 *
	 * @param integer $upper_bound Returned source_ids should be lower than this, use null/0 for no upper bound.
	 * @param integer $limit       Maximum number of source_ids to return. Required if not counting.
	 * @param bool    $count       Just return a count of matching source_ids? Negates $limit, default false.
	 *
	 * @return array|int
	 *
	 * NOTE: Must be overridden by subclass, only reason this is not abstract is because static is preferred.
	 */
	public static function get_missing_source_ids( $upper_bound, $limit, $count = false ) {
		if ( $count ) {
			return 0;
		} else {
			return array();
		}
	}

	/**
	 * Get absolute file paths associated with source item.
	 *
	 * @param integer $id
	 *
	 * @return array
	 */
	abstract protected function source_paths( $id );
}