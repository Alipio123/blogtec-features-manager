<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Blogtec_Slider_Control_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'blogtec_slider_control';
    }

    public function get_title() {
        return __('Slider Control Widget', 'blogtec-features-manager');
    }

    public function get_icon() {
        return 'eicon-slider-push';
    }

    public function get_categories() {
        return ['blogtec-widgets'];
    }

    protected function _register_controls() {

        // Control Section for Settings
        $this->start_controls_section(
            'control_section',
            [
                'label' => __('Control Settings', 'blogtec-features-manager'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Add new control for the first heading
        $this->add_control(
            'first_heading',
            [
                'label' => __('First Heading', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Choose word count', 'blogtec-features-manager'),
                'description' => __('Provide the heading for the first range slider.', 'blogtec-features-manager'),
            ]
        );

        // Add new control for the second heading
        $this->add_control(
            'second_heading',
            [
                'label' => __('Second Heading', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Choose number of pieces', 'blogtec-features-manager'),
                'description' => __('Provide the heading for the second range slider.', 'blogtec-features-manager'),
            ]
        );

        // Control to specify the Controlled Widget ID
        $this->add_control(
            'controlled_widget_id',
            [
                'label' => __('Controlled Widget ID', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Enter the Controlled Widget ID of the Initial Number Display widget to control.', 'blogtec-features-manager'),
            ]
        );

        // Control to select the pricing table
        $this->add_control(
            'pricing_table_id',
            [
                'label' => __('Select Pricing Table', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_pricing_tables(),
                'description' => __('Select a Blogtec Pricing Table to base the data on.', 'blogtec-features-manager'),
            ]
        );

        $this->end_controls_section();

        // Add Style Tab Controls for Headings
        $this->start_controls_section(
            'heading_style_section',
            [
                'label' => __('Headings Style', 'blogtec-features-manager'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Typography control for first heading
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'first_heading_typography',
                'label' => __('First Heading Typography', 'blogtec-features-manager'),
                'selector' => '{{WRAPPER}} .first-heading',
            ]
        );

        // Color control for first heading
        $this->add_control(
            'first_heading_color',
            [
                'label' => __('First Heading Color', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .first-heading' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Typography control for second heading
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'second_heading_typography',
                'label' => __('Second Heading Typography', 'blogtec-features-manager'),
                'selector' => '{{WRAPPER}} .second-heading',
            ]
        );

        // Color control for second heading
        $this->add_control(
            'second_heading_color',
            [
                'label' => __('Second Heading Color', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .second-heading' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Add Style Tab Controls for Slider Labels
        $this->start_controls_section(
            'slider_label_style_section',
            [
                'label' => __('Slider Labels', 'blogtec-features-manager'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Typography control for slider labels
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'slider_label_typography',
                'label' => __('Typography', 'blogtec-features-manager'),
                'selector' => '{{WRAPPER}} .slider-label',
            ]
        );

        // Color control for slider labels
        $this->add_control(
            'slider_label_color',
            [
                'label' => __('Label Color', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .slider-label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    // Method to get pricing tables
    private function get_pricing_tables() {
        global $wpdb;

        // Fetch all categories from the blogtec_pricing_categories table
        $table_name = $wpdb->prefix . 'blogtec_pricing_categories';
        $results = $wpdb->get_results("SELECT id, category_name FROM $table_name");

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
        $controlled_widget_id = esc_attr($settings['controlled_widget_id']);
        $pricing_table_id = intval($settings['pricing_table_id']);
        $widget_id = $this->get_id();
        $first_heading = $settings['first_heading'];
        $second_heading = $settings['second_heading'];

        // Fetch data rows from the selected pricing table
        $data_rows = $this->get_pricing_table_data($pricing_table_id);
        if (empty($data_rows)) {
            echo esc_html__('No data available for the selected pricing table.', 'blogtec-features-manager');
            return;
        }

        // Output the slider with Word Count and Pieces inputs
        ?>
        <div id="blogtec-slider-<?php echo esc_attr($widget_id); ?>" class="blogtec-slider-control">
            <div class="flex-justify">
                <div class="first-heading"><?php echo esc_html($first_heading); ?></div>
                <div class="word_count_wrap"> 
                    <span class="word_count-<?php echo esc_attr($widget_id); ?>">0</span> 
                    <?php echo __('words', 'blogtec-features-manager'); ?>
                </div>
            </div>
            
            <input type="range" min="0" max="<?php echo esc_attr(count($data_rows) - 1); ?>" value="0" step="1" />

            <div class="slider-labels">
                <?php foreach ($data_rows as $index => $row): ?>
                    <span class="slider-label" style="left: <?php echo esc_attr(($index / (count($data_rows) - 1)) * 100); ?>%;">
                        <?php echo esc_html($row['word_count_max']); ?>
                    </span>
                <?php endforeach; ?>
            </div>

            <!-- New Input for Number of Pieces -->
            <div class="pieces_wrap">
                <div class="flex-justify">
                    <div class="second-heading"><?php echo esc_html($second_heading); ?></div>
                    <div class="word_count_wrap">
                        <span class="pieces_count-<?php echo esc_attr($widget_id); ?>">1</span> 
                        <?php echo __('Post(s)', 'blogtec-features-manager'); ?>
                    </div>
                </div>
                <input type="range" class="pieces-range" min="1" max="200" value="1" step="1" />
                <div class="slider-labels">
                    <?php for ($i = 1; $i <= 150; $i += 50): ?>
                        <span class="slider-label" style="left: <?php echo (($i - 1) / 199) * 100; ?>%;">
                            <?php echo $i; ?>
                        </span>
                    <?php endfor; ?>
                    <!-- Last label for 200 -->
                    <span class="slider-label" style="left: 100%;">200</span>
                </div>
            </div>
        </div>

        <style>
            .word_count_wrap {
                float: right;
                font-size: 12px;
            }

            .pieces_wrap {
                width: 100%;
                margin-top: 30px;
            }

            .word_count_wrap span, .pieces_wrap span {
                font-size: 12px;
            }

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
                text-align: center;
                width: 30px;
            }

            /* Ensure the slider input occupies 100% width */
            .blogtec-slider-control  input {
                width: 100%;
            }

            #blogtec-slider-<?php echo esc_attr($widget_id); ?> input[type="range"] {
                -webkit-appearance: none;
                appearance: none;
                border-radius: 100px;
            }

            /* Slider track appearance */
            #blogtec-slider-<?php echo esc_attr($widget_id); ?> input[type="range"]::-webkit-slider-runnable-track {
                border-radius: 100px;
            }

            /* Default thumb appearance */
            #blogtec-slider-<?php echo esc_attr($widget_id); ?> input[type="range"]::-webkit-slider-thumb {
                -webkit-appearance: none;
                appearance: none;
                height: 25px;
                width: 25px;
                border-radius: 50%;
                cursor: pointer;
            }

            .flex-justify {
                display: flex;
                justify-content: space-between;
                align-items: center;
                width: 100%;
            }

            .flex-justify div {
                display: inline-block;
                vertical-align: middle;
                margin: 0;
            }
        </style>

        <script>
        (function($) {
            $(document).ready(function() {
                var dataRows = <?php echo json_encode(array_values($data_rows)); ?>;
                var slider = $('#blogtec-slider-<?php echo esc_js($widget_id); ?> input[type="range"]');
                var piecesSlider = $('.pieces-range');
                var display = $('#blogtec-initial-number-<?php echo esc_js($controlled_widget_id); ?>');
                var word_count_container = $('.word_count-<?php echo esc_attr($widget_id); ?>');
                var piecesCountContainer = $('.pieces_count-<?php echo esc_attr($widget_id); ?>');

                // Initialize the display with the first price value and word count
                if (display.length) {
                    display.text(dataRows[0].price);
                    word_count_container.text(dataRows[0].word_count_max);
                }

                function updatePrice() {
                    var index = slider.val();
                    var pieces = piecesSlider.val();
                    var price = dataRows[index].price;
                    var total = price * pieces;
                    if (display.length) {
                        display.text(total);
                        word_count_container.text(dataRows[index].word_count_max);
                        piecesCountContainer.text(pieces);
                    }
                }

                slider.on('input change', updatePrice);
                piecesSlider.on('input change', updatePrice);
            });
        })(jQuery);
        </script>
        <?php
    }

    private function get_pricing_table_data($table_id) {
        global $wpdb;

        // Fetch data from the blogtec_pricing table
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
