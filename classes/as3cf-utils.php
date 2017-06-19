<?php
/**
 * Plugin Utilities
 *
 * @package     amazon-s3-and-cloudfront
 * @subpackage  Classes/Utils
 * @copyright   Copyright (c) 2015, Delicious Brains
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'AS3CF_Utils' ) ) {

	/**
	 * AS3CF_Utils Class
	 *
	 * This class contains utility functions that need to be available
	 * across the Pro plugin codebase
	 *
	 */
	class AS3CF_Utils {
		/**
		 * Get post ID.
		 *
		 * @param null|int|WP_Post $post    Optional. Post ID or post object. Defaults to current post.
		 *
		 * @return int
		 */
		public static function get_post_id( $post = null ) {
			return (int) get_post_field( 'ID', $post );
		}

		/**
		 * Checks if another version of WP Offload S3 (Lite) is active and deactivates it.
		 * To be hooked on `activated_plugin` so other plugin is deactivated when current plugin is activated.
		 *
		 * @param string $plugin
		 *
		 * @return bool
		 */
		public static function deactivate_other_instances( $plugin ) {
			if ( ! in_array( basename( $plugin ), array( 'amazon-s3-and-cloudfront-pro.php', 'wordpress-s3.php' ) ) ) {
				return false;
			}

			$plugin_to_deactivate             = 'wordpress-s3.php';
			$deactivated_notice_id            = '1';
			$activated_plugin_min_version     = '1.1';
			$plugin_to_deactivate_min_version = '1.0';
			if ( basename( $plugin ) === $plugin_to_deactivate ) {
				$plugin_to_deactivate             = 'amazon-s3-and-cloudfront-pro.php';
				$deactivated_notice_id            = '2';
				$activated_plugin_min_version     = '1.0';
				$plugin_to_deactivate_min_version = '1.1';
			}

			$version = self::get_plugin_version_from_basename( $plugin );

			if ( version_compare( $version, $activated_plugin_min_version, '<' ) ) {
				return false;
			}

			if ( is_multisite() ) {
				$active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
				$active_plugins = array_keys( $active_plugins );
			} else {
				$active_plugins = (array) get_option( 'active_plugins', array() );
			}

			foreach ( $active_plugins as $basename ) {
				if ( false !== strpos( $basename, $plugin_to_deactivate ) ) {
					$version = self::get_plugin_version_from_basename( $basename );

					if ( version_compare( $version, $plugin_to_deactivate_min_version, '<' ) ) {
						return false;
					}

					set_transient( 'as3cf_deactivated_notice_id', $deactivated_notice_id, HOUR_IN_SECONDS );
					deactivate_plugins( $basename );

					return true;
				}
			}

			return false;
		}

		/**
		 * Get plugin data from basename
		 *
		 * @param string $basename
		 *
		 * @return string
		 */
		public static function get_plugin_version_from_basename( $basename ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			$plugin_path = WP_PLUGIN_DIR . '/' . $basename;
			$plugin_data = get_plugin_data( $plugin_path );

			return $plugin_data['Version'];
		}

		/**
		 * Trailing slash prefix string ensuring no leading slashes.
		 *
		 * @param $string
		 *
		 * @return string
		 */
		public static function trailingslash_prefix( $string ) {
			return ltrim( trailingslashit( $string ), '/\\' );
		}

		/**
		 * Remove scheme from URL.
		 *
		 * @param string $url
		 *
		 * @return string
		 */
		public static function remove_scheme( $url ) {
			return preg_replace( '/^(?:http|https):/', '', $url );
		}

		/**
		 * Remove size from filename (image[-100x100].jpeg).
		 *
		 * @param string $url
		 * @param bool   $remove_extension
		 *
		 * @return string
		 */
		public static function remove_size_from_filename( $url, $remove_extension = false ) {
			$url = preg_replace( '/^(\S+)-[0-9]{1,4}x[0-9]{1,4}(\.[a-zA-Z0-9\.]{2,})?/', '$1$2', $url );

			if ( $remove_extension ) {
				$ext = pathinfo( $url, PATHINFO_EXTENSION );
				$url = str_replace( ".$ext", '', $url );
			}

			return $url;
		}

		/**
		 * Reduce the given URL down to the simplest version of itself.
		 *
		 * Useful for matching against the full version of the URL in a full-text search
		 * or saving as a key for dictionary type lookup.
		 *
		 * @param string $url
		 *
		 * @return string
		 */
		public static function reduce_url( $url ) {
			$parts = self::parse_url( $url );
			$host  = isset( $parts['host'] ) ? $parts['host'] : '';
			$port  = isset( $parts['port'] ) ? ":{$parts['port']}" : '';
			$path  = isset( $parts['path'] ) ? $parts['path'] : '';

			return '//' . $host . $port . $path;
		}

		/**
		 * Parses a URL into its components. Compatible with PHP < 5.4.7.
		 *
		 * @param $url string The URL to parse.
		 *
		 * @return array|false The parsed components or false on error.
		 */
		public static function parse_url( $url ) {
			$url       = trim( $url );
			$no_scheme = 0 === strpos( $url, '//' );

			if ( $no_scheme ) {
				$url = 'http:' . $url;
			}

			$parts = parse_url( $url );

			if ( $no_scheme ) {
				unset( $parts['scheme'] );
			}

			return $parts;
		}

		/**
		 * Is the string a URL?
		 *
		 * @param string $string
		 *
		 * @return bool
		 */
		public static function is_url( $string ) {
			if ( preg_match( '@^(?:https?:)?\/\/[a-zA-Z0-9\-]{3,}@', $string ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Get file paths for all attachment versions.
		 *
		 * @param int        $attachment_id
		 * @param bool       $exists_locally
		 * @param array|bool $meta
		 * @param bool       $include_backups
		 *
		 * @return array
		 */
		public static function get_attachment_file_paths( $attachment_id, $exists_locally = true, $meta = false, $include_backups = true ) {
			$file_path = get_attached_file( $attachment_id, true );
			$paths     = array(
				'original' => $file_path,
			);

			if ( ! $meta ) {
				$meta = get_post_meta( $attachment_id, '_wp_attachment_metadata', true );
			}

			if ( is_wp_error( $meta ) ) {
				return $paths;
			}

			$file_name = wp_basename( $file_path );

			// If file edited, current file name might be different.
			if ( isset( $meta['file'] ) ) {
				$paths['file'] = str_replace( $file_name, wp_basename( $meta['file'] ), $file_path );
			}

			// Thumb
			if ( isset( $meta['thumb'] ) ) {
				$paths['thumb'] = str_replace( $file_name, $meta['thumb'], $file_path );
			}

			// Sizes
			if ( isset( $meta['sizes'] ) ) {
				foreach ( $meta['sizes'] as $size => $file ) {
					if ( isset( $file['file'] ) ) {
						$paths[ $size ] = str_replace( $file_name, $file['file'], $file_path );
					}
				}
			}

			$backups = get_post_meta( $attachment_id, '_wp_attachment_backup_sizes', true );

			// Backups
			if ( $include_backups && is_array( $backups ) ) {
				foreach ( $backups as $size => $file ) {
					if ( isset( $file['file'] ) ) {
						$paths[ $size ] = str_replace( $file_name, $file['file'], $file_path );
					}
				}
			}

			// Allow other processes to add files to be uploaded
			$paths = apply_filters( 'as3cf_attachment_file_paths', $paths, $attachment_id, $meta );

			// Remove duplicates
			$paths = array_unique( $paths );

			// Remove paths that don't exist
			if ( $exists_locally ) {
				foreach ( $paths as $key => $path ) {
					if ( ! file_exists( $path ) ) {
						unset( $paths[ $key ] );
					}
				}
			}

			return $paths;
		}

		/**
		 * Get an attachment's edited file paths.
		 *
		 * @param int $attachment_id
		 *
		 * @return array
		 */
		public static function get_attachment_edited_file_paths( $attachment_id ) {
			$paths = self::get_attachment_file_paths( $attachment_id, false );
			$paths = array_filter( $paths, function ( $path ) {
				return preg_match( '/-e[0-9]{13}(?:-[0-9]{1,4}x[0-9]{1,4})?\./', wp_basename( $path ) );
			} );

			return $paths;
		}

		/**
		 * Get an attachment's edited S3 keys.
		 *
		 * @param int   $attachment_id
		 * @param array $s3object
		 *
		 * @return array
		 */
		public static function get_attachment_edited_keys( $attachment_id, $s3object ) {
			$prefix = trailingslashit( pathinfo( $s3object['key'], PATHINFO_DIRNAME ) );
			$paths  = self::get_attachment_edited_file_paths( $attachment_id );
			$paths  = array_map( function ( $path ) use ( $prefix ) {
				return array( 'Key' => $prefix . wp_basename( $path ) );
			}, $paths );

			return $paths;
		}

		/**
		 * Get intermediate size from attachment filename.
		 *
		 * @param int    $attachment_id
		 * @param string $filename
		 *
		 * @return string
		 */
		public static function get_intermediate_size_from_filename( $attachment_id, $filename ) {
			$sizes = self::get_attachment_file_paths( $attachment_id, false );

			foreach ( $sizes as $size => $file ) {
				if ( wp_basename( $file ) === $filename ) {
					return $size;
				}
			}

			return '';
		}

		/**
		 * Strip edited image suffix and extension from path.
		 *
		 * @param string $path
		 *
		 * @return string
		 */
		public static function strip_image_edit_suffix_and_extension( $path ) {
			$parts    = pathinfo( $path );
			$filename = preg_replace( '/-e[0-9]{13}/', '', $parts['filename'] );

			return str_replace( $parts['basename'], $filename, $path );
		}

		/**
		 * Create a site link for given URL.
		 *
		 * @param string $url
		 * @param string $text
		 *
		 * @return string
		 */
		public static function dbrains_link( $url, $text ) {
			return sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $text ) );
		}
	}
}
