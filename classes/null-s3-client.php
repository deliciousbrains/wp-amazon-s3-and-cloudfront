<?php

namespace DeliciousBrains\WP_Offload_S3;

use AS3CF_Error;

class Null_S3_Client {

	/**
	 * Log and fail calls to instance methods.
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @throws \Exception
	 */
	public function __call( $name, $arguments ) {
		AS3CF_Error::log( $arguments, __CLASS__ . "->$name()" );
		throw new \Exception( 'Failed to instantiate the AWS S3 client. Check your error log.' );
	}

	/**
	 * Log and fail calls to static methods.
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @throws \Exception
	 */
	public static function __callStatic( $name, $arguments ) {
		AS3CF_Error::log( $arguments, __CLASS__ . "::$name()" );
		throw new \Exception( 'Failed to instantiate the AWS S3 client. Check your error log.' );
	}
}
