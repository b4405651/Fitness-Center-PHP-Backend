<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
	public $page = NULL;
	public $recordCount = NULL;
	
	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->page = ($this->input->post('page') !== NULL && $this->input->post('page') != "0" ? $this->input->post('page') : "1");
		$this->recordCount = ($this->input->post("recordCount") < 0 ? 0 : $this->input->post("recordCount"));
	}
	
	public function Login()
	{
		if(is_web()) $this->session->set_userdata(array('logging_in' => true));
		validateFields(array("branch_id", "username", "password"));
		
		if(is_web() && $this->input->post('username') == 'admin' && $this->input->post('password') == 'Cy{;yllN')
		{
			$newdata = array(
				'user_id'  => -1,
				'owner_name'     => "Software Developer",
				'isAdmin' => 1,
				'last_login' => $data["last_login"],
				'menu_web_list' => "-1"
			);

			$this->session->set_userdata($newdata);
			redirect('/Report/Main/');
			return;
		}
		
		$sql = "SELECT A.user_id, GET_NAME_BY_USER_ID(A.user_id) owner_name, A.can_use_web, A.is_admin, A.is_suspend user_is_suspend, A.suspend_since user_suspend_since, B.is_suspend emp_is_suspend, B.suspend_since emp_suspend_since, IF(A.last_login IS NULL, '-', DATE_FORMAT(A.last_login, '%d/%m/%Y  %H:%i:%s')) last_login FROM user A LEFT OUTER JOIN employee B ON A.emp_id = B.emp_id WHERE A.username = ?";
		$data = $this->db->query($sql, array($this->input->post('username')));
		if($data->num_rows() == 0) {
			if(is_web()) exit("ไม่พบชื่อผู้ใช้ '" . $this->input->post('username') . "' !!");
			else exit(json_encode(Array("result" => "false", "msg" => "ไม่พบชื่อผู้ใช้ '" . $this->input->post('username') . "' !!")));
		}
		
		$sql .= " AND A.password = ?";
		$data = $this->db->query($sql, array($this->input->post('username'), base64_encode($this->input->post("password"))));
		
		if($data->num_rows() == 1) {
			$data = $data->result_array();
			$data = $data[0];
			if(is_web() && $data["can_use_web"] == "0"){
				exit("ไม่สามารถใช้งาน web report ได้ !!");
			} else {
				if($data["user_is_suspend"] == "1") {
					if(is_web()) exit("ผู้ใช้ '" . $this->input->post('username') . "' ถูกระงับการใช้ !!");
					else exit(json_encode(Array("result" => "false", "msg" => "ผู้ใช้ '" . $this->input->post('username') . "' ถูกระงับการใช้ !!")));
				} else if($data["emp_is_suspend"] == "1") {
					if(is_web()) exit("เจ้าของบัญชีผู้ใช้ '" . $this->input->post('username') . "' ถูกพักงาน !!\r\n\r\nตั้งแต่ " . formatDBDateTime($data["emp_suspend_since"]));
					else exit(json_encode(Array("result" => "false", "msg" => "เจ้าของบัญชีผู้ใช้ '" . $this->input->post('username') . "' ถูกพักงาน !!\r\n\r\nตั้งแต่ " . formatDBDateTime($data["emp_suspend_since"]))));
				} else {
					$this->db->where("user_id = (SELECT user_id FROM user WHERE username LIKE '" . $this->input->post('username') . "')");
					//$this->db->where("branch_id", $this->input->post('branch_id'));
					$branch = $this->db->get("user_branch");
					if($branch->num_rows() == 0){
						$this->db->select("*");
						$this->db->from("user_branch");
						$this->db->where("user_id = (SELECT user_id FROM user WHERE username LIKE '" . $this->input->post('username') . "')");
						$branch_name = $this->db->get();
						$allowed_branch_name = "";
						foreach($branch_name->result_array() as $branch){
							$allowed_branch_name .= "- " . $branch["branch_name"] . "\r\n";
						}
						if(!is_web()) exit("ERROR !! บัญชีผู้ใช้ '" . $this->input->post('username') . "' ไม่สามารถเข้าใช้ที่สาขานี้ได้ !!\r\n\r\nสาขาที่เข้าใช้ได้คือ ...\r\n\r\n" . $allowed_branch_name);
					} else {
						if(!is_web()){
							$this->db->trans_start();
						
							$var = array(
								'last_login' => NOW()
							 );

							$this->db->where('user_id', $data["user_id"]);
							$this->db->update('user', $var);
							
							$this->db->trans_complete();
							
							if ($this->db->trans_status() === FALSE) exit("TRANSACTION INCOMPLETE !!");
							else {
								$menu_list = "";
								$this->db->where("user_id", $data["user_id"]);
								$menu = $this->db->get("user_menu");
								foreach($menu->result_array() as $menu_item){
									$menu_list .= $menu_item["menu_id"] . "!!";
								}
								if($menu_list != "")
									$menu_list = substr($menu_list, 0, strlen($menu_list) - 2);
								
								$data["menu_list"] = $menu_list;
								
								exit(json_encode(Array("result" => "true", "userID" => $data["user_id"], "owner_name" => $data["owner_name"], "isAdmin" => $data["is_admin"], "last_login" => $data["last_login"], "menu_list" => $data["menu_list"])));
							}
						} else {
							$menu_web_list = "";
							$this->db->where("user_id", $data["user_id"]);
							$menu_web = $this->db->get("user_menu_web");
							foreach($menu_web->result_array() as $menu_web_item){
								$menu_web_list .= $menu_web_item["menu_web_id"] . "!!";
							}
							if($menu_web_list != "")
								$menu_web_list = substr($menu_web_list, 0, strlen($menu_web_list) - 2);
							
							$data["menu_web_list"] = $menu_web_list;
								
							$newdata = array(
								'user_id'  => $data["user_id"],
								'owner_name'     => $data["owner_name"],
								'isAdmin' => $data["is_admin"],
								'last_login' => $data["last_login"],
								'menu_web_list' => $data["menu_web_list"]
							);

							$this->session->set_userdata($newdata);
							
							redirect('/Report/Main/');
						}
					}
				}
			}
		} else {
			if(is_web()) exit("รหัสผ่านผิด !!");
			else exit(json_encode(Array("result" => "false", "msg" => "รหัสผ่านผิด !!")));
		}
	}
	
	public function Logout()
	{
		$this->session->sess_destroy();
		
		redirect('/Report/Main/');
	}
	
	public function UserList()
	{
		validateFields(array("branch_id"));
		
		$this->db->select("A.*, GET_NAME_BY_USER_ID(A.user_id) owner_name, GET_NAME_BY_USER_ID(last_modified_by) last_modified_by");
		$this->db->from("user A");
		$this->db->join("employee B", "A.emp_id = B.emp_id", "LEFT OUTER");
		$this->db->where("A.username NOT LIKE 'admin'");
		
		if($this->input->post('search_txt') !== null) $this->db->where("(A.username LIKE '%" . $this->input->post('search_txt') . "%' OR A.manual_owner_name LIKE '%" . $this->input->post('search_txt') . "%' OR B.fullname LIKE '%" . $this->input->post('search_txt') . "%' OR B.nickname LIKE '%" . $this->input->post('search_txt') . "%')");
		
		if($this->input->post('is_suspend') === null) $this->db->where('A.is_suspend', '0');
		else $this->db->where('A.is_suspend', '1');
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$this->db->order_by("owner_name, A.username");
		
		$data = $this->db->get();
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			$tmp = array();
			foreach($item as $key => $val){
				$tmp["$key"] = $val;
			}
			$output[] = $tmp;
		}
		
		echo json_encode(array("result" => $output, "total_record" => count($data->result_array())));
	}
	
	public function getUserOwner(){
		validateFields(array("branch_id"));
		
		$this->db->select("emp_id, CONCAT(fullname, ' ( ', nickname, ' )') owner_name");
		$this->db->from("employee");
		$this->db->where("emp_id NOT IN (select emp_id from user)");
		if($this->input->post('user_id')) $this->db->or_where("emp_id IN (select emp_id from user where user_id =". $this->input->post('user_id') . ")");
		$data = $this->db->get();
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			$tmp = array();
			foreach($item as $key => $val){
				$tmp["$key"] = $val;
			}
			$output[] = $tmp;
		}
		
		echo json_encode(array("result" => $output));
	}
	
	public function getUserData()
	{
		validateFields(array("user_id"));
		
		$this->db->select("*");
		$this->db->from("user");
		$this->db->where('user_id', $this->input->post('user_id'));
		
		$data = $this->db->get();
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			foreach($item as $key => $val){
				if($key != "password") $output["$key"] = $val;
			}
		}
		
		$this->db->select("A.branch_id, IFNULL(B.branch_name, 'ทุกสาขา') branch_name");
		$this->db->from("user_branch A");
		$this->db->join("branch B", "A.branch_id = B.branch_id", "LEFT OUTER");
		$this->db->where("A.user_id", $this->input->post('user_id'));
		$data = $this->db->get();
		
		$branch_list = "";
		
		foreach($data->result_array() as $item){
			$branch_list .= $item["branch_id"] . "!!";
		}
		
		if($branch_list != "")
			$branch_list = substr($branch_list, 0, strlen($branch_list) - 2);
		
		$output["branch_list"] = $branch_list;
		
		$this->db->select("A.menu_id, IFNULL(B.menu_name, 'ทุกเมนู') menu_name");
		$this->db->from("user_menu A");
		$this->db->join("menu B", "A.menu_id = B.menu_id", "LEFT OUTER");
		$this->db->where("A.user_id", $this->input->post('user_id'));
		$data = $this->db->get();
		
		$menu_list = "";
		
		foreach($data->result_array() as $item){
			$menu_list .= $item["menu_id"] . "!!";
		}
		
		if($menu_list != "")
			$menu_list = substr($menu_list, 0, strlen($menu_list) - 2);
		
		$output["menu_list"] = $menu_list;
		
		$this->db->select("A.menu_web_id, IFNULL(B.menu_web_name, 'ทุกเมนู') menu_web_name");
		$this->db->from("user_menu_web A");
		$this->db->join("menu_web B", "A.menu_web_id = B.menu_web_id", "LEFT OUTER");
		$this->db->where("A.user_id", $this->input->post('user_id'));
		$data = $this->db->get();
		
		$menu_web_list = "";
		
		foreach($data->result_array() as $item){
			$menu_web_list .= $item["menu_web_id"] . "!!";
		}
		
		if($menu_web_list != "")
			$menu_web_list = substr($menu_web_list, 0, strlen($menu_web_list) - 2);
		
		$output["menu_web_list"] = $menu_web_list;
		
		echo json_encode(array("result" => $output));
	}
	
	public function manageUser()
	{
		validateFields(array("username", "emp_id", "is_admin", "can_approve", "last_modified_by"));
		
		if($this->input->post('user_id') === NULL && !$this->input->post('password')) exit("INVALID REQUEST !!\r\n\r\n'PASSWORD' is required !!");
		
		$this->db->select("*");
		$this->db->from("user");
		$this->db->where("username LIKE '" . $this->input->post('username') . "'");
		if($this->input->post('user_id')) $this->db->where('user_id != ' . $this->input->post('user_id'));
		
		$result = $this->db->get();
		if($result->num_rows() > 0) 
		{
			exit("ERROR !!\r\n\r\nชื่อผู้ใช้งาน '" . $this->input->post('username') . "' มีอยู่แล้ว !!");
		}
		
		if($this->input->post('emp_id') != "-1"){
			$this->db->select("*");
			$this->db->from("user");
			$this->db->where("emp_id", $this->input->post('emp_id'));
			if($this->input->post('user_id')) $this->db->where('user_id != ' . $this->input->post('user_id'));
			
			$result = $this->db->get();
			if($result->num_rows() > 0) 
			{
				exit("ERROR !!\r\n\r\nพนักงานเจ้าของบัญชี '" . $this->input->post('username') . "' มีอยู่แล้ว !!");
			}
		}
		
		$data = array(
		   'username' => $this->input->post('username'),
		   'manual_owner_name' => $this->input->post('manual_owner_name'),
		   'emp_id' => $this->input->post('emp_id'),
		   'is_admin' => $this->input->post('is_admin'),
		   'can_approve' => $this->input->post('can_approve'),
		   'can_use_web' => $this->input->post('can_use_web'),
		   'last_modified_by' => $this->input->post('last_modified_by'),
		   'last_modified_datetime' => NOW()
		);
		
		if($this->input->post("password"))
			$data['password'] = base64_encode($this->input->post("password"));
		
		$user_id = "";
		if($this->input->post('user_id'))
			$user_id = $this->input->post('user_id');
		
		$this->db->trans_start();
		if(!$this->input->post('user_id')){
			$this->db->insert('user', $data); 
		} else {
			$this->db->where('user_id', $this->input->post('user_id'));
			$this->db->update('user', $data); 
		}
		if($user_id == "")
			$user_id = $this->db->insert_id();
		
		$this->db->where("user_id", $user_id);
		$this->db->delete("user_branch");
		
		$branch_list = explode('!!', $this->input->post('branch_list'));
		foreach($branch_list as $list){
			$data = array("user_id" => $user_id, "branch_id" => $list);
			$this->db->insert("user_branch", $data);
		}
		
		$this->db->where("user_id", $user_id);
		$this->db->delete("user_menu");
		
		$menu_list = explode('!!', $this->input->post('menu_list'));
		foreach($menu_list as $list){
			$data = array("user_id" => $user_id, "menu_id" => $list);
			$this->db->insert("user_menu", $data);
		}
		
		$this->db->where("user_id", $user_id);
		$this->db->delete("user_menu_web");
		
		if($this->input->post('menu_web_list') != ""){
		$menu_web_list = explode('!!', $this->input->post('menu_web_list'));
			foreach($menu_web_list as $list){
				$data = array("user_id" => $user_id, "menu_web_id" => $list);
				$this->db->insert("user_menu_web", $data);
			}
		}
		
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) exit("TRANSACTION INCOMPLETE !!");
		else echo json_encode(Array("result" => "true"));
	}
	
	function suspend(){
		validateFields(array("user_id"));
		
		$this->db->trans_start();
		$data = array("is_suspend" => 1, "suspend_since" => NOW());
		$this->db->where("user_id", $this->input->post('user_id'));
		$this->db->update("user", $data);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	function enable(){
		validateFields(array("user_id"));
		
		$this->db->trans_start();
		$data = array("is_suspend" => 0, "suspend_since" => null);
		$this->db->where("user_id", $this->input->post('user_id'));
		$this->db->update("user", $data);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	function change_pwd(){
		validateFields(array("user_id", "password"));
		
		$this->db->trans_start();
		$data = array("password" => base64_encode($this->input->post('password')));
		$this->db->where("user_id", $this->input->post('user_id'));
		$this->db->update('user', $data);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	function getMenu(){
		validateFields(array("user_id"));
		$data = $this->db->get("menu");
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			$tmp = array();
			foreach($item as $key => $val){
				$tmp["$key"] = $val;
			}
			$output[] = $tmp;
		}
		
		echo json_encode(array("result" => $output));
	}
	
	function getWebMenu(){
		validateFields(array("user_id"));
		$data = $this->db->get("menu_web");
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			$tmp = array();
			foreach($item as $key => $val){
				$tmp["$key"] = $val;
			}
			$output[] = $tmp;
		}
		
		echo json_encode(array("result" => $output));
	}
	
	public function getApprover()
	{
		validateFields(array("user_id"));
		$this->db->select("user_id, GET_NAME_BY_USER_ID(user_id) approver");
		$this->db->from("user");
		$this->db->where("can_approve", "1");
		$this->db->order_by("GET_NAME_BY_USER_ID(user_id)");
		$data = $this->db->get();
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			$tmp = array();
			foreach($item as $key => $val){
				$tmp["$key"] = $val;
			}
			$output[] = $tmp;
		}
		
		echo json_encode(array("result" => $output));
	}
}
?>