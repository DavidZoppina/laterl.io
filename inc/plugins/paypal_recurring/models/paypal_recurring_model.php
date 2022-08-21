<?php
class paypal_recurring_model extends MY_Model {
	public function __construct(){
		parent::__construct();
		$this->load->model('payment/payment_model', 'payment_model');

		//
		$module_path = get_module_directory(__DIR__);
		include $module_path.'libraries/vendor/autoload.php';

		$this->module_name = get_module_config( $module_path, 'name' );
		$this->module_icon = get_module_config( $module_path, 'icon' );
		$this->module_color = get_module_config( $module_path, 'color' );
		//
	}

	public function block_payment_settings($path = ""){
		$dir = get_directory_block_setttings( __DIR__, get_class($this) );
		
		return array(
			"position" => 9999,
			"content" => view( $dir.'settings', [], true, $this )
		);
	}

	public function block_payment_view($path = ""){
		$dir = get_directory_block(__DIR__, get_class($this));
		
		return array(
			"position" => 9999,
			"content" => view( $dir.'block', [], true, $this )
		);
	}

	public function cancel_subscription($subscription_id){
		$client_id = get_option("paypal_client_id");
        $client_secret = get_option("paypal_client_secret");
		$webhook_id = get_option("paypal_recurring_webhook_id");
		if( get_option("paypal_recurring_status") && $client_id && $client_secret && $webhook_id){

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
			
			$agreement = new \PayPal\Api\Agreement();
	        $agreement->setId($subscription_id);
	        $agreementStateDescriptor = new \PayPal\Api\AgreementStateDescriptor();
	        $agreementStateDescriptor->setNote("Cancel the agreement");

	        try {
	            $agreement->cancel($agreementStateDescriptor, $this->apiContext);
	            $cancelAgreementDetails = \PayPal\Api\Agreement::get($agreement->getId(), $this->apiContext); 
	            $this->payment_model->delete_supscription($subscription_id);
	            return true;               
	        } catch (Exception $e) {
	            return false;
	        }
		}

		return false;
	}
}
