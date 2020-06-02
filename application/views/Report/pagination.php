<?php
if($mode == 0){
	if($total_record > 0){
		$total_page = ceil($total_record / $this->config->item("record_per_page"));
?>
<div style="margin-top: 0px;">
<button id="first_btn" style="font-size: 10px; font-weight: bold;">|<</button>
<button id="prev_btn" style="font-size: 10px; font-weight: bold;"><<</button>
<span style="margin-left: 20px; margin-right: 20px;">
หน้าปัจจุบัน : <input type="text" name="page" id="page" value="<?=($page == "" ? "1" : $page);?>" style="width: 50px; text-align: center;" />
&nbsp;/&nbsp;<?=number_format($total_page);?>
</span>
<button id="next_btn" style="font-size: 10px; font-weight: bold;">>></button>
<button id="last_btn" style="font-size: 10px; font-weight: bold;">>|</button>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
ข้อมูลทั้งหมด : <?=number_format($total_record);?>
</div>
<script>
$(function(){
	if(<?=$page;?> > <?=$total_page;?> || <?=$page;?> < 1 || <?=$total_page;?> == 1)
	{
		$('#first_btn, #prev_btn, #next_btn, #last_btn').button( "option", "disabled", true );
		$('#page').attr('disabled', 'disabled');
	}
	
	switch(<?=$page;?>){
		case 1:
			$('#first_btn, #prev_btn').button( "option", "disabled", true );
			break;
		case <?=$total_page;?>:
			$('#next_btn, #last_btn').button( "option", "disabled", true );
			break;
		default:
			$('#first_btn, #prev_btn, #next_btn, #last_btn').button( "option", "disabled", false );
	}
	
	$('#first_btn').click(function(){
		$('#page').val('1');
		$('#page').change();
	});
	
	$('#prev_btn').click(function(){
		$('#page').val(parseInt($('#page').val()) - 1);
		$('#page').change();
	});
	
	$('#next_btn').click(function(){
		$('#page').val(parseInt($('#page').val()) + 1);
		$('#page').change();
	});
	
	$('#last_btn').click(function(){
		$('#page').val(<?=$total_page;?>);
		$('#page').change();
	});
	
	$('#page').change(function(){
		if(parseInt($(this).val()) <= 1) {
			$(this).val('1');
			$('#next_btn, #last_btn').button( "option", "disabled", false );
			$('#first_btn, #prev_btn').button( "option", "disabled", true );
		} else if(parseInt($(this).val()) >= <?=$total_page;?>) {
			$(this).val('<?=$total_page;?>');
			$('#first_btn, #prev_btn').button( "option", "disabled", false );
			$('#next_btn, #last_btn').button( "option", "disabled", true );
		} else {
			$('#first_btn, #prev_btn, #next_btn, #last_btn').button( "option", "disabled", false );
		}
		
		$('form').submit();
	});
});
</script>
<?php
	}
}
?>