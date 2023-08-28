<?php
// model-product-image.php

if (!defined('ABSPATH')) {
    echo 'You have been stopped from doing bad things';
    exit;
}


class ModelProductImage
{
    private $wpdb;

    public function __construct($wpdb) {
        $this->wpdb = $wpdb;
    }

    /**
     * Creates the images_for_sale table
     * when the plugin is initially activated
     */
    public function create_images_for_sale_table() {
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
    public function remove_images_for_sale_table() {

        $table_name = $this->wpdb->prefix . 'images_for_sale';

        $this->wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    /**
     * Insert an entry into the images_for_sale table
     * @param int $image_id
     * @param int $product_id
     */
    public function insert_image_for_sale($image_id, $product_id) {
        $table_name = $this->wpdb->prefix . 'images_for_sale';
        
        $this->wpdb->insert(
            $table_name,
            array(
                'image_id' => $image_id,
                'product_id' => $product_id,
            )
        );

    }

    public function update_image_for_sale($product_id, $image_id) {
        $table_name = $this->wpdb->prefix . 'images_for_sale';

        $this->wpdb->update(
            $table_name,
            array(
                'image_id' => $image_id
            ),
            array(
                'product_id' => $product_id
            )
        );
    }

    /**
     * Delete an entry from the images_for_sale table
     * @param int $product_id
     */
    public function delete_image_for_sale($product_id) {
        $table_name = $this->wpdb->prefix . 'images_for_sale';
    
        $this->wpdb->delete(
            $table_name,
            array(
                'product_id' => $product_id
            )
        );
    }
   
    /**
     * Check if an entry exists in the images_for_sale table
     * by product_id
     * @return bool
     */
    public function check_product_exists($product_id) {
        $table_name = $this->wpdb->prefix . 'images_for_sale';
        $query = $this->wpdb->prepare("SELECT * FROM $table_name WHERE product_id = %d", $product_id);
        $result = $this->wpdb->get_results($query);
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

}