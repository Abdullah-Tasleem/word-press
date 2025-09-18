<?php
class CFB_Ajax
{
    public function __construct()
    {
        add_action('wp_ajax_cfb_save_form', [$this, 'save_form']);
    }

    public function save_form()
    {
        global $wpdb;

        // ✅ Permission & Nonce
        if (! current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission denied'], 403);
        }
        check_ajax_referer('cfb_nonce', 'nonce');

        // ✅ Inputs
        $id     = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $title  = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : 'Untitled Form';
        $fields = isset($_POST['fields']) ? wp_unslash($_POST['fields']) : '[]'; // JSON string

        $table = $wpdb->prefix . 'cfb_forms';
        $now   = current_time('mysql');

        if ($id) {
            // 🔄 Update existing
            $updated = $wpdb->update(
                $table,
                [
                    'title'      => $title,
                    'fields'     => $fields,
                    'updated_at' => $now
                ],
                ['id' => $id],
                ['%s', '%s', '%s'],
                ['%d']
            );

            if (false === $updated) {
                wp_send_json_error(['message' => 'Form not found or update failed'], 404);
            }
        } else {
            // ➕ Create new
            $wpdb->insert(
                $table,
                [
                    'title'      => $title,
                    'fields'     => $fields,
                    'shortcode'  => '', // temporary
                    'created_at' => $now,
                    'updated_at' => $now
                ],
                ['%s', '%s', '%s', '%s', '%s']
            );

            $id = $wpdb->insert_id;

            // ✅ Update shortcode
            $shortcode = sprintf('[cfb id="%d"]', $id);
            $wpdb->update(
                $table,
                ['shortcode' => $shortcode],
                ['id' => $id],
                ['%s'],
                ['%d']
            );
        }

        // ✅ Fetch shortcode to return
        $form = $wpdb->get_row($wpdb->prepare("SELECT shortcode FROM $table WHERE id=%d", $id));

        wp_send_json_success([
            'message'   => 'Form saved!',
            'id'        => $id,
            'shortcode' => $form->shortcode,
            'title'     => $title,
        ]);
    }
}
