jQuery(document).ready(function(){
    getCountry();
    if(jQuery().select2){
        jQuery('select#billing_country').select2().on("select2-selecting", function(e) {
            if(!jQuery('#ship-to-different-address-checkbox').is(":checked")){
                setTimeout(function(){
                    if(jQuery('input.shipping_method').is("input[type='hidden']")) {
                        if(jQuery("input:hidden.shipping_method").val() == 'woocommerce_transdirect'){
                            jQuery('.tdCalc').show();
                            getCountry();
                        }
                    } else if(jQuery('input.shipping_method').is("input[type='radio']")) {
                        if(jQuery('li input:radio.shipping_method:checked').val() == 'woocommerce_transdirect') {
                            jQuery('.tdCalc').show();
                            getCountry();
                            if(jQuery('.session_price').val()){
                                jQuery('.sel-courier').show();

                            }   
                        }
                    }
                }, 2500);
            }
        });
    }

    if(jQuery().select2){
        jQuery('select#shipping_country').select2().on("select2-selecting", function(e) {
            setTimeout(function(){
                if(jQuery('input.shipping_method').is("input[type='hidden']")) {
                    if(jQuery("input:hidden.shipping_method").val() == 'woocommerce_transdirect'){
                        jQuery('.tdCalc').show();
                        getCountry();
                    } else if(jQuery('input.shipping_method').is("input[type='radio']")) {
                        if(jQuery('li input:radio.shipping_method:checked').val() == 'woocommerce_transdirect') {
                            jQuery('.tdCalc').show();
                            getCountry();
                            if(jQuery('.session_price').val()){
                                jQuery('.sel-courier').show();  
                            }   
                        }
                    }
                }
            }, 2500);
        });
    }    
});
//When update button of cart is clicked, reset Quotes
jQuery('input[name="update_cart"]').click(function(){
    setTimeout(function(){
        getCountry();
        jQuery('#to_postcode').hide();
    }, 3500);
});

jQuery(document).on('change', '#calc_shipping_country', function(){ 
    setTimeout(function(){
        getCountry();
        jQuery('#to_postcode').hide();
    }, 2500);
});


function validate(plugin_url) {
    var postcode = document.getElementById('to_postcode').value;
	if (document.getElementById('to_location').value == '') {
        alert("Please select a delivery location.");
		return false;
	} else if (document.getElementById('business').value == ''
        || document.getElementById('residential').value == '') {
        alert("Please select a delivery type");
		return false;
	} else if (jQuery("#to_postcode").css('display') != 'none' &&  postcode == '') {
        alert("Please enter postcode");
        return false;
    } else {
        jQuery("button[name='calc_shipping']").attr('disabled', 'disabled');
        // jQuery('.blockUI').show();/
        //jQuery('#trans_frm').addClass('blockOverlay');
        //jQuery('#trans_frm').addClass('load');
        ajaxindicatorstart('Getting Quote(s)');


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
                                      document.getElementById('insurance_value').value : 0,
                'country'           : document.getElementById('txt_country').value,
                'to_postcode'       : document.getElementById('to_postcode').value
            }, function(response) {
                jQuery("button[name='calc_shipping']").removeAttr('disabled');
                jQuery("#shipping_type").html('');
                jQuery("#shipping_type").append(response);
                jQuery("#shipping_type").show();
                //jQuery('#trans_frm').removeClass('load');
                // jQuery('.blockUI').hide();
                //jQuery('#trans_frm').removeClass('blockOverlay');
                ajaxindicatorstop();
            }
        );
	}
}


