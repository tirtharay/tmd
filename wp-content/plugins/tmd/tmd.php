<?php
// TODO: Change the header to match your details
/**
 * Plugin Name: TMB
 * Description: A Movies data system
 * Version: 1.0
 * Author: suasecretariaremota
 * Author URI: http://www.suasecretariaremota.com
 */


class suasecretariaremota
{

    public function __construct()
    {
        define('MOVIESPATH', plugin_dir_path(__FILE__));
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdmin'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue'));

        // // Load the all short code 
        require_once MOVIESPATH . '/inc/shortcode/shortcode.php';
        new shortcode();

        add_filter('admin_init', array(&$this, 'register_fields'));
        add_action('plugin_loaded', array($this, 'ajax_loader'));

        add_action('wp_head', array($this, 'movies_loader')); // loader for all the events 
    }

    public function enqueueAdmin()
    {
    }


    public function enqueue()
    {
        wp_enqueue_script('movies-fronted', plugins_url('assets/js/fronted.js', __FILE__), array('jquery'), time(), true);
        wp_enqueue_script('owl.carousel.min', plugins_url('assets/js/owl.carousel.min.js', __FILE__), array('jquery'), '1.0', true);
        wp_enqueue_style('movies-fronted', plugins_url('assets/css/fronted.css', __FILE__), null, '1.0');
        wp_enqueue_style('owl.carousel.min', plugins_url('assets/css/owl.carousel.min.css', __FILE__), null, '1.0');
        wp_localize_script('movies-fronted', 'movieConfig', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }

    public function ajax_loader()
    {
        // Ajax Hendler
        require_once MOVIESPATH . '/inc/ajax/functions.php'; // ajax functions 
        require_once MOVIESPATH . '/inc/ajax/ajax.php'; // ajax hooks


    }

    public function register_fields()
    {
        register_setting('general', 'movies_apikey', 'esc_attr');
        add_settings_field('fav_color', '<label for="movies_apikey">' . __('API KEY', 'movies_apikey') . '</label>', array(&$this, 'fields_html'), 'general');
    }
    public function fields_html()
    {
        $value = get_option('movies_apikey', '');
        echo '<input style="width: 50%;" type="text" id="movies_apikey" name="movies_apikey" value="' . $value . '" />';
    }

    public function movies_loader()
    {
        echo '<div class="movies-container">
                <div class="wrapper">
                    <div class="circle circle8 c81"></div>
                    <div class="circle circle8 c82"></div>
                    <div class="circle circle8 c83"></div>
                    <div class="circle circle8 c84"></div>
                    <div class="circle circle8 c85"></div>
                </div>
            </div>';
    }
}

new suasecretariaremota();
