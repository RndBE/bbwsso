<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends CI_Controller {


	public function index()
	{
		$data['konten']='konten/hal_home';
		$this->load->view('template/site',$data);
	}
	
	function remove_session()
	{
		$tes = time() - 720*60;
		//$cek = $this->db->where('timestamp <= ',$tes)->get('ci_sessions')->result_array();
		
		//foreach($cek as $v) {
		$this->db->where('timestamp <= ',$tes);
		$this->db->delete('ci_sessions');
		//}
		
	}
}
