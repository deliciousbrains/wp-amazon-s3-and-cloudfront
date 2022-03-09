<?php

use DeliciousBrains\WP_Offload_Media\Integrations\Core as Core_Integration;
use DeliciousBrains\WP_Offload_Media\Integrations\Integration_Manager;
use DeliciousBrains\WP_Offload_Media\Integrations\Media_Library as Media_Library_Integration;
use DeliciousBrains\WP_Offload_Media\Items\Download_Handler;
use DeliciousBrains\WP_Offload_Media\Items\Item;
use DeliciousBrains\WP_Offload_Media\Items\Item_Handler;
use DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item;
use DeliciousBrains\WP_Offload_Media\Items\Remove_Local_Handler;
use DeliciousBrains\WP_Offload_Media\Items\Remove_Provider_Handler;
use DeliciousBrains\WP_Offload_Media\Items\Upload_Handler;
use DeliciousBrains\WP_Offload_Media\Providers\Delivery\Another_CDN;
use DeliciousBrains\WP_Offload_Media\Providers\Delivery\AWS_CloudFront;
use DeliciousBrains\WP_Offload_Media\Providers\Delivery\Cloudflare;
use DeliciousBrains\WP_Offload_Media\Providers\Delivery\Delivery_Provider;
use DeliciousBrains\WP_Offload_Media\Providers\Delivery\DigitalOcean_Spaces_CDN;
use DeliciousBrains\WP_Offload_Media\Providers\Delivery\GCP_CDN;
use DeliciousBrains\WP_Offload_Media\Providers\Delivery\KeyCDN;
use DeliciousBrains\WP_Offload_Media\Providers\Delivery\Other;
use DeliciousBrains\WP_Offload_Media\Providers\Delivery\StackPath;
use DeliciousBrains\WP_Offload_Media\Providers\Delivery\Storage;
use DeliciousBrains\WP_Offload_Media\Providers\Provider;
use DeliciousBrains\WP_Offload_Media\Providers\Storage\AWS_Provider;
use DeliciousBrains\WP_Offload_Media\Providers\Storage\DigitalOcean_Provider;
use DeliciousBrains\WP_Offload_Media\Providers\Storage\GCP_Provider;
use DeliciousBrains\WP_Offload_Media\Providers\Storage\Null_Provider;
use DeliciousBrains\WP_Offload_Media\Providers\Storage\Storage_Provider;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_Content_Replace_URLs;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_EDD_Replace_URLs;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_File_Sizes;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_Filter_Post_Excerpt;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_Item_Extra_Data;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_Items_Table;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_Meta_WP_Error;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_Region_Meta;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_Tools_Errors;
use DeliciousBrains\WP_Offload_Media\Upgrades\Upgrade_WPOS3_To_AS3CF;

class Amazon_S3_And_CloudFront extends AS3CF_Plugin_Base {

	/**
	 * @var Storage_Provider
	 */
	private $storage_provider;

	/**
	 * @var Storage_Provider
	 */
	private $provider_client;

	/**
	 * @var string
	 */
	private $provider_client_region;

	/**
	 * @var Delivery_Provider
	 */
	private $delivery_provider;

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
	protected static $default_storage_provider = 'aws';

	/**
	 * @var string
	 */
	protected static $default_delivery_provider = 'storage';

	/**
	 * @var array Known storage provider classes.
	 */
	protected static $storage_provider_classes = array();

	/**
	 * @var array Known delivery provider classes.
	 */
	protected static $delivery_provider_classes = array();

	/**
	 * @var Item_Handler[]
	 */
	protected $item_handlers = array();

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

	/**
	 * Class map to determine Item subclass per item source type
	 *
	 * @var string[]
	 */
	private $source_type_classes = array(
		'media-library' => 'DeliciousBrains\WP_Offload_Media\Items\Media_Library_Item',
	);

	/**
	 * @var Integration_Manager
	 */
	protected $integration_manager;

	const LATEST_UPGRADE_ROUTINE = 10;

