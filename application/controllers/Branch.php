<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Branch extends CI_Controller {
	public $page = NULL;
	public $recordCount = NULL;
	
	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->page = ($this->input->post('page') !== NULL && $this->input->post('page') != "0" ? $this->input->post('page') : "1");
		$this->recordCount = ($this->input->post("recordCount") < 0 ? 0 : $this->input->post("recordCount"));
	}
	
	public function BranchList()
	{
		validateFields(array("user_id"));
		
		if($this->input->post('is_suspend') === NULL)
			$this->db->where("is_suspend", "0");
		else
			$this->db->where("is_suspend", $this->input->post('is_suspend'));
		
		if($this->input->post('search_txt'))
			$this->db->where("branch_name LIKE '%" . $this->input->post('search_txt') . "%' OR prefix LIKE '%" . $this->input->post('search_txt') . "%'");
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$data = $this->db->get("branch");
		
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
	
	public function getBranchData()
	{
		validateFields(array("branch_id"));
		
		if(!$this->input->post('branch_id')) exit('INVALID REQUEST !!');
		$this->db->where("branch_id", $this->input->post('branch_id'));
		$data = $this->db->get("branch");
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			foreach($item as $key => $val){
				$output["$key"] = $val;
			}
		}
		
		echo json_encode(array("result" => $output));
	}
	
	public function manageBranch()
	{
		validateFields(array("branch_name", "prefix", "company_name", "address", "tax_id"));
		
		$data = array(
			"branch_name" => $this->input->post('branch_name'),
			"prefix" => $this->input->post('prefix'),
			"company_name" => $this->input->post('company_name'),
			"address" => $this->input->post('address'),
			"tax_id" => $this->input->post('tax_id')
		);
		
		$this->db->trans_start();
		if(!$this->input->post('branch_id'))
			$this->db->insert('branch', $data);
		else {
			$this->db->where('branch_id', $this->input->post('branch_id'));
			$this->db->update('branch', $data);
		}
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE) echo json_encode("TRANSACTION INCOMPLETE !!");
		else echo json_encode(Array("result" => "true"));
	}
	
	function Suspend(){
		validateFields(array("branch_id"));
		
		$this->db->trans_start();
		$data = array("is_suspend" => 1, "suspend_since" => NOW());
		$this->db->where("branch_id", $this->input->post('branch_id'));
		$this->db->update("branch", $data);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	function Enable(){
		validateFields(array("branch_id"));
		
		$this->db->trans_start();
		$data = array("is_suspend" => 0, "suspend_since" => null);
		$this->db->where("branch_id", $this->input->post('branch_id'));
		$this->db->update("branch", $data);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
}
?>