<?php
if (! defined('ABSPATH')) exit;

class WCASC_Ajax
{

	public function __construct()
	{
		add_action('wp_ajax_wcasc_add_to_cart', array($this, 'add_to_cart'));
		add_action('wp_ajax_nopriv_wcasc_add_to_cart', array($this, 'add_to_cart'));

		add_action('wp_ajax_wcasc_update_qty', array($this, 'update_qty'));
		add_action('wp_ajax_nopriv_wcasc_update_qty', array($this, 'update_qty'));

		add_action('wp_ajax_wcasc_remove_item', array($this, 'remove_item'));
		add_action('wp_ajax_nopriv_wcasc_remove_item', array($this, 'remove_item'));

		// Sync endpoint used by frontend to force the server to return refreshed fragments
		add_action('wp_ajax_wcasc_sync_cart', array($this, 'sync_cart'));
		add_action('wp_ajax_nopriv_wcasc_sync_cart', array($this, 'sync_cart'));
	}

	private function check()
	{
		check_ajax_referer('wcasc_nonce', 'nonce');
		if (! function_exists('WC')) {
			wp_send_json_error(array('message' => __('WooCommerce not loaded', 'wcasc')));
		}
	}

	public function add_to_cart()
	{
		$this->check();
		$product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
		$qty        = isset($_POST['quantity']) ? max(1, absint($_POST['quantity'])) : 1;

		if (! $product_id) {
			wp_send_json_error(array('message' => __('Invalid product', 'wcasc')));
		}

		$added = WC()->cart->add_to_cart($product_id, $qty);
		if (! $added) {
			wp_send_json_error(array('message' => __('Could not add to cart', 'wcasc')));
		}

		// Return Woo fragments for seamless updates (legacy behaviour)
		if (class_exists('WC_AJAX') && method_exists('WC_AJAX', 'get_refreshed_fragments')) {
			WC_AJAX::get_refreshed_fragments();
		} else {
			wp_send_json_success();
		}
	}

	public function update_qty()
	{
		$this->check();
		$key = sanitize_text_field($_POST['cart_item_key'] ?? '');
		$qty = max(0, absint($_POST['quantity'] ?? 1));

		if (! $key || ! WC()->cart) {
			wp_send_json_error(array('message' => __('Invalid cart item', 'wcasc')));
		}

		WC()->cart->set_quantity($key, $qty, true);

		if (class_exists('WC_AJAX') && method_exists('WC_AJAX', 'get_refreshed_fragments')) {
			WC_AJAX::get_refreshed_fragments();
		} else {
			wp_send_json_success();
		}
	}

	public function remove_item()
	{
		$this->check();
		$key = sanitize_text_field($_POST['cart_item_key'] ?? '');

		if (! $key || ! WC()->cart) {
			wp_send_json_error(array('message' => __('Invalid cart item', 'wcasc')));
		}

		// Remove the item
		WC()->cart->remove_cart_item($key);

		// Important: recalc totals
		WC()->cart->calculate_totals();

		// Return WooCommerce fragments for sidebar + cart widget
		if (class_exists('WC_AJAX') && method_exists('WC_AJAX', 'get_refreshed_fragments')) {
			$fragments = WC_AJAX::get_refreshed_fragments();
			wp_send_json_success($fragments);
		} else {
			wp_send_json_success();
		}
	}


	/**
	 * Sync cart endpoint — returns refreshed fragments (legacy) and ensures server-side cart state is up-to-date.
	 */
	public function sync_cart()
	{
		$this->check();
		if (class_exists('WC_AJAX') && method_exists('WC_AJAX', 'get_refreshed_fragments')) {
			WC_AJAX::get_refreshed_fragments();
		} else {
			wp_send_json_success();
		}
	}
}
