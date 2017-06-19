<div class="support support-section">
	<p><?php _e( 'As this is a free plugin, we do not provide support.', 'amazon-s3-and-cloudfront'); ?></p>

	<p><?php printf( __( 'You may ask the WordPress community for help by posting to the <a href="%s">WordPress.org support forum</a>. Response time can range from a few days to a few weeks and will likely be from a non-developer.', 'amazon-s3-and-cloudfront'), 'https://wordpress.org/plugins/amazon-s3-and-cloudfront/' ); ?></p>

	<?php $url = $this->dbrains_url( '/wp-offload-s3/', array(
		'utm_campaign' => 'WP+Offload+S3',
		'utm_content'  => 'support+tab',
	) ); ?>
	<p class="upgrade-to-pro"><?php printf( __( 'If you want a <strong>timely response via email from a developer</strong> who works on this plugin, <a href="%s">upgrade</a> and send us an email.', 'amazon-s3-and-cloudfront' ), $url ); ?></p>

	<p><?php printf( __( 'If you\'ve found a bug, please <a href="%s">submit an issue on GitHub</a>.', 'amazon-s3-and-cloudfront' ), 'https://github.com/deliciousbrains/wp-amazon-s3-and-cloudfront/issues' ); ?></p>

</div>
