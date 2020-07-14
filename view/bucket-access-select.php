<div class="as3cf-change-bucket-access-prompt">
	<?php
	$back_args = array();
	if ( ! empty( $_GET['prev_action'] ) && 'change-bucket' === $_GET['prev_action'] ) {
		$back_args = array( 'action' => 'change-bucket' );

		if ( ! empty( $_GET['orig_provider'] ) ) {
			$back_args['orig_provider'] = $_GET['orig_provider'];
		}
	}
	echo '<a href="' . $this->get_plugin_page_url( $back_args ) . '">' . __( '&laquo;&nbsp;Back', 'amazon-s3-and-cloudfront' ) . '</a>';

	if ( is_subclass_of( $this->get_provider_client(), 'DeliciousBrains\WP_Offload_Media\Providers\Storage\Storage_Provider' ) ){
		try {
			$public_access_blocked = $this->get_provider_client()->public_access_blocked( $selected_bucket );
		} catch ( Exception $e ) {
			$public_access_blocked = null;
		}
	}

	/** @var \DeliciousBrains\WP_Offload_Media\Providers\Delivery\Delivery_Provider $delivery_provider */
	$delivery_provider = $this->get_delivery_provider();

	$using_cloudfront = false;
	if ( ! empty( $delivery_provider ) && is_a( $delivery_provider, '\DeliciousBrains\WP_Offload_Media\Providers\Delivery\Delivery_Provider' ) && \DeliciousBrains\WP_Offload_Media\Providers\Delivery\AWS_CloudFront::get_provider_key_name() === $delivery_provider->get_provider_key_name() ) {
		$using_cloudfront = true;
	}

	$cloudfront_setup_doc = $this->dbrains_url(
		'/wp-offload-media/doc/cloudfront-setup/',
		array( 'utm_campaign' => 'support+docs', 'utm_content' => 'change+bucket+access' )
	);
	$bucket_settings_doc  = $this->dbrains_url(
		'/wp-offload-media/doc/settings/',
		array( 'utm_campaign' => 'support+docs', 'utm_content' => 'change+bucket+access' ),
		'bucket'
	);

	if ( empty( $public_access_blocked ) ) {
		if ( $using_cloudfront ) {
			?>
			<h3><?php _e( 'Block All Public Access is currently disabled', 'amazon-s3-and-cloudfront' ); ?></h3>
			<p>
				<?php
				_e( 'Since you\'re using Amazon CloudFront for delivery we recommend you enable Block All Public Access once you have set up the required Origin Access Identity and bucket policy.', 'amazon-s3-and-cloudfront' );
				echo ' ' . $this->settings_more_info_link( 'bucket', 'change+bucket+access' );
				?>
			</p>
			<p>
				<label for="origin-access-identity-confirmation">
					<input type="checkbox" id="origin-access-identity-confirmation">
					<?php
					printf(
						__( 'I have set up the required <a href="%1$s">Origin Access Identity and bucket policy</a>', 'amazon-s3-and-cloudfront' ),
						$bucket_settings_doc
					);
					?>
				</label>
			</p>
			<p class="actions select">
				<button type="submit" name="block-public-access" value="1" class="button button-primary right" id="block-public-access-confirmed" disabled><?php _e( 'Enable "Block All Public Access"', 'amazon-s3-and-cloudfront' ); ?></button>
				<button type="submit" name="block-public-access" value="0" class="button right"><?php _e( 'Leave "Block All Public Access" disabled', 'amazon-s3-and-cloudfront' ); ?></button>
			</p>
		<?php } else { ?>
			<h3><?php _e( 'Block All Public Access is currently disabled', 'amazon-s3-and-cloudfront' ); ?></h3>
			<p>
				<?php
				_e( 'Since you\'re not using Amazon CloudFront for delivery, we recommend you keep Block All Public Access disabled unless you have a very good reason to enable it.', 'amazon-s3-and-cloudfront' );
				echo ' ' . $this->settings_more_info_link( 'bucket', 'change+bucket+access' );
				?>
			</p>
			<p class="actions select">
				<button type="submit" name="block-public-access" value="0" class="button button-primary right"><?php _e( 'Leave "Block All Public Access" Disabled', 'amazon-s3-and-cloudfront' ); ?></button>
				<button type="submit" name="block-public-access" value="1" class="button right"><?php _e( 'Enable "Block All Public Access"', 'amazon-s3-and-cloudfront' ); ?></button>
			</p>
			<?php
		}
	} else {
		if ( $using_cloudfront ) {
			?>
			<h3><?php _e( 'Block All Public Access is currently enabled', 'amazon-s3-and-cloudfront' ); ?></h3>
			<p>
				<?php
				_e( 'Since you\'re using Amazon CloudFront for delivery we recommend you keep Block All Public Access enabled.', 'amazon-s3-and-cloudfront' );
				echo ' ' . $this->settings_more_info_link( 'bucket', 'change+bucket+access' );
				?>
			</p>
			<p class="actions select">
				<button type="submit" name="block-public-access" value="1" class="button button-primary right"><?php _e( 'Leave "Block All Public Access" enabled', 'amazon-s3-and-cloudfront' ); ?></button>
				<button type="submit" name="block-public-access" value="0" class="button right"><?php _e( 'Disable "Block All Public Access"', 'amazon-s3-and-cloudfront' ); ?></button>
			</p>
		<?php } elseif ( empty( $_GET['orig_provider'] ) ) { ?>
			<h3><?php _e( 'Warning: Block All Public Access is currently enabled', 'amazon-s3-and-cloudfront' ); ?></h3>
			<p>
				<?php
				printf(
					__( 'If you\'re following <a href="%1$s">our documentation on setting up Amazon CloudFront for delivery</a>, you can ignore this warning and continue. If you\'re not planning on using Amazon CloudFront for delivery, you need to <a href="%2$s">disable Block All Public Access</a>.', 'amazon-s3-and-cloudfront' ),
					$cloudfront_setup_doc,
					$bucket_settings_doc
				);
				?>
			</p>
			<p class="actions select">
				<button type="submit" name="block-public-access" value="1" class="button button-primary right"><?php _e( 'Continue', 'amazon-s3-and-cloudfront' ); ?></button>
				<button type="submit" name="block-public-access" value="0" class="button right"><?php _e( 'Disable "Block All Public Access"', 'amazon-s3-and-cloudfront' ); ?></button>
			</p>
		<?php } else { ?>
			<h3><?php _e( 'Block All Public Access is currently enabled', 'amazon-s3-and-cloudfront' ); ?></h3>
			<p>
				<?php
				if ( $delivery_provider->get_provider_key_name() === 'storage' ) {
					_e( 'You need to disable Block All Public Access so that your bucket is accessible for delivery. Block All Public Access should only been enabled when Amazon CloudFront is configured for delivery.', 'amazon-s3-and-cloudfront' );
				} else {
					printf(
						__( 'You need to disable Block All Public Access so that %1$s can access your bucket for delivery. Block All Public Access should only been enabled when Amazon CloudFront is configured for delivery.', 'amazon-s3-and-cloudfront' ),
						$delivery_provider->get_provider_name()
					);
				}
				echo ' ' . $this->settings_more_info_link( 'bucket', 'change+bucket+access' );
				?>
			</p>
			<p class="actions select">
				<button type="submit" name="block-public-access" value="0" class="button button-primary right"><?php _e( 'Disable "Block All Public Access"', 'amazon-s3-and-cloudfront' ); ?></button>
				<button type="submit" name="block-public-access" value="1" class="button right"><?php _e( 'Leave "Block All Public Access" enabled', 'amazon-s3-and-cloudfront' ); ?></button>
			</p>
			<?php
		}
	}
	?>
</div>
