<?php
class paypal extends MY_Controller {
	
	public $tb_package_manager = "sp_package_manager";

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
		$this->load->model('payment/payment_model', 'payment_model');

		$module_path = get_module_directory(__DIR__);
		include $module_path.'libraries/vendor/autoload.php';

		//
		$this->module_name = get_module_config( $this, 'name' );
		$this->module_icon = get_module_config( $this, 'icon' );
		//

		if( ! get_option("paypal_status") ){
			redirect( get_url() );
		}

		$client_id = get_option("paypal_client_id");
		$client_secret = get_option("paypal_client_secret");

		if( $client_id == "" || $client_secret == "" ){
			redirect( get_url("settings/index/04462744e12e12e249f9fa1b00754d36") );
		}

        $this->apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential($client_id, $client_secret)
        );

        if(get_option("payment_environment", 0)){
            $this->apiContext->setConfig(
                array(
                    'mode' => 'live',
                )
            );
        }
	}

	public function index($ids = "", $plan = 1)
	{
        if(!_s("uid")){
            redirect( get_url("login?redirect=".urlencode( get_url("payment/index/".$ids."/".$plan) )) );
        }

        if(_gd("is_subscription", 0)){
            $error = __("You are using the monthly payment plan. Cancel it if you want to change the package or change your payment method.");
            redirect( get_url( "profile/index/package?error=".urlencode($error) ) );
        }
        
		$package = $this->payment_model->get_package($ids, $plan);

        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');

        $items[0] = new \PayPal\Api\Item();
        $items[0]->setName( $package->name." - ".($plan==2?"Annually":"Monthly") )
        ->setCurrency( get_option('payment_currency','USD') )
        ->setQuantity(1)
        ->setSku($package->id)
        ->setPrice( $package->amount );

        $itemList = new \PayPal\Api\ItemList();
        $itemList->setItems($items);

        $amount = new \PayPal\Api\Amount();
        $amount->setTotal( $package->amount );
        $amount->setTotal( $package->amount );
        $amount->setCurrency( get_option('payment_currency','USD') );

        $transaction = new \PayPal\Api\Transaction();
        $transaction->setAmount($amount);
        $transaction->setItemList($itemList);
        
        $redirectUrls = new \PayPal\Api\RedirectUrls();
        $redirectUrls->setReturnUrl( get_url("paypal/complete/".$ids."/".$plan) )
            ->setCancelUrl( get_url("payment/unsuccess") );
        $payment = new \PayPal\Api\Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions(array($transaction))
            ->setRedirectUrls($redirectUrls);

        try {
        	_ss("paypal_check", true);
            $payment->create($this->apiContext);
            redirect($payment->getApprovalLink());
        }
        catch (\PayPal\Exception\PayPalConnectionException $e) {
            pr($e);
            exit(0);
        }
	}

	public function complete($ids = "", $plan = ""){

		try {
			if(!_s("paypal_check")) redirect( get_module_url("index/".$ids."/".$plan) );

            $package = $this->payment_model->get_package($ids, $plan);
	        $payment = \PayPal\Api\Payment::get( post("paymentId") , $this->apiContext);
	        $execution = new \PayPal\Api\PaymentExecution();
	        $execution->setPayerId( post("PayerID") );

            $payment = $payment->execute($execution, $this->apiContext);

            _us("paypal_check");

            if($payment->getState() == "approved"){
            	$data = [
					'type' => 'paypal',
					'package' => $package->id,
					'transaction_id' => $payment->getTransactions()[0]->getRelatedResources()[0]->getSale()->getId(),
					'amount' => $payment->getTransactions()[0]->getAmount()->getTotal(),
					'plan' => $plan,
				];

				$this->payment_model->save($data);
            }else{
            	redirect( get_url("payment/unsuccess") );
            }

        } catch (Exception $e) {
            echo $e->getMessage();
            exit(0);
        }

	}

}