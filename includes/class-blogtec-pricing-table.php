<?php
if (!defined('ABSPATH')) {
    exit;
}

class Blogtec_Pricing_Table {

    private $table_name;
    private $category_table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'blogtec_pricing';
        $this->category_table_name = $wpdb->prefix . 'blogtec_pricing_categories';
        
        // Admin menu hooks
        add_action('admin_menu', array($this, 'add_pricing_admin_menu'));
        
        // Admin script enqueue hook
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    // Create the pricing and category tables on plugin activation
    public function create_pricing_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Create categories table
        $category_sql = "CREATE TABLE IF NOT EXISTS $this->category_table_name (
            id INT NOT NULL AUTO_INCREMENT,
            category_name varchar(255) NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($category_sql);

        // Insert the default "SEO Content" category
        $category_id = $wpdb->get_var("SELECT id FROM $this->category_table_name WHERE category_name = 'SEO Content'");
        if (!$category_id) {
            $wpdb->insert($this->category_table_name, array(
                'category_name' => 'SEO Content',
            ));
            $category_id = $wpdb->insert_id;  // Get the inserted category's ID
        }

        // Create the pricing table with simple integer ID
        $pricing_sql = "CREATE TABLE IF NOT EXISTS $this->table_name (
            id INT NOT NULL AUTO_INCREMENT,
            word_count_range varchar(255) NOT NULL,
            price decimal(10, 2) NOT NULL,
            category_id INT NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        dbDelta($pricing_sql);

