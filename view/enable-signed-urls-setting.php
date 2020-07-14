<?php
/** @var bool $enable_delivery_domain */
$enable_signed_urls = $this->get_setting( 'enable-signed-urls' );
$args               = $this->get_setting_args( 'enable-signed-urls' );
?>
<tr class="<?php
echo $args['tr_class'];
echo ( $enable_delivery_domain ) ? '' : ' hide'; // xss ok
?>">
	<td>
		<?php
		$args['class'] = 'sub-toggle';
		$this->render_view( 'checkbox', $args );
		?>
	</td>
	<td>
		<?php echo $args['setting_msg']; ?>
		<h4><?php _e( 'Private Media', 'amazon-s3-and-cloudfront' ) ?></h4>
		<p class="signed-urls-desc">
			<?php _e( 'You can prevent public access to certain media files by enabling this option and the files will only be accessibly via signed URLs.', 'amazon-s3-and-cloudfront' ); ?>
			<?php echo $this->settings_more_info_link( 'signed-urls', 'media+private' ); ?>
		</p>
		<?php
		$this->render_view( 'signed-urls-setting', array(
			'enable_signed_urls'              => $enable_signed_urls,
			'selected_delivery_provider'      => $selected_delivery_provider,
			'selected_delivery_provider_name' => $selected_delivery_provider_name,
		) );
		?>
	</td>
</tr>