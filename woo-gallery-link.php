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

require_once plugin_dir_path(__FILE__) . 'includes/database-functions.php';

class wooGalleryLink
{
    private $db;
    public function __construct()
    {
        $this->db = $this->initialize_database_functions();
        add_action('plugins_loaded', array($this, 'initialize_plugin'));
        register_activation_hook(__FILE__, array(__CLASS__, 'activate_plugin'));
        register_uninstall_hook(__FILE__, array(__CLASS__, 'remove_custom_table'));
    }

    public function get_db(){
        return $this->db;
    }

    public static function activate_plugin()
    {
        $instance = new self();
        $instance->db->create_custom_table();
    }

    public static function deactivate_plugin()
    {
        $instance = new self();
        $instance->db->remove_custom_table();
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

    public function initialize_database_functions(){
        global $wpdb;
        $db = new DatabaseFunctions($wpdb);
        return $db;
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
        $db = $this->get_db();
        foreach ($products as $product) {
            $product_id = $product->ID;
            $product_main_image_id = get_post_thumbnail_id($product_id);
            if (in_array($product_main_image_id, $image_ids)) {
                $db->insert_image_for_sale($product_main_image_id, $product_id);
            } 
        }
    }
}

new wooGalleryLink;
