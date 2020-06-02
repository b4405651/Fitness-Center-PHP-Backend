<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class TrainerJob extends CI_Controller {
	public $page = NULL;
	public $recordCount = NULL;
	
	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->page = ($this->input->post('page') !== NULL && $this->input->post('page') != "0" ? $this->input->post('page') : "1");
		$this->recordCount = ($this->input->post("recordCount") < 0 ? 0 : $this->input->post("recordCount"));
	}
	
	public function getTrainerJob()
	{
		validateFields(array("branch_id"));
		
		$this->db->select("A.trainer_job_id, A.job_date, CONCAT(A.start_time, ' - ', A.end_time) during, A.detail, CONCAT(B.fullname, ' ( ', B.nickname, ' )') trainer_name, GET_NAME_BY_USER_ID(A.create_by) create_by, A.create_datetime, GET_NAME_BY_USER_ID(A.last_modified_by) last_modified_by, A.last_modified_datetime, GET_NAME_BY_USER_ID(A.confirm_by) confirm_by, A.confirm_datetime");
		$this->db->from("trainer_job A");
		$this->db->join("employee B", "A.trainer_emp_id = B.emp_id", "INNER");
		
		if($this->input->post('search_txt'))
			$this->db->where("A.detail LIKE '%" . $this->input->post('search_txt') . "%'");
		if($this->input->post('trainer_emp_id'))
			$this->db->where("A.trainer_emp_id", $this->input->post('trainer_emp_id'));
		if($this->input->post('since'))
			$this->db->where("STR_TO_DATE(A.job_date, '%d/%m/%Y') >= STR_TO_DATE('" . $this->input->post('since') . "','%d/%m/%Y')");
		if($this->input->post('until'))
			$this->db->where("STR_TO_DATE(A.job_date, '%d/%m/%Y') <= STR_TO_DATE('" . $this->input->post('until') . "','%d/%m/%Y')");
		
		$data = $this->db->get();
		$recordCount = $data->num_rows();
		
		$this->db->select("A.trainer_job_id, A.job_date, CONCAT(A.start_time, ' - ', A.end_time) during, A.detail, CONCAT(B.fullname, ' ( ', B.nickname, ' )') trainer_name, GET_NAME_BY_USER_ID(A.create_by) create_by, A.create_datetime, GET_NAME_BY_USER_ID(A.last_modified_by) last_modified_by, A.last_modified_datetime, GET_NAME_BY_USER_ID(A.confirm_by) confirm_by, A.confirm_datetime");
		$this->db->from("trainer_job A");
		$this->db->join("employee B", "A.trainer_emp_id = B.emp_id", "INNER");
		
		if($this->input->post('search_txt'))
			$this->db->where("A.detail LIKE '%" . $this->input->post('search_txt') . "%'");
		if($this->input->post('trainer_emp_id'))
			$this->db->where("A.trainer_emp_id", $this->input->post('trainer_emp_id'));
		if($this->input->post('since'))
			$this->db->where("STR_TO_DATE(A.job_date, '%d/%m/%Y') >= STR_TO_DATE('" . $this->input->post('since') . "','%d/%m/%Y')");
		if($this->input->post('until'))
			$this->db->where("STR_TO_DATE(A.job_date, '%d/%m/%Y') <= STR_TO_DATE('" . $this->input->post('until') . "','%d/%m/%Y')");
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$this->db->order_by("A.job_date DESC, A.start_time DESC");
		
		$data = $this->db->get();
		//exit($this->db->last_query());
		$output = Array();
		
		foreach($data->result_array() as $item){
			$tmp = array();
			foreach($item as $key => $val){
				$tmp["$key"] = $val;
			}
			$output[] = $tmp;
		}
		
		echo json_encode(array("result" => $output, "total_record" => $recordCount));
	}
	
	public function getJobData(){
		validateFields(array("trainer_job_id"));
		
		$this->db->where("trainer_job_id", $this->input->post('trainer_job_id'));
		$data = $this->db->get("trainer_job");
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			foreach($item as $key => $val){
				$output["$key"] = $val;
			}
		}
		
		echo json_encode(array("result" => $output));
	}
	
	public function manageJob(){
		validateFields(array("trainer_emp_id", "job_date", "start_time", "end_time", "detail", "create_by"));
		
		$the_datetime = NOW();
		
		$data = array(
			"trainer_emp_id" => $this->input->post("trainer_emp_id"), 
			"job_date" => $this->input->post("job_date"),
			"start_time" => $this->input->post("start_time"),
			"end_time" => $this->input->post("end_time"),
			"detail" => $this->input->post("detail"),
			"last_modified_by" => $this->input->post("create_by"),
			"last_modified_datetime" => $the_datetime
		);
		
		$this->db->trans_start();
		if(!$this->input->post('trainer_job_id')){
			$data["create_by"] = $this->input->post("create_by");
			$data["create_datetime"] = $the_datetime;
			$this->db->insert('trainer_job', $data); 
		} else {
			$this->db->where('trainer_job_id', $this->input->post('trainer_job_id'));
			$this->db->update('trainer_job', $data); 
		}
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) exit("TRANSACTION INCOMPLETE !!");
		else echo json_encode(Array("result" => "true"));
	}
	
	function Confirm(){
		validateFields(array("trainer_job_id", "confirm_by"));
		
		$this->db->trans_start();
		$data = array("confirm_by" => $this->input->post('confirm_by'), "confirm_datetime" => NOW());
		$this->db->where("trainer_job_id", $this->input->post('trainer_job_id'));
		$this->db->update("trainer_job", $data);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	function listMember()
	{
		validateFields(array("trainer_emp_id"));
		
		$this->db->select("A.process_datetime datetime, CONCAT(A.max_hours, ' ชม. ') pt_course_name, CONCAT(B.fullname, ' ( ', B.nickname, ' )') seller, CONCAT(left_hours, '/', max_hours) hours, A.expiry_date, A.member_pt_id, C.member_no, C.firstname_th, C.lastname_th, C.nickname_th");
		$this->db->from("member_pt A");
		$this->db->join("employee B", "A.pt_seller_id = B.emp_id", "INNER");
		$this->db->join("member C", "A.member_id = C.member_id", "INNER");
		$this->db->where("A.pt_emp_id", $this->input->post('trainer_emp_id'));
		
		if($this->input->post("show_expired_or_empty") !== NULL){
			$this->db->where("(A.left_hours = 0 OR STR_TO_DATE(expiry_date,'%d/%m/%Y') < STR_TO_DATE('" . NOW() . "','%Y-%m-%d'))");
		}
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$this->db->order_by("C.member_no");
		$data = $this->db->get();
		//exit($this->db->last_query());
		
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
}
?>