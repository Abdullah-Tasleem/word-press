<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WCASC_Checkout_Addons_Admin {

	public function __construct() {
		add_action( 'init', array( $this, 'register_cpt' ) );
		add_action( 'add_meta_boxes', array( $this, 'meta_boxes' ) );
		add_action( 'save_post_wcasc_addon', array( $this, 'save_meta' ) );
	}

	public function register_cpt() {
		$labels = array(
			'name'               => __( 'Checkout Add-Ons', 'wcasc' ),
			'singular_name'      => __( 'Checkout Add-On', 'wcasc' ),
			'add_new'            => __( 'Add New', 'wcasc' ),
			'add_new_item'       => __( 'Add New Checkout Add-On', 'wcasc' ),
			'edit_item'          => __( 'Edit Checkout Add-On', 'wcasc' ),
			'new_item'           => __( 'New Checkout Add-On', 'wcasc' ),
			'view_item'          => __( 'View', 'wcasc' ),
			'search_items'       => __( 'Search', 'wcasc' ),
			'not_found'          => __( 'Not found', 'wcasc' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'wcasc' ),
			'menu_name'          => __( 'Checkout Add-Ons', 'wcasc' ),
		);

		register_post_type( 'wcasc_addon', array(
			'labels'        => $labels,
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => 'woocommerce',
			'supports'      => array( 'title' ),
			'menu_position' => 56,
		) );
	}

	public function meta_boxes() {
		add_meta_box( 'wcasc_addon_settings', __( 'Add-On Settings', 'wcasc' ), array( $this, 'render_settings_box' ), 'wcasc_addon', 'normal', 'high' );
		add_meta_box( 'wcasc_addon_preview', __( 'Live Preview', 'wcasc' ), array( $this, 'render_preview_box' ), 'wcasc_addon', 'side', 'default' );
	}

	public function render_settings_box( $post ) {
		wp_nonce_field( 'wcasc_addon_save', 'wcasc_addon_nonce' );

		$enabled       = (int) get_post_meta( $post->ID, '_wcasc_enabled', true );
		$heading       = get_post_meta( $post->ID, '_wcasc_heading', true );
		$source        = get_post_meta( $post->ID, '_wcasc_source', true ) ?: 'products';

		// Saved meta as CSV strings (back-compat) -> arrays for UI.
		$saved_products_csv  = (string) get_post_meta( $post->ID, '_wcasc_product_ids', true );
		$saved_categories_csv= (string) get_post_meta( $post->ID, '_wcasc_category_ids', true );

		$product_ids  = array_filter( array_map( 'absint', $saved_products_csv ? explode( ',', $saved_products_csv ) : array() ) );
		$category_ids = array_filter( array_map( 'absint', $saved_categories_csv ? explode( ',', $saved_categories_csv ) : array() ) );

		$limit         = absint( get_post_meta( $post->ID, '_wcasc_limit', true ) ?: 8 );

		?>
		<p>
			<label><input type="checkbox" name="wcasc_enabled" value="1" <?php checked( $enabled, 1 ); ?>>
				<?php esc_html_e( 'Enable this add-on group', 'wcasc' ); ?>
			</label>
		</p>

		<p>
			<label for="wcasc_heading"><?php esc_html_e( 'Heading', 'wcasc' ); ?></label><br>
			<input type="text" id="wcasc_heading" name="wcasc_heading" value="<?php echo esc_attr( $heading ?: __( 'You May Also Like …', 'wcasc' ) ); ?>" class="regular-text">
		</p>

		<p>
			<label for="wcasc_source"><?php esc_html_e( 'Source', 'wcasc' ); ?></label><br>
			<select id="wcasc_source" name="wcasc_source">
				<option value="products" <?php selected( $source, 'products' ); ?>><?php esc_html_e( 'Specific products', 'wcasc' ); ?></option>
				<option value="categories" <?php selected( $source, 'categories' ); ?>><?php esc_html_e( 'Product categories', 'wcasc' ); ?></option>
			</select>
		</p>

		<p id="wcasc_products_field">
			<label for="wcasc_product_ids"><?php esc_html_e( 'Products', 'wcasc' ); ?></label><br>
			<select id="wcasc_product_ids"
				name="wcasc_product_ids[]"
				class="wc-enhanced-select"
				multiple="multiple"
				style="width: 100%;"
				data-placeholder="<?php esc_attr_e( 'Select products…', 'wcasc' ); ?>"
				data-allow_clear="true">
				<?php
				$all_products = wc_get_products([ 'limit' => 200, 'orderby' => 'title', 'order' => 'ASC' ]);
				foreach ( $all_products as $product ) {
					$pid = $product->get_id();
					$label = wp_strip_all_tags( $product->get_formatted_name() );
					printf( '<option value="%d"%s>%s (#%d)</option>',
						$pid,
						selected( in_array( $pid, $product_ids, true ), true, false ),
						esc_html( $label ),
						$pid
					);
				}
				?>
			</select>
			<span class="description"><?php esc_html_e( 'Tip: Prefer simple products for one-click add to cart.', 'wcasc' ); ?></span>
		</p>

		<p id="wcasc_categories_field">
			<label for="wcasc_category_ids"><?php esc_html_e( 'Product categories', 'wcasc' ); ?></label><br>
			<select id="wcasc_category_ids"
				name="wcasc_category_ids[]"
				class="wc-enhanced-select"
				multiple="multiple"
				style="width: 100%;"
				data-placeholder="<?php esc_attr_e( 'Select categories…', 'wcasc' ); ?>"
				data-allow_clear="true">
				<?php
				$cats = get_terms( array(
					'taxonomy'   => 'product_cat',
					'hide_empty' => false,
					'number'     => 0,
				) );
				if ( ! is_wp_error( $cats ) && $cats ) {
					foreach ( $cats as $cat ) {
						printf(
							'<option value="%d"%s>%s (#%d)</option>',
							$cat->term_id,
							selected( in_array( $cat->term_id, $category_ids, true ), true, false ),
							esc_html( $cat->name ),
							$cat->term_id
						);
					}
				}
				?>
			</select>
		</p>

		<p>
			<label for="wcasc_limit"><?php esc_html_e( 'Max products to show', 'wcasc' ); ?></label><br>
			<input type="number" id="wcasc_limit" name="wcasc_limit" value="<?php echo esc_attr( $limit ); ?>" min="1" max="50" step="1">
		</p>

		<?php
	}

	public function render_preview_box( $post ) {
		$enabled      = (int) get_post_meta( $post->ID, '_wcasc_enabled', true );
		$heading      = get_post_meta( $post->ID, '_wcasc_heading', true ) ?: __( 'You May Also Like …', 'wcasc' );
		$source       = get_post_meta( $post->ID, '_wcasc_source', true ) ?: 'products';

		// CSV to arrays
		$product_ids  = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post->ID, '_wcasc_product_ids', true ) ) ) );
		$category_ids = array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post->ID, '_wcasc_category_ids', true ) ) ) );
		$limit        = absint( get_post_meta( $post->ID, '_wcasc_limit', true ) ?: 8 );

		$products = wcasc_get_products_by_source( array(
			'source'       => $source,
			'product_ids'  => $product_ids,
			'category_ids' => $category_ids,
			'limit'        => $limit,
		) );
		?>
		<div style="border:10px solid #ccd0d4;padding:10px;background:#fff;">
			<strong><?php echo esc_html( $heading ); ?></strong>
			<?php if ( ! $enabled ) : ?>
				<p style="color:#a00;"><?php esc_html_e( 'This add-on group is disabled.', 'wcasc' ); ?></p>
			<?php endif; ?>
			<div class="wcasc-preview-grid" style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:10px;">
				<?php if ( $products ) : foreach ( $products as $product ) : ?>
					<div style="border:1px solid #e5e5e5;padding:8px;border-radius:6px;">
						<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'style' => 'max-width:100%;height:auto;border-radius:4px;' ) ); ?>
						<div style="margin-top:6px;font-weight:600;"><?php echo esc_html( $product->get_name() ); ?></div>
						<div><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
						<button type="button" class="button" disabled><?php esc_html_e( 'Add', 'wcasc' ); ?></button>
					</div>
				<?php endforeach; else : ?>
					<p><?php esc_html_e( 'No products match your selection.', 'wcasc' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	public function save_meta( $post_id ) {
		if ( ! isset( $_POST['wcasc_addon_nonce'] ) || ! wp_verify_nonce( $_POST['wcasc_addon_nonce'], 'wcasc_addon_save' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$enabled = ! empty( $_POST['wcasc_enabled'] ) ? 1 : 0;
		update_post_meta( $post_id, '_wcasc_enabled', $enabled );

		$heading = sanitize_text_field( $_POST['wcasc_heading'] ?? '' );
		update_post_meta( $post_id, '_wcasc_heading', $heading );

		$source = in_array( $_POST['wcasc_source'] ?? 'products', array( 'products','categories' ), true ) ? $_POST['wcasc_source'] : 'products';
		update_post_meta( $post_id, '_wcasc_source', $source );

		// Convert arrays to CSV for storage (back-compat with frontend loader)
		$product_ids = array_map( 'absint', (array) ( $_POST['wcasc_product_ids'] ?? array() ) );
		$category_ids= array_map( 'absint', (array) ( $_POST['wcasc_category_ids'] ?? array() ) );

		update_post_meta( $post_id, '_wcasc_product_ids', implode( ',', array_filter( $product_ids ) ) );
		update_post_meta( $post_id, '_wcasc_category_ids', implode( ',', array_filter( $category_ids ) ) );

		update_post_meta( $post_id, '_wcasc_limit', absint( $_POST['wcasc_limit'] ?? 8 ) );
	}
}