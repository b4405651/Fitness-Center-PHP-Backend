<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Shop extends CI_Controller {
	public function Payment(){
		validateFields(array("grand_total", "product_list", "payment_list", "branch_id", "receive_by", "vat", "total_net_price_before_vat", "total_vat_amount"));
		
		$this->db->trans_start();
		
		// GET BILL
		$bill = get_bill($this->input->post('branch_id'));
		$bill_id = $bill['bill_id'];
		$bill_no = $bill['bill_no'];
		
		// INSERT SHOP
		$data = array(
			"bill_id" => $bill_id,
			"branch_id" => $this->input->post('branch_id'),
			"total_price" => $this->input->post('grand_total'),
			"receive_by" => $this->input->post('receive_by'),
			"receive_datetime" => NOW(),
			"vat" => number_format((float)$this->input->post('vat'), 2, '.', ''),
			"net_price_before_vat" => $this->input->post('total_net_price_before_vat'),
			"vat_amount" => $this->input->post('total_vat_amount')
		);
		
		$this->db->insert('shop', $data);
		$shop_id = $this->db->insert_id();
		
		// MAP SHOP WITH BILL
		$this->db->where('bill_id', $bill_id);
		$this->db->update('bill', array('src_type' => 4, 'src_id' => $shop_id));
		
		// INSERT SHOP_DETAIL
		$product_list = explode('!!', $this->input->post('product_list'));
		foreach($product_list as $list){
			$product_data = explode('##', $list);
			$data = array(
				"shop_id" => $shop_id,
				"product_id" => $product_data[0],
				"unit_price" => $product_data[1],
				"amount" => $product_data[2],
				"total_price" => $product_data[3],
				"net_price_before_vat" => $product_data[4],
				"vat_amount" => $product_data[5],
				"vat" => number_format((float)$product_data[6], 2, '.', '')
			);
			$this->db->insert('shop_detail', $data);
		}
		
		// INSERT SHOP_PAYMENT
		$payment_list = explode('!!', $this->input->post('payment_list'));
		foreach($payment_list as $list){
			$payment_data = explode('##', $list);
			$data = array(
				"shop_id" => $shop_id,
				"branch_id" => $this->input->post('branch_id'),
				"payment_type" => $payment_data[0],
				"amount" => $payment_data[1],
				"card_no" => $payment_data[2],
				"card_expiry_date" => $payment_data[3],
				"receive_by" => $this->input->post('receive_by'),
				"receive_datetime" => NOW()
			);
			$this->db->insert('shop_payment', $data);
		}
		
		// INSERT PRODUCT_TRX
		$data = array(
		   'branch_id' => $this->input->post('branch_id'),
		   'shop_id' => $shop_id,
		   'ref' => "BILL NO : " . $bill_no,
		   'trx_datetime' => NOW(),
		   'trx_by' => $this->input->post('receive_by')
		);
		$this->db->insert('product_trx', $data);
		$product_trx_id = $this->db->insert_id();
		
		// INSERT PRODUCT_TRX_DETAIL
		$list = explode("!!", $this->input->post('product_list'));
		foreach($list as $listItem){
			$item = explode("##", $listItem);
			$data = array(
				'branch_id' => $this->input->post('branch_id'),
				'product_id' => $item[0],
				'amount' => "-" . $item[2],
				'product_trx_id' => $product_trx_id
			);
			$this->db->insert('product_trx_detail', $data);
		}
		
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		
		exit(json_encode(Array("result" => array("shop_id" => $shop_id, "bill_id" => $bill_id))));
	}
	
	public function getBillHeader()
	{
		validateFields(array("bill_id", "issue_vat"));
		
		if($this->input->post("issue_vat") === "1"){
			$this->db->trans_start();
			
			$data = array("issue_vat" => "1");
			$this->db->where("bill_id", $this->input->post("bill_id"));
			$this->db->update("bill", $data);
			
			$this->db->trans_complete();
			
			if ($this->db->trans_status() === FALSE) {
				echo json_encode("TRANSACTION INCOMPLETE !!");
				return;
			}
		}
		
		$this->db->select("A.bill_no, A.bill_datetime, A.branch_id, C.total_price, C.net_price_before_vat, C.vat, C.vat_amount, GET_NAME_BY_USER_ID(C.receive_by) receive_by");
		$this->db->from("bill A");
		$this->db->join("branch B", "A.branch_id = B.branch_id", "INNER");
		$this->db->join("shop C", "A.bill_id = C.bill_id", "INNER");
		$this->db->join("user D", "C.receive_by = D.user_id", "LEFT OUTER");
		$this->db->join("employee E", "D.emp_id = E.emp_id", "LEFT OUTER");
		$this->db->where("A.bill_id", $this->input->post('bill_id'));
		$data = $this->db->get();
		$data = $data->row(0);

		exit(json_encode(Array("result" => array("bill_no" => $data->bill_no, "bill_datetime" => $data->bill_datetime, "branch_id" => $data->branch_id, "total_price" => $data->total_price, "net_price_before_vat" => $data->net_price_before_vat, "vat" => $data->vat, "vat_amount" => $data->vat_amount, "receive_by" => $data->receive_by))));
	}
	
	public function getDataByBillNo()
	{
		validateFields(array("bill_no"));

		$this->db->select("A.bill_id, A.src_type, B.is_void, GET_NAME_BY_USER_ID(B.void_by) void_by, B.void_datetime, B.void_reason");
		$this->db->from("bill A");
		$this->db->join("shop B", "A.bill_id = B.bill_id", "INNER");
		$this->db->join("user C", "B.void_by = C.user_id", "LEFT OUTER");
		$this->db->join("employee D", "C.emp_id = D.emp_id", "LEFT OUTER");
		$this->db->where("A.bill_no", $this->input->post('bill_no'));
		$data = $this->db->get();
		if($data->num_rows() == 0){
			exit(json_encode(Array("result" => "false", "msg" => "ไม่พบข้อมูลของหมายเลขบิล '". $this->input->post('bill_no') . "'")));
		}
		
		$data = $data->row(0);
		
		if($data->src_type != "4"){
			switch($data->src_type){
				case "1":
					exit(json_encode(Array("result" => "false", "msg" => "บิลหมายเลข '". $this->input->post('bill_no') . "' เป็นบิล 'ค่ามัดจำ MEMBER' !! \r\n\r\nหากต้องการดูรายละเอียด สามารถดูได้ที่ หัวข้อ สมาชิก > เลือกสมาชิก > ประวัติการซื้อ MEMBER")));
					break;
				case "2":
					exit(json_encode(Array("result" => "false", "msg" => "บิลหมายเลข '". $this->input->post('bill_no') . "' เป็นบิล 'ค่า MEMBER เต็มจำนวน / ส่วนที่เหลือ' !! \r\n\r\nหากต้องการดูรายละเอียด สามารถดูได้ที่ หัวข้อ สมาชิก > เลือกสมาชิก > ประวัติการซื้อ MEMBER")));
					break;
				case "3":
					exit(json_encode(Array("result" => "false", "msg" => "บิลหมายเลข '". $this->input->post('bill_no') . "' เป็นบิล 'ค่าซื้อ PT' !! \r\n\r\nหากต้องการดูรายละเอียด สามารถดูได้ที่ หัวข้อ สมาชิก > เลือกสมาชิก > ประวัติการซื้อ PT")));
					break;
			}
		}
		
		$bill_id = $data->bill_id;
		$is_void = $data->is_void;
		$void_by = $data->void_by;
		$void_datetime = $data->void_datetime;
		$void_reason = $data->void_reason;
		
		$output = Array();
		
		$this->db->select("C.product_name, B.unit_price price, B.product_id, B.amount, B.total_price, B.net_price_before_vat, B.vat_amount, B.vat");
		$this->db->from("shop A");
		$this->db->join("shop_detail B", "A.shop_id = B.shop_id", "INNER");
		$this->db->join("product C", "B.product_id = C.product_id", "INNER");
		$this->db->where("A.bill_id", $bill_id);
		$data = $this->db->get();
		
		$product_data = "";
			
		foreach($data->result_array() as $item){
			foreach($item as $key => $val){
				$product_data .= $val . "##";
			}
			$product_data .= "!!";
		}
		
		if(trim($product_data) != "")
			$product_data = substr($product_data, 0, strlen($product_data) - 2);
		
		$output["product_data"] = $product_data;
		
		$this->db->select("B.payment_type, B.amount, B.card_no, B.card_expiry_date, GET_NAME_BY_USER_ID(B.receive_by) receive_by_name, B.receive_datetime");
		$this->db->from("shop A");
		$this->db->join("shop_payment B", "A.shop_id = B.shop_id", "INNER");
		$this->db->where("A.bill_id", $bill_id);
		$data = $this->db->get();
		
		$payment_data = "";
			
		foreach($data->result_array() as $item){
			foreach($item as $key => $val){
				$payment_data .= $val . "##";
			}
			$payment_data .= "!!";
		}
		
		if(trim($payment_data) != "")
			$payment_data = substr($payment_data, 0, strlen($payment_data) - 2);
		
		$output["payment_data"] = $payment_data;
		
		exit(json_encode(Array("result" => array("bill_id" => $bill_id, "is_void" => $is_void, "void_by" => $void_by, "void_datetime" => $void_datetime, "void_reason" => $void_reason, "product_data" => $product_data, "payment_data" => $payment_data))));
	}
	
	public function Void()
	{
		validateFields(array("bill_id", "void_by", "void_reason"));
		
		$this->db->select("shop_id");
		$this->db->from("shop");
		$this->db->where("bill_id", $this->input->post('bill_id'));
		$data = $this->db->get();
		$data = $data->row(0);
		
		$shop_id = $data->shop_id;
		
		$this->db->select("product_id, amount");
		$this->db->from("shop_detail");
		$this->db->where("shop_id", $shop_id);
		$data = $this->db->get();
		$product_list = $data->result_array();
		
		$data = array(
			"is_void" => "1",
			"void_by" => $this->input->post('void_by'),
			"void_datetime" => NOW(),
			"void_reason" => $this->input->post('void_reason')
		);
		
		$this->db->trans_start();
		
		$this->db->where("shop_id", $shop_id);
		$this->db->update("shop", $data);
		
		$this->db->where("bill_id", $this->input->post('bill_id'));
		$this->db->update("bill", $data);
		
		$this->db->where("shop_id", $shop_id);
		$this->db->update("product_trx", array("is_void" => "1"));
		
		$this->db->select("product_trx_id");
		$this->db->from("product_trx");
		$this->db->where("shop_id", $shop_id);
		$data = $this->db->get();
		$data = $data->row(0);
		$product_trx_id = $data->product_trx_id;
		
		$this->db->where("product_trx_id", $product_trx_id);
		$this->db->update("product_trx_detail", array("is_void" => "1"));
		
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		
		$this->db->select("A.bill_id, B.is_void, GET_NAME_BY_USER_ID(B.void_by) void_by, B.void_datetime, B.void_reason");
		$this->db->from("bill A");
		$this->db->join("shop B", "A.bill_id = B.bill_id", "INNER");
		$this->db->join("user C", "B.void_by = C.user_id", "LEFT OUTER");
		$this->db->join("employee D", "C.emp_id = D.emp_id", "LEFT OUTER");
		$this->db->where("A.bill_id", $this->input->post('bill_id'));
		$data = $this->db->get();
		$data = $data->row(0);
		$is_void = $data->is_void;
		$void_by = $data->void_by;
		$void_datetime = $data->void_datetime;
		$void_reason = $data->void_reason;
		
		exit(json_encode(Array("result" => array("void_by" => $void_by, "void_datetime" => $void_datetime, "void_reason" => $void_reason))));
	}
}
?>