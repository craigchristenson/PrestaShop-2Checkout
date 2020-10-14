<?php

/**
 * Class TwocheckoutValidationModuleFrontController
 */
class TwocheckoutInlineModuleFrontController extends ModuleFrontController
{

    /**
     * @throws \PrestaShopException
     */
    public function initContent()
    {
    	parent::initContent();
        $this->context->smarty->assign(
            [
                'merchantId' => Configuration::get('TWOCHECKOUT_SID'),
                'currency' => Tools::getValue('currency'),
                'language' => Tools::getValue('language'),
                'returnUrl' => Tools::getValue('return-url'),
                'test' => Tools::getValue('test'),
                'url_data' => Tools::getValue('url_data'),
                'order_ext_ref' => Tools::getValue('order-ext-ref'),
                'customer_ext_ref' => Tools::getValue('customer-ext-ref'),
                'products' => (Tools::getValue('products')),
                'billing_address' => Tools::getValue('billing_address'),
                'shipping_address' => Tools::getValue('shipping_address'),
                'signature' => Tools::getValue('signature'),
            ]);

        $this->setTemplate('module:twocheckout/views/templates/front/inline.tpl');
    }

}
