<?php

namespace DeliciousBrains\WP_Offload_Media\Providers;

use AS3CF_Utils;
use DeliciousBrains\WP_Offload_Media\Gcp\Google\Cloud\Core\Exception\GoogleException;
use DeliciousBrains\WP_Offload_Media\Gcp\Google\Cloud\Core\ServiceBuilder;
use DeliciousBrains\WP_Offload_Media\Gcp\Google\Cloud\Storage\Bucket;
use DeliciousBrains\WP_Offload_Media\Gcp\Google\Cloud\Storage\StorageClient;
use DeliciousBrains\WP_Offload_Media\Gcp\Google\Cloud\Storage\StorageObject;
use DeliciousBrains\WP_Offload_Media\Providers\Streams\GCP_GCS_Stream_Wrapper;

class GCP_Provider extends Provider {

	/**
	 * @var ServiceBuilder
	 */
	private $cloud;

	/**
	 * @var StorageClient
	 */
	private $storage;

	/**
	 * @var string
	 */
	protected static $provider_name = 'Google Cloud Platform';

	/**
	 * @var string
	 */
	protected static $provider_short_name = 'GCP';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $provider_key_name = 'gcp';

	/**
	 * @var string
	 */
	protected static $service_name = 'Google Cloud Storage';

	/**
	 * @var string
	 */
	protected static $service_short_name = 'GCS';

	/**
	 * Used in filters and settings.
	 *
	 * @var string
	 */
	protected static $service_key_name = 'gcs';

	/**
	 * Optional override of "Provider Name" + "Service Name" for friendly name for service.
	 *
	 * @var string
	 */
	protected static $provider_service_name = 'Google Cloud Storage';

	/**
	 * The slug for the service's quick start guide doc.
	 *
	 * @var string
	 */
	protected static $provider_service_quick_start_slug = 'google-cloud-storage-quick-start-guide';

	/**
	 * @var array
	 */
	protected static $use_server_roles_constants = array(
		'AS3CF_GCP_USE_GCE_IAM_ROLE',
	);

	/**
	 * @var array
	 */
	protected static $key_file_path_constants = array(
		'AS3CF_GCP_KEY_FILE_PATH',
	);

	/**
	 * @var array
	 */
	protected $regions = array(
		'asia'                    => 'Asia (Multi-Regional)',
		'eu'                      => 'European Union (Multi-Regional)',
		'us'                      => 'United States (Multi-Regional)',
		'northamerica-northeast1' => 'Montréal',
		'us-central1'             => 'Iowa',
		'us-east1'                => 'South Carolina',
		'us-east4'                => 'Northern Virginia',
		'us-west1'                => 'Oregon',
		'us-west2'                => 'Los Angeles',
		'southamerica-east1'      => 'São Paulo',
		'europe-north1'           => 'Finland',
		'europe-west1'            => 'Belgium',
		'europe-west2'            => 'London',
		'europe-west3'            => 'Frankfurt',
		'europe-west4'            => 'Netherlands',
		'asia-east1'              => 'Taiwan',
		'asia-east2'              => 'Hong Kong',
		'asia-northeast1'         => 'Tokyo',
		'asia-south1'             => 'Mumbai',
		'asia-southeast1'         => 'Singapore',
		'australia-southeast1'    => 'Sydney',
	);

	/**
	 * @var string
	 */
	protected $default_region = 'us';

	/**
	 * @var string
	 */
	protected $default_domain = 'storage.googleapis.com';

	/**
	 * @var string
	 */
	protected $console_url = 'https://console.cloud.google.com/storage/browser/';

	/**
	 * @var string
	 */
	protected $console_url_prefix_param = '/';

	const DEFAULT_ACL = 'publicRead';
	const PRIVATE_ACL = 'projectPrivate';

	/**
	 * GCP_Provider constructor.
	 *
	 * @param \AS3CF_Plugin_Base $as3cf
	 */
	public function __construct( \AS3CF_Plugin_Base $as3cf ) {
		parent::__construct( $as3cf );

		// Autoloader.
		require_once $as3cf->get_plugin_sdks_dir_path() . '/Gcp/autoload.php';
	}

	/**
	 * Returns default args array for the client.
	 *
	 * @return array
	 */
	protected function default_client_args() {
		return array();
	}

