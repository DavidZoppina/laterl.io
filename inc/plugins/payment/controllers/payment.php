<?php
class payment extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
		$this->tb_package_manager = "sp_package_manager";

		//
		$this->module_name = get_module_config( $this, 'name' );
		$this->module_icon = get_module_config( $this, 'icon' );
		//
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

		$package = $this->model->get("*", $this->tb_package_manager, "ids = '{$ids}'");
		if(empty($package)){
			redirect( get_url() );
		}

		$result = $this->model->get_package($ids, $plan);

		$counpon_view = false;
		if( find_modules("coupon_manager") ){
			$counpon_view = view("../../coupon_manager/views/pages/view", [ "package_id" => $package->id ], true);
		}

		view("index", [ 
			"result" => $result, 
			"counpon_view" => $counpon_view
		]);
	}

	public function cancel_subscription(){
		$this->model->stop_subscription();
	}

	public function success()
	{
		view("success", []);
	}

	public function unsuccess()
	{
		view("unsuccess", []);
	}
}