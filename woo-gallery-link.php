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
        add_action('save_post_product', array($this, 'product_created_updated'), 10, 3);
        add_action('before_delete_post', array($this, 'product_deleted'));

        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        register_uninstall_hook(__FILE__, array($this, 'uninstall_plugin'));
    }

    /**
     * Get the instance of db
     * @return DatabaseFunctions
     */
    public function get_db()
    {
        return $this->db;
    }

    /**
     * Handle plugin activation
     * Creates the images_for_sale table
     */
    public function activate_plugin()
    {
        $instance = new self();
        $instance->db->create_custom_table();

        $products = $this->get_wc_products();
        $image_ids = $this->get_image_ids();
        $this->init_images_for_sale($products, $image_ids);
    }

    /**
     * Handle plugin deactivation
     */
    function spectrocoin_deactivate_plugin()
    {
        deactivate_plugins(plugin_basename(__FILE__));
    }

    /**
     * Handle plugin uninstall
     * Removes the images_for_sale table
     */
    public static function uninstall_plugin()
    {
        $instance = new self();
        $instance->db->remove_custom_table();
    }

    /**
     * Plugin initialization
     */
    public function initialize_plugin()
    {   
        if (class_exists('WooCommerce')) {
            $this->load_woocommerce();
        } else {
            $this->spectrocoin_admin_notice('WooCommerce is not active or not properly loaded.');
        }
    }

    /**
     * Log admin notice
     */
    public function spectrocoin_admin_notice($message)
    {
        ?>
        <div class="notice notice-error">
            <p>
                <?php echo esc_html($message); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Database object initialization
     * @return DatabaseFunctions
     */
    public function initialize_database_functions(){
        global $wpdb;
        $db = new DatabaseFunctions($wpdb);
        return $db;
    }

    /**
     * Include WooCommerce functions
     */
    public function load_woocommerce()
    {
        if (!function_exists('wc')) {
            include_once(WP_PLUGIN_DIR . '/woocommerce/woocommerce.php');
        }
    }

    /**
     * Get ids of all images in the WP gallery
     * @return array $image_ids
     */
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

    /**
     * Get all WooCommerce products
     * @return array $products
     */
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

    /**
     * Checks if the image is in the product gallery
     * And if it is, adds it to the images_for_sale table
     * Use it on plugin initialization to add all images to the table
     * @param array $products
     * @param array $image_ids
     */
    public function init_images_for_sale($products, $image_ids)
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

    /**
     * Add image id and product id to the images_for_sale table
     * @param int $product_id
     * @param int $image_id
     */

    public function set_image_for_sale($product_id, $image_id)
    {
        $db = $this->get_db();
        $db->insert_image_for_sale($image_id, $product_id);
    }


    /**
     * Delete image id from the images_for_sale table
     * @param int $product_id
     */
    public function delete_image_for_sale($image_id)
    {
        $db = $this->get_db();
        $db->delete_image_for_sale($image_id);
    }

    /**
     * Handle product creation and update
     * @param int $product_id
     * @param WP_Post $post
     * @param bool $update
     */
    public function product_created_updated($product_id, $post, $update)
    {
        if ($post->post_type === 'product') {
            $product = wc_get_product($product_id);
            $product_main_image_id = $product->get_image_id();

            if ($product_main_image_id) {
                $this->set_image_for_sale($product_id, $product_main_image_id);
            }
        }
    }

    /**
     * Handle product deletion
     * @param int $product_id
     */
    public function product_deleted($product_id)
    {
        $product = wc_get_product($product_id);
        $product_main_image_id = $product->get_image_id();

        if ($product_main_image_id) {
            $this->delete_image_for_sale($product_main_image_id);
        }
    }
    
}

new wooGalleryLink;
