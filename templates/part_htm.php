
<?php
/**
 * Shipping Transdirect Settings
 *
 * @author 		Transdirect
 * @version     6.7
 */


?>

<h3><?php echo $this->method_title; ?></h3>

<div id="td-main-container">
	<table class="form-table td-shipping" cellpadding="0" cellspacing="10" border="0" width="100%">
		<!-- Logo Section -->
		<tr id="auth-error" class="td-auth-error" style="display:none;">
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
		<tr id="auth-success" class="td-auth-success" style="display:none;">
			<td class="border-fomat" colspan="2">
				<table width="100%">
					<tr>
						<td>
							<div>
								<p id="auth-success-message"></p>
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<?php if(!isset($default_values['api_key']) ||  $default_values['api_key']== '') {?>
		<tr class='td-auth-error'>
			<td class="border-fomat" colspan="2">
				<table width="100%">
					<tr >
						<td>
							For use this plugin, you need an API key. 
							<br/>
							Please generate new api key  <a href="https://www.transdirect.com.au/members/api/apimodules" target="_blank" title="Transdirect Api Key" style="color:#a94442; text-decoration: underline;">here</a>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<?php }
		?>
		<tr>
			<td colspan="2" class="border-fomat">
				<table width="100%">
					<td colspan="3" width="50%">
						<div class="td-logoSection">
							<img src="<?php echo plugins_url(); ?>/transdirect-shipping/assets/images/logo-transdirect.png" width="100%"/>
						</div>
					</td>
					<td width="25%">
						<a href="https://www.transdirect.com.au/education/faqs/" target="_blank" class="td-btn-warning td-onLineFAQ">
							online FAQ's
						</a>
					</td>
					<td width="25%">
						<a href="mailto:info@transdirect.com.au" target="_top" class="td-btn-warning td-contactSales">
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
		<!--  Authentication API KEY -->
        <tr>
			<td width="100%" colspan="2" class="border-fomat">
				<table width="100%">
					<tr>
						<td width="" height="70px">
							<label for="woocommerce_transdirect_shipping" class="td-label">Enable/Disable</label>
							<img class="help_tip td_help_tip" data-tip="Enable Transdirect Shipping on your shipping method"
						    src="<?php echo plugins_url();?>/woocommerce/assets/images/help.png" height="16" width="16" />
						</td>
						<td>
							<label for="<?php echo $field; ?>enabled">
							 	<input class="" type="checkbox" name="<?php echo $field; ?>enabled" id="<?php echo $field; ?>enabled" style="" value="yes"
								 <?php if ($default_values['enabled'] == 'yes') : ?> checked="checked" <?php endif; ?> >
							</label>
							Enable Transdirect
						</td>
					</tr>
					<tr class="api-key">
						<td width="30%" height="70px">
							<label for="woocommerce_transdirect_shipping" class="td-label">API Key:</label>
							<img class="help_tip td_help_tip" data-tip="API key of the account provided by Transdirect"
						     src="<?php echo plugins_url();?>/woocommerce/assets/images/help.png" height="16" width="16" />
						</td>
		                <td>
			                 <input class="input-text regular-input api" type="text" name="<?php echo $field;?>api_key" id="<?php echo $field; ?>api_key"
						     value="<?php echo $default_values['api_key']; ?>">
					        <input type="hidden" name="transdirect_hidden" id="transdirect_hidden" value="1" />
		                </td>
		                <td>
		                	<table>
		                		<tr>
			                		<td>
			                			<a href="https://www.transdirect.com.au/members/api/apimodules" target="_blank" title="Transdirect Api Key" class="td-btn-api-key td-btn-warning">Get API key</a>
			                		</td>
			                		<td>
			                			<input type="button" id="btn-api-test" title="Transdirect Test Api Key" class="td-btn-api-test  td-btn-warning" value="Test API">
			                		</td>
		                		</tr>
		                	</table>
		                </td>
					</tr>
	            </table>
			</td>
		</tr>    
	</table>
</div>

<!-- SCRIPTS  -->
<script>
	jQuery(document).ready(function(){
		
		jQuery('#btn-api-test, .submit input[name="save"], button[name="save"]').on('click', function(){
			var btnValue = jQuery(this).val();
			$apiKey   = jQuery('#woocommerce_woocommerce_transdirect_api_key').val();
			if($apiKey == '') {
				jQuery('#auth-error').show();
				jQuery('#auth-error-message').text("Please enter an api key.");
				jQuery("#auth-error").delay(4000).slideUp(200, function() {
							    jQuery(this).hide();
							});
				return false;
			}

			jQuery.ajax({
				type: "POST",
				url: "admin-ajax.php",
				data: {action: 'check_api_key_details', apiKey: $apiKey },
				success: function(data) {
					data = jQuery.parseJSON(data);
					
					if(data.message == 'Unauthorized') 
					{
						jQuery('#auth-error').show();
						jQuery('#auth-error-message').text("API is invalid.");
						jQuery("html, body").animate({ scrollTop: 0 }, "fast");
						jQuery("#auth-error").delay(4000).slideUp(200, function() {
							    jQuery(this).hide();
							});

						return false;
					} 
					else if(data.module_type_id == 1 || data.module_type_id == 0) 
					{
						if(data.status == 1 ) {
							    jQuery('#auth-success').show();
								jQuery('#auth-success-message').text("API is active under " + data.member_email + " member.");
								jQuery("html, body").animate({ scrollTop: 0 }, "fast");
								jQuery("#auth-success").delay(4000).slideUp(200, function() {
								    jQuery(this).hide();
								});
								if(btnValue == 'Save changes') {
									jQuery("#mainform").submit();
								}
							}
						else {
							jQuery('#auth-error').show();
							jQuery('#auth-error-message').text("API is deactivated under " + data.member_email + "  member.");
							jQuery("html, body").animate({ scrollTop: 0 }, "fast");
							jQuery("#auth-error").delay(4000).slideUp(200, function() {
							    jQuery(this).hide();
							});
						}
					} else {
						jQuery('#auth-error').show();
						jQuery('#auth-error-message').text("API is invalid.");
						jQuery("html, body").animate({ scrollTop: 0 }, "fast");
						jQuery("#auth-error").delay(4000).slideUp(200, function() {
							    jQuery(this).hide();
							});
					}
				}
			});
			return false;
		});
	});	
</script>