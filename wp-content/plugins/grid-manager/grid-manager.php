<?php

/**
 * Plugin Name: Grid Manager
 * Plugin URI:  https://example.com
 * Description: Manage a responsive grid of cards (image, title, link). Provides a custom post type and a shortcode [gm_grid].
 * Version:     1.0.0
 * Author:      Your Name
 * Author URI:  https://example.com
 * License:     GPL-2.0-or-later
 * Text Domain: grid-manager
 */

namespace GM;

if (!defined('ABSPATH')) exit;

define('GM_VERSION', '1.0.0');
define('GM_URL', \plugin_dir_url(__FILE__));
define('GM_PATH', \plugin_dir_path(__FILE__));

// i18n
\add_action('init', function () {
    \load_plugin_textdomain('grid-manager', false, dirname(\plugin_basename(__FILE__)) . '/languages');
});

// Register CPT, taxonomy, and meta
\add_action('init', __NAMESPACE__ . '\\register_content_types');
function register_content_types()
{
    $labels = array(
        'name'               => __('Grid Items', 'grid-manager'),
        'singular_name'      => __('Grid Item', 'grid-manager'),
        'menu_name'          => __('Grid Items', 'grid-manager'),
        'add_new'            => __('Add New', 'grid-manager'),
        'add_new_item'       => __('Add New Grid Item', 'grid-manager'),
        'edit_item'          => __('Edit Grid Item', 'grid-manager'),
        'new_item'           => __('New Grid Item', 'grid-manager'),
        'search_items'       => __('Search Grid Items', 'grid-manager'),
        'not_found'          => __('No grid items found', 'grid-manager'),
        'not_found_in_trash' => __('No grid items found in Trash', 'grid-manager'),
    );

    \register_post_type('gm_item', array(
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'menu_icon'          => 'dashicons-grid-view',
        'supports'           => array('title', 'thumbnail', 'page-attributes'),
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'has_archive'        => false,
        'rewrite'            => false,
        'show_in_rest'       => true,
    ));

    $tax_labels = array(
        'name'          => __('Groups', 'grid-manager'),
        'singular_name' => __('Group', 'grid-manager'),
        'menu_name'     => __('Groups', 'grid-manager'),
        'all_items'     => __('All Groups', 'grid-manager'),
        'edit_item'     => __('Edit Group', 'grid-manager'),
        'add_new_item'  => __('Add New Group', 'grid-manager'),
        'new_item_name' => __('New Group Name', 'grid-manager'),
        'search_items'  => __('Search Groups', 'grid-manager'),
    );

    \register_taxonomy('gm_group', array('gm_item'), array(
        'hierarchical'      => true,
        'labels'            => $tax_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => false,
        'show_in_rest'      => true,
    ));

    \register_post_meta('gm_item', 'gm_url', array(
        'type'              => 'string',
        'single'            => true,
        'sanitize_callback' => 'esc_url_raw',
        'show_in_rest'      => true,
        'auth_callback'     => function () {
            return \current_user_can('edit_posts');
        },
    ));
}

// URL meta box
\add_action('add_meta_boxes', __NAMESPACE__ . '\\register_meta_boxes');
function register_meta_boxes()
{
    \add_meta_box(
        'gm_url_mb',
        __('Link URL', 'grid-manager'),
        __NAMESPACE__ . '\\render_url_metabox',
        'gm_item',
        'side',
        'default'
    );
}

function render_url_metabox($post)
{
    $value = \get_post_meta($post->ID, 'gm_url', true);
    \wp_nonce_field('gm_save_url_' . $post->ID, 'gm_url_nonce');
    echo '<p><label for="gm_url_field" class="screen-reader-text">' . \esc_html__('Link URL', 'grid-manager') . '</label>';
    echo '<input type="url" id="gm_url_field" name="gm_url_field" value="' . \esc_attr($value) . '" placeholder="https://example.com" style="width:100%" /></p>';
    echo '<p style="font-size:12px;color:#555;">' . \esc_html__('Paste a full URL. Leave empty to render a non-clickable card.', 'grid-manager') . '</p>';
}

