<?php
class paypal_recurring extends MY_Controller {
    
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

        if( ! get_option("paypal_recurring_status") ){
            redirect( get_url() );
        }

        $client_id = get_option("paypal_client_id");
        $client_secret = get_option("paypal_client_secret");
        $webhook_id = get_option("paypal_recurring_webhook_id");

        if( $client_id == "" || $client_secret == "" || $webhook_id == ""){
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
        
        try {
            $package = $this->payment_model->get_package($ids, $plan);
            $package->amount = round($package->amount, 2);

            $cycles = '12';
            $frequency = 'MONTH';
            if($plan == 2){
                $cycles = '2';
                $frequency = 'YEAR';
            }

            $create_plan = new \PayPal\Api\Plan();
            $create_plan->setName( "Package: ".$package->name. " - " . ($plan == 2?"Anually":"Monthly") )
                ->setDescription($package->description)
                ->setType('FIXED');

            // Set billing plan definitions
            $paymentDefinition = new \PayPal\Api\PaymentDefinition();
            $paymentDefinition->setName('Regular Payments')
                ->setType('REGULAR')
                ->setFrequency($frequency)
                ->setFrequencyInterval('1')
                ->setCycles($cycles)
                ->setAmount(new \PayPal\Api\Currency(array(
                    'value' => $package->amount,
                    'currency' => "USD"
                )
            ));

            // Set merchant preferences
            $merchantPreferences = new \PayPal\Api\MerchantPreferences();
            $merchantPreferences
                ->setReturnUrl( get_url("paypal_recurring/complete/".$ids."/".$plan) )
                ->setCancelUrl( get_url("payment/unsuccess") )
                ->setAutoBillAmount('yes')
                ->setInitialFailAmountAction('CONTINUE')
                ->setMaxFailAttempts('0')
                ->setSetupFee(new \PayPal\Api\Currency( ['value' => $package->amount, 'currency' => "USD" ]));

            $create_plan->setPaymentDefinitions([$paymentDefinition]);
            $create_plan->setMerchantPreferences($merchantPreferences);

            $createdPlan = $create_plan->create($this->apiContext);

            $patch = new \PayPal\Api\Patch();
            $patch->setOp('replace')
                ->setPath('/')
                ->setValue( json_decode('{"state":"ACTIVE"}') );

            $patchRequest = new \PayPal\Api\PatchRequest();
            $patchRequest->addPatch($patch);
            $createdPlan->update($patchRequest, $this->apiContext);
            $patchedPlan = \PayPal\Api\Plan::get($createdPlan->getId(), $this->apiContext);
            
            
            // Create new agreement
            $startDate = date('c', time() + 30 * 86400);
            $agreement = new \PayPal\Api\Agreement();
            $agreement->setName( "Package: ".$package->name. " - " . ($plan == 2?"Anually":"Monthly") )
                ->setDescription( "Package: ".$package->name. " - " . ($plan == 2?"Anually":"Monthly") )
                ->setStartDate($startDate);

            // Set plan id
            $plan = new \PayPal\Api\Plan();
            $plan->setId($patchedPlan->getId());
            $agreement->setPlan($plan);

            // Add payer type
            $payer = new \PayPal\Api\Payer();
            $payer->setPaymentMethod('paypal');
            $agreement->setPayer($payer);

            $agreement = $agreement->create($this->apiContext);
            $approvalUrl = $agreement->getApprovalLink();
            
            redirect($approvalUrl);

        } catch (Exception $e) {
            echo $e->getMessage();
            exit(0);
        }
    }

    public function complete($ids = "", $plan = ""){

        try {
            $package = $this->payment_model->get_package($ids, $plan);
            $agreement = new \PayPal\Api\Agreement();

            //Execute agreement
            $payment = $agreement->execute( post("token"), $this->apiContext);

            /*REMOVE OLD SUPSCRIBTION*/
            $this->cancel_subscription();

            $subscription = [
                "type" => "paypal_recurring",
                "plan" => $plan,
                "package" => $package->id,
                "subscription_id" => $payment->getId(),
                "customer_id" => $payment->getPayer()->getPayerInfo()->getPayerId(),
            ];

            $this->payment_model->save_subscription($subscription);

        } catch (Exception $e) {
            echo $e->getMessage();
            exit(0);
        }

    }

    public function cancel_subscription(){
        $subscription = $this->payment_model->get_supscription("paypal_recurring");
        if($subscription){
            $agreement = new \PayPal\Api\Agreement();
            $agreement->setId($subscription->subscription_id);
            $agreementStateDescriptor = new \PayPal\Api\AgreementStateDescriptor();
            $agreementStateDescriptor->setNote("Cancel the agreement");

            try {
                $agreement->cancel($agreementStateDescriptor, $this->apiContext);
                $cancelAgreementDetails = \PayPal\Api\Agreement::get($agreement->getId(), $this->apiContext); 
                $this->payment_model->delete_supscription($subscription->subscription_id);
                return true;               
            } catch (Exception $e) {
                return false;
            }
        }
    }

    public function webhook(){
        $requestBody = file_get_contents('php://input');
        $headers = getallheaders();
        $headers = array_change_key_case($headers, CASE_UPPER);

        if(isset($headers['PAYPAL-AUTH-ALGO'])){

            $signatureVerification = new \PayPal\Api\VerifyWebhookSignature();
            $signatureVerification->setAuthAlgo($headers['PAYPAL-AUTH-ALGO']);
            $signatureVerification->setTransmissionId($headers['PAYPAL-TRANSMISSION-ID']);
            $signatureVerification->setCertUrl($headers['PAYPAL-CERT-URL']);
            $signatureVerification->setWebhookId( get_option('paypal_recurring_webhook_id', '') );
            $signatureVerification->setTransmissionSig($headers['PAYPAL-TRANSMISSION-SIG']);
            $signatureVerification->setTransmissionTime($headers['PAYPAL-TRANSMISSION-TIME']);

            $signatureVerification->setRequestBody($requestBody);
            $request = clone $signatureVerification;

            try {
                $output = $signatureVerification->post($this->apiContext);
                if($output->verification_status == "SUCCESS"){

                    $request = json_decode($request);
                    $request = $request->webhook_event;

                    switch ($request->event_type) {
                        case 'PAYMENT.SALE.COMPLETED':
                            $this->payment_model->update_subscription([
                                "subscription_id" => $request->resource->billing_agreement_id,
                                'transaction_id' => $request->resource->id,
                                'amount' => $request->resource->amount->total
                            ]);
                            break;

                        case 'BILLING.SUBSCRIPTION.CANCELLED':
                            $this->payment_model->cancel_subscription($this->resource->billing_agreement_id);
                            break;

                        default:
                            break;
                    }
                }
            } catch (Exception $e) {
            }
        }
    }
}