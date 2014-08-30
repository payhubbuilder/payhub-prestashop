<?php
/*
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
*/

if (!defined('_PS_VERSION_'))
	exit;

class PayHubGateway extends PaymentModule
{
	public function __construct()
	{
		$this->name = 'payhubgateway';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.0';
		$this->author = 'PayHub';
		$this->payhubgateway_available_currencies = array('USD');

		parent::__construct();

		$this->displayName = 'PayHub Gateway';
		$this->description = $this->l('Receive payments with PayHub');


		/* For 1.4.3 and less compatibility */
		$updateConfig = array(
			'PS_OS_CHEQUE' => 1,
			'PS_OS_PAYMENT' => 2,
			'PS_OS_PREPARATION' => 3,
			'PS_OS_SHIPPING' => 4,
			'PS_OS_DELIVERED' => 5,
			'PS_OS_CANCELED' => 6,
			'PS_OS_REFUND' => 7,
			'PS_OS_ERROR' => 8,
			'PS_OS_OUTOFSTOCK' => 9,
			'PS_OS_BANKWIRE' => 10,
			'PS_OS_PAYPAL' => 11,
			'PS_OS_WS_PAYMENT' => 12);

		foreach ($updateConfig as $u => $v)
			if (!Configuration::get($u) || (int)Configuration::get($u) < 1)
			{
				if (defined('_'.$u.'_') && (int)constant('_'.$u.'_') > 0)
					Configuration::updateValue($u, constant('_'.$u.'_'));
				else
					Configuration::updateValue($u, $v);
			}

		/* Check if cURL is enabled */
		if (!is_callable('curl_exec'))
			$this->warning = $this->l('cURL extension must be enabled on your server to use this module.');

		/* Backward compatibility */
		require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');

		$this->checkForUpdates();
	}

	public function install()
	{
		return parent::install() &&
			$this->registerHook('orderConfirmation') &&
			$this->registerHook('payment') &&
			$this->registerHook('header') &&
			$this->registerHook('backOfficeHeader') &&
			Configuration::updateValue('PAYHUB_GATEWAY_MODE', "demo") &&
			Configuration::updateValue('PAYHUB_GATEWAY_CARD_VISA', 1) &&
			Configuration::updateValue('PAYHUB_GATEWAY_CARD_MASTERCARD', 1) &&			
			Configuration::updateValue('PAYHUB_GATEWAY_CARD_DISCOVER', 1);			
			//Configuration::updateValue('PAYHUB_GATEWAY_HOLD_REVIEW_OS', _PS_OS_ERROR_);
	}

	public function uninstall()
	{
		Configuration::deleteByName('PAYHUB_GATEWAY_MODE');
		Configuration::deleteByName('PAYHUB_GATEWAY_CARD_VISA');
		Configuration::deleteByName('PAYHUB_GATEWAY_CARD_MASTERCARD');
		Configuration::deleteByName('PAYHUB_GATEWAY_CARD_DISCOVER');
		Configuration::deleteByName('PAYHUB_GATEWAY_CARD_AX');
		//Configuration::deleteByName('PAYHUB_GATEWAY_HOLD_REVIEW_OS');

		/* Removing credentials configuration variables */
		$currencies = Currency::getCurrencies(false, true);
		foreach ($currencies as $currency)
			if (in_array($currency['iso_code'], $this->payhubgateway_available_currencies))
			{
				Configuration::deleteByName('PAYHUB_GATEWAY_ORGID_'.$currency['iso_code']);
				Configuration::deleteByName('PAYHUB_GATEWAY_USERNAME_'.$currency['iso_code']);
				Configuration::deleteByName('PAYHUB_GATEWAY_PASSWORD_'.$currency['iso_code']);
				Configuration::deleteByName('PAYHUB_GATEWAY_TID_'.$currency['iso_code']);
			}

		return parent::uninstall();
	}

	public function addAssets()
	{
		$this->context->controller->addJQuery();
		if (version_compare(_PS_VERSION_, '1.5', '>='))
			$this->context->controller->addJqueryPlugin('fancybox');

		$this->context->controller->addJS($this->_path.'js/payhubgateway.js');
		$this->context->controller->addCSS($this->_path.'css/payhubgateway.css');
	}

