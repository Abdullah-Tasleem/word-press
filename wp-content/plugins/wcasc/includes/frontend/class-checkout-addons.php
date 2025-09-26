<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WCASC_Checkout_Addons {
	/**
	 * Shortcode handler for [wcasc_checkout_addons]
	 */
	public static function shortcode() {
		ob_start();
		$instance = new self();
		$instance->render();
		return ob_get_clean();
	}

	public function __construct() {
		add_action( 'woocommerce_after_checkout_form', array( $this, 'render' ), 12 );
		add_shortcode( 'wcasc_checkout_addons', array( __CLASS__, 'shortcode' ) );
	}

	public function render() {
		if ( is_admin() ) return;

		$addons = get_posts( array(
			'post_type'      => 'wcasc_addon',
			'posts_per_page' => -1,
			'meta_key'       => '_wcasc_enabled',
			'meta_value'     => 1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		) );

		if ( empty( $addons ) ) {
			return;
		}

		echo '<div class="wcasc-checkout-addons-wrapper">';

		foreach ( $addons as $addon ) {
			$heading      = get_post_meta( $addon->ID, '_wcasc_heading', true ) ?: __( 'You May Also Like …', 'wcasc' );
			$source       = get_post_meta( $addon->ID, '_wcasc_source', true ) ?: 'products';
			$product_ids  = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $addon->ID, '_wcasc_product_ids', true ) ) ) );
			$category_ids = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $addon->ID, '_wcasc_category_ids', true ) ) ) );
			$limit        = absint( get_post_meta( $addon->ID, '_wcasc_limit', true ) ?: 8 );

			$products = wcasc_get_products_by_source( array(
				'source'       => $source,
				'product_ids'  => $product_ids,
				'category_ids' => $category_ids,
				'limit'        => $limit,
			) );

			if ( empty( $products ) ) continue;

			wcasc_get_template( 'checkout-addons.php', array(
				'heading'  => $heading,
				'products' => $products,
			) );
		}

		echo '</div>';
	}
	
}