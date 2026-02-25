<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

	function __construct()
	{
		parent :: __construct();
		$this->load->model('mlogin');
		$this->load->model('m_analisa');
	}

	function json_sihka () {
		$data = [
			'id_logger'=>'10250',
			'uid'=>'11111111',
			'nama_lokasi'=>'Pos_DAS_Bogowonto',
			'tanggal'=>'2023-04-24',
			'jam'=>'10:05',
			'latitude'=>'-7.779985',
			'longitude'=>'110.44871',
			'tma'=>'2',
			'ch'=>'0.2',
		];
		echo json_encode($data);
	}

	function sihka(){
		$idlogger= $this->input->get('id_logger');
		$bulan= $this->input->get('bulan');
		$username = $this->input->get('username');
		$password = $this->input->get('password');
		$awal = $bulan . '-01 00:00';
		$akhir = $bulan . '-31 23:59';
		$data_logger = $this->db->join('kategori_logger','kategori_logger.id_katlogger=t_logger.kategori_log')->join('t_lokasi','t_lokasi.idlokasi=t_logger.lokasi_logger')->where('t_logger.id_logger',$idlogger)->get('t_logger')->row();
		$parameter_sensor = $this->db->where('logger_id',$idlogger)->get('parameter_sensor')->result_array();

		$dt = [];
		if($username == 'userbbws' && $password == 'user1'){
			if($data_logger){
				$data = $this->db->query('select waktu,sensor1, sensor2, sensor3, sensor4, sensor5, sensor6, sensor7,sum(sensor8) as sensor8, sum(sensor9) as sensor9, sensor10, sensor11, sensor12, sensor13, sensor14, sensor15, sensor16 from '.$data_logger->tabel_main.' where code_logger = "'.$idlogger.'" and waktu >= "'.$awal.'" and waktu <= "'.$akhir.'" GROUP BY TIMESTAMPDIFF(MINUTE, "1970-01-01 00:00:00", waktu) DIV 5')->result_array();
				if($data){
					foreach($data as $key2=>$v){
						$dt[$key2]['waktu'] = $v['waktu'];

						foreach($parameter_sensor as $key=>$val){
							$field_sensor = $val['kolom_sensor'];
							$nama_parameter = $val['nama_parameter'];
							$dt[$key2]['data'][] = [
								'namaParameter'=>$nama_parameter,
								'nilai'=> number_format($v[$field_sensor],2,'.',''),
								'satuan'=>$val['satuan'],
							];
						}
					}
				}
				$vt = [
					'status'=>true,
					'namaPos'=>$data_logger->nama_lokasi,
					'data'=>$dt
				];
				echo json_encode($vt);
			}else{
				$vt = [
					'status'=>false,
					'message'=>'ID Logger Tidak Terdaftar'
				];
				echo json_encode($vt);
			}
		}else{
			$vt = [
				'status'=>false,
				'message'=>'Username atau Password Salah'
			];
			echo json_encode($vt);
		}
	}

	function sihka2(){ 
		$idlogger= $this->input->get('id_logger'); 
		$awal = $this->input->get('awal') . ' 00:00'; 
		$akhir = $this->input->get('akhir') . ' 23:59'; 
		$data_logger = $this->db->join('kategori_logger','kategori_logger.id_katlogger=t_logger.kategori_log')->join('t_lokasi','t_lokasi.idlokasi=t_logger.lokasi_logger')->where('t_logger.id_logger',$idlogger)->get('t_logger')->row(); 
		$parameter_sensor = $this->db->where('logger_id',$idlogger)->get('parameter_sensor')->result_array();
		$dt = []; 
		if($data_logger){ 
			$data = $this->db->query('select * from '.$data_logger->tabel_main.' where code_logger = "'.$idlogger.'" and waktu >= "'.$awal.'" and waktu <= "'.$akhir.'"')->result_array(); 
			if($data){ 
				foreach($data as $key2=>$v){ 
					$dt[$key2]['waktu'] = $v['waktu'];
					foreach($parameter_sensor as $key=>$val){ 
						$field_sensor = $val['kolom_sensor']; 
						$nama_parameter = $val['nama_parameter'];
						$dt[$key2]['data'][] = [ 
							'namaParameter'=>$nama_parameter, 
							'nilai'=> number_format($v[$field_sensor],2,'.',''), 
							'satuan'=>$val['satuan'], 
						]; 
					} 
				} 
			} 
			$vt = [ 
				'status'=>true, 
				'namaPos'=>$data_logger->nama_lokasi, 
				'data'=>$dt 
			]; 
			echo json_encode($vt); 
		}else{ 
			$vt = [ 
				'status'=>false, 
				'message'=>'ID Logger Tidak Terdaftar' 
			]; 
			echo json_encode($vt); 
		} 
	}


	function api_logger ($id_logger){
		$username = $this->input->get('username');
		$password = $this->input->get('password');

		if($username == 'userbbws' and $password=='user1'){
			$data_lokasi = $this->db->join('kategori_logger','kategori_logger.id_katlogger = t_logger.kategori_log')->join('t_lokasi','t_lokasi.idlokasi = t_logger.lokasi_logger')->where('t_logger.id_logger', $id_logger)->get('t_logger')->row();
			$temp_data = $this->db->where('code_logger',$id_logger)->get($data_lokasi->temp_data)->row();

			$param = $this->db->where('logger_id',$id_logger)->get('parameter_sensor')->result_array();
			$sensor = [];
			$awal=date('Y-m-d H:i',(mktime(date('H')-1,0,0,date('m'),date('d'),date('Y'))));
			foreach($param as $key=>$val){
				$kolom = $val['kolom_sensor'];
				$analisa = $this->db->select('sum('.$kolom.') as akum')->select('avg('.$kolom.') as rata')->select('min('.$kolom.') as min')->select('max('.$kolom.') as max')->where('code_logger',$id_logger)->where('waktu >=',$awal)->get($data_lokasi->tabel)->row();
				if($kolom=='sensor8' || $kolom=='sensor9')
				{
					$sensor[]=array(
						'namaSensor'=>$val['nama_parameter'],
						'value'=>$temp_data->$kolom,
						'min'=>number_format($analisa->min,2),
						'max'=>number_format($analisa->max,2),
						'akum'=>number_format($analisa->akum,2),
						'satuan'=>$val['satuan']

					);
				}else{
					$sensor[]=array(
						'namaSensor'=>$val['nama_parameter'],
						'value'=>$temp_data->$kolom,
						'min'=>number_format($analisa->min,2),
						'max'=>number_format($analisa->max,2),
						'avg'=>number_format($analisa->rata,2),
						'satuan'=>$val['satuan']
					);
				}
			}
			$data = [
				'lokasi'=>$data_lokasi->nama_lokasi,
				'lat'=>$data_lokasi->latitude,
				'long'=>$data_lokasi->longitude,
				'waktu'=>$temp_data->waktu,
				'sensor'=>$sensor,
			];
			echo json_encode($data);
		}
	}

	function login_app2()
	{
		$username = $this->input->get('username');
		$password = md5($this->input->get('password'));
		$this->mlogin->apiambilPengguna2($username, $password);

	}

	function linear_interpolation($x) {
		// Data is now embedded within the function
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

		// If x is outside the range of the data, return null
		if (is_null($x1) || is_null($x2)) {
			return null;
		}

		// If x matches an x value in the data, return the corresponding y value
		if ($x == $x1) {
			return $y1;
		}

		// Calculate the interpolated y value
		$y = $y1 + (($x - $x1) / ($x2 - $x1)) * ($y2 - $y1);

		return $y;
	}

	function notifikasi(){
		$tanggal = $this->input->get('tanggal');
		$data = $this->db->select('notifikasi.id,t_logger.id_logger,id_tingkat_siaga,kategori_log,nama_lokasi,tma,datetime')->join('t_logger','t_logger.id_logger = notifikasi.id_logger')->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')->like('datetime',$tanggal)->order_by('datetime','desc')->get('notifikasi')->result_array();
		$date_now=date('Y-m-d H:i:s');
		$awal = date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($date_now)));
		foreach($data as $key=>$vl){
			$nama = $this->db->where('id_status',$vl['id_tingkat_siaga'])->get('tingkat_siaga_awlr')->row();
			$data[$key]['nama'] = $nama->nama;
			$parameter = $this->db->where('logger_id',$vl['id_logger'])->limit(1)->get('parameter_sensor')->row();
			$data[$key]['parameter'] = $parameter;
			$kategori = $this->db->where('id_katlogger',$vl['kategori_log'])->get('kategori_logger')->row();
			$data[$key]['kategori'] = $kategori;
			$waktu = $this->db->where('code_logger',$vl['id_logger'])->get($kategori->temp_data)->row();
			if($waktu >= $awal){
				$koneksi = 'On';
			}else{
				$koneksi = 'Off';
			}
			$data[$key]['koneksi'] = $koneksi;
		}
		echo json_encode($data);
	}

	function live_data ($id_logger){
		$parameter = $this->db->where('logger_id',$id_logger)->get('parameter_sensor')->result_array();
		$tabel = $this->db->join('kategori_logger','kategori_logger.id_katlogger = t_logger.kategori_log')->where('t_logger.id_logger',$id_logger)->get('t_logger')->row()->tabel_main;
		$data_1 = $this->db->where('code_logger',$id_logger)->limit(20)->get($tabel)->result_array();
		$dt= [];
		foreach($data_1 as $key=>$val){
			$pr = [];
			foreach($parameter as $k=>$v){
				$kolom = $v['kolom_sensor'];
				$nilai = $val[$kolom];
				$pr[$k]['nama_parameter']= $v['nama_parameter'];
				$pr[$k]['nilai']= $nilai;
				$pr[$k]['satuan']= $v['satuan'];
			}
			$dt[$key]['waktu'] = $val['waktu'];
			$dt[$key]['data'] = $pr;
		}
		$data = array(
			'param'=>$parameter,
			'data'=>$dt,
		);
		echo json_encode($data);
	}

	function live_data2 (){
		$id_logger = $this->input->get('id_logger');
		$idsensor = $this->input->get('id_sensor');
		$parameter = $this->db->where('logger_id',$id_logger)->get('parameter_sensor')->result_array();
		$param1 = $this->db->where('logger_id',$id_logger)->where('id_param',$idsensor)->get('parameter_sensor')->row();

		$tabel = $this->db->join('kategori_logger','kategori_logger.id_katlogger = t_logger.kategori_log')->join('t_lokasi','t_lokasi.idlokasi = t_logger.lokasi_logger')->where('t_logger.id_logger',$id_logger)->get('t_logger')->row();

		$data_1 = $this->db->where('code_logger',$id_logger)->order_by('waktu','desc')->limit(25)->get($tabel->tabel_main)->result_array();
		$data_terakhir = $this->db->where('code_logger',$id_logger)->get($tabel->temp_data)->row();
		$data_tabel= [];
		$kolom = $param1->kolom_sensor;

		foreach($data_1 as $key=>$val){
			$data_tabel[$key]['waktu'] = $val['waktu'];
			$data_tabel[$key]['nilai'] = $val[$kolom];
		}		
		array_multisort(array_map('strtotime',array_column($data_1,'waktu')),
						SORT_ASC, 
						$data_1);
		$data_chart = [];
		foreach($data_1 as $key=>$val){
			$data_chart[$key]['waktu'] = $val['waktu'];
			$data_chart[$key]['nilai'] = $val[$kolom];
		}

		$waktu = $data_terakhir->waktu;
		$date_now = date('Y:m:d H:i:s');
		$awal = date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($date_now)));
		if($waktu > $awal){
			$status = 'On';
		}else{
			$status = 'Off';
		}
		$data = array(
			'nama_pos'=>$tabel->nama_lokasi,
			'nama_param'=>$param1->nama_parameter,
			'koneksi'=>$status,
			'waktu'=>$waktu,
			'kolom'=>$param1->kolom_sensor,
			'satuan'=>$param1->satuan,
			'tipe_graf'=>$param1->tipe_graf,
			'param'=>$parameter,
			'data_tabel'=>$data_tabel,
			'data_chart'=>$data_chart,

		);
		echo json_encode($data);
	}

	public function pilihparameter($idlogger)
	{
		$data=array();
		$q_parameter=$this->db->query("SELECT * FROM parameter_sensor where logger_id='".$idlogger."'");
		foreach($q_parameter->result() as $param)
		{
			$data[]=array(
				'idParameter'=>$param->id_param,
				'namaParameter'=>$param->nama_parameter,
				'fieldParameter'=>$param->kolom_sensor,
				'icon'=>$param->icon_app
			);
		}
		echo json_encode($data);
	}

	function lokasi_new(){
		$kategori=array();
		$data = array();
		$query_kategori=$this->db->query('select * from kategori_logger where view = 1');
		//$klasifikasi
		foreach ($query_kategori->result()  as $kat) {
			$tabel=$kat->tabel;
			$tabel_temp=$kat->temp_data;
			$content=array();
			$query_lokasilogger=$this->db->query("select * from t_logger inner join t_lokasi ON t_logger.lokasi_logger=t_lokasi.idlokasi where kategori_log='$kat->id_katlogger'");

			foreach ($query_lokasilogger->result() as $loklogger){
				$id_logger=$loklogger->id_logger;
				$tabel_main = $loklogger->tabel_main;
				$parameter=array();
				$query_data=$this->db->query('select * from '.$tabel_temp.' where code_logger="'.$id_logger.'"');
				foreach ($query_data->result() as $dt){
					$waktu=$dt->waktu;
					$date_now=date('Y-m-d H:i:s');
					$awal = date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($date_now)));
					$query_parameter=$this->db->query('select * from parameter_sensor where logger_id="'.$id_logger.'" limit 1');
					foreach ($query_parameter->result() as $param) {
						$kolom=$param->kolom_sensor;
						$dta=$dt->$kolom;
						$get='tabel='.$kat->tabel.'&id_param='.$param->id_param;
						$link_parameter= anchor($kat->controller.'/set_sensordash?'.$get,$param->nama_parameter);
						$parameter[]='
								<td>'.$link_parameter.'</td><td>'.$dta.' '.$param->satuan.'</td>
								';	
					}
					$data_sensor = $query_parameter->result_array()[0];
					######### cek status koneksi ######
					$dta=$dt->$kolom;
					$koneksi = '';

					if($waktu >= $awal)
					{
						if($kat->controller == 'awr' or $kat->controller == 'arr'){
							$waktu=$dt->waktu;
							$awal=date('Y-m-d H:i',(mktime(date('H')-1)));
							$p_utama = $this->db->where('logger_id',$id_logger)->where('parameter_utama','1')->get('parameter_sensor')->row();
							$query_akumulasi = $this->db->query('select sum('.$p_utama->kolom_sensor.') as '.$p_utama->kolom_sensor.' from '.$tabel_main.' where code_logger = "' . $id_logger . '" and waktu >= "' . date('Y-m-d H') . ':00" ')->row();
							$kolom_s = $p_utama->kolom_sensor;
							$data_p = $query_akumulasi->$kolom_s;
							if ($data_p <= 0) {
								$koneksi = 'Tidak Hujan';
								$kn = 'On';
								$icon_marker=$kat->controller.'_on';
							} elseif ($data_p >= 0.1 and $data_p < 1) {
								$koneksi = 'Hujan Sangat Ringan';
								$kn = 'On';
								$icon_marker=$kat->controller.'_hujan_sangat_ringan';
							} elseif ($data_p >= 1 and $data_p < 5) {
								$koneksi = 'Hujan Ringan';
								$kn = 'On';
								$icon_marker=$kat->controller.'_hujan_ringan';
							} elseif ($data_p >= 5 and $data_p < 10) {
								$koneksi = 'Hujan Sedang';
								$kn = 'On';
								$icon_marker=$kat->controller.'_hujan_sedang';
							} elseif ($data_p >= 10 and $data_p < 20) {
								$koneksi = 'Hujan Lebat';
								$kn = 'On';
								$icon_marker=$kat->controller.'_hujan_lebat';
							} elseif ($data_p >= 20) {
								$koneksi = 'Hujan Sangat Lebat';
								$kn = 'On';
								$icon_marker=$kat->controller.'_hujan_sangat_lebat';
							}
						}else{
							$koneksi = 'Koneksi Terhubung';
							$kn = 'On';
							$icon_marker=$kat->controller.'_on';
						}
					}else{
						$koneksi = 'Koneksi Terputus';
						$kn = 'Off';
						$icon_marker=$kat->controller.'_off';
					}

				}

				$data[] = array(
					'id_kategori'=>$kat->id_katlogger,
					'tabel' => $tabel,
					'sensor'=>$data_sensor['id_param'],
					'nama_param'=>$data_sensor['nama_parameter'],
					'icon_sensor'=>$data_sensor['icon_app'],
					'id_param'=>$data_sensor['id_param'],
					'lokasi'=>$loklogger->nama_lokasi,
					'latitude'=>$loklogger->latitude,
					'longitude'=>$loklogger->longitude,
					'id_logger'=>$id_logger,
					'waktu'=>$waktu,
					'koneksi'=>$koneksi,
					'koneksi_log'=>$kn,
					'icon' => $icon_marker,
				);
			}

		}
		echo json_encode($data);
	}

	function menu()
	{
		$dataMenu=array();
		$kategori=$this->db->query("SELECT * FROM kategori_logger where view = 1");
		foreach ($kategori->result() as $kat) {
			$logger = $this->db->where('kategori_log',$kat->id_katlogger)->get('t_logger')->result_array();
			$filter = $this->db->where('id_kategori',$kat->id_katlogger)->get('filter')->result_array();
			if($logger){
				$dataMenu[]=array(
					'id_kategori' =>$kat->id_katlogger,
					'menu' =>$kat->nama_kategori,
					'controller'=>$kat->controller,
					'tabel'=>$kat->tabel,
					'icon'=>$kat->icon_app,
					'temp_tabel'=>$kat->temp_data,	
					'filter'=>$filter,	
				); 
			}

		}
		echo json_encode($dataMenu);
	}


	public function notif_versi(){
		$versi = '1.3.2';
		echo json_encode(array(
			'versi'=> $versi, 
			'link'=>'https://bbwsso.monitoring4system.com/pengaturan/unduh_aplikasi',
			'status'=> true, 
			'pesan'=>'Sistem Sedang Dimatikan',

		));
	}

	public function notif_versi_ios(){
		$versi = '1.3.2';
		echo json_encode(array(
			'versi'=> $versi, 
			'link'=>'https://bbwsso.monitoring4system.com/pengaturan/unduh_aplikasi',
			'status'=> true, 
			'pesan'=>'Sistem Menyala'));
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

		// If x is outside the range of the data, return null
		if (is_null($x1) || is_null($x2)) {
			return null;
		}

		// If x matches an x value in the data, return the corresponding y value
		if ($x == $x1) {
			return $y1;
		}

		// Calculate the interpolated y value
		$y = $y1 + (($x - $x1) / ($x2 - $x1)) * ($y2 - $y1);

		return $y;
	}

	function lokasi()
	{
		$kategori=$this->input->get('kategori_log');
		$tabel=$this->input->get('tabel');
		$dataLokasi=array();

		$query_lokasi = $this->db->query("SELECT * FROM t_logger join t_lokasi on t_logger.lokasi_logger=t_lokasi.idlokasi where kategori_log='".$kategori."'");
		foreach($query_lokasi->result() as $lokasilog)
		{
			$this->session->set_userdata('id_log',$lokasilog->id_logger);
			$query_perbaikan=$this->db->query('select * from t_perbaikan where id_logger="'.$lokasilog->id_logger.'" ');
			if($query_perbaikan->num_rows() == null) {
				$cek = $this->db->where('code_logger',$lokasilog->id_logger)->get($tabel)->row()->waktu;

				$date = date('Y-m-d H:i:s',(mktime(date('H')-1)));
				if($cek > $date){
					$status = 'On';
				}else{
					$status = 'Off';
				}
				$dataLokasi[]=array(
					'logger_id' =>$lokasilog->id_logger,
					'nama_logger' =>$lokasilog->nama_logger,
					'lokasi' =>$lokasilog->nama_lokasi,
					'latitude'=>$lokasilog->latitude,
					'longitude'=>$lokasilog->longitude,
					'status'=>$status,
				);
			}
			else {
				$dataLokasi[]=array(
					'logger_id' =>$lokasilog->id_logger,
					'nama_logger' =>$lokasilog->nama_logger,
					'lokasi' =>$lokasilog->nama_lokasi,
					'latitude'=>$lokasilog->latitude,
					'longitude'=>$lokasilog->longitude,
					'status'=>"perbaikan",	
				);
			}
		}
		$data_psda = [];
		if($kategori == '2'){
			$data_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/api/lokasi_baru?kategori_log=8&tabel=temp_awlr'),true);
		}elseif($kategori == '6'){
			$data_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/api/lokasi_baru?kategori_log=1&tabel=temp_weather_station'),true);
		}elseif($kategori == '7'){
			$data_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/api/lokasi_baru?kategori_log=2&tabel=temp_weather_station'),true);
		}

		if($data_psda){
			$array_merge = array_merge($dataLokasi,$data_psda['lokasi']);
			echo json_encode(array('lokasi_first'=>$array_merge[0],'lokasi'=>$array_merge));
		}else{
			echo json_encode(array('lokasi_first'=>$dataLokasi[0],'lokasi'=>$dataLokasi));
		}
		//echo $this->session->userdata('id_log');
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

	function dtakhir()
	{
		$idlog = $this->input->get('idlogger');
		$tabel = $this->input->get('tabel');
		$data_logger = $this->db->join('t_lokasi','t_logger.lokasi_logger=t_lokasi.idlokasi')->where('t_logger.id_logger',$idlog)->get('t_logger')->row();

		if($data_logger){
			$is_perbaikan = $this->db->where('id_logger',$idlog)->count_all_results('t_perbaikan') > 0;
			$qparam = $this->db->query("SELECT * FROM parameter_sensor WHERE logger_id='$idlog' ORDER BY CAST(SUBSTR(kolom_sensor,7) AS UNSIGNED)");
			$qdata = $this->db->query("SELECT * FROM $tabel WHERE code_logger='$idlog' ORDER BY waktu DESC LIMIT 1")->row();
			$waktu = $qdata->waktu ?? null;
			$data_terakhir = [];
			foreach ($qparam->result() as $s) {
				$h = $qdata->{$s->kolom_sensor} ?? null;
				if ($s->nama_parameter=='Illumination') $v/=1000;
				if (!$is_perbaikan) {
					if ($s->debit_awlr=='1' and $idlog == '10063') {

						$debit = $this->kalimeneng((float)$h);
						$h = $h<0?0:$debit;
					} elseif ($s->nama_parameter=='Debit' && $idlog=='10249') {
						$h = $this->linear_interpolation($qdata->sensor2*100)*$h;
					} elseif ($s->nama_parameter=='Luas_Penampang_Basah') {
						$h = $this->linear_interpolation($qdata->sensor2*100);
					} elseif ($s->nama_parameter=='Debit_Aliran_Sungai') {
						$h = $this->debit_interpolation(abs($qdata->sensor1-$qdata->sensor2));
					}
				}
				if ($s->nama_parameter!='Wind_Direction') $h=number_format($h,2,'.','');
				$data_terakhir[]=[
					'idsensor'=>$s->id_param,
					'sensor'=>$s->nama_parameter,
					'data'=>$h,
					'satuan'=>$s->satuan,
					'icon'=>$s->icon_app,
					'tipe_graf'=>$s->tipe_graf
				];
			}
			$out=[
				'nama_logger'=>$data_logger->nama_lokasi??'',
				'waktu'=>$waktu,
				'tabel'=>$tabel,
				'data_terakhir'=>$data_terakhir
			];
			if($is_perbaikan)$out['status']='perbaikan';
		}else{
			if($tabel == 'temp_awlr'){
				$out = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/api/dtakhir?idlogger='.$idlog.'&tabel=temp_awlr'),true);
			}elseif($tabel != 'temp_afmr'){
				$out = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/api/dtakhir?idlogger='.$idlog.'&tabel=temp_weather_station'),true);
			}
		}

		echo json_encode($out);
	}

	function analisapertanggal()
	{
		$idlogger=$this->input->get('idlogger');
		$idsensor=$this->input->get('idsensor');
		$tanggal=$this->input->get('tanggal');

		$data=array();
		$min=array();
		$max=array();
		$tb_main = $this->db->where('id_logger',$idlogger)->get('t_logger')->row();
		if($tb_main){
			$qparam=$this->db->query("SELECT * FROM parameter_sensor where id_param='".$idsensor."'");		
			foreach($qparam->result() as $param)
			{
				if($param->tipe_graf=='column')
				{
					$namaSensor='Akumulasi_'.$param->nama_parameter;
					$select='sum('.$param->kolom_sensor.') as '.$namaSensor;
				}
				else{
					$namaSensor='Rerata_'.$param->nama_parameter;
					$select='avg('.$param->kolom_sensor.') as '.$namaSensor;
				}

				$sensor=$param->kolom_sensor;
				$satuan=$param->satuan;
				$namaparameter=$param->nama_parameter;
				if($param->debit_awlr == '1'){
					$cek_rumus = true;
				}else{
					$cek_rumus = false;
				}
			}
			$query_data = $this->db->query("SELECT  avg(sensor1 - sensor2) AS avg_diff, min(sensor1 - sensor2) AS min_diff, max(sensor1 - sensor2) AS max_diff, avg(sensor2) as tma,min(sensor2) as tma_min,max(sensor2) as tma_max,waktu,".$select.",min(".$sensor.") as min,max(".$sensor.") as max FROM ".$tb_main->tabel_main." where code_logger='".$idlogger."' and waktu >= '".$tanggal." 00:00' and waktu <= '".$tanggal." 23:59' group by HOUR(waktu),DAY(waktu),MONTH(waktu),YEAR(waktu);");
			$hsl = $query_data->result();

			foreach($hsl as $datalog)
			{
				$h = $datalog->$namaSensor;
				$max_value = $datalog->max;
				$min_value = $datalog->min;

				if ($cek_rumus && $namaparameter == 'Debit') {
					$rumus = $this->db->where('id_logger',$idlogger)->get('rumus_debit')->row()->rumus;
					if($h<0){
						$avg = number_format(0,2,'.','') ;
					}else{
						$debit_avg= eval('return ' . $rumus . ';');
						$h =  $min_value;
						$min_value = eval('return ' . $rumus . ';');
						$h =  $max_value;
						$max_value = eval('return ' . $rumus . ';');
						$h = $debit_avg;
					}
				} 
				if($namaparameter == 'Debit' and $idlogger == '10249'){
					if($h<0){
						$avg = number_format(0,2,'.','') ;
					}else{
						$n2 = $datalog->tma;
						$h = number_format($this->linear_interpolation($n2*100) *$datalog->$namaSensor,2,'.','');
						$n2_max = $datalog->tma_max;
						$max_value = number_format($this->linear_interpolation($n2_max*100) *$datalog->max,2,'.','');
						$n2_min = $datalog->tma_min;
						$min_value = number_format($this->linear_interpolation($n2_min*100) *$datalog->min,2,'.','');
					}
				}elseif($namaparameter == 'Luas_Penampang_Basah'){
					if($h<0){
						$avg = number_format(0,2,'.','') ;
					}else{
						$n2 = $datalog->tma;
						$h = number_format($this->linear_interpolation($n2*100),2,'.','');
						$n2_max = $datalog->tma_max;
						$max_value = number_format($this->linear_interpolation($n2_max*100),2,'.','');
						$n2_min = $datalog->tma_min;
						$min_value = number_format($this->linear_interpolation($n2_min*100),2,'.','');
					}
				}elseif($namaparameter == 'Debit_Aliran_Sungai'){

					$avg_debit = $this->debit_interpolation(abs($datalog->avg_diff));
					$min_debit = $this->debit_interpolation(abs($datalog->min_diff));
					$max_debit = $this->debit_interpolation(abs($datalog->max_diff));

					$h = $avg_debit;
					$min_value = $min_debit;
					$max_value = $max_debit;
				}
				$waktu[]= date('Y-m-d H',strtotime($datalog->waktu)).":00";
				$data[]= number_format($h,2,'.',''); 
				$min[]=number_format($min_value,2,'.','');
				$max[]=number_format($max_value,2,'.','');
			}
			if($hsl){
				$stts = 'sukses';
				$dataAnalisa=array(
					'status'=>'sukses',
					'idLogger' =>$idlogger,
					'nosensor'=>$sensor,
					'namaSensor' =>$namaSensor,
					'satuan'=>$satuan,
					'waktu' =>$waktu,
					'tipegraf'=>$param->tipe_graf,
					'data'=>$data,
					'datamin'=>$min,
					'datamax'=>$max,
				);
			}else{
				$stts = 'error';
				$dataAnalisa = null;
			}
		}else{
			$data_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/api/analisapertanggal2?idsensor='.$idsensor.'&tanggal='.$tanggal),true);
			if($data_psda['data']){
				$stts = 'sukses';
				$dataAnalisa= $data_psda;
			}else{
				$stts = 'error';
				$dataAnalisa = null;
			}
		}



		echo json_encode(
			array(
				'status' => $stts,
				'data'=>$dataAnalisa
			)
		);
	}

	function analisaperbulan()
	{
		$idlogger=$this->input->get('idlogger');
		$idsensor=$this->input->get('idsensor');
		$tabel=$this->input->get('tabel');
		$tanggal=$this->input->get('tanggal');

		$data=array();
		$min=array();
		$max=array();
		$waktu = [];
		$tb_main = $this->db->where('id_logger',$idlogger)->get('t_logger')->row();
		$qparam=$this->db->query("SELECT * FROM parameter_sensor where id_param='".$idsensor."'");		
		foreach($qparam->result() as $param)
		{

			if($tabel == 't_klimatologi' && $param->kolom_sensor == 'sensor8')
			{
				$namaSensor='Akumulasi_'.$param->nama_parameter;
				$select='sum('.$param->kolom_sensor.')as '.$namaSensor;
			}elseif($tabel == 'arr' && $param->kolom_sensor == 'sensor9')
			{
				$namaSensor='Akumulasi_'.$param->nama_parameter;
				$select='sum('.$param->kolom_sensor.')as '.$namaSensor;
			}
			elseif($param->tipe_graf=='column')
			{
				$namaSensor='Akumulasi_'.$param->nama_parameter;
				$select='sum('.$param->kolom_sensor.')as '.$namaSensor;
			}
			else{
				$namaSensor='Rerata_'.$param->nama_parameter;
				$select='avg('.$param->kolom_sensor.')as '.$namaSensor;
			}
			$sensor=$param->kolom_sensor;
			$satuan=$param->satuan;
			$namaparameter=$param->nama_parameter;
			if($param->debit_awlr == '1'){
				$cek_rumus = true;
			}else{
				$cek_rumus = false;
			}
		}
		$query_data = $this->db->query("SELECT avg(sensor1 - sensor2) AS avg_diff, min(sensor1 - sensor2) AS min_diff, max(sensor1 - sensor2) AS max_diff,avg(sensor2) as tma,min(sensor2) as tma_min,max(sensor2) as tma_max,waktu,DATE(waktu) as tanggal,".$select.",min(".$sensor.") as min,max(".$sensor.") as max FROM ".$tb_main->tabel_main." where code_logger='".$idlogger."' and waktu >= '".$tanggal."-01 00:00' and waktu <= '".$tanggal."-31 23:59' group by DAY(waktu),MONTH(waktu),YEAR(waktu);");
		$dbt = 0;

		$hsl = $query_data->result();

		foreach($hsl as $datalog)
		{
			$h = $datalog->$namaSensor;
			$max_value = $datalog->max;
			$min_value = $datalog->min;

			if ($cek_rumus && $namaparameter == 'Debit') {
				$rumus = $this->db->where('id_logger',$idlogger)->get('rumus_debit')->row()->rumus;
				if($h<0){
					$avg = number_format(0,2,'.','') ;
				}else{
					$debit_avg= eval('return ' . $rumus . ';');
					$h =  $min_value;
					$min_value = eval('return ' . $rumus . ';');
					$h =  $max_value;
					$max_value = eval('return ' . $rumus . ';');
					$h = $debit_avg;
				}
			} 
			if($namaparameter == 'Debit' and $idlogger == '10249'){
				if($h<0){
					$avg = number_format(0,2,'.','') ;
				}else{
					$n2 = $datalog->tma;
					$h = number_format($this->linear_interpolation($n2*100) *$datalog->$namaSensor,2,'.','');
					$n2_max = $datalog->tma_max;
					$max_value = number_format($this->linear_interpolation($n2_max*100) *$datalog->max,2,'.','');
					$n2_min = $datalog->tma_min;
					$min_value = number_format($this->linear_interpolation($n2_min*100) *$datalog->min,2,'.','');
				}
			}elseif($namaparameter == 'Luas_Penampang_Basah'){
				if($h<0){
					$avg = number_format(0,2,'.','') ;
				}else{
					$n2 = $datalog->tma;
					$h = number_format($this->linear_interpolation($n2*100),2,'.','');
					$n2_max = $datalog->tma_max;
					$max_value = number_format($this->linear_interpolation($n2_max*100),2,'.','');
					$n2_min = $datalog->tma_min;
					$min_value = number_format($this->linear_interpolation($n2_min*100),2,'.','');
				}
			}elseif($namaparameter == 'Debit_Aliran_Sungai'){

				$avg_debit = $this->debit_interpolation(abs($datalog->avg_diff));
				$min_debit = $this->debit_interpolation(abs($datalog->min_diff));
				$max_debit = $this->debit_interpolation(abs($datalog->max_diff));

				$h = $avg_debit;
				$min_value = $min_debit;
				$max_value = $max_debit;
			}
			$waktu[]= date('Y-m-d',strtotime($datalog->waktu));
			$data[]= number_format($h,2,'.',''); 
			$min[]=number_format($min_value,2,'.','');
			$max[]=number_format($max_value,2,'.','');

		}

		if($hsl){
			$stts = 'sukses';
			$dataAnalisa=array(
				'status'=>'sukses',
				'idLogger' =>$idlogger,
				'nosensor'=>$sensor,
				'namaSensor' =>$namaSensor,
				'satuan'=>$satuan,
				'waktu' =>$waktu,
				'tipegraf'=>$param->tipe_graf,
				'data'=>$data,
				'datamin'=>$min,
				'datamax'=>$max,
			);

		}else{
			$stts = 'error';
			$dataAnalisa = null;
		}

		echo json_encode(
			array(
				'status' => $stts,
				'data'=>$dataAnalisa
			)
		);
	}


	function analisaperrange()
	{
		$idlogger=$this->input->get('idlogger');
		$idsensor=$this->input->get('idsensor');
		$tabel=$this->input->get('tabel');
		$awal=$this->input->get('awal');
		$akhir=$this->input->get('akhir');

		$data=array();
		$min=array();
		$max=array();
		$waktu = [];
		$tb_main = $this->db->where('id_logger',$idlogger)->get('t_logger')->row();
		$qparam=$this->db->query("SELECT * FROM parameter_sensor where id_param='".$idsensor."'");		
		foreach($qparam->result() as $param)
		{

			if($tabel == 't_klimatologi' && $param->kolom_sensor == 'sensor8')
			{
				$namaSensor='Akumulasi_'.$param->nama_parameter;
				$select='sum('.$param->kolom_sensor.')as '.$namaSensor;
			}elseif($tabel == 'arr' && $param->kolom_sensor == 'sensor9')
			{
				$namaSensor='Akumulasi_'.$param->nama_parameter;
				$select='sum('.$param->kolom_sensor.')as '.$namaSensor;
			}
			elseif($param->tipe_graf=='column')
			{
				$namaSensor='Akumulasi_'.$param->nama_parameter;
				$select='sum('.$param->kolom_sensor.')as '.$namaSensor;
			}
			else{
				$namaSensor='Rerata_'.$param->nama_parameter;
				$select='avg('.$param->kolom_sensor.')as '.$namaSensor;
			}
			$sensor=$param->kolom_sensor;
			$satuan=$param->satuan;
			$namaparameter=$param->nama_parameter;
			if($param->debit_awlr == '1'){
				$cek_rumus = true;
			}else{
				$cek_rumus = false;
			}
		}
		$query_data = $this->db->query("SELECT avg(sensor1 - sensor2) AS avg_diff, min(sensor1 - sensor2) AS min_diff, max(sensor1 - sensor2) AS max_diff, avg(sensor2) as tma,min(sensor2) as tma_min,max(sensor2) as tma_max,waktu,DATE(waktu) as tanggal,".$select.",min(".$sensor.") as min,max(".$sensor.") as max FROM ".$tb_main->tabel_main." where code_logger='".$idlogger."' and waktu >='" . $awal . "' and waktu <='" . $akhir . " 23:59:00' group by HOUR(waktu),DAY(waktu),MONTH(waktu),YEAR(waktu) order by waktu asc;");
		$dbt = 0;
		$hsl = $query_data->result();

		foreach($hsl as $datalog)
		{
			$h = $datalog->$namaSensor;
			$max_value = $datalog->max;
			$min_value = $datalog->min;

			if ($cek_rumus && $namaparameter == 'Debit') {
				$rumus = $this->db->where('id_logger',$idlogger)->get('rumus_debit')->row()->rumus;
				if($h<0){
					$avg = number_format(0,2,'.','') ;
				}else{
					$debit_avg= eval('return ' . $rumus . ';');
					$h =  $min_value;
					$min_value = eval('return ' . $rumus . ';');
					$h =  $max_value;
					$max_value = eval('return ' . $rumus . ';');
					$h = $debit_avg;
				}
			} 

			if($namaparameter == 'Debit' and $idlogger == '10249'){
				if($h<0){
					$avg = number_format(0,2,'.','') ;
				}else{
					$n2 = $datalog->tma;
					$h = number_format($this->linear_interpolation($n2*100) *$datalog->$namaSensor,2,'.','');
					$n2_max = $datalog->tma_max;
					$max_value = number_format($this->linear_interpolation($n2_max*100) *$datalog->max,2,'.','');
					$n2_min = $datalog->tma_min;
					$min_value = number_format($this->linear_interpolation($n2_min*100) *$datalog->min,2,'.','');
				}
			}elseif($namaparameter == 'Luas_Penampang_Basah'){
				if($h<0){
					$avg = number_format(0,2,'.','') ;
				}else{
					$n2 = $datalog->tma;
					$h = number_format($this->linear_interpolation($n2*100),2,'.','');
					$n2_max = $datalog->tma_max;
					$max_value = number_format($this->linear_interpolation($n2_max*100),2,'.','');
					$n2_min = $datalog->tma_min;
					$min_value = number_format($this->linear_interpolation($n2_min*100),2,'.','');
				}
			}elseif($namaparameter == 'Debit_Aliran_Sungai'){

				$avg_debit = $this->debit_interpolation(abs($datalog->avg_diff));
				$min_debit = $this->debit_interpolation(abs($datalog->min_diff));
				$max_debit = $this->debit_interpolation(abs($datalog->max_diff));

				$h = $avg_debit;
				$min_value = $min_debit;
				$max_value = $max_debit;
			}
			$waktu[]= date('Y-m-d H',strtotime($datalog->waktu)).":00";
			$data[]= number_format($h,2,'.',''); 
			$min[]=number_format($min_value,2,'.','');
			$max[]=number_format($max_value,2,'.','');
		}
		if(!$hsl){
			$stts = 'error';
			$dataAnalisa = null;
		}else{
			$stts = 'sukses';
			$dataAnalisa=array(
				'status'=>'sukses',
				'idLogger' =>$idlogger,
				'nosensor'=>$sensor,
				'namaSensor' =>$namaSensor,
				'satuan'=>$satuan,
				'waktu' =>$waktu,
				'tipegraf'=>$param->tipe_graf,
				'data'=>$data,
				'datamin'=>$min,
				'datamax'=>$max,
			);
		}

		echo json_encode(
			array(
				'status' => $stts,
				'data'=>$dataAnalisa
			)
		);
	}

	function analisapertahun()
	{
		$idlogger=$this->input->get('idlogger');
		$idsensor=$this->input->get('idsensor');
		$tabel=$this->input->get('tabel');
		$tanggal=$this->input->get('tahun');

		$data=array();
		$min=array();
		$max=array();
		$dta_avg = array();
		$dta_min = array();
		$dta_max = array();
		$tb_main = $this->db->where('id_logger',$idlogger)->get('t_logger')->row();
		$qparam=$this->db->query("SELECT * FROM parameter_sensor where id_param='".$idsensor."'");	

		foreach($qparam->result() as $param)
		{
			if($param->tipe_graf=='column')
			{
				$namaSensor='Akumulasi_'.$param->nama_parameter;
				$select='sum('.$param->kolom_sensor.')as '.$namaSensor;
			}
			else{
				//$namaSensor='Rerata_'.$param->nama_parameter;
				$namaSensor='Rerata_'.$param->nama_parameter;
				$select='avg('.$param->kolom_sensor.')as '.$namaSensor;
			}
			$sensor=$param->kolom_sensor;
			$satuan=$param->satuan;
			$namaparameter=$param->nama_parameter;
			if($param->debit_awlr == '1'){
				$cek_rumus = true;
			}else{
				$cek_rumus = false;
			}
		}
		$query_data = $this->db->query("SELECT  avg(sensor1 - sensor2) AS avg_diff, min(sensor1 - sensor2) AS min_diff, max(sensor1 - sensor2) AS max_diff,avg(sensor2) as tma,min(sensor2) as tma_min,max(sensor2) as tma_max,waktu,DATE(waktu) as tanggal,MONTH(waktu) as bulan,".$select.",min(".$sensor.") as min,max(".$sensor.") as max FROM ".$tb_main->tabel_main." where code_logger='".$idlogger."' and waktu >= '".$tanggal."-01-01 00:00' and waktu <= '".$tanggal."-12-31 23:59' group by MONTH(waktu),YEAR(waktu);");

		$dbt = 0;
		foreach($query_data->result() as $datalog)
		{
			$h = $datalog->$namaSensor;
			$max_value = $datalog->max;
			$min_value = $datalog->min;

			if ($cek_rumus && $namaparameter == 'Debit') {
				$rumus = $this->db->where('id_logger',$idlogger)->get('rumus_debit')->row()->rumus;
				if($h<0){
					$avg = number_format(0,2,'.','') ;
				}else{
					$debit_avg= eval('return ' . $rumus . ';');
					$h =  $min_value;
					$min_value = eval('return ' . $rumus . ';');
					$h =  $max_value;
					$max_value = eval('return ' . $rumus . ';');
					$h = $debit_avg;
				}
			} 

			if($namaparameter == 'Debit' and $idlogger == '10249'){
				if($h<0){
					$avg = number_format(0,2,'.','') ;
				}else{
					$n2 = $datalog->tma;
					$h = number_format($this->linear_interpolation($n2*100) *$datalog->$namaSensor,2,'.','');
					$n2_max = $datalog->tma_max;
					$max_value = number_format($this->linear_interpolation($n2_max*100) *$datalog->max,2,'.','');
					$n2_min = $datalog->tma_min;
					$min_value = number_format($this->linear_interpolation($n2_min*100) *$datalog->min,2,'.','');
				}
			}elseif($namaparameter == 'Luas_Penampang_Basah'){
				if($h<0){
					$avg = number_format(0,2,'.','') ;
				}else{
					$n2 = $datalog->tma;
					$h = number_format($this->linear_interpolation($n2*100),2,'.','');
					$n2_max = $datalog->tma_max;
					$max_value = number_format($this->linear_interpolation($n2_max*100),2,'.','');
					$n2_min = $datalog->tma_min;
					$min_value = number_format($this->linear_interpolation($n2_min*100),2,'.','');
				}
			}elseif($namaparameter == 'Debit_Aliran_Sungai'){

				$avg_debit = $this->debit_interpolation(abs($datalog->avg_diff));
				$min_debit = $this->debit_interpolation(abs($datalog->min_diff));
				$max_debit = $this->debit_interpolation(abs($datalog->max_diff));

				$h = $avg_debit;
				$min_value = $min_debit;
				$max_value = $max_debit;
			}
			$waktu[]= date('Y-m',strtotime($datalog->waktu));
			$data[]= number_format($h,2,'.',''); 
			$min[]=number_format($min_value,2,'.','');
			$max[]=number_format($max_value,2,'.','');
		}

		if(!$query_data->result_array()){
			$stts = 'error';
			$dataAnalisa = null;
		}else{
			$stts = 'sukses';
			$dataAnalisa=array(
				'status'=>'sukses',
				'idLogger' =>$idlogger,
				'nosensor'=>$sensor,
				'namaSensor' =>$namaSensor,
				'satuan'=>$satuan,
				'waktu' =>$waktu,
				'tipegraf'=>$param->tipe_graf,
				'data'=>$data,
				'datamin'=>$min,
				'datamax'=>$max,
			);
		}

		echo json_encode(
			array(
				'status' => $stts,
				'data'=>$dataAnalisa
			)
		);
	}


	function infov2() {
		$skr2 = date('Y-m-d H:i',mktime(0,0,0,date('m'),date('d')-1,date('Y')));

		$idlogger=$this->input->get('idlogger');
		$data_informasi=array();
		$data_terakhir=array();
		$query = $this->db->query('SELECT * from kategori_logger INNER JOIN t_logger on t_logger.kategori_log = kategori_logger.id_katlogger;');
		foreach($query->result() as $code_l)
		{
			$tabel = $code_l->temp_data;
		}
		$status_sd='OK';
		$query_informasi=$this->db->query('SELECT * FROM t_informasi where logger_id="'.$idlogger.'"');
		foreach($query_informasi->result() as $data)
		{
			$query_logger=$this->db->query('SELECT * FROM t_logger where id_logger="'.$idlogger.'"');
			foreach($query_logger->result() as $logger)
			{
				$query_kategori=$this->db->query('SELECT * FROM kategori_logger where id_katlogger="'.$logger->kategori_log.'"');
				foreach($query_kategori->result() as $kategori)
				{
					$query_ceksd=$this->db->query('SELECT sensor13,sensor12 FROM '.$kategori->temp_data.' where code_logger="'.$idlogger.'" order by waktu desc limit 1');
					foreach($query_ceksd->result() as $ceksd)
					{
						if($ceksd->sensor13 == '1')
						{
							$status_sd='OK';
						}
						else{
							$status_sd='Terjadi Kesalahan';
						}

						if($ceksd->sensor12 == '1')
						{
							$status_sensor='OK';
						}
						else{
							$status_sensor='Terjadi Kesalahan';
						}
					}

				}
			}

			if (empty($data->elevasi)) {
				$data_informasi=array(
					array(
						'nama'=>'ID Logger','nilai'=>$data->logger_id),
					array('nama'=>
						  'Seri', 'nilai'=>$data->seri_logger),
					array('nama'=>
						  'Serial Number', 'nilai'=>$data->serial_number),
					array('nama'=>
						  'Sensor','nilai'=>$data->sensor),
					array('nama'=>
						  'Status SD','nilai'=>$status_sd),
					array('nama'=>
						  'Awal Kontrak','nilai'=>$data->awal_kontrak),
					array('nama'=>
						  'Akhir Garansi','nilai'=>$data->garansi),
					array('nama'=>
						  'Logger Aktif','nilai'=>$data->tanggal_pemasangan),
					array('nama'=>
						  'No Seluler','nilai'=>$data->nosell),
					array('nama'=>
						  'IMEI','nilai'=>$data->imei),

					array('nama'=>
						  'Nama PIC','nilai'=>$data->nama_pic),
					array('nama'=>
						  'No PIC','nilai'=>$data->no_pic),
				);
			}else {
				$data_informasi=array(
					array(
						'nama'=>'ID Logger','nilai'=>$data->logger_id),
					array('nama'=>
						  'Seri', 'nilai'=>$data->seri_logger),
					array('nama'=>
						  'Serial Number', 'nilai'=>$data->serial_number),
					array('nama'=>'Sensor','nilai'=>$data->sensor),
					array('nama'=>
						  'Status SD','nilai'=>$status_sd),
					array('nama'=>
						  'Awal Kontrak','nilai'=>$data->awal_kontrak),
					array('nama'=>
						  'Akhir Garansi','nilai'=>$data->garansi),
					array('nama'=>
						  'Logger Aktif','nilai'=>$data->tanggal_pemasangan),
					array('nama'=>'Elevasi','nilai'=>$data->elevasi),
					array('nama'=>
						  'No Seluler','nilai'=>$data->nosell),
					array('nama'=>
						  'IMEI','nilai'=>$data->imei),
					array('nama'=>
						  'Nama PIC','nilai'=>$data->nama_pic),
					array('nama'=>
						  'No PIC','nilai'=>$data->no_pic),
				);
			}

		}




		$data_terakhir=array(
			'data'=>$data_informasi,
			//'elevasi'=>$data->elevasi
		);

		echo json_encode($data_terakhir);


	}

}
