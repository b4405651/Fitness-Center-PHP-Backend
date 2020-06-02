<div style="padding: 10px;">
<?php
$this->db->where("is_suspend", 0);
$branch_list = $this->db->get("branch");
if($mode == 0){
?>
<h1>รายงานสมาชิกชำระเงินค่าซื้อ Member</h1>
<form method="post">
<input type="hidden" name="mode" id="mode" value="<?=$mode;?>" />
<input type="hidden" name="report_name" value="sell_member" />
<table border=0 cellpadding=5 cellspacing=5 style="margin: 5px 0 5px 0;">
<tr class="thead">
	<td style="color: white;">สาขา</td>
	<td style="color: white;">เลือกดู</td>
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
	<td style="vertical-align: top; border: none;">
		<input type="radio" name="member_view" value="-1"<?php if($member_view == "" || $member_view == "-1") {?> checked<?php }?> />ทั้งหมด<br />
		<input type="radio" name="member_view" value="1"<?php if($member_view === "1") {?> checked<?php }?> />ชำระมัดจำ<br />
		<input type="radio" name="member_view" value="0"<?php if($member_view === "0") {?> checked<?php }?> />ชำระเต็มจำนวน / ส่วนที่เหลือ<br />
	</td>
</tr>
<tr>
	<td colspan=2>
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
<div style="margin: 0; padding: 0;"><h1>รายงานสมาชิกชำระเงินค่าซื้อ Member</h1></div>
<br />
<div style="text-align: left"><b>สาขา : </b><?=$branch_str;?></div>
<br />
<div style="text-align: left"><b>เลือกดู : </b>
<?php
	switch($member_view){
		case "1":
			echo "ชำระมัดจำ";
			break;
		case "0":
			echo "ชำระเต็มจำนวน / ส่วนที่เหลือ";
			break;
		default:
			echo "ทั้งหมด";
			break;
	}
?>
</div>
<br />
<div style="text-align: left"><b>ณ วันที่ : </b><?=$on_date;?></div>
<br />
<?php
}

