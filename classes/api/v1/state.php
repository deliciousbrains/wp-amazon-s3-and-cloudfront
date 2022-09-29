<?php

namespace DeliciousBrains\WP_Offload_Media\API\V1;

use DeliciousBrains\WP_Offload_Media\API\API;
use WP_REST_Request;
use WP_REST_Response;

class State extends API {
	/** @var int */
	protected static $version = 1;

	/** @var string */
	protected static $name = 'state';

	/**
	 * Register REST API routes.
	 */
	public function register_routes() {
		register_rest_route(
			static::api_namespace(),
			static::route(),
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_state' ),
				'permission_callback' => array( $this, 'check_permissions' ),
			)
		);
	}

	/**
	 * Processes a REST GET request and returns the current state.
	 *
	 * @param WP_REST_Request $request
	 *
	 * @return WP_REST_Response|mixed
	 */
	public function get_state( WP_REST_Request $request ) {
		return $this->rest_ensure_response(
			'get',
			static::name(),
			array_merge(
				$this->endpoint_common_response( Settings::name() ),
				array(
					'counts'   => $this->as3cf->media_counts(),
					'upgrades' => $this->as3cf->get_upgrades_info(),
				)
			)
		);
	}
}
