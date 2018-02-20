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
		 * @param     $url string The URL to parse.
		 *
		 * @param int $component PHP_URL_ constant for URL component to return.
		 *
		 * @return mixed An array of the parsed components, mixed for a requested component, or false on error.
		 */
		public static function parse_url( $url, $component = -1 ) {
			$url       = trim( $url );
			$no_scheme = 0 === strpos( $url, '//' );

			if ( $no_scheme ) {
				$url = 'http:' . $url;
			}

			$parts = parse_url( $url, $component );

			if ( 0 < $component ) {
				return $parts;
			}

			if ( $no_scheme && is_array( $parts ) ) {
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
			if ( preg_match( '@^(?:https?:)?//[a-zA-Z0-9\-]+@', $string ) ) {
				return true;
			}

			return false;
		}

		/**
		 * Is the string a relative URL?
		 *
		 * @param $string
		 *
		 * @return bool
		 */
		public static function is_relative_url( $string ) {
			if ( empty( $string ) || ! is_string( $string ) ) {
				return false;
			}

			$url = static::parse_url( $string );

			return ( empty( $url['scheme'] ) && empty( $url['host'] ) );
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

		/**
		 * Check whether two URLs share the same domain.
		 *
		 * @param string $url_a
		 * @param string $url_b
		 *
		 * @return bool
		 */
		public static function url_domains_match( $url_a, $url_b ) {
			if ( ! static::is_url( $url_a ) || ! static::is_url( $url_b ) ) {
				return false;
			}

			return static::parse_url( $url_a, PHP_URL_HOST ) === static::parse_url( $url_b, PHP_URL_HOST );
		}

		/**
		 * Get the current domain.
		 *
		 * @return string|false
		 */
		public static function current_domain() {
			return parse_url( home_url(), PHP_URL_HOST );
		}

		/**
		 * Get the base domain of the current domain.
		 *
		 * @return string
		 */
		public static function current_base_domain() {
			$domain = static::current_domain();
			$parts  = explode( '.', $domain, 2 );

			if ( isset( $parts[1] ) && in_array( $parts[0], array( 'www' ) ) ) {
				return $parts[1];
			}

			return $domain;
		}

		/**
		 * A safe wrapper for deactivate_plugins()
		 */
		public static function deactivate_plugins() {
			if ( ! function_exists( 'deactivate_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			call_user_func_array( 'deactivate_plugins', func_get_args() );
		}

		/**
		 * Get the first defined constant from the given list of constant names.
		 *
		 * @param array $constants
		 *
		 * @return string|false string constant name if defined, otherwise false if none are defined
		 */
		public static function get_first_defined_constant( $constants ) {
			foreach ( (array) $constants as $constant ) {
				if ( defined( $constant ) ) {
					return $constant;
				}
			}

			return false;
		}
	}
}
