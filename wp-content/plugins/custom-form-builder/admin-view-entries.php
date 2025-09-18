<?php
// admin-view-entries.php
// Usage: admin.php?page=cfb-view-data&form_id=123

if (!defined('ABSPATH')) exit;

global $wpdb;

// Get form_id
$form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;
if (!$form_id) {
    echo '<div class="wrap"><h2>No form selected.</h2></div>';
    return;
}

$entries_table = $wpdb->prefix . 'cfb_entries';

// Fetch entries normally
$entries = $wpdb->get_results(
    $wpdb->prepare("SELECT entry, created_at FROM {$entries_table} WHERE form_id = %d ORDER BY id DESC", $form_id)
);
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Entries for Form #<?php echo esc_html($form_id); ?></h1>
    <hr class="wp-header-end">

    <?php if ($entries && count($entries)): ?>
        <?php $first = json_decode($entries[0]->entry, true); ?>

        <!-- Toolbar -->
        <div class="cfb-toolbar" style="margin:15px 0;">
            <a href="<?php echo esc_url( admin_url( 'admin-post.php?action=cfb_export_entries&form_id=' . $form_id . '&nonce=' . wp_create_nonce( 'cfb_export_entries_' . $form_id ) ) ); ?>" class="button button-primary">Export CSV</a>  
            <p><a href="?page=cfb-dashboard" class="button">Back to Dashboard</a></p>          
        </div>

        <!-- Entries Table -->
        <table class="widefat fixed striped table-view-list" id="cfb-entries-table">
            <thead>
                <tr>
                    <?php foreach ($first as $key => $val): ?>
                        <th><?php echo esc_html(ucwords(str_replace('field_', '', $key))); ?></th>
                    <?php endforeach; ?>
                    <th>Submitted At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries as $entry): $data = json_decode($entry->entry, true); ?>
                    <tr>
                        <?php foreach ($first as $key => $val): ?>
                            <td><?php echo isset($data[$key]) ? esc_html(is_array($data[$key]) ? $data[$key][0] : $data[$key]) : ''; ?></td>
                        <?php endforeach; ?>
                        <td><?php echo esc_html($entry->created_at); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- DataTables -->
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />
        <script>
        jQuery(document).ready(function($){
            $("#cfb-entries-table").DataTable({
                pageLength: 10,
                order: [[<?php echo count($first); ?>, "desc"]]
            });
        });
        </script>

        <style>
        #cfb-entries-table_wrapper {
            margin-top: 20px;
        }
        #cfb-entries-table thead th {
            background: #f1f1f1;
            font-weight: 600;
        }
        #cfb-entries-table td, 
        #cfb-entries-table th {
            padding: 8px 12px;
        }
        </style>

    <?php else: ?>
        <p>No entries found for this form.</p>
    <?php endif; ?>
</div>
