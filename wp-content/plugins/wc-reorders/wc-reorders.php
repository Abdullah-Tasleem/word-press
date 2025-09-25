<?php

/**
 * Plugin Name: WooCommerce Reorders
 * Description: Adds a Reorder button to My Account → Orders and a Reorders endpoint listing products the customer has reordered.
 * Version: 1.0.0
 * Author: Abdullah
 * License: GPL-2.0-or-later
 * Text Domain: wc-reorders
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 9.1
 */

if (!defined('ABSPATH')) exit;

final class WCR_Reorders
{
    const VERSION       = '1.0.0';
    const TEXT_DOMAIN   = 'wc-reorders';
    const ENDPOINT      = 'reorders';
    const USER_META_LOG = '_wc_reorders_log';

    private static $instance = null;

    public static function instance()
    {
        if (null === self::$instance) self::$instance = new self();
        return self::$instance;
    }

    private function __construct()
    {
        add_action('plugins_loaded', [$this, 'plugins_loaded']);
        register_activation_hook(__FILE__, [__CLASS__, 'activate']);
        register_deactivation_hook(__FILE__, [__CLASS__, 'deactivate']);
        register_uninstall_hook(__FILE__, [__CLASS__, 'uninstall']);
    }

    public static function activate()
    {
        self::register_endpoint();
        flush_rewrite_rules();
    }

    public static function deactivate()
    {
        flush_rewrite_rules();
    }

    public static function uninstall()
    {
        // Keep user data by default. If you want to remove reorder logs on uninstall,
        // you could iterate users and delete_user_meta for self::USER_META_LOG.
    }

    public function plugins_loaded()
    {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', [$this, 'admin_notice_no_wc']);
            return;
        }

        load_plugin_textdomain(self::TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages');

        add_action('init', [__CLASS__, 'register_endpoint']);
        add_filter('woocommerce_get_query_vars', [$this, 'add_query_vars']);
        add_filter('woocommerce_account_menu_items', [$this, 'add_account_menu_item']);
        add_filter('woocommerce_endpoint_' . self::ENDPOINT . '_title', [$this, 'endpoint_title']);
        add_action('woocommerce_account_' . self::ENDPOINT . '_endpoint', [$this, 'render_endpoint']);

        add_filter('woocommerce_my_account_my_orders_actions', [$this, 'add_reorder_action'], 10, 2);

        add_action('template_redirect', [$this, 'maybe_handle_reorder_request']);
        // add_action('template_redirect', [$this, 'maybe_handle_clear_history']);
    }

    public function admin_notice_no_wc()
    {
        echo '<div class="notice notice-error"><p>' .
            esc_html__('WooCommerce Reorders requires WooCommerce to be installed and active.', self::TEXT_DOMAIN) .
            '</p></div>';
    }

    public static function register_endpoint()
    {
        add_rewrite_endpoint(self::ENDPOINT, EP_ROOT | EP_PAGES);
    }

    public function add_query_vars($vars)
    {
        $vars[self::ENDPOINT] = self::ENDPOINT;
        return $vars;
    }

    public function endpoint_title($title)
    {
        return __('Reorders', self::TEXT_DOMAIN);
    }

    public function add_account_menu_item($items)
    {
        $new = [];
        foreach ($items as $key => $label) {
            $new[$key] = $label;
            if ('orders' === $key) {
                $new[self::ENDPOINT] = __('Reorders', self::TEXT_DOMAIN);
            }
        }
        if (!isset($new[self::ENDPOINT])) {
            $new[self::ENDPOINT] = __('Reorders', self::TEXT_DOMAIN);
        }
        return $new;
    }

    public function add_reorder_action($actions, $order)
    {
        if (!is_user_logged_in()) return $actions;

        $user_id = get_current_user_id();
        if ($order && (int) $order->get_user_id() === $user_id) {
            if ($this->order_has_reorderable_items($order)) {
                $url = add_query_arg(
                    [
                        'wc-reorder' => $order->get_id(),
                        '_wpnonce'   => wp_create_nonce('wc-reorder-' . $order->get_id()),
                    ],
                    wc_get_page_permalink('myaccount')
                );

                $actions['wcr_reorder'] = [
                    'url'  => $url,
                    'name' => __('Reorder', self::TEXT_DOMAIN),
                ];
            }
        }
        return $actions;
    }

