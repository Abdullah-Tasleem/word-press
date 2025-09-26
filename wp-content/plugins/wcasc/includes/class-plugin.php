<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WCASC_Plugin {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Boot subsystems.
		new WCASC_Assets();
		new WCASC_Ajax();
		new WCASC_Admin();
		new WCASC_Checkout_Addons_Admin();
		new WCASC_Checkout_Addons();
		new WCASC_Sidebar_Cart();
	}
}