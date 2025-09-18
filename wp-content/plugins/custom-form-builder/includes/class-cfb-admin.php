<?php
class CFB_Admin
{
    private $dashboard;
    private $builder;

    public function __construct()
    {
        // Load classes
        require_once CFB_PATH . 'includes/class-cfb-dashboard.php';
        require_once CFB_PATH . 'includes/class-cfb-builder.php';
        require_once CFB_PATH . 'includes/class-cfb-ajax.php';

        $this->dashboard = new CFB_Dashboard();
        $this->builder   = new CFB_Builder();
        // Initialize AJAX endpoints (e.g., cfb_save_form)
        new CFB_Ajax();

        // Admin menu
        add_action('admin_menu', [$this, 'register_menu']);

        // Enqueue CSS + JS
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function register_menu()
    {
        // Parent Menu
        add_menu_page(
            'Custom Form Builder',       // Page title
            'Form Builder',              // Menu title
            'manage_options',            // Capability
            'cfb-dashboard',             // Slug
            [$this->dashboard, 'render_page'], // Callback
            'dashicons-feedback',        // Icon
            30
        );

        // Submenu: Dashboard
        add_submenu_page(
            'cfb-dashboard',
            'Form Builder Dashboard',
            'Dashboard',
            'manage_options',
            'cfb-dashboard',
            [$this->dashboard, 'render_page']
        );

        // Submenu: Contact Us Builder
        add_submenu_page(
            'cfb-dashboard',
            'Contact Us Form',
            'Contact Us',
            'manage_options',
            'cfb-contact',
            [$this->builder, 'render_page']
        );
    }

    public function enqueue_assets($hook)
    {
        // Sirf hamari "Contact Us" builder page par scripts load karna
        if ($hook !== 'form-builder_page_cfb-contact') {
            return;
        }

        // CSS
        wp_enqueue_style(
            'cfb-admin',
            CFB_URL . 'assets/css/admin.css',
            [],
            '1.0.0'
        );

        // JS
        wp_enqueue_script(
            'cfb-builder',
            CFB_URL . 'assets/js/builder.js',
            ['jquery', 'jquery-ui-draggable', 'jquery-ui-sortable', 'jquery-ui-droppable'],
            '1.0.0',
            true
        );

        // PHP → JS (nonce + ajax url)
        wp_localize_script('cfb-builder', 'cfb_vars', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('cfb_nonce')
        ]);
    }
}
