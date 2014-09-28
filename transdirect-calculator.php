<?php
/**
 * Shipping Transdrict Calculator
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.8
 */
 
 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce,$wpdb;
?>

<style>
.shipping-calculator-form1 { position:relative;}
#autocomplete-div { background:#FFFFFF; border: 1px solid #EDEDED; border-radius: 3px 3px 3px 3px; display: none; height: 200px;  margin: -5px 0 0 1px; overflow: auto; padding: 5px; position: absolute;  width: 189px;   z-index: 99;}
#autocomplete-div ul li {padding:0 !important;margin:0 !important; text-indent:0 !important;list-style: none;cursor:pointer;}
#autocomplete-div ul li:hover {background:#ededed;list-style: none;}
#trans_frm { right: 0;width:305px; text-indent:0; margin-left:-17px; padding:5px; border:1px solid #ededed;margin-top: -25px;background-color:#FFFFFF;position:absolute;z-index:1000;}
#trans_frm h4 {margin:0 0 0 0 !important;}
#shipping_type {border-top:1px solid #ededed; padding-top:10px; margin-top:10px;}
p.form-row-wide {margin:5px 0 !important;}
p.form-row-wide input[type="text"] {width:202px;}
.woocommerce table.shop_table tfoot td, .woocommerce table.shop_table tfoot th, .woocommerce-page table.shop_table tfoot td, .woocommerce-page table.shop_table tfoot th {font-weight:normal;}
.woocommerce .cart-collaterals, .woocommerce-page .cart-collaterals { position:relative; }
.loadinggif 
{
   background:
     url('<?php echo site_url(); ?>/wp-content/plugins/transdirect-shipping/ajax-loader.gif')
     no-repeat
     right center;
}
</style>

<?php
//var_dump(count(WC()->cart->get_cart()));exit;
	// echo '<pre>';
	  // $wer = new WC_Shipping_Method();
	 // $wer->add_rate('10');
	  // $_session['k'] = 0;
	  // if($_session['k']==0)
	  // {
		// $_session['k'] = 1;
		// WC()->session->shipping_total = WC()->session->shipping_total + 10;
	  // }
	  // var_dump(WC()->session->shipping_total);
	  // var_dump($_SESSION['k']);
	  // if(!isset($_SESSION['k']))
	  // {
	  // echo 'aaa';
	  // WC()->shipping->packages[0]['rates']['woocommerce_transdirect']->cost = 10;
	  // }
	  // var_dump(WC()->shipping->packages[0]['rates']['woocommerce_transdirect']->cost);
	// var_dump(WC()->shipping->packages[0]['rates']);exit;
	
	 // WC()->session->chosen_shipping_methods[0]
	// var_dump(WC()->session->subtotal);
	
	// exit;
// $ch = curl_init();
// curl_setopt($ch, CURLOPT_URL, "https://www.transdirect.com.au/api/locations/search?q=w");
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
// curl_setopt($ch, CURLOPT_HEADER, FALSE);
// $response_location = curl_exec($ch);
// curl_close($ch);

// $locations = json_decode($response_location);


