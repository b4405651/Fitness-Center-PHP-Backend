<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee extends CI_Controller {
	public $page = NULL;
	public $recordCount = NULL;
	
	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->page = ($this->input->post('page') !== NULL && $this->input->post('page') != "0" ? $this->input->post('page') : "1");
		$this->recordCount = ($this->input->post("recordCount") < 0 ? 0 : $this->input->post("recordCount"));
	}
	
	public function EmployeeList()
	{
		validateFields(array("user_id"));
		
		$this->db->select("A.*, B.branch_name");
		$this->db->from("employee A");
		$this->db->join("branch B", "A.branch_id = B.branch_id", "INNER");
		
		if($this->input->post('branch_id') !== NULL)
			$this->db->where("A.branch_id", $this->input->post("branch_id"));
		
		if($this->input->post('is_suspend'))
			$this->db->where("A.is_suspend", "1");
		else
			$this->db->where("A.is_suspend", "0");
			
		if($this->input->post('search_txt')){
			$search_txt = $this->input->post('search_txt');
			$this->db->where("(A.emp_code LIKE '%$search_txt%' OR A.fullname LIKE '%$search_txt%' OR A.nickname LIKE '%$search_txt%')");
		}
		
		if($this->input->post('is_trainer'))
			$this->db->where("A.is_trainer", $this->input->post('is_trainer'));
		
		if($this->input->post('can_get_commission'))
			$this->db->where("A.can_get_commission", $this->input->post('can_get_commission'));
		
		//if($this->input->post('member_ext_id'))
		//	$this->db->where("A.emp_id NOT IN (select seller_emp_id from member_ext where member_ext_id = " . $this->input->post('member_ext_id') . ")");
		
		$this->db->order_by("fullname, nickname");
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
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
	
	public function getEmployeeData()
	{
		validateFields(array("emp_id"));
		
		$this->db->select("*");
		$this->db->from("employee");
		$this->db->where('emp_id = ' . $this->input->post('emp_id'));
		
		$data = $this->db->get();
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			foreach($item as $key => $val){
				$output["$key"] = $val;
			}
		}
		
		echo json_encode(array("result" => $output));
	}
	
	public function getEmployeeByCode()
	{
		validateFields(array("emp_code"));
		
		$this->db->select("emp_id, fullname, nickname");
		$this->db->from("employee");
		$this->db->where("emp_code LIKE '" . $this->input->post('emp_code') . "'");
		
		$data = $this->db->get();
		
		if($data->num_rows() == 0){
			exit(json_encode(array("result" => "NOT FOUND", "msg"=>"ไม่พบพนักงานรหัส '" . $this->input->post('emp_code') . "'")));
		}
		
		$data = $data->row(0);
		
		echo json_encode(array("result" => "YES", "emp_id" => $data->emp_id, "emp_name" => $data->fullname . " (" . $data->nickname . ")"));
	}
	
	public function manageEmployee()
	{
		validateFields(array("emp_code", "fullname", "nickname", "is_trainer", "can_get_commission", "branch_id"));
		
		$this->db->where("emp_code", $this->input->post('emp_code'));
		if($this->input->post('emp_id'))
			$this->db->where("emp_id != " . $this->input->post('emp_id'));
		$data = $this->db->get("employee");
		if($data->num_rows() > 0){
			echo "ERROR !!\r\n\r\nรหัสพนังาน '" . $this->input->post('emp_code') . "' มีอยู่ในระบบแล้ว !!";
			return;
		}
		
		$data = array(
			"emp_code" => $this->input->post('emp_code'),
			"fullname" => $this->input->post('fullname'),
			"nickname" => $this->input->post('nickname'),
			"is_trainer" => $this->input->post('is_trainer'),
			"can_get_commission" => $this->input->post('can_get_commission'),
			"branch_id" => $this->input->post('branch_id')
		);
		
		$this->db->trans_start();
		if(!$this->input->post('emp_id'))
			$this->db->insert('employee', $data);
		else{
			$this->db->where('emp_id', $this->input->post('emp_id'));
			$this->db->update('employee', $data);
		}
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	function suspend(){
		validateFields(array("emp_id"));
		
		$this->db->trans_start();
		$data = array("is_suspend" => 1, "suspend_since" => NOW());
		$this->db->where("emp_id", $this->input->post('emp_id'));
		$this->db->update("employee", $data);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	function enable(){
		validateFields(array("emp_id"));
		
		$this->db->trans_start();
		$data = array("is_suspend" => 0, "suspend_since" => null);
		$this->db->where("emp_id", $this->input->post('emp_id'));
		$this->db->update("employee", $data);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	function getTrainer(){
		validateFields(array("branch_id"));
		
		$branch_id = explode(",", $this->input->post("branch_id"));
		
		$this->db->select("emp_id trainer_emp_id, CONCAT(fullname, ' ( ', nickname, ' )') trainer_name");
		$this->db->from("employee");
		$this->db->where("is_trainer", "1");
		if($this->input->post("branch_id") !== "-1")
			$this->db->where_in("branch_id", $branch_id);
		
		$this->db->order_by("fullname, nickname");
		
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
	
	function getEmpCardFileName(){
		validateFields(array("user_id"));
		
		$this->db->select("IFNULL(card_front, '') card_front");
		$data = $this->db->get("employee_card");
		$data = $data->row(0);
		exit(json_encode(array("result" => array("filename" => $data->card_front))));
	}
	
	function manageEmpCard(){
		validateFields(array("filename"));
		
		$data = $this->db->get("employee_card");
		
		$this->db->trans_start();
		if($data->num_rows() == 0){
			$this->db->query("INSERT INTO employee_card (card_front) VALUES ('" . $this->input->post('filename') . "')");
		} else {
			$this->db->query("UPDATE employee_card SET card_front = '" . $this->input->post('filename') . "'");
		}
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
}
?>