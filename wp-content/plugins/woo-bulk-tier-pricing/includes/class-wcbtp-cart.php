<?php
if (!defined('ABSPATH')) exit;

class WCBTP_Cart {
    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function init() {
        add_action('woocommerce_before_calculate_totals', [$this, 'apply_discounts'], 20, 1);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'add_order_item_meta'], 10, 4);
    }

    public function apply_discounts($cart) {
        if (is_admin() && !defined('DOING_AJAX')) return;
        if (did_action('woocommerce_before_calculate_totals') > 1) {
            // avoid recursion storms
        }

        static $processing = false;
        if ($processing) return;
        $processing = true;

        // Aggregate quantities by parent product ID (for variations)
        $agg_qty = [];
        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (!isset($cart_item['data']) || !is_a($cart_item['data'], 'WC_Product')) continue;
            $product = $cart_item['data'];
            $pid = $product->get_id();
            $parent_id = $product->get_parent_id() ? $product->get_parent_id() : $pid;
            $qty = intval($cart_item['quantity']);
            if (!isset($agg_qty[$parent_id])) $agg_qty[$parent_id] = 0;
            $agg_qty[$parent_id] += $qty;
        }

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            if (!$product || !is_a($product, 'WC_Product')) continue;

            $pid = $product->get_id();
            $parent_id = $product->get_parent_id() ? $product->get_parent_id() : $pid;

            $rule = WCBTP_Rules::choose_rule_for_product($pid);
            if (!$rule) {
                // Reset to base price if previously discounted
                $this->maybe_reset_price($cart_item);
                continue;
            }

            $qty_total = isset($agg_qty[$parent_id]) ? intval($agg_qty[$parent_id]) : intval($cart_item['quantity']);
            $tier = WCBTP_Rules::get_applicable_tier_for_qty($rule, $qty_total);
            if (!$tier) {
                $this->maybe_reset_price($cart_item);
                continue;
            }

            // Get base (current) product price afresh, not the possibly modified cart product price
            $base_price = wcbtp_get_clean_product_price($pid);
            $new_price  = wcbtp_calculate_discounted_price($base_price, $tier);

            // Only set if lower than base_price (avoid minor float issues)
            if ($new_price < $base_price - 0.00001) {
                $cart_item['data']->set_price($new_price);
                $cart_item['wcbtp'] = [
                    'applied'    => true,
                    'rule_id'    => $rule['id'],
                    'rule_name'  => $rule['name'],
                    'tier'       => $tier,
                    'base_price' => $base_price,
                    'new_price'  => $new_price,
                    'qty_total'  => $qty_total,
                ];
            } else {
                $this->maybe_reset_price($cart_item);
            }
        }

        $processing = false;
    }

    private function maybe_reset_price(&$cart_item) {
        if (!isset($cart_item['data']) || !is_a($cart_item['data'], 'WC_Product')) return;
        if (!empty($cart_item['wcbtp']['applied'])) {
            // Reset to clean product price
            $pid = $cart_item['data']->get_id();
            $base_price = wcbtp_get_clean_product_price($pid);
            $cart_item['data']->set_price($base_price);
            unset($cart_item['wcbtp']);
        }
    }

    public function add_order_item_meta($item, $cart_item_key, $values, $order) {
        if (isset($values['wcbtp']) && !empty($values['wcbtp']['applied'])) {
            $tier = $values['wcbtp']['tier'];
            $label = sprintf(
                /* translators: 1: min qty, 2: discount description */
                __('Bulk discount applied (Min %1$d, %2$s)', 'wcbtp'),
                intval($tier['min_qty']),
                $tier['type'] === 'percentage' ? intval($tier['amount']) . '%' : wc_price(floatval($tier['amount']))
            );
            $item->add_meta_data(__('Bulk Discount', 'wcbtp'), $label, true);
        }
    }
}