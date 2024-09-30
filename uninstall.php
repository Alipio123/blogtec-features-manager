<?php
// If uninstall is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Global WordPress database variable
global $wpdb;

// Table names to delete
$table_name_pricing = $wpdb->prefix . 'blogtec_pricing';
$table_name_categories = $wpdb->prefix . 'blogtec_pricing_categories';

// Delete the custom tables
$wpdb->query("DROP TABLE IF EXISTS $table_name_pricing");
$wpdb->query("DROP TABLE IF EXISTS $table_name_categories");

// Option name to delete
$option_name = 'blogtec_features_settings';

// Delete the plugin settings option
delete_option($option_name);

// If your plugin is a multisite installation
// Delete the option in all sites of a network (optional)
// if (is_multisite()) {
//     $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
//     foreach ($blog_ids as $blog_id) {
//         switch_to_blog($blog_id);
//         delete_option($option_name);
//         $wpdb->query("DROP TABLE IF EXISTS $table_name_pricing");
//         $wpdb->query("DROP TABLE IF EXISTS $table_name_categories");
//         restore_current_blog();
//     }
// }
