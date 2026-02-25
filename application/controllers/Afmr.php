<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Afmr extends CI_Controller
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
		$this->session->set_userdata('controller', 'afmr');
		redirect('afmr/analisa');
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
		$this->session->set_userdata('controller', 'afmr');
		redirect('afmr/analisa');
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
		redirect('afmr/analisa');
	}

	### Set Pos #####
	public function pilihposawlr()
	{
		$data = array();
		if($this->session->userdata('id_user') =='2'){
			$q_pos = $this->db->query("SELECT * FROM t_logger INNER JOIN t_lokasi ON t_logger.lokasi_logger = t_lokasi.idlokasi where kategori_log='8' and user_id = 2 order by id_logger asc");
		}else{
			$q_pos = $this->db->query("SELECT * FROM t_logger INNER JOIN t_lokasi ON t_logger.lokasi_logger = t_lokasi.idlokasi where kategori_log='8' order by id_logger asc");
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

		redirect('afmr/analisa');
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
		redirect('afmr/analisa');
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
		redirect('afmr/analisa');
	}

	function settgl()
	{
		$tgl = str_replace('/', '-', $this->input->post('tgl'));
		$this->session->set_userdata('tanggal', $tgl);
		$this->session->set_userdata('pada', $tgl);
		redirect('afmr/analisa');
	}

	function setbulan()
	{
		$tgl = str_replace('/', '-', $this->input->post('bulan'));
		$this->session->set_userdata('bulan', $tgl);
		$this->session->set_userdata('pada', $tgl);
		redirect('afmr/analisa');
	}

	function settahun()
	{
		$tgl = str_replace('/', '-', $this->input->post('tahun'));
		$this->session->set_userdata('tahun', $tgl);
		$this->session->set_userdata('pada', $tgl);
		redirect('afmr/analisa');
	}

	function setrange()
	{
		$this->session->set_userdata('dari', $this->input->post('dari'));
		$this->session->set_userdata('sampai', $this->input->post('sampai'));
		redirect('afmr/analisa');
	}


	function setrerata()
	{
		$this->session->set_userdata('mode', 'rerata');
		redirect('afmr/analisa2');
	}

	function setpermenit()
	{
		$this->session->set_userdata('mode', 'permenit');
		redirect('afmr/analisa2');
	}


	
	
	function linear_interpolation($x) {
		$data = [
			[10, 0.251],
			[20, 1.005],
			[30, 2.262],
			[40, 4.021],
			[50, 6.283],
			[60, 9.048],
			[70, 12.315],
			[80, 15.834],
			[90, 19.392],
			[100, 22.988],
			[110, 26.623],
			[120, 30.296],
			[130, 34.008],
			[140, 37.758],
			[150, 41.546],
			[160, 45.373],
			[170, 49.238],
			[180, 53.142],
			[190, 57.084],
			[200, 61.065],
			[210, 65.084],
			[220, 69.142],
			[230, 73.238],
			[240, 77.373],
			[250, 81.546],
			[260, 85.757],
			[270, 90.007],
			[280, 94.295],
			[290, 98.622],
			[300, 102.987],
			[310, 107.391],
			[320, 111.833],
			[330, 116.314],
			[340, 120.833],
			[350, 125.397],
			[360, 130.024],
			[370, 134.725],
			[380, 139.501],
			[390, 144.789],
			[400, 150.182],
			[410, 155.626],
			[420, 161.121],
			[430, 166.667],
			[440, 172.264],
			[450, 177.912],
			[460, 183.611],
			[470, 189.36],
			[480, 195.16],
			[490, 201.011],
			[500, 206.913],
			[510, 212.866],
			[520, 218.87],
			[530, 224.925],
			[540, 231.031],
			[550, 237.188],
			[560, 243.396],
			[570, 249.655],
			[580, 256.03],
			[590, 262.738],
			[600, 269.795],
			[610, 277.202],
			[620, 284.958],
			[630, 293.048],
			[640, 301.23],
			[650, 309.422],
			[660, 317.624],
			[670, 325.836],
			[680, 334.058],
			[690, 343.05],
			[700, 352.048],
			[710, 361.046],
			[720, 370.044],
			[730, 379.042],
			[740, 388.04],
			[750, 397.038],
			[760, 406.036],
			[770, 415.034],
			[780, 424.032],
			[790, 433.03],
			[800, 442.028],
			[810, 451.026],
			[820, 460.024],
			[830, 469.022],
			[837, 475.321],
		];
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
	

	function analisa()
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
			######################### HARI ##################
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


				$query_data = $this->db->query("SELECT avg(sensor2) as tma,max(sensor2) as tma_max,min(sensor2) as tma_min, waktu, HOUR(waktu) as jam,DAY(waktu) as hari,MONTH(waktu) as bulan,YEAR(waktu) as tahun," . $select . ",min(" . $kolom . ") as min,max(" . $kolom . ") as max FROM " . $tb_main->tabel_main . " where code_logger='" . $this->session->userdata('idlogger') . "' and waktu >= '".$this->session->userdata('pada')." 00:00' and waktu <= '".$this->session->userdata('pada')." 23:59' group by HOUR(waktu),DAY(waktu),MONTH(waktu),YEAR(waktu) order by waktu asc;");

				foreach ($query_data->result() as $datalog) {
					$h = $datalog->$nama_sensor;
					$min_data = $datalog->min;
					$max_data = $datalog->max;
					
					if ($this->session->userdata('nama_parameter') == 'Debit') {
						if($h<0){
							$avg = number_format(0,2,'.','') ;
							$min_data =	number_format(0,2,'.','') ;
							$max_data = number_format(0,2,'.','') ;
						}else{
							$tma = $datalog->tma * 100;
							$h =  $this->linear_interpolation($tma) *$h;
							$tma_min = $datalog->tma_min * 100;
							$min_data =	$this->linear_interpolation($tma_min) *$datalog->$nama_sensor;
							$tma_max =  $datalog->tma_max * 100;
							$max_data = $this->linear_interpolation($tma_max) * $datalog->$nama_sensor;
						}
					} if ($this->session->userdata('nama_parameter') == 'Luas_Penampang_Basah') {
						if($h<0){
							$avg = number_format(0,2,'.','') ;
						}else{
							$tma = $datalog->tma * 100;
							$h =  $this->linear_interpolation($tma) ;
							$tma_min = $datalog->tma_min * 100;
							$min_data =	$this->linear_interpolation($tma_min);
							$tma_max =  $datalog->tma_max * 100;
							$max_data = $this->linear_interpolation($tma_max) ;
						}
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
				$query_data = $this->db->query("SELECT avg(sensor2) as tma,max(sensor2) as tma_max,min(sensor2) as tma_min,waktu, DATE(waktu) as tanggal, DAY(waktu) as hari,MONTH(waktu) as bulan,YEAR(waktu) as tahun," . $select . ",min(" . $kolom . ") as min,max(" . $kolom . ") as max FROM " . $tb_main->tabel_main . " where code_logger='" . $this->session->userdata('idlogger') . "' and waktu >= '".$this->session->userdata('pada')."-01 00:00' and waktu <= '".$this->session->userdata('pada')."-31 23:59' group by DAY(waktu),MONTH(waktu),YEAR(waktu)  order by waktu asc;");
				foreach ($query_data->result() as $datalog) {
					$h = $datalog->$nama_sensor;
					$min_data = $datalog->min;
					$max_data = $datalog->max;

					if ($this->session->userdata('nama_parameter') == 'Debit') {
						if($h<0){
							$avg = number_format(0,2,'.','') ;
						}else{
							$tma = $datalog->tma * 100;
							$h =  $this->linear_interpolation($tma) *$h;
							$tma_min = $datalog->tma_min * 100;
							$min_data =	$this->linear_interpolation($tma_min) *$datalog->$nama_sensor;
							$tma_max =  $datalog->tma_max * 100;
							$max_data = $this->linear_interpolation($tma_max) * $datalog->$nama_sensor;
						}
					} if ($this->session->userdata('nama_parameter') == 'Luas_Penampang_Basah') {
						if($datalog->tma < 0){
							$avg = number_format(0,2,'.','') ;
							$h = 0 ;
							$min_data =0 ;
							$max_data =0 ;
						}else{
							$tma = $datalog->tma * 100;
							$h =  $this->linear_interpolation($tma) ;
							$tma_min = $datalog->tma_min * 100;
							$min_data =	$this->linear_interpolation($tma_min);
							$tma_max =  $datalog->tma_max * 100;
							$max_data = $this->linear_interpolation($tma_max) ;
						}
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

				$query_data = $this->db->query("SELECT avg(sensor2) as tma,max(sensor2) as tma_max,min(sensor2) as tma_min,DATE(waktu) as tanggal,MONTH(waktu) as bulan,YEAR(waktu) as tahun," . $select . ",min(" . $kolom . ") as min,max(" . $kolom . ") as max FROM " . $tb_main->tabel_main . " where code_logger='" . $this->session->userdata('idlogger') . "' and waktu >= '".$this->session->userdata('pada')."-01-01 00:00' and waktu <= '".$this->session->userdata('pada')."-12-31 23:59' group by MONTH(waktu),YEAR(waktu)  order by waktu asc;");
				$dbt = 0;
				foreach ($query_data->result() as $datalog) {
					
					$h = $datalog->$nama_sensor;
					$min_data = $datalog->min;
					$max_data = $datalog->max;

					if ($this->session->userdata('nama_parameter') == 'Debit') {
						if($h<0){
							$avg = number_format(0,2,'.','') ;
						}else{
							$tma = $datalog->tma * 100;
							$h =  $this->linear_interpolation($tma) *$h;
							$tma_min = $datalog->tma_min * 100;
							$min_data =	$this->linear_interpolation($tma_min) *$datalog->$nama_sensor;
							$tma_max =  $datalog->tma_max * 100;
							$max_data = $this->linear_interpolation($tma_max) * $datalog->$nama_sensor;
						}
					} if ($this->session->userdata('nama_parameter') == 'Luas_Penampang_Basah') {
						if($h<0){
							$avg = number_format(0,2,'.','') ;
						}else{
							$tma = $datalog->tma * 100;
							$h =  $this->linear_interpolation($tma) ;
							$tma_min = $datalog->tma_min * 100;
							$min_data =	$this->linear_interpolation($tma_min);
							$tma_max =  $datalog->tma_max * 100;
							$max_data = $this->linear_interpolation($tma_max) ;
						}
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

				$query_data = $this->db->query("SELECT avg(sensor2) as tma,max(sensor2) as tma_max,min(sensor2) as tma_min,waktu,DATE(waktu) as tanggal, HOUR(waktu) as jam,DAY(waktu) as hari,MONTH(waktu) as bulan,YEAR(waktu) as tahun," . $select . ",min(" . $kolom . ") as min,max(" . $kolom . ") as max FROM " . $tb_main->tabel_main . " where code_logger='" . $this->session->userdata('idlogger') . "' and waktu >='" . $this->session->userdata('dari') . "' and waktu <='" . $this->session->userdata('sampai') . "' group by HOUR(waktu),DAY(waktu),MONTH(waktu),YEAR(waktu) order by waktu asc;");

				foreach ($query_data->result() as $datalog) {
					
					$h = $datalog->$nama_sensor;
					$min_data = $datalog->min;
					$max_data = $datalog->max;

					if ($this->session->userdata('nama_parameter') == 'Debit') {
						if($h<0){
							$avg = number_format(0,2,'.','') ;
						}else{
							$tma = $datalog->tma * 100;
							$h =  $this->linear_interpolation($tma) *$h;
							$tma_min = $datalog->tma_min * 100;
							$min_data =	$this->linear_interpolation($tma_min) *$datalog->$nama_sensor;
							$tma_max =  $datalog->tma_max * 100;
							$max_data = $this->linear_interpolation($tma_max) * $datalog->$nama_sensor;
						}
					} if ($this->session->userdata('nama_parameter') == 'Luas_Penampang_Basah') {
						if($h<0){
							$avg = number_format(0,2,'.','') ;
						}else{
							$tma = $datalog->tma * 100;
							$h =  $this->linear_interpolation($tma) ;
							$tma_min = $datalog->tma_min * 100;
							$min_data =	$this->linear_interpolation($tma_min);
							$tma_max =  $datalog->tma_max * 100;
							$max_data = $this->linear_interpolation($tma_max) ;
						}
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
			$data['konten'] = 'konten/back/afmr/analisa_afmr';
			$this->load->view('template_admin/site', $data);
		} else {
			redirect('login');
		}
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
