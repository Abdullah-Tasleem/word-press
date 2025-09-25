<?php
/**
 * Plugin Name: Woo AJAX Product Filters
 * Description: Modern, super-fast AJAX filter bar (Color, Size, Gender, Price) for WooCommerce – fully Astra compatible.
 * Author:      Abdullah
 * Version:     1.0.0
 * License:     GPL-2.0+
 * Text Domain: wc-apf
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WC_APF_VER',        '1.0.0' );
define( 'WC_APF_PATH',       plugin_dir_path( __FILE__ ) );
define( 'WC_APF_URL',        plugin_dir_url ( __FILE__ ) );

require_once WC_APF_PATH . 'includes/class-wc-apf-helpers.php';
require_once WC_APF_PATH . 'includes/class-wc-apf-admin.php';
require_once WC_APF_PATH . 'includes/class-wc-apf-frontend.php';

register_activation_hook( __FILE__, array( 'WC_APF_Helpers', 'activate' ) );