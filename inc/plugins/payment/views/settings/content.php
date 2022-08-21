<?php
$CI = &get_instance();
if($CI->load->module_name == 'settings'){
	$module_paths = get_module_paths();
	$settings_payment_data = array();
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
	        	if (preg_match("/block_payment_settings/i", $model_content))
				{	
					$path = '../../'.DIR_ROOT.$dir.$folder_name.'/models/'.strtolower($file_name);
					$key = md5($path);
					
					$CI->load->model($path, $key);
					$settings_payment_data[$key] = $CI->$key->block_payment_settings($key);
				}

	        }

	    }
	}

	if( !empty($settings_payment_data)){
		uasort($settings_payment_data, function($a, $b) {
            return $a['position'] < $b['position'];
        });
	}

}
?>

<form>
	<div class="m-b-40">
  		<h5 class="fs-16 fw-4 text-info m-b-20"><i class="fas fa-caret-right"></i> <?php _e('General')?></h5>
	  	<div class="form-group">
			<label for="status"><?php _e('Environment')?></label>
			<div>
				<label class="i-radio i-radio--tick i-radio--brand m-r-10">
					<input type="radio" name="payment_environment" <?php _e( get_option('payment_environment', 0)  == 1?"checked":"" )?> value="1"> <?php _e('Live')?>
					<span></span>
				</label>
				<label class="i-radio i-radio--tick i-radio--brand m-r-10">
					<input type="radio" name="payment_environment" <?php _e( get_option('payment_environment', 0)  == 0?"checked":"" )?> value="0"> <?php _e('Sandbox')?>
					<span></span>
				</label>
			</div>
		</div>
	  	<div class="form-group">
	        <label for="file_manager_google_api_key"><?php _e('Currency')?></label>
	        <select class="form-control" name="payment_currency">
	        <?php foreach (get_list_currency() as $currency => $name) {?>
	        	<option value="<?php _e( $currency )?>" <?php _e( get_option('payment_currency', 'USD') == $currency?"selected":"" )?> ><?php _e( $currency )?></option>
	        <?php }?>
	        </select>
	  	</div>
	  	<div class="form-group">
	        <label for="payment_symbol"><?php _e('Symbol')?></label>
	        <input type="text" class="form-control" id="payment_symbol" name="payment_symbol" value="<?php _e( get_option('payment_symbol', '$') )?>">
	  	</div>
	</div>
	<?php if(!empty($settings_payment_data)){?>

		<?php foreach ($settings_payment_data as $key => $value): ?>
			
			<?php _e( $value['content'], false )?>

		<?php endforeach ?>

	<?php }?>

  	<button type="submit" class="btn btn-info"><?php _e('Submit')?></button>
</form>