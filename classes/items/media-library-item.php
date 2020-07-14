<?php

namespace DeliciousBrains\WP_Offload_Media\Items;

use Amazon_S3_And_CloudFront;
use WP_Error;
use AS3CF_Utils;

class Media_Library_Item extends Item {
	private static $attachment_counts = array();
	private static $attachment_count_skips = array();

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
	 * @param array  $extra_info        An optional associative array of extra data to be associated with the item.
	 *                                  Recognised keys:
	 *                                  'private_sizes' => ['thumbnail', 'medium', ...]
	 *                                  'private_prefix' => 'private/'
	 *                                  For backwards compatibility, if a simple array is supplied it is treated as
	 *                                  private thumbnail sizes that should be private objects in the bucket.
	 * @param null   $id                Optional Item record ID.
	 */
	public function __construct( $provider, $region, $bucket, $path, $is_private, $source_id, $source_path, $original_filename = null, $extra_info = array(), $id = null ) {
		// For Media Library items, the source path should be relative to the Media Library's uploads directory.
		$uploads = wp_upload_dir();

		if ( false === $uploads['error'] && 0 === strpos( $source_path, $uploads['basedir'] ) ) {
			$source_path = AS3CF_Utils::unleadingslashit( substr( $source_path, strlen( $uploads['basedir'] ) ) );
		}

		$private_sizes  = array();
		$private_prefix = '';

		// Ensure re-hydration is clean.
		if ( ! empty( $extra_info ) && is_array( $extra_info ) ) {
			if ( isset( $extra_info['private_sizes'] ) ) {
				$private_sizes = $extra_info['private_sizes'];
			}
			if ( isset( $extra_info['private_prefix'] ) ) {
				$private_prefix = $extra_info['private_prefix'];
			}

			// Compatibility fallback for if just an array of private sizes is supplied.
			if ( ! isset( $extra_info['private_sizes'] ) && ! isset( $extra_info['private_prefix'] ) ) {
				$private_sizes = $extra_info;
			}
		}

		$extra_info = array(
			'private_sizes'  => $private_sizes,
			'private_prefix' => $private_prefix,
		);

		parent::__construct( $provider, $region, $bucket, $path, $is_private, $source_id, $source_path, $original_filename, $extra_info, $id );
	}

	/**
	 * (Re)initialize the static cache used for speeding up queries.
	 */
	public static function init_cache() {
		parent::init_cache();

		self::$attachment_counts      = array();
		self::$attachment_count_skips = array();
	}

	/**
	 * Get the item based on source id.
	 *
	 * @param integer $source_id
	 *
	 * @return bool|Media_Library_Item
	 */
	public static function get_by_source_id( $source_id ) {
		$as3cf_item = parent::get_by_source_id( $source_id );

		if ( ! $as3cf_item ) {
			$provider_object = static::_legacy_get_attachment_provider_info( $source_id );

			if ( is_array( $provider_object ) ) {
				$as3cf_item = static::_legacy_provider_info_to_item( $source_id, $provider_object );
			}
		}

		return $as3cf_item;
	}

	/**
	 * Full key (path) for given file that belongs to offloaded attachment.
	 *
	 * If no filename given, full sized path returned.
	 * Path is prepended with private prefix if size associated with filename is private,
	 * and a private prefix has been assigned to offload.
	 *
	 * @param string|null $filename
	 *
	 * @return string
	 */
	public function key( $filename = null ) {
		// Public full path.
		if ( empty( $filename ) && empty( $this->private_prefix() ) ) {
			return parent::path();
		}

		if ( empty( $filename ) ) {
			$filename = wp_basename( parent::path() );
		}

		if ( ! empty( $this->private_prefix() ) ) {
			$size = \AS3CF_Utils::get_intermediate_size_from_filename( $this->source_id(), $filename );

			// Private path.
			if ( $this->is_private_size( $size ) ) {
				return $this->private_prefix() . $this->normalized_path_dir() . $filename;
			}
		}

		// Public path.
		return $this->normalized_path_dir() . $filename;
	}

