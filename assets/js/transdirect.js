// Check if simplified mode is set
function is_simplified_mode() {
    var mode = jQuery('#display_mode').val();
    if(mode == 'simplified_mode') {
        return true;
    } else {
        return false;
    }
}

// Check if transdirect plugin is enable
function is_td_enable(){
    if(jQuery('#td_enable').val() == 'yes') {
        return true;
    } else {
        return false;
    }
}

// get selected shipping method
function get_selected_shipping() {
    if((jQuery('input.shipping_method').is("input[type='hidden']")
        && jQuery("input:hidden.shipping_method").val() == 'woocommerce_transdirect') || (jQuery('input.shipping_method').is("input[type='radio']") && jQuery('li input:radio.shipping_method:checked').val() == 'woocommerce_transdirect')) {
        return true;
    } else {
        return false;
    }
}

// Hide calculator base on shipping method
function show_hide_cals() {
    if(get_selected_shipping()){
            jQuery('.tdCalc').show();
            getCountry();
    } else {
        jQuery('.tdCalc').hide();
    }
}

// Get shipping postcode in checkout page
function get_shipping_postcode() {
    var postcode = '';
    if(!jQuery('#ship-to-different-address-checkbox').is(":checked")){
        postcode = jQuery('#billing_postcode').val();
    } else {
        postcode = jQuery('#shipping_postcode').val();
    }
    return postcode;
}

// Get shipping suburb in checkout page
function get_shipping_suburb() {
    var suburb = '';
    if(!jQuery('#ship-to-different-address-checkbox').is(":checked")){
        suburb = jQuery('#billing_city').val();
    } else {
        suburb = jQuery('#shipping_city').val();
    }
    return suburb;
}
// Get shipping country in checkout page
function get_shipping_country() {
	var country = '';
	if(!jQuery('#ship-to-different-address-checkbox').is(":checked")){
        country = jQuery('#billing_country').val();
    } else {
        country = jQuery('#shipping_country').val();
    }
	return country;
}

function get_billing_data() {
    var billing = '';
    if(!jQuery('#ship-to-different-address-checkbox').is(":checked")){
        billing = 'yes';
    } else {
        billing = 'no';
    }
    return billing;
}

// Call new quote api
function getCheckoutData() {

    if(is_td_enable() && is_simplified_mode()) {
        var postcode = get_shipping_postcode();
        var suburb = get_shipping_suburb();
		var country = get_shipping_country();
        if(jQuery('#is_page').val()) {
            var suburbArray = suburb.split(',');
            if(suburbArray.length == 2) {
                jQuery('#billing_city').val(suburbArray[1]);
                jQuery('#shipping_city').val(suburbArray[1]);
            }
        }
        if(postcode != '' && suburb != '' && country != '' && !jQuery('#is_quote').val()) {
			if(country == 'AU' && postcode.length == 4) {
				javascript:get_quote_new();
			} else if(country != 'AU') {
				javascript:get_quote_new();
			}
        }
    }
}

