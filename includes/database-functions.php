<?php
// database-functions.php

class DatabaseFunctions
{
    private $wpdb;

    public function __construct($wpdb) {
        $this->wpdb = $wpdb;
    }

    /**
     * Creates the images_for_sale table
     * when the plugin is activated
     */
    public function create_custom_table() {
        $table_name = $this->wpdb->prefix . 'images_for_sale';

        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            image_id bigint(20) NOT NULL,
            product_id bigint(20) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Removes the images_for_sale table
     * when the plugin is uninstalled
     */
    public static function remove_custom_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'images_for_sale';

        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    /**
     * Insert an entry into the images_for_sale table
     * @param int $image_id
     * @param int $product_id
     */
    public function insert_image_for_sale($image_id, $product_id) {
        $table_name = $this->wpdb->prefix . 'images_for_sale';

        $existing_entry = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM $table_name WHERE image_id = %d AND product_id = %d", $image_id, $product_id)
        );

        if (!$existing_entry) {
            $this->wpdb->insert(
                $table_name,
                array(
                    'image_id' => $image_id,
                    'product_id' => $product_id,
                )
            );
        }
    }

    /**
     * Delete an entry from the images_for_sale table
     * @param int $image_id
     */
    public function delete_image_for_sale($image_id) {
        $table_name = $this->wpdb->prefix . 'images_for_sale';
    
        $this->wpdb->delete(
            $table_name,
            array(
                'image_id' => $image_id
            )
        );
    }

}