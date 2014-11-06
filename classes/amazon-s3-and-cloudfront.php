<?php
use Aws\S3\S3Client;

class Amazon_S3_And_CloudFront extends AWS_Plugin_Base {
	private $aws, $s3client;

	const DEFAULT_ACL = 'public-read';
	const PRIVATE_ACL = 'private';
	const DEFAULT_EXPIRES = 900;

	const SETTINGS_KEY = 'tantan_wordpress_s3';

	function __construct( $plugin_file_path, $aws ) {
		$this->plugin_slug = 'amazon-s3-and-cloudfront';

		parent::__construct( $plugin_file_path );

		$this->aws = $aws;

		add_action( 'aws_admin_menu', array( $this, 'admin_menu' ) );

		$this->plugin_title = __( 'Amazon S3 and CloudFront', 'as3cf' );
		$this->plugin_menu_title = __( 'S3 and CloudFront', 'as3cf' );

		add_action( 'wp_ajax_as3cf-create-bucket', array( $this, 'ajax_create_bucket' ) );

		if ( $this->get_setting( 'serve-from-s3' ) ) {
			add_filter( 'wp_get_attachment_url', array( $this, 'wp_get_attachment_url' ), 99, 2 );
			add_filter( 'image_downsize', array( $this, 'image_downsize' ), 10, 3 );
		}

		add_filter( 'wp_handle_upload_prefilter', array( $this, 'wp_handle_upload_prefilter' ), 1 );
		add_filter( 'wp_update_attachment_metadata', array( $this, 'wp_update_attachment_metadata' ), 100, 2 );
		add_filter( 'delete_attachment', array( $this, 'delete_attachment' ), 20 );
	}

	function get_setting( $key, $default = '' ) {
		$settings = $this->get_settings();

		// If legacy setting set, migrate settings
		if ( isset( $settings['wp-uploads'] ) && $settings['wp-uploads'] && in_array( $key, array( 'copy-to-s3', 'serve-from-s3' ) ) ) {
			return '1';
		}

		// Default object prefix
		if ( 'object-prefix' == $key && !isset( $settings['object-prefix'] ) ) {
	        $uploads = wp_upload_dir();
	        $parts = parse_url( $uploads['baseurl'] );
	        return substr( $parts['path'], 1 ) . '/';
		}

		if ( 'bucket' == $key && defined( 'AS3CF_BUCKET' ) ) {
			$value = AS3CF_BUCKET;
		}
		else {
			$value = parent::get_setting( $key );
		}

		return apply_filters( 'as3cf_setting_' . $key, $value );
	}

	/**
	 * Allowed mime types array that can be edited for specific S3 uploading
	 *
	 * @return array
	 */
	function get_allowed_mime_types() {
		return apply_filters( 'as3cf_allowed_mime_types', get_allowed_mime_types() );
	}

    function delete_attachment( $post_id ) {
        if ( !$this->is_plugin_setup() ) {
            return;
        }

        $backup_sizes = get_post_meta( $post_id, '_wp_attachment_backup_sizes', true );

        $intermediate_sizes = array();
        foreach ( get_intermediate_image_sizes() as $size ) {
            if ( $intermediate = image_get_intermediate_size( $post_id, $size ) )
                $intermediate_sizes[] = $intermediate;
        }

        if ( !( $s3object = $this->get_attachment_s3_info( $post_id ) ) ) {
            return;
        }

	    $bucket = $s3object['bucket'];

	    $this->set_s3client_region( $s3object );

        $amazon_path = dirname( $s3object['key'] );
        $objects = array();

        // remove intermediate and backup images if there are any
        foreach ( $intermediate_sizes as $intermediate ) {
            $objects[] = array(
            	'Key' => path_join( $amazon_path, $intermediate['file'] )
            );
        }

        if ( is_array( $backup_sizes ) ) {
            foreach ( $backup_sizes as $size ) {
	            $objects[] = array(
	            	'Key' => path_join( $amazon_path, $size['file'] )
	            );
            }
        }

        // Try removing any @2x images but ignore any errors
        if ( $objects ) {
        	$hidpi_images = array();
        	foreach ( $objects as $object ) {
        		$hidpi_images[] = array(
        			'Key' => $this->get_hidpi_file_path( $object['Key'] )
        		);
        	}

			try {
		        $this->get_s3client()->deleteObjects( array( 
		        	'Bucket' => $bucket,
		        	'Objects' => $hidpi_images
		        ) );
			}
			catch ( Exception $e ) {}
        }

        $objects[] = array(
        	'Key' => $s3object['key']
        );

		try {
	        $this->get_s3client()->deleteObjects( array( 
	        	'Bucket' => $bucket,
	        	'Objects' => $objects
	        ) );
		}
		catch ( Exception $e ) {
			error_log( 'Error removing files from S3: ' . $e->getMessage() );
			return;
		}

        delete_post_meta( $post_id, 'amazonS3_info' );
    }

