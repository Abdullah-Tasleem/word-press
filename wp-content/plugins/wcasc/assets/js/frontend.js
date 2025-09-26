(function($){
	'use strict';

	function ajax(action, data, done) {
		data = data || {};
		data.action = action;
		data.nonce = WCASC_Vars.nonce;
		$.post(WCASC_Vars.ajaxurl, data).done(function(resp){
			if (resp && resp.fragments) {
				$.each(resp.fragments, function(selector, html){
					var $el = $(selector);
					if ($el.length) {
						$el.replaceWith(html);
					}
				});
				// Always open sidebar after AJAX update
				openSidebar();
			}
			if (typeof done === 'function') done(resp);
		});
	}

	function openSidebar() {
		// Always close overlay first to reset state
		$('#wcasc-overlay').removeClass('is-open');
		$('#wcasc-sidebar-cart').attr('aria-hidden', 'true').removeClass('is-open');
		$('body').removeClass('wcasc-open');
		// Always open sidebar and overlay
		$('#wcasc-overlay').addClass('is-open');
		$('#wcasc-sidebar-cart').attr('aria-hidden', 'false').addClass('is-open');
		$('body').addClass('wcasc-open');
	}

	function closeSidebar() {
		$('#wcasc-overlay').removeClass('is-open');
		$('#wcasc-sidebar-cart').attr('aria-hidden', 'true').removeClass('is-open');
		$('body').removeClass('wcasc-open');
	}

	// Delegated events to handle fragment re-renders
	$(document)
	.on('click', '#wcasc-cart-toggle', function(e){
		e.preventDefault();
		openSidebar();
	})
	.on('click', '#wcasc-overlay, #wcasc-sidebar-cart .wcasc-close', function(e){
		e.preventDefault();
		closeSidebar();
	})
	.on('click', '[data-wcasc-add]', function(e){
		e.preventDefault();
		var pid = parseInt($(this).attr('data-wcasc-add'), 10);
		if (!pid) return;
		ajax('wcasc_add_to_cart', { product_id: pid, quantity: 1 });
	})
	.on('click', '#wcasc-sidebar-cart .wcasc-remove-item', function(e){
		e.preventDefault();
		var key = $(this).closest('.wcasc-item').data('cart-key');
		if (!key) return;
		ajax('wcasc_remove_item', { cart_item_key: key });
	})
	.on('click', '#wcasc-sidebar-cart .wcasc-qty-inc, #wcasc-sidebar-cart .wcasc-qty-dec', function(e){
		e.preventDefault();
		var $item = $(this).closest('.wcasc-item');
		var key   = $item.data('cart-key');
		var $input= $item.find('.wcasc-qty-input');
		var val   = parseInt($input.val(),10) || 0;

		if ($(this).hasClass('wcasc-qty-inc')) val++;
		else val = Math.max(0, val - 1);

		ajax('wcasc_update_qty', { cart_item_key: key, quantity: val });
	})
	.on('change', '#wcasc-sidebar-cart .wcasc-qty-input', function(){
		var $item = $(this).closest('.wcasc-item');
		var key   = $item.data('cart-key');
		var val   = Math.max(0, parseInt($(this).val(),10) || 0);
		ajax('wcasc_update_qty', { cart_item_key: key, quantity: val });
	});

})(jQuery);