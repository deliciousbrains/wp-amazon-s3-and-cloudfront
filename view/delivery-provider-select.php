<?php
/* @var Amazon_S3_And_CloudFront|Amazon_S3_And_CloudFront_Pro $this */
/* @var \DeliciousBrains\WP_Offload_Media\Providers\Storage\Storage_Provider $selected_storage_provider */
$current_delivery_provider = $this->get_delivery_provider();
// TODO: Do we want AS3CF_DELIVERY_PROVIDER?
$delivery_provider_defined              = (bool) defined( 'AS3CF_DELIVERY_PROVIDER' ) || $this->get_defined_setting( 'delivery-provider', false );
$delivery_providers                     = $this->get_provider_classes( 'delivery' );
$delivery_provider_service_name_defined = (bool) $this->get_defined_setting( 'delivery-provider-service-name', false );
?>

<div class="as3cf-content as3cf-delivery-provider-select">
	<?php
	$back_args = array();
	if ( ! empty( $_GET['prev_action'] ) ) {
		$back_args['action'] = $_GET['prev_action'];
	}
	echo '<a href="' . $this->get_plugin_page_url( $back_args ) . '">' . __( '&laquo;&nbsp;Back', 'amazon-s3-and-cloudfront' ) . '</a>';
	?>
	<h3><?php _e( 'How would you like to deliver your media?', 'amazon-s3-and-cloudfront' ) ?></h3>

	<div class="as3cf-delivery-provider-select-options">
		<?php
		$delivery_providers_tree              = array();
		$last_top_level_delivery_provider_key = '';

		/* @var \DeliciousBrains\WP_Offload_Media\Providers\Delivery\Delivery_Provider $delivery_provider_class */
		foreach ( $delivery_providers as $delivery_provider_key => $delivery_provider_class ) {
			/* @var \DeliciousBrains\WP_Offload_Media\Providers\Delivery\Delivery_Provider $delivery_provider */
			$delivery_provider = new $delivery_provider_class( $this );

			if ( ! $delivery_provider->supports_storage( $selected_storage_provider ) ) {
				continue;
			}

			$delivery_provider_parent_key          = '';
			$delivery_provider_selected            = $delivery_provider_key === $current_delivery_provider->get_provider_key_name();
			$delivery_provider_sub_option          = 'DeliciousBrains\WP_Offload_Media\Providers\Delivery\Delivery_Provider' !== get_parent_class( $delivery_provider );
			$delivery_provider_sub_option_selected = false;

			// Allow linking of top level provider with sub options, and protect against meddled with provider classes array.
			if ( empty( $last_top_level_delivery_provider_key ) || ! $delivery_provider_sub_option ) {
				$last_top_level_delivery_provider_key = $delivery_provider_key;
				$delivery_provider_sub_option         = false;

				// Has a sub option of this provider been selected?
				if ( ! $delivery_provider_selected && get_class( $delivery_provider ) === get_parent_class( $current_delivery_provider ) ) {
					$delivery_provider_selected            = true;
					$delivery_provider_sub_option_selected = true;
				}
			}

			/*
			 * If no sub option has been selected we may still need to decorate a sub option's parent as such...
			 * If last top level provider has correct class for this sub option's parent, confirm it as such.
			 * If last top level provider does not have correct class, hoist this sub option as it might just be a "normal" subclass of another.
			 */
			if ( ! empty( $last_top_level_delivery_provider_key ) && $delivery_provider_sub_option ) {
				if ( get_parent_class( $delivery_provider ) === $delivery_providers_tree[ $last_top_level_delivery_provider_key ]['delivery_provider_class'] ) {
					$delivery_provider_parent_key = $last_top_level_delivery_provider_key;

					// Update the parent.
					$delivery_providers_tree[ $last_top_level_delivery_provider_key ]['delivery_provider_sub_option_container'] = true;
				} else {
					$last_top_level_delivery_provider_key = $delivery_provider_key;
					$delivery_provider_sub_option         = false;

					// Has a sub option of this provider been selected?
					if ( ! $delivery_provider_selected && get_class( $delivery_provider ) === get_parent_class( $current_delivery_provider ) ) {
						$delivery_provider_sub_option_selected = true;
					}
				}
			}

			$delivery_providers_tree[ $delivery_provider_key ] = array(
				'delivery_provider_class'                => $delivery_provider_class,
				'delivery_provider_sub_option'           => $delivery_provider_sub_option,
				'delivery_provider_sub_option_container' => $delivery_provider_sub_option_selected,
				'delivery_provider_selected'             => $delivery_provider_selected,
				'delivery_provider_parent_key'           => $delivery_provider_parent_key,
			);
		}

		$delivery_provider_sub_option_group_started = false;
		foreach ( $delivery_providers_tree as $delivery_provider_key => $delivery_provider_details ) {
			/** @var bool $delivery_provider_sub_option_container */

			extract( $delivery_provider_details );

			/* @var \DeliciousBrains\WP_Offload_Media\Providers\Delivery\Delivery_Provider $delivery_provider */
			$delivery_provider = new $delivery_provider_class( $this );

			$delivery_provider_data = ' data-delivery-provider="' . $delivery_provider_key . '"';
			$delivery_provider_data .= ' data-delivery-provider-parent="' . $delivery_provider_parent_key . '"';

			// Finish of previous sub options group if need be.
			if ( ! $delivery_provider_sub_option && $delivery_provider_sub_option_group_started ) {
				$delivery_provider_sub_option_group_started = false;
				echo '</div>';
			}

			if ( $delivery_provider_sub_option_container ) {
				$container_classes = array( 'as3cf-delivery-provider-sub-option-container' );

				if ( $delivery_provider_selected ) {
					$container_classes[] = 'selected';
				}

				$delivery_provider_sub_option_group_started = true;
				echo '<div class="' . implode( ' ', $container_classes ) . '"' . $delivery_provider_data . '>';
			}
			?>
			<p class="as3cf-delivery-provider <?php echo $delivery_provider_sub_option ? 'as3cf-delivery-provider-sub-option' : 'as3cf-delivery-provider-option';
			echo $delivery_provider_selected ? ' selected' : ''; ?>"
				<?php echo $delivery_provider_data ?>
			>
				<input
					type="radio"
					name="<?php echo $delivery_provider_sub_option_container ? 'delivery-provider-sub-option-parent' : 'delivery-provider'; ?>"
					id="as3cf-delivery-provider-<?php echo $delivery_provider_key; ?>"
					value="<?php echo $delivery_provider_key; ?>"
					<?php
					echo $delivery_provider_selected ? ' checked="checked"' : '';
					echo ( ! $delivery_provider_selected && $delivery_provider_defined ) ? ' disabled="disabled"' : '';
					echo $delivery_provider_data;
					?>
				>
				<?php
				if ( $delivery_provider_selected && $delivery_provider_defined ) {
					echo '<span class="as3cf-defined-in-config">' . __( 'defined in wp-config.php', 'amazon-s3-and-cloudfront' ) . '</span>';
				}
				?>
				<label for="as3cf-delivery-provider-<?php echo $delivery_provider_key; ?>">
					<?php
					$desc = '<strong>' . $delivery_provider->get_provider_service_name( false ) . '</strong>';

					if ( ! $delivery_provider_sub_option && ! empty( $delivery_provider->features_description() ) ) {
						$desc .= '&nbsp;(' . $delivery_provider->features_description() . ')';
					}

					echo $desc;
					?>
				</label>
				<?php
				if ( $delivery_provider->provider_service_name_override_allowed() ) {
					?>
					<span class="as3cf-setting">
					<?php
					if ( $delivery_provider_selected && $delivery_provider_service_name_defined ) {
						echo '<span class="as3cf-defined-in-config">' . __( 'defined in wp-config.php', 'amazon-s3-and-cloudfront' ) . '</span>';
					}
					?>
					<label for="as3cf-delivery-provider-service-name-<?php echo $delivery_provider_key; ?>">
						<?php
						echo '<strong>' . __( 'CDN Name', 'amazon-s3-and-cloudfront' ) . '</strong>';
						?>
						<input
							type="text"
							name="delivery-provider-service-name"
							id="as3cf-delivery-provider-service-name-<?php echo $delivery_provider_key; ?>"
							value="<?php echo stripslashes( esc_attr( $this->get_setting( 'delivery-provider-service-name' ) ) ); ?>"
							size="30"
							<?php echo $delivery_provider_service_name_defined ? ' disabled="disabled"' : ''; ?>
						/>
					</label>
					</span>
					<?php
				}
				?>
			</p>
			<?php
		} // Delivery_Providers

		// Finish of previous sub options group if need be.
		if ( $delivery_provider_sub_option_group_started ) {
			$delivery_provider_sub_option_group_started = false;
			echo '</div>';
		}
		?>
	</div>
	<input
		type="radio"
		name="delivery-provider-sub-option-parent"
		id="as3cf-delivery-provider-dummy-parent"
		value="dummy-parent"
		style="display: none;"
		disabled="disabled"
	>
	<p class="actions">
		<button type="submit" class="button button-primary"><?php echo empty( $delivery_provider_defined ) ? __( 'Save Delivery Provider', 'amazon-s3-and-cloudfront' ) : __( 'Next', 'amazon-s3-and-cloudfront' ); ?></button>
	</p>
</div>
