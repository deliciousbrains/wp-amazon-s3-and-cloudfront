<?php

// Check if already defined
if ( ! class_exists( 'WP_Offload_S3_Autoloader' ) ) {

	class WP_Offload_S3_Autoloader {

		/**
		 * @var string
		 */
		protected $abspath;

		/**
		 * @var string
		 */
		protected $prefix;

		/**
		 * @var string
		 */
		protected $vendor = 'DeliciousBrains';

		/**
		 * Autoloader constructor.
		 *
		 * @param string $prefix
		 * @param string $abspath
		 */
		public function __construct( $prefix, $abspath ) {
			$this->prefix  = $prefix;
			$this->abspath = $abspath;

			spl_autoload_register( array( $this, 'autoloader' ) );
		}

		/**
		 * Autoloader.
		 *
		 * @param string $class_name
		 */
		public function autoloader( $class_name ) {
			if ( ! $this->class_belongs_to_plugin( $class_name ) ) {
				return;
			}

			$path = $this->get_classes_directory() . $this->get_class_path( $class_name );

			if ( file_exists( $path ) ) {
				require_once $path;
			}
		}

		/**
		 * Class belong to plugin.
		 *
		 * @param string $class_name
		 *
		 * @return bool
		 */
		protected function class_belongs_to_plugin( $class_name ) {
			if ( 0 !== strpos( $class_name, $this->vendor . '\\' . $this->prefix . '\\' ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Get class path.
		 *
		 * @param string $class_name
		 *
		 * @return string
		 */
		protected function get_class_path( $class_name ) {
			$parts = explode( '\\', strtolower( $class_name ) );
			$parts = array_slice( $parts, 2 );

			$filename = implode( DIRECTORY_SEPARATOR, $parts ) . '.php';

			return str_replace( '_', '-', strtolower( $filename ) );
		}

		/**
		 * Get classes directory.
		 *
		 * @return string
		 */
		protected function get_classes_directory() {
			return $this->abspath . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR;
		}

	}
}
