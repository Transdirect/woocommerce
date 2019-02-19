
<?php
/**
 * Shipping Transdirect Settings
 *
 * @author 		Transdirect Developer
 * @version     4.7
 */


?>
<!-- CSS styles -->
<style>
	table.form-table.shipping {
		border-collapse:separate;
	}

	table.form-table.shipping td {
		vertical-align:top;
		padding:0;
	}

	table.form-table.shipping td.noBorder{
		border:none;
	}

	table.form-table.shipping td table td {
		border:none;
		padding:5px;
		vertical-align:middle;
	}

	.form-table th {
		width: 0px !important;
	}

	table.courier-name {
		border-collapse: collapse !important;
	}

	table.courier-name td{
		border: 1px solid #ccc !important;
		text-align: center;
	}

	table.courier-name th{
		font-weight: 700;
		text-align: center;
		border: 1px solid #ccc !important;
	}

	tr.order-container {
		outline:1px solid #ccc;
		outline-offset:-10px;
	}

	td.order {
		border:none;
		padding: 10px !important;
		padding-left: 1px !important;
	}

	td.from-address-container {
		border:none;
		padding-right: 30px !important;
	}

	table.from-address {
		padding-bottom: 15px !important;
	}

	table.from-address input[type="text"]{
		text-transform: capitalize;
	}

	.border-fomat {
		border:1px solid #ccc;
	}

	#main-container {
		width:800px;
		padding:10px;
	}

	#main-container a {
		text-decoration: none;
		color: #fff;
	}

	#main-container a:focus {
		color: #000;
	}

	.hidden-table {
		display: none;
	}

	p.submit input.button-primary,
	p.submit input.button-primary:hover,
	p.submit input.button-primary:focus,
	.btn-warning {
	  color: #fff;
	  background-color: #f60;
	  border-color: #e65c00;
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

	p.submit input.button-primary{
		line-height: 0;
		padding: 20px;
	}

	.btn-warning {
	 	line-height: 1.625;
	 	padding: 8px 20px;
	}

	.onLineFAQ,
	.contactSales {
		border:1px solid #ddd;
	}

	.logoSection,
	.onLineFAQ,
	.contactSales {
		margin-bottom:5px;
	}

	.onLineFAQ,
	.contactSales {
		background-color: #f60;
		text-align: center;
		padding: 40px;
		cursor: pointer;
	}

	.onLineFAQ {
		margin-left: 10px;
	}

	.logoSection {
		height:145px;
		margin-top:5px;
	}

	.logoSection img {
		margin-top: 30px;
	}

	.chosen-container-single .chosen-single abbr {
		top:8px
	}

	.autocomplete-div {
		background:#FFFFFF;
		border: 1px solid #EDEDED;
		border-radius: 3px 3px 3px 3px;
		display: none;
		height: auto;
		max-height: 150px;
		margin: -2px 0 0 1px;
		overflow: auto;
		padding: 5px;
		padding-left: 10px;
		position: absolute;
		font-size: 15px;
		width: 165px;
		z-index: 99;
	}

	.autocomplete-div ul {
		margin: 0 0 0px 0px !important;
	}

	.autocomplete-div ul li {
		padding:0 !important;
		margin:0 !important;
		text-indent:0 !important;
		list-style: none;
		cursor:pointer;
		border-bottom: 1px solid #f8f7f3;
	}

	.autocomplete-div ul li:hover {
		background:#ededed;
		list-style: none;
	}

	.loadinggif {
		background:url('<?php echo site_url(); ?>/wp-content/plugins/transdirect-shipping/ajax-loader.gif') no-repeat right center;
	}

	.placeholder {
	    position: relative;
	    display: inline;
	}

	.placeholder::after
	{
	    position: absolute;
	    left: 80px;
	    top: 0px;
	    content: attr(data-placeholder);
	    pointer-events: none;
	    opacity: 0.6;
	}

	.placeholder.dimension:after {
		left: 45px !important;
	}

	.placeholder.dimension input{
		padding-right: 30px;
	}

	.placeholder.dimension input[type="number"]::-webkit-outer-spin-button,
	.placeholder.dimension input[type="number"]::-webkit-inner-spin-button {
	    -webkit-appearance: none;
	    margin: 0;
	}

	.placeholder.weight input[type="number"]::-webkit-outer-spin-button,
	.placeholder.weight input[type="number"]::-webkit-inner-spin-button {
	    -webkit-appearance: none;
	    margin: 0;
	}

	.placeholder.weight:after {
		left: 80px !important;
	}

	.placeholder.weight input {
		width:100px;
		padding-right: 25px;
	}

	select.address-type,
	select.order-sync,
	input.order-sync {
		width: 275px;
	}

	input.quotes_display {
		width:70px;
	}

	input.quotes_display_rename {
		width: 120px;
	}

	.quote-options {
		width: 60px;
	}
	.header-style-insurance {
		left: 15px;
	}

	.header-style-surcharge {
		padding-right:0px !important;
	}
	.header-style-insurance .include_surcharge_courier {
		margin-top: 0px;
		margin-left: 5px;
	}

	.header-style-services {
		left: 15px;
	}

	.header-style-enabled {
		width:50px;
		left:10px;
	}

	.header-style-courier {
		width:200px;
		left: 10px;
	}

	.header-style-rename .help_tip {
		float:none !important;
		margin-left:10px !important;
	}

	.include_surcharge_container {
		display: inline-flex;
		display: -webkit-inline-flex;
		display: -ms-inline-flexbox;
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

	.woocommerce table.form-table input.regular-input.api {
		width: 25em !important;
	  height: 2.5em !important;
	}
	.trans-order-status-sync{
		padding-left: 10px;
	}
	.auth-error {
		color: #a94442;
		background-color: #f2dede;
		border-color: #ebccd1;
		border: 1px solid transparent;
	}
</style>
<!-- end of styles -->
<h3><?php echo $this->method_title; ?></h3>

<div id="main-container">
	<table class="form-table shipping" cellpadding="0" cellspacing="10" border="0" width="100%">
		<!-- Logo Section -->
		<tr class="auth-error" style="display:none;">
			<td class="border-fomat" colspan="2">
				<table width="100%">
					<tr>
						<td>
							<div>
								<p id="auth-error-message"></p>
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="border-fomat">
				<table width="100%">
					<td colspan="3" width="50%">
						<div class="logoSection">
							<img src="<?php echo plugins_url(); ?>/transdirect-shipping/logo-transdirect.png" width="100%"/>
						</div>
					</td>
					<td width="25%">
						<a href="https://www.transdirect.com.au/education/faqs/" target="_blank" class="btn-warning onLineFAQ">
							online FAQ's
						</a>
					</td>
					<td width="25%">
						<a href="mailto:info@transdirect.com.au" target="_top" class="btn-warning contactSales">
							Contact Sales
						</a>
					</td>
				</table>
			</td>
		</tr>
		<!-- Enable Transdirect Section -->
		<?php
			if (version_compare(phpversion(), '5.6', '<')) {
		?>
		<tr>
			<td class="border-fomat" colspan="2">
				<table width="100%">
					<tr>
						<td>
							<?php
								echo "<h4> Your current PHP version: ".(PHP_VERSION+0).PHP_EOL."</h4>"; 
								echo "<h4>Please upgrade your PHP to V5.6</h4>";
							?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<?php }
		?>
		<tr>
			<td class="border-fomat" colspan="2">
				<table width="100%">
					<tr>
						<td>
							<label for="<?php echo $field; ?>enabled">
							 	<input class="" type="checkbox" name="<?php echo $field; ?>enabled" id="<?php echo $field; ?>enabled" style="" value="yes"
								 <?php if ($default_values['enabled'] == 'yes') : ?> checked="checked" <?php endif; ?> >
							</label>
						</td>
						<td>
							Enable Transdirect
							<img class="help_tip" data-tip="Enable Transdirect Shipping on your shipping method"
						    src="<?php echo plugins_url();?>/woocommerce/assets/images/help.png" height="16" width="16" />
						</td>
					</tr>
		            <tr>
		            	<td>Title:</td>
		                <td>
			                <input class="input-text regular-input" type="text" name="<?php echo $field; ?>title"
			                id="<?php echo $field; ?>title" value="<?php echo $default_values['title']; ?>">
			                <img class="help_tip" data-tip="WooCommerce Shipping Method Name"
			                src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		                </td>
					</tr>
				</table>
			</td>
		</tr>
		<!-- Frontend Styles Transdirect Calculator Section -->
		<tr>
			<td class="border-fomat" colspan="2">
				<table width="100%">
					<tr>
					    <td colspan="2">
					    	<b>Show on cart:</b>
					    	<img class="help_tip" data-tip="This will change the view on your transdirect calculator"
							src="<?php echo plugins_url();?>/woocommerce/assets/images/help.png" height="16" width="16" />
					    </td>
					</tr>
					<tr>
						<td>
							<label for="<?php echo $field; ?>trans_title">
								Set title of calculate shipping :
								<input class="input-text regular-input" type="text" name="<?php echo $field;?>trans_title" id="<?php echo $field; ?>trans_title"
						     value="<?php if(isset($default_values['trans_title']) && $default_values['trans_title'] != '') { echo $default_values['trans_title']; } else { echo 'Get a shipping estimate';} ?>">
							</label>
						</td>
					</tr>
					<tr>
						<td>
							<label for="<?php echo $field; ?>address_type">
								Default Address Type:
								<select class="select address-type" name="<?php echo $field; ?>address_type" id="<?php echo $field; ?>address_type">
									<option value="Commercial" <?php if ($default_values['address_type'] == 'Commercial') : ?> selected="selected"<?php endif; ?> >Commercial</option>
									<option value="Residential" <?php if ($default_values['address_type'] == 'Residential') : ?> selected="selected"<?php endif; ?>>Residential</option>
								</select>
							</label>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<!--  Authentication API KEY -->
        <tr>
			<td width="100%" colspan="2" class="border-fomat">
				<table width="100%">
					<tr>
					    <td colspan="2">
					    	<b>Authentication:</b>
					    	<img class="help_tip" data-tip="These details must be validated when entered,
                            and then saved to the database, they are used in all requests to the server."
                            src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					    </td>
					</tr>
					<tr>
						<td>Choose Authentication Type:</td>
						<td>
							<select class="select" name="<?php echo $field; ?>authtype" id="<?php echo $field; ?>authtype" style="width: 275px;">
								<option value="Email and Password" <?php if ($default_values['authtype'] == 'Email and Password') : ?> selected="selected"<?php endif; ?>>Email and Password</option>
								<option value="API Key" <?php if ($default_values['authtype'] == 'API Key') : ?> selected="selected"<?php endif; ?> >API Key</option>
							</select>
						</td>
					</tr>
						<!--  Authentication Email and password section -->
					<tr class="password-email">
					    <td>Email:</td>
					    <td>
						    <input class="input-text regular-input " type="email" name="<?php echo $field;?>email" id="<?php echo $field; ?>email"
						     value="<?php echo $default_values['email'];?>" autocorrect="off" autocapitalize="off">
						     <img class="help_tip" data-tip="Authentication email provided by Transdirect"
						     src="<?php echo plugins_url();?>/woocommerce/assets/images/help.png" height="16" width="16" />
					        <input type="hidden" name="transdirect_hidden" id="transdirect_hidden" value="1" />
					    </td>
					</tr>
					<tr class="password-email">
					    <td>Password:</td>
					    <td>
						    <input class="input-text regular-input" type="password" name="<?php echo $field;?>password"
						    id="<?php echo $field; ?>password" value="<?php echo $default_values['password'];?>">
						    <img class="help_tip" data-tip="Authentication password provided by Transdirect"
						    src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					    </td>
					</tr>
					<tr class="api-key">
						<td>API Key:</td>
		                <td>
			                 <input class="input-text regular-input api" type="text" name="<?php echo $field;?>api_key" id="<?php echo $field; ?>api_key"
						     value="<?php echo $default_values['api_key']; ?>">

						     <img class="help_tip" data-tip="API key of the account provided by Transdirect"
						     src="<?php echo plugins_url();?>/woocommerce/assets/images/help.png" height="16" width="16" />
					        <input type="hidden" name="transdirect_hidden" id="transdirect_hidden" value="1" />
		                </td>
					</tr>
					<tr class="hidden-table">
					    <td></td>
					    <td>&nbsp;</td>
					</tr>
	            </table>
			</td>
		</tr>

		<!-- Set up your default warehouse settings. e.g: warehouse address and default sizes] -->
        <tr>
			<td align="left" width="100%" class="border-fomat warehouse-td" colspan="2">
				<table width="100%">
				<tr><td>
				<?php
				$totalHouse = 0;
				if(isset($default_values['postcode']) && !empty($default_values['postcode']) && is_array($default_values['postcode'])){
				for ($i=0; $i < count($default_values['postcode']); $i++) { ?>
        <table width="100%" id="warehouse-table<?php echo $totalHouse; ?>">
					<tr>
					    <td colspan="3">
					    	<b>Warehouse Address :</b>
					    	<img class="help_tip" data-tip="This must just be saved to the database, is used when generating a quote."
					    	src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					 	</td>
					</tr>
					<tr>
					    <td>Postcode:
								<img class="help_tip" data-tip="Autocomplete list of postcode, suburb."
						    	src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					    </td>
					    <td>
						    <input type="text" name="<?php echo $field; ?>postcode[]" id="<?php echo $field; ?>postcode<?php echo $i;?>" class="warehouse-postcode"
						    style="" value="<?php echo $default_values['postcode'][$i]; ?>" autocomplete="off" placeholder="Enter youe postcode, suburb">
								<br/>
						    <span class="loading-div" class="hidden-table"></span>
						    <div class="autocomplete-div"></div>
					    </td>
					    <td><input type="button" id="<?php echo $i; ?>" class="btn-delete" name="btn-delete" value="Delete"></td>
					</tr>
					<tr>
						<td></td>
						<td colspan="2">
							<input type="radio" name="<?php echo $field; ?>postcode_type[<?php echo $totalHouse; ?>][]" value="business" id="Commercial<?php echo $i;?>"
							<?php if ($default_values['postcode_type'][$i][0] == 'business') : ?>checked<?php endif; ?>> Commercial
							<input type="radio" name="<?php echo $field; ?>postcode_type[<?php echo $totalHouse; ?>][]" value="residential" id="Residential<?php echo $i;?>"
							<?php if ($default_values['postcode_type'][$i][0] == 'residential') : ?>checked<?php endif; ?>> Residential
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<hr>
						</td>
					</tr>
          </table>
          <?php
          	$totalHouse++;
        		}
					} else {?>
					<table width="100%" id="warehouse-table<?php echo $totalHouse; ?>">
					<tr>
					    <td colspan="3">
					    	<b>Warehouse Address :</b>
					    	<img class="help_tip" data-tip="This must just be saved to the database, is used when generating a quote."
					    	src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					 	</td>
					</tr>
					<tr>
					    <td>Postcode:
							<img class="help_tip" data-tip="Autocomplete list of postcode, suburb."
					    	src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					    </td>
					    <td>
						    <input type="text" name="<?php echo $field; ?>postcode[]" id="<?php echo $field; ?>postcode<?php echo $totalHouse;?>" class="warehouse-postcode"
						    style="" autocomplete="off" placeholder="Enter youe postcode, suburb">
							<br/>
						    <span class="loading-div" class="hidden-table"></span>
						    <div class="autocomplete-div"></div>
					    </td>
					    <td><input type="button" id="<?php echo $totalHouse; ?>" class="btn-delete" name="btn-delete" value="Delete"></td>
					</tr>
					<tr>
           	<td></td>
	          <td colspan="2">
							<input type="radio" name="<?php echo $field; ?>postcode_type[<?php echo $totalHouse; ?>][]" value="business" id="Commercial<?php echo $i;?>"> Commercial
							<input type="radio" name="<?php echo $field; ?>postcode_type[<?php echo $totalHouse; ?>][]" value="residential" id="Residential<?php echo $i;?>"> Residential
	          </td>
					</tr>
					<tr>
						<td colspan="3">
							<hr>
						</td>
					</tr>
          </table>
          <?php
          	$totalHouse++;
          } ?>
          </td>
          </tr>
          <tr>
          	<td>
          		<div class="new-warehouse" id="warehouse-content">
					    </div>
          	</td>
          </tr>
          <tr>
				    <td>
				    <input type="button" class="button-primary" id="add-warehouse" value="Add Warehouse"></td>
				    <td>&nbsp;</td>
				    <td></td>
					</tr>
          </table>
			</td>
			</tr>
			<tr>
			<td align="left" width="100%" class="border-fomat" colspan="2">
                <table>
					<tr>
					    <td colspan="2">
						    <b>Default Item Size:</b>
						    <img class="help_tip" data-tip="Saved to the database, used in the calculation if a product size is not avaliable."
						    src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					    </td>
					</tr>
					<tr>
	                    <td>Dimentions:</td>
	                    <td>
	                    	<div class="placeholder dimension" data-placeholder="cm">
							    <input type="number" name="<?php echo $field; ?>height" id="<?php echo $field; ?>height"
							    style="width:70px;" value="<?php echo $default_values['height']; ?>">
						    </div>
							<span>&nbsp;X&nbsp;</span>
							<div class="placeholder dimension" data-placeholder="cm">
								<input type="number" name="<?php echo $field; ?>width" id="<?php echo $field; ?>width"
								style="width:70px;" value="<?php echo $default_values['width']; ?>">
							</div>
							<span>&nbsp;X&nbsp;</span>
							<div class="placeholder dimension" data-placeholder="cm">
								<input type="number" name="<?php echo $field; ?>length" id="<?php echo $field; ?>length"
								style="width:70px;" value="<?php echo $default_values['length']; ?>">
							</div>
						</td>
					</tr>
					<tr>
	                    <td>Weight:</td>
	                    <td>
		                    <div class="placeholder weight" data-placeholder="kg">
		                        <input type="number" name="<?php echo $field; ?>weight" id="<?php echo $field; ?>weight"
		                        value="<?php echo $default_values['weight']; ?>">
		                     </div>
	                    </td>
					</tr>
					<tr>
					    <td colspan="2">* This will be used if you have not entered any details for a Product</td>
					</tr>
                </table>
			</td>
        </tr>
		<!--  Save orders based on this settings in api -->
        <tr class="order-container">
			<td align="left" class="order" width="50%">
        <table width="100%">
					<tr>
					    <td>
					    	<b>Order Sync</b>
					    	 <img class="help_tip" data-tip="Sync orders in woocommerce based on status and date selected."
						    src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
					    </td>
					</tr>
					<tr>
					    <td width="100%">
					    	<label for="<?php echo $field; ?>enable_order">
								<input class="order-status-sync" type="checkbox" name="<?php echo $field; ?>enable_order"
								id="<?php echo $field; ?>enable_order" onclick="order_sync()" value="yes"
								<?php if ($default_values['enable_order'] == 'yes') : ?>checked="checked" <?php endif; ?>>
								Enable Order Sync Functionality
								<img class="help_tip" data-tip="This can be enabled if all from address feild is filled out."
						    	src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
							</label>
						</td>
					</tr>
					<tr>
		                <td>Sync if Status is:</td>
					</tr>
					<tr>
						<td>
							<?php /* ?>
							<select class="select order-sync" name="<?php echo $field; ?>order_status" id="<?php echo $field; ?>order_status">
								<?php $orderStat = wc_get_order_statuses();
									foreach ($orderStat as $key => $value) :
									   	echo '<option value="' . $value . '" ' . ($default_values['order_status'] != $value ? '>' : 'selected="selected">') . $value . '</option>';
									endforeach;  ?>
							</select>
							<?php */ ?>
							<div class="trans-order-status-sync">
								<?php $orderStat = wc_get_order_statuses();
								foreach ($orderStat as $key => $value) : ?>
									<p>
										<input type="checkbox" name="<?php echo $field; ?>order_status[]"
										id="<?php echo $field; ?>order_status[]" class="trans-sync-ele" value="<?php echo $value; ?>"
										<?php if(is_array($default_values['order_status'])) { if (in_array($value, $default_values['order_status'])) : ?>checked="checked" <?php endif; }?>>
										<?php echo $value; ?>
									</p>
								<?php
								endforeach; ?>
							</div>
						</td>
					</tr>
					<tr>
		        <td>From Date</td>
					</tr>
					<tr>
						<td>
							<?php if ($default_values['order_date'] == "" ) { $default_values['order_date'] = date("Y-m-d"); } ?>
							<input class="order-sync" type="date" name="<?php echo $field; ?>order_date" id="<?php echo $field; ?>order_date"
	                        value="<?php echo $default_values['order_date']; ?>">
						</td>
					</tr>
                </table>
			</td>
			<td align="left" width="50%" class="from-address-container">
                <table class="from-address">
					<tr>
					    <td colspan="2"></td>
					</tr>
					<tr>
	                    <td colspan="2"><b>From Address:</b></td>
					</tr>
					<tr>
						<td>Company Name</td>
	                    <td>
	                        <input class="order-sync" type="text" name="<?php echo $field; ?>order_name" id="<?php echo $field; ?>order_name"
	                        value="<?php echo $default_values['order_name']; ?>">
	                    </td>
					</tr>
					<tr>
						<td>Email</td>
	                    <td>
	                        <input class="order-sync" type="email" name="<?php echo $field; ?>order_email" id="<?php echo $field; ?>order_email"
	                        value="<?php echo $default_values['order_email']; ?>">
	                    </td>
					</tr>
					<tr>
						<td>Phone</td>
	                    <td>
	                        <input class="order-sync" type="text" name="<?php echo $field; ?>order_phone" id="<?php echo $field; ?>order_phone"
	                        value="<?php echo $default_values['order_phone']; ?>">
	                    </td>
					</tr>
					<tr>
						<td>Address</td>
	                    <td>
	                        <input class="order-sync" type="text" name="<?php echo $field; ?>order_address" id="<?php echo $field; ?>order_address"
	                        value="<?php echo $default_values['order_address']; ?>">
	                    </td>
					</tr>
					<tr>
						<td>Suburb</td>
	                    <td>
						    <input class="order-sync" type="text" name="<?php echo $field; ?>order_suburb" id="<?php echo $field; ?>order_suburb"
						     value="<?php echo $default_values['order_suburb']; ?>" autocomplete="off" placeholder="Enter your postcode, suburb">
							<br/>
						    <span class="loading-div" class="hidden-table"></span>
						    <div class="autocomplete-div"></div>
	                    </td>
					</tr>
                </table>
			</td>
        </tr>
		<!--  Quotes -->
	    <tr align="left" valign="top">
			<td colspan='2' class="border-fomat">
                <table width="100%">
					<tr>
	                    <td colspan="2">
		                    <b>Quote Options:</b><img class="help_tip" data-tip="This selects which couriers you are able to quote from,
		                    this is an option on the api." src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
		                    <!-- <button style="float:right;" class="btn-warning" onclick="">Reset All Options</button> -->
	                    </td>
					</tr>
					<tr>
						<td colspan="2">
							Quote Display:<img class="help_tip" data-tip="This is manipulation that the module needs to do to the results
							that it gets back from the API. The options that are listed in the dropdown are self explanitory."
                            src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
						</td>
					</tr>
					<tr>
						<td width="40%">
							<select class="select" name="<?php echo $field; ?>quotes" id="<?php echo $field; ?>quotes" style="width: 275px;">
								<option value="Display all Quotes" <?php if ($default_values['quotes'] == 'Display all Quotes') : ?> selected="selected"<?php endif; ?> >Display all Quotes</option>
								<option value="Display Cheapest" <?php if ($default_values['quotes'] == 'Display Cheapest') : ?> selected="selected"<?php endif; ?>>Display Cheapest</option>
								<option value="Display Cheapest Fastest" <?php if ($default_values['quotes'] == 'Display Cheapest Fastest') : ?> selected="selected"<?php endif; ?>>Display Cheapest &amp; Fastest</option>
							</select>
						</td>
						<td width="60%">
							<label for="<?php echo $field; ?>enabled_pickup">
								<input type="checkbox" name="<?php echo $field; ?>enabled_pickup"
								id="<?php echo $field; ?>enabled_pickup"
								value="yes" <?php if ($default_values['enabled_pickup'] == 'yes') : ?>checked="checked" <?php endif; ?>>
								Pickup - If Tail Lift Required Include in Quote
							</label>
						</td>
					</tr>
					<tr>
						<td></td>
						<td>
							<label for="<?php echo $field; ?>enabled_delivery">
								<input type="checkbox" name="<?php echo $field; ?>enabled_delivery"
								id="<?php echo $field; ?>enabled_delivery"
								value="yes" <?php if ($default_values['enabled_delivery'] == 'yes') : ?>checked="checked" <?php endif; ?>>
								Delivery - If Tail Lift Required Include in Quote
							</label>
						</td>
					</tr>
					<tr>
					    <td colspan="2">
	                        <input class="" type="checkbox" name="<?php echo $field; ?>surcharge" id="<?php echo $field; ?>surcharge"
	                        value="yes" <?php if ($default_values['surcharge'] == 'yes') : ?>checked="checked"<?php endif; ?>>

							Apply General Surcharge<img class="help_tip" data-tip="Add a surcharge to the quoted amounts."
							src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />

							<input type="number" name="<?php echo $field; ?>surcharge_price" id="<?php echo $field; ?>surcharge_price"
							 class="quote-options" value="<?php echo $default_values['surcharge_price']; ?>">

							<select class="select " name="<?php echo $field; ?>unit" id="<?php echo $field; ?>unit">
								<option value="$" <?php if($default_values['unit']=='$'): ?> selected="selected"<?php endif; ?>>$</option>
								<option value="%" <?php if($default_values['unit']=='%'): ?> selected="selected"<?php endif; ?> >%</option>
							</select>
						</td>
					</tr>
					<tr>
					    <td colspan="2">
					        <input class="" type="checkbox" name="<?php echo $field; ?>fixed_error" id="<?php echo $field; ?>fixed_error" style=""
					        value="yes" <?php if ($default_values['fixed_error'] == 'yes') : ?>checked="checked" <?php endif; ?>>

							Fixed Price on Error<img class="help_tip" data-tip="If there is some error getting a quote, just return the set fixed price."
							src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />

							<div class="placeholder weight" data-placeholder="$">
								<input type="number" name="<?php echo $field; ?>fixed_error_price" id="<?php echo $field; ?>fixed_error_price"
								 class="quote-options" value="<?php echo $default_values['fixed_error_price']; ?>">
							</div>
						</td>
					</tr>
					<tr>
	                    <td colspan="2">
		                    <input class="include_surcharge" type="checkbox" name="<?php echo $field; ?>insurance_surcharge"
		                    id="<?php echo $field; ?>insurance_surcharge" onclick="selectAll()"
		                    value="yes" <?php if ($default_values['insurance_surcharge'] == 'yes') : ?>checked="checked"<?php endif; ?>>
							Include Insurance Surcharge. If you remove this item, please see FAQ's
							<img class="help_tip" data-tip="If this checked all the carriers will display in getting a quotes."
                            src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
	                    </td>
					</tr>
                	<tr>
                		<td style="padding:20px;" colspan="2">
							<table width="100%" class="courier-name">
								<tr>
									<th class="header-style-enabled">
										Enable
									</th>
									<th class="header-style-courier">
										Courier
									</th>
									<th class="header-style-services">
										Service
									</th>
									<th class="header-style-rename">
										Rename/Group<img class="help_tip" data-tip="You can rename the couriers and group it as one."
										src="<?php echo plugins_url(); ?>/woocommerce/assets/images/help.png" height="16" width="16" />
									</th>
									<th class="header-style-surcharge">
										Surcharge
										<!-- <select style="margin-top:-4px;" class="select " name="<?php //echo $field; ?>courier_unit" id="<?php// echo $field; ?>courier_unit">
											<option value="%" <?php //if($default_values['courier_unit']=='%'): ?> selected="selected"<?php// endif; ?> >%</option>
											<option value="$" <?php //if($default_values['courier_unit']=='$'): ?> selected="selected"<?php //endif; ?>>$</option>
										</select> -->
									</th>
									<th class="header-style-insurance">
										Insurance <input class="include_surcharge_courier" type="checkbox" onclick="selectAllInsurance()" value="yes"
										name="<?php echo $field; ?>include_surcharge_courier"  id="<?php echo $field; ?>include_surcharge_courier"
										<?php if ($default_values['include_surcharge_courier'] == 'yes') : ?>checked="checked"<?php endif; ?>>
									</th>
									<th>
										Courier Fixed Price
									</th>
								</tr>
									<?php if(isset($couriers_name) && !empty($couriers_name)) {?>
										<?php foreach ($couriers_name as $courier => $value): ?>
											<?php
												$services = $value;
												if (gettype($value) == 'string') {
													$services = new stdClass();
													$services->{$courier} = $value;
												}
											?>
											<?php foreach ($services as $key => $services): ?>
												<tr>
													<td>
														<input type="checkbox" name="<?php echo $field .'cor_'. $services; ?>"
														id="<?php echo $field; ?>value[]" class="enable_courier" value="yes" onchange="selectCourier()"
														<?php if ($default_values['cor_'. $services] == 'yes') : ?>checked="checked" <?php endif; ?>>
													</td>
													<td>
														<?php echo $courier; ?>
													</td>
													<td>
														<?php echo gettype($value) == 'string' ? '' : $key; ?>
													</td>
													<td>
														<input type="text" name="<?php echo $field .'rg_'. $services; ?>"
														id="<?php echo $field .'rg_'. $services; ?>" class="quotes_display_rename"
						                        		value="<?php echo $default_values['rg_'.$services]; ?>">
													</td>
													<td>
														<div class="include_surcharge_container">
															<input type="number" name="<?php echo $field .'cg_'. $services; ?>"
															id="<?php echo $field .'cg_'. $services; ?>" class="quotes_display"
							                        		value="<?php echo $default_values['cg_'. $services]; ?>" >

							                        		<select class="select " name="<?php echo $field .'cu_'. $services; ?>" id="<?php echo $field .'cu_'. $services; ?>">
																<option value="$" <?php if($default_values['cu_'. $services]=='$'): ?> selected="selected"<?php endif; ?>>$</option>
																<option value="%" <?php if($default_values['cu_'. $services]=='%'): ?> selected="selected"<?php endif; ?> >%</option>
															</select>
														</div>
													</td>
													<td>
														<input class="enable-insurance" type="checkbox" name="<?php echo $field .'enabsurg_'. $services; ?>"
														id="<?php echo $field; ?>enabsurg[]" value="yes" onchange="selectInsurance()"
														<?php if ($default_values['enabsurg_'.$services] == 'yes') { ?>checked="checked" <?php } ?>>
													</td>
													<td>
														<div class="include_surcharge_container">
															<input type="number" step="0.01" min="0" name="<?php echo $field . 'fpval_'.$services; ?>" class="quotes_display_rename" value="<?php echo $default_values['fpval_'.$services]; ?>">
														</div>
													</td>
												</tr>
											<?php endforeach;?>
										<?php endforeach; ?>
									<?php } ?>
							</table>
						</td>
                	</tr>
                </table>
			</td>
        </tr>

        <tr>
            <td colspan="2" class="border-fomat">
            	<table width="100%">
                    <tr>
                        <td>
                            <b>Order Boxing:</b>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            Warning - Using this functionality may result in in some shipping costs being approximated
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="<?php echo $field; ?>enabled_group_box">
                                <input type="checkbox" name="<?php echo $field; ?>enabled_group_box"
                                id="<?php echo $field; ?>enabled_group_box"
                                value="yes" <?php if ($default_values['enabled_group_box'] == 'yes') { ?>checked="checked" <?php } ?>>
                                Group Smaller items in "Boxes" for the purpose of quoting
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            Box Size:
                            <div class="placeholder" data-placeholder="kg">
	                            <input class="input-text regular-input" type="number" name="<?php echo $field; ?>box_size"
	                            id="<?php echo $field; ?>box_size" style="width:100px; padding-right: 30px;"
	                            value="<?php echo $default_values['box_size']; ?>" >
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
	</table>
</div>

<!-- SCRIPTS  -->
<script>
	// onready
	jQuery(document).ready(function() {
		jQuery("#trans_frm").show();

		jQuery('body').click(function() {
			jQuery('.autocomplete-div').hide('');
			jQuery('#dynamic_content').hide('');
		});

		//get the post code and suburb
		getPostcodeSub();
		// order details function
		order_details();
		// auth details function
		auth_details();
		//auth type on change
		authtypechange();
		//number validation in all fields
		numberValidation();

	});
	// end of on ready


	function getPostcodeSub(){

		var latestRequestNumber = 0;
		var globalTimeout = null;

		jQuery(document).on('keyup', '.warehouse-postcode,'
			+ '#woocommerce_woocommerce_transdirect_order_suburb', function() {

			// variables
			var key_val = jQuery(this).val();
			var position = jQuery(this).position();
			var html = '';
			var context = this;

			jQuery(this).addClass('loadinggif');

			if (key_val=='') {
				key_val=0;
			}

			jQuery.getJSON("<?php echo plugins_url('locations.php', __FILE__); ?>", {'q':key_val, requestNumber: ++latestRequestNumber}, function(data) {

				if (data.requestNumber < latestRequestNumber) {
					return;
				}

				if (data.locations != '' && key_val != '0') {
					jQuery.each(data.locations, function(index, value ) {
						if(value.postcode &&  value.locality) {
							html = html+'<li onclick="get_value(\''+value.postcode+'\',\''+value.locality+'\',\''+ context.id +'\')">'+value.postcode+', '+value.locality+'</li>';
						}
					});

					var main_content = '<ul id="auto_complete">'+html+'</ul>';
					jQuery(".loading-div").hide();
					jQuery(".autocomplete-div").show();
					jQuery(".autocomplete-div").html(main_content);
					jQuery(".autocomplete-div").css('left', position.left);
					jQuery(".autocomplete-div").css('top', parseInt(position.top) + 30);

				} else {
						html = html+'<li>No Results Found</li>';
		                var main_content = '<ul id="auto_complete">'+html+'</ul>';
		                jQuery(".autocomplete-div").show();
				        jQuery(".autocomplete-div").html(main_content);
				        jQuery(".autocomplete-div").css('left', position.left);
				        jQuery(".autocomplete-div").css('top', parseInt(position.top) + 30);
				        jQuery(".autocomplete-div").css('overflow-y','hidden');
				        jQuery(context).removeClass('loadinggif');
				}

				jQuery(context).removeClass('loadinggif');
			});
		});
	}


	function numberValidation() {
		// on input
		jQuery('.form-table').on('input', 'input[type="number"]', function() {
			jQuery(this).each(function() {
				if(jQuery(this).val() <= 0 && jQuery(this).val()){
					alert('Please input valid value.');
					jQuery(this).css('border', '1px solid red');
					jQuery('input[type="submit"].button-primary').attr('disabled', 'disabled');
				} else {
					jQuery('input[type="submit"].button-primary').removeAttr('disabled');
					jQuery(this).css('border', '1px solid #ddd');
				}
			});
		});
	}


	function authtypechange() {
		// on change
		jQuery('#woocommerce_woocommerce_transdirect_authtype').on('change', function() {
			if(jQuery('#woocommerce_woocommerce_transdirect_authtype').val() == "API Key") {

				if(!validateEmail(jQuery('#woocommerce_woocommerce_transdirect_email').val())) {
					jQuery('#woocommerce_woocommerce_transdirect_email').val("");
					jQuery('#woocommerce_woocommerce_transdirect_password').val("");
				}

				jQuery('.api-key').show();
				jQuery('.password-email').hide();

			} else if(jQuery('#woocommerce_woocommerce_transdirect_authtype').val() == "Email and Password") {

				if(!validateEmail(jQuery('#woocommerce_woocommerce_transdirect_email').val())) {
					jQuery('#woocommerce_woocommerce_transdirect_email').val("");
					jQuery('#woocommerce_woocommerce_transdirect_password').val("");
				}

				jQuery('.password-email').show();
				jQuery('.api-key').hide();
			}
		});
	}


	function order_details() {
		jQuery('.order .order-status-sync').on('click', function(e) {
			jQuery('.from-address input[type="text"]').each(function() {
				if(!jQuery(this).val()) {
					jQuery(this).css('border', '1px solid red');
					e.preventDefault();
				}
			});
		});

		jQuery('.from-address').on('input', 'input[type="text"]', function() {
			 if(!jQuery(this).val()) {
			 	jQuery('.order .order-status-sync').removeAttr('checked');
			 } else {
			 	jQuery(this).css('border', '1px solid transparent');
			 }
		});

		jQuery('.submit input[name="save"]').on('click', function() {
			if(jQuery('#woocommerce_woocommerce_transdirect_authtype').val() == "API Key") {
				if(!validateEmail(jQuery('#woocommerce_woocommerce_transdirect_email').val())) {
					jQuery('#woocommerce_woocommerce_transdirect_email').val("");
					jQuery('#woocommerce_woocommerce_transdirect_password').val("");
				}
			}

			$authType = jQuery('#woocommerce_woocommerce_transdirect_authtype').val();
			$email    = jQuery('#woocommerce_woocommerce_transdirect_email').val();
			$password = jQuery('#woocommerce_woocommerce_transdirect_password').val();
			$apiKey   = jQuery('#woocommerce_woocommerce_transdirect_api_key').val();

		    jQuery.ajax({
				type: "POST",
				url: "admin-ajax.php",
				data: {action: 'check_auth_detail', authtype : $authType, email: $email, password: $password, apiKey: $apiKey },
				success: function(data) {
					data = JSON.parse(data);
					if(data.message == 'Unauthorized') {
						jQuery('.auth-error').show();
						jQuery('#auth-error-message').text("Please check your credentials.");
						jQuery("html, body").animate({ scrollTop: 0 }, "fast");
						return false;
					} else {
						jQuery("form").submit();
					}
				}

			});
			return false;
		});
	}

	function auth_details() {
		if(jQuery('#woocommerce_woocommerce_transdirect_authtype').val() == "API Key") {
			jQuery('.api-key').show();
			jQuery('.password-email').hide();

		} else if(jQuery('#woocommerce_woocommerce_transdirect_authtype').val() == "Email and Password"){
			jQuery('.api-key').hide();
			jQuery('.password-email').show();
		}
	}

	function get_value(postcode, locality, context) {
		jQuery('#'+ context).val(postcode + ',' + locality);
		jQuery(".autocomplete-div").html('');
		jQuery(".autocomplete-div" ).hide();
	}

	function order_sync() {
	  //   if(jQuery('.order-status-sync').is(':checked')) {
	  //       jQuery.ajax({
			//   type: "GET",
			//   url: "admin-ajax.php",
			//   data: {action: 'cronstarter_activation'},
			//   success: function(data) {
			//   }
			  
			// });
	  //   }
	}

	var selectAllClicked = true;
	function selectAll() {
		if(jQuery('.include_surcharge').is(':checked')){
		 	jQuery('.enable_courier').each(function() {
		 		if(!jQuery(this).is(':checked')) {
		 			jQuery(this).attr('checked', 'checked');
		 			selectAllClicked = true;
		 		}
			});
       } else {
          	jQuery('.enable_courier').each(function() {
                if(jQuery(this).is(':checked')) {
		 			jQuery(this).attr('checked', null);
		 			selectAllClicked = false;
		 		}
            });
       	}
	}

	function selectAllInsurance() {
		if(jQuery('.include_surcharge_courier').is(':checked')){
		 	jQuery('.enable-insurance').each(function() {
		 		if(!jQuery(this).is(':checked')) {
		 			jQuery(this).attr('checked', 'checked');
		 			selectAllClicked = true;
		 		}
			});
       } else {
          	jQuery('.enable-insurance').each(function() {
                if(jQuery(this).is(':checked')) {
		 			jQuery(this).attr('checked', null);
		 			selectAllClicked = false;
		 		}
            });
       	}
	}

	function selectCourier() {
		jQuery('.enable_courier').click(function() {
	 		if(selectAllClicked) {
	 			jQuery('.include_surcharge').attr('checked', null);
	 			selectAllClicked = false;
	 		}
		});
	}

	function selectInsurance() {
		jQuery('.enable-insurance').click(function() {
	 		if(selectAllClicked) {
	 			jQuery('.include_surcharge_courier').attr('checked', null);
	 			selectAllClicked = false;
	 		}
		});
	}


	function validateEmail(email) {
	    var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
	    return re.test(email);
	}
	jQuery(document).ready(function(){
		var totalTr = jQuery('.warehouse-postcode').length;
		if(totalTr == 1){
			jQuery('.btn-delete').hide();
		}
		var count = <?php echo $totalHouse; ?>;
		jQuery('#add-warehouse').click(function(){
			var totalTr = jQuery('.warehouse-postcode').length;
			if(totalTr == 1){
				jQuery('.btn-delete').show();
			}

			var content  =  '<table width="100%" id="warehouse-table'+ count +'">';
			content +=	'<tr><td colspan="3">';
			content +=			'<b>Warehouse Address :</b>';
			content +=			'</td>';
			content +=		'</tr>';
			content +=		'<tr>';
			content +=			'<td>Postcode:';
			content +=			'</td>';
			content +=			'<td>';
			content +=				'<input type="text" name="<?php echo $field; ?>postcode[]" id="<?php echo $field; ?>postcode'+ count +'"style="" value="" autocomplete="off" class="warehouse-postcode" placeholder="Enter youe postcode, suburb">';
			content +=				'<br/>';
			content +=				'<span class="loading-div" class="hidden-table"></span>';
			content +=				'<div class="autocomplete-div"></div>';
			content +=			'</td>';
			content +=			'<td><input type="button" id="'+ count +'" class="btn-delete" name="btn-delete" value="Delete"></td>';
			content +=		'</tr>';
			content +=		'<tr>';
			content +=			'<td></td>';
			content +=			'<td colspan="2">';
			content +=				'<input type="radio" name="<?php echo $field; ?>postcode_type['+ count +'][]" value="business" id="Commercial'+ count +'"> Commercial';
			content +=				'  <input type="radio" name="<?php echo $field; ?>postcode_type['+ count +'][]" value="residential" id="Residential'+ count +'"> Residential';
			content +=			'</td>';
			content +=		'</tr>';
			content +=		'<tr>';
			content +=		  '<td colspan="3">';
			content +=		  '<hr>';
			content +=			'</td>';
			content +=		'</tr>';
			content +=		'</table>';
			jQuery('#warehouse-content').append(content);
			count++;
		});

		jQuery('.warehouse-td').on('click', '.btn-delete',function(){
			var totalTr = jQuery('.warehouse-postcode').length;
			if(totalTr == 2){
				jQuery('.btn-delete').hide();
			}
			i = jQuery(this).attr('id');
			jQuery('#warehouse-table'+i).remove();
		});
	});
</script>