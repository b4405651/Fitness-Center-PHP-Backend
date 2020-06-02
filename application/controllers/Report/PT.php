<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class PT extends CI_Controller {
	public $data = array();
	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->library('Mobile_Detect'); 
		$this->data['detect'] = new Mobile_Detect();

		$this->data['mode'] = ($this->input->post('mode') === NULL ? '0' : $this->input->post('mode'));
		$this->data['page'] = ($this->input->post('page') !== NULL && $this->input->post('page') != "0" ? $this->input->post('page') : "1");		
		$this->data['total_record'] = 0;
		
		$this->load->view('Report/header', $this->data);
	}
	
	public function ListPT()
	{
		validateFields(array("mode", "branch", "on_date"));
		$this->data['branch'] = array("-1");
		if($this->input->post('branch')) {
			if(gettype($this->input->post('branch')) == "array") $this->data['branch'] = $this->input->post('branch');
			else $this->data['branch'] = explode(',', $this->input->post('branch'));
		}
			
		$this->data['on_date'] = ($this->input->post('on_date') ? $this->input->post('on_date') : TODAY_UI());
		
		if($this->input->post('get_total_record') != NULL){
			$this->db->select("A.emp_id, A.fullname, A.nickname, B.branch_name");
			$this->db->from("employee A");
			$this->db->join("branch B", "A.branch_id = B.branch_id", "INNER");
			$this->db->where("A.is_trainer", 1);
			$this->db->where_in("A.branch_id", $this->input->post("branch_id"));
			$this->db->order_by("A.fullname, A.nickname");
			$emp = $this->db->get();
			exit(json_encode(array("result" => array("total_record" => $emp->num_rows()))));
		}
		
		$this->load->view('Report/PT/list', $this->data);
	}
	
	public function Training()
	{
		validateFields(array("mode", "branch", "start_date", "end_date"));
		$this->data['branch'] = array("-1");
		if($this->input->post('branch')) {
			if(gettype($this->input->post('branch')) == "array") $this->data['branch'] = $this->input->post('branch');
			else $this->data['branch'] = explode(',', $this->input->post('branch'));
		}
		
		$this->data['pt_emp_id'] = ($this->input->post('pt_emp_id') ? $this->input->post('pt_emp_id') : "");
		$this->data['start_date'] = ($this->input->post('start_date') ? $this->input->post('start_date') : TODAY_UI());
		$this->data['end_date'] = ($this->input->post('end_date') ? $this->input->post('end_date') : TODAY_UI());
		
		if($this->input->post('get_total_record') != NULL){
			$this->db->select("A.use_datetime, D.firstname_th, D.lastname_th, D.nickname_th, B.left_hours, B.max_hours");
			$this->db->from("member_use_pt A");
			$this->db->join("member_pt B", "A.member_pt_id = B.member_pt_id", "INNER");
			$this->db->join("member D", "B.member_id = D.member_id", "INNER");
			$this->db->where("A.pt_emp_id", $this->data['pt_emp_id']);
			$this->db->where("STR_TO_DATE(A.use_datetime,'%d/%m/%Y') BETWEEN STR_TO_DATE('" . $this->data['start_date'] . "','%d/%m/%Y') AND STR_TO_DATE('" . $this->data['end_date'] . "','%d/%m/%Y')");
			$this->db->order_by("A.use_datetime");
			$training = $this->db->get();
			$total_record = $training->num_rows();
			
			exit(json_encode(array("result" => array("total_record" => $total_record))));
		}
		
		$this->load->view('Report/PT/training', $this->data);
	}
	
	public function Schedule()
	{
		validateFields(array("mode", "branch", "start_date", "end_date"));
		
		$this->data['branch'] = array("-1");
		if($this->input->post('branch')) {
			if(gettype($this->input->post('branch')) == "array") $this->data['branch'] = $this->input->post('branch');
			else $this->data['branch'] = explode(',', $this->input->post('branch'));
		}
		
		$this->data['pt_emp_id'] = ($this->input->post('pt_emp_id') ? $this->input->post('pt_emp_id') : "");
		$this->data['start_date'] = ($this->input->post('start_date') ? $this->input->post('start_date') : TODAY_UI());
		$this->data['end_date'] = ($this->input->post('end_date') ? $this->input->post('end_date') : TODAY_UI());
		
		if($this->input->post('get_total_record') != NULL){
			$this->db->select("A.use_datetime, D.firstname_th, D.lastname_th, D.nickname_th, B.left_hours, B.max_hours");
			$this->db->from("member_use_pt A");
			$this->db->join("member_pt B", "A.member_pt_id = B.member_pt_id", "INNER");
			$this->db->join("member D", "B.member_id = D.member_id", "INNER");
			$this->db->where("A.pt_emp_id", $this->data['pt_emp_id']);
			$this->db->where("STR_TO_DATE(A.use_datetime,'%d/%m/%Y') BETWEEN STR_TO_DATE('" . $this->data['start_date'] . "','%d/%m/%Y') AND STR_TO_DATE('" . $this->data['end_date'] . "','%d/%m/%Y')");
			$this->db->order_by("A.use_datetime");
			$training = $this->db->get();
			$total_record = $training->num_rows();
			exit(json_encode(array("result" => array("total_record" => $total_record))));
		}
		
		$this->load->view('Report/PT/schedule', $this->data);
	}
}
?>