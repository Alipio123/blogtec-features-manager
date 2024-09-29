<?php
if (!defined('ABSPATH')) {
    exit;
}

class Blogtec_Admin_Setting {
     public function __construct() {

        // Admin menu hooks
        add_action('admin_menu', array($this, 'add_features_manager'));
    }

     public function add_features_manager() {
                 add_menu_page(
            __('Blogtec Features', 'blogtec-features-manager'), // Main menu page title
            __('Blogtec Features', 'blogtec-features-manager'), // Menu label in admin
            'manage_options', // Capability
            'blogtec-features-manager', // Main menu slug
            array($this, 'render_pricing_page'), // Main menu page callback function
            'dashicons-admin-generic', // Icon
            6 // Menu position
        );
     }

      public function render_pricing_page() {
        ?>
        <div class="wrap">
            <h1>Hello</h1>
        </div>
        <?php
      }
}


// Instantiate the class
new Blogtec_Admin_Setting();