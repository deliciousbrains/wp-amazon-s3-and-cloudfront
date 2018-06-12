<?php

namespace DeliciousBrains\WP_Offload_S3\Providers;

use DeliciousBrains\WP_Offload_S3\Aws3\Aws\CommandPool;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\ResultInterface;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\S3\Exception\S3Exception;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\S3\S3Client;
use DeliciousBrains\WP_Offload_S3\Aws3\Aws\Sdk;
use DeliciousBrains\WP_Offload_S3\Providers\Streams\AWS_S3_Stream_Wrapper;

class AWS_Provider extends Provider {

	/**
	 * @var Sdk
	 */
	private $aws_client;

	/**
	 * @var S3Client
	 */
	private $s3_client;

	/**
	 * @var string
	 */
	protected $provider_short_name = 'aws';

	/**
	 * @var string
	 */
	protected $service_short_name = 's3';

	/**
	 * @var string
	 */
	protected $access_key_id_setting_name = 'aws-access-key-id';

	/**
	 * @var string
	 */
	protected $secret_access_key_setting_name = 'aws-secret-access-key';

	/**
	 * @var array
	 */
	protected static $access_key_id_constants = array(
		'AS3CF_AWS_ACCESS_KEY_ID',
		'DBI_AWS_ACCESS_KEY_ID',
		'AWS_ACCESS_KEY_ID',
	);

	/**
	 * @var array
	 */
	protected static $secret_access_key_constants = array(
		'AS3CF_AWS_SECRET_ACCESS_KEY',
		'DBI_AWS_SECRET_ACCESS_KEY',
		'AWS_SECRET_ACCESS_KEY',
	);

	/**
	 * @var array
	 */
	protected static $use_server_roles_constants = array(
		'AS3CF_AWS_USE_EC2_IAM_ROLE',
		'DBI_AWS_USE_EC2_IAM_ROLE',
		'AWS_USE_EC2_IAM_ROLE',
	);

	/**
	 * @var array
	 */
	protected $regions = array(
		'us-east-1'      => 'US East (N. Virginia)',
		'us-east-2'      => 'US East (Ohio)',
		'us-west-1'      => 'US West (N. California)',
		'us-west-2'      => 'US West (Oregon)',
		'ca-central-1'   => 'Canada (Central)',
		'ap-south-1'     => 'Asia Pacific (Mumbai)',
		'ap-northeast-2' => 'Asia Pacific (Seoul)',
		//'ap-northeast-3' => 'Asia Pacific (Osaka-Local)', // Restricted access, left in for reference only.
		'ap-southeast-1' => 'Asia Pacific (Singapore)',
		'ap-southeast-2' => 'Asia Pacific (Sydney)',
		'ap-northeast-1' => 'Asia Pacific (Tokyo)',
		//'cn-north-1'     => 'China (Beijing)', // Restricted access, left in for reference only.
		//'cn-northwest-1' => 'China (Ningxia)', // Restricted access, left in for reference only.
		'eu-central-1'   => 'EU (Frankfurt)',
		'eu-west-1'      => 'EU (Ireland)',
		'eu-west-2'      => 'EU (London)',
		'eu-west-3'      => 'EU (Paris)',
		'sa-east-1'      => 'South America (Sao Paulo)',
	);

	/**
	 * @var string
	 */
	protected $default_region = 'us-east-1';

	const API_VERSION = '2006-03-01';
	const SIGNATURE_VERSION = 'v4';

	const DEFAULT_ACL = 'public-read';
	const PRIVATE_ACL = 'private';

	/**
	 * AWS_Provider constructor.
	 *
	 * @param \AS3CF_Plugin_Base $as3cf
	 */
	public function __construct( \AS3CF_Plugin_Base $as3cf ) {
		parent::__construct( $as3cf );

		// Autoloader.
		require_once $as3cf->get_plugin_sdks_dir_path() . '/Aws3/aws-autoloader.php';
	}

	/**
	 * Returns default args array for the client.
	 *
	 * @return array
	 */
	protected function default_client_args() {
		return array(
			'signature_version' => self::SIGNATURE_VERSION,
			'version'           => self::API_VERSION,
			'region'            => $this->default_region,
		);
	}

	/**
	 * Instantiate a new client for the provider's SDK.
	 *
	 * @param array $args
	 */
	protected function init_client( Array $args ) {
		$this->aws_client = new Sdk( $args );
	}

	/**
	 * Instantiate a new service specific client.
	 *
	 * @param array $args
	 *
	 * @return S3Client
	 */
	protected function init_service_client( Array $args ) {
		$this->s3_client = $this->aws_client->createS3( $args );

		return $this->s3_client;
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

		$region = strtolower( $region );

		/**
		 * Translate older bucket locations to newer S3 region names
		 * http://docs.aws.amazon.com/general/latest/gr/rande.html#s3_region
		 */
		switch ( $region ) {
			case 'eu':
				$region = 'eu-west-1';
				break;
		}

		return $region;
	}

	/**
	 * Create bucket.
	 *
	 * @param array $args
	 */
	public function create_bucket( Array $args ) {
		if ( ! empty( $args['LocationConstraint'] ) ) {
			$args['CreateBucketConfiguration']['LocationConstraint'] = $args['LocationConstraint'];
			unset( $args['LocationConstraint'] );
		}

		$this->s3_client->createBucket( $args );
	}

