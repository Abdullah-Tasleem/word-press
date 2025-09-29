<?php
if (! defined('ABSPATH')) {
	exit;
}

class WCASC_Sidebar_Cart
{

	public function __construct()
	{

		/* ------------------  NEW LINE  ------------------ */
		// Load Swiper + our tiny init-snippet on the front-end
		add_action('wp_enqueue_scripts', array($this, 'enqueue_swiper_assets'));

		add_action('wp_footer', array($this, 'output_markup'));
		add_action('wp_footer', array($this, 'inline_variables_style'), 1);

		add_filter('woocommerce_add_to_cart_fragments', array($this, 'fragments'));

		add_shortcode('wcasc_cart_icon', array($this, 'shortcode_icon'));
	}

	/* ---------------------------------------------------
	 *  NEW METHOD : Enqueue Swiper + the init file
	 * ------------------------------------------------- */
	public function enqueue_swiper_assets()
	{

		$settings = wcasc_get_settings();
		if (empty($settings['enable_sidebar'])) {
			return; // nothing to do if sidebar disabled
		}

		/* 1) Swiper library (you can keep using the CDN or
		      ship local copies inside the plugin)           */
		wp_enqueue_style(
			'swiper',
			'https://unpkg.com/swiper@9/swiper-bundle.min.css',
			array(),
			'9.0'
		);

		wp_enqueue_script(
			'swiper',
			'https://unpkg.com/swiper@9/swiper-bundle.min.js',
			array(),
			'9.0',
			true
		);

		/* 2) Init snippet – lives inside the plugin folder
		      eg:  /assets/js/wcasc-swiper.js               */
		wp_enqueue_script(
			'wcasc-swiper-init',
			plugins_url('assets/js/wcasc-swiper.js', __FILE__),
			array('jquery', 'swiper', 'wcasc-frontend'), // <- adjust handle if plugin’s main JS uses a different one
			'1.0',
			true
		);
	}

	/* ------------- rest of the class unchanged ---------------- */

	public function inline_variables_style()
	{
		$s = wcasc_get_settings();
		if (! $s['enable_sidebar']) return;

		$width_d = absint($s['width_desktop']);
		$width_m = absint($s['width_mobile']);
?>
		<style id="wcasc-variables">
			:root {
				--wcasc-accent: <?php echo esc_html($s['accent_color']); ?>;
				--wcasc-text: <?php echo esc_html($s['text_color']); ?>;
				--wcasc-savings: <?php echo esc_html($s['savings_color']); ?>;
				--wcasc-btn-bg: <?php echo esc_html($s['btn_bg']); ?>;
				--wcasc-btn-text: <?php echo esc_html($s['btn_text']); ?>;
				--wcasc-btn-radius: <?php echo esc_html($s['btn_radius']); ?>px;
				--wcasc-overlay: <?php echo esc_html($s['overlay_color']); ?>;
				--wcasc-width-desktop: <?php echo esc_html($width_d); ?>px;
				--wcasc-width-mobile: <?php echo esc_html($width_m); ?>vw;
			}

			<?php if ($s['inherit_fonts']) : ?>#wcasc-sidebar-cart,
			#wcasc-sidebar-cart * {
				font-family: inherit;
			}

			<?php endif; ?>
		</style>
<?php
	}

	public function output_markup()
	{
		$s = wcasc_get_settings();
		if (! $s['enable_sidebar']) return;

		// Floating icon (can be replaced by shortcode).
		echo '<button id="wcasc-cart-toggle" aria-label="' . esc_attr__('Open cart', 'wcasc') . '">
				<span class="wcasc-icon-cart"></span>
				<span id="wcasc-cart-count" class="wcasc-badge">' . esc_html(WC()->cart ? WC()->cart->get_cart_contents_count() : 0) . '</span>
			  </button>';

		// Sidebar cart content container.
		echo '<div id="wcasc-overlay" class="wcasc-overlay" role="presentation"></div>';

		// add currency & decimals on the aside so JS can format
		echo '<aside id="wcasc-sidebar-cart" class="wcasc-sidebar" aria-hidden="true" data-currency="' . esc_attr(get_woocommerce_currency()) . '" data-decimals="' . esc_attr(wc_get_price_decimals()) . '">';
		$this->render_sidebar();
		echo '</aside>';
	}

	public function shortcode_icon()
	{
		ob_start();
		echo '<button id="wcasc-cart-toggle" class="wcasc-shortcode-icon" aria-label="' . esc_attr__('Open cart', 'wcasc') . '">
				<span class="wcasc-icon-cart"></span>
				<span id="wcasc-cart-count" class="wcasc-badge">' . esc_html(WC()->cart ? WC()->cart->get_cart_contents_count() : 0) . '</span>
			  </button>';
		return ob_get_clean();
	}

	public function render_sidebar()
	{
		$settings = wcasc_get_settings();

		$threshold = (float) $settings['free_shipping_threshold'];
		$total     = wcasc_cart_running_total();
		$percent   = $threshold > 0 ? min(100, round(($total / $threshold) * 100)) : 100;
		$away      = max(0, $threshold - $total);

		$recommend_products = array();
		$limit = (int) $settings['sidebar_addon_limit'];
		// Add products selected directly
		if (! empty($settings['sidebar_addon_product_ids'])) {
			$products = wc_get_products([
				'include' => (array) $settings['sidebar_addon_product_ids'],
				'limit'   => $limit,
			]);
			foreach ($products as $p) {
				$recommend_products[$p->get_id()] = $p;
			}
		}
		// Add products from selected categories, up to limit
		if (! empty($settings['sidebar_addon_cat_ids'])) {
			$cat_products = wcasc_get_products_by_source(array(
				'source'       => 'categories',
				'category_ids' => (array) $settings['sidebar_addon_cat_ids'],
				'limit'        => $limit,
			));
			foreach ($cat_products as $p) {
				if (count($recommend_products) >= $limit) break;
				$recommend_products[$p->get_id()] = $p;
			}
		}
		// Use only values (products)
		$recommend_products = array_values($recommend_products);

		wcasc_get_template('sidebar-cart.php', array(
			'cart'     => WC()->cart,
			'settings' => $settings,
			'threshold' => $threshold,
			'percent'  => $percent,
			'away'     => $away,
			'recos'    => $recommend_products,
		));
	}

	public function fragments($fragments)
	{
		ob_start();
		$this->render_sidebar();
		$sidebar_html = ob_get_clean();

		$fragments['#wcasc-sidebar-cart'] = $sidebar_html;

		$count = (int) (WC()->cart ? WC()->cart->get_cart_contents_count() : 0);
		$fragments['#wcasc-cart-count'] = '<span id="wcasc-cart-count" class="wcasc-badge">' . esc_html($count) . '</span>';

		return $fragments;
	}
}
