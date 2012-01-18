<?php
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/checkout.php');

/* Retrieve entered information from 2checkout form post. */
	$credit_card_processed	= $_REQUEST['credit_card_processed'];
	$order_number			= $_REQUEST['order_number'];
	$cart_id 				= $_REQUEST['cart_id'];
    $secure_key             = $_REQUEST['secure_key'];

$cart=new Cart($cart_id);

/* Create Necessary variables for order placement */
$currency = new Currency(intval(isset($_REQUEST['currency_payement']) ? $_REQUEST['currency_payement'] : $cookie->id_currency));
$total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));

$checkout = new checkout();
$checkout->validateOrder($cart_id, _PS_OS_WS_PAYMENT_, $total, $checkout->displayName,  NULL,  NULL, $currency->id);

$order = new Order($checkout->currentOrder);

/*  Once complete, redirect to order-confirmation.php */
$url=__PS_BASE_URI__."order-confirmation.php?id_cart={$cart_id}&id_module={$checkout->id}&id_order={$checkout->currentOrder}";

echo '<script type="text/javascript">location.replace("'.$url.'")</script>';
?>