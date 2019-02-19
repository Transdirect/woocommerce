<?php
/**
 * Shipping Transdirect Scripts
 *
 * @author      Transdirect
 * @version     6.7
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;


/**
*
* Add Css and javascript files.
* @access public
*
*/
function td_add_my_css_and_my_js_files() {
    wp_enqueue_script('transdirect-script', TD_SHIPPING_URL.'assets/js/transdirect.js', array('jquery'), '1.2.3.4.5', true);
    wp_enqueue_script('transdirect-country-script', TD_SHIPPING_URL.'assets/js/countrySelect.js', array('jquery'), '', true);
    wp_enqueue_style('transdirect-country-css', TD_SHIPPING_URL.'assets/css/countrySelect.css');
    wp_enqueue_style('style', TD_SHIPPING_URL. 'assets/css/style.css');
    wp_localize_script( 'transdirect-script', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )) );
}

/**
*
* Hook for adding action to wp_enqueue_scripts
*
*/
add_action('wp_enqueue_scripts', "td_add_my_css_and_my_js_files");

function td_load_admin_styles() {
    wp_enqueue_style( 'style', TD_SHIPPING_URL.'assets/css/style.css');
} 

/**
*
* Hook for adding action to wp_enqueue_scripts in admin
*
*/
add_action( 'admin_enqueue_scripts', 'td_load_admin_styles' );