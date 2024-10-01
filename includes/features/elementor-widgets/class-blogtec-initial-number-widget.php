<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Blogtec_Initial_Number_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'blogtec_initial_number';
    }

    public function get_title() {
        return __('Custom Number Range', 'blogtec-features-manager');
    }

    public function get_icon() {
        return 'eicon-number-field';
    }

    public function get_categories() {
        return ['blogtec-widgets'];
    }

    protected function _register_controls() {

        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'blogtec-features-manager'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'controlled_widget_id',
            [
                'label' => __('Controlled Widget ID', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'description' => __('Set a unique ID for this widget to be controlled by the Slider Control Widget.', 'blogtec-features-manager'),
            ]
        );

        $this->add_control(
            'initial_number',
            [
                'label' => __('Initial Number', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 100,
            ]
        );

        $this->end_controls_section();

        // Style Tab: Typography, Colors, Border, etc.
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'blogtec-features-manager'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'label' => __('Typography', 'blogtec-features-manager'),
                'selector' => '{{WRAPPER}} .blogtec-initial-number',
            ]
        );

        // Text Color
        $this->add_control(
            'text_color',
            [
                'label' => __('Text Color', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .blogtec-initial-number' => 'color: {{VALUE}}',
                ],
            ]
        );

        // Background Color
        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background',
                'label' => __('Background', 'blogtec-features-manager'),
                'types' => [ 'classic', 'gradient' ],
                'selector' => '{{WRAPPER}} .blogtec-initial-number',
            ]
        );

        // Border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border',
                'label' => __('Border', 'blogtec-features-manager'),
                'selector' => '{{WRAPPER}} .blogtec-initial-number',
            ]
        );

        // Border Radius
        $this->add_control(
            'border_radius',
            [
                'label' => __('Border Radius', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .blogtec-initial-number' => 'border-radius: {{SIZE}}{{UNIT}}',
                ],
            ]
        );

        // Padding
        $this->add_responsive_control(
            'padding',
            [
                'label' => __('Padding', 'blogtec-features-manager'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'selectors' => [
                    '{{WRAPPER}} .blogtec-initial-number' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $number = intval($settings['initial_number']); // Sanitize number as an integer
        $controlled_widget_id = !empty($settings['controlled_widget_id']) ? esc_attr($settings['controlled_widget_id']) : esc_attr($this->get_id());

        // Use sprintf for better string handling
        echo sprintf(
            '<div id="blogtec-initial-number-%s" class="blogtec-initial-number">%s</div>',
            esc_attr($controlled_widget_id),
            esc_html($number)
        );
    }
}
