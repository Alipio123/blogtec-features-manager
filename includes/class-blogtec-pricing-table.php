<?php
if (!defined('ABSPATH')) {
    exit;
}

class Blogtec_Pricing_Table {

    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'blogtec_pricing';
        // Admin menu hooks
        add_action('admin_menu', array($this, 'add_pricing_admin_menu'));
        
        // Admin script enqueue hook
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    // Create the pricing table on plugin activation
    public function create_pricing_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // SQL to create the table
        $sql = "CREATE TABLE IF NOT EXISTS $this->table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            word_count_range varchar(255) NOT NULL,
            price decimal(10, 2) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Insert default values if the table is empty
        if ($wpdb->get_var("SELECT COUNT(*) FROM $this->table_name") == 0) {
            $this->insert_default_values();
        }
    }

    // Insert default pricing data
    private function insert_default_values() {
        global $wpdb;
        $ranges = [
            '0-200' => 35,
            '200-400' => 52,
            '400-600' => 69,
            '600-800' => 86,
            '800-1000' => 103,
            '1000-1200' => 120,
            '1200-1400' => 137,
            '1400-1600' => 154,
            '1600-1800' => 171,
            '1800-2000' => 188,
            '2000-2200' => 205,
            '2200-2400' => 222,
            '2400-2600' => 239,
            '2600-2800' => 256,
            '2800-3000' => 315
        ];

        foreach ($ranges as $range => $price) {
            $wpdb->insert($this->table_name, array(
                'word_count_range' => $range,
                'price' => $price
            ));
        }
    }

    // Enqueue admin scripts
    public function enqueue_admin_scripts() {
        wp_enqueue_style('blogtec-admin-style', BLOGTEC_PLUGIN_URL . 'assets/css/admin-style.css', array(), BLOGTEC_PLUGIN_VERSION);
    }

    // Add the admin menu
    public function add_pricing_admin_menu() {
        add_submenu_page(
            'blogtec-features-manager', // Parent slug
            __('Pricing Table', 'blogtec-features-manager'), // Page title
            __('Pricing Table', 'blogtec-features-manager'), // Submenu title
            'manage_options', // Capability
            'blogtec-pricing-table', // Submenu slug
            array($this, 'render_pricing_page') // Callback function
        );
    }

    // Render the pricing table page
    public function render_pricing_page() {
        global $wpdb;

        // Update pricing if the form is submitted
        if (isset($_POST['save_pricing'])) {
            foreach ($_POST['pricing'] as $id => $price) {
                $wpdb->update(
                    $this->table_name,
                    array('price' => $price),
                    array('id' => $id),
                    array('%f'),
                    array('%d')
                );
            }
            echo '<div class="updated"><p>Pricing updated successfully!</p></div>';
        }

        // Fetch current pricing data
        $pricing_data = $wpdb->get_results("SELECT * FROM $this->table_name");

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
}

// Instantiate the class
new Blogtec_Pricing_Table();

