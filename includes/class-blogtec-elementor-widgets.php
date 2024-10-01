<?php
if (!defined('ABSPATH')) {
    exit;
}

class Blogtec_Elementor_Widgets {

    public function __construct() {
        add_action('elementor/widgets/widgets_registered', array($this, 'register_widgets'));
    }

    // Register the Elementor widgets
    public function register_widgets() {
        if (class_exists('Blogtec_Initial_Number_Widget')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Blogtec_Initial_Number_Widget());
        }
        if (class_exists('Blogtec_Slider_Control_Widget')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Blogtec_Slider_Control_Widget());
        }
    }
}
