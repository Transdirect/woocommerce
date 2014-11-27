<?php
/**
 * Shipping Transdirect Calculator
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6
 */
 
if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $woocommerce, $wpdb; ?>

<style>
	.shipping-calculator-form1 {
		position:relative;
	}

	#autocomplete-div {
		background:#FFFFFF; 
		border: 1px solid #EDEDED; 
		border-radius: 3px 3px 3px 3px; 
		display: none; 
		height: auto; 
		max-height: 150px; 
		margin: -5px 0 0 1px; 
		overflow: auto; 
		padding: 5px; 
		position: absolute;  
		width: 189px;   
		z-index: 99;
	}

	#autocomplete-div ul li {
		padding:0 !important;
		margin:0 !important; 
		text-indent:0 !important;
		list-style: none;
		cursor:pointer;
	}

	#autocomplete-div ul {
		margin: 0 0 0px 0px !important;
	}

	#autocomplete-div ul li:hover {
		background:#ededed;
		list-style: none;
	}

	#trans_frm {
		right: 0;
		width:305px; 
		text-indent:0; 
		padding:5px;
		margin-bottom:20px; 
		border:1px solid #ededed;
		margin-top: 0px;
		background-color:#FFFFFF;
		position:relative;
	}

	#trans_frm h4 {
		margin:0 0 0 0 !important;
	}

	#shipping_type {
		border-top:1px solid #ededed; 
		padding-top:10px; 
		margin-top:10px; 
		text-align:left;
	}
	
	p.form-row-wide, p.form-row-small {
		margin:5px 0 !important;
	}

	p.form-row-wide input[type="text"] {
		width:202px;
	}

	p.form-row-small input[type="text"] {
		width:135px; 
		text-align:right
	}

	.woocommerce table.shop_table tfoot td, 
	.woocommerce table.shop_table tfoot th, 
	.woocommerce-page table.shop_table tfoot td, 
	.woocommerce-page table.shop_table tfoot th {
		font-weight:normal;
	}

	.woocommerce .cart-collaterals, 
	.woocommerce-page .cart-collaterals {
		position:relative; 
	}

	.loadinggif {
		background:url('<?php echo site_url(); ?>/wp-content/plugins/transdirect-shipping/ajax-loader.gif') no-repeat right center;
	}

	span.close-option {
		top: -15px;
		right: 5px;
		position: relative;
	}

	.load { 
		z-index: 1000; 
		border: none; 
		margin: 0px; 
		padding: 0px; 
		width: 100%;
		height: 100%; 
		top: 0px; 
		left: 0px; 
		opacity: 0.6;
		cursor: wait;
		position: absolute; 
		background: url(http://localhost/wordpress-sample/wp-content/plugins/woocommerce/assets/images/ajax-loader@2x.gif) 50% 50% / 16px 16px no-repeat rgb(255, 255, 255);
	}
</style>

<?php if (!empty($_POST['shipping_variation']))	{

	$shipping_details = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name`='woocommerce_".WC()->session->chosen_shipping_methods[0]."_settings'");

	$default_values = unserialize($shipping_details[0]->option_value);

	$shipping_type = $_POST['shipping_type_radio'];
	$price 		   = $_POST[$shipping_type.'_price'];
	$transit_time  = $_POST[$shipping_type.'_transit_time'];
	$service_type  = $_POST[$shipping_type.'_service_type'];

	if ($default_values['Surcharge'] == 'yes') {
		$_SESSION['price'] =  $price+ $default_values['Surcharge_price'];
	} else {
		$_SESSION['price'] =  $price;
	}
}

if (isset($_SESSION['price']) && WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect') {

	$price = $_SESSION['price'];

	WC()->shipping->shipping_total = $price;
	WC()->cart->total = WC()->cart->subtotal + $price;
	WC()->session->shipping_total = $price;
	WC()->session->total = WC()->session->subtotal + $price;
	
	WC()->cart->add_fee(__('Shipping Cost', 'woocommerce'), $price);
}

if (!empty($_POST['to_location'])) {

	$_SESSION['price'] = '0';

	if(!empty(WC()->session->chosen_shipping_methods[0]))
	{
		// Default settings
		$shipping_details = $wpdb->get_results("SELECT `option_value` FROM `wp_options` WHERE `option_name`='woocommerce_".WC()->session->chosen_shipping_methods[0]."_settings'");
		$default_values = unserialize($shipping_details[0]->option_value);
		
		$api_arr = '';
		$explode_from = explode(',',$default_values['postcode']);
		$explode_to = explode(',',$_POST['to_location']);
		
		$api_arr['sender']['country']   = 'AU';
		$api_arr['sender']['postcode'] 	= $explode_from[0];
		$api_arr['sender']['suburb'] 	= $explode_from[1];
		$api_arr['sender']['type'] 		= $_POST['to_type'];
		$api_arr['receiver']['country'] = 'AU';
		$api_arr['receiver']['postcode']= $explode_to[0];
		$api_arr['receiver']['suburb'] 	= $explode_to[1];
		$api_arr['receiver']['type']    = $_POST['to_type'];
		
		$cart_content = WC()->cart->get_cart();
		$i = 0;

		foreach($cart_content as $cc) {

			if (!empty($cc['weight']))
				$api_arr['items'][$i]['weight'] = $cc['weight'];
			else
				$api_arr['items'][$i]['weight'] = $default_values['weight'];
			
			if (!empty($cc['height']))
				$api_arr['items'][$i]['height'] = $cc['height'];
			else
				$api_arr['items'][$i]['height'] = $default_values['height'];
			
			if (!empty($cc['width']))
				$api_arr['items'][$i]['width'] = $cc['width'];
			else
				$api_arr['items'][$i]['width'] = $default_values['width'];
			
			if (!empty($cc['length']))
				$api_arr['items'][$i]['length'] = $cc['length'];
			else
				$api_arr['items'][$i]['length'] = $default_values['length'];
			
			$api_arr['items'][$i]['quantity'] = $cc['quantity'];
			$api_arr['items'][$i]['description'] = 'carton';

			$i++;
		}
	
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www.transdirect.com.au/api/bookings");
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
	}
}

if (WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect') {

	if(!isset($_SESSION['price']) || $_SESSION['price'] == '' || $_SESSION['price'] == 0) { ?>

		<style> #place_order {display : none !important;} </style>

		<script>
			jQuery('#place_order').attr('disabled','disabled');
		</script>

	<?php } ?>

	<style> form.shipping_calculator {display : none !important;} </style>

	<script>



		function hideContent() {
			jQuery("#autocomplete-div").html('');
			jQuery("#autocomplete-div").hide();
		}
	
		jQuery(document).ready(function() {
			// console.log('show');
			
			jQuery("#trans_frm").show();

			jQuery('body').click(function() {

				jQuery('#autocomplete-div').hide('');
				jQuery('#dynamic_content').hide('');

			});

			var latestRequestNumber = 0;
			var globalTimeout = null;

			jQuery('#to_location').keyup(function() {
	            var key_val = jQuery("#to_location").val();
				var position = jQuery("#to_location").position();
	            var html = '';

	            jQuery('#to_location').addClass('loadinggif');

				if (key_val=='') {
	                key_val=0;
	            }


				jQuery.getJSON("<?php echo plugins_url('locations.php' , __FILE__ ); ?>", {'q':key_val, requestNumber: 
					++latestRequestNumber}, function(data) {
					
		            if (data.requestNumber < latestRequestNumber) {
		            	return;
		            }

					if (data.locations != '' && key_val != '0') {
		                jQuery.each(data.locations, function(index, value ) {
					        html = html+'<li onclick="get_value(\''+value.postcode+'\',\''+value.locality+'\')">'+value.postcode+', '+value.locality+'</li>';
				        });
		   
				        var main_content = '<ul id="auto_complete">'+html+'</ul>';

						jQuery("#loading-div").hide();
				        jQuery("#autocomplete-div").show();
				        jQuery("#autocomplete-div").html(main_content);
				        jQuery("#autocomplete-div").css('left', position.left);
				        jQuery("#autocomplete-div").css('top', parseInt(position.top) + 38);
		            } else {
		                // jQuery( "#autocomplete-div" ).hide();
		                 html = html+'<li>No Results Found</li>';
		                 var main_content = '<ul id="auto_complete">'+html+'</ul>';
		               
		                jQuery("#autocomplete-div").show();
				        jQuery("#autocomplete-div").html(main_content);
				        jQuery("#autocomplete-div").css('left', position.left);
				        jQuery("#autocomplete-div").css('top', parseInt(position.top) + 38);
				        jQuery("#autocomplete-div").css('overflow-y','hidden');

				        jQuery('#to_location').removeClass('loadinggif');
		            }

					jQuery('#to_location').removeClass('loadinggif');
	            });
			});
		});
	</script>
<?php } ?>


<script>

	function get_value(postcode, locality) {
	    jQuery("#to_location").val(postcode + ',' + locality);
		jQuery("#autocomplete-div").html('');
	    jQuery( "#autocomplete-div" ).hide();
	}

	function get_dynamic_value(field_id, locality) {
	    jQuery("#" + field_id).val(locality);
		jQuery("#dynamic_content").remove();
	}


	jQuery(document).ready(function() {
	// 	var latestRequestNumber1 = 0;

	// 	jQuery('#billing_city').keyup(function() {

	// 		jQuery('#billing_city').addClass('loadinggif');

	// 		var key_val = jQuery('#billing_city').val();
	// 		var key_id = jQuery('#billing_city').attr('id');
	// 		var position = jQuery('#billing_city').position();

	// 		var html = '';
	// 		var content_type = '';

	// 		if (key_id == 'billing_city') {
	// 			content_type = 'text';
	// 		}

	// 		if (key_val == '') {
	// 			jQuery('#billing_city').removeClass('loadinggif');
	// 			key_val = 0;
	// 		}

	// 		jQuery.getJSON("<?php echo plugins_url('locations.php' ,__FILE__); ?>",{'q':key_val,requestNumber: ++latestRequestNumber1}, function(data) {

	// 			if (data.requestNumber < latestRequestNumber1) {
	// 				return;
	// 			}

	// 			if(data.locations != '' && key_val != '0') {
	// 				jQuery(".woocommerce-billing-fields").css('position', 'relative');

	// 				jQuery.each(data.locations, function(index, value) {
	// 					if (content_type == 'text') {
	// 						html = html + '<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.locality+'\')">'+value.locality+'</li>';
	// 					} else {
	// 						html = html + '<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.postcode+'\')">'+value.postcode+'</li>';
	// 					}
	// 				});

	// 				var top_cal = parseInt(position.top) + 40;

	// 				var main_content = '<div id="dynamic_content" style="cursor:pointer;overflow: auto;background-color:#FFFFFF;z-index:1000; position: absolute;  width: 189px;height: 200px; top:'+position.top_cal+'px;"><ul >'+html+'</ul></div>';

	// 				jQuery("#"+key_id+"_field").append(main_content);
	// 				jQuery('#billing_city').removeClass('loadinggif');
	// 			} else {
	// 				jQuery("#dynamic_content").remove();
	// 				jQuery('#billing_city').removeClass('loadinggif');
	// 			}
	// 		});
	// 	});

		// var latestRequestNumber2 = 0;

		// jQuery('#billing_state').keyup(function() {
		// 	jQuery('#billing_state').addClass('loadinggif');

		// 	var key_val = jQuery("#billing_state").val();
		// 	var key_id = jQuery("#billing_state").attr('id');
		// 	var position = jQuery("#billing_state").position();

		// 	var html = '';
		// 	var content_type = '';

		// 	if (key_id == 'billing_state') {
		// 		content_type = 'text';
		// 	}
				 
		// 	if (key_val == '') {
		// 		jQuery('#billing_state').removeClass('loadinggif');
		// 		key_val =0;
		// 	}

		// 	jQuery.getJSON("<?php echo plugins_url('locations.php', __FILE__); ?>",{'q':key_val,requestNumber: ++latestRequestNumber2}, function(data) {
					 
		// 		if (data.requestNumber < latestRequestNumber2) {return;}

		// 		if(data.locations!='' && key_val!=0) {
		// 			jQuery(".woocommerce-billing-fields").css('position', 'relative');

		// 			jQuery.each(data.locations, function(index, value) {
		// 				if (content_type=='text') {
		// 					html = html + '<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.locality+'\')">'+value.locality+'</li>';
		// 				} else {
		// 					html = html + '<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.postcode+'\')">'+value.postcode+'</li>';
		// 				}
		// 			});

		// 			var top_cal = parseInt(position.top) + 40;

		// 			var main_content = '<div id="dynamic_content" style="cursor:pointer;overflow: auto;background-color:#FFFFFF;z-index:1000; position: absolute;  width: 189px;height: 200px; top:'+position.top_cal+'px;"><ul >'+html+'</ul></div>';

		// 			jQuery("#" + key_id + "_field").append(main_content);
		// 			jQuery('#billing_state').removeClass('loadinggif');
		// 		} else {
		// 			jQuery("#dynamic_content").remove();
		// 			jQuery('#billing_state').removeClass('loadinggif');
		// 		}
		// 	});
		// });
			
		// var latestRequestNumber3 = 0;

		// jQuery('#billing_postcode').keyup(function() {
		// 	jQuery('#billing_postcode').addClass('loadinggif');

		// 	var key_val = jQuery("#billing_postcode").val();
		// 	var key_id = jQuery("#billing_postcode").attr('id');
		// 	var position = jQuery("#billing_postcode").position();

		// 	var html = '';
		// 	var content_type = '';

		// 	if (key_id == 'billing_postcode') {
		// 		content_type = 'numeric';
		// 	}

		// 	if (key_val == '') {
		// 		jQuery('#billing_postcode').removeClass('loadinggif');
		// 		key_val = 0;
		// 	}

		// 	jQuery.getJSON("<?php echo plugins_url('locations.php' , __FILE__); ?>",{'q':key_val,requestNumber: ++latestRequestNumber3}, function(data) {
					 
		// 		if (data.requestNumber < latestRequestNumber3) {return;}

		// 		if (data.locations != '' && key_val != 0) {
		// 			jQuery(".woocommerce-billing-fields").css('position', 'relative');

		// 			jQuery.each(data.locations, function(index, value) {
		// 				 if (content_type=='text') {
		// 					 html = html + '<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.locality+'\')">'+value.locality+'</li>';
		// 				 } else {
		// 					 html = html + '<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.postcode+'\')">'+value.postcode+' , '+value.locality+'</li>';
		// 				 }
		// 			});

		// 			var top_cal = parseInt(position.top) + 40;
		// 			var main_content = '<div id="dynamic_content" style="cursor:pointer;overflow: auto;background-color:#FFFFFF;z-index:1000; position: absolute;  width: 189px;height: 200px; top:'+position.top_cal+'px;"><ul >'+html+'</ul></div>';
							 
		// 			jQuery("#"+key_id+"_field").append(main_content);
		// 			jQuery('#billing_postcode').removeClass('loadinggif');
		// 		} else {
		// 			jQuery("#dynamic_content").remove();
		// 			jQuery('#billing_postcode').removeClass('loadinggif');
		// 		}
		// 	});
		// });
			
		// var latestRequestNumber4 = 0;

		// jQuery('#shipping_city').keyup(function() {
		// 	jQuery('#shipping_city').addClass('loadinggif');

		// 	var key_val = jQuery("#shipping_city").val();
		// 	var key_id = jQuery("#shipping_city").attr('id');
		// 	var position = jQuery( "#shipping_city" ).position();

		// 	var html = '';
		// 	var content_type = '';

		// 	if (key_id == 'shipping_city') {
		// 		content_type = 'text';
		// 	}

		// 	if (key_val == '') {
		// 		jQuery('#shipping_city').removeClass('loadinggif');
		// 		key_val = 0;
		// 	}
				 
		// 	jQuery.getJSON("<?php echo plugins_url('locations.php' ,__FILE__ ); ?>",{'q':key_val,requestNumber: ++latestRequestNumber4}, function(data) {
					 
		// 		if (data.requestNumber < latestRequestNumber4) {return;}

		// 		if (data.locations != '' && key_val != 0) {
		// 			jQuery( ".woocommerce-shipping-fields" ).css('position', 'relative');

		// 			jQuery.each(data.locations, function(index, value) {
		// 				if (content_type=='text') {
		// 					html = html + '<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.locality+'\')">'+value.locality+'</li>';
		// 				} else {
		// 					html = html + '<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.postcode+'\')">'+value.postcode+'</li>';
		// 				}
		// 			});

		// 			var top_cal = parseInt(position.top) + 40;
		// 			var main_content = '<div id="dynamic_content" style="cursor:pointer;overflow: auto;background-color:#FFFFFF;z-index:1000; position: absolute;  width: 189px;height: 200px; top:'+position.top_cal+'px;"><ul >'+html+'</ul></div>';

		// 			jQuery("#" + key_id + "_field").append(main_content);
		// 			jQuery('#shipping_city').removeClass('loadinggif');
		// 		} else {
		// 			jQuery("#dynamic_content").remove();
		// 			jQuery('#shipping_city').removeClass('loadinggif');
		// 		}
		// 	});
		// });
			
		// var latestRequestNumber5 = 0;

		// jQuery('#shipping_state').keyup(function() {
		//     jQuery('#shipping_state').addClass('loadinggif');

		// 	var key_val = jQuery( "#shipping_state" ).val();
		// 	var key_id = jQuery( "#shipping_state" ).attr('id');
		// 	var position = jQuery( "#shipping_state" ).position();

		// 	var html = '';
		// 	var content_type = '';
		// 	if (key_id == 'shipping_state') {
		// 		 content_type = 'text';
		// 	}

		// 	if (key_val == '') {
		// 		jQuery('#shipping_state').removeClass('loadinggif');
		// 		key_val = 0;
		// 	}

		// 	jQuery.getJSON("<?php echo plugins_url('locations.php' , __FILE__); ?>",{'q':key_val,requestNumber: ++latestRequestNumber5}, function(data) {
		// 		if (data.requestNumber < latestRequestNumber5)  {return;}

		// 		if(data.locations != '' && key_val != 0) {
		// 			jQuery(".woocommerce-shipping-fields").css('position', 'relative');

		// 			jQuery.each(data.locations, function(index, value) {
		// 			    if (content_type == 'text') {
		// 				    html = html + '<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.locality+'\')">'+value.locality+'</li>';
		// 			    } else {
		// 				    html = html + '<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.postcode+'\')">'+value.postcode+'</li>';
		// 			    }
		// 			});

		// 			var top_cal = parseInt(position.top) + 40;

		// 			var main_content = '<div id="dynamic_content" style="cursor:pointer;overflow: auto;background-color:#FFFFFF;z-index:1000; position: absolute;  width: 189px;height: 200px; top:'+position.top_cal+'px;"><ul >'+html+'</ul></div>';

		// 			jQuery("#" + key_id + "_field").append(main_content);
		// 			jQuery('#shipping_state').removeClass('loadinggif');
		// 		} else {
		// 		    jQuery("#dynamic_content").remove();
		// 		    jQuery('#shipping_state').removeClass('loadinggif');
		// 		}
		// 	});
		// });
			
		// var latestRequestNumber6 = 0;

		// jQuery('#shipping_postcode').keyup(function() {
		// 	jQuery('#shipping_postcode').addClass('loadinggif');

		// 	var key_val = jQuery("#shipping_postcode").val();
		// 	var key_id = jQuery("#shipping_postcode").attr('id');
		// 	var position = jQuery("#shipping_postcode").position();

		// 	var html = '';
		// 	var content_type = '';

		// 	if (key_id == 'shipping_postcode') {
		// 		content_type = 'numeric';
		// 	}

		// 	if (key_val == '') {
		// 		jQuery('#shipping_postcode').removeClass('loadinggif');
		// 		key_val = 0;
		// 	}

		// 	jQuery.getJSON("<?php echo plugins_url('locations.php' , __FILE__); ?>",{'q':key_val,requestNumber: ++latestRequestNumber6}, function(data) {
		// 		if (data.requestNumber < latestRequestNumber6)  {return;}

		// 		if(data.locations != '' && key_val != 0) {
		// 		    jQuery(".woocommerce-shipping-fields").css('position', 'relative');

		// 			jQuery.each(data.locations, function(index, value) {
		// 			    if (content_type=='text') {
		// 				    html = html + '<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.locality+'\')">'+value.locality+'</li>';
		// 			    } else {
		// 				    html = html + '<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.postcode+'\')">'+value.postcode+' , '+value.locality+'</li>';
		// 			    }
		// 			});

		// 			var top_cal = parseInt(position.top) + 40;
		// 			var main_content = '<div id="dynamic_content" style="cursor:pointer;overflow: auto;background-color:#FFFFFF;z-index:1000; position: absolute;  width: 189px;height: 200px; top:'+position.top_cal+'px;"><ul >'+html+'</ul></div>';

		// 			jQuery("#" + key_id + "_field").append(main_content);
		// 			jQuery('#shipping_postcode').removeClass('loadinggif');
		// 		} else {
		// 		    jQuery("#dynamic_content").remove();
		// 		    jQuery('#shipping_postcode').removeClass('loadinggif');
		// 		}
		// 	});
		// });
	});
</script>


<?php if (WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect') {
	//if (WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect' && !isset($_SESSION['price'])) {
	// Default settings
		// var_dump(WC()->session);
	// var_dump(WC()->session->total);
	// var_dump(get_woocommerce_currency_symbol());
	$shipping_details = $wpdb
		->get_results("SELECT `option_value` FROM `wp_options` WHERE `option_name`='woocommerce_".WC()
		->session
		->chosen_shipping_methods[0]."_settings'");

	$default_values = unserialize($shipping_details[0]->option_value);
?>
	<div class="blockUI" style="display:none"></div>

	<div class="shipping_calculator" id="trans_frm">
		<h4><?php _e('Get a Shipping Estimate', 'woocommerce'); ?></h4>
		
		<section class="shipping-calculator-form1">

			<p class="form-row form-row-wide">
				<input type="text" name="to_location" id="to_location" value="" placeholder="Delivery Location"
				autocomplete="off"/>
				<br/>
				<span id="loading-div" style="display:none;"></span>
				<div id="autocomplete-div"></div>
			</p>

			<p class="form-row form-row-wide">
				<input type="radio" name="to_type" id="business" value="business" checked/> Commercial
				<input type="radio" name="to_type" id="residential" value="residential"/> Residential
			</p>

			<?php if ($default_values['insurance_surcharge'] == 'yes') { ?>
				<p class="form-row form-row-small">
					<?php //echo '<b>'.get_woocommerce_currency_symbol().''.number_format(WC()->session->total, 2).'</b>' ?>
					<input type="text" name="insurance_value" id="insurance_value" value="" placeholder="Declared Value"/>
				</p>
			<?php } ?>

			<p>
				<button type="button" name="calc_shipping" value="1" class="button" 
				onclick="javascript:validate('<?php echo plugins_url('quotes.php', __FILE__); ?>');">
					<?php _e('Get a quote', 'woocommerce'); ?>
				</button>
			</p>

			<?php wp_nonce_field('woocommerce-cart'); ?>

		</section>

		<div id="shipping_type" style="display:none;">
			<input type="hidden" name="shipping_variation" id="shipping_variation" value="1" />
		</div>

	</div>
<?php } ?>



