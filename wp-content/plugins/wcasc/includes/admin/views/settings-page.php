<?php
if (! defined('ABSPATH')) exit;
$cats = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => false));
?>
<div class="wrap wcasc-settings">
	<h1><?php esc_html_e('WC Add-Ons & Sidebar Cart', 'wcasc'); ?></h1>

	<form method="post">
		<h2><?php esc_html_e('Sidebar Add-On Products', 'wcasc'); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e('Products to recommend', 'wcasc'); ?></th>
				<td>
					<select id="wcasc_sidebar_addon_product_ids"
						name="wcasc[sidebar_addon_product_ids][]"
						class="wc-enhanced-select"
						multiple="multiple"
						style="min-width: 340px;"
						data-placeholder="<?php esc_attr_e('Select products…', 'wcasc'); ?>"
						data-allow_clear="true">
						<?php
						$products = wc_get_products([ 'limit' => 100, 'orderby' => 'title', 'order' => 'ASC' ]);
						foreach ($products as $product) : ?>
							<option value="<?php echo esc_attr($product->get_id()); ?>" <?php selected(isset($settings['sidebar_addon_product_ids']) && in_array($product->get_id(), (array) $settings['sidebar_addon_product_ids'], true)); ?>>
								<?php echo esc_html($product->get_name() . ' (#' . $product->get_id() . ')'); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e('Select products to show as add-ons in the sidebar cart.', 'wcasc'); ?></p>
				</td>
			</tr>
		</table>
		<?php wp_nonce_field('wcasc_save_settings', 'wcasc_settings_nonce'); ?>

		<h2><?php esc_html_e('Sidebar Cart Settings', 'wcasc'); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e('Enable Sidebar Cart', 'wcasc'); ?></th>
				<td><label><input type="checkbox" name="wcasc[enable_sidebar]" value="1" <?php checked($settings['enable_sidebar'], 1); ?>> <?php esc_html_e('Enable', 'wcasc'); ?></label></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Inherit fonts from theme', 'wcasc'); ?></th>
				<td><label><input type="checkbox" name="wcasc[inherit_fonts]" value="1" <?php checked($settings['inherit_fonts'], 1); ?>></label></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Show strike-through prices (regular vs sale)', 'wcasc'); ?></th>
				<td><label><input type="checkbox" name="wcasc[show_strike]" value="1" <?php checked($settings['show_strike'], 1); ?>></label></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Show subtotal line', 'wcasc'); ?></th>
				<td><label><input type="checkbox" name="wcasc[show_subtotal]" value="1" <?php checked($settings['show_subtotal'], 1); ?>></label></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Free shipping threshold', 'wcasc'); ?></th>
				<td><input type="number" step="0.01" name="wcasc[free_shipping_threshold]" value="<?php echo esc_attr($settings['free_shipping_threshold']); ?>"></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Cart width (desktop, px)', 'wcasc'); ?></th>
				<td><input type="number" min="320" max="700" name="wcasc[width_desktop]" value="<?php echo esc_attr($settings['width_desktop']); ?>"></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Cart width (mobile, % of viewport)', 'wcasc'); ?></th>
				<td><input type="number" min="80" max="100" name="wcasc[width_mobile]" value="<?php echo esc_attr($settings['width_mobile']); ?>"></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Accent color', 'wcasc'); ?></th>
				<td><input type="text" class="regular-text" name="wcasc[accent_color]" value="<?php echo esc_attr($settings['accent_color']); ?>"></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Text color', 'wcasc'); ?></th>
				<td><input type="text" class="regular-text" name="wcasc[text_color]" value="<?php echo esc_attr($settings['text_color']); ?>"></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Savings text color', 'wcasc'); ?></th>
				<td><input type="text" class="regular-text" name="wcasc[savings_color]" value="<?php echo esc_attr($settings['savings_color']); ?>"></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Button background', 'wcasc'); ?></th>
				<td><input type="text" class="regular-text" name="wcasc[btn_bg]" value="<?php echo esc_attr($settings['btn_bg']); ?>"></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Button text color', 'wcasc'); ?></th>
				<td><input type="text" class="regular-text" name="wcasc[btn_text]" value="<?php echo esc_attr($settings['btn_text']); ?>"></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Button radius (px)', 'wcasc'); ?></th>
				<td><input type="number" min="0" max="40" name="wcasc[btn_radius]" value="<?php echo esc_attr($settings['btn_radius']); ?>"></td>
			</tr>
			<tr>
				<th><?php esc_html_e('Overlay color (rgba)', 'wcasc'); ?></th>
				<td><input type="text" class="regular-text" name="wcasc[overlay_color]" value="<?php echo esc_attr($settings['overlay_color']); ?>"></td>
			</tr>
		</table>

		<h2><?php esc_html_e('Sidebar Add-Ons (Slider)', 'wcasc'); ?></h2>
		<table class="form-table">
			<tr>
				<th><?php esc_html_e('Product categories to recommend', 'wcasc'); ?></th>
				<td>
					<select id="wcasc_sidebar_addon_cat_ids"
						name="wcasc[sidebar_addon_cat_ids][]"
						class="wc-enhanced-select"
						multiple="multiple"
						style="min-width: 340px;"
						data-placeholder="<?php esc_attr_e('Select categories…', 'wcasc'); ?>"
						data-allow_clear="true">
						<?php foreach ($cats as $cat) : ?>
							<option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected(in_array($cat->term_id, (array) $settings['sidebar_addon_cat_ids'], true)); ?>>
								<?php echo esc_html($cat->name . ' (#' . $cat->term_id . ')'); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e('Shown as a slider in the sidebar cart under “You’ll love these”.', 'wcasc'); ?></p>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e('Max products in slider', 'wcasc'); ?></th>
				<td><input type="number" min="1" max="30" name="wcasc[sidebar_addon_limit]" value="<?php echo esc_attr($settings['sidebar_addon_limit']); ?>"></td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>
</div>