        // Insert initial pricing data if empty
        if ($wpdb->get_var("SELECT COUNT(*) FROM $this->table_name WHERE category_id = '$category_id'") == 0) {
            $this->insert_initial_pricing_data($category_id);
        }
    }

    private function insert_initial_pricing_data($category_id) {
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
                'price' => $price,
                'category_id' => $category_id
            ));
        }
    }

    // Enqueue admin scripts
    public function enqueue_admin_scripts() {
        wp_enqueue_style('blogtec-admin-style', BLOGTEC_PLUGIN_URL . 'assets/css/admin-style.css', array(), BLOGTEC_PLUGIN_VERSION);
        wp_enqueue_script('blogtec-pricing-table', BLOGTEC_PLUGIN_URL . 'assets/js/blogtec-pricing-table-admin-script.js', array(), BLOGTEC_PLUGIN_VERSION);
    }

    // Add the admin menu
    public function add_pricing_admin_menu() {
        add_submenu_page(
            'blogtec-features-manager', 
            __('Pricing Table', 'blogtec-features-manager'), 
            __('Pricing Table', 'blogtec-features-manager'), 
            'manage_options', 
            'blogtec-pricing-table', 
            array($this, 'render_pricing_page')
        );
    }

    // Function to fetch the category name
    private function get_selected_category_name($category_id) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT category_name FROM $this->category_table_name WHERE id = %d", $category_id));
    }

    // Render the pricing table page with category management
    public function render_pricing_page() {
        global $wpdb;

        // Handle category creation
        if (isset($_POST['create_category'])) {
            check_admin_referer('blogtec_category_nonce');
            $this->create_category(sanitize_text_field($_POST['category_name']));
        }

        // Handle category deletion
        if (isset($_POST['delete_category']) && !empty($_POST['category_id'])) {
            check_admin_referer('blogtec_category_nonce');
            $this->delete_category(sanitize_text_field($_POST['category_id']));
        }

        // Fetch current categories and selected category
        $categories = $wpdb->get_results("SELECT * FROM $this->category_table_name");
        $selected_category_id = isset($_POST['selected_category']) ? sanitize_text_field($_POST['selected_category']) : $categories[0]->id;

        // Fetch the selected category's name
        $selected_category_name = $this->get_selected_category_name($selected_category_id);

        // Fetch current pricing data for the selected category, ordered by word count range
        $pricing_data = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $this->table_name 
            WHERE category_id = %d 
            ORDER BY CAST(SUBSTRING_INDEX(word_count_range, '-', 1) AS UNSIGNED) ASC
        ", $selected_category_id));

        ?>
        <div class="wrap">
            <h1><?php _e('Pricing Table for ', 'blogtec-features-manager'); echo esc_html($selected_category_name); ?></h1>

            <!-- Category Management -->
            <form method="post">
                <?php wp_nonce_field('blogtec_category_nonce'); ?>
                <h2><?php _e('Manage Categories', 'blogtec-features-manager'); ?></h2>
                <select name="selected_category" onchange="this.form.submit()">
                    <?php foreach ($categories as $category) { ?>
                        <option value="<?php echo esc_attr($category->id); ?>" <?php selected($selected_category_id, $category->id); ?>>
                            <?php echo esc_html($category->category_name); ?>
                        </option>
                    <?php } ?>
                </select>

                <h3><?php _e('Add New Category', 'blogtec-features-manager'); ?></h3>
                <input type="text" name="category_name" placeholder="Enter category name" />
                <button type="submit" name="create_category" class="button-primary"><?php _e('Create Category', 'blogtec-features-manager'); ?></button>

                <!-- Delete category -->
                <h3><?php _e('Delete Category', 'blogtec-features-manager'); ?></h3>
                <!-- Add hidden input to send selected category ID -->
                <input type="hidden" name="category_id" value="<?php echo esc_attr($selected_category_id); ?>">
                <button type="submit" name="delete_category" class="button-secondary" onclick="return confirm('Are you sure?');">
                    <?php _e('Delete Category', 'blogtec-features-manager'); ?>
                </button>
            </form>

            <!-- Pricing Table Management -->
            <form method="post">
                <?php wp_nonce_field('blogtec_pricing_nonce'); ?>
                <h2><?php _e('Manage Pricing for ', 'blogtec-features-manager'); echo esc_html($selected_category_name); ?></h2>
                <table class="wp-list-table widefat fixed striped" id="pricing-table">
                    <thead>
                        <tr>
                            <th><?php _e('Word Count Range', 'blogtec-features-manager'); ?></th>
                            <th><?php _e('Price', 'blogtec-features-manager'); ?></th>
                            <th><?php _e('Actions', 'blogtec-features-manager'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pricing_data as $data) { ?>
                            <tr>
                                <td>
                                    <input type="text" name="pricing[<?php echo esc_attr($data->id); ?>][word_count_range]" value="<?php echo esc_attr($data->word_count_range); ?>" />
                                </td>
                                <td>
                                    <input type="text" name="pricing[<?php echo esc_attr($data->id); ?>][price]" value="<?php echo esc_attr($data->price); ?>" />
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field('blogtec_pricing_nonce'); ?>
                                        <input type="hidden" name="delete_row_id" value="<?php echo esc_attr($data->id); ?>">
                                        <button type="submit" name="delete_row" class="button-secondary" onclick="return confirm('Are you sure you want to delete this row?');">
                                            <?php _e('Delete', 'blogtec-features-manager'); ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <p>
                    <button type="button" id="add-row" class="button-secondary"><?php _e('Add New Row', 'blogtec-features-manager'); ?></button>
                </p>
                <table id="new-rows-table" style="display: none;">
                    <tbody>
                        <tr>
                            <td><input type="text" name="new_row[0][word_count_range]" placeholder="Enter Word Count Range" /></td>
                            <td><input type="text" name="new_row[0][price]" placeholder="Enter Price" /></td>
                        </tr>
                    </tbody>
                </table>
                <p>
                    <input type="submit" name="save_pricing" class="button-primary" value="<?php _e('Save Pricing', 'blogtec-features-manager'); ?>" />
                </p>
            </form>
        </div>
        <?php
    }

    // Function to create a new category
    private function create_category($category_name) {
        global $wpdb;

        // Check if category name is empty
        if (empty($category_name)) {
            echo '<div class="error"><p>' . __('Category name cannot be empty!', 'blogtec-features-manager') . '</p></div>';
            return; // Stop the function if the category name is empty
        }

        // Insert the category into the database
        $wpdb->insert($this->category_table_name, array(
            'category_name' => $category_name,
        ));

        // Get the newly inserted category's ID
        $new_category_id = $wpdb->insert_id;

        // Call the function to insert initial pricing data for the new category
        $this->insert_initial_pricing_data($new_category_id);

        echo '<div class="updated"><p>Category created and initial pricing added successfully!</p></div>';
    }

    // Function to delete a category
    private function delete_category($category_id) {
        global $wpdb;

        // Cast the category_id to an integer to ensure proper comparison
        $category_id = (int) $category_id;

        // Check if the category is the main category (ID 1)
        if ($category_id === 1) {
            echo '<div class="error"><p>' . __('You cannot delete the Main Category', 'blogtec-features-manager') . '</p></div>';
            return; // Stop the function if the category ID is 1
        }

        // Delete the category and associated pricing data
        $wpdb->delete($this->category_table_name, array('id' => $category_id), array('%d'));
        $wpdb->delete($this->table_name, array('category_id' => $category_id), array('%d'));
        echo '<div class="updated"><p>Category and its associated pricing data deleted successfully!</p></div>';
    }

    // Function to save pricing data
    private function save_pricing() {
        global $wpdb;

        // Update existing pricing data
        if (!empty($_POST['pricing'])) {
            foreach ($_POST['pricing'] as $id => $values) {
                $wpdb->update(
                    $this->table_name,
                    array(
                        'word_count_range' => sanitize_text_field($values['word_count_range']),
                        'price' => floatval($values['price'])
                    ),
                    array('id' => sanitize_text_field($id)),
                    array('%s', '%f'),
                    array('%d')
                );
            }
        }

        // Add new rows if any
        if (!empty($_POST['new_row'])) {
            foreach ($_POST['new_row'] as $new_row) {
                if (!empty($new_row['word_count_range']) && !empty($new_row['price'])) {
                    $wpdb->insert($this->table_name, array(
                        'word_count_range' => sanitize_text_field($new_row['word_count_range']),
                        'price' => floatval($new_row['price']),
                        'category_id' => sanitize_text_field($_POST['selected_category'])
                    ));
                }
            }
        }
        echo '<div class="updated"><p>Pricing updated successfully!</p></div>';
    }

    // Function to delete pricing row
    private function delete_pricing_row($row_id) {
        global $wpdb;
        $wpdb->delete($this->table_name, array('id' => $row_id), array('%d'));
        echo '<div class="updated"><p>Row deleted successfully!</p></div>';
    }

    public function blogtec_pricing_table_deactivate() {
        global $wpdb;
        // Drop the pricing and category tables
        $wpdb->query("DROP TABLE IF EXISTS $this->table_name");
        $wpdb->query("DROP TABLE IF EXISTS $this->category_table_name");
    }
}

// Instantiate the class
new Blogtec_Pricing_Table();