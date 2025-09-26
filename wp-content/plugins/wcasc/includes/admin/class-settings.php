<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WCASC_Settings {

	public static function render_page() {
		if ( isset( $_POST['wcasc_settings_nonce'] ) && wp_verify_nonce( $_POST['wcasc_settings_nonce'], 'wcasc_save_settings' ) ) {
			$in = $_POST['wcasc'] ?? array();

			$settings = array(
				'enable_sidebar'          => ! empty( $in['enable_sidebar'] ) ? 1 : 0,
				'inherit_fonts'           => ! empty( $in['inherit_fonts'] ) ? 1 : 0,
				'show_strike'             => ! empty( $in['show_strike'] ) ? 1 : 0,
				'show_subtotal'           => ! empty( $in['show_subtotal'] ) ? 1 : 0,
				'width_desktop'           => isset( $in['width_desktop'] ) ? absint( $in['width_desktop'] ) : 420,
				'width_mobile'            => isset( $in['width_mobile'] ) ? absint( $in['width_mobile'] ) : 100,
				'accent_color'            => sanitize_hex_color( $in['accent_color'] ?? '#3e66fb' ),
				'text_color'              => sanitize_hex_color( $in['text_color'] ?? '#222222' ),
				'savings_color'           => sanitize_hex_color( $in['savings_color'] ?? '#1a7f37' ),
				'btn_bg'                  => sanitize_hex_color( $in['btn_bg'] ?? '#111111' ),
				'btn_text'                => sanitize_hex_color( $in['btn_text'] ?? '#ffffff' ),
				'btn_radius'              => isset( $in['btn_radius'] ) ? absint( $in['btn_radius'] ) : 6,
				'overlay_color'           => sanitize_text_field( $in['overlay_color'] ?? 'rgba(0,0,0,0.5)' ),
				'free_shipping_threshold' => isset( $in['free_shipping_threshold'] ) ? floatval( $in['free_shipping_threshold'] ) : 1000,
				'sidebar_addon_product_ids' => array_map( 'absint', $in['sidebar_addon_product_ids'] ?? array() ),
				'sidebar_addon_cat_ids'   => array_map( 'absint', $in['sidebar_addon_cat_ids'] ?? array() ),
				'sidebar_addon_limit'     => isset( $in['sidebar_addon_limit'] ) ? absint( $in['sidebar_addon_limit'] ) : 12,
			);

			update_option( 'wcasc_settings', $settings );
			echo '<div class="updated"><p>' . esc_html__( 'Settings saved.', 'wcasc' ) . '</p></div>';
		}

		$settings = wcasc_get_settings();
		include WCASC_DIR . 'includes/admin/views/settings-page.php';
	}
}