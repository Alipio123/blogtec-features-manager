<?php
if (!defined('ABSPATH')) {
    exit;
}

class Blogtec_Elementor_Widgets_Admin_Settings implements Blogtec_Admin_Settings_Interface {

    private $settings_option = 'blogtec_features_settings';

    public function __construct() {
        // Hook into the admin_init action to add the settings fields
        add_action('admin_init', array($this, 'add_settings_fields'));
    }

    // Add settings fields for the Elementor Widgets
    public function add_settings_fields() {
        add_settings_field(
            'enable_custom_features',
            __('Enable Elementor Widgets', 'blogtec-features-manager'),
            array($this, 'render_elementor_widgets_checkbox'),
            'blogtec-features-settings',
            'blogtec_features_section',
            array(
                'label_for' => 'enable_custom_features',
                'description' => __('Enable Elementor custom widgets (e.g., number sliders).', 'blogtec-features-manager')
            )
        );
    }

    // Render the checkbox field for enabling Elementor Widgets
    public function render_elementor_widgets_checkbox($args) {
        // Get the plugin settings from the database, or default to an empty array
        $options = get_option($this->settings_option, []);

        // Determine the current value for the checkbox (default to 0 if not set)
        $value = !empty($options[$args['label_for']]) ? 1 : 0;

        // Output the checkbox field with its label, ensuring proper escaping
        echo sprintf(
            '<input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1" %3$s />
             <label for="%1$s">%4$s</label>',
            esc_attr($args['label_for']), // %1$s: The 'label_for' attribute, used as the checkbox ID and name
            esc_attr($this->settings_option), // %2$s: The settings option used in the name attribute
            checked(1, $value, false), // %3$s: The checked attribute (if the option is enabled)
            esc_html($args['description']) // %4$s: The description label
        );
    }

}