if($this->input->post("branch") !== null && $this->input->post("member_view") !== null && $this->input->post("on_date") !== null){
?>
<table border=0 cellpadding=5 cellspacing=<?=($mode==0 ? '1 style="margin-top: 5px;"' : '0');?>>
<tr class="thead">
	<td rowspan=2></td>
	<td rowspan=2 style="vertical-align: middle; text-align: center;">วันและเวลา</td>
	<td rowspan=2 style="vertical-align: middle; text-align: center;">สมาชิกผู้ซื้อ</td>
	<td rowspan=2 style="vertical-align: middle; text-align: center;">ประเภท</td>
	<td rowspan=2 style="vertical-align: middle; text-align: center;">สัญญา</td>
	<td rowspan=2 style="vertical-align: middle; text-align: center;">ราคา</td>
	<td rowspan=2 style="vertical-align: middle; text-align: center;">ส่วนลด</td>
	<td rowspan=2 style="vertical-align: middle; text-align: center;">ยอดหลังส่วนลด</td>
	<td colspan=2 style="vertical-align: middle; text-align: center;">มัดจำ</td>
	<td colspan=2 style="vertical-align: middle; text-align: center;">ชำระส่วนที่เหลือ / เต็มจำนวน</td>
	<td rowspan=2 style="vertical-align: middle; text-align: center;">เริ่มต้นวันที่</td>
	<td rowspan=2 style="vertical-align: middle; text-align: center;">ถึงวันที่</td>
	<td rowspan=2 style="vertical-align: middle; text-align: center;">ผู้ทำรายการ</td>
	<td rowspan=2 style="vertical-align: middle; text-align: center;">หมายเหตุ</td>
</tr>
<tr class="thead">
	<td style="vertical-align: middle; text-align: center;">เงินสด</td>
	<td style="vertical-align: middle; text-align: center;">บัตร</td>
	<td style="vertical-align: middle; text-align: center;">เงินสด</td>
	<td style="vertical-align: middle; text-align: center;">บัตร</td>
</tr>
<?php
	$this->db->distinct("A.member_ext_id, A.received_datetime");
	$this->db->from("member_ext_payment A");
	$this->db->join("member_ext B", "A.member_ext_id = B.member_ext_id", "INNER");
	$this->db->join("member C", "B.member_id = C.member_id", "INNER");
	$this->db->where("A.branch_id", $branch);
	$this->db->where("STR_TO_DATE(A.received_datetime,'%Y-%m-%d') = STR_TO_DATE('$on_date','%d/%m/%Y')");
	if($member_view !== NULL && $member_view != "-1")
		$this->db->where("A.is_deposit", $member_view);
	$this->db->order_by("STR_TO_DATE(A.received_datetime, '%Y-%m-%d %H:%i:%s')");
	$this->db->limit($this->config->item('record_per_page'), ($page - 1) * $this->config->item('record_per_page'));
	//$this->db->limit(($page - 1) * $this->config->item('record_per_page'), $this->config->item('record_per_page'));
	$all_payment = $this->db->get();
	$total_record = $all_payment->num_rows();
	if($all_payment->num_rows() == 0){
?>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: center;" colspan=16>ไม่มีข้อมูล</td>
</tr>
<?php
	} else {
		$sum_price = 0;
		$sum_discount = 0;
		$sum_total_price = 0;
		
		$sum_deposit_cash = 0;
		$sum_deposit_card = 0;
		$sum_full_cash = 0;
		$sum_full_card = 0;
		
		$row_num = ($page - 1) * $this->config->item('record_per_page');
		$member_ext_id = "";
		foreach($all_payment->result_array() as $all_payment_data){
			if($member_ext_id != $all_payment_data["member_ext_id"]){
			$member_ext_id = $all_payment_data["member_ext_id"];
				$row_num++;
?>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: center;"><?=$row_num;?></td>
	<td style="vertical-align: middle; text-align: left;"><?=formatDBDateTime($all_payment_data["received_datetime"]);?></td>
<?php
				$this->db->select("D.branch_name, C.member_type_name, B.full_amount, B.discount_amount, B.discount_by, B.member_ext_id, B.receive_deposit_datetime, B.receive_full_datetime, A.firstname_th, A.lastname_th, A.nickname_th, B.contract_no, B.start_date, B.expiry_date, B.discount_note, GET_NAME_BY_USER_ID(B.ext_by) process_by");
				$this->db->from("member A");
				$this->db->join("member_ext B", "A.member_id = B.member_id", "INNER");
				$this->db->join("member_type C", "B.member_type_id = C.member_type_id", "INNER");
				$this->db->join("branch D", "A.create_branch_id = D.branch_id", "INNER");
				$this->db->where("B.member_ext_id", $all_payment_data["member_ext_id"]);
				$member_ext = $this->db->get();
				$member_ext = $member_ext->row(0);
				
				$sum_price += $member_ext->full_amount;
				$sum_discount += $member_ext->discount_amount;
				$sum_total_price += $member_ext->full_amount - $member_ext->discount_amount;
?>
	<td style="vertical-align: middle; text-align: left;"><?=$member_ext->firstname_th . " " . $member_ext->lastname_th . (trim($member_ext->nickname_th) != "" ? " (" . $member_ext->nickname_th . ")" : "");?></td>
	<td style="vertical-align: middle; text-align: center;"><?=($member_ext->receive_full_datetime != "" ? $member_ext->member_type_name : "");?></td>
	<td style="vertical-align: middle; text-align: center;"><?=$member_ext->contract_no;?></td>
	<td style="vertical-align: middle; text-align: center;"><?=number_format($member_ext->full_amount);?></td>
	<td style="vertical-align: middle; text-align: center;"><?=($member_ext->discount_amount != "" ? number_format($member_ext->discount_amount) : "");?></td>
	<td style="vertical-align: middle; text-align: center;"><?=($member_ext->discount_amount == "" ? number_format($member_ext->full_amount) : number_format($member_ext->full_amount - $member_ext->discount_amount))?></td>
<?php
				$this->db->select("SUM(IFNULL(amount, '')) amount");
				$this->db->from("member_ext_payment");
				$this->db->where("member_ext_id", $all_payment_data["member_ext_id"]);
				$this->db->where("payment_type", 0);
				$this->db->where("is_deposit", 1);
				$this->db->where("branch_id", $branch);
				$deposit_cash = $this->db->get()->row(0);
				$sum_deposit_cash += $deposit_cash->amount;
				
				$this->db->select("SUM(IFNULL(amount, '')) amount");
				$this->db->from("member_ext_payment");
				$this->db->where("member_ext_id", $all_payment_data["member_ext_id"]);
				$this->db->where("payment_type", 1);
				$this->db->where("is_deposit", 1);
				$this->db->where("branch_id", $branch);
				$deposit_card = $this->db->get()->row(0);
				$sum_deposit_card += $deposit_card->amount;
?>
	<td style="vertical-align: middle; text-align: center;"><?=($deposit_cash->amount == 0 ? "" : number_format($deposit_cash->amount));?></td>
	<td style="vertical-align: middle; text-align: center;"><?=($deposit_card->amount == 0 ? "" : number_format($deposit_card->amount));?></td>
<?php
				$this->db->select("SUM(IFNULL(amount, '')) amount");
				$this->db->from("member_ext_payment");
				$this->db->where("member_ext_id", $all_payment_data["member_ext_id"]);
				$this->db->where("payment_type", 0);
				$this->db->where("is_deposit", 0);
				$this->db->where("branch_id", $branch);
				$full_cash = $this->db->get()->row(0);
				//echo $this->db->last_query();
				$sum_full_cash += $full_cash->amount;
				
				$this->db->select("SUM(IFNULL(amount, '')) amount");
				$this->db->from("member_ext_payment");
				$this->db->where("member_ext_id", $all_payment_data["member_ext_id"]);
				$this->db->where("payment_type", 1);
				$this->db->where("is_deposit", 0);
				$this->db->where("branch_id", $branch);
				$full_card = $this->db->get()->row(0);
				$sum_full_card += $full_card->amount;
?>
	<td style="vertical-align: middle; text-align: center;"><?=($full_cash->amount == 0 ? "" : number_format($full_cash->amount));?></td>
	<td style="vertical-align: middle; text-align: center;"><?=($full_card->amount == 0 ? "" : number_format($full_card->amount));?></td>
	<td style="vertical-align: middle; text-align: center;"><?=$member_ext->start_date;?></td>
	<td style="vertical-align: middle; text-align: center;"><?=$member_ext->expiry_date;?></td>
	<td style="vertical-align: middle; text-align: left;"><?=$member_ext->process_by;?></td>
	<td style="vertical-align: middle; text-align: left;">
<?php
				if($member_ext->discount_note != "") {
					echo $member_ext->discount_note . " - โดย ";
					$this->db->select("A.emp_id, B.fullname emp_fullname, B.nickname emp_nickname, A.manual_owner_name");
					$this->db->from("user A");
					$this->db->join("employee B", "A.emp_id = B.emp_id", "LEFT OUTER");
					$this->db->where("A.user_id", $member_ext->discount_by);
					$discount_by = $this->db->get();
					$discount_by = $discount_by->row(0);
					if($discount_by->emp_id == "-1") echo $discount_by->manual_owner_name;
					else echo $discount_by->emp_fullname . " (". $discount_by->emp_nickname . ")";
				}
?>
	</td>
</tr>
<?php
			}
		}
		$total_page = ceil($total_record / $this->config->item("record_per_page"));
		if($page == $total_page){
?>
<tr>
	<td colspan=5 style="vertical-align: middle; text-align: right;">รวม : </td>
	<td style="vertical-align: middle; text-align: center;"><?=(number_format($sum_price) != "0" ? number_format($sum_price) : "0");?></td>
	<td style="vertical-align: middle; text-align: center;"><?=(number_format($sum_discount) != "0" ? number_format($sum_discount) : "0");?></td>
	<td style="vertical-align: middle; text-align: center;"><?=(number_format($sum_total_price) != "0" ? number_format($sum_total_price) : "0");?></td>
	<td style="vertical-align: middle; text-align: center;"><?=(number_format($sum_deposit_cash) != "0" ? number_format($sum_deposit_cash) : "");?></td>
	<td style="vertical-align: middle; text-align: center;"><?=(number_format($sum_deposit_card) != "0" ? number_format($sum_deposit_card) : "");?></td>
	<td style="vertical-align: middle; text-align: center;"><?=(number_format($sum_full_cash) != "0" ? number_format($sum_full_cash) : "");?></td>
	<td style="vertical-align: middle; text-align: center;"><?=(number_format($sum_full_card) != "0" ? number_format($sum_full_card) : "");?></td>
	<td colspan=4></td>
</tr>
<?php
		}
?>
</table>
<?php
		$this->load->view("Report/pagination", array("mode" => $mode, "page" => $page, "total_record" => $total_record));
	}
}
?>
</div>
</body>
</html>