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

        // First Heading Control
        $this->add_control(
            'first_heading',
            [
                'label' => __('First Heading', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Choose word count', 'blogtec-features-manager'),
                'description' => __('Provide the heading for the first range slider.', 'blogtec-features-manager'),
            ]
        );

        // Second Heading Control
        $this->add_control(
            'second_heading',
            [
                'label' => __('Second Heading', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Choose number of pieces', 'blogtec-features-manager'),
                'description' => __('Provide the heading for the second range slider.', 'blogtec-features-manager'),
            ]
        );

        // Controlled Widget ID Control
        $this->add_control(
            'controlled_widget_id',
            [
                'label' => __('Controlled Widget ID', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Enter the Controlled Widget ID of the Initial Number Display widget to control.', 'blogtec-features-manager'),
            ]
        );

        // Pricing Table Selection Control
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

        // Style Tab: Headings Style
        $this->start_controls_section(
            'heading_style_section',
            [
                'label' => __('Headings Style', 'blogtec-features-manager'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // First Heading Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'first_heading_typography',
                'label' => __('First Heading Typography', 'blogtec-features-manager'),
                'selector' => '{{WRAPPER}} .first-heading',
            ]
        );

        // First Heading Color
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

        // Second Heading Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'second_heading_typography',
                'label' => __('Second Heading Typography', 'blogtec-features-manager'),
                'selector' => '{{WRAPPER}} .second-heading',
            ]
        );

        // Second Heading Color
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

        // Style Tab: Slider Style
        $this->start_controls_section(
            'slider_style_section',
            [
                'label' => __('Slider Style', 'blogtec-features-manager'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Slider Label Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'slider_label_typography',
                'label' => __('Typography', 'blogtec-features-manager'),
                'selector' => '{{WRAPPER}} .slider-label',
            ]
        );

        // Slider Label Color
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

        // Slider Background Color
        $this->add_control(
            'slider_background_color',
            [
                'label' => __('Slider Background Color', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input[type="range"]::-webkit-slider-runnable-track' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} input[type="range"]::-moz-range-track' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} input[type="range"]::-ms-track' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // Slider Handle (Thumb) Color
        $this->add_control(
            'slider_knob_color',
            [
                'label' => __('Slider Handle Color', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} input[type="range"]::-webkit-slider-thumb' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} input[type="range"]::-moz-range-thumb' => 'background-color: {{VALUE}};',
                    '{{WRAPPER}} input[type="range"]::-ms-thumb' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    private function get_pricing_tables() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'blogtec_pricing_categories';
        $results = $wpdb->get_results("SELECT id, category_name FROM $table_name");

        $categories = [];
        if (!empty($results)) {
            foreach ($results as $category) {
                $categories[$category->id] = esc_html($category->category_name);
            }
        }

        return $categories;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $controlled_widget_id = esc_attr($settings['controlled_widget_id']);
        $pricing_table_id = intval($settings['pricing_table_id']);
        $widget_id = esc_attr($this->get_id());
        $first_heading = esc_html($settings['first_heading']);
        $second_heading = esc_html($settings['second_heading']);

        $data_rows = $this->get_pricing_table_data($pricing_table_id);
        if (empty($data_rows)) {
            echo esc_html__('No data available for the selected pricing table.', 'blogtec-features-manager');
            return;
        }

        echo sprintf(
            '<div id="blogtec-slider-%1$s" class="blogtec-slider-control">
                <div class="flex-justify">
                    <div class="first-heading">%2$s</div>
                    <div class="word_count_wrap">
                        <span class="word_count-%1$s">0</span> %3$s
                    </div>
                </div>
                <input type="range" min="0" max="%4$d" value="0" step="1" />
                <div class="slider-labels">%5$s</div>
                <div class="pieces_wrap">
                    <div class="flex-justify">
                        <div class="second-heading">%6$s</div>
                        <div class="word_count_wrap">
                            <span class="pieces_count-%1$s">1</span> %7$s
                        </div>
                    </div>
                    <input type="range" class="pieces-range" min="1" max="50" value="1" step="1" />
                    <div class="slider-labels">%8$s</div>
                </div>
            </div>',
            esc_attr($widget_id), // %1$s
            esc_html($first_heading), // %2$s
            esc_html__('words', 'blogtec-features-manager'), // %3$s
            count($data_rows) - 1, // %4$d
            $this->render_slider_labels($data_rows), // %5$s
            esc_html($second_heading), // %6$s
            esc_html__('Post(s)', 'blogtec-features-manager'), // %7$s
            $this->render_pieces_labels(), // %8$s
        );


        // Include the inline styles and JavaScript
        $this->render_styles_and_scripts($widget_id, $controlled_widget_id, $data_rows);
    }

    private function render_slider_labels($data_rows) {
        $labels = '';
        foreach ($data_rows as $index => $row) {
            $position = ($index / (count($data_rows) - 1)) * 100;
            $labels .= sprintf('<span class="slider-label" style="left: %1$d%%;">%2$d</span>', $position, $row['word_count_max']);
        }
        return $labels;
    }

    private function render_pieces_labels() {
        $pieces = '';
        $label_values = [1, 25, 50]; // Define the desired label values
        $max_value = 50; // Maximum value for the range

        foreach ($label_values as $value) {
            $position = (($value - 1) / ($max_value - 1)) * $max_value; // Calculate position as a percentage
            $pieces .= sprintf('<span class="slider-label" style="left: %1$d%%;">%2$d</span>', $position, $value);
        }

        return $pieces;
    }

    private function render_styles_and_scripts($widget_id, $controlled_widget_id, $data_rows) {
    ?>
    <style>
        .word_count_wrap {
            float: right;
            font-size: 15px;
            font-weight: 700;
        }

        .pieces_wrap {
            width: 100%;
            margin-top: 30px;
        }

        .word_count_wrap span, .pieces_wrap span {
            font-size: 15px;
            font-weight: 700;
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
        .blogtec-slider-control input {
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

        /* Tooltip styles */
        .tooltip {
            position: absolute;
            background-color: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 5px 10px;
            font-size: 14px;
            border-radius: 4px;
            white-space: nowrap;
            display: none;
            z-index: 999;
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

    <div class="tooltip" id="tooltip-<?php echo esc_attr($widget_id); ?>"></div>

    <script>
    (function($) {
        $(document).ready(function() {
            var dataRows = <?php echo json_encode(array_values($data_rows)); ?>;
            var slider = $('#blogtec-slider-<?php echo esc_js($widget_id); ?> input[type="range"]');
            var piecesSlider = $('.pieces-range');
            var display = $('#blogtec-initial-number-<?php echo esc_js($controlled_widget_id); ?>');
            var word_count_container = $('.word_count-<?php echo esc_js($widget_id); ?>');
            var piecesCountContainer = $('.pieces_count-<?php echo esc_js($widget_id); ?>');
            var tooltip = $('#tooltip-<?php echo esc_js($widget_id); ?>');

            if (display.length) {
                display.text(parseInt(dataRows[0].price));
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

            function updateTooltip(sliderType) {
                var tooltipText = '';
                var sliderThumb, position, thumbWidth, parentOffset, tooltipWidth, adjustedLeft;

                // Get the parent Elementor container
                var parentContainer = $('.elementor-element-<?php echo esc_js($widget_id); ?>.elementor-widget-blogtec_slider_control');

                if (sliderType === 'word_count') {
                    var index = slider.val();
                    var wordCount = dataRows[index].word_count_max;
                    tooltipText = wordCount + ' words';
                    sliderThumb = slider[0].getBoundingClientRect();
                    position = slider.val() / slider.attr('max');
                } else if (sliderType === 'pieces') {
                    var pieces = piecesSlider.val();
                    tooltipText = pieces + ' post(s)';
                    sliderThumb = piecesSlider[0].getBoundingClientRect();
                    position = piecesSlider.val() / piecesSlider.attr('max');
                }

                thumbWidth = sliderThumb.width || sliderThumb.right - sliderThumb.left;
                tooltipWidth = tooltip.outerWidth(); // Get the width of the tooltip
                parentOffset = parentContainer[0].getBoundingClientRect(); // Get the parent container's position

                // Calculate the left position relative to the parent container
                adjustedLeft = (sliderThumb.left - parentOffset.left + (thumbWidth * position)) - (tooltipWidth / 2);
                adjustedLeft = Math.max(0, Math.min(adjustedLeft, parentContainer.width() - tooltipWidth)); // Ensure it stays within the parent container

                // Position and show the tooltip above the slider thumb, relative to the parent container
                tooltip.css({
                    left: adjustedLeft + 20 + 'px', // Center tooltip relative to the thumb
                    top: sliderThumb.top - parentOffset.top - tooltip.outerHeight() - 10 + 'px' // Position above the thumb with some spacing
                }).text(tooltipText).fadeIn(200);
            }

            slider.on('input change', function() {
                updatePrice();
                updateTooltip('word_count'); // Pass 'word_count' when updating word count range tooltip
            });

            piecesSlider.on('input change', function() {
                updatePrice();
                updateTooltip('pieces'); // Pass 'pieces' when updating pieces range tooltip
            });

            slider.on('mouseleave', function() {
                tooltip.fadeOut(200);
            });

            piecesSlider.on('mouseleave', function() {
                tooltip.fadeOut(200);
            });
        });
        })(jQuery);
        </script>
        <?php
    }


    private function get_pricing_table_data($table_id) {
        global $wpdb;
        $pricing_table_name = $wpdb->prefix . 'blogtec_pricing';
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT word_count_range, price 
            FROM $pricing_table_name 
            WHERE category_id = %d 
            ORDER BY CAST(SUBSTRING_INDEX(word_count_range, '-', 1) AS UNSIGNED) ASC
        ", $table_id));

        $data = [];
        foreach ($results as $row) {
            $max_value = strpos($row->word_count_range, '-') !== false ? (int) explode('-', $row->word_count_range)[1] : (int) $row->word_count_range;
            $data[] = ['word_count_max' => $max_value, 'price' => $row->price];
        }

        return $data;
    }
}