	public function hookBackOfficeHeader()
	{
		self::addAssets();
	}

	public function getContent()
	{
		$confirmation_html = '';

		if (Tools::isSubmit('submitModule'))
		{
			$payhubgateway_mode = Tools::getvalue('payhubgateway_mode');
			// Test environment
			if ($payhubgateway_mode == "demo")
			{
				Configuration::updateValue('PAYHUB_GATEWAY_MODE', 'demo');
			}
			// Default to production environment
			else
			{
				Configuration::updateValue('PAYHUB_GATEWAY_MODE', 'live');
			}

			Configuration::updateValue('PAYHUB_GATEWAY_CARD_VISA', Tools::getvalue('payhubgateway_card_visa'));
			Configuration::updateValue('PAYHUB_GATEWAY_CARD_MASTERCARD', Tools::getvalue('payhubgateway_card_mastercard'));
			Configuration::updateValue('PAYHUB_GATEWAY_CARD_DISCOVER', Tools::getvalue('payhubgateway_card_discover'));
			Configuration::updateValue('PAYHUB_GATEWAY_CARD_AX', Tools::getvalue('payhubgateway_card_ax'));
			//Configuration::updateValue('PAYHUB_GATEWAY_HOLD_REVIEW_OS', Tools::getvalue('payhubgateway_hold_review_os'));

			/* Updating credentials for each active currency */
			foreach ($_POST as $key => $value)
			{
				if (strstr($key, 'payhubgateway_orgid_'))
					Configuration::updateValue('PAYHUB_GATEWAY_ORGID_'.str_replace('payhubgateway_orgid_', '', $key), $value);
				elseif (strstr($key, 'payhubgateway_username_'))
					Configuration::updateValue('PAYHUB_GATEWAY_USERNAME_'.str_replace('payhubgateway_username_', '', $key), $value);
				elseif (strstr($key, 'payhubgateway_password_'))
					Configuration::updateValue('PAYHUB_GATEWAY_PASSWORD_'.str_replace('payhubgateway_password_', '', $key), $value);
				elseif (strstr($key, 'payhubgateway_tid_'))
					Configuration::updateValue('PAYHUB_GATEWAY_TID_'.str_replace('payhubgateway_tid_', '', $key), $value);
			}

			$confirmation_html = $this->displayConfirmation($this->l('Configuration updated'));
		}

		// For "Hold for Review" order status
		$currencies = Currency::getCurrencies(false, true);
		$order_states = OrderState::getOrderStates((int)$this->context->cookie->id_lang);

		$this->context->smarty->assign(array(
			'available_currencies' => $this->payhubgateway_available_currencies,
			'currencies' => $currencies,
			'module_dir' => $this->_path,
			'order_states' => $order_states,
			'update_confirmation' => $confirmation_html,

			'PAYHUB_GATEWAY_MODE' => Configuration::get('PAYHUB_GATEWAY_MODE'),

			'PAYHUB_GATEWAY_CARD_VISA' => Configuration::get('PAYHUB_GATEWAY_CARD_VISA'),
			'PAYHUB_GATEWAY_CARD_MASTERCARD' => Configuration::get('PAYHUB_GATEWAY_CARD_MASTERCARD'),
			'PAYHUB_GATEWAY_CARD_DISCOVER' => Configuration::get('PAYHUB_GATEWAY_CARD_DISCOVER'),
			'PAYHUB_GATEWAY_CARD_AX' => Configuration::get('PAYHUB_GATEWAY_CARD_AX'),
			//'PAYHUB_GATEWAY_HOLD_REVIEW_OS' => (int)Configuration::get('PAYHUB_GATEWAY_HOLD_REVIEW_OS'),
		));

		/* Determine which currencies are enabled on the store and supported by PayHub & list one credentials section per available currency */
		foreach ($currencies as $currency)
		{
			if (in_array($currency['iso_code'], $this->payhubgateway_available_currencies))
			{
				$configuration_orgid_name = 'PAYHUB_GATEWAY_ORGID_'.$currency['iso_code'];
 				$configuration_username_name = 'PAYHUB_GATEWAY_USERNAME_'.$currency['iso_code'];
 				$configuration_password_name = 'PAYHUB_GATEWAY_PASSWORD_'.$currency['iso_code'];
 				$configuration_tid_name = 'PAYHUB_GATEWAY_TID_'.$currency['iso_code'];
				$this->context->smarty->assign($configuration_orgid_name, Configuration::get($configuration_orgid_name));
				$this->context->smarty->assign($configuration_username_name, Configuration::get($configuration_username_name));
				$this->context->smarty->assign($configuration_password_name, Configuration::get($configuration_password_name));
				$this->context->smarty->assign($configuration_tid_name, Configuration::get($configuration_tid_name));
			}
		}

		return $this->context->smarty->fetch(dirname(__FILE__).'/views/templates/admin/configuration.tpl');
	}

