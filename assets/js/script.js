(function($) {

	$(document).ready(function() {

		$('.as3cf-settings').each(function() {
			var $container = $(this);

			$('select.bucket', $container).change(function() {
				var $select = $(this);

				if ($select.val() !== 'new') {
					return;
				}

				var error_func = function(jqXHR, textStatus, errorThrown) {
					alert(as3cf_i18n.create_bucket_error + errorThrown);
					$select[0].selectedIndex = 0;
					console.log( jqXHR );
					console.log( textStatus );
					console.log( errorThrown );
				};

				var success_func = function(data, textStatus, jqXHR) {
					if (typeof data['success'] !== 'undefined') {
						var opt = document.createElement('option');
						opt.value = opt.innerHTML = bucket_name;
						var inserted_at_position = 0;
						$('option', $select).each(function() {
							// For some reason, no error occurs when
							// adding a bucket you've already added
							if ($(this).val() == bucket_name) {
								return false;
							}
							if ($(this).val() > bucket_name) {
								$(opt).insertBefore($(this));
								return false;
							}
							inserted_at_position = inserted_at_position + 1;
						});
						$select[0].selectedIndex = inserted_at_position;

						// If they decided to create a new bucket before refreshing
						// the page, we need another nonce
						as3cf_i18n.create_bucket_nonce = data['_nonce'];
					}
					else {
						alert(as3cf_i18n.create_bucket_error + data['error']);
						$select[0].selectedIndex = 0;
					}
				};

				var bucket_name = window.prompt(as3cf_i18n.create_bucket_prompt);
				if (!bucket_name) {
					$select[0].selectedIndex = 0;
					return;
				}

				var data = {
					action: 		'as3cf-create-bucket',
					bucket_name: 	bucket_name,
					_nonce:			as3cf_i18n.create_bucket_nonce
				};

				$.ajax({
					url:		ajaxurl,
					type: 		'POST',
					dataType: 	'JSON',
					success: 	success_func,
					error: 		error_func,
					data: 		data
				});
			});

		});

	});

})(jQuery);