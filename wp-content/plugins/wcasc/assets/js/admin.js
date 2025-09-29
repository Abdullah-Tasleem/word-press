jQuery(function($){
	// Init WooCommerce enhanced selects
	$(document.body).trigger('wc-enhanced-select-init');

	function toggleSourceFields(){
		var val = $('#wcasc_source').val();
		if (val === 'products') {
			$('#wcasc_products_field').show();
			$('#wcasc_categories_field').hide();
		} else {
			$('#wcasc_products_field').hide();
			$('#wcasc_categories_field').show();
		}
	}
	$('#wcasc_source').on('change', toggleSourceFields);
	toggleSourceFields();
});
