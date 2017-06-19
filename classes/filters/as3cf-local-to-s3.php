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
		add_filter( 'customize_value_custom_css', array( $this, 'filter_customize_value_custom_css' ), 10, 2 );
		add_filter( 'wp_get_custom_css', array( $this, 'filter_wp_get_custom_css' ), 10, 2 );
		// Posts
		add_action( 'the_post', array( $this, 'filter_post_data' ) );
		add_filter( 'content_pagination', array( $this, 'filter_content_pagination' ) );
		add_filter( 'the_content', array( $this, 'filter_post' ), 100 );
		add_filter( 'the_excerpt', array( $this, 'filter_post' ), 100 );
		add_filter( 'content_edit_pre', array( $this, 'filter_post' ) );
		add_filter( 'excerpt_edit_pre', array( $this, 'filter_post' ) );
		add_filter( 'as3cf_filter_post_local_to_s3', array( $this, 'filter_post' ) );
		// Widgets
		add_filter( 'widget_form_callback', array( $this, 'filter_widget_display' ), 10, 2 );
		add_filter( 'widget_display_callback', array( $this, 'filter_widget_display' ), 10, 2 );
	}

	/**
	 * Filter customize value custom CSS.
	 *
	 * @param mixed                           $value
	 * @param WP_Customize_Custom_CSS_Setting $setting
	 *
	 * @return mixed
	 */
	public function filter_customize_value_custom_css( $value, $setting ) {
		return $this->filter_custom_css( $value, $setting->stylesheet );
	}

	/**
	 * Filter `wp_get_custom_css`.
	 *
	 * @param string $css
	 * @param string $stylesheet
	 *
	 * @return string
	 */
	public function filter_wp_get_custom_css( $css, $stylesheet ) {
		return $this->filter_custom_css( $css, $stylesheet );
	}

	/**
	 * Filter post data.
	 *
	 * @param WP_Post $post
	 */
	public function filter_post_data( $post ) {
		global $pages;

		$cache    = $this->get_post_cache( $post->ID );
		$to_cache = array();

		if ( 1 === count( $pages ) && ! empty( $pages[0] ) ) {
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
	 * Filter widget display.
	 *
	 * @param array     $instance
	 * @param WP_Widget $class
	 *
	 * @return array
	 */
	public function filter_widget_display( $instance, $class ) {
		return $this->handle_widget( $instance, $class );
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
		$base_url = $this->as3cf->maybe_fix_local_subsite_url( $uploads['baseurl'] );
		$base_url = AS3CF_Utils::remove_scheme( $base_url );

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

		$full_url = AS3CF_Utils::remove_scheme( AS3CF_Utils::remove_size_from_filename( $url ) );

		if ( isset( $this->query_cache[ $full_url ] ) ) {
			// ID already cached, return
			return $this->query_cache[ $full_url ];
		}

		$upload_dir = wp_upload_dir();
		$base_url   = AS3CF_Utils::remove_scheme( $upload_dir['baseurl'] );
		$path       = $this->as3cf->decode_filename_in_path( ltrim( str_replace( $base_url, '', $full_url ), '/' ) );

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
	 * Get attachment IDs from URLs.
	 *
	 * @param array $urls
	 *
	 * @return array url => attachment ID (or false)
	 */
	protected function get_attachment_ids_from_urls( $urls ) {
		global $wpdb;

		$results = array();

		if ( empty( $urls ) ) {
			return $results;
		}

		if ( ! is_array( $urls ) ) {
			$urls = array( $urls );
		}

		$upload_dir = wp_upload_dir();
		$base_url   = AS3CF_Utils::remove_scheme( $upload_dir['baseurl'] );

		$paths     = array();
		$full_urls = array();

		foreach ( $urls as $url ) {
			$full_url = AS3CF_Utils::remove_scheme( AS3CF_Utils::remove_size_from_filename( $url ) );

			if ( isset( $this->query_cache[ $full_url ] ) ) {
				// ID already cached, use it.
				$results[ $url ] = $this->query_cache[ $full_url ];

				continue;
			}

			$path = $this->as3cf->decode_filename_in_path( ltrim( str_replace( $base_url, '', $full_url ), '/' ) );

			$paths[ $path ]         = $full_url;
			$full_urls[ $full_url ] = $url;
			$meta_values[]          = "'" . esc_sql( $path ) . "'";
		}

		if ( ! empty( $meta_values ) ) {
			$sql = "
				SELECT post_id, meta_value FROM {$wpdb->postmeta}
				WHERE meta_key = '_wp_attached_file'
				AND meta_value IN ( " . implode( ',', $meta_values ) . " )
 		    ";

			$query_results = $wpdb->get_results( $sql );

			if ( ! empty( $query_results ) ) {
				foreach ( $query_results as $postmeta ) {
					$attachment_id                      = (int) $postmeta->post_id;
					$full_url                           = $paths[ $postmeta->meta_value ];
					$this->query_cache[ $full_url ]     = $attachment_id;
					$results[ $full_urls[ $full_url ] ] = $attachment_id;
				}

			}

			// No more attachment IDs found, set remaining results as false.
			if ( count( $urls ) !== count( $results ) ) {
				foreach ( $full_urls as $full_url => $url ) {
					if ( ! array_key_exists( $url, $results ) ) {
						$this->query_cache[ $full_url ] = false;
						$results[ $url ]                = false;
					}
				}
			}
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
		$uploads  = wp_upload_dir();
		$base_url = AS3CF_Utils::remove_scheme( $uploads['baseurl'] );

		return $this->remove_aws_query_strings( $content, $base_url );
	}
}
