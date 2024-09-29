<?php
if (!defined('ABSPATH')) {
    exit;
}

class Blogtec_Admin_Setting {

    private $option_name = 'blogtec_features_settings';

    public function __construct() {
        // Hook to add admin menu for the settings page
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Hook to register settings
        add_action('admin_init', array($this, 'register_settings'));
    }

    // Add the admin menu
    public function add_admin_menu() {
        add_menu_page(
            __('Blogtec Features Settings', 'blogtec-features-manager'), // Page title
            __('Blogtec Features', 'blogtec-features-manager'),          // Menu title
            'manage_options',                                            // Capability
            'blogtec-features-settings',                                 // Menu slug
            array($this, 'settings_page'),                               // Callback function
            '',                                                          // Icon URL (optional)
            3                                                           // Position in menu
        );
    }

    // Register settings
    public function register_settings() {
        // Register the settings option
        register_setting($this->option_name, $this->option_name, array($this, 'sanitize_settings'));

        // Add settings section
        add_settings_section(
            'blogtec_features_section',                                 // Section ID
            __('Features Settings', 'blogtec-features-manager'),         // Title
            null,                                                       // Callback (not needed)
            'blogtec-features-settings'                                 // Page slug
        );

        // Add settings field for the pricing table toggle
        add_settings_field(
            'enable_pricing_table',                                      // Field ID
            __('Enable Pricing Table Feature', 'blogtec-features-manager'), // Title
            array($this, 'enable_pricing_table_callback'),               // Callback to render the field
            'blogtec-features-settings',                                 // Page slug
            'blogtec_features_section'                                   // Section ID
        );
    }

    // Callback to render the pricing table toggle field
    public function enable_pricing_table_callback() {
        $options = get_option($this->option_name);
        $checked = isset($options['enable_pricing_table']) ? $options['enable_pricing_table'] : 0;
        ?>
        <input type="checkbox" name="<?php echo $this->option_name; ?>[enable_pricing_table]" value="1" <?php checked(1, $checked, true); ?>>
        <label for="enable_pricing_table"><?php _e('Enable the Pricing Table feature', 'blogtec-features-manager'); ?></label>
        <?php
    }

    // Sanitize the settings input
    public function sanitize_settings($input) {
        $new_input = array();
        $new_input['enable_pricing_table'] = isset($input['enable_pricing_table']) ? 1 : 0; // Ensure it's a boolean value
        return $new_input;
    }

    // Render the settings page
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Blogtec Features Settings', 'blogtec-features-manager'); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields($this->option_name);   // Output security fields for the registered setting
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
