<?php
if (! defined('ABSPATH')) exit;

class WCASC_Checkout_Addons
{
	/**
	 * Shortcode handler for [wcasc_checkout_addons]
	 */
	public static function shortcode()
	{
		ob_start();
		$instance = new self();
		$instance->render();
		return ob_get_clean();
	}

	public function __construct()
	{
		// add_action( 'woocommerce_after_checkout_form', array( $this, 'render' ), 12 );
		add_filter('render_block', array($this, 'inject_shortcode_before_order_summary'), 20, 2);
		add_shortcode('wcasc_checkout_addons', array(__CLASS__, 'shortcode'));
	}
	public function inject_shortcode_before_order_summary($block_content, $block)
	{
		if (! is_checkout() || is_admin()) {
			return $block_content;
		}

		if (isset($block['blockName']) && $block['blockName'] === 'woocommerce/checkout-order-summary-block') {
			$extra  = '<div class="at-checkout-shortcode" style="margin-bottom:15px;">';
			$extra .= do_shortcode('[wcasc_checkout_addons]');
			$extra .= '</div>';

			return $extra . $block_content;
		}

		return $block_content;
	}

	public function render()
	{
		if (is_admin()) return;

		$addons = get_posts(array(
			'post_type'      => 'wcasc_addon',
			'posts_per_page' => -1,
			'meta_key'       => '_wcasc_enabled',
			'meta_value'     => 1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		));

		if (empty($addons)) {
			return;
		}

		echo '<div class="wcasc-checkout-addons-wrapper">';

		foreach ($addons as $addon) {
			$heading      = get_post_meta($addon->ID, '_wcasc_heading', true) ?: __('You May Also Like …', 'wcasc');
			$source       = get_post_meta($addon->ID, '_wcasc_source', true) ?: 'products';
			$product_ids  = array_filter(array_map('absint', explode(',', (string) get_post_meta($addon->ID, '_wcasc_product_ids', true))));
			$category_ids = array_filter(array_map('absint', explode(',', (string) get_post_meta($addon->ID, '_wcasc_category_ids', true))));
			$limit        = absint(get_post_meta($addon->ID, '_wcasc_limit', true) ?: 8);

			// Build exclude list (cart product ids) to avoid recommending items already in the cart
			$exclude_ids = array();
			if (function_exists('WC') && WC()->cart) {
				foreach (WC()->cart->get_cart() as $cart_item) {
					$product_id = 0;
					if (isset($cart_item['product_id'])) {
						$product_id = absint($cart_item['product_id']);
					} elseif (isset($cart_item['data']) && is_object($cart_item['data']) && method_exists($cart_item['data'], 'get_id')) {
						$product_id = absint($cart_item['data']->get_id());
					}
					if ($product_id) {
						$exclude_ids[] = $product_id;
					}
				}
				$exclude_ids = array_unique($exclude_ids);
			}

			$products = wcasc_get_products_by_source(array(
				'source'       => $source,
				'product_ids'  => $product_ids,
				'category_ids' => $category_ids,
				'limit'        => $limit,
				'exclude_ids'  => $exclude_ids,
			));
			
			
			// After $products = wcasc_get_products_by_source(...);
			// if (empty($products)) {
			// 	error_log('WCASC: No related products found for add-on ID ' . $addon->ID . ' (source: ' . $source . ')');
			// 	// Optionally, echo a message for debugging:
			// 	echo '<div style="color:red">No related products found for this add-on group.</div>';
			// 	continue;
			// }

			if (empty($products)) continue;

			wcasc_get_template('checkout-addons.php', array(
				'heading'  => $heading,
				'products' => $products,
			));
		}

		echo '</div>';
	}
}
