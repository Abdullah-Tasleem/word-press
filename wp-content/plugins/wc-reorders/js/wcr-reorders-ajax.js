jQuery(function ($) {
    let page = 1;
    let loading = false;
    let hasMore = true;
    let ordersLoaded = 0; // track how many orders loaded total

    const $container = $('.woocommerce-account-reorders');
    if (! $container.length) return;

    // Skeleton loader markup
    const skeleton = `
    <div class="wcr-skeleton-group">
        <div class="wcr-skeleton-table">
            <div class="wcr-skeleton-row"></div>
            <div class="wcr-skeleton-row"></div>
            <div class="wcr-skeleton-row"></div>
        </div>
    </div>`;
    const $loader = $(skeleton).hide();
    $container.after($loader);

    function loadReorders() {
        if (loading || !hasMore) return;

        loading = true;
        $loader.show();

        $.post(wcrReorders.ajaxUrl, {
            action: 'load_reorders',
            page: page,
            security: wcrReorders.nonce
        }, function (response) {
            if (response.success) {
                if (response.data.html) {
                    $container.append(response.data.html);

                    // Count how many orders came in this batch
                    const newOrders = $(response.data.html).filter('table.reorder-table').length;
                    ordersLoaded += newOrders;

                    // After every 2 orders, show skeleton for UX
                    if (ordersLoaded % 2 === 0 && hasMore) {
                        $loader.show();
                    } else {
                        $loader.hide();
                    }

                    page++;
                }
                hasMore = response.data.hasMore;
            }
            loading = false;
        });
    }

    // Initial load
    loadReorders();

    // Infinite scroll
    $(window).on('scroll', function () {
        if ($(window).scrollTop() + $(window).height() >= $(document).height() - 200) {
            loadReorders();
        }
    });

    // Inject skeleton CSS
    if (!$('#wcr-skeleton-css').length) {
        $('head').append(`
        <style id="wcr-skeleton-css">
        .wcr-skeleton-group { margin: 20px 0; }
        .wcr-skeleton-table { background: #fff; border-radius: 6px; box-shadow: 0 1px 4px rgba(0,0,0,0.04); padding: 16px; }
        .wcr-skeleton-row { height: 32px; margin-bottom: 12px; background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%); border-radius: 4px; animation: wcr-skeleton-anim 1.2s infinite linear; }
        @keyframes wcr-skeleton-anim {
            0% { background-position: -200px 0; }
            100% { background-position: 200px 0; }
        }
        </style>
        `);
    }
});
