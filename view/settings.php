<?php
/* @var \Amazon_S3_And_CloudFront|\Amazon_S3_And_CloudFront_Pro $this */
?>

<?php $this->render_view( 'settings/media' ) ?>
<?php $this->render_view( 'settings/addons' ) ?>
<?php $this->render_view( 'settings/support' ) ?>

<?php do_action( 'as3cf_after_settings' ); ?>

<?php $this->render_view( 'sidebar' ); ?>
