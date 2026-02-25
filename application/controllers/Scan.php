<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class scan extends CI_Controller {
function __construct()
	{
		parent :: __construct();
		//$this->load->model('mlokasi');
		
	}

public function index()
	{
		echo "tes";
	}

function qrcode() {
		$idlogger=$this->uri->segment(3);
		$q_logger=$this->db->query("select * from t_logger where id_logger='".$idlogger."' ");
		
		if($q_logger->num_rows() > 0)
		{
			$data['idlogger']=$this->uri->segment(3);
			$this->load->view('scan',$data);
		}
		else{
			echo "kosong";
		}

}
	
	
	
	#################### Akhir ##########################
		
}


