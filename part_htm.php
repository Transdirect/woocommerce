<style>
table.form-table.shipping {border-collapse:separate; }
table.form-table.shipping td {vertical-align:top; padding:0; border:1px solid #797979; }
table.form-table.shipping td.noBorder{border:none;}
table.form-table.shipping td table td {border:none; padding:5px; vertical-align:middle;}
.logoSection, .onLineFAQ, .contactSales { border:1px solid #797979; }
.logoSection {margin-bottom:11px; height:145px;}
.onLineFAQ, .contactSales { margin-bottom:11px; min-height:75px; }



@media only screen and (-webkit-min-device-pixel-ratio:2), only screen and (min-resolution:144dpi) {
.chosen-container .chosen-results-scroll-down span, .chosen-container .chosen-results-scroll-up span, .chosen-container-multi .chosen-choices .search-choice .search-choice-close, .chosen-container-single .chosen-search input[type=text], .chosen-container-single .chosen-single abbr, .chosen-container-single .chosen-single div b, .chosen-rtl .chosen-search input[type=text] {
background-image:url(../images/chosen-sprite@2x.png)!important;
background-size:52px 37px!important;
background-repeat:no-repeat!important
}
}
.chosen-container-single .chosen-single abbr {
	top:8px
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
					<input class="" type="checkbox" name="<?php echo $field; ?>enabled" id="<?php echo $field; ?>enabled" style="" value="yes" <?php if($defult_values['enabled'] =='yes'){ ?>checked="checked" <?php } ?> > Enable Transdirect</label>
			</td>
		</tr>
		 <tr>
		  <td>Title:</td>
		  <td><input class="input-text regular-input " type="text" name="<?php echo $field; ?>title" id="<?php echo $field; ?>title" style="" value="<?php echo $defult_values['title'];?>" placeholder=""><img class="help_tip" data-tip="WooCommerce Shipping Method Name" src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		  </td>
		</tr>
		<tr>
		  <td>Email:</td>
		  <td><input class="input-text regular-input " type="email" name="<?php echo $field; ?>email" id="<?php echo $field; ?>email" style="" value="<?php echo $defult_values['email'];?>" placeholder=""><img class="help_tip" data-tip="Authentication email provided by Transdirect" src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		  <input type="hidden" name="transdirect_hidden" id="transdirect_hidden" value="1" />
		  </td>
		</tr>
		<tr>
		  <td>Password:</td>
		  <td><input class="input-text regular-input " type="password" name="<?php echo $field; ?>password" id="<?php echo $field; ?>password" style="" value="<?php echo $defult_values['password'];?>" placeholder=""><img class="help_tip" data-tip="Authentication password provided by Transdirect" src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" /></td>
		</tr>
		<tr style="display:none;">
		  <td></td>
		  <td>&nbsp;</td>
		</tr>
	  </table>
	</td>
	
	<td width="30%" rowspan="4" valign="top" class="noBorder"  >
	<div class="logoSection" style="color: #fff;background-color: #fff;border-color: #e65c00;"><img src="<?php echo plugins_url(); ?>/transdirect-shipping/logo-transdirect.png" width="216" style="margin-top: 44px;" /></div>    
	  <div class="onLineFAQ" style="color: #fff;background-color: #f60;border-color: #e65c00;text-align: center;padding-top: 50px;"><a href="https://www.transdirect.com.au/education/faqs/" target="_blank">online FAQ's</a></div>
	  <div class="contactSales" style="color: #fff;background-color: #f60;border-color: #e65c00;text-align: center;padding-top: 50px;"><a href="mailto:info@transdirect.com.au" target="_top">Contact Sales</a></div></td>
  </tr>
 
 
 
  <tr>
	<td  align="left" valign="top" >
	  <table>
		<tr>
		  <td colspan="2"><b>Warehouse Address:<img class="help_tip" data-tip="This must just be saved to the database, is used when generating a quote." src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" /></b></td>
		</tr>
		<tr>
		  <td>Postcode:</td>
		  <td><input type="text" name="<?php echo $field; ?>postcode" id="<?php echo $field; ?>postcode" style="" value="<?php echo $defult_values['postcode'];?>" placeholder=""></td>
		</tr>
		<tr>
		  <td></td>
		  <td>
		  <input type="radio" name="<?php echo $field; ?>postcode_type" value="commercial" id="Commercial" <?php if($defult_values['postcode_type'] == 'commercial'){ ?>checked<?php } ?>> Commercial										
		  <input type="radio" name="<?php echo $field; ?>postcode_type" value="residential" id="Residential" <?php if($defult_values['postcode_type'] == 'residential'){ ?>checked<?php } ?>> Residential	</td>
		</tr>
		<tr>
		  <td>&nbsp;</td>
		  <td>&nbsp;</td>
		</tr>
	  </table>
	</td>
	  
  </tr>
 
 
  <tr align="left" valign="top">
	<td colspan='' >
	  <table>
		<tr>
		  <td colspan="2"><b>Default Item Size:</b><img class="help_tip" data-tip="Saved to the database, used in the calculation if a product size is not avaliable." src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" /></td>
		</tr>
		<tr>
		  <td>Dimentions:</td>
		  <td>
		  <input type="number" name="<?php echo $field; ?>height" id="<?php echo $field; ?>height" style="width:50px;" value="<?php echo $defult_values['height'];?>" placeholder="H">
			X
			 <input type="number" name="<?php echo $field; ?>width" id="<?php echo $field; ?>width" style="width:50px;" value="<?php echo $defult_values['width'];?>" placeholder="W">
			X
			<input type="number" name="<?php echo $field; ?>length" id="<?php echo $field; ?>length" style="width:50px;" value="<?php echo $defult_values['length'];?>" placeholder="D">
			</td>
		</tr>
		<tr>
		  <td>Weight:</td>
		  <td>
		  <input type="number" name="<?php echo $field; ?>weight" id="<?php echo $field; ?>weight" style="" value="<?php echo $defult_values['weight'];?>" placeholder="kg" style="width: 275px;text-align:right;"></td>
		</tr>
		<tr>
		  <td colspan="2">* This will be used if you have not entered any details for a Product</td>
		</tr>
	  </table>
	</td>
  </tr>
 
  <tr align="left" valign="top">
	<td  colspan='2' style="border: 1px solid #000000;">
	  <table>
		<tr>
		  <td colspan="2"><b>Display Options:</b><img class="help_tip" data-tip="This selects which couriers you are able to quote from, this is an option on the api." src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		  </td>
		</tr>
		<tr>
		  <td>Available Couriers:(Shift Select)</td>
		  <td>
			<select multiple="multiple" class="multiselect " name="<?php echo $field; ?>Couriers[]" id="<?php echo $field; ?>Couriers" style="width: 275px;">
				
				<?php if(in_array('Northline',$defult_values['Couriers']))
				{ 
				?>
				<option value="Toll" selected="selected">Toll</option>
				<?php
				}
				else
				{
				?>
				<option value="Toll" >Toll</option>
				<?php
				}
				?>
				<?php if(in_array('Northline',$defult_values['Couriers']))
				{ 
				?>
				<option value="Toll Priority"   selected="selected">Toll Priority</option>
				<?php
				}
				else
				{
				?>
				<option value="Toll Priority"  >Toll Priority</option>
				<?php
				}
				?>
				<?php if(in_array('Northline',$defult_values['Couriers']))
				{ 
				?>
				<option value="Allied Express"   selected="selected">Allied Express</option>
				<?php
				}
				else
				{
				?>
				<option value="Allied Express"  >Allied Express</option>
				<?php
				}
				?>
				<?php if(in_array('Northline',$defult_values['Couriers']))
				{ 
				?>
				<option value="Couriers Please"  selected="selected">Couriers Please</option>
				<?php
				}
				else
				{
				?>
				<option value="Couriers Please"  >Couriers Please</option>
				<?php
				}
				?>
				
				<?php if(in_array('Northline',$defult_values['Couriers']))
				{ 
				?>
				<option value="Fastway" selected="selected">Fastway</option>
				<?php
				}
				else
				{
				?>
				<option value="Fastway" >Fastway</option>
				<?php
				}
				?>
				<?php if(in_array('Northline',$defult_values['Couriers']))
				{ 
				?>
				<option value="Mainfreight" selected="selected">Mainfreight</option>
				<?php
				}
				else
				{
				?>
				<option value="Mainfreight" >Mainfreight</option>
				<?php
				}
				?>
				<?php if(in_array('Northline',$defult_values['Couriers']))
				{ 
				?>
				<option value="Northline"  selected="selected">Northline</option>
				<?php
				}
				else
				{
				?>
				<option value="Northline">Northline</option>
				<?php
				}
				?>
			</select>
			<img class="help_tip" data-tip="Transdirect Couriers to be made available. (Shift Select)" src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</td>
		</tr>
		<tr>
		  <td>Quote Display:</td>
		  <td>			
			<select class="select " name="<?php echo $field; ?>quotes" id="<?php echo $field; ?>quotes" style="width: 275px;">
				<option value="Display all Quotes" <?php if($defult_values['quotes']=='Display all Quotes'){ ?> selected="selected"<?php } ?> >Display all Quotes</option>
				<option value="Display Cheapest" <?php if($defult_values['quotes']=='Display Cheapest'){ ?> selected="selected"<?php } ?>>Display Cheapest</option>
				<option value="Display Cheapest &amp; Fastest" <?php if($defult_values['quotes']=='Display Cheapest &amp; Fastest'){ ?> selected="selected"<?php } ?>>Display Cheapest &amp; Fastest</option>
			</select>
			</td>
		</tr>
		<tr>
		  <td colspan="2">
		  <input class="" type="checkbox" name="<?php echo $field; ?>fied_error" id="<?php echo $field; ?>fied_error" style="" value="yes" <?php if($defult_values['fied_error'] == 'yes'){ ?>checked="checked" <?php } ?>> 
			Fixed price on Error
			<input type="price" name="<?php echo $field; ?>fied_error_price" id="<?php echo $field; ?>fied_error_price" style="width:50px;" value="<?php echo $defult_values['fied_error_price'];?>" placeholder="">
			$ <img class="help_tip" data-tip=" If there is some error getting a quote, the fixed price listed will be returned." src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
			</td>
		</tr>
		<tr>
		  <td colspan="2"><input class="" type="checkbox" name="<?php echo $field; ?>show_courier" id="<?php echo $field; ?>show_courier" style="" value="yes" <?php if($defult_values['show_courier'] == 'yes'){ ?>checked="checked"<?php } ?>>
			Show Courier Names 
		</td>
		</tr>
		<tr>
		  <td colspan="2">
		  <input class="" type="checkbox" name="<?php echo $field; ?>Surcharge" id="<?php echo $field; ?>Surcharge" style="" value="yes" <?php if($defult_values['Surcharge'] == 'yes'){ ?>checked="checked"<?php } ?>>
			Handling Surcharge
			<input type="text" name="<?php echo $field; ?>Surcharge_price" id="<?php echo $field; ?>Surcharge_price" style="width:50px;" value="<?php echo $defult_values['Surcharge_price'];?>" placeholder="">
			$ <img class="help_tip" data-tip="Add a surcharge to the quoted amounts." src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />			
			</td>
		</tr>
		<tr>
		  <td colspan="2"><input class="" type="checkbox" name="<?php echo $field; ?>insurance_surcharge" id="<?php echo $field; ?>insurance_surcharge" style="" value="yes" <?php if($defult_values['insurance_surcharge'] == 'yes'){ ?>checked="checked"<?php } ?>>
			Include Insurance Surcharge. If you remove this item, please see FAQ's </td>
		</tr>
	  </table>
	</td>
  </tr>
</table>
</div>

