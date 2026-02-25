<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Terima extends CI_Controller {
	
	public function migrasi () {
		$id_logger = $this->input->post('id_logger');
		$bulan = $this->input->post('bulan');
		$tabel = $this->db->where('id_logger',$id_logger)->get('t_logger')->row()->tabel_main;
		$this->session->set_userdata('logger_m',$id_logger);
		$this->session->set_userdata('bln_m',$bulan);
		$jsonData = json_decode(file_get_contents('https://monitoring4system.com/kirim/kirim_data?id_logger='.$id_logger.'&bulan='.$bulan));
		
		$a = $this->db->insert_batch($tabel, $jsonData); 
		if($a){
			$data= array(
				'id_logger'=>$id_logger,
				'bulan'=>$bulan,
			);
			$this->db->insert('migrasi',$data);
			//file_get_contents('https://monitoring4system.com/kirim/hapus_data?id_logger='.$id_logger.'&bulan='.$bulan);
			redirect('terima/view_migrasi');
		}
	}
	
	public function view_migrasi () {
		$data['logger'] = $this->db->order_by('id_logger')->get('t_logger')->result_array();
		$migrasi = $this->db->order_by('id_logger')->order_by('bulan','desc')->get('migrasi')->result_array();
		
		
		foreach($data['logger'] as $key=>$vl){
			$data_migrasi = $this->db->where('id_logger', $vl['id_logger'])->order_by('bulan','desc')->get('migrasi')->result_array();
			$data_last = $this->db->where('id_logger', $vl['id_logger'])->order_by('bulan','desc')->limit(1)->get('migrasi')->row();
			$data['logger'][$key]['list_migrasi'] = $data_migrasi;
			$data['logger'][$key]['data_last'] = $data_last;
		}
		$data['list_migrasi'] = $migrasi;
		$this->load->view('v_migrasi',$data);
	}
}