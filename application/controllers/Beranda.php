<?php if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Beranda extends CI_Controller
{
	function __construct()
	{
		parent::__construct();

		$this->load->model('m_dashboard');

	}

	function linear_interpolation($x)
	{
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

	function debit_interpolation($x)
	{
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

	function kalimeneng($tmaInput)
	{
		$data = [
			['TMA' => 0.3, 'Cd' => 1.185, 'Bef' => 29.674],
			['TMA' => 0.35, 'Cd' => 1.185, 'Bef' => 29.721],
			['TMA' => 0.4, 'Cd' => 1.185, 'Bef' => 29.708],
			['TMA' => 0.5, 'Cd' => 1.185, 'Bef' => 29.692],
			['TMA' => 0.6, 'Cd' => 1.322, 'Bef' => 29.668],
			['TMA' => 1.0, 'Cd' => 1.322, 'Bef' => 29.608],
			['TMA' => 1.5, 'Cd' => 1.394, 'Bef' => 29.5],
			['TMA' => 2.0, 'Cd' => 1.415, 'Bef' => 29.46],
			['TMA' => 2.5, 'Cd' => 1.414, 'Bef' => 29.26],
			['TMA' => 3.0, 'Cd' => 1.394, 'Bef' => 29.14],
			['TMA' => 3.5, 'Cd' => 1.389, 'Bef' => 29.14],
			['TMA' => 7.5, 'Cd' => 1.389, 'Bef' => 29.02],
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

	public function index()
	{
		if ($this->session->userdata('logged_in')) {
			$isUser2 = ($this->session->userdata('id_user') === '2');
			if ($isUser2) {
				$ktg = $this->db
					->where_in('id_katlogger', ['2', '7'])
					->get('kategori_logger')
					->result_array();
			} else {
				$ktg = $this->db
					->where('view', '1')
					->get('kategori_logger')
					->result_array();
			}
			foreach ($ktg as $key => $kat) {
				$tabelTemp = $kat['temp_data'];
				$idKat = $kat['id_katlogger'];

				$this->db->from('t_logger')
					->join('t_lokasi', 't_logger.lokasi_logger = t_lokasi.idlokasi', 'left')
					->where('kategori_log', $idKat)
					->order_by('id_logger', 'ASC');

				if ($isUser2) {
					$this->db->where('t_logger.user_id', '2');
				} else {
					$this->db->where('t_lokasi.das !=', '');
				}

				$data_logger = $this->db->get()->result_array();

				if (empty($data_logger)) {
					$ktg[$key]['logger'] = [];
					continue;
				}

				$loggerIds = array_column($data_logger, 'id_logger');

				$perbaikanRows = $this->db
					->where_in('id_logger', $loggerIds)
					->get('t_perbaikan')
					->result();
				$mapPerbaikan = [];
				foreach ($perbaikanRows as $r) {
					$mapPerbaikan[$r->id_logger] = true;
				}

				$tempRows = $this->db
					->where_in('code_logger', $loggerIds)
					->get($tabelTemp)
					->result();
				$mapTemp = [];
				foreach ($tempRows as $r) {
					$mapTemp[$r->code_logger] = $r;
				}
				$paramRows = $this->db
					->query("
                SELECT *
                FROM parameter_sensor
                WHERE logger_id IN ?
                ORDER BY CAST(SUBSTRING(kolom_sensor,7) AS UNSIGNED)
            ", [$loggerIds])
					->result_array();

				$mapParams = [];
				foreach ($paramRows as $pr) {
					$lid = $pr['logger_id'];
					if (!isset($mapParams[$lid]))
						$mapParams[$lid] = [];
					$mapParams[$lid][] = $pr;
				}

				$awalTs = time() - 3600; // 1 jam
				$awal = date('Y-m-d H:i', $awalTs);
				$sd15Loggers = ['10247', '10248', '10288', '10249', '10290', '10289', '10345', '10358', '10347', '10346', '10348'];

				$listLogger = [];
				foreach ($data_logger as $log) {
					$idLogger = $log['id_logger'];
					$namaLokasi = $log['nama_lokasi'];

					$temp = isset($mapTemp[$idLogger]) ? $mapTemp[$idLogger] : null;

					$waktu = $temp && !empty($temp->waktu) ? $temp->waktu : null;

					$color = 'black';
					$statusLogger = 'Koneksi Terputus';

					if ($waktu && $waktu >= $awal) {
						$color = '#32b344';
						$statusLogger = 'Koneksi Terhubung';
					}

					if (!empty($mapPerbaikan[$idLogger])) {
						$color = '#7f6226';
						$statusLogger = 'Perbaikan';
					}

					$kolomSd = in_array($idLogger, $sd15Loggers, true) ? 'sensor15' : 'sensor13';
					$sdcard = 'Bermasalah';
					if ($temp && isset($temp->$kolomSd) && $temp->$kolomSd === '1') {
						$sdcard = 'OK';
					}

					$paramList = isset($mapParams[$idLogger]) ? $mapParams[$idLogger] : [];
					foreach ($paramList as $ky => $val) {
						$kolom = $val['kolom_sensor'];
						$h = ($temp && isset($temp->$kolom)) ? $temp->$kolom : null;
						if ($val['debit_awlr'] === '1' && $idLogger === '10063') {
							$debit = $this->kalimeneng((float) $h);
							$paramList[$ky]['nilai'] = number_format(max(0, (float) $debit), 2, '.', '');
						} elseif ($val['nama_parameter'] === 'Debit' && $idLogger === '10249') {
							$n2 = ($temp && isset($temp->sensor2)) ? (float) $temp->sensor2 : 0.0;
							$paramList[$ky]['nilai'] = number_format($this->linear_interpolation($n2 * 100) * (float) $h, 2, '.', '');
						} elseif ($val['nama_parameter'] === 'Luas_Penampang_Basah') {
							$n2 = ($temp && isset($temp->sensor2)) ? (float) $temp->sensor2 : 0.0;
							$paramList[$ky]['nilai'] = number_format($this->linear_interpolation($n2 * 100), 2, '.', '');
						} elseif ($val['nama_parameter'] === 'Debit_Aliran_Sungai') {
							$s1 = ($temp && isset($temp->sensor1)) ? (float) $temp->sensor1 : 0.0;
							$s2 = ($temp && isset($temp->sensor2)) ? (float) $temp->sensor2 : 0.0;
							$n2 = $s1 - $s2;
							if ($s2 > $s1) {
								$paramList[$ky]['nilai'] = number_format(0, 2, '.', '');
							} else {
								$paramList[$ky]['nilai'] = number_format($this->debit_interpolation($n2), 2, '.', '');
							}
						} else {
							$paramList[$ky]['nilai'] = $h;
						}

						$get = http_build_query([
							'id_param' => $val['id_param'] . '_' . 'bbws',
						]);
						$paramList[$ky]['link'] = 'analisa/set_sensordash?' . $get;
					}
					if ($idLogger === '10247') {
						// Peta param by id_param
						$byId = [];
						foreach ($paramList as $p) {
							$byId[(string) $p['id_param']] = $p;
						}

						// Definisi grup
						$shared = ['330', '331', '332'];
						$group1Id = '371';
						$group2Id = '372';
						$extra2 = ['374'];
						$extra1 = ['329'];

						$group1_ids = array_merge([$group1Id], $shared, $extra1);
						$group2_ids = array_merge([$group2Id], $shared, $extra2);

						// Kumpulkan param sesuai grup (hanya yang ada)
						$group1_params = [];
						foreach ($group1_ids as $pid) {
							if (isset($byId[$pid]))
								$group1_params[] = $byId[$pid];
						}
						$group2_params = [];
						foreach ($group2_ids as $pid) {
							if (isset($byId[$pid]))
								$group2_params[] = $byId[$pid];
						}

						// Tambahkan entri terpisah sesuai ketersediaan param
						if (!empty($group1_params)) {
							$listLogger[] = [
								'id_logger' => $idLogger . '_' . $group1Id,         // unik
								'nama_lokasi' => 'Pos AWLR Carik Barat',    // rename
								'waktu' => $waktu,
								'color' => $color,
								'status_logger' => $statusLogger,
								'status_sd' => $sdcard,
								'param' => $group1_params,                      // 371 + 330/331/332 + 324
							];
						}

						if (!empty($group2_params)) {
							$listLogger[] = [
								'id_logger' => $idLogger . '_' . $group2Id,         // unik
								'nama_lokasi' => 'Pos AWLR Sungai Bogowonto',    // rename
								'waktu' => $waktu,
								'color' => $color,
								'status_logger' => $statusLogger,
								'status_sd' => $sdcard,
								'param' => $group2_params,                      // 372 + 330/331/332 + 329
							];
						}
					} else {
						// default (logger normal, tidak di-split)
						$listLogger[] = [
							'id_logger' => $idLogger,

							'nama_lokasi' => $namaLokasi,
							'waktu' => $waktu,
							'color' => $color,
							'status_logger' => $statusLogger,
							'status_sd' => $sdcard,
							'param' => $paramList,
						];
					}
				}
				$ktg[$key]['logger'] = $listLogger;
			}

			$data_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/beranda'), true);

			foreach ($ktg as $kw => $sv) {
				if ($sv['nama_kategori'] == 'AWLR') {
					$nama = 'Duga Air Sungai';
					$logger_psda = $data_psda[$nama]['logger'];
					$merge = array_merge($logger_psda, $sv['logger']);
					$ktg[$kw]['logger'] = (array) $merge;
				} elseif ($sv['nama_kategori'] == 'ARR') {
					$nama = 'Curah Hujan';
					$logger_psda = $data_psda[$nama]['logger'];
					$merge = array_merge($logger_psda, $sv['logger']);
					$ktg[$kw]['logger'] = (array) $merge;
				}
				if ($sv['nama_kategori'] == 'AWS') {
					$nama = 'Stasiun Cuaca';
					$logger_psda = $data_psda[$nama]['logger'];
					$merge = array_merge($logger_psda, $sv['logger']);
					$ktg[$kw]['logger'] = (array) $merge;
				}
			}
			$data['data_konten'] = $ktg;
			$data['konten'] = 'konten/back/v_beranda';
			$this->load->view('template_admin/site', $data);
		} else {
			redirect('login');
		}
	}


	public function beranda_get()
	{
		$isUser2 = ($this->session->userdata('id_user') === '2');
		if ($isUser2) {
			$ktg = $this->db
				->where_in('id_katlogger', ['2', '7'])
				->get('kategori_logger')
				->result_array();
		} else {
			$ktg = $this->db
				->where('view', '1')
				->get('kategori_logger')
				->result_array();
		}
		foreach ($ktg as $key => $kat) {
			$tabelTemp = $kat['temp_data'];
			$idKat = $kat['id_katlogger'];

			$this->db->from('t_logger')
				->join('t_lokasi', 't_logger.lokasi_logger = t_lokasi.idlokasi', 'left')
				->where('kategori_log', $idKat)
				->order_by('id_logger', 'ASC');

			if ($isUser2) {
				$this->db->where('t_logger.user_id', '2');
			} else {
				$this->db->where('t_lokasi.das !=', '');
			}

			$data_logger = $this->db->get()->result_array();

			if (empty($data_logger)) {
				$ktg[$key]['logger'] = [];
				continue;
			}

			$loggerIds = array_column($data_logger, 'id_logger');

			$perbaikanRows = $this->db
				->where_in('id_logger', $loggerIds)
				->get('t_perbaikan')
				->result();
			$mapPerbaikan = [];
			foreach ($perbaikanRows as $r) {
				$mapPerbaikan[$r->id_logger] = true;
			}

			$tempRows = $this->db
				->where_in('code_logger', $loggerIds)
				->get($tabelTemp)
				->result();
			$mapTemp = [];
			foreach ($tempRows as $r) {
				$mapTemp[$r->code_logger] = $r;
			}
			$paramRows = $this->db
				->query("
                SELECT *
                FROM parameter_sensor
                WHERE logger_id IN ?
                ORDER BY CAST(SUBSTRING(kolom_sensor,7) AS UNSIGNED)
            ", [$loggerIds])
				->result_array();

			$mapParams = [];
			foreach ($paramRows as $pr) {
				$lid = $pr['logger_id'];
				if (!isset($mapParams[$lid]))
					$mapParams[$lid] = [];
				$mapParams[$lid][] = $pr;
			}

			$awalTs = time() - 3600; // 1 jam
			$awal = date('Y-m-d H:i', $awalTs);
			$sd15Loggers = ['10247', '10248', '10288', '10249', '10290', '10289', '10345', '10358', '10347', '10346', '10348'];

			$listLogger = [];
			foreach ($data_logger as $log) {
				$idLogger = $log['id_logger'];
				$namaLokasi = $log['nama_lokasi'];

				$temp = isset($mapTemp[$idLogger]) ? $mapTemp[$idLogger] : null;

				$waktu = $temp && !empty($temp->waktu) ? $temp->waktu : null;

				$color = 'black';
				$statusLogger = 'Koneksi Terputus';

				if ($waktu && $waktu >= $awal) {
					$color = '#32b344';
					$statusLogger = 'Koneksi Terhubung';
				}

				if (!empty($mapPerbaikan[$idLogger])) {
					$color = '#7f6226';
					$statusLogger = 'Perbaikan';
				}

				$kolomSd = in_array($idLogger, $sd15Loggers, true) ? 'sensor15' : 'sensor13';
				$sdcard = 'Bermasalah';
				if ($temp && isset($temp->$kolomSd) && $temp->$kolomSd === '1') {
					$sdcard = 'OK';
				}

				$paramList = isset($mapParams[$idLogger]) ? $mapParams[$idLogger] : [];
				foreach ($paramList as $ky => $val) {
					$kolom = $val['kolom_sensor'];
					$h = ($temp && isset($temp->$kolom)) ? $temp->$kolom : null;
					if ($val['debit_awlr'] === '1' && $idLogger === '10063') {
						$debit = $this->kalimeneng((float) $h);
						$paramList[$ky]['nilai'] = number_format(max(0, (float) $debit), 2, '.', '');
					} elseif ($val['nama_parameter'] === 'Debit' && $idLogger === '10249') {
						$n2 = ($temp && isset($temp->sensor2)) ? (float) $temp->sensor2 : 0.0;
						$paramList[$ky]['nilai'] = number_format($this->linear_interpolation($n2 * 100) * (float) $h, 2, '.', '');
					} elseif ($val['nama_parameter'] === 'Luas_Penampang_Basah') {
						$n2 = ($temp && isset($temp->sensor2)) ? (float) $temp->sensor2 : 0.0;
						$paramList[$ky]['nilai'] = number_format($this->linear_interpolation($n2 * 100), 2, '.', '');
					} elseif ($val['nama_parameter'] === 'Debit_Aliran_Sungai') {
						$s1 = ($temp && isset($temp->sensor1)) ? (float) $temp->sensor1 : 0.0;
						$s2 = ($temp && isset($temp->sensor2)) ? (float) $temp->sensor2 : 0.0;
						$n2 = $s1 - $s2;
						if ($s2 > $s1) {
							$paramList[$ky]['nilai'] = number_format(0, 2, '.', '');
						} else {
							$paramList[$ky]['nilai'] = number_format($this->debit_interpolation($n2), 2, '.', '');
						}
					} else {
						$paramList[$ky]['nilai'] = $h;
					}

					$get = http_build_query([
						'id_param' => $val['id_param'] . '_' . 'bbws',
					]);
					$paramList[$ky]['link'] = 'analisa/set_sensordash?' . $get;
				}
				if ($idLogger === '10247') {
					// Peta param by id_param
					$byId = [];
					foreach ($paramList as $p) {
						$byId[(string) $p['id_param']] = $p;
					}

					// Definisi grup
					$shared = ['330', '331', '332'];
					$group1Id = '371';
					$group2Id = '372';
					$extra2 = ['374'];
					$extra1 = ['329'];

					$group1_ids = array_merge([$group1Id], $shared, $extra1);
					$group2_ids = array_merge([$group2Id], $shared, $extra2);

					// Kumpulkan param sesuai grup (hanya yang ada)
					$group1_params = [];
					foreach ($group1_ids as $pid) {
						if (isset($byId[$pid]))
							$group1_params[] = $byId[$pid];
					}
					$group2_params = [];
					foreach ($group2_ids as $pid) {
						if (isset($byId[$pid]))
							$group2_params[] = $byId[$pid];
					}

					// Tambahkan entri terpisah sesuai ketersediaan param
					if (!empty($group1_params)) {
						$listLogger[] = [
							'id_logger' => $idLogger . '_' . $group1Id,         // unik
							'nama_lokasi' => 'Pos AWLR Carik Barat',    // rename
							'waktu' => $waktu,
							'color' => $color,
							'status_logger' => $statusLogger,
							'status_sd' => $sdcard,
							'param' => $group1_params,                      // 371 + 330/331/332 + 324
						];
					}

					if (!empty($group2_params)) {
						$listLogger[] = [
							'id_logger' => $idLogger . '_' . $group2Id,         // unik
							'nama_lokasi' => 'Pos AWLR Sungai Bogowonto',    // rename
							'waktu' => $waktu,
							'color' => $color,
							'status_logger' => $statusLogger,
							'status_sd' => $sdcard,
							'param' => $group2_params,                      // 372 + 330/331/332 + 329
						];
					}
				} else {
					// default (logger normal, tidak di-split)
					$listLogger[] = [
						'id_logger' => $idLogger,

						'nama_lokasi' => $namaLokasi,
						'waktu' => $waktu,
						'color' => $color,
						'status_logger' => $statusLogger,
						'status_sd' => $sdcard,
						'param' => $paramList,
					];
				}
			}
			$ktg[$key]['logger'] = $listLogger;
		}

		$data_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/beranda'), true);

		foreach ($ktg as $kw => $sv) {
			if ($sv['nama_kategori'] == 'AWLR') {
				$nama = 'Duga Air Sungai';
				$logger_psda = $data_psda[$nama]['logger'];
				$merge = array_merge($logger_psda, $sv['logger']);
				$ktg[$kw]['logger'] = (array) $merge;
			} elseif ($sv['nama_kategori'] == 'ARR') {
				$nama = 'Curah Hujan';
				$logger_psda = $data_psda[$nama]['logger'];
				$merge = array_merge($logger_psda, $sv['logger']);
				$ktg[$kw]['logger'] = (array) $merge;
			}
			if ($sv['nama_kategori'] == 'AWS') {
				$nama = 'Stasiun Cuaca';
				$logger_psda = $data_psda[$nama]['logger'];
				$merge = array_merge($logger_psda, $sv['logger']);
				$ktg[$kw]['logger'] = (array) $merge;
			}
		}

		// Perbarui file logger_mapping.json
		$jsonPath = FCPATH . 'logger_mapping.json';
		file_put_contents($jsonPath, json_encode($ktg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));


	}


	public function integrasi()
	{
		if ($this->session->userdata('logged_in')) {
			$kategori = array();
			if ($this->session->userdata('id_user') == '2') {
				$ktg = $this->db->where('id_katlogger', '2')->or_where('id_katlogger', '7')->get('kategori_logger')->result_array();
			} else {
				$ktg = $this->db->where('view', '1')->get('kategori_logger')->result_array();
			}

			foreach ($ktg as $key => $kat) {

				$tabel = $kat['temp_data'];
				$content = array();
				if ($this->session->userdata('id_user') == '2') {
					$data_logger = $this->db->join('t_lokasi', 't_logger.lokasi_logger = t_lokasi.idlokasi')->where('kategori_log', $kat['id_katlogger'])->where('t_logger.user_id', '2')->order_by('id_logger')->get('t_logger')->result_array();
				} else {
					$data_logger = $this->db->join('t_lokasi', 't_logger.lokasi_logger = t_lokasi.idlokasi')->where('kategori_log', $kat['id_katlogger'])->where('t_lokasi.das !=', '')->order_by('id_logger')->get('t_logger')->result_array();
				}

				foreach ($data_logger as $k => $log) {
					$id_logger = $log['id_logger'];
					$temp_data = $this->db->where('code_logger', $id_logger)->get($tabel)->row();
					$awal = date('Y-m-d H:i', (mktime(date('H') - 1)));
					if ($temp_data->waktu >= $awal) {
						$color = "#32b344";
						$status_logger = "Koneksi Terhubung";
					} else {
						$color = "black";
						$status_logger = "Koneksi Terputus";
					}
					$perbaikan = $this->db->get_where('t_perbaikan', array('id_logger' => $id_logger))->row();
					if ($perbaikan) {
						$color = "#7f6226";
						$status_logger = "Perbaikan";
					}
					if ($id_logger == '10247' or $id_logger == '10248' or $id_logger == '10288' or $id_logger == '10249' or $id_logger == '10290') {
						$kolom_sd = 'sensor15';
					} else {
						$kolom_sd = 'sensor13';
					}
					if ($temp_data->$kolom_sd == '1') {
						$sdcard = 'OK';
					} else {
						$sdcard = 'Bermasalah';
					}
					$param = $this->db->query("SELECT * FROM `parameter_sensor` WHERE logger_id = '$id_logger' ORDER BY CAST(SUBSTR(`kolom_sensor`,7) AS UNSIGNED)")->result_array();
					foreach ($param as $ky => $val) {
						$get = 'tabel=' . $kat['tabel'] . '&id_param=' . $val['id_param'];
						$kolom = $val['kolom_sensor'];
						$h = $temp_data->$kolom;
						if ($val['debit_awlr'] == '1' and $id_logger == '10063') {
							$debit = $this->kalimeneng($h);
							if ($h < 0) {
								$param[$ky]['nilai'] = number_format(0, 2, '.', '');
							} else {
								$param[$ky]['nilai'] = number_format($debit, 2, '.', '');
							}
						} elseif ($val['nama_parameter'] == 'Debit' and $id_logger == '10249') {
							$n2 = $temp_data->sensor2;
							$param[$ky]['nilai'] = number_format($this->linear_interpolation($n2 * 100) * $h, 2, '.', '');
						} elseif ($val['nama_parameter'] == 'Luas_Penampang_Basah') {
							$n2 = $temp_data->sensor2;
							$param[$ky]['nilai'] = number_format($this->linear_interpolation($n2 * 100), 2, '.', '');
						} elseif ($val['nama_parameter'] == 'Debit_Aliran_Sungai') {
							$n2 = $temp_data->sensor1 - $temp_data->sensor2;
							if ($temp_data->sensor2 > $temp_data->sensor1) {
								$param[$ky]['nilai'] = number_format(0, 2, '.', '');
							} else {
								$param[$ky]['nilai'] = number_format($this->debit_interpolation($n2), 2, '.', '');
							}

						} else {
							$param[$ky]['nilai'] = $h;
						}

						$param[$ky]['link'] = base_url() . 'analisa/set_sensordash?' . $get;
					}
					$ktg[$key]['logger'][$k] = [
						'id_logger' => $id_logger,
						'nama_lokasi' => $log['nama_lokasi'],
						'waktu' => $temp_data->waktu,
						'color' => $color,
						'status_logger' => $status_logger,
						'status_sd' => $sdcard,
						'param' => $param
					];
				}
			}
			$data_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/beranda'), true);

			foreach ($ktg as $kw => $sv) {
				if ($sv['nama_kategori'] == 'AWLR') {
					$nama = 'Duga Air Sungai';
					$logger_psda = $data_psda[$nama]['logger'];
					$merge = array_merge($logger_psda, $sv['logger']);
					$ktg[$kw]['logger'] = (array) $merge;
				} elseif ($sv['nama_kategori'] == 'ARR') {
					$nama = 'Curah Hujan';
					$logger_psda = $data_psda[$nama]['logger'];
					$merge = array_merge($logger_psda, $sv['logger']);
					$ktg[$kw]['logger'] = (array) $merge;
				}
				if ($sv['nama_kategori'] == 'AWS') {
					$nama = 'Stasiun Cuaca';
					$logger_psda = $data_psda[$nama]['logger'];
					$merge = array_merge($logger_psda, $sv['logger']);
					$ktg[$kw]['logger'] = (array) $merge;
				}
			}
			$data['data_konten'] = $ktg;
			$data['konten'] = 'konten/back/v_beranda2';
			$this->load->view('template_admin/site', $data);
		} else {
			redirect('login');
		}
	}
	function riset_rumus()
	{
		$rumus = $this->db->where('id_logger', '10052')->get('rumus_debit')->row()->rumus;
		$h = 0.514;
		$newVariable = eval ('return ' . $rumus . ';');
		echo $newVariable;
		//echo $s;
	}
}