	/**
	 * Check whether bucket exists.
	 *
	 * @param $bucket
	 *
	 * @return bool
	 */
	public function does_bucket_exist( $bucket ) {
		return $this->s3_client->doesBucketExist( $bucket );
	}

	/**
	 * Returns region for bucket.
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	public function get_bucket_location( Array $args ) {
		$location = $this->s3_client->getBucketLocation( $args );
		$region   = empty( $location['LocationConstraint'] ) ? '' : $location['LocationConstraint'];

		return $region;
	}

	/**
	 * List buckets.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function list_buckets( Array $args = array() ) {
		return $this->s3_client->listBuckets( $args )->toArray();
	}

	/**
	 * Check whether key exists in bucket.
	 *
	 * @param       $bucket
	 * @param       $key
	 * @param array $options
	 *
	 * @return bool
	 */
	public function does_object_exist( $bucket, $key, Array $options = array() ) {
		return $this->s3_client->doesObjectExist( $bucket, $key, $options );
	}

	/**
	 * Get default "canned" ACL string.
	 *
	 * @return string
	 */
	public function get_default_acl() {
		return self::DEFAULT_ACL;
	}

	/**
	 * Get private "canned" ACL string.
	 *
	 * @return string
	 */
	public function get_private_acl() {
		return self::PRIVATE_ACL;
	}

	/**
	 * Download object, destination specified in args.
	 *
	 * @see https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#getobject
	 *
	 * @param array $args
	 */
	public function get_object( Array $args ) {
		$this->s3_client->getObject( $args );
	}

	/**
	 * Get object's URL.
	 *
	 * @param       $bucket
	 * @param       $key
	 * @param       $expires
	 * @param array $args
	 *
	 * @return string
	 */
	public function get_object_url( $bucket, $key, $expires, Array $args = array() ) {
		$commandArgs = [ 'Bucket' => $bucket, 'Key' => $key ];

		if ( ! empty( $args ) ) {
			$commandArgs = array_merge( $commandArgs, $args );
		}

		$command = $this->s3_client->getCommand( 'GetObject', $commandArgs );

		if ( empty( $expires ) ) {
			return (string) \DeliciousBrains\WP_Offload_S3\Aws3\Aws\serialize( $command )->getUri();
		} else {
			return (string) $this->s3_client->createPresignedRequest( $command, $expires )->getUri();
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
		return $this->s3_client->listObjects( $args )->toArray();
	}

	/**
	 * Update the ACL for an object.
	 *
	 * @param array $args
	 */
	public function update_object_acl( Array $args ) {
		$this->s3_client->putObjectAcl( $args );
	}

	/**
	 * Upload file to bucket.
	 *
	 * @param array $args
	 */
	public function upload_object( Array $args ) {
		$this->s3_client->putObject( $args );
	}

	/**
	 * Delete object from bucket.
	 *
	 * @param array $args
	 */
	public function delete_object( Array $args ) {
		$this->s3_client->deleteObject( $args );
	}

	/**
	 * Delete multiple objects from bucket.
	 *
	 * @param array $args
	 */
	public function delete_objects( Array $args ) {
		if ( ! isset( $args['Delete'] ) && isset( $args['Objects'] ) ) {
			$args['Delete']['Objects'] = $args['Objects'];
			unset( $args['Objects'] );
		}

		$this->s3_client->deleteObjects( $args );
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

		$commands = array_map( function ( $location ) {
			return $this->s3_client->getCommand( 'ListObjects', $location );
		}, $locations );

		$results = CommandPool::batch( $this->s3_client, $commands, array( 'preserve_iterator_keys' => true ) );

		/* @var ResultInterface $result */
		foreach ( $results as $attachment_id => $result ) {
			$found_keys = $result->search( 'Contents[].Key' );

			if ( ! empty( $found_keys ) ) {
				$keys[ $attachment_id ] = $found_keys;
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

		$commands = array_map( function ( $item ) {
			return $this->s3_client->getCommand( 'CopyObject', $item );
		}, $items );

		$results = CommandPool::batch( $this->s3_client, $commands );

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				/* @var S3Exception $result */
				if ( is_a( $result, 'DeliciousBrains\WP_Offload_S3\Aws3\Aws\S3\Exception\S3Exception' ) ) {
					$command    = $result->getCommand()->toArray();
					$failures[] = array(
						'Key'     => $command['Key'],
						'Message' => $result->getAwsErrorMessage(),
					);
				}
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
		$protocol = 's3';
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

		// Register the region specific S3 stream wrapper to be used by plugins
		AWS_S3_Stream_Wrapper::register( $this->s3_client, $protocol );

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
				'ACL'    => 'public-read',
			) );

			// delete it straight away if created
			$this->delete_object( array(
				'Bucket' => $bucket,
				'Key'    => $key,
			) );

			return true;
		} catch ( \Exception $e ) {
			// If we encounter an error that isn't access denied, throw that error.
			if ( ! $e instanceof S3Exception || 'AccessDenied' !== $e->getAwsErrorCode() ) {
				return $e->getMessage();
			}
		}

		return false;
	}
}
