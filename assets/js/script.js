(function($) {

	$(document).ready(function() {

		$('.as3cf-settings').each(function() {
			var $container = $(this);
			var $bucketList = $('.as3cf-bucket-list');
			var $createBucketForm = $container.find('.as3cf-create-bucket-form');

			if($createBucketForm.length){
				var $createBucketButton = $createBucketForm.find('button'),
					origButtonText = $createBucketButton.text();

				$createBucketForm.on('submit', function(e){
					e.preventDefault();
					$bucketList.addClass('saving');
					$createBucketButton.text($createBucketButton.attr('data-working'));
					$createBucketButton.prop('disabled', true);
					var bucketName = $createBucketForm.find('input[name="bucket_name"]').val();

					var data = {
						action: 		'as3cf-create-bucket',
						bucket_name: 	bucketName,
						_nonce:			as3cf_i18n.create_bucket_nonce
					};

					$.ajax({
						url:		ajaxurl,
						type: 		'POST',
						dataType: 	'JSON',
						data: 		data,
						error: function(jqXHR, textStatus, errorThrown) {
							$createBucketButton.text(origButtonText);
							alert(as3cf_i18n.create_bucket_error + errorThrown);
						},
						success: function(data, textStatus, jqXHR) {
							$createBucketButton.text(origButtonText);
							$createBucketButton.prop('disabled', false);
							if (typeof data['success'] !== 'undefined') {
								$( '.updated' ).show();
								$('.as3cf-settings').addClass('as3cf-has-bucket');
								$('.as3cf-active-bucket').text(bucketName);
								$createBucketForm.find('input[name="bucket_name"]').val('');
								$('.as3cf-bucket-list a' ).removeClass('selected');
								loadBuckets();
								$bucketList.removeClass('saving');
							} else {
								alert(as3cf_i18n.create_bucket_error + data['error']);
							}
						}
					});
				});
			}

			var $changeBucket = $container.find('.as3cf-change-bucket');
			if($changeBucket.length){
				$changeBucket.on('click', function(e){
					e.preventDefault();
					$( '.updated' ).hide();
					$('.as3cf-settings').removeClass('as3cf-has-bucket');
					if ( $('.as3cf-active-bucket' ).html ) {
						$('.as3cf-cancel-bucket-select-wrap' ).show();
					}
					if ( $( '.as3cf-bucket-list a.selected' ).length ) {
						$( '.as3cf-bucket-list' ).scrollTop( $( '.as3cf-bucket-list a.selected' ).position().top - 50 );
					}
				});
			}

			var $refreshBuckets = $container.find('.as3cf-refresh-buckets');
			if($refreshBuckets.length){
				$refreshBuckets.on('click', function(e){
					e.preventDefault();
					loadBuckets();
				});
			}

			var $cancelChangeBucket = $container.find('.as3cf-cancel-bucket-select');
			if($cancelChangeBucket.length){
				$cancelChangeBucket.on('click', function(e){
					e.preventDefault();
					$('.as3cf-settings').addClass('as3cf-has-bucket');
				});
			}

		});

		var $bucketList = $('.as3cf-bucket-list');
		function loadBuckets() {
			$bucketList.html('<li class="loading">'+ $bucketList.attr('data-working') +'</li>');

			var data = {
				action: 'as3cf-get-buckets',
				_nonce: as3cf_i18n.get_buckets_nonce
			};

			$.ajax({
				url:		ajaxurl,
				type: 		'POST',
				dataType: 	'JSON',
				data: 		data,
				error: function(jqXHR, textStatus, errorThrown) {
					$bucketList.html('');
					alert(as3cf_i18n.get_buckets_error + errorThrown);
				},
				success: function(data, textStatus, jqXHR) {
					$bucketList.html('');
					if (typeof data['success'] !== 'undefined') {
						if(data['can_write'] === false){
							$('.as3cf-can-write-error').show();
						}

						$(data['buckets']).each(function(idx, bucket){
							var bucket_class = ( bucket.Name == data['selected'] ) ? 'selected' : '';
							$bucketList.append('<li><a class="' + bucket_class + '" href="#" data-bucket="'+ bucket.Name +'"><span class="bucket"><span class="dashicons dashicons-portfolio"></span> '+ bucket.Name +'</span><span class="spinner"></span></span></a></li>');
						});
					} else {
						alert(as3cf_i18n.get_buckets_error + data['error']);
					}
				}
			});
		}

		$bucketList.on('click', 'a', function(e){
			e.preventDefault();

			if ( $(this).hasClass('selected') ) {
				$('.as3cf-settings').addClass('as3cf-has-bucket');
				return;
			}

			var bucket = this;
			$('.as3cf-bucket-list a' ).removeClass('selected');
			$(bucket).addClass('selected');

			$bucketList.addClass('saving');
			$(bucket).find('.spinner').show();
			var bucketName = $(bucket).attr('data-bucket');

			var data = {
				action: 'as3cf-save-bucket',
				bucket_name: bucketName,
				_nonce: as3cf_i18n.save_bucket_nonce
			};

			$.ajax({
				url:		ajaxurl,
				type: 		'POST',
				dataType: 	'JSON',
				data: 		data,
				error: function(jqXHR, textStatus, errorThrown) {
					$bucketList.removeClass('saving');
					alert(as3cf_i18n.save_bucket_error + errorThrown);
				},
				success: function(data, textStatus, jqXHR) {
					$(bucket).find('.spinner').hide();
					$bucketList.removeClass('saving');
					if (typeof data['success'] !== 'undefined') {
						$('.as3cf-settings').addClass('as3cf-has-bucket');
						$('.as3cf-active-bucket').text(bucketName);
						$( '.updated' ).show();
					} else {
						alert(as3cf_i18n.save_bucket_error + data['error']);
					}
				}
			});
		});

		$('.as3cf-settings').on('click', 'input[type="checkbox"]', function(e){
			var checkbox = $(this ).attr('id');
			$('.as3cf-setting.' + checkbox ).toggleClass('hide');
		});

	});

})(jQuery);