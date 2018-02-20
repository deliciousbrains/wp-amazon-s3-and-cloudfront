<?php
/* @var \Amazon_S3_And_CloudFront|\Amazon_S3_And_CloudFront_Pro $this */
/* @var array $addon */
/* @var string $slug */
$activated = ! empty( $addon['active'] );
$installed = $activated || file_exists( WP_PLUGIN_DIR . '/' . $slug );
$links     = ! empty( $addon['links'] ) ? $addon['links'] : array();

$addon_details_link = function() use ( $addon, $slug ) {
	$url   = $addon['url'];
	$title = __( 'More Details &raquo;', 'amazon-s3-and-cloudfront' );
	$class = 'as3cf-addon-details';

	if ( ! empty( $addon['free'] ) ) {
		$url   = self_admin_url( 'plugin-install.php?tab=plugin-information&amp;plugin=' . $slug . '&amp;TB_iframe=true&amp;width=600&amp;height=800' );
		$class .= ' thickbox';
	}

	return sprintf( '<a class="%s" href="%s">%s</a>', $class, esc_url( $url ), esc_html( $title ) );
};

$activate_url = function() use ( $slug ) {
	$plugin_path = $slug . '/' . $slug . '.php';

	return wp_nonce_url( self_admin_url( 'plugins.php?action=activate&amp;plugin=' . $plugin_path ), 'activate-plugin_' . $plugin_path );
};

$install_url = function() use ( $slug ) {
	return wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=' . $slug ), 'install-plugin_' . $slug );
};
?>
<div class="as3cf-addon <?php echo $slug; ?>">
	<?php if ( isset( $addon['icon'] ) ) : ?>
		<img class="as3cf-addon-icon" src="<?php echo $this->get_addon_icon_url( $slug ) ?>" width="100" height="100">
	<?php endif ?>

	<div class="as3cf-addon-info">
		<div class="as3cf-addon-links">
			<?php if ( $installed && $activated ) : ?>
				<span class="installed activated"><?php echo esc_html_x( 'Installed & Activated', 'Plugin already installed and activated', 'amazon-s3-and-cloudfront' ) ?></span>
			<?php elseif ( $installed ) : ?>
				<span class="installed"><?php echo esc_html_x( 'Installed', 'Plugin already installed', 'amazon-s3-and-cloudfront' ) ?></span>
				<span class="activate"><a href="<?php echo esc_url( $activate_url() ) ?>"><?php echo esc_html_x( 'Activate Now', 'Activate plugin now', 'amazon-s3-and-cloudfront' ) ?></a></span>
			<?php elseif ( ! empty( $addon['install'] ) ) : ?>
				<span class="install"><a href="<?php echo esc_url( $install_url() ) ?>"><?php echo esc_html_x( 'Install Now', 'Install plugin now', 'amazon-s3-and-cloudfront' ) ?></a></span>
			<?php endif ?>

			<?php foreach ( $links as $link ) : ?>
				<span class="extra"><a href="<?php echo esc_url( $link['url'] ) ?>"><?php echo esc_html( $link['text'] ) ?></a></span>
			<?php endforeach ?>
		</div>

		<h1 class="as3cf-addon-title"><?php echo $addon['title'] ?></h1>

		<?php if ( isset( $addon['sub'] ) ) : ?>
			<div class="as3cf-addon-description">
				<?php echo esc_html( $addon['sub'] ) . ' ' . $addon_details_link() ?>
			</div>
		<?php endif ?>
	</div>
</div>