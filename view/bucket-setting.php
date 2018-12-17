<?php
$needs_keys      = $this->get_provider()->needs_access_keys();
$constant_bucket = strtoupper( str_replace( '-', '_', $prefix ) . '_BUCKET' );
$tr_class        = ( isset( $tr_class ) ) ? $tr_class : '';
?>

<tr class="as3cf-bucket <?php echo $tr_class; ?>">
	<td><h4><?php _e( 'Bucket:', 'amazon-s3-and-cloudfront' ); ?></h4></td>
	<td>
		<span id="<?php echo $prefix; ?>-active-bucket" class="as3cf-active-bucket">
			<?php echo $selected_bucket; // xss ok ?>
		</span>
		<a id="<?php echo $prefix; ?>-view-bucket" target="_blank" class="as3cf-view-bucket" href="<?php echo $this->get_provider()->get_console_url( $selected_bucket, $selected_bucket_prefix ); ?>" title="<?php _e( 'View in provider\'s console', 'amazon-s3-and-cloudfront' ); ?>">
			<span class="dashicons dashicons-external"></span>
		</a>
		<?php if ( defined( $constant_bucket ) || false !== $this->get_defined_setting( 'bucket', false ) ) {
			echo '<span class="as3cf-defined-in-config">' . __( 'defined in wp-config.php', 'amazon-s3-and-cloudfront' ) . '</span>';
		} elseif ( ! $needs_keys ) { ?>
			<a href="<?php echo $this->get_plugin_page_url( array( 'action' => 'change-bucket' ) ); ?>" id="<?php echo $prefix; ?>-change-bucket" class="as3cf-change-bucket"><?php _e( 'Change', 'amazon-s3-and-cloudfront' ); ?></a>
		<?php } ?>

		<p id="<?php echo $prefix; ?>-active-region" class="as3cf-active-region" title="<?php _e( 'The region that the bucket is in.', 'amazon-s3-and-cloudfront' ); ?>">
			<?php echo $this->get_provider()->get_region_name( $selected_region ); // xss ok ?>
		</p>

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

		$lock_bucket_args = array(
			'message' => __( '<strong>Provider &amp; Bucket Select Disabled</strong> &mdash; Provider and bucket selection has been disabled while files are copied between buckets.', 'amazon-s3-and-cloudfront' ),
			'id'      => 'as3cf-bucket-select-locked',
			'inline'  => true,
			'type'    => 'notice-warning',
			'style'   => 'display: none',
		);
		$this->render_view( 'notice', $lock_bucket_args ); ?>
	</td>
</tr>