if(!empty($_POST['shipping_variation']))	
{
	$shipping_details = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name`='woocommerce_".WC()->session->chosen_shipping_methods[0]."_settings'");
	$defult_values = unserialize($shipping_details[0]->option_value);

	$shipping_type = $_POST['shipping_type_radio'];
	$price 		   = $_POST[$shipping_type.'_price'];
	$transit_time  = $_POST[$shipping_type.'_transit_time'];
	$service_type  = $_POST[$shipping_type.'_service_type'];
	//WC()->shipping->packages[0]['rates'][WC()->session->chosen_shipping_methods[0]]['cost'] = $price;
	//echo WC()->cart->get_cart_subtotal();exit;
	
	//var_dump($shipping_type);exit;
	if($defult_values['Surcharge'] == 'yes')
	{
		$_SESSION['price'] =  $price+$defult_values['Surcharge_price'];
	}
	else
	{
		$_SESSION['price'] =  $price;
	}
	
}

if(isset($_SESSION['price']) && WC()->session->chosen_shipping_methods[0] =='woocommerce_transdirect')
{
	$price = $_SESSION['price'];
	WC()->shipping->shipping_total = $price;
	WC()->cart->total = WC()->cart->subtotal + $price;
	WC()->session->shipping_total = $price;
	WC()->session->total = WC()->session->subtotal + $price;
	
	// WC()->session->set( 'shipping_total', $price );
	// WC()->session->set( 'total', WC()->cart->total );
	 WC()->cart->add_fee( __('Shipping Cost', 'woocommerce'), $price );
}

if(!empty($_POST['to_location']))
{
	$_SESSION['price'] = '0';
	if(!empty(WC()->session->chosen_shipping_methods[0]))
	{
		//default settings
		$shipping_details = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name`='woocommerce_".WC()->session->chosen_shipping_methods[0]."_settings'");
		$defult_values = unserialize($shipping_details[0]->option_value);
		
		$api_arr = '';
		$explode_from = explode(',',$defult_values['postcode']);
		$explode_to = explode(',',$_POST['to_location']);
		
		
		$api_arr['sender']['country'] 		= 'AU';
		$api_arr['sender']['postcode'] 		= $explode_from[0];
		$api_arr['sender']['suburb'] 		= $explode_from[1];
		$api_arr['sender']['type'] 			= $_POST['to_type'];
		$api_arr['receiver']['country'] 	= 'AU';
		$api_arr['receiver']['postcode'] 	= $explode_to[0];
		$api_arr['receiver']['suburb'] 	= $explode_to[1];
		$api_arr['receiver']['type']        = $_POST['to_type'];
		
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
		curl_setopt($ch, CURLOPT_URL, "https://www.transdirect.com.au/api/bookings");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($api_arr));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$response1 = curl_exec($ch);
		curl_close($ch);
		$shipping_quotes1 = json_decode(str_replace("''","0",$response1));
		$shipping_quotes = $shipping_quotes1->quotes;
		
		//var_dump($shipping_quotes);exit;		
		
	}	
	 
}

// if ( get_option( 'woocommerce_enable_shipping_calc' ) === 'no' || ! WC()->cart->needs_shipping() )
	// return;

if(WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect')
{

	if(!isset($_SESSION['price']) || $_SESSION['price'] =='' || $_SESSION['price'] ==0)
	{
	?>
	<style>
	#place_order{
	display:none !important;
	}
	</style>
	<script>
	jQuery('#place_order').attr('disabled','disabled');
	</script>
	<?php
	}

?>

<script>
function hideContent()
{
jQuery( "#autocomplete-div" ).html('');
jQuery( "#autocomplete-div" ).hide();
}
	
jQuery( document ).ready(function() {	
	//jQuery.ajaxSetup({async:false});
	
	/* jQuery("#shipping_method_0_woocommerce_transdirect").on('click',function(){
		alert('fdgdfgd');
		});*/
	
   jQuery("#trans_frm").show();
   
   // jQuery(".shipping_type_radio").on("click",function(){
   // alert('hii');
		
   // });
   
   // jQuery("#auto_complete li").on("click",function(){
   // alert('hi');
   // });
	/*jQuery( "#to_location" ).on('blur',function(e) 
	{
		hideContent();	
	});*/
	jQuery('body').click( function() {
	jQuery('#autocomplete-div').hide('');
	jQuery('#dynamic_content').hide('');
	
	});
	var latestRequestNumber = 0;
	var globalTimeout = null;  
	jQuery('#to_location').keyup(function(){
	 /*if(globalTimeout != null) clearTimeout(globalTimeout);
	 jQuery('#to_location').addClass('loadinggif');
	 globalTimeout =setTimeout(toFunc,200);
	 latestRequestNumber++;
	 toFunc(latestRequestNumber);*/
	 
	    var key_val = jQuery( "#to_location" ).val();
		   
	    var position = jQuery( "#to_location" ).position();		
	    var html = '';
	   // jQuery( "#autocomplete-div" ).html('');
	    //jQuery( "#autocomplete-div" ).hide();
	    
	 //  if (key_val!='') {
	      jQuery('#to_location').addClass('loadinggif');
	      if (key_val=='') {
	       key_val=0;
	      }
	   jQuery.getJSON( "<?php echo plugins_url( 'locations.php' , __FILE__ ); ?>",{'q':key_val, requestNumber: ++latestRequestNumber}, function( data ) {
			
		   if (data.requestNumber < latestRequestNumber){  return; }
		   if(data.locations!='' && key_val!='0')                                    
		   {
		    
		   jQuery.each(data.locations, function( index, value ) {	
					   html =html+'<li onclick="get_value(\''+value.postcode+'\',\''+value.locality+'\')">'+value.postcode+' , '+value.locality+'</li>';
				   });
		   
				   var main_content = '<ul id="auto_complete">'+html+'</ul>';
				   //console.log(main_content);
				   jQuery( "#loading-div" ).hide();
				   jQuery( "#autocomplete-div" ).show();
				   jQuery( "#autocomplete-div" ).html(main_content);
				   jQuery( "#autocomplete-div" ).css('left', position.left);
				   jQuery( "#autocomplete-div" ).css('top', parseInt(position.top)+38);
		   }
		   else{
		      jQuery( "#autocomplete-div" ).hide();
		   }
		   //jQuery("#to_location").prop('disabled',false);
		   jQuery('#to_location').removeClass('loadinggif');
				   
				   
		   
	   });
	  /* }
	   else{
	   jQuery('#to_location').removeClass('loadinggif');	
	   }*/
	
	});
});	
   
</script>

<?php
}
?>
<script>

