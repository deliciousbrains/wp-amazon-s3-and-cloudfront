<h2 class="nav-tab-wrapper">
	<?php
	foreach ( $this->get_settings_tabs() as $tab => $label ) : ?>
		<a href="#<?php echo $tab; ?>" class="nav-tab js-action-link <?php echo $tab; ?>" data-tab="<?php echo $tab; ?>">
			<?php echo esc_html( $label ); ?>
		</a>
	<?php endforeach; ?>
</h2>