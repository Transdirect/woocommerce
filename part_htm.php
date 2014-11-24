<style>
	table.form-table.shipping {
		border-collapse:separate;
	}
	table.form-table.shipping td {
		vertical-align:top; 
		padding:0; border:1px 
		solid #797979;
	}
	table.form-table.shipping td.noBorder{
		border:none;
	}
	table.form-table.shipping td table td {
		border:none; 
		padding:5px; 
		vertical-align:middle;
	}

	.logoSection, 
	.onLineFAQ, 
	.contactSales {
		border:1px solid #797979;
	}

	.logoSection {
		margin-bottom:11px; 
		height:145px;
	}
	.onLineFAQ, 
	.contactSales { 
		margin-bottom:11px; 
		min-height:75px;
	}

	@media only screen and (-webkit-min-device-pixel-ratio:2), only screen and (min-resolution:144dpi) {
		.chosen-container .chosen-results-scroll-down span, 
		.chosen-container .chosen-results-scroll-up span, 
		.chosen-container-multi .chosen-choices .search-choice .search-choice-close, 
		.chosen-container-single .chosen-search input[type=text], 
		.chosen-container-single .chosen-single abbr, 
		.chosen-container-single .chosen-single div b, 
		.chosen-rtl .chosen-search input[type=text] {
			background-image:url(../images/chosen-sprite@2x.png)!important;
			background-size:52px 37px!important;
			background-repeat:no-repeat!important;
		}
	}

	.chosen-container-single .chosen-single abbr {
		top:8px
	}

	#autocomplete-div {
		background:#FFFFFF; 
		border: 1px solid #EDEDED; 
		border-radius: 3px 3px 3px 3px; 
		display: none; 
		height: auto;
		max-height: 150px;  
		margin: -2px 0 0 1px; 
		overflow: auto; 
		padding: 5px; 
		position: absolute;  
		width: 159px;   
		z-index: 99;
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
		border-bottom: 1px solid #f8f7f3;
	}
	#autocomplete-div ul li:hover {
		background:#ededed;
		list-style: none;
	}

	.loadinggif {
		background:url('<?php echo site_url(); ?>/wp-content/plugins/transdirect-shipping/ajax-loader.gif') no-repeat right center;
	}

