<div class="payhubgateway-wrapper">
{if $update_confirmation}{$update_confirmation}{/if}
<a href="http://www.payhub.com" class="payhubgateway-logo" target="_blank"><img src="{$module_dir}img/payhub_logo.png" alt="PayHub Gateway" border="0" /></a>
<p class="payhubgateway-intro">{l s='Accept payments through your PrestaShop store with PayHub Gateway - a FREE payment solution from PayHub.  All you need to use it is a PayHub merchant account.  Every PayHub account comes with a virtual terminal and mobile app (iOS and Android) - all at no extra cost!' mod='payhubgateway'}</p>
<p class="payhubgateway-sign-up">{l s='Need a PayHub account? ' mod='payhubgateway'}<a href="http://www.payhub.com/sign-up.html" target="_blank">{l s='Sign Up Now' mod='payhubgateway'}</a></p>
<div class="payhubgateway-content" style="display:none">
	<div class="payhubgateway-leftCol">
	</div>
	<div class="payhubgateway-video">
	</div>
</div>

<form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post">
	<fieldset>
		<legend>{l s='PayHub Gateway Configuration' mod='payhubgateway'}</legend>

		{* Determine which currencies are enabled on the store and supported by PayHub & list one credentials section per available currency *}
		{foreach from=$currencies item='currency'}
			{if (in_array($currency.iso_code, $available_currencies))}
				{assign var='configuration_orgid_name' value="PAYHUB_GATEWAY_ORGID_"|cat:$currency.iso_code}
				{assign var='configuration_username_name' value="PAYHUB_GATEWAY_USERNAME_"|cat:$currency.iso_code}
				{assign var='configuration_password_name' value="PAYHUB_GATEWAY_PASSWORD_"|cat:$currency.iso_code}
				{assign var='configuration_tid_name' value="PAYHUB_GATEWAY_TID_"|cat:$currency.iso_code}
				<div class="get-payhub-credentials-section">
				**Your PayHub credentials can be found in Admin->3rd Party API page in your VirtualHub login.
				</div>				
				<table>
					<tr>
						<td>
<!-- 							<p>{l s='Credentials for' mod='payhubgateway'}<b> {$currency.iso_code}</b> {l s='currency' mod='payhubgateway'}</p> -->
							<label for="payhubgateway_orgid">{l s='Organization ID' mod='payhubgateway'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="20" id="payhubgateway_orgid_{$currency.iso_code}" name="payhubgateway_orgid_{$currency.iso_code}" value="{${$configuration_orgid_name}}" autocomplete="off" /></div>
							<label for="payhubgateway_username">{l s='Username' mod='payhubgateway'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="20" id="payhubgateway_username_{$currency.iso_code}" name="payhubgateway_username_{$currency.iso_code}" value="{${$configuration_username_name}}" autocomplete="off" /></div>
							<label for="payhubgateway_password">{l s='Password' mod='payhubgateway'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="20" id="payhubgateway_password_{$currency.iso_code}" name="payhubgateway_password_{$currency.iso_code}" value="{${$configuration_password_name}}" autocomplete="off" /></div>
							<label for="payhubgateway_tid">{l s='Terminal ID' mod='payhubgateway'}:</label>
							<div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="20" id="payhubgateway_tid_{$currency.iso_code}" name="payhubgateway_tid_{$currency.iso_code}" value="{${$configuration_tid_name}}" autocomplete="off" /></div>
						</td>
					</tr>
				<table>
				<hr size="1" style="background: #BBB; margin: 0; height: 1px;" noshade /><br />
			{/if}
		{/foreach}

		<label for="payhubgateway_mode">{l s='Mode:' mod='payhubgateway'}</label>
		<div class="margin-form" id="payhubgateway_mode">
			<input type="radio" name="payhubgateway_mode" value="live" style="vertical-align: middle;" {if $PAYHUB_GATEWAY_MODE == 'live'}checked="checked"{/if} />
			<span>{l s='Live mode' mod='payhubgateway'}</span><br/>
			<input type="radio" name="payhubgateway_mode" value="demo" style="vertical-align: middle;" {if $PAYHUB_GATEWAY_MODE == 'demo'}checked="checked"{/if} />
			<span>{l s='Test mode' mod='payhubgateway'}</span><br/>
		</div>
		<label for="payhubgateway_cards">{l s='Cards* :' mod='payhubgateway'}</label>
		<div class="margin-form" id="payhubgateway_cards">
			<input type="checkbox" name="payhubgateway_card_visa" {if $PAYHUB_GATEWAY_CARD_VISA}checked="checked"{/if} />
				<img src="{$module_dir}/cards/visa.gif" alt="visa" />
			<input type="checkbox" name="payhubgateway_card_mastercard" {if $PAYHUB_GATEWAY_CARD_MASTERCARD}checked="checked"{/if} />
				<img src="{$module_dir}/cards/mastercard.gif" alt="visa" />
			<input type="checkbox" name="payhubgateway_card_discover" {if $PAYHUB_GATEWAY_CARD_DISCOVER}checked="checked"{/if} />
				<img src="{$module_dir}/cards/discover.gif" alt="visa" />
			<input type="checkbox" name="payhubgateway_card_ax" {if $PAYHUB_GATEWAY_CARD_AX}checked="checked"{/if} />
				<img src="{$module_dir}/cards/ax.gif" alt="visa" />
		</div>

		<!-- <label for="payhubgateway_hold_review_os">{l s='Order status:  "Hold for Review" ' mod='payhubgateway'}</label>
		<div class="margin-form">
			<select id="payhubgateway_hold_review_os" name="payhubgateway_hold_review_os">';
				// Hold for Review order state selection
				{foreach from=$order_states item='os'}
					<option value="{if $os.id_order_state|intval}" {((int)$os.id_order_state == $PAYHUB_GATEWAY_HOLD_REVIEW_OS)} selected{/if}>
						{$os.name|stripslashes}
					</option>
				{/foreach}
			</select>
		</div> -->
		<br />
		<center>
			<input type="submit" name="submitModule" value="{l s='Update settings' mod='payhubgateway'}" class="button" />
		</center>
	</fieldset>
</form>
</div>
