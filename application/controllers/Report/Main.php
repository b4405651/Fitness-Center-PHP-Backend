<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends CI_Controller {
	public $data = array();
	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->library('Mobile_Detect'); 
		$this->data['detect'] = new Mobile_Detect();

		if($this->input->post('mode') === NULL) $this->data['mode'] = '0';
		else $this->data['mode'] = $this->input->post('mode');
		
		$this->load->view('Report/header', $this->data);
	}
	
	public function index(){
		$this->load->view('Report/index', $this->data);
	}
}
?>