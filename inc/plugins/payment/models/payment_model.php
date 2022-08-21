<?php
class payment_model extends MY_Model {
	public $tb_users = "sp_users";
	public $tb_team = "sp_team";
	public $tb_team_memeber = "sp_team_member";
	public $tb_package_manager = "sp_package_manager";
	public $tb_payment_history = "sp_payment_history";
	public $tb_payment_subscriptions = "sp_payment_subscriptions";

	public function __construct(){
		parent::__construct();

		//
		$module_path = get_module_directory(__DIR__);
		$this->module_name = get_module_config( $module_path, 'name' );
		$this->module_icon = get_module_config( $module_path, 'icon' );
		$this->module_color = get_module_config( $module_path, 'color' );
		//
	}

	/*
	* SETTINGS
	*/
	public function block_settings($path = ""){
		$dir = get_directory_block_setttings( __DIR__, get_class($this) );
		
		return array(
			"position" => 9999,
			"menu" => view( $dir.'settings/menu', ['path' => $path], true, $this ),
			"content" => view( $dir.'settings/content', [], true, $this )
		);
	}
	/*
	* END SETTINGS
	*/

	public function save($data, $redirect = true){
		$uid = isset($data['uid'])?$data['uid']:_u("id");
		$save = array(
			'ids' => ids(),
			'uid' => $uid,
			'package' => $data['package'],
			'type' => $data['type'],
			'transaction_id' => $data['transaction_id'],
			'amount' => $data['amount'],
			'plan' => $data['plan'],
			'created' => time()
		);
		$this->db->insert($this->tb_payment_history, $save);
		$this->update_package($data['package'], $data['plan'], $uid);

		$is_subscription = _gd("is_subscription", 0, $uid);
		_ud("is_subscription", 0, $uid);

		$payment_getway = _gd("payment_getway", "", $uid);
		_ud("payment_getway", $data['type'], $uid);

		$subscription_id = _gd("subscription_id", "", $uid);
		_ud("subscription_id", "", $uid);

		if(get_option("email_payment_status")){
			$result = send_mail("payment", $uid);
		}

        if($redirect) {
            
		redirect( get_url("payment/success") );
        }
	}

	public function save_subscription($data){
		$uid = _u("id");
		$save = array(
			'ids' => ids(),
			'uid' => $uid,
			'package' => $data['package'],
			'type' => $data['type'],
			'subscription_id' => $data['subscription_id'],
			'customer_id' => $data['customer_id'],
			'plan' => $data['plan'],
			'created' => time()
		);
		$this->db->insert($this->tb_payment_subscriptions, $save);

		$is_subscription = _gd("is_subscription", 0);
		_ud("is_subscription", 1);

		$payment_getway = _gd("payment_getway", "");
		_ud("payment_getway", $data['type']);

		$subscription_id = _gd("subscription_id", "");
		_ud("subscription_id", $data['subscription_id']);

		if(get_option("email_payment_status")){
			$result = send_mail("payment", $uid);
		}

		redirect( get_url("payment/success") );
	}

	public function update_subscription($data){
		$subscription = $this->model->get("*", $this->tb_payment_subscriptions, "subscription_id = '".$data["subscription_id"]."'");
		if($subscription){
			$save = array(
				'ids' => ids(),
				'uid' => $subscription->uid,
				'package' => $subscription->package,
				'type' => $subscription->type,
				'transaction_id' => $data['transaction_id'],
				'amount' => $data['amount'],
				'plan' => $subscription->plan,
				'created' => time()
			);
			$this->db->insert($this->tb_payment_history, $save);
			$this->update_package($subscription->package, $subscription->plan, $subscription->uid);
		}
	}

	public function cancel_subscription($subscription_id){
		$subscription = $this->model->get("*", $this->tb_payment_subscriptions, "subscription_id = '{$subscription_id}'");

		if($subscription){
			$is_subscription = _gd("is_subscription", 0);
			_ud("is_subscription", 0, $subscription->uid);

			$payment_getway = _gd("payment_getway", "");
			_ud("payment_getway", "", $subscription->uid);

			$subscription_id = _gd("subscription_id", "");
			_ud("subscription_id", "", $subscription->uid);
		}

		$this->db->delete($this->tb_payment_subscriptions, [ "subscription_id" => $subscription_id ]);
	}

