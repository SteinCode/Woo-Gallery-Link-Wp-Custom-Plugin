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
                    settings_fields('woo_gallery_link_settings_group'); // Correct group name
                    do_settings_sections('woo-gallery-link-settings');
                    
                ?>
                <h3>Select Pages</h3>
                <?php
                    $pages = get_pages();
                    $selected_pages = get_option('selected_pages', array());
                    foreach ($pages as $page) {
                        $page_id = $page->ID;
                        $page_title = $page->post_title;
                        $checked = in_array($page_id, $selected_pages) ? 'checked="checked"' : '';
                        echo '<label><input type="checkbox" name="selected_pages[]" value="' . $page_id . '" ' . $checked . '> ' . $page_title . '</label><br>';
                    }
                    submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting(
            'woo_gallery_link_settings_group',  // Settings group name
            'selected_pages',                   // Option name
            array($this, 'sanitize_selected_pages') // Sanitization callback
        );
    }

    public function sanitize_selected_pages($input) {
        $sanitized_input = array();
        if (is_array($input)) {
            foreach ($input as $page_id) {
                $sanitized_input[] = absint($page_id); // Ensure it's an integer
            }
        }
        return $sanitized_input;
    }
}
