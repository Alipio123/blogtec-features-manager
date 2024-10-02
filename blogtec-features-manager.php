<?php
/*  
Plugin Name: Blogtec Features Manager
Plugin URI: https://blogtec.io
Description: A custom plugin to manage all Blogtec.io specific features and functionalities.
Version: 1.4.4
Author: Alipio Gabriel
Author URI: https://blogtec.io
License: GPL2
Text Domain: blogtec-features-manager
*/

if (!defined('ABSPATH')) {
    exit;
}

define('BLOGTEC_PLUGIN_VERSION', '1.4.4');
define('BLOGTEC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BLOGTEC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Autoload Feature Classes
spl_autoload_register(function ($class_name) {
    if (false !== strpos($class_name, 'Blogtec')) {
        $file_name = str_replace('_', '-', strtolower($class_name)) . '.php';

        $paths = [
            BLOGTEC_PLUGIN_DIR . 'includes/',              
            BLOGTEC_PLUGIN_DIR . 'includes/features/',    
            BLOGTEC_PLUGIN_DIR . 'includes/features/elementor-widgets/',
            BLOGTEC_PLUGIN_DIR . 'includes/features/pricing-table/',
            BLOGTEC_PLUGIN_DIR . 'includes/interfaces/',
        ];

        foreach ($paths as $path) {
            $file_path = $path . 'class-' . $file_name;
            if (file_exists($file_path)) {
                require_once $file_path;
                break;
            }
        }
    }
});

function blogtec_features_manager_init() {
    Blogtec_Features_Manager::get_instance();
}
add_action('plugins_loaded', 'blogtec_features_manager_init');

function blogtec_features_manager_activate() {
    Blogtec_Features_Manager::get_instance()->activate();
}
register_activation_hook(__FILE__, 'blogtec_features_manager_activate');

function blogtec_features_manager_deactivate() {
    Blogtec_Features_Manager::get_instance()->deactivate();
}
register_deactivation_hook(__FILE__, 'blogtec_features_manager_deactivate');

//github update checker
require BLOGTEC_PLUGIN_DIR . 'includes/plugin-update-checker-master/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

function blogtec_initiate_update_checker() {
    $updateChecker = PucFactory::buildUpdateChecker(
        'https://github.com/Alipio123/blogtec-features-manager',
        __FILE__,
        'blogtec-features-manager'
    );

    // Set the branch to check the plugin updates from
    $updateChecker->setBranch('main');

    // Optional: Add an authentication token if your GitHub repository is private.
    // $updateChecker->setAuthentication('your-github-token-here');
}
add_action('plugins_loaded', 'blogtec_initiate_update_checker');
