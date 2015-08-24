<?php
/**
 * Plugin Name: Transdirect Shipping
 * Plugin URI: https://www.transdirect.com.au/e-commerce/woo-commerce/
 * Description: This plugin allows you to calculate shipping as per your delivery location.
 * FAQ: https://www.transdirect.com.au/e-commerce/woo-commerce/
 * Version: 1.9
 * Author: Transdirect
 * Author URI: http://transdirect.com.au/
 * Text Domain: woocommerce_transdirect
 * Domain Path: /lang
**/

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

if (!session_id()) session_start();
error_reporting(E_ALL & ~E_NOTICE);

// global $couriers_names;

// $couriers_name  = array(
//     'allied' => array(
//         'name' => 'Allied Express',
//         'services' => 'Express'
//     ),
//     'couriers_please' => array(
//         'name' => 'Couriers Please',
//         'services' => ''
//     ),  
//     'fastway' => array(
//         'name' => 'Fastway',
//         'services' => ''
//     ),  
//     'mainfreight' => array(
//         'name' => 'Mainfreight',
//         'services' => ''
//     ), 
//     'northline' => array(
//         'name' => 'Northline',
//         'services' => ''
//     ),
//     'toll' => array(
//         'name' => 'Toll',
//         'services' => 'Express'
//     ),
//     'toll_priority_sameday' => array(
//         'name' => 'Toll Priority Sameday',
//         'services' => 'Sameday'
//     ), 
//     'toll_priority_overnight' => array(
//         'name' => 'Toll Priority Overnight',
//         'services' => 'Overnight'
//     ),
//     'auspost_regular_eparcel' => array(
//         'name' => 'Auspost Regular Eparcel',
//         'services' => 'Regular'
//     ),
//     'auspost_express_eparcel' => array(
//         'name' => 'Auspost Express Eparcel',
//         'services' => 'Express'
//     ),  
//     'tnt_nine_express' => array(
//         'name' => 'TNT Nine Express',
//         'services' => 'Nine Express'
//     ),
//     'tnt_overnight_express' => array(
//         'name' => 'TNT Overnight Express',
//         'services' => 'Overnight Express'
//     ),
//     'tnt_road_express' => array(
//         'name' => 'TNT Road Express',
//         'services' => 'Road Express'
//     ),
//     'tnt_ten_express' => array(
//         'name' => 'TNT Ten Express',
//         'services' => 'Ten Express'
//     ),
//     'tnt_twelve_express' => array(
//         'name' => 'TNT Twelve Express',
//         'services' => 'Twelve Express'
//     ),
//     'direct_couriers_regular' => array(
//         'name' => 'Direct Regular Couriers',
//         'services' => 'Regular'
//     ),
//      'direct_couriers_express' => array(
//         'name' => 'Direct Express Couriers',
//         'services' => 'Express'
//     ),
//     'direct_couriers_elite' => array(
//         'name' => 'Direct Elite Couriers',
//         'services' => 'Elite'
//     )
// );

/**
 * Check if WooCommerce is active
 */
