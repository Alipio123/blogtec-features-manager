<?php
/*
Plugin Name: Blogtec Features Manager
Plugin URI: https://blogtec.io
Description: A custom plugin to manage all Blogtec.io specific features and functionalities.
Version: 1.2.0
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
define('BLOGTEC_PLUGIN_VERSION', '1.0.4');
define('BLOGTEC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BLOGTEC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Includes the admin setting
require_once BLOGTEC_PLUGIN_DIR . 'includes/class-blogtec-admin-setting.php';

// Includes the pricing feature
require_once BLOGTEC_PLUGIN_DIR . 'includes/class-blogtec-pricing-table.php';


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
    $blogtec_pricingTable = new Blogtec_Pricing_Table();
    $blogtec_pricingTable->create_pricing_table();
}
register_activation_hook(__FILE__, 'blogtec_features_manager_activate');

// Plugin deactivation hook
function blogtec_features_manager_deactivate() {
    // Code to run during activation (e.g., creating custom tables or options)
    $blogtec_pricingTable = new Blogtec_Pricing_Table();
    $blogtec_pricingTable->blogtec_pricing_table_deactivate();
}
register_deactivation_hook(__FILE__, 'blogtec_features_manager_deactivate');

// Enqueue scripts and styles
function blogtec_enqueue_scripts() {
    wp_enqueue_style('blogtec-style', BLOGTEC_PLUGIN_URL . 'assets/css/style.css', array(), BLOGTEC_PLUGIN_VERSION);
    wp_enqueue_script('blogtec-script', BLOGTEC_PLUGIN_URL . 'assets/js/script.js', array('jquery'), BLOGTEC_PLUGIN_VERSION, true);
}
add_action('wp_enqueue_scripts', 'blogtec_enqueue_scripts');