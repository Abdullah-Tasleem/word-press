jQuery(function ($) {

	const $wrapper   = $('#wc-apf-wrapper');
	const $products  = $('.woocommerce .products');

	/* helper to collect current selections */
	const collect = () => {

		let data = {
			action : 'wc_apf_filter',
			nonce  : WC_APF.nonce
		};

		// each block
		$wrapper.find('.wc-apf-block').each(function () {

			let slug = $(this).data('filter');

			switch (slug) {

				case 'price':
					let min = $('#wc-apf-min-price').val();
					let max = $('#wc-apf-max-price').val();
					if (min) data['min_price'] = min;
					if (max) data['max_price'] = max;
					break;

				default:
					let arr = [];
					$(this).find('input[type="checkbox"]:checked').each(function () {
						arr.push($(this).val());
					});
					if (arr.length) data[slug] = arr;
					break;
			}
		});

		return data;
	};

	/* send AJAX & swap grid */
	const request = () => {
		$('body').addClass('loading'); // simple loader hook, style in CSS
		$.post(WC_APF.ajax, collect(), function (resp) {
			if (resp.success) {
				$products.fadeTo(100, 0, function () {
					$products.html(resp.data).fadeTo(100, 1);
				});
			}
			$('body').removeClass('loading');
		});
	};

	/* events */
	$wrapper
		.on('change', 'input[type="checkbox"]', request)
		.on('click', '.wc-apf-price-go', function (e) { e.preventDefault(); request(); });

});