// Save URL
\add_action('save_post_gm_item', __NAMESPACE__ . '\\save_url_meta', 10, 2);
function save_url_meta($post_id, $post)
{
    if (\defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['gm_url_nonce']) || !\wp_verify_nonce($_POST['gm_url_nonce'], 'gm_save_url_' . $post_id)) return;
    if (!\current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['gm_url_field'])) {
        $url = \esc_url_raw(trim($_POST['gm_url_field']));
        if ($url) \update_post_meta($post_id, 'gm_url', $url);
        else \delete_post_meta($post_id, 'gm_url');
    }
}

// Admin list columns
\add_filter('manage_gm_item_posts_columns', __NAMESPACE__ . '\\admin_columns');
\add_action('manage_gm_item_posts_custom_column', __NAMESPACE__ . '\\admin_columns_content', 10, 2);

function admin_columns($cols)
{
    $new = array();
    $new['cb']        = $cols['cb'];
    $new['thumbnail'] = __('Image', 'grid-manager');
    $new['title']     = __('Title', 'grid-manager');
    $new['gm_size']   = __('Size', 'grid-manager'); // ← ADD THIS
    $new['gm_url']    = __('Link URL', 'grid-manager');
    $new['gm_group']  = __('Groups', 'grid-manager');
    $new['date']      = $cols['date'];
    $new['gm_img_height'] = __('Img Height', 'grid-manager');
    return $new;
}

function admin_columns_content($column, $post_id)
{
    switch ($column) {
        case 'thumbnail':
            echo \get_the_post_thumbnail($post_id, array(60, 60));
            break;
        case 'gm_url':
            $url = \get_post_meta($post_id, 'gm_url', true);
            if ($url) {
                $display = \esc_html(\wp_parse_url($url, PHP_URL_HOST) . (\wp_parse_url($url, PHP_URL_PATH) ?? ''));
                echo '<a href="' . \esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . $display . '</a>';
            } else {
                echo '<span style="color:#999">—</span>';
            }
            break;
        case 'gm_img_height':
            $height = \get_post_meta($post_id, 'gm_img_height', true);
            echo $height ?: '<span style="color:#999">—</span>';
            break;
        case 'gm_group':
            $terms = \get_the_terms($post_id, 'gm_group');
            if (!empty($terms) && !\is_wp_error($terms)) {
                $out = array();
                foreach ($terms as $t) {
                    $out[] = '<a href="' . \esc_url(\admin_url('edit.php?post_type=gm_item&gm_group=' . $t->slug)) . '">' . \esc_html($t->name) . '</a>';
                }
                echo \implode(', ', $out);
            } else {
                echo '<span style="color:#999">—</span>';
            }
            break;
        case 'gm_size': // ← ADD THIS CASE
            $size = \get_post_meta($post_id, 'gm_size', true);
            echo $size ?: '<span style="color:#999">1×1</span>';
            break;
    }
}

// Assets
\add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\register_assets');
function register_assets()
{
    \wp_register_style('gm-frontend', GM_URL . 'assets/css/frontend.css', array(), GM_VERSION);
}

