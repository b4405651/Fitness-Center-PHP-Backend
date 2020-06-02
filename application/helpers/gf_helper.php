<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if(!function_exists('NOW'))
{
	function NOW()
	{
		$t = microtime(true);
		$micro = sprintf("%06d",($t - floor($t)) * 1000000);
		return (date("Y") + 543) . "-" . date("m") . "-" . date("d") . " " . date("H:i:s") . "." . $micro;
	}
}

if(!function_exists('TODAY_UI'))
{
	function TODAY_UI($use_underscore = false)
	{
		if($use_underscore) return date("d") . "_" . date("m") . "_" . (date("Y") + 543);
		else return date("d") . "/" . date("m") . "/" . (date("Y") + 543);
	}
}

if(!function_exists('get_bill'))
{
	function get_bill($branch_id)
	{
		$CI =& get_instance();
		
		$CI->db->select("UPPER(prefix) branch_prefix");
		$CI->db->from("branch");
		$CI->db->where("branch_id", $branch_id);
		$data = $CI->db->get();
		$data = $data->row(0);
		$branch_prefix = $data->branch_prefix;
		
		$CI->db->select("IFNULL(MAX(bill_no), '" . $branch_prefix . (date('y') + 43) . date('m') . "00000') last_bill_no");
		$CI->db->from("bill");
		$CI->db->where("UPPER(SUBSTR(bill_no, 1, 6)) LIKE '" . $branch_prefix . (date('y') + 43) . date('m') . "'");
		$data = $CI->db->get();
		$data = $data->row(0);
		$bill_no = $data->last_bill_no;
		$bill_no = $branch_prefix . (date('y') + 43) . date('m') . str_pad(substr($bill_no, -5) + 1, 5, "0", STR_PAD_LEFT);
		$bill_datetime = NOW();
		$CI->db->insert('bill', array("bill_no" => $bill_no, "branch_id" => $branch_id, "bill_datetime" => $bill_datetime));
		return array("bill_id" => $CI->db->insert_id(), "bill_no" => $bill_no, "bill_datetime" => $bill_datetime);
	}
}

if(!function_exists('formatDBDateTime')){
	function formatDBDateTime($strDateTime, $onlyDay = false)
	{
		if (trim($strDateTime) == "") return "";
		if (strpos($strDateTime, ".") > -1)
                $strDateTime = substr($strDateTime, 0, strpos($strDateTime, "."));

		$tmp = explode(' ', $strDateTime);
		$date = explode('-', $tmp[0]);
		if(count($tmp) > 1)
			$time = explode(':', $tmp[1]);
		else
			$onlyDay = true;

		if($onlyDay)
			return $date[2] . "/" . $date[1] . "/" . $date[0];
		else
			return $date[2] . "/" . $date[1] . "/" . $date[0] . " " . $time[0] . ":" . $time[1];
	}
}

if(!function_exists('calculateAge')){
	function calculateAge($birthday)
	{
		if($birthday == "") return "";
		$birthday = explode("/", $birthday);
		$birthday = $birthday[0] . "/" . $birthday[1] . "/" . ($birthday[2] - 543);
		
		$age = DateTime::createFromFormat('d/m/Y', $birthday)->diff(new DateTime('now'));
		return $age->y . " ปี " . $age->m . " เดือน " . $age->d . " วัน";
		
		//get age from date or birthday
		//$age = (date("md", date("U", mktime(0, 0, 0, $birthday[0], $birthday[1], ($birthday[2] - 543)))) > date("md") ? ((date("Y") - ($birthday[2] - 543)) - 1) : (date("Y") - ($birthday[2] - 543)));
		//return $age;
	}
}

if(!function_exists('do_return'))
{
	function do_return($arr)
	{
		$CI =& get_instance();
		
		if(!is_web()) 
		{
			if(is_array($arr)) echo json_encode($arr);
			else echo $arr;
		}
		else return $arr;
	}
}

if(!function_exists('validateFields'))
{
	function validateFields($fieldList)
	{
		$CI =& get_instance();
		
		if(is_web())
			if(!is_logging_in() && !$CI->session->has_userdata('user_id'))
				redirect('/index.php');
		
		foreach($fieldList as $field)
		{
			if(!$CI->input->post($field) && ($field == "branch_id" && !is_web()))
				exit("INVALID REQUEST !!\r\n\r\n'" . $field . "' IS MISSING !!'");
		}
	}
}

if(!function_exists('is_web'))
{
	function is_web()
	{
		$CI =& get_instance();
		
		if($CI->agent->agent_string() != "FAMS") return true;
		else return false;
	}
}

if(!function_exists('is_logging_in'))
{
	function is_logging_in()
	{
		$CI =& get_instance();
		
		if($CI->agent->agent_string() != "FAMS" && !$CI->session->has_userdata('logging_in')) return false;
		else {
			if($CI->session->has_userdata('logging_in')){
				return ($CI->session->userdata('logging_in') == true);
			} else return false;
		}
	}
}

if(!function_exists('get_month_name'))
{
	function get_month_name($month_no, $getShort = false)
	{
		$full_month_name = array("มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน", "กรกฏาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม");
		$short_month_name = array("ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
		
		if($getShort) return $short_month_name[$month_no - 1];
		else return $full_month_name[$month_no - 1];
	}
}
?>