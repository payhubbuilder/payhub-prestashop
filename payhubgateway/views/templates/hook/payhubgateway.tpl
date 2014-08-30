{*
* Copyright 2014 PayHub, Inc
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
* http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to automatically upgrade this 
* module to newer versions in the future.
*
* @author PayHub <wecare@payhub.com>
*}

<link rel="shortcut icon" type="image/x-icon" href="{$module_dir}img/secure.png" />
<div class="payhubgateway-payment-section">
	<div class="row">
		<div class="col-xs-12 col-md-4">
			<h2>{l s=' Pay by credit card' mod='payhubgateway'}</h1>
		</div>
		<div class="col-xs-12 col-md-offset-4 col-md-4">
			<div class="payhubgateway-accepted-cards">
				<h3>We accept:&nbsp;&nbsp;
				{if $cards.visa == 1}
					<img src="{$module_dir}/img/visa.jpg" alt="{l s='Visa ' mod='payhubgateway'}" />&nbsp;&nbsp;
				{/if}
				{if $cards.mastercard == 1}
					<img src="{$module_dir}/img/mastercard.jpg" alt="{l s='Mastercard ' mod='payhubgateway'}" />&nbsp;&nbsp;
				{/if}
				{if $cards.discover == 1}
					<img src="{$module_dir}/img/discover.jpg" alt="{l s='Discover ' mod='payhubgateway'}" />&nbsp;&nbsp;
				{/if}
				{if $cards.ax == 1}
					<img src="{$module_dir}/img/amex.jpg" alt="{l s='American Express ' mod='payhubgateway'}" />
				{/if}	
				</h3>	
			</div>
		</div>
		{if $is_failed == 1}
		<div class="col-xs-12 col-md-12">	
			<p class="processing-error">	
				{if !empty($error_message)}
					{l s='Failed to process the payment.' mod='payhubgateway'}&nbsp;&nbsp;
					{l s='Error:' mod='payhubgateway'}&nbsp;<span>{$error_message|htmlentities}</span>
				{else}	
					{l s='Failed to process the payment with an unknown error.' mod='payhubgateway'}
				{/if}
				<br />
				{l s='Please verify your card information and try again.' mod='payhubgateway'}
			</p>
		</div>
		{/if}
	</div>
	<div class="row">
		<form role="form" class="form-horizontal" action="{$module_dir}validation.php" method="post">
			<input type="hidden" name="ph_invoice_num" value="{$ph_invoice_num|escape:'htmlall':'UTF-8'}" />
			<input type="hidden" name="ph_currency_code" value="{$currency->iso_code|escape:'htmlall':'UTF-8'}" />
			<div class="form-group">
				<label for="ph_name_on_card" class="col-xs-12 col-md-offset-2 col-md-3 control-label">{l s='Name on Card' mod='payhubgateway'}</label> 
				<div class="col-xs-12 col-md-4">				
					<input class="form-control" type="text" name="ph_name_on_card" id="ph_name_on_card" maxlength="64" />
					<span class="help-block error-state" style="display:none">{l s='This field is required.' mod='payhubgateway'}</span>
				</div>
			</div>
			<div class="form-group">						
				<label for="ph_card_type" class="col-xs-12 col-md-offset-2 col-md-3 control-label">{l s='Card Type' mod='payhubgateway'}</label>
				<div class="col-xs-12 col-md-4">
					<select id="ph_card_type" class="form-control">                                  
						{if $cards.ax == 1}<option value="AmEx">American Express</option>{/if}
	            		{if $cards.visa == 1}<option value="Visa">Visa</option>{/if}
	    				{if $cards.mastercard == 1}<option value="MasterCard">MasterCard</option>{/if}
						{if $cards.discover == 1}<option value="Discover">Discover</option>{/if}
					</select>
				</div>
			</div>
			<div class="form-group">
				<label for="ph_card_num" class="col-xs-12 col-md-offset-2 col-md-3 control-label">{l s='Card number' mod='payhubgateway'}</label> 
				<div class="col-xs-12 col-md-4">
					<input class="form-control" type="text" name="ph_card_num" id="ph_card_num" maxlength="16" autocomplete="off" />
					<span class="help-block error-state" style="display:none">{l s='This field is required and must contain a valid credit card number.' mod='payhubgateway'}</span>
				</div>
			</div>
			<div class="form-group row">
				<label for="ph_exp_date_m" class="col-xs-12 col-md-offset-2 col-md-3 control-label">{l s='Expiration date' mod='payhubgateway'}</label>
				<div class="col-xs-3 col-md-2">
					<select class="form-control" id="ph_exp_date_m" name="ph_exp_date_m">
						{section name=date_m start=01 loop=13}					
							<option value="{$smarty.section.date_m.index}">{$smarty.section.date_m.index}</option>
						{/section}				
					</select>
					<span class="help-block error-state" style="display:none">{l s='The expiration date cannot be in the past.' mod='payhubgateway'}</span>
				</div>
				<div class="col-xs-3 col-md-2">
					<select class="form-control" id="ph_exp_date_y" name="ph_exp_date_y">
						{section name=date_y start=14 loop=26}
							<option value="20{$smarty.section.date_y.index}">20{$smarty.section.date_y.index}</option>
						{/section}				
					</select>
				</div>
			</div>
			<div class="form-group">				
				<label for="ph_card_cvv" class="col-xs-12 col-md-offset-2 col-md-3 control-label">{l s='CVV' mod='payhubgateway'}</label> 
				<div class="col-xs-10 col-md-3">
					<input class="form-control" type="text" name="ph_card_cvv" id="ph_card_cvv" size="4" maxlength="4" autocomplete="off" />			
					<span class="help-block error-state" style="display:none">{l s='This field is required and must be either 3 or 4 digits.' mod='payhubgateway'}</span>
				</div>
				<div class="col-xs-2 col-md-1">
					<a id="ph_cvv_help" href="{$module_dir}img/cvv.png">
						<img src="{$module_dir}img/help.png" title="{l s='The 3 last digits on the back of your credit card' mod='payhubgateway'}" alt="?" />
					</a>
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-offset-5 col-md-4">
					<button type="submit" id="submit_payment_btn" value="{l s='Submit Order' mod='payhubgateway'}" class="btn btn-primary"><img src="{$module_dir}img/secure.png" alt="" id="ph_security_image"/> Submit Order</button>
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<img src="{$module_dir}img/powered_by_payhub.png" alt="Powered by PayHub" />
				</div>
			</div>
			</div>		
		</form>
	</div>
</div>
