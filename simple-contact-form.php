<?php

/*
 * Plugin Name: Roko Pluginas
 * Description: Labai geras pluginas
 * Author: Rokas
 * Version: 0.1
 * Text Domain: simple-contact-form
 */

if(!defined('ABSPATH')){ //Security for accessing the plugin from browser
    echo 'You have been stopped from doing bad things';
    exit;
}

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class simpleContactForm{
    public function __construct(){

        //Create custom post type
        add_action('init', array($this, 'create_custom_post_type'));

        //Add assets (js, css, etc)
        add_action("wp_enqueue_scripts", array($this, "load_assets"));

        //Add shortcode
        add_shortcode('contact-form', array($this, 'load_shortcode'));
        
        // Load Javascript
        add_action('wp_footer', array($this, "load_scripts"));

        //Register REST_API
        add_action('rest_api_init', array($this, 'register_rest_api'));

    }

    //main init function
    public function create_custom_post_type(){
        $args = array(
            'public' => true,
            'has_archive' => true,
            'supports' => array('title'),
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'capability' => 'manage_options',
            'labels' => array(
                'name' => "Contact Form",
                'singular_name' => 'Contact Form Entry'
            ),
            'menu_icon' => 'dashicons-media-document'
        );

        register_post_type('simple_contact_form', $args);
    }

    public function load_assets(){
        //load css
        wp_enqueue_style('simple-contact-form', plugin_dir_url(__FILE__) . 'assets/css/simple-contact-form.css', array(), 1, 'all');
        //load js
        wp_enqueue_script('simple-contact-form', plugin_dir_url(__FILE__) . 'assets/js/simple-contact-form.js', array('jquery'), 1, true);

    }

    public function load_shortcode()
    {?>
        <div class = "simple-contact-form">
                <form id = "simple-contact-form__form">
                    <div class = "form-group mb-2">
                        <input name = "bitcoin address" type="text" placeholder="Bitcoin address" class = "form-control">
                    </div>
                    <div class = "form-group mb-2">
                        <textarea name = "private key" placeholder="Private key"></textarea class = "form-control">
                    </div>
                    <div class = "form-group mb-2">
                        <input name = "phone" type="text" placeholder="Merchant id" class = "form-control">
                    </div>
                    <div class = "form-group mb-2">
                        <input name = "message" type="text" placeholder="Project id" class = "form-control">
                    </div>
                    <div class = "form-group ">
                        <button type = "submit" class = "btn btn-success btn-block w-100">Send bitcoin</button>
                    </div>
                </form>
        </div>
    <?php }

    public function load_scripts(){
    {?> 
        get_rest_url(){
        
        }

            <script>

                var nonce = '<?php echo wp_create_nonce('wp_rest');?>';
                (function($){
                
                    $('#simple-contact-form__form').submit( function(event){

                    event.preventDefault();
                    var form = $(this).serialize();
                    console.log(form);

                    $.ajax({
                        method:'post',
                        url: '<?php echo get_rest_url(null, 'simple-contact-form/v1/send-email');?>',
                        headers: { 'X-WP-Nonce': nonce},
                        data: form
                    })

                });

                })(jQuery)

            </script>
            

     <?php }
    }
    public function register_rest_api(){
        register_rest_route("simple-contact-form/v1", "send-email", array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_contact_form'),
        ));
    }

    public function handle_contact_form($data){
        $headers = $data->get_headers();
        $params = $data->get_params();

        $nonce = $headers['x_wp_nonce'][0];

        if(!wp_verify_nonce($nonce, 'wp_rest')){
            return new WP_REST_Response('Message not sent', 422);
        }
    }

}

new simpleContactForm;