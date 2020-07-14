<?php
/* @var \Amazon_S3_And_CloudFront|\Amazon_S3_And_CloudFront_Pro $this */
/** @var $selected_provider string */

$needs_keys      = $this->get_storage_provider()->needs_access_keys();
$constant_bucket = strtoupper( str_replace( '-', '_', $prefix ) . '_BUCKET' );
$tr_class        = ( isset( $tr_class ) ) ? $tr_class : '';

// Can't do anything with a WP_Error.
if ( is_wp_error( $selected_region ) ) {
	$selected_region = null;
}
?>

<tr class="as3cf-bucket <?php echo $tr_class; ?>">
	<td><h4><?php _e( 'Bucket:', 'amazon-s3-and-cloudfront' ); ?></h4></td>
	<td>
		<span id="<?php echo $prefix; ?>-active-bucket" class="as3cf-active-bucket">
			<?php echo $selected_bucket; // xss ok ?>
		</span>
		<a id="<?php echo $prefix; ?>-view-bucket" target="_blank" class="as3cf-view-bucket" href="<?php echo $this->get_storage_provider()->get_console_url( $selected_bucket, $selected_bucket_prefix, $selected_region ); ?>" title="<?php _e( 'View in provider\'s console', 'amazon-s3-and-cloudfront' ); ?>">
			<span class="dashicons dashicons-external"></span>
		</a>
		<?php if ( ! $needs_keys ) { ?>
			<a href="<?php echo $this->get_plugin_page_url( array( 'action' => 'change-bucket', 'orig_provider' => $selected_provider ) ); ?>" id="<?php echo $prefix; ?>-change-bucket" class="as3cf-change-settings as3cf-change-bucket"><?php _e( 'Change', 'amazon-s3-and-cloudfront' ); ?></a>
		<?php } ?>

		<p id="<?php echo $prefix; ?>-active-region" class="as3cf-active-region" title="<?php _e( 'The region that the bucket is in.', 'amazon-s3-and-cloudfront' ); ?>">
			<?php
			try {
				$region_name = $this->get_storage_provider()->get_region_name( $selected_region );
			} catch ( Exception $e ) {
				$region_name = __( 'Unknown Region', 'amazon-s3-and-cloudfront' );
			}
			echo $region_name; // xss ok
			?>
		</p>

		<?php
		if (
			$this->get_storage_provider()->block_public_access_allowed() &&
			is_subclass_of( $this->get_provider_client(), 'DeliciousBrains\WP_Offload_Media\Providers\Storage\Storage_Provider')
		) {

			try {
				$public_access_blocked = $this->get_provider_client()->public_access_blocked( $selected_bucket );
			} catch ( Exception $e ) {
				$public_access_blocked = null;
			}

			$public_access_blocked_text  = '';
			$public_access_blocked_title = '';
			if ( true === $public_access_blocked ) {
				$public_access_blocked_text  = __( 'Block All Public Access Enabled', 'amazon-s3-and-cloudfront' );
				$public_access_blocked_title = __( 'Public access to bucket has been blocked at either account or bucket level.', 'amazon-s3-and-cloudfront' );
			} elseif ( false === $public_access_blocked ) {
				$public_access_blocked_text  = __( 'Block All Public Access Disabled', 'amazon-s3-and-cloudfront' );
				$public_access_blocked_title = __( 'Public access to bucket has not been blocked at either account or bucket level.', 'amazon-s3-and-cloudfront' );
			} else {
				$public_access_blocked_text  = __( 'Block All Public Access Status Unknown', 'amazon-s3-and-cloudfront' );
				$public_access_blocked_title = __( 'Public access to bucket status unknown, please grant IAM User the s3:GetBucketPublicAccessBlock permission.', 'amazon-s3-and-cloudfront' );
			}
			?>
			<p id="<?php echo $prefix; ?>-block-public-access" class="as3cf-block-public-access" title="<?php echo $public_access_blocked_title; ?>">
				<?php
				echo $public_access_blocked_text . ' ' . $this->settings_more_info_link( 'bucket', 'media+block+public+access' );
				?>
			</p>
			<?php
			if ( true === $public_access_blocked && ! $this->get_delivery_provider()->use_signed_urls_key_file_allowed() ) {
				$cloudfront_setup_doc = $this->dbrains_url(
					'/wp-offload-media/doc/cloudfront-setup/',
					array( 'utm_campaign' => 'support+docs', 'utm_content' => 'change+bucket+access' )
				);
				$bucket_settings_doc  = $this->dbrains_url(
					'/wp-offload-media/doc/settings/',
					array( 'utm_campaign' => 'support+docs', 'utm_content' => 'change+bucket+access' ),
					'bucket'
				);

				$bucket_access_blocked_message = sprintf(
					__( '<strong>Block All Public Access is Enabled</strong> &mdash; If you\'re following <a href="%1$s">our documentation on setting up Amazon CloudFront for delivery</a>, you can ignore this warning and continue. If you\'re not planning on using Amazon CloudFront for delivery, you need to <a href="%2$s">disable Block All Public Access</a>.', 'amazon-s3-and-cloudfront' ),
					$cloudfront_setup_doc,
					$this->get_plugin_page_url( array( 'action' => 'change-bucket-access', 'orig_provider' => $selected_provider ) )
				);
				$bucket_access_blocked_notice  = array(
					'message' => $bucket_access_blocked_message,
					'id'      => 'as3cf-bucket-access-blocked',
					'inline'  => true,
					'type'    => 'notice-warning',
				);
				$this->render_view( 'notice', $bucket_access_blocked_notice );
			}
		}
		?>

		<input id="<?php echo $prefix; ?>-bucket" type="hidden" class="no-compare" name="bucket" value="<?php echo esc_attr( $selected_bucket ); ?>">
		<input id="<?php echo $prefix; ?>-region" type="hidden" class="no-compare" name="region" value="<?php echo esc_attr( $selected_region ); ?>">

		<?php
		$region = $this->get_setting( 'region' );
		if ( is_wp_error( $region ) ) {
			$region = '';
		} ?>
		<?php $bucket_select = $this->get_setting( 'manual_bucket' ) ? 'manual' : ''; ?>
		<input id="<?php echo $prefix; ?>-bucket-select" type="hidden" class="no-compare" value="<?php echo esc_attr( $bucket_select ); ?>">
		<?php
		if ( isset( $after_bucket_content ) ) {
			echo $after_bucket_content;
		}

		if ( ! defined( $constant_bucket ) && ! $this->get_defined_setting( 'bucket', false ) && $needs_keys ) {
			$needs_keys_notice = array(
				'message' => sprintf( __( '<strong>Bucket Select Disabled</strong> &mdash; <a href="%s">Define your access keys</a> to configure the bucket', 'amazon-s3-and-cloudfront' ), '#settings' ),
				'id'      => 'as3cf-bucket-select-needs-keys',
				'inline'  => true,
				'type'    => 'notice-warning',
			);
			$this->render_view( 'notice', $needs_keys_notice );
		}
		?>
	</td>
</tr>