jQuery(document).ready(function(){
    // When billing city changed and billing and shipping country are same than reset quote.
    jQuery('#billing_city, #billing_postcode').on('change', function(){
        if(is_td_enable() && is_simplified_mode()) {
            if(!jQuery('#ship-to-different-address-checkbox').is(":checked")){
                var postcode = get_shipping_postcode();
                var suburb = get_shipping_suburb();
				var country = get_shipping_country();
                if(postcode != '' && suburb != '' && country != '') {
                    if(country == 'AU' && postcode.length == 4) {
						javascript:get_quote_new();
					} else if(country != 'AU') {
						javascript:get_quote_new();
					}
                } else {
                    javascript:reset_quote();
                    jQuery('#td_value').val('');
                }
            }
        }
    });

    // When shipping city or postcode changed then reset quote.
    jQuery('#shipping_city, #shipping_postcode').on('change', function(){
        if(is_td_enable() && is_simplified_mode()) {
            var postcode = get_shipping_postcode();
            var suburb = get_shipping_suburb();
			var country = get_shipping_country();
            if(postcode != '' && suburb != '' && country != '') {
				if(country == 'AU' && postcode.length == 4) {
					javascript:get_quote_new();
				} else if(country != 'AU') {
					javascript:get_quote_new();
				}
            } else {
                javascript:reset_quote();
                jQuery('#td_value').val('');
            }
        }
    });

    jQuery('#ship-to-different-address-checkbox').on('change', function(){
        jQuery('#td_value').val('');
    });

    if(is_td_enable() && is_simplified_mode()) {
        var postcode = get_shipping_postcode();
        var suburb = get_shipping_suburb();
        if(jQuery('.get_postcode').val() != postcode || jQuery('.get_location').val() != suburb.toLowerCase() ) {
            getCheckoutData();
        } else if(jQuery('.session_price').val() == '')  {
            if(jQuery('#billing_city').val() != '' && jQuery('#billing_postcode').val() != '' && !jQuery('#is_quote').val()) {
                javascript:get_quote_new();
            }
        }
    }

    jQuery('#ship-to-different-address-checkbox').on('change', function(){
        if(is_td_enable() && is_simplified_mode()) {
            var postcode = get_shipping_postcode();
            var suburb = get_shipping_suburb();
            if(jQuery('#ship-to-different-address-checkbox').is(":checked")){
                if(postcode != '' && suburb != '') {
                    javascript:get_quote_new();
                }
                else {
                    reset_quote();
                }
            } else {
                if(jQuery('#billing_value').val() != ''){
                    javascript:get_quote_new();
                }
                else if(postcode != '' && suburb != '') {
                    javascript:get_quote_new();
                }
                else {
                    javascript:reset_quote();
                }
            }
        }
    });

    jQuery( 'body' ).on( 'updated_checkout', function() {
        if(!is_simplified_mode()) {
            show_hide_cals();
        } else if(is_simplified_mode()) {
            if(jQuery('#td_value').val() != '') {
                if(jQuery('input.shipping_method').is("input[type='radio']")) {
                jQuery( "#shipping_method" ).find("input[value='woocommerce_transdirect']").parent("li").hide();
                jQuery( "#shipping_method" ).find("input[value='woocommerce_transdirect']").parent("li").after(jQuery('#td_value').val());
                } else {
                    jQuery('.shipping td').html(jQuery('#td_value').val());
                }
            }
            jQuery('label[for="shipping_method_0_woocommerce_transdirect"]').after('<span onclick="javascript:get_quote_new();"  class="td-reset-btn">Reset</span>');
            jQuery('#shipping_method_0').after('<span onclick="javascript:get_quote_new();"  class="td-reset-btn">Reset</span>');
        }
    });

    jQuery('select#shipping_country, select#billing_country').change(function() {
        jQuery("#selected_country").val(jQuery("select#billing_country").select2('data')['id']);
    });

    if(jQuery().select2){
        jQuery('select#shipping_country, select#billing_country').select2().on("select2-selecting", function(e) {
            if(!is_simplified_mode()) {
                setTimeout(function(){
                    show_hide_cals();
                }, 2500);
            }
        });
    }

    if(jQuery('#session_allied_gst').val() != '' && jQuery('#session_allied_gst').val() != undefined){
        var gst_session = jQuery('#session_allied_gst').val();
        var gst_type = gst_session.split('<|>');
        if(gst_type[1] == 'incl'){
            jQuery('body .order-total > td > small >  span:first-child').html('<span class="woocommerce-Price-currencySymbol">'+ jQuery('#session_wc_currency').val() +'</span>'+ gst_type[0]);
        }
    }
});


jQuery( 'body' ).on( 'updated_checkout', function() {
    if(jQuery('#session_allied_gst').val() != '' && jQuery('#session_allied_gst').val() != undefined){
        var gst_session = jQuery('#session_allied_gst').val();
        var gst_type = gst_session.split('<|>');
        if(gst_type[1] == 'incl'){
            jQuery('body .order-total > td > small >  span:first-child').html('<span class="woocommerce-Price-currencySymbol">'+ jQuery('#session_wc_currency').val() +'</span>'+ gst_type[0]);
        }
    }
    if(!is_simplified_mode()) {
        show_hide_cals();
    }
});

