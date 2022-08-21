<?php
class payment_manager_model extends MY_Model {
	
	public $tb_payment_history = "sp_payment_history";
	public $tb_users = "sp_users";
	public $tb_package_manager = "sp_package_manager";

	public function __construct(){
		parent::__construct();
		//
		$module_path = get_module_directory(__DIR__);
		$this->module_name = get_module_config( $module_path, 'name' );
		$this->module_icon = get_module_config( $module_path, 'icon' );
		$this->module_color = get_module_config( $module_path, 'color' );
		$this->fields = ['c.fullname', 'c.email', 'b.name', 'a.transaction_id', 'a.type'];
		//
	}

	public function block_permissions($path = ""){
		$dir = get_directory_block(__DIR__, get_class($this));
		return [
			'position' => 1000,
			'name' => $this->module_name,
			'color' => $this->module_color,
			'icon' => $this->module_icon, 
			'id' => str_replace("_model", "", get_class($this)),
			'html' => view( $dir.'pages/block_permissions', ['path' => $path], true, $this ),
		];
	}

	public function get_report(){

		//Recrent registers
		$recently_payments = false; //$this->model->fetch("*", $this->tb_payment_history, "", "id", "desc", 0, 10);
		$this->db->select("a.*, c.fullname, c.email, b.name");
		$this->db->from($this->tb_payment_history." as a");
		$this->db->join($this->tb_package_manager." as b", "a.package = b.id");
		$this->db->join($this->tb_users." as c", "a.uid = c.id");
		$this->db->order_by("a.id", "DESC");
		$this->db->limit(10, 0);
		$query = $this->db->get();
		if($query->result()){
			$recently_payments = $query->result();
		}

		//Count by date
		$today = $this->model->get("count(*) as count", $this->tb_payment_history, " FROM_UNIXTIME(created) > NOW() - INTERVAL 1 DAY ")->count;
		$week = $this->model->get("count(*) as count", $this->tb_payment_history, " FROM_UNIXTIME(created) > NOW() - INTERVAL 7 DAY ")->count;
		$month = $this->model->get("count(*) as count", $this->tb_payment_history, " FROM_UNIXTIME(created) > NOW() - INTERVAL 30 DAY ")->count;
		$year = $this->model->get("count(*) as count", $this->tb_payment_history, " FROM_UNIXTIME(created) > NOW() - INTERVAL 365 DAY ")->count;

		$count_by_day = [
			"today" => $today,
			"week" => $week,
			"month" => $month,
			"year" => $year
		];

		//Total by date
		$today = $this->model->get("SUM(amount) as total", $this->tb_payment_history, " FROM_UNIXTIME(created) > NOW() - INTERVAL 1 DAY ")->total;
		$week = $this->model->get("SUM(amount) as total", $this->tb_payment_history, " FROM_UNIXTIME(created) > NOW() - INTERVAL 7 DAY ")->total;
		$month = $this->model->get("SUM(amount) as total", $this->tb_payment_history, " FROM_UNIXTIME(created) > NOW() - INTERVAL 30 DAY ")->total;
		$year = $this->model->get("SUM(amount) as total", $this->tb_payment_history, " FROM_UNIXTIME(created) > NOW() - INTERVAL 365 DAY ")->total;

		$total_by_day = [
			"today" => (float)$today,
			"week" => (float)$week,
			"month" => (float)$month,
			"year" => (float)$year
		];

		//Chart
		$value_string = "";
		$date_string = "";

		$date_list = array();
		$date = strtotime(date('Y-m-d', strtotime(NOW)));
		for ($i=29; $i >= 0; $i--) { 
			$left_date = $date - 86400 * $i;
			$date_list[date('Y-m-d', $left_date)] = 0;
		}

		$query = $this->db->query("SELECT COUNT(*) as count, DATE(FROM_UNIXTIME(created)) as created FROM ".$this->tb_payment_history." WHERE FROM_UNIXTIME(created) > NOW() - INTERVAL 30 DAY GROUP BY DATE(FROM_UNIXTIME(created));");
		if($query->result()){
			
			foreach ($query->result() as $key => $value) {
				if(isset($date_list[$value->created])){
					$date_list[$value->created] = $value->count;
				}
			}
		}

		foreach ($date_list as $date => $value) {
			$value_string .= "{$value},";
			$date_string .= "'{$date}',";
		}

		$value_string = "[".substr($value_string, 0, -1)."]";
		$date_string  = "[".substr($date_string, 0, -1)."]";

		$chart = [
			"value" => $value_string,
			"date" => $date_string
		];

		return (object)[
			"count_by_day" => (object)$count_by_day,
			"total_by_day" => (object)$total_by_day,
			"recently_payments" => $recently_payments,
			"chart" => (object)$chart,
		];
	}

	public function get_data()
	{
		$page   = (int)post("p");
		$limit  = 59;
		$result = $this->get_list($limit, $page);
		$total  = $this->get_list(-1, -1);

		$query = [];
		$query_string = "";
		if(post("c")) $query["c"] = post("c");
		if(post("t")) $query["t"] = post("t");
		if(post("k")) $query["k"] = post("k");

		if( ! empty($query) )
		{
			$query_string = "?".http_build_query($query);
		}

		$configs = [
			"base_url"   => get_module_url($query_string), 
			"total_rows" => $total, 
			"per_page"   => $limit
		];

		$this->pagination->initialize($configs);

		$data = [
			"result"     => $result,
			"total"      => $total,
			"page"       => $page,
			"limit"      => $limit,
			"pagination" => $this->pagination->create_links()
		];

		return $data;
	}

	public function get_list($limit=-1, $page=-1)
	{
		$c = (int)post('c');
		$t = post('t'); 
		$k = post('k');
		
		if($limit == -1)
		{
			$this->db->select('count(a.id) as sum');
			$this->db->join($this->tb_package_manager." as b", "a.package = b.id");
			$this->db->join($this->tb_users." as c", "a.uid = c.id");
		}
		else
		{
			$this->db->select('a.*, c.email, c.fullname, b.name');
			$this->db->join($this->tb_package_manager." as b", "a.package = b.id");
			$this->db->join($this->tb_users." as c", "a.uid = c.id");
			$this->db->limit($limit, $page);
		}

		$this->db->from($this->tb_payment_history." as a");

		if($k)
		{
			$i = 1;
			foreach ($this->fields as $field)
			{
				if($i == 1)
				{
					$this->db->like($field, $k);
				}
				else
				{
					$this->db->or_like($field, $k);
				}
				$i++;
			}
		}

		if($c){
			$i = 1;
			$s = ( $t && ( $t == "asc" || $t == "desc") )? $t : "desc";
			foreach ($this->fields as $field)
			{
				if($i == $c)
				{
					$this->db->order_by($field, $s);
				}
				$i++;
			}
		}
		else
		{
			$this->db->order_by('a.created', 'desc');
		}

		$query = $this->db->get();

		if($query->result())
		{
			if($limit == -1)
			{
				return $query->row()->sum;
			}
			else
			{
				return  $query->result();
			}
		}

		return false;
	}
}
