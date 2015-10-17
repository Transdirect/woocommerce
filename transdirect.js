function validate(plugin_url) {        
	if (document.getElementById('to_location').value == '') {
        alert("Please select a delivery location.");     
		return false;
	} else if (document.getElementById('business').value == '' 
        || document.getElementById('residential').value == '') {
        alert("Please select a delivery type");
		return false;
	} else {
        jQuery("button[name='calc_shipping']").attr('disabled', 'disabled');
        jQuery('#trans_frm').addClass('load');
        
        jQuery.post(
            // See tip #1 for how we declare global javascript variables
            MyAjax.ajaxurl, {
                // here we declare the parameters to send along with the request
                // this means the following action hooks will be fired:
                // wp_ajax_nopriv_myajax-submit and wp_ajax_myajax-submit
                action              : 'myajax-submit',

                // other parameters can be added along with "action"
                'to_location'       : document.getElementById('to_location').value,
                'to_type'           : document.getElementById('business').checked ? 
                                      document.getElementById('business').value : document.getElementById('residential').value,
                'insurance_value'   : document.getElementById('insurance_value') ? 
                                      document.getElementById('insurance_value').value : 0
            }, function(response) {
                jQuery("button[name='calc_shipping']").removeAttr('disabled');                
                jQuery("#shipping_type").html('');
                jQuery("#shipping_type").append(response);
                jQuery("#shipping_type").show();
                jQuery('#trans_frm').removeClass('load');

            }
        );
	}
}


function get_quote(name) {
	var shipping_name = name;
	var shipping_price = jQuery("#" + name + "_price").val();

	var shipping_transit_time = jQuery("#" + name + "_transit_time").val();
	var shipping_service_type = jQuery("#" + name + "_service_type").val();
    jQuery('#trans_frm').addClass('load');
	jQuery.post(
        // see tip #1 for how we declare global javascript variables
        MyAjax.ajaxurl, {
            // here we declare the parameters to send along with the request
            // this means the following action hooks will be fired:
            // wp_ajax_nopriv_myajax-subcmit and wp_ajax_myajax-submit
            action : 'myajaxdb-submit',
                
            // other parameters can be added along with "action"
            'shipping_name' : shipping_name,
	        'shipping_price' : shipping_price,
	        'shipping_transit_time' : shipping_transit_time,
	        'shipping_service_type' : shipping_service_type

        },
        function(response) {
            jQuery('#trans_frm').removeClass('load');
            window.location.reload();   
        }
    );
}

function showCalc() {
    jQuery('.shipping_calculator').slideToggle();
}


