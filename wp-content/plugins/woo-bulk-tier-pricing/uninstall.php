<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;

// Remove CPT posts and related meta (optional - comment out if you want to retain on uninstall)
$rules = get_posts([
    'post_type'      => 'wcbtp_rule',
    'posts_per_page' => -1,
    'post_status'    => 'any',
    'fields'         => 'ids',
]);

foreach ($rules as $rid) {
    wp_delete_post($rid, true);
}

// Cleanup transients/options
delete_transient('wcbtp_rules_cache');