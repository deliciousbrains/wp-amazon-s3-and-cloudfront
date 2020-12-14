<?php

use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;

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
		add_filter( 'rss_enclosure', array( $this, 'filter_post' ), 100 );
		add_filter( 'content_edit_pre', array( $this, 'filter_post' ) );
		add_filter( 'excerpt_edit_pre', array( $this, 'filter_post' ) );
		add_filter( 'as3cf_filter_post_local_to_s3', array( $this, 'filter_post' ) ); // Backwards compatibility
		add_filter( 'as3cf_filter_post_local_to_provider', array( $this, 'filter_post' ) );
		// Widgets
		add_filter( 'widget_form_callback', array( $this, 'filter_widget_display' ), 10, 2 );
		add_filter( 'widget_display_callback', array( $this, 'filter_widget_display' ), 10, 2 );
		// Edit Media page
		add_filter( 'set_url_scheme', array( $this, 'set_url_scheme' ), 10, 3 );
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

		if ( is_array( $pages ) && 1 === count( $pages ) && ! empty( $pages[0] ) ) {
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
	public function url_needs_replacing( $url ) {
		if ( str_replace( $this->get_bare_upload_base_urls(), '', $url ) === $url ) {
			// Remote URL, no replacement needed
			return false;
		}

		// Local URL, perform replacement
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
	public function get_attachment_id_from_url( $url ) {
		$results = $this->get_attachment_ids_from_urls( array( $url ) );

		if ( empty( $results ) ) {
			return false;
		}

		foreach ( $results as $result ) {
			if ( $result ) {
				return $result;
			}
		}

		return false;
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

		$query_set = array();
		$paths     = array();
		$full_urls = array();

		// Quickly parse given URLs to add versions without size as we should lookup with size info first as that could be the "full" size.
		foreach ( $urls as $url ) {
			$query_set[]  = $url;
			$size_removed = AS3CF_Utils::remove_size_from_filename( $url );

			if ( $url !== $size_removed ) {
				$query_set[] = $size_removed;
			}
		}

		foreach ( $query_set as $url ) {
			// Path to search for in query set should be based on bare URL.
			$bare_url = AS3CF_Utils::remove_scheme( $url );
			// There can be multiple URLs in the query set that belong to the same full URL for the Media Library item.
			$full_url = AS3CF_Utils::remove_size_from_filename( $bare_url );

			if ( isset( $this->query_cache[ $full_url ] ) ) {
				// ID already cached, use it.
				$results[ $url ] = $this->query_cache[ $full_url ];

				continue;
			}

			$path = AS3CF_Utils::decode_filename_in_path( ltrim( str_replace( $this->get_bare_upload_base_urls(), '', $bare_url ), '/' ) );

			$paths[ $path ]           = $full_url;
			$full_urls[ $full_url ][] = $url;
		}

		if ( ! empty( $paths ) ) {
			$as3cf_items = Media_Library_Item::get_by_source_path( array_keys( $paths ) );

			if ( ! empty( $as3cf_items ) ) {
				/* @var Media_Library_Item $as3cf_item */
				foreach ( $as3cf_items as $as3cf_item ) {
					// Each returned item may have matched on either the source_path or original_source_path.
					// Because the base image file name of a thumbnail might match the original rather scaled or rotated full image
					// it's possible that both source paths are used by separate URLs.
					foreach ( array( $as3cf_item->source_path(), $as3cf_item->original_source_path() ) as $source_path ) {
						if ( ! empty( $paths[ $source_path ] ) ) {
							$matched_full_url = $paths[ $source_path ];

							if ( ! empty( $full_urls[ $matched_full_url ] ) ) {
								$attachment_id                          = $as3cf_item->source_id();
								$this->query_cache[ $matched_full_url ] = $attachment_id;

								foreach ( $full_urls[ $matched_full_url ] as $url ) {
									$results[ $url ] = $attachment_id;
								}
								unset( $full_urls[ $matched_full_url ] );
							}
						}
					}
				}
			}

			// No more attachment IDs found, set remaining results as false.
			if ( count( $query_set ) !== count( $results ) ) {
				foreach ( $full_urls as $full_url => $schema_urls ) {
					foreach ( $schema_urls as $url ) {
						if ( ! array_key_exists( $url, $results ) ) {
							$this->query_cache[ $full_url ] = false;
							$results[ $url ]                = false;
						}
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
		return AS3CF_Utils::decode_filename_in_path( $url );
	}

	/**
	 * Normalize replace value.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	protected function normalize_replace_value( $url ) {
		return AS3CF_Utils::encode_filename_in_path( $url );
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

	/**
	 * Each time a URL is replaced this function is called to allow for logging or further updates etc.
	 *
	 * @param string $find    URL with no scheme.
	 * @param string $replace URL with no scheme.
	 * @param string $content
	 *
	 * @return string
	 */
	protected function url_replaced( $find, $replace, $content ) {
		if ( (bool) $this->as3cf->get_setting( 'force-https' ) ) {
			$content = str_replace( 'http:' . $replace, 'https:' . $replace, $content );
		}

		return $content;
	}

	/**
	 * Might need to re-fix remote URL's schema if WordPress core has substituted in HTTP but HTTPS is required.
	 *
	 * @param string $url
	 * @param string $scheme
	 * @param string $orig_scheme
	 *
	 * @return string
	 */
	public function set_url_scheme( $url, $scheme, $orig_scheme ) {
		if (
			'http' === $scheme && empty( $orig_scheme ) &&
			$this->as3cf->get_setting( 'force-https' ) &&
			$this->should_filter_content() &&
			! $this->url_needs_replacing( $url )
		) {
			// Check that it's one of ours and not external.
			$parts = AS3CF_Utils::parse_url( $url );

			if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) || 'http' !== $parts['scheme'] ) {
				return $url;
			}

			$ours = false;
			if ( $this->as3cf->get_setting( 'enable-delivery-domain' ) && $this->as3cf->get_setting( 'delivery-domain', '' ) === $parts['host'] ) {
				$ours = true;
			} elseif ( false !== strpos( $parts['host'], $this->as3cf->get_storage_provider()->get_domain() ) ) {
				$ours = true;
			}

			if ( $ours ) {
				return substr_replace( $url, 'https', 0, 4 );
			}
		}

		return $url;
	}
}
