<?php
/* @var \Amazon_S3_And_CloudFront|\Amazon_S3_And_CloudFront_Pro $this */
?>
<div id="tab-addons" data-prefix="as3cf" class="as3cf-tab as3cf-content as3cf-addons">

	<?php if ( ! $this->is_pro() ) : ?>
		<div class="notice inline as3cf-get-addons as3cf-notice-warning">
			<p>
			<?php
				printf( __( '<strong>Get Addons</strong> — The following addons are available with a WP Offload Media Gold license or better.<br>Visit <a href="%s">deliciousbrains.com</a> to purchase in just a few clicks.', 'amazon-s3-and-cloudfront' ),
					esc_url( $this->get_my_account_url() )
				);
			?>
			</p>
		</div>
	<?php endif ?>

	<div class="as3cf-addons-list">
		<?php $this->render_addons(); ?>
	</div>
</div>
