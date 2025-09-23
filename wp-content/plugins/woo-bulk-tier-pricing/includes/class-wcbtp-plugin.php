<?php
if (!defined('ABSPATH')) exit;

final class WCBTP_Plugin {
    private static $instance = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init() {
        load_plugin_textdomain('wcbtp', false, dirname(WCBTP_BASENAME) . '/languages');

        add_action('init', [ 'WCBTP_Rules', 'register_cpt' ]);

        if (is_admin()) {
            WCBTP_Admin::instance()->init();
        }

        WCBTP_Frontend::instance()->init();
        WCBTP_Cart::instance()->init();
    }
}
