<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class MemberType extends CI_Controller {
	public $page = NULL;
	public $recordCount = NULL;
	
	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->page = ($this->input->post('page') !== NULL && $this->input->post('page') != "0" ? $this->input->post('page') : "1");
		$this->recordCount = ($this->input->post("recordCount") < 0 ? 0 : $this->input->post("recordCount"));
	}
	
	public function MemberTypeList()
	{
		validateFields(array("user_id"));
		
		$this->db->where("is_suspend", "0");
		$data = $this->db->get("member_type");
		
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
	
	public function getMemberType(){
		validateFields(array("is_suspend"));
		
		if($this->input->post('search_txt'))
			$this->db->where("A.member_type_name LIKE '%" . $this->input->post('search_txt') . "%'");
		
		$this->db->where("A.is_suspend", $this->input->post('is_suspend'));
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$this->db->select("A.*, GET_NAME_BY_USER_ID(A.suspend_by_user_id) suspender");
		$this->db->from("member_type A");
		$this->db->join("user B", "A.suspend_by_user_id = B.user_id", "LEFT OUTER");
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
	
	public function getMemberTypeData(){
		validateFields(array("member_type_id"));
		
		$this->db->where("member_type_id", $this->input->post('member_type_id'));
		$data = $this->db->get("member_type");
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			foreach($item as $key => $val){
				$output["$key"] = $val;
			}
		}
		
		echo json_encode(array("result" => $output));
	}
	
	public function manageMemberType(){
		validateFields(array("member_type_name", "month_amount", "price"));
		
		$data = array(
			"member_type_name" => $this->input->post("member_type_name"), 
			"month_amount" => $this->input->post("month_amount"),
			"price" => $this->input->post("price")
		);
		
		$member_type_id = "";
		if($this->input->post('member_type_id'))
			$member_type_id = $this->input->post('member_type_id');
		
		$this->db->trans_start();
		if(!$this->input->post('member_type_id')){
			$this->db->insert('member_type', $data); 
		} else {
			$this->db->where('member_type_id', $this->input->post('member_type_id'));
			$this->db->update('member_type', $data); 
		}
		
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) exit("TRANSACTION INCOMPLETE !!");
		else echo json_encode(Array("result" => "true"));
	}
	
	function memberTypeSuspend(){
		validateFields(array("member_type_id", "suspend_by_user_id"));
		
		$this->db->trans_start();
		$data = array("is_suspend" => 1, "suspend_since" => NOW(), "suspend_by_user_id" => $this->input->post('suspend_by_user_id'));
		$this->db->where("member_type_id", $this->input->post('member_type_id'));
		$this->db->update("member_type", $data);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	function memberTypeEnable(){
		validateFields(array("member_type_id"));
		
		$this->db->trans_start();
		$data = array("is_suspend" => 0, "suspend_since" => null, "suspend_by_user_id" => null);
		$this->db->where("member_type_id", $this->input->post('member_type_id'));
		$this->db->update("member_type", $data);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
}
?>