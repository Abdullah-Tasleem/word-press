<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WCASC_Assets {
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend' ) );
	}

	public function admin( $hook ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		$is_wcasc_screen = false;
		if ( $screen ) {
			$is_wcasc_screen = (
				// Our settings page under WooCommerce
				'settings_page_wcasc' === $screen->id ||
				'woocommerce_page_wcasc' === $screen->id ||
				// Our CPT edit screens
				'wcasc_addon' === $screen->post_type ||
				'edit-wcasc_addon' === $screen->id
			);
		}

		if ( ! $is_wcasc_screen ) {
			return;
		}

		// WooCommerce enhanced select (Select2/SelectWoo)
		if ( wp_style_is( 'woocommerce_admin_styles', 'registered' ) ) {
			wp_enqueue_style( 'woocommerce_admin_styles' );
		}
		if ( wp_script_is( 'selectWoo', 'registered' ) ) {
			wp_enqueue_script( 'selectWoo' );
		}
		if ( wp_style_is( 'select2', 'registered' ) ) {
			wp_enqueue_style( 'select2' );
		}
		if ( wp_script_is( 'wc-enhanced-select', 'registered' ) ) {
			wp_enqueue_script( 'wc-enhanced-select' );
		}

		wp_enqueue_style( 'wcasc-admin', WCASC_ASSETS . 'css/admin.css', array(), WCASC_VERSION );
		wp_enqueue_script( 'wcasc-admin', WCASC_ASSETS . 'js/admin.js', array( 'jquery' ), WCASC_VERSION, true );

		// Ensure Select2 is initialized on our fields.
		wp_add_inline_script( 'wcasc-admin', "jQuery(function($){ $(document.body).trigger('wc-enhanced-select-init'); });" );
	}

	public function frontend() {
		$settings = wcasc_get_settings();

		wp_enqueue_style( 'wcasc-frontend', WCASC_ASSETS . 'css/frontend.css', array(), WCASC_VERSION );

		wp_enqueue_script( 'wcasc-frontend', WCASC_ASSETS . 'js/frontend.js', array( 'jquery' ), WCASC_VERSION, true );
		wp_localize_script( 'wcasc-frontend', 'WCASC_Vars', array(
			'ajaxurl'     => admin_url( 'admin-ajax.php' ),
			'nonce'       => wp_create_nonce( 'wcasc_nonce' ),
			'enabled'     => (int) $settings['enable_sidebar'],
			'strings'     => array(
				'free_shipping_on' => __( 'Free shipping on orders over', 'wcasc' ),
				'you_are_x_away'   => __( 'You are {amount} away from free shipping', 'wcasc' ),
				'free_shipping'    => __( 'Free shipping unlocked!', 'wcasc' ),
				'youll_love'       => __( "You'll love these", 'wcasc' ),
			),
		) );
	}
}