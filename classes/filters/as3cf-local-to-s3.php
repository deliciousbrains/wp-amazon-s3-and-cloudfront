<?php

class AS3CF_Local_To_S3 extends AS3CF_Filter {

	/**
	 * Init.
	 */
	protected function init() {
		// EDD
		add_filter( 'edd_download_files', array( $this, 'filter_edd_download_files' ) );
		// Customizer
		add_filter( 'theme_mod_background_image', array( $this, 'filter_customizer_image' ) );
		add_filter( 'theme_mod_header_image', array( $this, 'filter_customizer_image' ) );
		// Posts
		add_action( 'the_post', array( $this, 'filter_post_data' ) );
		add_filter( 'content_pagination', array( $this, 'filter_content_pagination' ) );
		add_filter( 'the_content', array( $this, 'filter_post' ), 100 );
		add_filter( 'the_excerpt', array( $this, 'filter_post' ), 100 );
		add_filter( 'content_edit_pre', array( $this, 'filter_post' ) );
		add_filter( 'excerpt_edit_pre', array( $this, 'filter_post' ) );
		// Widgets
		add_filter( 'widget_text', array( $this, 'filter_widget' ) );
		add_filter( 'widget_form_callback', array( $this, 'filter_widget_form' ), 10, 2 );
	}

	/**
	 * Filter post data.
	 *
	 * @param WP_Post $post
	 */
	public function filter_post_data( $post ) {
		global $pages;

		$cache    = $this->get_post_cache();
		$to_cache = array();

		if ( count( $pages ) === 1 ) {
			// Post already filtered and available on global $page array, continue
			$post->post_content = $pages[0];
		} else {
			$post->post_content = $this->process_content( $post->post_content, $cache, $to_cache );
		}

		$post->post_excerpt = $this->process_content( $post->post_excerpt, $cache, $to_cache );

		$this->maybe_update_post_cache( $to_cache );
	}

	/**
	 * Filter content pagination.
	 *
	 * @param array $pages
	 *
	 * @return array
	 */
	public function filter_content_pagination( $pages ) {
		$cache    = $this->get_post_cache();
		$to_cache = array();

		foreach ( $pages as $key => $page ) {
			$pages[ $key ] = $this->process_content( $page, $cache, $to_cache );
		}

		$this->maybe_update_post_cache( $to_cache );

		return $pages;
	}

	/**
	 * Filter widget.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	public function filter_widget( $content ) {
		$cache    = $this->get_option_cache();
		$to_cache = array();
		$content  = $this->process_content( $content, $cache, $to_cache );

		$this->maybe_update_option_cache( $to_cache );

		return $content;
	}

	/**
	 * Filter widget form.
	 *
	 * @param array     $instance
	 * @param WP_Widget $class
	 *
	 * @return string
	 */
	public function filter_widget_form( $instance, $class ) {
		if ( ! is_a( $class, 'WP_Widget_Text' ) || empty( $instance ) ) {
			return $instance;
		}

		$cache            = $this->get_option_cache();
		$to_cache         = array();
		$instance['text'] = $this->process_content( $instance['text'], $cache, $to_cache );

		$this->maybe_update_option_cache( $to_cache );

		return $instance;
	}

	/**
	 * Does URL need replacing?
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	protected function url_needs_replacing( $url ) {
		$uploads  = wp_upload_dir();
		$base_url = $this->as3cf->remove_scheme( $uploads['baseurl'] );

		if ( false !== strpos( $url, $base_url ) ) {
			// Local URL, perform replacement
			return true;
		}

		// Remote URL, no replacement needed
		return false;
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
		return $this->as3cf->get_attachment_url( $attachment_id, null, $size );
	}

	/**
	 * Get base URL.
	 *
	 * @param int $attachment_id
	 *
	 * @return string|false
	 */
	protected function get_base_url( $attachment_id ) {
		return $this->as3cf->get_attachment_local_url( $attachment_id );
	}

	/**
	 * Get attachment ID from URL.
	 *
	 * @param string $url
	 *
	 * @return bool|int
	 */
	protected function get_attachment_id_from_url( $url ) {
		global $wpdb;

		$upload_dir = wp_upload_dir();
		$base_url   = $this->as3cf->remove_scheme( $upload_dir['baseurl'] );
		$full_url   = $this->as3cf->remove_scheme( $this->as3cf->remove_size_from_filename( $url ) );
		$path       = $this->as3cf->decode_filename_in_path( ltrim( str_replace( $base_url, '', $full_url ), '/' ) );

		if ( isset( $this->query_cache[ $full_url ] ) ) {
			// ID already cached, return
			return $this->query_cache[ $full_url ];
		}

		$sql = $wpdb->prepare( "
 		    SELECT post_id FROM {$wpdb->postmeta}
 		    WHERE meta_key = %s
 		    AND meta_value = %s
 		", '_wp_attached_file', $path );

		$result = $wpdb->get_var( $sql );

		if ( is_null( $result ) ) {
			// Attachment ID not found, return false
			$this->query_cache[ $full_url ] = false;

			return false;
		}

		$this->query_cache[ $full_url ] = (int) $result;

		return (int) $result;
	}

	/**
	 * Normalize find value.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	protected function normalize_find_value( $url ) {
		return $this->as3cf->decode_filename_in_path( $url );
	}

	/**
	 * Normalize replace value.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	protected function normalize_replace_value( $url ) {
		return $this->as3cf->encode_filename_in_path( $url );
	}

	/**
	 * Post process content.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	protected function post_process_content( $content ) {
		return $content;
	}

	/**
	 * Pre replace content.
	 *
	 * @param string $content
	 *
	 * @return string
	 */
	protected function pre_replace_content( $content ) {
		return $this->remove_aws_query_strings( $content );
	}
}