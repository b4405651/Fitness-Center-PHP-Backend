<div style="padding: 10px;">
<?php
$this->db->where("is_suspend", 0);
$branch_list = $this->db->get("branch");
if($mode == 0){
?>
<h1>รายชื่อสินค้าทั้งหมด</h1>
<form method="post">
<input type="hidden" name="mode" id="mode" value="<?=$mode;?>" />
<input type="hidden" name="report_name" value="product_list" />
<br />
<button onclick="$('#mode').val('2'); $('form').attr('target', '_self'); $('form').submit(); return false;">EXCEL</button>
<button onclick="$('#mode').val('3'); $('form').attr('target', '_blank'); $('form').submit(); return false;">PRINT</button>
</form>
<?php
} else {
?>
<div style="margin: 0; padding: 0;"><h1>รายชื่อสินค้าทั้งหมด</h1></div>
<br />
<?php
}
?>
<table border=0 cellpadding=5 cellspacing=<?=($mode==0 ? '1 style="margin-top: 5px;"' : '0');?>>
<tr class="thead">
	<td></td>
	<td>ชื่อสินค้า</td>
	<td>ราคา</td>
	<td>บาร์โค้ด</td>
</tr>
<?php
$this->db->select("*");
$this->db->from("product");
$this->db->limit($this->config->item('record_per_page'), ($page - 1) * $this->config->item('record_per_page'));
//$this->db->limit(($this->data['page'] - 1) * $this->config->item('record_per_page'), $this->config->item('record_per_page'));
$this->db->order_by("product_name, product_code");

$product = $this->db->get();
$total_record = $product->num_rows();
if($product->num_rows() == 0){
?>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: center;" colspan=4>ไม่มีข้อมูล</td>
</tr>
<?php
} else {
	$row_num = ($page - 1) * $this->config->item('record_per_page');
	foreach($product->result_array() as $product_data){
		$row_num++;
?>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: center;"><?=$row_num;?></td>
	<td style="vertical-align: middle; text-align: left;"><?=$product_data["product_name"];?></td>
	<td style="vertical-align: middle; text-align: center;"><?=number_format($product_data["price"]);?></td>
	<td style="vertical-align: middle; text-align: left;"><?=$product_data["product_code"];?></td>
</tr>
<?php
	}
}
?>
</table>
<?php
$this->load->view("Report/pagination", array("mode" => $mode, "page" => $page, "total_record" => $total_record));
?>