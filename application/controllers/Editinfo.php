<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Editinfo extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->model('m_inputdata');
	}
	
	public function index()
	{
		//$query=;
		$q_info=$this->db->query("SELECT t_logger.*,t_informasi.*,t_lokasi.*,t_garansi.tgl_kontrak,t_garansi.no_kontrak,t_garansi.tgl_aktif,t_garansi.garansi FROM `t_logger` join t_informasi on t_logger.id_logger=t_informasi.logger_id join t_lokasi on t_logger.lokasi_logger=t_lokasi.idlokasi join t_garansi on t_logger.id_logger=t_garansi.id_logger"); //
		$tab_info=array();
		foreach($q_info->result() as $info){
			$tabel_info="<tr><td>".$info->nama_logger."</td><td>".$info->id_logger."</td><td>".$info->seri."</td><td>".$info->sensor."</td><td>".$info->nosell."</td><td>".$info->nama_lokasi."</td><td>".$info->latitude."</td><td>".$info->longitude."</td><td>".$info->elevasi."</td><td>".$info->tgl_aktif."</td><td>".$info->tgl_kontrak."</td><td>".$info->garansi."</td><td>".$info->no_kontrak."</td>
			<td><a class='btn btn-outline-primary' href='Editinfo/edit/".$info->id."' role='button'>Sunting</a></td></tr>";
			//$tabel_info="<tr><td>".$info->nama_logger."</td></tr>";
			$tab_info[]= $tabel_info;
		}
//<td>".anchor('Editinfo/tambah/'.$info->id,'Tambah ')."</td>
		
		 $tabinfo= "
<h2 class='mx-3 my-2'>Sunting Data Informasi</h2>
<br>
<a class='btn btn-outline-success mx-4' href='Editinfo/tambah/' role='button' style='float:right'>Tambah</a>
<br>
<br>
<br>
<div class='mx-4 card'>
		<table class='table table-striped table-hover table-sm'>
		<thead><tr><th>Nama Logger</th><th>ID Logger</th><th>Seri Logger</th><th>Sensor</th><th>No. Seluler</th><th>Nama Pos</th><th>Latitude</th><th>Longitude</th><th>Elevasi</th><th>Pemasangan</th><th>Tanggal Kontrak</th><th>Garansi</th><th>No Kontrak</th>
		<th>Aksi</th></tr></thead>
		<tbody>".join($tab_info)."</tbody>
		</table></div>"; 
		
		$data['konten']=$tabinfo;
		$this->load->view('v_tabel_info',$data);
	}
	
	function edit($id) {
		$where = array('id' => $id);
		//$query = $this->db->query("SELECT * FROM `t_logger` join t_info on t_logger.id_logger=t_info.logger_id join t_lokasi on t_logger.lokasi_logger=t_lokasi.idlokasi where id='".$id."'");
		
		//foreach ($query->result as $q) {
		//	echo $q;
		//}
		$data['user'] = $this->m_inputdata->edit_data_info($id)->result();
		//print_r($data);
		$this->load->view('v_edit_info',$data);	
	}
	
	function tambah() {
	$this->load->view('v_tambah_info');	
	}
	
	function tambah_info() {
	$id = $this->input->post('id');
	$idlokasi = $this->input->post('idlokasi');
	$idinfo = $this->input->post('idinfo');
		
	 
	$data1 = array(
		"id_logger" => $this->input->post('codelogger'),
		"nama_logger" => $this->input->post('namalogger'),
		"tgl_pemasangan" => $this->input->post('pemasangan'),
		"garansi" => $this->input->post('garansi'),

	);
		
			$data2 = array(
		"logger_id" => $this->input->post('codelogger'),
		"seri_logger" => $this->input->post('serilogger'),
		"sensor" => $this->input->post('sensor'),
		"elevasi" => $this->input->post('elev'),
		"nosell" => $this->input->post('nosell'),

	);
			$data3 = array(
		"nama_lokasi" => $this->input->post('namalok'),
		"latitude" => $this->input->post('lat'),
		"longitude" => $this->input->post('long'),
	);

		/**
		$where1 = array(
			'id' => $id
		);

		$where3 = array(
			'idlokasi' => $idlokasi
		);

		$where2 = array(
			'idinfo' => $idinfo
		);*/
 
	//print_r($data);
	//$this->m_inputdata->update_data_awr_crud($where,$data,'t_klimatologi');
		$this->m_inputdata->tambah_data_info($data1,'t_logger',$data2,'t_info',$data3,'t_lokasi',);

	//print_r($where);
	redirect('editinfo');

	}
	
	function update_info() {
		$id = $this->input->post('id');
		$idlokasi = $this->input->post('idlokasi');
		$idinfo = $this->input->post('idinfo');

	 
	$data1 = array(
		"nama_logger" => $this->input->post('namalogger'),
		//"tgl_pemasangan" => $this->input->post('pemasangan'),
		//"garansi" => $this->input->post('garansi'),

	);

		$data2 = array(
			"seri" => $this->input->post('serilogger'),
			"sensor" => $this->input->post('sensor'),
			"elevasi" => $this->input->post('elev'),
			"nosell" => $this->input->post('nosell'),

		);
		$data3 = array(
			"nama_lokasi" => $this->input->post('namalok'),
			"latitude" => $this->input->post('lat'),
			"longitude" => $this->input->post('long'),
		);
		
		$data4 = array(
			"no_kontrak" => $this->input->post('nokontrak'),
			"tgl_kontrak" => $this->input->post('tglkontrak'),
			"tgl_aktif" => $this->input->post('pemasangan'),
			"garansi" => $this->input->post('garansi'),
		);

		$where1 = array(
			'id' => $id
		);

		$where3 = array(
			'idlokasi' => $idlokasi
		);

		$where2 = array(
			'id_inf' => $idinfo
		);
 
		$where4 = array(
			'id_logger' => $this->input->post('idlogger')
		);
	//print_r($data);
	//$this->m_inputdata->update_data_awr_crud($where,$data,'t_klimatologi');
		$this->m_inputdata->update_data_info($where1,$data1,'t_logger',$where2,$data2,'t_informasi',$where3,$data3,'t_lokasi',$where4,$data4,'t_garansi',);

	//print_r($where);
	redirect('editinfo');
	}
}
