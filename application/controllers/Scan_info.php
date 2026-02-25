<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Scan_info extends CI_Controller {
	
	public function infologger(){
		$idlogger=$this->uri->segment(3);
		$q_logger= $this->db->where('id_logger', $idlogger )->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')->get('t_logger')->row();
		$q_info= $this->db->where('logger_id', $idlogger )->get('t_informasi')->row();
			$info = array(
				'idlogger' =>$q_info->logger_id,
				'seri' => $q_info->seri,
				'sensor' => str_replace('<br/>',',',$q_info->sensor),
				'elevasi' => $q_info->elevasi,
				'nosell' => $q_info->nosell,
				'pic' => '-',
				'nopic' => '-',
				'pos' => $q_logger->nama_lokasi
			);
	
		################## Data terakhir logger #############
		$data_sensor=array();
		
		$iduser='1';
		$q_katlogger= $this->db->where('id_katlogger', $q_logger->kategori_log )->get('kategori_logger')->row();
		$tabel_temp=$q_katlogger->temp_data;
		if($q_katlogger->tabel == 'debit')
		{
			$q_dtterakhir=$this->db->limit(1)->order_by('waktu','desc')->where('id_logger', $idlogger )->get('debit')->row();
		}
		else{
			$q_dtterakhir=$this->db->where('code_logger', $idlogger )->get($tabel_temp)->row();
		}	

		$q_sensor=$this->db->where('logger_id', $idlogger )->get('parameter_sensor')->result();
		foreach($q_sensor as $sensor)
		{  

			if($sensor->kolom_sensor != 'debit' && $sensor->kolom_sensor != 'status')
			{
				$alias=$sensor->nama_parameter;
				$field_sensor=$sensor->kolom_sensor;
				$data_sensor1=$q_dtterakhir->$field_sensor;
				$satuan = $sensor->satuan;
				$data_sensor[] = array('nama_paramater'=> $alias,'value' => $data_sensor1,'satuan' => $satuan);
			}



		}
		
		$data_terakhir=array('waktu' => $q_dtterakhir->waktu,
							'data'=> json_encode($data_sensor));
		
		#################### Info User #########################
		$q_user= $this->db->where('id_user', $iduser )->get('t_user')->row();
		$data_user=array( 
			'user' => $q_user->nama,
			'alamat' => $q_user->alamat,
			'telp' =>'-'
		);
		
		################### APLikasi ###########################
		$aplikasi =array(
					'website' => 'https://ikn.monitoring4system.com',
					'android' => '-',
					'ios' => '-'
					);
		
		########################################################
		$data=array(
		'info' => $info,
		'data_terakhir' => $data_terakhir,
		'user_info' => $data_user,
		'aplikasi' => $aplikasi
		);
		
		echo json_encode($data);
		
		
	}
	
}