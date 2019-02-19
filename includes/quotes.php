<?php
/**
 * Shipping Transdirect Call Quotes API
 *
 * @author 		Transdirect
 * @version     6.7
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Generate quotes from receiver details entered in TD calculator
 *
 * Sender's details are managed at td account
 *
 * List of couriers are managed at td account
 */
class Quotes {
	function td_get_quote() {
		// Get default settings
        $getTitle = td_getApiDetails();
        if(strpos($_POST['to_location'], ',')){
            $explode_to     = explode(',', $_POST['to_location']);
        } else {
            $country_to     = strtoupper($_POST['country']);
            $postcode_to    = $_POST['to_postcode'];
        }
        if($getTitle->mode != "simplified_mode") {
            $country_to = $country_to ? $country_to : 'AU';
        } else {
            $country_to = $_POST['country'] ? $_POST['country'] : 'AU'; 
        }

        $suburb = $explode_to[1] ? strtoupper($explode_to[1]) : '';
        $postcode = $postcode_to ? $postcode_to : $explode_to[0];

	    global $wpdb;
	    
        $shipping_details = $wpdb->get_results("SELECT `option_value` FROM " . $wpdb->prefix ."options WHERE `option_name` like '%woocommerce_transdirect_settings'");
        $default_values = unserialize($shipping_details[0]->option_value);

        // Set request array for td quote api
        $api_arr = [];
        $api_arr['receiver']['country'] = $country_to ? $country_to : 'AU';
        $api_arr['receiver']['postcode']= $postcode_to ? $postcode_to : $explode_to[0];
        $api_arr['receiver']['suburb']  = $explode_to[1] ? strtoupper($explode_to[1]) : '';
        $api_arr['receiver']['type']    = $getTitle->street_type;
        $api_arr['declared_value']      = (isset($getTitle->insurance_select) && $getTitle->insurance_select == 'on') ? $getTitle->insurance_value : "0.00";
        $api_arr['referrer']            = 'woocommerce';
        $api_arr['requesting_site']     = get_site_url();
        $api_arr['wp_plugin_version']   = 'updated';
        $api_arr['total_amount']  =  floatval(WC()->cart->total);

        $_SESSION['postcode'] =  $api_arr['receiver']['postcode'];
        $_SESSION['to_location'] = $api_arr['receiver']['suburb'];
        $_SESSION['to_country']  = $api_arr['receiver']['country'];

        $cart_content = WC()->cart->get_cart();
        $api_arr = array_merge($api_arr, $this->td_get_cart_items($cart_content));

        $args = array();
        $args =  td_request_method_headers($default_values['api_key'], $api_arr, 'POST');

        // Send request to td api to get quote
        $link                   = "https://www.transdirect.com.au/api/bookings/v4";
        $response               = wp_remote_retrieve_body(wp_remote_post($link, $args));
        $response               = str_replace("true // true if the booking has a tailgate delivery, false if not", "0", $response);
        $response               = str_replace("''", "0", $response);
        $shipping_quotes        = json_decode(str_replace("''", "0", $response));
        $_SESSION['booking_id'] = $shipping_quotes->id;
        $shipping_quotes        = $shipping_quotes->quotes;
        $jsonResponse           = json_decode($response);

        $quotes      = array();
        $total_quote = array();
        $total_price = 0;
    
        // Check error in reciever address or item details
        if(isset($jsonResponse->errors->receiver->suburb) && !empty($jsonResponse->errors->receiver->suburb)){
            return 'Invalid delivery postcode.';
        }
        if(isset($jsonResponse->errors->items)) {
            return 'Invalid item dimension.';
        }
        if($jsonResponse->status == 'error' && isset($jsonResponse->message) && $jsonResponse->message == 'Please submit separate orders as these products ship from different Warehouses') {
            return $jsonResponse->message;
        }
        
        // Check quote not null
        if ($shipping_quotes != '') {

            foreach ($shipping_quotes as $k => $sq) {
                $price_insurance_ex = $sq->price_insurance_ex;
                $applied_gst = $sq->applied_gst;
                $insurance_fee      = $sq->fee;
                $total              = $sq->total;
                $courier            = $sq->courier ? $sq->courier : ucwords(str_replace('_', ' ', $k));
                $handling_surcharge = 0;
                array_push($total_quote, array(
                    'courier' => $courier , // courier name
                    'totals' =>  $total,    // total courier price
                    'gst' => $applied_gst,  // courier gst value
                    'transit_time' => $sq->transit_time,
                    'base' => $k,
                    )
                );
            }//end of foreach

            usort($total_quote, function ($item1, $item2) {
                if ($item1['totals'] == $item2['totals']) return 0;
                return $item1['totals'] < $item2['totals'] ? -1 : 1;
            });

            // Create html view of quote, to show quote on cart or checkout page
            foreach ($total_quote as $key => $value) {
                if($total_quote[$key]['totals'] == 0) {
                    $total_quote_price = 'Free Shipping';
                    $_SESSION['free_shipping'] = 'Free Shipping';
                } else {
                    $total_quote_price = get_woocommerce_currency_symbol() .
                '&nbsp;'. number_format($total_quote[$key]['totals'], 2); 
                }
                $replace =  preg_replace('~([^a-zA-Z\n\r()0-9]+)~', '_',
                    $total_quote[$key]['courier']);

                $quotes['couriers'][$total_quote[$key]['courier']]['html'] =
                '<span class="td_shipping"><input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\'' .  $replace . '\');"
                id="' .  $replace . '" value="' .  $replace. '" />' .
                '<b>&nbsp;&nbsp;&nbsp;' .  $total_quote[$key]['courier'] .
                '</b> &nbsp;-&nbsp;' . $total_quote_price . '<br/>

                <input type="hidden" name="' .  $replace . '_price"
                id="' .  $replace .'_price" value="' . $total_quote[$key]['totals']. '" />

                <input type="hidden" name="' .  $replace . '_base"
                    id="' .  $replace .'_base" value="' . $total_quote[$key]['base']. '" />

                <input type="hidden" name="' .  $replace . '_applied_gst"
                    id="' .  $replace .'_applied_gst" value="' . $total_quote[$key]['gst']. '" />

                <input type="hidden" name="' . $total_quote[$key]['courier'] . '_transit_time"
                id="' .  $replace . '_transit_time"
                value="' . $total_quote[$key]['transit_time'] . '" /></span>';
            }
        }
        
        if($getTitle->mode == 'simplified_mode') {
            $html = '';
        } else {
            $html = '<span class="td-close-option" style="float:right;"><a href="javascript:void(0)" title="close"
        onclick="document.getElementById(\'shipping_type\').style.display=\'none\';">Close</a></span>';
        }

        if($quotes['couriers']){
            foreach ($quotes['couriers'] as $key => $value) {
                $html = $html . $value['html'];
            }
        } else{
            $html = "Please check module settings in transdirect account.";
        }
        $_SESSION['td_response'] = $html;
        return $html;
	}

