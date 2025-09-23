<?php
/**
 * Tier Table Template
 *
 * Vars:
 * - $rows: array of [range, min_qty, unit_price, type, amount]
 * - $base_price: float
 * - $product_id: int
 * - $rule: array
 */
if (!defined('ABSPATH')) exit;

if (empty($rows)) return;

echo '<div class="wcbtp-wrapper">';
echo '<h3>' . esc_html__('Tiered pricing', 'wcbtp') . '</h3>';
echo '<table class="wcbtp-tier-table" data-base-price="' . esc_attr($base_price) . '">';
echo '<thead><tr>';
echo '<th>' . esc_html__('Quantity', 'wcbtp') . '</th>';
echo '<th>' . esc_html__('Price each', 'wcbtp') . '</th>';
echo '</tr></thead>';
echo '<tbody>';

foreach ($rows as $r) {
    $data_attrs = sprintf(
        ' data-min="%d" data-type="%s" data-amount="%s" data-price-format="%s"',
        intval($r['min_qty']),
        esc_attr($r['type']),
        esc_attr($r['amount']),
        esc_attr('%s') // placeholder - JS updates with 2 decimals; if you want formatted wc_price in JS, localize more info
    );
    $price_html = wc_price($r['unit_price']);
    echo '<tr' . $data_attrs . '>';
    echo '<td>' . esc_html($r['range']) . '</td>';
    echo '<td class="wcbtp-unit-price">' . wp_kses_post($price_html) . '</td>';
    echo '</tr>';
}

echo '</tbody></table>';
echo '<p class="wcbtp-note">' . esc_html__('Discount applies automatically at checkout. Product-level rules override category rules.', 'wcbtp') . '</p>';
echo '</div>';