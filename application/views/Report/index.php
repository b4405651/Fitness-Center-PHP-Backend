<?php
$this->session->unset_userdata('logging_in');

/*$this->db->select("member_checkin_id, checkin_datetime");
$this->db->where("member_checkin_id >= 21932");
$this->db->where("member_checkin_id <= 27170");
$data = $this->db->get("member_checkin");
$this->db->trans_start();
foreach($data->result_array() as $obj){
	$ms = substr($obj["checkin_datetime"], strpos($obj["checkin_datetime"], "."), strlen($obj["checkin_datetime"]) - strpos($obj["checkin_datetime"], "."));
	$datetime = DateTime::createFromFormat('Y-m-d H:i:s.u', $obj["checkin_datetime"]);
	//echo $datetime->format('Y-m-d H:i:s') . "<BR>";
	$datetime->add(new DateInterval('PT11H'));
	
	$update_data = array("checkin_datetime" => $datetime->format('Y-m-d H:i:s') . $ms);
	$this->db->where('member_checkin_id', $obj["member_checkin_id"]);
	$this->db->update('member_checkin', $update_data);
	
	//echo $obj["checkin_datetime"] . " => " . $datetime->format('Y-m-d H:i:s') . $ms . "<BR>";
}
$this->db->trans_complete();*/

if(!$this->session->has_userdata('user_id')){
?>
<form action="<?php echo base_url();?>User/Login" method="post">
	<table cellspacing=0 style="border: 1px solid #1E90FF; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);">
	<tr>
		<td align=right><b>username : </b></td>
		<td><input name="username" value="" /></td>
	</tr>
	<tr>
		<td align=right><b>password : </b></td>
		<td><input type="password" name="password" value="" /></td>
	</tr>
	<tr>
		<td colspan=2 align=center>
			<input type="submit" style="border: none; background-color: #1E90FF; color: white; padding: 10px;" value="เข้าสู่ระบบ" />
		</td>
	</tr>
	</table>
</form>
<?php
}
?>
</div>
</body>
</html>