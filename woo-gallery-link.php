<?php
/*
 * Plugin Name: WooCommerce Gallery Link
 * Description: A plugin to automatically link WooCommerce product images to WP gallery images.
 * Author: Stein Ego
 * Version: 0.1
 * Text Domain: woo-gallery-link
 */

if (!defined('ABSPATH')) {
    echo 'You have been stopped from doing bad things';
    exit;
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class wooGalleryLink
{
    public function __construct()
    {
        add_action('plugins_loaded', array($this, 'initialize_plugin'));
        register_activation_hook(__FILE__, array($this, 'create_custom_table'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'remove_custom_table'));
    }

    public function initialize_plugin()
    {
        if (class_exists('WooCommerce')) {
            $this->load_woocommerce();
            $products = $this->get_wc_products();
            $image_ids = $this->get_image_ids();
            $this->get_image_ids_for_sale($products, $image_ids);
        } else {
            echo 'WooCommerce is not active or not properly loaded.';
        }
    }

    public function load_woocommerce()
    {
        if (!function_exists('wc')) {
            include_once(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php');
        }
    }


    public function get_image_ids()
    {
        $ids = get_posts(
            array(
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'post_status'    => 'inherit',
                'posts_per_page' => -1,
                'fields'         => 'ids',
            )
        );
        $image_ids = array();
        foreach ($ids as $id)
            $image_ids[] = $id;

        return $image_ids;
    }

    public function get_wc_products()
    {
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => 10,
            'orderby'        => 'date',
            'order'          => 'DESC'
        );
        $query = new WP_Query($args);
        $products = $query->get_posts();
    
        return $products;
    }

    public function get_image_ids_for_sale($products, $image_ids)
    {
        foreach ($products as $product) {
            $product_id = $product->ID;
            $product_main_image_id = get_post_thumbnail_id($product_id);
            if (in_array($product_main_image_id, $image_ids)) {
                $this->insert_image_for_sale($product_main_image_id, $product_id);
            } 
        }
    }

    //Database functions
    //TODO move database functions to separate file

    public function create_custom_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'images_for_sale';

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            image_id bigint(20) NOT NULL,
            product_id bigint(20) NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function insert_image_for_sale($image_id, $product_id) {
        global $wpdb;
    
        $table_name = $wpdb->prefix . 'images_for_sale';
    
        // Check if the entry already exists
        $existing_entry = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE image_id = %d AND product_id = %d", $image_id, $product_id)
        );
    
        if (!$existing_entry) {
            $wpdb->insert(
                $table_name,
                array(
                    'image_id' => $image_id,
                    'product_id' => $product_id,
                )
            );
        }
    }
    
    public static function remove_custom_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'images_for_sale';

        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }
}

new wooGalleryLink;
