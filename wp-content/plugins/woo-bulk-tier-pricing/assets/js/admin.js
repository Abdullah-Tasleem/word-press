jQuery(function($){
    // Tiers repeater
    var $tbody = $('#wcbtp-tier-rows');
    $('#wcbtp-add-tier').on('click', function(e){
        e.preventDefault();
        var index = $tbody.find('tr.wcbtp-tier-row').length;
        var row = '<tr class="wcbtp-tier-row">'
            + '<td><input type="number" name="wcbtp_tiers[' + index + '][min_qty]" min="1" step="1" value="1" /></td>'
            + '<td><select name="wcbtp_tiers[' + index + '][type]">'
            + '<option value="percentage">Percentage (%)</option>'
            + '<option value="fixed">Fixed amount</option>'
            + '</select></td>'
            + '<td><input type="number" name="wcbtp_tiers[' + index + '][amount]" step="0.0001" min="0" value="0" /></td>'
            + '<td><button class="button wcbtp-remove-tier">Remove</button></td>'
            + '</tr>';
        $tbody.append(row);
    });
    $tbody.on('click', '.wcbtp-remove-tier', function(e){
        e.preventDefault();
        $(this).closest('tr').remove();
        // Re-index inputs
        $tbody.find('tr.wcbtp-tier-row').each(function(i, tr){
            $(tr).find('input, select').each(function(){
                var name = $(this).attr('name');
                if (name) {
                    name = name.replace(/wcbtp_tiers\\[\\d+\\]/, 'wcbtp_tiers[' + i + ']');
                    $(this).attr('name', name);
                }
            });
        });
    });
});