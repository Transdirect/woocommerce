<?php
/**
 * Shipping Transdirect Call Order Sync API
 *
 * @author      Transdirect
 * @version     6.7
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Sync orders to user's transdirect account
 *
 * To view orders, check in orders tab in member area of transdirect
 */
class order_sync {

    // Get sync details from td account
    public function td_get_sync_detail() {
        $apiKey    = td_get_auth_api_key();
        $api_array = '';
        $args      = td_request_method_headers($apiKey, $api_array, 'GET');
        $link      = "https://www.transdirect.com.au/api/bookings/sync";
        $response  = wp_remote_retrieve_body(wp_remote_post($link, $args));
        $response  = json_decode($response);
        $response  = json_decode(json_encode($response), true);
        $response  = array_keys($response, 'on');
        return $response;
    }

    // Synced new order to td system
    public function td_sync_order($order, $booking_id, $selected_courier) {

        $api_array     = [];
        $status_prefix = "wc-";
        $sku; $sale_price = 0;
        $item_des;
        $apiKey = td_get_auth_api_key();

        $statuses_val = wc_get_order_status_name($order->post->post_status);
        $statuses     =  substr_replace($status_prefix, $statuses_val, 0);
        $items        = $order->get_items();

        $orderItems = array();
        foreach ($items as $item) {
            $product_name         = $item['name'];
            $product_id           = $item['product_id'];
            $product_variation_id = $item['variation_id'];

            if ($product_variation_id) {
                $product = new WC_Product_Variation($item['variation_id']);
            } else {
                $product = new WC_Product($item['product_id']);
            }
            // Get SKU
            $sku = $sku . ', ' . $product->get_sku();
            //Get Price
            $sale_price += get_post_meta($product->id, '_price', true);
            $item_des =  $item_des . ', ' . $product_name;
        }

        $sku = substr($sku, 1);
        $item_des = substr($item_des, 1);
        //$order_status = $this->td_get_sync_detail();
    
        $from_date = substr($order->post->post_modified, 0, strpos($order->post->post_modified, ' '));
        if(isset(td_getApiDetails()->sync_from_date) && td_getApiDetails()->sync_from_date <= $from_date) {

            $api_array['transdirect_order_id']  = (int) $booking_id;
            $api_array['order_id']              = $order->id;
            $api_array['goods_summary']         = $sku;
            $api_array['goods_dump']            = $item_des;
            $api_array['imported_from']         = 'Woocommerce';
            $api_array['purchased_time']        = $order->order_date;
            $api_array['sale_price']            = number_format($sale_price, 2);
            $api_array['selected_courier']      = strtolower($selected_courier);
            $api_array['courier_price']         = $order->get_total_shipping();
            $api_array['paid_time']             = $order->completed_date;
            $api_array['buyer_name']            = $order->billing_first_name .' '. $order->billing_last_name;
            $api_array['buyer_email']           = $order->billing_email;
            $api_array['delivery']['name']      = $order->shipping_first_name .' '. $order->shipping_last_name;
            $api_array['delivery']['email']     = $order->billing_email;
            $api_array['delivery']['phone']     = $order->billing_phone;
            $api_array['delivery']['address']   = $order->shipping_address_1. ' '.$order->shipping_address_2;
            $api_array['last_updated']          = $order->modified_date;

            $args     = td_request_method_headers($apiKey, $api_array, 'POST');
            
            $link     = "https://www.transdirect.com.au/api/orders/";
            $response = wp_remote_retrieve_body(wp_remote_post($link, $args));

            $response = json_decode($response);
            
            return $response;
        }
    }

    public function td_sync_all_orders($order) {

        $apiKey = td_get_auth_api_key();
        $statuses_val = wc_get_order_status_name($order->post->post_status);
        $statuses     =  substr_replace($status_prefix, $statuses_val, 0);
        //$order_status = $this->td_get_sync_detail();
        
        $from_date = substr($order->post->post_modified, 0, strpos($order->post->post_modified, ' '));

        if((isset(td_getApiDetails()->sync_from_date) && td_getApiDetails()->sync_from_date <= $from_date) || td_getApiDetails()->mode == 'no_display_mode') {
            global $woocommerce;
            $order_id = $order->id;
            
            $api_array['shipping_first_name'] =  get_post_meta($order_id,'_shipping_first_name',true);
            $api_array['shipping_last_name'] = get_post_meta($order_id,'_billing_last_name',true);
            $api_array['shipping_company'] = get_post_meta($order_id,'_shipping_company',true);
            $api_array['shipping_address'] = get_post_meta($order_id,'_shipping_address_1',true);
            $api_array['shipping_address2'] = get_post_meta($order_id,'_shipping_address_2',true);
            $api_array['shipping_city'] = get_post_meta($order_id,'_shipping_city',true);
            $api_array['shipping_postcode'] = get_post_meta($order_id,'_shipping_postcode',true);
            $api_array['shipping_country'] = get_post_meta($order_id,'_shipping_country',true);
            $api_array['shipping_state'] = get_post_meta($order_id,'_shipping_state',true);
            $api_array['shipping_email'] = get_post_meta($order_id,'_shipping_email',true);
            $api_array['shipping_phone'] = get_post_meta($order_id,'_shipping_phone',true);
            $api_array['address_type'] =  td_getApiDetails()->street_type;

            $items        = $order->get_items();
            require_once("quotes.php");
            $quotes = new Quotes();
            $api_array = array_merge($api_array, $quotes->td_get_cart_items($items));

            foreach ($items as $item) {
                $product->id = $item['product_id'];
                $sale_price += get_post_meta($product->id, '_price', true);
            }

            $api_array['order_id']          = $order->id;
            $api_array['imported_from']     = 'Woocommerce';
            $api_array['purchased_time']    = $order->order_date;
            $api_array['sale_price']        = number_format($sale_price, 2);
            $api_array['courier_price']     = $order->get_total_shipping();
            $api_array['paid_time']         = $order->completed_date;
            $api_array['buyer_name']        = $order->billing_first_name .' '. $order->billing_last_name;
            $api_array['buyer_email']       = $order->billing_email;
            $api_array['delivery_name']     = $order->shipping_first_name .' '. $order->shipping_last_name;
            $api_array['delivery_email']    = $order->billing_email;
            $api_array['delivery_phone']    = $order->billing_phone;
            $api_array['delivery_address']  = $order->shipping_address_1. ' '.$order->shipping_address_2;
            $api_array['last_updated']      = $order->modified_date;  

            $args     = td_request_method_headers($apiKey, $api_array, 'POST');
            $link     = "https://www.transdirect.com.au/api/orders/syncOrders";
            $response = wp_remote_retrieve_body(wp_remote_post($link, $args));
            $response = json_decode($response);
            return $response;
        }

    }

