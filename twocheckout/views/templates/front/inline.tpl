{extends file=$layout}

{block name='content'}
    <section id="main">
        <div class="cart-grid row">

            <!-- Left Block: cart product informations & shpping -->
            <div class="cart-grid-body col-xs-12 col-lg-8">

                <!-- cart products detailed -->
                <div class="card cart-container">
                    <div class="card-block">
                        <h1 class="h1">{l s='Shopping Cart' d='Shop.Theme.Checkout'}</h1>
                    </div>
                    <hr class="separator">
                    {block name='cart_overview'}
                        {include file='checkout/_partials/cart-detailed.tpl' cart=$cart}
                    {/block}
                </div>

                {block name='continue_shopping'}
                    <a class="label" href="{$urls.pages.index}">
                        <i class="material-icons">chevron_left</i>{l s='Continue shopping' d='Shop.Theme.Actions'}
                    </a>
                {/block}

                <!-- shipping informations -->
                {block name='hook_shopping_cart_footer'}
                    {hook h='displayShoppingCartFooter'}
                {/block}
            </div>

            <!-- Right Block: cart subtotal & cart total -->
            <div class="cart-grid-right col-xs-12 col-lg-4">

                {block name='cart_summary'}
                    <div class="card cart-summary">

                        {block name='hook_shopping_cart'}
                            {hook h='displayShoppingCart'}
                        {/block}

                        {block name='cart_totals'}
                            {include file='checkout/_partials/cart-detailed-totals.tpl' cart=$cart}
                        {/block}

                        {block name='cart_actions'}
                            {include file='checkout/_partials/cart-detailed-actions.tpl' cart=$cart}
                        {/block}

                    </div>
                {/block}

                {block name='hook_reassurance'}
                    {hook h='displayReassurance'}
                {/block}

            </div>

        </div>
    </section>
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
{/block}