    function wp_update_attachment_metadata( $data, $post_id ) {
        if ( !$this->get_setting( 'copy-to-s3' ) || !$this->is_plugin_setup() ) {
            return $data;
        }

	    // allow S3 upload to be cancelled for any reason
	    $pre = apply_filters( 'as3cf_pre_update_attachment_metadata', false, $data, $post_id );
	    if ( false !== $pre ) {
		    return $data;
	    }

	    $type          = get_post_mime_type( $post_id );
	    $allowed_types = $this->get_allowed_mime_types();

	    // check mime type of file is in allowed S3 mime types
	    if ( ! in_array( $type, $allowed_types ) ) {
		    return $data;
	    }

	    if ( isset( $data['file'] ) ) {
		    $time = untrailingslashit( dirname( $data['file'] ) );
	    } else {
		    $time = $this->get_attachment_folder_time( $post_id );
		    $time = date( 'Y/m', $time );
	    }

		$prefix = ltrim( trailingslashit( $this->get_setting( 'object-prefix' ) ), '/' );
        $prefix .= ltrim( trailingslashit( $this->get_dynamic_prefix( $time ) ), '/' );

        if ( $this->get_setting( 'object-versioning' ) ) {
        	$prefix .= $this->get_object_version_string( $post_id );
        }

        $file_path = get_attached_file( $post_id, true );
	    $file_name = basename( $file_path );

	    $default_acl = self::DEFAULT_ACL;

	    if ( ( $old_s3object = $this->get_attachment_s3_info( $post_id ) ) ) {
		    // use existing non default ACL if attachment already exists
		    if ( isset( $old_s3object['acl'] ) ) {
			    $default_acl = $old_s3object['acl'];
		    }
	    };

        $acl = apply_filters( 'wps3_upload_acl', $default_acl, $type, $data, $post_id, $this ); // Old naming convention, will be deprecated soon
        $acl = apply_filters( 'as3cf_upload_acl', $acl, $data, $post_id );

        $s3client = $this->get_s3client();

	    $bucket = $this->get_setting( 'bucket' );

	    $s3object = array(
		    'bucket' => $bucket,
		    'key'    => $prefix . $file_name
	    );

	    // store acl if not default
	    if ( $acl != self::DEFAULT_ACL ) {
		    $s3object['acl'] = $acl;
	    }

	    $s3object['region'] = $this->set_s3client_region( $s3object );

	    $args = array(
			'Bucket'     => $bucket,
			'Key'        => $prefix . $file_name,
			'SourceFile' => $file_path,
			'ACL'        => $acl
        );

        // If far future expiration checked (10 years)
		if ( $this->get_setting( 'expires' ) ) {
			$args['Expires'] = date( 'D, d M Y H:i:s O', time()+315360000 );
		}

        $files_to_remove = array();
        if (file_exists($file_path)) {
            $files_to_remove[] = $file_path;
            try {
                $s3client->putObject( $args );
            }
            catch ( Exception $e ) {
                error_log( 'Error uploading ' . $file_path . ' to S3: ' . $e->getMessage() );
                return $data;
            }
        }

        delete_post_meta( $post_id, 'amazonS3_info' );

	    add_post_meta( $post_id, 'amazonS3_info', $s3object );

		$additional_images = array();

        if ( isset( $data['thumb'] ) && $data['thumb'] ) {
			$path = str_replace( $file_name, $data['thumb'], $file_path );
	        if ( file_exists( $path ) ) {
		        $additional_images[] = array(
			        'Key'        => $prefix . $data['thumb'],
			        'SourceFile' => $path
		        );
		        $files_to_remove[]   = $path;
	        }
        } 
        elseif ( !empty( $data['sizes'] ) ) {
        	foreach ( $data['sizes'] as $size ) {
				$path = str_replace( $file_name, $size['file'], $file_path );
		        if ( file_exists( $path ) ) {
			        $additional_images[] = array(
				        'Key'        => $prefix . $size['file'],
				        'SourceFile' => $path
			        );
			        $files_to_remove[]   = $path;
		        }
            }
        }

        // Because we're just looking at the filesystem for files with @2x
        // this should work with most HiDPI plugins
        if ( $this->get_setting( 'hidpi-images' ) ) {
        	$hidpi_images = array();

	        foreach ( $additional_images as $image ) {
	        	$hidpi_path = $this->get_hidpi_file_path( $image['SourceFile'] );
	        	if ( file_exists( $hidpi_path ) ) {
	        		$hidpi_images[] = array(
						'Key'        => $this->get_hidpi_file_path( $image['Key'] ),
						'SourceFile' => $hidpi_path
	        		);
	        		$files_to_remove[] = $hidpi_path;
	        	}
	        }

			$additional_images = array_merge( $additional_images, $hidpi_images );
		}

        foreach ( $additional_images as $image ) {
			try {
				$args = array_merge( $args, $image );
				$s3client->putObject( $args );
			}
			catch ( Exception $e ) {
				error_log( 'Error uploading ' . $args['SourceFile'] . ' to S3: ' . $e->getMessage() );
			}
        }

        if ( $this->get_setting( 'remove-local-file' ) ) {
        	$this->remove_local_files( $files_to_remove );
        }

        return $data;
    }

