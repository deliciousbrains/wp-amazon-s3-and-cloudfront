<?php
/* @var \Amazon_S3_And_CloudFront|\Amazon_S3_And_CloudFront_Pro $this */
$provider         = $this->get_storage_provider();
$provider_regions = $provider->get_regions();
$region_required  = $provider->region_required();
$bucket_defined   = defined( strtoupper( str_replace( '-', '_', $prefix ) . '_BUCKET' ) ) ? true : (bool) $this->get_defined_setting( 'bucket', false );

$bucket_mode = empty( $_GET['bucket_mode'] ) || $bucket_defined ? 'manual' : $_GET['bucket_mode'];
$bucket_mode = in_array( $bucket_mode, array( 'manual', 'select', 'create' ) ) ? $bucket_mode : 'manual';

$mode_args = array(
	'action' => 'change-bucket',
);

if ( ! empty( $_GET['prev_action'] ) ) {
	$mode_args['prev_action'] = $_GET['prev_action'];
}

if ( ! empty( $_GET['orig_provider'] ) ) {
	$mode_args['orig_provider'] = $_GET['orig_provider'];
}

$manual_mode = $this->get_plugin_page_url( array_merge( $mode_args, array( 'bucket_mode' => 'manual' ) ) );
$select_mode = $this->get_plugin_page_url( array_merge( $mode_args, array( 'bucket_mode' => 'select' ) ) );
$create_mode = $this->get_plugin_page_url( array_merge( $mode_args, array( 'bucket_mode' => 'create' ) ) );
?>

<div class="as3cf-bucket-container <?php echo $prefix; ?>">
	<?php
	if ( ! $this->get_setting( 'bucket' ) || ( ! empty( $_GET['action'] ) && 'change-bucket' === $_GET['action'] ) || ! empty( $_GET['prev_action'] ) ) {
		$back_args = $this->get_setting( 'bucket' ) ? array() : array( 'action' => 'change-provider' );
		if ( empty( $back_args['action'] ) && ! empty( $_GET['prev_action'] ) ) {
			$back_args['action'] = $_GET['prev_action'];

			if ( ! empty( $_GET['orig_provider'] ) ) {
				$back_args['orig_provider'] = $_GET['orig_provider'];
			}
		}
		echo '<a href="' . $this->get_plugin_page_url( $back_args ) . '">' . __( '&laquo;&nbsp;Back', 'amazon-s3-and-cloudfront' ) . '</a>';
	}

	if ( 'manual' === $bucket_mode ) {
		?>
		<div class="as3cf-bucket-manual">
			<h3><?php _e( 'What bucket would you like to use?', 'amazon-s3-and-cloudfront' ); ?></h3>
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
						<?php
						$disabled = '';
						if ( $bucket_defined ) {
							$disabled = ' disabled="disabled"';
							echo '<span class="as3cf-defined-in-config">' . __( 'defined in wp-config.php', 'amazon-s3-and-cloudfront' ) . '</span>';
						}
						?>
						<input
							type="text"
							id="<?php echo $prefix; ?>-bucket-manual-name"
							class="as3cf-bucket-name"
							name="bucket_name"
							placeholder="<?php _e( 'Existing bucket name', 'amazon-s3-and-cloudfront' ); ?>"
							value="<?php echo $selected_bucket; ?>"
							<?php echo $disabled; ?>
						>
						<p class="as3cf-invalid-bucket-name"></p>
					</td>
				</tr>
			</table>
			<p class="bucket-actions actions manual">
				<button id="<?php echo $prefix; ?>-bucket-manual-save" type="submit" class="bucket-action-save button button-primary"><?php $bucket_defined ? _e( 'Next', 'amazon-s3-and-cloudfront' ) : _e( 'Save Bucket Setting', 'amazon-s3-and-cloudfront' ); ?></button>
				<?php if ( ! $bucket_defined ) { ?>
					<span><a href="<?php echo $select_mode; ?>" id="<?php echo $prefix; ?>-bucket-action-browse" class="bucket-action-browse"><?php _e( 'Browse existing buckets', 'amazon-s3-and-cloudfront' ); ?></a></span>
					<span><a href="<?php echo $create_mode; ?>" id="<?php echo $prefix; ?>-bucket-action-create" class="bucket-action-create"><?php _e( 'Create new bucket', 'amazon-s3-and-cloudfront' ); ?></a></span>
				<?php } ?>
			</p>
		</div>
	<?php } elseif ( 'select' === $bucket_mode ) { ?>
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
			<input id="<?php echo $prefix; ?>-bucket-select-name" type="hidden" class="no-compare" name="bucket_name" value="<?php echo esc_attr( $selected_bucket ); ?>">
			<p class="bucket-actions actions select">
				<button id="<?php echo $prefix; ?>-bucket-select-save" type="submit" class="bucket-action-save button button-primary"><?php _e( 'Save Selected Bucket', 'amazon-s3-and-cloudfront' ); ?></button>
				<span><a href="<?php echo $manual_mode; ?>" id="<?php echo $prefix; ?>-bucket-action-manual" class="bucket-action-manual"><?php _e( 'Enter bucket name', 'amazon-s3-and-cloudfront' ); ?></a></span>
				<span><a href="<?php echo $create_mode; ?>" id="<?php echo $prefix; ?>-bucket-action-create" class="bucket-action-create"><?php _e( 'Create new bucket', 'amazon-s3-and-cloudfront' ); ?></a></span>
				<span><a href="#" class="bucket-action-refresh"><?php _e( 'Refresh', 'amazon-s3-and-cloudfront' ); ?></a></span>
			</p>
		</div>
	<?php } elseif ( 'create' === $bucket_mode ) { ?>
		<div class="as3cf-bucket-create">
			<h3><?php _e( 'Create new bucket', 'amazon-s3-and-cloudfront' ); ?></h3>
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
						if ( ! defined( 'AS3CF_REGION' ) && false === $this->get_defined_setting( 'region', false ) ) {
							$selected_region = $provider->is_region_valid( $selected_region ) ? $selected_region : $provider->get_default_region();
							?>
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
			<p class="bucket-actions actions create">
				<button id="<?php echo $prefix; ?>-bucket-create" type="submit" class="button button-primary"><?php _e( 'Create New Bucket', 'amazon-s3-and-cloudfront' ); ?></button>
				<span><a href="<?php echo $select_mode; ?>" id="<?php echo $prefix; ?>-bucket-action-browse" class="bucket-action-browse"><?php _e( 'Browse existing buckets', 'amazon-s3-and-cloudfront' ); ?></a></span>
				<span><a href="<?php echo $manual_mode; ?>" id="<?php echo $prefix; ?>-bucket-action-manual" class="bucket-action-manual"><?php _e( 'Enter bucket name', 'amazon-s3-and-cloudfront' ); ?></a></span>
			</p>
		</div>
	<?php } ?>
</div>