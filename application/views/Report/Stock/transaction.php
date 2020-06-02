<div style="padding: 10px;">
<?php
$this->db->where("is_suspend", 0);
$branch_list = $this->db->get("branch");
if($mode == 0){
?>
<h1>รายงานการเข้าออก ของสินค้า</h1>
<form method="post">
<input type="hidden" name="mode" id="mode" value="<?=$mode;?>" />
<input type="hidden" name="report_name" value="product_transaction" />
<table border=0 cellpadding=5 cellspacing=5 style="margin: 5px 0 5px 0;">
<tr class="thead">
	<td style="color: white;">ของสาขา</td>
</tr>
<tr>
	<td style="vertical-align: top; border: none;">
<?php
foreach($branch_list->result_array() as $branch_data){
?>
		<input type="radio" name="branch_id" value="<?=$branch_data["branch_id"]; ?>"<?php if($branch_data["branch_id"] == $branch_id) { ?> checked<?php }?> /><?=$branch_data["branch_name"]; ?><br />
<?php
}
?>
	</td>
</tr>
<tr class="thead">
	<td>
		สินค้า :&nbsp;
		<select name="product_id" id="product_id">
			<option value="" selected>เลือกสินค้า</option>
<?php
$this->db->select("product_id, product_name, price");
$this->db->from("product");
$product = $this->db->get();
foreach($product->result_array() as $product_data){
?>
			<option value="<?=$product_data["product_id"];?>"<?php if($product_id == $product_data["product_id"]) echo " selected";?>><?=$product_data["product_name"] . " (" . number_format($product_data["price"]) . " บาท.)"?></option>
<?php
}
?>
		</select>
	</td>
</tr>
<tr>
	<td>
		ตั้งแต่วันที่ : <input name="start_date" class="datepicker" value="<?=$start_date;?>" /><br />
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
foreach($branch_list->result_array() as $branch_data){
	if($branch_data["branch_id"] == $branch_id) $branch_str .= $branch_data["branch_name"] . ", ";
}
$branch_str = substr($branch_str, 0, strlen($branch_str) - 2);
?>
<div style="margin: 0; padding: 0;"><h1>รายงานการเข้าออก ของสินค้า</h1></div>
<br />
<div style="text-align: left"><b>ของสาขา : </b><?=$branch_str;?></div>
<div style="text-align: left"><b>สินค้า : </b>
<?php
if($product_id == "") echo "-";
else {
	$this->db->select("product_name, price");
	$this->db->from("product");
	$this->db->where("product_id", $product_id);
	$product = $this->db->get();
	$product = $product->row(0);
	echo $product->product_name . " (" . number_format($product->price) . " บาท.)";
}
?>
</div>
<br />
<div style="text-align: left"><b>ตั้งแต่วันที่ : </b><?=$start_date;?></div>
<div style="text-align: left"><b>ถึงวันที่ : </b><?=$end_date;?></div>
<br />
<?php
}

if($this->input->post("branch_id") && $this->input->post("branch_id") != "" && $this->input->post("product_id") && $this->input->post("product_id") != "" && $this->input->post("start_date") && $this->input->post("end_date")){
?>
<table border=0 cellpadding=5 cellspacing=<?=($mode==0 ? '1 style="margin-top: 5px;"' : '0');?>>
<tr class="thead">
	<td></td>
	<td>วัน และ เวลา</td>
	<td>จำนวน</td>
	<td>เอกสารอ้างอิง</td>
</tr>
<?php
	$this->db->select("A.trx_datetime, B.amount, A.ref");
	$this->db->from("product_trx A");
	$this->db->join("product_trx_detail B", "A.product_trx_id = B.product_trx_id", "INNER");
	$this->db->where("B.product_id", $product_id);
	$this->db->where("A.branch_id", $branch_id);
	$this->db->where("B.is_void", 0);
	$this->db->where("STR_TO_DATE(A.trx_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('$start_date','%d/%m/%Y') AND STR_TO_DATE('$end_date','%d/%m/%Y')");
	$this->db->limit($this->config->item('record_per_page'), ($page - 1) * $this->config->item('record_per_page'));
	//$this->db->limit(($page - 1) * $this->config->item('record_per_page'), $this->config->item('record_per_page'));
	$this->db->order_by("A.trx_datetime");
	$trx = $this->db->get();
	
	$total_record = $trx->num_rows();
	if($trx->num_rows() == 0){
?>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: center;" colspan=4>ไม่มีข้อมูล</td>
</tr>
<?php
	} else {
		$row_num = ($page - 1) * $this->config->item("record_per_page");
		foreach($trx->result_array() as $trx_data){
			$row_num++;
?>
<tr class="tbody">
	<td style="vertical-align: middle; text-align: center;"><?=$row_num;?></td>
	<td style="vertical-align: middle; text-align: left;"><?=formatDBDateTime($trx_data["trx_datetime"]);?></td>
	<td style="vertical-align: middle; text-align: center;"><?=number_format($trx_data["amount"]);?></td>
	<td style="vertical-align: middle; text-align: left;"><?=$trx_data["ref"];?></td>
</tr>
<?php
		}
	}
	if($page == ceil($total_record / $this->config->item("record_per_page"))){
?>
<tr class="tsum">
	<td style="vertical-align: middle; text-align: right;" colspan=2>รวมสุทธิ</td>
	<td style="vertical-align: middle; text-align: center;">
<?php
		$this->db->select("IFNULL(SUM(B.amount), 0) amount");
		$this->db->from("product_trx A");
		$this->db->join("product_trx_detail B", "A.product_trx_id = B.product_trx_id", "INNER");
		$this->db->where("B.product_id", $product_id);
		$this->db->where("A.branch_id", $branch_id);
		$this->db->where("B.is_void", 0);
		$this->db->where("STR_TO_DATE(A.trx_datetime,'%Y-%m-%d') BETWEEN STR_TO_DATE('$start_date','%d/%m/%Y') AND STR_TO_DATE('$end_date','%d/%m/%Y')");
		$balance = $this->db->get();
		$balance = $balance->row(0);
		echo number_format($balance->amount);
?>
	</td>
	<td></td>
</tr>
<?php
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
	if($('[name="branch_id"]:checked').length == 0){
		alert("ยังไม่ได้เลือก 'สาขา' !");
		return false;
	}
	
	if($('#product_id').val() == ""){
		alert("ยังไม่ได้เลือก 'สินค้า' !");
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