<?php

if (!defined('ABSPATH')) {
    echo 'You have been stopped from doing bad things';
    exit;
}

require_once plugin_dir_path(__FILE__) . '/../model/model-product-image.php';


class ControllerProductImage
{

    private $modelProductImage;
    public function __construct(){
        $this->modelProductImage = $this->initialize_model_product_image_functions();

        add_action('save_post_product', array($this, 'product_created_updated'), 10, 3);
        add_action('before_delete_post', array($this, 'product_deleted'));
    }

    /**
     * Get the instance of ModelProductImage class
     * @return ModelProductImage
     */
    public function get_model_product_image()
    {
        return $this->modelProductImage;
    }

    

    /**
     * ModelProductImage object initialization
     * @return ModelProductImage
     */
    public function initialize_model_product_image_functions(){
        global $wpdb;
        $db = new ModelProductImage($wpdb);
        return $db;
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
        $db = $this->get_model_product_image();
        foreach ($products as $product) {
            $product_id = $product->ID;
            $product_main_image_id = get_post_thumbnail_id($product_id);
            if (in_array($product_main_image_id, $image_ids)) {
                if ($db->check_product_exists($product_id) == false){
                    $db->insert_image_for_sale($product_main_image_id, $product_id);
                }
            } 
        }
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
            $db = $this->get_model_product_image();
            if ($product_main_image_id) {
                if ($db->check_product_exists($product_id)) {
                    $db->update_image_for_sale($product_id, $product_main_image_id);
                } else {
                    $db->insert_image_for_sale($product_main_image_id, $product_id);
                }
            }
            else{
                $this->product_image_deleted($product_id);
            }
        }
    }

    /**
     * Handle product deletion
     * @param int $product_id
     */
    public function product_deleted($product_id)
    {
        $db = $this->get_model_product_image();
        $db->delete_image_for_sale($product_id);
    }

    /**
     * Handle product image deletion
     * @param int $product_id
     */
    public function product_image_deleted($product_id){
        $db = $this->get_model_product_image();
        $db->delete_image_for_sale($product_id);
    }

}