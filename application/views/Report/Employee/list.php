<div style="padding: 10px;">
<?php
$this->db->where("is_suspend", 0);
$branch_list = $this->db->get("branch");

$date_param = explode('/', $on_date);
if($mode == 0){
?>
<h1>รายชื่อ และ รายได้ ของ พนักงานทั่วไป</h1>
<form method="post">
<input type="hidden" name="mode" id="mode" value="<?=$mode;?>" />
<input type="hidden" name="report_name" value="employee_list" />
<table border=0 cellpadding=5 cellspacing=5 style="margin: 5px 0 5px 0;">
<tr class="thead">
	<td style="color: white;">พนักงานสังกัดสาขา</td>
</tr>
<tr>
	<td style="vertical-align: top; border: none;">
		<input type="checkbox" name="branch[]" value="-1" onclick="$('[name=\'branch[]\'][value!=-1]').attr('checked', false);"<?php if(in_array("-1", $branch) || count($branch) == 0) {?> checked<?php }?> />ทุกสาขา<br />
<?php
foreach($branch_list->result_array() as $branch_data){
?>
		<input type="checkbox" name="branch[]" value="<?=$branch_data["branch_id"]; ?>" onclick="$('[name=\'branch[]\'][value=-1]').attr('checked', false);"<?php if(in_array($branch_data["branch_id"], $branch)) { ?> checked<?php }?> /><?=$branch_data["branch_name"]; ?><br />
<?php
}
?>
	</td>
</tr>
<tr>
	<td>
		ณ วันที่ : <input name="on_date" class="datepicker" value="<?=$on_date;?>" />
	</td>
</tr>
</table>
<button onclick="$('#mode').val('<?=$mode;?>'); $('form').attr('target', '_self');">เรียกรายงาน</button>
<button onclick="$('#mode').val('2'); $('form').attr('target', '_self'); $('form').submit();">EXCEL</button>
<button onclick="$('#mode').val('3'); $('form').attr('target', '_blank'); $('form').submit();">PRINT</button>
</form>
<?php
} else {
?>
<?php
$branch_str = "";
if(count($branch) == 1 && $branch[0] == "-1") $branch_str = 'ทุกสาขา';
else
{
	foreach($branch_list->result_array() as $branch_data){
		if(in_array($branch_data["branch_id"], $branch)) $branch_str .= $branch_data["branch_name"] . ", ";
	}
	$branch_str = substr($branch_str, 0, strlen($branch_str) - 2);
}
?>
<div style="margin: 0; padding: 0;"><h1>รายชื่อ และ รายได้ ของ พนักงานทั่วไป</h1></div>
<br />
<div style="text-align: left"><b>พนักงานสังกัดสาขา : </b><?=$branch_str;?></div>
<br />
<div style="text-align: left"><b>ณ วันที่ : </b><?=$on_date;?></div>
<br />
<?php
}

