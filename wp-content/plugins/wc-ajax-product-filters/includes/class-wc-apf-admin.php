<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class WC_APF_Admin {

	public function __construct() {
		add_action( 'admin_menu',        array( $this, 'add_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_ajax_wc_apf_save',    array( $this, 'save_options' ) );
	}

	public function add_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'AJAX Product Filters', 'wc-apf' ),
			__( 'AJAX Product Filters', 'wc-apf' ),
			'manage_woocommerce',
			'wc-apf',
			array( $this, 'settings_page' )
		);
	}

	public function enqueue( $hook ) {
		if ( $hook !== 'woocommerce_page_wc-apf' ) return;

		wp_enqueue_style( 'wp-components' ); // for switches
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style ( 'wc-apf-admin', WC_APF_URL . 'assets/css/filters.css', array(), WC_APF_VER );
		wp_enqueue_script( 'wc-apf-admin', WC_APF_URL . 'assets/js/filters-admin.js', array( 'jquery', 'jquery-ui-sortable' ), WC_APF_VER, true );
		wp_localize_script( 'wc-apf-admin', 'WC_APF',
			array(
				'ajax'   => admin_url( 'admin-ajax.php' ),
				'nonce'  => wp_create_nonce( 'wc_apf_admin' ),
				'msg_ok' => __( 'Saved!', 'wc-apf' ),
			)
		);
	}

	public function settings_page() {
		$options = get_option( 'wc_apf_options', WC_APF_Helpers::defaults() );
		$order   = $options['order'];
		$enabled = $options['enabled'];
		?>
		<div class="wrap">
			<h1>Woo AJAX Product Filters</h1>

			<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Filters Enabled & Order', 'wc-apf' ); ?></th>
				<td>
					<p><?php esc_html_e( 'Drag to change order, toggle to enable/disable.', 'wc-apf' ); ?></p>
					<ul id="wc-apf-sortable" class="wc-apf-sortable">
						<?php foreach ( $order as $slug ) : 
							$label = ucfirst( $slug );
							$checked = in_array( $slug, $enabled ) ? 'checked' : '';
							?>
							<li data-slug="<?php echo esc_attr( $slug ); ?>">
								<span class="dashicons dashicons-menu"></span>
								<?php echo esc_html( $label ); ?>
								<label class="switch">
									<input type="checkbox" value="<?php echo esc_attr( $slug ); ?>" <?php echo $checked; ?> >
									<span class="slider round"></span>
								</label>
							</li>
						<?php endforeach; ?>
					</ul>
					<p><button class="button button-primary" id="wc-apf-save"><?php _e( 'Save Changes', 'wc-apf' ); ?></button></p>
				</td>
			</tr>
			</table>
		</div>
		<?php
	}

	/* ----------  save AJAX ------------- */
	public function save_options() {

		check_ajax_referer( 'wc_apf_admin', 'nonce' );

		$order   = isset( $_POST['order'] )   ? array_map( 'sanitize_text_field', $_POST['order'] )   : array();
		$enabled = isset( $_POST['enabled'] ) ? array_map( 'sanitize_text_field', $_POST['enabled'] ) : array();

		WC_APF_Helpers::update_option( 'order',   $order );
		WC_APF_Helpers::update_option( 'enabled', $enabled );

		wp_send_json_success();
	}
}

new WC_APF_Admin();