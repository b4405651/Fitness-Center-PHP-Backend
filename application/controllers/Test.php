<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {
	public function TestCI()
	{
		echo "ทดสอบ CodeIgniter<br />";
		echo "<form method='post' action='/Test/Result/'>";
		echo "<input type='text' name='param' value='' /><br />";
		echo "<input type='submit' value='submit' />";
		echo "</form>";
		echo "ทดสอบ json_encode = " . json_encode("ทดสอบ json_encode", JSON_UNESCAPED_UNICODE);
	}
	
	public function TestPHP()
	{
		echo "ทดสอบ PHP<br />";
		echo "<form method='post' action='/test_php.php'>";
		echo "<input type='text' name='param' value='' /><br />";
		echo "<input type='submit' value='submit' />";
		echo "</form>";
		echo "ทดสอบ json_encode = " . json_encode("ทดสอบ json_encode", JSON_UNESCAPED_UNICODE);
	}
	
	public function Result()
	{
		echo $this->input->post('param');
	}
}
?>