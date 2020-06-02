<div style="padding: 10px;">
<?php
$this->db->where("is_suspend", 0);
$branch_list = $this->db->get("branch");

$date_param = explode('/', $on_date);
if($mode == 0){
?>
<h1>รายงานการขาย PT</h1>
<form method="post">
<input type="hidden" name="mode" id="mode" value="<?=$mode;?>" />
<input type="hidden" name="report_name" value="sell_pt" />
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
<div style="margin: 0; padding: 0;"><h1>รายงานการขาย PT</h1></div>
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
	<td rowspan="2"></td>
	<td rowspan="2" style="vertical-align: middle; text-align: center;">วันและเวลา</td>
	<td rowspan="2" style="vertical-align: middle; text-align: center;">คอร์ส PT</td>
	<td rowspan="2" style="vertical-align: middle; text-align: center;">ราคา</td>
	<td colspan="2" style="vertical-align: middle; text-align: center;">การชำระเงิน</td>
	<td rowspan="2" style="vertical-align: middle; text-align: center;">สมาชิกผู้ซื้อ</td>
	<td rowspan="2" style="vertical-align: middle; text-align: center;">ผู้ขาย PT</td>
	<td rowspan="2" style="vertical-align: middle; text-align: center;">ทำรายการโดย</td>
	<td rowspan="2" style="vertical-align: middle; text-align: center;">หมายเหตุ</td>
</tr>
<tr class="thead">
	<td style="vertical-align: middle; text-align: center;">เงินสด</td>
	<td style="vertical-align: middle; text-align: center;">บัตร</td>
</tr>
<?php
	$sum_price = 0;
	$sum_cash = 0;
	$sum_credit_card = 0;
	
	$this->db->distinct();
	$this->db->select("member_pt_id, receive_datetime");
	$this->db->from("member_pt_payment");
	$this->db->where("STR_TO_DATE(receive_datetime,'%Y-%m-%d') = STR_TO_DATE('$on_date','%d/%m/%Y')");
	$this->db->where("branch_id", $branch);
	$this->db->order_by("STR_TO_DATE(receive_datetime,'%Y-%m-%d %H:%i:%s')");
	$this->db->limit($this->config->item('record_per_page'), ($page - 1) * $this->config->item('record_per_page'));
	$paid_list = $this->db->get();
	$total_record = $paid_list->num_rows();
	if($paid_list->num_rows() == 0){
?>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: center;" colspan=10>ไม่มีข้อมูล</td>
</tr>
<?php
	} else {
		$count = 0;
		foreach($paid_list->result_array() as $paid_data){
			$count++;
			
			$this->db->select("A.process_datetime, CONCAT(A.max_hours, ' ชม. ') pt_course_name, A.price, CONCAT(B.fullname, ' ( ', B.nickname, ' ) - ', B.emp_code) seller, GET_NAME_BY_USER_ID(A.process_by) process_by, A.start_date, A.expiry_date, A.member_pt_id, A.is_void, GET_NAME_BY_USER_ID(A.void_by) void_by, A.void_datetime, A.void_reason, D.member_no, D.firstname_th, D.lastname_th, D.nickname_th");
			$this->db->from("member_pt A");
			$this->db->join("employee B", "A.pt_seller_id = B.emp_id", "INNER");
			$this->db->join("employee C", "A.process_by = C.emp_id", "INNER");
			$this->db->join("member D", "A.member_id = D.member_id", "INNER");
			$this->db->where("A.member_pt_id", $paid_data["member_pt_id"]);
			$member_pt = $this->db->get();
			$member_pt = $member_pt->row(0);
			
			$sum_price += $member_pt->price;
?>
<tr class="tbody">
	<td><?=$count;?></td>
	<td align=left><?=formatDBDateTime($member_pt->process_datetime);?></td>
	<td><?=$member_pt->pt_course_name;?></td>
	<td><?=number_format($member_pt->price);?></td>
	<td>
<?php
			$this->db->select("amount");
			$this->db->from("member_pt_payment");
			$this->db->where("payment_type", 0);
			$this->db->where("member_pt_id", $paid_data["member_pt_id"]);
			$payment_list = $this->db->get();
			$cash = 0;
			foreach($payment_list->result_array() as $payment){
				$cash += $payment["amount"];
			}
			$sum_cash += $cash;
			echo (number_format($cash) != "0" ? number_format($cash) : "");
?>
	</td>
	<td>
<?php
			$this->db->select("amount");
			$this->db->from("member_pt_payment");
			$this->db->where("payment_type", 1);
			$this->db->where("member_pt_id", $paid_data["member_pt_id"]);
			$payment_list = $this->db->get();
			$credit_card = 0;
			foreach($payment_list->result_array() as $payment){
				$credit_card += $payment["amount"];
			}
			$sum_credit_card += $credit_card;
			echo (number_format($credit_card) != "0" ? number_format($credit_card) : "");
?>
	</td>
	<td align=left><?=(trim($member_pt->member_no) != "" ? $member_pt->member_no . " - " : "") . $member_pt->firstname_th . " " . $member_pt->lastname_th . (trim($member_pt->nickname_th) != "" ? " (" . $member_pt->nickname_th . ")" : "");?></td>
	<td align=left><?=$member_pt->seller;?></td>
	<td align=left><?=$member_pt->process_by;?></td>
	<td align=left><?=($member_pt->is_void == "1" ? "ยกเลิกโดย " . $member_pt->void_by . " สาเหตุ : " . $member_pt->void_reason : "");?></td>
</tr>
<?php
		}
	}
?>
<tr class="tbody">
	<td colspan=3 align=right>รวม : </td>
	<td><?=number_format($sum_price);?></td>
	<td><?=number_format($sum_cash);?></td>
	<td><?=number_format($sum_credit_card);?></td>
	<td colspan=4></td>
</tr>
</table>
<?php
	$this->load->view("Report/pagination", array("mode" => $mode, "page" => $page, "total_record" => $total_record));
}
?>
</div>
</body>
</html>