    private function order_has_reorderable_items(WC_Order $order)
    {
        foreach ($order->get_items('line_item') as $item) {
            $product_id = isset($item['product_id']) ? $item['product_id'] : 0;
            $product = wc_get_product($product_id);
            if ($product && $product->is_purchasable() && $product->is_in_stock()) {
                return true;
            }
        }
        return false;
    }

    public function maybe_handle_reorder_request()
    {
        if (!isset($_GET['wc-reorder'])) return;

        if (!is_user_logged_in()) {
            wc_add_notice(__('Please log in to reorder.', self::TEXT_DOMAIN), 'error');
            wp_safe_redirect(wc_get_page_permalink('myaccount'));
            exit;
        }

        $order_id = absint($_GET['wc-reorder']);
        if (!$order_id) return;

        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (!$nonce || !wp_verify_nonce($nonce, 'wc-reorder-' . $order_id)) {
            wc_add_notice(__('Security check failed. Please try again.', self::TEXT_DOMAIN), 'error');
            wp_safe_redirect(wc_get_page_permalink('myaccount'));
            exit;
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wc_add_notice(__('Order not found.', self::TEXT_DOMAIN), 'error');
            wp_safe_redirect(wc_get_page_permalink('myaccount'));
            exit;
        }

        $user_id = get_current_user_id();
        if ((int) $order->get_user_id() !== $user_id) {
            wc_add_notice(__('You are not allowed to reorder this order.', self::TEXT_DOMAIN), 'error');
            wp_safe_redirect(wc_get_page_permalink('myaccount'));
            exit;
        }

        $added   = 0;
        $skipped = 0;
        $log_entries = [];

        foreach ($order->get_items('line_item') as $item_id => $item) {
            $product = $item->get_product();
            if (!$product || !$product->is_purchasable() || !$product->is_in_stock()) {
                $skipped++;
                continue;
            }

            $qty = max(1, (int) $item->get_quantity());

            $variation_id = 0;
            $product_id   = $product->get_id();
            $variations   = [];

            if ($product->is_type('variation')) {
                $variation_id = $product->get_id();
                $product_id   = $product->get_parent_id();
                $variations   = isset($item['variation']) ? $item['variation'] : [];
            } elseif (isset($item['variation_id']) && $item['variation_id']) {
                $variation_id = $item['variation_id'];
                $variations   = isset($item['variation']) ? $item['variation'] : [];
            }

            $cart_item_key = WC()->cart ? WC()->cart->add_to_cart($product_id, $qty, $variation_id, $variations) : false;

            if ($cart_item_key) {
                $added++;
                $log_entries[] = [
                    'product_id'   => (int) $product_id,
                    'variation_id' => (int) $variation_id,
                    'order_id'     => (int) $order_id,
                    'qty'          => (int) $qty,
                    'time'         => time(),
                ];
            } else {
                $skipped++;
            }
        }

        if ($log_entries) {
            $log = get_user_meta($user_id, self::USER_META_LOG, true);
            if (!is_array($log)) $log = [];
            $log = array_merge($log, $log_entries);
            update_user_meta($user_id, self::USER_META_LOG, $log);
        }

        if ($added) {
            wc_add_notice(
                sprintf(_n('%d item was added to your cart.', '%d items were added to your cart.', $added, self::TEXT_DOMAIN), $added),
                'success'
            );
        }
        if ($skipped) {
            wc_add_notice(
                sprintf(_n('%d item could not be reordered (unavailable).', '%d items could not be reordered (unavailable).', $skipped, self::TEXT_DOMAIN), $skipped),
                'notice'
            );
        }

        wp_safe_redirect(wc_get_cart_url());
        exit;
    }

    // public function maybe_handle_clear_history() {
    //     if (!isset($_GET['wcr-clear-history'])) return;
    //     if (!is_user_logged_in()) return;

    //     $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
    //     if (!$nonce || !wp_verify_nonce($nonce, 'wcr-clear-history')) {
    //         wc_add_notice(__('Security check failed. Please try again.', self::TEXT_DOMAIN), 'error');
    //         wp_safe_redirect(wc_get_endpoint_url(self::ENDPOINT, '', wc_get_page_permalink('myaccount')));
    //         exit;
    //     }

