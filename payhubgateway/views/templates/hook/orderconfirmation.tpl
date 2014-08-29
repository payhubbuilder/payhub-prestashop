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

{if $status == 'ok'}
	<p>{l s='Thank you for your order!  You will receive a confirmation email with details on your order.' mod='payhubgateway'}
		<br /><br />
		{l s='For any questions or for further information, please contact our' mod='payhubgateway'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='payhubgateway'}</a>.
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='payhubgateway'} 
		<a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='payhubgateway'}</a>.
	</p>
{/if}
