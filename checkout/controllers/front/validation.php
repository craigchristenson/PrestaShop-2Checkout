<?php

class CheckoutValidationModuleFrontController extends ModuleFrontController
{
	public function postProcess()
	{

		$sid			= Configuration::get('CHECKOUT_SID');
		$secret_word		= Configuration::get('CHECKOUT_SECRET');
		$credit_card_processed	= $_REQUEST['credit_card_processed'];
		$order_number		= $_REQUEST['order_number'];
		$cart_id 		= $_REQUEST['merchant_order_id'];

		$cart=new Cart($cart_id);
		$total = number_format($cart->getOrderTotal(true, 3), 2, '.', '');
		$checkout = new checkout();

		//Check the hash
		if ($_REQUEST['demo'] == 'Y') {
			$order_number = 1;
		}
		$compare_string = $secret_word . $sid . $order_number . $total;
		$compare_hash1 = strtoupper(md5($compare_string));
		$compare_hash2 = $_REQUEST['key'];

		if ($compare_hash1 == $compare_hash2) {
			$customer = new Customer($cart->id_customer);
			$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
			$checkout->validateOrder($cart_id, _PS_OS_PAYMENT_, $total, $checkout->displayName,  $message, array(), NULL, false, $customer->secure_key);
			$order = new Order($checkout->currentOrder);
			Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$checkout->currentOrder);
		} else {
			echo 'Hash Mismatch! Please contact the seller directly for assistance.</br>';
			echo 'Total: '.$total.'</br>';
			echo '2CO Total: '.$_REQUEST['total'];
		}
	}
}