    function remove_local_files( $file_paths ) {
    	foreach ( $file_paths as $path ) {
    		if ( !@unlink( $path ) ) {
    			error_log( 'Error removing local file ' . $path );
    		}
    	}
    }

    function get_hidpi_file_path( $orig_path ) {
		$hidpi_suffix = apply_filters( 'as3cf_hidpi_suffix', '@2x' );
		$pathinfo = pathinfo( $orig_path );
		return $pathinfo['dirname'] . '/' . $pathinfo['filename'] . $hidpi_suffix . '.' . $pathinfo['extension'];
    }

    function get_object_version_string( $post_id ) {
		if ( get_option( 'uploads_use_yearmonth_folders' ) ) {
			$date_format = 'dHis';
		}
		else {
			$date_format = 'YmdHis';
		}

		$time = $this->get_attachment_folder_time( $post_id );

		$object_version = date( $date_format, $time ) . '/';
		$object_version = apply_filters( 'as3cf_get_object_version_string', $object_version );
		
		return $object_version;
    }

    // Media files attached to a post use the post's date 
    // to determine the folder path they are placed in
    function get_attachment_folder_time( $post_id ) {
		$time = current_time( 'timestamp' );

        if ( !( $attach = get_post( $post_id ) ) ) {
        	return $time;
        }

        if ( !$attach->post_parent ) {
        	return $time;
        }

		if ( !( $post = get_post( $attach->post_parent ) ) ) {
			return $time;
		}

		if ( substr( $post->post_date_gmt, 0, 4 ) > 0 ) {
			return strtotime( $post->post_date_gmt . ' +0000' );
		}

        return $time;
    }

