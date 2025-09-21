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

    $html = '<div class="gm-saved-products-row" style="display:flex;flex-wrap:wrap;gap:3.33%;justify-content:flex-start;width:100%;">';
        foreach ($saved as $pid) {
            $product = wc_get_product($pid);
            if ($product) {
                $post_object = get_post($pid);
                setup_postdata($GLOBALS['post'] = &$post_object);
                $review_html = function_exists('wc_get_rating_html') ? wc_get_rating_html($product->get_average_rating(), $product->get_rating_count()) : '';
                $price_html = $product->get_price_html();
                $add_to_cart = '<form class="cart" method="post" enctype="multipart/form-data">'
                    . '<button type="submit" class="button add_to_cart_button" name="add-to-cart" value="' . esc_attr($pid) . '">' . esc_html__('Add to cart', 'woocommerce') . '</button>'
                    . '</form>';
                $remove_btn = '<button type="button" class="save-product-btn" data-productid="' . esc_attr($pid) . '" style="margin-top:8px;">❌ Remove from Saved</button>';
                $msg_box = '<div class="save-product-message" style="margin-top:8px;color:green;font-weight:500;"></div>';
                $img_html = $product->get_image();
                $title_html = '<h2 class="woocommerce-loop-product__title">' . esc_html($product->get_name()) . '</h2>';
                $html .= '<div class="gm-saved-product" style="flex:0 0 30%;max-width:30%;border:1px solid #eee;padding:16px;margin-bottom:18px;border-radius:8px;box-sizing:border-box;display:flex;flex-direction:column;align-items:center;">'
                    . $img_html
                    . $title_html
                    . $review_html
                    . '<div class="gm-center-actions" style="display:flex;flex-direction:column;align-items:center;width:100%;margin-top:12px;">'
                        . $price_html
                        . $add_to_cart
                        . $remove_btn
                        . $msg_box
                    . '</div>'
                    . '</div>';
            }
        }
        wp_reset_postdata();
        $html .= '</div>';

        return $html;
    }
}

new Save_Product();
