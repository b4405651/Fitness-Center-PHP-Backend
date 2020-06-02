<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Stock extends CI_Controller {
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
	
	public function ListProduct()
	{
		validateFields(array("mode"));
		
		$this->db->select("*");
		$this->db->from("product");
		$this->db->limit($this->config->item('record_per_page'), ($this->data['page'] - 1) * $this->config->item('record_per_page'));
		//$this->db->limit(($this->data['page'] - 1) * $this->config->item('record_per_page'), $this->config->item('record_per_page'));
		$this->db->order_by("product_name, product_code");

		$product = $this->db->get();

		if($this->input->post('get_total_record') != NULL)
			exit(json_encode(array("result" => array("total_record" => $product->num_rows()))));
		else
			$this->load->view('Report/Stock/list', $this->data);
	}
	
	public function Balance()
	{
		validateFields(array("mode", "branch", "on_date"));
		
		$this->data['branch'] = array("-1");
		if($this->input->post('branch')) {
			if(gettype($this->input->post('branch')) == "array") $this->data['branch'] = $this->input->post('branch');
			else $this->data['branch'] = explode(',', $this->input->post('branch'));
		}
			
		$this->data['on_date'] = ($this->input->post('on_date') ? $this->input->post('on_date') : TODAY_UI());
		
		$this->db->select("*");
		$this->db->from("product");
		$this->db->limit($this->config->item('record_per_page'), ($this->data['page'] - 1) * $this->config->item('record_per_page'));
		//$this->db->limit(($this->data['page'] - 1) * $this->config->item('record_per_page'), $this->config->item('record_per_page'));
		$this->db->order_by("product_name, product_code");
		$balance = $this->db->get();
		
		if($this->input->post('get_total_record') != NULL)
			exit(json_encode(array("result" => array("total_record" => $balance->num_rows()))));
		else
			$this->load->view('Report/Stock/balance', $this->data);
	}
	
	public function Transaction()
	{
		validateFields(array("mode", "branch_id", "on_date", "product_id"));
		
		$this->data['branch_id'] = $this->input->post('branch_id');
		$this->data['start_date'] = ($this->input->post('start_date') ? $this->input->post('start_date') : TODAY_UI());
		$this->data['end_date'] = ($this->input->post('end_date') ? $this->input->post('end_date') : TODAY_UI());
		$this->data['product_id'] = $this->input->post('product_id');
		
		$this->db->select("A.trx_datetime, B.amount, A.ref");
		$this->db->from("product_trx A");
		$this->db->join("product_trx_detail B", "A.product_trx_id = B.product_trx_id", "INNER");
		$this->db->where("B.product_id", $this->data['product_id']);
		$this->db->where("A.branch_id", $this->data['branch_id']);
		$this->db->where("B.is_void", 0);
		$this->db->where("STR_TO_DATE(A.trx_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('" . $this->data["start_date"] . "','%d/%m/%Y') AND STR_TO_DATE('" . $this->data["end_date"] . "','%d/%m/%Y')");
		$this->db->limit($this->config->item('record_per_page'), ($this->data['page'] - 1) * $this->config->item('record_per_page'));
		//$this->db->limit(($this->data['page'] - 1) * $this->config->item('record_per_page'), $this->config->item('record_per_page'));
		$this->db->order_by("A.trx_datetime");
		$trx = $this->db->get();
		
		if($this->input->post('get_total_record') != NULL)
			exit(json_encode(array("result" => array("total_record" => $trx->num_rows()))));
		else
			$this->load->view('Report/Stock/transaction', $this->data);
	}
}
?>