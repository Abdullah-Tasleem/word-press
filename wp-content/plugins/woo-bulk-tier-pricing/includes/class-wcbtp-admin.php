<?php
if (!defined('ABSPATH')) exit;

class WCBTP_Admin {
    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function init() {
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_' . WCBTP_Rules::CPT, [$this, 'save_rule']);
        add_filter('manage_edit-' . WCBTP_Rules::CPT . '_columns', [$this, 'columns']);
        add_action('manage_' . WCBTP_Rules::CPT . '_posts_custom_column', [$this, 'column_content'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function enqueue_admin_assets($hook) {
        global $post_type;
        if ($post_type === WCBTP_Rules::CPT) {
            wp_enqueue_style('wcbtp-admin', WCBTP_URL . 'assets/css/admin.css', [], WCBTP_VERSION);
            wp_enqueue_script('wcbtp-admin', WCBTP_URL . 'assets/js/admin.js', ['jquery'], WCBTP_VERSION, true);
            // WooCommerce enhanced select for product search
            if (function_exists('wc_enqueue_js')) {
                wp_enqueue_script('wc-enhanced-select');
                wp_enqueue_style('woocommerce_admin_styles');
            }
        }
    }

    public function add_meta_boxes() {
        add_meta_box(
            'wcbtp_rule_settings',
            __('Rule Settings', 'wcbtp'),
            [$this, 'render_rule_box'],
            WCBTP_Rules::CPT,
            'normal',
            'high'
        );
    }

    public function render_rule_box($post) {
        wp_nonce_field('wcbtp_save_rule', 'wcbtp_nonce');

        $rule = WCBTP_Rules::rule_to_array($post->ID);
        $enabled  = $rule['enabled'];
        $scope    = $rule['scope'];
        $products = $rule['products'];
        $cats     = $rule['cats'];
        $priority = $rule['priority'];
        $tiers    = $rule['tiers'];

        // Preload selected products as options
        $selected_products = [];
        if (!empty($products)) {
            foreach ($products as $pid) {
                $p = wc_get_product($pid);
                if ($p) {
                    $selected_products[] = [
                        'id' => $pid,
                        'text' => $p->get_formatted_name()
                    ];
                }
            }
        }
        // All product categories for selection
        $all_cats = get_terms([
            'taxonomy' => 'product_cat',
            'hide_empty' => false
        ]);
        ?>
        <div class="wcbtp-field">
            <label>
                <input type="checkbox" name="wcbtp_enabled" value="yes" <?php checked($enabled, true); ?> />
                <?php esc_html_e('Enable this rule', 'wcbtp'); ?>
            </label>
        </div>
        <div class="wcbtp-field">
            <label for="wcbtp_scope"><strong><?php esc_html_e('Scope', 'wcbtp'); ?></strong></label><br/>
            <label><input type="radio" name="wcbtp_scope" value="product" <?php checked($scope, 'product'); ?> /> <?php esc_html_e('Product-specific', 'wcbtp'); ?></label>
            &nbsp;&nbsp;
            <label><input type="radio" name="wcbtp_scope" value="category" <?php checked($scope, 'category'); ?> /> <?php esc_html_e('Category-based', 'wcbtp'); ?></label>
            <p class="description"><?php esc_html_e('Choose whether this rule targets specific products or product categories.', 'wcbtp'); ?></p>
        </div>

        <div class="wcbtp-field wcbtp-scope wcbtp-scope-product" style="<?php echo $scope === 'product' ? '' : 'display:none;'; ?>">
            <label for="wcbtp_product_ids"><strong><?php esc_html_e('Products', 'wcbtp'); ?></strong></label>
            <select class="wc-product-search" multiple="multiple" style="width:100%;" id="wcbtp_product_ids" name="wcbtp_product_ids[]" data-placeholder="<?php esc_attr_e('Search for products…', 'wcbtp'); ?>" data-action="woocommerce_json_search_products_and_variations">
                <?php
                if (!empty($selected_products)) {
                    foreach ($selected_products as $sp) {
                        printf('<option value="%d" selected="selected">%s</option>', absint($sp['id']), esc_html($sp['text']));
                    }
                }
                ?>
            </select>
            <p class="description"><?php esc_html_e('Select one or more products (variations inherit the parent rule).', 'wcbtp'); ?></p>
        </div>

        <div class="wcbtp-field wcbtp-scope wcbtp-scope-category" style="<?php echo $scope === 'category' ? '' : 'display:none;'; ?>">
            <label for="wcbtp_category_ids"><strong><?php esc_html_e('Categories', 'wcbtp'); ?></strong></label>
            <select multiple="multiple" style="width:100%;" id="wcbtp_category_ids" name="wcbtp_category_ids[]">
                <?php
                if (!empty($all_cats) && !is_wp_error($all_cats)) {
                    foreach ($all_cats as $term) {
                        printf('<option value="%d" %s>%s</option>',
                            absint($term->term_id),
                            selected(in_array($term->term_id, $cats, true), true, false),
                            esc_html($term->name)
                        );
                    }
                }
                ?>
            </select>
            <p class="description"><?php esc_html_e('Select one or more product categories for this rule.', 'wcbtp'); ?></p>
        </div>

        <div class="wcbtp-field">
            <label for="wcbtp_priority"><strong><?php esc_html_e('Priority', 'wcbtp'); ?></strong></label>
            <input type="number" id="wcbtp_priority" name="wcbtp_priority" value="<?php echo esc_attr(intval($priority)); ?>" min="1" step="1" style="width:90px;" />
            <p class="description"><?php esc_html_e('Lower numbers run first. Product rules override category rules by default.', 'wcbtp'); ?></p>
        </div>

        <hr/>
        <div class="wcbtp-field">
            <label><strong><?php esc_html_e('Tiers', 'wcbtp'); ?></strong></label>
            <table class="widefat wcbtp-tiers">
                <thead>
                    <tr>
                        <th style="width:20%"><?php esc_html_e('Min Qty', 'wcbtp'); ?></th>
                        <th style="width:30%"><?php esc_html_e('Discount Type', 'wcbtp'); ?></th>
                        <th style="width:30%"><?php esc_html_e('Amount', 'wcbtp'); ?></th>
                        <th style="width:20%"></th>
                    </tr>
                </thead>
                <tbody id="wcbtp-tier-rows">
                    <?php
                    if (empty($tiers)) {
                        $tiers = [
                            [ 'min_qty' => 1, 'type' => 'fixed', 'amount' => 0 ],
                        ];
                    }
                    foreach ($tiers as $index => $t) : ?>
                        <tr class="wcbtp-tier-row">
                            <td><input type="number" name="wcbtp_tiers[<?php echo esc_attr($index); ?>][min_qty]" min="1" step="1" value="<?php echo esc_attr($t['min_qty']); ?>" /></td>
                            <td>
                                <select name="wcbtp_tiers[<?php echo esc_attr($index); ?>][type]">
                                    <option value="percentage" <?php selected($t['type'], 'percentage'); ?>><?php esc_html_e('Percentage (%)', 'wcbtp'); ?></option>
                                    <option value="fixed" <?php selected($t['type'], 'fixed'); ?>><?php esc_html_e('Fixed amount', 'wcbtp'); ?></option>
                                </select>
                            </td>
                            <td><input type="number" name="wcbtp_tiers[<?php echo esc_attr($index); ?>][amount]" step="0.0001" min="0" value="<?php echo esc_attr($t['amount']); ?>" /></td>
                            <td><button class="button wcbtp-remove-tier"><?php esc_html_e('Remove', 'wcbtp'); ?></button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p><button class="button button-primary" id="wcbtp-add-tier"><?php esc_html_e('Add Tier', 'wcbtp'); ?></button></p>
            <p class="description"><?php esc_html_e('Define quantity breakpoints and their discounts. Example: Min 1 = 0, Min 5 = 10% off, Min 10 = $5 off.', 'wcbtp'); ?></p>
        </div>

        <script>
        jQuery(function($){
            $('input[name="wcbtp_scope"]').on('change', function(){
                var scope = $(this).val();
                $('.wcbtp-scope').hide();
                $('.wcbtp-scope-' + scope).show();
            });
        });
        </script>
        <?php
    }

    public function save_rule($post_id) {
        if (!isset($_POST['wcbtp_nonce']) || !wp_verify_nonce($_POST['wcbtp_nonce'], 'wcbtp_save_rule')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        $enabled = isset($_POST['wcbtp_enabled']) && $_POST['wcbtp_enabled'] === 'yes' ? 'yes' : 'no';
        $scope   = isset($_POST['wcbtp_scope']) && in_array($_POST['wcbtp_scope'], ['product','category'], true) ? $_POST['wcbtp_scope'] : 'product';
        $products = isset($_POST['wcbtp_product_ids']) ? array_map('absint', (array) $_POST['wcbtp_product_ids']) : [];
        $cats     = isset($_POST['wcbtp_category_ids']) ? array_map('absint', (array) $_POST['wcbtp_category_ids']) : [];
        $priority = isset($_POST['wcbtp_priority']) ? intval($_POST['wcbtp_priority']) : 10;

        $tiers = isset($_POST['wcbtp_tiers']) ? (array) $_POST['wcbtp_tiers'] : [];
        $tiers = wcbtp_sanitize_tiers($tiers);

        update_post_meta($post_id, WCBTP_Rules::META_ENABLED, $enabled);
        update_post_meta($post_id, WCBTP_Rules::META_SCOPE, $scope);
        update_post_meta($post_id, WCBTP_Rules::META_PRODUCTS, $products);
        // update_post_meta($post_id, WCBTP_Rules::META_CATEGORY, $cats);
        update_post_meta($post_id, WCBTP_Rules::META_CATS, $cats);
        update_post_meta($post_id, WCBTP_Rules::META_PRIORITY, max(1, $priority));
        update_post_meta($post_id, WCBTP_Rules::META_TIERS, $tiers);

        WCBTP_Rules::clear_cache();
    }

    public function columns($columns) {
        $new = [];
        foreach ($columns as $k => $label) {
            $new[$k] = $label;
            if ($k === 'title') {
                $new['wcbtp_scope'] = __('Scope', 'wcbtp');
                $new['wcbtp_targets'] = __('Targets', 'wcbtp');
                $new['wcbtp_tiers'] = __('Tiers', 'wcbtp');
                $new['wcbtp_enabled'] = __('Enabled', 'wcbtp');
                $new['wcbtp_priority'] = __('Priority', 'wcbtp');
            }
        }
        return $new;
    }

    public function column_content($column, $post_id) {
        $rule = WCBTP_Rules::rule_to_array($post_id);
        switch ($column) {
            case 'wcbtp_scope':
                echo esc_html(ucfirst($rule['scope']));
                break;
            case 'wcbtp_targets':
                if ($rule['scope'] === 'product') {
                    $names = [];
                    foreach ($rule['products'] as $pid) {
                        $p = wc_get_product($pid);
                        if ($p) $names[] = $p->get_name();
                    }
                    echo esc_html(implode(', ', $names));
                } else {
                    $names = [];
                    foreach ($rule['cats'] as $cid) {
                        $term = get_term($cid, 'product_cat');
                        if ($term && !is_wp_error($term)) $names[] = $term->name;
                    }
                    echo esc_html(implode(', ', $names));
                }
                break;
            case 'wcbtp_tiers':
                $parts = [];
                foreach ($rule['tiers'] as $i => $t) {
                    $parts[] = sprintf('%s %s%s',
                        intval($t['min_qty']),
                        $t['type'] === 'percentage' ? intval($t['amount']) . '%' : wc_price(floatval($t['amount'])),
                        $t['type'] === 'percentage' ? '' : ''
                    );
                }
                echo esc_html(implode(' | ', $parts));
                break;
            case 'wcbtp_enabled':
                echo $rule['enabled'] ? '✅' : '—';
                break;
            case 'wcbtp_priority':
                echo esc_html(intval($rule['priority']));
                break;
        }
    }
}