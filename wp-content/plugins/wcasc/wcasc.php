<?php
/**
 * Plugin Name: WC Add-Ons & Sidebar Cart (Pro)
 * Description: Checkout add-ons and a slide-in sidebar cart with admin customization for WooCommerce.
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: wcasc
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WCASC_VERSION', '1.0.0' );
define( 'WCASC_FILE', __FILE__ );
define( 'WCASC_DIR', plugin_dir_path( __FILE__ ) );
define( 'WCASC_URL', plugin_dir_url( __FILE__ ) );
define( 'WCASC_ASSETS', WCASC_URL . 'assets/' );

require_once ABSPATH . 'wp-admin/includes/plugin.php';

add_action( 'plugins_loaded', function () {
	// Check WooCommerce.
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'WC Add-Ons & Sidebar Cart requires WooCommerce to be installed and active.', 'wcasc' ) . '</p></div>';
		} );
		return;
	}

	// Load text domain.
	load_plugin_textdomain( 'wcasc', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	// Includes.
	require_once WCASC_DIR . 'includes/helpers.php';
	require_once WCASC_DIR . 'includes/class-plugin.php';
	require_once WCASC_DIR . 'includes/class-assets.php';
	require_once WCASC_DIR . 'includes/class-ajax.php';
	require_once WCASC_DIR . 'includes/admin/class-admin.php';
	require_once WCASC_DIR . 'includes/admin/class-settings.php';
	require_once WCASC_DIR . 'includes/admin/class-checkout-addons-admin.php';
	require_once WCASC_DIR . 'includes/frontend/class-checkout-addons.php';
	require_once WCASC_DIR . 'includes/frontend/class-sidebar-cart.php';

	// Bootstrap.
	WCASC_Plugin::instance();
}, 5 );