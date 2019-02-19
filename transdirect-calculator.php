<?php
/**
 * Shipping Transdirect Calculator
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     4.7
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

global $woocommerce, $wpdb; ?>

<style>
	.shipping-calculator-form1 {
		position:relative;
	}

	.shipping-calculator-form1 p.form-row {
           width: 100% !important;
        }

	#autocomplete-div {
		background:#FFFFFF;
		border: 1px solid #EDEDED;
		border-radius: 3px 3px 3px 3px;
		display: none;
		height: auto;
		max-height: 150px;
		margin: -5px 0 0 1px;
		overflow-y: auto;
		padding: 5px;
		position: absolute;
		width: 189px;
		z-index: 99;
		width: 100%;
	}

	#autocomplete-div ul {
		margin: 0 0 0px 0px !important;
	}

	#autocomplete-div ul li {
		padding:0 !important;
		margin:0 !important;
		text-indent:0 !important;
		list-style: none;
		cursor:pointer;
	}

	#autocomplete-div ul li:hover {
		background:#ededed;
		list-style: none;
	}

	#trans_frm {
		right: 0;
		width:350px;
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
		margin: 10px 0 !important;
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
		border: none;
		margin: 0px;
		padding: 0px;
		width: 100%;
		height: 100%;
		top: 0px;
		left: 0px;
		z-index:10000000;
	  opacity: 0.4;
	  filter: alpha(opacity=40);
		cursor: wait;
		position: absolute;
		background: url('<?php echo site_url(); ?>/wp-content/plugins/woocommerce/assets/images/ajax-loader@2x.gif') 50% 50% / 16px 16px no-repeat rgb(255, 255, 255);
		background-position:center;
	}

	.courier-selected {
		margin-bottom: 0px;
		/*font-style: italic;*/
		font-size: 18px;
	}

	.link-show-calculator {
		float: right;
		margin-bottom: 10px;
		color: blue;
		cursor: pointer;
		font-size: 18px;
	}

	.hide-shipping-calc {
		display: none;
	}

	.btn-warning, .btn-warning:focus {
	    color: #fff !important;
	    background-color: #f60 !important;
	    border-color: #e65c00 !important;
	    font-size: 14px;
	    font-weight: 700;
	    text-align: center;
	    vertical-align: middle;
	    cursor: pointer;
	    border: 1px solid transparent;
	    border-radius: 4px;
	    white-space: nowrap;
	    -webkit-user-select: none;
	    -moz-user-select: none;
	    -ms-user-select: none;
	    -o-user-select: none;
	}


	/* CHECKOUT */
	form.checkout.woocommerce-checkout .tdCalc{
		margin-top: 80px;
	}


	#to_location {
		width: 100%;
	}

	#to_postcode {
		width: 100%;
		margin-top: 10px;
	}

	#residential {
		margin-left: 15px;
	}

	#btn-get-quote {
	    margin-top: 30px;
    	text-align: center;
	}

	.woocommerce .shipping-calculator-form1 form.checkout .col2-set .form-row {
		width: 100% !important;
	}
</style>

<?php
	$shipping_details = $wpdb->get_results("SELECT `option_value` FROM " . $wpdb->prefix . "options WHERE `option_name` like '%woocommerce_transdirect_settings'");
  $default_values = unserialize($shipping_details[0]->option_value);
 	if(isset($default_values['trans_title']) && $default_values['trans_title'] != ''){
 		$trans_title = ucfirst($default_values['trans_title']);
 	}
 	else{
 		$trans_title = 'Get a shipping estimate';
 	}

	if (!empty($_POST['shipping_variation'])) {
		$shipping_details = $wpdb->get_results( "SELECT `option_value` FROM " .$wpdb->prefix . "options WHERE `option_name`='woocommerce_".WC()->session->chosen_shipping_methods[0]."_settings'");
		$default_values = unserialize($shipping_details[0]->option_value);

		$shipping_type = $_POST['shipping_type_radio'];
		$price 		   = $_POST[$shipping_type.'_price'];
		$transit_time  = $_POST[$shipping_type.'_transit_time'];
		$service_type  = $_POST[$shipping_type.'_service_type'];

		if ($default_values['Surcharge'] == 'yes') {
			$_SESSION['price'] =  $price + $default_values['Surcharge_price'];
		} else {
			$_SESSION['price'] =  $price;
		}
	}

	if (isset($_SESSION['price']) &&  WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect') {
		$price = $_SESSION['price'];
		WC()->shipping->shipping_total = $price;
		WC()->cart->total = WC()->cart->subtotal + $price;
		WC()->session->shipping_total = $price;
		WC()->session->total = WC()->session->subtotal + $price;
		WC()->cart->add_fee(__('Shipping Cost', 'woocommerce'), $price);
	}

	if (!empty($_POST['to_location'])) {

		$_SESSION['price'] = '0';

		if(!empty(WC()->session->chosen_shipping_methods[0])) {
			// Default settings
			$shipping_details = $wpdb->get_results("SELECT `option_value` FROM " . $wpdb->prefix . "options WHERE `option_name`='woocommerce_".WC()->session->chosen_shipping_methods[0]."_settings'");
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

            $args = array(
                'headers'   => array(
                    'Authorization' => 'Basic ' . base64_encode($default_values['email'] . ':' . $default_values['password']),
                    'Content-Type'  => 'application/json'
                ),
                'body'      => json_encode($api_arr),
                'timeout'   => 45
            );


            $link = "https://www.transdirect.com.au/api/bookings";
            $response1 = wp_remote_retrieve_body(wp_remote_post($link, $args));
            $response1 = str_replace("true, // true if the booking has a tailgate pickup, false if not", "0,", $response1);
            $response1 = str_replace("true // true if the booking has a tailgate delivery, false if not", "0", $response1);
            $response1 = str_replace("''", "0", $response1);
            $shipping_quotes1 = json_decode(str_replace("''", "0", $response1));
			$shipping_quotes = $shipping_quotes1->quotes;
		}
	}
