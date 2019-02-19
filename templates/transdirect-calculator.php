<?php
/**
 * Shipping Transdirect Calculator
 *
 * @author 		Transdirect
 * @version     6.7
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly
global $woocommerce, $wpdb; 
?>

<?php
	$getTitle = td_getApiDetails();
 	$trans_title = !empty($getTitle->shipping_title) ? $getTitle->shipping_title : 'Get a shipping estimate';
 	if($getTitle->mode != 'no_display_mode') {
?>

<script>
	jQuery(document).ready(function() {
		var mode = '<?php echo $getTitle->mode; ?>';
		jQuery("#to_postcode").hide();
		imageUrl = "<?php echo site_url(); ?>/wp-content/plugins/transdirect-shipping/assets/images/ajax-loader-bg.gif";
		jQuery('body').click(function() {
			jQuery('#autocomplete-div').hide('');
			jQuery('#dynamic_content').hide('');
		});
		jQuery("#calc_shipping_city").val('');
        jQuery("#calc_shipping_postcode").val('');
		jQuery('body').on('click', '.shipping-calculator-button', function() {
			jQuery("#calc_shipping_city").val('');
            jQuery("#calc_shipping_postcode").val('');
		});
		jQuery('body').on('change', '#shipping_method input.shipping_method', function() {
			if(mode != 'simplified_mode') {
				if(jQuery(this).val() != 'woocommerce_transdirect') {
					jQuery('div.tdCalc').hide();
				} else {
					jQuery('div.tdCalc').show();
				}
			}
		});
		jQuery('#to_location').on('change', function() {
		    var countryData = jQuery("#to_location").countrySelect("getSelectedCountryData");
		    if(countryData)
		    {
		        jQuery("#to_location").val(countryData.name);
		        jQuery("#txt_country").val(countryData.iso2);
		    }
		});

		var latestRequestNumber = 0;
		var globalTimeout = null;

		jQuery('body').on('keyup', '#to_location', function() {
   			td_autocomplete('to_location', 'autocomplete-div');
		});

		jQuery('body').on('keyup', '#calc_shipping_postcode, #calc_shipping_city', function() {
			if(mode == 'simplified_mode'){
				if(jQuery("#calc_shipping_city").length > 0) {
					td_autocomplete('calc_shipping_city', 'simple_autocomplete_div');
				} else {
					td_autocomplete('calc_shipping_postcode', 'simple_autocomplete_div');
				}
        	}
		});

		function td_autocomplete(td_selector, auto_selector) {
			var key_val = jQuery("#"+td_selector).val();
			var position = jQuery("#"+td_selector).position();
            var html = '';
            jQuery('#'+td_selector).addClass('loadinggif');
			if (key_val=='') {
                key_val=0;
            }
			jQuery.getJSON("<?php echo plugins_url('includes/locations.php' ,  dirname(__FILE__) ); ?>", {'q':key_val, requestNumber: ++latestRequestNumber }, function(data) {
	            if (data.requestNumber < latestRequestNumber) {
	            	return;
	            }
				if (data.locations != '' && key_val != '0') {
	                jQuery.each(data.locations, function(index, value ) {
	                	if(value.postcode &&  value.locality) {
		                	jQuery('.get_postcode').val(value.postcode);
		                	jQuery('.get_location').val(value.locality);
					        html = html+'<li onclick="get_value(\''+value.postcode+'\',\''+value.locality+'\', \''+td_selector+'\', \''+auto_selector+'\')">'+value.postcode+', '+value.locality+'</li>';
					    }
			        });
			        var main_content = '<ul id="auto_complete">'+html+'</ul>';
					jQuery("#loading-div").hide();
			        jQuery("#"+auto_selector).show();
			        jQuery("#"+auto_selector).html(main_content);
			        jQuery("#"+auto_selector).css('left', position.left);
			        jQuery("#"+auto_selector).css('top', parseInt(position.top) + 45);
	            } else {
	                html = html+'<li>No Results Found</li>';
	                var main_content = '<ul id="auto_complete">'+html+'</ul>';

	                jQuery("#"+auto_selector).show();
			        jQuery("#"+auto_selector).html(main_content);
			        jQuery("#"+auto_selector).css('left', position.left);
			        jQuery("#"+auto_selector).css('top', parseInt(position.top) + 45);
			        jQuery("#"+auto_selector).css('overflow-y','hidden');

			        jQuery('#'+auto_selector).removeClass('loadinggif');
			        jQuery('#'+td_selector).removeClass('loadinggif');
	            }
				jQuery('#'+auto_selector).removeClass('loadinggif');
				jQuery('#'+td_selector).removeClass('loadinggif');
            });
		}
	});

	function get_value(postcode, locality, txt_selector, div_selector) {
		jQuery("#"+txt_selector).countrySelect("setCountry", 'Australia');
		jQuery("#"+txt_selector).countrySelect("selectCountry", 'au');
	    jQuery("#"+txt_selector).val(postcode + ',' + locality);
	    jQuery("#simple_mode_data").val(postcode + ',' + locality);
	    jQuery("#simple_mode_country").val(jQuery("#calc_shipping_country").val());
		jQuery("#"+div_selector).html('');
	    jQuery("#"+div_selector).hide();
	    jQuery("#to_postcode").hide();
	}
	var price = <?php echo $_SESSION['price'] ? $_SESSION['price']  : '0'; ?>;
</script>
<?php if((!empty($_SESSION['price']) && !empty($_SESSION['selected_courier']))): ?>
	<script>
	jQuery('.sel-courier').hide();
	</script>
<?php endif; ?>

<div id="simple_autocomplete_div"></div>
<input type="hidden" id="is_quote" value="<?php if(!empty($_SESSION['price'])) { echo 'true';} else { echo 'false';} ?>" >
<?php if ( ((get_option('woocommerce_enable_shipping_calc') === 'no' || get_option('woocommerce_enable_shipping_calc') === 'yes') &&
		 !is_cart()) || (get_option( 'woocommerce_enable_shipping_calc' ) === 'yes' && is_cart()) ): ?>
	<?php
		$shipping_details = $wpdb->get_results("SELECT `option_value` FROM " . $wpdb->prefix . "options WHERE `option_name`='woocommerce_woocommerce_transdirect_settings'");
		$default_values = unserialize($shipping_details[0]->option_value);
	?>
		
	<div class="tdCalc">
		<input type="hidden" class="session_allied_gst" id="session_allied_gst" value="<?= $_SESSION['applied_gst']?>">
		<input type="hidden" class="session_wc_currency" id="session_wc_currency" value="<?=$_SESSION['currency']?>">
	    <?php if((!empty($_SESSION['price']) && !empty($_SESSION['selected_courier'])) || (isset($_SESSION['free_shipping']) && !empty($_SESSION['free_shipping']))): ?>
	    <div class="sel-courier">
	        <input type="hidden" class="session_price" value="<?= $_SESSION['price']?>">
	        <input type="hidden" class="session_selected_courier" value="<?= $_SESSION['selected_courier']?>">
	        <p class="td-courier-selected">
	            <b>Selected Courier:</b>&nbsp;
	            <i class="courier-data"><?php echo $string = str_replace('_', ' ', $_SESSION['selected_courier']);?></i> - <strong class="price-data"><?php echo get_woocommerce_currency_symbol().' '.number_format($_SESSION['price'], 2); ?></strong>
	        </p>
	        <input type="hidden" class="get_postcode" value="<?= $_SESSION['postcode']?>">
	        <input type="hidden" class="get_location" value="<?= $_SESSION['to_location']?>">
	        <input type="hidden" name="txt_country" id="txt_country" class="get_country">
	        <input type="hidden" id="locationUrl" value="<?php echo plugins_url('includes/locations.php' , dirname(__FILE__) ); ?>">
	        <p><a onclick="showCalc()" class="td-link-show-calculator">(change)</a></p>
	    </div>
	    <?php else: ?>
	    <div class="sel-courier" style="display:none">
	        <input type="hidden" class="session_price">
	        <input type="hidden" class="session_selected_courier">
	        <p class="courier-selected">
	            <b>Selected Courier:</b>&nbsp;
	            <i class="courier-data"></i> - <strong class="price-data"></strong>
	        </p>
	        <input type="hidden" class="get_postcode">
	        <input type="hidden" class="get_location">
	        <input type="hidden" class="get_country" id="txt_country" name="txt_country">
	        <input type="hidden" id="locationUrl" value="<?php echo plugins_url('includes/locations.php' , dirname(__FILE__) ); ?>">
	        <p><a onclick="showCalc()" class="td-link-show-calculator">(change)</a></p>
	    </div>
	    <?php $showShippingCalc = true; ?>
	    <?php  endif; ?>
	    <div class="blockUI" style="display:none"></div>
	    <div class="shipping_calculator td-trans-frm" id="trans_frm" style="<?= isset($showShippingCalc) ? '' : 'display:none' ?>">
	        <h4><?php _e($trans_title, 'woocommerce'); ?></h4>
	        <br/>
	        <section class="td-shipping-calculator-form1">
	            <p class="form-row">
	                <input type="text" name="to_location" id="to_location" placeholder="Enter Postcode, Suburb" autocomplete="off" width="100%" />
	                <input type="text" name="to_postcode" id="to_postcode" placeholder="Enter Postcode" autocomplete="off" width="100%" />
	                <input type="hidden" class="get_location">
	                <input type="hidden" class="get_postcode">
	                <input type="hidden" class="get_country" id="txt_country" name="txt_country">
	                <input type="hidden" id="locationUrl" value="<?php echo plugins_url('includes/locations.php' , __FILE__ ); ?>">
	                <span id="loading-div" style="display:none;"></span>
	                <div id="autocomplete-div"></div>
	            </p>
	            <p class="form-row form-row-wide">
	                <input type="radio" name="to_type" id="business" value="business" <?php if($getTitle->street_type == 'business' ):?> checked="checked"
	                <?php endif; ?>/> Commercial
	                <input type="radio" name="to_type" class="td-residential" id="residential" value="residential" <?php if ($getTitle->street_type == 'residential' ): ?> checked="checked"
	                <?php endif; ?>/> Residential
	            </p>
	            <input type="hidden" name="td_value" id="td_value">
	            <input type="hidden" name="display_mode" id="display_mode" value="<?php echo $getTitle->mode; ?>">
	            <input type="hidden" name="billing_value" id="billing_value">
	            <input type="hidden" name="selected_country" id="selected_country">
	            <input type="hidden" name="shipping_value" id="shipping_value">
	            <input type="hidden" name="td_enable" id="td_enable" value="<?php echo $default_values['enabled']; ?>"> 
	            <p id="btn-get-quote" class="td-btn-get-quote">
	                <button type="button" name="calc_shipping" value="1" class="button calculator td-btn-warning" onclick="javascript:validate();">
	                    <?php _e('Get a quote', 'woocommerce'); ?>
	                </button>
	            </p>
	            <?php wp_nonce_field('woocommerce-cart'); ?>
	        </section>
	        <div id="shipping_type" class="td-shipping_type" style="display:none;">
	            <input type="hidden" name="shipping_variation" id="shipping_variation" value="1" />
	            <input type="hidden" name="is_page" id="is_page" value="<?php echo is_checkout(); ?>">
	        </div>
	    </div>
	</div>
 <?php endif; ?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		var mode = '<?php echo $getTitle->mode; ?>';

		// check for transdirect is selected as shipping method or not
		var wcShipping = '<?php echo WC()->session->chosen_shipping_methods[0]; ?>';
		var enabled = '<?php echo $default_values["enabled"]?>';
		if(wcShipping && wcShipping == 'woocommerce_transdirect' && enabled && !is_simplified_mode()){
			jQuery('.tdCalc').show();
		}else{
			jQuery('.tdCalc').hide();
		}
		
		if(!is_simplified_mode()) {
			setTimeout(function(){
				if(get_selected_shipping()) {
					jQuery('.tdCalc').show();
					if(jQuery('.session_price').val()){
						jQuery('.sel-courier').show();	
					}	
				} else {
					jQuery('.tdCalc').hide();
				}
			}, 6000);
		}

		jQuery( document ).on( 'change', 'input.shipping_method, input[name^=shipping_method]', function() {
			if(!is_simplified_mode()) {
				var context = this;
				var paymentInterval = setInterval(function () {
					if (!jQuery('#payment .blockUI').length) {
						if(jQuery(context).val() == 'woocommerce_transdirect') {
							jQuery('.tdCalc').show();
							if(jQuery('.session_price').val()){
								jQuery('.sel-courier').show();	
							}
						} else{
							jQuery('.tdCalc').hide();
						}
						clearInterval(paymentInterval);
					}
				}, 100);
			}
		});

		jQuery(document).on('click', 'span.country-name,ul.country-list li', function(){
			var countryData = jQuery("#to_location").countrySelect("getSelectedCountryData");
		    if(countryData)
		    {	
		        if(countryData.iso2 == 'au'){
		    		jQuery("#to_location").val("");
		    	} else {
		    		jQuery("#to_location").val(countryData.name);
		    	}
		        jQuery("#txt_country").val(countryData.iso2);
		        if(countryData.postcode == 1){
		        	jQuery("#to_postcode").show();
		        } else {
		        	jQuery("#to_postcode").hide();
		        }
		        if(countryData.iso2 == 'au') {
		        	jQuery("#to_postcode").hide();
		        }
		    }
		});

		setTimeout(function(){
			if(jQuery("body #to_location").val() == 'Australia'){
				jQuery("body #to_location").val('');
				jQuery("#to_postcode").hide();
			}
		}, 7000);

		jQuery('.cart-collaterals').append('<input type="hidden" id="simple_mode_data" name="simple_mode_data"><input type="hidden" id="simple_mode_country" name="simple_mode_country">');
		jQuery(document.body).on( 'updated_cart_totals', function() { 
			if(get_selected_shipping()) {
				jQuery('.tdCalc').show();
				getCountry();
				if(jQuery('.session_price').val()){
					jQuery('.sel-courier').show();
				}
			} else {
				jQuery('.tdCalc').hide();
			}
			if(mode == 'simplified_mode' && (jQuery('#simple_mode_data').val() != '' || jQuery("#calc_shipping_city").val() != '')) {
				if(jQuery('#simple_mode_data').val() == '') {
					if(jQuery("#calc_shipping_city").length > 0) {
						jQuery('#simple_mode_data').val(jQuery("#calc_shipping_city").val())
					}
				}
				if(jQuery('#simple_mode_data').val() != '') {
					jQuery("#calc_shipping_city").val('');
                    jQuery("#calc_shipping_postcode").val('');
					get_quote_new(); 
				}
			}
		});
	});

	


</script>
<?php } ?>