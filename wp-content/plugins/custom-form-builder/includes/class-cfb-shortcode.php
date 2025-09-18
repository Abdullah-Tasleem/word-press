<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CFB_Shortcode {
    public function __construct() {
        // Fallback shortcode (primary renderer is CFB_Render)
        add_shortcode( 'cfb', [ $this, 'render_form' ] );

        // Capture front-end submissions
        add_action( 'init', [ $this, 'maybe_capture_entry' ] );

        // Admin page to view entries (DataTables)
        add_action( 'admin_menu', [ $this, 'register_entries_page' ] );
        // Hide 'View Data' from the submenu while keeping page accessible via direct link
        add_action( 'admin_menu', [ $this, 'hide_entries_submenu' ], 999 );
    }

    /**
     * Render saved form fields (fallback)
     */
    public function render_form( $atts ) {
        global $wpdb;

        $atts = shortcode_atts( [ 'id' => 0 ], $atts, 'cfb' );
        $id   = absint( $atts['id'] );
        if ( ! $id ) {
            return '';
        }

        $table = $wpdb->prefix . 'cfb_forms';
        $form  = $wpdb->get_row( $wpdb->prepare( "SELECT fields FROM {$table} WHERE id = %d", $id ) );

        if ( ! $form ) {
            return '';
        }

        return (string) $form->fields;
    }

    /**
     * Store entries in wp_cfb_entries when a form is submitted.
     */
    public function maybe_capture_entry() {
        // Avoid double insertion when admin-post handler processes the request
        if ( isset( $_POST['action'] ) && 'cfb_submit_entry' === $_POST['action'] ) {
            return;
        }
        if ( empty( $_POST['cfb_form_id'] ) || empty( $_POST['cfb_entry_nonce'] ) ) {
            return;
        }
        $form_id = absint( $_POST['cfb_form_id'] );
        if ( ! $form_id ) {
            return;
        }
        if ( ! wp_verify_nonce( $_POST['cfb_entry_nonce'], 'cfb_entry' ) ) {
            return;
        }

        // Collect only posted fields that start with 'field_'
        $payload = [];
        foreach ( $_POST as $key => $value ) {
            if ( 0 === strpos( $key, 'field_' ) ) {
                if ( is_array( $value ) ) {
                    $payload[ $key ] = array_map( 'sanitize_text_field', wp_unslash( $value ) );
                } else {
                    $payload[ $key ] = sanitize_text_field( wp_unslash( $value ) );
                }
            }
        }

        if ( empty( $payload ) ) {
            return;
        }

        global $wpdb;
        $entries_table = $wpdb->prefix . 'cfb_entries';
        $wpdb->insert( $entries_table, [
            'form_id'    => $form_id,
            'entry'      => wp_json_encode( $payload ),
            'created_at' => current_time( 'mysql' ),
        ], [ '%d', '%s', '%s' ] );

        // Optional: redirect to avoid resubmission on refresh
        // if ( ! headers_sent() ) { wp_safe_redirect( add_query_arg( 'cfb_submitted', '1', wp_get_referer() ?: home_url() ) ); exit; }
    }

    /**
     * Add admin submenu page for viewing entries (linked from Dashboard button)
     */
    public function register_entries_page() {
        add_submenu_page(
            'cfb-dashboard',
            __( 'Form Entries', 'custom-form-builder' ),
            __( 'View Data', 'custom-form-builder' ),
            'manage_options',
            'cfb-view-data',
            [ $this, 'render_entries_page_bridge' ]
        );

        add_submenu_page(
            'cfb-dashboard',
            __( 'All Entries', 'custom-form-builder' ),
            __( 'All Entries', 'custom-form-builder' ),
            'manage_options',
            'cfb-entries',
            [ $this, 'render_all_entries_page' ]
        );
    }

    /**
     * Hide the 'View Data' submenu item under Form Builder while keeping the page accessible.
     */
    public function hide_entries_submenu() {
        remove_submenu_page( 'cfb-dashboard', 'cfb-view-data' );
        remove_submenu_page( 'cfb-dashboard', 'cfb-entries' );
    }

    /**
     * Bridge to load entries UI from dedicated file and keep the page off the submenu.
     */
    public function render_entries_page_bridge() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'custom-form-builder' ) );
        }
        if ( isset( $_GET['id'] ) && ! isset( $_GET['form_id'] ) ) {
            $_GET['form_id'] = absint( $_GET['id'] );
        }
        // Delegate rendering to a standalone file.
        include CFB_PATH . 'admin-view-entries.php';
    }

    /**
     * Render all entries across all forms
     */
    public function render_all_entries_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'custom-form-builder' ) );
        }

        global $wpdb;
        $forms_table   = $wpdb->prefix . 'cfb_forms';
        $entries_table = $wpdb->prefix . 'cfb_entries';

        $rows = $wpdb->get_results( "SELECT e.id, e.form_id, e.entry, e.created_at, f.title FROM {$entries_table} e LEFT JOIN {$forms_table} f ON e.form_id = f.id ORDER BY e.id DESC" );

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'All Form Entries', 'custom-form-builder' ) . '</h1>';
        echo '<p><a href="?page=cfb-dashboard" class="button">' . esc_html__( 'Back to Dashboard', 'custom-form-builder' ) . '</a></p>';

        if ( empty( $rows ) ) {
            echo '<p>' . esc_html__( 'No entries found yet.', 'custom-form-builder' ) . '</p>';
            echo '</div>';
            return;
        }

        echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />';

        echo '<table id="cfb-all-entries-table" class="display widefat fixed striped" style="width:100%">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__( 'Entry ID', 'custom-form-builder' ) . '</th>';
        echo '<th>' . esc_html__( 'Form ID', 'custom-form-builder' ) . '</th>';
        echo '<th>' . esc_html__( 'Form Title', 'custom-form-builder' ) . '</th>';
        echo '<th>' . esc_html__( 'Submitted At', 'custom-form-builder' ) . '</th>';
        echo '<th>' . esc_html__( 'Data', 'custom-form-builder' ) . '</th>';
        echo '</tr></thead><tbody>';

        foreach ( $rows as $r ) {
            $data = json_decode( (string) $r->entry, true );
            echo '<tr>';
            echo '<td>' . (int) $r->id . '</td>';
            echo '<td>' . (int) $r->form_id . '</td>';
            echo '<td>' . esc_html( $r->title ? (string) $r->title : 'Untitled' ) . '</td>';
            echo '<td>' . esc_html( $r->created_at ) . '</td>';
            echo '<td>';
            if ( is_array( $data ) ) {
                echo '<ul style="margin:0;padding-left:18px;">';
                foreach ( $data as $k => $v ) {
                    if ( is_array( $v ) ) { $v = implode( ', ', array_map( 'sanitize_text_field', $v ) ); }
                    echo '<li><strong>' . esc_html( $k ) . ':</strong> ' . esc_html( (string) $v ) . '</li>';
                }
                echo '</ul>';
            } else {
                echo '<code>' . esc_html( (string) $r->entry ) . '</code>';
            }
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';

        echo '<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>';
        echo '<script>jQuery(function($){ $("#cfb-all-entries-table").DataTable({ pageLength: 10, order: [[0, "desc"]] }); });</script>';

        echo '</div>';
    }

    /**
     * Render entries with DataTables
     */
    public function render_entries_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'You do not have sufficient permissions to access this page.', 'custom-form-builder' ) );
        }

        global $wpdb;
        $form_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
        $forms_table   = $wpdb->prefix . 'cfb_forms';
        $entries_table = $wpdb->prefix . 'cfb_entries';

        $form = $form_id ? $wpdb->get_row( $wpdb->prepare( "SELECT id, title FROM {$forms_table} WHERE id = %d", $form_id ) ) : null;

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__( 'Form Entries', 'custom-form-builder' ) . '</h1>';

        if ( ! $form ) {
            echo '<p>' . esc_html__( 'Invalid or missing form ID.', 'custom-form-builder' ) . '</p>';
            echo '</div>';
            return;
        }

        echo '<h2>' . sprintf( esc_html__( 'Form: %s (ID: %d)', 'custom-form-builder' ), esc_html( $form->title ), (int) $form->id ) . '</h2>';
        echo '<p><a href="?page=cfb-dashboard" class="button">' . esc_html__( 'Back to Dashboard', 'custom-form-builder' ) . '</a></p>';

        $entries = $wpdb->get_results( $wpdb->prepare( "SELECT id, entry, created_at FROM {$entries_table} WHERE form_id = %d ORDER BY id DESC", $form_id ) );

        if ( empty( $entries ) ) {
            echo '<p>' . esc_html__( 'No entries found for this form yet.', 'custom-form-builder' ) . '</p>';
            echo '</div>';
            return;
        }

        // Load DataTables from CDN
        echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />';

        echo '<table id="cfb-entries-table" class="display widefat fixed striped" style="width:100%">';
        echo '<thead><tr>';
        echo '<th>' . esc_html__( 'Entry ID', 'custom-form-builder' ) . '</th>';
        echo '<th>' . esc_html__( 'Submitted At', 'custom-form-builder' ) . '</th>';
        echo '<th>' . esc_html__( 'Data', 'custom-form-builder' ) . '</th>';
        echo '</tr></thead><tbody>';

        foreach ( $entries as $e ) {
            $data = json_decode( (string) $e->entry, true );
            echo '<tr>';
            echo '<td>' . (int) $e->id . '</td>';
            echo '<td>' . esc_html( $e->created_at ) . '</td>';
            echo '<td>';
            if ( is_array( $data ) ) {
                echo '<ul style="margin:0;padding-left:18px;">';
                foreach ( $data as $k => $v ) {
                    if ( is_array( $v ) ) { $v = implode( ', ', array_map( 'sanitize_text_field', $v ) ); }
                    echo '<li><strong>' . esc_html( $k ) . ':</strong> ' . esc_html( (string) $v ) . '</li>';
                }
                echo '</ul>';
            } else {
                echo '<code>' . esc_html( (string) $e->entry ) . '</code>';
            }
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';

        echo '<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>';
        echo '<script>jQuery(function($){ $("#cfb-entries-table").DataTable({ pageLength: 10, order: [[0, "desc"]] }); });</script>';

        echo '</div>';
    }
}
