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

include(dirname(__FILE__). '/../../config/config.inc.php');
include(dirname(__FILE__). '/../../init.php');

/* will include backward file */
include(dirname(__FILE__). '/payhubgateway.php');

$payhubgateway = new PayHubGateway();

/* Does the cart exist and is valid? */
$cart = Context::getContext()->cart;

if (!isset($_POST['ph_invoice_num']))
{
	Logger::addLog('Missing ph_invoice_num', 4);
	die('An unrecoverable error occured: Missing parameter');
}

if (!Validate::isLoadedObject($cart))
{
	Logger::addLog('Cart loading failed for cart '.(int)$_POST['ph_invoice_num'], 4);
	die('An unrecoverable error occured with the cart '.(int)$_POST['ph_invoice_num']);
}

if ($cart->id != $_POST['ph_invoice_num'])
{
	Logger::addLog('Conflict between cart id order and customer cart id');
	die('An unrecoverable conflict error occured with the cart '.(int)$_POST['ph_invoice_num']);
}

$customer = new Customer((int)$cart->id_customer);
$invoiceAddress = new Address((int)$cart->id_address_invoice);
$currency = new Currency((int)$cart->id_currency);
$order_total = number_format((float)$cart->getOrderTotal(true, 3), 2, '.', '');
$full_card_num = Tools::safeOutput($_POST['ph_card_num']);

if (!Validate::isLoadedObject($customer) || !Validate::isLoadedObject($invoiceAddress) && !Validate::isLoadedObject($currency))
{
	Logger::addLog('Issue loading customer, address and/or currency data');
	die('An unrecoverable error occured while retrieving your data');
}

$params = array(
	'mode' => Configuration::get('PAYHUB_GATEWAY_MODE'),
	'orgid' => Tools::safeOutput(Configuration::get('PAYHUB_GATEWAY_ORGID_'.$currency->iso_code)),
	'username' => Tools::safeOutput(Configuration::get('PAYHUB_GATEWAY_USERNAME_'.$currency->iso_code)),
	'password' => Tools::safeOutput(Configuration::get('PAYHUB_GATEWAY_PASSWORD_'.$currency->iso_code)),
	'tid' => Tools::safeOutput(Configuration::get('PAYHUB_GATEWAY_TID_'.$currency->iso_code)),
	'invoice' => "ps-" . $_POST['ph_invoice_num'],
	'amount' => $order_total,
	'address1' => Tools::safeOutput($invoiceAddress->address1.' '.$invoiceAddress->address2),
	'zip' => Tools::safeOutput($invoiceAddress->postcode),
	'first_name' => Tools::safeOutput($customer->firstname),
	'last_name' => Tools::safeOutput($customer->lastname),
	'trans_type' => 'sale',
	'cc' => $full_card_num,
	'cvv' => Tools::safeOutput($_POST['ph_card_cvv']),
	'month' => Tools::safeOutput(str_pad($_POST['ph_exp_date_m'], 2, "0", STR_PAD_LEFT)),
	'year' => Tools::safeOutput($_POST['ph_exp_date_y'])
);

$post_data = json_encode($params);

$payhubgateway_api_url = 'https://checkout.payhub.com/transaction/api';

/* Do the CURL request to PayHub Gateway */
$request = curl_init($payhubgateway_api_url);
curl_setopt($request, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
curl_setopt($request, CURLOPT_POST, true);
curl_setopt($request, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($request, CURLOPT_SSL_VERIFYHOST, true);
$payhubgateway_response = curl_exec($request);
curl_close($request);

$response = json_decode($payhubgateway_response);
if (!$response)
{
	$msg = 'PayHub Gateway returned a malformed response for cart: $response: ';
	if (isset($response))
		$msg .= "var_dump($response)";
	Logger::addLog($msg, 4);
	die('PayHub Gateway returned a malformed response, aborted.');
}

$response_code = $response->RESPONSE_CODE;
$response_text = $response->RESPONSE_TEXT;
$risk_status = $response->RISK_STATUS_RESPONSE_CODE;

$payment_method = 'Credit Card x' . $payhubgateway->getTruncatedCard($full_card_num);

// if($risk_status == "") #risk hold
// {
// 	$payhubgateway->validateOrder((int)$cart->id,
// 	Configuration::get('PAYHUB_GATEWAY_HOLD_REVIEW_OS'), 
// 		$order_total, $payhubgateway->displayName, $response[3], NULL, NULL, false, $customer->secure_key);
// }
// else
if($response_code == "00") #success
{
	$payhubgateway->validateOrder((int)$cart->id, Configuration::get('PS_OS_PAYMENT'), 
		$order_total, $payment_method, $response_text, NULL, NULL, false, $customer->secure_key);
}
else #failed
{
	$error_message = urlencode(Tools::safeOutput($response_text."(".$response_code.")"));

	$checkout_type = Configuration::get('PS_ORDER_PROCESS_TYPE') ? 'order-opc' : 'order';
	$checkout_url = _PS_VERSION_ >= '1.5' ? 'index.php?controller='.$checkout_type.'&' : $checkout_type.'.php?';
	$checkout_url .= 'step=3&cgv=1&payhuberror=1&message='.$error_message;

	if (!isset($_SERVER['HTTP_REFERER']) || strstr($_SERVER['HTTP_REFERER'], 'order'))
		Tools::redirect($checkout_url);
	else if (strstr($_SERVER['HTTP_REFERER'], '?'))
		Tools::redirect($_SERVER['HTTP_REFERER'].'&payhuberror=1&message='.$error_message, '');
	else
		Tools::redirect($_SERVER['HTTP_REFERER'].'?payhuberror=1&message='.$error_message, '');
}

$checkout_url = 'index.php?controller=order-confirmation&';
if (_PS_VERSION_ < '1.5')
	$checkout_url = 'order-confirmation.php?';
	
$auth_order = new Order($payhubgateway->currentOrder);
Tools::redirect($checkout_url.'id_module='.(int)$payhubgateway->id.'&id_cart='.(int)$cart->id.'&key='.$auth_order->secure_key);
