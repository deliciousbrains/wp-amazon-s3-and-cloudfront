(function( $ ) {
	var $body = $( 'body' );

	var as3cf = as3cf || {};

	/**
	 * Handle changes to the selected Delivery Provider.
	 */
	as3cf.deliveryProvider = {
		changed: function() {
			var provider = $( 'input[name="delivery-provider"]:checked' ).val();
			var parent = $( 'input[name="delivery-provider"]:checked' ).attr( 'data-delivery-provider-parent' );

			// De-select all providers.
			$( '.as3cf-delivery-provider' ).each( function() {
				$( this ).removeClass( 'selected' );
			} );

			// De-select all sub option containers.
			$( '.as3cf-delivery-provider-sub-option-container' ).each( function() {
				$( this ).removeClass( 'selected' );
			} );

			// Select chosen provider.
			$( '.as3cf-delivery-provider[data-delivery-provider="' + provider + '"]' ).each( function() {
				$( this ).addClass( 'selected' );
			} );

			if ( 0 < parent.length ) {
				// If switching sub options, re-select container and parent too.
				$( '.as3cf-delivery-provider-sub-option-container[data-delivery-provider="' + parent + '"]' ).each( function() {
					$( this ).addClass( 'selected' );
				} );
				$( '.as3cf-delivery-provider[data-delivery-provider="' + parent + '"]' ).each( function() {
					$( this ).addClass( 'selected' );
				} );
			} else {
				// Switching top level provider, de-select any sub option container...
				$( '.as3cf-delivery-provider-sub-option-container' ).each( function() {
					$( this ).removeClass( 'selected' );
				} );

				// ... and select the dummy hidden radio button.
				$( 'input#as3cf-delivery-provider-dummy-parent[name="delivery-provider-sub-option-parent"]' ).each( function() {
					$( this ).prop( 'checked', true );
					$( this ).trigger( 'change' );
				} );
			}
		},

		subOptionParentChanged: function() {
			var parent = $( 'input[name="delivery-provider-sub-option-parent"]:checked' ).val();

			// De-select all other top level options.
			$( '.as3cf-delivery-provider-option' ).each( function() {
				$( this ).removeClass( 'selected' );
			} );

			// Select chosen sub option container.
			$( '.as3cf-delivery-provider-sub-option-container[data-delivery-provider="' + parent + '"]' ).each( function() {
				$( this ).addClass( 'selected' );
			} );

			// Select chosen sub option parent.
			$( '.as3cf-delivery-provider-option[data-delivery-provider="' + parent + '"]' ).each( function() {
				$( this ).addClass( 'selected' );
			} );

			// Find default sub option to select.
			$( 'input[data-delivery-provider-parent="' + parent + '"]' ).first().each( function() {
				$( this ).prop( 'checked', true );
				$( this ).trigger( 'change' );
			} );
		}
	};

	$( document ).ready( function() {
		// Switch displayed delivery provider content.
		$body.on( 'change', 'input[name="delivery-provider"]', function( e ) {
			e.preventDefault();
			as3cf.deliveryProvider.changed();
		} );
		$body.on( 'change', 'input[name="delivery-provider-sub-option-parent"]', function( e ) {
			e.preventDefault();
			as3cf.deliveryProvider.subOptionParentChanged();
		} );
	} );

})( jQuery );
