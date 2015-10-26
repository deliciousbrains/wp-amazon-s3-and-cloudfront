<?php
$type        = ( isset( $type ) ) ? $type : 'notice-info';
$dismissible = ( isset( $dismissible ) ) ? $dismissible : false;
$inline      = ( isset( $inline ) ) ? $inline : false;
$id          = ( isset( $id ) ) ? 'id="' . $id . '"' : '';
$style       = ( isset( $style ) ) ? $style : '';
?>
<div <?php echo $id; ?> class="notice <?php echo $type; ?><?php echo ( $dismissible ) ? ' is-dismissible' : ''; ?> as3cf-notice <?php echo ( $inline ) ? ' inline' : ''; ?>" style="<?php echo $style; ?>">
	<p><?php echo $message; // xss ok ?></p>
</div>