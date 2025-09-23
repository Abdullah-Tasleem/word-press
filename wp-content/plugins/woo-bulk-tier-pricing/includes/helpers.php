<?php
if (!defined('ABSPATH')) exit;

function wcbtp_array_get($array, $key, $default = null) {
    return isset($array[$key]) ? $array[$key] : $default;
}

function wcbtp_sanitize_tiers($tiers) {
    $clean = [];
    if (!is_array($tiers)) return $clean;
    foreach ($tiers as $tier) {
        $min = isset($tier['min_qty']) ? absint($tier['min_qty']) : 0;
        $type = isset($tier['type']) && in_array($tier['type'], ['percentage', 'fixed'], true) ? $tier['type'] : 'percentage';
        $amount = isset($tier['amount']) ? floatval($tier['amount']) : 0;
        if ($min < 1) continue; // ignore invalid
        $clean[] = [
            'min_qty' => $min,
            'type'    => $type,
            'amount'  => $amount,
        ];
    }
    // sort by min_qty asc
    usort($clean, function ($a, $b) {
        return $a['min_qty'] <=> $b['min_qty'];
    });
    return $clean;
}

function wcbtp_calculate_discounted_price($base_price, $tier) {
    $base = floatval($base_price);
    $amount = floatval($tier['amount']);
    $new = $base;
    if ($tier['type'] === 'percentage') {
        $new = $base * (1 - $amount / 100);
    } else {
        $new = $base - $amount;
    }
    return max($new, 0);
}

function wcbtp_format_range($min, $next_min) {
    if (!$next_min) {
        return sprintf(_x('%d+', 'last tier range', 'wcbtp'), $min);
    }
    return sprintf(_x('%d–%d', 'tier range', 'wcbtp'), $min, $next_min - 1);
}

function wcbtp_get_product_categories_ids($product_id) {
    $terms = get_the_terms($product_id, 'product_cat');
    if (empty($terms) || is_wp_error($terms)) return [];
    return array_map('intval', wp_list_pluck($terms, 'term_id'));
}

function wcbtp_get_clean_product_price($product_id) {
    $product = wc_get_product($product_id);
    if (!$product) return 0;
    // Use current "active" price (includes potential sale)
    return floatval($product->get_price());
}