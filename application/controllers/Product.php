<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Product extends CI_Controller {
	public $page = NULL;
	public $recordCount = NULL;
	
	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->page = ($this->input->post('page') !== NULL && $this->input->post('page') != "0" ? $this->input->post('page') : "1");
		$this->recordCount = ($this->input->post("recordCount") < 0 ? 0 : $this->input->post("recordCount"));
	}
	
	public function getProductList(){
		validateFields(array("is_suspend"));
		
		if($this->input->post('search_txt'))
			$this->db->where("product_name LIKE '%" . $this->input->post('search_txt') . "%' OR product_code LIKE '%" . $this->input->post('search_txt') . "%'");
		$this->db->where("is_suspend", $this->input->post('is_suspend'));
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$data = $this->db->get("product");
		
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
	
	public function getProductData(){
		validateFields(array("product_id"));
		
		$this->db->where("product_id", $this->input->post('product_id'));
		$data = $this->db->get("product");
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			foreach($item as $key => $val){
				$output["$key"] = $val;
			}
		}
		
		echo json_encode(array("result" => $output));
	}
	
	public function getProductFromBarcode(){
		validateFields(array("product_code"));
		
		$this->db->where("product_code", $this->input->post('product_code'));
		$data = $this->db->get("product");
		$data = $data->result_array();
		
		if(count($data) == 0){
			exit(json_encode(Array("result" => "false", "msg" => "ไม่พบสินค้ารหัส '" . $this->input->post('product_code') . "'")));
		}
		$output = Array();
		
		foreach($data as $item){
			if($item["is_suspend"] == "1"){
				exit(json_encode(array("result" => "false", "msg" => "สินค้าถูกระงับการใช้ !!")));
			}
			foreach($item as $key => $val){
				$output["$key"] = $val;
			}
		}
		
		echo json_encode(array("result" => $output));
	}
	
	public function manageProduct(){
		validateFields(array("product_name", "price", "alert_amount"));
		
		$data = array(
			"product_name" => $this->input->post("product_name"), 
			"price" => $this->input->post("price"),
			"alert_amount" => $this->input->post("alert_amount")
		);
		
		if($this->input->post("product_code") !== null)
			$data["product_code"] = $this->input->post("product_code");
		
		$this->db->trans_start();
		if(!$this->input->post('product_id')){
			$this->db->insert('product', $data); 
		} else {
			$this->db->where('product_id', $this->input->post('product_id'));
			$this->db->update('product', $data); 
		}
		
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) exit("TRANSACTION INCOMPLETE !!");
		else echo json_encode(Array("result" => "true"));
	}
	
	function Suspend(){
		validateFields(array("product_id"));
		
		$this->db->trans_start();
		$data = array("is_suspend" => 1, "suspend_since" => NOW());
		$this->db->where("product_id", $this->input->post('product_id'));
		$this->db->update("product", $data);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	function Enable(){
		validateFields(array("product_id"));
		
		$this->db->trans_start();
		$data = array("is_suspend" => 0, "suspend_since" => null);
		$this->db->where("product_id", $this->input->post('product_id'));
		$this->db->update("product", $data);
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	public function getProductStock(){
		validateFields(array("is_alert", "branch_id"));
		
		$this->db->select("A.product_id, A.product_name, A.product_code, IFNULL(SUM(B.amount), 0) amount, A.alert_amount");
		$this->db->from("product A");
		$this->db->join("product_trx_detail B", "A.product_id = B.product_id and B.branch_id = " . $this->input->post('branch_id') . " AND B.is_void = 0", "left outer");
		if($this->input->post('search_txt'))
			$this->db->where("A.product_name LIKE '%" . $this->input->post('search_txt') . "%' OR A.product_code LIKE '%" . $this->input->post('search_txt') . "%'");
		$this->db->group_by("A.product_id");
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$this->db->order_by("A.product_name");
		$data = $this->db->get();
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			$tmp = array();
			if($this->input->post('is_alert') !== NULL && $this->input->post('is_alert') === '1')
			{
				if($item['amount'] <= $item['alert_amount']){
					foreach($item as $key => $val){
						if($key != "alert_amount")
							$tmp["$key"] = $val;
					}
					$tmp["is_alert"] = "1";
					$output[] = $tmp;
				}
			} else {
				foreach($item as $key => $val){
					if($key != "alert_amount")
						$tmp["$key"] = $val;
				}
				if($item['amount'] <= $item['alert_amount'])
					$tmp["is_alert"] = "1";
				else
					$tmp["is_alert"] = "0";
				$output[] = $tmp;
			}
		}
		
		echo json_encode(array("result" => $output, "total_record" => count($data->result_array())));
	}
	
	public function getProductTrx()
	{
		validateFields(array("branch_id", "product_id"));
		
		$this->db->select("A.ref, B.amount, A.trx_datetime, GET_NAME_BY_USER_ID(A.trx_by) trx_by");
		$this->db->from("product_trx A");
		$this->db->join("product_trx_detail B", "A.product_trx_id = B.product_trx_id", "INNER");
		$this->db->join("user C", "A.trx_by = C.user_id", "LEFT OUTER");
		$this->db->join("employee D", "C.emp_id = D.emp_id", "LEFT OUTER");
		$this->db->where("A.branch_id", $this->input->post('branch_id'));
		$this->db->where("A.is_void = 0");
		$this->db->where("B.product_id", $this->input->post('product_id'));
		if($this->input->post('trx_since'))
			$this->db->where("STR_TO_DATE(SUBSTR(A.trx_datetime, 1, 10), '%Y-%m-%d') >= STR_TO_DATE('" . $this->input->post('trx_since') . "','%d/%m/%Y')");
		if($this->input->post('trx_until'))
			$this->db->where("STR_TO_DATE(SUBSTR(A.trx_datetime, 1, 10), '%Y-%m-%d') <= STR_TO_DATE('" . $this->input->post('trx_until') . "','%d/%m/%Y')");
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$this->db->order_by("A.trx_datetime, B.product_trx_detail_id");
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
	
	public function manageProductTrx()
	{
		validateFields(array("branch_id", "trx_by", "product_list"));
		
		$this->db->trans_start();
		$data = array(
		   'branch_id' => $this->input->post('branch_id') ,
		   'ref' => $this->input->post('ref'),
		   'trx_datetime' => NOW(),
		   'trx_by' => $this->input->post('trx_by')
		);
		$this->db->insert('product_trx', $data);
		$product_trx_id = $this->db->insert_id();
		
		$list = explode("@@@", $this->input->post('product_list'));
		foreach($list as $listItem){
			$item = explode("###", $listItem);
			$data = array(
				'branch_id' => $this->input->post('branch_id'),
				'product_id' => $item[0],
				'amount' => $item[1],
				'product_trx_id' => $product_trx_id
			);
			$this->db->insert('product_trx_detail', $data);
		}
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) echo "TRANSACTION INCOMPLETE !!";
		else echo json_encode(Array("result" => "true"));
	}
}
?>