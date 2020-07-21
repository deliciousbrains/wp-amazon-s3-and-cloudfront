<?php
/** @var int $enable_signed_urls */
/** @var string $selected_delivery_provider */
/** @var string $selected_delivery_provider_name */
$key_id_defined        = $this->get_defined_setting( 'signed-urls-key-id' );
$key_id_args           = $this->get_setting_args( 'signed-urls-key-id' );
$key_file_path_defined = $this->get_defined_setting( 'signed-urls-key-file-path' );
$key_file_path_args    = $this->get_setting_args( 'signed-urls-key-file-path' );
$object_prefix_defined = $this->get_defined_setting( 'signed-urls-object-prefix' );
$object_prefix_args    = $this->get_setting_args( 'signed-urls-object-prefix' );

$prefix = $this->get_plugin_prefix_slug();
?>
<div class="as3cf-setting <?php echo $prefix; ?>-signed-urls <?php echo ( $enable_signed_urls ) ? '' : 'hide'; // xss ok ?>">
	<div class="as3cf-sub-setting <?php echo $key_id_args['tr_class']; ?>">
		<?php
		if ( $key_id_defined ) {
			echo '<span class="as3cf-defined-in-config">' . __( 'defined in wp-config.php', 'amazon-s3-and-cloudfront' ) . '</span>';
		}
		?>
		<h4 class="as3cf-sub-setting-heading"><?php echo $this->get_delivery_provider()->signed_urls_key_id_name(); ?></h4>
		<?php if ( ! empty( $this->get_delivery_provider()->signed_urls_key_id_description() ) ) { ?>
			<p class="signed-urls-key-id-desc">
				<?php
				echo $this->get_delivery_provider()->signed_urls_key_id_description();
				echo '&nbsp;';
				echo $this->settings_more_info_link( 'signed-urls-key-id', 'media+private+key+id' );
				?>
			</p>
		<?php } ?>
		<p>
			<input
				type="text"
				name="signed-urls-key-id"
				value="<?php echo esc_attr( $this->get_setting( 'signed-urls-key-id' ) ); ?>"
				size="30"
				<?php echo $key_id_args['disabled'] ? 'class="disabled"' : ''; ?>
				<?php echo $key_id_args['disabled_attr']; ?>
			/>
			<span class="as3cf-validation-error" style="display: none;">
			<?php _e( 'Invalid character. Letters and numbers are allowed.', 'amazon-s3-and-cloudfront' ); ?>
		</span>
		</p>
	</div>
	<div class="as3cf-sub-setting <?php echo $key_file_path_args['tr_class']; ?>">
		<?php
		if ( $key_file_path_defined ) {
			echo '<span class="as3cf-defined-in-config">' . __( 'defined in wp-config.php', 'amazon-s3-and-cloudfront' ) . '</span>';
		}
		?>
		<h4 class="as3cf-sub-setting-heading"><?php echo $this->get_delivery_provider()->signed_urls_key_file_path_name(); ?></h4>
		<?php if ( ! empty( $this->get_delivery_provider()->signed_urls_key_file_path_description() ) ) { ?>
			<p class="signed-urls-key-file-path-desc">
				<?php
				echo $this->get_delivery_provider()->signed_urls_key_file_path_description();
				echo '&nbsp;';
				echo $this->settings_more_info_link( 'signed-urls-key-file-path', 'media+private+key+file+path' );
				?>
			</p>
		<?php } ?>
		<p>
			<input
				type="text"
				name="signed-urls-key-file-path"
				value="<?php echo esc_attr( $this->get_setting( 'signed-urls-key-file-path' ) ); ?>"
				size="30"
				placeholder="<?php echo \DeliciousBrains\WP_Offload_Media\Providers\Delivery\Delivery_Provider::signed_urls_key_file_path_placeholder(); ?>"
				<?php echo $key_file_path_args['disabled'] ? 'class="disabled"' : ''; ?>
				<?php echo $key_file_path_args['disabled_attr']; ?>
			/>
			<span class="as3cf-validation-error" style="display: none;">
			<?php _e( 'Invalid character. Letters, numbers, periods, hyphens, colons, spaces, underscores and slashes are allowed.', 'amazon-s3-and-cloudfront' ); ?>
		</span>
		</p>
	</div>
	<div class="as3cf-sub-setting <?php echo $object_prefix_args['tr_class']; ?>">
		<?php
		if ( $object_prefix_defined ) {
			echo '<span class="as3cf-defined-in-config">' . __( 'defined in wp-config.php', 'amazon-s3-and-cloudfront' ) . '</span>';
		}
		?>
		<h4 class="as3cf-sub-setting-heading"><?php echo $this->get_delivery_provider()->signed_urls_object_prefix_name(); ?></h4>
		<?php if ( ! empty( $this->get_delivery_provider()->signed_urls_object_prefix_description() ) ) { ?>
			<p class="signed-urls-object-prefix-desc">
				<?php
				echo $this->get_delivery_provider()->signed_urls_object_prefix_description();
				echo '&nbsp;';
				echo $this->settings_more_info_link( 'signed-urls-object-prefix', 'media+private+object+prefix' );
				?>
			</p>
		<?php } ?>
		<p>
			<input
				type="text"
				name="signed-urls-object-prefix"
				value="<?php echo esc_attr( $this->get_setting( 'signed-urls-object-prefix' ) ); ?>"
				size="30"
				placeholder="private/"
				<?php echo $object_prefix_args['disabled'] ? 'class="disabled"' : ''; ?>
				<?php echo $object_prefix_args['disabled_attr']; ?>
			/>
			<span class="as3cf-validation-error" style="display: none;">
			<?php _e( 'Invalid character. Letters, numbers, hyphens, spaces and forward slashes are allowed.', 'amazon-s3-and-cloudfront' ); ?>
		</span>
		</p>
	</div>
</div>
