<?php

use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;

class AS3CF_S3_To_Local extends AS3CF_Filter {

	/**
	 * Init.
	 */
	protected function init() {
		// EDD
		add_filter( 'edd_metabox_save_edd_download_files', array( $this, 'filter_edd_download_files' ) );
		// Customizer
		add_filter( 'pre_set_theme_mod_background_image', array( $this, 'filter_customizer_image' ), 10, 2 );
		add_filter( 'pre_set_theme_mod_header_image', array( $this, 'filter_customizer_image' ), 10, 2 );
		add_filter( 'pre_set_theme_mod_header_image_data', array( $this, 'filter_header_image_data' ), 10, 2 );
		add_filter( 'update_custom_css_data', array( $this, 'filter_update_custom_css_data' ), 10, 2 );
		// Posts
		add_filter( 'content_save_pre', array( $this, 'filter_post' ) );
		add_filter( 'excerpt_save_pre', array( $this, 'filter_post' ) );
		add_filter( 'as3cf_filter_post_s3_to_local', array( $this, 'filter_post' ) ); // Backwards compatibility
		add_filter( 'as3cf_filter_post_provider_to_local', array( $this, 'filter_post' ) );
		// Widgets
		add_filter( 'widget_update_callback', array( $this, 'filter_widget_save' ), 10, 4 );
		// Srcset handling
		add_filter( 'wp_image_file_matches_image_meta', array( $this, 'image_file_matches_image_meta' ), 10, 4 );
	}

	/**
	 * Filter update custom CSS data.
	 *
	 * @param array $data
	 * @param array $args
	 *
	 * @return array
	 */
	public function filter_update_custom_css_data( $data, $args ) {
		$data['css'] = $this->filter_custom_css( $data['css'], $args['stylesheet'] );

		return $data;
	}

	/**
	 * Filter widget on save.
	 *
	 * @param array     $instance
	 * @param array     $new_instance
	 * @param array     $old_instance
	 * @param WP_Widget $class
	 *
	 * @return array
	 *
	 */
	public function filter_widget_save( $instance, $new_instance, $old_instance, $class ) {
		return $this->handle_widget( $instance, $class );
	}

	/**
	 * Should filter content.
	 *
	 * @return bool
	 */
	protected function should_filter_content() {
		return true;
	}

	/**
	 * Does URL need replacing?
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public function url_needs_replacing( $url ) {
		if ( str_replace( $this->get_bare_upload_base_urls(), '', $url ) !== $url ) {
			// Local URL, no replacement needed.
			return false;
		}

		if ( str_replace( $this->get_remote_domains(), '', $url ) === $url ) {
			// Not a known remote URL, no replacement needed.
			return false;
		}

		// Remote URL, perform replacement
		return true;
	}

	/**
	 * Get URL
	 *
	 * @param int         $attachment_id
	 * @param null|string $size
	 *
	 * @return bool|string
	 */
	protected function get_url( $attachment_id, $size = null ) {
		return $this->as3cf->get_attachment_local_url_size( $attachment_id, $size );
	}

	/**
	 * Get base URL.
	 *
	 * @param int $attachment_id
	 *
	 * @return string|false
	 */
	protected function get_base_url( $attachment_id ) {
		return $this->as3cf->get_attachment_url( $attachment_id );
	}

	/**
	 * Get attachment ID from URL.
	 *
	 * @param string $url
	 *
	 * @return bool|int
	 */
	public function get_attachment_id_from_url( $url ) {
		// Result for sized URL already cached in request, return it.
		if ( isset( $this->query_cache[ $url ] ) ) {
			return $this->query_cache[ $url ];
		}

		$post_id = Media_Library_Item::get_source_id_by_remote_url( $url );

		if ( $post_id ) {
			$this->query_cache[ $url ] = $post_id;

			return $post_id;
		}

		$full_url = AS3CF_Utils::remove_size_from_filename( $url );

		// If we've already tried to find this URL above because it didn't have a size suffix, cache and return.
		if ( $url === $full_url ) {
			$this->query_cache[ $url ] = $post_id;

			return $post_id;
		}

		// Result for URL already cached in request whether found or not, return it.
		if ( isset( $this->query_cache[ $full_url ] ) ) {
			return $this->query_cache[ $full_url ];
		}

		$post_id = Media_Library_Item::get_source_id_by_remote_url( $full_url );

		$this->query_cache[ $full_url ] = $post_id;

		return $post_id;
	}

	/**
	 * Get attachment IDs from URLs.
	 *
	 * @param array $urls
	 *
	 * @return array url => attachment ID (or false)
	 */
	protected function get_attachment_ids_from_urls( $urls ) {
		$results = array();

		if ( empty( $urls ) ) {
			return $results;
		}

		if ( ! is_array( $urls ) ) {
			$urls = array( $urls );
		}

		foreach ( $urls as $url ) {
			$results[ $url ] = $this->get_attachment_id_from_url( $url );
		}

		return $results;
	}

	/**
	 * Normalize find value.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	protected function normalize_find_value( $url ) {
		return AS3CF_Utils::encode_filename_in_path( $url );
	}

	/**
	 * Normalize replace value.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	protected function normalize_replace_value( $url ) {
		return AS3CF_Utils::decode_filename_in_path( $url );
	}

	/**
	 * Post process content.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	protected function post_process_content( $content ) {
		return $this->remove_aws_query_strings( $content );
	}

	/**
	 * Pre replace content.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	protected function pre_replace_content( $content ) {
		return $content;
	}

	/**
	 * Determines if the image meta data is for the image source file.
	 *
	 * @handles wp_image_file_matches_image_meta
	 *
	 * @param bool   $match
	 * @param string $image_location
	 * @param array  $image_meta
	 * @param int    $attachment_id
	 *
	 * @return bool
	 */
	public function image_file_matches_image_meta( $match, $image_location, $image_meta, $attachment_id ) {
		// If already matched or the URL is local, there's nothing for us to do.
		if ( $match || ! $this->url_needs_replacing( $image_location ) ) {
			return $match;
		}

		return $this->attachment_id_matches_src( $attachment_id, $image_location );
	}
}
