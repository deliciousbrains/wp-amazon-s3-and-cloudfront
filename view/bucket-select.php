<?php
/* @var \Amazon_S3_And_CloudFront|\Amazon_S3_And_CloudFront_Pro $this */
$provider         = $this->get_provider();
$provider_regions = $provider->get_regions();
$region_required  = $provider->region_required();
?>

<div class="as3cf-bucket-container <?php echo $prefix; ?>">
	<div class="as3cf-bucket-manual">
		<h3 data-modal-title="<?php _e( 'Change bucket', 'amazon-s3-and-cloudfront' ); ?>"><?php _e( 'What bucket would you like to use?', 'amazon-s3-and-cloudfront' ); ?></h3>
		<form method="post" class="as3cf-manual-save-bucket-form">
			<table class="form-table">
				<?php
				$this->render_view( 'provider-setting',
					array(
						'prefix'   => $prefix,
						'tr_class' => "{$prefix}-provider-setting",
					)
				);
				?>
				<?php if ( defined( 'AS3CF_REGION' ) || true === $region_required ) { ?>
					<tr>
						<td>
							<?php _e( 'Region:', 'amazon-s3-and-cloudfront' ); ?>
						</td>
						<td>
							<?php
							if ( ! defined( 'AS3CF_REGION' ) && false === $this->get_defined_setting( 'region', false ) ) { ?>
								<select id="<?php echo $prefix; ?>-bucket-manual-region" class="bucket-manual-region" name="region_name">
									<?php foreach ( $provider_regions as $value => $label ) {
										$selected = ( $value === $selected_region ) ? ' selected="selected"' : '';
										?>
										<option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
									<?php } ?>
								</select>
							<?php } else {
								$region      = defined( 'AS3CF_REGION' ) ? AS3CF_REGION : $this->get_defined_setting( 'region' );
								$region_name = isset( $provider_regions[ $region ] ) ? $provider_regions[ $region ] : $region;
								printf( __( '%s (defined in wp-config.php)', 'amazon-s3-and-cloudfront' ), $region_name );
							} ?>
						</td>
					</tr>
				<?php } ?>
				<tr>
					<td>
						<?php _e( 'Bucket:', 'amazon-s3-and-cloudfront' ); ?>
					</td>
					<td>
						<input type="text" id="<?php echo $prefix; ?>-bucket-manual-name" class="as3cf-bucket-name" name="bucket_name" placeholder="<?php _e( 'Existing bucket name', 'amazon-s3-and-cloudfront' ); ?>" value="<?php echo $selected_bucket; ?>">
					</td>
				</tr>
			</table>
			<p class="bucket-actions actions manual">
				<button id="<?php echo $prefix; ?>-bucket-manual-save" type="submit" class="bucket-action-save button button-primary" data-working="<?php _e( 'Saving...', 'amazon-s3-and-cloudfront' ); ?>"><?php _e( 'Save Bucket', 'amazon-s3-and-cloudfront' ); ?></button>
				<span><a href="#" id="<?php echo $prefix; ?>-bucket-action-browse" class="bucket-action-browse"><?php _e( 'Browse existing buckets', 'amazon-s3-and-cloudfront' ); ?></a></span>
				<span><a href="#" id="<?php echo $prefix; ?>-bucket-action-create" class="bucket-action-create"><?php _e( 'Create new bucket', 'amazon-s3-and-cloudfront' ); ?></a></span>
			</p>
			<p class="bucket-actions actions select">
				<button id="<?php echo $prefix; ?>-bucket-select-save" type="submit" class="bucket-action-save button button-primary" data-working="<?php _e( 'Saving...', 'amazon-s3-and-cloudfront' ); ?>"><?php _e( 'Save Bucket', 'amazon-s3-and-cloudfront' ); ?></button>
				<span><a href="#" class="bucket-action-cancel"><?php _e( 'Cancel', 'amazon-s3-and-cloudfront' ); ?></a></span>
			</p>
		</form>
	</div>
	<div class="as3cf-bucket-select">
		<h3><?php _e( 'Select bucket', 'amazon-s3-and-cloudfront' ); ?></h3>
		<table class="form-table">
			<?php
			$this->render_view( 'provider-setting',
				array(
					'prefix'   => $prefix,
					'tr_class' => "{$prefix}-provider-setting",
				)
			);
			?>
		</table>
		<?php if ( defined( 'AS3CF_REGION' ) || false !== $this->get_defined_setting( 'region', false ) || true === $region_required ) { ?>
			<table class="form-table">
				<tr>
					<td>
						<?php _e( 'Region:', 'amazon-s3-and-cloudfront' ); ?>
					</td>
					<td>
						<?php
						if ( ! defined( 'AS3CF_REGION' ) && false === $this->get_defined_setting( 'region', false ) ) { ?>
							<select id="<?php echo $prefix; ?>-bucket-select-region" class="bucket-select-region" name="region_name">
								<?php foreach ( $provider_regions as $value => $label ) {
									$selected = ( $value === $selected_region ) ? ' selected="selected"' : '';
									?>
									<option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
								<?php } ?>
							</select>
						<?php } else {
							$region      = defined( 'AS3CF_REGION' ) ? AS3CF_REGION : $this->get_defined_setting( 'region' );
							$region_name = isset( $provider_regions[ $region ] ) ? $provider_regions[ $region ] : $region;
							printf( __( '%s (defined in wp-config.php)', 'amazon-s3-and-cloudfront' ), $region_name );
						} ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php _e( 'Bucket:', 'amazon-s3-and-cloudfront' ); ?>
					</td>
					<td>
						<ul class="as3cf-bucket-list" data-working="<?php _e( 'Loading...', 'amazon-s3-and-cloudfront' ); ?>" data-nothing-found="<?php _e( 'Nothing found', 'amazon-s3-and-cloudfront' ); ?>"></ul>
					</td>
				</tr>
			</table>
		<?php } else { ?>
			<ul class="as3cf-bucket-list" data-working="<?php _e( 'Loading...', 'amazon-s3-and-cloudfront' ); ?>" data-nothing-found="<?php _e( 'Nothing found', 'amazon-s3-and-cloudfront' ); ?>"></ul>
		<?php } ?>
		<p class="bucket-actions actions manual">
			<span><a href="#" class="bucket-action-cancel"><?php _e( 'Cancel', 'amazon-s3-and-cloudfront' ); ?></a></span>
			<span class="right"><a href="#" class="bucket-action-refresh"><?php _e( 'Refresh', 'amazon-s3-and-cloudfront' ); ?></a></span>
		</p>
		<p class="bucket-actions actions select">
			<span><a href="#" id="<?php echo $prefix; ?>-bucket-action-manual" class="bucket-action-manual"><?php _e( 'Enter bucket name', 'amazon-s3-and-cloudfront' ); ?></a></span>
			<span><a href="#" id="<?php echo $prefix; ?>-bucket-action-create" class="bucket-action-create"><?php _e( 'Create new bucket', 'amazon-s3-and-cloudfront' ); ?></a></span>
			<span class="right"><a href="#" class="bucket-action-refresh"><?php _e( 'Refresh', 'amazon-s3-and-cloudfront' ); ?></a></span>
		</p>
	</div>
	<div class="as3cf-bucket-create">
		<h3><?php _e( 'Create new bucket', 'amazon-s3-and-cloudfront' ); ?></h3>
		<form method="post" class="as3cf-create-bucket-form">
			<?php wp_nonce_field( 'as3cf-save-settings' ) ?>
			<table class="form-table">
				<?php
				$this->render_view( 'provider-setting',
					array(
						'prefix'   => $prefix,
						'tr_class' => "{$prefix}-provider-setting",
					)
				);
				?>
				<tr>
					<td>
						<?php _e( 'Region:', 'amazon-s3-and-cloudfront' ); ?>
					</td>
					<td>
						<?php
						if ( ! defined( 'AS3CF_REGION' ) && false === $this->get_defined_setting( 'region', false ) ) { ?>
							<select id="<?php echo $prefix; ?>-bucket-create-region" class="bucket-create-region" name="region_name">
								<?php foreach ( $provider_regions as $value => $label ) {
									$selected = ( $value === $selected_region ) ? ' selected="selected"' : '';
									?>
									<option value="<?php echo $value; ?>"<?php echo $selected; ?>><?php echo $label; ?></option>
								<?php } ?>
							</select>
						<?php } else {
							$region      = defined( 'AS3CF_REGION' ) ? AS3CF_REGION : $this->get_defined_setting( 'region' );
							$region_name = isset( $provider_regions[ $region ] ) ? $provider_regions[ $region ] : $region;
							printf( __( '%s (defined in wp-config.php)', 'amazon-s3-and-cloudfront' ), $region_name );
						} ?>
					</td>
				</tr>
				<tr>
					<td>
						<?php _e( 'Bucket:', 'amazon-s3-and-cloudfront' ); ?>
					</td>
					<td>
						<input type="text" id="<?php echo $prefix; ?>-create-bucket-name" class="as3cf-bucket-name" name="bucket_name" placeholder="<?php _e( 'New bucket name', 'amazon-s3-and-cloudfront' ); ?>">
						<p class="as3cf-invalid-bucket-name"></p>
					</td>
				</tr>
			</table>
			<p class="bucket-actions actions">
				<button id="<?php echo $prefix; ?>-bucket-create" type="submit" class="button button-primary" data-working="<?php _e( 'Creating...', 'amazon-s3-and-cloudfront' ); ?>"><?php _e( 'Create New Bucket', 'amazon-s3-and-cloudfront' ); ?></button>
				<span><a href="#" class="bucket-action-cancel"><?php _e( 'Cancel', 'amazon-s3-and-cloudfront' ); ?></a></span>
			</p>
		</form>
	</div>
</div>