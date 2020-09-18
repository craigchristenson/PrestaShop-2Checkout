{if $order->valid == 1}
    <div style="font-size: .8125rem; background-color: #d8f4d0; border-color:#66c643; color:#429325;
    padding: .75rem 1.25rem;margin-bottom: 1rem;">
        <p style="font-size: .8125rem; margin: 0">{l s=' Order (ID REF: ' mod='twocheckout'}
            <strong>{$order->reference}</strong>) {l s=' has been confirmed by 2Checkout!' mod='twocheckout'}</p>
    </div>
{else}
    <div style="font-size: .8125rem; background-color: #f2dede; border-color:#ebcccc; color:#a94442;
    padding: .75rem 1.25rem;margin-bottom: 1rem;">
        <p style="font-size: .8125rem; color:#a94442;">{l s='Unfortunately, an error occurred while processing the transaction.' mod='twocheckout'}</p>
        <p style="font-size: .8125rem; color:#a94442; margin-bottom: 0;">
            {l s='Your order cannot be created. If you think this is an error, feel free to contact our' mod='twocheckout'}
            <a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='customer support team' mod='twocheckout'}</a>
        </p>
    </div>
{/if}