    //     delete_user_meta(get_current_user_id(), self::USER_META_LOG);
    //     wc_add_notice(__('Reorder history cleared.', self::TEXT_DOMAIN), 'success');
    //     wp_safe_redirect(wc_get_endpoint_url(self::ENDPOINT, '', wc_get_page_permalink('myaccount')));
    //     exit;
    // }

    private function get_user_log($user_id)
    {
        $log = get_user_meta($user_id, self::USER_META_LOG, true);
        return is_array($log) ? $log : [];
    }

    private function aggregate_log($log)
    {
        $agg = [];
        foreach ($log as $entry) {
            $pid = isset($entry['product_id']) ? (int) $entry['product_id'] : 0;
            $vid = isset($entry['variation_id']) ? (int) $entry['variation_id'] : 0;
            $key = $pid . '|' . $vid;

            if (!isset($agg[$key])) {
                $agg[$key] = [
                    'product_id'   => $pid,
                    'variation_id' => $vid,
                    'times'        => 0,
                    'last_time'    => 0,
                    'qty_total'    => 0,
                ];
            }

            $agg[$key]['times']++;
            $agg[$key]['qty_total'] += isset($entry['qty']) ? (int) $entry['qty'] : 0;
            $agg[$key]['last_time'] = max($agg[$key]['last_time'], isset($entry['time']) ? (int) $entry['time'] : 0);
        }
        return $agg;
    }

    public function render_endpoint()
    {
        if (!is_user_logged_in()) {
            echo '<p>' . esc_html__('You need to be logged in to view this page.', self::TEXT_DOMAIN) . '</p>';
            return;
        }

        $user_id = get_current_user_id();

        // Get all past orders for this customer
        $customer_orders = wc_get_orders([
            'customer_id' => $user_id,
            'status'      => ['wc-completed', 'wc-processing'], // only successful orders
            'limit'       => -1,
            'orderby'     => 'date',
            'order'       => 'DESC',
        ]);

        if (empty($customer_orders)) {
            echo '<p>' . esc_html__('You have not placed any orders yet.', self::TEXT_DOMAIN) . '</p>';
            return;
        }

        echo '<div class="woocommerce-account-reorders">';
        echo '<h3>' . esc_html__('Your Reorders', self::TEXT_DOMAIN) . '</h3>';

        foreach ($customer_orders as $order) {
            $order_id = $order->get_id();

            // Reorder URL
            $reorder_url = add_query_arg(
                [
                    'wc-reorder' => $order_id,
                    '_wpnonce'   => wp_create_nonce('wc-reorder-' . $order_id),
                ],
                wc_get_page_permalink('myaccount')
            );

            echo '<table class="shop_table my_account_orders reorder-table">';
            echo '<thead>';
            echo '<tr class="reorder-header">';
            echo '<th colspan="2">';
            echo esc_html__('Order #', self::TEXT_DOMAIN) . ' ' . esc_html($order->get_order_number());
            echo '</th>';
            echo '<th class="reorder-btn">';
            echo '<a href="' . esc_url($reorder_url) . '" class="button">' . esc_html__('Reorder', self::TEXT_DOMAIN) . '</a>';
            echo '</th>';
            echo '</tr>';
            echo '<tr>';
            echo '<th>' . esc_html__('Product', self::TEXT_DOMAIN) . '</th>';
            echo '<th>' . esc_html__('Quantity', self::TEXT_DOMAIN) . '</th>';
            echo '<th>' . esc_html__('Order Date', self::TEXT_DOMAIN) . '</th>';
            echo '</tr>';
            echo '</thead><tbody>';

            foreach ($order->get_items() as $item_id => $item) {
                $product = $item->get_product();
                if (!$product) continue;

                $thumb     = $product->get_image('thumbnail');
                $name      = $product->get_name();
                $permalink = $product->get_permalink();
                $date_str  = $order->get_date_created()->date_i18n(get_option('date_format'));

                echo '<tr>';
                echo '<td><a href="' . esc_url($permalink) . '">' . $thumb . ' ' . esc_html($name) . '</a></td>';
                echo '<td>' . esc_html($item->get_quantity()) . '</td>';
                echo '<td>' . esc_html($date_str) . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table><br>';
        }

        echo '</div>';
    }
}

WCR_Reorders::instance();
