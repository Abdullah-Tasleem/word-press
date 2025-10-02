<?php
if (! defined('ABSPATH')) exit;

/**
 * Get plugin settings array.
 */

function wcasc_get_settings()
{
	$defaults = array(
		'enable_sidebar'          => 1,
		'inherit_fonts'           => 1,
		'show_strike'             => 1,
		'show_subtotal'           => 1,
		'width_desktop'           => 420,
		'width_mobile'            => 100, // percentage
		'accent_color'            => '#3e66fb',
		'text_color'              => '#222222',
		'savings_color'           => '#1a7f37',
		'btn_bg'                  => '#111111',
		'btn_text'                => '#ffffff',
		'btn_radius'              => 6,
		'overlay_color'           => 'rgba(0,0,0,0.5)',
		'free_shipping_threshold' => 1000,
		'sidebar_addon_cat_ids'   => array(),
		'sidebar_addon_limit'     => 12,
	);
	$settings = get_option('wcasc_settings', array());
	return wp_parse_args($settings, $defaults);
}

/**
 * Get a single setting.
 */
function wcasc_get_setting($key, $default = null)
{
	$all = wcasc_get_settings();
	return isset($all[$key]) ? $all[$key] : $default;
}


/**
 * Format currency using WooCommerce.
 */
function wcasc_price($amount)
{
	if (function_exists('wc_price')) {
		return wc_price($amount);
	}
	return esc_html($amount);
}

function wcasc_cat_ids_to_slugs($ids)
{
	$slugs = array();

	foreach ((array) $ids as $id) {
		$term = get_term($id, 'product_cat');
		if (! is_wp_error($term) && $term && ! empty($term->slug)) {
			$slugs[] = $term->slug;
		}
	}

	return array_unique($slugs);
}

/**
 * Get selected products from options: by products or categories.
 */
function wcasc_get_products_by_source($args)
{
	$defaults = array(
		'source'       => 'products', // 'products', 'categories', 'related_cart'
		'product_ids'  => array(),
		'category_ids' => array(),
		'limit'        => 8,
		'orderby'      => 'date',
		'order'        => 'DESC',
		'exclude_ids'  => array(),
	);
	$args = wp_parse_args($args, $defaults);

	$q = array(
		'status'       => 'publish',
		'limit'        => (int) $args['limit'],
		'orderby'      => $args['orderby'],
		'order'        => $args['order'],
		'return'       => 'objects',
		'stock_status' => 'instock',
	);

	$exclude_ids = array_filter(array_map('absint', (array) $args['exclude_ids']));

	/* -------------------------------------------------------------------- */
	/* ----------------------- 1. MANUAL PRODUCTS ------------------------- */
	if ('products' === $args['source'] && ! empty($args['product_ids'])) {
		$q['include'] = array_map('absint', (array) $args['product_ids']);
	}

	/* -------------------------------------------------------------------- */
	/* ----------------------- 2. FIXED CATEGORIES ------------------------ */
	if ('categories' === $args['source'] && ! empty($args['category_ids'])) {
		$q['category'] = wcasc_cat_ids_to_slugs($args['category_ids']);
	}

	/* -------------------------------------------------------------------- */
	/* ----------------------- 3. RELATED-TO-CART ------------------------- */
	if ('related_cart' === $args['source']) {

		if (function_exists('WC') && WC()->cart) {
			$cart_items       = WC()->cart->get_cart();
			$cart_product_ids = array();
			$cat_ids          = array();

			foreach ($cart_items as $cart_item) {
				$product_id = isset($cart_item['product_id'])
					? absint($cart_item['product_id'])
					: (isset($cart_item['data']) && is_object($cart_item['data'])
						? absint($cart_item['data']->get_id())
						: 0);

				if ($product_id) {
					$cart_product_ids[] = $product_id;
					$terms              = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
					if (! is_wp_error($terms) && $terms) {
						$cat_ids = array_merge($cat_ids, $terms);
					}
				}
			}

			$cat_slugs = wcasc_cat_ids_to_slugs($cat_ids);

			if ($cat_slugs) {
				$q['category'] = $cat_slugs;
			} else {
				// No categories found in cart – nothing to recommend.
				return array();
			}

			$exclude_ids = array_merge($exclude_ids, $cart_product_ids);
			$q['orderby'] = 'rand'; // random works better for related items
		} else {
			// No live cart (admin or empty cart)
			return array();
		}
	}

	/* -------------------------------------------------------------------- */
	if (! empty($exclude_ids)) {
		$q['exclude'] = array_unique($exclude_ids);
	}

	return wc_get_products($q);
}

/**
 * Get cart subtotal used for progress calculation (pre-shipping, pre-coupons).
 */
function wcasc_cart_running_total()
{
	if (function_exists('WC') && WC()->cart) {
		// Use cart contents total, excluding shipping but including discounts.
		return (float) WC()->cart->get_cart_contents_total();
	}
	return 0.0;
}

/**
 * Render template from frontend/templates directory.
 */
function wcasc_get_template($template, $vars = array())
{
	$path = WCASC_DIR . 'includes/frontend/templates/' . $template;
	if (file_exists($path)) {
		extract($vars, EXTR_SKIP);
		include $path;
	}
}
