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
// error_reporting(E_ALL);

require_once plugin_dir_path(__FILE__) . 'includes/controller/controller-product-image.php';
require_once plugin_dir_path(__FILE__) . 'includes/controller/controller-settings.php';

class wooGalleryLink
{
    //Controllers
    private $controllerProductImage;
    private $controllerSettings;

    public function __construct()
    {

        include_once plugin_dir_path(__FILE__) . 'includes/view/view-settings.php';

        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));

        add_action('plugins_loaded', array($this, 'initialize_plugin'));

        $controllerProductImage = $this->initialize_controller_product_image();
        $controllerSettings = $this->initialize_controller_settings();

        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        register_uninstall_hook(__FILE__, array($this, 'uninstall_plugin'));

        add_action('wp_enqueue_scripts', array($this, 'enqueue_custom_styles'));

        $settings = new ViewSettings();

        add_action('admin_menu', array($settings, 'add_settings_page'));
        add_action('admin_init', array($settings, 'register_settings'));
    }

    public function initialize_controller_product_image()
    {
        $this->controllerProductImage = new ControllerProductImage();
        return $this->controllerProductImage;
    }

    public function initialize_controller_settings()
    {
        $this->controllerSettings = new ControllerSettings();
        return $this->controllerSettings;
    }

    public function get_controller_product_image()
    {
        return $this->controllerProductImage;
    }

    public function get_controller_settings()
    {
        return $this->controllerSettings;
    }

    public function enqueue_custom_styles()
    {
    wp_enqueue_style('buy-bubbles', plugin_dir_url(__FILE__) . 'assets/css/buy-bubbles.css');
    }

    /**
     * Handle plugin activation
     * Creates the images_for_sale table
     */
    public function activate_plugin()
    {        
        $controller_product_image = $this->get_controller_product_image();
        
        $controller_product_image->get_model_product_image()->create_images_for_sale_table();
        
        $products = $controller_product_image->get_wc_products();

        $image_ids = $controller_product_image->get_image_ids();

        $controller_product_image->init_images_for_sale($products, $image_ids);
    }

    /**
     * Handle plugin deactivation
     */
    function deactivate_plugin()
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
        
        $controller_product_image->get_model_product_image()->remove_images_for_sale_table();
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
    public function add_settings_link($links)
    {
        $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=woo-gallery-link-settings')) . '">' . esc_html__('Settings', 'woo-gallery-link') . '</a>';
        array_push($links, $settings_link);
        return $links;
    }

}

new wooGalleryLink;
