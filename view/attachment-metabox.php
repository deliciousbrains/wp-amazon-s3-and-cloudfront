<?php
/** @var array|bool $s3object */
/** @var WP_Post $post */
/** @var array $available_actions */
/** @var bool $local_file_exists */
/** @var string $sendback */
$is_removable    = $s3object && in_array( 'remove', $available_actions );
$is_copyable     = $local_file_exists && in_array( 'copy', $available_actions );
$is_downloadable = ! $local_file_exists && in_array( 'download', $available_actions );
?>
<div class="s3-details">
	<?php if ( ! $s3object ) : ?>
		<div class="misc-pub-section">
			<em class="not-copied"><?php _e( 'This item has not been copied to S3 yet.', 'amazon-s3-and-cloudfront' ); ?></em>
		</div>
	<?php else : ?>
		<div class="misc-pub-section">
			<div class="s3-key"><?php echo $this->get_media_action_strings( 'bucket' ); ?>:</div>
			<input type="text" id="as3cf-bucket" class="widefat" readonly="readonly" value="<?php echo $s3object['bucket']; ?>">
		</div>
		<div class="misc-pub-section">
			<div class="s3-key"><?php echo $this->get_media_action_strings( 'key' ); ?>:</div>
			<input type="text" id="as3cf-key" class="widefat" readonly="readonly" value="<?php echo $s3object['key']; ?>">
		</div>
		<?php if ( isset( $s3object['region'] ) && $s3object['region'] ) : ?>
			<div class="misc-pub-section">
				<div class="s3-key"><?php echo $this->get_media_action_strings( 'region' ); ?>:</div>
				<div id="as3cf-region" class="s3-value"><?php echo $s3object['region']; ?></div>
			</div>
		<?php endif; ?>
		<div class="misc-pub-section">
			<div class="s3-key"><?php echo $this->get_media_action_strings( 'acl' ); ?>:</div>
			<div id="as3cf-acl" class="s3-value">
				<?php echo $this->get_acl_value_string( $s3object['acl'] ); ?>
			</div>
		</div>
		<?php if ( $is_downloadable ) : ?>
			<div class="misc-pub-section">
				<div class="not-copied"><?php _e( 'File does not exist on server', 'amazon-s3-and-cloudfront' ); ?></div>
				<a id="as3cf-download-action" href="<?php echo $this->get_media_action_url( 'download', $post->ID, $sendback ); ?>">
					<?php echo $this->get_media_action_strings( 'download' ); ?>
				</a>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<div class="clear"></div>
</div>

<?php if ( $is_removable || $is_copyable ) : ?>
	<div class="s3-actions">
		<?php if ( $is_removable ) : ?>
			<div class="remove-action">
				<a id="as3cf-remove-action" href="<?php echo $this->get_media_action_url( 'remove', $post->ID, $sendback ); ?>">
					<?php echo $this->get_media_action_strings( 'remove' ); ?>
				</a>
			</div>
		<?php endif; ?>
		<?php if ( $is_copyable ) : ?>
			<div class="copy-action">
				<a id="as3cf-copy-action" href="<?php echo $this->get_media_action_url( 'copy', $post->ID, $sendback ); ?>" class="button button-secondary">
					<?php echo $this->get_media_action_strings( 'copy' ); ?>
				</a>
			</div>
		<?php endif; ?>
		<div class="clear"></div>
	</div>
<?php endif; ?>