if($this->input->post("branch") && $this->input->post("on_date")){
	$branch_id = array();
	if(count($branch) == 1 && $branch[0] == "-1") {
		foreach($branch_list->result_array() as $branch_data){
			array_push($branch_id, $branch_data["branch_id"]);
		}
	}
	else $branch_id = $branch;
?>
<table border=0 cellpadding=5 cellspacing=<?=($mode==0 ? '1 style="margin-top: 5px;"' : '0');?>>
<tr class="thead">
	<td style="vertical-align: middle; text-align: center;" rowspan=2>พนักงาน</td>
	<td style="vertical-align: middle; text-align: center;" rowspan=2>สังกัดสาขา</td>
	<td style="vertical-align: middle; text-align: center;"><?="วันที่ " . $date_param[0] . " " . get_month_name($date_param[1], true) . " $date_param[2]";?></td>
	<td style="vertical-align: middle; text-align: center;" colspan=2><?='สะสมทั้งเดือน ' . get_month_name($date_param[1], true) . ' ' . $date_param[2];?></td>
	<td style="vertical-align: middle; text-align: center;" colspan=2><?='สะสมทั้งปี ' . $date_param[2];?></td>
</tr>
<tr class="thead">
	<td style="vertical-align: middle; text-align: center; padding: 5px;">ยอดขาย Member</td>
	<td style="vertical-align: middle; text-align: center; padding: 5px;">ยอดขาย Member</td>
	<td style="vertical-align: middle; text-align: center; padding: 5px;">ค่าคอม Member</td>
	<td style="vertical-align: middle; text-align: center; padding: 5px;">ยอดขาย Member</td>
	<td style="vertical-align: middle; text-align: center; padding: 5px;">ค่าคอม Member</td>
</tr>
<?php
	$this->db->select("A.emp_id, A.fullname, A.nickname, B.branch_name");
	$this->db->from("employee A");
	$this->db->join("branch B", "A.branch_id = B.branch_id", "INNER");
	$this->db->where("A.is_trainer", 0);
	$this->db->where_in("A.branch_id", $branch_id);
	$this->db->order_by("A.fullname, A.nickname");
	$emp = $this->db->get();
	$total_record = $emp->num_rows();
	if($emp->num_rows() == 0)
	{
?>
<tr>
	<td colspan=7 style="vertical-align: middle; text-align: center;">ไม่มีข้อมูล</td>
</tr>
<?php
	} else {
		$count = 0;
		foreach($emp->result_array() as $emp_data){
			$count++;
?>
<tr class="tbody">
	<td style="text-align: left; vertical-align: middle;"><?=$count . ". " . $emp_data["fullname"] . " (" . $emp_data["nickname"] . ")"?></td>
	<td style="text-align: center; vertical-align: middle;"><?=$emp_data["branch_name"];?></td>
	<td>
<?php
			//ยอดขาย ณ วันที่
			$this->db->select("IFNULL(SUM(B.amount), 0) amount");
			$this->db->from("member_ext A");
			$this->db->join("member_ext_payment B", "A.member_ext_id = B.member_ext_id", "INNER");
			$this->db->where("A.seller_emp_id", $emp_data["emp_id"]);
			$this->db->where("STR_TO_DATE(received_datetime,'%Y-%m-%d') = STR_TO_DATE('$on_date','%d/%m/%Y')");
			$data = $this->db->get();
			$data = $data->row(0);
			echo number_format($data->amount);
?>
	</td>
	<td>
<?php
			//ยอดขาย สะสมทั้งเดือน
			$this->db->select("IFNULL(SUM(B.amount), 0) amount, COMM_MEMBER_AMOUNT(IFNULL(SUM(B.amount), 0)) comm_amount, COMM_MEMBER(IFNULL(SUM(B.amount), 0)) comm");
			$this->db->from("member_ext A");
			$this->db->join("member_ext_payment B", "A.member_ext_id = B.member_ext_id", "INNER");
			$this->db->where("A.seller_emp_id", $emp_data["emp_id"]);
			$this->db->where("STR_TO_DATE(received_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/$date_param[1]/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('" . cal_days_in_month(CAL_GREGORIAN,$date_param[1],($date_param[2] - 543)) . "/$date_param[1]/$date_param[2]','%d/%m/%Y')");
			$data = $this->db->get();
			$data = $data->row(0);
			echo number_format($data->amount);
?>
	</td>
	<td>
<?php
			//ค่าคอม ของ เดือน
			echo number_format(round($data->comm_amount, 2)) . " (" . $data->comm . "%)";
?>
	</td>
	<td>
<?php
			//ยอดขายสะสมทั้งปี
			$this->db->select("IFNULL(SUM(B.amount), 0) amount");
			$this->db->from("member_ext A");
			$this->db->join("member_ext_payment B", "A.member_ext_id = B.member_ext_id", "INNER");
			$this->db->where("A.seller_emp_id", $emp_data["emp_id"]);
			$this->db->where("STR_TO_DATE(received_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/01/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('31/12/$date_param[2]','%d/%m/%Y')");
			$data = $this->db->get();
			$data = $data->row(0);
			echo number_format($data->amount);
?>
	</td>
	<td>
<?php
			//ค่าคอม สะสมทั้งปี
			$sum_comm = 0;
			for($i=1; $i<=12; $i++)
			{
				$this->db->select("IFNULL(SUM(B.amount), 0) amount, COMM_MEMBER_AMOUNT(IFNULL(SUM(B.amount), 0)) comm_amount");
				$this->db->from("member_ext A");
				$this->db->join("member_ext_payment B", "A.member_ext_id = B.member_ext_id", "INNER");
				$this->db->where("A.seller_emp_id", $emp_data["emp_id"]);
				$this->db->where("STR_TO_DATE(received_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/$i/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('" . cal_days_in_month(CAL_GREGORIAN,$i,($date_param[2] - 543)) . "/$i/$date_param[2]','%d/%m/%Y')");
				$data = $this->db->get();
				$data = $data->row(0);
				$sum_comm += round($data->comm_amount, 2);
			}
			echo number_format($sum_comm);
?>
	</td>
</tr>
<?php
		}
	}
?>
</table>
<?php
	$this->load->view("Report/pagination", array("mode" => $mode, "page" => $page, "total_record" => $total_record));
}
?>
</div>
</body>
</html>