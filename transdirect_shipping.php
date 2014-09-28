<?php

/**

 * Plugin Name: Transdirect Shipping

 * Plugin URI: https://www.transdirect.com.au/e-commerce/woo-commerce/	

 * Description: This plugin allows you to calculate shipping as per your delivery location. 
 
 * FAQ: https://www.transdirect.com.au/e-commerce/woo-commerce/ 

 * Version: 2.7

 * Author: Transdirect

 * Author URI: http://transdirect.com.au/	

 * Text Domain: woocommerce_transdirect		

 * Domain Path: /lang

**/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!session_id())session_start();

error_reporting(E_ALL & ~E_NOTICE);

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



					if(isset($this->settings['title']))$this->title	= $this->settings['title'];
					else $this->title				= '';

					if(isset($this->settings['enabled']))$this->enabled= $this->settings['enabled'];
					else $this->enabled				= $this->settings['enabled'];



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
					
					$shipping_details_plugin = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name` like '%woocommerce_transdirect_settings'");
					
					if(count($shipping_details_plugin) > 0)
					{
						// echo "UPDATE `wp_options` SET  `option_value`='".$defult_values_plugin."' WHERE `option_name`='woocommerce_woocommerce_transdirect_settings'";exit;
						 $wpdb->query("UPDATE `wp_options` SET  `option_value`='".$defult_values_plugin."' WHERE `option_name` like  '%woocommerce_transdirect_settings'");
					}
					else
					{
						 $wpdb->query("INSERT INTO `wp_options` SET  `option_value`='".$defult_values_plugin."', `option_name` like '%woocommerce_transdirect_settings'");
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
				
				$shipping_details_plugin = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name` like '%woocommerce_transdirect_settings'");
				$shippin_data = unserialize($shipping_details_plugin[0]->option_value);
						
				$final_rate=0;
				if($shippin_data!='')	$label=__($shippin_data['title'], $this->id);
				else $label=__('Transdirect Shipping', $this->id);
				
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
	
	add_filter('woocommerce_cart_totals_before_order_total', 'return_custom_price'); 
	
	function return_custom_price() {    
	    global $post, $woocommerce;
		//if (!session_id())session_start();
		
		if(WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect' && isset($_SESSION['price']))
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
	
	add_filter('woocommerce_email_order_meta_keys', 'my_custom_checkout_field_order_meta_keys');
	
	function my_custom_checkout_field_order_meta_keys( $keys ) {
		global $post, $woocommerce;
		if (!session_id())session_start();
		if(WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect')
		{
			$keys['Shipping Cost'] = $_SESSION['price'];
			return $keys;
		}
	}
	
	function shipping_quotes_get()
	{
		global $woocommerce,$wpdb;
		if (!session_id())session_start();
		$_SESSION['price'] = '0';
		var_dump(WC()->session);exit;
		if(!empty(WC()->session->chosen_shipping_methods[0]))
		{
			//default settings
			$shipping_details = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name` like '%woocommerce_transdirect_settings'");
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
	add_action( 'quotes_get_hook', 'shipping_quotes_get');
	
	
	function add_my_css_and_my_js_files()
	{
		wp_enqueue_script('your-script-name', plugins_url('transdirect.js', __FILE__), array('jquery'), '1.2.3', true);
		wp_localize_script( 'your-script-name', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )) );
		
		
	}
	add_action('wp_enqueue_scripts', "add_my_css_and_my_js_files");
	
	// this hook is fired if the current viewer is not logged in
	do_action( 'wp_ajax_nopriv_myajax-submit');
	
	// if logged in:
	do_action( 'wp_ajax_myajax-submit');
	
	add_action( 'wp_ajax_nopriv_myajax-submit', 'myajax_submit' );
	add_action( 'wp_ajax_myajax-submit', 'myajax_submit' );
	//submit code
	/*function add_my_css_and_my_js_files1()
	{
		wp_enqueue_script('your-script-name', plugins_url('transdirect.js', __FILE__), array('jquery'), '1.2.3', true);
		wp_localize_script( 'your-script-name', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )) );
		
		
	}*/
	//add_action('wp_enqueue_scripts', "add_my_css_and_my_js_files");
	
	// this hook is fired if the current viewer is not logged in
	do_action( 'wp_ajax_nopriv_myajaxdb-submit');
	
	// if logged in:
	do_action( 'wp_ajax_myajaxdb-submit');
	
	add_action( 'wp_ajax_nopriv_myajaxdb-submit', 'myajaxdb_submit' );
	add_action( 'wp_ajax_myajaxdb-submit', 'myajaxdb_submit' );
	function myajaxdb_submit()
	{
		if (!session_id())session_start();
		global $woocommerce,$wpdb;

		$shipping_details = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name`='woocommerce_".WC()->session->chosen_shipping_methods[0]."_settings'");
		$defult_values = unserialize($shipping_details[0]->option_value);
		
		$shipping_type = $_REQUEST['shipping_name'];
		$price 		   = $_REQUEST['shipping_price'];
		$transit_time  = $_REQUEST['shipping_transit_time'];
		$service_type  = $_REQUEST['shipping_service_type'];
		//WC()->shipping->packages[0]['rates'][WC()->session->chosen_shipping_methods[0]]['cost'] = $price;
		//echo WC()->cart->get_cart_subtotal();exit;
		
		//var_dump($shipping_type);exit;
		$_SESSION['price'] =  $price;
		echo '1';exit;
	}
	
	
	function myajax_submit()
	{
	if (!session_id())session_start();
	global $woocommerce,$wpdb;
	
	
	
	$_SESSION['price'] = '0';
	if(!empty(WC()->session->chosen_shipping_methods[0]))
	{
		//default settings
		$shipping_details = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name` like '%woocommerce_transdirect_settings'");
		$extra_price = '';
		$defult_values = unserialize($shipping_details[0]->option_value);
		
		if($defult_values['Surcharge'] == 'yes')
		{
			// if($defult_values['type'] =='fixed')$extra_price = number_format ($defult_values['Surcharge_price'],2);
			// if($defult_values['type'] =='percent')
			// {
				// $extra_price = number_format (($defult_values['Surcharge_price']*100)/WC()->cart->total,2);
			// }
			// if($defult_values['type'] =='product')
			// {
				// $extra_price = number_format ($defult_values['Surcharge_price']*count(WC()->cart->get_cart()),2);
			// }
			$extra_price = number_format ($defult_values['Surcharge_price'],2);
		}
		
		$api_arr = '';
		$explode_from = explode(',',$defult_values['postcode']);
		$explode_to = explode(',',$_POST['to_location']);
		//$explode_to = explode(',','3000,MELBOURNE');
		
		
		$api_arr['sender']['country'] 		= 'AU';
		$api_arr['sender']['postcode'] 		= $explode_from[0];
		$api_arr['sender']['suburb'] 		= $explode_from[1];
		$api_arr['sender']['type'] 		= $defult_values['postcode_type'];
		$api_arr['receiver']['country'] 	= 'AU';
		$api_arr['receiver']['postcode'] 	= $explode_to[0];
		$api_arr['receiver']['suburb'] 		= $explode_to[1];
		//$api_arr['receiver']['type']        = 'residential';
		$api_arr['receiver']['type']        = $_REQUEST['to_type'];
		
		$api_arr['declared_value']        = '10000';
		
		
		$cart_content = WC()->cart->get_cart();
		$i = 0;
		foreach($cart_content as $cc)
		{
			$meta_values = get_post_meta($cc['data']->id);
			if(!empty($meta_values['_weight']['0'])) $api_arr['items'][$i]['weight'] = $meta_values['_weight']['0'];
			else $api_arr['items'][$i]['weight'] = $defult_values['weight'];
			
			//if less than 1
			if(!empty($meta_values['_weight']['0']) && $api_arr['items'][$i]['weight']<1)$api_arr['items'][$i]['weight'] = '1.0';
			
			if(!empty($meta_values['_height']['0'])) $api_arr['items'][$i]['height'] = $meta_values['_height']['0'];
			else $api_arr['items'][$i]['height'] = $defult_values['height'];
			
			if(!empty($meta_values['_width']['0'])) $api_arr['items'][$i]['width'] = $meta_values['_width']['0'];
			else $api_arr['items'][$i]['width'] = $defult_values['width'];
			
			if(!empty($meta_values['_length']['0'])) $api_arr['items'][$i]['length'] = $meta_values['_length']['0'];
			else $api_arr['items'][$i]['length'] = $defult_values['length'];
			
			$api_arr['items'][$i]['quantity'] = $cc['quantity'];
			$api_arr['items'][$i]['description'] = 'carton';
			$i++;
		}
		//echo '<pre>';
		
		$ch = curl_init();
	    //curl_setopt($ch, CURLOPT_URL, "https://www.staging.transdirect.com.au/api/bookings");
		curl_setopt($ch, CURLOPT_URL, "https://private-179c1-transdirect.apiary-mock.com/api/bookings");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_arr));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$response1 = curl_exec($ch);
		curl_close($ch);
		
		$response1 = str_replace("true, // true if the booking has a tailgate pickup, false if not","0,",$response1);
		$response1 = str_replace("true // true if the booking has a tailgate delivery, false if not","0",$response1);
		$response1 = str_replace("''","0",$response1);
		
		$shipping_quotes1 = json_decode(str_replace("''","0",$response1));
		//var_dump($shipping_quotes1);exit;
		$shipping_quotes = $shipping_quotes1->quotes;
		//var_dump($response1);exit;
		$html = '';
		$toll_html = '';
		$toll_express_html = '';
		$allied_html = '';
		$couriers_please_html = '';
		$fastway_html = '';
		$minfreight_html = '';
		$northline_html = '';
		//var_dump($shipping_quotes);exit;
		if($shipping_quotes !='')
		{
			foreach($shipping_quotes as $k=>$sq)
			{
				// if($sq->total !='')
				// {
					if($defult_values['fied_error']=='yes' && !isset($sq->total))$total_price =wc_format_decimal($defult_values['fied_error_price']);
					elseif($defult_values['fied_error']!='yes' && $sq->total =='')continue;
					else
					{
						$total_price = wc_format_decimal($sq->total+$extra_price);				
						if($defult_values['insurance_surcharge'] == 'yes')
						{
							$total_price = wc_format_decimal($total_price+$sq->insurance_fee);
						}
					}
					if($defult_values['show_courier']=='yes')
					{
					if($k == 'toll')$display_name = ' - Toll';
					if($k == 'allied')$display_name = ' - Allied Express';
					if($k == 'toll_priority')$display_name = ' - Toll Priority';
					if($k == 'couriers_please')$display_name = ' - Couriers Please';
					if($k == 'fastway')$display_name = ' - Fastway';
					if($k == 'minfreight')$display_name = ' - Mainfreight';
					if($k == 'northline')$display_name = ' - Northline';
					}
					else{
						$display_name = '';
					}
			if(count($defult_values['Couriers'])>0)
			{
					if($k == 'toll' && in_array('Toll',$defult_values['Couriers']))
					{
						if($defult_values['quotes'] =='Display all Quotes')
						{
							$toll_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
							<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
							<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
							<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						}
						
						if($defult_values['quotes'] =='Display Cheapest')
						{
							if(($prev_price!='' && $total_price < $prev_price) || $prev_price =='')
							{
								$toll_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
								<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
								<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
								<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
							}
							$prev_price = $total_price;
						}
						
						if($defult_values['quotes'] =='Display Cheapest & Fastest' && !empty($sq->transit_time))
						{
							$t_time = explode('' ,$sq->transit_time);
							$tt_time = explode('-',$t_time);
							$ship_time = $tt_time[count($tt_time)];
							if(($prev_price!='' && $total_price < $prev_price && $prev_ship_time!='' && $ship_time < $prev_ship_time) || ($prev_price =='' && $prev_ship_time==''))
							{
								$toll_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
								<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
								<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
								<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
							}
							$prev_price = $total_price;
							$prev_ship_time = $ship_time;
						}
					}
					if($k == 'toll_priority' && in_array('Toll Priority',$defult_values['Couriers']))
					{
						// $toll_express_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
						// <input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
						// <input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
						// <input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						if($defult_values['quotes'] =='Display all Quotes')
						{
							$toll_express_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
							<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
							<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
							<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						}
						
						if($defult_values['quotes'] =='Display Cheapest')
						{
							if(($prev_price1!='' && $total_price < $prev_price1) || $prev_price1 =='')
							{
								$toll_express_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
								<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
								<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
								<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
							}
							$prev_price1 = $total_price;
						}
						
						if($defult_values['quotes'] =='Display Cheapest & Fastest' && !empty($sq->transit_time))
						{
							$t_time = explode('' ,$sq->transit_time);
							$tt_time = explode('-',$t_time);
							$ship_time = $tt_time[count($tt_time)];
							if(($prev_price1!='' && $total_price < $prev_price1 && $prev_ship_time1!='' && $ship_time < $prev_ship_time1) || ($prev_price1 =='' && $prev_ship_time1==''))
							{
								$toll_express_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
								<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
								<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
								<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
							}
							$prev_price1 = $total_price;
							$prev_ship_time1 = $ship_time;
						}
					}
					if($k == 'allied' && in_array('Allied Express',$defult_values['Couriers']))
					{
						// $allied_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
						// <input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
						// <input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
						// <input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						
						if($defult_values['quotes'] =='Display all Quotes')
						{
							$allied_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
							<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
							<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
							<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						}
						
						if($defult_values['quotes'] =='Display Cheapest')
						{
							if(($prev_price2!='' && $total_price < $prev_price2) || $prev_price2 =='')
							{
								$allied_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
								<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
								<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
								<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
							}
							$prev_price2 = $total_price;
						}
						
						if($defult_values['quotes'] =='Display Cheapest & Fastest' && !empty($sq->transit_time))
						{
							$t_time = explode('' ,$sq->transit_time);
							$tt_time = explode('-',$t_time);
							$ship_time = $tt_time[count($tt_time)];
							if(($prev_price2!='' && $total_price < $prev_price2 && $prev_ship_time2!='' && $ship_time < $prev_ship_time2) || ($prev_price2 =='' && $prev_ship_time2==''))
							{
								$allied_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
								<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
								<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
								<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
							}
							$prev_price2 = $total_price;
							$prev_ship_time2 = $ship_time;
						}
					}
					if($k == 'couriers_please' && in_array('Couriers Please',$defult_values['Couriers']))
					{
						// $couriers_please_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
						// <input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
						// <input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
						// <input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						if($defult_values['quotes'] =='Display all Quotes')
						{
							if(isset($sq->transit_time))
							{
								$transit_time = $sq->transit_time;	
							}
							else
							{
								$transit_time = 0;
							}
							
							if(isset($sq->service_type))
							{
								$service_type = $sq->service_type;	
							}
							else
							{
								$service_type = 0;
							}
							$couriers_please_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
							<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
							<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$transit_time.'" />
							<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$service_type.'" />';
						}
						
						if($defult_values['quotes'] =='Display Cheapest')
						{
							if(($prev_price3!='' && $total_price < $prev_price3) || $prev_price3 =='')
							{
								$couriers_please_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
								<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
								<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
								<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
							}
							$prev_price3 = $total_price;
						}
						
						if($defult_values['quotes'] =='Display Cheapest & Fastest' && !empty($sq->transit_time))
						{
							$t_time = explode('' ,$sq->transit_time);
							$tt_time = explode('-',$t_time);
							$ship_time = $tt_time[count($tt_time)];
							if(($prev_price3!='' && $total_price < $prev_price3 && $prev_ship_time3!='' && $ship_time < $prev_ship_time3) || ($prev_price3 =='' && $prev_ship_time3==''))
							{
								$couriers_please_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
								<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
								<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
								<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
							}
							$prev_price3 = $total_price;
							$prev_ship_time3 = $ship_time;
						}
						
						
					}
					if($k == 'fastway' && in_array('Fastway',$defult_values['Couriers']))
					{
						// $fastway_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
						// <input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
						// <input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
						// <input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						if($defult_values['quotes'] =='Display all Quotes')
						{
							$fastway_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
							<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
							<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
							<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						}
						
						if($defult_values['quotes'] =='Display Cheapest')
						{
							if(($prev_price4!='' && $total_price < $prev_price4) || $prev_price4 =='')
							{
								$fastway_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
								<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
								<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
								<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
							}
							$prev_price4 = $total_price;
						}
						
						if($defult_values['quotes'] =='Display Cheapest & Fastest' && !empty($sq->transit_time))
						{
							$t_time = explode('' ,$sq->transit_time);
							$tt_time = explode('-',$t_time);
							$ship_time = $tt_time[count($tt_time)];
							if(($prev_price4!='' && $total_price < $prev_price4 && $prev_ship_time4!='' && $ship_time < $prev_ship_time4) || ($prev_price4 =='' && $prev_ship_time4==''))
							{
								$fastway_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
								<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
								<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
								<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
							}
							$prev_price4 = $total_price;
							$prev_ship_time4 = $ship_time;
						}
					}
					if($k == 'minfreight' && in_array('Mainfreight',$defult_values['Couriers']))
					{
						// $minfreight_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
						// <input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
						// <input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
						// <input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						if($defult_values['quotes'] =='Display all Quotes')
						{
							$minfreight_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
							<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
							<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
							<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						}
						
						if($defult_values['quotes'] =='Display Cheapest')
						{
							if(($prev_price5!='' && $total_price < $prev_price5) || $prev_price5 =='')
							{
								$minfreight_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
								<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
								<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
								<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
							}
							$prev_price5 = $total_price;
						}
						
						if($defult_values['quotes'] =='Display Cheapest & Fastest' && !empty($sq->transit_time))
						{
							$t_time = explode('' ,$sq->transit_time);
							$tt_time = explode('-',$t_time);
							$ship_time = $tt_time[count($tt_time)];
							if(($prev_price5!='' && $total_price < $prev_price5 && $prev_ship_time5!='' && $ship_time < $prev_ship_time5) || ($prev_price5 =='' && $prev_ship_time5==''))
							{
								$minfreight_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
								<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
								<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
								<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
							}
							$prev_price5 = $total_price;
							$prev_ship_time5 = $ship_time;
						}
					}
					if($k == 'northline' && in_array('Northline',$defult_values['Couriers']))
					{
						// $northline_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
						// <input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
						// <input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
						// <input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						if($defult_values['quotes'] =='Display all Quotes')
						{
							$northline_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
							<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
							<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
							<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						}
						
						if($defult_values['quotes'] =='Display Cheapest')
						{
							if(($prev_price6!='' && $total_price < $prev_price6) || $prev_price6 =='')
							{
								$northline_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
								<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
								<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
								<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
							}
							$prev_price6 = $total_price;
						}
						
						if($defult_values['quotes'] =='Display Cheapest & Fastest' && !empty($sq->transit_time))
						{
							$t_time = explode('' ,$sq->transit_time);
							$tt_time = explode('-',$t_time);
							$ship_time = $tt_time[count($tt_time)];
							if(($prev_price6!='' && $total_price < $prev_price6 && $prev_ship_time6!='' && $ship_time < $prev_ship_time6) || ($prev_price6 =='' && $prev_ship_time6==''))
							{
								$northline_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.$display_name.'<br/>
								<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
								<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
								<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
							}
							$prev_price6 = $total_price;
							$prev_ship_time6 = $ship_time;
						}
					}
			}
					
				//}
				
			
				/*if($sq->total !='')
				{
					
					$total_price = wc_format_decimal($sq->total+$extra_price);
					if($k == 'toll')$display_name = 'Toll';
					if($k == 'allied ')$display_name = 'Allied Express';
					if($defult_values['quotes'] =='Display all Quotes')
					{
						$html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
						<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
						<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
						<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
					}
					if($defult_values['quotes'] =='Display Cheapest')
					{
						if(($prev_price!='' && $total_price < $prev_price) || $prev_price =='')
						{
							$html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
							<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
							<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
							<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						}
						$prev_price = $total_price;
					}
					if($defult_values['quotes'] =='Display Cheapest & Fastest')
					{
						$t_time = explode('' ,$sq->transit_time);
						$tt_time = explode('-',$t_time);
						$ship_time = $tt_time[count($tt_time)];
						if(($prev_price!='' && $total_price < $prev_price && $prev_ship_time!='' && $ship_time < $prev_ship_time) || ($prev_price =='' && $prev_ship_time==''))
						{
							$html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
							<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
							<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
							<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						}
						$prev_price = $total_price;
						$prev_ship_time = $ship_time;
					}
				}*/
			}
		}
		$html = '<span style="float:right;"><a href="javascript:void(0)" title="close" onclick="document.getElementById(\'shipping_type\').style.display=\'none\';">X</a></span>'.$toll_html.$toll_express_html.$allied_html.$couriers_please_html.$fastway_html.$minfreight_html.$northline_html;
		
		if($html =='' && $defult_values['fied_error']=='yes')
		{
			$_SESSION['price'] =  $defult_values['fied_error_price'];
			echo 'No data found';
		}
	header( "Content-Type: text/html" );
	echo $html;
	}
	else
	{
		echo '';
	}
	// IMPORTANT: don't forget to "exit"
	exit;
	}
	
	
	function plugin_test()
	{
		global $woocommerce,$wpdb;
		include 'transdirect-calculator.php';
		
	}
	
	//cart page html show hooks
	add_action( 'woocommerce_after_cart_totals', 'plugin_test');
	add_action( 'woocommerce_review_order_before_payment', 'plugin_test');
	
	//check out page price show hooks
	add_filter('woocommerce_checkout_order_review', 'return_custom_price');
	//add_filter('wp_ajax_woocommerce_update_order_review', 'return_custom_price');
	//add_filter('wp_ajax_nopriv_woocommerce_update_order_review', 'return_custom_price');
	
	add_filter( 'woocommerce_cart_shipping_method_full_label', 'remove_local_pickup_free_label', 10, 2 );
	function remove_local_pickup_free_label($full_label, $method){
		global $wpdb;
		if($method->id=='woocommerce_transdirect')
		{
			
				$shipping_details_plugin = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name` like '%woocommerce_transdirect_settings'");
				$shippin_data = unserialize($shipping_details_plugin[0]->option_value);
						
				
				if($shippin_data['title']!='')	$label=$shippin_data['title'];
				else $label='Transdirect Shipping';
			
			$full_label = $label;
			return $full_label;
		}
		else
		{
			return $full_label;	
		}
	}
	
	
	/**
	* if shipping is disable
	**/
	add_filter( 'woocommerce_package_rates', 'hide_shipping_when_free_is_available', 10, 2 ); 

	function hide_shipping_when_free_is_available( $rates, $package ) {
		global $wpdb;
		// Only modify rates if free_shipping is present		
		if ( isset( $rates['woocommerce_transdirect'] ) ) {
			$shipping_details_plugin = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name` like '%woocommerce_transdirect_settings'");
			$shippin_data = unserialize($shipping_details_plugin[0]->option_value);
		
			// To unset a single rate/method, do the following. This example unsets flat_rate shipping
			if($shippin_data['enabled']!='yes')
			unset( $rates['woocommerce_transdirect'] );			
		}
		
		return $rates;
	}
	
	/**
	* Process the checkout
	*/
	add_action('woocommerce_checkout_process', 'my_custom_checkout_field_process');
	
	function my_custom_checkout_field_process() {
	// Check if set, if its not set add an error.
	if ( ! $_POST['billing_postcode'] || !is_numeric($_POST['billing_postcode']))
	wc_add_notice( __( 'Please enter a valid postcode/ZIP.' ), 'error' );
	}
	
	
	
	


}


