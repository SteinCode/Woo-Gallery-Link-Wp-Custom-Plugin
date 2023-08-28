<?php
if (!defined('ABSPATH')) {
    echo 'You have been stopped from doing bad things';
    exit;
}



class ModelSettings{

    /**
     * Creates the images_for_sale table
     * when the plugin is initially activated
     */
    public function create_images_for_sale_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'woo-gallery-link-settings';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Removes the images_for_sale table
     * when the plugin is uninstalled
     */
    public function remove_custom_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'woo-gallery-link-settings';

        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

}