<?php

/**
 * Plugin Name: Scheduler
 * Description: Schedule WooCommerce product status changes
 * Version: 1.6.2
 * Author: Abdullah
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load assets
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'toplevel_page_scheduler') {
        wp_enqueue_style('scheduler-admin', plugin_dir_url(__FILE__) . 'assets/admin.css');
        wp_enqueue_script('scheduler-admin', plugin_dir_url(__FILE__) . 'assets/admin.js', ['jquery'], false, true);
    }
});

// Add menu page
add_action('admin_menu', function () {
    add_menu_page(
        'Scheduler',
        'Scheduler',
        'manage_options',
        'scheduler',
        'sqv_render_page'
    );
});

function sqv_render_page()
{
    global $wpdb;

    echo '<div class="wrap"><h1>Scheduler</h1>';

    // =========================
    // Schedule Form
    // =========================
    echo '<form method="post" action="">';

    echo '<table class="widefat fixed striped" id="sqv-forms-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Select Product</th>
                    <th style="width: 30%;">Status</th>
                    <th style="width: 30%;">Action</th>
                </tr>
            </thead>
            <tbody id="sqv-forms-wrapper">';

    // product dropdown options (for cloning)
    $products = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type='product' ORDER BY post_title ASC");
    $product_options = '<option value="">-- Select Product --</option>';
    foreach ($products as $product) {
        $product_options .= "<option value='{$product->ID}'>#{$product->ID} - {$product->post_title}</option>";
    }

    // initial row
    echo '<tr class="sqv-form-row">
            <td><select name="sqv_product_id[]" required>' . $product_options . '</select></td>
            <td>
                <select name="sqv_new_status[]" required>
                    <option value="publish">Publish</option>
                    <option value="draft">Draft</option>
                </select>
            </td>
            <td><button type="button" class="button sqv-remove-row">❌</button></td>
          </tr>';

    echo '</tbody></table>';

    echo '<button type="button" id="add-form-row" class="button">+ Add Another</button><br><br>';

    echo '<input type="submit" class="button button-primary" name="sqv_schedule_submit" value="Schedule Status Update " />';
    echo '</form><hr>';

    // =========================
    // Handle form submission
    // =========================
    if (isset($_POST['sqv_schedule_submit'])) {
        $product_ids = $_POST['sqv_product_id'];
        $statuses    = $_POST['sqv_new_status'];

        if (!empty($product_ids)) {
            foreach ($product_ids as $index => $product_id) {
                $product_id = intval($product_id);
                $new_status = sanitize_text_field($statuses[$index]);

                if ($product_id && $new_status) {
                    // ✅ use WordPress timezone
                    $timestamp = current_time('timestamp') + 120;

                    wp_schedule_single_event(
                        $timestamp,
                        'sqv_change_product_status',
                        [$product_id, $new_status]
                    );

                    echo '<div class="updated notice"><p>✅ Scheduled product ID ' . $product_id . ' to become <strong>' . esc_html($new_status) . '</strong> </p></div>';
                }
            }
        }
    }

    // =========================
    // Scheduled Events Viewer
    // =========================
    echo '<h2>Scheduled Product Status Updates</h2>';

    $crons     = _get_cron_array();
    $scheduled = [];

    if ($crons) {
        foreach ($crons as $timestamp => $cronhooks) {
            if (isset($cronhooks['sqv_change_product_status'])) {
                foreach ($cronhooks['sqv_change_product_status'] as $event) {
                    $args        = $event['args'];
                    $scheduled[] = [
                        'time'       => $timestamp,
                        'product_id' => $args[0],
                        'status'     => $args[1],
                    ];
                }
            }
        }
    }

    if ($scheduled) {
        echo '<table class="widefat"><thead><tr><th>Product ID</th><th>Scheduled Status</th><th>Run Time</th><th>Status</th></tr></thead><tbody>';
        foreach ($scheduled as $s) {
            // Convert UTC timestamp -> Site timezone
            $utc_time  = gmdate('Y-m-d H:i:s', $s['time']);
            $run_time  = get_date_from_gmt($utc_time, 'Y-m-d H:i:s');

            echo '<tr>
                <td>' . esc_html($s['product_id']) . '</td>
                <td>' . esc_html($s['status']) . '</td>
                <td>' . esc_html($run_time) . '</td>
                <td><span style="color:orange">⏳ Pending</span></td>
              </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No scheduled product status updates.</p>';
    }


    // =========================
    // Executed Events Viewer
    // =========================
    $executed = get_option('sqv_executed_events', []);
    if (!empty($executed)) {
        echo '<h3>Executed Updates</h3>';
        echo '<table class="widefat"><thead><tr><th>Product ID</th><th>Updated To</th><th>Executed At</th></tr></thead><tbody>';
        foreach (array_reverse($executed) as $row) {
            echo '<tr>
                    <td>' . esc_html($row['product_id']) . '</td>
                    <td>' . esc_html($row['status']) . '</td>
                    <td>' . esc_html($row['executed_at']) . '</td>
                  </tr>';
        }
        echo '</tbody></table>';
    }

    echo '</div>'; // wrap
}

// =========================
// Action Hook to change product status
// =========================
add_action('sqv_change_product_status', function ($product_id, $new_status) {
    global $wpdb;
    $wpdb->update(
        $wpdb->posts,
        ['post_status' => $new_status],
        ['ID' => $product_id],
        ['%s'],
        ['%d']
    );

    // Save executed log
    $executed   = get_option('sqv_executed_events', []);
    $executed[] = [
        'product_id' => $product_id,
        'status'     => $new_status,
        'executed_at' => current_time('mysql'),
    ];
    if (count($executed) > 10) {
        $executed = array_slice($executed, -10); // keep last 10
    }
    update_option('sqv_executed_events', $executed);
}, 10, 2);
