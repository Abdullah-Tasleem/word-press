<?php
/**
 * Plugin Name: Custom Form Builder
 */

if ( ! defined('ABSPATH') ) exit;

define('CFB_PATH', plugin_dir_path(__FILE__));
define('CFB_URL', plugin_dir_url(__FILE__));

require_once CFB_PATH . 'includes/class-cfb-admin.php';
require_once CFB_PATH . 'includes/class-cfb-render.php';
require_once CFB_PATH . 'includes/class-cfb-shortcode.php';
new CFB_Shortcode();

// CSV export handler (admin-post)
add_action('admin_post_cfb_export_entries', 'cfb_handle_export_entries');
function cfb_handle_export_entries() {
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions.');
    }
    $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
    if (!$form_id) {
        wp_die('Invalid form ID.');
    }
    $nonce = isset($_GET['nonce']) ? sanitize_text_field($_GET['nonce']) : '';
    if (!wp_verify_nonce($nonce, 'cfb_export_entries_' . $form_id)) {
        wp_die('Invalid export request.');
    }

    global $wpdb;
    $entries_table = $wpdb->prefix . 'cfb_entries';
    $entries = $wpdb->get_results(
        $wpdb->prepare("SELECT entry, created_at FROM {$entries_table} WHERE form_id = %d ORDER BY id DESC", $form_id)
    );

    // Start sending headers before any output
    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=form-' . $form_id . '-entries.csv');

    $output = fopen('php://output', 'w');

    if ($entries) {
        $first = json_decode($entries[0]->entry, true);
        $headers = array_keys((array) $first);
        $headers[] = 'Submitted At';
        fputcsv($output, $headers);

        foreach ($entries as $entry) {
            $data = json_decode($entry->entry, true);
            $row = [];
            foreach ($first as $key => $val) {
                $row[] = isset($data[$key]) ? (is_array($data[$key]) ? (isset($data[$key][0]) ? $data[$key][0] : implode(', ', $data[$key])) : $data[$key]) : '';
            }
            $row[] = $entry->created_at;
            fputcsv($output, $row);
        }
    }

    fclose($output);
    exit;
}


// ✅ Register activation hook
register_activation_hook(__FILE__, 'cfb_activate_plugin');

function cfb_activate_plugin() {
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    $forms_table   = $wpdb->prefix . 'cfb_forms';
    $entries_table = $wpdb->prefix . 'cfb_entries';

    // Forms table
    $sql_forms = "CREATE TABLE {$forms_table} (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        fields LONGTEXT NOT NULL,
        shortcode VARCHAR(100) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) {$charset_collate};";

    // Entries table
    $sql_entries = "CREATE TABLE {$entries_table} (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        form_id BIGINT(20) UNSIGNED NOT NULL,
        entry LONGTEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY form_id (form_id)
    ) {$charset_collate};";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_forms);
    dbDelta($sql_entries);
}

// Load admin class
add_action('plugins_loaded', function() {
    new CFB_Admin();
    // Register shortcodes for front-end usage
    new CFB_Render();
});


// Handle form submissions from frontend
add_action('admin_post_nopriv_cfb_submit_entry', 'cfb_handle_entry_submission');
add_action('admin_post_cfb_submit_entry', 'cfb_handle_entry_submission');

function cfb_handle_entry_submission() {
    if (!isset($_POST['cfb_entry_nonce']) || !wp_verify_nonce($_POST['cfb_entry_nonce'], 'cfb_entry')) {
        wp_die('Invalid nonce.');
    }
    $form_id = isset($_POST['cfb_form_id']) ? absint($_POST['cfb_form_id']) : 0;
    if (!$form_id) {
        wp_die('Form ID missing.');
    }
    // Remove internal fields
    $entry = $_POST;
    unset($entry['cfb_form_id'], $entry['cfb_entry_nonce'], $entry['action']);
    $entry_json = wp_json_encode($entry);
    global $wpdb;
    $table = $wpdb->prefix . 'cfb_entries';
    $wpdb->insert($table, [
        'form_id' => $form_id,
        'entry'   => $entry_json,
        'created_at' => current_time('mysql'),
    ], [
        '%d', '%s', '%s'
    ]);
    // Redirect after submission
    wp_redirect(add_query_arg('cfb_entry', 'success', wp_get_referer() ?: home_url()));
    exit;
}
