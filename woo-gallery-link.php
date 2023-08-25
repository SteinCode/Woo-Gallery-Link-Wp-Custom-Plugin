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

require_once plugin_dir_path(__FILE__) . 'includes/controller/controller-product-image.php';

class wooGalleryLink
{
    private $controllerProductImage;
    private $modelProductImage;
    public function __construct()
    {

        add_action('plugins_loaded', array($this, 'initialize_plugin'));

        $controllerProductImage = $this->initialize_controller_product_image();

        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        register_uninstall_hook(__FILE__, array($this, 'uninstall_plugin'));
    }

    public function initialize_controller_product_image()
    {
        $this->controllerProductImage = new ControllerProductImage();
        return $this->controllerProductImage;
    }

    public function get_controller_product_image()
    {
        return $this->controllerProductImage;
    }

    public function initialize_model_product_image()
    {
        $this->modelProductImage = new ModelProductImage();
        return $this-> modelProductImage;
    }

    public function get_model_product_image()
    {
        return $this->modelProductImage;
    }

    /**
     * Handle plugin activation
     * Creates the images_for_sale table
     */
    public function activate_plugin()
    {        
        $controller_product_image = $this->get_controller_product_image();
        
        $controller_product_image->get_model_product_image()->create_custom_table();
        
        $products = $controller_product_image->get_wc_products();

        $image_ids = $controller_product_image->get_image_ids();

        $controller_product_image->init_images_for_sale($products, $image_ids);
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
    public function uninstall_plugin()
    {
        $controller_product_image = $this->get_controller_product_image();
        
        $controller_product_image->get_model_product_image()->remove_custom_table();
    }

    /**
     * Plugin initialization
     */
    public function initialize_plugin()
    {   
        if (class_exists('WooCommerce')) {
            $this->load_woocommerce();
        } else {
            $this->display_admin_notice('WooCommerce is not active or not properly loaded.');
        }
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
     * Log admin notice
     */
    public function display_admin_notice($message)
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
     * Add plugin settings page
     */
    // public function add_settings_link($links)
    // {
    //     $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=woo-gallery-link-settings')) . '">' . esc_html__('Settings', 'woo-gallery-link') . '</a>';
    //     array_push($links, $settings_link);
    //     return $links;
    // }
    /**
     * Add plugin settings page
     */
    // public function add_plugin_settings_page()
    // {
    //     add_submenu_page(
    //         'options-general.php',
    //         __('Woo Gallery Link Settings', 'woo-gallery-link'),
    //         __('Woo Gallery Link Settings', 'woo-gallery-link'),
    //         'manage_options',
    //         'woo-gallery-link-settings',
    //         array($this, 'render_plugin_settings')
    //     );
    // }

    /**
     * Render the plugin settings page
     */
    // public function render_plugin_settings()
    // {
    //     // Include the settings file to display the settings page content
    //     include plugin_dir_path(__FILE__) . 'includes/view/view-settings.php';
    //     include plugin_dir_path(__FILE__) . 'includes/controller/controller-settings.php';
    // }


}

new wooGalleryLink;
