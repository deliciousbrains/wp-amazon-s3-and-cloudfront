<?php
/* @var \Amazon_S3_And_CloudFront|\Amazon_S3_And_CloudFront_Pro $this */
$prefix                          = $this->get_plugin_prefix_slug();
$selected_storage_provider       = $this->get_setting( 'provider', $this->get_default_storage_provider() );
$selected_region                 = $this->get_setting( 'region' );
$selected_bucket                 = $this->get_setting( 'bucket' );
$selected_bucket_prefix          = $this->get_object_prefix();
$selected_delivery_provider      = $this->get_setting( 'delivery-provider', $this->get_default_delivery_provider() );
$selected_delivery_provider_name = $this->get_delivery_provider()->get_provider_service_name();
$upgrade_lock_class              = $this->is_upgrading() ? ' locked locked-upgrade' : '';

if ( $this->get_storage_provider()->needs_access_keys() ) {
	$storage_classes = ' as3cf-needs-access-keys';
} else {
	$storage_classes = ' as3cf-has-access-keys';
}

if ( $selected_bucket ) {
	$storage_classes .= ' as3cf-has-bucket';
} else {
	$storage_classes .= ' as3cf-needs-bucket';
}

if ( ! empty( $_GET['action'] ) && 'change-provider' === $_GET['action'] ) {
	$storage_classes .= ' as3cf-change-provider';
}

if ( ! empty( $_GET['action'] ) && 'change-bucket' === $_GET['action'] ) {
	$storage_classes .= ' as3cf-change-bucket';
}

if ( ! empty( $_GET['action'] ) && 'change-bucket-access' === $_GET['action'] ) {
	$storage_classes .= ' as3cf-change-bucket-access';
}

if ( ! empty( $_GET['action'] ) && 'change-delivery-provider' === $_GET['action'] ) {
	$storage_classes .= ' as3cf-change-delivery-provider';
}

