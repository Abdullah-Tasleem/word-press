<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CFB_Render {
    public function __construct() {
        // Support both tags to avoid confusion
        add_shortcode( 'custom_form', [ $this, 'render_form' ] );
        add_shortcode( 'cfb', [ $this, 'render_form' ] );
    }

    /**
     * Renders a saved form by ID.
     * Usage: [custom_form id="123"] or [cfb id="123"]
     */
    public function render_form( $atts ) {
        global $wpdb;

        $atts = shortcode_atts( [ 'id' => 0 ], $atts, 'custom_form' );
        $id   = absint( $atts['id'] );
        if ( ! $id ) {
            return '';
        }

        $table = $wpdb->prefix . 'cfb_forms';
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT fields, title FROM {$table} WHERE id = %d", $id ) );
        if ( ! $row ) {
            return '';
        }

        $html = isset( $row->fields ) ? (string) $row->fields : '';
        if ( '' === $html ) {
            return '';
        }

        // Unslash in case data is stored with escapes
        $html = wp_unslash( $html );

        // Ensure form posts to admin-post.php with correct action
        $html = preg_replace('/<form(\b[^>]*)>/i', '<form$1 action="' . esc_url( admin_url('admin-post.php') ) . '" method="post">', $html, 1);

        // Inject action field for admin_post handler
        $html = preg_replace('/(<form\b[^>]*>)/i', '$1<input type="hidden" name="action" value="cfb_submit_entry" />', $html, 1);

        // Allowlist form-related tags & attributes so output isn't stripped
        $allowed = [
            'form' => [
                'id'        => true,
                'class'     => true,
                'action'    => true,
                'method'    => true,
                'name'      => true,
                'novalidate'=> true,
            ],
            'div' => [ 'id' => true, 'class' => true ],
            'span' => [ 'id' => true, 'class' => true ],
            'label' => [ 'for' => true, 'class' => true ],
            'input' => [
                'type'        => true,
                'name'        => true,
                'value'       => true,
                'placeholder' => true,
                'required'    => true,
                'readonly'    => true,
                'checked'     => true,
                'class'       => true,
                'id'          => true,
            ],
            'textarea' => [
                'name'        => true,
                'placeholder' => true,
                'required'    => true,
                'class'       => true,
                'id'          => true,
                'rows'        => true,
                'cols'        => true,
            ],
            'select' => [
                'name'     => true,
                'required' => true,
                'class'    => true,
                'id'       => true,
                'multiple' => true,
            ],
            'option' => [ 'value' => true, 'selected' => true ],
            'button' => [ 'type' => true, 'class' => true, 'id' => true ],
        ];

        $sanitized = wp_kses( $html, $allowed );

        // Minimal front-end styles so the form looks good on pages
        $style = '<style>
            .cfb-dropzone { display:flex; flex-direction:column; gap:12px; }
            .cfb-field { position:relative; padding:16px; background:#fff; border:1px solid #e6e9ef; border-radius:12px; }
            .cfb-field label { display:block; font-weight:600; margin-bottom:6px; color:#2b2f36; }
            .cfb-field input[type="text"],
            .cfb-field input[type="email"],
            .cfb-field input[type="url"],
            .cfb-field input[type="number"],
            .cfb-field input[type="tel"],
            .cfb-field select,
            .cfb-field textarea { width:100%; background:#fff; border:1px solid #e6e9ef; border-radius:10px; padding:10px 12px; outline:none; }
            .cfb-field textarea { min-height:110px; }
            .cfb-field input:focus,
            .cfb-field select:focus,
            .cfb-field textarea:focus { border-color:#2f80ed; box-shadow:0 0 0 3px rgba(47,128,237,.15); }
            .cfb-submit-button { background:#2f80ed; color:#fff; border:none; border-radius:12px; padding:12px 24px; font-weight:700; font-size:16px; cursor:pointer; transition:background-color .3s ease; box-shadow:0 4px 8px rgba(47,128,237,.3); }
            .cfb-submit-button:hover,
            .cfb-submit-button:focus { background:#1161d8; outline:none; }
        </style>';

        // Ensure form method="post" exists
        $sanitized = preg_replace('/<form\b(?![^>]*\bmethod=)/i', '<form method="post"', $sanitized, 1);

        // Inject hidden fields for entry capture immediately after opening <form>
        $hidden  = '<input type="hidden" name="cfb_form_id" value="' . esc_attr( $id ) . '" />';
        $hidden .= '<input type="hidden" name="cfb_entry_nonce" value="' . esc_attr( wp_create_nonce( 'cfb_entry' ) ) . '" />';
        $sanitized = preg_replace('/(<form\b[^>]*>)/i', '$1' . $hidden, $sanitized, 1);

    return $style . $sanitized;
    }
}
