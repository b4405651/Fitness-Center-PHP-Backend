<div style="padding: 10px;">
<?php
$this->db->where("is_suspend", 0);
$branch_list = $this->db->get("branch");
if($mode == 0){
?>
<h1>จำนวนสินค้าคงเหลือ</h1>
<form method="post">
<input type="hidden" name="mode" id="mode" value="<?=$mode;?>" />
<input type="hidden" name="report_name" value="product_balance" />
<table border=0 cellpadding=5 cellspacing=5 style="margin: 5px 0 5px 0;">
<tr class="thead">
	<td style="color: white;">ของสาขา</td>
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
		ณ วันที่ : <input name="on_date" class="datepicker" value="<?=$on_date;?>" />
	</td>
</tr>
</table>
<button onclick="$('#mode').val('<?=$mode;?>'); $('form').attr('target', '_self');">เรียกรายงาน</button>
<button onclick="$('#mode').val('2'); $('form').attr('target', '_self'); $('form').submit(); return false;">EXCEL</button>
<button onclick="$('#mode').val('3'); $('form').attr('target', '_blank'); $('form').submit(); return false;">PRINT</button>
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
<div style="margin: 0; padding: 0;"><h1>จำนวนสินค้าคงเหลือ</h1></div>
<br />
<div style="text-align: left"><b>ของสาขา : </b><?=$branch_str;?></div>
<div style="text-align: left"><b>ณ วันที่ : </b><?=$on_date;?></div>
<br />
<?php
}

if($this->input->post("branch") && $this->input->post("on_date")){
?>
<table border=0 cellpadding=5 cellspacing=<?=($mode==0 ? '1 style="margin-top: 5px;"' : '0');?>>
<tr class="thead">
	<td rowspan=2></td>
	<td rowspan=2>ชื่อสินค้า</td>
<?php
	foreach($branch_list->result_array() as $branch_data){
		if((count($branch) == 1 && $branch[0] == "-1") || in_array($branch_data["branch_id"], $branch)){
?>
	<td><?=$branch_data["branch_name"];?></td>
<?php
		}
	}
?>
	<td rowspan=2>ราคา</td>
	<td rowspan=2>บาร์โค้ด</td>
</tr>
<tr class="thead">
<?php
	foreach($branch_list->result_array() as $branch_data){
		if((count($branch) == 1 && $branch[0] == "-1") || in_array($branch_data["branch_id"], $branch)){
?>
	<td>คงเหลือ</td>
<?php
		}
	}
?>
</tr>
<?php
	$this->db->select("*");
	$this->db->from("product");
	$this->db->limit($this->config->item('record_per_page'), ($page - 1) * $this->config->item('record_per_page'));
	//$this->db->limit(($page - 1) * $this->config->item('record_per_page'), $this->config->item('record_per_page'));
	$this->db->order_by("product_name, product_code");
	$balance = $this->db->get();
	$total_record = $balance->num_rows();
	if($balance->num_rows() == 0){
?>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: center;" colspan=5>ไม่มีข้อมูล</td>
</tr>
<?php
	} else {
		$row_num = ($page - 1) * $this->config->item('record_per_page');
		foreach($balance->result_array() as $balance_data){
			$row_num++;
?>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: center;"><?=$row_num;?></td>
	<td style="vertical-align: middle; text-align: left;"><?=$balance_data["product_name"];?></td>
<?php
	foreach($branch_list->result_array() as $branch_data){
		if((count($branch) == 1 && $branch[0] == "-1") || in_array($branch_data["branch_id"], $branch)){
			$this->db->select("IFNULL(SUM(amount), 0) amount");
			$this->db->from("product_trx_detail");
			$this->db->where("branch_id", $branch_data["branch_id"]);
			$this->db->where("product_id", $balance_data["product_id"]);
			$this->db->where("is_void", 0);
			$product = $this->db->get();
			$product = $product->row(0);
?>
	<td style="vertical-align: middle; text-align: center;"><?=number_format($product->amount);?></td>
<?php
		}
	}
?>
	<td style="vertical-align: middle; text-align: center;"><?=$balance_data["price"];?></td>
	<td style="vertical-align: middle; text-align: center;"><?=$balance_data["product_code"];?></td>
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