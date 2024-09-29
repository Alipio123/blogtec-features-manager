<?php
/*
Plugin Name: Blogtec Features Manager
Plugin URI: https://blogtec.io
Description: A custom plugin to manage all Blogtec.io specific features and functionalities.
Version: 1.0.2
Author: Alipio Gabriel
Author URI: https://blogtec.io
License: GPL2
Text Domain: blogtec-features-manager
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define constants
define('BLOGTEC_PLUGIN_VERSION', '1.0.0');
define('BLOGTEC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BLOGTEC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Includes the pricing feature
require_once BLOGTEC_PLUGIN_DIR . 'includes/pricing-table.php';


// Initialize the update checker
require BLOGTEC_PLUGIN_DIR . 'includes/plugin-update-checker-master/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
$update_checker = PucFactory::buildUpdateChecker(
    'https://github.com/Alipio123/blogtec-features-manager/',
    __FILE__,
    'blogtec-features-manager'
);
$update_checker->setBranch('main');

// Plugin activation hook
function blogtec_features_manager_activate() {
    // Code to run during activation (e.g., creating custom tables or options)
}
register_activation_hook(__FILE__, 'blogtec_features_manager_activate');

// Plugin deactivation hook
function blogtec_features_manager_deactivate() {
    // Code to run during deactivation (e.g., cleaning up or saving settings)
}
register_deactivation_hook(__FILE__, 'blogtec_features_manager_deactivate');

// Enqueue scripts and styles
function blogtec_enqueue_scripts() {
    wp_enqueue_style('blogtec-style', BLOGTEC_PLUGIN_URL . 'assets/css/style.css', array(), BLOGTEC_PLUGIN_VERSION);
    wp_enqueue_script('blogtec-script', BLOGTEC_PLUGIN_URL . 'assets/js/script.js', array('jquery'), BLOGTEC_PLUGIN_VERSION, true);
}
add_action('wp_enqueue_scripts', 'blogtec_enqueue_scripts');

// Example custom function
function blogtec_custom_feature() {
    echo '<div class="blogtec-custom-feature">This is a custom feature for Blogtec.io</div>';
}
add_action('wp_footer', 'blogtec_custom_feature');

// Create a custom admin menu
function blogtec_add_admin_menu() {
    add_menu_page(
        __('Blogtec Features', 'blogtec-features-manager'),
        __('Blogtec Features', 'blogtec-features-manager'),
        'manage_options',
        'blogtec-features-manager',
        'blogtec_admin_page',
        'dashicons-admin-generic',
        6
    );
}
add_action('admin_menu', 'blogtec_add_admin_menu');

// Admin page content
function blogtec_admin_page() {
    ?>
    <div class="wrap">
        <h1><?php _e('Blogtec Features Manager', 'blogtec-features-manager'); ?></h1>
        <p><?php _e('Welcome to the Blogtec Features Manager plugin!', 'blogtec-features-manager'); ?></p>
        <!-- Add custom settings or features here -->
    </div>
    <?php
}