// Shortcode
\add_shortcode('gm_grid', __NAMESPACE__ . '\\shortcode_grid');
function shortcode_grid($atts, $content = null)
{
    $atts = \shortcode_atts(array(
        'group'        => '',
        'columns'      => '3',          // 1-8
        'gap'          => '16',         // px
        'limit'        => '-1',
        'orderby'      => 'menu_order', // menu_order|title|date|modified|rand
        'order'        => 'ASC',        // ASC|DESC
        'include'      => '',
        'exclude'      => '',
        'image_size'   => 'medium_large',
        'class'        => '',
        'link_target'  => '_self',      // _self|_blank
        'rel'          => '',           // e.g., nofollow
    ), $atts, 'gm_grid');

    $columns     = max(1, min(8, intval($atts['columns'])));
    $gap         = max(0, intval($atts['gap']));
    $limit       = intval($atts['limit']);
    $valid_order = array('menu_order', 'title', 'date', 'modified', 'rand');
    $orderby     = in_array($atts['orderby'], $valid_order, true) ? $atts['orderby'] : 'menu_order';
    $order       = strtoupper($atts['order']) === 'DESC' ? 'DESC' : 'ASC';
    $image_size  = sanitize_key($atts['image_size']);
    $target      = $atts['link_target'] === '_blank' ? '_blank' : '_self';
    $rel         = trim($atts['rel']);
    if (!$rel && $target === '_blank') $rel = 'noopener noreferrer';

    $include_ids = array_filter(array_map('intval', preg_split('/[\s,]+/', (string)$atts['include'])));
    $exclude_ids = array_filter(array_map('intval', preg_split('/[\s,]+/', (string)$atts['exclude'])));

    $tax_query = array();
    if (!empty($atts['group'])) {
        $tax_query[] = array(
            'taxonomy' => 'gm_group',
            'field'    => 'slug',
            'terms'    => array_map('sanitize_title', array_map('trim', explode(',', $atts['group']))),
        );
    }

    $args = array(
        'post_type'        => 'gm_item',
        'post_status'      => 'publish',
        'posts_per_page'   => $limit === 0 ? -1 : $limit,
        'orderby'          => $orderby,
        'order'            => $order,
        'tax_query'        => $tax_query,
        'post__in'         => $include_ids ? $include_ids : null,
        'post__not_in'     => $exclude_ids ? $exclude_ids : null,
        'suppress_filters' => false,
        'no_found_rows'    => true,
    );
    if ($include_ids) $args['orderby'] = 'post__in';

    $q = new \WP_Query($args);
    if (!$q->have_posts()) return '';

    \wp_enqueue_style('gm-frontend');

    $extra_class = trim(preg_replace('/[^A-Za-z0-9_\- ]/', '', (string)$atts['class']));
    $classes = 'gm-grid' . ($extra_class ? ' ' . $extra_class : '');

    ob_start();
?>
    <div class="<?php echo \esc_attr($classes); ?>" style="<?php echo \esc_attr("--gm-columns: {$columns}; --gm-gap: {$gap}px; --gm-aspect-ratio: 16/9;"); ?>">
        <?php while ($q->have_posts()): $q->the_post();
            $post_id  = \get_the_ID();
            $url      = \get_post_meta($post_id, 'gm_url', true);
            $thumb_id = \get_post_thumbnail_id($post_id);
            $img_html = '';
            if ($thumb_id) {
                $alt = \get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
                if (!$alt) $alt = \get_the_title($post_id);
                $img_html = \wp_get_attachment_image($thumb_id, $image_size, false, array('alt' => $alt));
            }
            $open  = $url ? '<a class="gm-link" href="' . \esc_url($url) . '" target="' . \esc_attr($target) . '"' . ($rel ? ' rel="' . \esc_attr($rel) . '"' : '') . '>' : '<div class="gm-link" aria-hidden="true">';
            $close = $url ? '</a>' : '</div>';
        ?>
            <?php
            $size = \get_post_meta($post_id, 'gm_size', true) ?: '1x1';
            list($col_span, $row_span) = explode('x', $size);

            $style_attr = '';
            if ($col_span > 1) $style_attr .= "grid-column: span {$col_span}; ";
            if ($row_span > 1) $style_attr .= "grid-row: span {$row_span}; ";
            ?>
            <article class="gm-card" <?php if ($style_attr) echo ' style="' . \esc_attr(trim($style_attr)) . '"'; ?>>
                <?php echo $open; ?>
                <?php
                $img_height = \get_post_meta($post_id, 'gm_img_height', true);
                $media_style = '';
                if ($img_height) {
                    $media_style = "height: {$img_height}; aspect-ratio: unset;";
                }
                ?>
                <figure class="gm-media" <?php if ($media_style) echo ' style="' . \esc_attr($media_style) . '"'; ?>>
                    <?php echo $img_html ?: '<div class="gm-media--placeholder"></div>'; ?>
                </figure>
                <h3 class="gm-title"><?php echo \esc_html(\get_the_title()); ?></h3>
                <?php echo $close; ?>
            </article>
        <?php endwhile;
        \wp_reset_postdata(); ?>
    </div>
<?php
    return ob_get_clean();
}

// Activation/Deactivation
\register_activation_hook(__FILE__, __NAMESPACE__ . '\\activate');
function activate()
{
    register_content_types();
    \flush_rewrite_rules();
}
\register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\deactivate');
function deactivate()
{
    \flush_rewrite_rules();
}

