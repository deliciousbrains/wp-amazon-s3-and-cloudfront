<?php
$domain           = $this->get_setting( 'domain' );
$args             = $this->get_setting_args( 'domain' );
$args['tr_class'] = $args['tr_class'] . ' url-preview';
if ( $this->show_deprecated_domain_setting( $domain ) ) {
	return $this->render_view( 'deprecated-domain-setting', $args );
}
?>
<tr class="<?php echo $args['tr_class']; ?>">
	<td>
		<?php
		$args['values'] = array( 'path', 'cloudfront' );
		$args['class']  = 'sub-toggle';
		$this->render_view( 'checkbox', $args );
		?>
	</td>
	<td>
		<?php echo $args['setting_msg']; ?>
		<h4><?php _e( 'Custom Domain (CNAME)', 'amazon-s3-and-cloudfront' ) ?></h4>
		<p class="domain-desc">
			<?php printf( __( 'We strongly recommend you configure a CDN to point at your bucket and configure a subdomain of %1$s to point at your CDN. If you don\'t enter a subdomain of your site\'s domain in the field below it will negatively impact your site\'s SEO.', 'amazon-s3-and-cloudfront' ), AS3CF_Utils::current_base_domain() ); ?>
			<?php echo $this->settings_more_info_link( 'domain', 'media+cloudfront+or+custom+domain' ); ?>
		</p>
		<?php
		$args           = $this->get_setting_args( 'cloudfront' );
		$args['domain'] = $domain;
		$this->render_view( 'cloudfront-setting', $args );
		?>
	</td>
</tr>