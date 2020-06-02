<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member extends CI_Controller {
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
	
	public function ListMember(){
		validateFields(array("mode", "member_view", "member_type", "branch"));
		
		$this->data['branch'] = array("-1");
		if($this->input->post('branch')) {
			if(gettype($this->input->post('branch')) == "array") $this->data['branch'] = $this->input->post('branch');
			else $this->data['branch'] = explode(',', $this->input->post('branch'));
		}
		
		$this->data['member_view'] = ($this->input->post('member_view') !== NULL ? $this->input->post('member_view') : "-1");
		
		$this->data['member_type'] = array("-1");
		if($this->input->post('member_type')) {
			if(gettype($this->input->post('member_type')) == "array") $this->data['member_type'] = $this->input->post('member_type');
			else $this->data['member_type'] = explode(',', $this->input->post('member_type'));
		}
		
		$this->data['on_date'] = ($this->input->post('on_date') ? $this->input->post('on_date') : TODAY_UI());
		
		$this->data['member_list'] = array();
		$this->data['total_record'] = -1;
		if($this->input->post('branch') !== NULL && $this->input->post('member_view') !== NULL && $this->input->post('member_type') !== NULL)
		{
			$member_id = array();
			$member_ext_id = array();
			
			switch($this->data["member_view"])
			{
				case "0": // NEW
					$this->db->select("member_id, member_ext_id");
					$this->db->from("member_ext");
					$this->db->where("is_paid", 1);
					$this->db->where("is_void", 0);
					$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') BETWEEN STR_TO_DATE(start_date,'%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y')");
					if(!(count($this->data["member_type"]) == 1 && $this->data["member_type"][0] == "-1"))
						$this->db->where_in("member_type_id", $this->data["member_type"]);
					$this->db->group_by("member_id");
					$this->db->having("COUNT(*)", "1");
					$new_member = $this->db->get();
					foreach($new_member->result_array() as $data_current)
					{
						// HAS ACTIVE MEMBERSHIP
						$this->db->select("*");
						$this->db->from("member_ext");
						$this->db->where("STR_TO_DATE(expiry_date,'%d/%m/%Y') < STR_TO_DATE('" . NOW() . "','%Y-%m-%d')");
						$this->db->where("member_id", $data_current["member_id"]);
						$this->db->where("is_void", 0);
						$data_past = $this->db->get();
						
						if($data_past->num_rows() == 0){
							// NO PAST MEMBERSHIP
							$this->db->select("*");
							$this->db->from("member_ext");
							$this->db->where("STR_TO_DATE(start_date,'%d/%m/%Y') > STR_TO_DATE('" . NOW() . "','%Y-%m-%d')");
							$this->db->where("member_id", $data_current["member_id"]);
							$data_future = $this->db->get();
							
							if($data_future->num_rows() == 0){
								// NO FUTURE MEMBERSHIP
								$member_id[] = $data_current["member_id"];
								$member_ext_id[$data_current["member_id"]] = $data_current["member_ext_id"];
							}
						}
					}
					break;
				case "1": // ACTIVE
					$this->db->select("member_id, member_ext_id");
					$this->db->from("member_ext");
					$this->db->where("is_paid", 1);
					$this->db->where("is_void", 0);
					$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') BETWEEN STR_TO_DATE(start_date,'%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y')");
					if(!(count($this->data["member_type"]) == 1 && $this->data["member_type"][0] == "-1"))
						$this->db->where_in("member_type_id", $this->data["member_type"]);
					$this->db->group_by("member_id");
					$this->db->having("COUNT(*)", 1);
					$active_member = $this->db->get();
					foreach($active_member->result_array() as $active_data)
					{
						$member_id[] = $active_data["member_id"];
						$member_ext_id[$active_data["member_id"]] = $active_data["member_ext_id"];
					}
					break;
				case "2": // EXPIRED
					$this->db->select("member_id, member_ext_id");
					$this->db->from("member_ext");
					$this->db->where("is_void", 0);
					$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') BETWEEN STR_TO_DATE(start_date,'%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y')");
					if(!(count($this->data["member_type"]) == 1 && $this->data["member_type"][0] == "-1"))
						$this->db->where_in("member_type_id", $this->data["member_type"]);
					$this->db->group_by("member_id");
					$this->db->having("COUNT(*)", 0);
					$expired_member = $this->db->get();
					foreach($expired_member->result_array() as $expired_data)
					{
						// NO ACTIVE MEMBERSHIP
						$this->db->select("member_ext_id");
						$this->db->from("member_ext");
						$this->db->where("STR_TO_DATE(expiry_date,'%d/%m/%Y') < STR_TO_DATE('" . NOW() . "','%Y-%m-%d')");
						$this->db->where("member_id", $expired_data["member_id"]);
						$this->db->order_by("expiry_date DESC");
						$data_past = $this->db->get();
						
						if($data_past->num_rows() > 0){
							// HAS PAST MEMBERSHIP
							$member_id[] = $expired_data["member_id"];
							$member_ext_id[$expired_data["member_id"]] = $data_past->row(0)->member_ext_id;
						}
					}
					break;
				case "3": // DROPPED
					$this->db->select("member_id, member_ext_id");
					$this->db->from("member_ext");
					$this->db->where("is_void", 0);
					$this->db->where("drop_start IS NOT NULL AND drop_end IS NULL AND drop_is_cancelled = 0");
					if(!(count($this->data["member_type"]) == 1 && $this->data["member_type"][0] == "-1"))
						$this->db->where_in("member_type_id", $this->data["member_type"]);
					$dropped_member = $this->db->get();
					foreach($dropped_member->result_array() as $dropped_data)
					{
						$member_id[] = $dropped_data["member_id"];
						$member_ext_id[$dropped_data["member_id"]] = $dropped_data["member_ext_id"];
					}
					break;
				case "4": // ONLY DEPOSIT
					$this->db->select("member_id, member_ext_id");
					$this->db->from("member_ext");
					$this->db->where("is_paid", 0);
					$this->db->where("is_void", 0);
					if(!(count($this->data["member_type"]) == 1 && $this->data["member_type"][0] == "-1"))
						$this->db->where_in("member_type_id", $this->data["member_type"]);
					$unpaid_member = $this->db->get();
					foreach($unpaid_member->result_array() as $unpaid_data)
					{
						$member_id[] = $unpaid_data["member_id"];
						$member_ext_id[$unpaid_data["member_id"]] = $unpaid_data["member_ext_id"];
					}
					break;
				case "5": // SUSPENDED
					$this->db->select("A.member_id, B.member_ext_id");
					$this->db->from("member A");
					$this->db->join("member_ext B", "A.member_id = B.member_id", "INNER");
					$this->db->where("A.is_suspend", 1);
					$this->db->where("B.is_void", 0);
					$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') BETWEEN STR_TO_DATE(B.start_date,'%d/%m/%Y') AND STR_TO_DATE(B.expiry_date,'%d/%m/%Y')");
					if(!(count($this->data["member_type"]) == 1 && $this->data["member_type"][0] == "-1"))
						$this->db->where_in("B.member_type_id", $this->data["member_type"]);
					$suspend_member = $this->db->get();
					foreach($suspend_member->result_array() as $suspend_data)
					{
						$member_id[] = $suspend_data["member_id"];
						$member_ext_id[$suspend_data["member_id"]] = $suspend_data["member_ext_id"];
					}
					break;
			}
			
			$this->db->select("*");
			$this->db->from("member");
			if($this->input->post("member_view") != "-1") {
				if(count($member_id) == 0) $this->db->where("1=0");
				else $this->db->where_in("member_id", $member_id);
			}
			
			if(!(count($this->data["branch"]) == 1 && $this->data["branch"][0] == "-1"))
				$this->db->where_in("create_branch_id", $this->data["branch"]);
			
			$this->db->limit($this->config->item('record_per_page'), ($this->data['page'] - 1) * $this->config->item('record_per_page'));
			//$this->db->limit(($this->data['page'] - 1) * $this->config->item('record_per_page'), $this->config->item('record_per_page'));
			
			$this->db->order_by("member_no");
			$member_result = $this->db->get();
			
			foreach($member_result->result_array() as $item){
				$tmp = array();
				foreach($item as $key => $val){
					$tmp["$key"] = $val;
				}
				
				$this->db->select("B.member_type_name, CONCAT(A.start_date, ' - ', A.expiry_date) during_date, A.contract_no, A.drop_start, A.drop_end, A.drop_is_cancelled, A.member_ext_id");
				$this->db->from("member_ext A");
				$this->db->join("member_type B", "A.member_type_id = B.member_type_id", "INNER");
				
				switch($this->data["member_view"]){
					case "-1":
						$this->db->where("A.member_id", $tmp["member_id"]);
						$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') BETWEEN STR_TO_DATE(A.start_date,'%d/%m/%Y') AND STR_TO_DATE(A.expiry_date,'%d/%m/%Y')");
						break;
					case "4":
						$this->db->where("A.is_paid", "0");
						$this->db->where("A.member_ext_id", $member_ext_id[$tmp["member_id"]]);
						break;
					default:
						$this->db->where("A.is_paid", "1");
						$this->db->where("A.member_ext_id", $member_ext_id[$tmp["member_id"]]);
						break;
				}
				
				$this->db->where("A.is_void", 0);
				$this->db->order_by("A.member_ext_id DESC");
				$member_ext_result = $this->db->get();
				//echo $this->db->last_query() . "<BR><br>";
				
				$tmp["current_member_type"] = "";
				$tmp["during_date"] = "";
				$tmp["contract_no"] = "";
				$tmp["suspend_reason"] = "";
				$tmp["suspend_since"] = "";
				$tmp["suspend_by"] = "";
				$tmp["drop_during"] = "";
				$tmp["is_drop_active"] = "NO";
					
				if($member_ext_result->num_rows() > 0){
					$tmp_row = $member_ext_result->row(0);
					
					$tmp["current_member_type"] = $tmp_row->member_type_name;
					$tmp["during_date"] = $tmp_row->during_date;
					$tmp["contract_no"] = $tmp_row->contract_no;
					if($tmp_row->drop_start != "" && $tmp_row->drop_end != "")
						$tmp["drop_during"] = $tmp_row->drop_start . " - " . ($tmp_row->drop_end == "" ? "?" : $tmp_row->drop_end);
					
					if($tmp_row->drop_is_cancelled !== "1")
					{
						$this->db->where("member_ext_id", $tmp_row->member_ext_id);
						$this->db->where("drop_is_cancelled", "0");
						$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') BETWEEN STR_TO_DATE(drop_start,'%d/%m/%Y') AND STR_TO_DATE(drop_end,'%d/%m/%Y')");
						$tmp_data = $this->db->get("member_ext");
						if($tmp_data->num_rows() > 0)
							$tmp["is_drop_active"] = "YES";
					}
				}
				
				if($tmp["is_suspend"] == "1" && $tmp["member_suspend_id"] != ""){
					$this->db->select("reason, suspend_datetime, GET_NAME_BY_USER_ID(suspend_by) suspend_by");
					$this->db->from("member_suspend");
					$this->db->where("member_suspend_id", $tmp["member_suspend_id"]);
					$member_suspend_result = $this->db->get();
					if($member_suspend_result->num_rows() == 1){
						$row = $member_suspend_result->row(0);
						$tmp["suspend_reason"] = $row->reason;
						$tmp["suspend_since"] = $row->suspend_datetime;
						$tmp["suspend_by"] = $row->suspend_by;
					} else {
						$tmp["suspend_reason"] = null;
						$tmp["suspend_since"] = null;
						$tmp["suspend_by"] = null;
					}
				}
				
				if($this->input->post("member_view") == "4"){
					$this->db->select("CAST(IFNULL(total_deposit_amount, 0) AS UNSIGNED) + CAST(IFNULL(total_full_payment_amount, 0) AS UNSIGNED) paid, full_amount");
					$this->db->from("member_ext");
					$this->db->where("member_id", $tmp["member_id"]);
					$total_paid = $this->db->get();
					$total_paid = $total_paid->row(0);
					
					$tmp["total_paid"] = number_format($total_paid->paid) . " / " . number_format($total_paid->full_amount);
				} else $tmp["total_paid"] = null;
				
				$this->data['member_list'][] = $tmp;
			}
			
			$this->data['total_record'] = count($this->data['member_list']);
		}
		
		if($this->input->post('get_total_record') != NULL)
			exit(json_encode(array("result" => array("total_record" => $this->data['total_record']))));
		else
			$this->load->view('Report/Member/list', $this->data);
	}
	
	public function SellMember()
	{
		validateFields(array("mode", "member_view", "branch", "on_date"));
		$this->data['branch'] = ($this->input->post('branch') ? $this->input->post('branch') : "1");
		
		$this->data['member_view'] = ($this->input->post('member_view') ? $this->input->post('member_view') : "-1");
			
		$this->data['on_date'] = ($this->input->post('on_date') ? $this->input->post('on_date') : TODAY_UI());
		
		$this->load->view('Report/Member/sell_member', $this->data);
	}
	
	public function SellPT()
	{
		validateFields(array("mode", "branch", "on_date"));
		$this->data['branch'] = ($this->input->post('branch') ? $this->input->post('branch') : "1");
			
		$this->data['on_date'] = ($this->input->post('on_date') ? $this->input->post('on_date') : TODAY_UI());
		
		$this->load->view('Report/Member/sell_pt', $this->data);
	}
	
	public function CheckIn()
	{
		validateFields(array("mode", "branch", "on_date"));
		$this->data['branch'] = array("-1");
		if($this->input->post('branch')) {
			if(gettype($this->input->post('branch')) == "array") $this->data['branch'] = $this->input->post('branch');
			else $this->data['branch'] = explode(',', $this->input->post('branch'));
		}
			
		$this->data['on_date'] = ($this->input->post('on_date') ? $this->input->post('on_date') : TODAY_UI());
		
		$this->load->view('Report/Member/check_in', $this->data);
	}
	
	public function UsePT()
	{
		validateFields(array("mode", "branch", "on_date"));
		
		$this->data['branch'] = ($this->input->post('branch') ? $this->input->post('branch') : "1");
			
		$this->data['on_date'] = ($this->input->post('on_date') ? $this->input->post('on_date') : TODAY_UI());
		
		$this->load->view('Report/Member/use_pt', $this->data);
	}
}
?>