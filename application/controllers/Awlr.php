<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Awlr extends CI_Controller
{

	function __construct()
	{
		parent::__construct();

		$this->load->library('csvimport');
		$this->load->model('m_awlr');
		if(!$this->session->userdata('logged_in'))
		{
			redirect('login');
		}
	}


	function kalimeneng($tmaInput) {
		$data = [
			['TMA' => 0.3,  'Cd' => 1.185, 'Bef' => 29.674],
			['TMA' => 0.35, 'Cd' => 1.185, 'Bef' => 29.721],
			['TMA' => 0.4,  'Cd' => 1.185, 'Bef' => 29.708],
			['TMA' => 0.5,  'Cd' => 1.185, 'Bef' => 29.692],
			['TMA' => 0.6,  'Cd' => 1.322, 'Bef' => 29.668],
			['TMA' => 1.0,  'Cd' => 1.322, 'Bef' => 29.608],
			['TMA' => 1.5,  'Cd' => 1.394, 'Bef' => 29.5],
			['TMA' => 2.0,  'Cd' => 1.415, 'Bef' => 29.46],
			['TMA' => 2.5,  'Cd' => 1.414, 'Bef' => 29.26],
			['TMA' => 3.0,  'Cd' => 1.394, 'Bef' => 29.14],
			['TMA' => 3.5,  'Cd' => 1.389, 'Bef' => 29.14],
			['TMA' => 7.5,  'Cd' => 1.389, 'Bef' => 29.02],
		];

		$K = 1.705;
		$n = count($data);

		// Pastikan TMA dalam jangkauan
		if ($tmaInput < $data[0]['TMA'] || $tmaInput > $data[$n - 1]['TMA']) {
			return "TMA $tmaInput di luar jangkauan data.";
		}

		for ($i = 0; $i < $n - 1; $i++) {
			$tma1 = $data[$i]['TMA'];
			$tma2 = $data[$i + 1]['TMA'];

			if ($tmaInput >= $tma1 && $tmaInput <= $tma2) {
				$cd1 = $data[$i]['Cd'];
				$cd2 = $data[$i + 1]['Cd'];
				$bef1 = $data[$i]['Bef'];
				$bef2 = $data[$i + 1]['Bef'];

				$Cd = $cd1 + (($tmaInput - $tma1) * ($cd2 - $cd1)) / ($tma2 - $tma1);
				$Bef = $bef1 + (($tmaInput - $tma1) * ($bef2 - $bef1)) / ($tma2 - $tma1);
				$Q = $K * $Cd * $Bef * pow($tmaInput, 1.5);

				return $Q;
			}
		}

		return 0;
	}


	function debit_interpolation($x) {
		// Data is now embedded within the function
		$data = [
			[0.20, 12.60],
			[0.40, 17.82],
			[0.60, 21.82],
			[0.80, 25.20],
			[1.00, 28.17],
			[1.20, 31.21],
			[1.40, 33.96],
			[1.60, 36.71],
			[1.80, 38.94],
			[2.00, 41.04],
			[2.20, 43.36],
			[2.40, 45.29],
			[2.60, 47.14],
			[2.80, 48.92],
			[3.00, 50.64],
			[3.20, 52.30],
			[3.40, 53.91],
			[3.60, 55.47],
			[3.80, 56.99],
			[4.00, 58.47],
			[4.20, 23.97],
			[4.40, 24.53],
			[4.60, 25.08],
			[4.80, 25.62],
			[5.00, 26.15],
			[5.20, 26.67],
		];

		usort($data, function ($a, $b) {
			return $a[0] - $b[0];
		});

		// Find the closest x values in the data
		$x1 = null;
		$x2 = null;
		$y1 = null;
		$y2 = null;
		foreach ($data as $point) {
			if ($point[0] <= $x) {
				$x1 = $point[0];
				$y1 = $point[1];
			}
			if ($point[0] >= $x) {
				$x2 = $point[0];
				$y2 = $point[1];
				break;
			}
		}
		if (is_null($x1) || is_null($x2)) {
			return 0;
		}
		if ($x == $x1) {
			return $y1;
		}
		$y = $y1 + (($x - $x1) / ($x2 - $x1)) * ($y2 - $y1);

		return $y;
	}

	### Dari Beranda ##########
	function set_sensordash()
	{
		$tabel = $this->input->get('tabel');
		$idparam = $this->input->get('id_param');

		$this->session->set_userdata('id_param', $this->input->get('id_param'));
		$this->session->set_userdata('tabel', $tabel);
		$tgl = date('Y-m-d');
		$this->session->set_userdata('pada', $tgl);
		$this->session->set_userdata('data', 'hari');
		$this->session->set_userdata('tanggal', $tgl);
		$this->session->set_userdata('mode', 'rerata');
		$q_parameter = $this->db->query("SELECT * FROM parameter_sensor where id_param='" . $idparam . "'");
		if ($q_parameter->num_rows() > 0) {
			$parameter = $q_parameter->row();
			//data hasil seleksi dimasukkan ke dalam $session
			$session = array(
				'idlogger' => $parameter->logger_id,
				'idparameter' => $parameter->id_param,
				'nama_parameter' => $parameter->nama_parameter,
				'kolom' => $parameter->kolom_sensor,
				'satuan' => $parameter->satuan,
				'tipe_grafik' => $parameter->tipe_graf,
				'kolom_acuan' => $parameter->kolom_acuan,
			);
			//data dari $session akhirnya dimasukkan ke dalam session
			$this->session->set_userdata($session);
			$querylogger = $this->db->query('select * from t_logger INNER JOIN t_lokasi ON t_logger.lokasi_logger=t_lokasi.idlokasi where id_logger="' . $parameter->logger_id . '";');
			$log = $querylogger->row();
			$lokasilog = $log->nama_lokasi;
			$this->session->set_userdata('namalokasi', $lokasilog);
		}
		$this->session->set_userdata('controller', 'awlr');
		redirect('awlr/analisa');
	}

	### Dari Analisa ##########
	function set_sensorselect()
	{

		$idlogger = $this->uri->segment(3);
		$tabel = $this->uri->segment(4);
		$this->session->set_userdata('tabel', $tabel);
		$tgl = date('Y-m-d');
		$this->session->set_userdata('pada', $tgl);
		$this->session->set_userdata('data', 'hari');
		$this->session->set_userdata('mode', 'rerata');
		$q_parameter = $this->db->query("SELECT * FROM parameter_sensor where logger_id='" . $idlogger . "'");
		if ($q_parameter->num_rows() > 0) {
			$parameter = $q_parameter->row();
			//data hasil seleksi dimasukkan ke dalam $session
			$session = array(
				'idlogger' => $parameter->logger_id,
				'idparameter' => $parameter->id_param,
				'nama_parameter' => $parameter->nama_parameter,
				'kolom' => $parameter->kolom_sensor,
				'satuan' => $parameter->satuan,
				'tipe_grafik' => $parameter->tipe_graf,
				'kolom_acuan' => $parameter->kolom_acuan
			);
			//data dari $session akhirnya dimasukkan ke dalam session
			$this->session->set_userdata($session);
			$querylogger = $this->db->query('select * from t_logger INNER JOIN t_lokasi ON t_logger.lokasi_logger=t_lokasi.idlokasi where id_logger="' . $parameter->logger_id . '";');
			$log = $querylogger->row();
			$lokasilog = $log->nama_lokasi;
			$this->session->set_userdata('namalokasi', $lokasilog);
		}
		$this->session->set_userdata('controller', 'awlr');
		redirect('awlr/analisa');
	}
	############################################

	public function download_file()
	{
		$filename = $this->input->post('filename');

		// Load the download helper
		$this->load->helper('download');

		// Path to the file you want to download
		$filePath = 'https://bbwsso.monitoring4system.com/unduh/laporan_op/' . $filename;
		echo $filePath;
		force_download($filePath, NULL);
		exit;
		// Check if the file exists
		if (file_exists($filePath)) {
			// Force download the file
			force_download($filePath, NULL);
		} else {
			echo 'File Tidak Ada';
		}
	}

	function set_param()
	{
		$tabel = $this->uri->segment(3);
		$idparam = $this->uri->segment(4);
		$lok = str_replace('_', ' ', $this->uri->segment(5));
		$this->session->set_userdata('namalokasi', $lok);
		$this->session->set_userdata('tabel', $tabel);
		$tgl = date('Y-m-d');
		$this->session->set_userdata('pada', $tgl);
		$this->session->set_userdata('data', 'hari');
		$q_parameter = $this->db->query("SELECT * FROM parameter_sensor where id_param='" . $idparam . "'");
		if ($q_parameter->num_rows() > 0) {
			$parameter = $q_parameter->row();
			//data hasil seleksi dimasukkan ke dalam $session
			$session = array(
				'idlogger' => $parameter->logger_id,
				'idparameter' => $parameter->id_param,
				'nama_parameter' => $parameter->nama_parameter,
				'kolom' => $parameter->kolom_sensor,
				'satuan' => $parameter->satuan,
				'tipe_grafik' => $parameter->tipe_graf,
				'kolom_acuan' => $parameter->kolom_acuan
			);
			//data dari $session akhirnya dimasukkan ke dalam session
			$this->session->set_userdata($session);
		}
		redirect('awlr/analisa');
	}

	### Set Pos #####
	public function pilihposawlr()
	{
		$data = array();
		if($this->session->userdata('id_user') =='2'){
			$q_pos = $this->db->query("SELECT * FROM t_logger INNER JOIN t_lokasi ON t_logger.lokasi_logger = t_lokasi.idlokasi where kategori_log='2' and user_id = 2 order by id_logger asc");
		}else{
			$q_pos = $this->db->query("SELECT * FROM t_logger INNER JOIN t_lokasi ON t_logger.lokasi_logger = t_lokasi.idlokasi where kategori_log='2' order by id_logger asc");
		}

		foreach ($q_pos->result() as $pos) {
			$data[] = array(
				'idLogger' => $pos->id_logger, 'namaPos' => $pos->nama_lokasi
			);
		}

		$data_pos = json_encode($data);
		return json_decode($data_pos);
	}

	function set_pos()
	{
		$idlog = $this->input->post('pilihpos');
		$querylogger = $this->db->query('select * from t_logger INNER JOIN t_lokasi ON t_logger.lokasi_logger=t_lokasi.idlokasi where id_logger="' . $idlog . '";');
		$log = $querylogger->row();
		$lokasilog = $log->nama_lokasi;
		$id_logger = $log->id_logger;
		$this->session->set_userdata('namalokasi', $lokasilog);
		$this->session->set_userdata('id_logger', $id_logger);

		$q_parameter = $this->db->query("SELECT * FROM parameter_sensor where logger_id='" . $idlog . "' order by id_param limit 1");
		if ($q_parameter->num_rows() > 0) {
			$parameter = $q_parameter->row();
			//data hasil seleksi dimasukkan ke dalam $session
			$session = array(
				'idlogger' => $parameter->logger_id,
				'idparameter' => $parameter->id_param,
				'nama_parameter' => $parameter->nama_parameter,
				'kolom' => $parameter->kolom_sensor,
				'satuan' => $parameter->satuan,
				'tipe_grafik' => $parameter->tipe_graf,
				'kolom_acuan' => $parameter->kolom_acuan
			);
			$this->session->set_userdata('id_param', $parameter->id_param);
			//data dari $session akhirnya dimasukkan ke dalam session
			$this->session->set_userdata($session);
		}

		redirect('awlr/analisa');
	}

	##### set Parameter #####
	public function pilihparameter($idlogger)
	{
		$data = array();
		$q_parameter = $this->db->query("SELECT * FROM parameter_sensor where logger_id='" . $idlogger . "'");
		foreach ($q_parameter->result() as $param) {
			$data[] = array(
				'idParameter' => $param->id_param, 'namaParameter' => $param->nama_parameter, 'fieldParameter' => $param->kolom_sensor
			);
		}

		$data_param = json_encode($data);
		return json_decode($data_param);
	}

	function set_parameter()
	{
		$q_parameter = $this->db->query("SELECT * FROM parameter_sensor where id_param='" . $this->input->post('mnsensor') . "'");
		$this->session->set_userdata('id_param', $this->input->post('mnsensor'));
		if ($q_parameter->num_rows() > 0) {
			$parameter = $q_parameter->row();
			//data hasil seleksi dimasukkan ke dalam $session
			$session = array(
				'idlogger' => $parameter->logger_id,
				'idparameter' => $parameter->id_param,
				'nama_parameter' => $parameter->nama_parameter,
				'kolom' => $parameter->kolom_sensor,
				'satuan' => $parameter->satuan,
				'tipe_grafik' => $parameter->tipe_graf,
				'kolom_acuan' => $parameter->kolom_acuan
			);
			//data dari $session akhirnya dimasukkan ke dalam session
			$this->session->set_userdata($session);
		}
		redirect('awlr/analisa');
	}


	function sesi_data()
	{
		if ($this->input->post('data') == 'hari') {
			$tgl = date('Y-m-d');
			$this->session->set_userdata('pada', $tgl);
		} elseif ($this->input->post('data') == 'bulan') {
			$tgl = date('Y-m');
			$this->session->set_userdata('bulan', $tgl);
			$this->session->set_userdata('pada', $tgl);
		} elseif ($this->input->post('data') == 'tahun') {
			$tgl = date('Y');
			$this->session->set_userdata('tahun', $tgl);
			$this->session->set_userdata('pada', $tgl);
		} elseif ($this->input->post('data') == 'range') {
			$dari = date('Y-m-d H:i', (mktime(date('H'), 0, 0, date('m'), date('d') - 1, date('Y'))));

			$sampai = date('Y-m-d H:i', (mktime(date('H'), 0, 0, date('m'), date('d'), date('Y'))));

			$this->session->set_userdata('dari', $dari);
			$this->session->set_userdata('sampai', $sampai);
		}
		$this->session->set_userdata('data', $this->input->post('data'));
		redirect('awlr/analisa');
	}

	function settgl()
	{
		$tgl = str_replace('/', '-', $this->input->post('tgl'));
		$this->session->set_userdata('tanggal', $tgl);
		$this->session->set_userdata('pada', $tgl);
		redirect('awlr/analisa');
	}

	function setbulan()
	{
		$tgl = str_replace('/', '-', $this->input->post('bulan'));
		$this->session->set_userdata('bulan', $tgl);
		$this->session->set_userdata('pada', $tgl);
		redirect('awlr/analisa');
	}

	function settahun()
	{
		$tgl = str_replace('/', '-', $this->input->post('tahun'));
		$this->session->set_userdata('tahun', $tgl);
		$this->session->set_userdata('pada', $tgl);
		redirect('awlr/analisa');
	}

	function setrange()
	{
		$this->session->set_userdata('dari', $this->input->post('dari'));
		$this->session->set_userdata('sampai', $this->input->post('sampai'));
		redirect('awlr/analisa');
	}


	function setrerata()
	{
		$this->session->set_userdata('mode', 'rerata');
		redirect('awlr/analisa2');
	}

	function setpermenit()
	{
		$this->session->set_userdata('mode', 'permenit');
		redirect('awlr/analisa2');
	}

	function analisa2()
	{

		if ($this->session->userdata('logged_in')) {
			$data = array();
			$data_tabel = array();
			$min = array();
			$max = array();
			$range = array();
			$tb_main = $this->db->where('id_logger',$this->session->userdata('idlogger'))->get('t_logger')->row();
			$foto = [];
			$get_foto = $this->db->where('id_logger',$this->session->userdata('idlogger'))->get('foto_pos')->result_array();
			if($get_foto){
				$foto = $get_foto;
			}
			$cek_rumus = $this->db->where('id_logger',$this->session->userdata('idlogger'))->get('rumus_debit');
			$tipe_graf = $this->session->userdata('tipe_grafik');
			####################################################################################### HARI ##################
			if ($this->session->userdata('data') == 'hari') {
				$sensor = $this->session->userdata('kolom');
				$kolom = $this->session->userdata('kolom');

				if($tipe_graf == 'column')
				{
					$nama_sensor = "Akumulasi_" . $this->session->userdata('nama_parameter');
					$select = 'sum(' . $kolom . ') as ' . $nama_sensor;

				}else
				{
					$nama_sensor = "Rerata_" . $this->session->userdata('nama_parameter');
					$select = 'avg(' . $kolom . ') as ' . $nama_sensor;
				}

				$satuan = $this->session->userdata('satuan');


				$query_data = $this->db->query("SELECT avg(sensor1 - sensor2) AS avg_diff, min(sensor1 - sensor2) AS min_diff, max(sensor1 - sensor2) AS max_diff,waktu, HOUR(waktu) as jam,DAY(waktu) as hari,MONTH(waktu) as bulan,YEAR(waktu) as tahun," . $select . ",min(" . $kolom . ") as min,max(" . $kolom . ") as max FROM " . $tb_main->tabel_main . " where code_logger='" . $this->session->userdata('idlogger') . "' and waktu >= '".$this->session->userdata('pada')." 00:00' and waktu <= '".$this->session->userdata('pada')." 23:59' group by HOUR(waktu),DAY(waktu),MONTH(waktu),YEAR(waktu) order by waktu asc;");

				foreach ($query_data->result() as $datalog) {
					$h = $datalog->$nama_sensor;
					$min_data = $datalog->min;
					$max_data = $datalog->max;

					if ($this->session->userdata('nama_parameter') == 'Debit' and $this->session->userdata('idlogger') == '10063') {
						if($h<0){
							$avg = number_format(0,2,'.','') ;
						}else{
							$h = $this->kalimeneng($datalog->$nama_sensor);
							$min_data = $this->kalimeneng($datalog->min);
							$max_data = $this->kalimeneng($datalog->max);
						}
					}elseif($this->session->userdata('nama_parameter') == 'Debit_Aliran_Sungai'){

						$avg_debit = $this->debit_interpolation(($datalog->avg_diff));
						$min_debit = $this->debit_interpolation(($datalog->min_diff));
						$max_debit = $this->debit_interpolation(($datalog->max_diff));
						$h = $datalog->avg_diff;
						$min_data = $min_debit;
						$max_data = $max_debit;
					}
					$data[] = "[ Date.UTC(" . $datalog->tahun . "," . $datalog->bulan . "-1," . $datalog->hari . "," . $datalog->jam . ")," . number_format($h, 3) . "]";
					$range[] = "[ Date.UTC(" . $datalog->tahun . "," . $datalog->bulan . "-1," . $datalog->hari . "," . $datalog->jam . ")," . $min_data . "," . $max_data . "]";
					$data_tabel[] = array(
						'waktu' => date('H',strtotime($datalog->waktu)) .':00:00',
						'dta' => number_format($h, 2),
						'min' => number_format($min_data, 2),
						'max' => number_format($max_data, 2)
					);
				}


				$dataAnalisa = array(
					'idLogger' => $this->session->userdata('idlogger'),
					'namaSensor' => $nama_sensor,
					'satuan' => $satuan,
					'tipe_grafik' => $this->session->userdata('tipe_grafik'),
					'data' => $data,
					'data_tabel' => $data_tabel,
					'nosensor' => $kolom,
					'range' => $range,
					'tooltip' => "Waktu %d-%m-%Y %H:%M"
				);
				$dataparam = json_encode($dataAnalisa);
				$data['data_sensor'] = json_decode($dataparam);
			}
			####################################################################################### BULAN ##################
			elseif ($this->session->userdata('data') == 'bulan') {
				$sensor = $this->session->userdata('kolom');
				$nama_sensor = "Rerata_" . $this->session->userdata('nama_parameter');

				$kolom = $this->session->userdata('kolom');


				if($tipe_graf == 'column')
				{
					$nama_sensor = "Akumulasi_" . $this->session->userdata('nama_parameter');
					$select = 'sum(' . $kolom . ') as ' . $nama_sensor;

				}else
				{
					$nama_sensor = "Rerata_" . $this->session->userdata('nama_parameter');
					$select = 'avg(' . $kolom . ') as ' . $nama_sensor;
				}

				$satuan = $this->session->userdata('satuan');
				$query_data = $this->db->query("SELECT  avg(sensor1 - sensor2) AS avg_diff, min(sensor1 - sensor2) AS min_diff, max(sensor1 - sensor2) AS max_diff,waktu, DATE(waktu) as tanggal, DAY(waktu) as hari,MONTH(waktu) as bulan,YEAR(waktu) as tahun," . $select . ",min(" . $kolom . ") as min,max(" . $kolom . ") as max FROM " . $tb_main->tabel_main . " where code_logger='" . $this->session->userdata('idlogger') . "' and waktu >= '".$this->session->userdata('pada')."-01 00:00' and waktu <= '".$this->session->userdata('pada')."-31 23:59' group by DAY(waktu),MONTH(waktu),YEAR(waktu)  order by waktu asc;");
				foreach ($query_data->result() as $datalog) {
					$h = $datalog->$nama_sensor;
					$min_data = $datalog->min;
					$max_data = $datalog->max;

					if ($this->session->userdata('nama_parameter') == 'Debit' and $this->session->userdata('idlogger') == '10063') {
						if($h<0){
							$avg = number_format(0,2,'.','') ;
						}else{
							$h = $this->kalimeneng($datalog->$nama_sensor);
							$min_data = $this->kalimeneng($datalog->min);
							$max_data = $this->kalimeneng($datalog->max);
						}
					}elseif($this->session->userdata('nama_parameter') == 'Debit_Aliran_Sungai'){

						$avg_debit = $this->debit_interpolation(abs($datalog->avg_diff));
						$min_debit = $this->debit_interpolation(abs($datalog->min_diff));
						$max_debit = $this->debit_interpolation(abs($datalog->max_diff));
						$h = $avg_debit;
						$min_data = $min_debit;
						$max_data = $max_debit;
					}
					$data[] = "[ Date.UTC(" . $datalog->tahun . "," . $datalog->bulan . "-1," . $datalog->hari . ")," . number_format($h, 3) . "]";
					$range[] = "[ Date.UTC(" . $datalog->tahun . "," . $datalog->bulan . "-1," . $datalog->hari . ")," . $min_data . "," . $max_data . "]";
					$data_tabel[] = array(
						'waktu' => date('Y-m-d',strtotime($datalog->waktu)) ,
						'dta' => number_format($h, 2),
						'min' => number_format($min_data, 2),
						'max' => number_format($max_data, 2)
					);
				}
				$dataAnalisa = array(
					'idLogger' => $this->session->userdata('idlogger'),
					'namaSensor' => $nama_sensor,
					'satuan' => $satuan,
					'tipe_grafik' => $this->session->userdata('tipe_grafik'),
					'data' => $data,
					'data_tabel' => $data_tabel,
					'nosensor' => $sensor,
					'range' => $range,
					'tooltip' => "Tanggal %d-%m-%Y"
				);
				$dataparam = json_encode($dataAnalisa);
				$data['data_sensor'] = json_decode($dataparam);
			}
			####################################################################################### TAHUN ##################
			elseif ($this->session->userdata('data') == 'tahun') {
				$sensor = $this->session->userdata('kolom');
				$nama_sensor = "Rerata_" . $this->session->userdata('nama_parameter');

				$kolom = $this->session->userdata('kolom');


				if($tipe_graf == 'column')
				{
					$nama_sensor = "Akumulasi_" . $this->session->userdata('nama_parameter');
					$select = 'sum(' . $kolom . ') as ' . $nama_sensor;

				}else
				{
					$nama_sensor = "Rerata_" . $this->session->userdata('nama_parameter');
					$select = 'avg(' . $kolom . ') as ' . $nama_sensor;
				}

				$satuan = $this->session->userdata('satuan');

				$query_data = $this->db->query("SELECT  avg(sensor1 - sensor2) AS avg_diff, min(sensor1 - sensor2) AS min_diff, max(sensor1 - sensor2) AS max_diff, DATE(waktu) as tanggal,MONTH(waktu) as bulan,YEAR(waktu) as tahun," . $select . ",min(" . $kolom . ") as min,max(" . $kolom . ") as max FROM " . $tb_main->tabel_main . " where code_logger='" . $this->session->userdata('idlogger') . "' and waktu >= '".$this->session->userdata('pada')."-01-01 00:00' and waktu <= '".$this->session->userdata('pada')."-12-31 23:59' group by MONTH(waktu),YEAR(waktu)  order by waktu asc;");
				$dbt = 0;
				foreach ($query_data->result() as $datalog) {

					$h = $datalog->$nama_sensor;
					$min_data = $datalog->min;
					$max_data = $datalog->max;

					if ($this->session->userdata('nama_parameter') == 'Debit' and $this->session->userdata('idlogger') == '10063') {
						if($h<0){
							$avg = number_format(0,2,'.','') ;
						}else{
							$h = $this->kalimeneng($datalog->$nama_sensor);
							$min_data = $this->kalimeneng($datalog->min);
							$max_data = $this->kalimeneng($datalog->max);
						}
					}elseif($this->session->userdata('nama_parameter') == 'Debit_Aliran_Sungai'){

						$avg_debit = $this->debit_interpolation(abs($datalog->avg_diff));
						$min_debit = $this->debit_interpolation(abs($datalog->min_diff));
						$max_debit = $this->debit_interpolation(abs($datalog->max_diff));
						$h = $avg_debit;
						$min_data = $min_debit;
						$max_data = $max_debit;
					}

					$data[] = "[ Date.UTC(" . $datalog->tahun . "," . $datalog->bulan . "-1)," . number_format($h, 3) . "]";
					$range[] = "[ Date.UTC(" . $datalog->tahun . "," . $datalog->bulan . "-1)," . $min_data . "," . $max_data . "]";
					$data_tabel[] = array(
						'waktu' => date('Y-m',strtotime($datalog->tanggal)) ,
						'dta' => number_format(number_format($h, 3), 2),
						'min' => number_format($min_data, 2),
						'max' => number_format($max_data, 2)
					);
				}


				$dataAnalisa = array(
					'idLogger' => $this->session->userdata('idlogger'),
					'namaSensor' => $nama_sensor,
					'satuan' => $satuan,
					'tipe_grafik' => $this->session->userdata('tipe_grafik'),
					'data' => $data,
					'data_tabel' => $data_tabel,
					'nosensor' => $sensor,
					'range' => $range,
					'tooltip' => "Tanggal %d-%m-%Y"
				);
				$dataparam = json_encode($dataAnalisa);
				$data['data_sensor'] = json_decode($dataparam);
			}
			####################################################################################### RANGE ##################
			elseif ($this->session->userdata('data') == 'range') {

				$sensor = $this->session->userdata('kolom');
				$nama_sensor = "Rerata_" . $this->session->userdata('nama_parameter');
				$kolom = $this->session->userdata('kolom');

				if($tipe_graf == 'column')
				{
					$nama_sensor = "Akumulasi_" . $this->session->userdata('nama_parameter');
					$select = 'sum(' . $kolom . ') as ' . $nama_sensor;

				}else
				{
					$nama_sensor = "Rerata_" . $this->session->userdata('nama_parameter');
					$select = 'avg(' . $kolom . ') as ' . $nama_sensor;
				}

				$satuan = $this->session->userdata('satuan');

				$query_data = $this->db->query("SELECT  avg(sensor1 - sensor2) AS avg_diff, min(sensor1 - sensor2) AS min_diff, max(sensor1 - sensor2) AS max_diff,waktu,DATE(waktu) as tanggal, HOUR(waktu) as jam,DAY(waktu) as hari,MONTH(waktu) as bulan,YEAR(waktu) as tahun," . $select . ",min(" . $kolom . ") as min,max(" . $kolom . ") as max FROM " . $tb_main->tabel_main . " where code_logger='" . $this->session->userdata('idlogger') . "' and waktu >='" . $this->session->userdata('dari') . "' and waktu <='" . $this->session->userdata('sampai') . "' group by HOUR(waktu),DAY(waktu),MONTH(waktu),YEAR(waktu) order by waktu asc;");

				foreach ($query_data->result() as $datalog) {

					$h = $datalog->$nama_sensor;
					$min_data = $datalog->min;
					$max_data = $datalog->max;

					if ($this->session->userdata('nama_parameter') == 'Debit' and $this->session->userdata('idlogger') == '10063') {
						if($h<0){
							$avg = number_format(0,2,'.','') ;
						}else{
							$h = $this->kalimeneng($datalog->$nama_sensor);
							$min_data = $this->kalimeneng($datalog->min);
							$max_data = $this->kalimeneng($datalog->max);
						}
					} elseif($this->session->userdata('nama_parameter') == 'Debit_Aliran_Sungai'){

						$avg_debit = $this->debit_interpolation(abs($datalog->avg_diff));
						$min_debit = $this->debit_interpolation(abs($datalog->min_diff));
						$max_debit = $this->debit_interpolation(abs($datalog->max_diff));
						$h = $avg_debit;
						$min_data = $min_debit;
						$max_data = $max_debit;
					}

					$data[]= "[ Date.UTC(".$datalog->tahun.",".$datalog->bulan."-1,".$datalog->hari.",".$datalog->jam."),".number_format($h,3) ."]";
					$range[] ="[ Date.UTC(".$datalog->tahun.",".$datalog->bulan."-1,".$datalog->hari.",".$datalog->jam."),". $min_data.",". $max_data ."]";
					$data_tabel[] = array(
						'waktu' => date('Y-m-d H',strtotime($datalog->waktu)) .':00:00' ,
						'dta' => number_format($h, 3),
						'min' => number_format($min_data, 2),
						'max' => number_format($max_data, 2)
					);
				}

				$dataAnalisa = array(
					'idLogger' => $this->session->userdata('idlogger'),
					'namaSensor' => $nama_sensor,
					'satuan' => $satuan,
					'tipe_grafik' => $this->session->userdata('tipe_grafik'),
					'data' => $data,
					'data_tabel' => $data_tabel,
					'nosensor' => $sensor,
					'range' => $range,
					'tooltip' => "Waktu %d-%m-%Y %H:%M",
					'tooltipper' => "Waktu %d-%m-%Y %H:%M"
				);
				$dataparam = json_encode($dataAnalisa);
				$data['data_sensor'] = json_decode($dataparam);
			}
			$data['data_op'] = $this->db->where('id_logger',$this->session->userdata('idlogger'))->get('t_riwayat')->result_array();
			$data['foto_pos'] = $foto;
			$data['pilih_pos'] = $this->pilihposawlr();
			$data['pilih_parameter'] = $this->pilihparameter($this->session->userdata('idlogger'));
			$data['konten'] = 'konten/back/awlr/analisa_awlr2';
			$this->load->view('template_admin/site', $data);
		} else {
			redirect('login');
		}
	}


	public function analisa() {
		
		$idLogger      = $this->session->userdata('idlogger');
		$tipeGrafik    = $this->session->userdata('tipe_grafik');
		$modeData      = $this->session->userdata('data');
		$kolom         = $this->session->userdata('kolom');
		$namaParameter = $this->session->userdata('nama_parameter');
		$satuan        = $this->session->userdata('satuan');
		$pada          = $this->session->userdata('pada');
		$dari          = $this->session->userdata('dari');
		$sampai        = $this->session->userdata('sampai');

		$tb_main = $this->db->where('id_logger', $idLogger)->get('t_logger')->row();
		if (!$tb_main) show_error('Logger tidak ditemukan');

		$foto_pos   = $this->db->where('id_logger', $idLogger)->get('foto_pos')->result_array();
		$riwayat_op = $this->db->where('id_logger', $idLogger)->get('t_riwayat')->result_array();

		$data = [];
		$data_tabel = [];
		$range = [];

		if ($tipeGrafik === 'column') {
			$nama_sensor = 'Akumulasi_' . $namaParameter;
			$selectAgg = "SUM($kolom) AS $nama_sensor";
		} else {
			$nama_sensor = 'Rerata_' . $namaParameter;
			$selectAgg = "AVG($kolom) AS $nama_sensor";
		}

		if ($modeData === 'hari') {
			$start = $pada . ' 00:00:00';
			$end   = $pada . ' 23:59:59';
			$select = "AVG(sensor1 - sensor2) AS avg_diff, MIN(sensor1 - sensor2) AS min_diff, MAX(sensor1 - sensor2) AS max_diff, waktu, HOUR(waktu) AS jam, DAY(waktu) AS hari, MONTH(waktu) AS bulan, YEAR(waktu) AS tahun, $selectAgg, MIN($kolom) AS min, MAX($kolom) AS max";
			$group  = "YEAR(waktu), MONTH(waktu), DAY(waktu), HOUR(waktu)";
			$order  = "tahun ASC, bulan ASC, hari ASC, jam ASC";
			$sql = "SELECT $select FROM {$tb_main->tabel_main} WHERE code_logger=? AND waktu BETWEEN ? AND ? GROUP BY $group ORDER BY $order";
			$q = $this->db->query($sql, [$idLogger, $start, $end]);
			foreach ($q->result() as $r) {
				$h = $r->$nama_sensor;
				$min_data = $r->min;
				$max_data = $r->max;
				if ($namaParameter === 'Debit' && $idLogger === '10063') {
					if ($h < 0) $h = 0;
					else {
						$h = $this->kalimeneng($r->$nama_sensor);
						$min_data = $this->kalimeneng($r->min);
						$max_data = $this->kalimeneng($r->max);
					}
				} elseif ($namaParameter === 'Debit_Aliran_Sungai') {
					$avg_debit = $this->debit_interpolation(abs($r->avg_diff));
					$min_debit = $this->debit_interpolation(abs($r->min_diff));
					$max_debit = $this->debit_interpolation(abs($r->max_diff));
					$h = $avg_debit;
					$min_data = $min_debit;
					$max_data = $max_debit;
				}
				$data[] = "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari,$r->jam)," . number_format($h, 3, '.', '') . "]";
				$range[] = "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari,$r->jam)," . $min_data . "," . $max_data . "]";
				$data_tabel[] = [
					'waktu' => date('H', strtotime($r->waktu)) . ':00:00',
					'dta'   => number_format($h, 2, '.', ''),
					'min'   => number_format($min_data, 2, '.', ''),
					'max'   => number_format($max_data, 2, '.', '')
				];
			}
			$tooltip = "Waktu %d-%m-%Y %H:%M";
		} elseif ($modeData === 'bulan') {
			$start = $pada . '-01 00:00:00';
			$end   = date('Y-m-t 23:59:59', strtotime($pada . '-01'));
			$select = "AVG(sensor1 - sensor2) AS avg_diff, MIN(sensor1 - sensor2) AS min_diff, MAX(sensor1 - sensor2) AS max_diff, waktu, DATE(waktu) AS tanggal, DAY(waktu) AS hari, MONTH(waktu) AS bulan, YEAR(waktu) AS tahun, $selectAgg, MIN($kolom) AS min, MAX($kolom) AS max";
			$group  = "YEAR(waktu), MONTH(waktu), DAY(waktu)";
			$order  = "tahun ASC, bulan ASC, hari ASC";
			$sql = "SELECT $select FROM {$tb_main->tabel_main} WHERE code_logger=? AND waktu BETWEEN ? AND ? GROUP BY $group ORDER BY $order";
			$q = $this->db->query($sql, [$idLogger, $start, $end]);
			foreach ($q->result() as $r) {
				$h = $r->$nama_sensor;
				$min_data = $r->min;
				$max_data = $r->max;
				if ($namaParameter === 'Debit' && $idLogger === '10063') {
					if ($h < 0) $h = 0;
					else {
						$h = $this->kalimeneng($r->$nama_sensor);
						$min_data = $this->kalimeneng($r->min);
						$max_data = $this->kalimeneng($r->max);
					}
				} elseif ($namaParameter === 'Debit_Aliran_Sungai') {
					$avg_debit = $this->debit_interpolation(abs($r->avg_diff));
					$min_debit = $this->debit_interpolation(abs($r->min_diff));
					$max_debit = $this->debit_interpolation(abs($r->max_diff));
					$h = $avg_debit;
					$min_data = $min_debit;
					$max_data = $max_debit;
				}
				$data[] = "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari)," . number_format($h, 3, '.', '') . "]";
				$range[] = "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari)," . $min_data . "," . $max_data . "]";
				$data_tabel[] = [
					'waktu' => date('Y-m-d', strtotime($r->waktu)),
					'dta'   => number_format($h, 2, '.', ''),
					'min'   => number_format($min_data, 2, '.', ''),
					'max'   => number_format($max_data, 2, '.', '')
				];
			}
			$tooltip = "Tanggal %d-%m-%Y";
		} elseif ($modeData === 'tahun') {
			$start = $pada . '-01-01 00:00:00';
			$end   = $pada . '-12-31 23:59:59';
			$select = "AVG(sensor1 - sensor2) AS avg_diff, MIN(sensor1 - sensor2) AS min_diff, MAX(sensor1 - sensor2) AS max_diff, DATE(waktu) AS tanggal, MONTH(waktu) AS bulan, YEAR(waktu) AS tahun, $selectAgg, MIN($kolom) AS min, MAX($kolom) AS max";
			$group  = "YEAR(waktu), MONTH(waktu)";
			$order  = "tahun ASC, bulan ASC";
			$sql = "SELECT $select FROM {$tb_main->tabel_main} WHERE code_logger=? AND waktu BETWEEN ? AND ? GROUP BY $group ORDER BY $order";
			$q = $this->db->query($sql, [$idLogger, $start, $end]);
			foreach ($q->result() as $r) {
				$h = $r->$nama_sensor;
				$min_data = $r->min;
				$max_data = $r->max;
				if ($namaParameter === 'Debit' && $idLogger === '10063') {
					if ($h < 0) $h = 0;
					else {
						$h = $this->kalimeneng($r->$nama_sensor);
						$min_data = $this->kalimeneng($r->min);
						$max_data = $this->kalimeneng($r->max);
					}
				} elseif ($namaParameter === 'Debit_Aliran_Sungai') {
					$avg_debit = $this->debit_interpolation(abs($r->avg_diff));
					$min_debit = $this->debit_interpolation(abs($r->min_diff));
					$max_debit = $this->debit_interpolation(abs($r->max_diff));
					$h = $avg_debit;
					$min_data = $min_debit;
					$max_data = $max_debit;
				}
				$data[] = "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ")," . number_format($h, 3, '.', '') . "]";
				$range[] = "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ")," . $min_data . "," . $max_data . "]";
				$data_tabel[] = [
					'waktu' => date('Y-m', strtotime($r->tanggal)),
					'dta'   => number_format($h, 2, '.', ''),
					'min'   => number_format($min_data, 2, '.', ''),
					'max'   => number_format($max_data, 2, '.', '')
				];
			}
			$tooltip = "Tanggal %d-%m-%Y";
		} else {
			$start = $dari;
			$end   = $sampai;
			$select = "AVG(sensor1 - sensor2) AS avg_diff, MIN(sensor1 - sensor2) AS min_diff, MAX(sensor1 - sensor2) AS max_diff, waktu, DATE(waktu) AS tanggal, HOUR(waktu) AS jam, DAY(waktu) AS hari, MONTH(waktu) AS bulan, YEAR(waktu) AS tahun, $selectAgg, MIN($kolom) AS min, MAX($kolom) AS max";
			$group  = "YEAR(waktu), MONTH(waktu), DAY(waktu), HOUR(waktu)";
			$order  = "tahun ASC, bulan ASC, hari ASC, jam ASC";
			$sql = "SELECT $select FROM {$tb_main->tabel_main} WHERE code_logger=? AND waktu BETWEEN ? AND ? GROUP BY $group ORDER BY $order";
			$q = $this->db->query($sql, [$idLogger, $start, $end]);
			foreach ($q->result() as $r) {
				$h = $r->$nama_sensor;
				$min_data = $r->min;
				$max_data = $r->max;
				if ($namaParameter === 'Debit' && $idLogger === '10063') {
					if ($h < 0) $h = 0;
					else {
						$h = $this->kalimeneng($r->$nama_sensor);
						$min_data = $this->kalimeneng($r->min);
						$max_data = $this->kalimeneng($r->max);
					}
				} elseif ($namaParameter === 'Debit_Aliran_Sungai') {
					$avg_debit = $this->debit_interpolation(abs($r->avg_diff));
					$min_debit = $this->debit_interpolation(abs($r->min_diff));
					$max_debit = $this->debit_interpolation(abs($r->max_diff));
					$h = $avg_debit;
					$min_data = $min_debit;
					$max_data = $max_debit;
				}
				$data[] = "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari,$r->jam)," . number_format($h, 3, '.', '') . "]";
				$range[] = "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari,$r->jam)," . $min_data . "," . $max_data . "]";
				$data_tabel[] = [
					'waktu' => date('Y-m-d H', strtotime($r->waktu)) . ':00:00',
					'dta'   => number_format($h, 3, '.', ''),
					'min'   => number_format($min_data, 2, '.', ''),
					'max'   => number_format($max_data, 2, '.', '')
				];
			}
			$tooltip = "Waktu %d-%m-%Y %H:%M";
		}

		$dataAnalisa = [
			'idLogger'     => $idLogger,
			'namaSensor'   => $nama_sensor,
			'satuan'       => $satuan,
			'tipe_grafik'  => $tipeGrafik,
			'data'         => $data,
			'data_tabel'   => $data_tabel,
			'nosensor'     => $kolom,
			'range'        => $range,
			'tooltip'      => $tooltip,
			'tooltipper'   => $tooltip
		];

		$payload = [];
		$payload['data_sensor']     = json_decode(json_encode($dataAnalisa));
		$payload['data_op']         = $riwayat_op;
		$payload['foto_pos']        = $foto_pos ?: [];
		$payload['pilih_pos']       = $this->pilihposawlr();
		$payload['pilih_parameter'] = $this->pilihparameter($idLogger);
		$payload['konten']          = 'konten/back/awlr/analisa_awlr2';

		$this->load->view('template_admin/site', $payload);
	} 
	
	function livedata()
	{
		if ($this->session->userdata('logged_in')) {
			$data['konten'] = 'konten/back/awlr/analisa_liveawlr';
			$this->load->view('template_admin/site', $data);
		} else {
			redirect('login');
		}
	}
}
