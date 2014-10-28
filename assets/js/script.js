(function($) {

	$(document).ready(function() {

		$('.as3cf-settings').each(function() {
			var $container = $(this);

			if(!$container.hasClass('as3cf-has-bucket')){
				loadBuckets();
			}

			var $createBucketForm = $container.find('.as3cf-create-bucket-form');
			if($createBucketForm.length){
				var $createBucketButton = $createBucketForm.find('button'),
					origButtonText = $createBucketButton.text();

				$createBucketForm.on('submit', function(e){
					e.preventDefault();
					$createBucketButton.text($createBucketButton.attr('data-working'));

					var data = {
						action: 		'as3cf-create-bucket',
						bucket_name: 	$createBucketForm.find('input[name="bucket_name"]').val(),
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
							if (typeof data['success'] !== 'undefined') {
								$('.as3cf-settings').addClass('as3cf-has-bucket');
							} else {
								alert(as3cf_i18n.create_bucket_error + data['error']);
							}
						}
					});
				});
			}

		});

		function loadBuckets() {
			var $bucketList = $('.as3cf-bucket-list');
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
							$bucketList.append('<li><a href="#" data-bucket="'+ bucket.Name +'"><span class="dashicons dashicons-portfolio"></span> '+ bucket.Name +'</a></li>');
						});
					} else {
						alert(as3cf_i18n.get_buckets_error + data['error']);
					}
				}
			});

			$bucketList.on('click', 'a', function(e){
				e.preventDefault();
				$bucketList.addClass('saving');

				var data = {
					action: 'as3cf-save-bucket',
					bucket_name: $(this).attr('data-bucket'),
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
						$bucketList.removeClass('saving');
						if (typeof data['success'] !== 'undefined') {
							$('.as3cf-settings').addClass('as3cf-has-bucket');
						} else {
							alert(as3cf_i18n.save_bucket_error + data['error']);
						}
					}
				});
			});
		}

	});

})(jQuery);