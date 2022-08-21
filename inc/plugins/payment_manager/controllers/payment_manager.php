<?php
class payment_manager extends MY_Controller {
	
	public $module_name;

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');

		//
		$this->module_name = get_module_config( $this, 'name' );
		$this->module_icon = get_module_config( $this, 'icon' );
		//
	}

	public function index($page = "", $ids = "")
	{
		$page_type = is_ajax()?false:true;

		//
		$data = [];
		switch ($page) {
			case 'history':
				$data = $this->model->get_data();
				break;

			default:
				$data['result'] = $this->model->get_report();
		}

		$page = page($this, "pages", "general", $page, $data, $page_type);
		//

		if( !is_ajax() ){

			$views = [
				"subheader" => view( 'main/subheader', [ 'module_name' => $this->module_name, 'module_icon' => $this->module_icon ], true ),
				"column_one" => view("main/sidebar", [ 'module_name' => $this->module_name, 'module_icon' => $this->module_icon ], true ),
				"column_two" => view("main/content", [ 'view' => $page ] ,true), 
			];
			
			views( [
				"title" => $this->module_name,
				"fragment" => "fragment_two",
				"views" => $views
			] );

		}else{
			_e( $page, false );
		}

	}

	public function ajax_account(){
		$fullname = post("fullname");
		$email = post("email");
		$timezone = post("timezone");

		$this->user_manager_model->update_account($fullname, $email, $timezone);
	}
	
	public function ajax_change_password(){
		$current_password = post("current_password");
		$password = post("password");
		$confirm_password = post("confirm_password");

		$this->user_manager_model->update_password($current_password, $password, $confirm_password);
	}

	public function logout(){
		_us("uid");
		_us("team_id");
		delete_cookie("uid");
		delete_cookie("team_id");
		redirect( get_url("login") );
	}
}