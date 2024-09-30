<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Blogtec_Slider_Control_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'blogtec_slider_control';
    }

    public function get_title() {
        return __('Slider Control Widget', 'blogtec');
    }

    public function get_icon() {
        return 'eicon-slider-push';
    }

    public function get_categories() {
        return ['blogtec-category'];
    }

    protected function _register_controls() {

        $this->start_controls_section(
            'control_section',
            [
                'label' => __('Control Settings', 'blogtec'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Control to specify the Controlled Widget ID
        $this->add_control(
            'controlled_widget_id',
            [
                'label' => __('Controlled Widget ID', 'blogtec'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Enter the Controlled Widget ID of the Initial Number Display widget to control.', 'blogtec'),
            ]
        );

        // Control to select the pricing table
        $this->add_control(
            'pricing_table_id',
            [
                'label' => __('Select Pricing Table', 'blogtec'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_pricing_tables(),
                'description' => __('Select a Blogtec Pricing Table to base the data on.', 'blogtec'),
            ]
        );

        $this->end_controls_section();
    }

    private function get_pricing_tables() {
        global $wpdb;

        // Fetch all categories from the blogtec_pricing_categories table
        $category_table_name = $wpdb->prefix . 'blogtec_pricing_categories';
        $results = $wpdb->get_results("SELECT id, category_name FROM $category_table_name");

        // Prepare an array to store category IDs and names
        $categories = [];
        if (!empty($results)) {
            foreach ($results as $category) {
                $categories[$category->id] = $category->category_name;
            }
        }

        return $categories;
    }


    protected function render() {
    $settings = $this->get_settings_for_display();
    $controlled_widget_id = $settings['controlled_widget_id'];
    $pricing_table_id = $settings['pricing_table_id'];
    $widget_id = $this->get_id();

    // Fetch data rows from the selected pricing table
    $data_rows = $this->get_pricing_table_data($pricing_table_id);
    if (empty($data_rows)) {
        echo __('No data available for the selected pricing table.', 'blogtec');
        return;
    }

    // Output the slider with Word Count labels
    ?>
    <div id="blogtec-slider-<?php echo esc_attr($widget_id); ?>" class="blogtec-slider-control">
        <input type="range" min="0" max="<?php echo count($data_rows) - 1; ?>" value="0" step="1" />
        <div class="slider-labels">
            <?php foreach ($data_rows as $index => $row): ?>
                <span class="slider-label" style="left: <?php echo ($index / (count($data_rows) - 1)) * 100; ?>%;">
                    <?php echo esc_html($row['word_count_max']); ?> words
                </span>
            <?php endforeach; ?>
        </div>
    </div>

    <style>
        #blogtec-slider-<?php echo esc_attr($widget_id); ?> {
            position: relative;
            margin-top: 20px;
        }
        #blogtec-slider-<?php echo esc_attr($widget_id); ?> .slider-labels {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        #blogtec-slider-<?php echo esc_attr($widget_id); ?> .slider-label {
            font-size: 12px;
            text-align: center;
            width: 50px;
            margin-left: -25px; /* Half of the width to center align */
        }

        #blogtec-slider-<?php echo esc_attr($widget_id); ?> input {
            width: 100%;
        }
    </style>

    <script>
    (function($) {
        $(document).ready(function() {
            var dataRows = <?php echo json_encode(array_values($data_rows)); ?>;
            var slider = $('#blogtec-slider-<?php echo esc_js($widget_id); ?> input[type="range"]');
            var display = $('#blogtec-initial-number-<?php echo esc_js($controlled_widget_id); ?>');

            // Initialize the display with the first price value
            if (display.length) {
                display.text(dataRows[0].price);  // Display the initial price value
            }

            slider.on('input change', function() {
                var index = $(this).val();
                var value = dataRows[index].price;  // Get the price corresponding to the word count range
                if (display.length) {
                    display.text(value);  // Update the initial widget with the price
                }
            });
        });
    })(jQuery);
    </script>
    <?php
}


private function get_pricing_table_data($table_id) {
    global $wpdb;

    // Fetch data from the blogtec_pricing table where category_id matches the selected category (table_id)
    $pricing_table_name = $wpdb->prefix . 'blogtec_pricing';
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT word_count_range, price 
        FROM $pricing_table_name 
        WHERE category_id = %d 
        ORDER BY CAST(SUBSTRING_INDEX(word_count_range, '-', 1) AS UNSIGNED) ASC
    ", $table_id));

    // Prepare an array to store the data
    $data = [];
    if (!empty($results)) {
        foreach ($results as $row) {
            // Extract the maximum number after the hyphen (if the range contains "-")
            if (strpos($row->word_count_range, '-') !== false) {
                $max_value = (int) explode('-', $row->word_count_range)[1];
            } else {
                // If no hyphen, treat the word_count_range as a single number
                $max_value = (int) $row->word_count_range;
            }

            // Add the max word count and price to the data array
            $data[] = [
                'word_count_max' => $max_value,
                'price' => $row->price
            ];
        }
    }

    return $data;
}


}
