<?php
/**
 * Plugin Name: Transdirect Shipping
 * Plugin URI: https://www.transdirect.com.au/e-commerce/woo-commerce/
 * Description: This plugin allows you to calculate shipping as per your delivery location.
 * FAQ: https://www.transdirect.com.au/e-commerce/woo-commerce/
 * Version: 1.4
 * Author: Transdirect
 * Author URI: http://transdirect.com.au/
 * Text Domain: woocommerce_transdirect
 * Domain Path: /lang
**/

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

if (!session_id()) session_start();
error_reporting(E_ALL & ~E_NOTICE);

/**
 * Check if WooCommerce is active
 */
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

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

					$this->method_title	= __('Transdirect Shipping', $this->id);
					$this->method_description = __('', $this->id);

					$this->wc_shipping_init();

					//$this->init_shipping_fields_per_country();

					//$this->init_shipping_fields_per_state();

					//$this->init_shipping_fields_per_postcode();
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
				function admin_options() {

					global $woocommerce, $wpdb;
					$field = $this->plugin_id . $this->id . '_';

					$shipping_details = $wpdb->get_results("SELECT `option_value` FROM `wp_options` WHERE `option_name`='" . $field . "settings'");
					$default_values = unserialize($shipping_details[0]->option_value);

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
						$shipping_details_plugin = $wpdb->get_results("SELECT `option_value` FROM `wp_options` WHERE `option_name` like '%woocommerce_transdirect_settings'");
					
						if(count($shipping_details_plugin) > 0) {
							//echo "UPDATE `wp_options` SET  `option_value`='".$default_values_plugin."' WHERE `option_name`='woocommerce_woocommerce_transdirect_settings'";exit;
						    $wpdb->query("UPDATE `wp_options` SET  `option_value`='".$default_values_plugin."' WHERE `option_name` like  '%woocommerce_transdirect_settings'");
						} else {
						    //$wpdb->query("INSERT INTO `wp_options` SET  `option_value`='".$default_values_plugin."', `option_name` like '%woocommerce_transdirect_settings'");
							//Changed by Lee
							$wpdb->query("INSERT INTO `wp_options` SET  `option_value`='".$default_values_plugin."', `option_name` = 'woocommerce_woocommerce_transdirect_settings'");
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
				
					$shipping_details_plugin = $wpdb->get_results("SELECT `option_value` FROM `wp_options` WHERE `option_name` like '%woocommerce_transdirect_settings'");
					$shipping_data = unserialize($shipping_details_plugin[0]->option_value);
						
					$final_rate = 0;

					if ($shipping_data!='')
						$label = __($shipping_data['title'], $this->id);
					else
						$label = __('Transdirect Shipping', $this->id);
				
					$rate = array(
						'id'        => $this->id,
						'label'     => (trim($label)!='' ? $label : $this->title),
						'cost'      => $final_rate,
						'calc_tax'  => 'per_order'
					);
	
					// Registers the rate
					$this->add_rate($rate);
				}
			}
		}
	}

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
	
	add_filter('woocommerce_cart_totals_before_order_total', 'return_custom_price'); 
	
	function return_custom_price() {    

		global $post, $woocommerce;
		//if (!session_id())session_start();
		
		if (WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect' && isset($_SESSION['price'])) {

			WC()->shipping->shipping_total = $_SESSION['price'];
			WC()->cart->total = WC()->cart->subtotal + $_SESSION['price'];
			WC()->session->shipping_total = '10';
			WC()->session->total = WC()->session->subtotal + $_SESSION['price'];
			WC()->cart->add_fee(__('Shipping Cost', 'woocommerce'), $_SESSION['price']);
			WC()->session->set('shipping_total"', $_SESSION['price']);
		} else {
			unset($_SESSION['price']);
		}
	}
	
	add_filter('woocommerce_checkout_update_order_meta', 'matter_ref_num_checkout_field_update_order_meta');
	
	function matter_ref_num_checkout_field_update_order_meta($order_id) {

		global $post, $woocommerce;

		if (!session_id())session_start();

		if (WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect') {

			update_post_meta($order_id, '_order_shipping', wc_format_decimal($_SESSION['price']));
			wc_add_order_item_meta($item_id, 'cost', wc_format_decimal($_SESSION['price']));
			update_post_meta( $order_id, '_order_total', wc_format_decimal( WC()->cart->total+$_SESSION['price'], get_option('woocommerce_price_num_decimals')));
		}
	}
	
	add_filter('woocommerce_email_order_meta_keys', 'my_custom_checkout_field_order_meta_keys');
	
	function my_custom_checkout_field_order_meta_keys( $keys ) {

		global $post, $woocommerce;

		if (!session_id())session_start();

		if (WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect') {
			$keys['Shipping Cost'] = $_SESSION['price'];
			return $keys;
		}
	}
	
	function shipping_quotes_get()
	{
		global $woocommerce,$wpdb;

		if (!session_id())session_start();

		$_SESSION['price'] = '0';

		var_dump(WC()->session); exit;

		if (!empty(WC()->session->chosen_shipping_methods[0])) {

			//default settings
			$shipping_details = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name` like '%woocommerce_transdirect_settings'");
			$default_values = unserialize($shipping_details[0]->option_value);
			
			$api_arr = '';
			$explode_from = explode(',',$default_values['postcode']);
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

			foreach($cart_content as $cc) {

				//var_dump($cc['quantity']);exit;
				if(!empty($cc['weight'])) $api_arr['items'][$i]['weight'] = $cc['weight'];
				else $api_arr['items'][$i]['weight'] = $default_values['weight'];
				
				if(!empty($cc['height'])) $api_arr['items'][$i]['height'] = $cc['height'];
				else $api_arr['items'][$i]['height'] = $default_values['height'];
				
				if(!empty($cc['width'])) $api_arr['items'][$i]['width'] = $cc['width'];
				else $api_arr['items'][$i]['width'] = $default_values['width'];
				
				if(!empty($cc['length'])) $api_arr['items'][$i]['length'] = $cc['length'];
				else $api_arr['items'][$i]['length'] = $default_values['length'];
				
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
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			$response1 = curl_exec($ch);
			curl_close($ch);
			$shipping_quotes1 = json_decode(str_replace("''","0",$response1));
			$shipping_quotes = $shipping_quotes1->quotes;

			foreach($shipping_quotes as $k=>$sq) {
			
				$html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" id="'.$k.'" value="'.$k.'" />'.$k.'-'.$sq->total.'-'.$sq->service_type.'-'.$sq->transit_time.'<br>
				<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$sq->total.'" />
				<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
				<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
			}
			
			echo $html;
		}
	}

	add_action( 'quotes_get_hook', 'shipping_quotes_get');

	function add_my_css_and_my_js_files() {
		wp_enqueue_script('your-script-name', plugins_url('transdirect.js', __FILE__), array('jquery'), '1.2.3', true);
		wp_localize_script( 'your-script-name', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )) );
	}

	add_action('wp_enqueue_scripts', "add_my_css_and_my_js_files");
	
	// this hook is fired if the current viewer is not logged in
	do_action('wp_ajax_nopriv_myajax-submit');
	
	// if logged in
	do_action('wp_ajax_myajax-submit');
	
	add_action('wp_ajax_nopriv_myajax-submit', 'myajax_submit');
	add_action('wp_ajax_myajax-submit', 'myajax_submit');

	//submit code
	/*function add_my_css_and_my_js_files1() {

		wp_enqueue_script('your-script-name', plugins_url('transdirect.js', __FILE__), array('jquery'), '1.2.3', true);
		wp_localize_script( 'your-script-name', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )) );
	}*/
	//add_action('wp_enqueue_scripts', "add_my_css_and_my_js_files");
	
	// this hook is fired if the current viewer is not logged in
	do_action('wp_ajax_nopriv_myajaxdb-submit');
	
	// if logged in
	do_action('wp_ajax_myajaxdb-submit');
	
	add_action('wp_ajax_nopriv_myajaxdb-submit', 'myajaxdb_submit');
	add_action('wp_ajax_myajaxdb-submit', 'myajaxdb_submit');

	function myajaxdb_submit() {

		if (!session_id()) session_start();

		$_SESSION['price'] =  $_REQUEST['shipping_price'];

		echo '1';
		exit;
	}

	function myajax_submit() {

		if (!session_id()) session_start();

		global $woocommerce, $wpdb;

		$_SESSION['price'] = '0';

		if (!empty(WC()->session->chosen_shipping_methods[0])) {

			// Get default settings
			$shipping_details = $wpdb->get_results("SELECT `option_value` FROM `wp_options` WHERE `option_name` like '%woocommerce_transdirect_settings'");
			$default_values = unserialize($shipping_details[0]->option_value);

			$explode_from   = explode(',', $default_values['postcode']);
			$explode_to     = explode(',', $_POST['to_location']);

			$api_arr = '';
			$api_arr['sender']['country']   = 'AU';
			$api_arr['sender']['postcode']  = $explode_from[0];
			$api_arr['sender']['suburb']    = $explode_from[1];
			$api_arr['sender']['type']      = $default_values['postcode_type'];
			$api_arr['receiver']['country'] = 'AU';
			$api_arr['receiver']['postcode']= $explode_to[0];
			$api_arr['receiver']['suburb'] 	= $explode_to[1];
			$api_arr['receiver']['type']    = $_POST['to_type'];
			$api_arr['declared_value']      = number_format(!empty($_POST['insurance_value']) ? $_POST['insurance_value'] : 0, 2, '.', '');

			$cart_content = WC()->cart->get_cart();

			$i = 0;

			foreach($cart_content as $cc) {

				$meta_values = get_post_meta($cc['data']->id);

				if (!empty($meta_values['_weight']['0']))
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

				$i++;
			}

			$args = array(
				'headers'   => array(
					'Authorization' => 'Basic ' . base64_encode($default_values['email'] . ':' . $default_values['password']),
					'Content-Type'  => 'application/json'
				),
				'body'      => json_encode($api_arr),
				'timeout'   => 45
			);

			$link = "https://www.transdirect.com.au/api/bookings";

			$response = wp_remote_retrieve_body(wp_remote_post($link, $args));
			$response = str_replace("true, // true if the booking has a tailgate pickup, false if not", "0,", $response);
			$response = str_replace("true // true if the booking has a tailgate delivery, false if not", "0", $response);
			$response = str_replace("''", "0", $response);
		
			$shipping_quotes = json_decode(str_replace("''", "0", $response));
			$shipping_quotes = $shipping_quotes->quotes;

			$quotes = array();

			if ($shipping_quotes != '') {
				$handling_surcharge = 0;
				if ($default_values['surcharge'] == 'yes')
					$handling_surcharge = number_format($default_values['surcharge_price'], 2);

				foreach ($shipping_quotes as $k => $sq) {

					$price_insurance_ex = $sq->price_insurance_ex;
					$insurance_fee      = $sq->fee;
					$total              = $sq->total;

					$insurance_fee_html = '';
					if ($default_values['insurance_surcharge'] == 'yes') {
						$total_price = wc_format_decimal($total + $handling_surcharge);
						//$insurance_fee_html = $default_values['insurance_surcharge'] == 'yes' ? (' + ' . get_woocommerce_currency_symbol() . $insurance_fee . ' insurance fee') : '';

						//addedd by ellen
						$insurance_fee_html = $default_values['insurance_surcharge'] == 'yes' ? ('&nbsp;&nbsp;+&nbsp;&nbsp;' . get_woocommerce_currency_symbol().'&nbsp;'. $insurance_fee . '&nbsp;insurance fee') : '';
					} else {
						$total_price = wc_format_decimal($price_insurance_ex + $handling_surcharge);
					}

					if ($default_values['show_courier'] == 'yes') {

						$courier_name = '';

						if ($k == 'toll')
							$courier_name = 'Toll';
						elseif ($k == 'allied')
							$courier_name = 'Allied Express';
						elseif ($k == 'toll_priority_sameday')
							$courier_name = 'Toll Priority Sameday';
						elseif ($k == 'toll_priority_overnight')
							$courier_name = 'Toll Priority Overnight';
						elseif ($k == 'couriers_please')
							$courier_name = 'Couriers Please';
						elseif ($k == 'fastway')
							$courier_name = 'Fastway';
						elseif ($k == 'minfreight')
							$courier_name = 'Mainfreight';
						elseif ($k == 'northline')
							$courier_name = 'Northline';

						if (count($default_values['couriers']) > 0) {

							if (in_array($courier_name, $default_values['couriers'])) {

								if ($default_values['quotes'] == 'Display all Quotes') {

									$quotes['couriers'][$k]['html'] = '<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\'' . $k . '\');" id="' . $k . '" value="' . $k . '" />
										'.'<b>' .$courier_name . '</b>&nbsp;-&nbsp;' . get_woocommerce_currency_symbol().'&nbsp;'. $price_insurance_ex . $insurance_fee_html . ($handling_surcharge > 0 ? '&nbsp;&nbsp;+&nbsp;&nbsp;'.$handling_surcharge . ' handling surcharge.' : '') . '<br/>

										<input type="hidden" name="' . $k . '_price" id="' . $k . '_price" value="' . $total_price . '" />
										<input type="hidden" name="' . $k . '_transit_time" id="' . $k . '_transit_time" value="' . $sq->transit_time . '" />
										<input type="hidden" name="' . $k . '_service_type" id="' . $k . '_service_type" value="' . $sq->service_type . '" />';

								} elseif ($default_values['quotes'] == 'Display Cheapest') {

									if ($quotes['cheapest']['price'] > $total_price || !isset($quotes['cheapest']['price'])) {
										$quotes['cheapest']['price'] = $total_price;

										// Initialize array to remove previous values
										$quotes['cheapest']['couriers'] = null;
										$quotes['cheapest']['couriers'][$k] = '<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\'' . $k . '\');" id="' . $k . '" value="' . $k . '" />
											'.'<b>' .$courier_name . '</b>&nbsp;-&nbsp;' . get_woocommerce_currency_symbol().'&nbsp;'. $price_insurance_ex . $insurance_fee_html . ($handling_surcharge > 0 ? '&nbsp;&nbsp;+&nbsp;&nbsp;'.$handling_surcharge . ' handling surcharge.' : '') . '<br/>
											<input type="hidden" name="' . $k . '_price" id="' . $k . '_price" value="' . $total_price . '" />
											<input type="hidden" name="' . $k . '_transit_time" id="' . $k . '_transit_time" value="' . $sq->transit_time . '" />
											<input type="hidden" name="' . $k . '_service_type" id="' . $k . '_service_type" value="' . $sq->service_type . '" />';

									} elseif ($quotes['cheapest']['price'] == $total_price || !isset($quotes['cheapest']['price'])) {

										$quotes['cheapest']['couriers'][$k] = '<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\'' . $k . '\');" id="' . $k . '" value="' . $k . '" />
											'.'<b>' .$courier_name . '</b>&nbsp;-&nbsp;' . get_woocommerce_currency_symbol().'&nbsp;'. $price_insurance_ex . $insurance_fee_html . ($handling_surcharge > 0 ? '&nbsp;&nbsp;+&nbsp;&nbsp;'.$handling_surcharge . ' handling surcharge.' : '') . '<br/>
											<input type="hidden" name="' . $k . '_price" id="' . $k . '_price" value="' . $total_price . '" />
											<input type="hidden" name="' . $k . '_transit_time" id="' . $k . '_transit_time" value="' . $sq->transit_time . '" />
											<input type="hidden" name="' . $k . '_service_type" id="' . $k . '_service_type" value="' . $sq->service_type . '" />';

									}

								} elseif ($default_values['quotes'] == 'Display Cheapest Fastest') {

									$timeDay    = explode(' ', $sq->transit_time);
									$timeNoDay  = explode('-', $timeDay[0]);
									$fastest    = $timeDay[0] == '' ? 0 : $timeNoDay[0];

									if ($quotes['fastest']['day'] > $fastest || !isset($quotes['fastest']['day'])) {
										$quotes['fastest']['day'] = $fastest;

										// Initialize array to remove previous values
										$quotes['fastest']['couriers'] = null;
										$quotes['fastest']['couriers'][$k] = '<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\'' . $k . '\');" id="' . $k . '" value="' . $k . '" />
											' . $courier_name . ' - ' . get_woocommerce_currency_symbol() . $price_insurance_ex . $insurance_fee_html . ' + ' . ($handling_surcharge > 0 ? $handling_surcharge . ' handling surcharge.' : '') . '<br/>
											<input type="hidden" name="' . $k . '_price" id="' . $k . '_price" value="' . $total_price . '" />
											<input type="hidden" name="' . $k . '_transit_time" id="' . $k . '_transit_time" value="' . $sq->transit_time . '" />
											<input type="hidden" name="' . $k . '_service_type" id="' . $k . '_service_type" value="' . $sq->service_type . '" />';

									} elseif ($quotes['fastest']['day'] == $fastest || !isset($quotes['fastest']['day'])) {

										$quotes['fastest']['couriers'][$k] = '<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\'' . $k . '\');" id="' . $k . '" value="' . $k . '" />
											' . $courier_name . ' - ' . get_woocommerce_currency_symbol() . $price_insurance_ex . $insurance_fee_html . ' + ' . ($handling_surcharge > 0 ? $handling_surcharge . ' handling surcharge.' : '') . '<br/>
											<input type="hidden" name="' . $k . '_price" id="' . $k . '_price" value="' . $total_price . '" />
											<input type="hidden" name="' . $k . '_transit_time" id="' . $k . '_transit_time" value="' . $sq->transit_time . '" />
											<input type="hidden" name="' . $k . '_service_type" id="' . $k . '_service_type" value="' . $sq->service_type . '" />';
									}
								}
							}
						}
					}
				}
			}

			$html = '<span class="close-option" style="float:right;"><a href="javascript:void(0)" title="close" 
			onclick="document.getElementById(\'shipping_type\').style.display=\'none\';">Close</a></span>';

			if ($default_values['quotes'] == 'Display all Quotes') {
				if($quotes['couriers']){
					foreach ($quotes['couriers'] as $key => $value) {
						$html = $html . $value['html'];
					}
				} else{
					//echo 'No data found ';
					echo 'No Login Provided';
				}
			} elseif ($default_values['quotes'] == 'Display Cheapest') {
				if($quotes['cheapest']['couriers']) {
					foreach ($quotes['cheapest']['couriers'] as $key => $value)
						$html = $html . $value;
				} else {
					//echo 'No data found';
					echo 'No Login Provided';
				}
			} elseif ($default_values['quotes'] == 'Display Cheapest Fastest') {
				if($quotes['fastest']['couriers']){
					foreach ($quotes['fastest']['couriers'] as $key => $value)
						$html = $html . $value;
				} else {
					//echo 'No data found';
					echo 'No Login Provided';
				}
			}

			if($html == '' && $default_values['fixed_error'] == 'yes') {
				$_SESSION['price'] =  $default_values['fixed_error_price'];
				//echo 'No data found';
			}

			if($html == '') {
				echo "No Login Provided";
			} else {
				header( "Content-Type: text/html" );
				echo $html;
			}
		}
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
	add_action('woocommerce_after_cart_totals', 'plugin_test');
	add_action('woocommerce_review_order_before_payment', 'plugin_test');

	//check out page price show hooks
	add_filter('woocommerce_checkout_order_review', 'return_custom_price');
	//add_filter('wp_ajax_woocommerce_update_order_review', 'return_custom_price');
	//add_filter('wp_ajax_nopriv_woocommerce_update_order_review', 'return_custom_price');
	
	add_filter( 'woocommerce_cart_shipping_method_full_label', 'remove_local_pickup_free_label', 10, 2 );

	function remove_local_pickup_free_label($full_label, $method) {

		global $wpdb;
		if ($method->id == 'woocommerce_transdirect') {
			
			$shipping_details_plugin = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name` like '%woocommerce_transdirect_settings'");
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

			$shipping_details_plugin = $wpdb->get_results("SELECT `option_value` FROM `wp_options` WHERE `option_name` like '%woocommerce_transdirect_settings'");
			$shipping_data = unserialize($shipping_details_plugin[0]->option_value);
		
			// To unset a single rate/method, do the following. This example unsets flat_rate shipping
			if ($shipping_data['enabled'] != 'yes')
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
		if (!$_POST['billing_postcode'] || !is_numeric($_POST['billing_postcode']))
			wc_add_notice( __( 'Please enter a valid postcode/ZIP.' ), 'error' );
	}
}