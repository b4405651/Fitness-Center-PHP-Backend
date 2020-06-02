<div style="padding: 10px;">
<?php
$this->db->where("is_suspend", 0);
$branch_list = $this->db->get("branch");
if($mode == 0){
?>
<h1>รายได้สาขา ระหว่างวันที่</h1>
<form method="post">
<input type="hidden" name="mode" id="mode" value="<?=$mode;?>" />
<input type="hidden" name="report_name" value="summary_during_date" />
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
		ระหว่างวันที่ : <input name="start_date" class="datepicker" value="<?=$start_date;?>" /><br />
		ถึงวันที่ : <input name="end_date" class="datepicker" value="<?=$end_date;?>" />
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
<div style="margin: 0; padding: 0;"><h1>รายได้สาขา ระหว่างวันที่</h1></div>
<br />
<div style="text-align: left"><b>สาขา : </b><?=$branch_str;?></div>
<br />
<div style="text-align: left"><b>ระหว่างวันที่ : </b><?=$start_date;?></div>
<div style="text-align: left"><b>ถึงวันที่ : </b><?=$end_date;?></div>
<br />
<?php
}

if($this->input->post("branch") && $this->input->post("start_date") && $this->input->post("end_date")){
	$sum = array();
	$branch_id = array();
	if(count($branch) == 1 && $branch[0] == "-1") {
		foreach($branch_list->result_array() as $branch_data){
			array_push($branch_id, $branch_data["branch_id"]);
		}
	}
	else $branch_id = $branch;
	
	for($i=0; $i<count($branch_id); $i++){
		$sum[$branch_id[$i]][0] = 0;
		$sum[$branch_id[$i]][1] = 0;
	}
?>
<table border=0 cellpadding=5 cellspacing=<?=($mode==0 ? '1 style="margin-top: 5px;"' : '0');?>>
<tr class="thead">
	<td rowspan=2 style="vertical-align: middle; text-align: center;">แหล่งรายได้</td>
<?php
	foreach($branch_list->result_array() as $branch_data){
		if((count($branch) == 1 && $branch[0] == "-1") || in_array($branch_data["branch_id"], $branch)){
?>
	<td colspan=2><?=$branch_data["branch_name"];?></td>
<?php
		}
	}
?>
</tr>
<tr class="thead">
<?php
	for($i=0; $i<count($branch_id); $i++){
?>
	<td align=left>เงินสด</td>
	<td>บัตร</td>
<?php
	}
?>
</tr>
<tr class="tbody">
	<td align=left>ค่าสมาชิก</td>
<?php
	for($i=0; $i<count($branch_id); $i++){
?>
	<td>
<?php
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_ext_payment");
		$this->db->where("STR_TO_DATE(received_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('$start_date','%d/%m/%Y') AND STR_TO_DATE('$end_date','%d/%m/%Y')");
		$this->db->where("payment_type", 0);
		$this->db->where("branch_id", $branch_id[$i]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
		$sum[$branch_id[$i]][0] += $data->amount;
?>
	</td>
	<td>
<?php
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_ext_payment");
		$this->db->where("STR_TO_DATE(received_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('$start_date','%d/%m/%Y') AND STR_TO_DATE('$end_date','%d/%m/%Y')");
		$this->db->where("payment_type", 1);
		$this->db->where("branch_id", $branch_id[$i]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
		$sum[$branch_id[$i]][1] += $data->amount;
?>
	</td>
<?php
	}
?>
</tr>
<tr class="tbody">
	<td align=left>ค่าซื้อ PT</td>
<?php
	for($i=0; $i<count($branch_id); $i++){
?>
	<td>
<?php
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_pt_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('$start_date','%d/%m/%Y') AND STR_TO_DATE('$end_date','%d/%m/%Y')");
		$this->db->where("payment_type", 0);
		$this->db->where("branch_id", $branch_id[$i]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
		$sum[$branch_id[$i]][0] += $data->amount;
?>
	</td>
	<td>
<?php
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_pt_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('$start_date','%d/%m/%Y') AND STR_TO_DATE('$end_date','%d/%m/%Y')");
		$this->db->where("payment_type", 1);
		$this->db->where("branch_id", $branch_id[$i]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
		$sum[$branch_id[$i]][1] += $data->amount;
?>
	</td>
<?php
	}
?>
</tr>
<tr class="tbody">
	<td align=left>ขายของ</td>
<?php
	for($i=0; $i<count($branch_id); $i++){
?>
	<td>
<?php
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("shop_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('$start_date','%d/%m/%Y') AND STR_TO_DATE('$end_date','%d/%m/%Y')");
		$this->db->where("payment_type", 0);
		$this->db->where("branch_id", $branch_id[$i]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
		$sum[$branch_id[$i]][0] += $data->amount;
?>
	</td>
	<td>
<?php
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("shop_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('$start_date','%d/%m/%Y') AND STR_TO_DATE('$end_date','%d/%m/%Y')");
		$this->db->where("payment_type", 1);
		$this->db->where("branch_id", $branch_id[$i]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
		$sum[$branch_id[$i]][1] += $data->amount;
?>
	</td>
<?php
	}
?>
</tr>
<tr class="tsum">
	<td align=right>รวม</td>
<?php
	for($i=0; $i<count($branch_id); $i++){
?>
	<td><?=number_format($sum[$branch_id[$i]][0])?></td>
	<td><?=number_format($sum[$branch_id[$i]][1])?></td>
<?php
	}
?>
</tr>
</table>
<?php
}
?>
</div>
</body>
</html>