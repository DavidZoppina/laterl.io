<?php
class stripe extends MY_Controller {
    
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

        if( ! get_option("stripe_status", 0) ){
            redirect( get_url() );
        }

        $publishable_key = get_option("stripe_publishable_key", "");
        $secret_key = get_option("stripe_secret_key", "");

        if( $publishable_key == "" || $secret_key == "" ){
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
        
        try {
            $iszdc = isZeroDecimalCurrency( get_option('payment_currency','USD') );
            
            $package = $this->payment_model->get_package($ids, $plan);

            \Stripe\Stripe::setApiKey( get_option("stripe_secret_key", "") );

            $session = \Stripe\Checkout\Session::create([
              'payment_method_types' => ['card'],
              'line_items' => [[
                'name' => $package->name,
                'description' => $package->description,
                'amount' => round( !$iszdc ? $package->amount : ($package->amount / 100), 2)*100,
                'currency' => strtolower( get_option('payment_currency', 'USD') ),
                'quantity' => 1,
              ]],
              'success_url' => get_url("stripe/complete/".$ids."/".$plan),
              'cancel_url' =>  get_url("payment/unsuccess"),
            ]);

            _ss("stripe_check", $session->id);

            view("index", [ "checkout_session_id" => $session->id ]);
        } catch (Exception $e) {
            echo $e->getMessage();
            exit(0);
        }
    }

    public function complete($ids = "", $plan = ""){
        try {
            if(!_s("stripe_check")) redirect( get_module_url("index/".$ids."/".$plan) );
            
            $package = $this->payment_model->get_package($ids, $plan);

            \Stripe\Stripe::setApiKey( get_option("stripe_secret_key", "") );
            $payment = \Stripe\Checkout\Session::retrieve( _s("stripe_check") );

            _us("stripe_check");

            $intent = \Stripe\PaymentIntent::retrieve( $payment->payment_intent );

            if( $intent->status == "succeeded" ){
                $data = [
                    'type' => 'stripe',
                    'package' => $package->id,
                    'transaction_id' => $payment->payment_intent,
                    'amount' => $payment->display_items[0]->amount/100,
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