<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Member extends CI_Controller {
	public $page = NULL;
	public $recordCount = NULL;
	
	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->page = ($this->input->post('page') !== NULL && $this->input->post('page') != "0" ? $this->input->post('page') : "1");
		$this->recordCount = ($this->input->post("recordCount") < 0 ? 0 : $this->input->post("recordCount"));
	}
	
	public function TEST()
	{
		$data = $this->db->get("TEST");
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			$tmp = array();
			foreach($item as $key => $val){
				for($i=1; $i<=5; $i++){
					$tmp["$key" . "_" . $i] = $val;
				}
			}
			$output[] = $tmp;
		}
		
		echo json_encode(array("result" => $output, "total_record" => count($data->result_array())));
	}
	
	public function MemberList()
	{
		validateFields(array("user_id"));
		
		$member_id_list = array();
		if($this->input->post('is_expired') !== NULL || $this->input->post('member_no') !== NULL || $this->input->post('not_start') !== NULL)
		{
			if($this->input->post('is_expired') !== NULL)
			{
				if($this->input->post('is_expired') === '0')
				{
					// ACTIVE ONLY
					$this->db->select("member_id, count(*) rowCount");
					$this->db->from("member_ext");
					$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') BETWEEN STR_TO_DATE(start_date,'%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y')");
					$this->db->where("member_id IN (SELECT member_id FROM member where create_branch_id = " . $this->input->post('branch_id') . ")");
					$this->db->group_by("member_id");
					$data = $this->db->get();
					//echo $this->db->last_query() . "\r\n\r\n";
					foreach($data->result_array() as $row)
						if($row["rowCount"] >= 1)
							$member_id_list[] = $row["member_id"];
				}
				
				if($this->input->post('is_expired') === '1')
				{
					// EXPIRED ONLY
					$tmp_member_id1 = array();
					
					$this->db->select("member_id");
					$this->db->from("member_ext");
					$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') > STR_TO_DATE(expiry_date,'%d/%m/%Y')");
					$this->db->where("member_id IN (SELECT member_id FROM member where create_branch_id = " . $this->input->post('branch_id') . ")");
					$data = $this->db->get();
					//echo $this->db->last_query() . "\r\n\r\n";
					foreach($data->result_array() as $row)
						$tmp_member_id1[] = $row["member_id"];
					
					$this->db->select("member_id");
					$this->db->from("member_ext");
					$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') < STR_TO_DATE(start_date,'%d/%m/%Y')");
					$this->db->where("member_id IN (SELECT member_id FROM member where create_branch_id = " . $this->input->post('branch_id') . ")");
					$this->db->where_in("member_id", $tmp_member_id1);
					$this->db->group_by("member_id");
					$data = $this->db->get();
					//exit($this->db->last_query() . "\r\n\r\n");
					
					$tmp_member_id2 = array();
					if($data->num_rows() > 0){
						foreach($data->result_array() as $row)
							$tmp_member_id2[] = $row["member_id"];
					}
					
					if($tmp_member_id2 != null){
						if(count($tmp_member_id2) > 0){
							for($i=0; $i<count($tmp_member_id1); $i++){
								if(!in_array($tmp_member_id1[$i], $tmp_member_id2))
									$member_id_list[] = $tmp_member_id1[$i];
							}
						} else $member_id_list = $tmp_member_id1;
					} else $member_id_list = $tmp_member_id1;
					
				}
			}
			
			if($this->input->post('member_no') !== NULL){
				$this->db->select("member_id");
				$this->db->from("member");
				$this->db->where("LENGTH(IFNULL(member_no, '')) = 0");
				$this->db->where("create_branch_id", $this->input->post('branch_id'));
				$data = $this->db->get();
				//echo $this->db->last_query() . "\r\n\r\n";
				//exit($this->db->last_query());
				//exit(print_r($data->result_array()));
				foreach($data->result_array() as $row){
					$member_id_list[] = $row["member_id"];
				}
			}
			
			if($this->input->post('not_start') !== NULL){
				// NOT START
				$tmp_member_id1 = array();
				$this->db->select("member_id");
				$this->db->from("member_ext");
				$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') < STR_TO_DATE(start_date,'%d/%m/%Y')");
				$this->db->where("member_id IN (SELECT member_id FROM member where create_branch_id = " . $this->input->post('branch_id') . ")");
				$data = $this->db->get();
				//echo $this->db->last_query() . "\r\n\r\n";
				foreach($data->result_array() as $row){
					$tmp_member_id1[] = $row["member_id"];
				}
				//echo "ID1 : " . implode(', ', $tmp_member_id1) . "\r\n\r\n";
				
				// ACTIVE
				$tmp_member_id2 = array();
				$this->db->select("member_id");
				$this->db->from("member_ext");
				$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') BETWEEN STR_TO_DATE(start_date,'%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y')");
				$this->db->where("member_id IN (SELECT member_id FROM member where create_branch_id = " . $this->input->post('branch_id') . ")");
				$this->db->group_by("member_id");
				$data = $this->db->get();
				//echo $this->db->last_query() . "\r\n\r\n";
				foreach($data->result_array() as $row){
						$tmp_member_id2[] = $row["member_id"];
				}
				//exit("ID2 : " . implode(', ', $tmp_member_id2) . "\r\n\r\n");
				
				if($tmp_member_id2 != null){
						if(count($tmp_member_id2) > 0){
							for($i=0; $i<count($tmp_member_id1); $i++){
								if(!in_array($tmp_member_id1[$i], $tmp_member_id2))
									$member_id_list[] = $tmp_member_id1[$i];
							}
						} else $member_id_list = $tmp_member_id1;
					} else $member_id_list = $tmp_member_id1;
			}
			
			if(count($member_id_list) == 0)
				exit(json_encode(Array("result" => array(), "total_record" => 0)));
		}
		
		// COUNT
		$this->db->select("member_id, member_no, card_no, firstname_th, lastname_th, nickname_th, gender, create_date, birthday, mobile_phone, is_suspend");
		$this->db->from("member");
		$this->db->where("create_branch_id", $this->input->post("branch_id"));
		
		if(count($member_id_list) > 0) $this->db->where_in("member_id", $member_id_list);
		/*if($this->input->post("member_no") === "") $this->db->where("member_no IS NULL");
		else $this->db->where("member_no IS NOT NULL");*/
		if($this->input->post("gender")) $this->db->where("gender", $this->input->post("gender"));
		if($this->input->post("search_txt")) {
			$search_txt = $this->input->post("search_txt");
			$this->db->where("( member_no LIKE '%$search_txt%' OR card_no LIKE '%$search_txt%' OR firstname_th LIKE '%$search_txt%' OR lastname_th LIKE '%$search_txt%' OR nickname_th LIKE '%$search_txt%' OR firstname_en LIKE '%$search_txt%' OR lastname_en LIKE '%$search_txt%' OR nickname_en LIKE '%$search_txt%' OR document_no LIKE '%$search_txt%' OR home_phone LIKE '%$search_txt%' OR mobile_phone LIKE '%$search_txt%' OR work_phone LIKE '%$search_txt%' OR email LIKE '%$search_txt%')");
		}
		$this->db->order_by("member_no");
		
		$data = $this->db->get();
		//echo $this->db->last_query() . "\r\n\r\n";
		//exit($this->db->last_query());
		$recordCount = $data->num_rows();
		
		// GET RECORDS
		$this->db->select("member_id, member_no, card_no, firstname_th, lastname_th, nickname_th, gender, create_date, birthday, mobile_phone, is_suspend, member_suspend_id");
		$this->db->from("member");
		$this->db->where("create_branch_id", $this->input->post("branch_id"));
		
		if(count($member_id_list) > 0) $this->db->where_in("member_id", $member_id_list);
		/*if($this->input->post("member_no") === "") $this->db->where("member_no IS NULL");
		else $this->db->where("member_no IS NOT NULL");*/
		if($this->input->post("gender")) $this->db->where("gender", $this->input->post("gender"));
		if($this->input->post("search_txt")) {
			$search_txt = $this->input->post("search_txt");
			$this->db->where("( member_no LIKE '%$search_txt%' OR card_no LIKE '%$search_txt%' OR firstname_th LIKE '%$search_txt%' OR lastname_th LIKE '%$search_txt%' OR nickname_th LIKE '%$search_txt%' OR firstname_en LIKE '%$search_txt%' OR lastname_en LIKE '%$search_txt%' OR nickname_en LIKE '%$search_txt%' OR document_no LIKE '%$search_txt%' OR home_phone LIKE '%$search_txt%' OR mobile_phone LIKE '%$search_txt%' OR work_phone LIKE '%$search_txt%' OR email LIKE '%$search_txt%')");
		}
		$this->db->order_by("member_no");
		
		if($this->input->post('page') !== NULL && $this->input->post('recordCount') !== NULL){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
		}
		$data = $this->db->get();
		
		//echo $this->db->last_query() . "\r\n\r\n";
		//exit($this->db->last_query());
		$output = Array();
		
		foreach($data->result_array() as $item){
			$tmp = array();
			foreach($item as $key => $val){
				$tmp["$key"] = $val;
			}
			
			$tmp["current_member_type"] = "";
			$tmp["during_date"] = "";
			$tmp["contract_no"] = "";
			$tmp["drop_during"] = "";
			$tmp["is_drop_active"] = "NO";
			$tmp["member_drop_id"] = "";
			
			$this->db->select("B.member_type_name, CONCAT(A.start_date, ' - ', A.expiry_date) during_date, A.contract_no, A.drop_start, A.drop_end, A.member_ext_id, STR_TO_DATE(A.drop_end,'%d/%m/%Y') < STR_TO_DATE('" . NOW() . "','%Y-%m-%d') is_drop_expired");
			$this->db->from("member_ext A");
			$this->db->join("member_type B", "A.member_type_id = B.member_type_id", "INNER");
			$this->db->where("A.is_void", "0");
			$this->db->where("A.is_paid", "1");
			$this->db->where("A.member_id", $tmp["member_id"]);
			$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') BETWEEN STR_TO_DATE(A.start_date,'%d/%m/%Y') AND STR_TO_DATE(A.expiry_date,'%d/%m/%Y')");
			$this->db->order_by("A.member_ext_id DESC");
			$result = $this->db->get();
			//echo $this->db->last_query() . "\r\n\r\n";
			if($result->num_rows() > 0){
				// ACTIVE MEMBER TYPE
				$tmp_row = $result->row(0);
				
				$tmp["current_member_type"] = $tmp_row->member_type_name;
				$tmp["during_date"] = $tmp_row->during_date;
				$tmp["contract_no"] = $tmp_row->contract_no;
				
				$this->db->select("*");
				$this->db->from("member_drop");
				$this->db->where("is_cancelled", "0");
				$this->db->where("member_id", $tmp["member_id"]);
				$this->db->order_by("drop_start ASC");
				$drop_result = $this->db->get();
				
				if($drop_result->num_rows() > 0){
					$drop_row = $drop_result->row(0);
					
					if($drop_row->drop_start != "" && $drop_row->drop_end != "") {
						$this->db->select("*");
						$this->db->from("member_ext");
						$this->db->where("is_void", "0");
						$this->db->where("member_id", $tmp["member_id"]);
						$this->db->where("(STR_TO_DATE('" . $drop_row->drop_start . "','%d/%m/%Y') BETWEEN STR_TO_DATE(start_date,'%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y') OR STR_TO_DATE('" . $drop_row->drop_end . "','%d/%m/%Y') BETWEEN STR_TO_DATE(start_date,'%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y'))");
						$this->db->order_by("start_date ASC");
						$ext_result = $this->db->get();
						
						if($ext_result->num_rows() > 0){
							$ext_row = $ext_result->row(0);
							$tmp["is_drop_active"] = "YES";
							$tmp["drop_during"] = $drop_row->drop_start . " - " . $drop_row->drop_end;
							$tmp["member_drop_id"] = $drop_row->member_drop_id;
						}
					} else 
						$tmp["drop_during"] = "";
				}
			} else {
				// NEXT MEMBER TYPE
				$this->db->select("B.member_type_name, CONCAT(A.start_date, ' - ', A.expiry_date) during_date, A.contract_no, A.drop_start, A.drop_end, A.member_ext_id, A.is_paid");
				$this->db->from("member_ext A");
				$this->db->join("member_type B", "A.member_type_id = B.member_type_id", "INNER");
				$this->db->where("A.is_void", "0");
				$this->db->where("A.is_paid", "1");
				$this->db->where("A.member_id", $tmp["member_id"]);
				$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') < STR_TO_DATE(A.start_date,'%d/%m/%Y')");
				$this->db->order_by("A.start_date");
				$result = $this->db->get();
				//echo $this->db->last_query() . "\r\n\r\n";
				if($result->num_rows() > 0){
					$tmp_row = $result->row(0);
					$tmp["current_member_type"] = "ยังไม่ถึงวันเริ่มต้น";
					if($tmp_row->is_paid == "0") $tmp["current_member_type"] .= ", ยังชำระไม่ครบ";
					
					$tmp["during_date"] = $tmp_row->during_date;
					$tmp["contract_no"] = $tmp_row->contract_no;
					
				} else {
					// LAST EXPIRED MEMBER TYPE
					$this->db->select("B.member_type_name, CONCAT(A.start_date, ' - ', A.expiry_date) during_date, A.contract_no, A.drop_start, A.drop_end, A.member_ext_id");
					$this->db->from("member_ext A");
					$this->db->join("member_type B", "A.member_type_id = B.member_type_id", "INNER");
					$this->db->where("A.is_void", "0");
					$this->db->where("A.is_paid", "1");
					$this->db->where("A.member_id", $tmp["member_id"]);
					$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') > STR_TO_DATE(A.expiry_date,'%d/%m/%Y')");
					$this->db->order_by("A.start_date");
					$result = $this->db->get();
					//echo $this->db->last_query() . "\r\n\r\n";
					if($result->num_rows() > 0){
						$tmp["current_member_type"] = "หมดอายุแล้ว";
					}
				}
			}
			
			if($tmp["is_suspend"] == "1" && $tmp["member_suspend_id"] != ""){
				$this->db->select("reason, suspend_datetime, GET_NAME_BY_USER_ID(suspend_by) suspend_by");
				$this->db->from("member_suspend");
				$this->db->where("member_suspend_id", $tmp["member_suspend_id"]);
				$result = $this->db->get();
				//echo $this->db->last_query() . "\r\n\r\n";
				if($result->num_rows() == 1){
					$row = $result->row(0);
					$tmp["suspend_reason"] = $row->reason;
					$tmp["suspend_since"] = $row->suspend_datetime;
					$tmp["suspend_by"] = $row->suspend_by;
				} else {
					$tmp["suspend_reason"] = null;
					$tmp["suspend_since"] = null;
					$tmp["suspend_by"] = null;
				}
			} else {
				$tmp["suspend_reason"] = null;
				$tmp["suspend_since"] = null;
				$tmp["suspend_by"] = null;
			}
			
			$output[] = $tmp;
		}
		
		/*$name_str = "";
		$val_str = "";
		$output_str = "";
		
		foreach($output as $row){
			$name_str = "";
			$val_str = "";
			foreach($row as $key => $val){
				$name_str .= $key . ", ";
				$val_str .= "'" . $val . "', ";
			}
			$name_str = substr($name_str, 0, strlen($name_str) - 2);
			$val_str = substr($val_str, 0, strlen($val_str) - 2);
			$output_str .= "INSERT INTO TEST (" . $name_str . ") VALUES (" . $val_str . ")\r\n";
		}
		echo $output_str;*/
		echo json_encode(Array("result" => $output, "total_record" => $recordCount));
	}
	
	public function getMemberData()
	{
		validateFields(array("member_id"));
		
		$this->db->select("*");
		$this->db->from("member");
		$this->db->where('member_id = ' . $this->input->post('member_id'));
		
		$data = $this->db->get();
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			foreach($item as $key => $val){
				$output["$key"] = $val;
			}
			$this->db->select("B.member_type_name, CONCAT(A.start_date, ' - ', A.expiry_date) during_date, A.contract_no");
			$this->db->from("member_ext A");
			$this->db->join("member_type B", "A.member_type_id = B.member_type_id", "INNER");
			$this->db->where("A.member_id", $this->input->post('member_id'));
			$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') BETWEEN STR_TO_DATE(A.start_date,'%d/%m/%Y') AND STR_TO_DATE(A.expiry_date,'%d/%m/%Y')");
			$this->db->order_by("A.member_ext_id DESC");
			$result = $this->db->get();
			if($result->num_rows() > 0){
				$row = $result->row(0);
				$output["current_member_type"] = $row->member_type_name;
				$output["during_date"] = $row->during_date;
				$output["contract_no"] = $row->contract_no;
			} else {
				$output["current_member_type"] = "";
				$output["during_date"] = "";
				$output["contract_no"] = "";
			}
		}
		
		echo json_encode(array("result" => $output));
	}
	
	public function manageMember()
	{
		validateFields(
			array(
				"firstname_th", 
				"lastname_th", 
				"nickname_th", 
				"martial_status",
				"birthday",
				"document_type",
				"document_no",
				"gender",
				"mobile_phone",
				"create_by",
				"member_prefix"
			)
		);
		/*if($this->input->post('member_id') && !$this->input->post('member_no')) 
			exit('INVALID REQUEST !!');*/
		
		if($this->input->post('member_id') && $this->input->post('member_no'))
		{
			$this->db->select("*");
			$this->db->from("member");
			$this->db->where("member_no = '" . $this->input->post('member_no') . "'");
			$this->db->where('member_id != ' . $this->input->post('member_id'));
			
			$result = $this->db->get();
			if($result->num_rows() > 0) 
			{
				exit(json_encode(Array("result" => "false", "msg" => "หมายเลขสมาชิก '" . $this->input->post('member_no') . "' มีในฐานข้อมูลแล้ว !!")));
			}
		}
		
		$this->db->select("*");
		$this->db->from("member");
		$this->db->where("firstname_th = '" . $this->input->post('firstname_th') . "' AND lastname_th = '" . $this->input->post('lastname_th') . "'");
		if($this->input->post('member_id')) $this->db->where('member_id != ' . $this->input->post('member_id'));
		
		$result = $this->db->get();
		if($result->num_rows() > 0) 
		{
			exit(json_encode(Array("result" => "false", "msg" => "สมาชิก ชื่อ '" . $this->input->post('firstname_th') . " " . $this->input->post('lastname_th') . "' มีในฐานข้อมูลแล้ว !!")));
		}
		
		if($this->input->post('firstname_en') != "" && $this->input->post('lastname_en') != ""){
			$this->db->select("*");
			$this->db->from("member");
			$this->db->where("firstname_en = '" . $this->input->post('firstname_en') . "' AND lastname_en = '" . $this->input->post('lastname_en') . "'");
			if($this->input->post('member_id')) $this->db->where('member_id != ' . $this->input->post('member_id'));
			
			$result = $this->db->get();
			if($result->num_rows() > 0) 
			{
				exit(json_encode(Array("result" => "false", "msg" => "สมาชิก ชื่อ '" . $this->input->post('firstname_en') . " " . $this->input->post('lastname_en') . "' มีในฐานข้อมูลแล้ว !!")));
			}
		}
		
		$data = array();
		
		foreach($this->input->post() as $key => $val){
			if($key != "member_prefix" && $key != "member_id")
			{
				if($key == "create_branch_id")
				{
					if(!$this->input->post('member_id'))
						$data[$key] = $val;
				}
				else
				{
					$data[$key] = $val;
				}
			}
		}
		
		$this->db->trans_start();
		if(!$this->input->post('member_id')){
			$data["create_date"] = NOW();
			$this->db->insert('member', $data); 
		} else {
			$data["last_modified"] = NOW();
			$this->db->where('member_id', $this->input->post('member_id'));
			$this->db->update('member', $data); 
		}
		
		//echo $this->db->last_query() . "<BR>";
		
		$member_id = "";
		if(!$this->input->post('member_id')) {
			$member_id = $this->db->insert_id();
		}
		else {
			$member_id = $this->input->post('member_id');
		}
		
		$this->db->select("member_no");
		$this->db->where("member_id", $member_id);
		$result = $this->db->get("member");
		$row = $result->row(0);
		$member_no = $row->member_no;
		
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE) echo json_encode("TRANSACTION INCOMPLETE !!");
		else echo json_encode(Array("result" => "true", "member_id" => $member_id, "member_no" => $member_no));
	}
	
	public function manageMemberExt(){
		validateFields(array(
			"member_id", 
			"member_type_id", 
			"full_amount", 
			"start_date", 
			"expiry_date", 
			"is_paid", 
			"is_deposit", 
			"seller_emp_id", 
			"branch_id", 
			"ext_by",
			"net_price_before_vat",
			"vat",
			"vat_amount",
			"current_total_paid",
			"allow_branch_id",
			"isForceEdit"
		));
		if($this->input->post('is_paid') === "0" && $this->input->post('payment_list') === null) 
			exit('INVALID REQUEST !!');
		
		if($this->input->post('isForceEdit') === '0'){
			$this->db->where("is_void", "0");
			$this->db->where("is_paid", "1");
			$this->db->where("STR_TO_DATE('" . $this->input->post('start_date') . "','%d/%m/%Y') BETWEEN STR_TO_DATE(start_date,'%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y')");
			$this->db->where("member_id", $this->input->post("member_id"));
			$overlapsed = $this->db->get("member_ext");
			//echo $this->db->last_query();
			if($overlapsed->num_rows() > 0)
				exit(json_encode(array("result"=>"ERROR", "error"=>"วันที่เริ่มต้นสมาชิก " . $this->input->post('start_date') . " ทับกับข้อมูล member อื่นที่ซื้อไว้ !!")));
			
			$this->db->where("is_void", "0");
			$this->db->where("is_paid", "1");
			$this->db->where("STR_TO_DATE('" . $this->input->post('expiry_date') . "','%d/%m/%Y') BETWEEN STR_TO_DATE(start_date,'%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y')");
			$this->db->where("member_id", $this->input->post("member_id"));
			$overlapsed = $this->db->get("member_ext");
			if($overlapsed->num_rows() > 0)
				exit(json_encode(array("result"=>"ERROR", "error"=>"วันที่สิ้นสุดสมาชิก " . $this->input->post('expiry_date') . " ทับกับข้อมูล member อื่นที่ซื้อไว้ !!")));
		}
		
		$this->db->trans_start();
		$now = NOW();
		$data = array(
			"branch_id" => $this->input->post('branch_id'),
			"member_id" => $this->input->post('member_id'),
			"member_type_id" => $this->input->post('member_type_id'),
			"full_amount" => $this->input->post('full_amount'),
			"contract_no" => $this->input->post('contract_no'),
			"start_date" => $this->input->post('start_date'),
			"expiry_date" => $this->input->post('expiry_date'),
			"allow_branch_id" => $this->input->post('allow_branch_id'),
			"ext_datetime" => $now,
			"ext_by" => $this->input->post('ext_by'),
			"seller_emp_id" => $this->input->post('seller_emp_id'),
			"is_paid" => $this->input->post('is_paid'),
			"net_price_before_vat" => $this->input->post('net_price_before_vat'),
			"vat" => $this->input->post('vat'),
			"vat_amount" => $this->input->post('vat_amount'),
			"note" => $this->input->post("note")
		);
		
		if($this->input->post('isForceEdit') === '1'){
			if($this->input->post('seller_emp_id') === '0')
				$data['seller_emp_id'] = NULL;
			unset($data['net_price_before_vat']);
			unset($data['vat']);
			unset($data['vat_amount']);
			unset($data['is_paid']);
			unset($data['ext_datetime']);
			unset($data['ext_by']);
		}
		
		if($this->input->post('isForceEdit') === '0'){
			if($this->input->post('discount_amount') !== NULL && $this->input->post('discount_by') !== NULL && $this->input->post('discount_note')){
				$data['discount_amount'] = $this->input->post('discount_amount');
				$data['discount_by'] = $this->input->post('discount_by');
				$data['discount_note'] = $this->input->post('discount_note');
			}
			
			$bill_id = get_bill($this->input->post('branch_id'))['bill_id'];
			if($this->input->post('is_deposit') === "1")
			{
				$data['bill_deposit_id'] = $bill_id;
				$data['receive_deposit_by'] = $this->input->post('ext_by');
				$data['receive_deposit_datetime'] = $now;
				$data['total_deposit_amount'] = $this->input->post('current_total_paid');
			} else {
				$data['bill_full_id'] = $bill_id;
				$data['receive_full_by'] = $this->input->post('ext_by');
				$data['receive_full_datetime'] = $now;
				$data['total_full_payment_amount'] = $this->input->post('current_total_paid');
			}
		}
		
		$member_ext_id = "";
		if(!$this->input->post('member_ext_id')){
			$this->db->insert('member_ext', $data);
			$member_ext_id = $this->db->insert_id();
		} else {
			$this->db->where('member_ext_id', $this->input->post('member_ext_id'));
			$this->db->update('member_ext', $data); 
			$member_ext_id = $this->input->post('member_ext_id');
		}
		
		if($this->input->post('isForceEdit') === '0'){
			if($bill_id != ""){
				$this->db->where('bill_id', $bill_id);
				$bill_data = array('src_id' => $member_ext_id);
				if($this->input->post('is_deposit') == "1")
					$bill_data['src_type'] = 1;
				else
					$bill_data['src_type'] = 2;
				
				$this->db->update('bill', $bill_data);
			}
			
			$payment_list = explode('!!', $this->input->post('payment_list'));
			foreach($payment_list as $list){
				$payment_data = explode('##', $list);
				$data = array(
					"member_ext_id" => $member_ext_id,
					"branch_id" => $this->input->post('branch_id'),
					"is_deposit" => $this->input->post('is_deposit'),
					"payment_type" => $payment_data[1],
					"amount" => $payment_data[2],
					"card_no" => $payment_data[3],
					"card_expiry_date" => $payment_data[4],
					"received_by" => $this->input->post('ext_by'),
					"received_datetime" => $now
				);
				$this->db->insert('member_ext_payment', $data);
			}
		}
		
		// GEN MEMBER NO
		if($this->input->post('is_paid') == "1" && $this->input->post('isForceEdit') === '0'){
			$this->db->select("A.member_no, B.prefix");
			$this->db->from("member A");
			$this->db->join("branch B", "A.create_branch_id = B.branch_id", "INNER");
			$this->db->where("A.member_id", $this->input->post('member_id'));
			$member = $this->db->get();
			$member = $member->row(0);
			if($member->member_no == ""){
			
				$this->db->select("IFNULL(MAX(member_no), 0) max_member_no");
				$this->db->where("SUBSTR(member_no, 1, 2) = '" . strtoupper($member->prefix) . "'");
				$this->db->limit(1);
				$this->db->order_by("member_id DESC");
				$result = $this->db->get("member");
				$row = $result->row(0);
				
				$member_no = "";
				if($row->max_member_no == "0") $member_no = strtoupper($member->prefix) . "00001";
				else {
					$next_member_no = substr($row->max_member_no, 2) + 1;
					$member_no = strtoupper($member->prefix) . str_pad($next_member_no, 5, "0", STR_PAD_LEFT);
				}
				
				if($member_no != ""){
					$this->db->set('member_no', $member_no);
					$this->db->where('member_id', $this->input->post('member_id'));
					$this->db->update('member');
				}
			}
		}
		
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		
		$this->db->select("B.member_type_name, CONCAT(A.start_date, ' - ', A.expiry_date) during_date, A.contract_no");
		$this->db->from("member_ext A");
		$this->db->join("member_type B", "A.member_type_id = B.member_type_id", "INNER");
		$this->db->where("A.member_ext_id", $member_ext_id);
		$this->db->order_by("A.member_ext_id DESC");
		$result = $this->db->get();
		$data = $result->row(0);
		
		echo json_encode(Array("result" => "true", "member_ext_id" => $member_ext_id, "current_member_type" => $data->member_type_name, "during_date" => $data->during_date, "contract_no" => $data->contract_no));
	}
	
	public function MemberExtChangeBranch(){
		validateFields(array("member_ext_id", "allow_branch_id"));
		
		$this->db->trans_start();
		
		$this->db->set('allow_branch_id', $this->input->post('allow_branch_id'));
		$this->db->where('member_ext_id', $this->input->post("member_ext_id"));
		$this->db->update('member_ext');
		
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	public function Drop(){
		validateFields(array("member_id", "drop_start", "drop_end", "drop_day_amount", "branch_id", "drop_by", "drop_note"));
		
		$this->db->trans_start();
		
		$data = array(
			"member_id" => $this->input->post("member_id"),
			"drop_start" => $this->input->post('drop_start'),
			"drop_end" => $this->input->post('drop_end'),
			"drop_day_amount" => $this->input->post('drop_day_amount'),
			"drop_note" => $this->input->post('drop_note'),
			"drop_at_branch" => $this->input->post('branch_id'),
			"drop_by" => $this->input->post('drop_by'),
			"drop_datetime" => NOW()
		);
		
		$this->db->insert("member_drop", $data);
		
		// EXTEND EXPIRY DATE OF AFFECTED CONTRACT
		$this->db->select("member_ext_id, expiry_date");
		$this->db->from("member_ext");
		$this->db->where("member_id", $this->input->post('member_id'));
		$this->db->where("STR_TO_DATE(expiry_date,'%d/%m/%Y') >= STR_TO_DATE('" . $this->input->post('drop_start') . "','%d/%m/%Y')");
		$this->db->where("STR_TO_DATE(start_date,'%d/%m/%Y') <= STR_TO_DATE('" . $this->input->post('drop_start') . "','%d/%m/%Y')");
		$data = $this->db->get();
		foreach($data->result_array() as $row){
			$date = explode('/', $row["expiry_date"]);
			$expiry_date = date_create(($date[2] - 543) . '-' . $date[1] . '-' . $date[0]);
			date_add($expiry_date, date_interval_create_from_date_string($this->input->post('drop_day_amount') . ' days'));
			$expiry_date = date_format($expiry_date, 'd') . '/' . date_format($expiry_date, 'm') . '/' . (date_format($expiry_date, 'Y') + 543);
			
			$this->db->where("member_ext_id", $row["member_ext_id"]);
			$this->db->update("member_ext", array("expiry_date" => $expiry_date));
		}
		
		// EXTEND START & EXPIRY DATE OF FUTURE CONTRACT
		$this->db->select("member_ext_id, start_date, expiry_date");
		$this->db->from("member_ext");
		$this->db->where("STR_TO_DATE(expiry_date,'%d/%m/%Y') >= STR_TO_DATE('" . $this->input->post('drop_start') . "','%d/%m/%Y')");
		$this->db->where("STR_TO_DATE(start_date,'%d/%m/%Y') >= STR_TO_DATE('" . $this->input->post('drop_start') . "','%d/%m/%Y')");
		$this->db->where("member_id", $this->input->post('member_id'));
		$data = $this->db->get();
		foreach($data->result_array() as $row){
			$date = explode('/', $row["start_date"]);
			$start_date = date_create(($date[2] - 543) . '-' . $date[1] . '-' . $date[0]);
			date_add($start_date, date_interval_create_from_date_string($this->input->post('drop_day_amount') . ' days'));
			$start_date = date_format($start_date, 'd') . '/' . date_format($start_date, 'm') . '/' . (date_format($start_date, 'Y') + 543);
			
			$date = explode('/', $row["expiry_date"]);
			$expiry_date = date_create(($date[2] - 543) . '-' . $date[1] . '-' . $date[0]);
			date_add($expiry_date, date_interval_create_from_date_string($this->input->post('drop_day_amount') . ' days'));
			$expiry_date = date_format($expiry_date, 'd') . '/' . date_format($expiry_date, 'm') . '/' . (date_format($expiry_date, 'Y') + 543);
			
			$this->db->where("member_ext_id", $row["member_ext_id"]);
			$this->db->update("member_ext", array("start_date" => $start_date, "expiry_date" => $expiry_date));
		}
		
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	public function cancelDrop(){
		validateFields(array("member_id", "member_drop_id", "cancelled_by"));
		
		$member_ext_id = 0;
		$start_date = ""; 
		$expiry_date = "";
		$drop_start = "";
		$drop_end = "";
		$drop_day_amount = 0;
		
		$this->db->select("member_ext_id, drop_start, drop_end, start_date, expiry_date");
		$this->db->from("member_ext");
		$this->db->where("member_id", $this->input->post('member_id'));

		$data = $this->db->get();
		if($data->num_rows() == 0)
			exit(json_encode(Array("result" => "false", "msg" => "ไม่พบข้อมูลการซื้อ Member ของสมาชิกนี้ !!")));
		
		$this->db->select("*");
		$this->db->from("member_drop");
		$this->db->where("is_cancelled", "0");
		$this->db->where("member_id", $this->input->post("member_id"));
		$this->db->order_by("drop_start ASC");
		$drop_result = $this->db->get();
		
		if($drop_result->num_rows() > 0){
			$drop_row = $drop_result->row(0);
			
			if($drop_row->drop_start != "" && $drop_row->drop_end != "") {
				$this->db->select("*");
				$this->db->from("member_ext");
				$this->db->where("is_void", "0");
				$this->db->where("member_id", $this->input->post("member_id"));
				$this->db->where("(STR_TO_DATE('" . $drop_row->drop_start . "','%d/%m/%Y') BETWEEN STR_TO_DATE(start_date,'%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y') OR STR_TO_DATE('" . $drop_row->drop_end . "','%d/%m/%Y') BETWEEN STR_TO_DATE(start_date,'%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y'))");
				$this->db->order_by("start_date ASC");
				$ext_result = $this->db->get();
				
				if($ext_result->num_rows() == 0)
					exit(json_encode(Array("result" => "false", "msg" => "ยังไม่ได้ดรอป !!")));
				else {
					$drop_start = $drop_row->drop_start;
					$drop_end = $drop_row->drop_end;
					$drop_day_amount = $drop_row->drop_day_amount;
					
					$this->db->trans_start();
					foreach($ext_result->result_array() as $ext){
						$member_ext_id = $ext["member_ext_id"];
						$start_date = $ext["start_date"];
						$expiry_date = $ext["expiry_date"];
						
						$date = explode('/', $start_date);
						$start_date = date_create(($date[2] - 543) . '-' . $date[1] . '-' . $date[0]);
						
						$date = explode('/', $expiry_date);
						$expiry_date = date_create(($date[2] - 543) . '-' . $date[1] . '-' . $date[0]);
						
						$date = explode('/', $drop_start);
						$drop_start = date_create(($date[2] - 543) . '-' . $date[1] . '-' . $date[0]);
						
						$date = explode('/', $drop_end);
						$drop_end = date_create(($date[2] - 543) . '-' . $date[1] . '-' . $date[0]);
						
						if($start_date > $drop_start){
							// MODIFY START DATE
							date_add($start_date, date_interval_create_from_date_string("-" . $drop_day_amount . ' days'));
							$start_date = date_format($start_date, 'd') . '/' . date_format($start_date, 'm') . '/' . (date_format($start_date, 'Y') + 543);
						} else 
							$start_date = date_format($start_date, "d/m/") . (date_format($start_date, "Y") + 543);
						
						// MODIFY EXPIRY DATE
						date_add($expiry_date, date_interval_create_from_date_string("-" . $drop_day_amount . ' days'));
						$expiry_date = date_format($expiry_date, 'd') . '/' . date_format($expiry_date, 'm') . '/' . (date_format($expiry_date, 'Y') + 543);
						
						$data = array(
							"start_date" => $start_date,
							"expiry_date" => $expiry_date
						);
						
						$this->db->where("member_ext_id", $member_ext_id);
						$this->db->update("member_ext", $data);
					}
					
					$data = array(
						"is_cancelled" => "1",
						"cancelled_by" => $this->input->post('cancelled_by'),
						"cancelled_datetime" => NOW()
					);
					
					$this->db->where("member_drop_id", $this->input->post("member_drop_id"));
					$this->db->update("member_drop", $data);
					
					$this->db->trans_complete();
		
					if ($this->db->trans_status() === FALSE) {
						echo json_encode("TRANSACTION INCOMPLETE !!");
						return;
					}
					echo json_encode(Array("result" => "true"));
				}
			} else 
				exit(json_encode(Array("result" => "false", "msg" => "ยังไม่ได้ดรอป !!")));
		}
	}
	
	public function getDropData()
	{
		validateFields(array("member_ext_id"));
		
		$this->db->select("drop_start, drop_day_amount, drop_end, drop_note");
		$this->db->from("member_ext");
		$this->db->where("member_ext_id", $this->input->post('member_ext_id'));
		
		$data = $this->db->get();
		
		$output = Array();
		
		if($data->num_rows() > 0){
		
			foreach($data->result_array() as $item){
				foreach($item as $key => $val)
					$output["$key"] = $val;
			}
		}
		// RETURN
		echo json_encode(array("result" => $output));
	}
	
	public function changeDrop()
	{
		validateFields(array("member_ext_id", "drop_start", "drop_end", "drop_day_amount", "branch_id", "drop_by", "drop_note"));
		
		$this->db->trans_start();
		
		$this->db->select("member_ext_id, droop_day_amount");
		$this->db->from("member_ext");
		$this->db->where("member_ext_id", $this->input->post('member_ext_id'));
		
		$data = $this->db->get();
		if($data->num_rows() == 0)
			exit(json_encode(Array("result" => "false", "msg" => "สมาชิกหมดอายุไปแล้ว หรือ ยังไม่ถึงวันเริ่มต้น !!")));
		
		$data = $data->row(0);
		$member_ext_id = $this->input->post("member_ext_id");
		$old_drop_day_amount = $data->drop_day_amount;
		$day_diff = $this->input->post("drop_day_amount") - $old_drop_day_amount;
		
		$data = array(
			"drop_start" => $this->input->post('drop_start'),
			"drop_end" => $this->input->post('drop_end'),
			"drop_day_amount" => $this->input->post('drop_day_amount'),
			"drop_note" => $this->input->post('drop_note'),
			"drop_at_branch" => $this->input->post('branch_id'),
			"drop_by" => $this->input->post('drop_by'),
			"drop_datetime" => NOW()
		);
		
		$this->db->where("member_ext_id", $member_ext_id);
		$this->db->update("member_ext", $data);
		
		// UPDATE EXPIRY DATE OF CURRENT CONTRACT
		$sql = "UPDATE member_ext SET expiry_date = DATE_ADD(expiry_date, INTERVAL " . $day_diff . " DAYS) WHERE member_ext_id = " . $member_ext_id;
		$this->db->query($sql);
		
		// UPDATE START & EXPIRY DATE OF FUTURE CONTRACT
		$sql = "UPDATE member_ext SET start_date = DATE_ADD(start_date, INTERVAL " . $day_diff . " DAYS), expiry_date = DATE_ADD(expiry_date, INTERVAL " . $day_diff . " DAYS) WHERE STR_TO_DATE(start_date,'%d/%m/%Y') >= STR_TO_DATE('" . $this->input->post('drop_start') . "','%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y') >= STR_TO_DATE('" . $this->input->post('drop_start') . "','%d/%m/%Y')";
		$this->db->query($sql);
		
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	public function getExtData(){
		validateFields(array("member_id"));
		
		$this->db->select("B.member_type_name, B.member_type_id, A.*");
		$this->db->from("member_ext A");
		$this->db->join("member_type B", "A.member_type_id = B.member_type_id", "INNER");
		if($this->input->post('member_id') && !$this->input->post("member_ext_id")) // GET UNPAID DATA
		{
			$this->db->where("A.member_id", $this->input->post('member_id'));
			$this->db->where("A.is_paid", 0);
		}
		else if(!$this->input->post("member_id") && $this->input->post('member_ext_id')) // VIEW DATA
		{
			$this->db->where("A.member_ext_id", $this->input->post('member_ext_id'));
			$this->db->where("A.is_paid", 1);
		}
			
		$data = $this->db->get();
		//echo $this->db->last_query() . "\r\n\r\n";
		
		$output = Array();
		
		if($data->num_rows() > 0){
		
			foreach($data->result_array() as $item){
				foreach($item as $key => $val)
					$output["$key"] = $val;
			}
			
			// GET DEPOSIT
			$this->db->select("B.payment_type, B.amount, B.card_no, B.card_expiry_date, GET_NAME_BY_USER_ID(B.received_by) received_by_name, B.received_datetime, B.payment_type, B.member_ext_payment_id");
			$this->db->from("member_ext A");
			$this->db->join("member_ext_payment B", "A.member_ext_id = B.member_ext_id", "INNER");
			$this->db->where("A.member_ext_id", $output["member_ext_id"]);
			$this->db->where("B.is_deposit", 1);
			$this->db->order_by("B.member_ext_payment_id");
			$data = $this->db->get();
			
			$deposit_data = "";
			
			foreach($data->result_array() as $item){
				foreach($item as $key => $val){
					$deposit_data .= $val . "##";
				}
				$deposit_data .= "!!";
			}
			
			if(trim($deposit_data) != "")
				$deposit_data = substr($deposit_data, 0, strlen($deposit_data) - 2);
			
			$output["deposit_data"] = $deposit_data;
			
			// GET FULL PAYMENT
			$this->db->select("B.payment_type, B.amount, B.card_no, B.card_expiry_date, GET_NAME_BY_USER_ID(B.received_by) received_by_name, B.received_datetime, B.payment_type, B.member_ext_payment_id, A.seller_emp_id");
			$this->db->from("member_ext A");
			$this->db->join("member_ext_payment B", "A.member_ext_id = B.member_ext_id", "INNER");
			$this->db->where("A.member_ext_id", $output["member_ext_id"]);
			$this->db->where("B.is_deposit", 0);
			$this->db->order_by("B.member_ext_payment_id");
			$data = $this->db->get();
			
			$full_payment_data = "";
			
			foreach($data->result_array() as $item){
				foreach($item as $key => $val){
					$full_payment_data .= $val . "##";
				}
				$full_payment_data .= "!!";
			}
			
			if(trim($full_payment_data) != "")
				$full_payment_data = substr($full_payment_data, 0, strlen($full_payment_data) - 2);
			
			$output["full_payment_data"] = $full_payment_data;
		}
		
		// RETURN
		echo json_encode(array("result" => $output));		
	}
	
	public function getBuyPTData()
	{
		validateFields(array("member_pt_id"));
		
		$this->db->select("age, price, left_hours, max_hours, start_date, expiry_date, note, pt_seller_id, pt_emp_id");
		$this->db->from("member_pt");
		$this->db->where("member_pt_id", $this->input->post('member_pt_id'));
		
		$data = $this->db->get();
		
		$output = Array();
		
		if($data->num_rows() > 0){
		
			foreach($data->result_array() as $item){
				foreach($item as $key => $val)
					$output["$key"] = $val;
			}
			
			// GET PAYMENT
			$this->db->select("B.payment_type, B.amount, B.card_no, B.card_expiry_date, GET_NAME_BY_USER_ID(B.receive_by) received_by_name, B.receive_datetime, B.payment_type, B.member_pt_payment_id");
			$this->db->from("member_pt A");
			$this->db->join("member_pt_payment B", "A.member_pt_id = B.member_pt_id", "INNER");
			$this->db->where("A.member_pt_id", $this->input->post("member_pt_id"));
			$this->db->order_by("B.receive_datetime");
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
		}
		// RETURN
		echo json_encode(array("result" => $output));
	}
	
	public function getMemberDataByCardNo()
	{
		validateFields(array("card_no"));
		if($this->input->post('is_check_in') === "1" && !$this->input->post('branch_id')) 
			exit('INVALID REQUEST !!');
		
		$this->db->select("member_id, image_file, firstname_th, lastname_th, nickname_th, member_no, card_no");
		$this->db->from("member");
		$this->db->where("card_no", $this->input->post('card_no'));
		$this->db->or_where("member_no", $this->input->post('card_no'));
		$data = $this->db->get();
		if($data->num_rows() == 0){
			echo json_encode(array("result"=>"NOT FOUND", "msg"=>"ไม่พบลูกค้าที่มีเลขที่บัตรหรือรหัสสมาชิก '" . $this->input->post('card_no') . "' !!\r\n\r\nหรือลูกค้าอาจยังไม่เคยซื้อ member !!"));
			return;
		}
		$data = $data->row(0);
		
		/*if($this->input->post('is_check_in') === "0")
			exit(json_encode(array("result"=>"YES", "member_id" => $data->member_id, "member_name" => $data->firstname_th . " " . $data->lastname_th . ($data->nickname_th == "" ? "" : " (" . $data->nickname_th . ")"))));*/
		
		$output = array("member_id"=>$data->member_id, "image_file"=>$data->image_file, "firstname_th"=>$data->firstname_th, "lastname_th"=>$data->lastname_th, "nickname_th"=>$data->nickname_th, "member_no"=>$data->member_no, "card_no"=>$data->card_no, "member_type"=>'', "during_date"=>'');
		$member_id = $data->member_id;
		
		$this->db->select("drop_note");
		$this->db->where("member_id", $member_id);
		$this->db->where("drop_by IS NOT NULL");
		$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') BETWEEN STR_TO_DATE(drop_start,'%d/%m/%Y') AND STR_TO_DATE(drop_end,'%d/%m/%Y')");
		$data = $this->db->get("member_ext");
		//echo $this->db->last_query() . "\r\n\r\n";
		if($data->num_rows() > 0){
			$data = $data->row(0);
			$output["result"] = "NO";
			$output["msg"] = "ลูกค้า drop สมาชิกไว้ !!\r\n\r\nสาเหตุ : " . $data->drop_note;
			echo json_encode($output);
			return;
		}
		
		$this->db->select("is_paid");
		$this->db->from("member_ext");
		$this->db->where("member_id", $member_id);
		$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') BETWEEN STR_TO_DATE(start_date,'%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y')");
		$data = $this->db->get();
		//echo $this->db->last_query() . "\r\n\r\n";
		if($data->num_rows() > 0){
			$row = $data->row(0);
			if($row->is_paid == "0"){
				$output["result"] = "NO";
				$output["msg"] = "ลูกค้ายังชำระเงินไม่ครบ !!";
				echo json_encode($output);
				return;
			}
		} else {
			$this->db->select("A.start_date, B.member_type_name");
			$this->db->from("member_ext A");
			$this->db->join("member_type B", "A.member_type_id = B.member_type_id", "INNER");
			$this->db->where("A.member_id", $member_id);
			$this->db->where("STR_TO_DATE(A.start_date,'%d/%m/%Y') > STR_TO_DATE('" . NOW() . "','%Y-%m-%d')");
			$data = $this->db->get();
			//echo $this->db->last_query() . "\r\n\r\n";
			if($data->num_rows() > 0){
				$data = $data->row(0);
				$output["result"] = "NO";
				$output["msg"] = "ยังไม่ถึงวันเริ่มต้น member !!\r\n\r\nเริ่มต้นวันที่ " . $data->start_date . "\r\n\r\nประเภทสมาชิกที่กำลังจะเริ่ม : " . $data->member_type_name;
				echo json_encode($output);
				return;
			} else {
				$this->db->select("A.expiry_date, B.member_type_name");
				$this->db->from("member_ext A");
				$this->db->join("member_type B", "A.member_type_id = B.member_type_id", "INNER");
				$this->db->where("A.member_id", $member_id);
				$this->db->where("STR_TO_DATE(A.expiry_date,'%d/%m/%Y') < STR_TO_DATE('" . NOW() . "','%Y-%m-%d')");
				$data = $this->db->get();
				//echo $this->db->last_query() . "\r\n\r\n";
				if($data->num_rows() > 0){
					$data = $data->row(0);
					$output["result"] = "NO";
					$output["msg"] = "สมาชิกหมดอายุไปเมื่อ " . $data->expiry_date . "\r\n\r\nประเภทสมาชิกล่าสุด : " . $data->member_type_name;
					echo json_encode($output);
					return;
				}
			}
		}
		
		$this->db->select("member_type_id, allow_branch_id");
		$this->db->from("member_ext");
		$this->db->where("member_id", $member_id);
		$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') BETWEEN STR_TO_DATE(start_date,'%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y')");
		$this->db->order_by("member_ext_id DESC");
		$result = $this->db->get();
		//echo $this->db->last_query() . "\r\n\r\n";
		if($result->num_rows() > 0){
			$row = $result->row(0);
			$member_type_id = $row->member_type_id;
			$allow_branch_id = $row->allow_branch_id;
			$allow_branch_id = explode(',', $allow_branch_id);
			
			if(in_array($this->input->post("branch_id"), $allow_branch_id)){
				$this->db->select("A.start_date, A.expiry_date, B.member_type_name");
				$this->db->from("member_ext A");
				$this->db->join("member_type B", "A.member_type_id = B.member_type_id", "INNER");
				$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') BETWEEN STR_TO_DATE(start_date,'%d/%m/%Y') AND STR_TO_DATE(expiry_date,'%d/%m/%Y')");
				$this->db->where("A.is_paid = 1");
				$this->db->where("A.is_void = 0");
				$this->db->where("A.member_id", $member_id);
				$result = $this->db->get();
				$tmp = $result->row(0);
				
				$output["member_type"] = $tmp->member_type_name;
				$output["during_date"] = $tmp->start_date . " - " . $tmp->expiry_date;
				$output["result"] = "YES";
				$output["msg"] = "";
				echo json_encode($output);
				return;
			} else {
				$allowed_branch_name = "";
				
				$this->db->select("branch_name, prefix");
				$this->db->from("branch");
				$this->db->where_in("branch_id", $allow_branch_id);
				$data = $this->db->get();

				foreach($data->result_array() as $branch){
					$allowed_branch_name .= "- " . $branch["branch_name"] . "\r\n";
				}
			
				$output["result"] = "NO";
				$output["msg"] = "ลูกค้าไม่สามารถเข้าใช้งานสาขานี้ได้ !!\r\n\r\nสาขาที่ลูกค้าสามารถเข้าใช้ได้คือ ...\r\n" . $allowed_branch_name;
				echo json_encode($output);
				return;
			}
		} else {
			$output["result"] = "NO";
			$output["error"] = "ไม่พบข้อมูลสมาชิก ...\r\n- ลูกค้าอาจยังไม่ได้ซื้อ Member\r\n- ยังไม่ถึงวันเริ่มต้น\r\n- หมดอายุไปแล้ว";
			echo json_encode($output);
			return;
		}
	}
	
	public function memberCheckIn(){
		validateFields(array("member_id", "branch_id"));
		
		$data = array("member_id" => $this->input->post('member_id'), "branch_id" => $this->input->post('branch_id'), "checkin_datetime" => NOW());
		$this->db->trans_start();
		$this->db->insert('member_checkin', $data);
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		
		echo json_encode(Array("result" => "true"));
	}
	
	function BuyPT(){
		validateFields(array("member_id", "price", "hours", "age", "start_date","expiry_date", "note", "pt_emp_id", "branch_id", "process_by", "net_price_before_vat", "vat", "vat_amount", "isForceAdd", "isForceEdit"));
		if($this->input->post('is_already_paid') === "0" && $this->input->post('payment_list') === NULL) 
			exit('INVALID REQUEST !!');
		
		$this->db->trans_start();
		
		if($this->input->post('isForceAdd') === '0' && $this->input->post('isForceEdit') === '0')
			$bill_id = get_bill($this->input->post('branch_id'))['bill_id'];
		
		$amount_left = "";
		if($this->input->post('amount_left') !== NULL)
			$amount_left = $this->input->post('amount_left');
		else
			$amount_left = $this->input->post('hours');
		
		$data = array(
			"branch_id" => $this->input->post('branch_id'),
			"member_id" => $this->input->post('member_id'),
			"price" => $this->input->post('price'),
			"left_hours" => $amount_left,
			"max_hours" => $this->input->post('hours'),
			"age" => $this->input->post('age'),
			"start_date" => $this->input->post('start_date'),
			"expiry_date" => $this->input->post('expiry_date'),
			"note" => $this->input->post('note'),
			"pt_emp_id" => $this->input->post('pt_emp_id'),
			"pt_seller_id" => (($this->input->post('isForceAdd') === '0' && $this->input->post('isForceEdit') === '0') ? $this->input->post('pt_emp_id') : $this->input->post('seller_emp_id')),
			"process_by" => $this->input->post('process_by'),
			"process_datetime" => NOW(),
			"net_price_before_vat" => $this->input->post('net_price_before_vat'),
			"vat" => $this->input->post('vat'),
			"vat_amount" => $this->input->post('vat_amount')
		);
		
		if($this->input->post('isForceAdd') === '0' && $this->input->post('isForceEdit') === '0')
			$data['bill_id'] = $bill_id;
		else
		{
			unset($data['bill_id']);
			unset($data['net_price_before_vat']);
			unset($data['vat']);
			unset($data['vat_amount']);
			
			if($this->input->post('isForceEdit') === '1'){
				unset($data['branch_id']);
				unset($data['member_id']);
			}
		}
		
		if($this->input->post('isForceAdd') === '0' && $this->input->post('isForceEdit') === '0')
		{
			$this->db->insert('member_pt', $data);
			$member_pt_id = $this->db->insert_id();
			
			$this->db->where('bill_id', $bill_id);
			$this->db->update('bill', array('src_type' => 3, 'src_id' => $member_pt_id));
			
			if($this->input->post("isForceAdd") === '0' && !($this->input->post("is_already_paid") === "1" && $this->input->post('do_not_insert_member_pt_payment') === "1"))
			{
				$payment_list = explode('!!', $this->input->post('payment_list'));
				foreach($payment_list as $list){
					$payment_data = explode('##', $list);
					$data = array(
						"branch_id" => $this->input->post('branch_id'),
						"member_pt_id" => $member_pt_id,
						"payment_type" => $payment_data[0],
						"amount" => $payment_data[1],
						"card_no" => $payment_data[2],
						"card_expiry_date" => $payment_data[3],
						"receive_by" => $this->input->post('process_by'),
						"receive_datetime" => NOW()
					);
					$this->db->insert('member_pt_payment', $data);
				}
			}
		}
		else
		{
			if($this->input->post("isForceAdd") === '1'){
				$data["process_by"] = $this->input->post('process_by');
				$data["process_datetime"] = NOW();
				
				$this->db->insert('member_pt', $data);
				$member_pt_id = $this->db->insert_id();
			}
			
			if($this->input->post("isForceEdit") === '1'){
				$this->db->where('member_pt_id', $this->input->post('member_pt_id'));
				$this->db->update('member_pt', $data);
				$member_pt_id = $this->input->post('member_pt_id');
			}
		}
		
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true", "member_pt_id" => $member_pt_id));
	}
	
	function ChangePT(){
		validateFields(array("member_pt_id", "pt_emp_id"));
		
		$this->db->trans_start();
		$this->db->set('pt_emp_id', $this->input->post("pt_emp_id"));
		$this->db->where('member_pt_id', $this->input->post("member_pt_id"));
		$this->db->update('member_pt');
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE)
			exit(json_encode("TRANSACTION INCOMPLETE !!"));
		else
			exit(json_encode(Array("result" => "true")));
	}
	
	function UsePT(){
		validateFields(array("member_id", "pt_emp_id", "branch_id", "process_by"));
		
		// PT course between member & PT
		$this->db->where("member_id", $this->input->post('member_id'));
		$this->db->where("pt_emp_id", $this->input->post('pt_emp_id'));
		$this->db->where("is_void = 0");
		$data = $this->db->get("member_pt");
		
		if($data->num_rows() == 0) // NOT FOUND
			exit(json_encode(Array("result" => "false", "msg" => "ไม่พบข้อมูลการซื้อ PT ระหว่าง สมาชิก และ เทรนเนอร์ นี้ !!")));
		else { 
			// FOUND
			// COURSE PT HAS LEFT ?
			$this->db->where("left_hours > 0");
			$this->db->where("is_void = 0");
			$this->db->where("member_id", $this->input->post('member_id'));
			$this->db->where("pt_emp_id", $this->input->post('pt_emp_id'));
			$data = $this->db->get("member_pt");
			
			if($data->num_rows() == 0) // NO
				exit(json_encode(Array("result" => "false", "msg" => "PT ที่ลูกค้าซื้อไว้ หมดแล้ว !!")));
			else {
				// YES
				// HAS ACTIVE PT COURSE NOW ? ( START_DATE >= NOW <= EXPIRY_DATE & LEFT_HOURS > 0)
				$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') >= STR_TO_DATE(start_date,'%d/%m/%Y')");
				$this->db->where("STR_TO_DATE('" . NOW() . "','%Y-%m-%d') <= STR_TO_DATE(expiry_date,'%d/%m/%Y')");
				$this->db->where("left_hours > 0");
				$this->db->where("is_void = 0");
				$this->db->where("member_id", $this->input->post('member_id'));
				$this->db->where("pt_emp_id", $this->input->post('pt_emp_id'));
				$this->db->order_by("start_date ASC");
				$data = $this->db->get("member_pt");
				
				if($data->num_rows() > 0){
					// YES
					$row = $data->row(0);
					$member_pt_id = $row->member_pt_id;
					$data = array(
						"member_pt_id" => $member_pt_id,
						"use_at_branch" => $this->input->post('branch_id'),
						"member_id" => $this->input->post('member_id'),
						"pt_emp_id" => $this->input->post('pt_emp_id'),
						"use_datetime" => NOW(),
						"hours_detail" => $row->left_hours . "/" . $row->max_hours . " => " . ($row->left_hours - 1) . "/" . $row->max_hours,
						"process_by" => $this->input->post('process_by'),
						"process_datetime" => NOW()
					);
					
					$this->db->trans_start();
		
					$this->db->insert("member_use_pt", $data);
					
					$this->db->query("UPDATE member_pt SET left_hours = left_hours - 1 WHERE member_pt_id = " . $member_pt_id);
					
					$this->db->trans_complete();
					if ($this->db->trans_status() === FALSE) {
						echo json_encode("TRANSACTION INCOMPLETE !!");
						return;
					}
					
					$this->db->where("member_pt_id", $member_pt_id);
					$this->db->select("left_hours, max_hours, start_date, expiry_date");
					$data = $this->db->get("member_pt");
					$data = $data->row(0);
					exit(json_encode(Array("result" => "true", "msg" => "คอร์ส PT ที่ตัดไป :\r\n" . $row->max_hours . " ชม. (" . $row->start_date . " - " . $row->expiry_date . ")\r\n\r\nจำนวน ชม. คอร์ส PT ที่เหลือ : " . $data->left_hours . "/" . $data->max_hours . " ชม.")));
				} else {
					// NO
					exit(json_encode(Array("result" => "false", "msg" => "PT ที่ลูกค้าซื้อไว้ หมดอายุไปหมดแล้ว และ/หรือ ยังไม่ถึงวันเริ่มต้น !!")));
				}
			}
		}
	}
	
	public function getHistoryCheckIn(){
		validateFields(array("member_id"));
			
		$this->db->select("A.checkin_datetime, B.branch_name");
		$this->db->from("member_checkin A");
		$this->db->join("branch B", "A.branch_id = B.branch_id", "INNER");
		$this->db->where("A.member_id", $this->input->post("member_id"));
		if($this->input->post('branch_id'))
			$this->db->where("A.branch_id", $this->input->post('branch_id'));
		if($this->input->post('since'))
			$this->db->where("STR_TO_DATE(SUBSTR(A.checkin_datetime, 1, 10), '%Y-%m-%d') >= STR_TO_DATE('" . $this->input->post('since') . "','%d/%m/%Y')");
		if($this->input->post('until'))
			$this->db->where("STR_TO_DATE(SUBSTR(A.checkin_datetime, 1, 10), '%Y-%m-%d') <= STR_TO_DATE('" . $this->input->post('until') . "','%d/%m/%Y')");
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$this->db->order_by("A.checkin_datetime DESC, B.branch_name");
		
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
	
	public function getHistoryPayment(){
		validateFields(array("member_id"));
		
		$query = "
		SELECT 
			X.detail, 
			X.datetime,
			X.cash_amount,
			X.card_amount,
			X.card_no,
			X.card_expiry_date,
			Y.branch_name, 
			GET_NAME_BY_USER_ID(X.process_by) process_by,
			X.priority
		FROM (
			(
				SELECT
					A.member_id,
					CONCAT('มัดจำต่ออายุสมาชิก - ', A2.member_type_name, ' ราคา ', FORMAT(A.full_amount, 0), ' บาท.') detail,
					A.receive_deposit_datetime datetime,
					A.branch_id,
					( SELECT FORMAT(SUM(amount), 0) cash_amount FROM member_ext_payment B WHERE A.member_ext_id = B.member_ext_id AND payment_type = 0 AND is_deposit = 1 ) cash_amount,
					( SELECT group_concat(FORMAT(amount, 0) SEPARATOR '+') card_amount FROM member_ext_payment C WHERE A.member_ext_id = C.member_ext_id AND payment_type = 1 AND is_deposit = 1 ) card_amount,
					( SELECT group_concat(card_no SEPARATOR ', ') card_no FROM member_ext_payment C WHERE A.member_ext_id = C.member_ext_id AND payment_type = 1 AND is_deposit = 1 ) card_no,
					( SELECT group_concat(card_expiry_date SEPARATOR ', ') card_expiry_date FROM member_ext_payment C WHERE A.member_ext_id = C.member_ext_id AND payment_type = 1 AND is_deposit = 1 ) card_expiry_date,
					A.ext_by process_by,
					1 priority,
					A.is_void
				FROM member_ext A
				INNER JOIN member_type A2 ON A.member_type_id = A2.member_type_id
			)
			UNION ALL
			(
				SELECT
					A.member_id,
					CONCAT('ชำระค่าสมาชิกเต็มจำนวน / ส่วนที่เหลือ - ', A2.member_type_name, ' ราคา ', FORMAT(A.full_amount, 0), ' บาท.') detail,
					A.receive_full_datetime datetime,
					A.branch_id,
					( SELECT FORMAT(SUM(amount), 0) cash_amount FROM member_ext_payment B WHERE A.member_ext_id = B.member_ext_id AND payment_type = 0 AND is_deposit = 0 ) cash_amount,
					( SELECT group_concat(FORMAT(amount, 0) SEPARATOR '+') card_amount FROM member_ext_payment C WHERE A.member_ext_id = C.member_ext_id AND payment_type = 1 AND is_deposit = 0 ) card_amount,
					( SELECT group_concat(card_no SEPARATOR ', ') card_no FROM member_ext_payment C WHERE A.member_ext_id = C.member_ext_id AND payment_type = 1 AND is_deposit = 0 ) card_no,
					( SELECT group_concat(card_expiry_date SEPARATOR ', ') card_expiry_date FROM member_ext_payment C WHERE A.member_ext_id = C.member_ext_id AND payment_type = 1 AND is_deposit = 0 ) card_expiry_date,
					A.ext_by process_by,
					2 priority,
					A.is_void
				FROM member_ext A
				INNER JOIN member_type A2 ON A.member_type_id = A2.member_type_id
			)
			UNION ALL
			(
				SELECT
					A.member_id,
					CONCAT('ซื้อ PT - ', A.max_hours, ' ชม. ', FORMAT(A.price, 0) ,' บาท. - PT ผู้ขาย : ', CONCAT(A2.fullname, ' ( ', A2.nickname, ' )')) detail,
					A.process_datetime datetime,
					A.branch_id,
					( SELECT FORMAT(SUM(amount), 0) cash_amount FROM member_pt_payment B WHERE A.member_pt_id = B.member_pt_id AND payment_type = 0 ) cash_amount,
					( SELECT group_concat(FORMAT(amount, 0) SEPARATOR '+') card_amount FROM member_pt_payment C WHERE A.member_pt_id = C.member_pt_id AND payment_type = 1 ) card_amount,
					( SELECT group_concat(card_no SEPARATOR ', ') card_no FROM member_pt_payment C WHERE A.member_pt_id = C.member_pt_id AND payment_type = 1 ) card_no,
					( SELECT group_concat(card_expiry_date SEPARATOR ', ') card_expiry_date FROM member_pt_payment C WHERE A.member_pt_id = C.member_pt_id AND payment_type = 1 ) card_expiry_date,
					A.process_by process_by,
					1 priority,
					A.is_void
				FROM member_pt A
				INNER JOIN employee A2 ON A.pt_seller_id = A2.emp_id
			)
		) X
		INNER JOIN branch Y ON X.branch_id = Y.branch_id
		LEFT OUTER JOIN user Z1 ON X.process_by = Z1.user_id
		LEFT OUTER JOIN employee Z2 ON Z1.emp_id = Z2.emp_id
		WHERE X.member_id = " . $this->input->post('member_id') . " AND (cash_amount is not null OR card_amount is not null) AND X.is_void = 0";
		
		if($this->input->post('branch_id'))
			$query .= " AND X.branch_id = " . $this->input->post('branch_id');
		if($this->input->post('since'))
			$query .= " AND STR_TO_DATE(SUBSTR(X.datetime, 1, 10), '%Y-%m-%d') >= STR_TO_DATE('" . $this->input->post('since') . "','%d/%m/%Y')";
		if($this->input->post('until'))
			$query .= " AND STR_TO_DATE(SUBSTR(X.datetime, 1, 10), '%Y-%m-%d') <= STR_TO_DATE('" . $this->input->post('until') . "','%d/%m/%Y')";
		
		$query .= " ORDER BY X.datetime DESC, priority";
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
		}
		
		$data = $this->db->query($query);
		//echo $this->db->last_query() . "\r\n\r\n";
		
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
	
	public function getHistoryBuyMember(){
		validateFields(array("member_id"));
		
		$this->db->select("A.member_ext_id, A.ext_datetime datetime, B.member_type_name, A.start_date, A.expiry_date, A.full_amount, A.discount_amount, GET_NAME_BY_USER_ID(A.discount_by) discount_by, A.discount_note, A.allow_branch_id, A.is_paid, A.seller_emp_id, A.is_void, GET_NAME_BY_USER_ID(A.void_by) void_by, A.void_datetime, A.void_reason");
		$this->db->from("member_ext A");
		$this->db->join("member_type B", "A.member_type_id = B.member_type_id", "INNER");		
		$this->db->where("A.member_id", $this->input->post('member_id'));
		
		if($this->input->post('member_type_id'))
			$this->db->where("A.member_type_id", $this->input->post('member_type_id'));
		if($this->input->post('since'))
			$this->db->where("STR_TO_DATE(SUBSTR(A.ext_datetime, 1, 10), '%Y-%m-%d') >= STR_TO_DATE('" . $this->input->post('since') . "','%d/%m/%Y')");
		if($this->input->post('until'))
			$this->db->where("STR_TO_DATE(SUBSTR(A.ext_datetime, 1, 10), '%Y-%m-%d') <= STR_TO_DATE('" . $this->input->post('until') . "','%d/%m/%Y')");
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$this->db->order_by("A.ext_datetime DESC");
		$data = $this->db->get();
		
		$output = Array();
		
		foreach($data->result_array() as $item){
			$tmp = array();
			foreach($item as $key => $val){
				if($key != "allow_branch_id") $tmp["$key"] = $val;
				else {
					$branch_list = explode(',', $val);
					$this->db->select("group_concat(prefix SEPARATOR ', ') branch_list");
					$this->db->where_in("branch_id", $branch_list);
					$branch = $this->db->get("branch");
					$tmp["$key"] = $branch->row(0)->branch_list;
				}
			}
			$output[] = $tmp;
		}
		
		echo json_encode(array("result" => $output, "total_record" => count($data->result_array())));
	}
	
	public function voidMemberExt(){
		validateFields(array("member_ext_id", "void_reason"));
		
		$data = array(
			"is_void" => 1,
			"void_by" => $this->input->post("void_by"),
			"void_datetime" => NOW(),
			"void_reason" => $this->input->post("void_reason")
		);
		
		$this->db->trans_start();
		
		$this->db->where("member_ext_id", $this->input->post('member_ext_id'));
		$this->db->update("member_ext", $data);
		
		$this->db->select("bill_deposit_id, bill_full_id");
		$this->db->from("member_ext");
		$this->db->where("member_ext_id", $this->input->post("member_ext_id"));
		$result = $this->db->get();
		$row = $result->row(0);
		$bill_deposit_id = $row->bill_deposit_id;
		$bill_full_id = $row->bill_full_id;
		
		$bill_id = array($bill_deposit_id, $bill_full_id);
		$this->db->where_in("bill_id", $bill_id);
		$this->db->update("bill", $data);
		
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		
		echo json_encode(Array("result" => "true"));
	}
	
	public function getHistoryBuyPT(){
		validateFields(array("member_id"));
		
		$this->db->select("A.process_datetime datetime, CONCAT(A.max_hours, ' ชม. ') pt_course_name, CONCAT(B.fullname, ' ( ', B.nickname, ' ) - ', B.emp_code) seller, CONCAT(C.fullname, ' ( ', C.nickname, ' ) - ', C.emp_code) trainer, CONCAT(left_hours, '/', max_hours) hours, A.start_date, A.expiry_date, A.member_pt_id, A.is_void, GET_NAME_BY_USER_ID(A.void_by) void_by, A.void_datetime, A.void_reason");
		$this->db->from("member_pt A");
		$this->db->join("employee B", "A.pt_seller_id = B.emp_id", "INNER");
		$this->db->join("employee C", "A.pt_emp_id = C.emp_id", "INNER");
		$this->db->where("A.member_id", $this->input->post('member_id'));
		
		if($this->input->post('since'))
			$this->db->where("STR_TO_DATE(SUBSTR(A.process_datetime, 1, 10), '%Y-%m-%d') >= STR_TO_DATE('" . $this->input->post('since') . "','%d/%m/%Y')");
		if($this->input->post('until'))
			$this->db->where("STR_TO_DATE(SUBSTR(A.process_datetime, 1, 10), '%Y-%m-%d') <= STR_TO_DATE('" . $this->input->post('until') . "','%d/%m/%Y')");
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$this->db->order_by("A.process_datetime DESC");
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
	
	public function voidMemberPT(){
		validateFields(array("member_pt_id", "void_reason"));
		
		$data = array(
			"is_void" => 1,
			"void_by" => $this->input->post("void_by"),
			"void_datetime" => NOW(),
			"void_reason" => $this->input->post("void_reason")
		);
		
		$this->db->trans_start();
		
		$this->db->where("member_pt_id", $this->input->post('member_pt_id'));
		$this->db->update("member_pt", $data);
		
		$this->db->select("bill_id");
		$this->db->from("member_pt");
		$this->db->where("member_pt_id", $this->input->post("member_pt_id"));
		$result = $this->db->get();
		$row = $result->row(0);
		$bill_id = $row->bill_id;
		
		if($bill_id != ""){
			$this->db->where("bill_id", $bill_id);
			$this->db->update("bill", $data);
		}
		
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		
		echo json_encode(Array("result" => "true"));
	}
	
	public function getHistoryPTUsage(){
		validateFields(array("member_id"));
		
		$this->db->select("A.use_datetime datetime, CONCAT(C.max_hours, ' ชม. ') pt_course_name, CONCAT(B.fullname, ' ( ', B.nickname, ' )') trainer, C.process_datetime, A.hours_detail");
		$this->db->from("member_use_pt A");
		$this->db->join("employee B", "A.pt_emp_id = B.emp_id", "INNER");
		$this->db->join("member_pt C", "A.member_pt_id = C.member_pt_id", "INNER");
		$this->db->where("A.member_id", $this->input->post('member_id'));
		if($this->input->post('since'))
			$this->db->where("STR_TO_DATE(SUBSTR(A.use_datetime, 1, 10), '%Y-%m-%d') >= STR_TO_DATE('" . $this->input->post('since') . "','%d/%m/%Y')");
		if($this->input->post('until'))
			$this->db->where("STR_TO_DATE(SUBSTR(A.use_datetime, 1, 10), '%Y-%m-%d') <= STR_TO_DATE('" . $this->input->post('until') . "','%d/%m/%Y')");
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$this->db->order_by("A.use_datetime DESC");
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
	
	public function getHistoryDrop(){
		validateFields(array("member_id"));
			
		$this->db->select("A.drop_start, A.drop_end, A.drop_note, GET_NAME_BY_USER_ID(A.drop_by) drop_by, A.drop_datetime, A.is_cancelled, GET_NAME_BY_USER_ID(A.cancelled_by) cancelled_by, A.cancelled_datetime, B.branch_name");
		$this->db->from("member_drop A");
		$this->db->join("branch B", "A.drop_at_branch = B.branch_id", "INNER");
		$this->db->where("A.member_id", $this->input->post('member_id'));
		if($this->input->post('since'))
			$this->db->where("STR_TO_DATE(A.drop_start, '%d/%m/%Y') >= STR_TO_DATE('" . $this->input->post('since') . "','%d/%m/%Y')");
		if($this->input->post('until'))
			$this->db->where("STR_TO_DATE(A.drop_end, '%d/%m/%Y') <= STR_TO_DATE('" . $this->input->post('until') . "','%d/%m/%Y')");
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$this->db->order_by("A.drop_datetime DESC, B.branch_name");
		
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
	
	public function getHistorySuspend()
	{
		validateFields(array("member_id"));
		
		$this->db->select("A.reason, A.suspend_datetime, GET_NAME_BY_USER_ID(A.suspend_by) suspend_by, A.cancel_suspend_note, A.cancel_suspend_datetime, GET_NAME_BY_USER_ID(A.cancel_suspend_by) cancel_suspend_by");
		$this->db->from("member_suspend A");
		$this->db->join("user B", "A.suspend_by = B.user_id", "LEFT OUTER");
		$this->db->join("employee C", "B.emp_id = C.emp_id", "LEFT OUTER");
		$this->db->join("user D", "A.cancel_suspend_by = D.user_id", "LEFT OUTER");
		$this->db->join("employee E", "D.user_id = E.emp_id", "LEFT OUTER");
		$this->db->where("A.member_id", $this->input->post('member_id'));

		if($this->input->post('since'))
			$this->db->where("(STR_TO_DATE(SUBSTR(A.suspend_datetime, 1, 10), '%Y-%m-%d') >= STR_TO_DATE('" . $this->input->post('since') . "','%d/%m/%Y') OR STR_TO_DATE(SUBSTR(A.cancel_suspend_datetime, 1, 10), '%Y-%m-%d') >= STR_TO_DATE('" . $this->input->post('since') . "','%d/%m/%Y'))");
		if($this->input->post('until'))
			$this->db->where("(STR_TO_DATE(SUBSTR(A.suspend_datetime, 1, 10), '%Y-%m-%d') <= STR_TO_DATE('" . $this->input->post('until') . "','%d/%m/%Y') OR STR_TO_DATE(SUBSTR(A.cancel_suspend_datetime, 1, 10), '%Y-%m-%d') <= STR_TO_DATE('" . $this->input->post('until') . "','%d/%m/%Y'))");
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$this->db->order_by("A.suspend_datetime DESC");
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
	
	public function Suspend()
	{
		validateFields(array("member_id", "reason", "suspend_by"));
		
		$data = array(
			"member_id" => $this->input->post('member_id'),
			"reason" => $this->input->post('reason'),
			"suspend_at_branch" => $this->input->post('branch_id'),
			"suspend_by" => $this->input->post('suspend_by'),
			"suspend_datetime" => NOW()
		);
		
		$this->db->trans_start();
		
		$this->db->insert("member_suspend", $data);
		$member_suspend_id = $this->db->insert_id();
		
		$data = array(
			"is_suspend" => 1,
			"member_suspend_id" => $member_suspend_id
		);
		$this->db->where("member_id", $this->input->post('member_id'));
		$this->db->update("member", $data);
		
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		
		echo json_encode(Array("result" => "true"));
	}
	
	public function cancelSuspend(){
		validateFields(array("member_id", "cancel_suspend_by"));
		
		$this->db->select("member_suspend_id");
		$this->db->from("member");
		$this->db->where("member_id", $this->input->post('member_id'));
		$result = $this->db->get();
		$member_suspend_id = "";
		if($result->num_rows() == 1){
			$row = $result->row(0);
			$member_suspend_id = $row->member_suspend_id;
		}
		if($member_suspend_id == "")
			exit(json_encode(Array("result" => "false", "msg" => "เกิดความผิดพลาด !!")));
		
		$this->db->trans_start();
		
		$data = array(
			"cancel_suspend_note" => $this->input->post('note'),
			"cancel_suspend_by" => $this->input->post('cancel_suspend_by'),
			"cancel_suspend_datetime" => NOW()
		);
		
		$this->db->where("member_suspend_id", $member_suspend_id);
		$this->db->update("member_suspend", $data);
		
		$data = array(
			"is_suspend" => "0",
			"member_suspend_id" => null
		);
		
		$this->db->where("member_id", $this->input->post('member_id'));
		$this->db->update("member", $data);
		
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	public function getExtBillDetail(){
		validateFields(array("member_ext_id", "issue_vat"));
		
		$field_list = "
			A.bill_full_id bill_id, 
			A.branch_id, 
			A.contract_no, 
			A.start_date, 
			A.expiry_date, 
			A.full_amount, 
			A.discount_amount, 
			A.discount_note, 
			A.is_paid, 
			A.total_deposit_amount, 
			A.total_full_payment_amount, 
			A.net_price_before_vat, 
			A.vat, 
			A.vat_amount, 
			GET_NAME_BY_USER_ID(A.receive_full_by) cashier_name, 
			CONCAT(B.firstname_th, ' ', B.lastname_th, ' (', B.nickname_th, ')') member_name, 
			C.member_type_name, 
			D.bill_no full_bill_no, 
			D.bill_datetime full_bill_datetime,
			E.bill_no deposit_bill_no, 
			E.bill_datetime deposit_bill_datetime";
		
		$this->db->select($field_list);
		$this->db->from("member_ext A");
		$this->db->join("member B", "A.member_id = B.member_id", "INNER");
		$this->db->join("member_type C", "A.member_type_id = C.member_type_id", "INNER");
		$this->db->join("bill D", "A.bill_full_id = D.bill_id", "INNER");
		$this->db->join("bill E", "A.bill_deposit_id = E.bill_id", "LEFT OUTER");
		$this->db->where("A.member_ext_id", $this->input->post('member_ext_id'));
		$data = $this->db->get();
		
		if($data->num_rows() == 0){
			exit(json_encode(Array("result" => "false", "msg" => "ไม่พบข้อมูล !!")));
		}
		
		$resultArr = Array();
		foreach($data->result_array() as $item){
			foreach($item as $key => $val){
				$resultArr[$key] = $val;
			}
		}
		
		$data = $data->row(0);
		$bill_id = $data->bill_id;
		
		$this->db->select("B.payment_type, B.amount, B.card_no, B.card_expiry_date, GET_NAME_BY_USER_ID(B.received_by) received_by_name, B.received_datetime");
		$this->db->from("member_ext A");
		$this->db->join("member_ext_payment B", "A.member_ext_id = B.member_ext_id", "INNER");
		$this->db->where("A.member_ext_id", $this->input->post('member_ext_id'));
		$this->db->where("B.is_deposit", "0");
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
		
		$resultArr["payment_data"] = $payment_data;
		
		if($this->input->post("issue_vat") == "1"){
			$this->db->trans_start();
			
			$data = array("issue_vat" => "1");
			$this->db->where("bill_id", $bill_id);
			$this->db->update("bill", $data);
			
			$this->db->trans_complete();
			
			if ($this->db->trans_status() === FALSE) {
				echo json_encode("TRANSACTION INCOMPLETE !!");
				return;
			}
		}
		
		exit(json_encode(Array("result" => $resultArr)));
	}
	
	public function getPTBillDetail(){
		validateFields(array("member_pt_id", "issue_vat"));
		
		$field_list = "
			A.bill_id, 
			A.branch_id, 
			A.expiry_date, 
			A.price, 
			A.max_hours,
			A.net_price_before_vat, 
			A.vat, 
			A.vat_amount, 
			GET_NAME_BY_USER_ID(A.process_by) cashier_name, 
			CONCAT(B.firstname_th, ' ', B.lastname_th, ' (', B.nickname_th, ')') member_name, 
			CONCAT(A.max_hours, ' ชม. ', ' ', FORMAT(A.price, 0) ,' บาท. ') pt_course_name,
			CONCAT(E.fullname, ' ( ', E.nickname, ' )') pt_name,
			D.bill_no, 
			D.bill_datetime";
		
		$this->db->select($field_list);
		$this->db->from("member_pt A");
		$this->db->join("member B", "A.member_id = B.member_id", "INNER");
		$this->db->join("bill D", "A.bill_id = D.bill_id", "INNER");
		$this->db->join("employee E", "A.pt_seller_id = E.emp_id", "INNER");
		$this->db->where("A.member_pt_id", $this->input->post('member_pt_id'));
		$data = $this->db->get();
		
		if($data->num_rows() == 0){
			exit(json_encode(Array("result" => "false", "msg" => "ไม่พบข้อมูล !!")));
		}
		
		$resultArr = Array();
		foreach($data->result_array() as $item){
			foreach($item as $key => $val){
				$resultArr[$key] = $val;
			}
		}
		
		$data = $data->row(0);
		$bill_id = $data->bill_id;
		
		$this->db->select("B.payment_type, B.amount, B.card_no, B.card_expiry_date");
		$this->db->from("member_pt A");
		$this->db->join("member_pt_payment B", "A.member_pt_id = B.member_pt_id", "INNER");
		$this->db->where("A.member_pt_id", $this->input->post('member_pt_id'));
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
		
		$resultArr["payment_data"] = $payment_data;
		
		if($this->input->post("issue_vat") == "1"){
			$this->db->trans_start();
			
			$data = array("issue_vat" => "1");
			$this->db->where("bill_id", $bill_id);
			$this->db->update("bill", $data);
			
			$this->db->trans_complete();
			
			if ($this->db->trans_status() === FALSE) {
				echo json_encode("TRANSACTION INCOMPLETE !!");
				return;
			}
		}
		
		exit(json_encode(Array("result" => $resultArr)));
	}
	
	public function changeCardNo()
	{
		validateFields(array("member_id", "card_no", "change_by"));
		
		$this->db->where("card_no", $this->input->post('card_no'));
		$this->db->where("member_id != " . $this->input->post('member_id'));
		$member_no = $this->db->get("member");
		if($member_no->num_rows() > 0)
			exit(json_encode(Array("result" => "false", "msg" => "เลขที่สมาชิก '" . $this->input->post('member_no') . "' มีอยู่ในฐานข้อมูลแล้ว !!")));
		
		$this->db->trans_start();
		
		$this->db->update('member', array("card_no" => $this->input->post("card_no")), "member_id = " . $this->input->post("member_id"));
		
		$data = array(
			"member_id" => $this->input->post("member_id"),
			"card_no" => $this->input->post("card_no"),
			"note" => $this->input->post("note"),
			"change_by" => $this->input->post("change_by"),
			"change_datetime" => NOW()
		);
		
		$this->db->set($data);
		$this->db->insert('card_no_log');
		
		$this->db->trans_complete();
		
		if ($this->db->trans_status() === FALSE) {
			echo json_encode("TRANSACTION INCOMPLETE !!");
			return;
		}
		echo json_encode(Array("result" => "true"));
	}
	
	public function getHistoryCardNo(){
		validateFields(array("member_id"));
			
		$this->db->select("change_datetime, card_no, note, GET_NAME_BY_USER_ID(change_by) change_by");
		$this->db->from("card_no_log");
		$this->db->where("member_id", $this->input->post("member_id"));
		if($this->input->post('since'))
			$this->db->where("STR_TO_DATE(change_datetime, '%Y-%m-%d') >= STR_TO_DATE('" . $this->input->post('since') . "','%d/%m/%Y')");
		if($this->input->post('until'))
			$this->db->where("STR_TO_DATE(change_datetime, '%Y-%m-%d') <= STR_TO_DATE('" . $this->input->post('until') . "','%d/%m/%Y')");
		
		if($this->input->post('page') && $this->input->post('recordCount')){
			$this->db->limit($this->recordCount, ($this->page - 1) * $this->recordCount);
			//$this->db->limit(($this->input->post('page') - 1) * $this->input->post('recordCount'), $this->input->post('recordCount'));
		}
		
		$this->db->order_by("change_datetime DESC");
		
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
}
?>