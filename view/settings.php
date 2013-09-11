<div class="aws-content as3cf-settings">

<?php
$buckets = $this->get_buckets();

if ( is_wp_error( $buckets ) ) :
	?>
	<div class="error">
		<p>
			<?php _e( 'Error retrieving a list of your S3 buckets from AWS:', 'as3cf' ); ?>
			<?php echo $buckets->get_error_message(); ?>
		</p>
	</div>
	<?php
endif;
?>

<form method="post">
<input type="hidden" name="action" value="save" />
<?php wp_nonce_field( 'as3cf-save-settings' ) ?>

<table class="form-table">
<tr valign="top">
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
		<label for="virtual-host"> <?php _e( 'Bucket is setup for virtual hosting', 'as3cf' ); ?></label> (<a href="http://docs.amazonwebservices.com/AmazonS3/2006-03-01/VirtualHosting.html">more info</a>)
		<br />

		<input type="checkbox" name="expires" value="1" id="expires" <?php echo $this->get_setting( 'expires' ) ? 'checked="checked" ' : ''; ?> />
		<label for="expires"> <?php printf( __( 'Set a <a href="%s" target="_blank">far future HTTP expiration header</a> for uploaded files <em>(recommended)</em>', 'as3cf' ), 'http://developer.yahoo.com/performance/rules.html#expires' ); ?></label>

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
		<h3><?php _e( 'WordPress Settings', 'as3cf' ); ?></h3>

		<input type="checkbox" name="wp-uploads" value="1" id="wp-uploads" <?php echo $this->get_setting( 'wp-uploads' ) ? 'checked="checked" ' : ''; ?> />
		<label for="wp-uploads"> <?php _e( 'Copy files to S3 as they are uploaded to the Media Library and automatically point all URLs to S3/CloudFront', 'as3cf' ); ?></label>
		<p class="description"><?php _e( 'Uncheck this to revert back to using your own web host for storage and delivery at anytime.', 'as3cf' ); ?></p>

	</td>
</tr>
<tr valign="top">
	<td>
		<button type="submit" class="button button-primary"><?php _e( 'Save Changes', 'amazon-web-services' ); ?></button>
	</td>
</tr>
</table>

</form>

</div>