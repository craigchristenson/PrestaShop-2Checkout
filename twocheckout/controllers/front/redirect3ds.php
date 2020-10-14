<?php

/**
 * Class TwocheckoutRedirect3dsModuleFrontController
 */
class TwocheckoutRedirect3dsModuleFrontController extends ModuleFrontController
{
    /**
     * TwocheckoutRedirect3dsModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function initContent()
    {

        if (Tools::getValue('action') === 'success') {
            return $this->success();
        }
        if (Tools::getValue('action') === 'cancel') {
            return $this->cancel();
        }
        exit('not allowed');
    }

    /**
     * success callback from 3ds
     * @return mixed
     * @throws Exception
     */
    private function success()
    {
        /**
         * Get current cart object from session
         */
        $cart = $this->context->cart;
        $authorized = false;

        $refNo = Tools::getValue('REFNO');
        if (!$refNo) {
            throw new Exception('Cannot handle 3ds redirect without TRANSACTION ID');
        }

        /**
         * Verify if this module is enabled and if the cart has
         * a valid customer, delivery address and invoice address
         */
        if (!$this->module->active || $cart->id_customer == 0 || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        /**
         * Verify if this payment module is authorized
         */
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'twocheckout') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->l('This payment method is not available.'));
        }

        /** @var CustomerCore $customer */
        $customer = new Customer($cart->id_customer);

        /**
         * Check if this is a valid customer account
         */
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $redirectUrl = 'index.php?controller=order-confirmation&id_cart=' . Tools::getValue('cart') .
            '&id_module=' . $this->module->id .
            '&id_order=' . Tools::getValue('order') .
            '&key=' . $customer->secure_key;

        return Tools::redirect($redirectUrl);
    }

    /**
     * cancel payment from 3ds
     * returns to cart summary
     * @return mixed
     */
    private function cancel()
    {
        $cart = $this->context->cart;

        if (!$cart) {
            $cart = new Cart(Tools::getValue('cart'));
        }
        $this->context->cookie->id_cart = (int)(Tools::getValue('cart'));
        $cart->update();
        $order = new Order(Tools::getValue('order'));
        $order->delete();

        return Tools::redirect('index.php?controller=cart&action=show');
    }
}
