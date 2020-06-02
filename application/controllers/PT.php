<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PT extends CI_Controller {
	public function PTList()
	{
		validateFields(array("branch_id"));
		
		$this->db->where("is_trainer", 1);
		if($this->input->post("branch_id") != "-1")
			$this->db->where("branch_id", $this->input->post("branch_id"));
		if($this->input->post("only_active"))
			$this->db->where("is_suspend", 0);
		$this->db->order_by("fullname, nickname");
		$data = $this->db->get("employee");
		
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