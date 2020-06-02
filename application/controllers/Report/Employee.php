<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Employee extends CI_Controller {
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
	
	public function ListEmployee()
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
			$this->db->where("A.is_trainer", 0);
			$this->db->where_in("A.branch_id", $this->data['branch']);
			$this->db->order_by("A.fullname, A.nickname");
			$emp = $this->db->get();
			exit(json_encode(array("result" => array("total_record" => $emp->num_rows()))));
		}
		
		$this->load->view('Report/Employee/list', $this->data);
	}
}
?>