<div style="padding: 10px;">
<?php
$this->db->where("is_suspend", 0);
$branch_list = $this->db->get("branch");

if($mode == 0){
?>
<h1>ยอดขายสินค้า ณ วันที่</h1>
<form method="post">
<input type="hidden" name="mode" id="mode" value="<?=$mode;?>" />
<input type="hidden" name="report_name" value="shop_sales" />
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
<div style="margin: 0; padding: 0;"><h1>ยอดขายสินค้า ณ วันที่</h1></div>
<br />
<div style="text-align: left"><b>สาขา : </b><?=$branch_str;?></div>
<br />
<div style="text-align: left"><b>ณ วันที่ : </b><?=$on_date;?></div>
<br />
<?php
	}

	if($this->input->post("branch") && $this->input->post("on_date")){
?>
<table border=0 cellpadding=5 cellspacing=<?=($mode==0 ? '1 style="margin-top: 5px;"' : '0');?>>
<tr class="thead">
	<td style="vertical-align: middle; text-align: center;"></td>
	<td style="vertical-align: middle; text-align: center;">วันและเวลา</td>
	<td style="vertical-align: middle; text-align: center;">เลขที่บิล</td>
	<td style="vertical-align: middle; text-align: center;">เงินสด</td>
	<td style="vertical-align: middle; text-align: center;">บัตร</td>
</tr>
<?php
	$this->db->select("A.bill_id, A.shop_id, A.receive_datetime, B.bill_no, SUM(C.amount) cash, SUM(D.amount) card");
	$this->db->from("shop A");
	$this->db->join("bill B", "A.bill_id = B.bill_id", "LEFT OUTER");
	$this->db->join("shop_payment C", "A.shop_id = C.shop_id and C.payment_type = 0", "LEFT OUTER");
	$this->db->join("shop_payment D", "A.shop_id = D.shop_id and D.payment_type = 1", "LEFT OUTER");
	$this->db->where("STR_TO_DATE(A.receive_datetime,'%Y-%m-%d') = STR_TO_DATE('$on_date','%d/%m/%Y')");
	
	if(!(count($branch) == 1 && $branch[0] == "-1"))
		$this->db->where_in("A.branch_id", $branch);
	
	$this->db->group_by("A.shop_id");
	$this->db->order_by("A.receive_datetime");
	$this->db->limit($this->config->item('record_per_page'), ($page - 1) * $this->config->item('record_per_page'));
	//$this->db->limit(($page - 1) * $this->config->item('record_per_page'), $this->config->item('record_per_page'));
	$bill = $this->db->get();
	if($bill->num_rows() == 0){
?>
<tr class="tbody">
	<td colspan="5" style="vertical-align: middle; text-align: center;">ไม่มีข้อมูล</td>
</tr>
<?php
	} else {
		$row_num = ($page - 1) * $this->config->item('record_per_page');
		$sum_cash = 0;
		$sum_card = 0;
		foreach($bill->result_array() as $bill_data){
			$row_num++;
			
			$sum_cash += $bill_data["cash"];
			$sum_card += $bill_data["card"];
			
			if($row_num % 2 == 0) $backgroundColor = "white";
			else $backgroundColor = "#edf3f3";
?>
<tr style="background-color: <?=$backgroundColor;?>;">
	<td style="vertical-align: middle; text-align: center;"><?=number_format($row_num);?></td>
	<td style="vertical-align: middle; text-align: left;"><?=formatDBDateTime($bill_data['receive_datetime']);?></td>
	<td style="vertical-align: middle; text-align: center;"><?=$bill_data['bill_no'];?></td>
	<td style="vertical-align: middle; text-align: center;"><?=($bill_data['cash'] != "" ? number_format($bill_data['cash']) : "");?></td>
	<td style="vertical-align: middle; text-align: center;"><?=($bill_data['card'] != "" ? number_format($bill_data['card']) : "");?></td>
</tr>
<tr>
	<td colspan=5 style="vertical-align: middle; text-align: left; background-color: <?=$backgroundColor;?>;">
<?php
			$this->db->select("B.product_name, A.amount, A.unit_price");
			$this->db->from("shop_detail A");
			$this->db->join("product B", "A.product_id = B.product_id", "INNER");
			$this->db->where("A.shop_id", $bill_data['shop_id']);
			$bill_detail = $this->db->get();
			foreach($bill_detail->result_array() as $bill_detail_data){
?>
		<?="- " . $bill_detail_data["product_name"] . " (@" . number_format($bill_detail_data["unit_price"]) . ".-) x " . number_format($bill_detail_data["amount"]) . "<br />";?>
<?php
			}
?>
	</td>
</tr>
<?php
		}
?>
<tr class="tsum">
	<td colspan=3 style="vertical-align: middle; text-align: right;">รวม : </td>
	<td style="vertical-align: middle; text-align: center;"><?=number_format($sum_cash);?></td>
	<td style="vertical-align: middle; text-align: center;"><?=number_format($sum_card);?></td>
</tr>
<?php
	}
?>
</table>
<?php
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