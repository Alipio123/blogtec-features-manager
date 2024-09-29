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

        $options = get_option('blogtec_features_settings');
        if (!empty($options['enable_pricing_table'])) {
            $this->setup_hooks();
        }
    }

    // Hook setup
    private function setup_hooks() {
        add_action('admin_menu', array($this, 'add_pricing_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    // Getters for Table Names
    public function get_table_name() {
        return $this->table_name;
    }

    public function get_category_table_name() {
        return $this->category_table_name;
    }

    // Create database tables
    public function create_pricing_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $category_sql = "
            CREATE TABLE IF NOT EXISTS {$this->get_category_table_name()} (
                id INT NOT NULL AUTO_INCREMENT,
                category_name VARCHAR(255) NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;
        ";
        dbDelta($category_sql);

        // Create pricing table
        $pricing_sql = "
            CREATE TABLE IF NOT EXISTS {$this->get_table_name()} (
                id INT NOT NULL AUTO_INCREMENT,
                word_count_range VARCHAR(255) NOT NULL,
                price DECIMAL(10, 2) NOT NULL,
                category_id INT NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;
        ";
        dbDelta($pricing_sql);

        $this->insert_default_category_and_pricing();
    }

    // Insert default category and pricing
    private function insert_default_category_and_pricing() {
        global $wpdb;
        $default_category = 'SEO Content';
        $category_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->get_category_table_name()} WHERE category_name = %s", $default_category));

        if (!$category_id) {
            $wpdb->insert($this->get_category_table_name(), array('category_name' => $default_category));
            $category_id = $wpdb->insert_id;
            $this->insert_initial_pricing_data($category_id);
        }
    }

    // Insert initial pricing data
    private function insert_initial_pricing_data($category_id) {
        global $wpdb;
        $ranges = [
            '0-200' => 35, '200-400' => 52, '400-600' => 69,
            '600-800' => 86, '800-1000' => 103, '1000-1200' => 120,
            '1200-1400' => 137, '1400-1600' => 154, '1600-1800' => 171,
            '1800-2000' => 188, '2000-2200' => 205, '2200-2400' => 222,
            '2400-2600' => 239, '2600-2800' => 256, '2800-3000' => 315
        ];

        foreach ($ranges as $range => $price) {
            $wpdb->insert($this->get_table_name(), array(
                'word_count_range' => sanitize_text_field($range),
                'price' => floatval($price),
                'category_id' => intval($category_id)
            ));
        }
    }

    // Enqueue admin scripts
    public function enqueue_admin_scripts() {
        wp_enqueue_style('blogtec-admin-style', BLOGTEC_PLUGIN_URL . 'assets/css/admin-style.css', array(), BLOGTEC_PLUGIN_VERSION);
        wp_enqueue_script('blogtec-pricing-table', BLOGTEC_PLUGIN_URL . 'assets/js/blogtec-pricing-table-admin-script.js', array('jquery'), BLOGTEC_PLUGIN_VERSION, true);
        wp_localize_script('blogtec-pricing-table', 'blogtecL10n', array(
            'cancel' => __('Cancel', 'blogtec-features-manager'),
            'addNewRow' => __('Add New Row', 'blogtec-features-manager'),
        ));
    }

    // Admin menu
    public function add_pricing_admin_menu() {
        add_menu_page(
            __('Services Pricing Table', 'blogtec-features-manager'), 
            __('Services Pricing Table', 'blogtec-features-manager'), 
            'manage_options', 
            'blogtec-pricing-table', 
            array($this, 'render_pricing_page'),
            'dashicons-editor-table',
            3  
        );
    }

    // Fetch category name
    private function get_category_name($category_id) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT category_name FROM {$this->get_category_table_name()} WHERE id = %d", $category_id));
    }

    // Render pricing page
    public function render_pricing_page() {
        global $wpdb;

        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handle_form_submission();
        }

        // Fetch categories and selected category
        $categories = $wpdb->get_results("SELECT * FROM {$this->get_category_table_name()}");
        $selected_category_id = isset($_POST['selected_category']) ? intval($_POST['selected_category']) : $categories[0]->id;

        // Fetch pricing data for selected category
        $pricing_data = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$this->get_table_name()} 
            WHERE category_id = %d 
            ORDER BY CAST(SUBSTRING_INDEX(word_count_range, '-', 1) AS UNSIGNED) ASC
        ", $selected_category_id));

        ?>
        <div class="wrap">
            <h1><?php _e('Pricing Table for ', 'blogtec-features-manager'); echo esc_html($this->get_category_name($selected_category_id)); ?></h1>

            <!-- Category Management -->
            <form method="post">
                <?php wp_nonce_field('create_category_action', 'create_category_nonce'); ?>
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
                <?php wp_nonce_field('delete_category_action', 'delete_category_nonce'); ?>
                <input type="hidden" name="category_id" value="<?php echo esc_attr($selected_category_id); ?>">
                <button type="submit" name="delete_category" class="button-secondary" onclick="return confirm('Are you sure?');">
                    <?php _e('Delete Category', 'blogtec-features-manager'); ?>
                </button>
            </form>

            <!-- Pricing Table Management -->
            <form method="post">
                <?php wp_nonce_field('save_pricing_action', 'save_pricing_nonce'); ?>
                <input type="hidden" name="selected_category" value="<?php echo esc_attr($selected_category_id); ?>" />

                <h2><?php _e('Manage Pricing for ', 'blogtec-features-manager'); echo esc_html($this->get_category_name($selected_category_id)); ?></h2>
                <table class="wp-list-table widefat fixed striped" id="pricing-table">
                    <thead>
                        <tr>
                            <th><?php _e('Word Count Range', 'blogtec-features-manager'); ?></th>
                            <th><?php _e('Price (€)', 'blogtec-features-manager'); ?></th>
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
                                        <?php wp_nonce_field('delete_row_action', 'delete_row_nonce'); ?>
                                        <input type="hidden" name="delete_row_id" value="<?php echo esc_attr($data->id); ?>">
                                        <input type="hidden" name="selected_category" value="<?php echo esc_attr($selected_category_id); ?>">
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
                <input type="submit" name="save_pricing" class="button-primary" value="<?php _e('Save Pricing', 'blogtec-features-manager'); ?>" />
            </form>
        </div>
        <?php
    }

    // Handle form submissions
    private function handle_form_submission() {
        if (isset($_POST['save_pricing'])) {
            if (wp_verify_nonce($_POST['save_pricing_nonce'], 'save_pricing_action')) {
                $this->save_pricing();
            } else {
                wp_die('Nonce verification failed for pricing.');
            }
        }

        if (isset($_POST['delete_row']) && !empty($_POST['delete_row_id'])) {
            if (wp_verify_nonce($_POST['delete_row_nonce'], 'delete_row_action')) {
                $this->delete_pricing_row(intval($_POST['delete_row_id']));
            } else {
                wp_die('Nonce verification failed for deleting row.');
            }
        }

        if (isset($_POST['create_category'])) {
            if (wp_verify_nonce($_POST['create_category_nonce'], 'create_category_action')) {
                $this->create_category(sanitize_text_field($_POST['category_name']));
            } else {
                wp_die('Nonce verification failed for creating category.');
            }
        }

        if (isset($_POST['delete_category']) && !empty($_POST['category_id'])) {
            if (wp_verify_nonce($_POST['delete_category_nonce'], 'delete_category_action')) {
                $this->delete_category(intval($_POST['category_id']));
            } else {
                wp_die('Nonce verification failed for deleting category.');
            }
        }
    }

    // Create new category
    private function create_category($category_name) {
        global $wpdb;

        if (empty($category_name)) {
            echo '<div class="error"><p>' . __('Category name cannot be empty!', 'blogtec-features-manager') . '</p></div>';
            return;
        }

        $wpdb->insert($this->get_category_table_name(), array('category_name' => $category_name));
        $new_category_id = $wpdb->insert_id;
        $this->insert_initial_pricing_data($new_category_id);
        $_POST['selected_category'] = $new_category_id;

        echo '<div class="updated"><p>' . __('Category created and initial pricing added successfully!', 'blogtec-features-manager') . '</p></div>';
    }

    // Delete category
    private function delete_category($category_id) {
        global $wpdb;

        if ($category_id === 1) {
            echo '<div class="error"><p>' . __('You cannot delete the Main Category', 'blogtec-features-manager') . '</p></div>';
            return;
        }

        $wpdb->delete($this->get_category_table_name(), array('id' => $category_id), array('%d'));
        $wpdb->delete($this->get_table_name(), array('category_id' => $category_id), array('%d'));

        // Set the first available category after deletion
        $first_available_category = $wpdb->get_var("SELECT id FROM {$this->get_category_table_name()} ORDER BY id ASC LIMIT 1");
        $_POST['selected_category'] = $first_available_category ?: 1;

        echo '<div class="updated"><p>' . __('Category and its associated pricing data deleted successfully!', 'blogtec-features-manager') . '</p></div>';
    }

    // Save pricing
    private function save_pricing() {
        global $wpdb;

        if (!empty($_POST['pricing'])) {
            foreach ($_POST['pricing'] as $id => $values) {
                $wpdb->update(
                    $this->get_table_name(),
                    array(
                        'word_count_range' => sanitize_text_field($values['word_count_range']),
                        'price' => floatval($values['price'])
                    ),
                    array('id' => intval($id)),
                    array('%s', '%f'),
                    array('%d')
                );
            }
        }

        if (!empty($_POST['new_row'])) {
            foreach ($_POST['new_row'] as $new_row) {
                if (!empty($new_row['word_count_range']) && !empty($new_row['price'])) {
                    $wpdb->insert($this->get_table_name(), array(
                        'word_count_range' => sanitize_text_field($new_row['word_count_range']),
                        'price' => floatval($new_row['price']),
                        'category_id' => intval($_POST['selected_category'])
                    ));
                }
            }
        }

        echo '<div class="updated"><p>' . __('Pricing updated successfully!', 'blogtec-features-manager').'</p></div>';
    }

    // Delete pricing row
    private function delete_pricing_row($row_id) {
        global $wpdb;
        $wpdb->delete($this->get_table_name(), array('id' => intval($row_id)), array('%d'));
        echo '<div class="updated"><p>' . __('Row deleted successfully!', 'blogtec-features-manager') . '</p></div>';
    }

    // Deactivation cleanup
    public function blogtec_pricing_table_deactivate() {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$this->get_table_name()}");
        $wpdb->query("DROP TABLE IF EXISTS {$this->get_category_table_name()}");
    }
}

// Instantiate the class
new Blogtec_Pricing_Table();
