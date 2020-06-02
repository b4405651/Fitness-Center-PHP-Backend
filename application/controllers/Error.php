<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Error extends CI_Controller {
	public function get_last_query_string(){
		validateFields(array("user_id"));
		exit(json_encode(Array("result" => array("queryString" => $this->db->last_query()))));
	}
}
?>