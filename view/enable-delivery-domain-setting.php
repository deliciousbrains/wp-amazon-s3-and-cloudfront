<?php
$enable_delivery_domain = $this->get_setting( 'enable-delivery-domain' );
$args                   = $this->get_setting_args( 'enable-delivery-domain' );
$args['tr_class']       = $args['tr_class'] . ' url-preview'; // Refresh URL Preview on changes
?>
<tr class="<?php echo $args['tr_class']; ?>">
	<td>
		<?php
		$args['class'] = 'sub-toggle';
		$this->render_view( 'checkbox', $args );
		?>
	</td>
	<td>
		<?php echo $args['setting_msg']; ?>
		<h4><?php _e( 'Custom Domain (CNAME)', 'amazon-s3-and-cloudfront' ) ?></h4>
		<p class="delivery-domain-desc">
			<?php printf( __( 'We strongly recommend you configure a subdomain of %1$s to point at your %2$s distribution. If you don\'t enter a subdomain of your site\'s domain in the field below it will negatively impact your site\'s SEO.', 'amazon-s3-and-cloudfront' ), AS3CF_Utils::current_base_domain(), $selected_delivery_provider_name ); ?>
			<?php echo $this->settings_more_info_link( 'delivery-domain', 'media+cloudfront+or+custom+domain' ); ?>
		</p>
		<?php
		$args                           = $this->get_setting_args( 'delivery-domain' );
		$args['enable_delivery_domain'] = $enable_delivery_domain;
		$this->render_view( 'delivery-domain-setting', $args );
		?>
	</td>
</tr>
<?php
if ( $this->get_delivery_provider()->use_signed_urls_key_file_allowed() ) {
	$this->render_view( 'enable-signed-urls-setting', array(
		'enable_delivery_domain'          => $enable_delivery_domain,
		'selected_delivery_provider'      => $selected_delivery_provider,
		'selected_delivery_provider_name' => $selected_delivery_provider_name,
	) );
}
?>
