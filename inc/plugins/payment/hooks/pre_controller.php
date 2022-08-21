<?php
$CI = &get_instance();
if($CI->load->module_name == 'payment'){
	$module_paths = get_module_paths();
	$payment_view_data = array();
	if(!empty($module_paths))
	{
	    foreach ($module_paths as $module_path) 
	    {

	        $models = $module_path.'/models/*.php';
	        $models = glob($models);

	        if(empty($models)) continue;

	        foreach ($models as $model) 
	        {
	        	//Get Directory
	        	$dir = str_replace(DIR_ROOT, "", $model);
	        	$dir = explode("/", $dir);
	        	$dir = $dir[0]."/";

	        	//Get file name
	        	$file_tmp = str_replace(".php", "", $model);
	        	$file_tmp = explode("/", $file_tmp);
	        	$file_name = end($file_tmp);

	        	//Get folder name
	        	$folder_name = str_replace("_model", "", $file_name);

	        	$model_content = file_get_contents($model);
	        	if (preg_match("/block_payment_view/i", $model_content))
				{	
					$path = '../../'.DIR_ROOT.$dir.$folder_name.'/models/'.strtolower($file_name);
					$key = md5($path);
					
					$CI->load->model($path, $key);
					$payment_view_data[$key] = $CI->$key->block_payment_view($key);
				}

	        }

	    }
	}

	if( !empty($payment_view_data)){
		uasort($payment_view_data, function($a, $b) {
            return $a['position'] < $b['position'];
        });

        if(!empty($payment_view_data)){
            $CI->payment_views = $payment_view_data;
        }else{
            $CI->payment_views = false;
        }
	}
}