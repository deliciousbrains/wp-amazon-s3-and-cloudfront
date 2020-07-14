<?php
/** @var int $enable_delivery_domain */
/** @var string $disabled_attr */

$prefix = $this->get_plugin_prefix_slug();
?>
<p class="as3cf-setting <?php echo $prefix; ?>-delivery-domain <?php echo ( $enable_delivery_domain ) ? '' : 'hide'; // xss ok ?>">
	<input type="text" name="delivery-domain" value="<?php echo esc_attr( $this->get_setting( 'delivery-domain' ) ); ?>" size="30" <?php echo $disabled_attr; ?> />
	<span class="as3cf-validation-error" style="display: none;">
		<?php _e( 'Invalid character. Letters, numbers, periods and hyphens are allowed.', 'amazon-s3-and-cloudfront' ); ?>
	</span>
</p>