if ( in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')) ) ) {

    function woocommerce_transdirect_init() {

        if (!class_exists('WC_Transdirect_Shipping')) {

            class WC_Transdirect_Shipping extends WC_Shipping_Method {
                /**
                 * Constructor for your shipping class
                 * @access public
                 */
                public function __construct() {

                    $this->id = 'woocommerce_transdirect';
                    load_plugin_textdomain($this->id, false, dirname(plugin_basename(__FILE__)) . '/lang/');
                    $this->method_title = __('Transdirect Shipping', $this->id);
                    $this->method_description = __('', $this->id);
                    $this->wc_shipping_init();
                }

                /* Init the settings */
                function wc_shipping_init() {
                    // Let's sort arrays the right way
                    setlocale(LC_ALL, get_locale());
                    // Regions - Source: http://www.geohive.com/earth/gen_codes.aspx
                    // Load the settings API
                    // This is part of the settings API. Override the method to add your own settings
                    $this->init_form_fields();
                    // This is part of the settings API. Loads settings you previously init.
                    $this->init_settings();
                    if (isset($this->settings['title']))
                        $this->title = $this->settings['title'];
                    else
                        $this->title = '';
                    if (isset($this->settings['enabled']))
                        $this->enabled= $this->settings['enabled'];
                    else
                        $this->enabled = $this->settings['enabled'];
                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
                }

                /**
                * The Shipping Fields
                */
                function init_form_fields() {
                    $this->form_fields = array(
                        'enabled' => array(
                            'title'       => __( 'Enable', 'woocommerce' ),
                            'type'        => 'checkbox',
                            'label'       => __( 'Enable Transdirect', 'woocommerce' ),
                            'default'     => 'no'
                        ),
                        'authentication' => array(
                            'type'              => 'authentication'
                        ),
                    );
                }

                /**
                 * admin_options function.
                 *
                 * @access public
                 * @return void
                 */
                function admin_options() {
                    global $woocommerce, $wpdb;
                    $field = $this->plugin_id . $this->id . '_';


                    $shipping_details = $wpdb->get_results("SELECT `option_value` FROM " . $wpdb->prefix . "options WHERE `option_name`='" . $field . "settings'");
                    $default_values = unserialize($shipping_details[0]->option_value);

                    $ch1 = curl_init();
                    curl_setopt($ch1, CURLOPT_URL, "https://www.transdirect.com.au/api/couriers");
                    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch1, CURLOPT_HEADER, FALSE);
                    curl_setopt($ch1, CURLOPT_HTTPHEADER, array(
                      "Authorization: Basic  " . base64_encode($default_values['email'] . ":" . $default_values['password']),
                      "Content-Type: application/json"
                    ));
                    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
                    $couriers_name = curl_exec($ch1);
                    curl_close($ch1);
                    $couriers_name = json_decode($couriers_name);
                    wp_localize_script( 'your-script-name', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
                    include 'part_htm.php';
                }
                

                function process_admin_options() {
                    global $wpdb;

                    if (!empty($_POST['transdirect_hidden'])) {
                        $data = array();
                        $field    = 'woocommerce_woocommerce_transdirect_';
        
                        foreach($_POST as $k=>$val) {
                            $key = str_replace ($field,'',$k);
                            $data[$key] = $val;
                        }
            
                        $default_values_plugin = serialize($data);
                        $shipping_details_plugin = $wpdb->get_results("SELECT `option_value` FROM ". $wpdb->prefix ."options WHERE `option_name` like '%woocommerce_transdirect_settings'");

                        if(count($shipping_details_plugin) > 0) {
                            //echo "UPDATE `wp_options` SET  `option_value`='".$default_values_plugin."' WHERE `option_name`='woocommerce_woocommerce_transdirect_settings'";exit;
                            $wpdb->query("UPDATE ". $wpdb->prefix ."options SET  `option_value`='".$default_values_plugin."' WHERE `option_name` like  '%woocommerce_transdirect_settings'");
                        } else {
                            //$wpdb->query("INSERT INTO `wp_options` SET  `option_value`='".$default_values_plugin."', `option_name` like '%woocommerce_transdirect_settings'");
                            //Changed by Lee
                            $wpdb->query("INSERT INTO ". $wpdb->prefix ."options SET  `option_value`='".$default_values_plugin."', `option_name` = 'woocommerce_woocommerce_transdirect_settings'");
                        }   
                    }
                    return true;
                }
            
                /**
                * Calculate the rate - This is where you'll add your rates
                */
                public function calculate_shipping($package) {

                    global $woocommerce, $wpdb;
                    $label = '';
                
                    $shipping_details_plugin = $wpdb->get_results("SELECT `option_value` FROM " . $wpdb->prefix ."options WHERE `option_name` like '%woocommerce_transdirect_settings'");
                    $shipping_data = unserialize($shipping_details_plugin[0]->option_value);
                    
                    if ($shipping_data!='')
                        $label = __($shipping_data['title'], $this->id);
                    else
                        $label = __('Transdirect Shipping', $this->id);
                    
                    $rate = array(
                        'id'        => $this->id,
                        'label'     => (trim($label)!='' ? $label : $this->title),
                        'cost'      => $_SESSION['price'],
                        'calc_tax'  => 'per_order'
                    );
                    // Registers the rate
                    $this->rates = array();
                    $this->add_rate($rate);
                }
            } //end of class

        }// end of if
    }//end of woocommerce_transdirect_init()

    add_action('woocommerce_shipping_init', 'woocommerce_transdirect_init' );

    /**
    * Add to WooCommerce
    */
    function woocommerce_transdirect_add($methods) {
        $methods[] = 'WC_Transdirect_Shipping'; 
        return $methods;
    }
    
    //include 'transdirect-calculator.php';

    add_filter('woocommerce_shipping_methods', 'woocommerce_transdirect_add' );

    /* Hooking code */
    /* If you're reading this you must know what you're doing ;-) Greetings from sunny Portugal! */
    // add_filter('woocommerce_cart_totals_before_order_total', 'return_custom_price'); 
    add_filter('woocommerce_after_calculate_totals', 'return_custom_price');
    function return_custom_price() {  

        global $post, $woocommerce;
        if (!session_id())session_start();

        // if (WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect' && isset($_SESSION['price'])) {
        if (WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect') {
            WC()->shipping->shipping_total = $_SESSION['price'];    
            WC()->cart->total = WC()->cart->subtotal + $_SESSION['price'];
            WC()->session->shipping_total = '0';   
            WC()->session->total = WC()->session->subtotal + $_SESSION['price'];
            WC()->session->set('shipping_total', $_SESSION['price']);
        }  else {
            unset($_SESSION['price']);
        }

    }

    // function woo_add_cart_fee() {
    //     global $woocommerce;
    //     WC()->cart->add_fee(__('Shipping Cost', 'woocommerce'), $_SESSION['price']);  
    // }
    // add_action( 'woocommerce_calculate_totals', 'woo_add_cart_fee');
    

    /**
    * Add the field to the checkout
    */
    add_action( 'woocommerce_after_order_notes', 'my_custom_checkout_field' );
     
    function my_custom_checkout_field( $checkout ) {
        echo '<div id="my_custom_checkout_field" style="display:none;"><h2>' . __('Extra Information') . '</h2>';
        woocommerce_form_field( 'selected_courier', array(
            'type'          => 'text',
            'class'         => array('my-field-class form-row-wide'),
            ), $_SESSION['selected_courier']);


        woocommerce_form_field( 'booking_id', array(
            'type'          => 'text',
            'class'         => array('my-field-class form-row-wide'),
            ), $_SESSION['booking_id']);
     
        echo '</div>';     
    }

    /**
     * Save the order meta with field value
     */
    add_action( 'woocommerce_checkout_update_order_meta', 'my_custom_checkout_field_update_order_meta' );
     
    function my_custom_checkout_field_update_order_meta( $order_id ) {
        if ( ! empty( $_POST['selected_courier'] ) ) {
            update_post_meta( $order_id, 'Selected Courier', sanitize_text_field( $_POST['selected_courier'] ) );
            update_post_meta( $order_id, 'Booking ID', sanitize_text_field( $_POST['booking_id'] ) );
        }
    }
    
    /**
    * Display field value on the order edit page
    */
    add_action( 'woocommerce_admin_order_data_after_billing_address', 'my_custom_checkout_field_display_admin_order_meta', 10, 1 );
     
    function my_custom_checkout_field_display_admin_order_meta($order){
        echo '<p><strong>'.__('Selected Courier').':</strong> ' . get_post_meta( $order->id, 'Selected Courier', true ) . '</p>';
        // echo '<p><strong>'.__('Booking ID').':</strong> ' . get_post_meta( $order->id, 'Booking ID', true ) . '</p>';
    }
   
    function add_my_css_and_my_js_files() {
        wp_enqueue_script('your-script-name', plugins_url('transdirect.js', __FILE__), array('jquery'), '1.2.3', true);
        wp_localize_script( 'your-script-name', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )) );
    }

    add_action('wp_enqueue_scripts', "add_my_css_and_my_js_files");
    
    // this hook is fired if the current viewer is not logged in
    do_action('wp_ajax_nopriv_cronstarter_activation');
    
    // if logged in
    do_action('wp_ajax_cronstarter_activation');
    
    add_action('wp_ajax_nopriv_cronstarter_activation', 'cronstarter_activation');
    add_action('wp_ajax_cronstarter_activation', 'cronstarter_activation');

    // create a scheduled event (if it does not exist already)
    function cronstarter_activation() { 
        wp_clear_scheduled_hook('mycronjob');
        // let's send it    
        wp_schedule_event( time(), '5mins', 'mycronjob' );
    }
    // and make sure it's called whenever WordPress loads
    add_action('wp', 'cronstarter_activation');
    
    function cron_add_minute( $schedules ) {
        // Adds once every 5 minutes to the existing schedules.
        $schedules['5mins'] = array(
            'interval' => 5*60,
            'display' => __( 'Once Every Five Minutes' )
        );
        return $schedules;
    }   
    add_filter( 'cron_schedules', 'cron_add_minute' );

    function cronstarter_deactivate() { 
        wp_clear_scheduled_hook('mycronjob');
    } 
    register_deactivation_hook (__FILE__, 'cronstarter_deactivate');

    function my_repeat_function() {
        global $wpdb;   

        $shipping_details_plugin = $wpdb->get_results("SELECT `option_value` FROM ". $wpdb->prefix ."options WHERE `option_name` like '%woocommerce_transdirect_settings'");
        $default_values = unserialize($shipping_details_plugin[0]->option_value);
      
        $filters = array(
            'post_status' => 'any',
            'post_type' => 'shop_order',
            'posts_per_page' => 200,
            'paged' => 1,
            'orderby' =>'modified',
            'order' => 'ASC'
        );

        $loop = new WP_Query($filters);
        $api_array = '';


        $ch1 = curl_init();
        curl_setopt($ch1, CURLOPT_URL, "https://www.transdirect.com.au/api/orders/");
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch1, CURLOPT_HEADER, FALSE);
        curl_setopt($ch1, CURLOPT_HTTPHEADER, array(
          "Authorization: Basic  " . base64_encode($default_values['email'] . ":" . $default_values['password']),
          "Content-Type: application/json"
        ));
        curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch1);
        curl_close($ch1);
        $response_order = json_decode($response);

        while ( $loop->have_posts() ) {
            $loop->the_post();
            $order = new WC_Order($loop->post->ID);
            $statuses = wc_get_order_status_name($order->post->post_status);
            $sku; $sale_price;
            foreach ($order->get_items() as $key => $lineItem) {
                $product_id = $lineItem['product_id'];
                $product = new WC_Product($product_id);
                $sku = $product->get_sku();
                $sale_price = get_post_meta($product_id, '_sale_price', true);
            }


            $from_date = substr($order->post->post_modified, 0, strpos($order->post->post_modified, ' '));
            if($default_values['order_status'] == $statuses && $default_values['order_date'] >= $from_date) {
                $booking_id = get_post_meta($order->id, 'Booking ID', true);
                $selected_courier = get_post_meta($order->id, 'Selected Courier', true);
                $api_array['transdirect_order_id']  = (int) $booking_id;
                $api_array['order_id'] = $order->id;
                $api_array['goods_summary'] = $sku;
                $api_array['goods_dump'] = 'test';
                $api_array['imported_from'] = 'Woocommerce';
                $api_array['purchased_time'] = $order->order_date;
                $api_array['sale_price'] = (int) number_format($sale_price, 2);
                $api_array['selected_courier'] = strtolower($selected_courier);
                $api_array['paid_time'] = '2015-06-01T16:06:52+1000';
                $api_array['buyer_name'] = $order->billing_first_name .' '. $order->billing_last_name;
                $api_array['buyer_email'] = $order->billing_email;
                $api_array['delivery']['name'] = $order->shipping_first_name .' '. $order->shipping_last_name;
                $api_array['delivery']['email'] = $order->billing_email;
                $api_array['delivery']['phone'] = $order->billing_phone;
                $api_array['delivery']['address'] = $order->shipping_address_1;
                $api_array['last_updated'] = $order->modified_date;

                $found = false;
                $foundOrder;
                foreach ($response_order as $key => $value) {
                    if($value->order_id == $order->id) {
                        $foundOrder = $value;
                        $found = true;
                        break;
                    }
                }

                if ($found) {
                    if ($foundOrder->last_updated <= $order->modified_date) {
                        $id  = (int) $foundOrder->id;
                        $ch2 = curl_init();
                        curl_setopt($ch2, CURLOPT_URL, "https://www.transdirect.com.au/api/orders/".$id);
                        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, TRUE);
                        curl_setopt($ch2, CURLOPT_HEADER, FALSE);
                        curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "PUT");
                        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($api_array));
                        curl_setopt($ch2, CURLOPT_HTTPHEADER, array(
                          "Authorization: Basic  " . base64_encode($default_values['email'] . ":" . $default_values['password']),
                          "Content-Type: application/json"
                        ));
                        curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
                        curl_exec($ch2);
                        curl_close($ch2);
                    }
                } else {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://www.transdirect.com.au/api/orders");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                    curl_setopt($ch, CURLOPT_HEADER, FALSE);
                    curl_setopt($ch, CURLOPT_POST, TRUE);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_array));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                      "Authorization: Basic  " . base64_encode($default_values['email'] . ":" . $default_values['password']),
                      "Content-Type: application/json"  
                    ));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_exec($ch);
                    curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                }
            }
        }
    }

    // hook that function onto our scheduled event:
    add_action ('mycronjob', 'my_repeat_function'); 


    function cmp($a, $b) {

        if ($a['totals'] == $b['totals']) {
            return 0;
        }
        return $a['totals'] > $b['totals'];
    }


    do_action('wp_ajax_nopriv_myajaxdb-submit');
    // if logged in
    do_action('wp_ajax_myajaxdb-submit');
    add_action('wp_ajax_nopriv_myajaxdb-submit', 'myajaxdb_submit');
    add_action('wp_ajax_myajaxdb-submit', 'myajaxdb_submit');

    function myajaxdb_submit() {
        if (!session_id()) session_start();
        $_SESSION['price'] =  $_REQUEST['shipping_price'];

        $_SESSION['selected_courier'] = $_REQUEST['shipping_name'];
        echo '1';
        exit;
    }

    // this hook is fired if the current viewer is not logged in
    do_action('wp_ajax_nopriv_myajax-submit');
    
    // if logged in
    do_action('wp_ajax_myajax-submit');
    
    add_action('wp_ajax_nopriv_myajax-submit', 'myajax_submit');
    add_action('wp_ajax_myajax-submit', 'myajax_submit');

    function sort_cubic_weight($a,$b) {
          return $a['cubic_weight']>$b['cubic_weight'];
     }

    function myajax_submit() {
        if (!session_id()) session_start();
        global $woocommerce, $wpdb;
        unset($_SESSION['price']);
        $_SESSION['price'] = '0';

        if (!empty(WC()->session->chosen_shipping_methods[0])) {

            // Get default settings
            $shipping_details = $wpdb->get_results("SELECT `option_value` FROM " . $wpdb->prefix ."options WHERE `option_name` like '%woocommerce_transdirect_settings'");
            $default_values = unserialize($shipping_details[0]->option_value);

             if($default_values['enabled_pickup'] == 'yes') {
                $default_values['enabled_pickup'] = true;
            } else {
                $default_values['enabled_pickup'] = false;
            }

            if($default_values['enabled_delivery'] == 'yes') {
                $default_values['enabled_delivery'] = true;
            } else {
                $default_values['enabled_delivery'] = false;
            }

            $explode_from   = explode(',', $default_values['postcode']);
            $explode_to     = explode(',', $_POST['to_location']);

            $api_arr = '';
            $api_arr['sender']['country']   = 'AU';
            $api_arr['sender']['postcode']  = $explode_from[0];
            $api_arr['sender']['suburb']    = $explode_from[1];
            $api_arr['sender']['type']      = $default_values['postcode_type'];
            $api_arr['receiver']['country'] = 'AU';
            $api_arr['receiver']['postcode']= $explode_to[0];
            $api_arr['receiver']['suburb']  = $explode_to[1];
            $api_arr['receiver']['type']    = $_POST['to_type'];
            $api_arr['declared_value']      = number_format(!empty($_POST['insurance_value']) ? $_POST['insurance_value'] : 0, 2, '.', '');
            $api_arr['referrer']            = 'woocommerce';
            $api_arr['tailgate_pickup']     = $default_values['enabled_pickup'];
            $api_arr['tailgate_delivery']   = $default_values['enabled_delivery'];


            $_SESSION['postcode'] =  $api_arr['receiver']['postcode'];
            $_SESSION['to_location'] = $api_arr['receiver']['suburb'];



            $cart_content = WC()->cart->get_cart();
            $i = 0;

            $items_list  = array();
            $box_items = array();

                foreach($cart_content as $cc) {

                    $meta_values = get_post_meta($cc['data']->id);
                    if (!empty($meta_values['_weight']['0']) )
                        $api_arr['items'][$i]['weight'] = $meta_values['_weight']['0'];
                    else
                        $api_arr['items'][$i]['weight'] = $default_values['weight'];
                
                    // If less than 1
                    if (!empty($meta_values['_weight']['0']) && $api_arr['items'][$i]['weight'] < 1)
                        $api_arr['items'][$i]['weight'] = '1.0';
                
                    if (!empty($meta_values['_height']['0']))   
                        $api_arr['items'][$i]['height'] = $meta_values['_height']['0'];
                    else
                        $api_arr['items'][$i]['height'] = $default_values['height'];
                
                    if (!empty($meta_values['_width']['0']))
                        $api_arr['items'][$i]['width'] = $meta_values['_width']['0'];
                    else
                        $api_arr['items'][$i]['width'] = $default_values['width'];
                
                    if (!empty($meta_values['_length']['0']))
                        $api_arr['items'][$i]['length'] = $meta_values['_length']['0'];
                    else
                        $api_arr['items'][$i]['length'] = $default_values['length'];
                
                    $api_arr['items'][$i]['quantity'] = $cc['quantity'];
                    $api_arr['items'][$i]['description'] = 'carton';

                    if($default_values['enabled_group_box'] == 'yes')  {
                        $cubic_weight = ($api_arr['items'][$i]['length'] * $api_arr['items'][$i]['width'] * $api_arr['items'][$i]['height']) / 250;
                            
                        if($api_arr['items'][$i]['weight'] > $cubic_weight) {
                            $cubic_weight = $api_arr['items'][$i]['weight'];
                        }

                        for($x = 1; $x <= $cc['quantity']; $x++) {
                            if ($cubic_weight > $default_values['box_size']) {
                                for($x = $default_values['box_size']; $x <= $cubic_weight; $x *= 2) {
                                   array_push($items_list, array(
                                        'itemidx' => $i,
                                        'cubic_weight' => $default_values['box_size']
                                    )); 
                                }
                                $r = 0;
                                if (($r = $cubic_weight % $default_values['box_size'])) {
                                    array_push($items_list, array(
                                        'itemidx' => $i,
                                        'cubic_weight' => $r
                                    )); 
                                }
                            } else {
                                array_push($items_list, array(
                                    'itemidx' => $i,
                                    'cubic_weight' => $cubic_weight
                                ));
                            }
                        }
                    }

                    $i++;
                }

                if($default_values['enabled_group_box'] == 'yes')  {
                    // usort($items_list, 'sort_cubic_weight');
                    foreach ($items_list as $item) {
                        $newBox = true;
                        foreach ($box_items as $box) {
                            if($item['cubic_weight'] <= $default_values['box_size'] - $box['weight']) {
                                $box['weight'] += $item['cubic_weight'];
                                $box['quantity']++;
                                $newBox = false;
                                break;
                            }
                        }
                        if ($newBox) {
                            $length = $width = $height = pow(250 * $item['cubic_weight'], 1/3);

                            array_push($box_items, array(
                                'weight'        => $item['cubic_weight'],
                                'height'        => $height,
                                'width'         => $width,
                                'length'        => $length,
                                'quantity'      => 1,
                                'description'   => $api_arr['items'][$item['itemidx']]['description'],
                            ));
                        }
                    }

                    $api_arr['items'] = $box_items;
                }


            $args = array(
                'headers'   => array(
                    'Authorization' => 'Basic ' . base64_encode($default_values['email'] . ':' . $default_values['password']),
                    'Content-Type'  => 'application/json'
                ),
                // 'sslverify' => false,
                'body'      => json_encode($api_arr),
                'timeout'   => 45
            );

            $link = "https://www.transdirect.com.au/api/bookings";
            $response = wp_remote_retrieve_body(wp_remote_post($link, $args));
            $response = str_replace("true, // true if the booking has a tailgate pickup, false if not", "0,", $response);
            $response = str_replace("true // true if the booking has a tailgate delivery, false if not", "0", $response);
            $response = str_replace("''", "0", $response);
            $shipping_quotes = json_decode(str_replace("''", "0", $response));

            $_SESSION['booking_id'] = $shipping_quotes->id;
            $shipping_quotes = $shipping_quotes->quotes;

            $quotes = array();
            $total_quote = array();
            $total_price = 0;

            if ($shipping_quotes != '') {

                $handling_surcharge = 0;
                if ($default_values['surcharge'] == 'yes'){
                    if($default_values['unit'] == '%') {
                        $default_values['surcharge_price'] = $default_values['surcharge_price'] / 100;
                    }
                    $handling_surcharge = number_format($default_values['surcharge_price'], 2);
                }

                    foreach ($shipping_quotes as $k => $sq) {

                        $price_insurance_ex = $sq->price_insurance_ex;
                        $insurance_fee      = $sq->fee;
                        $total              = $sq->total;

                        $insurance_fee_html = '';
                        if ($default_values['insurance_surcharge'] == 'yes') {
                            $total_price = wc_format_decimal($total + $handling_surcharge);
                        } else {
                            $total_price = wc_format_decimal($price_insurance_ex + $handling_surcharge);
                        }

                        // if ($default_values['show_courier'] == 'yes') {
                        $courier_name = ucwords(str_replace('_', ' ', $k));
                        if (empty($default_values['courier_'.$k])) {
                            // get base
                            $delimiterPos = strpos($k, '_');
                            $base = $delimiterPos != FALSE ? substr($k, 0, $delimiterPos) : $k;
                        } else {
                            $base = $k;
                        }
                          
                        if($default_values['enabled_surcharge_'.$base] == 'yes') {
                            if($default_values['corunit_'. $base] == '%'){
                                 $default_values['courier_surcharge_'.$base] = $default_values['courier_surcharge_'.$base] / 100;
                            } 
                            $individual_handling_surcharge = number_format($default_values['courier_surcharge_'.$base], 2); 
                            $total_price += $individual_handling_surcharge;
                        }

                        $continue = false;
                        if (!empty($default_values['rename_group_'.$base])) {
                            $courier_name = ucwords($default_values['rename_group_'.$base]);
                            foreach ($total_quote as $key => $value) {
                                if ($value['courier'] == $courier_name) {
                                    if ($total_price < $value['totals']) {
                                        array_splice($total_quote, $key);
                                        break;
                                    } else {
                                        $continue = true;
                                        break;
                                    }
                                }
                            }
                        }
                        if ($continue) { continue; }
                        
                        if (!empty($default_values['courier_'.$base])) {
                            //push data in an  array to access after the loop
                            array_push($total_quote, array(
                                'courier' => $courier_name, 
                                'totals' =>  $total_price, 
                                'transit_time' => $sq->transit_time, 
                                'service_type' => $sq->service_type
                                )
                            );
                        }
                        // }
                    }//end of foreach

                    //Sort courier cheapes to expensive.
                    usort($total_quote, "cmp");

                    foreach ($total_quote as $key => $value) {
                
                    // Replace spaces to _ for reading input id in
                     $replace =  preg_replace('~([^a-zA-Z\n\r()]+)~', '_', 
                        $total_quote[$key]['courier']);

                        if ($default_values['quotes'] == 'Display all Quotes') {

                        $quotes['couriers'][$total_quote[$key]['courier']]['html'] = 
                        '<input type="radio" name="shipping_type_radio" 
                        class="shipping_type_radio" onclick="get_quote(\'' .  $replace . '\');" 
                        id="' .  $replace . '" value="' .  $replace. '" />' . 
                        '<b>' . $total_quote[$key]['courier'] . 
                        '</b> &nbsp;-&nbsp;' . get_woocommerce_currency_symbol() . 
                        '&nbsp;'. number_format($total_quote[$key]['totals'], 2) . '<br/>
                                

                        <input type="hidden" name="' .  $replace . '_price" 
                        id="' .  $replace .'_price" value="' . $total_quote[$key]['totals']. '" />

                        <input type="hidden" name="' . $total_quote[$key]['courier'] . '_transit_time" 
                        id="' .  $replace . '_transit_time" 
                        value="' . $total_quote[$key]['transit_time'] . '" />

                        <input type="hidden" name="' .  $replace . '_service_type" 
                        id="' . $replace . '_service_type" 
                        value="' . $total_quote[$key]['service_type'] . '" />';

                        } elseif ($default_values['quotes'] == 'Display Cheapest') {

                            if ($quotes['cheapest']['price'] > $total_quote[$key]['totals'] || 
                                !isset($quotes['cheapest']['price'])) {
                                
                                $quotes['cheapest']['price'] = $total_quote[$key]['totals'];
                            
                                // Initialize array to remove previous values
                                $quotes['cheapest']['couriers'] = null;
                              
                          $quotes['cheapest']['couriers'][$total_quote[$key]['courier']] =
                                '<input type="radio" name="shipping_type_radio" 
                                class="shipping_type_radio" onclick="get_quote(\'' . $replace . '\');" 
                                id="' . $replace . '" value="' . $replace . '" />' . 
                                '<b>' . $total_quote[$key]['courier'] . 
                                '</b>&nbsp;-&nbsp;' . get_woocommerce_currency_symbol() . 
                                '&nbsp;' . number_format($total_quote[$key]['totals'], 2). '<br/>


                                <input type="hidden" name="' . $replace . '_price" 
                                id="' . $replace . '_price" 
                                value="' . number_format($total_quote[$key]['totals'], 2) . '" />
                                        
                                <input type="hidden" name="' . $replace . '_transit_time" 
                                id="' . $replace . '_transit_time" 
                                value="' . $total_quote[$key]['transit_time'] . '" />

                                <input type="hidden" name="' . $replace . '_service_type" 
                                id="' . $replace . '_service_type" 
                                value="' . $total_quote[$key]['service_type'] . '" />';

                            } elseif ($quotes['cheapest']['price'] == $total_quote[$key]['totals'] || 
                                !isset($quotes['cheapest']['price'])) {

                                // var_dump($quotes['fastest']['price']);

                                $quotes['cheapest']['couriers'][$total_quote[$key]['courier']] = '<input type="radio" name="shipping_type_radio" class="shipping_type_radio" 
                                    onclick="get_quote(\'' . $replace . '\');" 
                                    id="' . $replace . '" value="' . $replace . '" />
                                    ' . '<b>' . $total_quote[$key]['courier'] . 
                                    '</b>&nbsp;-&nbsp;' . get_woocommerce_currency_symbol() . 
                                    '&nbsp;' . number_format($total_quote[$key]['totals'], 2) . '<br/>

                                        <input type="hidden" name="' . $replace . '_price" 
                                        id="' . $replace . '_price" 
                                        value="' . number_format($total_quote[$key]['totals'], 2) . '" />

                                        <input type="hidden" name="' . $replace . '_transit_time" id="' . $replace . '_transit_time" 
                                        value="' . $total_quote[$key]['transit_time'] . '" />

                                        <input type="hidden" name="' . $replace . '_service_type" 
                                        id="' . $replace . '_service_type" 
                                        value="' . $total_quote[$key]['service_type'] . '" />';
                            } //end of inner if

                        } elseif ($default_values['quotes'] == 'Display Cheapest Fastest') {
                                    
                                    $timeDay = explode(' ', $sq->transit_time);
                                    $timeNoDay = explode('-', $timeDay[0]);
                                    $fastest = $timeDay[0] == '' ? 0 : $timeNoDay[0];
                                    
                                    if ($quotes['fastest']['day'] > $fastest || 
                                        !isset($quotes['fastest']['day'])) {

                                        

                                        $quotes['fastest']['day'] = $fastest;
                                        
                                        // Initialize array to remove previous values
                                        $quotes['fastest']['couriers'] = null;
                                        $quotes['fastest']['couriers'][$total_quote[$key]['courier']] = '<input type="radio" name="shipping_type_radio" class="shipping_type_radio" 
                                            onclick="get_quote(\'' . $replace . '\');" 
                                            id="' . $replace . '" 
                                            value="' . $replace . '" /> ' . 
                                            $total_quote[$key]['courier'] . ' - ' . 
                                            get_woocommerce_currency_symbol() . 
                                            number_format($total_quote[$key]['totals'], 2) . '<br/>

                                        <input type="hidden" name="' . $replace . '_price" 
                                        id="' . $replace . '_price" 
                                        value="' . number_format($total_quote[$key]['totals'], 2) . '" />

                                        <input type="hidden" name="' . $replace . '_transit_time" 
                                        id="' . $replace . '_transit_time" 
                                        value="' . $total_quote[$key]['transit_time'] . '" />

                                        <input type="hidden" name="' . $replace . '_service_type" 
                                        id="' .$replace . '_service_type" 
                                        value="' . $total_quote[$key]['service_type'] . '" />';

                                    } elseif ($quotes['fastest']['day'] == $fastest || 
                                        !isset($quotes['fastest']['day'])) {
                                        
                                    $quotes['fastest']['couriers'][$total_quote[$key]['courier']] = 
                                    '<input type="radio" name="shipping_type_radio" class="shipping_type_radio" 
                                        onclick="get_quote(\'' . $replace . '\');" 
                                        id="' . $replace . '" 
                                        value="' . $replace . '" />
                                        ' . $total_quote[$key]['courier'] . ' - ' . 
                                        get_woocommerce_currency_symbol() . 
                                        number_format($total_quote[$key]['totals'], 2). '<br/>

                                    <input type="hidden" name="' . $replace . '_price" 
                                    id="' . $replace . '_price" 
                                    value="' .  number_format($total_quote[$key]['totals'], 2) . '" />

                                    <input type="hidden" name="' . $replace . '_transit_time" 
                                    id="' . $replace . '_transit_time" 
                                    value="' . $total_quote[$key]['transit_time'] . '" />

                                    <input type="hidden" name="' . $replace . '_service_type" 
                                    id="' . $replace . '_service_type" 
                                    value="' . $total_quote[$key]['service_type'] . '" />';
                                    }
                                }
                    }   
            } //end of IF($shipping_quotes);

            $html = '<span class="close-option" style="float:right;"><a href="javascript:void(0)" title="close" 
            onclick="document.getElementById(\'shipping_type\').style.display=\'none\';">Close</a></span>';

            if ($default_values['quotes'] == 'Display all Quotes') {
                if($quotes['couriers']){
                    foreach ($quotes['couriers'] as $key => $value) {
                        $html = $html . $value['html'];
                    }
                } else{
                    $html = "No Quotes Provided in this Location";
                }
            } elseif ($default_values['quotes'] == 'Display Cheapest') {
                if($quotes['cheapest']['couriers']) {
                    foreach ($quotes['cheapest']['couriers'] as $key => $value)
                        $html = $html . $value;
                }
            } elseif ($default_values['quotes'] == 'Display Cheapest Fastest') {
                if($quotes['fastest']['couriers']){
                    foreach ($quotes['fastest']['couriers'] as $key => $value)
                        $html = $html . $value;
                } else{
                    $html = "No Quotes Provided in this Location";
                }
            }

            if($html == '' && $default_values['fixed_error'] == 'yes') {
                $_SESSION['price'] =  $default_values['fixed_error_price'];
                //echo 'No data found';
            }

        
                header( "Content-Type: text/html" );
                echo $html;
        
        } //end of IF (!empty(WC()->session->chosen_shipping_methods[0])
        else {
            
            echo 'No Login Provided';
        }

        exit;
    
    }


    

    function plugin_test() {
        global $woocommerce, $wpdb;
        include 'transdirect-calculator.php';
    }
    
    //cart page html show hooks
    // add_action('woocommerce_before_shipping_calculator', 'plugin_test');
    add_action('woocommerce_after_cart_totals', 'plugin_test');
    add_action('woocommerce_review_order_before_payment', 'plugin_test');

    //check out page price show hooks
    // add_filter('woocommerce_checkout_order_review', 'return_custom_price');
    // add_filter('wp_ajax_woocommerce_update_order_review', 'return_custom_price');
    // add_filter('wp_ajax_nopriv_woocommerce_update_order_review', 'return_custom_price');
    
    add_filter( 'woocommerce_cart_shipping_method_full_label', 'remove_local_pickup_free_label', 10, 2 );

    function remove_local_pickup_free_label($full_label, $method) {

        global $wpdb;
        if ($method->id == 'woocommerce_transdirect') {
            
            $shipping_details_plugin = $wpdb->get_results( "SELECT `option_value` FROM " . $wpdb->prefix ."options WHERE `option_name` like '%woocommerce_transdirect_settings'");
            $shippin_data = unserialize($shipping_details_plugin[0]->option_value);

            if ($shippin_data['title'] != '')
                $label = $shippin_data['title'];
            else
                $label = 'Transdirect Shipping';

            $full_label = $label;

            return $full_label;
        } else {
            return $full_label; 
        }
    }


    /**
    * if shipping is disable
    */
    add_filter( 'woocommerce_package_rates', 'hide_shipping_when_free_is_available', 10, 2);

    function hide_shipping_when_free_is_available($rates, $package) {

        global $wpdb;

        // Only modify rates if free_shipping is present
        if (isset($rates['woocommerce_transdirect'])) {

            $shipping_details_plugin = $wpdb->get_results("SELECT `option_value` FROM " . $wpdb->prefix ."options WHERE `option_name` like '%woocommerce_transdirect_settings'");
            $shipping_data = unserialize($shipping_details_plugin[0]->option_value);
        
            // To unset a single rate/method, do the following. This example unsets flat_rate shipping
            if ($shipping_data['enabled'] != 'yes')
                unset( $rates['woocommerce_transdirect'] );
        }
        
        return $rates;
    }
    

    add_filter( 'woocommerce_add_cart_item_data', 'wdm_empty_cart', 10, 3);
    function wdm_empty_cart()
    {
        unset($_SESSION['price']);
    }

    /**
    * Process the checkout
    */
    add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');
    
    function my_custom_checkout_field_process() {
        // Check if set, if its not set add an error.
        if (!$_POST['billing_postcode'] || !is_numeric($_POST['billing_postcode']))
            wc_add_notice( __( 'Please enter a valid postcode/ZIP.' ), 'error' );
    }
}