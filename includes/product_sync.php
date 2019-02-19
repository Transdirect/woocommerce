<?php
/**
 * Shipping Transdirect Call Product Sync API
 *
 * @author      Transdirect
 * @version     6.7
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Sync product to user's transdirect account
 *
 * To view product, check in api details in member area of transdirect
 */
class product_sync {

	/**
	 * Send all product to send_to_api function once in hour 
	 *
	 */
	public function sync_all_product() {
		
		$args = array(
		    'post_type' => 'product',
		 	);
		$query = new WP_Query( $args );
		$products = $query->get_posts();
		$data = array();
		if ( $query->have_posts() ): 
		   	foreach ($products as $product) {	
		   		if(get_post_meta($product->ID, '_sku', true)) :
			    	$data['products'][] = [
			    		"product_sku" => get_post_meta($product->ID, '_sku', true),
			    		"title" => get_the_title($product->ID),
			    		"description" => $product->post_content,
			    	];
			    endif;
		   	} 
		endif; 
		wp_reset_postdata();
		$this->send_to_api($data);
	}

	/**
	 * Send updated product to send_to_api function once any product updated. 
	 *
	 */
	public function sync_updated_product($post_id) {
		$args = array(
			'p'	=> $post_id,
		    'post_type' => 'product',
		 	);
		$query = new WP_Query( $args );
		$products = $query->get_posts();
		$data = array();
		if ( $query->have_posts() ): 
		   	foreach ($products as $product) {
		   		if(get_post_meta($product->ID, '_sku', true)) :
			    	$data['products'][] = [
			    		"product_sku" => get_post_meta($product->ID, '_sku', true),
			    		"title" => get_the_title($product->ID),
			    		"description" => $product->post_content,
			    	];
			    endif;
		   	} 
		endif;

		$this->send_to_api($data);
	}

	/**
	 * Send product to transdirect api. 
	 *
	 * To view product, check in api details in member area of transdirect 
	 */
	public function send_to_api($products) {

		$api_details = td_getSyncSettingsDetails(true);
		if(isset($api_details->multiwarehouse_enabled) && $api_details->multiwarehouse_enabled != '' && $api_details->multiwarehouse_enabled == 'on') {
			$apiKey = td_get_auth_api_key();
			$args     = td_request_method_headers($apiKey, $products, 'POST');

			$link     = "https://www.transdirect.com.au/api/products";
			$response = wp_remote_retrieve_body(wp_remote_post($link, $args));
			$response = json_decode($response);
		} else {
			$this->td_start_product_cron();
		}

	}

	/**
	 * Activate or Diactivate cron based on member area setting. 
	 *
	 */
	public function td_start_product_cron()
	{
		$api_details = td_getSyncSettingsDetails(true);

		if(isset($api_details->multiwarehouse_enabled) && $api_details->multiwarehouse_enabled != '' && $api_details->multiwarehouse_enabled == 'on') {
			if(!wp_get_schedule('myProductSyncCronjob')){
			    wp_schedule_event( time(), '24hours', 'myProductSyncCronjob' );    
			}
		} else {
			if(wp_get_schedule('myProductSyncCronjob')){
			    wp_clear_scheduled_hook('myProductSyncCronjob');
			}
		}
	}

}