function get_value(postcode,locality)
{
	 jQuery( "#to_location" ).val(postcode+','+locality);
	 jQuery( "#autocomplete-div" ).html('');
	 jQuery( "#autocomplete-div" ).hide();
}

function get_dynamic_value(field_id,locality)
{
	 jQuery("#"+field_id ).val(locality);
	jQuery( "#dynamic_content" ).remove();
}


</script>


	<script>
		
		jQuery( document ).ready(function() {
		  
			var latestRequestNumber1 = 0;	
			jQuery('#billing_city').keyup(function(){
				 jQuery('#billing_city').addClass('loadinggif');
				var key_val = jQuery( '#billing_city' ).val();
				 var key_id = jQuery( '#billing_city' ).attr('id');
				 var position = jQuery( '#billing_city' ).position();		
				 var html = '';
				 var content_type = '';
				 if (key_id == 'billing_city') {
					 content_type = 'text';
				 }
				 
				 if (key_val=='') {
					 jQuery('#billing_city').removeClass('loadinggif');
					 key_val = 0;
				 }
				 jQuery.getJSON( "<?php echo plugins_url( 'locations.php' , __FILE__ ); ?>",{'q':key_val,requestNumber: ++latestRequestNumber1}, function( data ) {
					 
					// if(jQuery( "#dynamic_content" ).length)jQuery( "#dynamic_content" ).remove();
				         if (data.requestNumber < latestRequestNumber1)  { return; }
					 if(data.locations!='' && key_val!='0')
					 {
					 jQuery( ".woocommerce-billing-fields" ).css('position', 'relative');
					 jQuery.each(data.locations, function( index, value ) {
						 if (content_type=='text') {
							 html =html+'<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.locality+'\')">'+value.locality+'</li>';
						 }
						 else{
							 html =html+'<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.postcode+'\')">'+value.postcode+'</li>';
							 
						 }
								 
							 });
					 var top_cal = parseInt(position.top)+40;
							 var main_content = '<div id="dynamic_content" style="cursor:pointer;overflow: auto;background-color:#FFFFFF;z-index:1000; position: absolute;  width: 189px;height: 200px; top:'+position.top_cal+'px;"><ul >'+html+'</ul></div>';
							 
							 //console.log(main_content);							
							 jQuery( "#"+key_id+"_field" ).append(main_content);							
							 //jQuery( "#autocomplete-div" ).css('top', parseInt(position.top)+38);
							  jQuery('#billing_city').removeClass('loadinggif');
					 }
					  else{
					     jQuery( "#dynamic_content" ).remove();
					      jQuery('#billing_city').removeClass('loadinggif');
					  }
							 
				 });
				 //jQuery('#billing_city').removeClass('loadinggif');
			});
			
			
			var latestRequestNumber2 = 0; 
			jQuery('#billing_state').keyup(function(){
			    jQuery('#billing_state').addClass('loadinggif');
				var key_val = jQuery( "#billing_state" ).val();
				 var key_id = jQuery( "#billing_state" ).attr('id');
				 var position = jQuery( "#billing_state" ).position();		
				 var html = '';
				 var content_type = '';
				 if (key_id == 'billing_state') {
					 content_type = 'text';
				 }
				 
				 if (key_val=='') {
					 jQuery('#billing_state').removeClass('loadinggif');
					 key_val =0;
				 }
				 
				 
				 jQuery.getJSON( "<?php echo plugins_url( 'locations.php' , __FILE__ ); ?>",{'q':key_val,requestNumber: ++latestRequestNumber2}, function( data ) {
					 
					 //if(jQuery( "#dynamic_content" ).length)jQuery( "#dynamic_content" ).remove();
				         if (data.requestNumber < latestRequestNumber2)  {return; }
					 if(data.locations!='' && key_val!=0)
					 {
					 jQuery( ".woocommerce-billing-fields" ).css('position', 'relative');
					 jQuery.each(data.locations, function( index, value ) {
						 if (content_type=='text') {
							 html =html+'<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.locality+'\')">'+value.locality+'</li>';
						 }
						 else{
							 html =html+'<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.postcode+'\')">'+value.postcode+'</li>';
							 
						 }
								 
							 });
					 var top_cal = parseInt(position.top)+40;
							 var main_content = '<div id="dynamic_content" style="cursor:pointer;overflow: auto;background-color:#FFFFFF;z-index:1000; position: absolute;  width: 189px;height: 200px; top:'+position.top_cal+'px;"><ul >'+html+'</ul></div>';
							 
							 //console.log(main_content);							
							 jQuery( "#"+key_id+"_field" ).append(main_content);							
							 //jQuery( "#autocomplete-div" ).css('top', parseInt(position.top)+38);
							 jQuery('#billing_state').removeClass('loadinggif'); 
					 }
					  else{
					     jQuery( "#dynamic_content" ).remove();
					      jQuery('#billing_state').removeClass('loadinggif');
					  }
							 
				 });
				 
			});
			
			var latestRequestNumber3 = 0;   
			jQuery('#billing_postcode').keyup(function(){
			    jQuery('#billing_postcode').addClass('loadinggif');
				var key_val = jQuery( "#billing_postcode" ).val();
				 var key_id = jQuery( "#billing_postcode" ).attr('id');
				 var position = jQuery( "#billing_postcode" ).position();		
				 var html = '';
				 var content_type = '';
				 if (key_id == 'billing_postcode') {
					 content_type = 'numeric';
				 }
				 if (key_val=='') {
					 jQuery('#billing_postcode').removeClass('loadinggif');
					 key_val =0;
				 }
				 
				 
				 jQuery.getJSON( "<?php echo plugins_url( 'locations.php' , __FILE__ ); ?>",{'q':key_val,requestNumber: ++latestRequestNumber3}, function( data ) {
					 
					 //if(jQuery( "#dynamic_content" ).length)jQuery( "#dynamic_content" ).remove();
					 if (data.requestNumber < latestRequestNumber3)  { return; }
					 if(data.locations!='' && key_val!=0)
					 {
					 jQuery( ".woocommerce-billing-fields" ).css('position', 'relative');
					 jQuery.each(data.locations, function( index, value ) {
						 if (content_type=='text') {
							 html =html+'<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.locality+'\')">'+value.locality+'</li>';
						 }
						 else{
							 html =html+'<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.postcode+'\')">'+value.postcode+' , '+value.locality+'</li>';
							 
						 }
								 
							 });
						 var top_cal = parseInt(position.top)+40;
							 var main_content = '<div id="dynamic_content" style="cursor:pointer;overflow: auto;background-color:#FFFFFF;z-index:1000; position: absolute;  width: 189px;height: 200px; top:'+position.top_cal+'px;"><ul >'+html+'</ul></div>';
							 
							 //console.log(main_content);							
							 jQuery( "#"+key_id+"_field" ).append(main_content);							
							 //jQuery( "#autocomplete-div" ).css('top', parseInt(position.top)+38);
							  jQuery('#billing_postcode').removeClass('loadinggif');
					 }
					 else{
					     jQuery( "#dynamic_content" ).remove();
					      jQuery('#billing_postcode').removeClass('loadinggif');
					  }
							 
				 });
				
			});
			
			
			var latestRequestNumber4 = 0;  
			jQuery('#shipping_city').keyup(function(){
			    jQuery('#shipping_city').addClass('loadinggif'); 
				 var key_val = jQuery( "#shipping_city" ).val();
				 var key_id = jQuery( "#shipping_city" ).attr('id');
				 var position = jQuery( "#shipping_city" ).position();		
				 var html = '';
				 var content_type = '';
				 if (key_id == 'shipping_city') {
					 content_type = 'text';
				 }
				 if (key_val=='') {
					 jQuery('#shipping_city').removeClass('loadinggif');
					 key_val = 0;
				 }
				 
				 
				 jQuery.getJSON( "<?php echo plugins_url( 'locations.php' , __FILE__ ); ?>",{'q':key_val,requestNumber: ++latestRequestNumber4}, function( data ) {
					 
					 if (data.requestNumber < latestRequestNumber4)  { return; }				
					 if(data.locations!='' && key_val!=0)
					 {
					 jQuery( ".woocommerce-shipping-fields" ).css('position', 'relative');
					 jQuery.each(data.locations, function( index, value ) {
						 if (content_type=='text') {
							 html =html+'<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.locality+'\')">'+value.locality+'</li>';
						 }
						 else{
							 html =html+'<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.postcode+'\')">'+value.postcode+'</li>';
							 
						 }
								 
							 });
					 var top_cal = parseInt(position.top)+40;
							 var main_content = '<div id="dynamic_content" style="cursor:pointer;overflow: auto;background-color:#FFFFFF;z-index:1000; position: absolute;  width: 189px;height: 200px; top:'+position.top_cal+'px;"><ul >'+html+'</ul></div>';
							 
							 //console.log(main_content);							
							 jQuery( "#"+key_id+"_field" ).append(main_content);							
							 //jQuery( "#autocomplete-div" ).css('top', parseInt(position.top)+38);
							  jQuery('#shipping_city').removeClass('loadinggif'); 
					 }
					 else{
					     jQuery( "#dynamic_content" ).remove();
					      jQuery('#shipping_city').removeClass('loadinggif');
					  }
							 
				 });
				
			});
			
			var latestRequestNumber5 = 0;
			jQuery('#shipping_state').keyup(function(){
			     jQuery('#shipping_state').addClass('loadinggif');
				var key_val = jQuery( "#shipping_state" ).val();
				 var key_id = jQuery( "#shipping_state" ).attr('id');
				 var position = jQuery( "#shipping_state" ).position();		
				 var html = '';
				 var content_type = '';
				 if (key_id == 'shipping_state') {
					 content_type = 'text';
				 }
				 if (key_val=='') {
					 jQuery('#shipping_state').removeClass('loadinggif');
					 key_val = 0;
				 }
				 
				 
				 jQuery.getJSON( "<?php echo plugins_url( 'locations.php' , __FILE__ ); ?>",{'q':key_val,requestNumber: ++latestRequestNumber5}, function( data ) {
					 
					 if (data.requestNumber < latestRequestNumber5)  { return; }				
					 if(data.locations!='' && key_val!=0)
					 {
					 jQuery( ".woocommerce-shipping-fields" ).css('position', 'relative');
					 jQuery.each(data.locations, function( index, value ) {
						 if (content_type=='text') {
							 html =html+'<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.locality+'\')">'+value.locality+'</li>';
						 }
						 else{
							 html =html+'<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.postcode+'\')">'+value.postcode+'</li>';
							 
						 }
								 
							 });
					 var top_cal = parseInt(position.top)+40;
							 var main_content = '<div id="dynamic_content" style="cursor:pointer;overflow: auto;background-color:#FFFFFF;z-index:1000; position: absolute;  width: 189px;height: 200px; top:'+position.top_cal+'px;"><ul >'+html+'</ul></div>';
							 
							 //console.log(main_content);							
							 jQuery( "#"+key_id+"_field" ).append(main_content);							
							  jQuery('#shipping_state').removeClass('loadinggif');
					 }
					  else{
					     jQuery( "#dynamic_content" ).remove();
					      jQuery('#shipping_state').removeClass('loadinggif');
					  }
							 
				 });
				
			});
			
			var latestRequestNumber6 = 0;  
			jQuery('#shipping_postcode').keyup(function(){
				  jQuery('#shipping_postcode').addClass('loadinggif');
				var key_val = jQuery( "#shipping_postcode" ).val();
				 var key_id = jQuery( "#shipping_postcode" ).attr('id');
				 var position = jQuery( "#shipping_postcode" ).position();		
				 var html = '';
				 var content_type = '';
				 if (key_id == 'shipping_postcode') {
					 content_type = 'numeric';
				 }
				 if (key_val=='') {
					 jQuery('#shipping_postcode').removeClass('loadinggif');
					 key_val = 0;
				 }
				 
				 jQuery.getJSON( "<?php echo plugins_url( 'locations.php' , __FILE__ ); ?>",{'q':key_val,requestNumber: ++latestRequestNumber6}, function( data ) {
					 
					 if (data.requestNumber < latestRequestNumber6)  { return; }						
					 if(data.locations!='' && key_val!=0)
					 {
					 jQuery( ".woocommerce-shipping-fields" ).css('position', 'relative');
					 jQuery.each(data.locations, function( index, value ) {
						 if (content_type=='text') {
							 html =html+'<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.locality+'\')">'+value.locality+'</li>';
						 }
						 else{
							 html =html+'<li style="list-style: none;margin:0;padding:0;" onclick="get_dynamic_value(\''+key_id+'\',\''+value.postcode+'\')">'+value.postcode+' , '+value.locality+'</li>';
							 
						 }
								 
							 });
						 var top_cal = parseInt(position.top)+40;
							 var main_content = '<div id="dynamic_content" style="cursor:pointer;overflow: auto;background-color:#FFFFFF;z-index:1000; position: absolute;  width: 189px;height: 200px; top:'+position.top_cal+'px;"><ul >'+html+'</ul></div>';
							 
							 //console.log(main_content);							
							 jQuery( "#"+key_id+"_field" ).append(main_content);							
							  jQuery('#shipping_postcode').removeClass('loadinggif');
					 }
					  else{
					     jQuery( "#dynamic_content" ).remove();
					      jQuery('#shipping_postcode').removeClass('loadinggif');
					  }
							 
				 });
				
			});
			
			
		});
		
		
		
	</script>

