<?php
if (!defined('ABSPATH')) exit;

class WCBTP_Rules {
    const CPT = 'wcbtp_rule';
    const META_ENABLED   = '_wcbtp_enabled';
    const META_SCOPE     = '_wcbtp_scope'; // product|category
    const META_PRODUCTS  = '_wcbtp_product_ids'; // array
    const META_CATS      = '_wcbtp_category_ids'; // array
    const META_PRIORITY  = '_wcbtp_priority'; // int (lower means higher priority)
    const META_TIERS     = '_wcbtp_tiers'; // array

    protected static $cache = null;

    public static function register_cpt() {
        $labels = [
            'name'               => __('Bulk Pricing Rules', 'wcbtp'),
            'singular_name'      => __('Bulk Pricing Rule', 'wcbtp'),
            'add_new'            => __('Add Rule', 'wcbtp'),
            'add_new_item'       => __('Add New Bulk Pricing Rule', 'wcbtp'),
            'edit_item'          => __('Edit Bulk Pricing Rule', 'wcbtp'),
            'new_item'           => __('New Bulk Pricing Rule', 'wcbtp'),
            'view_item'          => __('View Bulk Pricing Rule', 'wcbtp'),
            'search_items'       => __('Search Rules', 'wcbtp'),
            'not_found'          => __('No rules found', 'wcbtp'),
            'not_found_in_trash' => __('No rules found in Trash', 'wcbtp'),
            'menu_name'          => __('Bulk & Tier Pricing', 'wcbtp'),
        ];

        register_post_type(self::CPT, [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => 'woocommerce',
            'capability_type'    => 'post',
            'map_meta_cap'       => true,
            'supports'           => ['title'],
            'menu_icon'          => 'dashicons-editor-table',
        ]);

        // Clear cache on change
        add_action('save_post_' . self::CPT, [__CLASS__, 'clear_cache']);
        add_action('deleted_post', function ($post_id) {
            $post = get_post($post_id);
            if ($post && $post->post_type === self::CPT) {
                self::clear_cache();
            }
        });
    }

    public static function clear_cache() {
        self::$cache = null;
        delete_transient('wcbtp_rules_cache');
    }

    public static function get_rules() {
        if (self::$cache !== null) {
            return self::$cache;
        }
        $cached = get_transient('wcbtp_rules_cache');
        if (is_array($cached)) {
            self::$cache = $cached;
            return $cached;
        }

        $q = new WP_Query([
            'post_type'      => self::CPT,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_key'       => self::META_ENABLED,
            'meta_value'     => 'yes',
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ]);

        $rules = [];
        foreach ($q->posts as $post) {
            $rules[] = self::rule_to_array($post->ID);
        }

        // Sort by scope priority then by numeric priority (ascending)
        usort($rules, function ($a, $b) {
            // product rules first
            if ($a['scope'] !== $b['scope']) {
                return $a['scope'] === 'product' ? -1 : 1;
            }
            return intval($a['priority']) <=> intval($b['priority']);
        });

        set_transient('wcbtp_rules_cache', $rules, MINUTE_IN_SECONDS * 10);
        self::$cache = $rules;
        return $rules;
    }

    public static function rule_to_array($post_id) {
        $enabled = get_post_meta($post_id, self::META_ENABLED, true) === 'yes';
        $scope   = get_post_meta($post_id, self::META_SCOPE, true) ?: 'product';
        $products = get_post_meta($post_id, self::META_PRODUCTS, true);
        $cats     = get_post_meta($post_id, self::META_CATS, true);
        $priority = intval(get_post_meta($post_id, self::META_PRIORITY, true));
        $tiers    = get_post_meta($post_id, self::META_TIERS, true);

        $products = is_array($products) ? array_filter(array_map('absint', $products)) : [];
        $cats     = is_array($cats) ? array_filter(array_map('absint', $cats)) : [];
        $tiers    = is_array($tiers) ? wcbtp_sanitize_tiers($tiers) : [];
        $name     = get_the_title($post_id);

        return [
            'id'        => $post_id,
            'name'      => $name,
            'enabled'   => $enabled,
            'scope'     => $scope, // product|category
            'products'  => $products,
            'cats'      => $cats,
            'priority'  => $priority > 0 ? $priority : 10,
            'tiers'     => $tiers,
        ];
    }

    public static function get_matching_rules_for_product($product_id) {
        $product_id = absint($product_id);
        $product    = wc_get_product($product_id);
        if (!$product) return ['product' => [], 'category' => []];

        $parent_id = $product->get_parent_id() ? $product->get_parent_id() : $product_id;
        $cats = wcbtp_get_product_categories_ids($product_id);
        $rules = self::get_rules();
        $match_product = [];
        $match_category = [];

        foreach ($rules as $rule) {
            if (!$rule['enabled'] || empty($rule['tiers'])) continue;

            if ($rule['scope'] === 'product') {
                // match either exact product or its parent
                if (in_array($product_id, $rule['products'], true) || in_array($parent_id, $rule['products'], true)) {
                    $match_product[] = $rule;
                }
            } else {
                if (!empty(array_intersect($cats, $rule['cats']))) {
                    $match_category[] = $rule;
                }
            }
        }

        // sort within each set by priority asc
        $sortByPriority = function ($a, $b) {
            return intval($a['priority']) <=> intval($b['priority']);
        };
        usort($match_product, $sortByPriority);
        usort($match_category, $sortByPriority);

        return ['product' => $match_product, 'category' => $match_category];
    }

    public static function choose_rule_for_product($product_id) {
        $matches = self::get_matching_rules_for_product($product_id);
        if (!empty($matches['product'])) {
            return $matches['product'][0];
        }
        if (!empty($matches['category'])) {
            return $matches['category'][0];
        }
        return null;
    }

    public static function get_applicable_tier_for_qty($rule, $qty) {
        if (!$rule || empty($rule['tiers'])) return null;
        $qty = max(0, intval($qty));
        $chosen = null;
        foreach ($rule['tiers'] as $tier) {
            if ($tier['min_qty'] <= $qty) {
                $chosen = $tier; // keep last that satisfies
            } else {
                break;
            }
        }
        return $chosen;
    }
}