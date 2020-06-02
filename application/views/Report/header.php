<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
if($mode == 2) {
	$filename = $this->input->post('report_name') . "_" . TODAY_UI(true) . ".xls";

	header("Content-Disposition: attachment; filename=\"$filename\"");
	header("Content-Type: application/vnd.ms-excel");
}
?>
<html<?php if($mode == 3){ ?> moznomarginboxes mozdisallowselectionprint<?php } ?>>
<head>
	<title>FAMS REPORT</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="icon" href="<?=base_url()?>/favicon.ico">
	<script type="text/javascript" src="<?=base_url();?>jquery-2.1.4.min.js"></script>
	<link rel="stylesheet" href="<?=base_url();?>jquery-ui-1.11.4.custom/jquery-ui.css">
	<script src="<?=base_url();?>jquery-ui-1.11.4.custom/jquery-ui.js"></script>
	<link rel="stylesheet" type="text/css" href="<?=base_url();?>style.css">
	<script>
		var dateBefore=null;
		$(function(){
			$( "input[type=submit], button" ).button();
			$(".datepicker").attr('readonly', true).datepicker({  
				dateFormat: 'dd/mm/yy',  
				buttonImageOnly: false,  
				dayNamesMin: ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'],   
				monthNamesShort: ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'],  
				changeMonth: true,  
				changeYear: true,  
				beforeShow:function(){    
					if($(this).val()!=""){  
						var arrayDate=$(this).val().split("/");       
						arrayDate[2]=parseInt(arrayDate[2])-543;  
						$(this).val(arrayDate[0]+"/"+arrayDate[1]+"/"+arrayDate[2]);  
					}  
					setTimeout(function(){  
						$.each($(".ui-datepicker-year option"),function(j,k){  
							var textYear=parseInt($(".ui-datepicker-year option").eq(j).val())+543;  
							$(".ui-datepicker-year option").eq(j).text(textYear);  
						});               
					},50);  
				},  
				onChangeMonthYear: function(){  
					setTimeout(function(){  
						$.each($(".ui-datepicker-year option"),function(j,k){  
							var textYear=parseInt($(".ui-datepicker-year option").eq(j).val())+543;  
							$(".ui-datepicker-year option").eq(j).text(textYear);  
						});               
					},50);        
				},  
				onClose:function(){  
					if($(this).val()!=""){           
						var arrayDate;
						if($(this).val()!="") arrayDate=$(this).val().split("/");  
						else arrayDate=dateBefore.split("/");
						arrayDate[2]=parseInt(arrayDate[2])+543;  
						$(this).val(arrayDate[0]+"/"+arrayDate[1]+"/"+arrayDate[2]);      
					}         
				},  
				onSelect: function(dateText, inst){   
					dateBefore=$(this).val();  
					var arrayDate=dateText.split("/");  
					arrayDate[2]=parseInt(arrayDate[2]);  
					$(this).val(arrayDate[0]+"/"+arrayDate[1]+"/"+arrayDate[2]);  
				}
			});
		});
	</script>
