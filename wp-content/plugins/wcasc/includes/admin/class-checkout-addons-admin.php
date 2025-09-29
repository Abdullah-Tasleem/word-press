<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WCASC_Checkout_Addons_Admin {

	public function __construct() {
    add_action( 'init', array( $this, 'register_cpt' ) );
    add_action( 'add_meta_boxes', array( $this, 'meta_boxes' ) );
    add_action( 'save_post_wcasc_addon', array( $this, 'save_meta' ) );

    // Load WC select2 styles/scripts
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
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

	public function enqueue_admin_assets( $hook ) {
    global $post_type;
    if ( $post_type === 'wcasc_addon' ) {
        // Load WC's select2
        wp_enqueue_script( 'select2' );
        wp_enqueue_style( 'select2' );
        wp_enqueue_script( 'wc-enhanced-select' );
        wp_enqueue_style( 'woocommerce_admin_styles' ); // ensures WC admin CSS loads

        // Init Select2
        wp_add_inline_script( 'select2', "
            jQuery(function($){
                $('#wcasc_product_ids, #wcasc_category_ids').select2({
                    placeholder: 'Select…',
                    allowClear: true,
                    width: '100%'
                });
            });
        " );
    }
}

	public function render_settings_box( $post ) {
		wp_nonce_field( 'wcasc_addon_save', 'wcasc_addon_nonce' );

		$enabled       = (int) get_post_meta( $post->ID, '_wcasc_enabled', true );
		$heading       = get_post_meta( $post->ID, '_wcasc_heading', true );
		$source        = get_post_meta( $post->ID, '_wcasc_source', true ) ?: 'products';

		$saved_products_csv   = (string) get_post_meta( $post->ID, '_wcasc_product_ids', true );
		$saved_categories_csv = (string) get_post_meta( $post->ID, '_wcasc_category_ids', true );

		$product_ids  = array_filter( array_map( 'absint', $saved_products_csv ? explode( ',', $saved_products_csv ) : array() ) );
		$category_ids = array_filter( array_map( 'absint', $saved_categories_csv ? explode( ',', $saved_categories_csv ) : array() ) );

		$limit = absint( get_post_meta( $post->ID, '_wcasc_limit', true ) ?: 8 );
		?>
		<div class="wcasc-admin">
			<div class="wcasc-card">

				<p class="wcasc-field">
					<label class="wcasc-switch">
						<input type="checkbox" name="wcasc_enabled" value="1" <?php checked( $enabled, 1 ); ?>>
						<span class="wcasc-slider" aria-hidden="true"></span>
						<span class="wcasc-switch-label"><?php esc_html_e( 'Enable this add-on group', 'wcasc' ); ?></span>
					</label>
				</p>

				<div class="wcasc-grid">
					<div class="wcasc-field">
						<label for="wcasc_heading"><?php esc_html_e( 'Heading', 'wcasc' ); ?></label>
						<input type="text" id="wcasc_heading" name="wcasc_heading" value="<?php echo esc_attr( $heading ?: __( 'You May Also Like …', 'wcasc' ) ); ?>" class="regular-text">
					</div>

					<div class="wcasc-field">
						<label for="wcasc_source"><?php esc_html_e( 'Source', 'wcasc' ); ?></label>
						<select id="wcasc_source" name="wcasc_source">
							<option value="products" <?php selected( $source, 'products' ); ?>><?php esc_html_e( 'Specific products', 'wcasc' ); ?></option>
							<option value="categories" <?php selected( $source, 'categories' ); ?>><?php esc_html_e( 'Product categories', 'wcasc' ); ?></option>
						</select>
					</div>
				</div>

				<p id="wcasc_products_field" class="wcasc-field">
					<label for="wcasc_product_ids"><?php esc_html_e( 'Products', 'wcasc' ); ?></label>
					<select id="wcasc_product_ids"
						name="wcasc_product_ids[]"
						class="wc-enhanced-select"
						multiple
						style="width: 100%;"
						data-placeholder="<?php esc_attr_e( 'Select products…', 'wcasc' ); ?>"
						data-allow_clear="true">
						<?php
						$all_products = wc_get_products([ 'limit' => 200, 'orderby' => 'title', 'order' => 'ASC' ]);
						foreach ( $all_products as $product ) {
							$pid   = $product->get_id();
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
					<span class="wcasc-selected-count" data-count-for="wcasc_product_ids"></span>
					<span class="description"><?php esc_html_e( 'Tip: Prefer simple products for one-click add to cart.', 'wcasc' ); ?></span>
				</p>

				<p id="wcasc_categories_field" class="wcasc-field">
					<label for="wcasc_category_ids"><?php esc_html_e( 'Product categories', 'wcasc' ); ?></label>
					<select id="wcasc_category_ids"
						name="wcasc_category_ids[]"
						class="wc-enhanced-select"
						multiple
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
					<span class="wcasc-selected-count" data-count-for="wcasc_category_ids"></span>
				</p>

				<p class="wcasc-field">
					<label for="wcasc_limit"><?php esc_html_e( 'Max products to show', 'wcasc' ); ?></label>
					<input type="number" id="wcasc_limit" name="wcasc_limit" value="<?php echo esc_attr( $limit ); ?>" min="1" max="50" step="1">
				</p>

			</div>
		</div>

		<script>
		jQuery(function($){
			function toggleSourceFields(){
				var src = $('#wcasc_source').val();
				$('#wcasc_products_field').toggle(src === 'products');
				$('#wcasc_categories_field').toggle(src === 'categories');
			}
			function updateCount(id){
				var $el = $('#'+id), count = 0;
				if ($el.length){
					var val = $el.val();
					if (Array.isArray(val)) count = val.filter(Boolean).length;
				}
				$('.wcasc-selected-count[data-count-for="'+id+'"]').text(count ? (<?php echo json_encode( __( 'Selected', 'wcasc' ) ); ?> + ': ' + count) : '');
			}

			// Init
			toggleSourceFields();
			updateCount('wcasc_product_ids');
			updateCount('wcasc_category_ids');

			// Bind
			$('#wcasc_source').on('change', toggleSourceFields);
			$('#wcasc_product_ids, #wcasc_category_ids').on('change', function(){ updateCount(this.id); });

			// Ensure select2 enhances our fields
			$('#wcasc_product_ids, #wcasc_category_ids').select2({
				placeholder: function(){
					return $(this).data('placeholder');
				},
				allowClear: true,
				width: '100%'
			}).on('select2:select select2:unselect', function(){ updateCount(this.id); });
		});
		</script>
		<?php
	}

	public function render_preview_box( $post ) {
		$enabled      = (int) get_post_meta( $post->ID, '_wcasc_enabled', true );
		$heading      = get_post_meta( $post->ID, '_wcasc_heading', true ) ?: __( 'You May Also Like …', 'wcasc' );
		$source       = get_post_meta( $post->ID, '_wcasc_source', true ) ?: 'products';

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
		<div class="wcasc-preview">
			<div class="wcasc-preview-header">
				<span class="dashicons dashicons-cart"></span>
				<strong><?php esc_html_e( 'Checkout Add-Ons Preview', 'wcasc' ); ?></strong>
				<span class="wcasc-status <?php echo $enabled ? 'enabled' : 'disabled'; ?>">
					<?php echo $enabled ? esc_html__( 'Enabled', 'wcasc' ) : esc_html__( 'Disabled', 'wcasc' ); ?>
				</span>
			</div>
			<div class="wcasc-preview-body">
				<div class="wcasc-heading"><?php echo esc_html( $heading ); ?></div>
				<?php if ( ! $enabled ) : ?>
					<div class="wcasc-preview-note"><?php esc_html_e( 'This add-on group is disabled. Enable it to display on checkout.', 'wcasc' ); ?></div>
				<?php endif; ?>

				<div class="wcasc-preview-grid">
					<?php if ( $products ) : foreach ( $products as $product ) : ?>
						<div class="wcasc-card">
							<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'style' => 'max-width:100%;height:auto;border-radius:4px;' ) ); ?>
							<div class="wcasc-title"><?php echo esc_html( $product->get_name() ); ?></div>
							<div><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
							<div class="wcasc-footer">
								<button type="button" class="button" disabled><?php esc_html_e( 'Add', 'wcasc' ); ?></button>
							</div>
						</div>
					<?php endforeach; else : ?>
						<p class="wcasc-preview-note"><?php esc_html_e( 'No products match your selection.', 'wcasc' ); ?></p>
					<?php endif; ?>
				</div>

				<div class="wcasc-preview-footer">
					<?php
					printf(
						'%s %d • %s %s',
						esc_html__( 'Showing up to', 'wcasc' ),
						intval( $limit ),
						esc_html__( 'Source:', 'wcasc' ),
						$source === 'categories' ? esc_html__( 'Categories', 'wcasc' ) : esc_html__( 'Products', 'wcasc' )
					);
					?>
				</div>
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

		$product_ids  = array_map( 'absint', (array) ( $_POST['wcasc_product_ids'] ?? array() ) );
		$category_ids = array_map( 'absint', (array) ( $_POST['wcasc_category_ids'] ?? array() ) );

		update_post_meta( $post_id, '_wcasc_product_ids', implode( ',', array_filter( $product_ids ) ) );
		update_post_meta( $post_id, '_wcasc_category_ids', implode( ',', array_filter( $category_ids ) ) );

		update_post_meta( $post_id, '_wcasc_limit', absint( $_POST['wcasc_limit'] ?? 8 ) );
	}
}