	/**
	 * @param string      $plugin_file_path
	 * @param string|null $slug
	 *
	 * @throws Exception
	 */
	public function __construct( $plugin_file_path, $slug = null ) {
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
	public function init( $plugin_file_path ) {
		$this->plugin_title      = __( 'Offload Media Lite', 'amazon-s3-and-cloudfront' );
		$this->plugin_menu_title = __( 'Offload Media Lite', 'amazon-s3-and-cloudfront' );

		static::$storage_provider_classes = apply_filters( 'as3cf_storage_provider_classes', array(
			AWS_Provider::get_provider_key_name()          => 'DeliciousBrains\WP_Offload_Media\Providers\Storage\AWS_Provider',
			DigitalOcean_Provider::get_provider_key_name() => 'DeliciousBrains\WP_Offload_Media\Providers\Storage\DigitalOcean_Provider',
			GCP_Provider::get_provider_key_name()          => 'DeliciousBrains\WP_Offload_Media\Providers\Storage\GCP_Provider',
		) );

		static::$delivery_provider_classes = apply_filters( 'as3cf_delivery_provider_classes', array(
			AWS_CloudFront::get_provider_key_name()          => 'DeliciousBrains\WP_Offload_Media\Providers\Delivery\AWS_CloudFront',
			DigitalOcean_Spaces_CDN::get_provider_key_name() => 'DeliciousBrains\WP_Offload_Media\Providers\Delivery\DigitalOcean_Spaces_CDN',
			GCP_CDN::get_provider_key_name()                 => 'DeliciousBrains\WP_Offload_Media\Providers\Delivery\GCP_CDN',
			Another_CDN::get_provider_key_name()             => 'DeliciousBrains\WP_Offload_Media\Providers\Delivery\Another_CDN',
			// Sub Options of Another CDN.
			Cloudflare::get_provider_key_name()              => 'DeliciousBrains\WP_Offload_Media\Providers\Delivery\Cloudflare',
			KeyCDN::get_provider_key_name()                  => 'DeliciousBrains\WP_Offload_Media\Providers\Delivery\KeyCDN',
			StackPath::get_provider_key_name()               => 'DeliciousBrains\WP_Offload_Media\Providers\Delivery\StackPath',
			Other::get_provider_key_name()                   => 'DeliciousBrains\WP_Offload_Media\Providers\Delivery\Other',
			// Fallback to raw storage URLs.
			Storage::get_provider_key_name()                 => 'DeliciousBrains\WP_Offload_Media\Providers\Delivery\Storage',
		) );

		$this->set_storage_provider();
		$this->set_delivery_provider();

		// Bundled SDK may require AWS setup before data migrations.
		$this->handle_aws_access_key_migration();

		new Upgrade_Region_Meta( $this );
		new Upgrade_File_Sizes( $this );
		new Upgrade_Meta_WP_Error( $this );
		new Upgrade_Content_Replace_URLs( $this );
		new Upgrade_EDD_Replace_URLs( $this );
		new Upgrade_Filter_Post_Excerpt( $this );
		new Upgrade_WPOS3_To_AS3CF( $this );
		new Upgrade_Items_Table( $this );
		new Upgrade_Tools_Errors( $this );
		new Upgrade_Item_Extra_Data( $this );

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

		// UI AJAX
		add_action( 'wp_ajax_as3cf-get-buckets', array( $this, 'ajax_get_buckets' ) );
		add_action( 'wp_ajax_as3cf-get-url-preview', array( $this, 'ajax_get_url_preview' ) );
		add_action( 'wp_ajax_as3cf-get-diagnostic-info', array( $this, 'ajax_get_diagnostic_info' ) );

		// Enable integrations once everything has been initialized.
		add_action( 'as3cf_init', array( $this, 'enable_integrations' ) );

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

		// Register delivery provider scripts and styles
		$this->register_delivery_provider_assets();
	}

	/**
	 * Enable integrations.
	 *
	 * @param Amazon_S3_And_CloudFront $as3cf
	 */
	public function enable_integrations( $as3cf ) {
		/**
		 * Filters which integrations to enable. To disable an integration
		 * implement this filter and unset all unwanted integrations from
		 * the array.
		 *
		 * @param array $integrations Associative array of integrations
		 */
		$integrations = apply_filters( 'as3cf_integrations', array(
			'core' => new Core_Integration( $as3cf ),
			'mlib' => new Media_Library_Integration( $as3cf ),
		) );

		$this->integration_manager = Integration_Manager::get_instance();

		foreach ( $integrations as $integration_key => $integration ) {
			$this->integration_manager->register_integration( $integration_key, $integration );
		}
	}

	/**
	 * @return Storage_Provider
	 */
	public function get_storage_provider() {
		return $this->storage_provider;
	}

	/**
	 * Backwards compat alias function for `get_storage_provider()`.
	 *
	 * @return Provider
	 *
	 * @deprecated Use get_storage_provider
	 */
	public function get_provider() {
		return $this->get_storage_provider();
	}

	/**
	 * @param Storage_Provider|string|null $storage_provider
	 *
	 * @throws Exception
	 */
	public function set_storage_provider( $storage_provider = null ) {
		if ( empty( $storage_provider ) ) {
			$storage_provider = $this->get_core_setting( 'provider', static::get_default_storage_provider() );
		}

		// Specified provider does not exist, fall back to default.
		if ( is_string( $storage_provider ) && empty( self::$storage_provider_classes[ $storage_provider ] ) ) {
			$storage_provider = static::get_default_storage_provider();
		}

		if ( is_string( $storage_provider ) && ! empty( self::$storage_provider_classes[ $storage_provider ] ) ) {
			$storage_provider = new self::$storage_provider_classes[ $storage_provider ]( $this );
		}

		if ( ! empty( $storage_provider ) && ! is_string( $storage_provider ) ) {
			$this->storage_provider = $storage_provider;
		} else {
			// We really tried, we really did, but we're going to have to let things fail.
			$this->storage_provider = null;
		}
	}

	/**
	 * @return Delivery_Provider
	 */
	public function get_delivery_provider() {
		return $this->delivery_provider;
	}

	/**
	 * @param Delivery_Provider|string|null $delivery_provider
	 *
	 * @throws Exception
	 */
	public function set_delivery_provider( $delivery_provider = null ) {
		if ( empty( $delivery_provider ) ) {
			$delivery_provider = $this->get_core_setting( 'delivery-provider', static::get_default_delivery_provider() );
		}

		// Specified provider does not exist, fall back to default.
		if ( is_string( $delivery_provider ) && empty( self::$delivery_provider_classes[ $delivery_provider ] ) ) {
			$delivery_provider = static::get_default_delivery_provider();
		}

		if ( is_string( $delivery_provider ) && ! empty( self::$delivery_provider_classes[ $delivery_provider ] ) ) {
			$delivery_provider = new self::$delivery_provider_classes[ $delivery_provider ]( $this );
		}

		if ( ! empty( $delivery_provider ) && ! is_string( $delivery_provider ) ) {
			$this->delivery_provider = $delivery_provider;
		} else {
			// We really tried, we really did, but we're going to have to let things fail.
			$this->delivery_provider = null;
		}
	}

	/**
	 * Returns the currently supported Providers.
	 *
	 * @param string $type Which type of provider, "storage" or "delivery".
	 *
	 * @return array
	 */
	public function get_provider_classes( $type ) {
		switch ( $type ) {
			case 'storage':
				$providers = self::$storage_provider_classes;
				break;
			case 'delivery':
				$providers = self::$delivery_provider_classes;
				break;
			default:
				$providers = array(); // Error.
		}

		return $providers;
	}

	/**
	 * Returns provider class name for given key.
	 *
	 * @param string $key_name
	 * @param string $type
	 *
	 * @return Provider|null
	 */
	public function get_provider_class( $key_name, $type = 'storage' ) {
		if ( empty( $type ) ) {
			$type = 'storage';
		}
		$classes = $this->get_provider_classes( $type );

		return empty( $classes[ $key_name ] ) ? null : $classes[ $key_name ];
	}

	/**
	 * Provider name for given key.
	 *
	 * @param string $key_name
	 * @param string $type
	 *
	 * @return string
	 */
	public function get_provider_name( $key_name, $type = 'storage' ) {
		if ( empty( $type ) ) {
			$type = 'storage';
		}
		$class = $this->get_provider_class( $key_name, $type );

		return empty( $class ) ? __( 'Unknown', 'amazon-s3-and-cloudfront' ) : $class::get_provider_name();
	}

	/**
	 * Provider & Service name for given key.
	 *
	 * @param string $key_name
	 * @param string $type
	 *
	 * @return string
	 */
	public function get_provider_service_name( $key_name, $type = 'storage' ) {
		if ( empty( $type ) ) {
			$type = 'storage';
		}
		$class = $this->get_provider_class( $key_name, $type );

		return empty( $class ) ? __( 'Unknown', 'amazon-s3-and-cloudfront' ) : $class::get_provider_service_name();
	}

	/**
	 * Getter for the Integrations Manager instance
	 *
	 * @return Integration_Manager
	 */
	public function get_integration_manager() {
		return $this->integration_manager;
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
	 * Get the plugin title to be used in admin menu
	 *
	 * @return string
	 */
	function get_plugin_menu_title() {
		return apply_filters( 'as3cf_settings_menu_title', $this->plugin_menu_title );
	}

	/**
	 * Get the plugin prefix.
	 *
	 * @return string
	 */
	public function get_plugin_prefix() {
		return $this->plugin_prefix;
	}

	/**
	 * Get the plugin prefix in slug format, ie. replace underscores with hyphens
	 *
	 * @return string
	 */
	public function get_plugin_prefix_slug() {
		return str_replace( '_', '-', $this->get_plugin_prefix() );
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
			'tr_class'      => 'as3cf-settings-container ' . str_replace( '_', '-', $this->get_plugin_prefix() . '-' . $key . '-container' ),
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

		// Delivery Provider since 2.4.
		if ( 'delivery-provider' === $key && ! isset( $settings['delivery-provider'] ) ) {
			if ( ! empty( $this->get_setting( 'delivery-domain' ) ) ) {
				// Try and guess delivery provider from delivery domain, default to "other" if domain unknown.
				$delivery_provider = 'other';
				$domain            = $this->get_setting( 'cloudfront' );

				if ( strstr( $domain, '.cloudfront.net' ) ) {
					$delivery_provider = AWS_CloudFront::get_provider_key_name();
				} elseif ( strstr( $domain, '.cdn.digitaloceanspaces.com' ) ) {
					$delivery_provider = DigitalOcean_Spaces_CDN::get_provider_key_name();
				} elseif (
					'gcp' === $this->get_storage_provider()->get_provider_key_name() &&
					false === strstr( $domain, $this->get_storage_provider()->get_domain() )
				) {
					$delivery_provider = GCP_CDN::get_provider_key_name();
				}
			} else {
				// No delivery provider, fallback to default, which should equate to delivery via storage provider's defaults.
				$delivery_provider = $default;
			}

			return $delivery_provider;
		}

		// Delivery Domain since 2.4.
		if ( 'enable-delivery-domain' === $key && ! isset( $settings['enable-delivery-domain'] ) ) {
			if ( ! empty( $this->get_setting( 'delivery-domain' ) ) ) {
				return true;
			}

			return false;
		}

		// Delivery Domain since 2.4.
		if ( 'delivery-domain' === $key && ! isset( $settings['delivery-domain'] ) ) {
			if ( 'cloudfront' === $this->get_setting( 'domain' ) && ! empty( $this->get_setting( 'cloudfront' ) ) ) {
				$delivery_domain = $this->get_setting( 'cloudfront' );
			} else {
				// No delivery domain, fallback to storage provider's default domain.
				$delivery_domain = '';
			}

			return $delivery_domain;
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

		// Use Bucket ACLs
		if ( null !== ( $use_bucket_acls = $this->get_setting_use_bucket_acls( $settings, $key, null ) ) ) {
			return $use_bucket_acls;
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
			return $this->get_storage_provider()->sanitize_region( $settings['region'] );
		}

		return false;
	}

	/**
	 * Get the bucket and if a constant remove from database and clear region
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
		$this->remove_setting( 'use-bucket-acls' );
		$this->save_settings();
	}

	/**
	 * Get the derived use-bucket-acls setting
	 *
	 * @param array  $settings
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return bool|null
	 */
	public function get_setting_use_bucket_acls( $settings, $key, $default ) {
		if ( 'use-bucket-acls' === $key && isset( $settings['use-bucket-acls'] ) ) {
			return $settings['use-bucket-acls'];
		}

		if ( 'use-bucket-acls' === $key && ! isset( $settings['use-bucket-acls'] ) ) {
			if ( ! $this->get_storage_provider()->block_public_access_allowed() ) {
				// Got no choice, must use ACLs.
				parent::set_setting( 'use-bucket-acls', true );
				$this->save_settings();

				return true;
			}

			$bucket = $this->get_setting( 'bucket' );
			$region = $this->get_setting( 'region' );

			try {
				$public_access_blocked = $this->get_provider_client( $region )->public_access_blocked( $bucket );
			} catch ( Exception $e ) {
				$public_access_blocked = null;
			}

			// At present, we default to using ACLs if public access to bucket status unknown.
			if ( empty( $public_access_blocked ) || true !== $public_access_blocked ) {
				$use_bucket_acls = true;
			} else {
				$use_bucket_acls = false;
			}

			parent::set_setting( 'use-bucket-acls', $use_bucket_acls );
			$this->save_settings();

			return $use_bucket_acls;
		}

		return $default;
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
			'access-key-id',
			'secret-access-key',
			'key-file-path',
			'key-file',
			'use-server-roles',
			'bucket',
			'region',
			'delivery-provider-service-name',
			'use-bucket-acls',
			'virtual-host',
			'domain', // Legacy
			'cloudfront', // Legacy
		);

		foreach ( $whitelisted_settings as $setting ) {
			if ( in_array( $setting, $settings_to_skip ) ) {
				continue;
			}

			if (
				'object-prefix' === $setting &&
				isset( $defined_settings['enable-object-prefix'] ) &&
				empty( $defined_settings['enable-object-prefix'] )
			) {
				continue;
			}

			if (
				in_array( $setting, array( 'enable-delivery-domain', 'delivery-domain' ) ) &&
				! $this->get_delivery_provider()->delivery_domain_allowed()
			) {
				continue;
			}

			if (
				'delivery-domain' === $setting &&
				isset( $defined_settings['enable-delivery-domain'] ) &&
				empty( $defined_settings['enable-delivery-domain'] )
			) {
				continue;
			}

			if (
				in_array( $setting, array( 'enable-signed-urls', 'signed-urls-key-id', 'signed-urls-key-file-path', 'signed-urls-object-prefix' ) ) &&
				! $this->get_delivery_provider()->use_signed_urls_key_file_allowed()
			) {
				continue;
			}

			if (
				in_array( $setting, array( 'signed-urls-key-id', 'signed-urls-key-file-path', 'signed-urls-object-prefix' ) ) &&
				isset( $defined_settings['enable-signed-urls'] ) && empty( $defined_settings['enable-signed-urls'] )
			) {
				continue;
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
	public function get_allowed_mime_types() {
		/**
		 * Filters list of allowed mime types and file extensions for uploading
		 *
		 * @param array $types Mime types keyed by the file extension regex corresponding to those types.
		 */
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
	public function get_url_preview( $escape = true, $suffix = 'photo.jpg' ) {
		$as3cf_item = new Media_Library_Item(
			$this->get_storage_provider()->get_provider_key_name(),
			$this->get_setting( 'region' ),
			$this->get_setting( 'bucket' ),
			AS3CF_Utils::trailingslash_prefix( $this->get_simple_file_prefix() ) . $suffix,
			false,
			null,
			AS3CF_Utils::trailingslash_prefix( $this->get_simple_file_prefix() ) . $suffix
		);

		$url = $as3cf_item->get_provider_url();

		if ( is_wp_error( $url ) ) {
			return '';
		}

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

		// WordPress doesn't check its own basic record, so we will.
		$sql = $wpdb->prepare( "
			SELECT COUNT(*)
			FROM $wpdb->postmeta
			WHERE meta_key = %s
			AND meta_value = %s
		", '_wp_attached_file', $file );

		if ( (bool) $wpdb->get_var( $sql ) ) {
			return true;
		}

		// Check our records of local source path as it also covers original_image.
		if ( ! empty( Media_Library_Item::get_by_source_path( array( $file ), array(), true, true ) ) ) {
			return true;
		}

		return false;
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
		$filename = $name . '-' . $count . $ext;

		while ( $this->does_file_exist( $filename, $time ) ) {
			$count++;
			$filename = $name . '-' . $count . $ext;
		}

		return $filename;
	}

	/**
	 * Check the plugin is correctly setup
	 *
	 * @param bool $with_credentials Do provider credentials need to be set up too? Defaults to false.
	 *
	 * @return bool
	 *
	 * TODO: Performance - cache / static var by param.
	 */
	function is_plugin_setup( $with_credentials = false ) {
		if ( $with_credentials && $this->get_storage_provider()->needs_access_keys() ) {
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
	 * Is attachment served by provider.
	 *
	 * @param int                   $attachment_id
	 * @param bool                  $skip_rewrite_check          Still check if offloaded even if not currently rewriting URLs? Default: false
	 * @param bool                  $skip_current_provider_check Skip checking if offloaded to current provider. Default: false, negated if $provider supplied
	 * @param Storage_Provider|null $provider                    Provider where attachment expected to be offloaded to. Default: currently configured provider
	 * @param bool                  $check_is_verified           Check that metadata is verified, has no effect if $skip_rewrite_check is true. Default: false
	 *
	 * @return bool|Media_Library_Item
	 */
	public function is_attachment_served_by_provider( $attachment_id, $skip_rewrite_check = false, $skip_current_provider_check = false, Storage_Provider $provider = null, $check_is_verified = false ) {
		if ( ! $skip_rewrite_check && ! $this->get_setting( 'serve-from-s3' ) ) {
			// Not serving provider URLs
			return false;
		}

		$as3cf_item = Media_Library_Item::get_by_source_id( $attachment_id );

		if ( ! $as3cf_item ) {
			// File not uploaded to a provider
			return false;
		}

		if ( ! $skip_rewrite_check && ! empty( $check_is_verified ) && ! $as3cf_item->is_verified() ) {
			// Offload not verified, treat as not offloaded.
			return false;
		}

		if ( ! $skip_current_provider_check && empty( $provider ) ) {
			$provider = $this->get_storage_provider();
		}

		if ( ! empty( $provider ) && $provider::get_provider_key_name() !== $as3cf_item->provider() ) {
			// File not uploaded to required provider
			return false;
		}

		return $as3cf_item;
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

			if ( ! is_null( $region ) && $this->get_storage_provider()->get_default_region() !== $region ) {
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

			// Ensure Use Bucket ACLs is refreshed.
			$this->remove_setting( 'use-bucket-acls' );

			if ( empty( $region ) ) {
				// retrieve the bucket region if not supplied
				$region = $this->get_bucket_region( $bucket_name );
				if ( is_wp_error( $region ) ) {
					return $region;
				}
			}

			if ( ! $this->get_storage_provider()->region_required() && $this->get_storage_provider()->get_default_region() === $region ) {
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
			$this->get_plugin_menu_title(),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'render_page' )
		);

		do_action( 'as3cf_hook_suffix', $this->hook_suffix );

		add_action( 'load-' . $this->hook_suffix, array( $this, 'plugin_load' ) );
	}

	/**
	 * In our settings screen?
	 *
	 * @param WP_Screen|null $screen
	 *
	 * @return bool
	 */
	public function our_screen( WP_Screen $screen = null ) {
		if ( ! is_admin() || empty( $this->hook_suffix ) ) {
			return false;
		}

		if ( empty( $screen ) ) {
			$screen = get_current_screen();
		}

		if ( false === strpos( $screen->id, $this->hook_suffix ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Add the settings page to the top-level AWS menu item for backwards compatibility.
	 *
	 * @param Amazon_Web_Services $aws Plugin class instance from the amazon-web-services plugin.
	 */
	public function aws_admin_menu( $aws ) {
		$aws->add_page(
			$this->get_plugin_page_title(),
			$this->get_plugin_menu_title(),
			'manage_options',
			$this->plugin_slug,
			array( $this, 'render_page' )
		);
	}

	/**
	 * What is the default storage provider for legacy data?
	 *
	 * @return string
	 */
	public static function get_default_storage_provider() {
		return static::$default_storage_provider;
	}

	/**
	 * What is the default delivery provider for legacy data?
	 *
	 * @return string
	 */
	public static function get_default_delivery_provider() {
		return static::$default_delivery_provider;
	}

	/**
	 * Returns the Provider's default region slug.
	 *
	 * @return string
	 */
	public function get_default_region() {
		return $this->get_storage_provider()->get_default_region();
	}

	/**
	 * Get the S3 client
	 *
	 * @param bool|string $region specify region to client for signature
	 * @param bool        $force  force return of new provider client when swapping regions
	 *
	 * @return Storage_Provider|Null_Provider
	 * @throws Exception
	 */
	public function get_provider_client( $region = false, $force = false ) {
		if ( is_null( $this->provider_client ) ||
		     is_null( $this->provider_client_region ) ||
		     $force ||
		     ( false !== $region && $this->provider_client_region !== $region ) ) {
			$args = array();

			if ( $force ) {
				$this->set_storage_provider();
			}

			if ( $region ) {
				$args['region'] = $this->get_storage_provider()->sanitize_region( $region );
			}

			$provider_client_region = isset( $args['region'] ) ? $args['region'] : $region;

			try {
				$this->set_client( $this->get_storage_provider()->get_client( $args ), $provider_client_region );
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
	 * @param Storage_Provider|Null_Provider $client
	 * @param bool|string                    $region
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

		$region = $this->get_storage_provider()->sanitize_region( $region );

		if ( is_string( $region ) ) {
			$regions[ $bucket ] = $region;
			set_site_transient( 'as3cf_regions_cache', $regions, 5 * MINUTE_IN_SECONDS );
		}

		return $region;
	}

	/**
	 * AJAX handler for get_buckets()
	 */
	function ajax_get_buckets() {
		$this->verify_ajax_request();

		$region = empty( $_POST['region'] ) ? '' : $_POST['region'];
		$region = $this->check_region( $region, $this->get_storage_provider()->region_required() );

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
		if ( $this->get_storage_provider()->needs_access_keys() ) {
			// If no access keys set then no need check.
			return false;
		}

		if ( is_null( $bucket ) ) {
			// If changing provider or bucket don't bother to test saved bucket permissions.
			if ( ! empty( $_GET['action'] ) && in_array( $_GET['action'], array( 'change-provider', 'change-bucket', 'change-delivery-provider' ) ) ) {
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

		$key           = $this->get_simple_file_prefix() . 'as3cf-permission-check.txt';
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
	 * Get the file prefix for test and display purposes.
	 *
	 * Note: This should only be used for "naive" prefix calculations for
	 * display and write permission test purposes
	 *
	 * @param null|string $time
	 * @param bool        $object_versioning_allowed Can an Object Versioning string be appended if setting turned on? Default true.
	 *
	 * @return string
	 */
	private function get_simple_file_prefix( $time = null, $object_versioning_allowed = true ) {
		$prefix = AS3CF_Utils::trailingslash_prefix( $this->get_object_prefix() );
		$prefix .= AS3CF_Utils::trailingslash_prefix( $this->get_dynamic_prefix( $time ) );

		if ( ! empty( $object_versioning_allowed ) && $this->get_setting( 'object-versioning' ) ) {
			$prefix .= AS3CF_Utils::trailingslash_prefix( $this->get_object_version_string() );
		}

		return $prefix;
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
	 * Register delivery provider scripts and styles so they can be enqueued later
	 */
	function register_delivery_provider_assets() {
		$version = $this->get_asset_version();
		$suffix  = $this->get_asset_suffix();

		$src = plugins_url( 'assets/css/delivery-provider.css', $this->plugin_file_path );
		wp_register_style( 'as3cf-delivery-provider', $src, array(), $version );

		$src = plugins_url( 'assets/js/delivery-provider' . $suffix . '.js', $this->plugin_file_path );
		wp_register_script( 'as3cf-delivery-provider', $src, array( 'jquery' ), $version, true );
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

		$this->enqueue_style( 'as3cf-styles', 'assets/css/styles', array( 'as3cf-modal', 'as3cf-storage-provider', 'as3cf-delivery-provider' ) );
		$this->enqueue_script( 'as3cf-script', 'assets/js/script', array( 'jquery', 'underscore', 'as3cf-modal', 'as3cf-storage-provider', 'as3cf-delivery-provider' ) );

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
				'provider_console_url'              => $this->get_storage_provider()->get_console_url(),
				'provider_console_url_prefix_param' => $this->get_storage_provider()->get_console_url_prefix_param(),
			)
		);

		$this->handle_post_request();
		$this->http_prepare_download_log();
		$this->check_for_gd_imagick();
		$this->check_for_items_table();

		do_action( 'as3cf_plugin_load' );
	}

	/**
	 * Whitelist of settings allowed to be saved
	 *
	 * @return array
	 */
	function get_settings_whitelist() {
		return array(
			// Storage
			'provider',
			'access-key-id',
			'secret-access-key',
			'key-file-path',
			'key-file',
			'use-server-roles',
			'bucket',
			'region',
			'use-bucket-acls',
			'enable-object-prefix',
			'object-prefix',
			'use-yearmonth-folders',
			'object-versioning',
			'copy-to-s3', // TODO: Rename
			// Delivery
			'delivery-provider',
			'delivery-provider-service-name',
			'enable-delivery-domain',
			'delivery-domain',
			'virtual-host', // Legacy
			'domain', // Legacy
			'cloudfront', // Legacy
			'enable-signed-urls',
			'signed-urls-key-id',
			'signed-urls-key-file-path',
			'signed-urls-object-prefix',
			'force-https',
			'serve-from-s3', // TODO: Rename
			// Advanced
			'remove-local-file',
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
	 * @inheritDoc
	 */
	function get_path_format_settings() {
		return array(
			'key-file-path',
			'signed-urls-key-file-path',
		);
	}

	/**
	 * @inheritDoc
	 */
	function get_prefix_format_settings() {
		return array(
			'object-prefix',
			'signed-urls-object-prefix',
		);
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
		$orig_provider = isset( $_GET['orig_provider'] ) ? $_GET['orig_provider'] : '';

		// If we already have a bucket, then we should keep track of the associated provider too.
		if ( empty( $orig_provider ) && $this->get_setting( 'bucket', false ) ) {
			$orig_provider = $this->get_setting( 'provider' );
		}

		if ( $this->get_storage_provider()->needs_access_keys() || ( ! empty( $_GET['action'] ) && 'change-provider' === $_GET['action'] ) ) {
			// Changing Provider currently doesn't need anything special over saving settings,
			// but if not already set needs to be handled rather than change-bucket raising its hand.
			$changed_keys = $this->handle_save_settings();
		} elseif ( empty( $this->get_setting( 'bucket' ) ) || ( ! empty( $_GET['action'] ) && 'change-bucket' === $_GET['action'] ) ) {
			$changed_keys = $this->handle_change_bucket();
		} elseif ( ! empty( $_GET['action'] ) && 'change-bucket-access' === $_GET['action'] ) {
			$changed_keys = $this->handle_change_bucket_access();
		} elseif ( ! empty( $_GET['action'] ) && 'change-delivery-provider' === $_GET['action'] ) {
			$changed_keys = $this->handle_save_settings();
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

			// If anything about the Provider has changed then we need to verify the bucket selection.
			// If the bucket has changed, and provider allows blocking public access to bucket, verify bucket access requirement.
			// Otherwise we can let the filter decide whether there is an action to take.
			// Last implementer will win, but the above handlers take care of grouping things appropriately.
			if ( ! empty( array_intersect( $changed_keys, array( 'provider', 'access-key-id', 'secret-access-key', 'key-file', 'use-server-roles' ) ) ) && ! $this->get_defined_setting( 'bucket', false ) ) {
				$action = 'change-bucket';
			} elseif ( ! empty( array_intersect( $changed_keys, array( 'bucket', 'region' ) ) ) && $this->get_storage_provider()->block_public_access_allowed() ) {
				// Not initial setup, show change bucket access regardless if allowed.
				if ( ! empty( $_GET['orig_provider'] ) ) {
					$action = 'change-bucket-access';
				} else {
					// Is Block All Public Access enabled?
					try {
						$public_access_blocked = $this->get_provider_client()->public_access_blocked( $this->get_setting( 'bucket' ) );
					} catch ( Exception $e ) {
						$public_access_blocked = null;
					}

					if ( ! empty( $public_access_blocked ) && ! $this->get_delivery_provider()->use_signed_urls_key_file_allowed() ) {
						$action = 'change-bucket-access';
					}
				}
			} elseif ( in_array( 'delivery-provider', $changed_keys ) ) {
				// If delivery provider has just changed, there might be a masked change to enable-signed-urls.
				try {
					$this->set_delivery_provider();

					if ( $this->get_setting( 'enable-signed-urls', false ) && ! $this->get_delivery_provider()->use_signed_urls_key_file_allowed() ) {
						$changed_keys[] = 'enable-signed-urls';
						$this->set_setting( 'enable-signed-urls', false );
						$this->save_settings();
					}
				} catch ( Exception $e ) {
					// Meh, no biggie, the move tool can always be run manually.
					AS3CF_Error::log( sprintf( __( 'Could not set new Delivery Provider: %s', 'amazon-s3-and-cloudfront' ), $e->getMessage() ) );
				}
			}

			$action = empty( $action ) ? apply_filters( 'as3cf_action_for_changed_settings_key', $action, $changed_keys ) : $action;
		}

		// Stash which step we're on in possibly multi-step config.
		$prev_action = ! empty( $_GET['action'] ) ? $_GET['action'] : null;

		// Depending on the step we're on, we may need another step if not already determined by newly saved settings.
		if ( empty( $action ) && ! empty( $prev_action ) ) {
			if ( 'change-provider' === $prev_action && ! $this->get_defined_setting( 'bucket', false ) ) {
				// After change-provider we always want the user to confirm the bucket is still ok.
				// This gets round the change-provider => change-bucket => "back" problem.
				// but then no change in provider settings problem.
				$action = 'change-bucket';
			} elseif ( ! empty( $_GET['orig_provider'] ) && 'change-bucket' === $prev_action && $this->get_storage_provider()->block_public_access_allowed() ) {
				// If bucket didn't change, might want to update bucket access.
				$action = 'change-bucket-access';
			}
		}

		// If no action set yet, but there were changes earlier, let filter implementors have a look at them.
		if ( ! empty( $_GET['changed'] ) ) {
			$changed = (array) $_GET['changed'];
		}

		if ( empty( $action ) && ! empty( $changed ) ) {
			$action = null;

			$action = apply_filters( 'as3cf_action_for_changed_settings_key', $action, $changed );
		}

		if ( ! empty( $action ) ) {
			$url_args['action'] = $action;

			if ( ! empty( $prev_action ) ) {
				$url_args['prev_action'] = $prev_action;
			}

			if ( ! empty( $orig_provider ) ) {
				$url_args['orig_provider'] = $orig_provider;
			}

			if ( ! empty( $changed ) ) {
				$url_args['changed'] = $changed;
			}

			if ( ! empty( $changed_keys ) && is_array( $changed_keys ) ) {
				$changed             = empty( $url_args['changed'] ) ? array() : $url_args['changed'];
				$url_args['changed'] = array_merge( $changed, $changed_keys );
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
		if ( $this->get_defined_setting( 'bucket' ) ) {
			return array();
		}

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
		$region_required = 'create' === $bucket_mode ? true : $this->get_storage_provider()->region_required();
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
	 * Handle saving the block all public access preference to the bucket.
	 *
	 * @return array|bool
	 *
	 * There's no actual setting for this, the state of public access to the bucket is checked as required.
	 */
	private function handle_change_bucket_access() {
		// Whatever happens, refresh the Use Bucket ACLs setting afterwards.
		$this->get_settings();
		$this->remove_setting( 'use-bucket-acls' );
		$this->save_settings();

		if ( false === $this->get_storage_provider()->block_public_access_allowed() ) {
			$this->notices->add_notice(
				printf( _x( "Can't change Block All Public Access setting for %s buckets.", "Trying to change public access setting for given provider's bucket.", 'amazon-s3-and-cloudfront' ), $this->get_storage_provider()->get_provider_service_name() ),
				array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' )
			);

			return false;
		}

		if ( false === isset( $_POST['block-public-access'] ) ) {
			$this->notices->add_notice(
				__( 'No block public access setting provided.', 'amazon-s3-and-cloudfront' ),
				array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' )
			);

			return false;
		}

		$block_public_access = empty( $_POST['block-public-access'] ) ? false : true;

		$bucket = $this->get_setting( 'bucket' );

		if ( $this->get_storage_provider()->needs_access_keys() ) {
			$this->notices->add_notice(
				__( 'Storage Provider not configured with access credentials.', 'amazon-s3-and-cloudfront' ),
				array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' )
			);

			return false;
		}

		if ( empty( $bucket ) ) {
			$this->notices->add_notice(
				__( 'No bucket name provided.', 'amazon-s3-and-cloudfront' ),
				array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' )
			);

			return false;
		}

		try {
			$public_access_blocked = $this->get_provider_client()->public_access_blocked( $bucket );
		} catch ( Exception $e ) {
			$public_access_blocked = null;
		}

		if ( empty( $block_public_access ) !== empty( $public_access_blocked ) ) {
			try {
				$this->get_provider_client()->block_public_access( $bucket, $block_public_access );
			} catch ( Exception $e ) {
				$this->notices->add_notice(
					__( 'Could not change Block All Public Access status for bucket.', 'amazon-s3-and-cloudfront' ),
					array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' )
				);

				return false;
			}

			// The bucket level request may succeed, but account level overrides may negate the change or the change simply silently failed.
			// So check that all is as expected as we can't change the account level settings.
			try {
				$public_access_blocked = $this->get_provider_client()->public_access_blocked( $bucket );
			} catch ( Exception $e ) {
				$public_access_blocked = null;
			}

			if ( empty( $block_public_access ) !== empty( $public_access_blocked ) ) {
				if ( $block_public_access ) {
					$notice_message = __( '<strong>Failed to Enable Block All Public Access</strong> &mdash; We could not enable Block All Public Access. You will need to log in to the AWS Console and do it manually.', 'amazon-s3-and-cloudfront' );
				} else {
					$notice_message = __( '<strong>Failed to Disable Block All Public Access</strong> &mdash; We could not disable Block All Public Access. You will need to log in to the AWS Console and do it manually.', 'amazon-s3-and-cloudfront' );
				}
				$notice_message .= ' ' . $this->settings_more_info_link( 'bucket' );

				$this->notices->add_notice(
					$notice_message,
					array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' )
				);

				return false;
			}

			// No settings keys actually changed, but flag it anyway as status of bucket has changed.
			return array( 'use-bucket-acls' );
		}

		// No settings keys actually changed.
		return array();
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

			if ( 'access-key-id' === $var && 'db' !== $_POST['authmethod'] ) {
				continue;
			}

			if ( 'secret-access-key' === $var && 'db' !== $_POST['authmethod'] ) {
				continue;
			}

			if ( 'use-server-roles' === $var && 'server-role' !== $_POST['authmethod'] ) {
				continue;
			}

			if ( 'signed-urls-key-id' === $var && empty( $value ) && ! empty( $_POST['enable-signed-urls'] ) ) {
				$this->notices->add_notice(
					$this->get_delivery_provider()->signed_urls_key_id_name() . _x( ' not provided.', 'missing form field', 'amazon-s3-and-cloudfront' ),
					array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' )
				);

				return false;
			}

			if ( 'signed-urls-key-file-path' === $var && is_string( $value ) && ! empty( $value ) ) {
				// Can be a Windows path with backslashes, so need to undo what POST does to them.
				$value = stripslashes( $value );
			}

			if ( 'signed-urls-key-file-path' === $var && empty( $value ) && ! empty( $_POST['enable-signed-urls'] ) ) {
				$this->notices->add_notice(
					$this->get_delivery_provider()->signed_urls_key_file_path_name() . _x( ' not provided.', 'missing form field', 'amazon-s3-and-cloudfront' ),
					array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' )
				);

				return false;
			}

			if ( 'signed-urls-object-prefix' === $var && empty( $value ) && ! empty( $_POST['enable-signed-urls'] ) ) {
				$this->notices->add_notice(
					$this->get_delivery_provider()->signed_urls_object_prefix_name() . _x( ' not provided.', 'missing form field', 'amazon-s3-and-cloudfront' ),
					array( 'type' => 'error', 'only_show_in_settings' => true, 'only_show_on_tab' => 'media' )
				);

				return false;
			}

			$this->set_setting( $var, $value );

			// Some setting changes might have knock-on effects that require confirmation of secondary settings.
			if ( ( empty( $old_settings[ $var ] ) !== empty( $value ) ) || ( isset( $old_settings[ $var ] ) && $old_settings[ $var ] !== $value ) ) {
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
	 * @param bool   $can_use_yearmonth
	 *
	 * @return string
	 */
	public function get_dynamic_prefix( $time = null, $can_use_yearmonth = true ) {
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

		if ( $this->get_setting( 'use-yearmonth-folders' ) && $can_use_yearmonth ) {
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
			$blog_ids[] = (int) $blog['blog_id'];
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
	 * Make admin notice for when object ACL has changed
	 *
	 * @param Media_Library_Item $as3cf_item
	 * @param string|null        $size
	 */
	public function make_acl_admin_notice( Media_Library_Item $as3cf_item, $size = null ) {
		$filename = wp_basename( $as3cf_item->path( $size ) );
		$acl      = $as3cf_item->is_private( $size ) ? $this->get_storage_provider()->get_private_acl() : $this->get_storage_provider()->get_default_acl();
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
	 * Ensure items table(s) exists in the database
	 */
	private function check_for_items_table() {
		if ( ! $this->is_plugin_setup( true ) ) {
			// No notice until plugin is setup
			return;
		}

		if ( is_multisite() && ! is_network_admin() ) {
			return;
		}

		$missing_tables = $this->get_db_init_status( false );

		if ( count( $missing_tables ) !== 0 ) {
			$this->notices->add_notice(
				sprintf(
					__( '<strong>Missing Table</strong> &mdash; One or more required database tables are missing, please check the Diagnostic Info in the Support tab for details. %s', 'amazon-s3-and-cloudfront' ),
					$this->more_info_link(
						'/wp-offload-media/doc/missing-table-error-notice',
						'missing-table'
					)
				),
				array(
					'custom_id'             => 'items_table_error',
					'type'                  => 'error',
					'dismissible'           => false,
					'flash'                 => false,
					'only_show_to_user'     => false,
					'only_show_in_settings' => true,
				)
			);
		} else {
			$this->notices->remove_notice_by_id( 'items_table_error' );
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

		$media_counts = $this->media_counts();

		$output .= 'Media Files: ';
		$output .= number_format_i18n( $media_counts['total'] );
		$output .= "\r\n";

		$output .= 'Offloaded Media Files: ';
		$output .= number_format_i18n( $media_counts['offloaded'] );
		$output .= "\r\n";

		$output .= 'Not Offloaded Media Files: ';
		$output .= number_format_i18n( $media_counts['not_offloaded'] );
		$output .= "\r\n\r\n";

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

		$output .= 'OME Metadata Version: ';
		$output .= esc_html( $this->get_setting( 'post_meta_version' ) );
		$output .= "\r\n\r\n";

		/*
		 * Items db tables status
		 */

		$db_init_statuses = $this->get_db_init_status( true );
		$missing_tables   = $this->get_db_init_status( false );

		$output .= "Custom tables:\r\n";
		if ( count( $missing_tables ) === 0 ) {
			$output .= $db_init_statuses[1]['name'] . ': Ok';
			$output .= "\r\n";
		} else {
			// Output the first 5 missing tables
			$table_count = 0;
			foreach ( $missing_tables as $missing_table ) {
				$table_count++;
				if ( $table_count > 5 ) {
					break;
				}
				$output .= $missing_table['name'] . ': ';
				$output .= $missing_table['status'] ? 'Ok' : 'Missing';
				$output .= "\r\n";
			}
		}
		$output .= "\r\n";

		$storage_provider = $this->get_storage_provider();

		if ( empty( $storage_provider ) ) {
			$output .= 'Storage Provider: Not configured';
			$output .= "\r\n";
		} else {
			$output .= 'Storage Provider: ' . $storage_provider::get_provider_service_name();
			$output .= "\r\n";

			if ( $storage_provider::use_server_roles_allowed() ) {
				$output .= 'Use Server Roles: ';
				$output .= $storage_provider->use_server_roles() ? 'On' : 'Off';
			} else {
				$output .= 'Use Server Roles: N/A';
			}
			$output .= "\r\n";

			if ( $storage_provider::use_key_file_allowed() ) {
				$output .= 'Key File Path: ';
				$output .= empty( $storage_provider->get_key_file_path() ) ? 'None' : esc_html( $storage_provider->get_key_file_path() );
				$output .= "\r\n";
				$output .= 'Key File Path Define: ';
				$output .= $storage_provider::key_file_path_constant() ? $storage_provider::key_file_path_constant() : 'Not defined';
			} else {
				$output .= 'Key File Path: N/A';
			}
			$output .= "\r\n";

			if ( $storage_provider::use_access_keys_allowed() ) {
				$output .= 'Access Keys Set: ';
				$output .= $storage_provider->are_access_keys_set() ? 'Yes' : 'No';
				$output .= "\r\n";
				$output .= 'Access Key ID Define: ';
				$output .= $storage_provider::access_key_id_constant() ? $storage_provider::access_key_id_constant() : 'Not defined';
				$output .= "\r\n";
				$output .= 'Secret Access Key Define: ';
				$output .= $storage_provider::secret_access_key_constant() ? $storage_provider::secret_access_key_constant() : 'Not defined';
			} else {
				$output .= 'Access Keys Set: N/A';
			}
			$output .= "\r\n";
		}
		$output .= "\r\n";

		$bucket = $this->get_setting( 'bucket' );
		$output .= 'Bucket: ';
		$output .= empty( $bucket ) ? '(none)' : esc_html( $bucket );
		$output .= "\r\n";
		$value  = $this->get_setting( 'region' );
		$output .= 'Region: ';
		if ( is_wp_error( $value ) ) {
			$output .= '(error: "' . esc_html( $value->get_error_message() ) . '")';
		} elseif ( empty( $value ) ) {
			$output .= '(empty)';
		} else {
			$output .= esc_html( $value );
		}
		$output .= "\r\n";
		if (
			! empty( $storage_provider ) &&
			! empty( $bucket ) &&
			! $storage_provider->needs_access_keys() &&
			$storage_provider->block_public_access_allowed()
		) {
			try {
				$public_access_blocked = $this->get_provider_client()->public_access_blocked( $bucket );
			} catch ( Exception $e ) {
				$public_access_blocked = null;
			}
			$output .= 'Block All Public Access: ';
			if ( true === $public_access_blocked ) {
				$output .= 'Enabled';
			} elseif ( false === $public_access_blocked ) {
				$output .= 'Disabled';
			} else {
				$output .= 'Unknown';
			}
			$output .= "\r\n";
		}
		$output .= "\r\n";

		$output .= 'Copy Files to Bucket: ';
		$output .= $this->on_off( 'copy-to-s3' );
		$output .= "\r\n";
		$output .= 'Enable Path: ';
		$output .= $this->on_off( 'enable-object-prefix' );
		$output .= "\r\n";
		$value  = $this->get_setting( 'object-prefix' );
		$output .= 'Custom Path: ';
		$output .= empty( $value ) ? '(none)' : esc_html( $value );
		$output .= "\r\n";
		$output .= 'Use Year/Month: ';
		$output .= $this->on_off( 'use-yearmonth-folders' );
		$output .= "\r\n";
		$output .= 'Object Versioning: ';
		$output .= $this->on_off( 'object-versioning' );
		$output .= "\r\n";
		$output .= "\r\n";

		$delivery_provider = $this->get_delivery_provider();

		if ( empty( $delivery_provider ) ) {
			$output .= 'Delivery Provider: Not configured';
			$output .= "\r\n";
		} else {
			$output .= 'Delivery Provider: ' . $delivery_provider::get_provider_service_name();
			$output .= "\r\n";
			$output .= 'Rewrite Media URLs: ';
			$output .= $this->on_off( 'serve-from-s3' );
			$output .= "\r\n";

			if ( $delivery_provider::delivery_domain_allowed() ) {
				$output .= 'Enable Custom Domain (CNAME): ';
				$output .= $this->on_off( 'enable-delivery-domain' );
				$output .= "\r\n";
				$value  = $this->get_setting( 'delivery-domain' );
				$output .= 'Custom Domain (CNAME): ';
				$output .= empty( $value ) ? '(none)' : esc_html( $value );
				$output .= "\r\n";
			}

			if ( $delivery_provider::use_signed_urls_key_file_allowed() ) {
				$output .= 'Enable Signed URLs: ';
				$output .= $this->on_off( 'enable-signed-urls' );
				$output .= "\r\n";
				$output .= 'Signed URLs Key ID Set: ';
				$output .= $delivery_provider->get_signed_urls_key_id() ? 'Yes' : 'No';
				$output .= "\r\n";
				$value  = $this->get_setting( 'signed-urls-key-file-path' );
				$output .= 'Signed URLs Key File Path: ';
				$output .= empty( $value ) ? '(none)' : esc_html( $value );
				$output .= "\r\n";
				$value  = $this->get_setting( 'signed-urls-object-prefix' );
				$output .= 'Signed URLs Private Prefix: ';
				$output .= empty( $value ) ? '(none)' : esc_html( $value );
				$output .= "\r\n";
			}
			$output .= "\r\n";
		}

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
		$acl = empty( $acl ) ? 'default' : $acl;
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
		if ( $this->get_storage_provider()->needs_access_keys() ) {
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
	 * @param bool $space_used
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

		return wp_convert_hr_to_bytes( $memory_limit );
	}

	/**
	 * Get the total attachment and total offloaded/not offloaded attachment counts
	 *
	 * @param bool $skip_transient Whether to force database query and skip transient, default false
	 * @param bool $force          Whether to force database query and skip static cache, implies $skip_transient, default false
	 *
	 * @return array
	 */
	public function media_counts( $skip_transient = false, $force = false ) {
		if ( $skip_transient || false === ( $attachment_counts = get_site_transient( 'as3cf_attachment_counts' ) ) ) {
			$table_prefixes = $this->get_all_blog_table_prefixes();
			$total          = 0;
			$offloaded      = 0;
			$not_offloaded  = 0;

			foreach ( $table_prefixes as $blog_id => $table_prefix ) {
				$this->switch_to_blog( $blog_id );

				foreach ( $this->get_source_type_classes() as $class ) {
					$counts        = $class::count_items( $skip_transient, $force );
					$total         += $counts['total'];
					$offloaded     += $counts['offloaded'];
					$not_offloaded += $counts['not_offloaded'];
				}

				$this->restore_current_blog();
			}

			$attachment_counts = array(
				'total'         => $total,
				'offloaded'     => $offloaded,
				'not_offloaded' => $not_offloaded,
			);

			set_site_transient( 'as3cf_attachment_counts', $attachment_counts, 5 * MINUTE_IN_SECONDS );
		}

		return $attachment_counts;
	}

	/**
	 * Check the existence of the items table (as3cf_items). Returns an array with one row per
	 * possible database prefix (multisite support).
	 *
	 * @param bool $all            Return all tables or just missing tables. Defaults to all/true.
	 * @param bool $skip_transient Whether to force database query and skip transient, default false.
	 *
	 * @return array
	 */
	private function get_db_init_status( $all = true, $skip_transient = false ) {
		global $wpdb;

		if ( $skip_transient || false === ( $db_init_status = get_site_transient( 'as3cf_db_init_status' ) ) ) {
			$table_prefixes = $this->get_all_blog_table_prefixes();

			$db_init_status = array();

			foreach ( $table_prefixes as $blog_id => $table_prefix ) {
				$table_name = $table_prefix . Item::ITEMS_TABLE;

				$db_init_status[ $blog_id ] = array(
					'name'   => $table_name,
					'status' => false,
				);

				if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) ) === $table_name ) {
					$db_init_status[ $blog_id ]['status'] = true;
				}
			}

			set_site_transient( 'as3cf_db_init_status', $db_init_status, 5 * MINUTE_IN_SECONDS );
		}

		if ( ! $all ) {
			$db_init_status = array_filter( $db_init_status, function ( $table ) {
				return false === $table['status'];
			} );
		}

		return $db_init_status;
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
	 * @param string $path        Relative path on DBI site
	 * @param string $utm_content Optional utm_content value.
	 * @param string $hash        Optional hash anchor value without the '#'.
	 * @param string $text        Optional override of link text.
	 * @param string $prefix      Optional non-linked prefix text.
	 * @param string $suffix      Optional non-linked suffix text.
	 *
	 * @return string
	 */
	public function more_info_link( $path, $utm_content = '', $hash = '', $text = '', $prefix = '', $suffix = '' ) {
		$args = array(
			'utm_campaign' => 'support+docs',
		);

		if ( ! empty( $utm_content ) ) {
			$args['utm_content'] = $utm_content;
		}

		$text   = empty( $text ) ? __( 'More&nbsp;info&nbsp;&raquo;', 'amazon-s3-and-cloudfront' ) : $text;
		$prefix = empty( $prefix ) ? '' : $prefix;
		$suffix = empty( $suffix ) ? '' : $suffix;

		$url  = $this->dbrains_url( $path, $args, $hash );
		$link = AS3CF_Utils::dbrains_link( $url, $text );

		return sprintf( '<span class="more-info">%s%s%s</span>', $prefix, $link, $suffix );
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
	 * Potentially update path for delivery URLs.
	 *
	 * @param string   $path      Path in bucket to be used in URL.
	 * @param string   $domain    Domain to be used in URL.
	 * @param int|null $timestamp Optional time that signed URL expires.
	 *
	 * @return string
	 *
	 * Note: This is a wrapper for a filter, which only fires default (storage) delivery provider is not in use.
	 */
	public function maybe_update_delivery_path( $path, $domain, $timestamp = null ) {
		if ( static::get_default_delivery_provider() !== $this->get_delivery_provider()->get_provider_key_name() ) {
			$path_parts = apply_filters( 'as3cf_cloudfront_path_parts', explode( '/', $path ), $domain, $timestamp ); // Backwards compatibility.
			$path_parts = apply_filters( 'as3cf_delivery_domain_path_parts', $path_parts, $domain, $timestamp );

			if ( ! empty( $path_parts ) ) {
				$path = implode( '/', $path_parts );
			}
		}

		return $path;
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
	 * Ensure local URL is correct for multisite's non-primary subsites.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public function maybe_fix_local_subsite_url( $url ) {
		$siteurl = trailingslashit( get_option( 'siteurl' ) );

		if ( is_multisite() && ! $this->is_current_blog( get_current_blog_id() ) && 0 !== strpos( $url, $siteurl ) ) {
			// Replace original URL with subsite's current URL.
			$orig_siteurl = trailingslashit( apply_filters( 'as3cf_get_orig_siteurl', network_site_url() ) );
			$url          = str_replace( $orig_siteurl, $siteurl, $url );
		}

		return $url;
	}

	/**
	 * Get ACL for intermediate size.
	 *
	 * @param int                     $attachment_id
	 * @param string                  $size
	 * @param string                  $bucket     Optional bucket that ACL is potentially to be used with.
	 * @param Media_Library_Item|null $as3cf_item Optional item.
	 *
	 * @return string|null
	 */
	public function get_acl_for_intermediate_size( $attachment_id, $size, $bucket = null, Media_Library_Item $as3cf_item = null ) {
		if ( empty( $as3cf_item ) ) {
			$as3cf_item = Media_Library_Item::get_by_source_id( $attachment_id );
		}

		$acl = null;

		if ( $this->use_acl_for_intermediate_size( $attachment_id, $size, $bucket, $as3cf_item ) ) {
			$acl = $this->get_storage_provider()->get_default_acl();

			if ( ! empty( $as3cf_item ) ) {
				$acl = $as3cf_item->is_private( $size ) ? $this->get_storage_provider()->get_private_acl() : $this->get_storage_provider()->get_default_acl();
			}
		}

		return $acl;
	}

	/**
	 * Are ACLs in use for intermediate size on bucket?
	 *
	 * @param int       $attachment_id
	 * @param string    $size
	 * @param string    $bucket     Optional bucket that ACL is potentially to be used with.
	 * @param Item|null $as3cf_item Optional item.
	 *
	 * @return bool
	 */
	public function use_acl_for_intermediate_size( $attachment_id, $size, $bucket = null, Item $as3cf_item = null ) {
		// If this function is used without passing in an Item object, we're assuming $attachment
		if ( empty( $as3cf_item ) ) {
			$as3cf_item = Media_Library_Item::get_by_source_id( $attachment_id );
		}

		if ( empty( $bucket ) ) {
			$bucket = empty( $as3cf_item ) ? null : $as3cf_item->bucket();
		}

		if ( empty( $bucket ) ) {
			$bucket = $this->get_setting( 'bucket', null );
		}

		$use_acl            = apply_filters( 'as3cf_use_bucket_acls_for_intermediate_size', $this->get_setting( 'use-bucket-acls', true ), $attachment_id, $size, $bucket, $as3cf_item );
		$use_private_prefix = apply_filters( 'as3cf_enable_signed_urls_for_intermediate_size', $this->private_prefix_enabled(), $attachment_id, $size, $bucket, $as3cf_item );

		// If signed custom URLs are in play, and we have a private object, usually you can not use ACLs.
		if ( $use_acl && $use_private_prefix && ! empty( $as3cf_item ) && $as3cf_item->is_private( $size ) ) {
			$use_acl = false;
		}

		// Allow complete override if signed custom URLs and ACLs do play nice together some how, or other factors in play.
		return apply_filters( 'as3cf_use_acl_for_intermediate_size', $use_acl, $attachment_id, $size, $bucket, $as3cf_item );
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

			if ( is_a( $this->get_storage_provider(), '\DeliciousBrains\WP_Offload_Media\Providers\Storage\AWS_Provider' ) && $this->get_storage_provider()->needs_access_keys() ) {
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
	 * Is there an upgrade in progress?
	 *
	 * @return bool
	 */
	public function is_upgrading() {
		return Upgrade::is_locked();
	}

	/**
	 * Do current settings allow for private prefix to be used?
	 *
	 * @return bool
	 */
	public function private_prefix_enabled() {
		if (
			$this->get_setting( 'enable-delivery-domain', false ) &&
			$this->get_setting( 'enable-signed-urls', false ) &&
			! empty( $this->get_setting( 'signed-urls-object-prefix' ) )
		) {
			return true;
		}

		return false;
	}

	/**
	 * Register an item type name and class
	 *
	 * @param string $source_type
	 * @param string $class
	 */
	public function register_source_type( $source_type, $class ) {
		$this->source_type_classes[ $source_type ] = $class;
	}

	/**
	 * Get Item type class from item type identifier
	 *
	 * @param string $source_type
	 *
	 * @return string|false
	 */
	public function get_source_type_class( $source_type ) {
		if ( isset( $this->source_type_classes[ $source_type ] ) ) {
			return $this->source_type_classes[ $source_type ];
		}

		return false;
	}

	/**
	 * Get Item type human friendly name item type identifier
	 *
	 * @param string $source_type
	 *
	 * @return string|false
	 */
	public function get_source_type_name( $source_type ) {
		/** @var Item $class */
		$class = $this->get_source_type_class( $source_type );
		if ( ! empty( $class ) ) {
			return $class::source_type_name();
		}

		return false;
	}

	/**
	 * Get all registered Item classes
	 *
	 * @return array
	 */
	public function get_source_type_classes() {
		return $this->source_type_classes;
	}

	/**
	 * Returns the Item_Handler instance for the given handler type.
	 *
	 * @param string $handler_type
	 *
	 * @return Item_Handler
	 */
	public function get_item_handler( $handler_type ) {
		if ( isset( $this->item_handlers[ $handler_type ] ) ) {
			return $this->item_handlers[ $handler_type ];
		}

		switch ( $handler_type ) {
			case Upload_Handler::get_item_handler_key_name():
				$this->item_handlers[ $handler_type ] = new Upload_Handler( $this );
				break;
			case Download_Handler::get_item_handler_key_name():
				$this->item_handlers[ $handler_type ] = new Download_Handler( $this );
				break;
			case Remove_Local_Handler::get_item_handler_key_name():
				$this->item_handlers[ $handler_type ] = new Remove_Local_Handler( $this );
				break;
			case Remove_Provider_Handler::get_item_handler_key_name():
				$this->item_handlers[ $handler_type ] = new Remove_Provider_Handler( $this );
				break;
			default:
				return null;
		}

		return $this->item_handlers[ $handler_type ];
	}
}