	/**
	 * Create unique names for file to be uploaded to AWS
	 * This only applies when the remove local file option is enabled
	 *
	 * @param array $file An array of data for a single file.
	 *
	 * @return array $file The altered file array with AWS unique filename.
	 */
	function wp_handle_upload_prefilter( $file ){
		if ( ! $this->get_setting( 'copy-to-s3' ) || ! $this->is_plugin_setup() ) {
			return $file;
		}

		// only do this when we are removing local versions of files
		if ( ! $this->get_setting( 'remove-local-file' ) ) {
			return $file;
		}

		$filename = $file['name'];

		// sanitize the file name before we begin processing
		$filename = sanitize_file_name( $filename );

		// separate the filename into a name and extension
		$info = pathinfo( $filename );
		$ext  = ! empty( $info['extension'] ) ? '.' . $info['extension'] : '';
		$name = basename( $filename, $ext );

		// edge case: if file is named '.ext', treat as an empty name
		if ( $name === $ext ) {
			$name = '';
		}

		// rebuild filename with lowercase extension as S3 will have converted extension on upload
		$ext      = strtolower( $ext );
		$filename = $info['filename'] . $ext;

		$time = current_time( 'timestamp' );
		$time = date( 'Y/m', $time );

		$prefix = ltrim( trailingslashit( $this->get_setting( 'object-prefix' ) ), '/' );
		$prefix .= ltrim( trailingslashit( $this->get_dynamic_prefix( $time ) ), '/' );
		$s3client = $this->get_s3client();

		$bucket = $this->get_setting( 'bucket' );

		$number = '';
		while ( $s3client->doesObjectExist( $bucket, $prefix . $filename ) !== false ) {
			$previous = $number;
			++$number;
			if ( '' == $previous ) {
				$filename = $name . $number . $ext;
			} else {
				$filename = str_replace( "$previous$ext", $number . $ext, $filename );
			}
		}

		$file['name'] = $filename;

		return $file;
	}

	function wp_get_attachment_url( $url, $post_id ) {
		$new_url = $this->get_attachment_url( $post_id );
		if ( false === $new_url ) {
			return $url;
		}
		
		$new_url = apply_filters( 'wps3_get_attachment_url', $new_url, $post_id, $this ); // Old naming convention, will be deprecated soon
		$new_url = apply_filters( 'as3cf_wp_get_attachment_url', $new_url, $post_id );

		return $new_url;
	}

	/**
	 * Overrides the url for resized images which have a private ACL on S3
	 * because private thumbnails will break in media modal / upload page
	 *
	 * @param $downsize
	 * @param $attachment_id
	 * @param $size
	 *
	 * @return array
	 */
	function image_downsize( $downsize, $attachment_id, $size ) {
		if ( ! ( $s3object = $this->get_attachment_s3_info( $attachment_id ) ) ) {
			return $downsize;
		}

		if ( ! isset( $s3object['acl'] ) ) {
			return $downsize;
		}

		$meta = get_post_meta( $attachment_id, '_wp_attachment_metadata', true );

		if ( ! isset( $meta['sizes'][ $size ] ) ) {
			return $downsize;
		}

		$size_meta = $meta['sizes'][ $size ];

		list( $width, $height ) = image_constrain_size_for_editor( $size_meta['width'], $size_meta['height'], $size, 'edit' );

		$url = $this->get_attachment_url( $attachment_id, self::DEFAULT_EXPIRES, $size, $meta );

		$downsize = array(
			$url,
			$width,
			$height,
			true // $is_intermediate
		);

		return $downsize;
	}

	function get_attachment_s3_info( $post_id ) {
		return get_post_meta( $post_id, 'amazonS3_info', true );
	}

	function is_plugin_setup() {
		return (bool) $this->get_setting( 'bucket' ) && !is_wp_error( $this->aws->get_client() );
	}

