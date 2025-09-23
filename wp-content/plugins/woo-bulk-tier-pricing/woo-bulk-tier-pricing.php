<?php
/**
 * Plugin Name: WooCommerce Bulk Discounts & Tiered Pricing
 * Description: Quantity-based discounts per product or category. Shows tier tables on product pages and applies discounts automatically in cart/checkout.
 * Version: 1.0.0
 * Author: Abdullah
 * Text Domain: wcbtp
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 9.1
 */

if (!defined('ABSPATH')) exit;

define('WCBTP_VERSION', '1.0.0');
define('WCBTP_FILE', __FILE__);
define('WCBTP_BASENAME', plugin_basename(__FILE__));
define('WCBTP_PATH', plugin_dir_path(__FILE__));
define('WCBTP_URL', plugin_dir_url(__FILE__));

register_activation_hook(__FILE__, function () {
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die(esc_html__('WooCommerce Bulk Discounts & Tiered Pricing requires WooCommerce. Please install and activate WooCommerce.', 'wcbtp'));
    }
});

require_once WCBTP_PATH . 'includes/helpers.php';
require_once WCBTP_PATH . 'includes/class-wcbtp-plugin.php';
require_once WCBTP_PATH . 'includes/class-wcbtp-rules.php';
require_once WCBTP_PATH . 'includes/class-wcbtp-admin.php';
require_once WCBTP_PATH . 'includes/class-wcbtp-frontend.php';
require_once WCBTP_PATH . 'includes/class-wcbtp-cart.php';

add_action('plugins_loaded', function () {
    if (!class_exists('WooCommerce')) {
        return;
    }
    WCBTP_Plugin::instance()->init();
});