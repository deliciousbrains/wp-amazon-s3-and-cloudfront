<div class="as3cf-sidebar lite">

	<a class="as3cf-banner" href="<?php echo $this->dbrains_url( '/wp-offload-media/', array(
		'utm_campaign' => 'WP+Offload+S3',
		'utm_content'  => 'sidebar',
	) ); ?>">
	</a>

	<div class="as3cf-upgrade-details">
		<h1><?php _e( 'Upgrade', 'amazon-s3-and-cloudfront' ); ?></h1>

		<h2><?php _e( 'Gain access to more features when you upgrade to WP Offload Media', 'amazon-s3-and-cloudfront' ); ?></h2>

		<ul>
			<li><?php echo wptexturize( __( 'Offload existing Media Library items', 'amazon-s3-and-cloudfront' ) ); // xss ok ?></li>
			<li><?php echo wptexturize( __( 'Manage offloaded files in WordPress', 'amazon-s3-and-cloudfront' ) ); // xss ok ?></li>
			<li><?php echo wptexturize( __( 'Assets addon - Serve your CSS & JS from CloudFront or another CDN', 'amazon-s3-and-cloudfront' ) ); // xss ok ?></li>
			<li><?php echo wptexturize( __( 'Private media via CloudFront', 'amazon-s3-and-cloudfront' ) ); // xss ok ?></li>
			<li><?php echo wptexturize( __( 'WooCommerce integration', 'amazon-s3-and-cloudfront' ) ); // xss ok ?></li>
			<li><?php echo wptexturize( __( 'Easy Digital Downloads integration', 'amazon-s3-and-cloudfront' ) ); // xss ok ?></li>
			<li><?php echo wptexturize( __( 'Priority email support', 'amazon-s3-and-cloudfront' ) ); // xss ok ?></li>
		</ul>
	</div>

	<div class="subscribe">
		<h2><?php _e( 'Get up to 40% off your first year of WP Offload Media!', 'amazon-s3-and-cloudfront' ); ?></h2>

		<h3>
			<a href="<?php echo $this->dbrains_url( '/wp-offload-media/pricing/', array(
				'utm_campaign' => 'WP+Offload+S3',
				'utm_content'  => 'sidebar',
			) ); ?>"><?php _e( 'Get the discount', 'amazon-s3-and-cloudfront' ); ?></a>
		</h3>

		<p class="discount-applied"><?php _e( '* Discount applied automatically.', 'amazon-s3-and-cloudfront' ); ?></p>
	</div>

	<div class="credits">
		<h4><?php _e( 'Created and maintained by', 'amazon-s3-and-cloudfront' ); ?></h4>
		<ul>
			<li>
				<a href="<?php echo $this->dbrains_url( '', array(
					'utm_campaign' => 'WP+Offload+S3',
					'utm_content'  => 'sidebar',
				) ); ?>">
					<img src="<?php echo plugins_url( 'assets/img/logo-dbi.svg', $this->get_plugin_file_path() ); ?>" alt="Delicious Brains Inc.">
					<span><?php _e( 'Delicious Brains Inc.', 'amazon-s3-and-cloudfront' ); ?></span>
				</a>
			</li>
		</ul>
	</div>
</div>