	public function stop_subscription(){
		$is_subscription = _gd("is_subscription", 0);
		$payment_getway = _gd("payment_getway", "");
		$subscription_id = _gd("subscription_id", "");
		if($is_subscription){
			$this->load->model($payment_getway.'/'.$payment_getway.'_model', $payment_getway);
			$result = $this->$payment_getway->cancel_subscription($subscription_id);
			if($result){
				ms([
					"status" => "success",
					"message" => __("Success")
				]);
			}else{
				ms([
					"status" => "error",
					"message" => __("Have a problem on your request. Please try again later")
				]);
			}
		}
	}

	public function update_package($package_id, $plan, $uid = ""){
		$user = $this->model->get("*", $this->tb_users, "id = '".$uid."'");
		if( $user )
		{
			$package_old = $this->model->get("*", $this->tb_package_manager, "id = '".$user->package."'");
			$package_new = $this->model->get("*", $this->tb_package_manager, "id = '".$package_id."'");

			$new_days  = 30;
			if($plan == 2)
			{
				$new_days  = 365;
			}

			if( $package_old )
			{
				if( time() < strtotime( $user->expiration_date ) )
				{
					$date_now = strtotime( date("Y-m-d", time() ) );
					$date_expiration = strtotime( $user->expiration_date );
					$diff = abs( $date_expiration - $date_now );
					$left_days = floor( $diff / 86400 );

					if($plan == 2)
					{
						$day_added = round( ( $package_old->price_annually / $package_new->price_annually ) * $left_days );
					}
					else
					{
						$day_added = round( ( $package_old->price_monthly / $package_new->price_monthly ) * $left_days );
					}

					$total_day = $new_days + $day_added;
					$expiration_date = date( 'Y-m-d', strtotime( date("Y-m-d", time() ) . " +" . $total_day . " days") );
				}
				else
				{
					$expiration_date = date( 'Y-m-d', strtotime( date("Y-m-d", time() ) . " +" . $new_days . " days") );
				}
			}
			else
			{
				$expiration_date = date( 'Y-m-d', strtotime( date("Y-m-d", time() ) . " +" . $new_days . " days") );
			}

			$data = array(
				"package" => $package_new->id,
				"expiration_date" => $expiration_date
			);

			$this->db->update( $this->tb_users, $data, ["id" => $uid] );
			$team = $this->model->get("*", $this->tb_team, "owner = '{$uid}'");
			
			if($team)
			{
				$this->db->update( $this->tb_team, [ "permissions" => $package_new->permissions, "pid" => $package_id ], [ "id" => $team->id ] );
				$this->db->update( $this->tb_team_memeber, [ "permissions" => $package_new->permissions ], [ "team_id" => $team->id ] );
			}
		}
	}

	public function get_supscription($type){
		$uid = _u("id");
		return $this->model->get("*", $this->tb_payment_subscriptions, "type = '{$type}' AND uid = '{$uid}'");
	}

	public function delete_supscription($subscription_id){
		$this->model->delete($this->tb_payment_subscriptions, "subscription_id = '{$subscription_id}'");

		$is_subscription = _gd("is_subscription", 0);
		_ud("is_subscription", 0);

		$payment_getway = _gd("payment_getway", "");
		_ud("payment_getway", "");

		$subscription_id = _gd("subscription_id", "");
		_ud("subscription_id", "");
	}

	public function get_package($ids = "", $plan = 1){
		$package = $this->model->get("*", $this->tb_package_manager, "ids = '".$ids."' AND status = 1");

		if(empty($package)) redirect( get_url() );

		$price_monthly = $package->price_monthly;
        $price_annually = $package->price_annually;
		if(_s("coupon")){
            $coupon = (object)_s("coupon");
            $coupon_code = $coupon->code;
            if(in_array((int)$package->id, $coupon->package)){
                if($coupon->type == 1){
                    $price_monthly = number_format($price_monthly - $coupon->price, 2);
                    $price_annually = number_format($price_annually - $coupon->price, 2);
                }else{
                    $price_monthly = number_format($price_monthly*(100 - $coupon->price)/100, 1);
                    $price_annually = number_format($price_annually*(100 - $coupon->price)/100, 2);
                }
            }
        }

        $amount = $price_monthly;
		if($plan == 2){
			$amount = $price_annually*12;
		}else{
			$plan = 1;
		}

		$iszdc = isZeroDecimalCurrency( get_option('payment_currency','USD') );

        return (object)[
        	"amount" => $iszdc? round($amount):$amount,
        	"plan" => $plan,
        	"id" => $package->id,
        	"ids" => $package->ids,
        	"name"=> $package->name,
        	"description" => $package->description,
        	"trial_day" => $package->trial_day,
        	"created" => $package->created,
        ];
	}
}
