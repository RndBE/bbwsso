<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Datapos extends CI_Controller {
	function __construct() {
		parent::__construct();

		//	$this->load->model('m_ketinggian');
	}
	
	public function set_lokasi()
	{
		$data = explode(',', $this->input->post('id_logger'));
		$this->session->set_userdata('data_idlogger',$data[0]);
		$this->session->set_userdata('data_tabel',$data[1]);
		redirect('datapos');
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

		usort($data, function ($a, $b) {
			return $a[0] - $b[0];
		});
		
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
			return null;
		}

		if ($x == $x1) {
			return $y1;
		}
		$y = $y1 + (($x - $x1) / ($x2 - $x1)) * ($y2 - $y1);

		return $y;
	}
	
	function debit_interpolation($x) {
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
			return null;
		}
		if ($x == $x1) {
			return $y1;
		}
		$y = $y1 + (($x - $x1) / ($x2 - $x1)) * ($y2 - $y1);

		return $y;
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

	public function set_range()
	{
		$tgl_awal=$this->input->post('dari');
		$tgl_akhir=$this->input->post('sampai');
		$this->session->set_userdata('data_tglawal',$tgl_awal);
		$this->session->set_userdata('data_tglakhir',$tgl_akhir);

		redirect('datapos');
	}


	public function data2()
	{
		$idlogger=$this->session->userdata('data_idlogger');
		$tgl_awal=$this->session->userdata('data_tglawal');
		$tgl_akhir=$this->session->userdata('data_tglakhir');

		$data['pilih_pos'] = $this->db->from('t_logger')->join('t_lokasi','t_lokasi.idlokasi=t_logger.lokasi_logger')->join('kategori_logger','kategori_logger.id_katlogger=t_logger.kategori_log')->order_by('t_logger.kategori_log','asc')->get()->result_array();

		$select = "";
		$nama_pos = '';
		if(isset($idlogger) && isset($tgl_awal) && isset($tgl_akhir)){
			$nama_pos = $this->db->join('t_lokasi','t_lokasi.idlokasi = t_logger.lokasi_logger')->where('t_logger.id_logger',$idlogger)->get('t_logger')->row()->nama_lokasi;
			//	$querykategori = $this->db->query("SELECT * FROM t_logger INNER JOIN kategori_logger ON t_logger.katlog_id = kategori_logger.id_katlogger where t_logger.code_logger = '".$idlogger."'");
			//	$tabel = $querykategori->row()->tabel;
			$tabel = $this->db->where('id_logger',$idlogger)->get('t_logger')->row()->tabel_main;

			$query_parameter=$this->db->query("SELECT * FROM t_logger INNER JOIN parameter_sensor ON t_logger.id_logger = parameter_sensor.logger_id where parameter_sensor.logger_id = '".$idlogger."'  order by cast(SUBSTRING(kolom_sensor,7) as unsigned)");
			foreach($query_parameter->result() as $parameter)
			{
				if($parameter->kolom_sensor == "sensor8" || $parameter->kolom_sensor == "sensor9" ){
					$select .= "sum(".$parameter->kolom_sensor.") as ".$parameter->kolom_sensor.",";
				}else
				{
					$select .= "avg(".$parameter->kolom_sensor.") as ".$parameter->kolom_sensor.",";
				}

			}
			$select_fix = substr($select, 0, -1);
			$query_data = $this->db->query('select waktu,'.$select_fix.' from '.$tabel.' use index(waktu) where code_logger = "' . $idlogger . '" and waktu >= "' . $tgl_awal . '" and waktu <= "' . $tgl_akhir . '" group by HOUR(waktu),DAY(waktu),MONTH(waktu),YEAR(waktu) order by waktu asc ');
			if(!$query_data->result()){
				$query_data ="kosong";	
			}
		}else
		{
			$query_parameter="kosong";
			$query_data ="kosong";
		}
		$data['parameter']=$query_parameter;
		$data['datapos'] = $query_data;
		$data['nama_lokasi'] = $nama_pos;
		$data['konten']='konten/back/v_datapos';
		$this->load->view('template_admin/site',$data);
	}

	public function ubah_session(){
		$sesi = $this->input->get('sesi');
		$this->session->set_userdata('sesi_data',$sesi);
		redirect('datapos');
	}

	public function index2()
	{
		$idlogger=$this->session->userdata('data_idlogger');
		$tgl_awal=$this->session->userdata('data_tglawal');
		$tgl_akhir=$this->session->userdata('data_tglakhir');

		$data['pilih_pos'] = $this->db->from('t_logger')->join('t_lokasi','t_lokasi.idlokasi=t_logger.lokasi_logger')->join('kategori_logger','kategori_logger.id_katlogger=t_logger.kategori_log')->order_by('t_logger.kategori_log','asc')->get()->result_array();
		$sesi = $this->session->userdata('sesi_data');
		if(!$sesi){
			$this->session->set_userdata('sesi_data','hari');
		}
		$select = "";
		$nama_pos = '';
		if(isset($idlogger) && isset($tgl_awal) && isset($tgl_akhir)){
			$nama_pos = $this->db->join('t_lokasi','t_lokasi.idlokasi = t_logger.lokasi_logger')->where('t_logger.id_logger',$idlogger)->get('t_logger')->row()->nama_lokasi;
			$tabel = $this->db->where('id_logger',$idlogger)->get('t_logger')->row()->tabel_main;

			$query_parameter=$this->db->query("SELECT * FROM t_logger INNER JOIN parameter_sensor ON t_logger.id_logger = parameter_sensor.logger_id where parameter_sensor.logger_id = '".$idlogger."'  order by cast(SUBSTRING(kolom_sensor,7) as unsigned)");
			foreach($query_parameter->result() as $parameter)
			{
				if($parameter->satuan == "mm"){
					$select .= "sum(".$parameter->kolom_sensor.") as ".$parameter->nama_parameter.",";
				}else
				{
					$select .= "avg(".$parameter->kolom_sensor.") as ".$parameter->nama_parameter.",";
				}

			}
			$select_fix = substr($select, 0, -1);
			if($sesi == 'hari'){
				$query_data = $this->db->query('select waktu,'.$select_fix.' from '.$tabel.' use index(waktu) where code_logger = "' . $idlogger . '" and waktu >= "' . $tgl_awal . '" and waktu <= "' . $tgl_akhir . '" group by HOUR(waktu),DAY(waktu),MONTH(waktu),YEAR(waktu) order by waktu asc ');
			}else if($sesi == 'bulan'){
				$query_data = $this->db->query('select waktu,'.$select_fix.' from '.$tabel.' use index(waktu) where code_logger = "' . $idlogger . '" and waktu >= "' . $tgl_awal . '" and waktu <= "' . $tgl_akhir . '" group by DAY(waktu),MONTH(waktu),YEAR(waktu) order by waktu asc ');
			}else{
				$query_data = $this->db->query('select waktu,'.$select_fix.' from '.$tabel.' use index(waktu) where code_logger = "' . $idlogger . '" and waktu >= "' . $tgl_awal . '" and waktu <= "' . $tgl_akhir . '" group by MONTH(waktu),YEAR(waktu) order by waktu asc ');
			}

			if(!$query_data->result()){
				$query_data ="kosong";	
			}
		}else
		{
			$query_parameter="kosong";
			$query_data ="kosong";
		}
		$data['parameter']=$query_parameter;
		$data['datapos'] = $query_data;
		$data['nama_lokasi'] = $nama_pos;
		$data['konten']='konten/back/v_datapos2';
		$this->load->view('template_admin/site',$data);
	}
	
	public function index()
	{
		$idlogger=$this->session->userdata('data_idlogger');
		$tgl_awal=$this->session->userdata('data_tglawal');
		$tgl_akhir=$this->session->userdata('data_tglakhir');

		$data['pilih_pos'] = $this->db->from('t_logger')->join('t_lokasi','t_lokasi.idlokasi=t_logger.lokasi_logger')->join('kategori_logger','kategori_logger.id_katlogger=t_logger.kategori_log')->order_by('t_logger.kategori_log','asc')->get()->result_array();
		$sesi = $this->session->userdata('sesi_data');
		if(!$sesi){
			$this->session->set_userdata('sesi_data','hari');
		}
		$select = "";
		$nama_pos = '';
		if(isset($idlogger) && isset($tgl_awal) && isset($tgl_akhir)){
			$nama_pos = $this->db->join('t_lokasi','t_lokasi.idlokasi = t_logger.lokasi_logger')->where('t_logger.id_logger',$idlogger)->get('t_logger')->row()->nama_lokasi;
			$tabel = $this->db->where('id_logger',$idlogger)->get('t_logger')->row()->tabel_main;

			$query_parameter=$this->db->query("SELECT * FROM t_logger INNER JOIN parameter_sensor ON t_logger.id_logger = parameter_sensor.logger_id where parameter_sensor.logger_id = '".$idlogger."' order by cast(SUBSTRING(kolom_sensor,7) as unsigned)");
			foreach($query_parameter->result() as $parameter)
			{
				if($parameter->satuan == "mm"){
					$select .= "sum(".$parameter->kolom_sensor.") as ".$parameter->nama_parameter.",";
				}else
				{
					$select .= "avg(".$parameter->kolom_sensor.") as ".$parameter->nama_parameter.",";
				}

			}
			$select_fix = substr($select, 0, -1);
			if($sesi == 'hari'){
				$query_data = $this->db->query('select waktu,'.$select_fix.' from '.$tabel.' use index(waktu) where code_logger = "' . $idlogger . '" and waktu >= "' . $tgl_awal . '" and waktu <= "' . $tgl_akhir . '" group by HOUR(waktu),DAY(waktu),MONTH(waktu),YEAR(waktu) order by waktu asc ')->result_array();
			}else if($sesi == 'bulan'){
				$query_data = $this->db->query('select waktu,'.$select_fix.' from '.$tabel.' use index(waktu) where code_logger = "' . $idlogger . '" and waktu >= "' . $tgl_awal . '" and waktu <= "' . $tgl_akhir . '" group by DAY(waktu),MONTH(waktu),YEAR(waktu) order by waktu asc ')->result_array();
			}else{
				$query_data = $this->db->query('select waktu,'.$select_fix.' from '.$tabel.' use index(waktu) where code_logger = "' . $idlogger . '" and waktu >= "' . $tgl_awal . '" and waktu <= "' . $tgl_akhir . '" group by MONTH(waktu),YEAR(waktu) order by waktu asc ')->result_array();
			}
			
			foreach($query_data as $k=>$v){
				
				if (array_key_exists("Debit", $v) and $idlogger == '10063') {
					$debit = $this->kalimeneng($v['Debit']);
					if($v['Debit']<0){
						$query_data[$k]['Debit'] = number_format(0,2,'.','') ;
					}else{
						$query_data[$k]['Debit'] = number_format($debit,2,'.','') ;
					}
				}
				if(array_key_exists("Debit", $v) and $idlogger == '10249') {
					$h = $v['Debit'];
					$n2 = $v['Elevasi_Muka_Air'];
					$query_data[$k]['Debit'] = number_format($this->linear_interpolation($n2*100) *$h,2,'.','');
				}
				if(array_key_exists("Luas_Penampang_Basah", $v)) {
					$n2 = $v['Elevasi_Muka_Air'];
					$query_data[$k]['Luas_Penampang_Basah'] = number_format($this->linear_interpolation($n2*100),2,'.','');
				}
				
				/*elseif($val['nama_parameter'] == 'Debit_Aliran_Sungai') {
					$n2 = $temp_data->sensor1 - $temp_data->sensor2;
					if($temp_data->sensor2 > $temp_data->sensor1){
						$param[$ky]['nilai'] = number_format(0,2,'.','');
					}else{
						$param[$ky]['nilai'] = number_format($this->debit_interpolation($n2),2,'.','');
					}

				}else{
					$param[$ky]['nilai'] = $h;
				}*/
			}
			if(!$query_data){
				$query_data ="kosong";	
			}
		}else
		{
			$query_parameter="kosong";
			$query_data ="kosong";
		}
		$data['parameter']=$query_parameter;
		$data['datapos'] = $query_data;
		$data['nama_lokasi'] = $nama_pos;
		$data['konten']='konten/back/v_datapos2';
		$this->load->view('template_admin/site',$data);
	}

	public function api()
	{
		$idlogger=$this->input->get('id_logger');
		$tgl_awal=$this->input->get('awal');
		$tgl_akhir=$this->input->get('akhir');
		$sesi = $this->input->get('interval');

		$select = "";
		$nama_pos = '';
		if(isset($idlogger) && isset($tgl_awal) && isset($tgl_akhir)){
			$nama_pos = $this->db->join('t_lokasi','t_lokasi.idlokasi = t_logger.lokasi_logger')->where('t_logger.id_logger',$idlogger)->get('t_logger')->row()->nama_lokasi;
			$tabel = $this->db->where('id_logger',$idlogger)->get('t_logger')->row()->tabel_main;

			$query_parameter=$this->db->query("SELECT * FROM t_logger INNER JOIN parameter_sensor ON t_logger.id_logger = parameter_sensor.logger_id where parameter_sensor.logger_id = '".$idlogger."'  order by cast(SUBSTRING(kolom_sensor,7) as unsigned)");
			foreach($query_parameter->result() as $parameter)
			{
				if($parameter->satuan == "mm"){
					$select .= "sum(".$parameter->kolom_sensor.") as ".$parameter->nama_parameter.",";
				}else
				{
					$select .= "ROUND(avg(".$parameter->kolom_sensor."),2) as ".$parameter->nama_parameter.",";
				}
			}
			$select_fix = substr($select, 0, -1);
			if($sesi == 'hari'){
				$query_data = $this->db->query('select waktu as Waktu,'.$select_fix.' from '.$tabel.' use index(waktu) where code_logger = "' . $idlogger . '" and waktu >= "' . $tgl_awal . ' 00:00" and waktu <= "' . $tgl_akhir . ' 23:59" group by HOUR(waktu),DAY(waktu),MONTH(waktu),YEAR(waktu) order by waktu asc ');
			}else if($sesi == 'bulan'){
				$query_data = $this->db->query('select waktu as Waktu,'.$select_fix.' from '.$tabel.' use index(waktu) where code_logger = "' . $idlogger . '" and waktu >= "' . $tgl_awal . ' 00:00" and waktu <= "' . $tgl_akhir . ' 23:59" group by DAY(waktu),MONTH(waktu),YEAR(waktu) order by waktu asc ');
			}else{
				$query_data = $this->db->query('select waktu as Waktu,'.$select_fix.' from '.$tabel.' use index(waktu) where code_logger = "' . $idlogger . '" and waktu >= "' . $tgl_awal . ' 00:00" and waktu <= "' . $tgl_akhir . ' 23:59" group by MONTH(waktu),YEAR(waktu) order by waktu asc ');
			}
			$qd = [];
			if(!$query_data->result()){
				$query_data ="kosong";	
			}else{
				foreach($query_data->result_array() as $k =>$v){
					$qd[$k]['Waktu'] = $v['Waktu'];
					foreach($v as $x => $q){
						if($x != 'Waktu'){
							$satuan = $this->getSatuanByParameter($query_parameter->result_array(), $x);
							$qd[$k][str_replace('_',' ',$x). ' ('. $satuan.')'] = $q;
						}

					}
				}
			}
		}else
		{
			$query_parameter="kosong";
			$query_data ="kosong";
		}

		echo json_encode($qd);
	}

	function getSatuanByParameter($data, $parameterName) {
		foreach ($data as $item) {
			if (
				isset($item['nama_parameter'], $item['satuan']) &&
				strtolower($item['nama_parameter']) === strtolower($parameterName)
			) {
				return $item['satuan'];
			}
		}
		return null; // jika tidak ditemukan
	}

	public function export($data) {
		echo json_encode($data);
	}

	public function tes_ajax(){
		echo json_encode($this->input->post('parameter'));
	}

	function export_excel (){

		include APPPATH.'third_party/PHPExcel/PHPExcel.php';

		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		// Settingan awal fil excel
		$title = $this->input->post('title');
		$excel->getProperties()->setCreator('Beacon Engineering')
			->setTitle("Data")
			->setDescription("Data Semua Parameter");

		$data = json_decode(htmlspecialchars_decode($this->input->post('data')));
		$parameter = json_decode(htmlspecialchars_decode($this->input->post('parameter')));

		$start = new DateTime($this->session->userdata('data_tglawal'));
		$end = new DateTime($this->session->userdata('data_tglakhir'));
		$interval = new DateInterval('P1D');
		$period = new DatePeriod($start, $interval, $end);
		$row = '2';
		$excel->setActiveSheetIndex(0)->setCellValue('A2', 'Waktu');
		$columns = 'B';
		foreach($parameter as $key=>$v){
			$cl = $columns ++;
			$excel->setActiveSheetIndex(0)->setCellValue($cl . $row, str_replace('_',' ', $v->nama_parameter . ' ('.$v->satuan.')'));
		}
		$row2 = 2;
		foreach($data as $k =>$vl){
			$rows = $row2 + 1 + $k ;
			$excel->setActiveSheetIndex(0)->setCellValue('A' . $rows, $vl->waktu);
			$column = 'B';
			foreach($parameter as $key=>$v){
				$cl = $column ++;
				$sensor =$v->nama_parameter;
				$excel->setActiveSheetIndex(0)->setCellValue($cl . $rows, number_format($vl->$sensor,2,'.','') );
			}

		}
		foreach(range('A','O') as $columnID) {
			$excel->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}
		$excel->setActiveSheetIndex(0)->setCellValue('A1', $title);
		$excel->setActiveSheetIndex(0)->mergeCells('A1:'.$cl.'1');
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$title.'.xlsx"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
	}
}
