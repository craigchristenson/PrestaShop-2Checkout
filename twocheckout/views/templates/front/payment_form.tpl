<div id="tcoApiForm">
    <div id="tcoWait">
        <div class="text">
            <img src="{$spinner}">
            Processing, please wait!
        </div>
    </div>
    <form id="tco-payment-form" action="{$action}" method="post" data-json="{$style}">
        <div id="card-element">
            <!-- A TCO IFRAME will be inserted here. -->
        </div>

        <button class="btn btn-primary" disabled id="placeOrderTco">{l s='Place order' mod='twocheckout'}</button>
    </form>
</div>

<script type="text/javascript">
    let sellerId = "{$sellerId}";
</script>
<script type="text/javascript" src="https://2pay-js.2checkout.com/v1/2pay.js"></script>
<script type="text/javascript" src="{$script}"></script>
<link type="text/css" rel="stylesheet" href="{$css}">
