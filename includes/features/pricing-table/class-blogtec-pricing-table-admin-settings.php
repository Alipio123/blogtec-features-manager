<?php
if (!defined('ABSPATH')) {
    exit;
}

class Blogtec_Pricing_Table_Admin_Settings implements Blogtec_Admin_Settings_Interface {

    private $settings_option = 'blogtec_features_settings';

    public function __construct() {
        // Hook into the admin_init action to add the settings fields
        add_action('admin_init', array($this, 'add_settings_fields'));
    }

    // Add settings fields for the Pricing Table
    public function add_settings_fields() {
        add_settings_field(
            'enable_pricing_table',
            __('Enable Pricing Table', 'blogtec-features-manager'),
            array($this, 'render_pricing_table_checkbox'),
            'blogtec-features-settings',
            'blogtec_features_section',
            array(
                'label_for' => 'enable_pricing_table',
                'description' => __('Enable the pricing table feature for managing service pricing.', 'blogtec-features-manager')
            )
        );
    }

    // Render the checkbox field for enabling the Pricing Table
    public function render_pricing_table_checkbox($args) {
        $options = get_option($this->settings_option);
        $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : 0;
        ?>
        <input type="checkbox" id="<?php echo esc_attr($args['label_for']); ?>" name="<?php echo esc_attr($this->settings_option . '[' . $args['label_for'] . ']'); ?>" value="1" <?php checked(1, $value, true); ?> />
        <label for="<?php echo esc_attr($args['label_for']); ?>"><?php echo esc_html($args['description']); ?></label>
        <?php
    }
}