	/**
	 * Generate a link to download a file from Amazon S3 using query string
	 * authentication. This link is only valid for a limited amount of time.
	 *
	 * @param      $post_id Post ID of the attachment
	 * @param int  $expires Seconds for the link to live
	 * @param null $size Size of the image to get
	 * @param bool $check_serve Force check of serve from S3 setting
	 *
	 * @return mixed|void|WP_Error
	 */
	function get_secure_attachment_url( $post_id, $expires = null, $size = null, $check_serve = false ) {
		if ( is_null( $expires ) ) {
			$expires = self::DEFAULT_EXPIRES;
		}
		return $this->get_attachment_url( $post_id, $expires, $size, $check_serve );
	}

	/**
	 * Get the url of the file from Amazon S3
	 *
	 * @param      $post_id Post ID of the attachment
	 * @param null $expires Seconds for the link to live
	 * @param null $size Size of the image to get
	 * @param null $meta Pre retrieved _wp_attachment_metadata for the attachment
	 * @param bool $check_serve Force check of serve from S3 setting
	 *
	 * @return bool|mixed|void|WP_Error
	 */
	function get_attachment_url( $post_id, $expires = null, $size = null, $meta = null, $check_serve = false ) {
		if ( $check_serve && ! $this->get_setting( 'serve-from-s3' ) ) {
			return false;
		}

		if ( ! ( $s3object = $this->get_attachment_s3_info( $post_id ) ) ) {
			return false;
		}

		if ( is_ssl() || $this->get_setting( 'force-ssl' ) ) {
			$scheme = 'https';
		}
		else {
			$scheme = 'http';
		}

		// We don't use $this->get_s3object_region() here because we don't want
		// to make an AWS API call and slow down page loading
		if ( isset( $s3object['region'] ) ) {
			$region = $s3object['region'];
		}
		else {
			$region = '';
		}

		// force use of secured url when ACL has been set to private
		if ( is_null( $expires ) && isset( $s3object['acl'] ) && self::PRIVATE_ACL == $s3object['acl'] ) {
			$expires = self::DEFAULT_EXPIRES;
		}

		$prefix = ( '' == $region ) ? 's3' : 's3-' . $region;

		if ( is_null( $expires ) && $this->get_setting( 'cloudfront' ) ) {
			$domain_bucket = $this->get_setting( 'cloudfront' );
		}
		elseif ( $this->get_setting( 'virtual-host' ) ) {
			$domain_bucket = $s3object['bucket'];
		}
		elseif ( is_ssl() || $this->get_setting( 'force-ssl' ) ) {
			$domain_bucket = $prefix . '.amazonaws.com/' . $s3object['bucket'];
		}
		else {
			$domain_bucket = $s3object['bucket'] . '.' . $prefix . '.amazonaws.com';
		}
        
        if($size) {
	        if ( is_null( $meta ) ) {
		        $meta = get_post_meta( $post_id, '_wp_attachment_metadata', true );
	        }
            if(isset($meta['sizes'][$size]['file'])) {
                $s3object['key'] = dirname($s3object['key']) . '/' . $meta['sizes'][$size]['file'];
            }
        }

		$url = $scheme . '://' . $domain_bucket . '/' . $s3object['key'];

		if ( !is_null( $expires ) ) {
			try {
				$expires = time() + $expires;
			    $secure_url = $this->get_s3client()->getObjectUrl( $s3object['bucket'], $s3object['key'], $expires );
			    $url .= substr( $secure_url, strpos( $secure_url, '?' ) );
			}
			catch ( Exception $e ) {
				return new WP_Error( 'exception', $e->getMessage() );
			}
		}

	    return apply_filters( 'as3cf_get_attachment_url', $url, $s3object, $post_id, $expires );
	}

