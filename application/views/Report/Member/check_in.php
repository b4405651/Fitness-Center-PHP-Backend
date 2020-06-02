<div style="padding: 10px;">
<?php
$this->db->where("is_suspend", 0);
$branch_list = $this->db->get("branch");

$date_param = explode('/', $on_date);
if($mode == 0){
?>
<h1>รายงานการเข้าใช้บริการ</h1>
<form method="post">
<input type="hidden" name="mode" id="mode" value="<?=$mode;?>" />
<input type="hidden" name="report_name" value="member_checkin" />
<table border=0 cellpadding=5 cellspacing=5 style="margin: 5px 0 5px 0;">
<tr class="thead">
	<td style="color: white;">สาขา</td>
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
<div style="margin: 0; padding: 0;"><h1>รายงานการเข้าใช้บริการ</h1></div>
<br />
<div style="text-align: left"><b>สาขา : </b><?=$branch_str;?></div>
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
	<td style="vertical-align: middle; text-align: center;">ณ วันที่</td>
<?php
	foreach($branch_list->result_array() as $branch_data){
		if((count($branch) == 1 && $branch[0] == "-1") || in_array($branch_data["branch_id"], $branch)){
?>
	<td><?=$branch_data["branch_name"];?></td>
<?php
		}
	}
?>
</tr>
<tr class="tbody">
	<td align=left><?="วันที่ " . $date_param[0] . " " . get_month_name($date_param[1], true) . " $date_param[2]";?></td>
<?php
	for($i=0; $i<count($branch_id); $i++){
?>
	<td>
<?php
		$this->db->select("IFNULL(COUNT(*), 0) amount");
		$this->db->from("member_checkin");
		$this->db->where("STR_TO_DATE(checkin_datetime,'%Y-%m-%d') = STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("branch_id", $branch_id[$i]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
<?php
	}
?>
</tr>
<tr class="tbody">
	<td align=left><?="เดือน " . get_month_name($date_param[1]) . " $date_param[2]" . " (1 - " . $date_param[0] . " " . get_month_name($date_param[1], true) . " " . $date_param[2] . ")";?></td>
<?php
	for($i=0; $i<count($branch_id); $i++){
?>
	<td>
<?php
		$this->db->select("IFNULL(COUNT(*), 0) amount");
		$this->db->from("member_checkin");
		$this->db->where("STR_TO_DATE(checkin_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/$date_param[1]/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("branch_id", $branch_id[$i]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
<?php
	}
?>
</tr>
<tr class="tbody">
	<td align=left><?="ปี $date_param[2] (1 " . get_month_name(1, true) . " - " . $date_param[0] . " " . get_month_name($date_param[1], true) . " $date_param[2])";?></td>
<?php
	for($i=0; $i<count($branch_id); $i++){
?>
	<td>
<?php
		$this->db->select("IFNULL(COUNT(*), 0) amount");
		$this->db->from("member_checkin");
		$this->db->where("STR_TO_DATE(checkin_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/01/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("branch_id", $branch_id[$i]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
<?php
	}
?>
</tr>
</table>
<?php
	$this->db->select("A.checkin_datetime, B.firstname_th, B.lastname_th, B.nickname_th, C.branch_name");
	$this->db->from("member_checkin A");
	$this->db->join("member B", "A.member_id = B.member_id", "INNER");
	$this->db->join("branch C", "A.branch_id = C.branch_id", "INNER");
	$this->db->where("STR_TO_DATE(A.checkin_datetime,'%Y-%m-%d') = STR_TO_DATE('$on_date','%d/%m/%Y')");
	if(!(count($branch_id) == 1 && $branch_id[0] == "-1"))
		$this->db->where_in("A.branch_id", $branch_id);
	$this->db->order_by("A.checkin_datetime");
	$data = $this->db->get();
	if($data->num_rows() > 0){
?>
<br />
<table border=0 cellpadding=5 cellspacing=<?=($mode==0 ? '1 style="margin-top: 5px;"' : '0');?>>
<tr class="thead">
	<td>วัน และ เวลา</td>
	<td>ชื่อสมาชิก</td>
	<td>สาขา</td>
</tr>
<?php
		foreach($data->result_array() as $checkin_data)
		{
			$fullname = $checkin_data["firstname_th"];

			if(trim($checkin_data["lastname_th"]) != "" && trim($checkin_data["lastname_th"]) != "-")
				$fullname .= " " . trim($checkin_data["lastname_th"]);
			
			if(trim($checkin_data["nickname_th"]) != "" && trim($checkin_data["nickname_th"]) != "-")
				$fullname .= " (" . trim($checkin_data["nickname_th"]) . ")";
?>
<tr class="tbody">
	<td style="text-align: left; vertical-align: middle;"><?=formatDBDateTime($checkin_data["checkin_datetime"]);?></td>
	<td style="text-align: left; vertical-align: middle;"><?=$fullname;?></td>
	<td  style="text-align: center; vertical-align: middle;"><?=$checkin_data["branch_name"];?></td>
</tr>
<?php
		}
?>
</table>
<?php
	}
}
?>
</div>
</body>
</html>