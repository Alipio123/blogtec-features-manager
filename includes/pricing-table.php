<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin activation hook to create the pricing table
function blogtec_create_pricing_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'blogtec_pricing';
    $charset_collate = $wpdb->get_charset_collate();

    // SQL to create the table
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        word_count_range varchar(255) NOT NULL,
        price decimal(10, 2) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Insert default values if the table is empty
    if ($wpdb->get_var("SELECT COUNT(*) FROM $table_name") == 0) {
        $ranges = [
            '0-200' => 35,
            '200-400' => 52,
            // Continue for all ranges until '2800-3000' => 315
            '2800-3000' => 315
        ];

        foreach ($ranges as $range => $price) {
            $wpdb->insert($table_name, array(
                'word_count_range' => $range,
                'price' => $price
            ));
        }
    }
}
register_activation_hook(__FILE__, 'blogtec_create_pricing_table');

// Enqueue admin scripts for the pricing page
function blogtec_admin_enqueue_scripts() {
    wp_enqueue_style('blogtec-admin-style', BLOGTEC_PLUGIN_URL . 'assets/css/admin-style.css', array(), BLOGTEC_PLUGIN_VERSION);
}
add_action('admin_enqueue_scripts', 'blogtec_admin_enqueue_scripts');

// Create a custom admin page for managing the pricing table
function blogtec_add_pricing_admin_menu() {
    add_submenu_page(
        'blogtec-features-manager',
        __('Pricing Table', 'blogtec-features-manager'),
        __('Pricing Table', 'blogtec-features-manager'),
        'manage_options',
        'blogtec-pricing-table',
        'blogtec_render_pricing_page'
    );
}
add_action('admin_menu', 'blogtec_add_pricing_admin_menu');

// Render the admin page for the pricing table
function blogtec_render_pricing_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'blogtec_pricing';

    // Update pricing if the form is submitted
    if (isset($_POST['save_pricing'])) {
        foreach ($_POST['pricing'] as $id => $price) {
            $wpdb->update(
                $table_name,
                array('price' => $price),
                array('id' => $id),
                array('%f'),
                array('%d')
            );
        }
        echo '<div class="updated"><p>Pricing updated successfully!</p></div>';
    }

    // Fetch current pricing data
    $pricing_data = $wpdb->get_results("SELECT * FROM $table_name");

    ?>
    <div class="wrap">
        <h1><?php _e('Pricing Table', 'blogtec-features-manager'); ?></h1>
        <form method="post">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?php _e('Word Count Range', 'blogtec-features-manager'); ?></th>
                        <th><?php _e('Price', 'blogtec-features-manager'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pricing_data as $data) { ?>
                        <tr>
                            <td><?php echo esc_html($data->id); ?></td>
                            <td><?php echo esc_html($data->word_count_range); ?></td>
                            <td>
                                <input type="text" name="pricing[<?php echo esc_attr($data->id); ?>]" value="<?php echo esc_attr($data->price); ?>" />
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <p>
                <input type="submit" name="save_pricing" class="button-primary" value="<?php _e('Save Pricing', 'blogtec-features-manager'); ?>" />
            </p>
        </form>
    </div>
    <?php
}