</style>
<h3><?php echo $this->method_title; ?></h3>
<div style="border:1px solid #797979;  width:800px; padding:10px;">
	<table class="form-table shipping" cellpadding="0" cellspacing="10" border="0" width="100%">
        <tr align="left" valign="top">
			<td width="70%">
				<table>
					<tr>
					    <td colspan="2"><b>Authentication:</b></td>
					</tr>
					<tr valign="top">
						<td scope="row" class="titledesc">
							<label for="woocommerce_woocommerce_transdirect_enabled">Enable</label>
						</td>
						<td>
							<label for="<?php echo $field; ?>enabled">
								<input class="" type="checkbox" name="<?php echo $field; ?>enabled" id="<?php echo $field; ?>enabled" style="" value="yes" <?php if($default_values['enabled'] =='yes'){ ?>checked="checked" <?php } ?> > Enable Transdirect
							</label>
						</td>
					</tr>
		            <tr>
		                <td>Title:</td>
		                <td>
			                <input class="input-text regular-input " type="text" name="<?php echo $field; ?>title" id="<?php echo $field; ?>title" style="" value="<?php echo $default_values['title'];?>" placeholder=""><img class="help_tip" data-tip="WooCommerce Shipping Method Name" src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		                </td>
					</tr>
					<tr>
					    <td>Email:</td>
					    <td>
						    <input class="input-text regular-input " type="email" name="<?php echo $field; ?>email" id="<?php echo $field; ?>email" style="" value="<?php echo $default_values['email'];?>" placeholder=""><img class="help_tip" data-tip="Authentication email provided by Transdirect" src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					        <input type="hidden" name="transdirect_hidden" id="transdirect_hidden" value="1" />
					    </td>
					</tr>
					<tr>
					    <td>Password:</td>
					    <td>
						    <input class="input-text regular-input " type="password" name="<?php echo $field; ?>password" id="<?php echo $field; ?>password" style="" value="<?php echo $default_values['password'];?>" placeholder=""><img class="help_tip" data-tip="Authentication password provided by Transdirect" src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					    </td>
					</tr>
					<tr style="display:none;">
					    <td></td>
					    <td>&nbsp;</td>
					</tr>
	            </table>
			</td>
			<td width="30%" rowspan="4" valign="top" class="noBorder">
				<div class="logoSection" style="color: #fff;background-color: #fff;border-color: #e65c00;"><img src="<?php echo plugins_url(); ?>/transdirect-shipping/logo-transdirect.png" width="216" style="margin-top: 44px;" /></div>
				<div class="onLineFAQ" style="color: #fff;background-color: #f60;border-color: #e65c00;text-align: center;padding-top: 50px;"><a href="https://www.transdirect.com.au/education/faqs/" target="_blank">online FAQ's</a></div>
				<div class="contactSales" style="color: #fff;background-color: #f60;border-color: #e65c00;text-align: center;padding-top: 50px;"><a href="mailto:info@transdirect.com.au" target="_top">Contact Sales</a></div></td>
			</tr>
        <tr>
			<td align="left" valign="top" >
                <table>
					<tr>
					    <td colspan="2"><b>Warehouse Address:<img class="help_tip" data-tip="This must just be saved to the database, is used when generating a quote." src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" /></b></td>
					</tr>
					<tr>
					    <td>Postcode:</td>
					    <td>
						    <input type="text" name="<?php echo $field; ?>postcode" id="<?php echo $field; ?>postcode" 
						    style="" value="<?php echo $default_values['postcode'];?>" placeholder=""  autocomplete="off">
							<br/>
						    <span id="loading-div" style="display:none;"></span>
						    <div id="autocomplete-div"></div>
					    </td>
					</tr>
					<tr>
		                <td></td>
			            <td>
							<input type="radio" name="<?php echo $field; ?>postcode_type" value="business" id="Commercial" <?php if($default_values['postcode_type'] == 'business'){ ?>checked<?php } ?>> Commercial
							<input type="radio" name="<?php echo $field; ?>postcode_type" value="residential" id="Residential" <?php if($default_values['postcode_type'] == 'residential'){ ?>checked<?php } ?>> Residential
			            </td>
					</tr>
					<tr>
					    <td>&nbsp;</td>
					    <td>&nbsp;</td>
					</tr>
                </table>
			</td>
        </tr>
        <tr align="left" valign="top">
			<td colspan=''>
                <table>
					<tr>
					    <td colspan="2">
						    <b>Default Item Size:</b><img class="help_tip" data-tip="Saved to the database, used in the calculation if a product size is not avaliable." src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					    </td>
					</tr>
					<tr>
	                    <td>Dimentions:</td>
	                    <td>
						    <input type="number" name="<?php echo $field; ?>height" id="<?php echo $field; ?>height" style="width:50px;" value="<?php echo $default_values['height'];?>" placeholder="H">
							X
							<input type="number" name="<?php echo $field; ?>width" id="<?php echo $field; ?>width" style="width:50px;" value="<?php echo $default_values['width'];?>" placeholder="W">
							X
							<input type="number" name="<?php echo $field; ?>length" id="<?php echo $field; ?>length" style="width:50px;" value="<?php echo $default_values['length'];?>" placeholder="D">
						</td>
					</tr>
					<tr>
	                    <td>Weight:</td>
	                    <td>
	                        <input type="number" name="<?php echo $field; ?>weight" id="<?php echo $field; ?>weight" style="" value="<?php echo $default_values['weight'];?>" placeholder="kg" style="width: 275px;text-align:right;">
	                    </td>
					</tr>
					<tr>
					    <td colspan="2">* This will be used if you have not entered any details for a Product</td>
					</tr>
                </table>
			</td>
        </tr>
        <tr align="left" valign="top">
			<td colspan='2' style="border: 1px solid #000000;">
                <table>
					<tr>
	                    <td colspan="2">
		                    <b>Display Options:</b><img class="help_tip" data-tip="This selects which couriers you are able to quote from, this is an option on the api." src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	                    </td>
					</tr>
					<tr>
	                    <td>Available Couriers: (Shift Select)</td>
	                    <td>
							<select multiple="multiple" class="multiselect " name="<?php echo $field; ?>couriers[]" id="<?php echo $field; ?>couriers" style="width: 275px;">

								<?php if (in_array('Toll', (array) $default_values['couriers'])) { ?>
									<option value="Toll" selected="selected">Toll</option>
								<?php } else { ?>
									<option value="Toll">Toll</option>
								<?php } ?>

								<?php if (in_array('Toll Priority Sameday', (array) $default_values['couriers'])) { ?>
									<option value="Toll Priority Sameday" selected="selected">Toll Priority Sameday</option>
								<?php } else { ?>
									<option value="Toll Priority Sameday">Toll Priority Sameday</option>
								<?php } ?>

								<?php if (in_array('Toll Priority Overnight', (array) $default_values['couriers'])) { ?>
									<option value="Toll Priority Overnight" selected="selected">Toll Priority Overnight</option>
								<?php } else { ?>
									<option value="Toll Priority Overnight">Toll Priority Overnight</option>
								<?php } ?>

								<?php if (in_array('Allied Express', (array) $default_values['couriers'])) { ?>
									<option value="Allied Express" selected="selected">Allied Express</option>
								<?php } else { ?>
									<option value="Allied Express">Allied Express</option>
								<?php } ?>

								<?php if (in_array('Couriers Please', (array) $default_values['couriers'])) { ?>
									<option value="Couriers Please"  selected="selected">Couriers Please</option>
								<?php } else { ?>
									<option value="Couriers Please">Couriers Please</option>
								<?php } ?>

								<?php if (in_array('Fastway', (array) $default_values['couriers'])) { ?>
									<option value="Fastway" selected="selected">Fastway</option>
								<?php } else { ?>
									<option value="Fastway">Fastway</option>
								<?php } ?>

								<?php if (in_array('Mainfreight', (array) $default_values['couriers'])) { ?>
									<option value="Mainfreight" selected="selected">Mainfreight</option>
								<?php } else { ?>
									<option value="Mainfreight">Mainfreight</option>
								<?php } ?>

								<?php if (in_array('Northline', (array) $default_values['couriers'])) { ?>
									<option value="Northline" selected="selected">Northline</option>
								<?php } else { ?>
									<option value="Northline">Northline</option>
								<?php } ?>
							</select>
							<img class="help_tip" data-tip="Transdirect Couriers to be made available. (Shift Select)" src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
						</td>
					</tr>
					<tr>
					    <td>Quote Display:</td>
					    <td>
							<select class="select " name="<?php echo $field; ?>quotes" id="<?php echo $field; ?>quotes" style="width: 275px;">
								<option value="Display all Quotes" <?php if($default_values['quotes']=='Display all Quotes'){ ?> selected="selected"<?php } ?> >Display all Quotes</option>
								<option value="Display Cheapest" <?php if($default_values['quotes']=='Display Cheapest'){ ?> selected="selected"<?php } ?>>Display Cheapest</option>
								<option value="Display Cheapest Fastest" <?php if($default_values['quotes']=='Display Cheapest Fastest'){ ?> selected="selected"<?php } ?>>Display Cheapest &amp; Fastest</option>
							</select>
						</td>
					</tr>
					<tr>
					    <td colspan="2">
					        <input class="" type="checkbox" name="<?php echo $field; ?>fixed_error" id="<?php echo $field; ?>fixed_error" style="" value="yes" <?php if($default_values['fixed_error'] == 'yes'){ ?>checked="checked" <?php } ?>>
							Fixed Price on Error
							<input type="price" name="<?php echo $field; ?>fixed_error_price" id="<?php echo $field; ?>fixed_error_price" style="width:50px;" value="<?php echo $default_values['fixed_error_price'];?>" placeholder="">
							<?php echo get_woocommerce_currency_symbol(); ?> <img class="help_tip" data-tip=" If there is some error getting a quote, the fixed price listed will be returned." src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
						</td>
					</tr>
					<tr>
					    <td colspan="2"><input class="" type="checkbox" name="<?php echo $field; ?>show_courier" id="<?php echo $field; ?>show_courier" style="" value="yes" <?php if($default_values['show_courier'] == 'yes'){ ?>checked="checked"<?php } ?>>
							Show Courier Names
						</td>
					</tr>
					<tr>
	                    <td colspan="2">
	                        <input class="" type="checkbox" name="<?php echo $field; ?>surcharge" id="<?php echo $field; ?>surcharge" style="" value="yes" <?php if($default_values['surcharge'] == 'yes'){ ?>checked="checked"<?php } ?>>
							Handling Surcharge
							<input type="text" name="<?php echo $field; ?>surcharge_price" id="<?php echo $field; ?>surcharge_price" style="width:50px;" value="<?php echo $default_values['surcharge_price'];?>" placeholder="">
							<?php echo get_woocommerce_currency_symbol(); ?>  <img class="help_tip" data-tip="Add a surcharge to the quoted amounts." src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
						</td>
					</tr>
					<tr>
	                    <td colspan="2">
		                    <input class="" type="checkbox" name="<?php echo $field; ?>insurance_surcharge" id="<?php echo $field; ?>insurance_surcharge" style="" value="yes" <?php if($default_values['insurance_surcharge'] == 'yes'){ ?>checked="checked"<?php } ?>>
							Include Insurance Surcharge. If you remove this item, please see FAQ's
	                    </td>
					</tr>
                </table>
			</td>
        </tr>
	</table>
