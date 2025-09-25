<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class WC_APF_Frontend {

	public function __construct() {
		// assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

		// inject sidebar before products
		add_action( 'woocommerce_before_shop_loop', array( $this, 'sidebar' ), 5 );

		// AJAX endpoint
		add_action( 'wp_ajax_wc_apf_filter',       array( $this, 'ajax_filter' ) );
		add_action( 'wp_ajax_nopriv_wc_apf_filter',array( $this, 'ajax_filter' ) );
	}

	/* ----------  assets ------------- */
	public function enqueue() {
		if ( ! is_shop() && ! is_product_taxonomy() ) return;

		// CSS
		wp_enqueue_style( 'wc-apf', WC_APF_URL . 'assets/css/filters.css', array(), WC_APF_VER );

		// JS
		wp_enqueue_script( 'wc-apf', WC_APF_URL . 'assets/js/filters.js', array( 'jquery' ), WC_APF_VER, true );

		wp_localize_script( 'wc-apf', 'WC_APF',
			array(
				'ajax'     => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wc_apf' ),
				'layout'   => array(
					'sidebar_w' => WC_APF_Helpers::get_option( 'sidebar_w' ),
				)
			)
		);
	}

	/* ---------- sidebar markup ------------- */
	public function sidebar() {

		$enabled = WC_APF_Helpers::get_option( 'enabled' );
		$order   = WC_APF_Helpers::get_option( 'order' );

		/* Wrapper open */
		echo '<div id="wc-apf-wrapper" style="width:' . intval( WC_APF_Helpers::get_option( 'sidebar_w' ) ) . '%">';

		include WC_APF_PATH . 'templates/sidebar.php';

		echo '</div>';
	}

	/* ---------- AJAX: filter products ------------- */
	public function ajax_filter() {

		check_ajax_referer( 'wc_apf', 'nonce' );

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		);

		$tax_query  = array( 'relation' => 'AND' );
		$meta_query = array();

		// COLORS
		if ( ! empty( $_POST['color'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'pa_color',
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_text_field', $_POST['color'] ),
				'operator' => 'IN',
			);
		}

		// SIZE
		if ( ! empty( $_POST['size'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'pa_size',
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_text_field', $_POST['size'] ),
				'operator' => 'IN',
			);
		}

		// GENDER
		if ( ! empty( $_POST['gender'] ) ) {
			$tax_query[] = array(
				'taxonomy' => 'pa_gender',
				'field'    => 'slug',
				'terms'    => array_map( 'sanitize_text_field', $_POST['gender'] ),
				'operator' => 'IN',
			);
		}

		// PRICE
		$min = isset( $_POST['min_price'] ) ? floatval( $_POST['min_price'] ) : 0;
		$max = isset( $_POST['max_price'] ) ? floatval( $_POST['max_price'] ) : 0;
		if ( $min || $max ) {
			$meta_query[] = array(
				'key'     => '_price',
				'value'   => array( $min, $max ),
				'compare' => 'BETWEEN',
				'type'    => 'DECIMAL',
			);
		}

		if ( count( $tax_query ) > 1 ) {
			$args['tax_query'] = $tax_query;
		}
		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = $meta_query;
		}

		$query = new WP_Query( $args );

		ob_start();
		if ( $query->have_posts() ) {
			wc_get_template_part( 'loop/loop-start' );
			while ( $query->have_posts() ) {
				$query->the_post();
				wc_get_template_part( 'content', 'product' );
			}
			wc_get_template_part( 'loop/loop-end' );
			wp_reset_postdata();
		} else {
			echo '<p class="woocommerce-info">' . __( 'No products found matching your selection.', 'wc-apf' ) . '</p>';
		}
		$html = ob_get_clean();

		wp_send_json_success( $html );
	}
}

new WC_APF_Frontend();