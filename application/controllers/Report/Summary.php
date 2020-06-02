<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Summary extends CI_Controller {
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
	
	public function DuringDate(){
		validateFields(array("mode", "branch", "start_date", "end_date"));
		$this->data['branch'] = array("-1");
		if($this->input->post('branch')) {
			if(gettype($this->input->post('branch')) == "array") $this->data['branch'] = $this->input->post('branch');
			else $this->data['branch'] = explode(',', $this->input->post('branch'));
		}
			
		$this->data['start_date'] = ($this->input->post('start_date') ? $this->input->post('start_date') : TODAY_UI());
		$this->data['end_date'] = ($this->input->post('end_date') ? $this->input->post('end_date') : TODAY_UI());
		
		$this->load->view('Report/Summary/during_date', $this->data);
	}
	
	public function OnDate()
	{
		validateFields(array("mode", "branch", "on_date"));
		$this->data['branch'] = array("-1");
		if($this->input->post('branch')) {
			if(gettype($this->input->post('branch')) == "array") $this->data['branch'] = $this->input->post('branch');
			else $this->data['branch'] = explode(',', $this->input->post('branch'));
		}
			
		$this->data['on_date'] = ($this->input->post('on_date') ? $this->input->post('on_date') : TODAY_UI());
		
		$this->load->view('Report/Summary/on_date', $this->data);
	}
}
?>