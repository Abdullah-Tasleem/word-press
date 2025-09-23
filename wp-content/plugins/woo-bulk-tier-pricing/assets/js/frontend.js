jQuery(function($){
    function updateTable($table) {
        var qty = parseInt($table.closest('form.cart').find('input.qty').val(), 10) || 1;
        var base = parseFloat($table.data('base-price')) || 0;

        // Recalculate highlight and computed prices if variation changed base
        $table.find('tr').each(function(){
            var min = parseInt($(this).data('min'), 10) || 0;
            var type = $(this).data('type');
            var amount = parseFloat($(this).data('amount')) || 0;
            var $priceCell = $(this).find('.wcbtp-unit-price');

            var unit = base;
            if (type === 'percentage') {
                unit = base * (1 - amount/100);
            } else {
                unit = base - amount;
            }
            if (unit < 0) unit = 0;

            // Update unit price text if dataset present
            var formatted = $(this).data('price-format');
            if (formatted) {
                // Keep original format placeholder %s
                $priceCell.text(formatted.replace('%s', unit.toFixed(2)));
            } else {
                $priceCell.text(unit.toFixed(2));
            }

            if (qty >= min) {
                $(this).addClass('wcbtp-active');
            } else {
                $(this).removeClass('wcbtp-active');
            }
        });
        // Only keep the highest active row highlighted
        var $active = $table.find('tr.wcbtp-active');
        if ($active.length) {
            $table.find('tr').removeClass('wcbtp-highlight');
            $active.last().addClass('wcbtp-highlight');
        }
    }

    $('.wcbtp-tier-table').each(function(){
        var $table = $(this);
        var $qty = $table.closest('form.cart').find('input.qty');

        // initial
        updateTable($table);

        $qty.on('change keyup', function(){
            updateTable($table);
        });

        // Variable product: update base price on variation found
        $(document).on('found_variation', 'form.variations_form', function(event, variation){
            if (variation && typeof variation.display_price !== 'undefined') {
                $table.data('base-price', variation.display_price);
                updateTable($table);
            }
        });
    });
});