	/**
	 * Process the args before instantiating a new client for the provider's SDK.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	protected function init_client_args( Array $args ) {
		return $args;
	}

	/**
	 * Instantiate a new client for the provider's SDK.
	 *
	 * @param array $args
	 */
	protected function init_client( Array $args ) {
		$this->cloud = new ServiceBuilder( $args );
	}

	/**
	 * Process the args before instantiating a new service specific client.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	protected function init_service_client_args( Array $args ) {
		return $args;
	}

	/**
	 * Instantiate a new service specific client.
	 *
	 * @param array $args
	 *
	 * @return StorageClient
	 */
	protected function init_service_client( Array $args ) {
		$this->storage = $this->cloud->storage( $args );

		return $this->storage;
	}

	/**
	 * Make sure region "slug" fits expected format.
	 *
	 * @param string $region
	 *
	 * @return string
	 */
	public function sanitize_region( $region ) {
		if ( ! is_string( $region ) ) {
			// Don't translate any region errors
			return $region;
		}

		return strtolower( $region );
	}

	/**
	 * Create bucket.
	 *
	 * @param array $args
	 *
	 * @throws GoogleException
	 */
	public function create_bucket( Array $args ) {
		$name = '';
		if ( ! empty( $args['Bucket'] ) ) {
			$name = $args['Bucket'];
			unset( $args['Bucket'] );
		}

		if ( ! empty( $args['LocationConstraint'] ) ) {
			$args['location'] = $args['LocationConstraint'];
			unset( $args['LocationConstraint'] );
		}

		$this->storage->createBucket( $name, $args );
	}

	/**
	 * Check whether bucket exists.
	 *
	 * @param string $bucket
	 *
	 * @return bool
	 */
	public function does_bucket_exist( $bucket ) {
		return $this->storage->bucket( $bucket )->exists();
	}

	/**
	 * Returns region for bucket.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function get_bucket_location( Array $args ) {
		$info   = $this->storage->bucket( $args['Bucket'] )->info();
		$region = empty( $info['location'] ) ? '' : $info['location'];

		return $region;
	}

	/**
	 * List buckets.
	 *
	 * @param array $args
	 *
	 * @return array
	 * @throws GoogleException
	 */
	public function list_buckets( Array $args = array() ) {
		$result = array();

		$buckets = $this->storage->buckets( $args );

		if ( ! empty( $buckets ) ) {
			/** @var Bucket $bucket */
			foreach ( $buckets as $bucket ) {
				$result['Buckets'][] = array(
					'Name' => $bucket->name(),
				);
			}
		}

		return $result;
	}

	/**
	 * Check whether key exists in bucket.
	 *
	 * @param string $bucket
	 * @param string $key
	 * @param array  $options
	 *
	 * @return bool
	 */
	public function does_object_exist( $bucket, $key, Array $options = array() ) {
		return $this->storage->bucket( $bucket )->object( $key )->exists( $options );
	}

	/**
	 * Get default "canned" ACL string.
	 *
	 * @return string
	 */
	public function get_default_acl() {
		return static::DEFAULT_ACL;
	}

	/**
	 * Get private "canned" ACL string.
	 *
	 * @return string
	 */
	public function get_private_acl() {
		return static::PRIVATE_ACL;
	}

	/**
	 * Download object, destination specified in args.
	 *
	 * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#getobject
	 * @see https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/v0.90.0/storage/storageobject?method=downloadToFile
	 *
	 * @param array $args
	 */
	public function get_object( Array $args ) {
		$this->storage->bucket( $args['Bucket'] )->object( $args['Key'] )->downloadToFile( $args['SaveAs'] );
	}

	/**
	 * Get object's URL.
	 *
	 * @param string $bucket
	 * @param string $key
	 * @param int    $expires
	 * @param array  $args
	 *
	 * @return string
	 */
	public function get_object_url( $bucket, $key, $expires, Array $args = array() ) {
		if ( empty( $expires ) || ! is_int( $expires ) || $expires < 0 ) {
			$info = $this->storage->bucket( $bucket )->object( $key )->info();
			$link = empty( $info['selfLink'] ) ? '' : $info['selfLink'];

			return $link;
		} else {
			$options = array();

			if ( ! empty( $args['BaseURL'] ) ) {
				$options['cname'] = $args['BaseURL'];
			}

			return $this->storage->bucket( $bucket )->object( $key )->signedUrl( time() + $expires, $options );
		}
	}

