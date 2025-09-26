<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WCASC_Admin {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ), 60 );
	}

	public function menu() {
		// Submenu under WooCommerce.
		add_submenu_page(
			'woocommerce',
			__( 'WC Add-Ons & Sidebar Cart', 'wcasc' ),
			__( 'WC Add-Ons & Sidebar', 'wcasc' ),
			'manage_woocommerce',
			'wcasc',
			array( 'WCASC_Settings', 'render_page' )
		);
	}
}