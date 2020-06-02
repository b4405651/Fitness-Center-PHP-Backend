<div style="padding: 10px;">
<?php
$this->db->where("is_suspend", 0);
$branch_list = $this->db->get("branch");

$date_param = explode('/', $on_date);
if($mode == 0){
?>
<h1>รายได้ ณ วันที่ และ สะสม</h1>
<form method="post">
<input type="hidden" name="mode" id="mode" value="<?=$mode;?>" />
<input type="hidden" name="report_name" value="summary_on_date" />
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
<div style="margin: 0; padding: 0;"><h1>รายได้ ณ วันที่ และ สะสม</h1></div>
<br />
<div style="text-align: left"><b>สาขา : </b><?=$branch_str;?></div>
<br />
<div style="text-align: left"><b>ณ วันที่ : </b><?=$on_date;?></div>
<br />
<?php
}

if($this->input->post("branch") && $this->input->post("on_date")){
	$sum = array();
	$branch_id = array();
	$branch_name = array();
	if(count($branch) == 1 && $branch[0] == "-1") {
		foreach($branch_list->result_array() as $branch_data){
			array_push($branch_id, $branch_data["branch_id"]);
			array_push($branch_name, $branch_data["branch_name"]);
		}
	}
	else 
	{
		$branch_id = $branch;
		for($i=0; $i<count($branch_id); $i++)
		{
			foreach($branch_list->result_array() as $branch_data){
				if($branch_id[$i] == $branch_data["branch_id"]) {
					array_push($branch_name, $branch_data["branch_name"]);
					break;
				}
			}
		}
	}
	
	for($i=0; $i<count($branch_id); $i++){
		$sum[$branch_id[$i]][0] = 0;
		$sum[$branch_id[$i]][1] = 0;
	}
?>
<table border=0 cellpadding=5 cellspacing=<?=($mode==0 ? '1 style="margin-top: 5px;"' : '0');?>>
<tr class="thead">
	<td colspan=3 style="vertical-align: middle; text-align: center;">สาขา, แหล่งรายได้ และ แหล่งเงิน</td>
	<td style="vertical-align: middle; text-align: center;"><?="วันที่<br />" . $date_param[0] . " " . get_month_name($date_param[1], true) . " $date_param[2]";?></td>
	<td style="vertical-align: middle; text-align: center;"><?="เดือน " . get_month_name($date_param[1]) . " $date_param[2]" . "<br />1 - " . $date_param[0] . " " . get_month_name($date_param[1], true) . " " . $date_param[2];?></td>
	<td style="vertical-align: middle; text-align: center;"><?="ปี $date_param[2]<br />1 " . get_month_name(1, true) . " - " . $date_param[0] . " " . get_month_name($date_param[1], true) . " $date_param[2]";?></td>
</tr>
<?php
	for($index = 0; $index < count($branch_id); $index++){
?>
<tr class="tbody">
	<td rowspan=6 style="vertical-align: middle; text-align: left;<?php if($index % 2 != 0) { ?> background-color: white;<?php } if($index == (count($branch_id)) - 1) echo " border-bottom: none;";?>"><?=$branch_name[$index];?></td>
	<td rowspan=2 style="vertical-align: middle; text-align: left;<?php if($index % 2 != 0) { ?> background-color: white;<?php } ?>">ค่าสมาชิก</td>
	<td style="vertical-align: middle; text-align: left;">เงินสด</td>
	<td>
<?php
		// ค่าสมาชิก - เงินสด - ณ วันที่
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_ext_payment");
		$this->db->where("STR_TO_DATE(received_datetime,'%Y-%m-%d') = STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 0);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
	<td>
<?php
		// ค่าสมาชิก - เงินสด - ตั้งแต่ต้นเดือน
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_ext_payment");
		$this->db->where("STR_TO_DATE(received_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/$date_param[1]/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 0);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
	<td>
<?php
		// ค่าสมาชิก - เงินสด - ตั้งแต่ต้นปี
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_ext_payment");
		$this->db->where("STR_TO_DATE(received_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/01/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 0);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
</tr>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: left;">บัตร</td>
	<td>
<?php
		// ค่าสมาชิก - บัตร - ณ วันที่
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_ext_payment");
		$this->db->where("STR_TO_DATE(received_datetime,'%Y-%m-%d') = STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 1);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
	<td>
<?php
		// ค่าสมาชิก - บัตร - ตั้งแต่ต้นเดือน
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_ext_payment");
		$this->db->where("STR_TO_DATE(received_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/$date_param[1]/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 1);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
	<td>
<?php
		// ค่าสมาชิก - บัตร - ตั้งแต่ต้นปี
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_ext_payment");
		$this->db->where("STR_TO_DATE(received_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/01/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 1);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
</tr>
<tr class="tbody">
	<td rowspan=2 style="vertical-align: middle; text-align: left;<?php if($index % 2 != 0) { ?> background-color: white;<?php } ?>">ค่าซื้อ PT</td>
	<td style="vertical-align: middle; text-align: left;">เงินสด</td>
	<td>
<?php
		// ค่า PT - เงินสด - ณ วันที่
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_pt_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') = STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 0);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
	<td>
<?php
		// ค่า PT - เงินสด - ตั้งแต่ต้นเดือน
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_pt_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/$date_param[1]/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 0);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
	<td>
<?php
		// ค่า PT - เงินสด - ตั้งแต่ปี
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_pt_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/01/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 0);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
</tr>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: left;">บัตร</td>
	<td>
<?php
		// ค่า PT - บัตร - ณ วันที่
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_pt_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') = STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 1);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
	<td>
<?php
		// ค่า PT - บัตร - ตั้งแต่ต้นเดือน
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_pt_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/$date_param[1]/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 1);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
	<td>
<?php
		// ค่า PT - บัตร - ตั้งแต่ต้นปี
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("member_pt_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/01/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 1);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
</tr>
<tr class="tbody">
	<td rowspan=2 style="vertical-align: middle; text-align: left;<?php if($index % 2 != 0) { ?> background-color: white;<?php } if($index == (count($branch_id)) - 1) echo " border-bottom: none;";?>">ขายของ</td>
	<td style="vertical-align: middle; text-align: left;">เงินสด</td>
	<td>
<?php
		// ขายของ - เงินสด - ณ วันที่
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("shop_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') = STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 0);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
	<td>
<?php
		// ขายของ - เงินสด - ตั้งแต่ต้นเดือน
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("shop_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/$date_param[1]/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 0);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
	<td>
<?php
		// ขายของ - เงินสด - ตั้งแต่ต้นปี
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("shop_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/01/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 0);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
</tr>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: left;">บัตร</td>
	<td>
<?php
		// ขายของ - บัตร - ณ วันที่
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("shop_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') = STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 0);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
	<td>
<?php
		// ขายของ - บัตร - ตั้งแต่ต้นเดือน
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("shop_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/$date_param[1]/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 1);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
	<td>
<?php
		// ขายของ - บัตร - ตั้งแต่ต้นปี
		$this->db->select("IFNULL(SUM(amount), 0) amount");
		$this->db->from("shop_payment");
		$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('01/01/$date_param[2]','%d/%m/%Y') AND STR_TO_DATE('$on_date','%d/%m/%Y')");
		$this->db->where("payment_type", 1);
		$this->db->where("branch_id", $branch_id[$index]);
		$data = $this->db->get();
		$data = $data->row(0);
		echo number_format($data->amount);
?>
	</td>
</tr>
<?php
	}
?>
</table>
<?php
}
?>
</div>
</body>
</html>