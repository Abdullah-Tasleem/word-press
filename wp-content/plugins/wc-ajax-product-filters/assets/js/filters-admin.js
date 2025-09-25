jQuery(function ($) {

	let $list = $('#wc-apf-sortable');

	$list.sortable({
		axis: 'y',
		items: 'li',
		cursor: 'move'
	});

	$('#wc-apf-save').on('click', function (e) {
		e.preventDefault();

		let order   = [];
		let enabled = [];

		$list.find('li').each(function () {
			let slug = $(this).data('slug');
			order.push(slug);
			if ($(this).find('input[type="checkbox"]').prop('checked')) {
				enabled.push(slug);
			}
		});

		$.post(WC_APF.ajax, {
			action  : 'wc_apf_save',
			nonce   : WC_APF.nonce,
			order   : order,
			enabled : enabled
		}, function (resp) {
			if (resp.success) {
				alert(WC_APF.msg_ok);
			}
		});
	});

});