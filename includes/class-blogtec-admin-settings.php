<?php
if (!defined('ABSPATH')) {
    exit;
}

class Blogtec_Admin_Settings {

    private $settings_option = 'blogtec_features_settings';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_core_settings'));
    }

    // Add the main settings page for Blogtec Features
    public function add_settings_page() {
        add_menu_page(
            __('Blogtec Features Settings', 'blogtec-features-manager'),
            __('Blogtec Features', 'blogtec-features-manager'),
            'manage_options',
            'blogtec-features-settings',
            array($this, 'render_settings_page'),
            '',
            '59' // Position it above "Appearance"
        );
    }

    // Register the core settings (feature section but not fields)
    public function register_core_settings() {
        register_setting(
            $this->settings_option,
            $this->settings_option,
            array($this, 'sanitize_settings')
        );

        add_settings_section(
            'blogtec_features_section',
            __('Manage Plugin Features', 'blogtec-features-manager'),
            null,
            'blogtec-features-settings'
        );
    }

    // Sanitize settings input
    public function sanitize_settings($input) {
        $new_input = array();
        $new_input['enable_pricing_table'] = isset($input['enable_pricing_table']) ? 1 : 0;
        $new_input['enable_custom_features'] = isset($input['enable_custom_features']) ? 1 : 0;
        return $new_input;
    }

    // Render the settings page
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Blogtec Features Settings', 'blogtec-features-manager'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields($this->settings_option);
                do_settings_sections('blogtec-features-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
