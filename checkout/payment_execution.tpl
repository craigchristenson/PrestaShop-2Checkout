{capture name=path}{l s='Shipping'}{/capture}
<div id="cms_block">
	<h2>{l s='Credit Card payment Summary' mod='checkout'}</h2>
    {l s='Buy On-Line with Credit or Debit Card via 2Checkout.' mod='checkout'}
    </div>
		{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}
    
<p>
	<img src="{$this_path}2Checkout.gif" alt="{l s='2checkout' mod='checkout'}" style="float:left; margin: 0px 10px 5px 0px;" />
	{l s='You have chosen to pay by credit card - Online validation.' mod='checkout'}
</p>
<p>
	<b>{l s='Please confirm your order by clicking \'I confirm my order\'' mod='checkout'}.</b>
</p>


<p align="right"><a href="{$base_dir_ssl}order.php?step=3" class="button_large">{l s='Other payment methods' mod='checkout'}</a></p>

<form name="checkout_confirmation" action="{$CheckoutUrl}" method="post" />
    <input type="hidden" name="lang" value="{$lang_iso}">
    <input type="hidden" name="sid" value="{$sid}" />
    <input type="hidden" name="total" value="{$total}" />
    <input type="hidden" name="cart_order_id" value="{$cart_order_id}" />
    <input type="hidden" name="card_holder_name" value="{$card_holder_name}" />
    <input type="hidden" name="street_address" value="{$street_address}" />
    <input type="hidden" name="street_address2" value="{$street_address2}" />
    <input type="hidden" name="city" value="{$city}" />
    <input type="hidden" name="state" value="{if $state}{$state->name}{else}{$outside_state}{/if}" />
    <input type="hidden" name="zip" value="{$zip}" />
    <input type="hidden" name="country" value="{$country}" />

    <input type="hidden" name="ship_name" value="{$ship_name}" />
    <input type="hidden" name="ship_street_address" value="{$ship_street_address}" />
    <input type="hidden" name="ship_street_address2" value="{$ship_street_address2}" />
    <input type="hidden" name="ship_city" value="{$ship_city}" />
    <input type="hidden" name="ship_state" value="{if $ship_state}{$ship_state->name}{else}{$outside_state}{/if}" />
    <input type="hidden" name="ship_zip" value="{$ship_zip}" />
    <input type="hidden" name="ship_country" value="{$ship_country}" />

	{counter assign=i}
	{foreach from=$products item=product}
	<input type="hidden" name="id_type" value="1" />
	<input type="hidden" name="c_prod_{$i}" value="{$product.id_product},{$product.quantity}" />
	<input type="hidden" name="c_name_{$i}" value="{$product.name}" />
	<input type="hidden" name="c_description_{$i}" value="{$product.description_short}" />
	<input type="hidden" name="c_price_{$i}" value="{$product.price}" />
	{counter print=false}
	{/foreach}

    <input type="hidden" name="email" value="{$email}" />
    <input type="hidden" name="phone" value="{$phone}" />
    <input type="hidden" name="demo" value="{$demo}" />
    <input type="hidden" name="return_url" value="{$return_url}" />
    <p>
    <button style="background: url({$img_dir}button-medium.gif) no-repeat top left;" type="submit" name="submit" value="{l s='I confirm my order' mod='checkout'}" class="boton_mid img_png exclusive right">
{l s='I confirm my order' mod='checkout'}
	  </button>

      <button style="background: url({$img_dir}button-medium.gif) no-repeat top left;" type="button" name="processCarrier" value="{l s='Previous' mod='checkout'}" class="boton_mid img_png exclusive right" onclick="location.href='{$base_dir_ssl}order.php?step=3'" >{l s='Previous' mod='checkout'}
    </button>
    </p>
</form>