<?php

include_once 'digital-payment-4all-gateway.php';

class APP_Gateway_4all extends APP_Gateway {

  /**
	 * Sets up the gateway
	 */
	public function __construct() {
		parent::__construct( 'digitalPayments4all', array(
			'dropdown' => __( 'Pagamentos digitais 4all', 'digital-payment-4all' ),
			'admin' => __( 'Pagamentos digitais 4all', 'digital-payment-4all' ),
		) );
  }

  public function form() {
    $general = array(
			'title' => __( 'General', 'digital-payment-4all' ),
			'desc' => __( 'If you do not already have 4all merchant account, <a href="https://autocredenciamento.4all.com" target="_blank">please register in Production</a>.', 'digital-payment-4all' ),
			'fields' => array(
				array(
					'title' => __( 'Merchant key', 'digital-payment-4all' ),
					'type' => 'text',
					'name' => 'merchant_key_prod',
					'desc' => __( 'Please enter your merchantKey of production. This is needed to process the payment.', 'digital-payment-4all' ),
					'tip' => __( 'This is your private key to access the 4all gateway in the production environment', 'digital-payment-4all' ),
				)
			)
    );

    $sandbox = array(
			'title' => __( 'Sandbox', 'digital-payment-4all' ),
			'desc' => __( 'If enabled, sandbox mode will send your transactions to the homolog environment.', 'digital-payment-4all' ),
			'fields' => array(
        array(
					'title' => __( 'Enable', 'digital-payment-4all' ),
					'type' => 'checkbox',
          'name' => 'sandbox_enabled',
          'desc' => __( 'Enable/Disable the sandbox mode', 'digital-payment-4all' ),
				),
        array(
					'title' => __( 'Merchant key', 'digital-payment-4all' ),
					'type' => 'text',
					'name' => 'merchant_key_homolog',
					'desc' => __( 'Please enter your merchantKey of homolog. This is needed to process the payment.', 'digital-payment-4all' ),
					'tip' => __( 'This is your private key to access the 4all gateway in the homolog environment', 'digital-payment-4all' ),
				)
			)
    );

    return array('general' => $general, 'sandbox' => $sandbox);
  }

  public function process( $order, $options ) {
		$environment = $options['sandbox_enabled'] ? 'https://gateway.homolog-interna.4all.com/' : 'https://gateway.api.4all.com/';
		$key = $options['sandbox_enabled'] ? $options['merchant_key_homolog'] : $options['merchant_key_prod'];
		$settings = array('merchantKey' => $key, 'environment' => $environment);
		$gateway_4all = new Gateway_4all($settings);
		$transactionError = false;
		$formUrl = $order->get_return_url();
		$cancelUrl = $order->get_cancel_url();

		if (isset( $_POST['completeTransaction'] )) {
			$paymentData = [
				"cardData" => [
					"cardholderName" => $_REQUEST["cardholderName"],
					"buyerDocument" => str_replace(array('.', '-', ' '), '', $_REQUEST["buyerDocument"]),
					"cardNumber" => $_REQUEST["cardNumber"],
					"expirationDate" => $_REQUEST["expirationDate"],
					"securityCode" => $_REQUEST["securityCode"]
					],
				"installment" => $_REQUEST['installment'],
				"total" => (int)$order->get_total() * 100,
				"metaId" => "" . $order->get_id(),
			];

			$tryPay = $gateway_4all->paymentFlow_4all($paymentData);

			if ($tryPay["error"]) {
				$order->failed();
				$transactionError = true;
				require_once 'form-template.php';
			} else {
				$order->complete();;
			}
		} else {
			require_once 'form-template.php';
		}
	}
}

appthemes_register_gateway( 'APP_Gateway_4all' );