<?php

namespace DeliciousBrains\WP_Offload_S3;

use AS3CF_Utils;
use DeliciousBrains\WP_Offload_S3\Aws2\Aws\Common\Aws;
use Exception;

class Amazon_Web_Services extends \AS3CF_Plugin_Base {

	/**
	 * @var
	 */
	private $client;

	protected $plugin_slug = 'amazon-s3-and-cloudfront';

	const SETTINGS_KEY = \Amazon_S3_And_CloudFront::SETTINGS_KEY;

	/**
	 * Whether or not IAM access keys are needed.
	 *
	 * Keys are needed if we are not using EC2 roles or not defined/set yet.
	 *
	 * @return bool
	 */
	public function needs_access_keys() {
		if ( $this->use_ec2_iam_roles() ) {
			return false;
		}

		return ! $this->are_access_keys_set();
	}

	/**
	 * Check if both access key id & secret are present.
	 *
	 * @return bool
	 */
	function are_access_keys_set() {
		return $this->get_access_key_id() && $this->get_secret_access_key();
	}

	/**
	 * Get the AWS key from a constant or the settings.
	 *
	 * Falls back to settings only if neither constant is defined.
	 *
	 * @return string
	 */
	public function get_access_key_id() {
		if ( $this->is_any_access_key_constant_defined() ) {
			$constant = $this->access_key_id_constant();

			return $constant ? constant( $constant ) : '';
		}

		return $this->get_setting( 'aws-access-key-id' );
	}

	/**
	 * Get the AWS secret from a constant or the settings
	 *
	 * Falls back to settings only if neither constant is defined.
	 *
	 * @return string
	 */
	public function get_secret_access_key() {
		if ( $this->is_any_access_key_constant_defined() ) {
			$constant = $this->secret_access_key_constant();

			return $constant ? constant( $constant ) : '';
		}

		return $this->get_setting( 'aws-secret-access-key' );
	}

	/**
	 * Check if any access key (id or secret, prefixed or not) is defined.
	 *
	 * @return bool
	 */
	public static function is_any_access_key_constant_defined() {
		return static::access_key_id_constant() || static::secret_access_key_constant();
	}

	/**
	 * Allows the AWS client factory to use the IAM role for EC2 instances
	 * instead of key/secret for credentials
	 * http://docs.aws.amazon.com/aws-sdk-php/guide/latest/credentials.html#instance-profile-credentials
	 *
	 * @return bool
	 */
	public function use_ec2_iam_roles() {
		$constant = $this->use_ec2_iam_role_constant();

		return $constant && constant( $constant );
	}

	/**
	 * Get the constant used to define the aws access key id.
	 *
	 * @return string|false Constant name if defined, otherwise false
	 */
	public static function access_key_id_constant() {
		return AS3CF_Utils::get_first_defined_constant( array(
			'AS3CF_AWS_ACCESS_KEY_ID',
			'DBI_AWS_ACCESS_KEY_ID',
			'AWS_ACCESS_KEY_ID',
		) );
	}

	/**
	 * Get the constant used to define the aws secret access key.
	 *
	 * @return string|false Constant name if defined, otherwise false
	 */
	public static function secret_access_key_constant() {
		return AS3CF_Utils::get_first_defined_constant( array(
			'AS3CF_AWS_SECRET_ACCESS_KEY',
			'DBI_AWS_SECRET_ACCESS_KEY',
			'AWS_SECRET_ACCESS_KEY',
		) );
	}

	/**
	 * Get the constant used to enable the use of EC2 IAM roles.
	 *
	 * @return string|false Constant name if defined, otherwise false
	 */
	public static function use_ec2_iam_role_constant() {
		return AS3CF_Utils::get_first_defined_constant( array(
			'AS3CF_AWS_USE_EC2_IAM_ROLE',
			'DBI_AWS_USE_EC2_IAM_ROLE',
			'AWS_USE_EC2_IAM_ROLE',
		) );
	}

	/**
	 * Instantiate a new AWS service client for the AWS SDK
	 * using the defined AWS key and secret
	 *
	 * @return Aws
	 * @throws Exception
	 */
	function get_client() {
		if ( $this->needs_access_keys() ) {
			throw new Exception( sprintf( __( 'You must first <a href="%s">set your AWS access keys</a> to use this addon.', 'amazon-s3-and-cloudfront' ), $this->get_plugin_page_url() . '#settings' ) );
		}

		if ( is_null( $this->client ) ) {
			$args = array();

			if ( ! $this->use_ec2_iam_roles() ) {
				$args = array(
					'key'    => $this->get_access_key_id(),
					'secret' => $this->get_secret_access_key(),
				);
			}

			$args         = apply_filters( 'aws_get_client_args', $args );
			$this->client = Aws::factory( $args );
		}

		return $this->client;
	}
}
