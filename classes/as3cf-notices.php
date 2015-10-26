<?php

class AS3CF_Notices {

	/**
	 * @var AS3CF_Notices
	 */
	protected static $instance = null;

	/**
	 * @var Amazon_S3_And_CloudFront
	 */
	private $as3cf;

	/**
	 * @var string
	 */
	private $plugin_file_path;

	/**
	 * Make this class a singleton
	 *
	 * Use this instead of __construct()
	 *
	 * @param Amazon_S3_And_CloudFront $as3cf
	 * @param string                   $plugin_file_path
	 *
	 * @return AS3CF_Notices
	 */
	public static function get_instance( $as3cf, $plugin_file_path ) {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static( $as3cf, $plugin_file_path );
		}

		return static::$instance;
	}

	/**
	 * Constructor
	 *
	 * @param Amazon_S3_And_CloudFront $as3cf
	 * @param string                   $plugin_file_path
	 */
	protected function __construct( $as3cf, $plugin_file_path ) {
		$this->as3cf            = $as3cf;
		$this->plugin_file_path = $plugin_file_path;

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'network_admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_notice_scripts' ) );
		add_action( 'wp_ajax_as3cf-dismiss-notice', array( $this, 'ajax_dismiss_notice' ) );
	}

	/**
	 * As this class is a singelton it should not be clone-able
	 */
	protected function __clone() {
		// Singleton
	}

	/**
	 * Create a notice
	 *
	 * @param string $message
	 * @param array  $args
	 *
	 * @return string notice ID
	 */
	public function add_notice( $message, $args = array() ) {
		$defaults = array(
			'type'                  => 'info',
			'dismissible'           => true,
			'inline'                => false,
			'flash'                 => true,
			'only_show_to_user'     => true,
			'only_show_in_settings' => false,
			'custom_id'             => '',
		);

		$notice                 = array_intersect_key( array_merge( $defaults, $args ), $defaults );
		$notice['message']      = $message;
		$notice['triggered_by'] = get_current_user_id();
		$notice['created_at']   = time();

		if ( $notice['custom_id'] ) {
			$notice['id'] = $notice['custom_id'];
		} else {
			$notice['id'] = apply_filters( 'as3cf_notice_id_prefix', 'as3cf-notice-' ) . sha1( $notice['message'] );
		}

		$this->save_notice( $notice );

		return $notice['id'];
	}

	/**
	 * Save a notice
	 *
	 * @param array $notice
	 */
	protected function save_notice( $notice ) {
		$user_id = get_current_user_id();

		if ( $notice['only_show_to_user'] ) {
			$notices = get_user_meta( $user_id, 'as3cf_notices', true );
		} else {
			$notices = get_site_transient( 'as3cf_notices' );
		}

		if ( ! is_array( $notices ) ) {
			$notices = array();
		}

		if ( ! array_key_exists( $notice['id'], $notices ) ) {
			$notices[ $notice['id'] ] = $notice;

			if ( $notice['only_show_to_user'] ) {
				update_user_meta( $user_id, 'as3cf_notices', $notices );
			} else {
				set_site_transient( 'as3cf_notices', $notices );
			}
		}
	}

	/**
	 * Remove a notice
	 *
	 * @param array $notice
	 */
	protected function remove_notice( $notice ) {
		$user_id = get_current_user_id();

		if ( $notice['only_show_to_user'] ) {
			$notices = get_user_meta( $user_id, 'as3cf_notices', true );
		} else {
			$notices = get_site_transient( 'as3cf_notices' );
		}

		if ( ! is_array( $notices ) ) {
			$notices = array();
		}

		if ( array_key_exists( $notice['id'], $notices ) ) {
			unset( $notices[ $notice['id'] ] );

			if ( $notice['only_show_to_user'] ) {
				if ( ! empty( $notices ) ) {
					update_user_meta( $user_id, 'as3cf_notices', $notices );
				} else {
					delete_user_meta( $user_id, 'as3cf_notices' );
				}
			} else {
				if ( ! empty( $notices ) ) {
					set_site_transient( 'as3cf_notices', $notices );
				} else {
					delete_site_transient( 'as3cf_notices' );
				}
			}
		}
	}

	/**
	 * Remove a notice by it's ID
	 *
	 * @param string $notice_id
	 */
	public function remove_notice_by_id( $notice_id ) {
		$notice = $this->find_notice_by_id( $notice_id );
		if ( $notice ) {
			$this->remove_notice( $notice );
		}
	}

	/**
	 * Dismiss a notice
	 *
	 * @param string $notice_id
	 */
	protected function dismiss_notice( $notice_id ) {
		$user_id = get_current_user_id();

		$notice = $this->find_notice_by_id( $notice_id );
		if ( $notice ) {
			if ( $notice['only_show_to_user'] ) {
				if ( ! empty( $notices ) ) {
					unset( $notices[ $notice['id'] ] );
					update_user_meta( $user_id, 'as3cf_notices', $notices );
				} else {
					delete_user_meta( $user_id, 'as3cf_notices' );
				}
			} else {
				$dismissed_notices = get_user_meta( $user_id, 'as3cf_dismissed_notices', true );
				if ( ! is_array( $dismissed_notices ) ) {
					$dismissed_notices = array();
				}

				if ( ! in_array( $notice['id'], $dismissed_notices ) ) {
					$dismissed_notices[] = $notice['id'];
					update_user_meta( $user_id, 'as3cf_dismissed_notices', $dismissed_notices );
				}
			}
		}
	}

	/**
	 * Find a notice by it's ID
	 *
	 * @param string $notice_id
	 *
	 * @return mixed
	 */
	protected function find_notice_by_id( $notice_id ) {
		$user_id = get_current_user_id();

		$user_notices = get_user_meta( $user_id, 'as3cf_notices', true );
		if ( ! is_array( $user_notices ) ) {
			$user_notices = array();
		}

		$global_notices = get_site_transient( 'as3cf_notices' );
		if ( ! is_array( $global_notices ) ) {
			$global_notices = array();
		}
		$notices = array_merge( $user_notices, $global_notices );

		if ( array_key_exists( $notice_id, $notices ) ) {
			return $notices[ $notice_id ];
		}

		return null;
	}

	/**
	 * Show the notices
	 */
	public function admin_notices() {
		$user_id           = get_current_user_id();
		$dismissed_notices = get_user_meta( $user_id, 'as3cf_dismissed_notices', true );
		if ( ! is_array( $dismissed_notices ) ) {
			$dismissed_notices = array();
		}

		$user_notices = get_user_meta( $user_id, 'as3cf_notices', true );
		if ( is_array( $user_notices ) && ! empty( $user_notices ) ) {
			foreach ( $user_notices as $notice ) {
				$this->maybe_show_notice( $notice, $dismissed_notices );
			}
		}

		$global_notices = get_site_transient( 'as3cf_notices' );
		if ( is_array( $global_notices ) && ! empty( $global_notices ) ) {
			foreach ( $global_notices as $notice ) {
				$this->maybe_show_notice( $notice, $dismissed_notices );
			}
		}
	}

	/**
	 * If it should be shown, display an individual notice
	 *
	 * @param array $notice
	 */
	protected function maybe_show_notice( $notice, $dismissed_notices ) {
		$screen = get_current_screen();
		if ( $notice['only_show_in_settings'] && false === strpos( $screen->id, $this->as3cf->hook_suffix ) ) {
			return;
		}

		if ( ! $notice['only_show_to_user'] && in_array( $notice['id'], $dismissed_notices ) ) {
			return;
		}

		if ( 'info' === $notice['type'] ) {
			$notice['type'] = 'notice-info';
		}

		$this->as3cf->render_view( 'notice', $notice );

		if ( $notice['flash'] ) {
			$this->remove_notice( $notice );
		}
	}

	/**
	 * Enqueue notice scripts in the admin
	 */
	public function enqueue_notice_scripts() {
		$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : $GLOBALS['aws_meta']['amazon-s3-and-cloudfront']['version'];
		$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// Enqueue notice.js globally as notices can be dismissed on any admin page
		$src = plugins_url( 'assets/js/notice' . $suffix . '.js', $this->plugin_file_path );
		wp_enqueue_script( 'as3cf-notice', $src, array( 'jquery' ), $version, true );

		wp_localize_script( 'as3cf-notice', 'as3cf_notice', array(
			'strings' => array(
				'dismiss_notice_error' => __( 'Error dismissing notice.', 'amazon-s3-and-cloudfront' ),
			),
			'nonces'  => array(
				'dismiss_notice' => wp_create_nonce( 'as3cf-dismiss-notice' ),
			),
		) );
	}

	/**
	 * Handler for AJAX request to dismiss a notice
	 */
	public function ajax_dismiss_notice() {
		$this->as3cf->verify_ajax_request();

		if ( ! isset( $_POST['notice_id'] ) || ! ( $notice_id = sanitize_text_field( $_POST['notice_id'] ) ) ) {
			$out = array( 'error' => __( 'Invalid notice ID.', 'amazon-s3-and-cloudfront' ) );
			$this->as3cf->end_ajax( $out );
		}

		$this->dismiss_notice( $notice_id );

		$out = array(
			'success' => '1',
		);
		$this->as3cf->end_ajax( $out );
	}

}