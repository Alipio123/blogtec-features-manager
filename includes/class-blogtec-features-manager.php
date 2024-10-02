<?php
// Autoload github Update Checker
require BLOGTEC_PLUGIN_DIR . 'includes/plugin-update-checker-master/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

class Blogtec_Features_Manager {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Hook the load_textdomain function to init action
        $this->load_textdomain();

        //load update checker github
        $this->load_update_checker();

        // Load admin settings for enabling/disabling features
        $this->load_admin_settings();
        $this->load_features();
    }

    public function activate() {
        $this->load_pricing_table()->create_pricing_table();
    }

    public function deactivate() {
        // Delegate the deactivation to each feature's admin settings class
        if (class_exists('Blogtec_Pricing_Table_Admin_Settings')) {
            $Blogtec_Pricing_Table = new Blogtec_Pricing_Table();
            $Blogtec_Pricing_Table->deactivate();
        }
    }

    private function load_admin_settings() {
        new Blogtec_Admin_Settings();

        // Load feature-specific admin settings
        if (class_exists('Blogtec_Pricing_Table_Admin_Settings')) {
            new Blogtec_Pricing_Table_Admin_Settings(); // The constructor will handle registering the settings
        }
        if (class_exists('Blogtec_Elementor_Widgets_Admin_Settings')) {
            new Blogtec_Elementor_Widgets_Admin_Settings(); // The constructor will handle registering the settings
        }
    }


    private function load_features() {
        $options = get_option('blogtec_features_settings', array());

        // Load features based on admin settings
        if (isset($options['enable_pricing_table']) && $options['enable_pricing_table']) {
            $this->load_pricing_table();
        }

        if (isset($options['enable_custom_features']) && $options['enable_custom_features']) {
            $this->load_elementor_widgets();
        }
    }

    private function load_pricing_table() {
        if (class_exists('Blogtec_Pricing_Table')) {
            return new Blogtec_Pricing_Table();
        }
    }

    private function load_elementor_widgets() {
        if (class_exists('Blogtec_Elementor_Widgets')) {
            return new Blogtec_Elementor_Widgets();
        }
    }

    // Method to load the plugin's text domain for translation
    public function load_textdomain() {
        $loaded = load_plugin_textdomain('blogtec-features-manager', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }

    public function load_update_checker() {
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
}