?>

<script>
	jQuery(document).ready(function() {
		jQuery("#to_postcode").hide();
		imageUrl = "<?php echo site_url(); ?>/wp-content/plugins/transdirect-shipping/ajax-loader-bg.gif";
		jQuery('body').click(function() {
			jQuery('#autocomplete-div').hide('');
			jQuery('#dynamic_content').hide('');
		});

		if(!jQuery('body .session_price').val() && !jQuery('body .session_selected_courier').val() &&
			(jQuery('body #billing_postcode').val() && jQuery('body #billing_city').val())) {

			document.getElementById('to_location').value = jQuery('body #billing_postcode').val() +','+ jQuery('body #billing_city').val();
			// jQuery('body .button.calculator').click();
		}


		if(!jQuery('body #billing_postcode').val() || !jQuery('body #billing_city').val()){
			jQuery('body #billing_postcode').val(jQuery('.get_postcode').val());
			jQuery('body #billing_city').val(jQuery('.get_location').val());
		}

		if(!jQuery('body .session_price').val() && !jQuery('body .session_selected_courier').val() &&
			(jQuery('body #billing_postcode').val() && jQuery('body #billing_city').val()))
			jQuery('.get_postcode').val();
		jQuery('body').on('change', '#shipping_method input.shipping_method', function() {
			if(jQuery(this).val() != 'woocommerce_transdirect') {
				jQuery('div.tdCalc').hide();
			} else {
				jQuery('div.tdCalc').show();
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
			jQuery("#to_postcode").hide();
            var key_val = jQuery("#to_location").val();
			var position = jQuery("#to_location").position();
            var html = '';

            jQuery('#to_location').addClass('loadinggif');

			if (key_val=='') {
                key_val=0;
            }

			jQuery.getJSON("<?php echo plugins_url('locations.php' , __FILE__ ); ?>", {'q':key_val, requestNumber: ++latestRequestNumber }, function(data) {
	            if (data.requestNumber < latestRequestNumber) {
	            	return;
	            }
				if (data.locations != '' && key_val != '0') {
	                jQuery.each(data.locations, function(index, value ) {
	                	if(value.postcode &&  value.locality) {
		                	jQuery('.get_postcode').val(value.postcode);
		                	jQuery('.get_location').val(value.locality);
					        html = html+'<li onclick="get_value(\''+value.postcode+'\',\''+value.locality+'\')">'+value.postcode+', '+value.locality+'</li>';
					    }
			        });

			        var main_content = '<ul id="auto_complete">'+html+'</ul>';

					jQuery("#loading-div").hide();
			        jQuery("#autocomplete-div").show();
			        jQuery("#autocomplete-div").html(main_content);
			        jQuery("#autocomplete-div").css('left', position.left);
			        jQuery("#autocomplete-div").css('top', parseInt(position.top) + 45);

	            } else {
	                 html = html+'<li>No Results Found</li>';
	                 var main_content = '<ul id="auto_complete">'+html+'</ul>';

	                jQuery("#autocomplete-div").show();
			        jQuery("#autocomplete-div").html(main_content);
			        jQuery("#autocomplete-div").css('left', position.left);
			        jQuery("#autocomplete-div").css('top', parseInt(position.top) + 45);
			        jQuery("#autocomplete-div").css('overflow-y','hidden');

			        jQuery('#to_location').removeClass('loadinggif');
	            }

				jQuery('#to_location').removeClass('loadinggif');
            });
		});
	});


	function hideContent() {
		jQuery("#autocomplete-div").html('');
		jQuery("#autocomplete-div").hide();
	}

	function get_value(postcode, locality) {
		jQuery("#to_location").countrySelect("setCountry", 'Australia');
	    jQuery("#to_location").val(postcode + ',' + locality);
		jQuery("#autocomplete-div").html('');
	    jQuery( "#autocomplete-div" ).hide();
	}

	function get_dynamic_value(field_id, locality) {
	    jQuery("#" + field_id).val(locality);
		jQuery("#dynamic_content").remove();
	}

	var price = <?php echo $_SESSION['price'] ? $_SESSION['price']  : '0'; ?>;

</script>


<?php if(WC()->session->chosen_shipping_methods[0] == 'woocommerce_transdirect'): ?>
<?php 	if(!isset($_SESSION['price']) || $_SESSION['price'] == '' || $_SESSION['price'] == 0): ?>
				<script>
					var context = this;
					var paymentInterval = setInterval(function () {
						if (!jQuery('#payment .blockUI').length) {
							//jQuery('#place_order').hide();
							clearInterval(paymentInterval);
						}
				}, 100);

				 </script>
<?php 	endif; ?>
<?php endif; ?>
<?php if((!empty($_SESSION['price']) && !empty($_SESSION['selected_courier']))): ?>
	<script>
	jQuery('.sel-courier').hide();
	</script>
<?php endif; ?>


<?php if ( ((get_option('woocommerce_enable_shipping_calc') === 'no' || get_option('woocommerce_enable_shipping_calc') === 'yes') &&
		 !is_cart()) || (get_option( 'woocommerce_enable_shipping_calc' ) === 'yes' && is_cart()) ): ?>
		<?php
			$shipping_details = $wpdb->get_results("SELECT `option_value` FROM " . $wpdb->prefix . "options WHERE `option_name`='woocommerce_".WC()->session->chosen_shipping_methods[0]."_settings'");
			$default_values = unserialize($shipping_details[0]->option_value);
		?>

		<div class="tdCalc">
			<?php if(!empty($_SESSION['price']) && !empty($_SESSION['selected_courier'])): ?>
					<div class="sel-courier">
						<input type="hidden" class="session_price" value="<?= $_SESSION['price']?>">
						<input type="hidden" class="session_selected_courier" value="<?= $_SESSION['selected_courier']?>">
						<p class="courier-selected">
							<b>Selected Courier:</b>&nbsp;
							<i class="courier-data"><?php echo $string = str_replace('_', ' ', $_SESSION['selected_courier']);?></i>
							- <strong class="price-data"><?php echo get_woocommerce_currency_symbol().' '.number_format($_SESSION['price'], 2); ?></strong>
						</p>
						<input type="hidden" class="get_postcode" value="<?= $_SESSION['postcode']?>">
						<input type="hidden" class="get_location" value="<?= $_SESSION['to_location']?>">
						<input type="hidden" name="txt_country" id="txt_country" class="get_country">
						<input type="hidden" id="locationUrl" value="<?php echo plugins_url('locations.php' , __FILE__ ); ?>">
						<p><a onclick="showCalc()" class="link-show-calculator">(change)</a></p>
					</div>
			<?php else: ?>
				<div class="sel-courier" style="display:none">
					<input type="hidden" class="session_price">
					<input type="hidden" class="session_selected_courier">
					<p class="courier-selected">
						<b>Selected Courier:</b>&nbsp;
						<i class="courier-data"></i>
						- <strong class="price-data"></strong>
					</p>
					<input type="hidden" class="get_postcode">
					<input type="hidden" class="get_location">
					<input type="hidden" class="get_country" id="txt_country" name = "txt_country">
					<input type="hidden" id="locationUrl" value="<?php echo plugins_url('locations.php' , __FILE__ ); ?>">
					<p><a onclick="showCalc()" class="link-show-calculator">(change)</a></p>
				</div>
				<?php $showShippingCalc = true; ?>
			<?php  endif; ?>

			<div class="blockUI" style="display:none"></div>
			<div class="shipping_calculator" id="trans_frm" style="<?= isset($showShippingCalc) ? '' : 'display:none' ?>">
				<h4><?php _e($trans_title, 'woocommerce'); ?></h4><br/>
				<section class="shipping-calculator-form1">
					<p class="form-row">
						<input type="text" name="to_location" id="to_location" placeholder="Enter Postcode, Suburb"
						autocomplete="off" width="100%"/>
						<input type="text" name="to_postcode" id="to_postcode" placeholder="Enter Postcode"
						autocomplete="off" width="100%"/>
						<input type="hidden" class="get_location">
						<input type="hidden" class="get_postcode">
						<input type="hidden" class="get_country" id="txt_country" name="txt_country">
						<input type="hidden" id="locationUrl" value="<?php echo plugins_url('locations.php' , __FILE__ ); ?>">
						<span id="loading-div" style="display:none;"></span>
						<div id="autocomplete-div"></div>
					</p>

					<p class="form-row form-row-wide">
						<input type="radio" name="to_type" id="business" value="business"
						<?php if($default_values['address_type'] == 'Commercial'):?> checked="checked"
						<?php endif; ?>/> Commercial
						<input type="radio" name="to_type" id="residential" value="residential"
						<?php if ($default_values['address_type'] == 'Residential'): ?> checked="checked"
						<?php endif; ?>/> Residential
					</p>

					<?php
						//if ($default_values['insurance_surcharge'] == 'yes') { ?>
					<!-- 	<p class="form-row form-row-msall">
							<?php //echo '<b>'.get_woocommerce_currency_symbol().''.number_format(WC()->session->total, 2).'</b>' ?>
							<input type="text" name="insurance_value"
							id="insurance_value" value="" placeholder="Declared Value"/>
						</p> -->
					<?php //} ?>

					<p id="btn-get-quote">
						<button type="button" name="calc_shipping" value="1" class="button calculator btn-warning"
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
		</div>

	<?php// endif; ?>

<?php endif; ?>

<script type="text/javascript">

	jQuery(document).ready(function() {
		pluginUrl = '<?php echo plugins_url('quotes.php', __FILE__); ?>';
			setTimeout(function(){
				if(jQuery('input.shipping_method').is("input[type='radio']")) {

					if(jQuery('li input:radio.shipping_method:checked').val() == 'woocommerce_transdirect') {
						jQuery('.tdCalc').show();
						if(jQuery('.session_price').val()){
							jQuery('.sel-courier').show();	
						}	
					} else {
						jQuery('.tdCalc').hide();
					}

				} else if(jQuery('.shipping_method').is("select")){

					if(jQuery('select.shipping_method').val() == 'woocommerce_transdirect') {
						jQuery('.tdCalc').show();
						if(jQuery('.session_price').val()){
							jQuery('.sel-courier').show();	
						}
					} else {
						jQuery('.tdCalc').hide();
					}

					jQuery( document ).on( 'change', 'select.shipping_method, input[name^=shipping_method]', function() {
						if(jQuery(this).val() == 'woocommerce_transdirect') {
							jQuery('.tdCalc').show();
							if(jQuery('.session_price').val()){
								jQuery('.sel-courier').show();	
							}
						} else{
							jQuery('.tdCalc').hide();
						}
					});
				} else if(jQuery('input.shipping_method').is("input[type='hidden']")) {
					if(jQuery("input:hidden.shipping_method").val() == 'woocommerce_transdirect') {
						jQuery('.tdCalc').show();
						if(jQuery('.session_price').val()){
							jQuery('.sel-courier').show();	
						}
					} else{
						jQuery('.tdCalc').hide();
					}
				}
			}, 2100);

			// check for transdirect is selected as shipping method or not
			var wcShipping = '<?php echo WC()->session->chosen_shipping_methods[0]; ?>';
			var enabled = '<?php echo $default_values["enabled"]?>';
			if(wcShipping && wcShipping == 'woocommerce_transdirect' && enabled){
				jQuery('.tdCalc').show();
			}else{
				jQuery('.tdCalc').hide();
			}

			// if(jQuery('#billing_country').val() == 'AU'){
			// 	setTimeout(function(){
			// 		if(jQuery('input.shipping_method').is("input[type='radio']")) {
			// 			if(jQuery('li input:radio.shipping_method:checked').val() == 'woocommerce_transdirect') {
			// 				jQuery('.tdCalc').show();
			// 				if(jQuery('.session_price').val()){
			// 					jQuery('.sel-courier').show();	
			// 				}
			// 			} else {
			// 				jQuery('.tdCalc').hide();
			// 			}
			// 		} else if(jQuery('.shipping_method').is("select")){
			// 			if(jQuery('select.shipping_method').val() == 'woocommerce_transdirect') {
			// 				jQuery('.tdCalc').show();
			// 				if(jQuery('.session_price').val()){
			// 					jQuery('.sel-courier').show();	
			// 				}
			// 			} else {
			// 				jQuery('.tdCalc').hide();
			// 			}
			// 		} else if(jQuery('input.shipping_method').is("input[type='hidden']")) {
			// 			if(jQuery("input:hidden.shipping_method").val() == 'woocommerce_transdirect')
			// 			jQuery('.tdCalc').show();
			// 			if(jQuery('.session_price').val()){
			// 				jQuery('.sel-courier').show();	
			// 			}
			// 		}
			// 	}, 2100);
			// }

			// //when shipping country change and AU as selected country then show transdirect shipping method
			// if(jQuery().select2){
			//   jQuery('select').select2().on("select2-selecting", function(e) {
	  //       if(e.val == 'AU'){
	  //       	// get transdirect method after order review refresh process complete
	  //       	setTimeout(function(){
			// 				if(jQuery('input.shipping_method').is("input[type='radio']")) {
			// 					if(jQuery('li input:radio.shipping_method:checked').val() == 'woocommerce_transdirect') {
			// 						jQuery('.tdCalc').show();
			// 						if(jQuery('.session_price').val()){
			// 							jQuery('.sel-courier').show();	
			// 						}
			// 					} else {
			// 						jQuery('.tdCalc').hide();
			// 					}
			// 				} else if(jQuery('.shipping_method').is("select")){
			// 					if(jQuery('select.shipping_method').val() == 'woocommerce_transdirect') {
			// 						jQuery('.tdCalc').show();
			// 						if(jQuery('.session_price').val()){
			// 							jQuery('.sel-courier').show();	
			// 						}
			// 					} else {
			// 						jQuery('.tdCalc').hide();
			// 					}
			// 					jQuery( document ).on( 'change', 'select.shipping_method, input[name^=shipping_method]', function() {
			// 						if(jQuery(this).val() == 'woocommerce_transdirect') {
			// 							jQuery('.tdCalc').show();
			// 							if(jQuery('.session_price').val()){
			// 								jQuery('.sel-courier').show();	
			// 							}
			// 						} else{
			// 							jQuery('.tdCalc').hide();
			// 						}
			// 					});
			// 				} else if(jQuery('input.shipping_method').is("input[type='hidden']")) {
			// 					if(jQuery("input:hidden.shipping_method").val() == 'woocommerce_transdirect')
			// 					jQuery('.tdCalc').show();
			// 				}
			// 			}, 2100);
	  //       }else{
	  //       	if(jQuery('select#billing_country')[0] == e.target) {
	  //       		jQuery('.tdCalc').hide(); // hide td calculator for other country except AU
	  //       	}
	  //       }
	  //   	});
			// }

			jQuery( document ).on( 'change', 'input.shipping_method, input[name^=shipping_method]', function() {
				console.log(jQuery(this).val());
				var context = this;
				var paymentInterval = setInterval(function () {
					if (!jQuery('#payment .blockUI').length) {
						if(jQuery(context).val() == 'woocommerce_transdirect') {
							console.log(price);
							jQuery('.tdCalc').show();
							if(jQuery('.session_price').val()){
								jQuery('.sel-courier').show();	
							}
							//jQuery('#place_order').hide();
						} else{
								console.log(price);
							jQuery('.tdCalc').hide();
							//jQuery('#place_order').show();
						}

						clearInterval(paymentInterval);
					}
				}, 100);
			});

			jQuery(document).on('click', 'span.country-name,ul.country-list li', function(){
				var countryData = jQuery("#to_location").countrySelect("getSelectedCountryData");
			    if(countryData)
			    {	
			        jQuery("#to_location").val(countryData.name);
			        jQuery("#txt_country").val(countryData.iso2);
			        if(countryData.postcode == 1){
			        	jQuery("#to_postcode").show();
			        } else {
			        	jQuery("#to_postcode").hide();
			        }
			    }
			});

			setTimeout(function(){
				jQuery("#to_postcode").hide();
		 		var locationUrl = "<?php echo plugins_url('locations.php' , __FILE__ ); ?>"; 
				jQuery.getJSON(locationUrl, {'isInternational' : 'yes'},function(data) {
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
					jQuery("body #to_location").countrySelect({
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
			}, 5000);

			setTimeout(function(){
				if(jQuery("body #to_location").val() == 'Australia'){
					jQuery("body #to_location").val('');
					jQuery("#to_postcode").hide();
				}
			}, 5000);

	});
</script>