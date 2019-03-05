<?php

use DeliciousBrains\WP_Offload_Media\Providers\AWS_Provider;
use DeliciousBrains\WP_Offload_Media\Providers\DigitalOcean_Provider;
use DeliciousBrains\WP_Offload_Media\Providers\GCP_Provider;
use DeliciousBrains\WP_Offload_Media\Providers\Null_Provider;
use DeliciousBrains\WP_Offload_Media\Providers\Provider;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_Content_Replace_URLs;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_EDD_Replace_URLs;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_File_Sizes;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_Filter_Post_Excerpt;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_Meta_WP_Error;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_Region_Meta;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_WPOS3_To_AS3CF;

class Amazon_S3_And_CloudFront extends AS3CF_Plugin_Base {

	/**
	 * @var Provider
	 */
	private $provider;

	/**
	 * @var Provider
	 */
	private $provider_client;

	/**
	 * @var string
	 */
	private $provider_client_region;

	/**
	 * @var array
	 */
	private $uploaded_post_ids = array();

	/**
	 * @var string
	 */
	protected $plugin_title;

	/**
	 * @var string
	 */
	protected $plugin_menu_title;

	/**
	 * @var array
	 */
	protected static $admin_notices = array();

	/**
	 * @var string
	 */
	protected $plugin_prefix = 'as3cf';

	/**
	 * @var AS3CF_Local_To_S3
	 */
	public $filter_local;

	/**
	 * @var AS3CF_S3_To_Local
	 */
	public $filter_provider;

	/**
	 * @var AS3CF_Notices
	 */
	public $notices;

	/**
	 * @var string
	 */
	public $hook_suffix;

	/**
	 * @var array Store if each bucket, used by the plugin and addons, is writable
	 */
	protected static $buckets_check = array();

	/**
	 * @var string
	 */
	protected static $default_provider = 'aws';

	/**
	 * @var array Known provider classes.
	 */
	protected static $provider_classes = array();

	/**
	 * @var AS3CF_Plugin_Compatibility
	 */
	public $plugin_compat;

	const DEFAULT_EXPIRES = 900;

	const SETTINGS_KEY = 'tantan_wordpress_s3';

	/**
	 * @var array
	 */
	protected static $settings_constants = array(
		'AS3CF_SETTINGS',
		'WPOS3_SETTINGS',
	);

	const LATEST_UPGRADE_ROUTINE = 7;

	/**
	 * @param string      $plugin_file_path
	 * @param string|null $slug
	 *
	 * @throws Exception
	 */
	function __construct( $plugin_file_path, $slug = null ) {
		$this->plugin_slug = ( is_null( $slug ) ) ? 'amazon-s3-and-cloudfront' : $slug;

		parent::__construct( $plugin_file_path );

		$this->notices = AS3CF_Notices::get_instance( $this );

		$this->init( $plugin_file_path );
	}

