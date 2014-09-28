function validate(plugin_url)
{
	
	if(document.getElementById('to_location').value == '')
	{
		alert("Please select a to location");
		return false;
	}
	else if(document.getElementById('to_type').value == '')
	{
		alert("Please select a to location");
		return false;
	}	
	else
	{
			
		/*jQuery.post(  MyAjax.ajaxurl,{'to_location':document.getElementById('to_location').value,'to_type':document.getElementById('to_type').value}, function( data ) {			//alert(data);
			if(data == '5')window.location.reload();
			else
			{
				jQuery( "#shipping_type" ).html('');
				jQuery( "#shipping_type" ).append(data);
				//jQuery( "#trans_frm" ).hide();
				jQuery( "#shipping_type" ).show();
			}
		});*/
                
                jQuery.post(
                // see tip #1 for how we declare global javascript variables
                MyAjax.ajaxurl,
                {
                // here we declare the parameters to send along with the request
                // this means the following action hooks will be fired:
                // wp_ajax_nopriv_myajax-submit and wp_ajax_myajax-submit
                action : 'myajax-submit',
                
                // other parameters can be added along with "action"
                'to_location':document.getElementById('to_location').value,
                'to_type':document.getElementById('to_type').value
                },
                function( response ) {
                   // alert(response);
                 //  console.log(response);
                                 jQuery( "#shipping_type" ).html('');
				jQuery( "#shipping_type" ).append(response);
				jQuery( "#shipping_type" ).show();
                }
                );
                
             
	}
}

function get_quote(name)
{
	var shipping_name = name;
	var shipping_price = jQuery("#"+name+"_price").val();	
	var shipping_transit_time = jQuery("#"+name+"_transit_time").val();
	var shipping_service_type = jQuery("#"+name+"_service_type").val();
	//jQuery("#shipping_type").submit();
	/*jQuery.post( "<?php echo plugins_url( 'shipping_price.php' , __FILE__ ); ?>",{'shipping_name':shipping_name,'shipping_price':shipping_price,'shipping_transit_time':shipping_transit_time,'shipping_service_type':shipping_service_type}, function( data ) {
			window.location.reload();
			
		});*/
	jQuery.post(
                // see tip #1 for how we declare global javascript variables
                MyAjax.ajaxurl,
                {
                // here we declare the parameters to send along with the request
                // this means the following action hooks will be fired:
                // wp_ajax_nopriv_myajax-submit and wp_ajax_myajax-submit
                action : 'myajaxdb-submit',
                
                // other parameters can be added along with "action"
               'shipping_name':shipping_name,
	       'shipping_price':shipping_price,
	       'shipping_transit_time':shipping_transit_time,
	       'shipping_service_type':shipping_service_type
                },
                function( response ) {
                   window.location.reload();
                }
                );
}

