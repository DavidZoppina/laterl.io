<?php
class paypal_model extends MY_Model {
	public function __construct(){
		parent::__construct();

		//
		$module_path = get_module_directory(__DIR__);
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
}
