<div class="aws-content as3cf-error">

	<div class="error">
		<p><?php
			if ( isset( $error_message ) ) {
				echo $error_message;
			} else if ( isset( $error ) ) {
				echo $error->get_error_message();
			}
			?>
		</p>
	</div>

</div>