<?php
/*
Plugin Name: Save Product
Description: Adds a "Save Product" button on WooCommerce product pages that lets users save/unsave items (wishlist style). Logged-in users are stored in usermeta, guests in WooCommerce session. Provides [saved_products] shortcode.
Version: 2.0
Author: Your Name
*/

if (!defined('ABSPATH')) exit;

class Save_Product
{
    /**
     * Migrate guest saved products to user meta after login/registration
     */
    public function migrate_guest_saved_products($user_id)
    {
        if (!WC()->session || !WC()->session->has_session()) return;
        $guest_saved = WC()->session->get('saved_products', []);
        if (!empty($guest_saved)) {
            $user_saved = (array) get_user_meta($user_id, '_saved_products', true);
            $merged = array_unique(array_map('intval', array_merge($user_saved, $guest_saved)));
            update_user_meta($user_id, '_saved_products', $merged);
            WC()->session->__unset('saved_products');
        }
    }


    public function __construct()
    {
        // Migrate guest saved products after login/registration
        add_action('wp_login', [$this, 'migrate_guest_saved_products']);
        add_action('user_register', [$this, 'migrate_guest_saved_products']);
        // Scripts & styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Button under add-to-cart
        add_action('woocommerce_after_add_to_cart_button', [$this, 'add_save_button']);

        // Handle AJAX for save/un-save
        add_action('wp_ajax_save_product_action', [$this, 'handle_ajax']);
        add_action('wp_ajax_nopriv_save_product_action', [$this, 'handle_ajax']);

        // Shortcode
        add_shortcode('saved_products', [$this, 'shortcode_saved_products']);
    }

    /**
     * Load scripts & styles
     */
    public function enqueue_scripts()
    {
        if (is_product() || has_shortcode(get_post()->post_content ?? '', 'saved_products')) {
            wp_enqueue_script(
                'save-product-js',
                plugin_dir_url(__FILE__) . 'assets/js/save-product.js',
                ['jquery'],
                '2.0',
                true
            );

            wp_localize_script('save-product-js', 'saveProductObj', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('save_product_nonce'),
            ]);

            wp_enqueue_style(
                'save-product-css',
                plugin_dir_url(__FILE__) . 'assets/css/save-product.css',
                [],
                '2.0'
            );
        }
    }

    /**
     * Add Save/Unsave button
     */
    public function add_save_button()
    {
        global $product;
        if (!$product) return;

        $product_id  = $product->get_id();
        $button_text = $this->is_saved($product_id) ? '❌ Remove from Saved' : '❤️ Save Product';

        echo '<button type="button" 
        class="save-product-btn" 
        data-productid="' . esc_attr($product_id) . '">'
            . esc_html($button_text) .
            '</button>
     <div class="save-product-message" style="margin-top:10px;color:green;font-weight:500;"></div>';
    }

    /**
     * Check if product is saved
     */
    private function is_saved($product_id)
    {
        if (is_user_logged_in()) {
            $saved = (array) get_user_meta(get_current_user_id(), '_saved_products', true);
        } else {
            $saved = WC()->session ? WC()->session->get('saved_products', []) : [];
        }
        return in_array($product_id, $saved);
    }

    /**
     * Handle AJAX Save/Unsave
     */
    public function handle_ajax()
    {
        check_ajax_referer('save_product_nonce', 'nonce');

        $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        if (!$product_id || !wc_get_product($product_id)) {
            wp_send_json_error(['message' => 'Invalid product.']);
        }

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $saved   = (array) get_user_meta($user_id, '_saved_products', true);

            if (in_array($product_id, $saved)) {
                // Remove
                $saved = array_diff($saved, [$product_id]);
                empty($saved)
                    ? delete_user_meta($user_id, '_saved_products')
                    : update_user_meta($user_id, '_saved_products', array_map('intval', $saved));
                wp_send_json_success(['message' => 'Removed from Saved', 'removed' => true]);
            } else {
                // Add
                $saved[] = $product_id;
                update_user_meta($user_id, '_saved_products', array_map('intval', $saved));
                wp_send_json_success(['message' => 'Product Saved', 'removed' => false]);
            }
        } else {
            // Guest session
            if (null === WC()->session || !WC()->session->has_session()) {
                WC()->session->set_customer_session_cookie(true);
            }
            $saved = WC()->session->get('saved_products', []);

            if (in_array($product_id, $saved)) {
                $saved = array_diff($saved, [$product_id]);
                WC()->session->set('saved_products', array_map('intval', $saved));
                wp_send_json_success(['message' => 'Removed from Saved', 'removed' => true]);
            } else {
                $saved[] = $product_id;
                WC()->session->set('saved_products', array_map('intval', $saved));
                wp_send_json_success(['message' => 'Product Saved', 'removed' => false]);
            }
        }
    }

    /**
     * Shortcode [saved_products]
     */
    public function shortcode_saved_products()
    {
        if (is_user_logged_in()) {
            $saved = (array) get_user_meta(get_current_user_id(), '_saved_products', true);
        } else {
            $saved = WC()->session ? WC()->session->get('saved_products', []) : [];
        }

        if (empty($saved)) {
            return '<p>No saved products yet.</p>';
        }

        $html = '<div class="woocommerce columns-4">';
        foreach ($saved as $pid) {
            $post_object = get_post($pid);
            if ($post_object) {
                setup_postdata($GLOBALS['post'] = &$post_object);
                ob_start();
                wc_get_template_part('content', 'product');
                $html .= ob_get_clean();
            }
        }
        wp_reset_postdata();
        $html .= '</div>';

        return $html;
    }
}

new Save_Product();
