<?php

/**
 * Plugin Name: Transdirect Shipping
 * Plugin URI: https://www.transdirect.com.au/e-commerce/woo-commerce/	
 * Description: This plugin allows you to calculate shipping as per your delivery location. 
 * FAQ: https://www.transdirect.com.au/e-commerce/woo-commerce/ 
 * Version: 1.0
 * Author: Transdirect
 * Author URI: http://transdirect.com.au/	
 * Text Domain: woocommerce_transdirect		
 * Domain Path: /lang
**/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



/**
 * Check if WooCommerce is active
 **/

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) 
{
	function woocommerce_transdirect_init() 
	{

		

		if ( ! class_exists( 'WC_Transdirect_Shipping' ) ) 
		{

			class WC_Transdirect_Shipping extends WC_Shipping_Method 
			{

				/**
				 * Constructor for your shipping class
				 *
				 * @access public
				 * @return void
				 */
				public function __construct() 
				{
					$this->id					= 'woocommerce_transdirect';
					load_plugin_textdomain($this->id, false, dirname(plugin_basename(__FILE__)) . '/lang/');
					$this->method_title			= __('Transdirect Shipping', $this->id);
					$this->method_description	= __('', $this->id);
					$this->wc_shipping_init();
					//$this->init_shipping_fields_per_country();
					//$this->init_shipping_fields_per_state();
					//$this->init_shipping_fields_per_postcode();
				}

				/* Init the settings */
				function wc_shipping_init() 
				{
					//Let's sort arrays the right way
					setlocale(LC_ALL, get_locale());
					//Regions - Source: http://www.geohive.com/earth/gen_codes.aspx

					// Load the settings API
					$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
					$this->init_settings(); // This is part of the settings API. Loads settings you previously init.
					$this->title				= $this->settings['title'];
					$this->enabled				= $this->settings['enabled'];

					// Save settings in admin if you have any defined
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
				}
		

				/* The Shipping Fields */
				function init_form_fields() 
				{
					$this->form_fields = array(			
						'enabled' => array(
							'title'       => __( 'Enable', 'woocommerce' ),
							'type'        => 'checkbox',
							'label'       => __( 'Enable Transdirect', 'woocommerce' ),
							'default'     => 'no'
						),
						'authentication' => array(
							'type'				=> 'authentication'
							),							
					);
					//$this->form_fields=$fields;
				}
			
			/**
			 * admin_options function.
			 *
			 * @access public
			 * @return void
			 */
			function admin_options() 
			{
				global $woocommerce,$wpdb;
				$field    = $this->plugin_id . $this->id . '_';
				
				$shipping_details = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name`='".$field."settings'");
				$defult_values = unserialize($shipping_details[0]->option_value);
				include 'part_htm.php';
			
			}
				
			 function process_admin_options() 
			 {
				global $wpdb;
				//var_dump($_POST);exit;
				if(!empty($_POST['transdirect_hidden']))
				{
					$data = array();
					$field    = 'woocommerce_woocommerce_transdirect_';
						
					foreach($_POST as $k=>$val)
					{
						$key = str_replace ($field,'',$k);
						$data[$key] = $val;
					}
					
					$defult_values_plugin = serialize($data);	
					
					$shipping_details_plugin = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name`='woocommerce_woocommerce_transdirect_settings'");
					
					if(count($shipping_details_plugin) > 0)
					{
						// echo "UPDATE `wp_options` SET  `option_value`='".$defult_values_plugin."' WHERE `option_name`='woocommerce_woocommerce_transdirect_settings'";exit;
						 $wpdb->query("UPDATE `wp_options` SET  `option_value`='".$defult_values_plugin."' WHERE `option_name`='woocommerce_woocommerce_transdirect_settings'");
					}
					else
					{
						 $wpdb->query("INSERT INTO `wp_options` SET  `option_value`='".$defult_values_plugin."', `option_name`='woocommerce_woocommerce_transdirect_settings'");
					}
					
				}
				return true;
			}	
			
			/* Calculate the rate */  

		public function calculate_shipping($package) 
		{
			// This is where you'll add your rates

			global $woocommerce,$wpdb;

			$label='';

			if(trim($package['destination']['country'])!='') 
			{

				$final_rate=false;

				//Country

				$count=$this->settings['per_country_count'];

				for($i=1; $i<=$count; $i++){

					if (is_array($this->settings['per_country_'.$i.'_country'])) {

						if (in_array(trim($package['destination']['country']), $this->settings['per_country_'.$i.'_country'])) {

							$final_rate=floatval($this->settings['per_country_'.$i.'_fee']);

							$label=$woocommerce->countries->countries[trim($package['destination']['country'])];

							break;

						}

					}

				}


				//State

				if ($final_rate===false) {

					$count=$this->settings['per_state_count'];

					for($i=1; $i<=$count; $i++){

						if (is_array($this->settings['per_state_'.$i.'_state'])) {

							if (in_array(trim($package['destination']['state']), $this->settings['per_state_'.$i.'_state'])) {

								$final_rate=floatval($this->settings['per_state_'.$i.'_fee']);

								$label=$woocommerce->countries->states[trim($package['destination']['state'])];

								

								break;

							}

						}

					}

				}

									

				
				//PostNumber
				$pcode =$package['destination']['postcode'];
				$postcount=$this->settings['per_postcode_count'];
				
				if ($final_rate===false) {
					
					for($i=1; $i<=$postcount; $i++){							
						
						$shipcode =$this->settings['per_postcode_'.$i.'_postcode'];
						
						$pcodes = explode(",",$shipcode);
						
						$p=0;
						
						foreach($pcodes as $pc) {
						
							if($pcode == $pcodes[$p]){
								
								$final_rate=floatval($this->settings['per_postcode_'.$i.'_fee']);
		
								$label=__('Transdirect Shipping', $this->id);
								
								break;						
							}
						$p++;
						}
					}
				}
				
				
				
				//Rest of the World
				if ($final_rate===false) {

					$final_rate=floatval($this->settings['fee_world']);
					$shipping_details_plugin = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name`='woocommerce_woocommerce_transdirect_settings'");
					$shippin_data = unserialize($shipping_details_plugin[0]->option_value);
					
					$final_rate=floatval($final_rate);
					if($shippin_data!='')	$label=__($shippin_data['title'], $this->id);
					else $label=__('Transdirect Shipping', $this->id);

				}

			}
			else 
			{

				$final_rate=0; //No country?
				
				

			}
			

			$rate = array(

				'id'       => $this->id,

				'label'    => (trim($label)!='' ? $label : $this->title),

				'cost'     => $final_rate,

				'calc_tax' => 'per_order'

			);

			// Register the rate

			$this->add_rate($rate);

		}

		}
	}



}

	add_action( 'woocommerce_shipping_init', 'woocommerce_transdirect_init' );



	/* Add to WooCommerce */

	function woocommerce_transdirect_add( $methods ) 
	{

		$methods[] = 'WC_Transdirect_Shipping'; 

		return $methods;

	}
	
	//include 'transdirect-calculator.php';

	add_filter( 'woocommerce_shipping_methods', 'woocommerce_transdirect_add' );
	
	
	
	/* hooking code */
	/* If you're reading this you must know what you're doing ;-) Greetings from sunny Portugal! */
	
	add_filter('woocommerce_cart_totals_before_order_total', 'tds_return_custom_price');
	
	function tds_return_custom_price() {
	    global $post, $woocommerce;
		if (!session_id())session_start();
		//var_dump(WC()->session->chosen_shipping_methods[0]);
		if(WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect')
		{
			WC()->shipping->shipping_total = $_SESSION['price'];
			WC()->cart->total = WC()->cart->subtotal + $_SESSION['price'];
			WC()->session->shipping_total = '10';
			WC()->session->total = WC()->session->subtotal + $_SESSION['price'];
			WC()->cart->add_fee( __('Shipping Cost', 'woocommerce'), $_SESSION['price'] );
			WC()->session->set( 'shipping_total"', $_SESSION['price']);
		}
		else
		{
			unset($_SESSION['price']);
		}
	}
	
	add_filter('woocommerce_checkout_update_order_meta', 'matter_ref_num_checkout_field_update_order_meta');
	
	function matter_ref_num_checkout_field_update_order_meta( $order_id ) 
	{
		global $post, $woocommerce;
		if (!session_id())session_start();
		if(WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect')
		{
			update_post_meta( $order_id, '_order_shipping', 		wc_format_decimal( $_SESSION['price'] ) );
			wc_add_order_item_meta( $item_id, 'cost', wc_format_decimal( $_SESSION['price'] ) );
			update_post_meta( $order_id, '_order_total', 			wc_format_decimal( WC()->cart->total+$_SESSION['price'], get_option( 'woocommerce_price_num_decimals' ) ) );
		}
	}
	
	add_filter('woocommerce_email_order_meta_keys', 'tds_custom_checkout_field_order_meta_keys');
	
	function tds_custom_checkout_field_order_meta_keys( $keys ) {
		global $post, $woocommerce;
		if (!session_id())session_start();
		if(WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect')
		{
			$keys['Shipping Cost'] = $_SESSION['price'];
			return $keys;
		}
	}
	
	function tds_shipping_quotes_get()
	{
		global $woocommerce,$wpdb;
		if (!session_id())session_start();
		$_SESSION['price'] = '0';
		var_dump(WC()->session);exit;
		if(!empty(WC()->session->chosen_shipping_methods[0]))
		{
			//default settings
			$shipping_details = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name`='woocommerce_woocommerce_transdirect_settings'");
			$defult_values = unserialize($shipping_details[0]->option_value);
			
			$api_arr = '';
			$explode_from = explode(',',$defult_values['postcode']);
			$explode_to = explode(',',$_REQUEST['to_location']);
			
			
			$api_arr['sender']['country'] 		= 'AU';
			$api_arr['sender']['postcode'] 		= $explode_from[0];
			$api_arr['sender']['suburb'] 		= $explode_from[1];
			$api_arr['sender']['type'] 			= $_REQUEST['to_type'];
			$api_arr['receiver']['country'] 	= 'AU';
			$api_arr['receiver']['postcode'] 	= $explode_to[0];
			$api_arr['receiver']['suburb'] 	= $explode_to[1];
			$api_arr['receiver']['type']        = $_REQUEST['to_type'];
			
			$cart_content = WC()->cart->get_cart();
			$i = 0;
			foreach($cart_content as $cc)
			{
			//var_dump($cc['quantity']);exit;
				if(!empty($cc['weight'])) $api_arr['items'][$i]['weight'] = $cc['weight'];
				else $api_arr['items'][$i]['weight'] = $defult_values['weight'];
				
				if(!empty($cc['height'])) $api_arr['items'][$i]['height'] = $cc['height'];
				else $api_arr['items'][$i]['height'] = $defult_values['height'];
				
				if(!empty($cc['width'])) $api_arr['items'][$i]['width'] = $cc['width'];
				else $api_arr['items'][$i]['width'] = $defult_values['width'];
				
				if(!empty($cc['length'])) $api_arr['items'][$i]['length'] = $cc['length'];
				else $api_arr['items'][$i]['length'] = $defult_values['length'];
				
				$api_arr['items'][$i]['quantity'] = $cc['quantity'];
				$api_arr['items'][$i]['description'] = 'carton';
				$i++;
			}
	
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://private-3eff3-transdirect.apiary-mock.com/api/bookings");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_POST, TRUE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_arr));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			$response1 = curl_exec($ch);
			curl_close($ch);
			$shipping_quotes1 = json_decode(str_replace("''","0",$response1));
			$shipping_quotes = $shipping_quotes1->quotes;
			foreach($shipping_quotes as $k=>$sq)
			{
			
			$html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" id="'.$k.'" value="'.$k.'" />'.$k.'-'.$sq->total.'-'.$sq->service_type.'-'.$sq->transit_time.'<br>
			<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$sq->total.'" />
			<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
			<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
			
			}
		echo $html;
		}
		
	}
	add_action( 'quotes_get_hook', 'tds_shipping_quotes_get');
	
	
	
	function tds_plugin_test()
	{
		global $woocommerce,$wpdb;
		include 'transdirect-calculator.php';
		
	}
	
	//cart page html show hooks
	add_action( 'woocommerce_after_cart_totals', 'tds_plugin_test');
	add_action( 'woocommerce_review_order_before_payment', 'tds_plugin_test');
	
	//check out page price show hooks
	add_filter('woocommerce_checkout_order_review', 'tds_return_custom_price');
	//add_filter('wp_ajax_woocommerce_update_order_review', 'tds_return_custom_price');
	//add_filter('wp_ajax_nopriv_woocommerce_update_order_review', 'tds_return_custom_price');
	
	add_filter( 'woocommerce_cart_shipping_method_full_label', 'remove_local_pickup_free_label', 10, 2 );
	function remove_local_pickup_free_label($full_label, $method){
		if($method->id=='woocommerce_transdirect')
		{
			$full_label = str_replace("(Free)","",$full_label);
			return $full_label;
		}
		else
		{
			return $full_label;	
		}
	}
	
	/**
	* Process the checkout
	*/
	add_action('woocommerce_checkout_process', 'tds_custom_checkout_field_process');
	
	function tds_custom_checkout_field_process() {
	// Check if set, if its not set add an error.
	if ( ! $_POST['billing_postcode'] || !is_numeric($_POST['billing_postcode']))
	wc_add_notice( __( 'Please enter a valid postcode/ZIP.' ), 'error' );
	}
	
	
	
	


}


