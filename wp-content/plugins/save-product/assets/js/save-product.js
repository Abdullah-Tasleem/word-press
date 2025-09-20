jQuery(document).ready(function($) {
    $(document).on('click', '.save-product-btn', function(e) {
        e.preventDefault();

        var btn = $(this);
        var product_id = btn.data('productid');

        // Ensure there is a message box next to the button
        var msg = btn.siblings('.save-product-message');
        if (!msg.length) {
            msg = $('<div class="save-product-message" style="margin-top:10px;font-weight:600;display:none;"></div>');
            btn.after(msg);
        }

        // Start UI state
        btn.prop('disabled', true);
        msg.stop(true, true).hide();

        $.ajax({
            url: saveProductObj.ajax_url,
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'save_product_action',
                nonce: saveProductObj.nonce,
                product_id: product_id
            }
        }).done(function(response) {
            if (response && response.success) {
                // Toggle button label
                if (response.data && response.data.removed) {
                    btn.text('❤️ Save Product'); // after removal
                } else {
                    btn.text('❌ Remove from Saved'); // after save
                }

                // Show success/removal message
                var messageText = (response.data && response.data.message) ? response.data.message : 'Done.';
                var color = (response.data && response.data.removed) ? '#cc0000' : 'green';
                msg.text(messageText)
                   .css({ color: color })
                   .fadeIn(150)
                   .delay(2500)
                   .fadeOut(300);
            } else {
                var err = response && response.data && response.data.message ? response.data.message : 'Something went wrong.';
                msg.text(err)
                   .css({ color: '#cc0000' })
                   .fadeIn(150)
                   .delay(3000)
                   .fadeOut(300);
            }
        }).fail(function() {
            msg.text('Network error. Please try again.')
               .css({ color: '#cc0000' })
               .fadeIn(150)
               .delay(3000)
               .fadeOut(300);
        }).always(function() {
            btn.prop('disabled', false);
        });
    });
});
