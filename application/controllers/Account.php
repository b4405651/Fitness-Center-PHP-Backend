<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Account extends CI_Controller {
	public function index(){
		validateFields(array("report_year", "report_type", "branch_id"));
		$year = $this->input->post("report_year");
		
		$max_month = 6;
		if($this->input->post("report_type") == "1")
			$max_month = 12;
		
		for($month = $max_month; $month >= 1; $month--){
			$tmp = array("month" => $month, "data" => array());
			
			$this->db->from("bill");
			$this->db->where("is_void", "0");
			$this->db->where("branch_id", $this->input->post("branch_id"));
			
			$this->db->where("STR_TO_DATE(bill_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/" . str_pad($month, 2, "0", STR_PAD_LEFT) . "/" . $year . "','%d/%m/%Y') AND STR_TO_DATE('" . str_pad(cal_days_in_month(CAL_GREGORIAN,$month,($year - 543)), 2, "0", STR_PAD_LEFT) . "/" . str_pad($month, 2, "0", STR_PAD_LEFT) . "/" . $year . "','%d/%m/%Y')");
			
			$this->db->order_by("bill_datetime");
			$report = $this->db->get();
			
			foreach($report->result_array() as $report_data){
				$bill_result = null;
				$bill_type = "";
				switch($report_data["src_type"]){
					case "1": // MEMBER EXT DEPOSIT
						$bill_type = "MEMBER_EXT_DEPOSIT";
						$this->db->select("total_deposit_amount amount");
						$this->db->from("member_ext");
						$this->db->where("bill_deposit_id", $report_data["bill_id"]);
						$bill_result = $this->db->get();
						break;
					case "2": // MEMBER EXT FULL
						$bill_type = "MEMBER_EXT_FULL";
						$this->db->select("total_full_payment_amount amount");
						$this->db->from("member_ext");
						$this->db->where("bill_full_id", $report_data["bill_id"]);
						$bill_result = $this->db->get();
						break;
					case "3": // MEMBER PT
						$bill_type = "MEMBER_PT";
						$this->db->select("price amount");
						$this->db->from("member_pt");
						$this->db->where("bill_id", $report_data["bill_id"]);
						$bill_result = $this->db->get();
						break;
					case "4": // SHOP
						$bill_type = "SHOP";
						$this->db->select("total_price amount");
						$this->db->from("shop");
						$this->db->where("bill_id", $report_data["bill_id"]);
						$bill_result = $this->db->get();
						break;
				}
				if($bill_result != null)
				{
					if($bill_result->num_rows() == 1){
						$month_data = array("bill_no" => $report_data["bill_no"], "bill_type" => $bill_type, "amount" => $bill_result->row(0)->amount, "issue_vat" => $report_data["issue_vat"]);
						
						$tmp["data"][] = $month_data;
					}
				}
			}
			$output[] = $tmp;
		}
		
		exit(json_encode(Array("result" => $output)));
	}
}
?>