// Register Size Meta Box
\add_action('add_meta_boxes', __NAMESPACE__ . '\\register_size_metabox');
function register_size_metabox()
{
    \add_meta_box(
        'gm_size_mb',
        __('Card Size', 'grid-manager'),
        __NAMESPACE__ . '\\render_size_metabox',
        'gm_item',
        'side',
        'default'
    );
}

function render_size_metabox($post)
{
    $value = \get_post_meta($post->ID, 'gm_size', true);
    if (!$value) $value = '1x1';
    \wp_nonce_field('gm_save_size_' . $post->ID, 'gm_size_nonce');
?>
    <p>
        <label for="gm_size_field" class="screen-reader-text"><?php _e('Card Size', 'grid-manager'); ?></label>
        <select id="gm_size_field" name="gm_size_field" style="width:100%">
            <option value="1x1" <?php selected($value, '1x1'); ?>>1×1</option>
            <option value="2x1" <?php selected($value, '2x1'); ?>>2×1</option>
            <option value="1x2" <?php selected($value, '1x2'); ?>>1×2</option>
            <option value="2x2" <?php selected($value, '2x2'); ?>>2×2</option>
            <option value="3x1" <?php selected($value, '3x1'); ?>>3×1</option>
            <option value="1x3" <?php selected($value, '1x3'); ?>>1×3</option>
        </select>
    </p>
    <p style="font-size:12px;color:#555;"><?php _e('Choose how many columns and rows this card should span.', 'grid-manager'); ?></p>
<?php
}

// Save Size
\add_action('save_post_gm_item', __NAMESPACE__ . '\\save_size_meta', 10, 2);
function save_size_meta($post_id, $post)
{
    if (\defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['gm_size_nonce']) || !\wp_verify_nonce($_POST['gm_size_nonce'], 'gm_save_size_' . $post_id)) return;
    if (!\current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['gm_size_field'])) {
        $allowed = array('1x1', '2x1', '1x2', '2x2', '3x1', '1x3');
        $size = sanitize_text_field($_POST['gm_size_field']);
        if (in_array($size, $allowed)) {
            \update_post_meta($post_id, 'gm_size', $size);
        } else {
            \delete_post_meta($post_id, 'gm_size');
        }
    }
}

// Register Image Height Meta Box
\add_action('add_meta_boxes', __NAMESPACE__ . '\\register_image_height_metabox');
function register_image_height_metabox()
{
    \add_meta_box(
        'gm_img_height_mb',
        __('Image Height', 'grid-manager'),
        __NAMESPACE__ . '\\render_image_height_metabox',
        'gm_item',
        'side',
        'default'
    );
}

function render_image_height_metabox($post)
{
    $value = \get_post_meta($post->ID, 'gm_img_height', true);
    \wp_nonce_field('gm_save_img_height_' . $post->ID, 'gm_img_height_nonce');
?>
    <p>
        <label for="gm_img_height_field" class="screen-reader-text"><?php _e('Image Height', 'grid-manager'); ?></label>
        <input type="text" id="gm_img_height_field" name="gm_img_height_field"
            value="<?php echo \esc_attr($value); ?>"
            placeholder="e.g., 200px, 50vh, 30%"
            style="width:100%" />
    </p>
    <p style="font-size:12px;color:#555;">
        <?php _e('Set fixed height (e.g., 200px, 50vh). Leave empty to use grid default.', 'grid-manager'); ?>
    </p>
<?php
}

// Save Image Height
\add_action('save_post_gm_item', __NAMESPACE__ . '\\save_image_height_meta', 10, 2);
function save_image_height_meta($post_id, $post)
{
    if (\defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!isset($_POST['gm_img_height_nonce']) || !\wp_verify_nonce($_POST['gm_img_height_nonce'], 'gm_save_img_height_' . $post_id)) return;
    if (!\current_user_can('edit_post', $post_id)) return;

    if (isset($_POST['gm_img_height_field'])) {
        $height = sanitize_text_field(trim($_POST['gm_img_height_field']));
        if ($height) {
            // Basic validation: must contain px, vh, %, rem, em
            if (preg_match('/^\d+(px|vh|%|rem|em)$/i', $height)) {
                \update_post_meta($post_id, 'gm_img_height', $height);
            } else {
                \delete_post_meta($post_id, 'gm_img_height');
            }
        } else {
            \delete_post_meta($post_id, 'gm_img_height');
        }
    }
}