$storage_classes = apply_filters( 'as3cf_media_tab_storage_classes', $storage_classes );
?>
<div id="tab-media" data-prefix="as3cf" class="as3cf-tab as3cf-content<?php echo $storage_classes; // xss ok ?>">
	<div class="error inline as3cf-bucket-error as3cf-error" style="display: none;">
		<p>
			<span class="title"></span>
			<span class="message"></span>
		</p>
	</div>

	<?php
	do_action( 'as3cf_pre_tab_render', 'media' );
	$can_write = $this->render_bucket_permission_errors();
	?>

	<div class="as3cf-main-settings">
		<form method="post">
			<input type="hidden" name="action" value="save"/>
			<input type="hidden" name="plugin" value="<?php echo $this->get_plugin_slug(); ?>"/>
			<?php
			wp_nonce_field( $this->get_settings_nonce_key() );
			do_action( 'as3cf_form_hidden_fields' );

			$this->render_view( 'provider-select', compact( 'can_write' ) );

			$this->render_view( 'bucket-select',
				array(
					'prefix'            => $prefix,
					'selected_provider' => $selected_storage_provider,
					'selected_region'   => $selected_region,
					'selected_bucket'   => $selected_bucket,
				)
			);

			$this->render_view( 'bucket-access-select',
				array(
					'prefix'            => $prefix,
					'selected_provider' => $selected_storage_provider,
					'selected_region'   => $selected_region,
					'selected_bucket'   => $selected_bucket,
				)
			);

			$this->render_view( 'delivery-provider-select',
				array(
					'prefix'                    => $prefix,
					'selected_storage_provider' => $selected_storage_provider,
				)
			);

			do_action( 'as3cf_pre_media_settings' );
			?>

			<table class="form-table as3cf-media-settings<?php echo $upgrade_lock_class; ?>">

				<!-- URL Preview -->
				<tr class="configure-url">
					<td colspan="2">
						<div class="as3cf-url-preview-wrap">
							<span>URL Preview</span>
							<div class="as3cf-url-preview">
								<?php echo $this->get_url_preview(); // xss ok
								?>
							</div>
						</div>

						<?php
						$seo_friendly_url_link  = $this->more_info_link( '/wp-offload-media/doc/quick-start-guide/#using-a-cdn', 'seo+friendly+url+notice' );
						$seo_friendly_url_msg   = apply_filters( 'as3cf_seo_friendly_url_notice', sprintf( __( 'Yikes! That\'s not a very SEO-friendly URL. We strongly recommend you configure a CDN to point at your bucket and configure a subdomain of %1$s to point at your CDN. %2$s', 'amazon-s3-and-cloudfront' ), AS3CF_Utils::current_base_domain(), $seo_friendly_url_link ) );
						$seo_friendly_url_style = AS3CF_Utils::seo_friendly_url( $this->get_url_preview( false ) ) ? 'display: none' : '';
						$seo_friendly_url_args  = array(
							'message' => $seo_friendly_url_msg,
							'id'      => 'as3cf-seo-friendly-url-notice',
							'inline'  => true,
							'type'    => 'notice-info',
							'style'   => $seo_friendly_url_style,
						);
						$this->render_view( 'notice', $seo_friendly_url_args );
						?>
					</td>
				</tr>

				<!-- Storage -->
				<tr class="as3cf-setting-title">
					<td colspan="2"><h3><?php _e( 'Storage', 'amazon-s3-and-cloudfront' ); ?></h3></td>
				</tr>

				<?php
				$this->render_view( 'provider-setting',
					array(
						'prefix'   => $prefix,
						'tr_class' => "as3cf-settings-container {$prefix}-provider-setting",
					)
				);
				$this->render_view( 'bucket-setting',
					array(
						'prefix'                 => $prefix,
						'selected_provider'      => $selected_storage_provider,
						'selected_region'        => $selected_region,
						'selected_bucket'        => $selected_bucket,
						'selected_bucket_prefix' => $selected_bucket_prefix,
						'tr_class'               => "as3cf-settings-container {$prefix}-bucket-setting",
					)
				); ?>

				<?php $args = $this->get_setting_args( 'copy-to-s3' ); ?>
				<tr class="<?php echo $args['tr_class']; ?>">
					<td>
						<?php $this->render_view( 'checkbox', $args ); ?>
					</td>
					<td>
						<?php echo $args['setting_msg']; ?>
						<h4><?php _e( 'Copy Files to Bucket', 'amazon-s3-and-cloudfront' ) ?></h4>
						<p>
							<?php _e( 'When a file is uploaded to the Media Library, copy it to the bucket.', 'amazon-s3-and-cloudfront' ); ?>
							<?php echo $this->settings_more_info_link( 'copy-to-s3', 'media+copy+files+to+S3' ); ?>
						</p>

					</td>
				</tr>

				<?php $args = $this->get_setting_args( 'enable-object-prefix' ); ?>
				<tr class="url-preview <?php echo $args['tr_class']; ?>">
					<td>
						<?php $args['class'] = 'sub-toggle'; ?>
						<?php $this->render_view( 'checkbox', $args ); ?>
					</td>
					<td>
						<?php echo $args['setting_msg']; ?>
						<h4><?php _e( 'Path', 'amazon-s3-and-cloudfront' ) ?></h4>
						<p class="object-prefix-desc">
							<?php _e( 'By default the path is the same as your local WordPress files.', 'amazon-s3-and-cloudfront' ); ?>
							<?php echo $this->settings_more_info_link( 'object-prefix', 'media+path' ); ?>
						</p>
						<p class="as3cf-setting <?php echo $prefix; ?>-enable-object-prefix <?php echo ( $this->get_setting( 'enable-object-prefix' ) ) ? '' : 'hide'; // xss ok
						?>">
							<?php $args = $this->get_setting_args( 'object-prefix' ); ?>
							<input type="text" name="object-prefix" value="<?php echo esc_attr( $this->get_setting( 'object-prefix' ) ); ?>" size="30" placeholder="<?php echo $this->get_default_object_prefix(); ?>" <?php echo $args['disabled_attr']; ?> />
						</p>
					</td>
				</tr>

				<?php $args = $this->get_setting_args( 'use-yearmonth-folders' ); ?>
				<tr class="url-preview <?php echo $args['tr_class']; ?>">
					<td>
						<?php $this->render_view( 'checkbox', $args ); ?>
					</td>
					<td>
						<?php echo $args['setting_msg']; ?>
						<h4><?php _e( 'Year/Month', 'amazon-s3-and-cloudfront' ) ?></h4>
						<p>
							<?php _e( 'Add the Year/Month to the end of the path above just like WordPress does by default.', 'amazon-s3-and-cloudfront' ); ?>
							<?php echo $this->settings_more_info_link( 'use-yearmonth-folders', 'media+year+month' ); ?>
						</p>
						<?php do_action( 'as3cf_after_setting', 'use-yearmonth-folders', __( 'Year/Month', 'amazon-s3-and-cloudfront' ) ); ?>
					</td>
				</tr>

				<?php $args = $this->get_setting_args( 'object-versioning' ); ?>
				<tr class="advanced-options url-preview as3cf-border-bottom <?php echo $args['tr_class']; ?>">
					<td>
						<?php $this->render_view( 'checkbox', $args ); ?>
					</td>
					<td>
						<?php echo $args['setting_msg']; ?>
						<h4><?php _e( 'Object Versioning', 'amazon-s3-and-cloudfront' ) ?></h4>
						<p>
							<?php _e( 'Append a timestamp to the file\'s bucket path. Recommended when using a CDN so you don\'t have to worry about cache invalidation.', 'amazon-s3-and-cloudfront' ); ?>
							<?php echo $this->settings_more_info_link( 'object-versioning', 'media+object+versioning' ); ?>
						</p>
						<?php do_action( 'as3cf_after_setting', 'object-versioning', __( 'Object Versioning', 'amazon-s3-and-cloudfront' ) ); ?>
					</td>
				</tr>

				<!-- Delivery -->
				<tr class="as3cf-setting-title">
					<td colspan="2"><h3><?php _e( 'Delivery', 'amazon-s3-and-cloudfront' ); ?></h3></td>
				</tr>

				<?php
				$this->render_view( 'delivery-provider-setting',
					array(
						'prefix'   => $prefix,
						'tr_class' => "as3cf-settings-container {$prefix}-provider-setting",
					)
				); ?>

				<?php $args = $this->get_setting_args( 'serve-from-s3' ); ?>
				<tr class="<?php echo $args['tr_class']; ?>">
					<td>
						<?php $this->render_view( 'checkbox', $args ); ?>
					</td>
					<td>
						<?php echo $args['setting_msg']; ?>
						<h4><?php _e( 'Rewrite Media URLs', 'amazon-s3-and-cloudfront' ) ?></h4>
						<p>
							<?php printf( __( 'For Media Library files that have been copied to your bucket, rewrite the URLs so that they are served from %s instead of your server.', 'amazon-s3-and-cloudfront' ), $selected_delivery_provider_name ); ?>
							<?php echo $this->settings_more_info_link( 'serve-from-s3', 'media+rewrite+file+urls' ); ?>
						</p>

					</td>
				</tr>

				<?php
				if ( $this->get_delivery_provider()->delivery_domain_allowed() ) {
					$this->render_view( 'enable-delivery-domain-setting',
						array(
							'selected_delivery_provider'      => $selected_delivery_provider,
							'selected_delivery_provider_name' => $selected_delivery_provider_name,
						)
					);
				}
				?>

				<?php $args = $this->get_setting_args( 'force-https' ); ?>
				<tr class="as3cf-border-bottom url-preview <?php echo $args['tr_class']; ?>">
					<td>
						<?php $this->render_view( 'checkbox', $args ); ?>
					</td>
					<td>
						<?php echo $args['setting_msg']; ?>
						<h4><?php _e( 'Force HTTPS', 'amazon-s3-and-cloudfront' ) ?></h4>
						<p>
							<?php _e( 'By default we use HTTPS when the request is HTTPS and regular HTTP when the request is HTTP, but you may want to force the use of HTTPS always, regardless of the request.', 'amazon-s3-and-cloudfront' ); ?>
							<?php echo $this->settings_more_info_link( 'force-https', 'media+force+https' ); ?>
						</p>
					</td>
				</tr>

				<!-- Advanced Options -->
				<tr class="advanced-options as3cf-setting-title">
					<td colspan="2"><h3><?php _e( 'Advanced Options', 'amazon-s3-and-cloudfront' ); ?></h3></td>
				</tr>

				<?php $args = $this->get_setting_args( 'remove-local-file' ); ?>
				<tr class="advanced-options <?php echo $args['tr_class']; ?>">
					<td>
						<?php $this->render_view( 'checkbox', $args ); ?>
					</td>
					<td>
						<?php echo $args['setting_msg']; ?>
						<h4><?php _e( 'Remove Files From Server', 'amazon-s3-and-cloudfront' ) ?></h4>
						<p><?php _e( 'Once a file has been copied to the bucket, remove it from the local server.', 'amazon-s3-and-cloudfront' ); ?>
							<?php echo $this->settings_more_info_link( 'remove-local-file', 'media+remove+files+from+server' ); ?>
						</p>
						<?php
						$lost_files_msg  = apply_filters( 'as3cf_lost_files_notice', __( '<strong>Broken URLs</strong> &mdash; There will be broken URLs for files that don\'t exist locally. You can fix this by enabling <strong>Rewrite Media URLs</strong> to use the offloaded media.', 'amazon-s3-and-cloudfront' ) );
						$lost_files_args = array(
							'message' => $lost_files_msg,
							'id'      => 'as3cf-lost-files-notice',
							'inline'  => true,
							'type'    => 'error',
							'style'   => 'display: none',
						);
						$this->render_view( 'notice', $lost_files_args );

						$remove_local_link = $this->more_info_link( '/wp-offload-media/doc/compatibility-with-other-plugins/', 'error-media+remove+files+from+server' );
						$remove_local_msg  = sprintf( __( '<strong>Warning</strong> &mdash; Some plugins depend on the file being present on the local server and may not work when the file is removed. %s', 'amazon-s3-and-cloudfront' ), $remove_local_link );
						$remove_local_msg  .= "<br/><br />";
						$remove_local_msg  .= __( 'If you have a backup system in place (as you should) that backs up your site files, media, and database, your media will no longer be backed up as it will no longer be present on the filesystem.', 'amazon-s3-and-cloudfront' );
						$remove_local_msg  = apply_filters( 'as3cf_remove_local_notice', $remove_local_msg );
						$remove_local_args = array(
							'message' => $remove_local_msg,
							'id'      => 'as3cf-remove-local-notice',
							'inline'  => true,
							'type'    => 'notice-warning',
							'style'   => 'display: none',
						);
						$this->render_view( 'notice', $remove_local_args ); ?>
					</td>
				</tr>

				<!-- Save button for main settings -->
				<tr>
					<td colspan="2">
						<button type="submit" class="button button-primary" <?php echo $this->maybe_disable_save_button(); ?>><?php _e( 'Save Changes', 'amazon-s3-and-cloudfront' ); ?></button>
					</td>
				</tr>
			</table>
		</form>
	</div>

	<?php
	if ( $this->get_storage_provider()->needs_access_keys() ) {
		?>
		<p class="as3cf-need-help">
			<span class="dashicons dashicons-info"></span>
			<?php printf( __( 'Need help getting your Access Keys? <a href="%s">Check out the Quick Start Guide &rarr;</a>', 'amazon-s3-and-cloudfront' ), $this->dbrains_url( '/wp-offload-media/doc/quick-start-guide/', array(
				'utm_campaign' => 'support+docs',
			) ) ) ?>
		</p>
		<?php
	}
	?>
</div>
