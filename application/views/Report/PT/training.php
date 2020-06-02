<div style="padding: 10px;">
<?php
$this->db->where("is_suspend", 0);
$branch_list = $this->db->get("branch");
if($mode == 0){
?>
<h1>รายงานการเทรน</h1>
<form method="post">
<input type="hidden" name="mode" id="mode" value="<?=$mode;?>" />
<input type="hidden" name="report_name" value="pt_training" />
<table border=0 cellpadding=5 cellspacing=5 style="margin: 5px 0 5px 0;">
<tr class="thead">
	<td style="color: white;">เทรนเนอร์ สังกัดสาขา</td>
</tr>
<tr>
	<td style="vertical-align: top; border: none;">
		<input type="checkbox" name="branch[]" value="-1" onclick="$('[name=\'branch[]\'][value!=-1]').attr('checked', false);"<?php if(in_array("-1", $branch) || count($branch) == 0) {?> checked<?php }?> onChange="$('#mode').val('<?=$mode;?>'); $('form').attr('target', '_self').submit();" />ทุกสาขา<br />
<?php
foreach($branch_list->result_array() as $branch_data){
?>
		<input type="checkbox" name="branch[]" value="<?=$branch_data["branch_id"]; ?>" onclick="$('[name=\'branch[]\'][value=-1]').attr('checked', false);"<?php if(in_array($branch_data["branch_id"], $branch)) { ?> checked<?php }?> onChange="$('#mode').val('<?=$mode;?>'); $('form').attr('target', '_self').submit();" /><?=$branch_data["branch_name"]; ?><br />
<?php
}
?>
	</td>
</tr>
<tr class="thead">
	<td>
		เทรนเนอร์ :&nbsp;
		<select name="pt_emp_id" id="pt_emp_id">
			<option value="" selected>เลือกเทรนเนอร์</option>
<?php
$this->db->select("emp_id, fullname, nickname");
$this->db->from("employee");
$this->db->where("is_trainer", 1);
if(!(count($branch) == 1 && $branch[0] == -1))
	$this->db->where_in("branch_id", $branch);
$pt = $this->db->get();
foreach($pt->result_array() as $pt_data){
?>
			<option value="<?=$pt_data["emp_id"];?>"<?php if($pt_emp_id == $pt_data["emp_id"]) echo " selected";?>><?=$pt_data["fullname"] . " (" . $pt_data["nickname"] . ")"?></option>
<?php
}
?>
		</select>
	</td>
</tr>
<tr>
	<td>
		ระหว่างวันที่ : <input name="start_date" class="datepicker" value="<?=$start_date;?>" /><br />
		ถึงวันที่ : <input name="end_date" class="datepicker" value="<?=$end_date;?>" />
	</td>
</tr>
</table>
<button onclick="if(isValid()) { $('#mode').val('<?=$mode;?>'); $('form').attr('target', '_self'); } else return false;">เรียกรายงาน</button>
<button onclick="if(isValid()) { $('#mode').val('2'); $('form').attr('target', '_self'); $('form').submit(); } return false;">EXCEL</button>
<button onclick="if(isValid()) { $('#mode').val('3'); $('form').attr('target', '_blank'); $('form').submit(); } return false;">PRINT</button>
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
<div style="margin: 0; padding: 0;"><h1>รายงานการเทรน</h1></div>
<br />
<div style="text-align: left"><b>เทรนเนอร์ สังกัดสาขา : </b><?=$branch_str;?></div>
<div style="text-align: left"><b>เทรนเนอร์ : </b>
<?php
if($pt_emp_id == "") echo "-";
else {
	$this->db->select("emp_id, fullname, nickname");
	$this->db->from("employee");
	$this->db->where("emp_id", $pt_emp_id);
	$pt = $this->db->get();
	$pt = $pt->row(0);
	echo $pt->fullname . " (" . $pt->nickname . ")";
}
?>
</div>
<br />
<div style="text-align: left"><b>ระหว่างวันที่ : </b><?=$start_date;?></div>
<div style="text-align: left"><b>ถึงวันที่ : </b><?=$end_date;?></div>
<br />
<?php
}

if($this->input->post("branch") && $this->input->post("pt_emp_id") && $this->input->post("pt_emp_id") != "" && $this->input->post("start_date") && $this->input->post("end_date")){
?>
<table border=0 cellpadding=5 cellspacing=<?=($mode==0 ? '1 style="margin-top: 5px;"' : '0');?>>
<tr class="thead">
	<td>วัน และ เวลา</td>
	<td>ชื่อสมาชิก</td>
	<td>คอร์ส</td>
	<td>หลังเทรนนิ่ง คงเหลือ</td>
</tr>
<?php
	$this->db->select("A.use_datetime, D.firstname_th, D.lastname_th, D.nickname_th, B.left_hours, B.max_hours, B.price");
	$this->db->from("member_use_pt A");
	$this->db->join("member_pt B", "A.member_pt_id = B.member_pt_id", "INNER");
	$this->db->join("member D", "B.member_id = D.member_id", "INNER");
	$this->db->where("A.pt_emp_id", $pt_emp_id);
	$this->db->where("STR_TO_DATE(A.use_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('$start_date','%d/%m/%Y') AND STR_TO_DATE('$end_date','%d/%m/%Y')");
	$this->db->limit($this->config->item('record_per_page'), ($page - 1) * $this->config->item('record_per_page'));
	//$this->db->limit(($page - 1) * $this->config->item('record_per_page'), $this->config->item('record_per_page'));
	$this->db->order_by("A.use_datetime");
	$training = $this->db->get();
	$total_record = $training->num_rows();
	//echo $this->db->last_query();
	if($training->num_rows() == 0){
?>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: center;" colspan=4>ไม่มีข้อมูล</td>
</tr>
<?php
	} else {
		foreach($training->result_array() as $training_data){
?>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: left;"><?=formatDBDateTime($training_data["use_datetime"]);?></td>
	<td style="vertical-align: middle; text-align: left;"><?=$training_data["firstname_th"] . " " . $training_data["lastname_th"] . " (" . $training_data["nickname_th"] . ")"?></td>
	<td style="vertical-align: middle; text-align: center;"><?=$training_data["max_hours"] . "ชม. " . number_format($training_data["price"]) . " บาท.";?></td>
	<td style="vertical-align: middle; text-align: center;"><?=$training_data["left_hours"] . " / " . $training_data["max_hours"];?></td>
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
<?php
if($mode == 0){
?>
<script>
function isValid()
{
	if($('#pt_emp_id').val() == ""){
		alert("ยังไม่ได้เลือก 'เทรนเนอร์' !");
		return false;
	}
	return true;
}
</script>
<?php
}
?>
</body>
</html>