</head>
<body style="margin: 0; padding: 0;"<?php if($mode == 3) {?> onload="window.print(); window.close();"<?php }?>>
<?php
if($this->session->has_userdata('user_id')){
	$menu_web_list = array();
	if($this->session->has_userdata('menu_web_list')){
		$menu_web_list = explode("!!", $this->session->userdata('menu_web_list'));
	}
	
	if($mode == 0){
?>
<script type="text/javascript" src="<?=base_url();?>menu.js"></script>
<ul id="sddm">
<?php
if((count($menu_web_list) == 1 && $menu_web_list[0] == -1) || in_array("1", $menu_web_list)){
?>
    <li id="1">
		<a href="#" onmouseover="mopen('m1')" onmouseout="mclosetime()">รายได้สาขา</a>
		<div id="m1" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
			<a href="<?=site_url(array('Report', 'Summary', 'DuringDate'));?>">ระหว่างวันที่</a>
			<a href="<?=site_url(array('Report', 'Summary', 'OnDate'));?>">ณ วันที่ และ สะสม</a>
        </div>
    </li>
<?php
}
?>
<?php
if((count($menu_web_list) == 1 && $menu_web_list[0] == -1) || in_array("2", $menu_web_list)){
?>
    <li id="2">
		<a href="#" onmouseover="mopen('m2')" onmouseout="mclosetime()">
			สมาชิก
		</a>
        <div id="m2" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
			<a href="<?=site_url(array('Report', 'Member', 'ListMember'));?>">รายชื่อสมาชิก</a>
			<a href="<?=site_url(array('Report', 'Member', 'SellMember'));?>">รายงานสมาชิกชำระเงินค่าซื้อ Member</a>
			<a href="<?=site_url(array('Report', 'Member', 'SellPT'));?>">รายงานการขาย PT</a>
			<a href="<?=site_url(array('Report', 'Member', 'CheckIn'));?>">รายงานการเข้าใช้บริการ</a>
			<a href="<?=site_url(array('Report', 'Member', 'UsePT'));?>">รายงานการใช้ PT</a>
        </div>
    </li>
<?php
}
?>
<?php
if((count($menu_web_list) == 1 && $menu_web_list[0] == -1) || in_array("3", $menu_web_list)){
?>
    <li id="3">
		<a href="#" onmouseover="mopen('m3')" onmouseout="mclosetime()">
			พนักงาน
		</a>
        <div id="m3" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
			<a href="<?=site_url(array('Report', 'Employee', 'ListEmployee'));?>">รายชื่อและรายได้</a>
        </div>
    </li>
<?php
}
?>
<?php
if((count($menu_web_list) == 1 && $menu_web_list[0] == -1) || in_array("4", $menu_web_list)){
?>
	<li id="4">
		<a href="#" onmouseover="mopen('m4')" onmouseout="mclosetime()">
			เทรนเนอร์
		</a>
		<div id="m4" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
			<a href="<?=site_url(array('Report', 'PT', 'ListPT'));?>">รายชื่อและรายได้</a>
			<a href="<?=site_url(array('Report', 'PT', 'Training'));?>">รายงานการเทรน</a>
			<a href="<?=site_url(array('Report', 'PT', 'Schedule'));?>">ตารางการทำงาน</a>
        </div>
	</li>
<?php
}
?>
<?php
if((count($menu_web_list) == 1 && $menu_web_list[0] == -1) || in_array("5", $menu_web_list)){
?>
	<li id="5">
		<a href="#" onmouseover="mopen('m5')" onmouseout="mclosetime()">
			คลังสินค้า
		</a>
		<div id="m5" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
			<a href="<?=site_url(array('Report', 'Stock', 'ListProduct'));?>">รายชื่อสินค้าทั้งหมด</a>
			<a href="<?=site_url(array('Report', 'Stock', 'Balance'));?>">จำนวนสินค้าคงเหลือ</a>
			<a href="<?=site_url(array('Report', 'Stock', 'Transaction'));?>">รายงานการเข้าออก ของสินค้า</a>
        </div>
	</li>
<?php
}
?>
<?php
if((count($menu_web_list) == 1 && $menu_web_list[0] == -1) || in_array("6", $menu_web_list)){
?>
	<li id="6">
		<a href="#" onmouseover="mopen('m6')" onmouseout="mclosetime()">
			การขายสินค้า
		</a>
		<div id="m6" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
			<a href="<?=site_url(array('Report', 'Shop', 'Sales'));?>">รายงานการขายสินค้า ณ วันที่</a>
			<a href="<?=site_url(array('Report', 'Shop', 'Revenue'));?>">ยอดขายสินค้าสะสม</a>
			<a href="<?=site_url(array('Report', 'Shop', 'Void'));?>">รายงานการ VOID บิล</a>
        </div>
	</li>
<?php
}
?>
	<li>
		<a href="/User/Logout/">
			ออกจากระบบ
		</a>
	</li>
</ul>
<div style="clear:both"></div>
<br style="margin-bottom: -20px;" />
<?php 
	}
}
?>