	public function hookPayment($params)
	{
		$currency = Currency::getCurrencyInstance($this->context->cookie->id_currency);

		if (!Validate::isLoadedObject($currency))
			return false;

		if (Configuration::get('PS_SSL_ENABLED') || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off'))
		{
			$is_failed = Tools::getValue('payhuberror');
			$error_message = Tools::getValue('message');

			$cards = array();
			$cards['visa'] = Configuration::get('PAYHUB_GATEWAY_CARD_VISA') == 'on';
			$cards['mastercard'] = Configuration::get('PAYHUB_GATEWAY_CARD_MASTERCARD') == 'on';
			$cards['discover'] = Configuration::get('PAYHUB_GATEWAY_CARD_DISCOVER') == 'on';
			$cards['ax'] = Configuration::get('PAYHUB_GATEWAY_CARD_AX') == 'on';

			if (method_exists('Tools', 'getShopDomainSsl'))
				$url = 'https://'.Tools::getShopDomainSsl().__PS_BASE_URI__.'/modules/'.$this->name.'/';
			else
				$url = 'https://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__.'modules/'.$this->name.'/';

			$this->context->smarty->assign('ph_invoice_num', (int)$params['cart']->id);
			$this->context->smarty->assign('cards', $cards);
			$this->context->smarty->assign('is_failed', $is_failed);
			$this->context->smarty->assign('new_base_dir', $url);
			$this->context->smarty->assign('currency', $currency);
			$this->context->smarty->assign('error_message', $error_message);

			self::addAssets();

			return $this->display(__FILE__, 'views/templates/hook/payhubgateway.tpl');
		}
	}

	public function hookOrderConfirmation($params)
	{
		if ($params['objOrder']->module != $this->name)
			return;

		if ($params['objOrder']->getCurrentState() != Configuration::get('PS_OS_ERROR'))
		{
			Configuration::updateValue('PAYHUB_GATEWAY_CONFIGURATION_OK', true);
			$this->context->smarty->assign(array('status' => 'ok', 'id_order' => intval($params['objOrder']->id)));
		}
		else
			$this->context->smarty->assign('status', 'failed');

		self::addAssets();

		return $this->display(__FILE__, 'views/templates/hook/orderconfirmation.tpl');
	}

	public function hookHeader()
	{
		if (_PS_VERSION_ < '1.5')
			Tools::addJS(_PS_JS_DIR_.'jquery/jquery.validate.creditcard2-1.0.1.js');
		else
			$this->context->controller->addJqueryPlugin('validate-creditcard');
	}

	public function getTruncatedCard($card_num) 
	{
		if(strlen($card_num) > 4) 
			return substr($card_num, -4);
		else 
			return $card_num;
	}

	private function checkForUpdates()
	{
		// Used by PrestaShop 1.3 & 1.4
		if (version_compare(_PS_VERSION_, '1.5', '<') && self::isInstalled($this->name))
			foreach (array('1.4.8', '1.4.11') as $version)
			{
				$file = dirname(__FILE__).'/upgrade/install-'.$version.'.php';
				if (Configuration::get('PAYHUB_GATEWAY') < $version && file_exists($file))
				{
					include_once($file);
					call_user_func('upgrade_module_'.str_replace('.', '_', $version), $this);
				}
			}
	}

}
