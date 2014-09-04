<?php
session_start();
include_once('../../../wp-config.php');
include_once('../../../wp-load.php');
include_once('../../../wp-includes/wp-db.php');
global $woocommerce,$wpdb;

$shipping_details = $wpdb->get_results( "SELECT `option_value` FROM `wp_options` WHERE `option_name`='woocommerce_".WC()->session->chosen_shipping_methods[0]."_settings'");
$defult_values = unserialize($shipping_details[0]->option_value);

$shipping_type = $_REQUEST['shipping_name'];
$price 		   = $_REQUEST['shipping_price'];
$transit_time  = $_REQUEST['shipping_transit_time'];
$service_type  = $_REQUEST['shipping_service_type'];
//WC()->shipping->packages[0]['rates'][WC()->session->chosen_shipping_methods[0]]['cost'] = $price;
//echo WC()->cart->get_cart_subtotal();exit;

//var_dump($shipping_type);exit;
$_SESSION['price'] =  $price;
echo '1';exit;
?>