</div>
<script>
	jQuery(document).ready(function() {
		console.log('show');
		jQuery("#trans_frm").show();

		jQuery('body').click(function() {
			jQuery('#autocomplete-div').hide('');
			jQuery('#dynamic_content').hide('');
		});

		var latestRequestNumber = 0;
		var globalTimeout = null;

		jQuery('#woocommerce_woocommerce_transdirect_postcode').keyup(function() {

			var key_val = jQuery("#woocommerce_woocommerce_transdirect_postcode").val();
			var position = jQuery("#woocommerce_woocommerce_transdirect_postcode").position();
			var html = '';

			jQuery('#woocommerce_woocommerce_transdirect_postcode').addClass('loadinggif');

			if (key_val=='') {
				key_val=0;
			}

			jQuery.getJSON("<?php echo plugins_url('locations.php' , __FILE__ ); ?>", {'q':key_val, requestNumber: ++latestRequestNumber}, function(data) {

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
					jQuery("#autocomplete-div").css('top', parseInt(position.top) + 30);
				} else {
					// jQuery( "#autocomplete-div" ).hide();
						html = html+'<li>No Results Found</li>';
		                 var main_content = '<ul id="auto_complete">'+html+'</ul>';
		               
		                jQuery("#autocomplete-div").show();
				        jQuery("#autocomplete-div").html(main_content);
				        jQuery("#autocomplete-div").css('left', position.left);
				        jQuery("#autocomplete-div").css('top', parseInt(position.top) + 30);
				        jQuery("#autocomplete-div").css('overflow-y','hidden');

				        jQuery('#woocommerce_woocommerce_transdirect_postcode').removeClass('loadinggif');
				}

				jQuery('#woocommerce_woocommerce_transdirect_postcode').removeClass('loadinggif');
			});
		});
	});

	function get_value(postcode, locality) {
		jQuery("#woocommerce_woocommerce_transdirect_postcode").val(postcode + ',' + locality);
		jQuery("#autocomplete-div").html('');
		jQuery( "#autocomplete-div" ).hide();
	}
	
</script>

