<?php
if (!defined('ABSPATH')) exit;

class WCBTP_Frontend {
    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function init() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('woocommerce_single_product_summary', [$this, 'render_tier_table'], 11);
    }

    public function enqueue_assets() {
        if (is_product()) {
            wp_enqueue_style('wcbtp-frontend', WCBTP_URL . 'assets/css/frontend.css', [], WCBTP_VERSION);
            wp_enqueue_script('wcbtp-frontend', WCBTP_URL . 'assets/js/frontend.js', ['jquery'], WCBTP_VERSION, true);
        }
    }

    public function render_tier_table() {
        global $product;
        if (!$product || !is_a($product, 'WC_Product')) return;

        $rule = WCBTP_Rules::choose_rule_for_product($product->get_id());
        if (!$rule || empty($rule['tiers'])) {
            return;
        }

        $base_price = floatval($product->get_price());
        $tiers = $rule['tiers'];
        // Build display ranges
        $display_rows = [];
        foreach ($tiers as $idx => $tier) {
            $next_min = isset($tiers[$idx+1]) ? intval($tiers[$idx+1]['min_qty']) : null;
            $range_label = wcbtp_format_range(intval($tier['min_qty']), $next_min);
            $unit_price = wcbtp_calculate_discounted_price($base_price, $tier);
            $display_rows[] = [
                'range'      => $range_label,
                'min_qty'    => intval($tier['min_qty']),
                'unit_price' => $unit_price,
                'type'       => $tier['type'],
                'amount'     => floatval($tier['amount']),
            ];
        }

        wc_get_template(
            'tier-table.php',
            [
                'rows'       => $display_rows,
                'base_price' => $base_price,
                'product_id' => $product->get_id(),
                'rule'       => $rule,
            ],
            '', // default path
            WCBTP_PATH . 'templates/'
        );
    }
}