<?php
//var_dump($_SESSION['price']);
if(WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect' && !isset($_SESSION['price']))
{
?>
<div class="shipping_calculator" id="trans_frm">

	<h4><?php _e( 'Get a Shipping Estimate', 'woocommerce' ); ?></h4>

	<section class="shipping-calculator-form1">
		
		
		
		<p class="form-row form-row-wide" >
			
			
			<input type="text" name="to_location" id="to_location" value="" placeholder="Delivery Location" /><br/><span id="loading-div" style="display:none;"></span>
			<div id="autocomplete-div"></div>
		</p>
		<p class="form-row form-row-wide">
			<input type="radio" name="to_type" id="to_type" value="Commercial" checked /> Commercial
			<input type="radio" name="to_type" id="to_type" value=" Residential" /> Residential									        
										        
 		</p>	

		<p><button type="button" name="calc_shipping" value="1" class="button" onclick="javascript:validate('<?php echo plugins_url( 'quotes.php' , __FILE__ ); ?>');"><?php _e( 'Get a quote', 'woocommerce' ); ?></button></p>

		<?php wp_nonce_field( 'woocommerce-cart' ); ?>
	</section>
	<div id="shipping_type" style="display:none;">
<input type="hidden" name="shipping_variation" id="shipping_variation" value="1" />

</div>
</div>
<?php

}

?>



