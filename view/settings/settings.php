<?php
/* @var \Amazon_S3_And_CloudFront|\Amazon_S3_And_CloudFront_Pro $this */
$aws                  = $this->get_aws();
$key_constant         = $aws->access_key_id_constant();
$secret_constant      = $aws->secret_access_key_constant();
$any_constant_defined = (bool) $key_constant || $secret_constant;
$hide_form_initially  = false;
$database_warning_url = $aws->dbrains_url( '/wp-offload-s3/doc/quick-start-guide/#save-access-keys', array(
	'utm_campaign' => 'support+docs',
) );
?>
<div id="tab-settings" data-prefix="as3cf" class="as3cf-tab as3cf-content">
	<div class="as3cf-content as3cf-settings">

		<?php do_action( 'as3cf_licence_field' ) ?>

		<section class="as3cf-access-keys">
			<img class="as3cf-aws-logo alignleft" src="<?php echo plugins_url( 'assets/img/aws-logo.svg', $this->get_plugin_file_path() ) ?>" alt="" width="75" height="75">
			<h3 class="as3cf-section-heading"><?php _e( 'AWS Access Keys', 'amazon-s3-and-cloudfront' ) ?></h3>

			<?php if ( $aws->use_ec2_iam_roles() ) : ?>
				<p>
					<?php _e( 'You have enabled the use of IAM roles for Amazon EC2 instances.', 'amazon-s3-and-cloudfront' ) ?>
				</p>
			<?php elseif ( $any_constant_defined ) : ?>

				<?php if ( ! $aws->are_access_keys_set() ) : ?>
					<div class="notice-error notice">
						<p>
							<?php _e( 'Please check your wp-config.php file as it looks like one of your defines is missing or incorrect.', 'amazon-s3-and-cloudfront' ) ?>
						</p>
					</div>
				<?php endif ?>

				<p>
					<?php printf( __( 'You&#8217;ve already defined your AWS access keys in your wp-config.php. If you&#8217;d prefer to manage them here and store them in the database (<a href="%s">not recommended</a>), simply remove the lines from your wp-config.', 'amazon-s3-and-cloudfront' ), $database_warning_url ) ?>
				</p>

			<?php else : // no access keys defined & not using IAM roles ?>

				<p>
					<?php _e( 'We recommend defining your Access Keys in wp-config.php so long as you don&#8217;t commit it to source control (you shouldn&#8217;t be). Simply copy the following snippet and replace the stars with the keys.', 'amazon-s3-and-cloudfront' ) ?>
				</p>

				<textarea rows="2" class="as3cf-access-key-constants-snippet code clear" readonly>
define( 'AS3CF_AWS_ACCESS_KEY_ID',     '********************' );
define( 'AS3CF_AWS_SECRET_ACCESS_KEY', '**************************************' );
				</textarea>

				<?php if ( $aws->get_access_key_id() || $aws->get_secret_access_key() ) : ?>
					<p>
						<?php printf( __( 'You&#8217;re storing your Access Keys in the database (<a href="%s">not recommended</a>).</a>', 'amazon-s3-and-cloudfront' ), $database_warning_url ) ?>
					</p>
				<?php else : $hide_form_initially = true ?>
					<p class="reveal-form">
						<?php _e( 'If you&#8217;d rather store your Access Keys in the database, <a href="#" data-as3cf-toggle-access-keys-form>click here to reveal a form.</a>', 'amazon-s3-and-cloudfront' ) ?>
					</p>
				<?php endif ?>

			<?php endif ?>

			<div id="as3cf_access_keys" style="<?php echo $hide_form_initially ? 'display: none;' : '' ?>">
				<form method="post">
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e( 'Access Key ID', 'amazon-s3-and-cloudfront' ) ?></th>
							<td>
								<div class="as3cf-field-wrap <?php echo $key_constant ? 'as3cf-defined' : '' ?>">
									<input type="text"
									       name="aws-access-key-id"
									       value="<?php echo esc_attr( $aws->get_access_key_id() ) ?>"
									       autocomplete="off"
										<?php echo $key_constant ? 'disabled' : '' ?>
									>
									<?php if ( $key_constant ) : ?>
										<span class="as3cf-defined-in-config"><?php _e( 'defined in wp-config.php', 'amazon-s3-and-cloudfront' ) ?></span>
									<?php endif ?>
								</div>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e( 'Secret Access Key', 'amazon-s3-and-cloudfront' ) ?></th>
							<td>
								<div class="as3cf-field-wrap <?php echo $secret_constant ? 'as3cf-defined' : '' ?>">
									<input type="text"
									       name="aws-secret-access-key"
									       value="<?php echo $aws->get_secret_access_key() ? _x( '-- not shown --', 'placeholder for hidden access key, 39 char max', 'amazon-s3-and-cloudfront' ) : '' ?>"
									       autocomplete="off"
										<?php echo $secret_constant ? 'disabled' : '' ?>
									>
									<?php if ( $secret_constant ) : ?>
										<span class="as3cf-defined-in-config"><?php _e( 'defined in wp-config.php', 'amazon-s3-and-cloudfront' ) ?></span>
									<?php endif ?>
								</div>
							</td>
						</tr>
					</table>

					<?php if ( ! $any_constant_defined ) : ?>
						<div class="notice inline as3cf-notice-warning">
							<p>
								<?php _e( 'This will store your AWS access keys in the database (not recommended).', 'amazon-s3-and-cloudfront' ) ?>
								<?php echo $this->more_info_link( '/wp-offload-s3/doc/quick-start-guide/#save-access-keys' ) ?>
							</p>
						</div>

						<div data-as3cf-aws-keys-feedback class="notice inline" style="display: none;">
							<!-- response message filled here by JS -->
						</div>

						<button type="submit" class="button button-primary" data-as3cf-aws-keys-action="set"><?php _e( 'Save Changes', 'amazon-s3-and-cloudfront' ) ?></button>
						&nbsp;
						<?php if ( $aws->get_access_key_id() || $aws->get_secret_access_key() ) : ?>
							<button class="button" data-as3cf-aws-keys-action="remove"><?php _e( 'Remove Keys', 'amazon-s3-and-cloudfront' ) ?></button>
						<?php endif ?>

						<span data-as3cf-aws-keys-spinner class="spinner"></span>
					<?php endif ?>
				</form>
			</div>
		</section>

		<?php if ( $aws->needs_access_keys() ) : ?>
			<p class="as3cf-need-help">
				<span class="dashicons dashicons-info"></span>
				<?php printf( __( 'Need help getting your Access Keys? <a href="%s">Check out the Quick Start Guide &rarr;</a>', 'amazon-s3-and-cloudfront' ), $aws->dbrains_url( '/wp-offload-s3/doc/quick-start-guide/', array(
					'utm_campaign' => 'support+docs',
				) ) ) ?>
			</p>

		<?php endif ?>

	</div>
</div>