	/**
	 * List objects.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function list_objects( Array $args = array() ) {
		$result = array();

		$objects = $this->storage->bucket( $args['Bucket'] )->objects( $args['Prefix'] );

		if ( ! empty( $objects ) ) {
			/** @var StorageObject $object */
			foreach ( $objects as $object ) {
				$info                 = $object->info();
				$result['Contents'][] = array(
					'Key'  => $object->name(),
					'Size' => $info['size'],
				);
			}
		}

		return $result;
	}

	/**
	 * Update the ACL for an object.
	 *
	 * @param array $args
	 */
	public function update_object_acl( Array $args ) {
		$this->storage->bucket( $args['Bucket'] )->object( $args['Key'] )->update( array( 'predefinedAcl' => $args['ACL'] ) );
	}

	/**
	 * Upload file to bucket.
	 *
	 * @param array $args
	 *
	 * @throws \Exception
	 */
	public function upload_object( Array $args ) {
		if ( ! empty( $args['SourceFile'] ) ) {
			$file = fopen( $args['SourceFile'], 'r' );
		} elseif ( ! empty( $args['Body'] ) ) {
			$file = $args['Body'];
		} else {
			throw new \Exception( __METHOD__ . ' called without either "SourceFile" or "Body" arg.' );
		}

		$options = array(
			'name'          => $args['Key'],
			'predefinedAcl' => $args['ACL'],
		);

		if ( ! empty( $args['ContentType'] ) ) {
			$options['metadata']['contentType'] = $args['ContentType'];
		}

		if ( ! empty( $args['CacheControl'] ) ) {
			$options['metadata']['cacheControl'] = $args['CacheControl'];
		}

		// TODO: Potentially strip out known keys from $args and then put rest in $options['metadata']['metadata'].

		$object = $this->storage->bucket( $args['Bucket'] )->upload( $file, $options );
	}

	/**
	 * Delete object from bucket.
	 *
	 * @param array $args
	 */
	public function delete_object( Array $args ) {
		$this->storage->bucket( $args['Bucket'] )->object( $args['Key'] )->delete();
	}

	/**
	 * Delete multiple objects from bucket.
	 *
	 * @param array $args
	 */
	public function delete_objects( Array $args ) {
		if ( isset( $args['Delete']['Objects'] ) ) {
			$keys = $args['Delete']['Objects'];
		} elseif ( isset( $args['Objects'] ) ) {
			$keys = $args['Objects'];
		}

		if ( ! empty( $keys ) ) {
			$bucket = $this->storage->bucket( $args['Bucket'] );

			// Unfortunately the GCP PHP SDK does not have batch operations.
			foreach ( $keys as $key ) {
				$bucket->object( $key['Key'] )->delete();
			}
		}
	}

	/**
	 * Returns arrays of found keys for given bucket and prefix locations, retaining given array's integer based index.
	 *
	 * @param array $locations Array with attachment ID as key and Bucket and Prefix in an associative array as values.
	 *
	 * @return array
	 */
	public function list_keys( Array $locations ) {
		$keys = array();

		$results = array_map( function ( $location ) {
			return $this->storage->bucket( $location['Bucket'] )->objects( array( 'prefix' => $location['Prefix'], 'fields' => 'items/name' ) );
		}, $locations );

		foreach ( $results as $attachment_id => $objects ) {
			/** @var StorageObject $object */
			foreach ( $objects as $object ) {
				$keys[ $attachment_id ][] = $object->name();
			}
		}

		return $keys;
	}

	/**
	 * Copies objects into current bucket from another bucket hosted with provider.
	 *
	 * @param array $items
	 *
	 * @return array Failures with elements Key and Message
	 */
	public function copy_objects( Array $items ) {
		$failures = array();

		// Unfortunately the GCP PHP SDK does not have batch operations.
		foreach ( $items as $item ) {
			list( $bucket, $key ) = explode( '/', urldecode( $item['CopySource'] ), 2 );
			try {
				$this->storage->bucket( $bucket )->object( $key )->copy(
					$item['Bucket'],
					array(
						'name'          => $item['Key'],
						'predefinedAcl' => $item['ACL'],
					)
				);
			} catch ( \Exception $e ) {
				$failures[] = array(
					'Key'     => $item['Key'],
					'Message' => $e->getMessage(),
				);
			}
		}

		return $failures;
	}

	/**
	 * Generate the stream wrapper protocol
	 *
	 * @param string $region
	 *
	 * @return string
	 */
	protected function get_stream_wrapper_protocol( $region ) {
		$protocol = 'gs';

		// TODO: Determine whether same protocol for all regions is ok.
		// Assumption not as each may have client instance, hence keeping this for time being.
		$protocol .= str_replace( '-', '', $region );

		return $protocol;
	}

	/**
	 * Register a stream wrapper for specific region.
	 *
	 * @param string $region
	 *
	 * @return bool
	 */
	public function register_stream_wrapper( $region ) {
		$protocol = $this->get_stream_wrapper_protocol( $region );

		// Register the region specific stream wrapper to be used by plugins
		GCP_GCS_Stream_Wrapper::register( $this->storage, $protocol );

		return true;
	}

	/**
	 * Check that a bucket and key can be written to.
	 *
	 * @param string $bucket
	 * @param string $key
	 * @param string $file_contents
	 *
	 * @return bool|string Error message on unexpected exception
	 */
	public function can_write( $bucket, $key, $file_contents ) {
		try {
			// Attempt to create the test file.
			$this->upload_object( array(
				'Bucket' => $bucket,
				'Key'    => $key,
				'Body'   => $file_contents,
				'ACL'    => self::DEFAULT_ACL,
			) );

			// delete it straight away if created
			$this->delete_object( array(
				'Bucket' => $bucket,
				'Key'    => $key,
			) );

			return true;
		} catch ( \Exception $e ) {
			// If we encounter an error that isn't from Google, throw that error.
			if ( ! $e instanceof GoogleException ) {
				return $e->getMessage();
			}
		}
	}

	/**
	 * Get the region specific prefix for raw URL
	 *
	 * @param string   $region
	 * @param null|int $expires
	 *
	 * @return string
	 */
	protected function url_prefix( $region = '', $expires = null ) {
		return '';
	}

	/**
	 * Get the url domain for the files
	 *
	 * @param string $domain  Likely prefixed with region
	 * @param string $bucket
	 * @param string $region
	 * @param int    $expires
	 * @param array  $args    Allows you to specify custom URL settings
	 * @param bool   $preview When generating the URL preview sanitize certain output
	 *
	 * @return string
	 */
	protected function url_domain( $domain, $bucket, $region = '', $expires = null, $args = array(), $preview = false ) {
		if ( 'cloudfront' === $args['domain'] && $args['cloudfront'] ) {
			$cloudfront = $args['cloudfront'];
			if ( $preview ) {
				$cloudfront = AS3CF_Utils::sanitize_custom_domain( $cloudfront );
			}

			$domain = $cloudfront;
		} else {
			$domain = $domain . '/' . $bucket;
		}

		return $domain;
	}

	/**
	 * Get the suffix param to append to the link to the bucket on the provider's console.
	 *
	 * @param string $bucket
	 * @param string $prefix
	 *
	 * @return string
	 */
	protected function get_console_url_suffix_param( $bucket = '', $prefix = '' ) {
		if ( ! empty( $this->get_project_id() ) ) {
			return '?project=' . $this->get_project_id();
		}

		return '';
	}

	/**
	 * Get the Project ID for the current client.
	 *
	 * @return string|null
	 */
	private function get_project_id() {
		static $project_id = null;

		// If not already grabbed, get project id from key file data but only if client properly instantiated.
		if ( null === $project_id && ! empty( $this->storage ) && $this->use_key_file() ) {
			$key_file_path = $this->get_key_file_path();

			if ( ! empty( $key_file_path ) && file_exists( $key_file_path ) ) {
				$key_file_contents = json_decode( file_get_contents( $key_file_path ), true );

				if ( ! empty( $key_file_contents['project_id'] ) ) {
					$project_id = $key_file_contents['project_id'];

					return $project_id;
				}
			}

			$key_file_contents = $this->get_key_file();

			if ( is_array( $key_file_contents ) && ! empty( $key_file_contents['project_id'] ) ) {
				$project_id = $key_file_contents['project_id'];

				return $project_id;
			}
		}

		return $project_id;
	}
}
