<?php

namespace DeliciousBrains\WP_Offload_Media\Items;

use AS3CF_Error;
use DeliciousBrains\WP_Offload_Media\Providers\Storage\Storage_Provider;
use Exception;
use WP_Error;

class Download_Handler extends Item_Handler {
	/**
	 * @var string
	 */
	protected static $item_handler_key = 'download';

	/**
	 * The default options that should be used if none supplied.
	 *
	 * @return array
	 */
	public static function default_options() {
		return array(
			'full_source_paths' => array(),
		);
	}

	/**
	 * Prepare a manifest based on the item.
	 *
	 * @param Item  $as3cf_item
	 * @param array $options
	 *
	 * @return Manifest
	 */
	protected function pre_handle( Item $as3cf_item, array $options ) {
		$manifest   = new Manifest();
		$file_paths = array();

		foreach ( $as3cf_item->objects() as $object_key => $object ) {
			$file = $as3cf_item->full_source_path( $object_key );

			if ( 0 < count( $options['full_source_paths'] ) && ! in_array( $file, $options['full_source_paths'] ) ) {
				continue;
			}

			$file_paths[ $object_key ] = $file;
		}

		$file_paths = array_unique( $file_paths );

		foreach ( $file_paths as $object_key => $file_path ) {
			if ( ! file_exists( $file_path ) ) {
				$manifest->objects[] = array(
					'args' => array(
						'Bucket' => $as3cf_item->bucket(),
						'Key'    => $as3cf_item->provider_key( $object_key ),
						'SaveAs' => $file_path,
					),
				);
			}
		}

		return $manifest;
	}

	/**
	 * Perform the downloads.
	 *
	 * @param Item     $as3cf_item
	 * @param Manifest $manifest
	 * @param array    $options
	 *
	 * @return boolean|WP_Error
	 * @throws Exception
	 */
	protected function handle_item( Item $as3cf_item, Manifest $manifest, array $options ) {
		if ( ! empty( $manifest->objects ) ) {
			// This test is "late" so that we don't raise the error if the local files exist anyway.
			// If the provider of this item is different from what's currently configured,
			// we'll return an error.
			$current_provider = $this->as3cf->get_storage_provider();
			if ( ! empty( $current_provider ) && $current_provider::get_provider_key_name() !== $as3cf_item->provider() ) {
				$message = sprintf(
					__( '%1$s with ID %d is offloaded to a different provider than currently configured', 'amazon-s3-and-cloudfront' ),
					$this->as3cf->get_source_type_name( $as3cf_item->source_type() ),
					$as3cf_item->source_id()
				);

				return new WP_Error( 'exception', $message );
			} else {
				$provider_client = $this->as3cf->get_provider_client( $as3cf_item->region() );

				foreach ( $manifest->objects as &$manifest_object ) {
					// Save object to a file.
					$result = $this->download_object( $provider_client, $manifest_object['args'] );

					$manifest_object['download_result']['status'] = self::STATUS_OK;

					if ( is_wp_error( $result ) ) {
						$manifest_object['download_result']['status']  = self::STATUS_FAILED;
						$manifest_object['download_result']['message'] = $result->get_error_message();
					}
				}
			}
		}

		return true;
	}

	/**
	 * Perform post handle tasks. Log errors, update filesize totals etc.
	 *
	 * @param Item     $as3cf_item
	 * @param Manifest $manifest
	 * @param array    $options
	 *
	 * @return bool|WP_Error
	 */
	protected function post_handle( Item $as3cf_item, Manifest $manifest, array $options ) {
		// Look for errors
		$errors = new WP_Error;
		$i      = 1;

		foreach ( $manifest->objects as $manifest_object ) {
			if ( $manifest_object['download_result']['status'] !== self::STATUS_OK ) {
				$errors->add( 'download-error-' . $i++, $manifest_object['download_result']['message'] );
			}
		}

		if ( count( $errors->get_error_codes() ) ) {
			return $errors;
		}

		$as3cf_item->update_filesize_after_download_local();

		return true;
	}

	/**
	 * Download an object from provider.
	 *
	 * @param Storage_Provider $provider_client
	 * @param array            $object
	 *
	 * @return bool|WP_Error
	 */
	private function download_object( $provider_client, $object ) {
		// Make sure the local directory exists.
		$dir = dirname( $object['SaveAs'] );
		if ( ! is_dir( $dir ) && ! wp_mkdir_p( $dir ) ) {
			$error_message = sprintf( __( 'The local directory %s does not exist and could not be created.', 'amazon-s3-and-cloudfront' ), $dir );
			AS3CF_Error::log( sprintf( __( 'There was an error attempting to download the file %s from the bucket: %s', 'amazon-s3-and-cloudfront' ), $object['Key'], $error_message ) );
		}

		try {
			$provider_client->get_object( $object );
		} catch ( Exception $e ) {
			$error_msg = sprintf( __( 'Error downloading %1$s from bucket: %2$s', 'amazon-s3-and-cloudfront' ), $object['Key'], $e->getMessage() );
			AS3CF_Error::log( $error_msg );

			// If storage provider file doesn't exist, an empty local file will be created, clean it up.
			@unlink( $object['SaveAs'] );

			return new WP_Error( 'download_object', $error_msg );
		}

		return true;
	}
}