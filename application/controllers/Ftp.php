<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ftp extends CI_Controller {
	function __construct()
	{
		parent :: __construct();
		$this->load->model('mftp');
		$this->load->library('csvimport');
		$this->load->library('upload');
	}


	############ Update DB ###############################	
	function sinkrondatadb1() {

		$file_name = $this->input->get('nama_file');
		$tabel = $this->input->get('tabel');
		$file_path =  './filelogger/'.$file_name;
		if ($this->csvimport->parse_file($file_path)) {
			$csv_array = $this->csvimport->parse_file($file_path);

			foreach ($csv_array as $row) {
				$cekdata=$this->db->query('select waktu,code_logger from '.$tabel.'  where code_logger="'.$row['id_alat'].'" and waktu = "'.$row['tanggal'].' '.$row['jam'].'"');
				if($cekdata->num_rows() == 0)
				{
					$insert_data = array(
						'code_logger'=>$row['id_alat'],
						'waktu'=>$row['tanggal'].' '.$row['jam'],
						'sensor1'=>$row['sensor1'],
						'sensor2'=>$row['sensor2'],
						'sensor3'=>$row['sensor3'],
						'sensor4'=>$row['sensor4'],
						'sensor5'=>$row['sensor5'],
						'sensor6'=>$row['sensor6'],
						'sensor7'=>$row['sensor7'],
						'sensor8'=>$row['sensor8'],
						'sensor9'=>$row['sensor9'],
						'sensor10'=>$row['sensor10'],
						'sensor11'=>$row['sensor11'],
						'sensor12'=>$row['sensor12'],
						'sensor13'=>$row['sensor13'],
						'sensor14'=>$row['sensor14'],
						'sensor15'=>$row['sensor15'],
						'sensor16'=>$row['sensor16'],

					);
					//$this->mftp->insert_ftp($insert_data,$tabel);
					//echo $insert_data;
					echo json_encode($insert_data) .'<br/>';

				}
				else{
					echo 'Data sudah ada <br/>' ;
				}

			}

			//echo "<pre>"; print_r($insert_data);
		} else {
			echo 'gagal parsing';
		}
	}
	########### End Update DB #################

	############ Update DB ###############################	
	function sinkrondatabyrequest() {
		$file_name = $this->input->get('nama_file');
		//$tabel = $this->input->get('tabel');
		$file_path =  './filelogger/'.$file_name;
		$idlogger = substr( $file_name, 0, 5 );
		if(file_exists($file_path))
		{
			$ceklogger=$this->db->query('select * from t_logger INNER JOIN kategori_logger ON t_logger.kategori_log=kategori_logger.id_katlogger where id_logger = "'.$idlogger.'"');

			if($ceklogger->num_rows() == 0)
			{
				echo 'Logger tidak tersedia';
			}
			else{
				$cek = $ceklogger->row();
				$tabel = $cek->tabel;
				//echo $tabel;
				if ($this->csvimport->parse_file($file_path)) {
					$csv_array = $this->csvimport->parse_file($file_path);

					foreach ($csv_array as $row) {

						$cekdata=$this->db->query('select waktu,code_logger from '.$tabel.'  where code_logger="'.$row['id_alat'].'" and waktu = "'.$row['tanggal'].' '.$row['jam'].'"');
						//$cekdata=$this->db->query('select * from '.$tabel.'  where code_logger="'.$idlogger.'" and waktu = "'.$row['tanggal'].' '.$row['jam'].'"');
						if($cekdata->num_rows() == 0)
						{
							$insert_data = array(
								'code_logger'=>$row['id_alat'],
								'waktu'=>$row['tanggal'].' '.$row['jam'],
								'sensor1'=>$row['sensor1'],
								'sensor2'=>$row['sensor2'],
								'sensor3'=>$row['sensor3'],
								'sensor4'=>$row['sensor4'],
								'sensor5'=>$row['sensor5'],
								'sensor6'=>$row['sensor6'],
								'sensor7'=>$row['sensor7'],
								'sensor8'=>$row['sensor8'],
								'sensor9'=>$row['sensor9'],
								'sensor10'=>$row['sensor10'],
								'sensor11'=>$row['sensor11'],
								'sensor12'=>$row['sensor12'],
								'sensor13'=>$row['sensor13'],
								'sensor14'=>$row['sensor14'],
								'sensor15'=>$row['sensor15'],
								'sensor16'=>$row['sensor16'],

							);
							//$this->mftp->insert_ftp($insert_data,$tabel);
							//echo $insert_data;
							echo json_encode($insert_data) .'<br/>';

						}else{
							echo 'Data sudah ada';
						}
					}



					//echo "<pre>"; print_r($insert_data);
				} else {
					echo 'gagal parsing';
				}
				unlink($file_path);
				$updatedatasinkron=array(
									'idlogger'=>$idlogger,
									'tanggal'=> '0'
								);
				$this->db->where('idlogger',$idlogger);
				$this->db->update('set_sinkronisasi',$updatedatasinkron);
			}
		}
		else {
			echo 'File tidak ditemukan';
		}

	}
	########### End #################



	function sinkronisasi()
	{
		$this->load->view('sinkronisasi/view_sinkronisasi');
	}

	function set_sinkronisasi()
	{
		if(empty($this->input->post('tanggal')))
		{
			$tanggal = '0';
		}
		else
		{
			$tanggal = $this->input->post('tanggal');
		}

		$data=array(
			'idlogger'=>$this->input->post('id_logger'),
			'tanggal'=> $tanggal
		);


		$ceklogger=$this->db->query('select * from set_sinkronisasi where idlogger = "'.$this->input->post('id_logger').'"');
		if($ceklogger->num_rows() > 0)
		{
			//update
			$this->mftp->update_sinkron($data);
			$this->session->set_flashdata('pesan','Berhasil Update data sinkronisasi => '.json_encode($data));
		}
		else
		{
			//insert
			$this->mftp->insert_sinkron($data);
			$this->session->set_flashdata('pesan','Berhasil Tambah data sinkronisasi => '.json_encode($data));
		}
		redirect('ftp/sinkronisasi');
	}

	function get_sinkron()
	{
		$ceklogger=$this->db->query('select * from set_sinkronisasi where idlogger = "'.$this->input->get('idlogger').'"');
		if($ceklogger->num_rows() > 0)
		{
			foreach($ceklogger->result() as $sinkron)
			{

				if($sinkron->tanggal == 0)
				{
					$tanggal = '0';
					$filemodif= '0';
					$file = '0';
				}
				else{

					$date=date_create($sinkron->tanggal);
					$tanggal = date_format($date,'Ymd');
					$path='./filelogger/'.$sinkron->idlogger.'-'.$tanggal.'.csv';
					if(file_exists($path))
					{
						$file = '1';
						$filemodif= date("Y-m-d H:i:s", filemtime('./filelogger/'.$sinkron->idlogger.'-'.$tanggal.'.csv'));
					}
					else
					{
						$file = '0';
						$filemodif= '0';
					}
				}


				$data_sinkron =array(
					'idlogger' => $sinkron->idlogger,
					'filename' => $tanggal,
					'filexist' => $file,
					'fileinfo' => $filemodif,
					'ip' => getenv("REMOTE_ADDR")
				);
			}
			echo json_encode($data_sinkron);
		}
		else
		{
			//echo 'Logger belum ditambahkan di data sinkronisasi.';
			$data_sinkron2 =array(
				'idlogger' => "id_log belum ada",
				'filename' => "",
				'filexist' => "",
				'fileinfo' => "",
				'ip' => getenv("REMOTE_ADDR")
			);

			echo json_encode($data_sinkron2);
		}
	}

	############ Update DB ###############################	
	function sinkrondataperjam() {
		//$query_info= $this->db->where('logger_id', $idlogger )->get('t_informasi')->result();
		$dbsetsinkron=$this->db->get('set_sinkronisasi');
		if($dbsetsinkron->num_rows() > 0)
		{
			foreach($dbsetsinkron->result() as $logger)
			{
				$tanggal = date('Ymd');
				$path='./filelogger/'.$logger->idlogger.'-'.$tanggal.'.csv';
				$filenama=$logger->idlogger.'-'.$tanggal.'.csv';
				if(file_exists($path))
				{
					######### SEND FTP ########
					//$this->sendfile($filenama);
					//echo $path.'<br/>';
					####### Cek lOGGER ##############
					$ceklogger=$this->db->query('select * from t_logger INNER JOIN kategori_logger ON t_logger.kategori_log=kategori_logger.id_katlogger where id_logger = "'.$logger->idlogger.'"');

					if($ceklogger->num_rows() == 0)
					{
						echo 'Logger tidak tersedia';
					}
					else{
						$cek = $ceklogger->row();
						$tabel = $cek->tabel;
						//echo 'Lanjut Sinkron';
						//$tabel='t_demo';

						if ($this->csvimport->parse_file($path)) {
							$csv_array = $this->csvimport->parse_file($path);
							//echo count($csv_array);
							foreach ($csv_array as $row) {

								$cekdata=$this->db->query('select waktu,code_logger from '.$tabel.'  where code_logger="'.$row['id_alat'].'" and waktu = "'.$row['tanggal'].' '.$row['jam'].'"');
								//$cekdata=$this->db->query('select * from '.$tabel.'  where code_logger="'.$idlogger.'" and waktu = "'.$row['tanggal'].' '.$row['jam'].'"');
								if($cekdata->num_rows() == 0)
								{
									$insert_data = array(
										'code_logger'=>$row['id_alat'],
										'waktu'=>$row['tanggal'].' '.$row['jam'],
										'sensor1'=>$row['sensor1'],
										'sensor2'=>$row['sensor2'],
										'sensor3'=>$row['sensor3'],
										'sensor4'=>$row['sensor4'],
										'sensor5'=>$row['sensor5'],
										'sensor6'=>$row['sensor6'],
										'sensor7'=>$row['sensor7'],
										'sensor8'=>$row['sensor8'],
										'sensor9'=>$row['sensor9'],
										'sensor10'=>$row['sensor10'],
										'sensor11'=>$row['sensor11'],
										'sensor12'=>$row['sensor12'],
										'sensor13'=>$row['sensor13'],
										'sensor14'=>$row['sensor14'],
										'sensor15'=>$row['sensor15'],
										'sensor16'=>$row['sensor16'],

									);
									$this->mftp->insert_ftp($insert_data,$tabel);
									echo 'data ditambahkan';
									//echo json_encode($insert_data) .'<br/>';

								}
								/*else{
							echo 'Data sudah ada';
						}*/
							}
						}
						unlink($path);
						
						$updatedatasinkron=array(
									'idlogger'=>$logger->idlogger,
									'tanggal'=> '0'
								);
						//update
						$this->db->where('idlogger',$idlogger);
						$this->db->update('set_sinkronisasi',$updatedatasinkron);
					}
					###### eND Cek Logger #######
				}
			}
		}
		else{
			echo 'tidak ada data';
		}


	}
	########### End #################
	function cekfile()
	{
		$file_name = $this->input->get('nama_file');
		$file_path =  './filelogger/'.$file_name;
		$idlogger = substr( $file_name, 0, 5 );
		if(file_exists($file_path))
		{
			echo 'File '.$file_name.' tersedia.';
		}else{
			echo 'File '.$file_name.' tidak ditemukan.';
		}
	}
	############### Send File ############
	function sendfile($filepath)
	{
		$file_path = './filelogger/'.$filepath;
		//$file_path = './filelogger/10001-20230804.csv';
		$target_url = "https://monitoring4system.com/terimafile/cek_data";           

		$headers = array('Authorization: Beacon Engineering','Content-type: multipart/form-data'); 

		$csv_file = realpath($file_path);
		$post_data =array('file_contents'=>new CURLFile($csv_file));

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_VERBOSE, true);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_URL, $target_url);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		$response = curl_exec($curl);
		//	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl); 
		//	echo $response ;
	}
	
	function csv()
	{
		$file_path =  './filelogger/10030-20230926.csv';
		$csvData=file_get_contents($file_path);
		$rows = str_getcsv($csvData, ',','','\r');
		echo json_encode($rows);
	}

}