    function td_get_cart_items($items) {
        //$cart_content = WC()->cart->get_cart();
        $i = 0;
        $items_list  = array();
        $box_items = array();

        // Set item request array from cart items
        foreach($items as $cc) {
            $meta_values = get_post_meta($cc['data']->id);
            $item_id = $cc['product_id'];
            if(!empty($item_id)) {
                $product = wc_get_product($item_id);
                $product_type = $product->product_type;
                if($product_type == 'variable' || $product_type == 'variation')
                {
                    $varId = $cc['variation_id'];
                    $var_meta_values = get_post_meta($varId);					
                }else{
                    $var_meta_values['_weight']['0'] = '';
                    $var_meta_values['_height']['0'] = '';
                    $var_meta_values['_width']['0']  = '';
                    $var_meta_values['_length']['0'] = '';
					$var_meta_values['_sku']['0'] = '';
                }
            }

            if (!empty($var_meta_values['_weight']['0'])) {
                $api_arr['items'][$i]['weight'] = $var_meta_values['_weight']['0'];
            }
            else if (!empty($meta_values['_weight']['0']))  {
                $api_arr['items'][$i]['weight'] = $meta_values['_weight']['0'];
            }
            else if(!empty(get_post_meta($item_id,'_weight',true))) {
                $api_arr['items'][$i]['weight'] = get_post_meta($item_id,'_weight',true);
            }
            else {
                $api_arr['items'][$i]['weight'] = '';
            }
            // If less than 1
            //if (!empty($meta_values['_weight']['0']) && $api_arr['items'][$i]['weight'] < 1) {
            //    $api_arr['items'][$i]['weight'] = '1.0';
            //}

            if (!empty($var_meta_values['_height']['0'])) {
                $api_arr['items'][$i]['height'] = $var_meta_values['_height']['0'];
            }
            else if (!empty($meta_values['_height']['0']))   {
                $api_arr['items'][$i]['height'] = $meta_values['_height']['0'];
            }
            else if(!empty(get_post_meta($item_id,'_height',true))) {
                $api_arr['items'][$i]['height'] = get_post_meta($item_id,'_height',true);
            }
            else {
                $api_arr['items'][$i]['height'] = '';
            }

            if (!empty($var_meta_values['_width']['0'])) {
                $api_arr['items'][$i]['width'] = $var_meta_values['_width']['0'];
            }
            else if (!empty($meta_values['_width']['0'])) {
                $api_arr['items'][$i]['width'] = $meta_values['_width']['0'];
            }
            else if(!empty(get_post_meta($item_id,'_width',true))) {
                $api_arr['items'][$i]['width'] = get_post_meta($item_id,'_width',true);
            }
            else {
                $api_arr['items'][$i]['width'] = '';
            }

            if (!empty($var_meta_values['_length']['0'])) {
                $api_arr['items'][$i]['length'] = $var_meta_values['_length']['0'];
            }
            else if (!empty($meta_values['_length']['0'])) {
                $api_arr['items'][$i]['length'] = $meta_values['_length']['0'];
            }
            else if(!empty(get_post_meta($item_id,'_length',true))) {
                $api_arr['items'][$i]['length'] = get_post_meta($item_id,'_length',true);
            }
            else {
                $api_arr['items'][$i]['length'] = '';
            }

            $api_arr['items'][$i]['quantity'] = $cc['quantity'] != '' ? $cc['quantity'] : $cc['qty'] ;
            $api_arr['items'][$i]['description'] = 'carton';

            if(get_option('woocommerce_dimension_unit') != 'cm') {
                $api_arr['items'][$i]['height'] =  wc_get_dimension($api_arr['items'][$i]['height'], 'cm');
                $api_arr['items'][$i]['length'] =  wc_get_dimension($api_arr['items'][$i]['length'], 'cm');
                $api_arr['items'][$i]['width']  =  wc_get_dimension($api_arr['items'][$i]['width'], 'cm');
            }
            if(get_option('woocommerce_weight_unit') != 'kg') {
                $api_arr['items'][$i]['weight']  = wc_get_weight( $api_arr['items'][$i]['weight'], 'kg');
            }
			//Adds the variant SKU if one is set
			if(!empty($var_meta_values['_sku']['0'])) {
				$api_arr['items'][$i]['sku'] = $var_meta_values['_sku']['0'];
			} else {
				$api_arr['items'][$i]['sku'] = $product->get_sku();
			}
            $i++;
        } // end of foreach
        return $api_arr;
    }
}