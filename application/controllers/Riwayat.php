<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Riwayat extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->model('m_dashboard');

	}

	function pilih_riwayat(){
		$this->session->set_userdata('id_riwayat',$this->input->get('id_riwayat'));
		
		redirect('riwayat');
	}
	
	public function index(){
		
		
		$dt = [];
		$data_op = $this->db->join('t_lokasi','t_lokasi.idlokasi=lokasi_logger')->get('t_logger')->result_array();
		$key = 0;
		foreach($data_op as $k=>$v){
			$riwayat = $this->db->where('id_logger',$v['id_logger'])->get('t_riwayat')->result_array();
			$data_op[$k]['riwayat'] = [];
			if($riwayat){
				$ind = $key;
				$dt[$ind] = $v;
				$dt[$ind]['riwayat'] = $riwayat;
				$key +=1;
			}
		}
		if(!$this->session->userdata('id_riwayat')){
			$this->session->set_userdata('id_riwayat',$dt[0]['riwayat'][0]['id_riwayat']);
		}
		$selected = $this->session->userdata('id_riwayat');
		$data_riwayat = $this->db->join('t_logger','t_logger.id_logger = t_riwayat.id_logger')->join('t_lokasi','t_lokasi.idlokasi = t_logger.lokasi_logger')->where('t_riwayat.id_riwayat',$selected)->get('t_riwayat')->row();
		
		$data['data_op'] = $dt;
		$data['selected'] = $this->db->where('id_riwayat',$selected)->get('t_riwayat')->row();
		$data_gambar = json_decode(file_get_contents('http://bbwsso.monitoring4system.com/image/tes.php?folder=riwayat_op&word='.$data['selected']->gambar));
		
		if($data_gambar){
			$data['gambar'] = $data_gambar;
		}else{
			$data['gambar'] = [];
		}
		
		$data['konten']='konten/back/riwayat_op';
		$this->load->view('template_admin/site',$data);
	}
}