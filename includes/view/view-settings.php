<?php

// view-settings.php

if (!defined('ABSPATH')) {
    echo 'You have been stopped from doing bad things';
    exit;
}

class ViewSettings {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function add_settings_page() {
        add_options_page(
            'Woo Gallery Link Settings',      // Page Title
            'Woo Gallery Link',               // Menu Title
            'manage_options',                 // Capability
            'woo-gallery-link-settings',      // Menu Slug
            array($this, 'render_settings_page') // Callback
        );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h2>Woo Gallery Link Settings</h2>
            <form method="post" action="options.php">
                <?php
                    settings_fields('woo_gallery_link_settings_group');
                    do_settings_sections('woo-gallery-link-settings');
                    submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        add_settings_section(
            'woo_gallery_general_section',    // ID
            'General Settings',               // Title
            array($this, 'general_section_callback'), // Callback
            'woo-gallery-link-settings'       // Page
        );

        add_settings_field(
            'woo_gallery_enable_feature',     // ID
            'Enable Feature',                 // Title
            array($this, 'enable_feature_callback'), // Callback
            'woo-gallery-link-settings',      // Page
            'woo_gallery_general_section'     // Section
        );

        register_setting(
            'woo_gallery_link_settings_group', // Option group
            'woo_gallery_enable_feature'       // Option name
        );
    }

    public function general_section_callback() {
        echo 'General settings description here.';
    }

    public function enable_feature_callback() {
        $enabled = get_option('woo_gallery_enable_feature');
        ?>
        <input type="checkbox" name="woo_gallery_enable_feature" value="1" <?php checked(1, $enabled); ?>>
        <?php
    }
}