	/**
	 * Get absolute file paths associated with source item.
	 *
	 * @param integer $id
	 *
	 * @return array
	 */
	protected function source_paths( $id ) {
		$paths = array();

		return $paths;
	}

	/**
	 * Get the array of thumbnail sizes that are private in the bucket.
	 *
	 * @return array
	 */
	public function private_sizes() {
		$extra_info = $this->extra_info();

		if ( ! empty( $extra_info['private_sizes'] ) ) {
			// There was an issue with class re-hydration that meant empty private sizes embedded itself inside its key.
			if (
				isset( $extra_info['private_sizes']['private_sizes'] ) &&
				is_array( $extra_info['private_sizes']['private_sizes'] ) &&
				empty( $extra_info['private_sizes']['private_sizes'] )
			) {
				unset( $extra_info['private_sizes']['private_sizes'] );
			}

			return $extra_info['private_sizes'];
		}

		return array();
	}

	/**
	 * Get the private status for a specific size.
	 *
	 * @param string $size
	 *
	 * @return bool
	 */
	public function is_private_size( $size ) {
		if ( empty( $size ) || in_array( $size, array( 'full', 'original' ) ) ) {
			return $this->is_private();
		}

		return in_array( $size, $this->private_sizes() );
	}

	/**
	 * Get the private prefix for attachment's private objects.
	 *
	 * @return string
	 */
	public function private_prefix() {
		$extra_info = $this->extra_info();

		if ( ! empty( $extra_info['private_prefix'] ) ) {
			return \AS3CF_Utils::trailingslash_prefix( $extra_info['private_prefix'] );
		}

		return '';
	}