jQuery( document.body ).on( 'updated_cart_totals', function() {
    if(is_simplified_mode()) {
        jQuery('.tdCalc').hide();
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

function reset_quote() {
    jQuery.post(
        MyAjax.ajaxurl, {
            action : 'myajaxdb-submit-new',
            'shipping_price'        : jQuery('.session_price').val(),
        },
        function(response) {
            jQuery("body").trigger( 'update_checkout' );
        }
    );
}

// this function use for simplified mode
function get_quote_new() {
    if(is_simplified_mode && (jQuery('#simple_mode_data').val() != '' || jQuery('#is_page').val())){
        if(jQuery('#is_page').val()) {
            reset_quote();    
        }
        var postcode  = get_shipping_postcode();
        var suburb    = get_shipping_suburb();
        var isBilling = get_billing_data();
        var to_country   = jQuery("#selected_country").val() != '' ? jQuery("#selected_country").val() : jQuery("body #simple_mode_country").val();
        ajaxindicatorstart('Getting Shipping Quotes');
        var to_location = jQuery('#is_page').val() ? postcode + ',' + suburb : jQuery("body #simple_mode_data").val();
        jQuery.post(
            // See tip #1 for how we declare global javascript variables
            MyAjax.ajaxurl, {
                action              : 'myajax-submit',
                'to_location'       : to_location,
                'country'           : to_country,
                'to_type'           : document.getElementById('business').checked ?
                                      document.getElementById('business').value : document.getElementById('residential').value,
            }, function(response) {
                if(response != 'Invalid delivery postcode.' && response != 'Please check module settings in transdirect account.'){
                    if(jQuery('.td_shipping').length > 0) {
                        jQuery('.td_shipping').hide();
                        jQuery( "#shipping_method" ).find("input[value='woocommerce_transdirect']").parent("li").show();
                    }
                    if(jQuery('input.shipping_method').is("input[type='radio']")) {
                        jQuery( "#shipping_method" ).find("input[value='woocommerce_transdirect']").parent("li").hide();
                        jQuery( "#shipping_method" ).find("input[value='woocommerce_transdirect']").parent("li").after(response);
                    } else {
                        jQuery('.shipping td').html(response);
                    }
                    jQuery('#td_value').val(response);
                    jQuery('.get_postcode').val(postcode);
                    jQuery('.get_location').val(suburb);
                    if(isBilling == 'yes') {
                        jQuery('#billing_value').val(response);
                    } else {
                        jQuery('#shipping_value').val(response);
                    }
                    ajaxindicatorstop();
                } else {
                    ajaxindicatorstop();
                    reset_quote();
                    alert('Please ensure the correct suburb and postcode.');
                    jQuery('#td_value').val('');
                }
            }
        );
    }
}


function validate() {
    var postcode = document.getElementById('to_postcode').value;
    if (document.getElementById('to_location').value == 'Australia') {
        alert("Please select a delivery location.");
        document.getElementById('to_location').value = "";
        return false;
    }
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
        ajaxindicatorstart('Getting Quote(s)');

        jQuery.post(
            // See tip #1 for how we declare global javascript variables
            MyAjax.ajaxurl, {
                action              : 'myajax-submit',
                // other parameters can be added along with "action"
                'to_location'       : document.getElementById('to_location').value,
                'to_type'           : document.getElementById('business').checked ?
                                      document.getElementById('business').value : document.getElementById('residential').value,
                'insurance_value'   : document.getElementById('insurance_value') ?
                                      document.getElementById('insurance_value').value : 0,
                'country'           : document.getElementById('txt_country').value,
                'to_postcode'       : document.getElementById('to_postcode').value,
            }, function(response) {
                jQuery("button[name='calc_shipping']").removeAttr('disabled');
                jQuery("#shipping_type").html('');
                jQuery("#shipping_type").append(response);
                jQuery("#shipping_type").show();
                ajaxindicatorstop();
            }
        );
    }
}

// this function call when user select courier from list
function get_quote(name) {
    jQuery('#td_value').val('');
    var shipping_name = name;
    var shipping_price = jQuery("#" + name + "_price").val();
    var shipping_transit_time = jQuery("#" + name + "_transit_time").val();
    var shiping_applied_gst = document.getElementById(name + "_applied_gst").value;
    jQuery('#trans_frm').addClass('load');
    var shipping_base = jQuery("#" + name + "_base").val();
    var mode = jQuery('#display_mode').val();
    jQuery.post(
        // see tip #1 for how we declare global javascript variables
        MyAjax.ajaxurl, {
            action : 'myajaxdb-submit',
            // other parameters can be added along with "action"
            'shipping_name'         : shipping_name,
            'shipping_price'        : shipping_price,
            'shiping_applied_gst'   : shiping_applied_gst,
            'shipping_transit_time' : shipping_transit_time,
            'shipping_base'         : shipping_base,
            'location'              : document.getElementById('to_location').value != '' ? document.getElementById('to_location').value : jQuery("body #simple_mode_data").val(),
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
                        jQuery('label[for="shipping_method_0_woocommerce_transdirect"]').html(label.split(':')[0] + ' : <strong>'+ resp.currency + resp.courier_price +'</strong>');
                    }
                } else if(jQuery('input.shipping_method').is("input[type='hidden']")){
                    if(jQuery('.shipping > td > strong').length){
                        jQuery('.shipping > td > strong').html(resp.currency + resp.courier_price);
                    } else if(jQuery('label[for="shipping_method_0_woocommerce_transdirect"]').length){
                        if(jQuery('label[for="shipping_method_0_woocommerce_transdirect"] > strong').length){
                            jQuery('label[for="shipping_method_0_woocommerce_transdirect"] > strong').html(resp.currency + resp.courier_price);
                        } else {
                            label = jQuery('label[for="shipping_method_0_woocommerce_transdirect"]').text();
                            jQuery('label[for="shipping_method_0_woocommerce_transdirect"]').html(label.split(':')[0] + ' : <strong>'+ resp.currency + resp.courier_price +'</strong>');
                        }
                    } else {
                        jQuery('#shipping_method_0').before('<strong>'+ resp.currency + resp.courier_price +'</strong>');
                    }
                }
                jQuery( "#shipping_method" ).find("input[value='woocommerce_transdirect']").parent("li").show();
                jQuery('body .order-total > td > strong >  span').html('<span class="woocommerce-Price-currencySymbol">'+ resp.currency +'</span>'+ resp.total);
                jQuery('.shipping_calculator').slideToggle();
                jQuery('.sel-courier').show();
                jQuery('.session_price').val(resp.courier_price);
                jQuery('.session_selected_courier').val(resp.shipping_name);
                jQuery('.courier-data').html(resp.shipping_name);
                jQuery('.price-data').html(" " + "<strong>" + resp.currency + resp.courier_price + "</strong>");
                jQuery("body").trigger( 'update_checkout' );
                if(resp.total_gst){
                    jQuery('body .order-total > td > small >  span:first-child').html('<span class="woocommerce-Price-currencySymbol">'+ resp.currency +'</span>'+ resp.total_gst.split('<|>')[0]);
                    jQuery('#session_allied_gst').val(resp.total_gst);
                }
                jQuery(".td_shipping").hide();
                jQuery("#shipping_type").hide();
                jQuery('.get_postcode').val(resp.postcode);
                jQuery('.get_location').val(resp.suburl);
                jQuery("#to_location").val('');
                jQuery('#simple_mode_data').val('')
                jQuery("#calc_shipping_city").val('');
                jQuery("#calc_shipping_postcode").val('');
            }
        }
    );
    jQuery( "#shipping_method_0_woocommerce_transdirect" ).prop("checked", true);
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
        });
    }, 2500);
    
}
