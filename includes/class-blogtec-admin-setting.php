<?php
if (!defined('ABSPATH')) {
    exit;
}

class Blogtec_Admin_Setting {

    private $option_name = 'blogtec_features_settings';

    public function __construct() {
        $this->setup_hooks();
    }

    // Setup hooks
    private function setup_hooks() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    // Add the admin submenu under 'Settings'
    public function add_admin_menu() {
        add_options_page(
            __('Blogtec Features Settings', 'blogtec-features-manager'), // Page title
            __('Blogtec Features', 'blogtec-features-manager'),          // Menu title
            'manage_options',                                            // Capability
            'blogtec-features-settings',                                 // Menu slug
            array($this, 'render_settings_page')                         // Callback function
        );
    }

    // Register settings
    public function register_settings() {
        register_setting($this->get_option_name(), $this->get_option_name(), array($this, 'sanitize_settings'));

        add_settings_section(
            'blogtec_features_section',                                  // Section ID
            __('Features Settings', 'blogtec-features-manager'),          // Title
            null,                                                        // Callback (not needed)
            'blogtec-features-settings'                                  // Page slug
        );

        add_settings_field(
            'enable_pricing_table',                                      // Field ID
            __('Enable Pricing Table Feature', 'blogtec-features-manager'), // Title
            array($this, 'render_pricing_table_toggle'),                 // Callback to render the field
            'blogtec-features-settings',                                 // Page slug
            'blogtec_features_section'                                   // Section ID
        );
    }

    // Getter for option name
    private function get_option_name() {
        return $this->option_name;
    }

    // Callback to render the pricing table toggle field
    public function render_pricing_table_toggle() {
        $options = $this->get_options();
        $checked = isset($options['enable_pricing_table']) ? $options['enable_pricing_table'] : 0;
        ?>
        <input type="checkbox" name="<?php echo esc_attr($this->get_option_name()); ?>[enable_pricing_table]" value="1" <?php checked(1, $checked, true); ?>>
        <label for="enable_pricing_table"><?php esc_html_e('Enable the Pricing Table feature', 'blogtec-features-manager'); ?></label>
        <?php
    }

    // Get the current options
    private function get_options() {
        return get_option($this->get_option_name(), array());
    }

    // Sanitize the settings input
    public function sanitize_settings($input) {
        $new_input = array();
        $new_input['enable_pricing_table'] = isset($input['enable_pricing_table']) ? 1 : 0; // Ensure it's a boolean value
        return $new_input;
    }

    // Render the settings page
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Blogtec Features Settings', 'blogtec-features-manager'); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields($this->get_option_name());   // Output security fields for the registered setting
                do_settings_sections('blogtec-features-settings');  // Output settings sections and fields
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

// Instantiate the class
new Blogtec_Admin_Setting();