    // Sync new order in transdirect when cron running
    public function td_create_order(){  

        if((isset(td_getApiDetails()->enable_sync) && td_getApiDetails()->enable_sync == 'on') || (isset(td_getApiDetails()->sync_all_order) && td_getApiDetails()->sync_all_order == 'on' || td_getApiDetails()->mode == 'no_display_mode')){

            global $wpdb;
            $filters = array(
                'post_status'    => 'any',
                'post_type'      => 'shop_order',
                'posts_per_page' => 20,
                'meta_query'     => array(
                        array(
                        'key' => 'synced',
                        'value' => '0'
                        )
                )
            );
            $post_query = new WP_Query($filters);
        
            if( $post_query->have_posts() ){
                
                while ($post_query->have_posts()) : $post_query->the_post();

                    $order            = new WC_Order($post_query->post->ID);
                    $booking_id       = get_post_meta($order->id, 'Booking ID', true);
                    $selected_courier = get_post_meta($order->id, 'Base Courier', true);
            
                    if($booking_id && $selected_courier) {
                        $response = $this->td_sync_order($order, $booking_id, $selected_courier);
                        
                        if(isset($response->id) && $response->id != '' && get_post_meta($order->id,'synced', true) == '0') {
                            update_post_meta($order->id, 'synced', '1');
                        }    
                    } else if((isset(td_getApiDetails()->sync_all_order) && td_getApiDetails()->sync_all_order == 'on') || td_getApiDetails()->mode == 'no_display_mode') {
                        $response = $this->td_sync_all_orders($order);
                        if(isset($response->id) && $response->id != '' && get_post_meta($order->id,'synced', true) == '0') {
                            update_post_meta($order->id, 'synced', '1');
                        } 
                    }
                endwhile;
                wp_reset_query();
            }
        }
    }

    // Update order in td system when order updated in admin side.
    function td_update_order($post_id){
        global $wpdb, $post;
        $isOrderSynced = get_post_meta($post_id, 'synced', true);
        if($post->post_type == 'shop_order') {
            if(isset(td_getApiDetails()->enable_sync) && td_getApiDetails()->enable_sync == 'on' && $isOrderSynced == '1'){
                $order            = new WC_Order($post_id);
                $booking_id       = get_post_meta($order->id, 'Booking ID', true);
                $selected_courier = get_post_meta($order->id, 'Base Courier', true);
                if($booking_id && $selected_courier) {
                    $response = $this->td_sync_order($order, $booking_id, $selected_courier);
                }
            }
        }
    }

    // Cron start to sync order
    function td_start_cron() {
        global $wpdb;
        $filters = array(
            'post_status'    => 'any',
            'post_type'      => 'shop_order',
            'posts_per_page' => -1,
            'meta_query'     => array(
                    array(
                    'key' => 'synced'
                    ))
        );
        $post_query = new WP_Query($filters);
        if( !$post_query->have_posts() ){
            $filters = array(
                'post_status' => 'any',
                'post_type' => 'shop_order',
                'posts_per_page' => -1
            );
            $i=0;
            $loop = new WP_Query($filters);
            while ($loop->have_posts() ) {
                $loop->the_post();
                $order = new WC_Order($loop->post->ID);
                $orderId = get_post_meta($order->id,'synced', true);
                if($orderId == '') {
                   add_post_meta($order->id,'synced','0');
                }
            }
        }
        if((isset(td_getApiDetails()->enable_sync) && td_getApiDetails()->enable_sync == 'on') || (isset(td_getApiDetails()->sync_all_order) && td_getApiDetails()->sync_all_order == 'on' || td_getApiDetails()->mode == 'no_display_mode')){
            if(!wp_get_schedule('mycronjob')){
                wp_schedule_event( time(), '5mins', 'mycronjob' );    
            }
        } 
        else {
            if(wp_get_schedule('mycronjob')){
                wp_clear_scheduled_hook('mycronjob');
            }
        }
    }
}