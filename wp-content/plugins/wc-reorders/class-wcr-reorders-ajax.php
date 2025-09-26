<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WCR_Reorders_AJAX {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        // AJAX hooks
        add_action( 'wp_ajax_load_reorders', [ $this, 'load_reorders' ] );
        add_action( 'wp_ajax_nopriv_load_reorders', [ $this, 'load_reorders' ] );
    }

    public function enqueue_scripts() {
        if ( is_account_page() ) {
            wp_enqueue_script(
                'wcr-reorders-ajax',
                plugin_dir_url( __FILE__ ) . 'js/wcr-reorders-ajax.js',
                [ 'jquery' ],
                WCR_Reorders::VERSION,
                true
            );

            wp_localize_script( 'wcr-reorders-ajax', 'wcrReorders', [
                'ajaxUrl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'wcr-reorders-nonce' ),
            ] );
        }
    }

    public function load_reorders() {
        check_ajax_referer( 'wcr-reorders-nonce', 'security' );

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => __( 'You must be logged in.', 'wc-reorders' ) ] );
        }

        $user_id = get_current_user_id();
        $paged   = isset( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
        $per_page = 3; // number of orders per "page"

        $args = [
            'customer_id' => $user_id,
            'status'      => [ 'wc-completed', 'wc-processing' ],
            'limit'       => $per_page,
            'paged'       => $paged,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ];

        $orders = wc_get_orders( $args );

        if ( empty( $orders ) ) {
            wp_send_json_success( [ 'html' => '', 'hasMore' => false ] );
        }

        ob_start();

        foreach ( $orders as $order ) {
            $order_id    = $order->get_id();
            $reorder_url = add_query_arg(
                [
                    'wc-reorder' => $order_id,
                    '_wpnonce'   => wp_create_nonce( 'wc-reorder-' . $order_id ),
                ],
                wc_get_page_permalink( 'myaccount' )
            );

            echo '<table class="shop_table my_account_orders reorder-table">';
            echo '<thead>';
            echo '<tr class="reorder-header">';
            echo '<th colspan="2">Order # ' . esc_html( $order->get_order_number() ) . '</th>';
            echo '<th class="reorder-btn"><a href="' . esc_url( $reorder_url ) . '" class="button">Reorder</a></th>';
            echo '</tr>';
            echo '<tr><th>Product</th><th>Quantity</th><th>Order Date</th></tr>';
            echo '</thead><tbody>';

            foreach ( $order->get_items() as $item ) {
                $product = $item->get_product();
                if ( ! $product ) continue;

                $thumb    = $product->get_image( 'thumbnail' );
                $name     = $product->get_name();
                $link     = $product->get_permalink();
                $date_str = $order->get_date_created()->date_i18n( get_option( 'date_format' ) );

                echo '<tr>';
                echo '<td><a href="' . esc_url( $link ) . '">' . $thumb . ' ' . esc_html( $name ) . '</a></td>';
                echo '<td>' . esc_html( $item->get_quantity() ) . '</td>';
                echo '<td>' . esc_html( $date_str ) . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table><br>';
        }

        $html = ob_get_clean();

        wp_send_json_success( [
            'html'    => $html,
            'hasMore' => count( $orders ) === $per_page,
        ] );
    }
}

new WCR_Reorders_AJAX();