	/**
	 * Abstract class constructor
	 *
	 * @param string $plugin_file_path
	 *
	 * @throws Exception
	 */
	function init( $plugin_file_path ) {
		$this->plugin_title      = __( 'Offload Media', 'amazon-s3-and-cloudfront' );
		$this->plugin_menu_title = __( 'Offload Media', 'amazon-s3-and-cloudfront' );

		static::$provider_classes = array(
			AWS_Provider::get_provider_key_name()          => 'DeliciousBrains\WP_Offload_Media\Providers\AWS_Provider',
			DigitalOcean_Provider::get_provider_key_name() => 'DeliciousBrains\WP_Offload_Media\Providers\DigitalOcean_Provider',
			GCP_Provider::get_provider_key_name()          => 'DeliciousBrains\WP_Offload_Media\Providers\GCP_Provider',
		);

		$this->set_provider();

		// Bundled SDK may require AWS setup before data migrations.
		$this->handle_aws_access_key_migration();

		new Upgrade_Region_Meta( $this );
		new Upgrade_File_Sizes( $this );
		new Upgrade_Meta_WP_Error( $this );
		new Upgrade_Content_Replace_URLs( $this );
		new Upgrade_EDD_Replace_URLs( $this );
		new Upgrade_Filter_Post_Excerpt( $this );
		new Upgrade_WPOS3_To_AS3CF( $this );

		// Plugin setup
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'network_admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'aws_admin_menu', array( $this, 'aws_admin_menu' ) );
		add_filter( 'plugin_action_links', array( $this, 'plugin_actions_settings_link' ), 10, 2 );
		add_filter( 'network_admin_plugin_action_links', array( $this, 'plugin_actions_settings_link' ), 10, 2 );
		add_filter( 'pre_get_space_used', array( $this, 'multisite_get_space_used' ) );
		// display a notice when either lite or pro is automatically deactivated
		add_action( 'pre_current_active_plugins', array( $this, 'plugin_deactivated_notice' ) );
		add_action( 'as3cf_plugin_load', array( $this, 'remove_access_keys_if_constants_set' ) );

		// Attachment screens/modals
		add_action( 'load-upload.php', array( $this, 'load_media_assets' ), 11 );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_attachment_assets' ), 11 );
		add_action( 'add_meta_boxes', array( $this, 'attachment_provider_meta_box' ) );

		// UI AJAX
		add_action( 'wp_ajax_as3cf-get-buckets', array( $this, 'ajax_get_buckets' ) );
		add_action( 'wp_ajax_as3cf-get-url-preview', array( $this, 'ajax_get_url_preview' ) );
		add_action( 'wp_ajax_as3cf_get_attachment_provider_details', array( $this, 'ajax_get_attachment_provider_details' ) );
		add_action( 'wp_ajax_as3cf-get-diagnostic-info', array( $this, 'ajax_get_diagnostic_info' ) );

		// Rewriting URLs, doesn't depend on plugin being setup
		add_filter( 'wp_get_attachment_url', array( $this, 'wp_get_attachment_url' ), 99, 2 );
		add_filter( 'get_image_tag', array( $this, 'maybe_encode_get_image_tag' ), 99, 6 );
		add_filter( 'wp_get_attachment_image_src', array( $this, 'maybe_encode_wp_get_attachment_image_src' ), 99, 4 );
		add_filter( 'wp_prepare_attachment_for_js', array( $this, 'maybe_encode_wp_prepare_attachment_for_js', ), 99, 3 );
		add_filter( 'image_get_intermediate_size', array( $this, 'maybe_encode_image_get_intermediate_size' ), 99, 3 );
		add_filter( 'get_attached_file', array( $this, 'get_attached_file' ), 10, 2 );
		add_filter( 'wp_audio_shortcode', array( $this, 'wp_media_shortcode' ), 100, 5 );
		add_filter( 'wp_video_shortcode', array( $this, 'wp_media_shortcode' ), 100, 5 );

		// Communication with provider, plugin needs to be setup
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'wp_handle_upload_prefilter' ), 1 );
		add_filter( 'wp_handle_sideload_prefilter', array( $this, 'wp_handle_upload_prefilter' ), 1 );
		add_filter( 'wp_update_attachment_metadata', array( $this, 'wp_update_attachment_metadata' ), 110, 2 );
		add_filter( 'delete_attachment', array( $this, 'delete_attachment' ), 20 );
		add_filter( 'update_attached_file', array( $this, 'update_attached_file' ), 100, 2 );

		// Listen for settings changes
		if ( false !== static::settings_constant() ) {
			add_action( 'as3cf_constant_' . static::settings_constant() . '_changed_bucket', array( $this, 'bucket_changed' ) );
		}

		// Content filtering
		$this->filter_local    = new AS3CF_Local_To_S3( $this );
		$this->filter_provider = new AS3CF_S3_To_Local( $this );

		// include compatibility code for other plugins
		$this->plugin_compat = new AS3CF_Plugin_Compatibility( $this );

		load_plugin_textdomain( 'amazon-s3-and-cloudfront', false, dirname( plugin_basename( $plugin_file_path ) ) . '/languages/' );

		// Register modal scripts and styles
		$this->register_modal_assets();

		// Register storage provider scripts and styles
		$this->register_storage_provider_assets();
	}

	/**
	 * @return Provider
	 */
	public function get_provider() {
		return $this->provider;
	}

	/**
	 * @param Provider|string|null $provider
	 *
	 * @throws Exception
	 */
	public function set_provider( $provider = null ) {
		if ( empty( $provider ) ) {
			$provider = $this->get_core_setting( 'provider', static::$default_provider );
		}

		if ( empty( $provider ) ) {
			$wibble = $provider;
		}
		if ( is_string( $provider ) ) {
			$provider = new self::$provider_classes[ $provider ]( $this );
		}

		$this->provider = $provider;
	}

	/**
	 * Returns the currently supported Providers.
	 *
	 * @return array
	 */
	public function get_provider_classes() {
		return self::$provider_classes;
	}

	/**
	 * Returns provider class name for given key.
	 *
	 * @param string $key_name
	 *
	 * @return mixed|null
	 */
	public function get_provider_class( $key_name ) {
		$classes = $this->get_provider_classes();

		return empty( $classes[ $key_name ] ) ? null : $classes[ $key_name ];
	}

	/**
	 * Provider name for given key.
	 *
	 * @param string $key_name
	 *
	 * @return string
	 */
	public function get_provider_name( $key_name ) {
		/** @var Provider $class */
		$class = $this->get_provider_class( $key_name );

		return empty( $class ) ? __( 'Unknown', 'amazon-s3-and-cloudfront' ) : $class::get_provider_name();
	}

	/**
	 * Provider & Service name for given key.
	 *
	 * @param string $key_name
	 *
	 * @return string
	 */
	public function get_provider_service_name( $key_name ) {
		/** @var Provider $class */
		$class = $this->get_provider_class( $key_name );

		return empty( $class ) ? __( 'Unknown', 'amazon-s3-and-cloudfront' ) : $class::get_provider_service_name();
	}

	/**
	 * Get the plugin title to be used in page headings
	 *
	 * @return string
	 */
	function get_plugin_page_title() {
		return apply_filters( 'as3cf_settings_page_title', $this->plugin_title );
	}

	/**
	 * Get the plugin prefix in slug format, ie. replace underscores with hyphens
	 *
	 * @return string
	 */
	function get_plugin_prefix_slug() {
		return str_replace( '_', '-', $this->plugin_prefix );
	}

	/**
	 * Get the nonce key for the settings form of the plugin
	 *
	 * @return string
	 */
	function get_settings_nonce_key() {
		return $this->get_plugin_prefix_slug() . '-save-settings';
	}

	/**
	 * Gets arguements used to render a setting view.
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	function get_setting_args( $key ) {
		$is_defined = $this->get_defined_setting( $key, false );

		$args = array(
			'key'           => $key,
			'disabled'      => false,
			'disabled_attr' => '',
			'tr_class'      => str_replace( '_', '-', $this->plugin_prefix . '-' . $key . '-container' ),
			'setting_msg'   => '',
			'is_defined'    => false,
		);

		if ( false !== $is_defined ) {
			$args['is_defined']    = true;
			$args['disabled']      = true;
			$args['disabled_attr'] = 'disabled="disabled"';
			$args['tr_class']      .= ' as3cf-defined-setting';
			$args['setting_msg']   = '<span class="as3cf-defined-in-config">' . __( 'defined in wp-config.php', 'as3cf' ) . '</span>';
		}

		return $args;
	}

	/**
	 * Accessor for a plugin setting with conditions to defaults and upgrades
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return int|mixed|string|WP_Error
	 */
	function get_setting( $key, $default = '' ) {
		// use settings from $_POST when generating URL preview via AJAX
		if ( isset( $_POST['action'] ) && 'as3cf-get-url-preview' == sanitize_key( $_POST['action'] ) ) { // input var okay
			$this->verify_ajax_request();
			$value = empty( $default ) ? 0 : $default;
			if ( isset( $_POST[ $key ] ) ) { // input var okay
				$value = $_POST[ $key ]; // input var okay
				if ( is_array( $value ) ) {
					// checkbox is checked
					$value = $value[1];
				}
			}

			return $value;
		}

		$settings = $this->get_settings();

		// If legacy setting set, migrate settings
		if ( isset( $settings['wp-uploads'] ) &&
		     $settings['wp-uploads'] &&
		     in_array( $key, array( 'copy-to-s3', 'serve-from-s3', ) )
		) {
			return '1';
		}

		// Don't run upgrade routines on fresh install
		if ( 'post_meta_version' === $key && ! isset( $settings['post_meta_version'] ) ) {
			$routine = self::LATEST_UPGRADE_ROUTINE;

			$this->set_setting( 'post_meta_version', $routine );
			$this->save_settings();

			return $routine;
		}

		// Turn on object versioning by default
		if ( 'object-versioning' == $key && ! isset( $settings['object-versioning'] ) ) {
			return '1';
		}

		// Default object prefix
		if ( 'object-prefix' == $key && ! isset( $settings['object-prefix'] ) ) {
			return $this->get_default_object_prefix();
		}

		// Default use year and month folders
		if ( 'use-yearmonth-folders' == $key && ! isset( $settings['use-yearmonth-folders'] ) ) {
			return get_option( 'uploads_use_yearmonth_folders' );
		}

		// Default enable object prefix - enabled unless path is empty
		if ( 'enable-object-prefix' == $key ) {
			if ( isset( $settings['enable-object-prefix'] ) && '0' == $settings['enable-object-prefix'] ) {
				return 0;
			}

			if ( isset( $settings['object-prefix'] ) && '' == trim( $settings['object-prefix'] ) ) {
				if ( false === $this->get_defined_setting( 'object-prefix', false ) ) {
					return 0;
				}
			} else {
				return 1;
			}
		}

		// Region
		if ( false !== ( $region = $this->get_setting_region( $settings, $key, $default ) ) ) {
			return $region;
		}

		// Domain setting since 0.8
		if ( 'domain' === $key && ! isset( $settings['domain'] ) ) {
			if ( $this->get_setting( 'cloudfront' ) ) {
				$domain = 'cloudfront';
			} elseif ( $this->get_setting( 'virtual-host' ) ) {
				$domain = $this->upgrade_virtual_host();
			} else {
				$domain = 'path';
			}

			return $domain;
		}

		// 1.1 Update 'Bucket as Domain' to new CloudFront/Domain UI
		if ( 'domain' === $key && 'virtual-host' === $settings[ $key ] ) {
			return $this->upgrade_virtual_host();
		}

		// SSL radio buttons since 0.8
		if ( 'ssl' == $key && ! isset( $settings['ssl'] ) ) {
			if ( $this->get_setting( 'force-ssl', false ) ) {
				$ssl = 'https';
			} else {
				$ssl = 'request';
			}

			$this->set_setting( 'ssl', $ssl );
			$this->remove_setting( 'force-ssl' );
			$this->save_settings();

			return $ssl;
		}

		// Force HTTPS since 1.3
		if ( 'force-https' === $key && ! isset( $settings['force-https'] ) ) {
			$ssl = $this->get_setting( 'ssl', 'request' );

			$force_https = false;
			if ( 'https' === $ssl ) {
				$force_https = true;
			} elseif ( 'http' === $ssl ) {
				$this->maybe_display_deprecated_http_notice();
			}

			$this->set_setting( 'force-https', $force_https );
			$this->remove_setting( 'ssl' );
			$this->save_settings();

			return $force_https;
		}

		// Access Key ID since 2.0.
		if ( 'access-key-id' === $key && ! isset( $settings['access-key-id'] ) ) {
			$aws_access_key_id = $this->get_setting( 'aws-access-key-id' );

			$this->set_setting( 'access-key-id', $aws_access_key_id );
			$this->remove_setting( 'aws-access-key-id' );
			$this->save_settings();

			return $aws_access_key_id;
		}

		// Secret Access Key since 2.0.
		if ( 'secret-access-key' === $key && ! isset( $settings['secret-access-key'] ) ) {
			$aws_secret_access_key = $this->get_setting( 'aws-secret-access-key' );

			$this->set_setting( 'secret-access-key', $aws_secret_access_key );
			$this->remove_setting( 'aws-secret-access-key' );
			$this->save_settings();

			return $aws_secret_access_key;
		}

		$value = parent::get_setting( $key, $default );

		// Provider
		if ( false !== ( $provider = $this->get_setting_provider( $key, $value ) ) ) {
			return $provider;
		}

		// Bucket
		if ( false !== ( $bucket = $this->get_setting_bucket( $key, $value ) ) ) {
			return $bucket;
		}

		return apply_filters( 'as3cf_setting_' . $key, $value );
	}

	/**
	 * Get the provider and if a constant save to database
	 *
	 * @param string $key
	 * @param string $value
	 * @param string $constant
	 *
	 * @return string|false
	 */
	public function get_setting_provider( $key, $value, $constant = 'AS3CF_PROVIDER' ) {
		if ( 'provider' === $key && defined( $constant ) ) {
			$provider = constant( $constant );

			if ( ! empty( $value ) ) {
				// Clear bucket
				$this->remove_setting( 'provider' );
				$this->save_settings();
			}

			return $provider;
		}

		return false;
	}

	/**
	 * Get the region setting
	 *
	 * @param array  $settings
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return bool|string|WP_Error
	 */
	public function get_setting_region( $settings, $key, $default ) {
		// Region of bucket if not already retrieved
		if ( 'region' === $key && ! isset( $settings['region'] ) ) {
			$bucket = $this->get_setting( 'bucket' );
			$region = $default;
			if ( $bucket ) {
				$region = $this->get_bucket_region( $bucket );
			}

			// Store the region for future use
			if ( is_string( $region ) ) {
				parent::set_setting( 'region', $region );
				$this->save_settings();
			}

			return $region;
		}

		// Region of bucket translation
		if ( 'region' === $key && isset( $settings['region'] ) ) {
			return $this->get_provider()->sanitize_region( $settings['region'] );
		}

		return false;
	}

	/**
	 * Get the bucket and if a constant save to database and clear region
	 *
	 * @param string $key
	 * @param string $value
	 * @param string $constant
	 *
	 * @return string|false
	 */
	public function get_setting_bucket( $key, $value, $constant = 'AS3CF_BUCKET' ) {
		if ( 'bucket' === $key && defined( $constant ) ) {
			$bucket = constant( $constant );

			if ( ! empty( $value ) ) {
				// Clear bucket
				$this->remove_setting( 'bucket' );
				$this->save_settings();
			}

			$this->remove_region_on_constant_change( $bucket, $constant );

			return $bucket;
		}

		return false;
	}

	/**
	 * Remove region on constant change.
	 *
	 * @param string $bucket
	 * @param string $constant
	 */
	private function remove_region_on_constant_change( $bucket, $constant ) {
		$key   = 'as3cf_constant_' . strtolower( $constant );
		$value = get_site_transient( $key );

		if ( false === $value || $bucket !== $value ) {
			set_site_transient( $key, $bucket );
		}

		if ( false !== $value && $bucket !== $value ) {
			$this->bucket_changed();
		}
	}

	/**
	 * Perform necessary actions when the chosen bucket is changed.
	 */
	public function bucket_changed() {
		$this->remove_setting( 'region' );
		$this->save_settings();
	}

	/**
	 * Filter in defined settings with sensible defaults.
	 *
	 * @param array $settings
	 *
	 * @return array $settings
	 */
	function filter_settings( $settings ) {
		$defined_settings = $this->get_defined_settings();

		// Bail early if there are no defined settings
		if ( empty( $defined_settings ) ) {
			return $settings;
		}

		foreach ( $defined_settings as $key => $value ) {
			$allowed_values = array();

			if ( 'domain' === $key ) {
				$allowed_values = array(
					'subdomain',
					'path',
					'virtual-host',
					'cloudfront',
				);
			}

			$checkboxes = array(
				'copy-to-s3',
				'serve-from-s3',
				'enable-object-prefix',
				'remove-local-file',
				'object-versioning',
				'force-https',
			);

			if ( in_array( $key, $checkboxes ) ) {
				$allowed_values = array( '0', '1' );
			}

			// Unexpected value, remove from defined_settings array.
			if ( ! empty( $allowed_values ) && ! in_array( $value, $allowed_values ) ) {
				$this->remove_defined_setting( $key );
				continue;
			}

			// Value defined successfully
			$settings[ $key ] = $value;
		}

		return $settings;
	}

	/**
	 * Setter for a plugin setting with custom hooks
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	function set_setting( $key, $value ) {
		// Run class specific hooks before the setting is saved
		$this->pre_set_setting( $key, $value );

		$value = apply_filters( 'as3cf_set_setting_' . $key, $value );

		// Remove disallowed characters from custom domain
		if ( 'cloudfront' === $key ) {
			$value = AS3CF_Utils::sanitize_custom_domain( $value );
		}

		parent::set_setting( $key, $value );
	}

	/**
	 * Disables the save button if all settings have been defined.
	 *
	 * @param array $defined_settings
	 *
	 * @return string
	 */
	function maybe_disable_save_button( $defined_settings = array() ) {
		$attr                 = 'disabled="disabled"';
		$defined_settings     = ! empty( $defined_settings ) ? $defined_settings : $this->get_defined_settings();
		$whitelisted_settings = $this->get_settings_whitelist();
		$settings_to_skip     = array(
			'bucket',
			'region',
			'permissions',
			'virtual-host',
		);

		foreach ( $whitelisted_settings as $setting ) {
			if ( in_array( $setting, $settings_to_skip ) ) {
				continue;
			}

			if ( 'object-prefix' === $setting ) {
				if ( isset( $defined_settings['enable-object-prefix'] ) && '0' === $defined_settings['enable-object-prefix'] ) {
					continue;
				}
			}

			if ( 'cloudfront' === $setting ) {
				if ( isset( $defined_settings['domain'] ) && 'cloudfront' !== $defined_settings['domain'] ) {
					continue;
				}
			}

			if ( ! isset( $defined_settings[ $setting ] ) ) {
				// If we're here, there's a setting that hasn't been defined.
				return '';
			}
		}

		return $attr;
	}

	/**
	 * Return the default object prefix
	 *
	 * @return string
	 */
	function get_default_object_prefix() {
		if ( is_multisite() ) {
			return 'wp-content/uploads/';
		}

		$uploads = wp_upload_dir();
		$parts   = parse_url( $uploads['baseurl'] );
		$path    = ltrim( $parts['path'], '/' );

		return trailingslashit( $path );
	}

	/**
	 * Allowed mime types array that can be edited for specific provider uploading
	 *
	 * @return array
	 */
	function get_allowed_mime_types() {
		return apply_filters( 'as3cf_allowed_mime_types', get_allowed_mime_types() );
	}

	/**
	 * Wrapper for scheduling  cron jobs
	 *
	 * @param string      $hook
	 * @param null|string $interval Defaults to hook if not supplied
	 * @param array       $args
	 */
	public function schedule_event( $hook, $interval = null, $args = array() ) {
		if ( is_null( $interval ) ) {
			$interval = $hook;
		}

		// Always schedule events on primary blog
		$this->switch_to_blog();

		if ( ! wp_next_scheduled( $hook ) ) {
			wp_schedule_event( time(), $interval, $hook, $args );
		}

		$this->restore_current_blog();
	}

	/**
	 * Wrapper for clearing scheduled events for a specific cron job
	 *
	 * @param string $hook
	 */
	public function clear_scheduled_event( $hook ) {
		$timestamp = wp_next_scheduled( $hook );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, $hook );
		}

		if ( is_multisite() ) {
			// Always clear schedule events on primary blog
			$this->switch_to_blog();

			$timestamp = wp_next_scheduled( $hook );
			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, $hook );
			}

			$this->restore_current_blog();
		}
	}

	/**
	 * Get local URL preview.
	 *
	 * @param bool   $escape
	 * @param string $suffix
	 *
	 * @return string
	 */
	protected function get_local_url_preview( $escape = true, $suffix = 'photo.jpg' ) {
		$uploads = wp_upload_dir();
		$url     = trailingslashit( $uploads['url'] ) . $suffix;

		// Replace hyphens with non breaking hyphens for formatting
		if ( $escape ) {
			$url = str_replace( '-', '&#8209;', $url );
		}

		return $url;
	}

	/**
	 * Generate a preview of the URL of files uploaded to provider
	 *
	 * @param bool   $escape
	 * @param string $suffix
	 *
	 * @return string
	 */
	function get_url_preview( $escape = true, $suffix = 'photo.jpg' ) {
		$scheme = $this->get_url_scheme();
		$bucket = $this->get_setting( 'bucket' );
		$path   = $this->maybe_update_cloudfront_path( $this->get_file_prefix() );
		$region = $this->get_setting( 'region' );
		if ( is_wp_error( $region ) ) {
			$region = '';
		}

		$domain = $this->get_provider()->get_url_domain( $bucket, $region, null, array(), true );

		$url = $scheme . '://' . $domain . '/' . $path . $suffix;

		// Replace hyphens with non breaking hyphens for formatting
		if ( $escape ) {
			$url = str_replace( '-', '&#8209;', $url );
		}

		return $url;
	}

	/**
	 * AJAX handler for get_url_preview()
	 */
	function ajax_get_url_preview() {
		$this->verify_ajax_request();

		$url = $this->get_url_preview();

		$out = array(
			'success'      => '1',
			'url'          => $url,
			'seo_friendly' => AS3CF_Utils::seo_friendly_url( $this->get_url_preview( false ) ),
		);

		$this->end_ajax( $out );
	}

	/**
	 * AJAX handler for get_diagnostic_info()
	 */
	function ajax_get_diagnostic_info() {
		$this->verify_ajax_request();

		$out = array(
			'success'         => '1',
			'diagnostic_info' => $this->output_diagnostic_info(),
		);

		$this->end_ajax( $out );
	}

	/**
	 * Remove access keys from saved settings if a key constant is defined.
	 */
	public function remove_access_keys_if_constants_set() {
		if ( AWS_Provider::is_any_access_key_constant_defined() ) {
			$this->remove_access_keys();
		}
	}

	/**
	 * Remove access keys from settings.
	 */
	protected function remove_access_keys() {
		$this->remove_setting( 'access-key-id' );
		$this->remove_setting( 'secret-access-key' );
		$this->save_settings();
	}

	/**
	 * Delete bulk objects from an provider bucket
	 *
	 * @param string $region
	 * @param string $bucket
	 * @param array  $objects
	 * @param bool   $log_error
	 * @param bool   $return_on_error
	 * @param bool   $force_new_provider_client if we are deleting in bulk, force new provider client
	 *                                          to cope with possible different regions
	 *
	 * @return bool
	 */
	function delete_objects( $region, $bucket, $objects, $log_error = false, $return_on_error = false, $force_new_provider_client = false ) {
		$chunks = array_chunk( $objects, 1000 );

		try {
			foreach ( $chunks as $chunk ) {
				$this->get_provider_client( $region, $force_new_provider_client )->delete_objects( array(
					'Bucket'  => $bucket,
					'Objects' => $chunk,
				) );
			}
		} catch ( Exception $e ) {
			if ( $log_error ) {
				AS3CF_Error::log( 'Error removing files from bucket: ' . $e->getMessage() );
			}

			return false;
		}

		return true;
	}

	/**
	 * Removes an attachment's files from provider.
	 *
	 * @param int   $post_id
	 * @param array $provider_object
	 * @param bool  $remove_backup_sizes       remove previous edited image versions
	 * @param bool  $log_error
	 * @param bool  $return_on_error
	 * @param bool  $force_new_provider_client if we are deleting in bulk, force new provider client
	 *                                         to cope with possible different regions
	 */
	function remove_attachment_files_from_provider( $post_id, $provider_object, $remove_backup_sizes = true, $log_error = false, $return_on_error = false, $force_new_provider_client = false ) {
		$prefix = $this->normalize_object_prefix( $provider_object['key'] );
		$bucket = $provider_object['bucket'];
		$region = $this->get_provider_object_region( $provider_object );
		$paths  = AS3CF_Utils::get_attachment_file_paths( $post_id, false, false, $remove_backup_sizes );
		$paths  = apply_filters( 'as3cf_remove_attachment_paths', $paths, $post_id, $provider_object, $remove_backup_sizes );

		if ( is_wp_error( $region ) ) {
			$region = '';
		}

		$objects_to_remove = array();

		foreach ( $paths as $path ) {
			$objects_to_remove[] = array(
				'Key' => $prefix . wp_basename( $path ),
			);
		}

		// finally delete the objects from provider
		$this->delete_objects( $region, $bucket, $objects_to_remove, $log_error, $return_on_error, $force_new_provider_client );
	}

	/**
	 * Removes an attachment and intermediate image size files from provider
	 *
	 * @param int  $post_id
	 * @param bool $force_new_provider_client if we are deleting in bulk, force new provider client
	 *                                        to cope with possible different regions
	 */
	function delete_attachment( $post_id, $force_new_provider_client = false ) {
		if ( ! $this->is_plugin_setup( true ) ) {
			return;
		}

		if ( ! ( $provider_object = $this->get_attachment_provider_info( $post_id ) ) ) {
			return;
		}

		if ( ! $this->is_attachment_served_by_provider( $post_id, true ) ) {
			return;
		}

		$this->remove_attachment_files_from_provider( $post_id, $provider_object, true, true, true, $force_new_provider_client );

		delete_post_meta( $post_id, 'amazonS3_info' );
	}

	/**
	 * Handles the upload of the attachment to provider when an attachment is updated using
	 * the 'wp_update_attachment_metadata' filter
	 *
	 * @param array $data meta data for attachment
	 * @param int   $post_id
	 *
	 * @return array
	 * @throws Exception
	 */
	function wp_update_attachment_metadata( $data, $post_id ) {
		if ( ! $this->is_plugin_setup( true ) ) {
			return $data;
		}

		if ( ! ( $old_provider_object = $this->get_attachment_provider_info( $post_id ) ) && ! $this->get_setting( 'copy-to-s3' ) ) {
			// abort if not already uploaded to provider and the copy setting is off
			return $data;
		}

		// allow provider upload to be cancelled for any reason
		$pre = apply_filters( 'as3cf_pre_update_attachment_metadata', false, $data, $post_id, $old_provider_object );
		if ( false !== $pre ) {
			return $data;
		}

		// upload attachment to provider
		$provider_meta = $this->upload_attachment( $post_id, $data );

		if ( is_wp_error( $provider_meta ) ) {
			return $data;
		}

		return $provider_meta;
	}

	/**
	 * Upload attachment to provider
	 *
	 * @param int         $post_id
	 * @param array|null  $data
	 * @param string|null $file_path
	 * @param bool        $force_new_provider_client if we are uploading in bulk, force new provider client
	 *                                               to cope with possible different regions
	 * @param bool        $remove_local_files
	 *
	 * @return array|WP_Error $provider_object|$meta If meta is supplied, return it. Else return provider meta
	 * @throws Exception
	 */
	public function upload_attachment( $post_id, $data = null, $file_path = null, $force_new_provider_client = false, $remove_local_files = true ) {
		$return_metadata = null;
		if ( is_null( $data ) ) {
			$data = wp_get_attachment_metadata( $post_id, true );
		} else {
			// As we have passed in the meta, return it later
			$return_metadata = $data;
		}

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		// Allow provider upload to be hijacked / cancelled for any reason
		try {
			$pre = apply_filters( 'as3cf_pre_upload_attachment', false, $post_id, $data );
		} catch ( Exception $e ) {
			return $this->return_upload_error( $e->getMessage() );
		}

		if ( false !== $pre ) {
			return $data;
		}

		if ( is_null( $file_path ) ) {
			$file_path = get_attached_file( $post_id, true );
		}

		// Check file exists locally before attempting upload
		if ( ! file_exists( $file_path ) ) {
			$error_msg = sprintf( __( 'File %s does not exist', 'amazon-s3-and-cloudfront' ), $file_path );

			return $this->return_upload_error( $error_msg, $return_metadata );
		}

		// Get original file's stats.
		$filesize      = filesize( $file_path );
		$file_name     = wp_basename( $file_path );
		$type          = get_post_mime_type( $post_id );
		$allowed_types = $this->get_allowed_mime_types();

		// check mime type of file is in allowed provider mime types
		if ( ! in_array( $type, $allowed_types ) ) {
			$error_msg = sprintf( __( 'Mime type %s is not allowed', 'amazon-s3-and-cloudfront' ), $type );

			return $this->return_upload_error( $error_msg, $return_metadata );
		}

		$acl = $this->get_provider()->get_default_acl();

		// check the attachment already exists in provider, eg. edit or restore image
		if ( ( $old_provider_object = $this->get_attachment_provider_info( $post_id ) ) ) {
			// Must be offloaded to same provider as currently configured.
			if ( ! $this->is_attachment_served_by_provider( $post_id, true ) ) {
				return $this->return_upload_error( __( 'Already offloaded to a different provider', 'amazon-s3-and-cloudfront' ), $return_metadata );
			}

			// use existing non default ACL if attachment already exists
			if ( isset( $old_provider_object['acl'] ) ) {
				$acl = $old_provider_object['acl'];
			}

			// use existing prefix
			$prefix = $this->normalize_object_prefix( $old_provider_object['key'] );
			// use existing bucket
			$bucket = $old_provider_object['bucket'];
			// get existing region
			$region = isset( $old_provider_object['region'] ) ? $old_provider_object['region'] : '';
		} else {
			// derive prefix from various settings
			$time   = $this->get_attachment_folder_year_month( $post_id, $data );
			$prefix = $this->get_file_prefix( $time );

			// use bucket from settings
			$bucket = $this->get_setting( 'bucket' );
			$region = $this->get_setting( 'region' );
			if ( is_wp_error( $region ) ) {
				$region = '';
			}
		}

		$acl = apply_filters( 'wps3_upload_acl', $acl, $type, $data, $post_id, $this ); // Old naming convention, will be deprecated soon
		$acl = apply_filters( 'as3cf_upload_acl', $acl, $data, $post_id );

		$args = array(
			'Bucket'       => $bucket,
			'Key'          => $prefix . $file_name,
			'SourceFile'   => $file_path,
			'ACL'          => $acl,
			'ContentType'  => $type,
			'CacheControl' => 'max-age=31536000',
			'Expires'      => date( 'D, d M Y H:i:s O', time() + 31536000 ),
		);

		// TODO: Remove GZIP functionality.
		// Handle gzip on supported items
		if ( $this->should_gzip_file( $file_path, $type ) && false !== ( $gzip_body = gzencode( file_get_contents( $file_path ) ) ) ) {
			unset( $args['SourceFile'] );

			$args['Body']            = $gzip_body;
			$args['ContentEncoding'] = 'gzip';
		}

		$image_size      = wp_attachment_is_image( $post_id ) ? 'full' : '';
		$args            = apply_filters( 'as3cf_object_meta', $args, $post_id, $image_size, false );
		$provider_object = array(
			'provider' => $this->get_provider()->get_provider_key_name(),
			'region'   => $bucket !== $args['Bucket'] ? $this->get_bucket_region( $args['Bucket'], true ) : $region,
			'bucket'   => $args['Bucket'],
			'key'      => $args['Key'],
			'acl'      => $args['ACL'],
		);

		// Do not store object ACL if set to the default value.
		if ( $provider_object['acl'] === $this->get_provider()->get_default_acl() ) {
			unset( $provider_object['acl'] );
		}

		do_action( 'as3cf_upload_attachment_pre_remove', $post_id, $provider_object, $prefix, $args );

		$files_to_remove = array();

		$provider_client = $this->get_provider_client( $provider_object['region'], $force_new_provider_client );

		try {
			$provider_client->upload_object( $args );
			$files_to_remove[] = $file_path;
		} catch ( Exception $e ) {
			$error_msg = sprintf( __( 'Error offloading %s to provider: %s', 'amazon-s3-and-cloudfront' ), $file_path, $e->getMessage() );

			return $this->return_upload_error( $error_msg, $return_metadata );
		}

		delete_post_meta( $post_id, 'amazonS3_info' );

		add_post_meta( $post_id, 'amazonS3_info', $provider_object );

		$file_paths            = AS3CF_Utils::get_attachment_file_paths( $post_id, false, $data );
		$additional_images     = array();
		$provider_object_sizes = array();

		foreach ( $file_paths as $size => $file_path ) {
			if ( ! in_array( $file_path, $files_to_remove ) ) {
				$acl = apply_filters( 'as3cf_upload_acl_sizes', $this->get_provider()->get_default_acl(), $size, $post_id, $data );

				$additional_images[ $size ] = array(
					'Key'         => $prefix . wp_basename( $file_path ),
					'SourceFile'  => $file_path,
					'ACL'         => $acl,
					'ContentType' => $this->get_mime_type( $file_path ),
				);

				if ( $this->get_provider()->get_default_acl() !== $acl ) {
					$provider_object_sizes[ $size ]['acl'] = $acl;
				}
			}
		}

		$upload_errors = array();

		foreach ( $additional_images as $size => $image ) {
			$args = apply_filters( 'as3cf_object_meta', array_merge( $args, $image ), $post_id, $size, false );

			if ( ! file_exists( $args['SourceFile'] ) ) {
				$upload_errors[] = $this->return_upload_error( sprintf( __( 'File %s does not exist', 'amazon-s3-and-cloudfront' ), $args['SourceFile'] ) );
				continue;
			}

			try {
				$provider_client->upload_object( $args );
				$files_to_remove[] = $image['SourceFile'];
			} catch ( Exception $e ) {
				$upload_errors[] = $this->return_upload_error( sprintf( __( 'Error offloading %s to provider: %s', 'amazon-s3-and-cloudfront' ), $args['SourceFile'], $e->getMessage() ) );
			}
		}

		$remove_local_files_setting = $this->get_setting( 'remove-local-file' );

		if ( $remove_local_files ) {
			if ( $remove_local_files_setting ) {
				// Allow other functions to remove files after they have processed
				$files_to_remove = apply_filters( 'as3cf_upload_attachment_local_files_to_remove', $files_to_remove, $post_id, $file_path );

				// Remove duplicates
				$files_to_remove = array_unique( $files_to_remove );

				// Delete the files and record original file's size before removal.
				$this->remove_local_files( $files_to_remove, $post_id );

				// Store filesize in the attachment meta data for use by WP
				if ( 0 < $filesize ) {
					$data['filesize'] = $filesize;

					if ( is_null( $return_metadata ) ) {
						// Update metadata with filesize
						update_post_meta( $post_id, '_wp_attachment_metadata', $data );
					}
				}
			}
		}

		// Make sure we don't have cached file sizes in the meta if we previously added it.
		if ( ! $remove_local_files_setting && isset( $data['filesize'] ) && ! empty( get_post_meta( $post_id, 'as3cf_filesize_total', true ) ) ) {
			$data = $this->maybe_cleanup_filesize_metadata( $post_id, $data, empty( $return_metadata ) );
		}

		if ( ! empty( $provider_object_sizes ) ) {
			// Additional image sizes have custom ACLs, update meta
			$provider_object['sizes'] = $provider_object_sizes;
			update_post_meta( $post_id, 'amazonS3_info', $provider_object );
		}

		// Keep track of attachments uploaded by this instance.
		$this->uploaded_post_ids[] = $post_id;

		do_action( 'as3cf_post_upload_attachment', $post_id, $provider_object );

		if ( $upload_errors ) {
			return $this->consolidate_upload_errors( $upload_errors );
		}

		if ( ! is_null( $return_metadata ) ) {
			// If the attachment metadata is supplied, return it
			return $data;
		}

		return $provider_object;
	}

	/**
	 * Get a file's real mime type
	 *
	 * @param string $file_path
	 *
	 * @return string
	 */
	protected function get_mime_type( $file_path ) {
		$file_type = wp_check_filetype_and_ext( $file_path, wp_basename( $file_path ) );

		return $file_type['type'];
	}

	/**
	 * Should gzip file
	 *
	 * @param string $file_path
	 * @param string $type
	 *
	 * @return bool
	 */
	protected function should_gzip_file( $file_path, $type ) {
		$mimes = $this->get_mime_types_to_gzip( true );

		if ( in_array( $type, $mimes ) && is_readable( $file_path ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get mime types to gzip
	 *
	 * @param bool $media_library
	 *
	 * @return array
	 */
	protected function get_mime_types_to_gzip( $media_library = false ) {
		$mimes = apply_filters( 'as3cf_gzip_mime_types', array(
			'css'   => 'text/css',
			'eot'   => 'application/vnd.ms-fontobject',
			'html'  => 'text/html',
			'ico'   => 'image/x-icon',
			'js'    => 'application/javascript',
			'json'  => 'application/json',
			'otf'   => 'application/x-font-opentype',
			'rss'   => 'application/rss+xml',
			'svg'   => 'image/svg+xml',
			'ttf'   => 'application/x-font-ttf',
			'woff'  => 'application/font-woff',
			'woff2' => 'application/font-woff2',
			'xml'   => 'application/xml',
		), $media_library );

		return $mimes;
	}

	/**
	 * Helper to record errors and return meta data on upload error.
	 *
	 * @param string     $error_msg
	 * @param array|null $return
	 *
	 * @return array|WP_Error
	 */
	protected function return_upload_error( $error_msg, $return = null ) {

		AS3CF_Error::log( $error_msg );

		if ( is_null( $return ) ) {
			return new WP_Error( 'exception', $error_msg );
		}

		return $return;
	}

	/**
	 * Remove files from the local site, recording total filesize in meta if attachment ID given.
	 *
	 * @param array $file_paths array of files to remove
	 * @param int   $attachment_id
	 */
	function remove_local_files( $file_paths, $attachment_id = 0 ) {
		$filesize_total = 0;

		foreach ( $file_paths as $index => $path ) {
			if ( ! empty( $attachment_id ) && is_int( $attachment_id ) ) {
				$bytes = filesize( $path );

				$filesize_total += ( false !== $bytes ) ? $bytes : 0;
			}

			// Individual files might still be kept local, but we're still going to count them towards total above.
			if ( false !== ( $pre = apply_filters( 'as3cf_preserve_file_from_local_removal', false, $path ) ) ) {
				continue;
			}

			if ( ! @unlink( $path ) ) {
				$message = 'Error removing local file ';

				if ( ! file_exists( $path ) ) {
					$message = "Error removing local file. Couldn't find the file at ";
				} else if ( ! is_writable( $path ) ) {
					$message = 'Error removing local file. Ownership or permissions are mis-configured for ';
				}

				AS3CF_Error::log( $message . $path );
			}
		}

		// If we were able to sum up file sizes for an attachment, record it.
		if ( $filesize_total > 0 ) {
			update_post_meta( $attachment_id, 'as3cf_filesize_total', $filesize_total );
		}
	}

	/**
	 * Get the object versioning string prefix
	 *
	 * @return string
	 */
	function get_object_version_string() {
		if ( $this->get_setting( 'use-yearmonth-folders' ) ) {
			$date_format = 'dHis';
		} else {
			$date_format = 'YmdHis';
		}

		// Use current time so that object version is unique
		$time = current_time( 'timestamp' );

		$object_version = date( $date_format, $time ) . '/';
		$object_version = apply_filters( 'as3cf_get_object_version_string', $object_version );

		return $object_version;
	}

	/**
	 * Get the upload folder time from given URL
	 *
	 * @param string $url
	 *
	 * @return null|string
	 */
	function get_folder_time_from_url( $url ) {
		if ( ! is_string( $url ) ) {
			return null;
		}

		preg_match( '@[0-9]{4}/[0-9]{2}@', $url, $matches );

		if ( isset( $matches[0] ) ) {
			return $matches[0];
		}

		return null;
	}

	/**
	 * Get the year/month string for attachment's upload.
	 *
	 * Fall back to post date if attached, otherwise current date.
	 *
	 * @param int   $post_id Attachment's ID
	 * @param array $data    Attachment's metadata
	 *
	 * @return string
	 */
	function get_attachment_folder_year_month( $post_id, $data = null ) {
		if ( isset( $data['file'] ) ) {
			$time = $this->get_folder_time_from_url( $data['file'] );
		}

		if ( empty( $time ) && ( $local_url = wp_get_attachment_url( $post_id ) ) ) {
			$time = $this->get_folder_time_from_url( $local_url );
		}

		if ( empty( $time ) ) {
			$time = date( 'Y/m' );

			if ( ! ( $attach = get_post( $post_id ) ) ) {
				return $time;
			}

			if ( ! $attach->post_parent ) {
				return $time;
			}

			if ( ! ( $post = get_post( $attach->post_parent ) ) ) {
				return $time;
			}

			if ( substr( $post->post_date_gmt, 0, 4 ) > 0 ) {
				return date( 'Y/m', strtotime( $post->post_date_gmt . ' +0000' ) );
			}
		}

		return $time;
	}

	/**
	 * Filter file details before upload.
	 *
	 * @param array $file An array of data for a single file.
	 *
	 * @return array $file The altered file array with AWS unique filename.
	 */
	public function wp_handle_upload_prefilter( $file ) {
		// Get Post ID if uploaded in post screen.
		$post_id = filter_input( INPUT_POST, 'post_id', FILTER_VALIDATE_INT );

		$file['name'] = $this->filter_unique_filename( $file['name'], $post_id );

		return $file;
	}

	/**
	 * Create unique names for file to be uploaded to AWS.
	 * This only applies when the remove local file option is enabled.
	 *
	 * @param string $filename Unique file name.
	 * @param int    $post_id  Attachment's parent Post ID.
	 *
	 * @return string
	 */
	public function filter_unique_filename( $filename, $post_id = null ) {
		if ( ! $this->get_setting( 'copy-to-s3' ) || ! $this->is_plugin_setup( true ) ) {
			return $filename;
		}

		// sanitize the file name before we begin processing
		$filename = sanitize_file_name( $filename );

		// Get base filename without extension.
		$ext  = pathinfo( $filename, PATHINFO_EXTENSION );
		$ext  = $ext ? ".$ext" : '';
		$name = wp_basename( $filename, $ext );

		// Edge case: if file is named '.ext', treat as an empty name.
		if ( $name === $ext ) {
			$name = '';
		}

		// Rebuild filename with lowercase extension as provider will have converted extension on upload.
		$ext      = strtolower( $ext );
		$filename = $name . $ext;
		$time     = current_time( 'mysql' );

		// Get time if uploaded in post screen.
		if ( ! empty( $post_id ) ) {
			$time = $this->get_post_time( $post_id );
		}

		if ( ! $this->does_file_exist( $filename, $time ) ) {
			// File doesn't exist locally or on provider, return it.
			return $filename;
		}

		$filename = $this->generate_unique_filename( $name, $ext, $time );

		return $filename;
	}

	/**
	 * Get post time
	 *
	 * @param int $post_id
	 *
	 * @return string
	 */
	function get_post_time( $post_id ) {
		$time = current_time( 'mysql' );

		if ( ! $post = get_post( $post_id ) ) {
			return $time;
		}

		if ( substr( $post->post_date, 0, 4 ) > 0 ) {
			$time = $post->post_date;
		}

		return $time;
	}

	/**
	 * Does file exist
	 *
	 * @param string $filename
	 * @param string $time
	 *
	 * @return bool
	 */
	function does_file_exist( $filename, $time ) {
		if ( $this->does_file_exist_local( $filename, $time ) ) {
			return true;
		}

		if ( ! $this->get_setting( 'object-versioning' ) && $this->does_file_exist_provider( $filename, $time ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Does file exist local
	 *
	 * @param string $filename
	 * @param string $time
	 *
	 * @return bool
	 */
	function does_file_exist_local( $filename, $time ) {
		global $wpdb;

		$path = wp_upload_dir( $time );
		$path = ltrim( $path['subdir'], '/' );

		if ( '' !== $path ) {
			$path = trailingslashit( $path );
		}
		$file = $path . $filename;

		$sql = $wpdb->prepare( "
			SELECT COUNT(*)
			FROM $wpdb->postmeta
			WHERE meta_key = %s
			AND meta_value = %s
		", '_wp_attached_file', $file );

		return (bool) $wpdb->get_var( $sql );
	}

	/**
	 * Does file exist provider
	 *
	 * @param string $filename
	 * @param string $time
	 *
	 * @return bool
	 * @throws Exception
	 */
	function does_file_exist_provider( $filename, $time ) {
		$bucket = $this->get_setting( 'bucket' );
		$region = $this->get_setting( 'region' );

		if ( is_wp_error( $region ) ) {
			return false;
		}

		$provider_client = $this->get_provider_client( $region );
		$prefix          = AS3CF_Utils::trailingslash_prefix( $this->get_object_prefix() );
		$prefix          .= AS3CF_Utils::trailingslash_prefix( $this->get_dynamic_prefix( $time ) );

		return $provider_client->does_object_exist( $bucket, $prefix . $filename );
	}

	/**
	 * Generate unique filename
	 *
	 * @param string $name
	 * @param string $ext
	 * @param string $time
	 *
	 * @return string
	 */
	function generate_unique_filename( $name, $ext, $time ) {
		$count    = 1;
		$filename = $name . $count . $ext;

		while ( $this->does_file_exist( $filename, $time ) ) {
			$count++;
			$filename = $name . $count . $ext;
		}

		return $filename;
	}

	/**
	 * Get attachment provider info
	 *
	 * @param int $post_id
	 *
	 * @return mixed
	 */
	public function get_attachment_provider_info( $post_id ) {
		$provider_object = get_post_meta( $post_id, 'amazonS3_info', true );

		if ( is_array( $provider_object ) ) {
			$provider_object = array_merge( array(
				'provider' => static::$default_provider,
				'region'   => null,
			), $provider_object );
		}

		$provider_object = apply_filters( 'as3cf_get_attachment_s3_info', $provider_object, $post_id ); // Backwards compatibility

		return apply_filters( 'as3cf_get_attachment_provider_info', $provider_object, $post_id );
	}

	/**
	 * Check the plugin is correctly setup
	 *
	 * @param bool $with_credentials Do provider credentials need to be set up too? Defaults to false.
	 *
	 * @return bool
	 */
	function is_plugin_setup( $with_credentials = false ) {
		if ( $with_credentials && $this->get_provider()->needs_access_keys() ) {
			// AWS not configured
			return false;
		}

		if ( false === (bool) $this->get_setting( 'bucket' ) ) {
			// No bucket selected
			return false;
		}

		if ( is_wp_error( $this->get_setting( 'region' ) ) ) {
			// Region error when retrieving bucket location
			return false;
		}

		// All good, let's do this
		return true;
	}

	/**
	 * Generate a link to download a file from Amazon provider using query string
	 * authentication. This link is only valid for a limited amount of time.
	 *
	 * @param int         $post_id Post ID of the attachment
	 * @param int|null    $expires Seconds for the link to live
	 * @param string|null $size    Size of the image to get
	 * @param array       $headers Header overrides for request
	 * @param bool        $skip_rewrite_check
	 *
	 * @return mixed|WP_Error
	 */
	public function get_secure_attachment_url( $post_id, $expires = null, $size = null, $headers = array(), $skip_rewrite_check = false ) {
		if ( is_null( $expires ) ) {
			$expires = self::DEFAULT_EXPIRES;
		}

		return $this->get_attachment_url( $post_id, $expires, $size, null, $headers, $skip_rewrite_check );
	}

	/**
	 * Return the scheme to be used in URLs
	 *
	 * @param bool|null $use_ssl
	 *
	 * @return string
	 */
	function get_url_scheme( $use_ssl = null ) {
		if ( $this->use_ssl( $use_ssl ) ) {
			$scheme = 'https';
		} else {
			$scheme = 'http';
		}

		return $scheme;
	}

	/**
	 * Determine when to use https in URLS
	 *
	 * @param bool|null $use_ssl
	 *
	 * @return bool
	 */
	public function use_ssl( $use_ssl = null ) {
		if ( is_ssl() ) {
			$use_ssl = true;
		}

		if ( ! is_bool( $use_ssl ) ) {
			$use_ssl = $this->get_setting( 'force-https' );
		}

		if ( empty( $use_ssl ) ) {
			$use_ssl = false;
		}

		return apply_filters( 'as3cf_use_ssl', $use_ssl );
	}

	/**
	 * Get the custom object prefix if enabled
	 *
	 * @param string $toggle_setting
	 *
	 * @return string
	 */
	function get_object_prefix( $toggle_setting = 'enable-object-prefix' ) {
		if ( $this->get_setting( $toggle_setting ) ) {
			return trailingslashit( trim( $this->get_setting( 'object-prefix' ) ) );
		}

		return '';
	}

	/**
	 * Get the file prefix
	 *
	 * @param null|string $time
	 *
	 * @return string
	 */
	public function get_file_prefix( $time = null ) {
		$prefix = AS3CF_Utils::trailingslash_prefix( $this->get_object_prefix() );
		$prefix .= AS3CF_Utils::trailingslash_prefix( $this->get_dynamic_prefix( $time ) );

		if ( $this->get_setting( 'object-versioning' ) ) {
			$prefix .= AS3CF_Utils::trailingslash_prefix( $this->get_object_version_string() );
		}

		return $prefix;
	}

	/**
	 * Get the url of the file from Amazon provider
	 *
	 * @param int         $post_id            Post ID of the attachment
	 * @param int|null    $expires            Seconds for the link to live
	 * @param string|null $size               Size of the image to get
	 * @param array|null  $meta               Pre retrieved _wp_attachment_metadata for the attachment
	 * @param array       $headers            Header overrides for request
	 * @param bool        $skip_rewrite_check Always return the URL regardless of the 'Rewrite File URLs' setting.
	 *                                        Useful for the EDD and Woo addons to not break download URLs when the
	 *                                        option is disabled.
	 *
	 * @return bool|mixed|WP_Error
	 */
	public function get_attachment_url( $post_id, $expires = null, $size = null, $meta = null, $headers = array(), $skip_rewrite_check = false ) {
		if ( ! ( $provider_object = $this->is_attachment_served_by_provider( $post_id, $skip_rewrite_check ) ) ) {
			return false;
		}

		$url = $this->get_attachment_provider_url( $post_id, $provider_object, $expires, $size, $meta, $headers );

		return apply_filters( 'as3cf_wp_get_attachment_url', $url, $post_id );
	}

	/**
	 * Get attachment local URL.
	 *
	 * This is a direct copy of wp_get_attachment_url() from /wp-includes/post.php
	 * as we filter the URL in AS3CF and can't remove this filter using the current implementation
	 * of globals for class instances.
	 *
	 * @param int $post_id
	 *
	 * @return string|false Attachment URL, otherwise false.
	 */
	public function get_attachment_local_url( $post_id ) {
		$url = '';

		// Get attached file.
		if ( $file = get_post_meta( $post_id, '_wp_attached_file', true ) ) {
			// Get upload directory.
			if ( ( $uploads = wp_upload_dir() ) && false === $uploads['error'] ) {
				// Check that the upload base exists in the file location.
				if ( 0 === strpos( $file, $uploads['basedir'] ) ) {
					// Replace file location with url location.
					$url = str_replace( $uploads['basedir'], $uploads['baseurl'], $file );
				} elseif ( false !== strpos( $file, 'wp-content/uploads' ) ) {
					$url = $uploads['baseurl'] . substr( $file, strpos( $file, 'wp-content/uploads' ) + 18 );
				} else {
					// It's a newly-uploaded file, therefore $file is relative to the basedir.
					$url = $uploads['baseurl'] . "/$file";
				}
			}
		}

		if ( empty( $url ) ) {
			return false;
		}

		$url = $this->maybe_fix_local_subsite_url( $url );

		return $url;
	}

	/**
	 * Get attachment local URL size.
	 *
	 * @param int         $post_id
	 * @param string|null $size
	 *
	 * @return false|string
	 */
	public function get_attachment_local_url_size( $post_id, $size = null ) {
		$url = $this->get_attachment_local_url( $post_id );

		if ( empty( $size ) ) {
			return $url;
		}

		$meta = get_post_meta( $post_id, '_wp_attachment_metadata', true );

		if ( empty( $meta['sizes'][ $size ]['file'] ) ) {
			// No alternative sizes available, return
			return $url;
		}

		return str_replace( wp_basename( $url ), $meta['sizes'][ $size ]['file'], $url );
	}

	/**
	 * Get the provider URL for an attachment
	 *
	 * @param int               $post_id
	 * @param array             $provider_object
	 * @param null|int          $expires
	 * @param null|string|array $size
	 * @param null|array        $meta
	 * @param array             $headers
	 *
	 * @return mixed|WP_Error
	 */
	public function get_attachment_provider_url( $post_id, $provider_object, $expires = null, $size = null, $meta = null, $headers = array() ) {
		// We don't use $this->get_provider_object_region() here because we don't want
		// to make an AWS API call and slow down page loading
		if ( isset( $provider_object['region'] ) && ( $this->get_provider()->region_required() || $this->get_provider()->get_default_region() !== $provider_object['region'] ) ) {
			$region = $this->get_provider()->sanitize_region( $provider_object['region'] );
		} else {
			$region = '';
		}

		$size = $this->maybe_convert_size_to_string( $post_id, $size );

		// Force use of secured URL when ACL has been set to private
		if ( is_null( $expires ) ) {
			if ( is_null( $size ) && isset( $provider_object['acl'] ) && $this->get_provider()->get_private_acl() === $provider_object['acl'] ) {
				// Full size URL private
				$expires = self::DEFAULT_EXPIRES;
			}

			if ( ! is_null( $size ) && isset( $provider_object['sizes'][ $size ]['acl'] ) && $this->get_provider()->get_private_acl() === $provider_object['sizes'][ $size ]['acl'] ) {
				// Alternative size URL private
				$expires = self::DEFAULT_EXPIRES;
			}
		}

		if ( ! is_null( $size ) ) {
			if ( is_null( $meta ) ) {
				$meta = get_post_meta( $post_id, '_wp_attachment_metadata', true );
			}

			if ( is_wp_error( $meta ) ) {
				return $meta;
			}

			if ( ! empty( $meta ) && isset( $meta['sizes'][ $size ]['file'] ) ) {
				$size_prefix      = dirname( $provider_object['key'] );
				$size_file_prefix = ( '.' === $size_prefix ) ? '' : $size_prefix . '/';

				$provider_object['key'] = $size_file_prefix . $meta['sizes'][ $size ]['file'];
			}
		}

		$scheme   = $this->get_url_scheme();
		$domain   = $this->get_provider()->get_url_domain( $provider_object['bucket'], $region, $expires );
		$base_url = $scheme . '://' . $domain;

		if ( ! is_null( $expires ) && $this->is_plugin_setup( true ) ) {
			try {
				if ( $this->get_provider()->get_domain() !== $domain ) {
					$headers['BaseURL'] = $base_url;
				}

				$expires    = time() + apply_filters( 'as3cf_expires', $expires );
				$secure_url = $this->get_provider_client( $region )
				                   ->get_object_url( $provider_object['bucket'], $provider_object['key'], $expires, $headers );

				return apply_filters( 'as3cf_get_attachment_secure_url', $secure_url, $provider_object, $post_id, $expires, $headers );
			} catch ( Exception $e ) {
				return new WP_Error( 'exception', $e->getMessage() );
			}
		}

		$provider_object['key'] = $this->maybe_update_cloudfront_path( $provider_object['key'] );

		$file = $this->encode_filename_in_path( $provider_object['key'] );
		$url  = $base_url . '/' . $file;

		return apply_filters( 'as3cf_get_attachment_url', $url, $provider_object, $post_id, $expires, $headers );
	}

	/**
	 * Get attachment url
	 *
	 * @param string $url
	 * @param int    $post_id
	 *
	 * @return bool|mixed|WP_Error
	 */
	public function wp_get_attachment_url( $url, $post_id ) {
		$new_url = $this->get_attachment_url( $post_id );

		if ( false === $new_url ) {
			return $url;
		}

		$new_url = apply_filters( 'wps3_get_attachment_url', $new_url, $post_id, $this ); // Old naming convention, will be deprecated soon
		$new_url = apply_filters( 'as3cf_wp_get_attachment_url', $new_url, $post_id );

		return $new_url;
	}

	/**
	 * Maybe encode attachment URLs when retrieving the image tag
	 *
	 * @param string $html
	 * @param int    $id
	 * @param string $alt
	 * @param string $title
	 * @param string $align
	 * @param string $size
	 *
	 * @return string
	 */
	public function maybe_encode_get_image_tag( $html, $id, $alt, $title, $align, $size ) {
		if ( ! ( $provider_object = $this->is_attachment_served_by_provider( $id ) ) ) {
			// Not served by provider, return
			return $html;
		}

		if ( ! is_string( $html ) ) {
			return $html;
		}

		preg_match( '@\ssrc=[\'\"]([^\'\"]*)[\'\"]@', $html, $matches );

		if ( ! isset( $matches[1] ) ) {
			// Can't establish img src
			return $html;
		}

		$img_src     = $matches[1];
		$new_img_src = $this->maybe_sign_intermediate_size( $img_src, $id, $size, $provider_object );
		$new_img_src = $this->encode_filename_in_path( $new_img_src );

		return str_replace( $img_src, $new_img_src, $html );
	}

	/**
	 * Maybe encode URLs for images that represent an attachment
	 *
	 * @param array|bool   $image
	 * @param int          $attachment_id
	 * @param string|array $size
	 * @param bool         $icon
	 *
	 * @return array
	 */
	public function maybe_encode_wp_get_attachment_image_src( $image, $attachment_id, $size, $icon ) {
		if ( ! ( $provider_object = $this->is_attachment_served_by_provider( $attachment_id ) ) ) {
			// Not served by provider, return
			return $image;
		}

		if ( isset( $image[0] ) ) {
			$url = $this->maybe_sign_intermediate_size( $image[0], $attachment_id, $size, $provider_object );
			$url = $this->encode_filename_in_path( $url );

			$image[0] = $url;
		}

		return $image;
	}

	/**
	 * Maybe encode URLs when outputting attachments in the media grid
	 *
	 * @param array      $response
	 * @param int|object $attachment
	 * @param array      $meta
	 *
	 * @return array
	 */
	public function maybe_encode_wp_prepare_attachment_for_js( $response, $attachment, $meta ) {
		if ( ! ( $provider_object = $this->is_attachment_served_by_provider( $attachment->ID ) ) ) {
			// Not served by provider, return
			return $response;
		}

		if ( isset( $response['url'] ) ) {
			$response['url'] = $this->encode_filename_in_path( $response['url'] );
		}

		if ( isset( $response['sizes'] ) && is_array( $response['sizes'] ) ) {
			foreach ( $response['sizes'] as $size => $value ) {
				$url = $this->maybe_sign_intermediate_size( $value['url'], $attachment->ID, $size, $provider_object );
				$url = $this->encode_filename_in_path( $url );

				$response['sizes'][ $size ]['url'] = $url;
			}
		}

		return $response;
	}

	/**
	 * Maybe encode URLs when retrieving intermediate sizes.
	 *
	 * @param array        $data
	 * @param int          $post_id
	 * @param string|array $size
	 *
	 * @return array
	 */
	public function maybe_encode_image_get_intermediate_size( $data, $post_id, $size ) {
		if ( ! ( $provider_object = $this->is_attachment_served_by_provider( $post_id ) ) ) {
			// Not served by provider, return
			return $data;
		}

		if ( isset( $data['url'] ) ) {
			$url = $this->maybe_sign_intermediate_size( $data['url'], $post_id, $size, $provider_object );
			$url = $this->encode_filename_in_path( $url );

			$data['url'] = $url;
		}

		return $data;
	}

	/**
	 * Sign intermediate size.
	 *
	 * @param string       $url
	 * @param int          $attachment_id
	 * @param string|array $size
	 * @param bool|array   $provider_object
	 *
	 * @return mixed|WP_Error
	 */
	protected function maybe_sign_intermediate_size( $url, $attachment_id, $size, $provider_object = false ) {
		if ( ! $provider_object ) {
			$provider_object = $this->get_attachment_provider_info( $attachment_id );
		}

		$size = $this->maybe_convert_size_to_string( $attachment_id, $size );

		if ( isset( $provider_object['sizes'][ $size ] ) ) {
			// Private file, add AWS signature if required
			return $this->get_attachment_provider_url( $attachment_id, $provider_object, null, $size );
		}

		return $url;
	}

	/**
	 * Convert dimensions to size
	 *
	 * @param int   $attachment_id
	 * @param array $dimensions
	 *
	 * @return null|string
	 */
	protected function convert_dimensions_to_size_name( $attachment_id, $dimensions ) {
		$w                     = ( isset( $dimensions[0] ) && $dimensions[0] > 0 ) ? $dimensions[0] : 1;
		$h                     = ( isset( $dimensions[1] ) && $dimensions[1] > 0 ) ? $dimensions[1] : 1;
		$original_aspect_ratio = $w / $h;
		$meta                  = wp_get_attachment_metadata( $attachment_id );

		if ( ! isset( $meta['sizes'] ) || empty( $meta['sizes'] ) ) {
			return null;
		}

		$sizes = $meta['sizes'];
		uasort( $sizes, function ( $a, $b ) {
			// Order by image area
			return ( $a['width'] * $a['height'] ) - ( $b['width'] * $b['height'] );
		} );

		$nearest_matches = array();

		foreach ( $sizes as $size => $value ) {
			if ( $w > $value['width'] || $h > $value['height'] ) {
				continue;
			}

			$aspect_ratio = $value['width'] / $value['height'];

			if ( $aspect_ratio === $original_aspect_ratio ) {
				return $size;
			}

			$nearest_matches[] = $size;
		}

		// Return nearest match
		if ( ! empty( $nearest_matches ) ) {
			return $nearest_matches[0];
		}

		return null;
	}

	/**
	 * Maybe convert size to string
	 *
	 * @param int   $attachment_id
	 * @param mixed $size
	 *
	 * @return null|string
	 */
	protected function maybe_convert_size_to_string( $attachment_id, $size ) {
		if ( is_array( $size ) ) {
			return $this->convert_dimensions_to_size_name( $attachment_id, $size );
		}

		return $size;
	}

	/**
	 * Is attachment served by provider.
	 *
	 * @param int           $attachment_id
	 * @param bool          $skip_rewrite_check          Still check if offloaded even if not currently rewriting URLs? Default: false
	 * @param bool          $skip_current_provider_check Skip checking if offloaded to current provider. Default: false, negated if $provider supplied
	 * @param Provider|null $provider                    Provider where attachment expected to be offloaded to. Default: currently configured provider
	 *
	 * @return bool|array
	 */
	public function is_attachment_served_by_provider( $attachment_id, $skip_rewrite_check = false, $skip_current_provider_check = false, Provider $provider = null ) {
		if ( ! $skip_rewrite_check && ! $this->get_setting( 'serve-from-s3' ) ) {
			// Not serving provider URLs
			return false;
		}

		if ( ! ( $provider_object = $this->get_attachment_provider_info( $attachment_id ) ) ) {
			// File not uploaded to a provider
			return false;
		}

		if ( ! $skip_current_provider_check && empty( $provider ) ) {
			$provider = $this->get_provider();
		}

		if ( ! empty( $provider ) && $provider::get_provider_key_name() !== $provider_object['provider'] ) {
			// File not uploaded to required provider
			return false;
		}

		return $provider_object;
	}

	/**
	 * Encode file names according to RFC 3986 when generating urls
	 * As per Amazon https://forums.aws.amazon.com/thread.jspa?threadID=55746#jive-message-244233
	 *
	 * @param string $file
	 *
	 * @return string Encoded filename
	 */
	public function encode_filename_in_path( $file ) {
		$url = parse_url( $file );

		if ( ! isset( $url['path'] ) ) {
			// Can't determine path, return original
			return $file;
		}

		if ( isset( $url['query'] ) ) {
			// Manually strip query string, as passing $url['path'] to basename results in corrupt � characters
			$file_name = wp_basename( str_replace( '?' . $url['query'], '', $file ) );
		} else {
			$file_name = wp_basename( $file );
		}

		if ( false !== strpos( $file_name, '%' ) ) {
			// File name already encoded, return original
			return $file;
		}

		$encoded_file_name = rawurlencode( $file_name );

		if ( $file_name === $encoded_file_name ) {
			// File name doesn't need encoding, return original
			return $file;
		}

		return str_replace( $file_name, $encoded_file_name, $file );
	}

	/**
	 * Decode file name.
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	public function decode_filename_in_path( $file ) {
		$url = parse_url( $file );

		if ( ! isset( $url['path'] ) ) {
			// Can't determine path, return original
			return $file;
		}

		$file_name = wp_basename( $url['path'] );

		if ( false === strpos( $file_name, '%' ) ) {
			// File name not encoded, return original
			return $file;
		}

		$decoded_file_name = rawurldecode( $file_name );

		return str_replace( $file_name, $decoded_file_name, $file );
	}

	/**
	 * Allow processes to update the file on provider via update_attached_file()
	 *
	 * @param string $file
	 * @param int    $attachment_id
	 *
	 * @return string
	 */
	function update_attached_file( $file, $attachment_id ) {
		if ( ! $this->is_plugin_setup( true ) ) {
			return $file;
		}

		if ( ! ( $provider_object = $this->get_attachment_provider_info( $attachment_id ) ) ) {
			return $file;
		}

		$file = apply_filters( 'as3cf_update_attached_file', $file, $attachment_id, $provider_object );

		return $file;
	}

	/**
	 * Return the provider URL when the local file is missing
	 * unless we know the calling process is and we are happy
	 * to copy the file back to the server to be used
	 *
	 * @param string $file
	 * @param int    $attachment_id
	 *
	 * @return string
	 */
	function get_attached_file( $file, $attachment_id ) {
		if ( file_exists( $file ) || ! ( $provider_object = $this->is_attachment_served_by_provider( $attachment_id ) ) ) {
			return $file;
		}

		$url = $this->get_attachment_url( $attachment_id );

		// return the URL by default
		$file = apply_filters( 'as3cf_get_attached_file', $url, $file, $attachment_id, $provider_object );

		return $file;
	}

	/**
	 * Helper method for returning data to AJAX call
	 *
	 * @param array $return
	 */
	function end_ajax( $return = array() ) {
		wp_send_json( $return );
	}

	/**
	 * Ensure AJAX request from expected route and user with capability to handle offloaded media.
	 *
	 * @param string $capability Defaults to 'manage_options'.
	 * @param bool   $return
	 *
	 * @return bool
	 */
	function verify_ajax_request( $capability = null, $return = false ) {
		$capability = empty( $capability ) ? 'manage_options' : $capability;

		if ( ! is_admin() ) { // input var okay
			$msg = __( 'This action can only be performed through an admin screen.', 'amazon-s3-and-cloudfront' );
		} elseif ( empty( $_POST['_nonce'] ) || empty( $_POST['action'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_nonce'] ), sanitize_key( $_POST['action'] ) ) ) { // input var okay
			$msg = __( 'Cheatin&#8217; eh?', 'amazon-s3-and-cloudfront' );
		} elseif ( ! current_user_can( $capability ) ) {
			$msg = __( 'You do not have sufficient permissions to access this page.', 'amazon-s3-and-cloudfront' );
		}

		if ( ! empty( $msg ) ) {
			AS3CF_Error::log( $msg );

			if ( $return ) {
				return false;
			} else {
				wp_die( $msg );
			}
		}

		return true;
	}

	/**
	 * Returns cleaned up region name to be used while setting bucket or returns false if missing.
	 *
	 * @param string $region
	 * @param bool   $region_required
	 *
	 * @return string|bool
	 */
	function check_region( $region = '', $region_required = false ) {
		// If defined, just use.
		if ( defined( 'AS3CF_REGION' ) ) {
			return AS3CF_REGION;
		}

		// If defined in settings define, just use.
		if ( false !== $this->get_defined_setting( 'region', false ) ) {
			return $this->get_defined_setting( 'region' );
		}

		if ( ! empty( $region ) ) {
			$region = sanitize_text_field( $region );
		}

		if ( $region_required && empty( $region ) ) {
			return false;
		}

		return $region;
	}

	/**
	 * Returns cleaned up bucket name or returns false if missing.
	 *
	 * @param string $bucket
	 *
	 * @return string|bool
	 */
	function check_bucket( $bucket ) {
		$bucket = sanitize_text_field( $bucket );

		return empty( $bucket ) ? false : strtolower( $bucket );
	}

	/**
	 * Create an S3 bucket
	 *
	 * @param string      $bucket_name
	 * @param bool|string $region option location constraint
	 *
	 * @return bool|WP_Error
	 */
	function create_bucket( $bucket_name, $region = false ) {
		try {
			$args = array( 'Bucket' => $bucket_name );

			if ( defined( 'AS3CF_REGION' ) ) {
				// Make sure we always use the defined region
				$region = AS3CF_REGION;
			}

			if ( ! is_null( $region ) && $this->get_provider()->get_default_region() !== $region ) {
				$args['LocationConstraint'] = $region;
			}

			$this->get_provider_client( $region )->create_bucket( $args );
		} catch ( Exception $e ) {
			return new WP_Error( 'exception', $e->getMessage() );
		}

		return true;
	}

	/**
	 * Prepare the bucket error before returning to JS
	 *
	 * @param WP_Error $object
	 * @param bool     $single Are we dealing with a single bucket?
	 *
	 * @return string
	 */
	function prepare_bucket_error( $object, $single = true ) {
		if ( 'Access Denied' === $object->get_error_message() ) {
			// If the bucket error is access denied, show our notice message
			$out = $this->get_access_denied_notice_message( $single );
		} else {
			$out = $object->get_error_message();
		}

		return $out;
	}

	/**
	 * Perform custom actions before the setting is saved
	 *
	 * @param string $key
	 * @param string $value
	 */
	function pre_set_setting( $key, $value ) {
		if ( 'bucket' === $key && ! $this->get_setting( 'bucket' ) ) {
			// first time bucket select - enable main options by default
			$this->set_setting( 'copy-to-s3', '1' );
			$this->set_setting( 'serve-from-s3', '1' );
		}
	}

	/**
	 * Save bucket and bucket's region
	 *
	 * @param string      $bucket_name
	 * @param bool        $manual if we are entering the bucket via the manual input form
	 * @param null|string $region
	 *
	 * @return string|bool|WP_Error region on success
	 */
	function save_bucket( $bucket_name, $manual = false, $region = null ) {
		if ( $bucket_name ) {
			$this->get_settings();

			$this->set_setting( 'bucket', $bucket_name );

			if ( empty( $region ) ) {
				// retrieve the bucket region if not supplied
				$region = $this->get_bucket_region( $bucket_name );
				if ( is_wp_error( $region ) ) {
					return $region;
				}
			}

			if ( ! $this->get_provider()->region_required() && $this->get_provider()->get_default_region() === $region ) {
				$region = '';
			}

			$this->set_setting( 'region', $region );

			if ( $manual ) {
				// record that we have entered the bucket via the manual form
				$this->set_setting( 'manual_bucket', true );
			} else {
				$this->remove_setting( 'manual_bucket' );
			}

			$this->save_settings();

			return $region;
		}

		return false;
	}

	/**
	 * Add the settings page to the top-level Settings menu item.
	 */
	public function admin_menu() {
		$this->hook_suffix = add_submenu_page(
			$this->get_plugin_pagenow(),
			$this->get_plugin_page_title(),
			$this->plugin_menu_title,
			'manage_options',
			$this->plugin_slug,
			array( $this, 'render_page' )
		);

		do_action( 'as3cf_hook_suffix', $this->hook_suffix );

		add_action( 'load-' . $this->hook_suffix, array( $this, 'plugin_load' ) );
	}

	/**
	 * Add the settings page to the top-level AWS menu item for backwards compatibility.
	 *
	 * @param \Amazon_Web_Services $aws Plugin class instance from the amazon-web-services plugin.
	 */
	public function aws_admin_menu( $aws ) {
		$aws->add_page(
			$this->get_plugin_page_title(),
			$this->plugin_menu_title,
			'manage_options',
			$this->plugin_slug,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Returns the Provider's default region slug.
	 *
	 * @return string
	 */
	public function get_default_region() {
		return $this->get_provider()->get_default_region();
	}

	/**
	 * Get the S3 client
	 *
	 * @param bool|string $region specify region to client for signature
	 * @param bool        $force  force return of new provider client when swapping regions
	 *
	 * @return Provider|Null_Provider
	 * @throws Exception
	 */
	public function get_provider_client( $region = false, $force = false ) {
		if ( is_null( $this->provider_client ) ||
		     is_null( $this->provider_client_region ) ||
		     $force ||
		     ( false !== $region && $this->provider_client_region !== $region ) ) {
			$args = array();

			if ( $force ) {
				$this->set_provider();
			}

			if ( $region ) {
				$args['region'] = $this->get_provider()->sanitize_region( $region );
			}

			$provider_client_region = isset( $args['region'] ) ? $args['region'] : $region;

			try {
				$this->set_client( $this->get_provider()->get_client( $args ), $provider_client_region );
			} catch ( Exception $e ) {
				AS3CF_Error::log( $e->getMessage() );
				$this->set_client( new Null_Provider );
			}
		}

		return $this->provider_client;
	}

	/**
	 * Setter for Provider client
	 *
	 * @param Provider|Null_Provider $client
	 * @param bool|string            $region
	 */
	public function set_client( $client, $region = false ) {
		$this->provider_client = $client;

		if ( false !== $region ) {
			$this->provider_client_region = $region;
		}
	}

	/**
	 * Get the region of a bucket
	 *
	 * @param string  $bucket
	 * @param boolean $use_cache
	 *
	 * @return string|WP_Error
	 */
	public function get_bucket_region( $bucket, $use_cache = false ) {
		$regions = get_site_transient( 'as3cf_regions_cache' );

		if ( ! is_array( $regions ) ) {
			$regions = array();
		}

		if ( $use_cache && isset( $regions[ $bucket ] ) ) {
			return $regions[ $bucket ];
		}

		try {
			$region = $this->get_provider_client( false, true )->get_bucket_location( array( 'Bucket' => $bucket ) );
		} catch ( Exception $e ) {
			$error_msg_title = '<strong>' . __( 'Error Getting Bucket Region', 'amazon-s3-and-cloudfront' ) . '</strong> &mdash;';
			$error_msg       = sprintf( __( 'There was an error attempting to get the region of the bucket %s: %s', 'amazon-s3-and-cloudfront' ), $bucket, $e->getMessage() );
			AS3CF_Error::log( $error_msg );

			return new WP_Error( 'exception', $error_msg_title . $error_msg );
		}

		$region = $this->get_provider()->sanitize_region( $region );

		if ( is_string( $region ) ) {
			$regions[ $bucket ] = $region;
			set_site_transient( 'as3cf_regions_cache', $regions, 5 * MINUTE_IN_SECONDS );
		}

		return $region;
	}

	/**
	 * Get the region of the bucket stored in the provider metadata.
	 *
	 *
	 * @param array $provider_object
	 * @param int   $post_id - if supplied will update the provider meta if no region found
	 *
	 * @return string|WP_Error - region name
	 */
	function get_provider_object_region( $provider_object, $post_id = null ) {
		if ( ! isset( $provider_object['region'] ) ) {
			// if region hasn't been stored in the provider metadata retrieve using the bucket
			$region = $this->get_bucket_region( $provider_object['bucket'], true );
			if ( is_wp_error( $region ) ) {
				return $region;
			}

			$provider_object['region'] = $region;

			if ( ! is_null( $post_id ) ) {
				// retrospectively update provider metadata with region
				update_post_meta( $post_id, 'amazonS3_info', $provider_object );
			}
		}

		return $provider_object['region'];
	}

	/**
	 * AJAX handler for get_buckets()
	 */
	function ajax_get_buckets() {
		$this->verify_ajax_request();

		$region = empty( $_POST['region'] ) ? '' : $_POST['region'];
		$region = $this->check_region( $region, $this->get_provider()->region_required() );

		$result = $this->get_buckets( $region );

		if ( is_wp_error( $result ) ) {
			$out = array(
				'error' => $this->prepare_bucket_error( $result, false ),
			);
		} else {
			$out = array(
				'success' => '1',
				'buckets' => $result,
			);
		}

		$this->end_ajax( $out );
	}

	/**
	 * Get a list of buckets from S3
	 *
	 * @param bool $region
	 *
	 * @return array|WP_Error - list of buckets
	 */
	function get_buckets( $region = false ) {
		try {
			$result = $this->get_provider_client( $region )->list_buckets();
		} catch ( Exception $e ) {
			return new WP_Error( 'exception', $e->getMessage() );
		}

		if ( empty( $result['Buckets'] ) ) {
			return array();
		} else {
			return $result['Buckets'];
		}
	}

	/**
	 * Checks the user has write permission for S3
	 *
	 * @param string $bucket
	 * @param string $region
	 *
	 * @return bool|WP_Error
	 * @throws Exception
	 */
	function check_write_permission( $bucket = null, $region = null ) {
		if ( $this->get_provider()->needs_access_keys() ) {
			// If no access keys set then no need check.
			return false;
		}

		if ( is_null( $bucket ) ) {
			// If changing provider or bucket don't bother to test saved bucket permissions.
			if ( ! empty( $_GET['action'] ) && in_array( $_GET['action'], array( 'change-provider', 'change-bucket' ) ) ) {
				return false;
			}

			if ( ! ( $bucket = $this->get_setting( 'bucket' ) ) ) {
				// if no bucket set then no need check
				return true;
			}
		}

		// need to set region for buckets in non default region
		if ( is_null( $region ) ) {
			$region = $this->get_setting( 'region' );

			if ( is_wp_error( $region ) ) {
				return $region;
			}
		}

		if ( isset( self::$buckets_check[ $bucket ] ) ) {
			return self::$buckets_check[ $bucket ];
		}

		$key           = $this->get_file_prefix() . 'as3cf-permission-check.txt';
		$file_contents = __( 'This is a test file to check if the user has write permission to the bucket. Delete me if found.', 'amazon-s3-and-cloudfront' );

		$can_write = $this->get_provider_client( $region, true )->can_write( $bucket, $key, $file_contents );

		// If we get back an unexpected error message, throw an error.
		if ( is_string( $can_write ) ) {
			$error_msg = sprintf( __( 'There was an error attempting to check the permissions of the bucket %s: %s', 'amazon-s3-and-cloudfront' ), $bucket, $can_write );
			AS3CF_Error::log( $error_msg );

			return new WP_Error( 'exception', $error_msg );
		}

		self::$buckets_check[ $bucket ] = $can_write;

		return $can_write;
	}

	/**
	 * Render error messages in a view for bucket permission and access issues
	 *
	 * @return bool
	 * @throws Exception
	 */
	function render_bucket_permission_errors() {
		$can_write = $this->check_write_permission();
		// catch any checking issues
		if ( is_wp_error( $can_write ) ) {
			$this->render_view( 'error-fatal', array( 'message' => $can_write->get_error_message() ) );
			$can_write = false;
		} else {
			// display a error message if the user does not have write permission to S3 bucket
			$this->render_view( 'error-access', array( 'can_write' => $can_write ) );
		}

		return $can_write;
	}

	/**
	 * Register modal scripts and styles so they can be enqueued later
	 */
	function register_modal_assets() {
		$version = $this->get_asset_version();
		$suffix  = $this->get_asset_suffix();

		$src = plugins_url( 'assets/css/modal.css', $this->plugin_file_path );
		wp_register_style( 'as3cf-modal', $src, array(), $version );

		$src = plugins_url( 'assets/js/modal' . $suffix . '.js', $this->plugin_file_path );
		wp_register_script( 'as3cf-modal', $src, array( 'jquery' ), $version, true );
	}

	/**
	 * Register storage provider scripts and styles so they can be enqueued later
	 */
	function register_storage_provider_assets() {
		$version = $this->get_asset_version();
		$suffix  = $this->get_asset_suffix();

		$src = plugins_url( 'assets/css/storage-provider.css', $this->plugin_file_path );
		wp_register_style( 'as3cf-storage-provider', $src, array(), $version );

		$src = plugins_url( 'assets/js/storage-provider' . $suffix . '.js', $this->plugin_file_path );
		wp_register_script( 'as3cf-storage-provider', $src, array( 'jquery' ), $version, true );
	}

	/**
	 * On plugin load.
	 */
	public function plugin_load() {
		/*
		 * If the request is using the old parent page for the settings page, (i.e. in AWS menu)
		 * redirect to the new one. Unfortunately, there is no way to preserve the hash, if present.
		 * This works because the hook suffix is the same for both, regardless of parent page.
		 */
		if ( $this->get_plugin_pagenow() !== $GLOBALS['pagenow'] ) {
			wp_redirect( $this->get_plugin_page_url() );
			exit;
		}

		add_action( 'network_admin_notices', array( $this, 'settings_saved_notice' ) );

		$this->enqueue_style( 'as3cf-styles', 'assets/css/styles', array( 'as3cf-modal', 'as3cf-storage-provider' ) );
		$this->enqueue_script( 'as3cf-script', 'assets/js/script', array( 'jquery', 'underscore', 'as3cf-modal', 'as3cf-storage-provider' ) );

		wp_localize_script( 'as3cf-script',
			'as3cf',
			array(
				'strings'                           => array(
					'create_bucket_error'         => __( 'Error creating bucket', 'amazon-s3-and-cloudfront' ),
					'create_bucket_name_short'    => __( 'Bucket name too short.', 'amazon-s3-and-cloudfront' ),
					'create_bucket_name_long'     => __( 'Bucket name too long.', 'amazon-s3-and-cloudfront' ),
					'create_bucket_invalid_chars' => __( 'Invalid character. Bucket names can contain lowercase letters, numbers, periods and hyphens.', 'amazon-s3-and-cloudfront' ),
					'save_bucket_error'           => __( 'Error saving bucket', 'amazon-s3-and-cloudfront' ),
					'get_buckets_error'           => __( 'Error fetching buckets', 'amazon-s3-and-cloudfront' ),
					'get_url_preview_error'       => __( 'Error getting URL preview: ', 'amazon-s3-and-cloudfront' ),
					'save_alert'                  => __( 'The changes you made will be lost if you navigate away from this page', 'amazon-s3-and-cloudfront' ),
					'get_diagnostic_info'         => __( 'Getting diagnostic info...', 'amazon-s3-and-cloudfront' ),
					'get_diagnostic_info_error'   => __( 'Error getting diagnostic info: ', 'amazon-s3-and-cloudfront' ),
					'not_shown_placeholder'       => _x( '-- not shown --', 'placeholder for hidden access key, 39 char max', 'amazon-s3-and-cloudfront' ),
					// Mimic WP Core's notice text, therefore no translation needed here.
					'settings_saved'              => __( 'Settings saved.' ),
				),
				'nonces'                            => array(
					'create_bucket'       => wp_create_nonce( 'as3cf-create-bucket' ),
					'manual_bucket'       => wp_create_nonce( 'as3cf-manual-save-bucket' ),
					'get_buckets'         => wp_create_nonce( 'as3cf-get-buckets' ),
					'save_bucket'         => wp_create_nonce( 'as3cf-save-bucket' ),
					'get_url_preview'     => wp_create_nonce( 'as3cf-get-url-preview' ),
					'get_diagnostic_info' => wp_create_nonce( 'as3cf-get-diagnostic-info' ),
					'aws_keys_set'        => wp_create_nonce( 'as3cf-aws-keys-set' ),
					'aws_keys_remove'     => wp_create_nonce( 'as3cf-aws-keys-remove' ),
				),
				'is_pro'                            => $this->is_pro(),
				'provider_console_url'              => $this->get_provider()->get_console_url(),
				'provider_console_url_prefix_param' => $this->get_provider()->get_console_url_prefix_param(),
			)
		);

		$this->handle_post_request();
		$this->http_prepare_download_log();
		$this->check_for_gd_imagick();

		do_action( 'as3cf_plugin_load' );
	}

	/**
	 * Whitelist of settings allowed to be saved
	 *
	 * @return array
	 */
	function get_settings_whitelist() {
		return array(
			'provider',
			'access-key-id',
			'secret-access-key',
			'key-file-path',
			'key-file',
			'bucket',
			'region',
			'domain',
			'virtual-host',
			'permissions',
			'cloudfront',
			'object-prefix',
			'copy-to-s3',
			'serve-from-s3',
			'remove-local-file',
			'force-https',
			'object-versioning',
			'use-yearmonth-folders',
			'enable-object-prefix',
		);
	}

	/**
	 * Get the blacklisted settings for monitoring changes in defines.
	 * These settings will not be saved in the database.
	 *
	 * @return array
	 */
	function get_monitored_settings_blacklist() {
		return array(
			'access-key-id',
			'secret-access-key',
		);
	}

	/**
	 * List of settings that should skip full sanitize.
	 *
	 * @return array
	 */
	function get_skip_sanitize_settings() {
		return array( 'key-file' );
	}

	/**
	 * Handle the saving of the settings page
	 */
	public function handle_post_request() {
		if ( empty( $_POST['plugin'] ) || $this->get_plugin_slug() != sanitize_key( $_POST['plugin'] ) ) { // input var okay
			return;
		}

		if ( empty( $_POST['action'] ) || 'save' != sanitize_key( $_POST['action'] ) ) { // input var okay
			return;
		}

		if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), $this->get_settings_nonce_key() ) ) { // input var okay
			die( __( "Cheatin' eh?", 'amazon-s3-and-cloudfront' ) );
		}

		// Keep track of original provider at start of settings change flow.
		$orig_provider = empty( $_GET['orig_provider'] ) ? $this->get_setting( 'provider', false ) : $_GET['orig_provider'];

		if ( $this->get_provider()->needs_access_keys() || ( ! empty( $_GET['action'] ) && 'change-provider' === $_GET['action'] ) ) {
			// Changing Provider currently doesn't need anything special over saving settings,
			// but if not already set needs to be handled rather than change-bucket raising its hand.
			$changed_keys = $this->handle_save_settings();
		} elseif ( empty( $this->get_setting( 'bucket' ) ) || ( ! empty( $_GET['action'] ) && 'change-bucket' === $_GET['action'] ) ) {
			$changed_keys = $this->handle_change_bucket();
		} elseif ( ! empty( $_GET['action'] ) ) {
			$changed_keys = apply_filters( 'as3cf_handle_post_request', array() );
		} else {
			$changed_keys = $this->handle_save_settings();
		}

		// If the changes can't be saved, stay on same page.
		// An admin notice should be created with the error message.
		if ( false === $changed_keys ) {
			return;
		}

		// No failures, so let's make things super green.
		$url_args = array( 'updated' => '1' );

		if ( ! empty( $changed_keys ) ) {
			$action = null;

			foreach ( $changed_keys as $key ) {
				// If anything about the Provider has changed then we need to verify the bucket selection.
				// Otherwise we can let the filter decide whether there is an action to take.
				// Last implementer will win, but the above handlers take care of grouping things appropriately.
				if ( in_array( $key, array( 'provider', 'access-key-id', 'secret-access-key', 'key-file' ) ) && ! $this->get_defined_setting( 'bucket', false ) ) {
					$action = 'change-bucket';
					break;
				} else {
					$action = apply_filters( 'as3cf_action_for_changed_settings_key', $action, $key );
				}
			}
		}

		// Stash which step we're on in possibly multi-step config.
		$prev_action = ! empty( $_GET['action'] ) ? $_GET['action'] : null;

		// Depending on the step we're on, we may need another step if not already determined by newly saved settings.
		if ( empty( $action ) && ! empty( $prev_action ) ) {
			// After change-provider we always want the user to confirm the bucket is still ok.
			// This gets round the change-provider => change-bucket => "back" problem.
			// but then no change in provider settings problem.
			if ( 'change-provider' === $prev_action && ! $this->get_defined_setting( 'bucket', false ) ) {
				$action = 'change-bucket';
			}
		}

		if ( ! empty( $action ) ) {
			$url_args['action'] = $action;

			if ( ! empty( $prev_action ) ) {
				$url_args['prev_action'] = $prev_action;
			}

			if ( ! empty( $orig_provider ) ) {
				$url_args['orig_provider'] = $orig_provider;
			}
		}

		$url = $this->get_plugin_page_url( $url_args );
		wp_redirect( $url );
		exit;
	}

	/**
	 * Handle saving change in bucket as submitted by user, whether create, enter or select.
	 *
	 * @return array|bool
	 * @throws Exception
	 */
	private function handle_change_bucket() {
		// Quick check that bucket name actually given.
		$bucket = empty( $_POST['bucket_name'] ) ? false : $_POST['bucket_name'];

		if ( false === $bucket ) {
			$this->notices->add_notice( __( 'No bucket name provided.', 'amazon-s3-and-cloudfront' ), array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' ) );

			return false;
		}

		// Check and set bucket.
		$bucket = $this->check_bucket( $bucket );

		if ( false === $bucket ) {
			$this->notices->add_notice( __( 'Bucket name not valid.', 'amazon-s3-and-cloudfront' ), array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' ) );

			return false;
		}

		$bucket_mode = empty( $_GET['bucket_mode'] ) ? 'manual' : $_GET['bucket_mode'];

		// Check and set region.
		$region          = empty( $_POST['region_name'] ) ? '' : $_POST['region_name'];
		$region_required = 'create' === $bucket_mode ? true : $this->get_provider()->region_required();
		$region          = $this->check_region( $region, $region_required );

		if ( false === $region ) {
			$this->notices->add_notice( __( 'No region provided.', 'amazon-s3-and-cloudfront' ), array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' ) );

			return false;
		}

		// Are we creating a bucket?
		if ( 'create' === $bucket_mode ) {
			$result = $this->create_bucket( $bucket, $region );

			if ( is_wp_error( $result ) ) {
				$this->notices->add_notice( $this->prepare_bucket_error( $result, false ), array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' ) );

				return false;
			}

			// Check if we were previously selecting a bucket manually via the input.
			$manual_select = $this->get_setting( 'manual_bucket', false );

			$args = array(
				'_nonce' => wp_create_nonce( 'as3cf-create-bucket' ),
			);
		} elseif ( 'manual' === $bucket_mode ) {
			$manual_select = true;
		} else {
			$manual_select = false;
		}

		// Stash the current bucket and region before they change.
		$old_bucket = $this->get_setting( 'bucket', false );
		$old_region = $this->get_setting( 'region', '' );

		// Set bucket.
		$region = $this->save_bucket( $bucket, $manual_select, $region );

		if ( is_wp_error( $region ) ) {
			$this->notices->add_notice( $this->prepare_bucket_error( $region, false ), array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' ) );

			return false;
		}

		$can_write = $this->check_write_permission( $bucket, $region );

		if ( is_wp_error( $can_write ) ) {
			$this->notices->add_notice( $this->prepare_bucket_error( $can_write, false ), array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' ) );

			return false;
		}

		// Tell the parent handler whether the bucket or region have changed.
		$changed_keys = array();

		if ( ! $old_bucket || $bucket !== $old_bucket ) {
			$changed_keys[] = 'bucket';
		}

		if ( $region !== $old_region ) {
			$changed_keys[] = 'region';
		}

		return $changed_keys;
	}

	/**
	 * Handle saving settings submitted by user.
	 *
	 * @return array|bool
	 */
	protected function handle_save_settings() {
		$changed_keys = array();

		do_action( 'as3cf_pre_save_settings' );

		$post_vars    = $this->get_settings_whitelist();
		$old_settings = $this->get_settings();

		foreach ( $post_vars as $var ) {
			// Special case for when Secret Access Key is not changed.
			if ( 'secret-access-key' === $var && ! empty( $_POST['secret-access-key'] ) && _x( '-- not shown --', 'placeholder for hidden secret access key, 39 char max', 'amazon-s3-and-cloudfront' ) === $_POST['secret-access-key'] ) {
				continue;
			}

			$this->remove_setting( $var );

			if ( ! isset( $_POST[ $var ] ) ) { // input var okay
				continue;
			}

			$value = $this->sanitize_setting( $var, $_POST[ $var ] );

			if ( 'key-file' === $var && is_string( $value ) && ! empty( $value ) ) {
				$value = stripslashes( $value );

				// Guard against empty JSON.
				if ( '""' === $value ) {
					continue;
				}

				$value = json_decode( $value, true );

				if ( empty( $value ) ) {
					$this->notices->add_notice( __( 'Key File not valid JSON.', 'amazon-s3-and-cloudfront' ), array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' ) );

					return false;
				}
			}

			$this->set_setting( $var, $value );

			// Some setting changes might have knock-on effects that require confirmation of secondary settings.
			if ( isset( $old_settings[ $var ] ) && $old_settings[ $var ] !== $value ) {
				$changed_keys[] = $var;
			}
		}

		$this->save_settings();

		return $changed_keys;
	}

	/**
	 * Display the main settings page for the plugin
	 */
	function render_page() {
		$this->render_view( 'header', array( 'page_title' => $this->get_plugin_page_title(), 'page' => 'as3cf' ) );
		$this->render_view( 'settings-tabs' );

		do_action( 'as3cf_pre_settings_render' );

		$this->render_view( 'settings' );

		do_action( 'as3cf_post_settings_render' );

		$this->render_view( 'footer' );
	}

	/**
	 * Get the tabs available for the plugin settings page
	 *
	 * @return array
	 */
	function get_settings_tabs() {
		$tabs = array(
			'media'   => _x( 'Media Library', 'Show the media library tab', 'amazon-s3-and-cloudfront' ),
			'addons'  => _x( 'Addons', 'Show the addons tab', 'amazon-s3-and-cloudfront' ),
			'support' => _x( 'Support', 'Show the support tab', 'amazon-s3-and-cloudfront' ),
		);

		return apply_filters( 'as3cf_settings_tabs', $tabs );
	}

	/**
	 * Recursively build addons list
	 *
	 * @param array|null $addons
	 */
	function render_addons( $addons = null ) {
		if ( is_null( $addons ) ) {
			$addons = $this->get_addons();
		}

		foreach ( $addons as $slug => $addon ) {
			$this->render_view( 'addon', array(
				'slug'  => $slug,
				'addon' => $addon,
			) );
		}
	}

	/**
	 * Get the prefix path for the files. Ignores WP media library
	 * year month subdirectory setting and just uses S3 setting
	 *
	 * @param string $time
	 *
	 * @return string
	 */
	function get_dynamic_prefix( $time = null ) {
		$prefix = '';
		$subdir = '';

		// If multisite (and if not the main site in a post-MU network)
		if ( is_multisite() && ! ( is_main_network() && is_main_site() && defined( 'MULTISITE' ) ) ) {
			if ( ! get_site_option( 'ms_files_rewriting' ) ) {
				/*
				 * If ms-files rewriting is disabled (networks created post-3.5), it is fairly
				 * straightforward: Append sites/%d if we're not on the main site (for post-MU
				 * networks). (The extra directory prevents a four-digit ID from conflicting with
				 * a year-based directory for the main site. But if a MU-era network has disabled
				 * ms-files rewriting manually, they don't need the extra directory, as they never
				 * had wp-content/uploads for the main site.)
				 */

				if ( defined( 'MULTISITE' ) ) {
					$prefix = '/sites/' . get_current_blog_id();
				} else {
					$prefix = '/' . get_current_blog_id();
				}
			} elseif ( defined( 'UPLOADS' ) && ! ms_is_switched() ) {
				/*
				 * Handle the old-form ms-files.php rewriting if the network still has that enabled.
				 * When ms-files rewriting is enabled, then we only listen to UPLOADS when:
				 * 1) We are not on the main site in a post-MU network, as wp-content/uploads is used
				 *    there, and
				 * 2) We are not switched, as ms_upload_constants() hardcodes these constants to reflect
				 *    the original blog ID.
				 *
				 * Rather than UPLOADS, we actually use BLOGUPLOADDIR if it is set, as it is absolute.
				 * (And it will be set, see ms_upload_constants().) Otherwise, UPLOADS can be used, as
				 * as it is relative to ABSPATH. For the final piece: when UPLOADS is used with ms-files
				 * rewriting in multisite, the resulting URL is /files. (#WP22702 for background.)
				 */
				if ( defined( 'BLOGUPLOADDIR' ) ) {
					$prefix = untrailingslashit( BLOGUPLOADDIR );
				} else {
					$prefix = ABSPATH . UPLOADS;
				}
			}
		}

		if ( $this->get_setting( 'use-yearmonth-folders' ) ) {
			$subdir = $this->get_year_month_directory_name( $time );
			$prefix .= $subdir;
		}

		// support legacy MS installs (<3.5 since upgraded) for subsites
		if ( is_multisite() && ! ( is_main_network() && is_main_site() ) && false === strpos( $prefix, 'sites/' ) ) {
			$details          = get_blog_details( get_current_blog_id() );
			$legacy_ms_prefix = 'sites/' . $details->blog_id . '/';
			$legacy_ms_prefix = apply_filters( 'as3cf_legacy_ms_subsite_prefix', $legacy_ms_prefix, $details );
			$prefix           = '/' . trailingslashit( ltrim( $legacy_ms_prefix, '/' ) ) . ltrim( $subdir, '/' );
		}

		return $prefix;
	}

	/**
	 * Generate the year and month sub-directory from $time if provided,
	 * then POST time if available, otherwise use current time
	 *
	 * @param string $time
	 *
	 * @return string
	 */
	function get_year_month_directory_name( $time = null ) {
		if ( ! $time && isset( $_POST['post_id'] ) ) {
			$time = get_post_field( 'post_date', $_POST['post_id'] );
		}

		if ( ! $time ) {
			$time = current_time( 'mysql' );
		}

		$y      = substr( $time, 0, 4 );
		$m      = substr( $time, 5, 2 );
		$subdir = "/$y/$m";

		if ( false === strpos( $subdir, '//' ) ) {
			return $subdir;
		}

		return '';
	}

	/**
	 * Get all the blog IDs for the multisite network used for table prefixes
	 *
	 * @return false|array
	 */
	public function get_blog_ids() {
		if ( ! is_multisite() ) {
			return false;
		}

		$args = array(
			'limit'    => false, // Deprecated
			'number'   => false, // WordPress 4.6+
			'spam'     => 0,
			'deleted'  => 0,
			'archived' => 0,
		);

		if ( version_compare( $GLOBALS['wp_version'], '4.6', '>=' ) ) {
			$blogs = get_sites( $args );
		} else {
			$blogs = wp_get_sites( $args );
		}

		$blog_ids = array();

		foreach ( $blogs as $blog ) {
			$blog       = (array) $blog;
			$blog_ids[] = $blog['blog_id'];
		}

		return $blog_ids;
	}

	/**
	 * Check whether the pro addon is installed.
	 *
	 * @return bool
	 */
	function is_pro() {
		if ( ! class_exists( 'Amazon_S3_And_CloudFront_Pro' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Apply ACL to an attachment and associated files
	 *
	 * @param int    $post_id
	 * @param array  $provider_object
	 * @param string $acl
	 *
	 * @return array|bool|WP_Error
	 * @throws Exception
	 */
	public function set_attachment_acl_on_provider( $post_id, $provider_object, $acl ) {
		// Return early if already set to the desired ACL
		if ( isset( $provider_object['acl'] ) && $acl === $provider_object['acl'] ) {
			return false;
		}

		$args = array(
			'ACL'    => $acl,
			'Bucket' => $provider_object['bucket'],
			'Key'    => $provider_object['key'],
		);

		$region          = ( isset( $provider_object['region'] ) ) ? $provider_object['region'] : false;
		$provider_client = $this->get_provider_client( $region, true );

		try {
			$provider_client->update_object_acl( $args );
			$provider_object['acl'] = $acl;

			// update S3 meta data
			if ( $acl == $this->get_provider()->get_default_acl() ) {
				unset( $provider_object['acl'] );
			}

			update_post_meta( $post_id, 'amazonS3_info', $provider_object );
		} catch ( Exception $e ) {
			$msg = 'Error setting ACL to ' . $acl . ' for ' . $provider_object['key'] . ': ' . $e->getMessage();
			AS3CF_Error::log( $msg );

			return new WP_Error( 'acl_exception', $msg );
		}

		return $provider_object;
	}

	/**
	 * Make admin notice for when object ACL has changed
	 *
	 * @param array $provider_object
	 */
	function make_acl_admin_notice( $provider_object ) {
		$filename = wp_basename( $provider_object['key'] );
		$acl      = ( isset( $provider_object['acl'] ) ) ? $provider_object['acl'] : $this->get_provider()->get_default_acl();
		$acl_name = $this->get_acl_display_name( $acl );
		$text     = sprintf( __( '<strong>WP Offload Media</strong> &mdash; The file %s has been given %s permissions in the bucket.', 'amazon-s3-and-cloudfront' ), "<strong>{$filename}</strong>", "<strong>{$acl_name}</strong>" );

		$this->notices->add_notice( $text );
	}

	/**
	 * Check if PHP GD and Imagick is installed
	 */
	function check_for_gd_imagick() {
		if ( ! $this->is_plugin_setup( true ) ) {
			// No notice until plugin is setup
			return;
		}

		$gd_enabled      = $this->gd_enabled();
		$imagick_enabled = $this->imagick_enabled();

		if ( ! $gd_enabled && ! $imagick_enabled ) {
			$this->notices->add_notice(
				__( '<strong>WP Offload Media Requirement Missing</strong> &mdash; Looks like you don\'t have an image manipulation library installed on this server and configured with PHP. You may run into trouble if you try to edit images. Please setup GD or ImageMagick.', 'amazon-s3-and-cloudfront' ),
				array( 'flash' => false, 'only_show_to_user' => false, 'only_show_in_settings' => true )
			);
		}
	}

	/**
	 * Output image size names and dimensions to a string
	 *
	 * @return string
	 */
	function get_image_sizes_details() {
		global $_wp_additional_image_sizes;

		$size_details                 = '';
		$get_intermediate_image_sizes = get_intermediate_image_sizes();

		// Create array with sizes
		foreach ( $get_intermediate_image_sizes as $size ) {
			if ( in_array( $size, array( 'thumb', 'thumbnail', 'medium', 'large', 'post-thumbnail' ) ) ) {
				// Run checks for dimension and name values
				if ( ( $width = get_option( $size . '_size_w' ) ) && ( $height = get_option( $size . '_size_h' ) ) ) {
					$size_details .= $size . ' (' . $width . 'x' . $height . ')' . "\r\n";
				} else {
					$size_details .= $size . ' (none)' . "\r\n";
				}
			} elseif ( isset( $_wp_additional_image_sizes[ $size ] ) ) {
				$size_details .= $size . ' (' . $_wp_additional_image_sizes[ $size ]['width'] . 'x' . $_wp_additional_image_sizes[ $size ]['height'] . ')' . "\r\n";
			}
		}

		return $size_details;
	}

	/**
	 * Diagnostic information for the support tab
	 *
	 * @param bool $escape
	 *
	 * @return string
	 */
	function output_diagnostic_info( $escape = true ) {
		global $table_prefix;
		global $wpdb;

		/*
		 * WordPress & Server Environment
		 */

		$output = 'site_url(): ';
		$output .= esc_html( site_url() );
		$output .= "\r\n";

		$output .= 'home_url(): ';
		$output .= esc_html( home_url() );
		$output .= "\r\n";

		$output .= 'Database Name: ';
		$output .= esc_html( $wpdb->dbname );
		$output .= "\r\n";

		$output .= 'Table Prefix: ';
		$output .= esc_html( $table_prefix );
		$output .= "\r\n";

		$output .= 'WordPress: ';
		$output .= get_bloginfo( 'version', 'display' );
		if ( is_multisite() ) {
			$output .= ' Multisite ';
			$output .= '(' . ( is_subdomain_install() ? 'subdomain' : 'subdirectory' ) . ')';
			$output .= "\r\n";
			$output .= 'Multisite Site Count: ';
			$output .= esc_html( get_blog_count() );
			$output .= "\r\n";
			$output .= 'Domain Mapping: ' . ( defined( 'SUNRISE' ) && SUNRISE ? 'Enabled' : 'Disabled' );
		}
		$output .= "\r\n";

		$output .= 'Web Server: ';
		$output .= esc_html( ! empty( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : '' );
		$output .= "\r\n";

		$output .= 'PHP: ';
		if ( function_exists( 'phpversion' ) ) {
			$output .= esc_html( phpversion() );
		}
		$output .= "\r\n";

		$output .= 'MySQL: ';
		$output .= esc_html( $wpdb->db_version() );
		$output .= "\r\n";

		$output .= 'ext/mysqli: ';
		$output .= empty( $wpdb->use_mysqli ) ? 'no' : 'yes';
		$output .= "\r\n";

		$output .= 'PHP Memory Limit: ';
		if ( function_exists( 'ini_get' ) ) {
			$output .= esc_html( ini_get( 'memory_limit' ) );
		}
		$output .= "\r\n";

		$output .= 'WP Memory Limit: ';
		$output .= esc_html( WP_MEMORY_LIMIT );
		$output .= "\r\n";

		$output .= 'Memory Usage: ';
		$output .= size_format( memory_get_usage( true ) );
		$output .= "\r\n";

		$output .= 'Blocked External HTTP Requests: ';
		if ( ! defined( 'WP_HTTP_BLOCK_EXTERNAL' ) || ! WP_HTTP_BLOCK_EXTERNAL ) {
			$output .= 'None';
		} else {
			$accessible_hosts = ( defined( 'WP_ACCESSIBLE_HOSTS' ) ) ? WP_ACCESSIBLE_HOSTS : '';

			if ( empty( $accessible_hosts ) ) {
				$output .= 'ALL';
			} else {
				$output .= 'Partially (Accessible Hosts: ' . esc_html( $accessible_hosts ) . ')';
			}
		}
		$output .= "\r\n";

		$output .= 'WP Locale: ';
		$output .= esc_html( get_locale() );
		$output .= "\r\n";

		$output .= 'Organize offloads by month/year: ';
		$output .= esc_html( get_option( 'uploads_use_yearmonth_folders' ) ? 'Enabled' : 'Disabled' );
		$output .= "\r\n";

		$output .= 'WP_DEBUG: ';
		$output .= esc_html( ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes' : 'No' );
		$output .= "\r\n";

		$output .= 'WP_DEBUG_LOG: ';
		$output .= esc_html( ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) ? 'Yes' : 'No' );
		$output .= "\r\n";

		$output .= 'WP_DEBUG_DISPLAY: ';
		$output .= esc_html( ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) ? 'Yes' : 'No' );
		$output .= "\r\n";

		$output .= 'SCRIPT_DEBUG: ';
		$output .= esc_html( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? 'Yes' : 'No' );
		$output .= "\r\n";

		$output .= 'WP Max Upload Size: ';
		$output .= esc_html( size_format( wp_max_upload_size() ) );
		$output .= "\r\n";

		$output .= 'PHP Time Limit: ';
		if ( function_exists( 'ini_get' ) ) {
			$output .= esc_html( ini_get( 'max_execution_time' ) );
		}
		$output .= "\r\n";

		$output .= 'PHP Error Log: ';
		if ( function_exists( 'ini_get' ) ) {
			$output .= esc_html( ini_get( 'error_log' ) );
		}
		$output .= "\r\n";

		$output .= 'WP Cron: ';
		$output .= esc_html( ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) ? 'Disabled' : 'Enabled' );
		$output .= "\r\n";

		$output .= 'fsockopen: ';
		if ( function_exists( 'fsockopen' ) ) {
			$output .= 'Enabled';
		} else {
			$output .= 'Disabled';
		}
		$output .= "\r\n";

		$output          .= 'allow_url_fopen: ';
		$allow_url_fopen = ini_get( 'allow_url_fopen' );
		if ( empty( $allow_url_fopen ) ) {
			$output .= 'Disabled';
		} else {
			$output .= 'Enabled';
		}
		$output .= "\r\n";

		$output .= 'OpenSSL: ';
		if ( $this->open_ssl_enabled() ) {
			$output .= esc_html( OPENSSL_VERSION_TEXT );
		} else {
			$output .= 'Disabled';
		}
		$output .= "\r\n";

		$output .= 'cURL: ';
		if ( function_exists( 'curl_init' ) ) {
			$curl   = curl_version();
			$output .= esc_html( $curl['version'] );
		} else {
			$output .= 'Disabled';
		}
		$output .= "\r\n";

		$output .= 'Zlib Compression: ';
		if ( function_exists( 'gzcompress' ) ) {
			$output .= 'Enabled';
		} else {
			$output .= 'Disabled';
		}
		$output .= "\r\n";

		$output .= 'PHP GD: ';
		if ( $this->gd_enabled() ) {
			$gd_info = gd_info();
			$output  .= isset( $gd_info['GD Version'] ) ? esc_html( $gd_info['GD Version'] ) : 'Enabled';
		} else {
			$output .= 'Disabled';
		}
		$output .= "\r\n";

		$output .= 'Imagick: ';
		if ( $this->imagick_enabled() ) {
			$output .= 'Enabled';
		} else {
			$output .= 'Disabled';
		}
		$output .= "\r\n";

		$output .= 'Basic Auth: ';
		if ( isset( $_SERVER['REMOTE_USER'] ) || isset( $_SERVER['PHP_AUTH_USER'] ) || isset( $_SERVER['REDIRECT_REMOTE_USER'] ) ) {
			$output .= 'Enabled';
		} else {
			$output .= 'Disabled';
		}
		$output .= "\r\n";

		$output .= 'Proxy: ';
		if ( defined( 'WP_PROXY_HOST' ) || defined( 'WP_PROXY_PORT' ) ) {
			$output .= 'Enabled';
		} else {
			$output .= 'Disabled';
		}
		$output .= "\r\n\r\n";

		/*
		 * Media
		 */

		$media_counts = $this->diagnostic_media_counts();

		$output .= 'Media Files: ';
		$output .= number_format_i18n( $media_counts['all'] );
		$output .= "\r\n";

		$output .= 'Offloaded Media Files: ';
		$output .= number_format_i18n( $media_counts['s3'] );
		$output .= "\r\n";

		$output .= 'Number of Image Sizes: ';
		$sizes  = count( get_intermediate_image_sizes() );
		$output .= number_format_i18n( $sizes );
		$output .= "\r\n\r\n";

		$output       .= 'Names and Dimensions of Image Sizes: ';
		$output       .= "\r\n";
		$size_details = $this->get_image_sizes_details();
		$output       .= $size_details;
		$output       .= "\r\n";

		/*
		 * Defines
		 */

		$output .= 'WP_CONTENT_DIR: ';
		$output .= esc_html( ( defined( 'WP_CONTENT_DIR' ) ) ? WP_CONTENT_DIR : 'Not defined' );
		$output .= "\r\n";

		$output .= 'WP_CONTENT_URL: ';
		$output .= esc_html( ( defined( 'WP_CONTENT_URL' ) ) ? WP_CONTENT_URL : 'Not defined' );
		$output .= "\r\n";

		$output .= 'UPLOADS: ';
		$output .= esc_html( ( defined( 'UPLOADS' ) ) ? UPLOADS : 'Not defined' );
		$output .= "\r\n";

		$output .= 'WP_PLUGIN_DIR: ';
		$output .= esc_html( ( defined( 'WP_PLUGIN_DIR' ) ) ? WP_PLUGIN_DIR : 'Not defined' );
		$output .= "\r\n";

		$output .= 'WP_PLUGIN_URL: ';
		$output .= esc_html( ( defined( 'WP_PLUGIN_URL' ) ) ? WP_PLUGIN_URL : 'Not defined' );
		$output .= "\r\n\r\n";

		$output .= 'AS3CF_PROVIDER: ';
		$output .= esc_html( ( defined( 'AS3CF_PROVIDER' ) ) ? AS3CF_PROVIDER : 'Not defined' );
		$output .= "\r\n";

		$output .= 'AS3CF_BUCKET: ';
		$output .= esc_html( ( defined( 'AS3CF_BUCKET' ) ) ? AS3CF_BUCKET : 'Not defined' );
		$output .= "\r\n";

		$output .= 'AS3CF_REGION: ';
		$output .= esc_html( ( defined( 'AS3CF_REGION' ) ) ? AS3CF_REGION : 'Not defined' );
		$output .= "\r\n";

		$output .= 'AS3CF_SETTINGS: ';

		$settings_constant = $this::settings_constant();

		if ( $settings_constant ) {
			$output .= 'Defined';

			if ( 'AS3CF_SETTINGS' !== $settings_constant ) {
				$output .= ' (using ' . $settings_constant . ')';
			}

			$defined_settings = $this::get_defined_settings();
			if ( empty( $defined_settings ) ) {
				$output .= ' - *EMPTY*';
			} else {
				$output .= "\r\n";
				$output .= 'AS3CF_SETTINGS Keys: ' . implode( ', ', array_keys( $defined_settings ) );
			}
		} else {
			$output .= 'Not defined';
		}
		$output .= "\r\n\r\n";

		/*
		 * Settings
		 */

		$output .= "Local URL:\r\n";
		$output .= $this->get_local_url_preview( $escape );
		$output .= "\r\n";
		$output .= "Offload URL:\r\n";
		$output .= $this->get_url_preview( $escape );
		$output .= "\r\n";
		$output .= "\r\n";

		$provider = $this->get_provider();

		if ( empty( $provider ) ) {
			$output .= 'Provider: Not configured';
			$output .= "\r\n";
		} else {
			$output .= 'Provider: ' . $provider::get_provider_name();
			$output .= "\r\n";

			if ( $provider::use_server_roles_allowed() ) {
				$output .= 'Use Server Role: ' . $this->on_off( $provider::use_server_roles() );
			} else {
				$output .= 'Use Server Role: N/A';
			}
			$output .= "\r\n";

			if ( $provider::use_key_file_allowed() ) {
				$output .= 'Key File Path: ';
				$output .= empty( $provider->get_key_file_path() ) ? 'None' : esc_html( $provider->get_key_file_path() );
				$output .= "\r\n";
				$output .= 'Key File Path Define: ';
				$output .= $provider::key_file_path_constant() ? $provider::key_file_path_constant() : 'Not defined';
			} else {
				$output .= 'Key File Path: N/A';
			}
			$output .= "\r\n";

			if ( $provider::use_access_keys_allowed() ) {
				$output .= 'Access Keys Set: ';
				$output .= $provider->are_access_keys_set() ? 'Yes' : 'No';
				$output .= "\r\n";
				$output .= 'Access Key ID Define: ';
				$output .= $provider::access_key_id_constant() ? $provider::access_key_id_constant() : 'Not defined';
				$output .= "\r\n";
				$output .= 'Secret Access Key Define: ';
				$output .= $provider::secret_access_key_constant() ? $provider::secret_access_key_constant() : 'Not defined';
			} else {
				$output .= 'Access Keys Set: N/A';
			}
			$output .= "\r\n";
		}
		$output .= "\r\n";

		$output .= 'Bucket: ';
		$output .= esc_html( $this->get_setting( 'bucket' ) );
		$output .= "\r\n";
		$output .= 'Region: ';
		$region = esc_html( $this->get_setting( 'region' ) );
		if ( ! is_wp_error( $region ) ) {
			$output .= $region;
		}
		$output .= "\r\n";
		$output .= "\r\n";

		$output .= 'Copy Files to Bucket: ';
		$output .= $this->on_off( 'copy-to-s3' );
		$output .= "\r\n";
		$output .= 'Enable Path: ';
		$output .= $this->on_off( 'enable-object-prefix' );
		$output .= "\r\n";
		$output .= 'Custom Path: ';
		$output .= esc_html( $this->get_setting( 'object-prefix' ) );
		$output .= "\r\n";
		$output .= 'Use Year/Month: ';
		$output .= $this->on_off( 'use-yearmonth-folders' );
		$output .= "\r\n";
		$output .= 'Object Versioning: ';
		$output .= $this->on_off( 'object-versioning' );
		$output .= "\r\n";
		$output .= "\r\n";

		$output .= 'Rewrite Media URLs: ';
		$output .= $this->on_off( 'serve-from-s3' );
		$output .= "\r\n";
		$output .= 'Enable Custom Domain (CDN): ';
		$output .= 'cloudfront' === $this->get_setting( 'domain' ) ? 'On' : 'Off';
		$output .= "\r\n";
		$output .= 'Custom Domain (CDN): ';
		$output .= esc_html( $this->get_setting( 'cloudfront' ) );
		$output .= "\r\n";
		$output .= 'Force HTTPS: ';
		$output .= $this->on_off( 'force-https' );
		$output .= "\r\n";
		$output .= "\r\n";

		$output .= 'Remove Files From Server: ';
		$output .= $this->on_off( 'remove-local-file' );
		$output .= "\r\n\r\n";

		$output = apply_filters( 'as3cf_diagnostic_info', $output );
		if ( has_action( 'as3cf_diagnostic_info' ) ) {
			$output .= "\r\n";
		}

		$theme_info = wp_get_theme();

		if ( ! empty( $theme_info ) && is_a( $theme_info, 'WP_Theme' ) ) {
			$output .= "Active Theme Name: " . esc_html( $theme_info->get( 'Name' ) );
			$output .= "\r\n";
			$output .= "Active Theme Version: " . esc_html( $theme_info->get( 'Version' ) );
			$output .= "\r\n";
			$output .= "Active Theme Folder: " . esc_html( $theme_info->get_stylesheet() );
			$output .= "\r\n";

			if ( is_child_theme() ) {
				$parent_info = $theme_info->parent();

				if ( ! empty( $parent_info ) && is_a( $parent_info, 'WP_Theme' ) ) {
					$output .= "Parent Theme Name: " . esc_html( $parent_info->get( 'Name' ) );
					$output .= "\r\n";
					$output .= "Parent Theme Version: " . esc_html( $parent_info->get( 'Version' ) );
					$output .= "\r\n";
					$output .= "Parent Theme Folder: " . esc_html( $parent_info->get_stylesheet() );
					$output .= "\r\n";
				} else {
					$output .= "WARNING: Parent theme metadata not found\r\n";
				}
			}
			if ( ! file_exists( $theme_info->get_stylesheet_directory() ) ) {
				$output .= "WARNING: Active theme folder not found\r\n";
			}
		} else {
			$output .= "WARNING: Theme metadata not found\r\n";
		}

		$output .= "\r\n";

		$output         .= "Active Plugins:\r\n";
		$active_plugins = (array) get_option( 'active_plugins', array() );
		$plugin_details = array();

		if ( is_multisite() ) {
			$network_active_plugins = wp_get_active_network_plugins();
			$active_plugins         = array_map( array( $this, 'remove_wp_plugin_dir' ), $network_active_plugins );
		}

		foreach ( $active_plugins as $plugin ) {
			$plugin_details[] = $this->get_plugin_details( WP_PLUGIN_DIR . '/' . $plugin );
		}

		asort( $plugin_details );
		$output .= implode( '', $plugin_details );

		$mu_plugins = wp_get_mu_plugins();
		if ( $mu_plugins ) {
			$mu_plugin_details = array();
			$output            .= "\r\n";
			$output            .= "Must-use Plugins:\r\n";

			foreach ( $mu_plugins as $mu_plugin ) {
				$mu_plugin_details[] = $this->get_plugin_details( $mu_plugin );
			}

			asort( $mu_plugin_details );
			$output .= implode( '', $mu_plugin_details );
		}

		$dropins = get_dropins();
		if ( $dropins ) {
			$output .= "\r\n\r\n";
			$output .= "Drop-ins:\r\n";

			foreach ( $dropins as $file => $dropin ) {
				$output .= $file . ( isset( $dropin['Name'] ) ? ' - ' . $dropin['Name'] : '' );
				$output .= "\r\n";
			}
		}

		return $output;
	}

	/**
	 * Helper for displaying settings
	 *
	 * @param string $key setting key
	 *
	 * @return string
	 */
	function on_off( $key ) {
		$value = $this->get_setting( $key, 0 );

		return ( 1 == $value ) ? 'On' : 'Off';
	}

	/**
	 * Helper to display plugin details
	 *
	 * @param string $plugin_path
	 * @param string $suffix
	 *
	 * @return string
	 */
	function get_plugin_details( $plugin_path, $suffix = '' ) {
		$plugin_data = get_plugin_data( $plugin_path );
		if ( empty( $plugin_data['Name'] ) ) {
			return basename( $plugin_path );
		}

		return sprintf( "%s%s (v%s) by %s\r\n", $plugin_data['Name'], $suffix, $plugin_data['Version'], strip_tags( $plugin_data['AuthorName'] ) );
	}

	/**
	 * Helper to remove the plugin directory from the plugin path
	 *
	 * @param string $path Absolute plugin file path
	 *
	 * @return string
	 */
	public function remove_wp_plugin_dir( $path ) {
		$plugin_dir = trailingslashit( WP_PLUGIN_DIR );
		$plugin     = str_replace( $plugin_dir, '', $path );

		return $plugin;
	}

	/**
	 * Check for as3cf-download-log and related nonce and if found begin the
	 * download of the diagnostic log
	 *
	 * @return void
	 */
	function http_prepare_download_log() {
		if ( isset( $_GET['as3cf-download-log'] ) && wp_verify_nonce( $_GET['nonce'], 'as3cf-download-log' ) ) {
			$log      = $this->output_diagnostic_info( false );
			$url      = parse_url( home_url() );
			$host     = sanitize_file_name( $url['host'] );
			$filename = sprintf( '%s-diagnostic-log-%s.txt', $host, date( 'YmdHis' ) );
			header( 'Content-Description: File Transfer' );
			header( 'Content-Type: application/octet-stream' );
			header( 'Content-Length: ' . strlen( $log ) );
			header( 'Content-Disposition: attachment; filename=' . $filename );
			echo $log;
			exit;
		}
	}

	/**
	 * Return human friendly ACL name
	 *
	 * @param string $acl
	 *
	 * @return string
	 */
	function get_acl_display_name( $acl ) {
		$acl = ( 'public-read' === $acl ) ? 'public' : $acl;

		return ucwords( str_replace( '-', ' ', $acl ) );
	}

	/**
	 * Detect if OpenSSL is enabled
	 *
	 * @return bool
	 */
	function open_ssl_enabled() {
		if ( defined( 'OPENSSL_VERSION_TEXT' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Detect if PHP GD is enabled
	 *
	 * @return bool
	 */
	function gd_enabled() {
		if ( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Detect is Imagick is enabled
	 *
	 * @return bool
	 */
	function imagick_enabled() {
		if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) && class_exists( 'ImagickPixel' ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Is the current blog ID that specified in wp-config.php
	 *
	 * @param int $blog_id
	 *
	 * @return bool
	 */
	function is_current_blog( $blog_id ) {
		$default = defined( 'BLOG_ID_CURRENT_SITE' ) ? BLOG_ID_CURRENT_SITE : 1;

		if ( $default === $blog_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Helper to switch to a Multisite blog
	 *  - If the site is MS
	 *  - If the blog is not the current blog defined
	 *
	 * @param int|bool $blog_id
	 */
	public function switch_to_blog( $blog_id = false ) {
		if ( ! is_multisite() ) {
			return;
		}

		if ( ! $blog_id ) {
			$blog_id = defined( 'BLOG_ID_CURRENT_SITE' ) ? BLOG_ID_CURRENT_SITE : 1;
		}

		if ( $blog_id !== get_current_blog_id() ) {
			switch_to_blog( $blog_id );
		}
	}

	/**
	 * Helper to restore to the current Multisite blog
	 */
	public function restore_current_blog() {
		if ( is_multisite() ) {
			restore_current_blog();
		}
	}

	/**
	 * Get all the table prefixes for the blogs in the site. MS compatible
	 *
	 * @param array $exclude_blog_ids blog ids to exclude
	 *
	 * @return array associative array with blog ID as key, prefix as value
	 */
	function get_all_blog_table_prefixes( $exclude_blog_ids = array() ) {
		global $wpdb;
		$prefix = $wpdb->prefix;

		$table_prefixes = array();

		if ( ! in_array( 1, $exclude_blog_ids ) ) {
			$table_prefixes[1] = $prefix;
		}

		if ( is_multisite() ) {
			$blog_ids = $this->get_blog_ids();
			foreach ( $blog_ids as $blog_id ) {
				if ( in_array( $blog_id, $exclude_blog_ids ) ) {
					continue;
				}
				$table_prefixes[ $blog_id ] = $wpdb->get_blog_prefix( $blog_id );
			}
		}

		return $table_prefixes;
	}

	/**
	 * Get the access denied bucket error notice message
	 *
	 * @param bool $single
	 *
	 * @return string
	 */
	function get_access_denied_notice_message( $single = true ) {
		if ( $this->get_provider()->needs_access_keys() ) {
			return sprintf( __( '<a href="%s">Define your access keys</a> to enable write access to the bucket', 'amazon-s3-and-cloudfront' ), '#settings' );
		}

		$url = $this->dbrains_url( '/wp-offload-media/doc/quick-start-guide/', array(
			'utm_campaign' => 'error+messages',
		), 'bucket-restrictions' );

		$quick_start = sprintf( '<a class="js-link" href="%s">%s</a>', $url, __( 'Quick Start Guide', 'amazon-s3-and-cloudfront' ) );

		$message = sprintf( __( "Looks like we don't have write access to this bucket. It's likely that the user you've provided credentials for hasn't been granted the correct permissions. Please see our %s for instructions on setting up permissions correctly.", 'amazon-s3-and-cloudfront' ), $quick_start );
		if ( ! $single ) {
			$message = sprintf( __( "Looks like we don't have access to the buckets. It's likely that the user you've provided credentials for hasn't been granted the correct permissions. Please see our %s for instructions on setting up permissions correctly.", 'amazon-s3-and-cloudfront' ), $quick_start );
		}

		return $message;
	}

	/**
	 * Used to give a realistic total of storage space used on a Multisite subsite,
	 * when there have been attachments uploaded to S3 but removed from server
	 *
	 * @param $space_used bool
	 *
	 * @return float|int
	 */
	function multisite_get_space_used( $space_used ) {
		global $wpdb;

		// Sum the total file size (including image sizes) for all S3 attachments
		$sql = "SELECT SUM( meta_value ) AS bytes_total
				FROM {$wpdb->postmeta}
				WHERE meta_key = 'as3cf_filesize_total'";

		$space_used = $wpdb->get_var( $sql );

		// Get local upload sizes
		$upload_dir = wp_upload_dir();
		$space_used += get_dirsize( $upload_dir['basedir'] );

		if ( $space_used > 0 ) {
			// Convert to bytes to MB
			$space_used = $space_used / 1024 / 1024;
		}

		return $space_used;
	}

	/**
	 * Memory exceeded
	 *
	 * Ensures the a process never exceeds 90% of the maximum WordPress memory.
	 *
	 * @param null|string $filter_name Name of filter to apply to the return
	 *
	 * @return bool
	 */
	public function memory_exceeded( $filter_name = null ) {
		$memory_limit   = $this->get_memory_limit() * 0.9; // 90% of max memory
		$current_memory = memory_get_usage( true );
		$return         = false;

		if ( $current_memory >= $memory_limit ) {
			$return = true;
		}

		if ( is_null( $filter_name ) || ! is_string( $filter_name ) ) {
			return $return;
		}

		return apply_filters( $filter_name, $return );
	}

	/**
	 * Get memory limit
	 *
	 * @return int
	 */
	public function get_memory_limit() {
		if ( function_exists( 'ini_get' ) ) {
			$memory_limit = ini_get( 'memory_limit' );
		} else {
			// Sensible default
			$memory_limit = '128M';
		}

		if ( ! $memory_limit || -1 == $memory_limit ) {
			// Unlimited, set to 32GB
			$memory_limit = '32000M';
		}

		return intval( $memory_limit ) * 1024 * 1024;
	}

	/**
	 * Count attachments on a site
	 *
	 * @param string    $prefix
	 * @param null|bool $uploaded_to_provider
	 *      null  - All attachments
	 *      true  - Attachments only uploaded to S3
	 *      false - Attachments not uploaded to S3
	 *
	 * @return int
	 */
	public function count_attachments( $prefix, $uploaded_to_provider = null ) {
		global $wpdb;

		$sql = "SELECT COUNT(DISTINCT p.ID)
				FROM `{$prefix}posts` p";

		$where = "WHERE p.post_type = 'attachment'";

		if ( ! is_null( $uploaded_to_provider ) && is_bool( $uploaded_to_provider ) ) {
			$sql .= " LEFT OUTER JOIN `{$prefix}postmeta` pm
					ON p.`ID` = pm.`post_id`
					AND pm.`meta_key` = 'amazonS3_info'";

			$operator = $uploaded_to_provider ? 'not ' : '';
			$where    .= " AND pm.`post_id` is {$operator}null";
		}

		$sql .= ' ' . $where;

		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Get the total attachment and total S3 attachment counts for the diagnostic log
	 *
	 * @return array
	 */
	protected function diagnostic_media_counts() {
		if ( false === ( $attachment_counts = get_site_transient( 'as3cf_attachment_counts' ) ) ) {
			$table_prefixes     = $this->get_all_blog_table_prefixes();
			$all_media          = 0;
			$all_media_provider = 0;

			foreach ( $table_prefixes as $blog_id => $table_prefix ) {
				$count              = $this->count_attachments( $table_prefix );
				$all_media          += $count;
				$provider_count     = $this->count_attachments( $table_prefix, true );
				$all_media_provider += $provider_count;
			}

			$attachment_counts = array(
				'all' => $all_media,
				's3'  => $all_media_provider,
			);

			set_site_transient( 'as3cf_attachment_counts', $attachment_counts, 2 * MINUTE_IN_SECONDS );
		}

		return $attachment_counts;
	}

	/**
	 * Display a notice after either lite or pro plugin has been auto deactivated
	 */
	function plugin_deactivated_notice() {
		if ( false !== ( $deactivated_notice_id = get_transient( 'as3cf_deactivated_notice_id' ) ) ) {
			if ( '1' === $deactivated_notice_id ) {
				$title   = __( 'WP Offload Media Activation', 'amazon-s3-and-cloudfront' );
				$message = __( "WP Offload Media Lite and WP Offload Media cannot both be active. We've automatically deactivated WP Offload Media Lite.", 'amazon-s3-and-cloudfront' );
			} else {
				$title   = __( 'WP Offload Media Lite Activation', 'amazon-s3-and-cloudfront' );
				$message = __( "WP Offload Media Lite and WP Offload Media cannot both be active. We've automatically deactivated WP Offload Media.", 'amazon-s3-and-cloudfront' );
			}

			$message = sprintf( '<strong>%s</strong> &mdash; %s', esc_html( $title ), esc_html( $message ) );

			$this->render_view( 'notice', array( 'message' => $message ) );

			delete_transient( 'as3cf_deactivated_notice_id' );
		}
	}

	/**
	 * Throw error
	 *
	 * @param string $code
	 * @param string $message
	 * @param mixed  $data
	 *
	 * @return WP_Error
	 */
	public function _throw_error( $code, $message = '', $data = '' ) {
		return new WP_Error( $code, $message, $data );
	}

	/**
	 * Get UTM source for plugin.
	 *
	 * @return string
	 */
	protected function get_utm_source() {
		return $this->is_pro() ? 'OS3+Paid' : 'OS3+Free';
	}

	/**
	 * More info link.
	 *
	 * @param string $path
	 * @param string $utm_content
	 * @param string $hash
	 *
	 * @return string
	 */
	public function more_info_link( $path, $utm_content = '', $hash = '' ) {
		$args = array(
			'utm_campaign' => 'support+docs',
		);

		if ( ! empty( $utm_content ) ) {
			$args['utm_content'] = $utm_content;
		}

		$url  = $this->dbrains_url( $path, $args, $hash );
		$text = __( 'More&nbsp;info&nbsp;&raquo;', 'amazon-s3-and-cloudfront' );
		$link = AS3CF_Utils::dbrains_link( $url, $text );

		return sprintf( '<span class="more-info">%s</span>', $link );
	}

	/**
	 * Settings more info link.
	 *
	 * @param string $hash
	 * @param string $utm_content
	 *
	 * @return string
	 */
	public function settings_more_info_link( $hash, $utm_content = '' ) {
		return $this->more_info_link( '/wp-offload-media/doc/settings/', $utm_content, $hash );
	}

	/**
	 * Helper function for filtering super globals. Easily testable.
	 *
	 * @param string $variable
	 * @param int    $type
	 * @param int    $filter
	 * @param mixed  $options
	 *
	 * @return mixed
	 */
	public function filter_input( $variable, $type = INPUT_GET, $filter = FILTER_DEFAULT, $options = array() ) {
		return filter_input( $type, $variable, $filter, $options );
	}

	/**
	 * Helper function for terminating script execution. Easily testable.
	 *
	 * @param int|string $exit_code
	 *
	 * @return void
	 */
	public function _exit( $exit_code = 0 ) {
		exit( $exit_code );
	}

	/**
	 * Show the deprecated Domain option setting?
	 *
	 * @param null|string $domain
	 *
	 * @return bool
	 */
	public function show_deprecated_domain_setting( $domain = null ) {
		if ( is_null( $domain ) ) {
			$domain = $this->get_setting( 'domain' );
		}

		if ( ! in_array( $domain, array( 'path', 'cloudfront' ) ) ) {
			return true;
		}

		return apply_filters( 'as3cf_show_deprecated_domain_setting', false );
	}

	/**
	 * Upgrade the 'virtual host' / 'bucket as domain' setting to the
	 * new CloudFront / Domain setting
	 *
	 * @return string
	 */
	public function upgrade_virtual_host() {
		$domain = 'cloudfront';
		$this->set_setting( 'cloudfront', $this->get_setting( 'bucket' ) );
		$this->set_setting( 'domain', $domain );

		$this->save_settings();

		return $domain;
	}

	/**
	 * Display a notice if using setting to force HTTP as url scheme, removed in 1.3.
	 */
	protected function maybe_display_deprecated_http_notice() {
		if ( 'http' !== $this->get_setting( 'ssl', 'request' ) || ! $this->is_plugin_setup() ) {
			return;
		}

		$notice_args = array(
			'type'              => 'notice-info',
			'only_show_to_user' => false,
			'flash'             => false,
		);

		$doc_url  = $this->dbrains_url( '/wp-offload-media/doc/force-http-setting/', array(
			'utm_campaign' => 'support+docs',
		) );
		$doc_link = AS3CF_Utils::dbrains_link( $doc_url, __( 'this doc' ) );

		$message = sprintf( '<strong>%s</strong> &mdash; ', __( 'WP Offload Media Feature Removed', 'amazon-s3-and-cloudfront' ) );
		$message .= sprintf( __( 'You had the "Always non-SSL" option selected in your settings, but we\'ve removed this option in version 1.3. We\'ll now use HTTPS when the request is HTTPS and regular HTTP when the request is HTTP. This should work fine for your site, but please take a poke around and make sure things are working ok. See %s for more details on why we did this and how you can revert back to the old behavior.', 'amazon-s3-and-cloudfront' ), $doc_link );

		$this->notices->add_notice( $message, $notice_args );
	}

	/**
	 * Potentially update path for CloudFront URLs.
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	public function maybe_update_cloudfront_path( $path ) {
		if ( 'cloudfront' === $this->get_setting( 'domain' ) ) {
			$path_parts = apply_filters( 'as3cf_cloudfront_path_parts', explode( '/', $path ), $this->get_setting( 'cloudfront' ) );

			if ( ! empty( $path_parts ) ) {
				$path = implode( '/', $path_parts );
			}
		}

		return $path;
	}

	/**
	 * Add the S3 meta box to the attachment screen
	 */
	public function attachment_provider_meta_box() {
		add_meta_box(
			's3-actions',
			__( 'Offload', 'amazon-s3-and-cloudfront' ),
			array( $this, 'attachment_provider_actions_meta_box' ),
			'attachment',
			'side',
			'core'
		);
	}

	/**
	 * Check we can do the media actions
	 *
	 * @return bool
	 */
	public function verify_media_actions() {
		return false;
	}

	/**
	 * Get a list of available media actions which can be performed according to plugin and user capability requirements.
	 *
	 * @param string|null $scope
	 *
	 * @return array
	 */
	public function get_available_media_actions( $scope = '' ) {
		return array();
	}

	/**
	 * Render the S3 attachment meta box
	 */
	public function attachment_provider_actions_meta_box() {
		global $post;
		$file = get_attached_file( $post->ID, true );

		$args = array(
			'provider_object'   => $this->get_formatted_provider_info( $post->ID ),
			'post'              => $post,
			'local_file_exists' => file_exists( $file ),
			'available_actions' => $this->get_available_media_actions( 'singular' ),
			'sendback'          => 'post.php?post=' . $post->ID . '&action=edit',
		);

		$this->render_view( 'attachment-metabox', $args );
	}

	/**
	 * Get ACL value string.
	 *
	 * @param array $acl
	 * @param int   $post_id
	 *
	 * @return string
	 */
	protected function get_acl_value_string( $acl, $post_id ) {
		return $acl['name'];
	}

	/**
	 * Return a formatted S3 info with display friendly defaults
	 *
	 * @param int        $id
	 * @param array|null $provider_object
	 *
	 * @return array
	 */
	public function get_formatted_provider_info( $id, $provider_object = null ) {
		if ( is_null( $provider_object ) ) {
			if ( ! ( $provider_object = $this->get_attachment_provider_info( $id ) ) ) {
				return false;
			}
		}

		$provider_object['url'] = $this->get_attachment_provider_url( $id, $provider_object );

		$acl      = ( isset( $provider_object['acl'] ) ) ? $provider_object['acl'] : $this->get_provider()->get_default_acl();
		$acl_info = array(
			'acl'   => $acl,
			'name'  => $this->get_acl_display_name( $acl ),
			'title' => $this->get_media_action_strings( 'change_to_private' ),
		);

		if ( $this->get_provider()->get_private_acl() === $acl ) {
			$acl_info['title'] = $this->get_media_action_strings( 'change_to_public' );
		}

		$provider_object['acl'] = $acl_info;

		if ( isset( $provider_object['region'] ) ) {
			$provider_object['region'] = $this->get_provider()->get_region_name( $provider_object['region'] );
		}

		if ( ! empty( $provider_object['provider'] ) ) {
			$provider_object['provider_name'] = $this->get_provider_service_name( $provider_object['provider'] );
		}

		return $provider_object;
	}

	/**
	 * Get all strings or a specific string used for the media actions
	 *
	 * @param null|string $string
	 *
	 * @return array|string
	 */
	public function get_media_action_strings( $string = null ) {
		$strings = apply_filters( 'as3cf_media_action_strings', array(
			'provider'      => _x( 'Storage Provider', 'Storage provider key name', 'amazon-s3-and-cloudfront' ),
			'provider_name' => _x( 'Storage Provider', 'Storage provider name', 'amazon-s3-and-cloudfront' ),
			'bucket'        => _x( 'Bucket', 'Bucket name', 'amazon-s3-and-cloudfront' ),
			'key'           => _x( 'Path', 'Path to file in bucket', 'amazon-s3-and-cloudfront' ),
			'region'        => _x( 'Region', 'Location of bucket', 'amazon-s3-and-cloudfront' ),
			'acl'           => _x( 'Access', 'Access control list of the file in bucket', 'amazon-s3-and-cloudfront' ),
			'url'           => __( 'URL', 'amazon-s3-and-cloudfront' ),
		) );

		if ( ! is_null( $string ) ) {
			return isset( $strings[ $string ] ) ? $strings[ $string ] : '';
		}

		return $strings;
	}

	/**
	 * Load media assets.
	 */
	public function load_media_assets() {
		$this->enqueue_style( 'as3cf-media-styles', 'assets/css/media', array( 'as3cf-modal' ) );
		$this->enqueue_script( 'as3cf-media-script', 'assets/js/media', array(
			'jquery',
			'media-views',
			'media-grid',
			'wp-util',
		) );

		wp_localize_script( 'as3cf-media-script', 'as3cf_media', array(
			'strings' => $this->get_media_action_strings(),
			'nonces'  => array(
				'get_attachment_provider_details' => wp_create_nonce( 'get-attachment-s3-details' ),
			),
		) );
	}

	/**
	 * Handle retrieving the provider details for attachment modals.
	 */
	public function ajax_get_attachment_provider_details() {
		if ( ! isset( $_POST['id'] ) ) {
			return;
		}

		check_ajax_referer( 'get-attachment-s3-details', '_nonce' );

		$id = intval( $_POST['id'] );

		// get the actions available for the attachment
		$data = array(
			'links'           => $this->add_media_row_actions( array(), $id ),
			'provider_object' => $this->get_formatted_provider_info( $id ),
			'acl_toggle'      => $this->verify_media_actions() && $this->is_attachment_served_by_provider( $id, true ),
		);

		wp_send_json_success( $data );
	}

	/**
	 * Conditionally adds copy, remove and download S3 action links for an
	 * attachment on the Media library list view
	 *
	 * @param array       $actions
	 * @param WP_Post|int $post
	 *
	 * @return array
	 */
	function add_media_row_actions( Array $actions, $post ) {
		return $actions;
	}

	/**
	 * Load the attachment assets only when editing an attachment
	 *
	 * @param $hook_suffix
	 */
	public function load_attachment_assets( $hook_suffix ) {
		global $post;
		if ( 'post.php' !== $hook_suffix || 'attachment' !== $post->post_type ) {
			return;
		}

		$this->enqueue_style( 'as3cf-pro-attachment-styles', 'assets/css/attachment', array( 'as3cf-modal' ) );

		do_action( 'as3cf_load_attachment_assets' );
	}

	/**
	 * Maybe remove query string from URL.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function maybe_remove_query_string( $url ) {
		$parts = explode( '?', $url );

		return reset( $parts );
	}

	/**
	 * Normalize object prefix.
	 *
	 * @param string $prefix
	 *
	 * @return string
	 */
	protected function normalize_object_prefix( $prefix ) {
		$directory = dirname( $prefix );

		return ( '.' === $directory ) ? '' : $directory . '/';
	}

	/**
	 * Has the given attachment been uploaded by this instance?
	 *
	 * @param int $attachment_id
	 *
	 * @return bool
	 */
	public function attachment_just_uploaded( $attachment_id ) {
		if ( is_int( $attachment_id ) && in_array( $attachment_id, $this->uploaded_post_ids ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Filters the audio & video shortcodes output to remove "&_=NN" params from source.src as it breaks signed URLs.
	 *
	 * @param string $html    Shortcode HTML output.
	 * @param array  $atts    Array of shortcode attributes.
	 * @param string $media   Media file.
	 * @param int    $post_id Post ID.
	 * @param string $library Media library used for the shortcode.
	 *
	 * @return string
	 *
	 * Note: Depends on 30377.4.diff from https://core.trac.wordpress.org/ticket/30377
	 */
	public function wp_media_shortcode( $html, $atts, $media, $post_id, $library ) {
		$html = preg_replace( '/&#038;_=[0-9]+/', '', $html );

		return $html;
	}

	/**
	 * Ensure local URL is correct for multisite's non-primary subsites.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function maybe_fix_local_subsite_url( $url ) {
		$siteurl = trailingslashit( get_option( 'siteurl' ) );

		if ( is_multisite() && ! $this->is_current_blog( get_current_blog_id() ) && 0 !== strpos( $url, $siteurl ) ) {
			// Replace network URL with subsite's URL.
			$network_siteurl = trailingslashit( network_site_url() );
			$url             = str_replace( $network_siteurl, $siteurl, $url );
		}

		return $url;
	}

	/**
	 * Get ACL for intermediate size.
	 *
	 * @param int    $attachment_id
	 * @param string $size
	 *
	 * @return string
	 */
	public function get_acl_for_intermediate_size( $attachment_id, $size ) {
		$provider_info = $this->get_attachment_provider_info( $attachment_id );

		if ( 'original' === $size || empty( $size ) ) {
			return isset( $provider_info['acl'] ) ? $provider_info['acl'] : $this->get_provider()->get_default_acl();
		}

		if ( ! empty( $provider_info['sizes'][ $size ]['acl'] ) ) {
			return $provider_info['sizes'][ $size ]['acl'];
		}

		return $this->get_provider()->get_default_acl();
	}

	/**
	 * Consolidate an array of WP_Errors into a single WP_Error object.
	 *
	 * @param array $upload_errors
	 *
	 * @return WP_Error
	 */
	protected function consolidate_upload_errors( $upload_errors ) {
		$errors = new WP_Error;

		foreach ( $upload_errors as $error ) {

			/* @var WP_Error $error */
			$errors->add( $error->get_error_code(), $error->get_error_message() );
		}

		return $errors;
	}

	/**
	 * Get all defined addons that use this plugin
	 *
	 * @param bool $unfiltered
	 *
	 * @return array
	 */
	public function get_addons( $unfiltered = false ) {
		$addons = $this->get_available_addons();

		if ( $unfiltered ) {
			return $addons;
		}

		return apply_filters( 'as3cf_addons', $addons );
	}

	/**
	 * @return array
	 */
	protected function get_available_addons() {
		return array(
			'amazon-s3-and-cloudfront-assets-pull' => array(
				'title'  => __( 'Assets Pull', 'amazon-s3-and-cloudfront' ),
				'sub'    => __( 'An addon for WP Offload Media to serve your site\'s JS, CSS, and other enqueued assets from Amazon CloudFront or another CDN.', 'amazon-s3-and-cloudfront' ),
				'url'    => $this->dbrains_url( '/wp-offload-media/doc/assets-pull-addon/', array(
					'utm_campaign' => 'addons+install',
				) ),
				'label'  => __( 'Feature', 'amazon-s3-and-cloudfront' ),
				'icon'   => true,
				'active' => class_exists( 'Amazon_S3_And_CloudFront_Assets_Pull' ),
			),
		);
	}

	/**
	 * Get the URL of the addon's icon
	 *
	 * @param string $slug
	 *
	 * @return string
	 */
	function get_addon_icon_url( $slug ) {
		$filename = str_replace( 'amazon-s3-and-cloudfront-', '', $slug );
		$filename = 'icon-' . $filename . '.svg';

		return plugins_url( 'assets/img/' . $filename, $this->plugin_file_path );
	}

	/**
	 * Polyfill for displaying "Settings saved." consistently between single-site and multisite environments.
	 *
	 * TL;DR: options-head.php is loaded for options-general.php (single sites only) which does this, but not on multisite.
	 *
	 * @see https://github.com/WordPress/WordPress/blob/c2d709e9d6cbe7f9b3c37da0a7c9aae788158124/wp-admin/admin-header.php#L265-L266
	 * @see https://github.com/WordPress/WordPress/blob/9b68e5953406024c75b92f7ebe2aef0385c8956e/wp-admin/options-head.php#L13-L16
	 */
	public function settings_saved_notice() {
		if ( isset( $_GET['updated'] ) && isset( $_GET['page'] ) ) {
			// For back-compat with plugins that don't use the Settings API and just set updated=1 in the redirect.
			add_settings_error( 'general', 'settings_updated', __( 'Settings saved.' ), 'updated' );
		}

		settings_errors();
	}

	/**
	 * Migrate access keys from AWS database setting to this plugin's settings record and raise any notices.
	 */
	private function handle_aws_access_key_migration() {
		add_action( 'aws_access_key_form_header', array( $this, 'handle_aws_access_key_form_header' ) );

		if ( is_plugin_active( 'amazon-web-services/amazon-web-services.php' ) ) {
			$message = sprintf(
				__( '<strong>Amazon Web Services Plugin No Longer Required</strong> &mdash; As of version 1.6 of WP Offload Media, the <a href="%1$s">Amazon Web Services</a> plugin is no longer required. We have removed the dependency by bundling a small portion of the AWS SDK into WP Offload Media. As long as none of your other active plugins or themes depend on the Amazon Web Services plugin, it should be safe to deactivate and delete it. %2$s', 'amazon-s3-and-cloudfront' ),
				'https://wordpress.org/plugins/amazon-web-services/',
				$this->more_info_link( '/wp-offload-s3-1-6-released/', 'os3+settings+aws+active' )
			);
			$args    = array(
				'only_show_to_user'     => false,
				'only_show_in_settings' => true,
				'custom_id'             => 'aws-plugin-no-longer-required',
			);
			$this->notices->add_notice( $message, $args );

			if ( is_a( $this->get_provider(), '\DeliciousBrains\WP_Offload_Media\Providers\AWS_Provider' ) && $this->get_provider()->needs_access_keys() ) {
				// Have access keys been defined in still active AWS plugin's database settings?
				$aws_settings = get_site_option( 'aws_settings' );

				// If both AWS keys set and we already have a bucket set, safe to use the AWS keys.
				if ( ! empty( $aws_settings['access_key_id'] ) && ! empty( $aws_settings['secret_access_key'] ) && false !== $this->get_setting( 'bucket' ) ) {
					$this->set_setting( 'access-key-id', $aws_settings['access_key_id'] );
					$this->set_setting( 'secret-access-key', $aws_settings['secret_access_key'] );
					$this->save_settings();
				}
			}
		} else {
			$this->notices->remove_notice_by_id( 'aws-plugin-no-longer-required' );
		}
	}

	/**
	 * Create message in AWS access key form that this plugin no longer uses those settings.
	 */
	public function handle_aws_access_key_form_header() {
		$notice['message'] = sprintf(
			__( '<strong>WP Offload Media Settings Moved</strong> &mdash; You now define your AWS keys for WP Offload Media in the new <a href="%1$s">Settings tab</a>. Saving settings in the form below will have no effect on WP Offload Media. %2$s', 'amazon-s3-and-cloudfront' ),
			$this->get_plugin_page_url( array( 'hash' => 'settings' ) ),
			$this->more_info_link( '/wp-offload-s3-1-6-released/', 'aws+os3+access+keys+setting+moved' )
		);
		$notice['inline']  = true;

		$this->render_view( 'notice', $notice );
	}

	/**
	 * Remove 'filesize' from attachment's metatdata if appropriate, also our total filesize record.
	 *
	 * @param integer $post_id         Attachment's post_id.
	 * @param array   $data            Attachment's metadata.
	 * @param bool    $update_metadata Update the metadata record now? Defaults to true.
	 *
	 * @return array Attachment's cleaned up metadata.
	 */
	public function maybe_cleanup_filesize_metadata( $post_id, $data, $update_metadata = true ) {
		if ( ! is_int( $post_id ) || empty( $post_id ) || empty( $data ) || ! is_array( $data ) ) {
			return $data;
		}

		/*
		 * Audio and video have a filesize added to metadata by default, but images and anything else don't.
		 * Note: Could have used `wp_generate_attachment_metadata` here to test whether default metadata has 'filesize',
		 * but it not only has side effects it also does a lot of work considering it's not a huge deal for this entry to hang around.
		 */
		if (
			empty( $data['mime_type'] ) ||
			0 === strpos( $data['mime_type'], 'image/' ) ||
			! ( 0 === strpos( $data['mime_type'], 'audio/' ) || 0 === strpos( $data['mime_type'], 'video/' ) )
		) {
			unset( $data['filesize'] );
		}

		if ( $update_metadata ) {
			if ( empty( $data ) ) {
				delete_post_meta( $post_id, '_wp_attachment_metadata' );
			} else {
				update_post_meta( $post_id, '_wp_attachment_metadata', $data );
			}
		}

		delete_post_meta( $post_id, 'as3cf_filesize_total' );

		return $data;
	}
}
