<?php

namespace DeliciousBrains\WP_Offload_Media\Integrations;

use Amazon_S3_And_CloudFront;

abstract class Integration {

	/**
	 * @var Amazon_S3_And_CloudFront
	 */
	protected $as3cf;

	/**
	 * Integration constructor.
	 *
	 * @param Amazon_S3_And_CloudFront $as3cf
	 */
	public function __construct( $as3cf ) {
		$this->as3cf = $as3cf;
	}

	/**
	 * Is installed?
	 *
	 * @return bool
	 */
	public static function is_installed() {
		return false;
	}

	/**
	 * Init integration.
	 */
	abstract public function init();

}