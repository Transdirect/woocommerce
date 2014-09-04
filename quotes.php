<?php
session_start();
include_once('../../../wp-config.php');
include_once('../../../wp-load.php');
include_once('../../../wp-includes/wp-db.php');
global $woocommerce,$wpdb;

$_SESSION['price'] = '0';
if(!empty(WC()->session->chosen_shipping_methods[0]))
{
	//default settings
	$shipping_details = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name`='woocommerce_woocommerce_transdirect_settings'");
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
	//$explode_to = explode(',',$_POST['to_location']);
	$explode_to = explode(',','3000,MELBOURNE');
	
	
	$api_arr['sender']['country'] 		= 'AU';
	$api_arr['sender']['postcode'] 		= $explode_from[0];
	$api_arr['sender']['suburb'] 		= $explode_from[1];
	$api_arr['sender']['type'] 			= $defult_values['postcode_type'];
	$api_arr['receiver']['country'] 	= 'AU';
	$api_arr['receiver']['postcode'] 	= $explode_to[0];
	$api_arr['receiver']['suburb'] 	= $explode_to[1];
	$api_arr['receiver']['type']        = 'residential';
	//$api_arr['receiver']['type']        = $_REQUEST['to_type'];
	
	$api_arr['declared_value']        = '10000';
	
	
	$cart_content = WC()->cart->get_cart();
	$i = 0;
	foreach($cart_content as $cc)
	{
		$meta_values = get_post_meta($cc['data']->id);
		if(!empty($meta_values['_weight']['0'])) $api_arr['items'][$i]['weight'] = $meta_values['_weight']['0'];
		else $api_arr['items'][$i]['weight'] = $defult_values['weight'];
		
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
	//var_dump($api_arr);
	$ch = curl_init();
    //curl_setopt($ch, CURLOPT_URL, "https://www.staging.transdirect.com.au/api/bookings");
	curl_setopt($ch, CURLOPT_URL, "https://private-179c1-transdirect.apiary-mock.com/api/bookings");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_arr));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
	$response1 = curl_exec($ch);
	curl_close($ch);
	$shipping_quotes1 = json_decode(str_replace("''","0",$response1));
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
	if($shipping_quotes !='')
	{
		foreach($shipping_quotes as $k=>$sq)
		{
			// if($sq->total !='')
			// {
				if($defult_values['fied_error']=='yes' && $sq->total =='')$total_price =wc_format_decimal($defult_values['fied_error_price']);
				elseif($defult_values['fied_error']!='yes' && $sq->total =='')continue;
				else
				{
					$total_price = wc_format_decimal($sq->total+$extra_price);				
					if($defult_values['insurance_surcharge'] == 'yes')
					{
						$total_price = wc_format_decimal($total_price+$sq->insurance_fee);
					}
				}
				if($k == 'toll')$display_name = 'Toll';
				if($k == 'allied')$display_name = 'Allied Express';
				if($k == 'toll_priority')$display_name = 'Toll Priority';
				if($k == 'couriers_please')$display_name = 'Couriers Please';
				if($k == 'fastway')$display_name = 'Fastway';
				if($k == 'minfreight')$display_name = 'Mainfreight';
				if($k == 'northline')$display_name = 'Northline';
				
				if($k == 'toll' && in_array('Toll',$defult_values['Couriers']))
				{
					if($defult_values['quotes'] =='Display all Quotes')
					{
						$toll_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
						<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
						<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
						<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
					}
					
					if($defult_values['quotes'] =='Display Cheapest')
					{
						if(($prev_price!='' && $total_price < $prev_price) || $prev_price =='')
						{
							$toll_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
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
							$toll_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
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
						$toll_express_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
						<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
						<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
						<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
					}
					
					if($defult_values['quotes'] =='Display Cheapest')
					{
						if(($prev_price1!='' && $total_price < $prev_price1) || $prev_price1 =='')
						{
							$toll_express_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
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
							$toll_express_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
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
						$allied_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
						<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
						<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
						<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
					}
					
					if($defult_values['quotes'] =='Display Cheapest')
					{
						if(($prev_price2!='' && $total_price < $prev_price2) || $prev_price2 =='')
						{
							$allied_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
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
							$allied_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
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
						$couriers_please_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
						<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
						<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
						<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
					}
					
					if($defult_values['quotes'] =='Display Cheapest')
					{
						if(($prev_price3!='' && $total_price < $prev_price3) || $prev_price3 =='')
						{
							$couriers_please_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
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
							$couriers_please_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
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
						$fastway_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
						<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
						<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
						<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
					}
					
					if($defult_values['quotes'] =='Display Cheapest')
					{
						if(($prev_price4!='' && $total_price < $prev_price4) || $prev_price4 =='')
						{
							$fastway_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
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
							$fastway_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
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
						$minfreight_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
						<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
						<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
						<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
					}
					
					if($defult_values['quotes'] =='Display Cheapest')
					{
						if(($prev_price5!='' && $total_price < $prev_price5) || $prev_price5 =='')
						{
							$minfreight_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
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
							$minfreight_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
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
						$northline_html .='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
						<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
						<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
						<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
					}
					
					if($defult_values['quotes'] =='Display Cheapest')
					{
						if(($prev_price6!='' && $total_price < $prev_price6) || $prev_price6 =='')
						{
							$northline_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
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
							$northline_html ='<input type="radio" name="shipping_type_radio" class="shipping_type_radio" onclick="get_quote(\''.$k.'\');" id="'.$k.'" value="'.$k.'" />'.get_woocommerce_currency_symbol().$total_price.' - '.$display_name.'<br/>
							<input type="hidden" name="'.$k.'_price" id="'.$k.'_price" value="'.$total_price.'" />
							<input type="hidden" name="'.$k.'_transit_time" id="'.$k.'_transit_time" value="'.$sq->transit_time.'" />
							<input type="hidden" name="'.$k.'_service_type" id="'.$k.'_service_type" value="'.$sq->service_type.'" />';
						}
						$prev_price6 = $total_price;
						$prev_ship_time6 = $ship_time;
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
	$html = $toll_html.$toll_express_html.$allied_html.$couriers_please_html.$fastway_html.$minfreight_html.$northline_html;
	
	if($html =='' && $defult_values['fied_error']=='yes')
	{
		$_SESSION['price'] =  $defult_values['fied_error_price'];
		echo '5';
	}
echo $html;
}
		
		//var_dump($shipping_quotes);exit;
?>