function get_quote(name) {
	var shipping_name = name;
	var shipping_price = jQuery("#" + name + "_price").val();
    var shipping_base = jQuery("#" + name + "_base").val();

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
	        'shipping_service_type' : shipping_service_type,
            'shipping_base'         : shipping_base,
            'location' : document.getElementById('to_location').value

        },
        function(response) {
            jQuery('#trans_frm').removeClass('load');
            resp = jQuery.parseJSON(response);
            if(resp){
                if(jQuery('input.shipping_method').is("input[type='radio']")) { 
                    if(jQuery('label[for="shipping_method_0_woocommerce_transdirect"] > strong').length){ 
                        jQuery('label[for="shipping_method_0_woocommerce_transdirect"] > strong').html(resp.currency + resp.courier_price);
                    } else {
                        label = jQuery('label[for="shipping_method_0_woocommerce_transdirect"]').text();
                        jQuery('label[for="shipping_method_0_woocommerce_transdirect"]').html(label + ' :<strong>'+ resp.currency + resp.courier_price +'</strong>');
                    }                       
                } else if(jQuery('input.shipping_method').is("input[type='hidden']")){  
                    if(jQuery('.shipping > td > strong').length){
                        jQuery('.shipping > td > strong').html(resp.currency + resp.courier_price); 
                    } else {
                        jQuery('#shipping_method_0').before('<strong>'+ resp.currency + resp.courier_price +'</strong>');
                    }           
                }
                jQuery('.order-total > td > strong >  span').html('<span class="woocommerce-Price-currencySymbol">'+ resp.currency +'</span>'+ resp.total);
                jQuery('.shipping_calculator').slideToggle();
                jQuery('.sel-courier').show();
                jQuery('.session_price').val(resp.courier_price);
                jQuery('.session_selected_courier').val(resp.shipping_name);
                jQuery('.courier-data').html(resp.shipping_name);
                jQuery('.price-data').html(" " + "<strong>" + resp.currency + resp.courier_price + "</strong>");
                jQuery( 'body' ).trigger( 'update_checkout' );
                jQuery("#shipping_type").hide();
                jQuery('.get_postcode').val(resp.postcode);
                jQuery('.get_location').val(resp.suburl);
                jQuery("#to_location").val('');

            }
            //window.location.reload();
        }
    );
}

function showCalc() {
    jQuery("#to_location").countrySelect("setCountry", 'Australia');
    jQuery("#to_location").val('');
    jQuery("#txt_country").val('');
    jQuery('.shipping_calculator').slideToggle();
}

function ajaxindicatorstart(text)
{
    if(jQuery('body').find('#resultLoading').attr('id') != 'resultLoading'){
    jQuery('body').append('<div id="resultLoading" style="display:none"><div><img src="'+ imageUrl +'" /><div>'+text+'</div></div><div class="bg"></div></div>');
    }

    jQuery('#resultLoading').css({
        'width':'100%',
        'height':'100%',
        'position':'fixed',
        'z-index':'10000000',
        'top':'0',
        'left':'0',
        'right':'0',
        'bottom':'0',
        'margin':'auto'
    });

    jQuery('#resultLoading .bg').css({
        'background':'#000000',
        'opacity':'0.7',
        'width':'100%',
        'height':'100%',
        'position':'absolute',
        'top':'0'
    });

    jQuery('#resultLoading>div:first').css({
        'width': '250px',
        'height':'75px',
        'text-align': 'center',
        'position': 'fixed',
        'top':'0',
        'left':'0',
        'right':'0',
        'bottom':'0',
        'margin':'auto',
        'font-size':'16px',
        'z-index':'10',
        'color':'#ffffff'
    });

    jQuery('#resultLoading .bg').height('100%');
    jQuery('#resultLoading').fadeIn(800);
    jQuery('body').css('cursor', 'wait');
}

function ajaxindicatorstop()
{
    jQuery('#resultLoading .bg').height('100%');
    jQuery('#resultLoading').fadeOut(800);
    jQuery('body').css('cursor', 'default');
}

// Get country list from td country table and set in country select plugin.
function getCountry(){
    setTimeout(function(){ 
        if(jQuery("#to_location").val('') == 'Australia'){
            jQuery("body #to_location").val('');
            jQuery("body #to_postcode").hide();
        }
        jQuery.getJSON(jQuery('#locationUrl').val(), {'isInternational' : 'yes'},function(data) {
        var data =jQuery.map(data, function(el) { return el });
        var results =[];
        for (var i = 0; i < data.length; i++) {
            if(data[i].name != 'Croatia')
            {
                results.push({ 
                    "iso2"     : data[i].code.toLowerCase(),
                    "name"     : data[i].name,
                    "id"       : data[i].id,
                    "postcode" : data[i].postcode_status
                });
            }
        }
        jQuery.fn.countrySelect.setCountryData(results);
        jQuery("#to_location").countrySelect({
            defaultCountry: 'au',
            preferredCountries: ['au', 'cn', 'jp', 'nz', 'sg', 'gb', 'us']
        });
        var allCountryData = jQuery.fn.countrySelect.getCountryData();
        jQuery(allCountryData).each(function( index ) {
          if(jQuery(this)[0].iso2 == 'au') {
            jQuery('*[data-country-code="au"]').css('pointer-events','none');
          }
        });
    });        
    }, 2500);
}