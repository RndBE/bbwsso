
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Analisa extends CI_Controller {

	function __construct() {
		parent::__construct();

		$this->load->model('m_analisa');
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

	public function pilihpos()
	{
		$data = array();
		if($this->session->userdata('id_user') =='2'){
			$q_pos = $this->db->query("SELECT * FROM t_logger INNER JOIN t_lokasi ON t_logger.lokasi_logger = t_lokasi.idlokasi where user_id = 2 order by id_logger asc");
		}else{
			$q_pos = $this->db->query("SELECT * FROM t_logger INNER JOIN t_lokasi ON t_logger.lokasi_logger = t_lokasi.idlokasi  order by id_logger asc");
		}
		foreach ($q_pos->result() as $pos) {
			$data[] = array(
				'idLogger' => $pos->id_logger.'_'.'bbws', 'namaPos' => $pos->nama_lokasi
			);
		}
		$data_bbws = json_encode($data);
		$data_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/api_pilihpos'));
		
		$data_pos = (array_merge(json_decode($data_bbws),$data_psda));
		
		usort($data_pos, function ($a, $b) {
			
			return strcmp($a->idLogger, $b->idLogger);
		});
		$pos_all = json_encode($data_pos);
		return json_decode($pos_all);
		
	}

	public function pilihparameter($idlogger){
		$data = array();
		$q_parameter = $this->db->query("SELECT * FROM parameter_sensor where logger_id='" . $idlogger . "' ORDER BY CAST(SUBSTRING(kolom_sensor,7) AS UNSIGNED)");
		foreach ($q_parameter->result() as $param) {
			$data[] = array(
				'idParameter' => $param->id_param, 'namaParameter' => $param->nama_parameter, 'fieldParameter' => $param->kolom_sensor
			);
		}
		$data_param = json_encode($data);
		return json_decode($data_param);
	}


	function set_sensordash()
	{
		$idparam = $this->input->get('id_param');
		$id_param_only = explode('_',$idparam)[0];
		$aset = explode('_',$idparam)[1];
		if($aset == 'bbws'){
			$param_data = $this->db->join('t_logger', 't_logger.id_logger = parameter_sensor.logger_id')->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')->where('parameter_sensor.id_param', $idparam)->get('parameter_sensor')->row();

			$params = [
				'idlogger'       => $param_data->logger_id . '_'.$aset,
				'id_param'       => $id_param_only,
				'data'           => 'hari',
				'pada'           => date('Y-m-d'),
				'dari'           => '2025-09-01 00:00:00',
				'sampai'         => '2025-09-16 23:59:59'
			];
		}else{
			$param_data = $this->db->join('t_logger', 't_logger.id_logger = parameter_sensor.logger_id')->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')->where('parameter_sensor.id_param', $idparam)->get('parameter_sensor')->row();

			$params = [
				'idlogger'       => $this->input->get('id_logger'). '_'.$aset,
				'id_param'       => $id_param_only,
				'data'           => 'hari',
				'pada'           => date('Y-m-d'),
				'dari'           => '2025-09-01 00:00:00',
				'sampai'         => '2025-09-16 23:59:59'
			];
		}
		
		$enc = $this->encrypt_param($params);
		redirect("analisa/data/$enc");
	}

	private function _param_belongs_to_logger($id_param, $id_logger) {
		$logger_id = explode('_',$id_logger)[0];
		$aset = explode('_',$id_logger)[1];
		
		if($aset == 'bbws'){
			return (bool) $this->db->where('id_param', $id_param)
				->where('logger_id', $logger_id)
				->count_all_results('parameter_sensor');
		}else{
			$hasil = file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/param_belongs_to_logger?id_param='.$id_param.'&id_logger='.$logger_id);
			return $hasil;
		}
	
	}

	private function _get_default_param_for_logger($id_logger) {
		$logger_id = explode('_',$id_logger)[0];
		$aset = explode('_',$id_logger)[1];
		if($aset == 'bbws'){
			$row = $this->db->where('logger_id', $logger_id)
				->order_by('id_param', 'ASC')   
				->limit(1)
				->get('parameter_sensor')
				->row();
			return $row ? $row->id_param : null;
		}else{
			$hasil = file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/get_default_param_for_logger/'.$logger_id);
			return $hasil;
		}
		
	}

	function encrypt_param($array) {
		$key = "gantengbanget";
		$alias = [
			'l' => $array['idlogger'] ?? '',
			'p' => $array['id_param'] ?? '',
			'm' => $array['data'] ?? '',
			
			't' => $array['pada'] ?? '',
			'f' => $array['dari'] ?? '',
			's' => $array['sampai'] ?? ''
		];
		$json = json_encode($alias, JSON_UNESCAPED_SLASHES);
		$compressed = gzdeflate($json, 9);
		$encrypted = openssl_encrypt($compressed, "AES-128-ECB", $key, OPENSSL_RAW_DATA);
		return rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
	}

	function decrypt_param($string) {
		$key = "gantengbanget";
		$bin  = base64_decode(strtr($string, '-_', '+/'));
		$plain = openssl_decrypt($bin, "AES-128-ECB", $key, OPENSSL_RAW_DATA);
		$json = @gzinflate($plain);
		$alias = json_decode($json, true);
		if (!$alias) return null;
		return [
			'idlogger' => $alias['l'] ?? null,
			'id_param' => $alias['p'] ?? null,
			'data'     => $alias['m'] ?? null,
			'pada'     => $alias['t'] ?? null,
			'dari'     => $alias['f'] ?? null,
			'sampai'   => $alias['s'] ?? null
		];
	}

	public function set_token () {
		$current = [
			'idlogger' => null,
			'id_param' => null,
			'data'     => 'hari',
			'pada'     => date('Y-m-d'),
			'dari'     => null,
			'sampai'   => null,
		];

		$token = $this->input->post('token', true);
		if ($token) {
			$dec = $this->decrypt_param(str_replace(' ', '+', rawurldecode($token)));
			if (is_array($dec)) $current = array_merge($current, $dec);

		}
		
		$gabungan = explode('_', $this->input->post('id_logger', true));
		$id_logger_new = $this->input->post('id_logger', true);
		
		$id_param_new  = $this->input->post('id_param', true);
		$mode      = $this->input->post('mode', true);     
		$pada      = $this->input->post('pada', true);     
		$dari      = $this->input->post('dari', true);      
		$sampai    = $this->input->post('sampai', true);    
		if ($id_logger_new !== null && $id_logger_new !== '' && $id_logger_new !== $current['idlogger']) {
			$current['idlogger'] = $id_logger_new;
			$aset = $gabungan[1];
			if ($id_param_new) {
				if ($this->_param_belongs_to_logger($id_param_new, $id_logger_new)) {
					$current['id_param'] = $id_param_new;
				} else {
					$current['id_param'] = $this->_get_default_param_for_logger($id_logger_new);
				}
			} else {
				$current['id_param'] = $this->_get_default_param_for_logger($id_logger_new);
			}
			
			if (!$current['id_param']) {
				show_error('Logger terpilih belum memiliki parameter sensor.', 400);
				return;
			}
		}
		elseif ($id_param_new) {
			$logger_for_check = $current['idlogger'];
			if ($logger_for_check && $this->_param_belongs_to_logger($id_param_new, $logger_for_check)) {
				$current['id_param'] = $id_param_new;
			} else {
				$fallback = $this->_get_default_param_for_logger($logger_for_check);
				if ($fallback) $current['id_param'] = $fallback;
			}
		}
		if ($mode) {
			switch ($mode) {
				case 'hari':
					$current['data'] = 'hari';
					$current['pada'] = date('Y-m-d');
					$current['dari'] = $current['sampai'] = null;
					break;

				case 'bulan':
					$current['data'] = 'bulan';
					$current['pada'] = date('Y-m');
					$current['dari'] = $current['sampai'] = null;
					break;

				case 'tahun':
					$current['data'] = 'tahun';
					$current['pada'] = date('Y');
					$current['dari'] = $current['sampai'] = null;
					break;

				case 'range':
					$current['data'] = 'range';
					if ($dari && $sampai) {
						$current['dari']   = $dari;
						$current['sampai'] = $sampai;
					} else {
						$current['dari']   = date('Y-m-d H:i', mktime(date('H'), 0, 0, date('m'), date('d') - 1, date('Y')));
						$current['sampai'] = date('Y-m-d H:i', mktime(date('H'), 0, 0, date('m'), date('d'),     date('Y')));
					}

					$current['pada'] = null;
					break;
			}
		}
		elseif ($pada) {
			$current['pada'] = $pada;
			$current['dari'] = $current['sampai'] = null;
		}
		elseif ($dari || $sampai) {
			$current['data'] = 'range';
			$current['dari'] = $dari ?: $current['dari'];
			$current['sampai'] = $sampai ?: $current['sampai'];
			$current['pada'] = null;
		}
		$enc = $this->encrypt_param($current);
		redirect('analisa/data/'.$enc);
	}

	public function data($string) {
		$dec = $this->decrypt_param($string);

		if (!$dec) show_error('Token tidak valid');
		$gabungan = isset($dec['idlogger']) ? explode('_', trim($dec['idlogger'])) : null;
		
		$idLogger = $gabungan[0];
		$aset = $gabungan[1];
		
		$idParam  = isset($dec['id_param']) ? trim($dec['id_param']) : null;
		$modeData = isset($dec['data']) ? trim($dec['data']) : null;
		$pada     = isset($dec['pada']) ? trim($dec['pada']) : null;
		$dari     = isset($dec['dari']) ? trim($dec['dari']) : null;
		$sampai   = isset($dec['sampai']) ? trim($dec['sampai']) : null;
		$payload = [];
		if (!$idLogger) show_error('Parameter tidak lengkap');
	
		if($aset == 'bbws'){
			$dataParam = $this->db->where('id_param', $idParam)->get('parameter_sensor')->row();
			if (!$dataParam) show_error('Parameter sensor tidak ditemukan');

			$tipeGrafik    = $dataParam->tipe_graf;
			$kolom         = $dataParam->kolom_sensor;
			$namaParameter = $dataParam->nama_parameter;
			$satuan        = $dataParam->satuan;

			$tb_main = $this->db
				->join('t_informasi','t_informasi.logger_id = t_logger.id_logger')
				->join('t_lokasi','t_lokasi.idlokasi = t_logger.lokasi_logger')
				->join('kategori_logger','kategori_logger.id_katlogger = t_logger.kategori_log')
				->where('id_logger', $idLogger)
				->get('t_logger')->row();
			if (!$tb_main) show_error('Logger tidak ditemukan');

			$foto_pos   = $this->db->where('id_logger', $idLogger)->get('foto_pos')->result_array();
			$riwayat_op = $this->db->where('id_logger', $idLogger)->get('t_riwayat')->result_array();

			$data = []; $data_tabel = []; $range = [];

			$nama_sensor = ($tipeGrafik === 'column') ? ('Akumulasi_' . $namaParameter) : ('Rerata_' . $namaParameter);
			$selectAgg   = ($tipeGrafik === 'column') ? "SUM($kolom) AS $nama_sensor" : "AVG($kolom) AS $nama_sensor";

			if ($modeData === 'hari') {
				$start  = $pada . ' 00:00:00';
				$end    = $pada . ' 23:59:59';
				$select = "avg(sensor2) as tma,max(sensor2) as tma_max,min(sensor2) as tma_min,AVG(sensor1 - sensor2) AS avg_diff, MIN(sensor1 - sensor2) AS min_diff, MAX(sensor1 - sensor2) AS max_diff, waktu, HOUR(waktu) AS jam, DAY(waktu) AS hari, MONTH(waktu) AS bulan, YEAR(waktu) AS tahun, $selectAgg, MIN($kolom) AS min, MAX($kolom) AS max";
				$group  = "YEAR(waktu), MONTH(waktu), DAY(waktu), HOUR(waktu)";
				$order  = "tahun ASC, bulan ASC, hari ASC, jam ASC";
				$fmtTbl = function($r,$h,$min,$max){ return [
					'waktu' => date('H', strtotime($r->waktu)) . ':00:00',
					'dta'   => number_format($h, 2, '.', ''),
					'min'   => number_format($min, 2, '.', ''),
					'max'   => number_format($max, 2, '.', '')
				];};
				$fmtPoint = function($r,$val){ return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari,$r->jam)," . number_format($val, 3, '.', '') . "]"; };
				$fmtRange = function($r,$min,$max){ return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari,$r->jam)," . $min . "," . $max . "]"; };
				$tooltip  = "Waktu %d-%m-%Y %H:%M";
			} elseif ($modeData === 'bulan') {
				$start  = $pada . '-01 00:00:00';
				$end    = date('Y-m-t 23:59:59', strtotime($pada . '-01'));
				$select = "avg(sensor2) as tma,max(sensor2) as tma_max,min(sensor2) as tma_min,AVG(sensor1 - sensor2) AS avg_diff, MIN(sensor1 - sensor2) AS min_diff, MAX(sensor1 - sensor2) AS max_diff, waktu, DATE(waktu) AS tanggal, DAY(waktu) AS hari, MONTH(waktu) AS bulan, YEAR(waktu) AS tahun, $selectAgg, MIN($kolom) AS min, MAX($kolom) AS max";
				$group  = "YEAR(waktu), MONTH(waktu), DAY(waktu)";
				$order  = "tahun ASC, bulan ASC, hari ASC";
				$fmtTbl = function($r,$h,$min,$max){ return [
					'waktu' => date('Y-m-d', strtotime($r->waktu)),
					'dta'   => number_format($h, 2, '.', ''),
					'min'   => number_format($min, 2, '.', ''),
					'max'   => number_format($max, 2, '.', '')
				];};
				$fmtPoint = function($r,$val){ return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari)," . number_format($val, 3, '.', '') . "]"; };
				$fmtRange = function($r,$min,$max){ return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari)," . $min . "," . $max . "]"; };
				$tooltip  = "Tanggal %d-%m-%Y";
			} elseif ($modeData === 'tahun') {
				$start  = $pada . '-01-01 00:00:00';
				$end    = $pada . '-12-31 23:59:59';
				$select = "avg(sensor2) as tma,max(sensor2) as tma_max,min(sensor2) as tma_min,AVG(sensor1 - sensor2) AS avg_diff, MIN(sensor1 - sensor2) AS min_diff, MAX(sensor1 - sensor2) AS max_diff, DATE(waktu) AS tanggal, MONTH(waktu) AS bulan, YEAR(waktu) AS tahun, $selectAgg, MIN($kolom) AS min, MAX($kolom) AS max";
				$group  = "YEAR(waktu), MONTH(waktu)";
				$order  = "tahun ASC, bulan ASC";
				$fmtTbl = function($r,$h,$min,$max){ return [
					'waktu' => date('Y-m', strtotime($r->tanggal)),
					'dta'   => number_format($h, 2, '.', ''),
					'min'   => number_format($min, 2, '.', ''),
					'max'   => number_format($max, 2, '.', '')
				];};
				$fmtPoint = function($r,$val){ return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ")," . number_format($val, 3, '.', '') . "]"; };
				$fmtRange = function($r,$min,$max){ return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ")," . $min . "," . $max . "]"; };
				$tooltip  = "Tanggal %d-%m-%Y";
			} else {
				$start  = $dari;
				$end    = $sampai;
				$select = "avg(sensor2) as tma,max(sensor2) as tma_max,min(sensor2) as tma_min,AVG(sensor1 - sensor2) AS avg_diff, MIN(sensor1 - sensor2) AS min_diff, MAX(sensor1 - sensor2) AS max_diff, waktu, DATE(waktu) AS tanggal, HOUR(waktu) AS jam, DAY(waktu) AS hari, MONTH(waktu) AS bulan, YEAR(waktu) AS tahun, $selectAgg, MIN($kolom) AS min, MAX($kolom) AS max";
				$group  = "YEAR(waktu), MONTH(waktu), DAY(waktu), HOUR(waktu)";
				$order  = "tahun ASC, bulan ASC, hari ASC, jam ASC";
				$fmtTbl = function($r,$h,$min,$max){ return [
					'waktu' => date('Y-m-d H', strtotime($r->waktu)) . ':00:00',
					'dta'   => number_format($h, 3, '.', ''),
					'min'   => number_format($min, 2, '.', ''),
					'max'   => number_format($max, 2, '.', '')
				];};
				$fmtPoint = function($r,$val){ return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari,$r->jam)," . number_format($val, 3, '.', '') . "]"; };
				$fmtRange = function($r,$min,$max){ return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari,$r->jam)," . $min . "," . $max . "]"; };
				$tooltip  = "Waktu %d-%m-%Y %H:%M";
			}

			$sql = "SELECT $select FROM {$tb_main->tabel_main} WHERE code_logger=? AND waktu BETWEEN ? AND ? GROUP BY $group ORDER BY $order";
			$q   = $this->db->query($sql, [$idLogger, $start, $end]);
			$akumulasi_hujan = 0;
			foreach ($q->result() as $r) {
				$h        = $r->$nama_sensor;
				$min_data = $r->min;
				$max_data = $r->max;

				if ($namaParameter === 'Debit' && $idLogger === '10063') {
					if ($h < 0 || $min_data < 0 || $max_data < 0) {
						$h        = max(0, $h);
						$min_data = max(0, $min_data);
						$max_data = max(0, $max_data);
					} else {
						$h        = $this->kalimeneng($r->$nama_sensor);
						$min_data = $this->kalimeneng($r->min);
						$max_data = $this->kalimeneng($r->max);
					}
				} elseif ($namaParameter === 'Debit_Aliran_Sungai') {
					$avg_debit = $this->debit_interpolation(($r->avg_diff));
					$min_debit = $this->debit_interpolation(($r->min_diff));
					$max_debit = $this->debit_interpolation(($r->max_diff));
					$h        = $avg_debit;
					$min_data = $min_debit;
					$max_data = $max_debit;
				}elseif ($namaParameter === 'Debit' and $idLogger === '10249') {
					$tma = $r->tma * 100;
					$tma_min = $r->tma_min * 100;
					$tma_max = $r->tma_max * 100;
					$avg_debit = $this->linear_interpolation($tma) * $h;
					$min_debit = $this->linear_interpolation($tma_min)* $min_data;
					$max_debit = $this->linear_interpolation($tma_max)* $max_data;
					$h        = $avg_debit;
					$min_data = $min_debit;
					$max_data = $max_debit;
				}elseif ($namaParameter === 'Luas_Penampang_Basah' and $idLogger === '10249') {
					$tma = $r->tma * 100;
					$tma_min = $r->tma_min * 100;
					$tma_max = $r->tma_max * 100;
					$avg_debit = $this->linear_interpolation($tma);
					$min_debit = $this->linear_interpolation($tma_min);
					$max_debit = $this->linear_interpolation($tma_max);
					$h        = $avg_debit;
					$min_data = $min_debit;
					$max_data = $max_debit;
				}
				if($tipeGrafik == 'column'){
					$akumulasi_hujan += $h;
				}
				$data[]       = $fmtPoint($r, $h);
				$range[]      = $fmtRange($r, $min_data, $max_data);
				$data_tabel[] = $fmtTbl($r, $h, $min_data, $max_data);
			}

			$dataAnalisa = [
				'idParam'     => $idParam,
				'idLogger'    => $idLogger,
				'namaSensor'  => $nama_sensor,
				'satuan'      => $satuan,
				'tipe_grafik' => $tipeGrafik,
				'data'        => $data,
				'data_tabel'  => $data_tabel,
				'nosensor'    => $kolom,
				'range'       => $range,
				'tooltip'     => $tooltip,
				'tooltipper'  => $tooltip,
				'mode_data'   => $modeData,
				'pada'        => $pada,
				'dari'        => $dari,
				'sampai'      => $sampai,
				'akumulasi_hujan'      => $akumulasi_hujan,
			];

			$qstatus = $this->db->where('code_logger',$idLogger)->get($tb_main->temp_data)->row();
			$awal    = date('Y-m-d H:i', (mktime(date('H')-1)));
			$waktu   = $qstatus->waktu ?? null;

			if ($waktu && $waktu >= $awal) {
				$color = "green";
				$status_logger = "Koneksi Terhubung";
			} else {
				$color = "dark";
				$status_logger = "Koneksi Terputus";
			}

			$perbaikan = $this->db->get_where('t_perbaikan', ['id_logger'=> $idLogger])->row();
			if ($perbaikan) {
				$stts = '1';
				$status_logger = "Perbaikan";
			} else {
				$stts = '0';
			}
			
			$payload['informasi']        = $tb_main;
			
			$payload['data_sensor']      = json_decode(json_encode($dataAnalisa));
			$payload['data_op']          = $riwayat_op;
			$payload['foto_pos']         = $foto_pos ?: [];
			$payload['pilih_pos']        = $this->pilihpos();
			$payload['pilih_parameter']  = $this->pilihparameter($idLogger);
			
			$payload['temp_data'] = [
				'nama_lokasi'   => $tb_main->nama_lokasi,
				'color'         => $color,
				'status_logger' => $status_logger,
				'stts'          => $stts,
			];
		}else{
			if ($modeData === 'hari') {
				$data_psda= json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/analisapertanggal2?idsensor='.$idParam.'&tanggal='.$pada));
			}elseif($modeData === 'bulan'){
				$data_psda= json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/analisaperbulan2?idsensor='.$idParam.'&tanggal='.$pada));
			}elseif($modeData === 'tahun'){
				$data_psda= json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/analisapertahun2?idsensor='.$idParam.'&tahun='.$pada));
			}elseif($modeData === 'range'){
				$dari = urlencode($dari);
				$sampai = urlencode($sampai);
				$data_psda= json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/analisaperrange2?idsensor='.$idParam.'&dari='.$dari.'&sampai='.$sampai));
				
			}
			$payload['informasi']        = $data_psda->informasi;
			$payload['data_sensor']      = json_decode(json_encode($data_psda->data_sensor));
			$payload['data_op']          = [];
			$payload['foto_pos']         = [];
			$payload['pilih_pos']        = $this->pilihpos();
			$payload['pilih_parameter']  = $data_psda->pilih_parameter;
			
			$payload['temp_data'] = json_decode(json_encode($data_psda->temp_data), true);
		}
		$payload['token']        = $string;
		$payload['konten']           = 'konten/back/analisa_all';

		$this->load->view('template_admin/site', $payload);
	} 


	public function index()
	{
		if($this->session->userdata('logged_in'))
		{
			$this->load->library('googlemaps');
			$id_kategori = $this->session->userdata('id_kategori');
			$ktg = $this->db->get('kategori_logger')->result_array();

			$data['ktg_all'] = $this->db->get('kategori_logger')->result_array();
			$das = $this->db->get('list_das')->result_array();
			foreach($das as $key =>$ds){
				$das[$key]['logger'] = [];
				$data_logger = $this->db->join('kategori_logger', 't_logger.kategori_log = kategori_logger.id_katlogger')->join('t_lokasi','t_lokasi.idlokasi = t_logger.lokasi_logger')->where('t_lokasi.das',$ds['nama_das'])->order_by('id_logger')->get('t_logger')->result_array();

				foreach ($data_logger as $k=>$log){
					$tabel=$log['temp_data'];
					$id_logger=$log['id_logger'];
					$temp_data = $this->db->where('code_logger',$id_logger)->get($tabel)->row();
					$cek_perbaikan = $this->db->where('id_logger',$id_logger)->get('t_perbaikan')->row();

					$awal=date('Y-m-d H:i',(mktime(date('H')-1)));
					if($temp_data->waktu >= $awal)
					{
						$color="green";
						$status_logger="Koneksi Terhubung";
					}
					else{
						$color="red";
						$status_logger="Koneksi Terputus";			
					}
					if($cek_perbaikan){
						$color="#A16D28";
						$status_logger="Perbaikan";	
					}
					if($temp_data->sensor13 == '1' )
					{
						$sdcard='OK';
					}
					else{
						$sdcard='Bermasalah';
					}


					$param = $this->db->query("SELECT * FROM `parameter_sensor` WHERE logger_id = '$id_logger' ORDER BY CAST(SUBSTR(`kolom_sensor`,7) AS UNSIGNED)")->result_array();
					foreach($param as $ky => $val) {
						$get='id_param='.$val['id_param'].'_bbws';
						$kolom = $val['kolom_sensor'];
						$param[$ky]['nilai'] = $temp_data->$kolom;
						$param[$ky]['link'] = base_url() .'analisa/set_sensordash?'.$get;
					}

					$das[$key]['logger'][$k] = [
						'id_logger'=>$id_logger,
						'nama_lokasi'=>$log['nama_lokasi'],
						'waktu'=>$temp_data->waktu,
						'color'=>$color,
						'status_logger'=>$status_logger,
						'status_sd'=>$sdcard,
						'param'=>$param,
					];
				}
			}

			$kategori=array();
			$query_kategori=$this->db->query('select * from kategori_logger');
			//$klasifikasi
			$marker = [];
			foreach ($query_kategori->result()  as $kat) {
				$tabel=$kat->tabel;
				$tabel_temp=$kat->temp_data;
				$content=array();

				$query_lokasilogger=$this->db->query("select * from t_logger inner join t_lokasi ON t_logger.lokasi_logger=t_lokasi.idlokasi join kategori_logger on t_logger.kategori_log = kategori_logger.id_katlogger join t_informasi on t_logger.id_logger = t_informasi.logger_id where kategori_log='$kat->id_katlogger' and t_lokasi.das != ''");
				foreach ($query_lokasilogger->result() as $loklogger){
					$tabel_main = $loklogger->tabel_main;
					$id_logger=$loklogger->id_logger;
					$icon = $loklogger->tabel;
					$parameter=array();
					$id_param = $this->db->where('logger_id',$id_logger)->where('parameter_utama','1')->limit(1)->get('parameter_sensor')->row();
					
					$query_data=$this->db->query('select * from '.$tabel_temp.' where code_logger="'.$id_logger.'"')->result();


					foreach ($query_data as $dt){
						$waktu=$dt->waktu;
						$awal=date('Y-m-d H:i',(mktime(date('H')-1)));

						if ($icon == 'awlr') {
							$controller = $kat->controller;
							$kat_group = 'awlr';
							$data_p = $dt->sensor1;
							$perb = $this->db->where('id_logger', $id_logger)->get('t_perbaikan')->row();
							if ($perb) {
								$icon_marker = base_url() . 'pin_marker/awlr-iri-coklat.png';
								$status = '<p style="color:brown;margin-bottom:0px">Perbaikan</p>';
								$statlog = 'Perbaikan';
								$statuspantau = "Perbaikan";
								$anim = "";
							} else {
								if ($waktu >= $awal) {
									$icon_marker = base_url() . 'pin_marker/awlr-iri-hijau.png';
									$status = '<p style="color:green;margin-bottom:0px">Koneksi Terhubung</p>';
									$statlog = 'Koneksi Terhubung';
									$statuspantau = "Koneksi Terhubung";
									$anim = "";
								} else {
									$icon_marker = base_url() . 'pin_marker/awlr-iri-hitam.png';
									$status = '<p style="color:red;margin-bottom:0px">Koneksi Terputus</p>';
									$statlog = 'Koneksi Terputus';
									$statuspantau = "Koneksi Terputus";
									$anim = "google.maps.Animation.BOUNCE";
								}
							}
						} else if ($icon == 'arr') {
							$controller = 'arr';
							$kat_group = 'arr';
							$sen = $this->db->where('kolom_sensor', 'sensor9')->where('logger_id', $id_logger)->get('parameter_sensor')->row();
							$perb = $this->db->where('id_logger', $id_logger)->get('t_perbaikan')->row();
							if ($sen) {
								$query_akumulasi = $this->db->query('select sum(sensor9) as sensor9 from '.$tabel_main.' where code_logger = "' . $id_logger . '" and waktu >= "' . date('Y-m-d H') . ':00" ')->row();
								$data_p = $query_akumulasi->sensor9;
							} else {
								$query_akumulasi = $this->db->query('select sum(sensor8) as sensor8 from '.$tabel_main.' where code_logger = "' . $id_logger . '" and waktu >= "' . date('Y-m-d H') . ':00" ')->row();
								$data_p = $query_akumulasi->sensor8;
							}

							if ($perb) {
								$icon_marker = base_url() . 'pin_marker/arr_coklat.png';
								$status = '<p style="color:green;margin-bottom:0px">Koneksi Terhubung</p>';
								$statlog = 'Perbaikan';
								$statuspantau = "Perbaikan";
								$anim = "";
							} else {
								if ($waktu >= $awal) {
									if ($data_p <= 0) {
										$icon_marker = base_url() . 'pin_marker/arr_hijau.png';
										$status = '<p style="color:green;margin-bottom:0px">Koneksi Terhubung</p>';
										$statlog = 'th';
										$statuspantau = "Tidak Hujan";
										$anim = "";
									} elseif ($data_p >= 0.1 and $data_p < 1) {
										$icon_marker = base_url() . 'pin_marker/arr_biru.png';
										$status = '<p style="color:green;margin-bottom:0px">Koneksi Terhubung</p>';
										$statlog = 'sr';
										$statuspantau = "Hujan Sangat Ringan";
										$anim = "";
									} elseif ($data_p >= 1 and $data_p < 5) {
										$icon_marker = base_url() . 'pin_marker/arr_nila.png';
										$status = '<p style="color:green;margin-bottom:0px">Koneksi Terhubung</p>';
										$statlog = 'r';
										$statuspantau = "Hujan Ringan";
										$anim = "";
									} elseif ($data_p >= 5 and $data_p < 10) {
										$icon_marker = base_url() . 'pin_marker/arr_kuning.png';
										$status = '<p style="color:green;margin-bottom:0px">Koneksi Terhubung</p>';
										$statlog = 's';
										$statuspantau = "Hujan Sedang";
										$anim = "";
									} elseif ($data_p >= 10 and $data_p < 20) {
										$icon_marker = base_url() . 'pin_marker/arr_oranye.png';
										$status = '<p style="color:green;margin-bottom:0px">Koneksi Terhubung</p>';
										$statlog = 'l';
										$statuspantau = "Hujan Lebat";
										$anim = "google.maps.Animation.BOUNCE";
									} elseif ($data_p >= 20) {
										$icon_marker = base_url() . 'pin_marker/arr_merah.png';
										$status = '<p style="color:green;margin-bottom:0px">Koneksi Terhubung</p>';
										$statlog = 'sl';
										$statuspantau = "Hujan Sangat Lebat";
										$anim = "google.maps.Animation.BOUNCE";
									}
									$statlog = 'Koneksi Terhubung';
								} else {
									$icon_marker = base_url() . 'pin_marker/arr_hitam.png';
									$status = '<p style="color:red;margin-bottom:0px">Koneksi Terputus</p>';
									$statlog = 'Koneksi Terputus';
									$statuspantau = "Koneksi Terputus";
									$anim = "google.maps.Animation.BOUNCE";
								}
							}
						} else {
							$controller = $kat->controller;
							$kat_group = 'awr';
							$sen = $this->db->where('kolom_sensor', 'sensor9')->where('logger_id', $id_logger)->get('parameter_sensor')->row();
							$perb = $this->db->where('id_logger', $id_logger)->get('t_perbaikan')->row();
							if ($sen) {
								$query_akumulasi = $this->db->query('select sum(sensor9) as sensor9 from '.$tabel_main.' where code_logger = "' . $id_logger . '" and waktu >= "' . date('Y-m-d H') . ':00" ')->row();
								$data_p = $query_akumulasi->sensor9;
							} else {
								$query_akumulasi = $this->db->query('select sum(sensor8) as sensor8 from '.$tabel_main.' where code_logger = "' . $id_logger . '" and waktu >= "' . date('Y-m-d H') . ':00" ')->row();
								$data_p = $query_akumulasi->sensor8;
							}
							if ($perb) {
								$icon_marker = base_url() . 'pin_marker/awr_coklat.png';
								$status = '<p style="color:brown;margin-bottom:0px">Koneksi Terputus</p>';
								$statlog = 'Perbaikan';
								$statuspantau = "Perbaikan";
								$anim = "";
							} else {
								if ($waktu >= $awal) {
									if ($data_p >= 0 and $data_p < 0.1) {
										$icon_marker = base_url() . 'pin_marker/awr_hijau.png';
										$status = '<p style="color:green;margin-bottom:0px">Koneksi Terhubung</p>';
										$statlog = 'Koneksi Terhubung';
										$statuspantau = "Tidak Hujan";
										$anim = "";
									} elseif ($data_p >= 0.1 and $data_p < 1) {
										$icon_marker = base_url() . 'pin_marker/awr_biru.png';
										$status = '<p style="color:green;margin-bottom:0px">Koneksi Terhubung</p>';
										$statlog = 'Koneksi Terhubung';
										$statuspantau = "Hujan Sangat Ringan";
										$anim = "";
									} elseif ($data_p >= 1 and $data_p < 5) {
										$icon_marker = base_url() . 'pin_marker/awr_nila.png';
										$status = '<p style="color:green;margin-bottom:0px">Koneksi Terhubung</p>';
										$statlog = 'Koneksi Terhubung';
										$statuspantau = "Hujan Ringan";
										$anim = "";
									} elseif ($data_p >= 5 and $data_p < 10) {
										$icon_marker = base_url() . 'pin_marker/awr_kuning.png';
										$status = '<p style="color:green;margin-bottom:0px">Koneksi Terhubung</p>';
										$statlog = 'Koneksi Terhubung';
										$statuspantau = "Hujan Sedang";
										$anim = "";
									} elseif ($data_p >= 10 and $data_p < 20) {
										$icon_marker = base_url() . 'pin_marker/awr_oranye.png';
										$status = '<p style="color:green;margin-bottom:0px">Koneksi Terhubung</p>';
										$statlog = 'Koneksi Terhubung';
										$statuspantau = "Hujan Lebat";
										$anim = "google.maps.Animation.BOUNCE";
									} elseif ($data_p >= 20) {
										$icon_marker = base_url() . 'pin_marker/awr_merah.png';
										$status = '<p style="color:green;margin-bottom:0px">Koneksi Terhubung</p>';
										$statlog = 'Koneksi Terhubung';
										$statuspantau = "Hujan Sangat Lebat";
										$anim = "google.maps.Animation.BOUNCE";
									}
								} else {
									$icon_marker = base_url() . 'pin_marker/awr_hitam.png';
									$status = '<p style="color:red;margin-bottom:0px">Koneksi Terputus</p>';
									$statlog = 'Koneksi Terputus';
									$statuspantau = "Koneksi Terputus";
									$anim = "google.maps.Animation.BOUNCE";
								}
							}
						}
						$status_sd = 'OK';

					}
					$get='id_param='.$id_param->id_param.'_bbws';
					$link =  base_url() .'analisa/set_sensordash?'.$get;
					$url = $this->db->where('id_logger',$id_logger)->get('foto_pos')->row();
					$img_pos = '';
					if($url){
						$img_pos = '<div class="d-flex w-100 justify-content-center mb-2 mt-3"><div style="background:url(https://bbws.beacontelemetry.com/image/foto_pos/'.$url->url_foto.');width:300px;height:200px;background-size:cover;background-position:center" class"img-fluid"></div></div>';
					}
					$marker[] = [
						'nama_das'=>$loklogger->das,
						'id_kategori'=>$kat->id_katlogger,
						'id_logger'=>$loklogger->id_logger,
						'category'=>$kat_group,
						'status_aset'=>'BBWS Serayu Opak',
						'category_group'=>$statuspantau,
						'koneksi'=>$statlog,
						'status_sd'=>$status_sd,
						'latitude' => $loklogger->latitude,
						'longitude' => $loklogger->longitude,
						'nama_lokasi' => $loklogger->nama_lokasi,
						'icon' => $icon_marker,
						'id_param'=>$id_param->id_param,
						'link'=>$link,
						'nama_pic' => $loklogger->nama_pic,
						'no_pic' => $loklogger->no_pic,
						'foto_pos'=>$img_pos,
						'anim'=>$anim
					];
				}
			}
			$das_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/peta_lokasi'),true);
			$data_das = $das_psda['data_konten'];
			$data_marker = $das_psda['marker'];
			
			foreach($das as $ds=>$vd){
				if($vd['nama_das'] == 'Progo'){
					$merger = array_merge($data_das['PROGO']['logger'],$das[$ds]['logger']);
					$das[$ds]['logger'] = $merger;
				}elseif($vd['nama_das'] == 'Opak'){
					$merger = array_merge($data_das['OPAK-OYO']['logger'],$das[$ds]['logger']);
					$das[$ds]['logger'] = $merger;
				}elseif($vd['nama_das'] == 'Serang'){
					$merger = array_merge($data_das['SERANG']['logger'],$das[$ds]['logger']);
					$das[$ds]['logger'] = $merger;
				}
			}
			$data['data_konten']=$das;
			$data['das'] = $das;
			$data['marker'] = array_merge($data_marker,$marker);
			$this->load->view('konten/back/analisa_geojson',$data);
		}
		else
		{
			redirect('login');
		}

	}

	function combologger()
	{
		$set =explode(',',$this->input->post('id_logger'));
		$idlogger=$set[0];
		$controller=$set[1];
		$tabel=$set[2];

		redirect($controller.'/set_sensorselect/'.$idlogger.'/'.$tabel);
	}
}
