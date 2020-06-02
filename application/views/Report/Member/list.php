<div style="padding: 10px;">
<?php
$this->db->where("is_suspend", 0);
$branch_list = $this->db->get("branch");

$this->db->where("is_suspend", 0);
$member_type_list = $this->db->get("member_type");

if($mode == 0){
?>
<h1>รายชื่อสมาชิก</h1>
<form method="post">
<input type="hidden" name="mode" id="mode" value="<?=$mode;?>" />
<input type="hidden" name="report_name" value="member_list_page_<?=$page;?>" />
<table border=0 cellpadding=5 cellspacing=5 style="margin: 5px 0 5px 0;">
<tr class="thead">
	<td style="color: white;">สาขา</td>
	<td style="color: white;">เลือกดูสมาชิก</td>
	<td style="color: white;">ประเภทสมาชิก</td>
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
<td style="vertical-align: top; border: none;">
	<input type="radio" name="member_view" value="-1"<?php if($member_view == "-1") {?> checked<?php }?> />ทั้งหมด<br />
	<input type="radio" name="member_view" value="0"<?php if($member_view === "0") { ?> checked<?php }?> />สมาชิกใหม่<br />
	<input type="radio" name="member_view" value="1"<?php if($member_view === "1") { ?> checked<?php }?> />สมาชิกยังไม่หมดอายุ<br />
	<input type="radio" name="member_view" value="2"<?php if($member_view == "2") { ?> checked<?php }?> />สมาชิกหมดอายุ<br />
	<input type="radio" name="member_view" value="3"<?php if($member_view == "3") { ?> checked<?php }?> />สมาชิกดรอป<br />
	<input type="radio" name="member_view" value="4"<?php if($member_view == "4") { ?> checked<?php }?> />สมาชิกทั้งหมดที่ยังชำระไม่ครบ<br />
	<input type="radio" name="member_view" value="5"<?php if($member_view == "5") { ?> checked<?php }?> />สมาชิกที่ถูกระงับการใช้<br />
</td>
<td style="vertical-align: top; border: none;">
	<input type="checkbox" name="member_type[]" value="-1" onclick="$('[name=\'member_type[]\'][value!=-1]').attr('checked', false);"<?php if(in_array("-1", $member_type) || count($member_type) == 0) {?> checked<?php }?> />ทั้งหมด<br />
<?php
	foreach($member_type_list->result_array() as $member_type_data){
?>
	<input type="checkbox" name="member_type[]" value="<?=$member_type_data["member_type_id"]; ?>" onclick="$('[name=\'member_type[]\'][value=-1]').attr('checked', false);"<?php if(in_array($member_type_data["member_type_id"], $member_type)) { ?> checked<?php }?> /><?=$member_type_data["member_type_name"]; ?><br />
<?php
	}
?>
</td>
</tr>
</table>
<button onclick="$('#mode').val('<?=$mode;?>'); $('form').attr('target', '_self');">เรียกรายงาน</button>
<button onclick="$('#mode').val('2'); $('form').attr('target', '_self'); $('form').submit();">EXCEL</button>
<button onclick="$('#mode').val('3'); $('form').attr('target', '_blank'); $('form').submit();">PRINT</button>
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

	$member_view_str = "";
	switch($member_view){
		case "-1":
			$member_view_str = 'ทั้งหมด';
			break;
		case "0":
			$member_view_str = "สมาชิกใหม่";
			break;
		case "1":
			$member_view_str = "สมาชิกยังไม่หมดอายุ";
			break;
		case "2":
			$member_view_str = "สมาชิกหมดอายุ";
			break;
		case "3":
			$member_view_str = "สมาชิกดรอป";
			break;
		case "4":
			$member_view_str = "สมาชิกทั้งหมดที่ยังชำระไม่ครบ";
			break;
		case "5":
			$member_view_str = "สมาชิกที่ถูกระงับการใช้";
			break;
	}

	$member_type_str = "";
	if(count($member_type) == 1 && $member_type[0] == "-1") $member_type_str = 'ทั้งหมด';
	else
	{
		foreach($member_type_list->result_array() as $member_type_data){
			if(in_array($member_type_data["member_type_id"], $member_type)) $member_type_str .= $member_type_data["member_type_name"] . ", ";
		}
		$member_type_str = substr($member_type_str, 0, strlen($member_type_str) - 2);
	}
?>
<div style="margin: 0; padding: 0;"><h1>รายชื่อสมาชิก</h1></div>
<br />
<div style="text-align: left"><b>สาขา : </b><?=$branch_str;?></div>
<br />
<div style="text-align: left"><b>เลือกดูสมาชิก : </b><?=$member_view_str;?></div>
<br />
<div style="text-align: left"><b>ประเภทสมาชิก : </b><?=$member_type_str;?></div>
<br />
<div style="text-align: left"><b>ดูรายชื่อ ณ วันที่ : </b><?=$on_date;?></div>
<br />
<?php
	}

	if($this->input->post("branch") && $this->input->post("member_view") !== NULL && $this->input->post("member_type")){
?>
<table border=0 cellpadding=5 cellspacing=<?=($mode==0 ? '1 style="margin-top: 5px;"' : '0');?>>
<tr class="thead">
	<td style="vertical-align: middle; text-align: center;"></td>
	<td style="vertical-align: middle; text-align: center;">รหัสสมาชิก</td>
	<td style="vertical-align: middle; text-align: center;">ชื่อ (ไทย)</td>
	<td style="vertical-align: middle; text-align: center;">ชื่อ (อังกฤษ)</td>
<?php
		if($this->input->post("member_view") == "4"){
?>
	<td style="vertical-align: middle; text-align: center; color: red;">ชำระเงินไว้</td>
<?php
		}
?>
	<td style="vertical-align: middle; text-align: center;">เพศ</td>
	<td style="vertical-align: middle; text-align: center;">ข้อมูลเข้าระบบเมื่อ</td>
	<td style="vertical-align: middle; text-align: center;">
<?php
		$txt = "ประเภทสมาชิก";
		switch($member_view){
			case "2":
				$txt .= "ที่เพิ่งหมดอายุไป";
				break;
			case "3":
				$txt .= "ปัจจุบัน (ที่ดรอปไว้)";
				break;
			case "4":
				$txt .= "ที่ซื้อไว้";
				break;
			default:
				$txt .= "ปัจจุบัน";
				break;
		}
		echo $txt;
?>
	</td>
<?php
		if($member_view === "-1"){
?>
	<td style="vertical-align: middle; text-align: center;">ชำระเงิน</td>
<?php
		}
?>
	<td style="vertical-align: middle; text-align: center;">ระหว่างวันที่</td>
	<td style="vertical-align: middle; text-align: center;">วันเกิด</td>
	<td style="vertical-align: middle; text-align: center;">อายุ</td>
	<td style="vertical-align: middle; text-align: center;">โทรศัพท์</td>
	<td style="vertical-align: middle; text-align: center;">อีเมลล์</td>
<?php
		if($member_view !== "4"){
?>
	<td style="vertical-align: middle; text-align: center;"><?=($member_view === "3" ? "ดรอปตั้งแต่วันที่" : "ใช้สิทธิ์ดรอประหว่างวันที่");?></td>
<?php
		}
?>
<?php
		if($member_view === "5"){
?>
	<td style="vertical-align: middle; text-align: center;">ถูกระงับใช้เมื่อ</td>
	<td style="vertical-align: middle; text-align: center;">สาเหตุ</td>
	<td style="vertical-align: middle; text-align: center;">ระงับโดย</td>
<?php
		}
?>
</tr>
<?php
	if(count($member_list) == 0){
?>
<tr class="tbody">
	<td colspan="<?=($member_view === "-1" ? "17" : ($member_view === "5" ? "16" : ($member_view === "4" ? "15" : "13")));?>" style="vertical-align: middle; text-align: center;">ไม่มีข้อมูล</td>
</tr>
<?php
	} else {
		$row_num = ($page - 1) * $this->config->item('record_per_page');
		foreach($member_list as $member_data){
			$row_num++;
			$phone = "-";
			if($member_data['mobile_phone'] != "") $phone = $member_data['mobile_phone'] . " (มือถือ)";
			else if($member_data['home_phone'] != "") $phone = $member_data['home_phone'] . " (บ้าน)";
			else if($member_data['work_phone'] != "") $phone = $member_data['work_phone'] . " (ทำงาน)";
?>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: center;"><?=number_format($row_num);?></td>
	<td style="vertical-align: middle;"><?=$member_data['member_no'];?></td>
	<td style="vertical-align: middle; text-align: left;"><?=$member_data['firstname_th'] . " " . $member_data['lastname_th'] . (trim($member_data['nickname_th'] != "") ? " (" . $member_data['nickname_th'] . ")" : "");?></td>
	<td style="vertical-align: middle; text-align: left;"><?=$member_data['firstname_en'] . " " . $member_data['lastname_en'] . (trim($member_data['nickname_en'] != "") ? " (" . $member_data['nickname_en'] . ")" : "");?></td>
<?php
		if($this->input->post("member_view") == "4"){
?>
	<td style="vertical-align: middle; text-align: center; color: red;"><?=$member_data["total_paid"];?></td>
<?php
		}
?>
	<td style="vertical-align: middle; text-align: center;"><?=$member_data['gender'];?></td>
	<td style="vertical-align: middle; text-align: center;"><?=formatDBDateTime($member_data['create_date']);?></td>
	<td style="vertical-align: middle; text-align: center;"><?=$member_data['current_member_type'];?></td>
<?php
		if($member_view === "-1"){
?>
	<td style="vertical-align: middle; text-align: center;">
	</td>
<?php
		}
?>
	<td style="vertical-align: middle; text-align: center;"><?=$member_data['during_date'];?></td>
	<td style="vertical-align: middle; text-align: center;"><?=$member_data['birthday'];?></td>
	<td style="vertical-align: middle; text-align: center;"><?=calculateAge($member_data['birthday']);?></td>
	<td style="vertical-align: middle; text-align: center;"><?=$phone;?></td>
	<td style="vertical-align: middle; text-align: center;"><?=$member_data['email'];?></td>
<?php
		if($member_view !== "4"){
?>
	<td style="vertical-align: middle; text-align: center;"><?=$member_data['drop_during'];?></td>
<?php
		}
?>
<?php
		if($member_view === "5"){
?>
	<td style="vertical-align: middle; text-align: center;"><?=formatDBDateTime($member_data['suspend_since']);?></td>
	<td style="vertical-align: middle;"><?=$member_data['suspend_reason'];?></td>
	<td style="vertical-align: middle; text-align: center;"><?=$member_data['suspend_by'];?></td>
<?php
		}
?>
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
if($mode == 0) {
?>
</form>
<?php
}
?>
</body>
</html>