<script language="JavaScript">
    (function (document, src, libName, config) {
        var script = document.createElement('script');
        script.src = src;
        script.async = true;
        var firstScriptElement = document.getElementsByTagName('script')[0];
        script.onload = function () {
            for (var namespace in config) {
                if (config.hasOwnProperty(namespace)) {
                    window[libName].setup.setConfig(namespace, config[namespace]);
                }
            }
            window[libName].register();
            TwoCoInlineCart.setup.setMerchant({$merchantId});
            TwoCoInlineCart.setup.setMode('DYNAMIC');
            TwoCoInlineCart.register();

            TwoCoInlineCart.cart.setAutoAdvance(true);
            TwoCoInlineCart.cart.setCurrency('{$currency}');
            TwoCoInlineCart.cart.setLanguage('{$language}');
            TwoCoInlineCart.cart.setReturnMethod({$url_data|@json_encode nofilter});
            TwoCoInlineCart.cart.setTest({$test});
            TwoCoInlineCart.cart.setOrderExternalRef({$order_ext_ref});
            TwoCoInlineCart.cart.setExternalCustomerReference('{$customer_ext_ref}');
            TwoCoInlineCart.cart.setSource('PRESTASHOP_1_7_6');
            TwoCoInlineCart.cart.setSignature('{$signature}');

            TwoCoInlineCart.products.removeAll();
            TwoCoInlineCart.products.addMany({$products|@json_encode nofilter});
            TwoCoInlineCart.billing.setData({$billing_address|@json_encode nofilter});
            TwoCoInlineCart.shipping.setData({$shipping_address|@json_encode nofilter});
            TwoCoInlineCart.cart.checkout();
        };
        firstScriptElement.parentNode.insertBefore(script, firstScriptElement);
    })(document,
        'https://secure.2checkout.com/checkout/client/twoCoInlineCart.js',
        'TwoCoInlineCart',
            {literal} {'app': {'merchant': "{/literal}{$merchantId}{literal}"}, 'cart': {'host': 'https:\/\/secure.2checkout.com'}}{/literal}
    );
</script>
