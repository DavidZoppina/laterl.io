<?php
class stripe_recurring extends MY_Controller {
    
    public $tb_package_manager = "sp_package_manager";
    public $tb_payment_subscriptions = "sp_payment_subscriptions";

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

        if( ! get_option("stripe_recurring_status", 0) ){
            redirect( get_url() );
        }

        $publishable_key = get_option("stripe_publishable_key", "");
        $secret_key = get_option("stripe_secret_key", "");
        $webhook_id = get_option("stripe_recurring_webhook_id");

        if( $publishable_key == "" || $secret_key == "" || $webhook_id == "" ){
            redirect( get_url("settings/index/04462744e12e12e249f9fa1b00754d36") );
        }
    }

    public function index($ids = "", $plan = 1)
    {
        if(!_s("uid")){
            redirect( get_url("signup?redirect=".urlencode( get_url("payment/index/".$ids."/".$plan) )) );
        }

        if(_gd("is_subscription", 0)){
            $error = __("You are using the monthly payment plan. Cancel it if you want to change the package or change your payment method.");
            redirect( get_url( "profile/index/package?error=".urlencode($error) ) );
        }

        $iszdc = isZeroDecimalCurrency( get_option('payment_currency','USD') );

        try {
            $package = $this->payment_model->get_package($ids, $plan);

            \Stripe\Stripe::setApiKey( get_option("stripe_secret_key", "") );

            $uid = _u("id");
            $email = _u("email");
            $customer_id = _gd("stripe_customer_id");

            if (!$customer_id) {
                try {
                    $customer = \Stripe\Customer::create([
                        "email" => $email,
                        "metadata" => [
                            "user_id" => $uid
                        ]
                    ]);
                } catch (\Exception $e) {
                    echo "Couldn't create the new customer";
                    exit(0);
                }

                if (empty($customer->id)) {
                    echo "Couldn't create the new customer";
                    exit(0);
                } 

                $customer_id = $customer->id;
                _ud("stripe_customer_id", $customer_id);
            }

            if ($customer_id) {
                try {
                    $customer = \Stripe\Customer::retrieve($customer_id);
                } catch (\Exception $e) {
                    $customer_id = null;
                }

                if (!empty($customer->id)) {
                    $update = false;
                    if ($customer->email != $email) {
                        $customer->email = $email;
                        $update = true;
                    }

                    if (isset($customer->metadata->user_id) && $customer->metadata->user_id != $uid) {
                        $customer->metadata->user_id = $uid;
                        $update = true;
                    }

                    if ($update) {
                        $customer->save();
                    }
                } 
            }

            $plan_id = "plan"
                     . "-" . $package->id
                     . "-" . ($plan == 2 ? "annualy" : "monthly")
                     . "-" . ( $iszdc? round($package->amount): $package->amount*100 )
                     . "-" . strtolower( get_option('payment_currency','USD') );

            try {
                $plan_result = \Stripe\Plan::retrieve($plan_id);
            } catch (\Exception $e) {
                $plan = null;
            }

            if (empty($plan_result)) {
                // Create new plan
                try {
                    $plan_result = \Stripe\Plan::create([
                        "id" => $plan_id,
                        "amount" => $iszdc? round($package->amount): $package->amount*100,
                        "interval" => $plan == 2 ? "year" : "month",
                        "product" => [
                            "name" => $package->name
                                    . " - " 
                                    . ($plan == 2 ? "Annualy" : "Monthly")
                        ],
                        "currency" => get_option('payment_currency','USD')
                    ]);

                } catch (\Exception $e) {
                    echo $e->getMessage();
                    exit(0);
                }
            }

            // Create subscription
            try {
                $session = \Stripe\Checkout\Session::create([
                    'customer' => $customer_id,
                    'payment_method_types' => ['card'],
                    'subscription_data' => [
                        'items' => [
                            ['plan' => $plan_id]
                        ],
                    ],
                    "metadata" => [
                        "order_id" => $package->id,
                        "user_id" => $uid
                    ],
                    'success_url' => get_url("stripe_recurring/complete/".$ids."/".$plan),
                    'cancel_url' =>  get_url("payment/unsuccess"),
                ]);

                _ss("stripe_recurring_check", $session->id);
                view("index", [ "checkout_session_id" => $session->id ]);
            } catch (\Exception $e) {
                echo $e->getMessage();
                exit(0);
            }

        } catch (Exception $e) {
            echo $e->getMessage();
            exit(0);
        }
    }

    public function complete($ids = "", $plan = ""){
        try {
            if(!_s("stripe_recurring_check")) redirect( get_module_url("index/".$ids."/".$plan) );
            
            $package = $this->payment_model->get_package($ids, $plan);

            \Stripe\Stripe::setApiKey( get_option("stripe_secret_key", "") );
            $payment = \Stripe\Checkout\Session::retrieve( _s("stripe_recurring_check") );

            _us("stripe_recurring_check");

            if($payment->customer){
                $subscription = [
                    "type" => "stripe_recurring",
                    "plan" => $plan,
                    "package" => $package->id,
                    "subscription_id" => $payment->subscription,
                    "customer_id" => $payment->customer,
                ];

                $this->payment_model->save_subscription($subscription);
            }else{
                redirect( get_url("payment/unsuccess") );
            }

        } catch (Exception $e) {
            echo $e->getMessage();
            exit(0);
        }

    }

    public function webhook()
    {
        sleep(3);
        
        \Stripe\Stripe::setApiKey( get_option('stripe_secret_key', '') );

        $endpoint_secret = get_option('stripe_recurring_webhook_id', '');

        $payload = @file_get_contents('php://input');
        $sig_header = @$_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

        switch ($event->type) {
            case "invoice.payment_succeeded":
                $paymentIntent = $event->data->object; 
                $this->whInvoicePaymentSucceeded($paymentIntent);
                break;

            case "customer.subscription.deleted":
                $paymentMethod = $event->data->object;
                $this->whSubscriptionDeleted($paymentMethod);
                break;
            
            default:
                break;
        }

        http_response_code(200);
    }

    private function whInvoicePaymentSucceeded($event)
    {
        if (empty($event->charge)) {
            http_response_code(400);
            exit;
        }

        if (empty($event->subscription)) {
            http_response_code(400);
            exit;
        }

        $iszdc = isZeroDecimalCurrency( get_option('payment_currency','USD') );

        $this->payment_model->update_subscription([
            "subscription_id" => $event->subscription,
            'transaction_id' => $event->charge,
            'amount' => $iszdc ? $event->amount_paid : ($event->amount_paid / 100)
        ]);
    }

    private function whSubscriptionDeleted($event)
    {
        $this->payment_model->cancel_subscription($event->id);
    }

}