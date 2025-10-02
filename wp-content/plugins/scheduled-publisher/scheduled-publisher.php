<?php
/**
 * Plugin Name: Scheduled Publisher (Posts & Products)
 * Description: Schedule future publishing of posts or WooCommerce products from an easy admin screen.
 * Version:     1.0.0
 * Author:      Abdullah
 * License:     GPL-2.0+
 * Text Domain: scheduled-publisher
 */

if ( ! defined( 'ABSPATH' ) ) exit; // No direct access

define( 'SP_VERSION',       '1.0.0' );
define( 'SP_PLUGIN_PATH',   plugin_dir_path( __FILE__ ) );
define( 'SP_PLUGIN_URL',    plugin_dir_url(  __FILE__ ) );
define( 'SP_TABLE',         $GLOBALS['wpdb']->prefix . 'sp_schedules' );

require_once SP_PLUGIN_PATH . 'includes/class-sp-activator.php';
require_once SP_PLUGIN_PATH . 'includes/class-sp-admin.php';
require_once SP_PLUGIN_PATH . 'includes/class-sp-cron.php';

register_activation_hook( __FILE__, [ 'SP_Activator', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'SP_Activator', 'deactivate' ] );

// Kick-off admin & cron only when needed
if ( is_admin() ) {
	new SP_Admin;
}
new SP_Cron;