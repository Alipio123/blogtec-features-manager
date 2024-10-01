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
        $this->setup_hooks();
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

    // Method to check if the pricing table exists
    public function check_table_exists() {
        global $wpdb;
        return $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") === $this->table_name;
    }

    // Create database tables
    public function create_pricing_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = [
            "CREATE TABLE IF NOT EXISTS {$this->get_category_table_name()} (
                id INT NOT NULL AUTO_INCREMENT,
                category_name VARCHAR(255) NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;",
            
            "CREATE TABLE IF NOT EXISTS {$this->get_table_name()} (
                id INT NOT NULL AUTO_INCREMENT,
                word_count_range VARCHAR(255) NOT NULL,
                price DECIMAL(10, 2) NOT NULL,
                category_id INT NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;"
        ];

        foreach ($sql as $query) {
            dbDelta($query);
        }

        $this->insert_default_categories_and_pricing();
    }

    // Insert default categories and pricing
    private function insert_default_categories_and_pricing() {
        $categories_with_pricing = [
            'SEO Content' => [
                '0-200'     => 35,
                '200-400'   => 52,
                '400-600'   => 69,
                '600-800'   => 89,
                '800-1000'  => 115,
                '1000-1200' => 135,
                '1200-1400' => 155,
                '1400-1600' => 175,
                '1600-1800' => 195,
                '1800-2000' => 215,
                '2000-2200' => 235,
                '2200-2400' => 255,
                '2400-2600' => 275,
                '2600-2800' => 295,
                '2800-3000' => 315,
            ],
            'SEO Translation' => [
                '0-200'     => 25,
                '200-400'   => 35,
                '400-600'   => 44,
                '600-800'   => 52,
                '800-1000'  => 59,
                '1000-1200' => 68,
                '1200-1400' => 77,
                '1400-1600' => 85,
                '1600-1800' => 93,
                '1800-2000' => 101,
                '2000-2200' => 109,
                '2200-2400' => 117,
                '2400-2600' => 125,
                '2600-2800' => 133,
                '2800-3000' => 141,
            ],
            'Content Optimization' => [
                '0-600'     => 36,
                '600-1200'  => 65,
                '1200-1800' => 95,
                '1800-2400' => 125,
                '2400-3000' => 155,
            ],
        ];


        foreach ($categories_with_pricing as $category_name => $pricing_data) {
            $this->upsert_category_and_pricing($category_name, $pricing_data);
        }
    }

    // Upsert category and pricing in one method
    private function upsert_category_and_pricing($category_name, $pricing_data) {
        global $wpdb;

        // Upsert category
        $category_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$this->get_category_table_name()} WHERE category_name = %s", $category_name));
        if (!$category_id) {
            $wpdb->insert($this->get_category_table_name(), ['category_name' => $category_name]);
            $category_id = $wpdb->insert_id;
        }

        // Upsert pricing
        foreach ($pricing_data as $range => $price) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$this->get_table_name()} WHERE word_count_range = %s AND category_id = %d",
                $range,
                $category_id
            ));

            if (!$exists) {
                $wpdb->insert($this->get_table_name(), [
                    'word_count_range' => sanitize_text_field($range),
                    'price' => floatval($price),
                    'category_id' => intval($category_id)
                ]);
            }
        }
    }

    // Enqueue admin scripts
    public function enqueue_admin_scripts() {
        wp_enqueue_style('blogtec-admin-style', BLOGTEC_PLUGIN_URL . 'includes/features/pricing-table/assets/css/style.css', [], BLOGTEC_PLUGIN_URL);
        wp_enqueue_script('blogtec-pricing-table', BLOGTEC_PLUGIN_URL . 'includes/features/pricing-table/assets/js/blogtec-pricing-table-admin-script.js', ['jquery'], BLOGTEC_PLUGIN_VERSION, true);
    }

    // Admin menu
    public function add_pricing_admin_menu() {
        add_menu_page(
            __('Services Pricing Table', 'blogtec-features-manager'),
            __('Services Pricing Table', 'blogtec-features-manager'),
            'manage_options',
            'blogtec-pricing-table',
            [$this, 'render_pricing_page'],
            'dashicons-editor-table',
            3
        );
    }

    // Get selected category name by ID
    public function get_selected_category_name($category_id) {
        global $wpdb;
        return $wpdb->get_var($wpdb->prepare("SELECT category_name FROM {$this->get_category_table_name()} WHERE id = %d", $category_id));
    }

    // Render pricing page
    public function render_pricing_page() {
        global $wpdb;

        // Ensure user has the necessary permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handle_form_submission();
        }

        // Fetch categories and selected category
        $categories = $wpdb->get_results("SELECT * FROM {$this->get_category_table_name()}");
        $selected_category_id = $this->get_selected_category();

        // Fetch pricing data for selected category
        $pricing_data = $this->get_pricing_data($selected_category_id);

        // Render the admin page
        $this->render_pricing_admin($categories, $selected_category_id, $pricing_data);
    }

    // Get selected category
    private function get_selected_category() {
        global $wpdb;
        return isset($_POST['selected_category']) ? intval($_POST['selected_category']) : $wpdb->get_var("SELECT id FROM {$this->get_category_table_name()} LIMIT 1");
    }

    // Get pricing data for selected category
    private function get_pricing_data($selected_category_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$this->get_table_name()}
            WHERE category_id = %d
            ORDER BY CAST(SUBSTRING_INDEX(word_count_range, '-', 1) AS UNSIGNED) ASC
        ", $selected_category_id));
    }

    // Render pricing admin HTML
    private function render_pricing_admin($categories, $selected_category_id, $pricing_data) {
        echo sprintf(
            '<div class="wrap">
                <h1>%s %s</h1>
                <form method="post">
                    %s
                    <h2>%s</h2>',
            __('Pricing Table for ', 'blogtec-features-manager'),
            esc_html($this->get_selected_category_name($selected_category_id)),
            wp_nonce_field('create_category_action', 'create_category_nonce', true, false), // true, false to return the string
            __('Manage Categories', 'blogtec-features-manager')
        );

        // Render the category dropdown
        $this->render_category_dropdown($categories, $selected_category_id);

        echo sprintf(
            '<h3>%s</h3>
                <input type="text" name="category_name" placeholder="%s" />
                <button type="submit" name="create_category" class="button-primary">%s</button>
            </form>',
            __('Add New Category', 'blogtec-features-manager'),
            __('Enter category name', 'blogtec-features-manager'),
            __('Create Category', 'blogtec-features-manager')
        );

        echo sprintf(
            '<form method="post">
                %s
                <input type="hidden" name="selected_category" value="%s" />
                <h2>%s %s</h2>',
            wp_nonce_field('save_pricing_action', 'save_pricing_nonce', true, false),
            esc_attr($selected_category_id),
            __('Manage Pricing for ', 'blogtec-features-manager'),
            esc_html($this->get_selected_category_name($selected_category_id))
        );

        // Render the pricing table
        $this->render_pricing_table($pricing_data);

        echo sprintf(
            '<p><button type="button" id="add-row" class="button-secondary">%s</button></p>
                <input type="submit" name="save_pricing" class="button-primary" value="%s" />
            </form>
        </div>',
            __('Add New Row', 'blogtec-features-manager'),
            __('Save Pricing', 'blogtec-features-manager')
        );
    }


    // Render category dropdown
    private function render_category_dropdown($categories, $selected_category_id) {
        echo '<select name="selected_category" onchange="this.form.submit()">';

        foreach ($categories as $category) {
            $selected = selected($selected_category_id, $category->id, false); // 'false' to return the string
            echo sprintf(
                '<option value="%s" %s>%s</option>',
                esc_attr($category->id),
                $selected,
                esc_html($category->category_name)
            );
        }

        echo '</select>';
    }


    // Render pricing table
    private function render_pricing_table($pricing_data) {
    ?>
    <table class="wp-list-table widefat fixed striped" id="pricing-table">
        <thead>
            <tr>
                <th><?php _e('Word Count Range', 'blogtec-features-manager'); ?></th>
                <th><?php _e('Price (€)', 'blogtec-features-manager'); ?></th>
                <th><?php _e('Actions', 'blogtec-features-manager'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pricing_data as $data) {
                // Use sprintf for cleaner formatting
                echo sprintf(
                    '<tr>
                        <td><input type="text" name="pricing[%1$s][word_count_range]" value="%2$s" /></td>
                        <td><input type="text" name="pricing[%1$s][price]" value="%3$s" /></td>
                        <td>
                            <form method="post" style="display:inline;">
                                %4$s
                                <input type="hidden" name="delete_row_id" value="%1$s">
                                <button type="submit" name="delete_row" class="button-secondary" onclick="return confirm(\'%5$s\');">
                                    %6$s
                                </button>
                            </form>
                        </td>
                    </tr>',
                    esc_attr($data->id),
                    esc_attr($data->word_count_range),
                    esc_attr($data->price),
                    wp_nonce_field('delete_row_action', 'delete_row_nonce', true, false),
                    __('Are you sure you want to delete this row?', 'blogtec-features-manager'),
                    __('Delete', 'blogtec-features-manager')
                );
            } ?>
        </tbody>
    </table>
    <?php
    }

    private function keep_selected_category() {
        if (isset($_POST['selected_category'])) {
            // Retain the selected category after form submission
            $_POST['selected_category'] = intval($_POST['selected_category']);
        }
    }

    // Handle form submissions
    private function handle_form_submission() {
        if (isset($_POST['save_pricing'])) {
            $this->process_nonce_action('save_pricing_nonce', 'save_pricing_action', [$this, 'save_pricing']);
        }
        if (isset($_POST['delete_row'])) {
            $this->process_nonce_action('delete_row_nonce', 'delete_row_action', [$this, 'delete_pricing_row']);
        }
        if (isset($_POST['create_category'])) {
            $this->process_nonce_action('create_category_nonce', 'create_category_action', [$this, 'create_category']);
        }
        if (isset($_POST['delete_category'])) {
            $this->process_nonce_action('delete_category_nonce', 'delete_category_action', [$this, 'delete_category']);
        }

        // Always keep the selected category
        $this->keep_selected_category();
    }

    // Save pricing
    private function save_pricing() {
        global $wpdb;

        // Ensure user has permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.'));
        }

        foreach ($_POST['pricing'] as $id => $values) {
            $wpdb->update($this->get_table_name(), [
                'word_count_range' => sanitize_text_field($values['word_count_range']),
                'price' => floatval($values['price'])
            ], ['id' => intval($id)], ['%s', '%f'], ['%d']);
        }
    }

    // Delete pricing row
    private function delete_pricing_row() {
        global $wpdb;

        // Ensure user has permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.'));
        }

        $wpdb->delete($this->get_table_name(), ['id' => intval($_POST['delete_row_id'])], ['%d']);
    }

    // Create new category
    private function create_category() {
        global $wpdb;

        // Ensure user has permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.'));
        }

        $category_name = sanitize_text_field($_POST['category_name']);
        if (!empty($category_name)) {
            $wpdb->insert($this->get_category_table_name(), ['category_name' => $category_name]);
            $new_category_id = $wpdb->insert_id;
            $_POST['selected_category'] = $new_category_id;
        }
    }

    // Delete category
    private function delete_category() {
        global $wpdb;

        // Ensure user has permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.'));
        }

        $category_id = intval($_POST['category_id']);
        $wpdb->delete($this->get_category_table_name(), ['id' => $category_id], ['%d']);
        $wpdb->delete($this->get_table_name(), ['category_id' => $category_id], ['%d']);
    }

    // Process nonce actions
    private function process_nonce_action($nonce, $action, $callback) {
        if (wp_verify_nonce($_POST[$nonce], $action)) {
            call_user_func($callback);
        } else {
            wp_die('Nonce verification failed.');
        }
    }

    // Deactivation cleanup
    public function deactivate() {
        global $wpdb;

        // Ensure user has permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to perform this action.'));
        }

        $wpdb->query("DROP TABLE IF EXISTS {$this->get_table_name()}");
        $wpdb->query("DROP TABLE IF EXISTS {$this->get_category_table_name()}");
    }
}