	/**
	 * Count attachments on current site.
	 *
	 * @param bool $skip_transient Whether to force database query and skip transient, default false
	 * @param bool $force          Whether to force database query and skip static cache, implies $skip_transient, default false
	 *
	 * @return array Keys:
	 *               total: Total media count for site (current blog id)
	 *               offloaded: Count of offloaded media for site (current blog id)
	 *               not_offloaded: Difference between total and offloaded
	 */
	public static function count_attachments( $skip_transient = false, $force = false ) {
		global $wpdb;

		$transient_key = 'as3cf_' . get_current_blog_id() . '_attachment_counts';

		// Been here, done it, won't do it again!
		// Well, unless this is the first transient skip for the prefix, then we need to do it.
		if ( ! $force && ! empty( self::$attachment_counts[ $transient_key ] ) && ( false === $skip_transient || ! empty( self::$attachment_count_skips[ $transient_key ] ) ) ) {
			return self::$attachment_counts[ $transient_key ];
		}

		if ( $force || $skip_transient || false === ( $result = get_site_transient( $transient_key ) ) ) {
			// We want to count distinct relative Media Library paths
			// and ensure type is also attachment as other post types can use the same _wp_attached_file postmeta key.
			$sql = "
				SELECT COUNT(DISTINCT p.`ID`) total, COUNT(DISTINCT i.`id`) offloaded
				FROM " . $wpdb->posts . " AS p
				STRAIGHT_JOIN " . $wpdb->postmeta . " AS m ON p.ID = m.post_id AND m.`meta_key` = '_wp_attached_file'
				LEFT OUTER JOIN " . static::items_table() . " AS i ON p.`ID` = i.`source_id` AND i.`source_type` = 'media-library'
				WHERE p.`post_type` = 'attachment'
			";

			$result = $wpdb->get_row( $sql, ARRAY_A );

			$result['not_offloaded'] = max( $result['total'] - $result['offloaded'], 0 );

			ksort( $result );

			set_site_transient( $transient_key, $result, 5 * MINUTE_IN_SECONDS );

			// One way or another we've skipped the transient.
			self::$attachment_count_skips[ $transient_key ] = true;
		}

		self::$attachment_counts[ $transient_key ] = $result;

		return $result;
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
	 */
	public static function get_missing_source_ids( $upper_bound, $limit, $count = false ) {
		global $wpdb;

		$args = array( static::$source_type );

		if ( $count ) {
			$sql = 'SELECT COUNT(DISTINCT posts.ID)';
		} else {
			$sql = 'SELECT DISTINCT posts.ID';
		}

		$sql .= "
			FROM {$wpdb->prefix}posts AS posts
			WHERE posts.post_type = 'attachment'
			AND posts.ID NOT IN (
			    SELECT items.source_id
				FROM " . static::items_table() . " AS items
				WHERE items.source_type = %s
				AND items.source_id = posts.ID
			)
		";

		if ( ! empty( $upper_bound ) ) {
			$sql    .= ' AND posts.ID < %d';
			$args[] = $upper_bound;
		}

		/**
		 * Allow users to exclude certain MIME types from attachments to upload.
		 *
		 * @param array
		 */
		$ignored_mime_types = apply_filters( 'as3cf_ignored_mime_types', array() );
		if ( is_array( $ignored_mime_types ) && ! empty( $ignored_mime_types ) ) {
			$ignored_mime_types = array_map( 'sanitize_text_field', $ignored_mime_types );
			$sql                .= " AND posts.post_mime_type NOT IN ('" . implode( "','", $ignored_mime_types ) . "')";
		}

		if ( ! $count ) {
			$sql    .= ' ORDER BY posts.ID DESC LIMIT %d';
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
	 * Search for all items that have the source path(s).
	 *
	 * @param array|string $paths              Array of relative source paths.
	 * @param array|int    $exclude_source_ids Array of source_ids to exclude from search. Default, none.
	 * @param bool         $exact_match        Use paths as supplied (true, default), or greedy match on path without extension (e.g. find edited too).
	 * @param bool         $first_only         Only return first matched item sorted by source_id. Default false.
	 *
	 * @return array
	 */
	public static function get_by_source_path( $paths, $exclude_source_ids = array(), $exact_match = true, $first_only = false ) {
		global $wpdb;

		if ( ! is_array( $paths ) && is_string( $paths ) && ! empty( $paths ) ) {
			$paths = array( $paths );
		}

		if ( ! is_array( $paths ) || empty( $paths ) ) {
			return array();
		}

		$paths = \AS3CF_Utils::make_upload_file_paths_relative( $paths );

		$args = array( static::$source_type );

		$sql = '
			SELECT DISTINCT items.*
			FROM ' . static::items_table() . ' AS items USE INDEX (uidx_source_path, uidx_original_source_path)
			WHERE items.source_type = %s
		';

		if ( ! empty( $exclude_source_ids ) ) {
			if ( ! is_array( $exclude_source_ids ) ) {
				$exclude_source_ids = array( $exclude_source_ids );
			}

			$sql .= ' AND items.source_id NOT IN (' . join( ',', $exclude_source_ids ) . ')';
		}

		if ( $exact_match ) {
			$sql .= " AND (items.source_path IN ('" . join( "','", $paths ) . "')";
			$sql .= " OR items.original_source_path IN ('" . join( "','", $paths ) . "'))";
		} else {
			$likes = array_map( function ( $path ) {
				$ext  = '.' . pathinfo( $path, PATHINFO_EXTENSION );
				$path = substr_replace( $path, '%', -strlen( $ext ) );

				return "items.source_path LIKE '" . $path . "' OR items.original_source_path LIKE '" . $path . "'";
			}, $paths );

			$sql .= ' AND (' . join( ' OR ', $likes ) . ')';
		}

		if ( $first_only ) {
			$sql .= ' ORDER BY items.source_id LIMIT 1';
		}

		$sql = $wpdb->prepare( $sql, $args );

		return array_map( 'static::create', $wpdb->get_results( $sql ) );
	}

	/**
	 * Finds Media Library items with same source_path and sets them as offloaded.
	 */
	public function offload_duplicate_items() {
		global $wpdb;

		$sql = $wpdb->prepare(
			"
				SELECT m.post_id
				FROM " . $wpdb->postmeta . " AS m
				LEFT JOIN " . $wpdb->posts . " AS p ON m.post_id = p.ID AND p.`post_type` = 'attachment'
				WHERE m.meta_key = '_wp_attached_file'
				AND m.meta_value = %s
				AND m.post_id != %d
				AND m.post_id NOT IN (
					SELECT i.source_id
					FROM " . static::items_table() . " AS i
					WHERE i.source_type = %s
					AND i.source_id = m.post_id
				)
				;
			"
			, $this->source_path()
			, $this->source_id()
			, static::$source_type
		);

		$results = $wpdb->get_results( $sql );

		// Nothing found, shortcut out.
		if ( 0 === count( $results ) ) {
			return;
		}

		foreach ( $results as $result ) {
			$as3cf_item = new Media_Library_Item(
				$this->provider(),
				$this->region(),
				$this->bucket(),
				$this->path(),
				$this->is_private(),
				$result->post_id,
				$this->source_path(),
				wp_basename( $this->original_source_path() ),
				$this->extra_info()
			);
			$as3cf_item->save();
		}
	}

	/*
	 * >>> LEGACY ROUTINES BEGIN >>>
	 */

	/**
	 * Convert the provider info array for an attachment to item object.
	 *
	 * @param integer $source_id
	 * @param array   $provider_info
	 *
	 * @return bool|Media_Library_Item
	 */
	private static function _legacy_provider_info_to_item( $source_id, $provider_info ) {
		$attached_file = get_post_meta( $source_id, '_wp_attached_file', true );

		if ( is_string( $attached_file ) && ! empty( $attached_file ) ) {
			$private_sizes = array();

			if ( ! empty( $provider_info['sizes'] ) && is_array( $provider_info['sizes'] ) ) {
				$private_sizes = array_keys( $provider_info['sizes'] );
			}

			return new static(
				$provider_info['provider'],
				$provider_info['region'],
				$provider_info['bucket'],
				$provider_info['key'],
				isset( $provider_info['acl'] ) && false !== strpos( $provider_info['acl'], 'private' ) ? true : false,
				$source_id,
				$attached_file,
				wp_basename( $attached_file ),
				$private_sizes
			);
		}

		return false;
	}

	/**
	 * Get attachment provider info
	 *
	 * @param int $post_id
	 *
	 * @return bool|array
	 */
	private static function _legacy_get_attachment_provider_info( $post_id ) {
		$provider_object = get_post_meta( $post_id, 'amazonS3_info', true );

		if ( ! empty( $provider_object ) && is_array( $provider_object ) && ! empty( $provider_object['bucket'] ) && ! empty( $provider_object['key'] ) ) {
			$provider_object = array_merge( array(
				'provider' => Amazon_S3_And_CloudFront::get_default_storage_provider(),
			), $provider_object );
		} else {
			return false;
		}

		$provider_object['region'] = static::_legacy_get_provider_object_region( $provider_object );

		if ( is_wp_error( $provider_object['region'] ) ) {
			return false;
		}

		$provider_object = apply_filters( 'as3cf_get_attachment_s3_info', $provider_object, $post_id ); // Backwards compatibility

		return apply_filters( 'as3cf_get_attachment_provider_info', $provider_object, $post_id );
	}

	/**
	 * Get the region of the bucket stored in the provider metadata.
	 *
	 * @param array $provider_object
	 *
	 * @return string|WP_Error - region name
	 */
	private static function _legacy_get_provider_object_region( $provider_object ) {
		if ( ! isset( $provider_object['region'] ) ) {
			/** @var Amazon_S3_And_CloudFront $as3cf */
			global $as3cf;

			// If region hasn't been stored in the provider metadata retrieve using the bucket.
			$region = $as3cf->get_bucket_region( $provider_object['bucket'], true );

			// Could just return $region here regardless, but this format is good for debug during legacy migration.
			if ( is_wp_error( $region ) ) {
				return $region;
			}

			$provider_object['region'] = $region;
		}

		return $provider_object['region'];
	}

	/*
	 * <<< LEGACY ROUTINES END <<<
	 */
}