	function verify_ajax_request() {
		if ( !is_admin() || !wp_verify_nonce( $_POST['_nonce'], $_POST['action'] ) ) {
			wp_die( __( 'Cheatin&#8217; eh?', 'as3cf' ) );
		}

		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'as3cf' ) );
		}
	}

	function ajax_create_bucket() {
		$this->verify_ajax_request();

		if ( !isset( $_POST['bucket_name'] ) || !$_POST['bucket_name'] ) {
			wp_die( __( 'No bucket name provided.', 'as3cf' ) );
		}

		$result = $this->create_bucket( $_POST['bucket_name'] );
		if ( is_wp_error( $result ) ) {
			$out = array( 'error' => $result->get_error_message() );
		}
		else {
			$out = array( 'success' => '1', '_nonce' => wp_create_nonce( 'as3cf-create-bucket' ) );
		}

		echo json_encode( $out );
		exit;		
	}

	function create_bucket( $bucket_name ) {
		try {
		    $this->get_s3client()->createBucket( array( 'Bucket' => $bucket_name ) );
		}
		catch ( Exception $e ) {
			return new WP_Error( 'exception', $e->getMessage() );
		}

		return true;
	}

	function admin_menu( $aws ) {
		$hook_suffix = $aws->add_page( $this->plugin_title, $this->plugin_menu_title, 'manage_options', $this->plugin_slug, array( $this, 'render_page' ) );
		add_action( 'load-' . $hook_suffix , array( $this, 'plugin_load' ) );
	}

	function get_s3client() {
		if ( is_null( $this->s3client ) ) {
			$this->s3client = $this->aws->get_client()->get( 's3' );
		}

		return $this->s3client;
	}

	/**
	 * Get the region of the bucket.
	 *
	 *
	 * @param $s3object
	 * @param $post_id - if supplied will update the s3 meta if no region found
	 *
	 * @return string - region name
	 */
	function get_s3object_region( $s3object, $post_id = null ) {
		if ( ! isset( $s3object['region'] ) ) {
			// if region hasn't been stored in the s3 metadata retrieve using the bucket
			$region = $this->get_s3client()->getBucketLocation( array( 'Bucket' => $s3object['bucket'] ) );

			$s3object['region'] = $region['Location'];

			if ( ! is_null( $post_id ) ) {
				// retrospectively update s3 metadata with region
				update_post_meta( $post_id, 'amazonS3_info', $s3object );
			}
		}

		return $s3object['region'];
	}

	/**
	 * Set the region of the AWS client based on the bucket.
	 *
	 * This is needed for non US standard buckets to add and delete files.
	 *
	 * @param $s3object
	 * @param $post_id
	 *
	 * @return string - region name
	 */
	function set_s3client_region( $s3object, $post_id = null  ) {
		$region = $this->get_s3object_region( $s3object, $post_id );

		if ( $region ) {
			$this->get_s3client()->setRegion( $region );
		}

		return $region;
	}

	function get_buckets() {
		try {
			$result = $this->get_s3client()->listBuckets();
		}
		catch ( Exception $e ) {
			return new WP_Error( 'exception', $e->getMessage() );
		}

		return $result['Buckets'];
	}

	/**
	 * Checks the user has write permission for S3
	 *
	 * @param string $bucket
	 *
	 * @return bool
	 */
	function check_write_permission( $bucket ) {
		// fire up the filesystem API
		$filesystem = WP_Filesystem();
		global $wp_filesystem;
		if ( false === $filesystem || is_null( $wp_filesystem ) ) {
			return new WP_Error( 'exception', __( 'There was an error attempting to access the file system', 'as3cf' ) );
		}

		$uploads       = wp_upload_dir();
		$file_name     = 'as3cf-permission-check.txt';
		$file          = trailingslashit( $uploads['basedir'] ) . $file_name;
		$file_contents = __( 'This is a test file to check if the user has write permission to S3. Delete me if found.', 'as3cf' );
		// create a temp file to upload
		$temp_file = $wp_filesystem->put_contents( $file, $file_contents, FS_CHMOD_FILE );
		if ( false === $temp_file ) {
			return new WP_Error( 'exception', __( 'It looks like we cannot create a file locally to test the S3 permissions', 'as3cf' ) );
		}

		$args = array(
			'Bucket'     => $bucket,
			'Key'        => $file_name,
			'SourceFile' => $file,
			'ACL'        => 'public-read'
		);

		try {
			// attempt to create the test file
			$this->get_s3client()->putObject( $args );
			// delete it straight away if created
			$this->get_s3client()->deleteObject( array(
				'Bucket' => $bucket,
				'Key'    => $file_name
			) );
			$can_write = true;
		} catch ( Exception $e ) {
			// write permission not found
			$can_write = false;
		}

		// delete temp file
		$wp_filesystem->delete( $file );

		return $can_write;
	}

	function plugin_load() {
		$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : $this->plugin_version;

		$src = plugins_url( 'assets/css/styles.css', $this->plugin_file_path );
		wp_enqueue_style( 'as3cf-styles', $src, array(), $version );

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		$src = plugins_url( 'assets/js/script' . $suffix . '.js', $this->plugin_file_path );
		wp_enqueue_script( 'as3cf-script', $src, array( 'jquery' ), $version, true );
		
		wp_localize_script( 'as3cf-script', 'as3cf_i18n', array(
			'create_bucket_prompt'  => __( 'Bucket Name:', 'as3cf' ),
			'create_bucket_error'	=> __( 'Error creating bucket: ', 'as3cf' ),
			'create_bucket_nonce'	=> wp_create_nonce( 'as3cf-create-bucket' )
		) );

		$this->handle_post_request();
	}

	function handle_post_request() {
		if ( empty( $_POST['action'] ) || 'save' != $_POST['action'] ) {
			return;
		}

		if ( empty( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'as3cf-save-settings' ) ) {
			die( __( "Cheatin' eh?", 'amazon-web-services' ) );
		}

		$this->set_settings( array() );

		$post_vars = array( 'bucket', 'virtual-host', 'expires', 'permissions', 'cloudfront', 'object-prefix', 'copy-to-s3', 'serve-from-s3', 'remove-local-file', 'force-ssl', 'hidpi-images', 'object-versioning' );
		foreach ( $post_vars as $var ) {
			if ( !isset( $_POST[$var] ) ) {
				continue;
			}

			$this->set_setting( $var, $_POST[$var] );
		}

		$this->save_settings();

		wp_redirect( 'admin.php?page=' . $this->plugin_slug . '&updated=1' );
		exit;
	}

	function render_page() {
		$this->aws->render_view( 'header', array( 'page_title' => $this->plugin_title ) );
		
		$aws_client = $this->aws->get_client();

		if ( is_wp_error( $aws_client ) ) {
			$this->render_view( 'error', array( 'error' => $aws_client ) );
		}
		else {
			$this->render_view( 'settings' );
		}
		
		$this->aws->render_view( 'footer' );
	}

	function get_dynamic_prefix( $time = null ) {
        $uploads = wp_upload_dir( $time );
        return str_replace( $this->get_base_upload_path(), '', $uploads['path'] );
	}

	// Without the multisite subdirectory
	function get_base_upload_path() {	
		if ( defined( 'UPLOADS' ) && ! ( is_multisite() && get_site_option( 'ms_files_rewriting' ) ) ) {
			return ABSPATH . UPLOADS;
		}

		$upload_path = trim( get_option( 'upload_path' ) );

		if ( empty( $upload_path ) || 'wp-content/uploads' == $upload_path ) {
			return WP_CONTENT_DIR . '/uploads';
		} elseif ( 0 !== strpos( $upload_path, ABSPATH ) ) {
			// $dir is absolute, $upload_path is (maybe) relative to ABSPATH
			return path_join( ABSPATH, $upload_path );
		} else {
			return $upload_path;
		}
	}

}
