<div style="padding: 10px;">
<?php
$this->db->where("is_suspend", 0);
$branch_list = $this->db->get("branch");

$date_param = explode('/', $on_date);
if($mode == 0){
?>
<h1>รายงานการใช้ PT</h1>
<form method="post">
<input type="hidden" name="mode" id="mode" value="<?=$mode;?>" />
<input type="hidden" name="report_name" value="use_pt" />
<table border=0 cellpadding=5 cellspacing=5 style="margin: 5px 0 5px 0;">
<tr class="thead">
	<td style="color: white;">สาขา</td>
</tr>
<tr>
	<td style="vertical-align: top; border: none;">
<?php
foreach($branch_list->result_array() as $branch_data){
?>
		<input type="radio" name="branch" value="<?=$branch_data["branch_id"]; ?>"<?php if($branch_data["branch_id"] == $branch) { ?> checked<?php }?> /><?=$branch_data["branch_name"]; ?><br />
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
		if($branch_data["branch_id"] == $branch) $branch_str .= $branch_data["branch_name"] . ", ";
	}
	$branch_str = substr($branch_str, 0, strlen($branch_str) - 2);
}
?>
<div style="margin: 0; padding: 0;"><h1>รายงานการใช้ PT</h1></div>
<br />
<div style="text-align: left"><b>สาขา : </b><?=$branch_str;?></div>
<br />
<div style="text-align: left"><b>ณ วันที่ : </b><?=$on_date;?></div>
<br />
<?php
}

if($this->input->post("branch") !== null && $this->input->post("on_date") !== null){
?>
<table border=0 cellpadding=5 cellspacing=<?=($mode==0 ? '1 style="margin-top: 5px;"' : '0');?>>
<tr class="thead">
	<td></td>
	<td style="vertical-align: middle; text-align: center;">วันและเวลา</td>
	<td style="vertical-align: middle; text-align: center;">สมาชิก</td>
	<td style="vertical-align: middle; text-align: center;">เทรนเนอร์</td>
	<td style="vertical-align: middle; text-align: center;">คอร์ส PT</td>
	<td style="vertical-align: middle; text-align: center;">คงเหลือ</td>
	<td style="vertical-align: middle; text-align: center;">ทำรายการโดย</td>
	<td style="vertical-align: middle; text-align: center;">หมายเหตุ</td>
</tr>
<?php
	$this->db->select("A.process_datetime, CONCAT(D.max_hours, ' ชม. ') pt_course_name, A.hours_detail, B.member_no, B.firstname_th, B.lastname_th, B.nickname_th, CONCAT(C.fullname, ' ( ', C.nickname, ' ) - ', C.emp_code) trainer, GET_NAME_BY_USER_ID(A.process_by) process_by");
	$this->db->from("member_use_pt A");
	$this->db->join("member B", "A.member_id = B.member_id", "INNER");
	$this->db->join("employee C", "A.pt_emp_id = C.emp_id", "INNER");
	$this->db->join("member_pt D", "A.member_pt_id = D.member_pt_id", "INNER");
	$this->db->where("STR_TO_DATE(A.use_datetime,'%Y-%m-%d') = STR_TO_DATE('$on_date','%d/%m/%Y')");
	$this->db->where("A.use_at_branch", $branch);
	$this->db->order_by("STR_TO_DATE(A.use_datetime,'%Y-%m-%d %H:%i:%s')");
	$this->db->limit($this->config->item('record_per_page'), ($page - 1) * $this->config->item('record_per_page'));
	$use_list = $this->db->get();
	$total_record = $use_list->num_rows();
	if($use_list->num_rows() == 0){
?>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: center;" colspan=8>ไม่มีข้อมูล</td>
</tr>
<?php
	} else {
		$count = 0;
		foreach($use_list->result_array() as $use_data){
			$count++;
?>
<tr class="tbody">
	<td><?=$count;?></td>
	<td align=left><?=formatDBDateTime($use_data["process_datetime"]);?></td>
	<td align=left><?=(trim($use_data["member_no"]) != "" ? $use_data["member_no"] . " - " : "") . $use_data["firstname_th"] . " " . $use_data["lastname_th"] . (trim($use_data["nickname_th"]) != "" ? " (" . $use_data["nickname_th"] . ")" : "");?></td>
	<td align=left><?=$use_data["trainer"];?></td>
	<td><?=$use_data["pt_course_name"];?></td>
	<td align=left><?=$use_data["hours_detail"];?></td>
	<td align=left><?=$use_data["process_by"];?></td>
	<td></td>
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