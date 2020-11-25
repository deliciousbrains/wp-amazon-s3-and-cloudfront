( function( $, as3cfModal ) {

	var savedSettings = {};
	var bucketNamePattern = /[^a-z0-9.-]/;

	var $body = $( 'body' );
	var $tabs = $( '.as3cf-tab' );
	var $settings = $( '.as3cf-settings' );
	var $activeTab;

	/**
	 * Return the serialized string of the settings form
	 * excluding the bucket and region inputs as they get saved via AJAX
	 *
	 * @param string tab
	 *
	 * @returns {string}
	 */
	function serializedForm( tab ) {
		return $( '#' + tab + ' .as3cf-main-settings form' ).find( 'input:not(.no-compare)' ).serialize();
	}

	/**
	 * Set checkbox
	 *
	 * @param string checkbox_wrap
	 */
	function setCheckbox( checkbox_wrap ) {
		var $switch = $activeTab.find( '#' + checkbox_wrap );
		var $checkbox = $switch.find( 'input[type=checkbox]' );

		$switch.toggleClass( 'on' ).find( 'span' ).toggleClass( 'checked' );
		var switchOn = $switch.find( 'span.on' ).hasClass( 'checked' );
		$checkbox.prop( 'checked', switchOn ).trigger( 'change' );
	}

	/**
	 * Validate custom domain
	 *
	 * @param {object} $input
	 */
	function validateCustomDomain( $input ) {
		var $error = $input.next( '.as3cf-validation-error' );
		var $submit = $( '#' + $activeTab.attr( 'id' ) + ' form button[type="submit"]' );
		var pattern = /[^a-zA-Z0-9\.\-]/;

		if ( pattern.test( $input.val() ) ) {
			$error.show();
			$submit.prop( 'disabled', true );
		} else {
			$error.hide();
			$submit.prop( 'disabled', false );
		}
	}

	/**
	 * Validate Signed URLs Key ID.
	 *
	 * @param {object} $input
	 */
	function validateSignedUrlsKeyID( $input ) {
		var $error = $input.next( '.as3cf-validation-error' );
		var $submit = $( '#' + $activeTab.attr( 'id' ) + ' form button[type="submit"]' );
		var pattern = /[^a-zA-Z0-9]/; // Letters & Numbers only at present (CloudFront).

		if ( pattern.test( $input.val() ) ) {
			$error.show();
			$submit.prop( 'disabled', true );
		} else {
			$error.hide();
			$submit.prop( 'disabled', false );
		}
	}

	/**
	 * Validate Signed URLs Key File Path.
	 *
	 * @param {object} $input
	 */
	function validateSignedUrlsKeyFilePath( $input ) {
		var $error = $input.next( '.as3cf-validation-error' );
		var $submit = $( '#' + $activeTab.attr( 'id' ) + ' form button[type="submit"]' );
		var pattern = /[^a-zA-Z0-9\.\-\\:\/ _]/;

		if ( pattern.test( $input.val() ) ) {
			$error.show();
			$submit.prop( 'disabled', true );
		} else {
			$error.hide();
			$submit.prop( 'disabled', false );
		}
	}

	/**
	 * Validate Signed URLs Object Prefix.
	 *
	 * @param {object} $input
	 */
	function validateSignedUrlsObjectPrefix( $input ) {
		var $error = $input.next( '.as3cf-validation-error' );
		var $submit = $( '#' + $activeTab.attr( 'id' ) + ' form button[type="submit"]' );
		var pattern = /[^a-zA-Z0-9\-\/ ]/;

		if ( pattern.test( $input.val() ) ) {
			$error.show();
			$submit.prop( 'disabled', true );
		} else {
			$error.hide();
			$submit.prop( 'disabled', false );
		}
	}

	as3cf.tabs = {
		defaultTab: 'media',
		/**
		 * Toggle settings tab
		 *
		 * @param string hash
		 * @param boolean persist_updated_notice
		 */
		toggle: function( hash, persist_updated_notice ) {
			hash = as3cf.tabs.sanitizeHash( hash );

			$tabs.hide();
			$activeTab = $( '#tab-' + hash );
			$activeTab.show();
			$( '.nav-tab' ).removeClass( 'nav-tab-active' );
			$( 'a.nav-tab[data-tab="' + hash + '"]' ).addClass( 'nav-tab-active' );
			$( '.as3cf-main' ).data( 'tab', hash );
			if ( $activeTab.data( 'prefix' ) ) {
				as3cfModal.prefix = $activeTab.data( 'prefix' );
			}
			if ( ! persist_updated_notice ) {
				$( '.as3cf-updated' ).removeClass( 'show' );
			}

			if ( 'support' === hash ) {
				as3cf.tabs.getDiagnosticInfo();
			}
		},

		/**
		 * Update display of diagnostic info.
		 */
		getDiagnosticInfo: function() {
			var $debugLog = $( '.debug-log-textarea' );

			$debugLog.html( as3cf.strings.get_diagnostic_info );

			var data = {
				action: 'as3cf-get-diagnostic-info',
				_nonce: as3cf.nonces.get_diagnostic_info
			};

			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				dataType: 'JSON',
				data: data,
				error: function( jqXHR, textStatus, errorThrown ) {
					$debugLog.html( errorThrown );
				},
				success: function( data, textStatus, jqXHR ) {
					if ( 'undefined' !== typeof data[ 'success' ] ) {
						$debugLog.html( data[ 'diagnostic_info' ] );
					} else {
						$debugLog.html( as3cf.strings.get_diagnostic_info_error );
						$debugLog.append( data[ 'error' ] );
					}
				}
			} );
		},

		/**
		 * Sanitize hash to ensure it references a real tab.
		 *
		 * @param string hash
		 *
		 * @return string
		 */
		sanitizeHash: function( hash ) {
			var $newTab = $( '#tab-' + hash );

			if ( 0 === $newTab.length ) {
				hash = as3cf.tabs.defaultTab;
			}

			return hash;
		}
	};

	/**
	 * Handle the bucket selection, either inline or in a modal
	 */
	as3cf.buckets = {

		/**
		 * Buckets must be at least this many characters
		 */
		validLength: 3,

		/**
		 * Process lock for setting a bucket
		 */
		bucketSelectLock: false,

		/**
		 * Load bucket list
		 *
		 * @param {boolean} [forceUpdate]
		 */
		loadList: function( forceUpdate ) {
			if ( 'undefined' === typeof forceUpdate ) {
				forceUpdate = false;
			}

			var $selectBucketForm = $( '.as3cf-bucket-container.' + as3cfModal.prefix + ' .as3cf-bucket-select' );
			var $selectBucketRegion = $selectBucketForm.find( '.bucket-select-region' );
			var $bucketList = $selectBucketForm.find( '.as3cf-bucket-list' );
			var selectedBucket = $( '#' + as3cfModal.prefix + '-bucket' ).val();

			if ( false === forceUpdate && $bucketList.find( 'li' ).length > 1 ) {
				$( '.as3cf-bucket-list a' ).removeClass( 'selected' );
				$( '.as3cf-bucket-list a[data-bucket="' + selectedBucket + '"]' ).addClass( 'selected' );

				this.scrollToSelected();
				return;
			}

			$bucketList.html( '<li class="loading">' + $bucketList.data( 'working' ) + '</li>' );

			// Stop accidental submit while reloading list.
			this.disabledButtons();

			var data = {
				action: as3cfModal.prefix + '-get-buckets',
				_nonce: window[ as3cfModal.prefix.replace( /-/g, '_' ) ].nonces.get_buckets
			};

			if ( $selectBucketRegion.val() ) {
				data[ 'region' ] = $selectBucketRegion.val();
			}

			var that = this;

			$.ajax( {
				url: ajaxurl,
				type: 'POST',
				dataType: 'JSON',
				data: data,
				error: function( jqXHR, textStatus, errorThrown ) {
					$bucketList.html( '' );
					that.showError( as3cf.strings.get_buckets_error, errorThrown, 'as3cf-bucket-select' );
				},
				success: function( data, textStatus, jqXHR ) {
					$bucketList.html( '' );

					if ( 'undefined' !== typeof data[ 'success' ] ) {
						$( '.as3cf-bucket-error' ).hide();

						if ( 0 === data[ 'buckets' ].length ) {
							$bucketList.html( '<li class="loading">' + $bucketList.data( 'nothing-found' ) + '</li>' );
						} else {
							$( data[ 'buckets' ] ).each( function( idx, bucket ) {
								var bucketClass = bucket.Name === selectedBucket ? 'selected' : '';
								$bucketList.append( '<li><a class="' + bucketClass + '" href="#" data-bucket="' + bucket.Name + '"><span class="bucket"><span class="dashicons dashicons-portfolio"></span> ' + bucket.Name + '</span><span class="spinner"></span></span></a></li>' );
							} );

							that.scrollToSelected();
							that.disabledButtons();
						}
					} else {
						that.showError( as3cf.strings.get_buckets_error, data[ 'error' ], 'as3cf-bucket-select' );
					}
				}
			} );
		},

		/**
		 * Scroll to selected bucket
		 */
		scrollToSelected: function() {
			if ( ! $( '.as3cf-bucket-list a.selected' ).length ) {
				return;
			}

			var offset = $( 'ul.as3cf-bucket-list li' ).first().position().top + 150;

			$( '.as3cf-bucket-list' ).animate( {
				scrollTop: $( 'ul.as3cf-bucket-list li a.selected' ).position().top - offset
			} );
		},

		/**
		 * Set the selected bucket in list.
		 *
		 * @param {object} $link
		 */
		setSelected: function( $link ) {
			$( '.as3cf-bucket-list a' ).removeClass( 'selected' );
			$link.addClass( 'selected' );
			$( '#' + as3cfModal.prefix + '-bucket-select-name' ).val( $link.data( 'bucket' ) );
		},

		/**
		 * Disable bucket buttons
		 */
		disabledButtons: function() {
			var $createBucketForm = $( '.as3cf-bucket-container.' + as3cfModal.prefix + ' .as3cf-bucket-create' );
			var $manualBucketForm = $( '.as3cf-bucket-container.' + as3cfModal.prefix + ' .as3cf-bucket-manual' );
			var $selectBucketForm = $( '.as3cf-bucket-container.' + as3cfModal.prefix + ' .as3cf-bucket-select' );

			if ( 0 === $createBucketForm.length && 0 === $manualBucketForm.length && 0 === $selectBucketForm.length ) {
				return;
			}

			if ( 0 < $createBucketForm.length && this.isValidName( $createBucketForm.find( '.as3cf-bucket-name' ).val() ) ) {
				$createBucketForm.find( 'button[type=submit]' ).prop( 'disabled', false );
			} else {
				$createBucketForm.find( 'button[type=submit]' ).prop( 'disabled', true );
			}

			if ( 0 < $manualBucketForm.length && this.isValidName( $manualBucketForm.find( '.as3cf-bucket-name' ).val() ) ) {
				$manualBucketForm.find( 'button[type=submit]' ).prop( 'disabled', false );
			} else {
				$manualBucketForm.find( 'button[type=submit]' ).prop( 'disabled', true );
			}

			if ( 0 < $selectBucketForm.length && 1 === $selectBucketForm.find( '.as3cf-bucket-list a.selected' ).length ) {
				$selectBucketForm.find( 'button[type=submit]' ).prop( 'disabled', false );
			} else {
				$selectBucketForm.find( 'button[type=submit]' ).prop( 'disabled', true );
			}
		},

		/**
		 * Show bucket error
		 *
		 * @param {string} title
		 * @param {string} error
		 * @param {string} [context]
		 */
		showError: function( title, error, context ) {
			var $activeView = $( '.as3cf-bucket-container' ).children( ':visible' );
			var $bucketError = $activeView.find( '.as3cf-bucket-error' );

			context = ( 'undefined' === typeof context ) ? null : context;

			if ( context && ! $activeView.hasClass( context ) ) {
				return;
			}

			$bucketError.find( 'span.title' ).html( title + ' &mdash;' );
			$bucketError.find( 'span.message' ).html( error );
			$bucketError.show();

			// Unlock setting the bucket
			this.bucketSelectLock = false;
		},

		/**
		 * Check for a valid bucket name
		 *
		 * Bucket names must be at least 3 and no more than 63 characters long.
		 * They can contain lowercase letters, numbers, periods and hyphens.
		 *
		 * @param {string} bucketName
		 *
		 * @return boolean
		 */
		isValidName: function( bucketName ) {
			if ( bucketName.length < 3 || bucketName.length > 63 ) {
				return false;
			}
			if ( true === bucketNamePattern.test( bucketName ) ) {
				return false;
			}

			return true;
		},

		/**
		 * Update invalid bucket name notice
		 *
		 * @param {string} bucketName
		 */
		updateNameNotice: function( bucketName ) {
			var message = null;

			if ( true === bucketNamePattern.test( bucketName ) ) {
				message = as3cf.strings.create_bucket_invalid_chars;
			} else if ( bucketName.length < 3 ) {
				message = as3cf.strings.create_bucket_name_short;
			} else if ( bucketName.length > 63 ) {
				message = as3cf.strings.create_bucket_name_long;
			}

			if ( message && bucketName.length > 0 ) {
				$( '.as3cf-invalid-bucket-name' ).html( message );
			} else {
				$( '.as3cf-invalid-bucket-name' ).html( '' );
			}
		}

	};

	/**
	 * Reload the page, and show the persistent updated notice.
	 *
	 * Intended for use on plugin settings page.
	 */
	as3cf.reloadUpdated = function() {
		var url = location.pathname + location.search;

		if ( ! location.search.match( /[?&]updated=/ ) ) {
			url += '&updated=1';
		}

		url += location.hash;

		location.assign( url );
	};

	/**
	 * Show the standard "Settings saved." notice if not already visible.
	 */
	as3cf.showSettingsSavedNotice = function() {
		if ( 0 < $( '#setting-error-settings_updated:visible' ).length || 0 < $( '#as3cf-settings_updated:visible' ).length ) {
			return;
		}
		var settingsUpdatedNotice = '<div id="as3cf-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>' + as3cf.strings.settings_saved + '</strong></p></div>';
		$( 'h2.nav-tab-wrapper' ).after( settingsUpdatedNotice );
		$( document ).trigger( 'wp-updates-notice-added' ); // Hack to run WP Core's makeNoticesDismissible() function.
	};

	as3cf.Settings = as3cf.Settings ? as3cf.Settings : {};

	/**
	 * The object that handles locking and unlocking the Media settings.
	 */
	as3cf.Settings.Media = {
		/**
		 * Lock settings.
		 */
		lock: function( key ) {
			$( '#as3cf-media-settings-locked-' + key ).show();
			$( '.as3cf-media-settings' ).addClass( 'locked locked-' + key );
			$( '.as3cf-media-settings.locked-' + key ).each( function() {
				$( this ).find( 'input,button' ).prop( 'disabled', true );
				$( this ).find( '.as3cf-settings-container' ).addClass( 'as3cf-locked-setting' );
				$( this ).find( 'a.as3cf-change-settings' ).hide();
			} );
		},

		/**
		 * Unlock settings.
		 */
		unlock: function( key ) {
			$( '.as3cf-media-settings.locked-' + key ).each( function() {
				$( this ).find( 'input,button' ).filter( ':not(.disabled)' ).prop( 'disabled', false );
				$( this ).find( '.as3cf-settings-container' ).removeClass( 'as3cf-locked-setting' );
				$( this ).find( 'a.as3cf-change-settings' ).show();
				$( this ).removeClass( 'locked locked-' + key );
			} );
			$( '#as3cf-media-settings-locked-' + key ).hide();
		},

		/**
		 * Settings locked?
		 */
		locked: function() {
			return $( '.as3cf-media-settings' ).hasClass( 'locked' );
		}

	};

	/**
	 * Get the link to the bucket on the AWS Console and update the DOM
	 *
	 * @returns {string}
	 */
	function setBucketLink() {
		var bucket = $( '#' + as3cfModal.prefix + '-bucket' ).val();
		var $objectPrefix = $activeTab.find( 'input[name="object-prefix"]' );
		var prefix = $objectPrefix.val();

		if ( '' !== prefix ) {
			prefix = as3cf.provider_console_url_prefix_param + encodeURIComponent( prefix );
		}

		var url = as3cf.provider_console_url + bucket + prefix;

		$( '#' + as3cfModal.prefix + '-view-bucket' ).attr( 'href', url );
	}

	/*
	 * Toggle the lost files notice
	 */
	function toggleLostFilesNotice() {
		if ( $( '#as3cf-remove-local-file' ).is( ':checked' ) && $( '#as3cf-serve-from-s3' ).is( ':not(:checked)' ) ) {
			$( '#as3cf-lost-files-notice' ).show();
		} else {
			$( '#as3cf-lost-files-notice' ).hide();
		}
	}

	/*
	 * Toggle the remove local files notice
	 */
	function toggleRemoveLocalNotice() {
		if ( $( '#as3cf-remove-local-file' ).is( ':checked' ) ) {
			$( '#as3cf-remove-local-notice' ).show();
		} else {
			$( '#as3cf-remove-local-notice' ).hide();
		}
	}

	/*
	 * Toggle the seo friendly url notice.
	 */
	function toggleSEOFriendlyURLNotice( seo_friendly ) {
		if ( true !== seo_friendly ) {
			$( '#as3cf-seo-friendly-url-notice' ).show();
		} else {
			$( '#as3cf-seo-friendly-url-notice' ).hide();
		}
	}

	/**
	 * Generate URL preview
	 */
	function generateUrlPreview() {
		$( '.as3cf-url-preview' ).html( 'Generating...' );

		var data = {
			_nonce: as3cf.nonces.get_url_preview
		};

		$.each( $( '#tab-' + as3cf.tabs.defaultTab + ' .as3cf-main-settings form' ).serializeArray(), function( i, o ) {
			var n = o.name,
				v = o.value;
			n = n.replace( '[]', '' );
			data[ n ] = undefined === data[ n ] ? v : Array.isArray( data[ n ] ) ? data[ n ].concat( v ) : [ data[ n ], v ];
		} );

		// Overwrite the save action stored in the form
		data[ 'action' ] = 'as3cf-get-url-preview';

		$.ajax( {
			url: ajaxurl,
			type: 'POST',
			dataType: 'JSON',
			data: data,
			error: function( jqXHR, textStatus, errorThrown ) {
				alert( as3cf.strings.get_url_preview_error + errorThrown );
			},
			success: function( data, textStatus, jqXHR ) {
				if ( 'undefined' !== typeof data[ 'success' ] ) {
					$( '.as3cf-url-preview' ).html( data[ 'url' ] );
					toggleSEOFriendlyURLNotice( data[ 'seo_friendly' ] );
				} else {
					alert( as3cf.strings.get_url_preview_error + data[ 'error' ] );
				}
			}
		} );
	}

	/**
	 * Update the UI with the current active tab set in the URL hash.
	 */
	function renderCurrentTab() {

		// If rendering the default tab, or a bare hash clean the hash.
		if ( '#' + as3cf.tabs.defaultTab === location.hash ) {
			location.hash = '';

			return;
		}

		as3cf.tabs.toggle( location.hash.replace( '#', '' ), true );

		$( document ).trigger( 'as3cf.tabRendered', [ location.hash.replace( '#', '' ) ] );
	}

	$( document ).ready( function() {

		// Tabs
		// --------------------
		renderCurrentTab();

		/**
		 * Set the hashchange callback to update the rendered active tab.
		 */
		window.onhashchange = function( event ) {

			// Strip the # if still on the end of the URL
			if ( 'function' === typeof history.replaceState && '#' === location.href.slice( -1 ) ) {
				history.replaceState( {}, '', location.href.slice( 0, -1 ) );
			}

			renderCurrentTab();
		};

		// Move any compatibility errors below the nav tabs
		var $navTabs = $( '.as3cf-main .nav-tab-wrapper' );
		$( '.as3cf-compatibility-notice, div.updated, div.error, div.notice' ).not( '.below-h2, .inline' ).insertAfter( $navTabs );

		// Settings
		// --------------------

		// Save the original state of the forms for comparison later
		if ( $tabs.length ) {
			$tabs.each( function( i, tab ) {
				savedSettings[ tab.id ] = serializedForm( tab.id );
			} );
		}

		// Prompt user with dialog if leaving the settings page with unsaved changes
		$( window ).on( 'beforeunload.as3cf-settings', function() {
			if ( $.isEmptyObject( savedSettings ) || as3cf.Settings.Media.locked() ) {
				return;
			}

			var tab = $activeTab.attr( 'id' );

			if ( serializedForm( tab ) !== savedSettings[ tab ] ) {
				return as3cf.strings.save_alert;
			}
		} );

		// Let the save settings submit happen as normal
		$( document ).on( 'submit', '.as3cf-main-settings form', function( e ) {

			// Disable unload warning
			$( window ).off( 'beforeunload.as3cf-settings' );
		} );

		$( '.as3cf-switch' ).on( 'click', function( e ) {
			if ( ! $( this ).hasClass( 'disabled' ) && ! $( this ).parents().hasClass( 'locked' ) ) {
				setCheckbox( $( this ).attr( 'id' ) );
			}
		} );

		$tabs.on( 'change', '.sub-toggle', function( e ) {
			var setting = $( this ).attr( 'id' );
			$( '.as3cf-setting.' + setting ).toggleClass( 'hide' );
		} );

		$( '.url-preview' ).on( 'change', 'input', function( e ) {
			generateUrlPreview();
		} );

		toggleLostFilesNotice();
		$( '#as3cf-serve-from-s3,#as3cf-remove-local-file' ).on( 'change', function( e ) {
			toggleLostFilesNotice();
		} );

		toggleRemoveLocalNotice();
		$( '#as3cf-remove-local-file' ).on( 'change', function( e ) {
			toggleRemoveLocalNotice();
		} );

		// Don't allow 'enter' key to submit form on text input settings
		$( '.as3cf-setting input[type="text"]' ).on( 'keypress', function( event ) {
			if ( 13 === event.which ) {
				event.preventDefault();

				return false;
			}
		} );

		// Show or hide Custom Domain input and Enable Signed URLs section based on custom domain toggle switch.
		$( '.as3cf-enable-delivery-domain-container' ).on( 'change', 'input[type="checkbox"]', function( e ) {
			var deliveryDomainEnabled = $( this ).is( ':checked' );
			var $deliveryDomain = $( this ).parents( '.as3cf-enable-delivery-domain-container' ).find( '.as3cf-setting.as3cf-delivery-domain' );
			$deliveryDomain.toggleClass( 'hide', ! deliveryDomainEnabled );
			var $signedUrlsEnabled = $( this ).parents( '.as3cf-enable-delivery-domain-container' ).siblings( '.as3cf-enable-signed-urls-container' );
			$signedUrlsEnabled.toggleClass( 'hide', ! deliveryDomainEnabled );
		} );

		// Re-enable submit button on domain change
		$( 'input[name="enable-delivery-domain"]' ).on( 'change', function( e ) {
			var $input = $( this );
			var $submit = $( '#' + $activeTab.attr( 'id' ) + ' form button[type="submit"]' );

			if ( '1' !== $input.val() ) {
				$submit.prop( 'disabled', false );
			} else {
				validateCustomDomain( $input.next( '.as3cf-setting' ).find( 'input[name="delivery-domain"]' ) );
			}
		} );

		// Validate custom domain
		$( 'input[name="delivery-domain"]' ).on( 'keyup', function( e ) {
			validateCustomDomain( $( this ) );
		} );

		// Show or hide Signed URLs fields based on Enable Signed URLs toggle switch.
		$( '.as3cf-enable-signed-urls-container' ).on( 'change', 'input[type="checkbox"]', function( e ) {
			var signedUrlsEnabled = $( this ).is( ':checked' );
			var $signedUrls = $( this ).parents( '.as3cf-enable-signed-urls-container' ).find( '.as3cf-setting.as3cf-signed-urls' );
			$signedUrls.toggleClass( 'hide', ! signedUrlsEnabled );
		} );

		// Validate Signed URLs Key ID.
		$( 'input[name="signed-urls-key-id"]' ).on( 'keyup', function( e ) {
			validateSignedUrlsKeyID( $( this ) );
		} );

		// Re-enable submit button on Signed URLs Key ID change
		$( 'input[name="enable-signed-urls-key-id"]' ).on( 'change', function( e ) {
			var $input = $( this );
			var $submit = $( '#' + $activeTab.attr( 'id' ) + ' form button[type="submit"]' );

			if ( '1' !== $input.val() ) {
				$submit.prop( 'disabled', false );
			} else {
				validateSignedUrlsKeyID( $input.next( '.as3cf-setting' ).find( 'input[name="signed-urls-key-id"]' ) );
			}
		} );

		// Validate Signed URLs Key File Path.
		$( 'input[name="signed-urls-key-file-path"]' ).on( 'keyup', function( e ) {
			validateSignedUrlsKeyFilePath( $( this ) );
		} );

		// Re-enable submit button on Signed URLs Key File Path change
		$( 'input[name="enable-signed-urls-key-file-path"]' ).on( 'change', function( e ) {
			var $input = $( this );
			var $submit = $( '#' + $activeTab.attr( 'id' ) + ' form button[type="submit"]' );

			if ( '1' !== $input.val() ) {
				$submit.prop( 'disabled', false );
			} else {
				validateSignedUrlsKeyFilePath( $input.next( '.as3cf-setting' ).find( 'input[name="signed-urls-key-file-path"]' ) );
			}
		} );

		// Validate Signed URLs Object Prefix.
		$( 'input[name="signed-urls-object-prefix"]' ).on( 'keyup', function( e ) {
			validateSignedUrlsObjectPrefix( $( this ) );
		} );

		// Re-enable submit button on Signed URLs Object Prefix change
		$( 'input[name="enable-signed-urls-object-prefix"]' ).on( 'change', function( e ) {
			var $input = $( this );
			var $submit = $( '#' + $activeTab.attr( 'id' ) + ' form button[type="submit"]' );

			if ( '1' !== $input.val() ) {
				$submit.prop( 'disabled', false );
			} else {
				validateSignedUrlsObjectPrefix( $input.next( '.as3cf-setting' ).find( 'input[name="signed-urls-object-prefix"]' ) );
			}
		} );

		// Change bucket link when custom path changes
		$( 'input[name="object-prefix"]' ).on( 'change', function( e ) {
			setBucketLink();
		} );

		// Bucket select
		// --------------------

		// Move bucket errors
		$( '#tab-media > .as3cf-bucket-error' ).detach().insertAfter( '.as3cf-bucket-container h3' );

		// Enable/disable change bucket's save buttons.
		as3cf.buckets.disabledButtons();

		// Bucket list refresh handler
		$body.on( 'click', '.bucket-action-refresh', function( e ) {
			e.preventDefault();
			as3cf.buckets.loadList( true );
		} );

		// Bucket list refresh on region change handler
		$body.on( 'change', '.bucket-select-region', function( e ) {
			e.preventDefault();
			as3cf.buckets.loadList( true );
		} );

		// If select bucket form is available on load, populate its list.
		if ( 0 < $( '.as3cf-bucket-container.' + as3cfModal.prefix + ' .as3cf-bucket-select' ).length ) {
			as3cf.buckets.loadList( true );
		}

		// Bucket list click handler
		$body.on( 'click', '.as3cf-bucket-list a', function( e ) {
			e.preventDefault();
			as3cf.buckets.setSelected( $( this ) );
			as3cf.buckets.disabledButtons();
		} );

		// External links click handler
		$( '.as3cf-bucket-container' ).on( 'click', 'a.js-link', function( e ) {
			e.preventDefault();
			window.open( $( this ).attr( 'href' ) );

			return false;
		} );

		// Validate bucket name on create
		$body.on( 'input keyup', '.as3cf-bucket-create .as3cf-bucket-name', function( e ) {
			var bucketName = $( this ).val();
			as3cf.buckets.updateNameNotice( bucketName );
			as3cf.buckets.disabledButtons();
		} );

		$body.on( 'input keyup', '.as3cf-bucket-manual .as3cf-bucket-name', function( e ) {
			var bucketName = $( this ).val();
			as3cf.buckets.updateNameNotice( bucketName );
			as3cf.buckets.disabledButtons();
		} );

		// Don't allow 'enter' key to submit form on text input settings
		$( '.as3cf-bucket-container input[type="text"]' ).on( 'keypress', function( event ) {
			if ( 13 === event.which ) {
				event.preventDefault();

				return false;
			}
		} );

		// Enable/Disable Block All Public Access button during setup depending on checkbox.
		$( '.as3cf-change-bucket-access-prompt' ).on( 'change', '#origin-access-identity-confirmation', function( e ) {
			$( '#block-public-access-confirmed' ).prop( 'disabled', ! $( this ).prop( 'checked' ) );
		} );

		// If there's an upgrade in progress when the page loads, ensure settings are locked.
		$( '.as3cf-media-settings.locked.locked-upgrade' ).each( function() {
			as3cf.Settings.Media.lock( 'upgrade' );
		} );
	} );

} )( jQuery, as3cfModal );
