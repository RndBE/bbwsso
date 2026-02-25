<?php 

class Pengaturan extends CI_Controller {
	
	public function index () {
		redirect ('beranda');
	}
	
	public function tingkat_siaga_awlr (){
		$data_awlr = $this->db->join('t_lokasi','t_lokasi.idlokasi = t_logger.lokasi_logger')->where('kategori_log','2')->get('t_logger')->result_array();
		foreach($data_awlr as $key => $vl){
			
			$data_awlr[$key]['list_notif'] = $this->db->where('id_logger',$vl['id_logger'])->where('status','1')->get('tingkat_siaga_awlr')->result_array();
			if($data_awlr[$key]['list_notif']){
				$data_awlr[$key]['status'] = true;
			}else{
				$data_awlr[$key]['status'] = false;
			}
		}
		$data['data_awlr'] = $data_awlr;
		$data['konten'] = 'konten/back/v_pengaturan';
		$data['setting'] = 'konten/back/tingkat_siaga_awlr';
		$this->load->view('template_admin/site', $data);
	}
	
	public function rumus_debit (){
		$data_awlr = $this->db->join('t_lokasi','t_lokasi.idlokasi = t_logger.lokasi_logger')->where('kategori_log','2')->get('t_logger')->result_array();
		foreach($data_awlr as $key=>$val) {
			$rumus = $this->db->where('id_logger',$val['id_logger'])->get('rumus_debit')->row();
			if($rumus){
				$data_awlr[$key]['set_rumus'] = true;
			}else{
				$data_awlr[$key]['set_rumus'] = false;
			}
		}
		$data['data_awlr'] = $data_awlr;
		$data['konten'] = 'konten/back/v_pengaturan';
		$data['setting'] = 'konten/back/rumus_debit';
		$this->load->view('template_admin/site', $data);
	}
	
	public function indikator_curah_hujan (){
		
		$data['konten'] = 'konten/back/v_pengaturan';
		$data['setting'] = 'konten/back/indikator_hujan';
		$this->load->view('template_admin/site', $data);
	}
	
	public function unduh_aplikasi (){
		
		$data['konten'] = 'konten/back/v_pengaturan';
		$data['setting'] = 'konten/back/download_aplikasi';
		$this->load->view('template_admin/site', $data);
	}
	
	public function edit_notifikasi($id_logger) {
		
		$status = $this->input->post('status_notif');
		$nilai_list = $this->input->post('nilai_list');
		$nama_list = $this->input->post('nama_list');
		
		if($status =='on'){
			$this->db->where('id_logger',$id_logger);
			$this->db->delete('tingkat_siaga_awlr');
			$i = 1;
			foreach($nilai_list as $key=>$vl){
				$data = [
					'id_logger'=>$id_logger,
					'nama'=>$nama_list[$key],
					'nilai'=>$vl,
					'id_status'=>$i++,
					'status'=>'1',
				]; 
				$this->db->insert('tingkat_siaga_awlr',$data);
			}
			$data = [
				'id_logger'=>$id_logger,
				'nama'=>'Aman',
				'nilai'=>'0',
				'status'=>'0',
				'id_status'=>'0',
			]; 
			$this->db->insert('tingkat_siaga_awlr',$data);
			$jeda_notif = [
				'jeda_notif'=>$this->input->post('jeda_notif')
			];
			$this->db->where('id_logger',$id_logger);
			$this->db->update('t_logger',$jeda_notif);
		}else{
			
			$this->db->where('id_logger',$id_logger);
			$this->db->delete('tingkat_siaga_awlr');
			
			$jeda_notif = [
				'jeda_notif'=>0
			];
			$this->db->where('id_logger',$id_logger);
			$this->db->update('t_logger',$jeda_notif);
		}
		redirect('pengaturan/tingkat_siaga_awlr');
	}
	
	public function tambah_siaga (){
		$nama_siaga = $this->input->post('nama');
		$nilai = $this->input->post('nilai');
		
		$data = [
			'nama'=>$nama_siaga,
			'nilai'=>$nilai,
			'status'=>'1'
		];
		
		$insert = $this->db->insert('tingkat_siaga_awlr',$data);
		if($insert){
			redirect('pengaturan/tingkat_siaga_awlr');
		}
	}
	
	public function update_siaga ($id){
		$nama_siaga = $this->input->post('nama');
		$nilai = $this->input->post('nilai');
		
		$data = [
			'nama'=>$nama_siaga,
			'nilai'=>$nilai
		];
		$this->db->where('id',$id);
		$update = $this->db->update('tingkat_siaga_awlr',$data);
		if($update){
			redirect('pengaturan/tingkat_siaga_awlr');
		}
	}
	
	public function hapus($id){
		$this->db->where('id',$id);
		$delete = $this->db->delete('tingkat_siaga_awlr');
		if($delete){
			redirect('pengaturan/tingkat_siaga_awlr');
		}
	}
}