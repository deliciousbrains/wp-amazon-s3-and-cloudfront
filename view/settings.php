<div class="aws-content as3cf-settings<?php echo $this->get_setting( 'bucket' ) ? ' as3cf-has-bucket' : ''; ?>">

	<?php
	if ( isset( $_GET['updated'] ) ) {
		?>
		<div class="updated">
			<p>
				<?php _e( 'Settings saved.', 'as3cf' ); ?>
			</p>
		</div>
		<?php
	}
	?>

	<div class="error as3cf-can-write-error">
		<p>
			<strong>
				<?php _e( 'S3 Policy is Read-Only', 'as3cf' ); ?>
			</strong>&mdash;
			<?php printf( __( 'You need to go to  <a href="%s">Identity and Access Management</a> in your AWS console and manage the policy for the user you\'re using for this plugin. Your policy should look something like the following:', 'as3cf' ), 'https://console.aws.amazon.com/iam/home' ); ?>
		</p>
		<pre><code>{
  "Version": "2012-10-17",
  "Statement": [
    {
      "Effect": "Allow",
      "Action": "s3:*",
      "Resource": "*"
    }
  ]
}</code></pre>
	</div>

	<div class="as3cf-bucket-select">
		<h3><?php _e( 'Select an existing S3 bucket to use:', 'as3cf' ); ?></h3>
		<ul class="as3cf-bucket-list" data-working="<?php _e( 'Loading...', 'as3cf' ); ?>"></ul>

		<h3><?php _e( 'Or create a new bucket:', 'as3cf' ); ?></h3>
		<form method="post" class="as3cf-create-bucket-form">
			<?php wp_nonce_field( 'as3cf-save-settings' ) ?>
			<input type="text" name="bucket_name" placeholder="<?php _e( 'Bucket Name', 'as3cf' ); ?>">
			<button type="submit" class="button" data-working="<?php _e( 'Creating...', 'as3cf' ); ?>"><?php _e( 'Create', 'as3cf' ); ?></button>
		</form>
	</div>

	<div class="as3cf-main-settings">
		<form method="post">
			<input type="hidden" name="action" value="save" />
			<?php wp_nonce_field( 'as3cf-save-settings' ) ?>

			<table class="form-table">
				<tr class="as3cf-border-bottom">
					<td><h3><?php _e( 'Bucket', 'as3cf' ); ?></h3></td>
					<td>
						<span class="as3cf-active-bucket"><?php echo $this->get_setting( 'bucket' ); ?></span>
						<a href="#" class="as3cf-change-bucket"><?php _e( 'Change', 'as3cf' ); ?></a>
					</td>
				</tr>
				<tr>
					<td colspan="2"><h3><?php _e( 'Enable/Disable the Plugin', 'as3cf' ); ?></h3></td>
				</tr>
				<tr>
					<td>
						<input type="checkbox" name="copy-to-s3" value="1" id="copy-to-s3" <?php echo $this->get_setting( 'copy-to-s3' ) ? 'checked="checked" ' : ''; ?> />
					</td>
					<td>
						<h4><?php _e( 'Copy Files to S3', 'as3cf' ) ?></h4>
						<p><?php _e( 'When a file is uploaded to the Media Library, copy it to S3.', 'as3cf' ) ?><br>
						<?php _e( 'Existing files are <em>not</em> copied to S3.', 'as3cf' ) ?></p>
					</td>
				</tr>
				<tr class="as3cf-border-bottom">
					<td>
						<input type="checkbox" name="serve-from-s3" value="1" id="serve-from-s3" <?php echo $this->get_setting( 'serve-from-s3' ) ? 'checked="checked" ' : ''; ?> />
					</td>
					<td>
						<h4><?php _e( 'Rewrite File URLs', 'as3cf' ) ?></h4>
						<p><?php _e( 'For Media Library files that have been copied to S3, rewrite the URLs<br>so that they are served from S3/CloudFront instead of your server.', 'as3cf' ) ?></p>
					</td>
				</tr>

				<?php /*<tr valign="top">
					<td>
						<h3><?php _e( 'S3 Settings', 'as3cf' ); ?></h3>

						<select name="bucket" class="bucket">
						<option value="">-- <?php _e( 'Select an S3 Bucket', 'as3cf' ); ?> --</option>
						<?php if ( is_array( $buckets ) ) foreach ( $buckets as $bucket ): ?>
						    <option value="<?php echo esc_attr( $bucket['Name'] ); ?>" <?php echo $bucket['Name'] == $this->get_setting( 'bucket' ) ? 'selected="selected"' : ''; ?>><?php echo esc_html( $bucket['Name'] ); ?></option>
						<?php endforeach;?>
						<option value="new"><?php _e( 'Create a new bucket...', 'as3cf' ); ?></option>
						</select><br />

						<input type="checkbox" name="virtual-host" value="1" id="virtual-host" <?php echo $this->get_setting( 'virtual-host' ) ? 'checked="checked" ' : '';?> />
						<label for="virtual-host"> <?php _e( 'Bucket is setup for virtual hosting', 'as3cf' ); ?></label> (<a href="http://docs.aws.amazon.com/AmazonS3/latest/dev/VirtualHosting.html"><?php _e( 'more info', 'as3cf' ); ?></a>)
						<br />

						<input type="checkbox" name="expires" value="1" id="expires" <?php echo $this->get_setting( 'expires' ) ? 'checked="checked" ' : ''; ?> />
						<label for="expires"> <?php printf( __( 'Set a <a href="%s" target="_blank">far future HTTP expiration header</a> for uploaded files <em>(recommended)</em>', 'as3cf' ), 'http://developer.yahoo.com/performance/rules.html#expires' ); ?></label>
					</td>
				</tr>

				<tr valign="top">
					<td>
						<label><?php _e( 'Object Path:', 'as3cf' ); ?></label>&nbsp;&nbsp;
						<input type="text" name="object-prefix" value="<?php echo esc_attr( $this->get_setting( 'object-prefix' ) ); ?>" size="30" />
						<label><?php echo trailingslashit( $this->get_dynamic_prefix() ); ?></label>
					</td>
				</tr>

				<tr valign="top">
					<td>
						<h3><?php _e( 'CloudFront Settings', 'as3cf' ); ?></h3>

						<label><?php _e( 'Domain Name', 'as3cf' ); ?></label><br />
						<input type="text" name="cloudfront" value="<?php echo esc_attr( $this->get_setting( 'cloudfront' ) ); ?>" size="50" />
						<p class="description"><?php _e( 'Leave blank if you aren&#8217;t using CloudFront.', 'as3cf' ); ?></p>

					</td>
				</tr>

				<tr valign="top">
					<td>
						<h3><?php _e( 'Plugin Settings', 'as3cf' ); ?></h3>

						<input type="checkbox" name="copy-to-s3" value="1" id="copy-to-s3" <?php echo $this->get_setting( 'copy-to-s3' ) ? 'checked="checked" ' : ''; ?> />
						<label for="copy-to-s3"> <?php _e( 'Copy files to S3 as they are uploaded to the Media Library', 'as3cf' ); ?></label>
						<br />

						<input type="checkbox" name="serve-from-s3" value="1" id="serve-from-s3" <?php echo $this->get_setting( 'serve-from-s3' ) ? 'checked="checked" ' : ''; ?> />
						<label for="serve-from-s3"> <?php _e( 'Point file URLs to S3/CloudFront for files that have been copied to S3', 'as3cf' ); ?>
							<span class="tooltip" data-tooltip="<?php _e( 'When this is option unchecked, your Media Library images and other files will be served from your server instead of S3/CloudFront. If any images were removed from your server, they will be broken.', 'as3cf' ); ?>">
								<span class="dashicons dashicons-editor-help"></span>
							</span>
						</label>
						<br />

						<input type="checkbox" name="remove-local-file" value="1" id="remove-local-file" <?php echo $this->get_setting( 'remove-local-file' ) ? 'checked="checked" ' : ''; ?> />
						<label for="remove-local-file"> <?php _e( 'Remove uploaded file from local filesystem once it has been copied to S3', 'as3cf' ); ?></label>
						<br />

						<input type="checkbox" name="force-ssl" value="1" id="force-ssl" <?php echo $this->get_setting( 'force-ssl' ) ? 'checked="checked" ' : ''; ?> />
						<label for="force-ssl"> <?php _e( 'Always serve files over https (SSL)', 'as3cf' ); ?></label>
						<br />

						<input type="checkbox" name="object-versioning" value="1" id="object-versioning" <?php echo $this->get_setting( 'object-versioning' ) ? 'checked="checked" ' : ''; ?> />
						<label for="object-versioning"> <?php printf( __( 'Implement <a href="%s">object versioning</a> by appending a timestamp to the S3 file path', 'as3cf' ), 'http://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/ReplacingObjects.html' ); ?></label>
						<br />

						<input type="checkbox" name="hidpi-images" value="1" id="hidpi-images" <?php echo $this->get_setting( 'hidpi-images' ) ? 'checked="checked" ' : ''; ?> />
						<label for="hidpi-images"> <?php _e( 'Copy any HiDPI (@2x) images to S3 (works with WP Retina 2x plugin)', 'as3cf' ); ?></label>

					</td>
				</tr>
				<tr valign="top">
					<td>
						<button type="submit" class="button button-primary"><?php _e( 'Save Changes', 'amazon-web-services' ); ?></button>
					</td>
				</tr>*/ ?>
			</table>

		</form>
	</div>

	<?php $this->render_view( 'sidebar' ); ?>

</div>