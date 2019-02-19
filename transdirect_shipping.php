<?php

/**
 * Plugin Name: Transdirect Shipping
 * Plugin URI: https://www.transdirect.com.au/e-commerce/woo-commerce/
 * Description: This plugin allows you to calculate shipping as per your delivery location.
 * FAQ: https://www.transdirect.com.au/e-commerce/woo-commerce/
 * Version: 6.7
 * Author: Transdirect
 * Author URI: https://transdirect.com.au/
 * Text Domain: woocommerce_transdirect
 * Domain Path: /lang
**/

if (!defined('ABSPATH')) exit; //Exit if accessed directly

if (!session_id()) session_start();

/*
*
* Condition to check if WooCommerce is active
*
*/
if ( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {
    
    /**
    *
    * Initialize transdirect plugin
    *
    */
    function woocommerce_transdirect_init() {

        if ( !class_exists('WC_Transdirect_Shipping') ) {
            /**
            *
            * Overrides shpping class method for transdirect shipping.
            *
            * @class       WC_Transdirect_Shipping
            * @package     WooCommerce/Classes
            * @category    Class
            *
            */
            class WC_Transdirect_Shipping extends WC_Shipping_Method {
                public $tax_status   = '';
                /**
                *
                * Constructor for your shipping class
                * @access public
                *
                */
                public function __construct() {

                    $this->id = 'woocommerce_transdirect';
                    load_plugin_textdomain($this->id, false, dirname(plugin_basename(__FILE__)) . '/lang/');
                    $this->method_title = __('Transdirect Shipping', $this->id);
                    $this->method_description = __('', $this->id);
                    $this->wc_shipping_init();
                }

                /**
                *
                * Inigtializes shipping and load the settings API
                * @access public
                *
                */

                function wc_shipping_init() {
                    // Let's sort arrays the right way
                    setlocale(LC_ALL, get_locale());

                    // This is part of the settings API. Override the method to add your own settings
                    $this->init_form_fields();

                    // This is part of the settings API. Loads settings you previously init.
                    $this->init_settings();

                    if (isset($this->settings['title'])) {
                        $this->title = $this->settings['title'];
                    }
                    else {
                        $this->title = '';
                    }
                    if (isset($this->settings['enabled'])) {
                        $this->enabled= $this->settings['enabled'];
                    }
                    else {
                        $this->enabled = $this->settings['enabled'];
                    }

                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
                }

                /**
                *
                * Initialize shipping form fields.
                * @access public
                *
                */
                function init_form_fields() {
                    $this->form_fields = array(
                        'enabled' => array(
                            'title'       => __( 'Enable', 'woocommerce' ),
                            'type'        => 'checkbox',
                            'label'       => __( 'Enable Transdirect', 'woocommerce' ),
                            'default'     => 'no'
                        ),
                        'authentication'  => array(
                            'type'              => 'authentication'
                        ),
                    );
                }

                /**
                *  Set up for admin transdirect setting options.
                *
                * @access public
                * @return void
                *
                */
                function admin_options() {
                    global $woocommerce, $wpdb;
                    $field = $this->plugin_id . $this->id . '_';

                    $shipping_details = $wpdb->get_results("SELECT `option_value` FROM " . $wpdb->prefix . "options WHERE `option_name`='" . $field . "settings'");
                    $default_values = unserialize($shipping_details[0]->option_value);
                    include 'templates/part_htm.php';
                }

                /**
                *
                * Process admin transdirect setting options in database.
                * @access public
                * @return boolean
                *
                */
                function process_admin_options() {
                    global $wpdb;
                    if (!empty($_POST['transdirect_hidden'])) {

                        $data = array();
                        $field    = 'woocommerce_woocommerce_transdirect_';

                        foreach($_POST as $k => $val) {
                            $key = str_replace ($field,'',$k);
                            $data[$key] = $val;
                        }

                        $default_values_plugin = serialize($data);
                        $shipping_details_plugin = $wpdb->get_results("SELECT `option_value` FROM ". $wpdb->prefix ."options WHERE `option_name` like '%woocommerce_transdirect_settings'");

                        if(count($shipping_details_plugin) > 0) {
                            $wpdb->query("UPDATE ". $wpdb->prefix ."options SET  `option_value`='".$default_values_plugin."' WHERE `option_name` like  '%woocommerce_transdirect_settings'");
                        } else {
                            //Changed by Lee
                            $wpdb->query("INSERT INTO ". $wpdb->prefix ."options SET  `option_value`='".$default_values_plugin."', `option_name` = 'woocommerce_woocommerce_transdirect_settings'");
                        }
                    }
                    return true;
                }

                /**
                *
                * Calculate the rate - This is where you'll add your rates
                * @access public
                *
                */
                public function calculate_shipping($package = Array()) {
                    global $woocommerce, $wpdb;

                    $shipping_details_plugin = $wpdb->get_results("SELECT `option_value` FROM " . $wpdb->prefix ."options WHERE `option_name` like '%woocommerce_transdirect_settings'");
                    $shipping_data = unserialize($shipping_details_plugin[0]->option_value);
                    $getTitle = td_getApiDetails();
                    if($getTitle->mode != 'no_display_mode') {
                        if ($getTitle->shipping_title !=''){
                            $label = __($getTitle->shipping_title, $this->id);
                            if(isset($_SESSION['free_shipping']) && $_SESSION['free_shipping'] != '') {
                                    $label = $label. " - Free Shipping";
                            }
                        }
                        else{
                           $label = __('Transdirect Shipping', $this->id);
                        }
                        
                        $rate = array(
                            'id'        => $this->id,
                            'label'     => $label,
                            'cost'      => $_SESSION['price'],
                            'taxes'     => '',
                            'calc_tax'  => 'per_order'
                        );
                                            
                        // Registers the rate
                        $this->rates = array();
                        $this->add_rate($rate);
                    }
                }
            } //end of class
        }// end of if
    }//end of woocommerce_transdirect_init()

    /**
    *
    * Hook for adding action for woocommerce_shipping_init
    *
    */
    add_action('woocommerce_shipping_init', 'woocommerce_transdirect_init' );
    add_filter( 'https_local_ssl_verify', '__return_false' );
    add_filter( 'https_ssl_verify', '__return_false' );

    /**
    *
    * Add Transdirect method.
    * @access public
    * @return method name.
    *
    */
    function td_woocommerce_transdirect_add($methods) {
        $methods[] = 'WC_Transdirect_Shipping';
        return $methods;
    }
    
    /**
    *
    * Hook for adding filter for woocommerce_shipping_methods
    *
    */
    add_filter('woocommerce_shipping_methods', 'td_woocommerce_transdirect_add' );

    /**
    * Setup plugin constants
    *
    */
    function td_setup_constants() {
        define( 'TD_SHIPPING_DIR', plugin_dir_path( __FILE__ ) );  // Plugin path
        define( 'TD_SHIPPING_URL', plugin_dir_url( __FILE__ ) );  // Plugin URL
    }

    function transdirect_shipping_load() {
        global $wpdb;
        $shipping_details = $wpdb->get_results("SELECT `option_value` FROM " . $wpdb->prefix . "options WHERE `option_name` like '%woocommerce_transdirect_settings'");
        $default_values = unserialize($shipping_details[0]->option_value);
        $default_values['requesting_site'] = get_site_url();
        
        if(!isset($default_values['setting']) && isset($default_values['trans_title'])) {
            if(isset($default_values['api_key']) && !empty($default_values['api_key'])){
                $headers = array(
                                'Api-Key' => $default_values['api_key'],
                                'Content-Type'  => 'application/json'
                            );
            }
            else if(isset($default_values['email']) && !empty($default_values['email'])) {
                $headers = array(
                                'Authorization' => 'Basic ' . base64_encode($default_values['email'] . ':' . $default_values['password']),
                                'Content-Type'  => 'application/json'
                            );
            }
            
            $args = array(
                        'headers'   => $headers,
                        'method'    => 'POST',
                        'body'      => json_encode($default_values),
                        'timeout'   => 45
                    );

            $link = "https://www.transdirect.com.au/api/bookings/v4/set_api_settings";

            $response = wp_remote_retrieve_body(wp_remote_get($link, $args));
            $response = json_decode($response, true);
            $default_values['setting'] = 1;
            
            if(isset($response['API-Key']) && $response['API-Key'] != ''){
                $default_values['api_key'] = $response['API-Key']; 
            }
            $data = serialize($default_values);
            $qry = $wpdb->query("UPDATE ". $wpdb->prefix ."options SET  `option_value`='".$data."' WHERE `option_name` like  '%woocommerce_transdirect_settings'");
        }

        td_setup_constants();
        require_once("includes/order_sync.php");
        require_once("includes/product_sync.php");
        require_once TD_SHIPPING_DIR . 'includes/scripts.php';
    }

    // Hook fired when plugin loaded
    add_action( 'plugins_loaded', 'transdirect_shipping_load' );

    /**
    *
    * Hook add filter woocommerce_cart_shipping_method_full_label
    *
    */
   add_filter( 'woocommerce_cart_shipping_method_full_label', 'td_remove_local_pickup_free_label', 10, 2 );

    /**
    *
    * Remove free local pick up.
    * @access public
    * @return label of shipping (string)
    *
    */
    function td_remove_local_pickup_free_label($full_label, $method) {
        global $wpdb;
        if ($method->id == 'woocommerce_transdirect') {

            $shipping_details_plugin = $wpdb->get_results( "SELECT `option_value` FROM " . $wpdb->prefix ."options WHERE `option_name` like '%woocommerce_transdirect_settings'");
            $shippin_data = unserialize($shipping_details_plugin[0]->option_value);
            $getTitle = td_getApiDetails();
            
            if ($getTitle->shipping_title != ''){
                if(isset($_SESSION['price']) && !empty($_SESSION['price']))
                    $label = $getTitle->shipping_title.': <strong>'.get_woocommerce_currency_symbol().''.number_format($_SESSION['price'], 2).'</strong>';
                else
                    $label = $getTitle->shipping_title;
            }
            else{
                if(isset($_SESSION['price']) && !empty($_SESSION['price']))
                    $label = 'Transdirect Shipping: <strong>'.get_woocommerce_currency_symbol().''.number_format($_SESSION['price'], 2).'</strong>';
                else
                    $label = 'Transdirect Shipping';
            }
            $full_label = $label;
            return $full_label;
        } else {
           return $full_label;
        }
    }

    /**
    *
    * Hook for adding action for woocommerce_after_order_notes
    *
    */
    add_action( 'woocommerce_after_order_notes', 'td_my_custom_checkout_field' );

    /**
    *
    * Add Booking Id, Selected courier for custom checkout field.
    * @access public
    *
    */
    function td_my_custom_checkout_field( $checkout ) {
        global $post;
        echo '<div id="my_custom_checkout_field" style="display:none;"><h2>' . __('Extra Information') . '</h2>';
        woocommerce_form_field( 'selected_courier', array(
            'type'          => 'text',
            'class'         => array('my-field-class', 'update_totals_on_change'),
            ), $_SESSION['selected_courier']);

        woocommerce_form_field( 'booking_id', array(
            'type'          => 'text',
            'class'         => array('my-field-class form-row-wide', 'update_totals_on_change'),
            ), $_SESSION['booking_id']);
        woocommerce_form_field( 'base_courier', array(
            'type'          => 'text',
            'class'         => array('my-field-class form-row-wide'),

            ), $_SESSION['base_courier']);
        echo '</div>';
    }

    /**
    *
    * Hook for adding action for woocommerce_checkout_update_order_meta
    *
    */
    add_action( 'woocommerce_checkout_update_order_meta', 'td_my_custom_checkout_field_update_order_meta' );

    /**
    *
    * Add Booking Id, Selected courier for order details.
    * @access public
    *
    */
    function td_my_custom_checkout_field_update_order_meta( $order_id ) {
        $order            = new WC_Order($order_id);
        if($order->get_shipping_method() == td_getApiDetails()->shipping_title) {
            update_post_meta( $order_id, 'Selected Courier', sanitize_text_field( $_SESSION['selected_courier'] ) );
            update_post_meta( $order_id, 'Booking ID', sanitize_text_field( $_SESSION['booking_id'] ) );
            update_post_meta( $order_id, 'Base Courier', sanitize_text_field( $_SESSION['base_courier'] ) );
        }
    }

    /**
    *
    * Hook for adding action for woocommerce_admin_order_data_after_billing_address
    *
    */
    add_action( 'woocommerce_admin_order_data_after_billing_address', 'td_my_custom_checkout_field_display_admin_order_meta', 10, 1 );

    /**
    *
    * Add Selected Courier to display in order details.
    * @access public
    *
    */
    function td_my_custom_checkout_field_display_admin_order_meta($order){
        echo '<p><strong>'.__('Selected Courier').':</strong> ' . get_post_meta( $order->id, 'Selected Courier', true ) . '</p>';
    }

    /**
    *
    * Hook add action cart page html show hooks
    *
    */
    add_action('woocommerce_after_cart_totals', 'td_plugin_test');
    add_action('woocommerce_after_checkout_billing_form', 'td_plugin_test' );
    

    /**
    *
    * Display transdirect calculator.
    * @access public
    *
    */
    function td_plugin_test() {
        global $woocommerce, $wpdb;
        include 'templates/transdirect-calculator.php';
    }

    function td_request_method_headers($apiKey, $bodyVal, $methodVal) {
        $args = array();
        if($methodVal == "POST") {
            $args = array(
                'headers'   => array(
                    'Api-Key' => $apiKey,
                    'Content-Type'  => 'application/json'
                ),
                'method'    => $methodVal,
                'body'      => json_encode($bodyVal),
                'timeout'   => 45
            );
        } else {
            $args = array(
                'headers'   => array(
                    'Api-Key' => $apiKey,
                    'Content-Type'  => 'application/json'
                ),
                'method'    => $methodVal,
                'timeout'   => 45
            );
        }
        return $args;
    }

    // unset all td sessions
    function unset_td_session() {
        if (!session_id())session_start();
        unset($_SESSION['price']);
        unset($_SESSION['selected_courier']);
        unset($_SESSION['booking_id']);
        unset($_SESSION['base_courier']);
        unset($_SESSION['free_shipping']);
        unset($_SESSION['applied_gst']);
    }
    

    /**
    *
    * Hook is fired when test api key button click.
    *
    */
    do_action('wp_ajax_nopriv_check_api_key_details');
    do_action('wp_ajax_check_api_key_details');
    add_action('wp_ajax_nopriv_check_api_key_details', 'check_api_key_details');
    add_action('wp_ajax_check_api_key_details', 'check_api_key_details');

    // validate api key in td system
    function check_api_key_details(){
        $apiKey   = $_POST['apiKey'];
        $api_arr = ['test_api_key' => true];
        $args = td_request_method_headers($apiKey, $api_arr, 'POST');
        $link = "https://www.transdirect.com.au/api/bookings/v4/test_api_key_settings";
        $response = wp_remote_retrieve_body(wp_remote_get($link, $args));
        echo $response;
        exit();
    }

    // return api key
    function td_get_auth_api_key() {
        global $wpdb;
        $shipping_details_plugin = $wpdb->get_results("SELECT `option_value` FROM ". $wpdb->prefix ."options WHERE `option_name` like '%woocommerce_transdirect_settings'");
        $default_values = unserialize($shipping_details_plugin[0]->option_value);

        $apiKey = $default_values['api_key'];
        return $apiKey;
    }

    // Return api details save in td system
    function td_getApiDetails($init = false) {
        if($init) {
            $apiKey    = td_get_auth_api_key();
            $api_array = '';
            $args      = td_request_method_headers($apiKey, $api_array, 'GET');
            $link      = "https://www.transdirect.com.au/api/bookings/v4/api_details";
            $response  = wp_remote_retrieve_body(wp_remote_post($link, $args));
            $response  = json_decode($response);
            if( true === get_transient('td_api_response')) {
                delete_transient('td_api_response');
            }
            set_transient('td_api_response', $response, 0);
            return $response;
        } else {
            return get_transient('td_api_response');
        }
    }

    // Return sync settings save in td system
    function td_getSyncSettingsDetails($init = false) {
        if($init) {
            $apiKey    = td_get_auth_api_key();
            $api_array = '';
            $args      = td_request_method_headers($apiKey, $api_array, 'GET');
            $link      = "https://www.transdirect.com.au/api/bookings/sync";
            $response  = wp_remote_retrieve_body(wp_remote_post($link, $args));
            $response  = json_decode($response);
            if( true === get_transient('td_sync_api_response')) {
                delete_transient('td_sync_api_response');
            }
            set_transient('td_sync_api_response', $response, 0);
            return $response;
        } else {
            return get_transient('td_sync_api_response');
        }
    }

    /**
    *
    * Hook for adding filter for woocommerce_after_calculate_totals
    *
    */

    add_filter('woocommerce_after_calculate_totals', 'td_return_custom_price');

    /**
    *
    * Returns the custom price to cart total.
    * @access public
    *
    */
    function td_return_custom_price() {
        global $post, $woocommerce;
        if (!session_id())session_start();
        if (WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect') {
            if (!isset($_SESSION['price'])) {
                $_SESSION['price'] =  $_REQUEST['shipping_price'];
            }
            WC()->shipping->shipping_total = $_SESSION['price'];
            $extraFees = 0;
            if(!empty(WC()->cart->get_fee_total())) {
                if(!empty(WC()->cart->get_fee_taxes())) {
                    $fee_taxes = 0;
                    foreach (WC()->cart->get_fee_taxes() as $fee_tax) {
                       $fee_taxes = $fee_taxes + $fee_tax;
                    }
                }
                $fees = !empty($fee_taxes) ? (WC()->cart->get_fee_total() + $fee_taxes) : WC()->cart->get_fee_total();
                $extraFees = $extraFees + $fees;  
            }
            WC()->cart->total = WC()->cart->subtotal + $_SESSION['price'] + $extraFees;
            WC()->session->shipping_total  = '0';
            WC()->session->total = WC()->session->subtotal + $_SESSION['price'] + $extraFees;
            WC()->session->set('shipping_total', $_SESSION['price']);
            if(!empty($woocommerce->cart->applied_coupons)) {
                WC()->cart->total = WC()->cart->total - $woocommerce->cart->discount_cart;
                WC()->session->total = WC()->session->total - $woocommerce->cart->discount_cart;
            }
        }
    }

    /*
    * Filter apply after courier selected.
    *
    * Calculate shipping cost from selected courier.
    * @access public
    *
    */
    add_filter( 'woocommerce_cart_shipping_packages', 'td_calculate_woocommerce_cart_shipping_cost' );

    function td_calculate_woocommerce_cart_shipping_cost( $packages ) {
        global $wpdb;
        // Reset the packages
        $packages = array();
        $shipping_details_plugin = $wpdb->get_results("SELECT `option_value` FROM " . $wpdb->prefix ."options WHERE `option_name` like '%woocommerce_transdirect_settings'");
        $shipping_data = unserialize($shipping_details_plugin[0]->option_value);
        $selected_shipping_method = WC()->session->get( 'chosen_shipping_methods' );

        if($shipping_data['enabled'] == 'yes' && ($selected_shipping_method[0] == 'woocommerce_transdirect') && isset($_SESSION['price'])) {
            $packages[] = array(
                'contents'        => WC()->cart->get_cart(),
                'contents_cost'   => $_SESSION['price'],
                'applied_coupons' => WC()->cart->applied_coupons,
                'destination'     => array(
                    'country'     => WC()->customer->get_shipping_country(),
                    'state'       => WC()->customer->get_shipping_state(),
                    'postcode'    => WC()->customer->get_shipping_postcode(),
                    'city'        => WC()->customer->get_shipping_city(),
                    'address'     => WC()->customer->get_shipping_address(),
                    'address_2'   => WC()->customer->get_shipping_address_2()
                )
            );
        } else {
            $packages[] = array(
                'contents'        => WC()->cart->get_cart(),
                'applied_coupons' => WC()->cart->applied_coupons,
                'destination'     => array(
                    'country'     => WC()->customer->get_shipping_country(),
                    'state'       => WC()->customer->get_shipping_state(),
                    'postcode'    => WC()->customer->get_shipping_postcode(),
                    'city'        => WC()->customer->get_shipping_city(),
                    'address'     => WC()->customer->get_shipping_address(),
                    'address_2'   => WC()->customer->get_shipping_address_2()
                )
            );
        }
        return $packages;
    }

    /**
    *
    * Hook action fired when user select courier from coureir list.
    *
    */
    do_action('wp_ajax_nopriv_myajaxdb-submit');
    do_action('wp_ajax_myajaxdb-submit');
    add_action('wp_ajax_nopriv_myajaxdb-submit', 'myajaxdb_submit');
    add_action('wp_ajax_myajaxdb-submit', 'myajaxdb_submit');

    /**
    *
    * Set price and courier after submiting get quote.
    * @access public
    *
    */
    function myajaxdb_submit() {
        global $wpdb;
        $wpdb->query("DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_cp_quote_%') OR `option_name` LIKE ('_transient_timeout_cp_quote_%') OR `option_name` LIKE ('_transient_wc_ship_%')" );
        if (!session_id()) session_start();
        $_SESSION['price']             =  $_REQUEST['shipping_price'];
        $_SESSION['selected_courier']  = $_REQUEST['shipping_name'];
        $_SESSION['base_courier']      = $_REQUEST['shipping_base'];
        $_SESSION['currency'] = get_woocommerce_currency_symbol();
        $extraFees = 0;
        if(!empty(WC()->cart->get_fee_total())) {
            if(!empty(WC()->cart->get_fee_taxes())) {
                $fee_taxes = 0;
                foreach (WC()->cart->get_fee_taxes() as $fee_tax) {
                   $fee_taxes = $fee_taxes + $fee_tax;
                }
            }
            $fees = !empty($fee_taxes) ? (WC()->cart->get_fee_total() + $fee_taxes) : WC()->cart->get_fee_total();
            $extraFees = $extraFees + $fees;  
        }
        WC()->shipping->shipping_total = $_SESSION['price'];
        WC()->cart->total              = WC()->cart->subtotal + $_SESSION['price'] + $extraFees;
        WC()->session->shipping_total  = $_SESSION['price'];
        WC()->session->total           = WC()->session->subtotal + $_SESSION['price'] + $extraFees;
        WC()->session->set('shipping_total', $_SESSION['price']);

        if (WC()->cart->tax_display_cart == 'incl' && !isset($_SESSION['free_shipping'])) {
            if(get_option('woocommerce_tax_total_display', true) == 'itemized') {
                if(count(WC()->cart->get_taxes()) > 1) {
                    $tax = WC()->cart->get_taxes();
                    $_SESSION['applied_gst'] = reset($tax) + number_format( $_REQUEST['shiping_applied_gst'], 2).'<|>incl';
                } else {
                    $_SESSION['applied_gst'] = WC()->cart->tax_total + number_format( $_REQUEST['shiping_applied_gst'], 2).'<|>incl';
                }
            } else {
                $_SESSION['applied_gst'] = WC()->cart->tax_total + number_format( $_REQUEST['shiping_applied_gst'], 2).'<|>incl';
            }
        }
        WC()->cart->calculate_totals();
        WC()->session->set('chosen_shipping_methods', array( 'woocommerce_transdirect' ) );
        $location              = explode(',', $_REQUEST['location']);
        $resp                  = array();
        $resp['courier_price'] = number_format($_REQUEST['shipping_price'], 2);
        $resp['total']         = WC()->cart->subtotal + number_format($_SESSION['price'], 2) + $extraFees;
        $resp['currency']      = get_woocommerce_currency_symbol();
        $resp['postcode']      = $location[0];
        $resp['suburl']        = $location[1];
        $resp['shipping_name'] = str_replace('_', ' ', $_REQUEST['shipping_name']);
        if(WC()->cart->tax_display_cart == 'incl' && !isset($_SESSION['free_shipping']))
            $resp['total_gst']     = $_SESSION['applied_gst'];
        echo json_encode($resp);
        exit;
    }

    /**
    *
    * Hook  is fired when event submit is called.
    *
    */
    do_action('wp_ajax_nopriv_myajax-submit');
    do_action('wp_ajax_myajax-submit');
    add_action('wp_ajax_nopriv_myajax-submit', 'myajax_submit');
    add_action('wp_ajax_myajax-submit', 'myajax_submit');

    /**
    *
    * Get quote and create a booking.
    * @access public
    *
    */
    function myajax_submit() {
        if (!session_id()) session_start();
        global $woocommerce, $wpdb;
        unset_td_session();
        $_SESSION['price'] = '0';
        if (!empty(WC()->session->chosen_shipping_methods[0])) {
            require_once("includes/quotes.php");
            $getQuotes = new Quotes();
            $html      = $getQuotes->td_get_quote();
            header( "Content-Type: text/html" );
            echo $html;
        }
        else {
            echo 'Please check transdirect settings.';
        }
        exit;
    }

    /**
    *
    * Hook action fired when user select courier from coureir list.
    *
    */
    do_action('wp_ajax_nopriv_myajaxdb-submit-new');
    do_action('wp_ajax_myajaxdb-submit-new');
    add_action('wp_ajax_nopriv_myajaxdb-submit-new', 'myajaxdb_submit_new');
    add_action('wp_ajax_myajaxdb-submit-new', 'myajaxdb_submit_new');

    /**
    *
    * Set price and courier after submiting get quote.
    * @access public
    *
    */
    function myajaxdb_submit_new() {
        global $wpdb;
        $wpdb->query("DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_cp_quote_%') OR `option_name` LIKE ('_transient_timeout_cp_quote_%') OR `option_name` LIKE ('_transient_wc_ship_%')" );
        if (!session_id()) session_start();
        $_SESSION['price']             =  $_REQUEST['shipping_price'];
        WC()->shipping->shipping_total = '';
        WC()->cart->total              = WC()->cart->subtotal - $_SESSION['price'];
        WC()->session->shipping_total  = '';
        WC()->session->total           = WC()->session->subtotal - $_SESSION['price'];
        WC()->session->set('shipping_total', '');
        $a = WC()->cart->calculate_totals();
        //WC()->session->set('chosen_shipping_methods', array( '' ) );
        $_SESSION['price'] = '';
        unset($_SESSION['price']);
        $resp['total'] = WC()->session->total;
        echo json_encode($resp);
        exit;
    }

    /**
    *
    * Hook add action before process checkout.
    *
    */
    add_action('woocommerce_before_checkout_process', 'td_custom_process_before_checkout');

    /**
    *
    * Add error message in processing checkout for not selecting Quote.
    * @access public
    *
    */
    function td_custom_process_before_checkout() {
       if(WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect'){
            if(empty($_SESSION['price']) && empty($_SESSION['selected_courier'])){
                wc_add_notice( __('Please select a Shipping Quote.' ), 'error' );
            }
        }
    }

    /**
    *
    * Hook add action to process checkout.
    *
    */
    add_action('woocommerce_checkout_process', 'td_my_custom_checkout_field_process');

    /**
    *
    * Add error message in processing checkout.
    * @access public
    *
    */
    function td_my_custom_checkout_field_process() {
        // Check if set, if its not set add an error.
        if (!$_POST['billing_postcode'] || !is_numeric($_POST['billing_postcode']))
            wc_add_notice( __( 'Please enter a valid postcode/ZIP.' ), 'error' );
    }

    add_action('woocommerce_thankyou', 'td_custom_process_order', 10, 1);
    function td_custom_process_order($order_id) {
        unset_td_session();
        if(get_post_meta($order_id,'synced', true) == '') {
            add_post_meta($order_id,'synced','0');
        }
    }

    /**
    *
    * Hook add filter to unset session when new item added to cart.
    *
    */
    add_filter( 'woocommerce_add_to_cart', 'td_wdm_empty_cart', 10, 3);
    
    function td_wdm_empty_cart() {
        unset_td_session();
    }

    /**
    *
    * Hook add filter to unset session when item removed from cart.
    *
    */
    add_action( 'woocommerce_cart_item_removed', 'td_reset_quote_after_item_remove' );

    function td_reset_quote_after_item_remove($cart_item_key) {
        unset_td_session();
    }

    /**
    *
    * Hook add filter to unset session when item updated in cart.
    *
    */
    add_action('woocommerce_cart_updated', 'td_cart_update');

    function td_cart_update(){
        if(isset($_POST['update_cart'])){
            unset_td_session();
        }
    }

    /**
    *
    * Set up time interval for cron job schedules.
    * @access public
    *
    */
    function td_cron_add_minute( $schedules ) {
        $schedules['5mins'] = array(
            'interval' => 5 * 60,
            'display' => __( 'Once Every Five Minutes' )
        );
        $schedules['24hours'] = array(
            'interval' => 24 * 60 * 60,
            'display' => __( 'Once In A Day' )
        );
        return $schedules;
    }


    /**
    *
    * Hook add action to make cron set up time add in schedules.
    *
    */
    add_filter( 'cron_schedules', 'td_cron_add_minute' );

    /**
    *
    * Hook add action to make cron work in background when wordpress is load.
    *
    */
    add_action('admin_init', 'td_cronstarter_activation');
    add_action('wp', 'td_cronstarter_activation');

    /**
    *
    * This will start the and activate cron job every 5 minutes.
    * @access public
    *
    */
    function td_cronstarter_activation() {
        td_getApiDetails(true);
        if (!get_transient( 'timeout_for_30_min' )) {
            set_transient( 'timeout_for_30_min', 'cron_activation_timeout', 0.5 * HOUR_IN_SECONDS );
            $startCron = new order_sync();
            $startCron->td_start_cron();
            $startProductCron = new product_sync();
            $startProductCron->td_start_product_cron();
        }
    }

    /*
    * Add synced flag to all order's in wp
    */
    register_activation_hook( __FILE__, 'td_run_at_activation' );

    function td_run_at_activation(){
        $filters = array(
            'post_status' => 'any',
            'post_type' => 'shop_order',
            'posts_per_page' => -1
        );
        $i=0;
        $loop = new WP_Query($filters);
        while ($loop->have_posts() ) {
            $loop->the_post();
            $order = new WC_Order($loop->post->ID);
            $orderId = get_post_meta($order->id,'synced', true);
            if($orderId == '') {
               add_post_meta($order->id,'synced','0');
            }
        }
    }

    /**
    *
    * Hook add action that function onto our scheduled event.
    *
    */
    add_action ('mycronjob', 'td_my_repeat_function');

    /**
    *
    * Set up process when running the cron job.
    * @access public
    *
    */
    function td_my_repeat_function() {
        $order_sync = new order_sync();
        $order_sync->td_create_order();
    }

    /**
    *
    * Hook add action to deactivate cron job.
    *
    */
    register_deactivation_hook (__FILE__, 'td_cronstarter_deactivate');

    /**
    *
    * Deactivate running cron job.
    * @access public
    *
    */
    function td_cronstarter_deactivate() {
        wp_clear_scheduled_hook('mycronjob');
        wp_clear_scheduled_hook('myProductSyncCronjob');
    }

    /**
    *
    * Call update api when order updated.
    * @access public
    *
    */
    add_action('save_post','td_save_post_callback');

    function td_save_post_callback($post_id, $post = null, $update = null){
        $order_sync = new order_sync();
        $order_sync->td_update_order($post_id);
        // $startProductCron = new product_sync();
        // $startProductCron->sync_updated_product($post_id);
    }

    /**
    *
    * Call update api when product updated.
    * @access public
    *
    */
    add_action( 'woocommerce_update_product', 'action_woocommerce_update_product', 10, 1 ); 
 
    function action_woocommerce_update_product( $product_get_id ) { 
        $startProductCron = new product_sync();
        $startProductCron->sync_updated_product($product_get_id);
    }; 
             

    add_action('myProductSyncCronjob','td_repeat_product_sync_function');

    /**
    *
    * Set up process when running the cron job.
    * @access public
    *
    */
    function td_repeat_product_sync_function() {
        $startProductCron = new product_sync();
        $startProductCron->sync_all_product();
    }

}