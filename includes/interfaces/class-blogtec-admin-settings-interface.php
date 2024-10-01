<?php

if (!defined('ABSPATH')) {
    exit;
}

interface Blogtec_Admin_Settings_Interface {
    public function add_settings_fields();
}