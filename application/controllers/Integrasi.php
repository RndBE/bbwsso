<?php if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 * Integrasi.php
 *
 * Endpoint integrasi BBWSSO -> DPUPESDM. Hanya mengekspos 7 pos asli BBWS
 * yang lokasinya berada di wilayah DIY. Daftar logger sengaja di-hardcode
 * supaya tidak ada kemungkinan bocor pos non-DIY ke konsumen.
 *
 * Suffix idLogger pada response selalu `_bbws` untuk konsistensi dengan
 * pola yang sudah dipakai (DPUPESDM memakai `_psda`).
 */
class Integrasi extends CI_Controller
{
	/** Whitelist 7 pos BBWS di wilayah DIY (Yogyakarta) */
	private $diy_loggers = ['10044', '10093', '10223', '10247', '10250', '10289', '10348'];

	function __construct()
	{
		parent::__construct();
		$this->load->model('m_analisa');
		header('Access-Control-Allow-Origin: *');
		header('Content-Type: application/json');
	}

	private function _is_diy($id_logger)
	{
		return in_array((string) $id_logger, $this->diy_loggers, true);
	}

	private function _reject_non_diy($id_logger)
	{
		if (!$this->_is_diy($id_logger)) {
			http_response_code(403);
			echo json_encode(['error' => 'Logger di luar wilayah DIY tidak diizinkan']);
			exit;
		}
	}

	// ─── Helper kalkulasi debit (mirror dari Analisa.php) ──────────────

	private function _hitung_debit_rating_curve($idLogger, $ma)
	{
		static $cache = [];
		if (!isset($cache[$idLogger])) {
			$rows = $this->db->where('id_logger', $idLogger)
				->order_by('segmen', 'ASC')
				->get('rumus_rating_curve')->result();
			$cache[$idLogger] = empty($rows) ? null : $rows;
		}
		$segments = $cache[$idLogger];
		if ($segments === null)
			return null;

		$domainMin = (float) $segments[0]->domain_min;
		$domainMax = (float) $segments[0]->domain_max;
		if ($ma < $domainMin || $ma > $domainMax)
			return null;

		foreach ($segments as $seg) {
			$segMin = (float) $seg->ma_min;
			$segMax = (float) $seg->ma_max;
			if ($ma >= $segMin && $ma <= $segMax) {
				$base = $ma + (float) $seg->koef_b;
				if ($base < 0)
					return 0;
				return (float) $seg->koef_a * pow($base, (float) $seg->koef_c);
			}
		}
		return null;
	}

	private function _nilai_debit_rating_curve($idLogger, $ma, $fallback)
	{
		$debit = $this->_hitung_debit_rating_curve($idLogger, (float) $ma);
		return ($debit !== null) ? max(0, (float) $debit) : $fallback;
	}

	private function _debit_interpolation($x)
	{
		$data = [
			[0.20, 12.60], [0.40, 17.82], [0.60, 21.82], [0.80, 25.20], [1.00, 28.17],
			[1.20, 31.21], [1.40, 33.96], [1.60, 36.71], [1.80, 38.94], [2.00, 41.04],
			[2.20, 43.36], [2.40, 45.29], [2.60, 47.14], [2.80, 48.92], [3.00, 50.64],
			[3.20, 52.30], [3.40, 53.91], [3.60, 55.47], [3.80, 56.99], [4.00, 58.47],
			[4.20, 23.97], [4.40, 24.53], [4.60, 25.08], [4.80, 25.62], [5.00, 26.15],
			[5.20, 26.67],
		];
		usort($data, function ($a, $b) {
			return $a[0] - $b[0];
		});
		$x1 = $x2 = $y1 = $y2 = null;
		foreach ($data as $p) {
			if ($p[0] <= $x) {
				$x1 = $p[0];
				$y1 = $p[1];
			}
			if ($p[0] >= $x) {
				$x2 = $p[0];
				$y2 = $p[1];
				break;
			}
		}
		if ($x1 === null || $x2 === null)
			return 0;
		if ($x == $x1)
			return $y1;
		return $y1 + (($x - $x1) / ($x2 - $x1)) * ($y2 - $y1);
	}

	private function _apply_debit($nama_parameter, $idLogger, $h, $min_data, $max_data, $row)
	{
		if ($nama_parameter === 'Debit') {
			$h = $this->_nilai_debit_rating_curve($idLogger, $h, $h);
			$min_data = $this->_nilai_debit_rating_curve($idLogger, $min_data, $min_data);
			$max_data = $this->_nilai_debit_rating_curve($idLogger, $max_data, $max_data);
		} elseif ($nama_parameter === 'Debit_Aliran_Sungai') {
			$h = $this->_debit_interpolation($row->avg_diff);
			$min_data = $this->_debit_interpolation($row->min_diff);
			$max_data = $this->_debit_interpolation($row->max_diff);
		}
		return [$h, $min_data, $max_data];
	}

	// ─── Pilih Pos / Parameter ─────────────────────────────────────────

	public function api_pilihpos()
	{
		$kategori = $this->input->get('kategori'); // optional: 'awlr' / 'arr' / 'awr'
		$this->db->select('t_logger.id_logger, t_lokasi.nama_lokasi, kategori_logger.tabel as icon, kategori_logger.id_katlogger')
			->from('t_logger')
			->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
			->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
			->where_in('t_logger.id_logger', $this->diy_loggers)
			->order_by('t_logger.id_logger', 'ASC');
		if ($kategori) {
			$this->db->where('kategori_logger.tabel', $kategori);
		}
		$rows = $this->db->get()->result();
		$data = [];
		foreach ($rows as $r) {
			$data[] = [
				'idLogger' => $r->id_logger . '_bbws',
				'namaPos' => $r->nama_lokasi,
				'kategori' => $r->icon,
				'id_katlogger' => $r->id_katlogger,
			];
		}
		echo json_encode($data);
	}

	public function pilihparameter($idlogger)
	{
		$this->_reject_non_diy($idlogger);
		$data = [];
		$rows = $this->db->query(
			"SELECT * FROM parameter_sensor WHERE logger_id = ? ORDER BY CAST(SUBSTRING(kolom_sensor,7) AS UNSIGNED)",
			[$idlogger]
		)->result();
		foreach ($rows as $p) {
			$data[] = [
				'idParameter' => $p->id_param,
				'namaParameter' => $p->nama_parameter,
				'fieldParameter' => $p->kolom_sensor,
			];
		}
		echo json_encode($data);
	}

	public function param_belongs_to_logger()
	{
		$id_param = $this->input->get('id_param');
		$id_logger = $this->input->get('id_logger');
		if (!$this->_is_diy($id_logger)) {
			echo '0';
			return;
		}
		$cnt = $this->db->where('id_param', $id_param)
			->where('logger_id', $id_logger)
			->count_all_results('parameter_sensor');
		echo (string) (int) (bool) $cnt;
	}

	public function get_default_param_for_logger($id_logger)
	{
		$this->_reject_non_diy($id_logger);
		$row = $this->db->where('logger_id', $id_logger)
			->order_by('id_param', 'ASC')
			->limit(1)
			->get('parameter_sensor')->row();
		echo $row ? $row->id_param : '';
	}

	// ─── Analisa per Tanggal / Bulan / Tahun / Range ───────────────────

	private function _fetch_param_meta($idsensor)
	{
		return $this->db->select('parameter_sensor.*, t_logger.tabel_main, t_logger.kategori_log, t_lokasi.nama_lokasi, t_lokasi.das, t_lokasi.latitude, t_lokasi.longitude, kategori_logger.temp_data, kategori_logger.tabel, kategori_logger.controller, t_informasi.*')
			->join('t_logger', 't_logger.id_logger = parameter_sensor.logger_id')
			->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
			->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
			->join('t_informasi', 't_informasi.logger_id = t_logger.id_logger', 'left')
			->where('parameter_sensor.id_param', $idsensor)
			->get('parameter_sensor')->row();
	}

	private function _status_temp($id_logger, $nama_lokasi, $temp_data_tbl)
	{
		$qstatus = $this->db->where('code_logger', $id_logger)->get($temp_data_tbl)->row();
		$awal = date('Y-m-d H:i', mktime(date('H') - 1, 0, 0, date('m'), date('d'), date('Y')));
		$waktu = $qstatus->waktu ?? null;

		if ($waktu && $waktu >= $awal) {
			$color = 'green';
			$status_logger = 'Koneksi Terhubung';
		} else {
			$color = 'dark';
			$status_logger = 'Koneksi Terputus';
		}
		$perbaikan = $this->db->get_where('t_perbaikan', ['id_logger' => $id_logger])->row();
		if ($perbaikan) {
			$stts = '1';
			$status_logger = 'Perbaikan';
		} else {
			$stts = '0';
		}
		return [
			'nama_lokasi' => $nama_lokasi,
			'color' => $color,
			'status_logger' => $status_logger,
			'stts' => $stts,
		];
	}

	private function _run_analisa($idsensor, $modeData, $pada, $dari, $sampai)
	{
		$qparam = $this->_fetch_param_meta($idsensor);
		if (!$qparam) {
			http_response_code(404);
			echo json_encode(['error' => 'Parameter tidak ditemukan']);
			exit;
		}
		$this->_reject_non_diy($qparam->logger_id);

		$idLogger = $qparam->logger_id;
		$kolom = $qparam->kolom_sensor;
		$namaParameter = $qparam->nama_parameter;
		$satuan = $qparam->satuan;
		$tipeGrafik = $qparam->tipe_graf;
		$tabel_main = $qparam->tabel_main;
		$temp_tbl = $qparam->temp_data;
		$nama_sensor = ($tipeGrafik === 'column') ? ('Akumulasi_' . $namaParameter) : ('Rerata_' . $namaParameter);
		$selectAgg = ($tipeGrafik === 'column') ? "SUM($kolom) AS $nama_sensor" : "AVG($kolom) AS $nama_sensor";

		// rentang & format
		if ($modeData === 'hari') {
			$start = $pada . ' 00:00:00';
			$end = $pada . ' 23:59:59';
			$select = "avg(sensor2) as tma,max(sensor2) as tma_max,min(sensor2) as tma_min,AVG(sensor1 - sensor2) AS avg_diff, MIN(sensor1 - sensor2) AS min_diff, MAX(sensor1 - sensor2) AS max_diff, waktu, HOUR(waktu) AS jam, DAY(waktu) AS hari, MONTH(waktu) AS bulan, YEAR(waktu) AS tahun, $selectAgg, MIN($kolom) AS min, MAX($kolom) AS max";
			$group = 'YEAR(waktu), MONTH(waktu), DAY(waktu), HOUR(waktu)';
			$order = 'tahun ASC, bulan ASC, hari ASC, jam ASC';
			$fmtTbl = function ($r, $h, $min, $max) {
				return [
					'waktu' => date('H', strtotime($r->waktu)) . ':00:00',
					'dta' => number_format($h, 2, '.', ''),
					'min' => number_format($min, 2, '.', ''),
					'max' => number_format($max, 2, '.', ''),
				];
			};
			$fmtPoint = function ($r, $val) {
				return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari,$r->jam)," . number_format($val, 3, '.', '') . "]";
			};
			$fmtRange = function ($r, $min, $max) {
				return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari,$r->jam)," . $min . "," . $max . "]";
			};
			$tooltip = 'Waktu %d-%m-%Y %H:%M';
		} elseif ($modeData === 'bulan') {
			$start = $pada . '-01 00:00:00';
			$end = date('Y-m-t 23:59:59', strtotime($pada . '-01'));
			$select = "avg(sensor2) as tma,max(sensor2) as tma_max,min(sensor2) as tma_min,AVG(sensor1 - sensor2) AS avg_diff, MIN(sensor1 - sensor2) AS min_diff, MAX(sensor1 - sensor2) AS max_diff, waktu, DATE(waktu) AS tanggal, DAY(waktu) AS hari, MONTH(waktu) AS bulan, YEAR(waktu) AS tahun, $selectAgg, MIN($kolom) AS min, MAX($kolom) AS max";
			$group = 'YEAR(waktu), MONTH(waktu), DAY(waktu)';
			$order = 'tahun ASC, bulan ASC, hari ASC';
			$fmtTbl = function ($r, $h, $min, $max) {
				return [
					'waktu' => date('Y-m-d', strtotime($r->waktu)),
					'dta' => number_format($h, 2, '.', ''),
					'min' => number_format($min, 2, '.', ''),
					'max' => number_format($max, 2, '.', ''),
				];
			};
			$fmtPoint = function ($r, $val) {
				return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari)," . number_format($val, 3, '.', '') . "]";
			};
			$fmtRange = function ($r, $min, $max) {
				return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari)," . $min . "," . $max . "]";
			};
			$tooltip = 'Tanggal %d-%m-%Y';
		} elseif ($modeData === 'tahun') {
			$start = $pada . '-01-01 00:00:00';
			$end = $pada . '-12-31 23:59:59';
			$select = "avg(sensor2) as tma,max(sensor2) as tma_max,min(sensor2) as tma_min,AVG(sensor1 - sensor2) AS avg_diff, MIN(sensor1 - sensor2) AS min_diff, MAX(sensor1 - sensor2) AS max_diff, DATE(waktu) AS tanggal, MONTH(waktu) AS bulan, YEAR(waktu) AS tahun, $selectAgg, MIN($kolom) AS min, MAX($kolom) AS max";
			$group = 'YEAR(waktu), MONTH(waktu)';
			$order = 'tahun ASC, bulan ASC';
			$fmtTbl = function ($r, $h, $min, $max) {
				return [
					'waktu' => date('Y-m', strtotime($r->tanggal)),
					'dta' => number_format($h, 2, '.', ''),
					'min' => number_format($min, 2, '.', ''),
					'max' => number_format($max, 2, '.', ''),
				];
			};
			$fmtPoint = function ($r, $val) {
				return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ")," . number_format($val, 3, '.', '') . "]";
			};
			$fmtRange = function ($r, $min, $max) {
				return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ")," . $min . "," . $max . "]";
			};
			$tooltip = 'Tanggal %d-%m-%Y';
		} else { // range
			$start = $dari;
			$end = $sampai;
			$select = "avg(sensor2) as tma,max(sensor2) as tma_max,min(sensor2) as tma_min,AVG(sensor1 - sensor2) AS avg_diff, MIN(sensor1 - sensor2) AS min_diff, MAX(sensor1 - sensor2) AS max_diff, waktu, DATE(waktu) AS tanggal, HOUR(waktu) AS jam, DAY(waktu) AS hari, MONTH(waktu) AS bulan, YEAR(waktu) AS tahun, $selectAgg, MIN($kolom) AS min, MAX($kolom) AS max";
			$group = 'YEAR(waktu), MONTH(waktu), DAY(waktu), HOUR(waktu)';
			$order = 'tahun ASC, bulan ASC, hari ASC, jam ASC';
			$fmtTbl = function ($r, $h, $min, $max) {
				return [
					'waktu' => date('Y-m-d H', strtotime($r->waktu)) . ':00:00',
					'dta' => number_format($h, 3, '.', ''),
					'min' => number_format($min, 2, '.', ''),
					'max' => number_format($max, 2, '.', ''),
				];
			};
			$fmtPoint = function ($r, $val) {
				return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari,$r->jam)," . number_format($val, 3, '.', '') . "]";
			};
			$fmtRange = function ($r, $min, $max) {
				return "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari,$r->jam)," . $min . "," . $max . "]";
			};
			$tooltip = 'Waktu %d-%m-%Y %H:%M';
		}

		$sql = "SELECT $select FROM {$tabel_main} WHERE code_logger=? AND waktu BETWEEN ? AND ? GROUP BY $group ORDER BY $order";
		$rs = $this->db->query($sql, [$idLogger, $start, $end])->result();

		$data = [];
		$range = [];
		$data_tabel = [];
		$akumulasi_hujan = 0;
		foreach ($rs as $r) {
			$h = $r->$nama_sensor;
			$min_data = $r->min;
			$max_data = $r->max;
			[$h, $min_data, $max_data] = $this->_apply_debit($namaParameter, $idLogger, $h, $min_data, $max_data, $r);
			if ($tipeGrafik === 'column')
				$akumulasi_hujan += $h;
			$data[] = $fmtPoint($r, $h);
			$range[] = $fmtRange($r, $min_data, $max_data);
			$data_tabel[] = $fmtTbl($r, $h, $min_data, $max_data);
		}

		$dataAnalisa = [
			'idParam' => $idsensor,
			'idLogger' => $idLogger,
			'namaSensor' => $nama_sensor,
			'satuan' => $satuan,
			'tipe_grafik' => $tipeGrafik,
			'data' => $data,
			'data_tabel' => $data_tabel,
			'nosensor' => $kolom,
			'range' => $range,
			'tooltip' => $tooltip,
			'tooltipper' => $tooltip,
			'mode_data' => $modeData,
			'pada' => $pada,
			'dari' => $dari,
			'sampai' => $sampai,
			'akumulasi_hujan' => $akumulasi_hujan,
		];

		$temp_data = $this->_status_temp($idLogger, $qparam->nama_lokasi, $temp_tbl);

		// pilih_pos & pilih_parameter inline (return-style, bukan echo)
		$pilih_pos = [];
		$rows_pos = $this->db->select('t_logger.id_logger, t_lokasi.nama_lokasi')
			->from('t_logger')->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
			->where_in('t_logger.id_logger', $this->diy_loggers)
			->order_by('t_logger.id_logger', 'ASC')->get()->result();
		foreach ($rows_pos as $r) {
			$pilih_pos[] = ['idLogger' => $r->id_logger . '_bbws', 'namaPos' => $r->nama_lokasi];
		}

		$pilih_parameter = [];
		$rows_par = $this->db->query(
			"SELECT * FROM parameter_sensor WHERE logger_id = ? ORDER BY CAST(SUBSTRING(kolom_sensor,7) AS UNSIGNED)",
			[$idLogger]
		)->result();
		foreach ($rows_par as $p) {
			$pilih_parameter[] = [
				'idParameter' => $p->id_param,
				'namaParameter' => $p->nama_parameter,
				'fieldParameter' => $p->kolom_sensor,
			];
		}

		$foto_pos = $this->db->where('id_logger', $idLogger)->get('foto_pos')->result_array();
		$riwayat_op = $this->db->where('id_logger', $idLogger)->get('t_riwayat')->result_array();

		return [
			'informasi' => $qparam,
			'data_sensor' => $dataAnalisa,
			'pilih_pos' => $pilih_pos,
			'pilih_parameter' => $pilih_parameter,
			'temp_data' => $temp_data,
			'foto_pos' => $foto_pos ?: [],
			'data_op' => $riwayat_op ?: [],
		];
	}

	public function analisapertanggal2()
	{
		$result = $this->_run_analisa(
			$this->input->get('idsensor'),
			'hari',
			$this->input->get('tanggal'),
			null,
			null
		);
		echo json_encode($result);
	}

	public function analisaperbulan2()
	{
		$result = $this->_run_analisa(
			$this->input->get('idsensor'),
			'bulan',
			$this->input->get('tanggal'),
			null,
			null
		);
		echo json_encode($result);
	}

	public function analisapertahun2()
	{
		$result = $this->_run_analisa(
			$this->input->get('idsensor'),
			'tahun',
			$this->input->get('tahun'),
			null,
			null
		);
		echo json_encode($result);
	}

	public function analisaperrange2()
	{
		$result = $this->_run_analisa(
			$this->input->get('idsensor'),
			'range',
			null,
			$this->input->get('dari'),
			$this->input->get('sampai')
		);
		echo json_encode($result);
	}

	// ─── Peta Lokasi (marker 7 pos DIY) ────────────────────────────────

	public function peta_lokasi()
	{
		$awal = date('Y-m-d H:i', mktime(date('H') - 1, 0, 0, date('m'), date('d'), date('Y')));
		$jam_now = date('Y-m-d H') . ':00';

		$loggers = $this->db->select('t_logger.*, t_lokasi.*, kategori_logger.*, t_informasi.*')
			->from('t_logger')
			->join('t_lokasi', 't_logger.lokasi_logger = t_lokasi.idlokasi')
			->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
			->join('t_informasi', 't_logger.id_logger = t_informasi.logger_id', 'left')
			->where_in('t_logger.id_logger', $this->diy_loggers)
			->get()->result();

		$marker = [];
		$das_map = [];
		foreach ($loggers as $log) {
			$id_logger = $log->id_logger;
			$icon = $log->tabel; // awlr / arr / awr
			$temp_tbl = $log->temp_data;
			$tabel_main = $log->tabel_main;

			$dt = $this->db->where('code_logger', $id_logger)->get($temp_tbl)->row();
			$perb = $this->db->where('id_logger', $id_logger)->get('t_perbaikan')->row();
			$waktu = $dt ? $dt->waktu : null;
			$param_utama = $this->db->where('logger_id', $id_logger)
				->order_by('parameter_utama', 'DESC')
				->order_by('id_param', 'ASC')->limit(1)
				->get('parameter_sensor')->row();

			$data_p = 0;
			if ($icon === 'awlr') {
				$data_p = $dt ? (float) $dt->sensor1 : 0;
			} else {
				$sen9 = $this->db->where('logger_id', $id_logger)->where('kolom_sensor', 'sensor9')
					->count_all_results('parameter_sensor');
				$col = $sen9 ? 'sensor9' : 'sensor8';
				$aku = $this->db->query(
					"SELECT SUM($col) AS v FROM $tabel_main WHERE code_logger=? AND waktu >= ?",
					[$id_logger, $jam_now]
				)->row();
				$data_p = $aku ? (float) $aku->v : 0;
			}

			[$icon_marker, $statlog, $statuspantau, $anim] = $this->_marker_state($icon, $perb, $waktu, $awal, $data_p);

			$id_param = $param_utama ? $param_utama->id_param : '';
			$foto = $this->db->where('id_logger', $id_logger)->get('foto_pos')->row();
			$img_pos = '';
			if ($foto) {
				$img_pos = '<div class="d-flex w-100 justify-content-center mb-2 mt-3"><div style="background:url(https://bbws.beacontelemetry.com/image/foto_pos/' . $foto->url_foto . ');width:300px;height:200px;background-size:cover;background-position:center" class="img-fluid"></div></div>';
			}

			$kat_group = $icon === 'awlr' ? 'awlr' : ($icon === 'arr' ? 'arr' : 'awr');
			$marker[] = [
				'nama_das' => $log->das,
				'id_kategori' => $log->id_katlogger,
				'id_logger' => $id_logger,
				'category' => $kat_group,
				'status_aset' => 'BBWS Serayu Opak',
				'category_group' => $statuspantau,
				'koneksi' => $statlog,
				'status_sd' => 'OK',
				'latitude' => $log->latitude,
				'longitude' => $log->longitude,
				'nama_lokasi' => $log->nama_lokasi,
				'icon' => $icon_marker,
				'id_param' => $id_param,
				'link' => 'https://bbwsso.monitoring4system.com/analisa/set_sensordash?id_param=' . $id_param . '_bbws',
				'nama_pic' => $log->nama_pic ?? '',
				'no_pic' => $log->no_pic ?? '',
				'foto_pos' => $img_pos,
				'anim' => $anim,
			];

			// kumpulkan untuk data_konten per DAS
			$das_map[$log->das][] = [
				'id_logger' => $id_logger,
				'nama_lokasi' => $log->nama_lokasi,
				'waktu' => $waktu,
				'color' => ($waktu && $waktu >= $awal) ? 'green' : 'red',
				'status_logger' => ($waktu && $waktu >= $awal) ? 'Koneksi Terhubung' : 'Koneksi Terputus',
			];
		}

		$data_konten = [];
		foreach ($das_map as $nama_das => $list) {
			$data_konten[] = ['nama_das' => $nama_das, 'logger' => $list];
		}

		echo json_encode([
			'status' => 'ok',
			'data_konten' => $data_konten,
			'marker' => $marker,
		]);
	}

	// ─── Beranda (dashboard cards) ─────────────────────────────────────

	/**
	 * Return data 7 pos DIY siap-pakai untuk dashboard. Bentuk respons
	 * mirror dari DPUPESDM Integrasi::beranda — array kategori, tiap
	 * kategori berisi `nama_kategori` dan list `logger` lengkap dengan
	 * parameter terbaru. Hanya kategori yang dimiliki pos DIY yang
	 * dimunculkan (AWLR + Curah Hujan).
	 */
	public function beranda()
	{
		$awal = date('Y-m-d H:i', mktime(date('H') - 1, 0, 0, date('m'), date('d'), date('Y')));
		$jam_now = date('Y-m-d H') . ':00';

		$loggers = $this->db
			->select('t_logger.*, t_lokasi.nama_lokasi, t_lokasi.das, t_lokasi.latitude, t_lokasi.longitude, kategori_logger.nama_kategori, kategori_logger.tabel as icon, kategori_logger.temp_data, kategori_logger.id_katlogger, kategori_logger.controller')
			->from('t_logger')
			->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
			->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
			->where_in('t_logger.id_logger', $this->diy_loggers)
			->order_by('t_logger.id_logger', 'ASC')
			->get()->result();

		$kategori = [];
		foreach ($loggers as $log) {
			$id_logger = $log->id_logger;
			$temp_tbl = $log->temp_data;
			$tabel_main = $log->tabel_main;
			// Mapping nama kategori BBWSSO → DPUPESDM agar merge di beranda DPUPESDM
			// match section yang sama (AWLR → "Duga Air Sungai", ARR → "Curah Hujan")
			if ($log->icon === 'awlr') {
				$nama_kat = 'Duga Air Sungai';
			} elseif ($log->icon === 'arr') {
				$nama_kat = 'Curah Hujan';
			} else {
				$nama_kat = $log->nama_kategori;
			}

			$dt = $this->db->where('code_logger', $id_logger)->get($temp_tbl)->row();
			$cek_perb = $this->db->where('id_logger', $id_logger)->get('t_perbaikan')->row();

			if ($cek_perb) {
				$status = 'aktif';
				$warna = '#7e6126';
			} elseif ($dt && $dt->waktu >= $awal) {
				$status = 'aktif';
				$warna = '#2fb344';
			} else {
				$status = 'terputus';
				$warna = '#181823';
			}

			$params = $this->db->query(
				"SELECT * FROM parameter_sensor WHERE logger_id = ? ORDER BY CAST(SUBSTR(kolom_sensor,7) AS UNSIGNED)",
				[$id_logger]
			)->result_array();

			$controller_dpupesdm = ($log->icon === 'awlr') ? 'awlr' : 'curah_hujan';

			foreach ($params as $k => $p) {
				$kolom = $p['kolom_sensor'];
				$nilai = '-';
				if ($dt && isset($dt->$kolom) && $dt->$kolom !== null) {
					$nilai = number_format((float) $dt->$kolom, 3);
					// Debit pakai rating curve (10044) atau debit_interpolation (10247)
					if ($p['nama_parameter'] === 'Debit') {
						$rc = $this->_hitung_debit_rating_curve($id_logger, (float) $dt->$kolom);
						if ($rc !== null) {
							$nilai = number_format(max(0, $rc), 3);
						}
					} elseif ($p['nama_parameter'] === 'Debit_Aliran_Sungai') {
						$diff = (float) ($dt->sensor1 ?? 0) - (float) ($dt->sensor2 ?? 0);
						$nilai = number_format((float) $this->_debit_interpolation($diff), 3);
					}
				}
				$params[$k]['nilai'] = $nilai;
				$params[$k]['alias_sensor'] = $p['nama_parameter'];
				$params[$k]['field_sensor'] = $p['kolom_sensor'];
				// Link relatif → DPUPESDM Beranda akan prepend base_url + handle in-place
				$params[$k]['link_path'] = $controller_dpupesdm . '/set_sensordash?id_param=' . $p['id_param'] . '_bbws&id_logger=' . $id_logger;
				$params[$k]['link'] = $params[$k]['link_path']; // fallback; akan diganti DPUPESDM jadi absolute URL lokal
			}

			if (!isset($kategori[$nama_kat])) {
				$kategori[$nama_kat] = [
					'nama_kategori' => $nama_kat,
					'logger' => [],
				];
			}
			$kategori[$nama_kat]['logger'][] = [
				'code_logger' => $id_logger,
				'id_logger' => $id_logger,
				'nama_lokasi' => $log->nama_lokasi,
				'das' => $log->das,
				'waktu' => $dt ? $dt->waktu : '',
				'status' => $status,
				'warna' => $warna,
				'icon' => $log->icon,
				'status_aset' => 'BBWS Serayu Opak',
				'parameter' => $params,
			];
		}

		echo json_encode($kategori);
	}

	// ─── Monitoring (rekap 24 jam, horizontal) ─────────────────────────

	/**
	 * Mirror dari DPUPESDM Monitoring::index — return list 7 pos DIY
	 * dengan data per-jam (0–23) untuk satu tanggal. Diset id_kategori
	 * via input: 'awlr' → 3 pos AWLR DIY; 'arr'/'1'/'2' → 2 pos ARR DIY.
	 */
	public function monitoring()
	{
		$kategori = $this->input->get('id_kategori'); // 'awlr', 'arr', '1', '2', '8'
		$tanggal = $this->input->get('tanggal') ?: date('Y-m-d');

		// Normalisasi kategori → icon BBWS
		$icon = null;
		if (in_array($kategori, ['awlr', '8'], true)) {
			$icon = 'awlr';
		} elseif (in_array($kategori, ['arr', '1', '2'], true)) {
			$icon = 'arr';
		} else {
			echo json_encode(['data_rekap' => [], 'nama_logger' => '']);
			return;
		}

		$loggers = $this->db
			->select('t_logger.id_logger, t_logger.tabel_main, t_lokasi.nama_lokasi, kategori_logger.tabel as icon, kategori_logger.controller')
			->from('t_logger')
			->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
			->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
			->where_in('t_logger.id_logger', $this->diy_loggers)
			->where('kategori_logger.tabel', $icon)
			->order_by('t_logger.id_logger', 'ASC')
			->get()->result();

		$data_rekap = [];
		foreach ($loggers as $log) {
			$id_logger = $log->id_logger;
			$tabel_main = $log->tabel_main;

			if ($icon === 'awlr') {
				$sensor_field = 'sensor1';
				$select = "AVG($sensor_field) AS nilai";
				$tabel = $tabel_main; // awlr / awlr4
			} else {
				// ARR — pilih sensor9 kalau ada, fallback sensor8
				$has_s9 = $this->db->where('logger_id', $id_logger)
					->where('kolom_sensor', 'sensor9')
					->count_all_results('parameter_sensor');
				$sensor_field = $has_s9 ? 'sensor9' : 'sensor8';
				$select = "SUM($sensor_field) AS nilai";
				$tabel = $tabel_main;
			}

			$rows = $this->db->query(
				"SELECT HOUR(waktu) AS jam, $select FROM $tabel
				 WHERE code_logger=? AND waktu BETWEEN ? AND ?
				 GROUP BY HOUR(waktu), DAY(waktu), MONTH(waktu), YEAR(waktu)
				 ORDER BY waktu ASC",
				[$id_logger, $tanggal . ' 00:00', $tanggal . ' 23:59']
			)->result_array();

			$by_hour = [];
			foreach ($rows as $r) {
				$by_hour[(int) $r['jam']] = $r['nilai'];
			}

			$data24 = [];
			for ($i = 0; $i < 24; $i++) {
				$jam_str = $i > 9 ? (string) $i : ('0' . $i);
				if (isset($by_hour[$i]) && $by_hour[$i] !== null) {
					$v = (float) $by_hour[$i];
					$warna = 'white';
					if ($icon === 'arr') {
						if ($v < 0.1) $warna = 'white';
						elseif ($v < 1) $warna = '#70cddd';
						elseif ($v < 5) $warna = '#35549d';
						elseif ($v < 10) $warna = '#fef216';
						elseif ($v < 20) $warna = '#f47e2c';
						else $warna = '#ed1c24';
					}
					$data24[] = ['jam' => $jam_str, 'nilai' => number_format($v, 2), 'warna' => $warna];
				} else {
					$data24[] = ['jam' => $jam_str, 'nilai' => '-', 'warna' => 'white'];
				}
			}

			// Pilih param default sesuai kategori (TMA sensor1 untuk AWLR; sensor9/8 untuk ARR)
			$prefer_field = ($icon === 'awlr') ? 'sensor1' : 'sensor9';
			$param_pick = $this->db->where('logger_id', $id_logger)
				->where('kolom_sensor', $prefer_field)
				->limit(1)->get('parameter_sensor')->row();
			if (!$param_pick && $icon === 'arr') {
				$param_pick = $this->db->where('logger_id', $id_logger)
					->where('kolom_sensor', 'sensor8')
					->limit(1)->get('parameter_sensor')->row();
			}
			if (!$param_pick) {
				$param_pick = $this->db->where('logger_id', $id_logger)
					->order_by('id_param', 'ASC')->limit(1)
					->get('parameter_sensor')->row();
			}
			$id_param = $param_pick ? ($param_pick->id_param . '_bbws') : '';

			$data_rekap[] = [
				'id_logger' => $id_logger,
				'icon' => $icon,
				'nama_logger' => $log->nama_lokasi,
				'data' => $data24,
				'id_param' => $id_param,
				'tabel' => $tabel,
				'controller' => $log->controller,
				'status_aset' => 'BBWS Serayu Opak',
			];
		}

		echo json_encode([
			'data_rekap' => $data_rekap,
			'nama_logger' => $icon === 'awlr' ? 'Duga Air Sungai' : 'Curah Hujan',
		]);
	}

	// ─── Komparasi Series ──────────────────────────────────────────────

	/**
	 * Return series chart komparasi untuk SATU pos BBWS DIY pada SATU
	 * tanggal. Format sama dengan slot komparasi DPUPESDM (idLogger,
	 * namaSensor, satuan, tipe_grafik, data, nosensor, tooltip).
	 * - tipe=awlr → series TMA (avg sensor1)
	 * - tipe=arr  → series Curah Hujan akumulasi (sum sensor9/8)
	 */
	public function komparasi_series()
	{
		$id_logger = $this->input->get('id_logger');
		$tanggal = $this->input->get('tanggal') ?: date('Y-m-d');
		$tipe = $this->input->get('tipe'); // 'awlr' | 'arr'

		$this->_reject_non_diy($id_logger);

		$log = $this->db->select('t_logger.tabel_main, kategori_logger.tabel as icon')
			->from('t_logger')
			->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
			->where('t_logger.id_logger', $id_logger)
			->get()->row();
		if (!$log) {
			echo json_encode(null);
			return;
		}
		if (!$tipe) {
			$tipe = ($log->icon === 'awlr') ? 'awlr' : 'arr';
		}

		if ($tipe === 'awlr') {
			$kolom = 'sensor1';
			$nama_sensor = 'Rerata_Tinggi_Muka_Air';
			$select = "avg($kolom) AS $nama_sensor";
			$satuan = 'm';
			$tipe_grafik = 'spline';
		} else { // arr
			$has_s9 = $this->db->where('logger_id', $id_logger)
				->where('kolom_sensor', 'sensor9')
				->count_all_results('parameter_sensor');
			$kolom = $has_s9 ? 'sensor9' : 'sensor8';
			$nama_sensor = 'Akumulasi_Curah_Hujan';
			$select = "sum($kolom) AS $nama_sensor";
			$satuan = 'mm';
			$tipe_grafik = 'column';
		}

		$rows = $this->db->query(
			"SELECT HOUR(waktu) AS jam, DAY(waktu) AS hari, MONTH(waktu) AS bulan, YEAR(waktu) AS tahun, $select
			 FROM {$log->tabel_main}
			 WHERE code_logger=? AND waktu BETWEEN ? AND ?
			 GROUP BY HOUR(waktu), DAY(waktu), MONTH(waktu), YEAR(waktu)
			 ORDER BY waktu ASC",
			[$id_logger, $tanggal . ' 00:00', $tanggal . ' 23:59']
		)->result();

		$data = [];
		foreach ($rows as $r) {
			$v = (float) $r->$nama_sensor;
			$data[] = "[ Date.UTC($r->tahun," . ($r->bulan - 1) . ",$r->hari,$r->jam)," . number_format($v, 3, '.', '') . "]";
		}

		echo json_encode([
			'idLogger' => $id_logger,
			'namaSensor' => $nama_sensor,
			'satuan' => $satuan,
			'tipe_grafik' => $tipe_grafik,
			'data' => $data,
			'nosensor' => $kolom,
			'tooltip' => 'Waktu %d-%m-%Y %H:%M',
		]);
	}

	// ─── Mobile API (dipanggil oleh DPUPESDM Api.php publik) ───────────

	/**
	 * Return list 7 pos DIY dengan field lengkap untuk konsumen mobile API
	 * DPUPESDM (list_logger, daftar_pos, lokasi_new, pilih_pos*). Setiap
	 * pos sudah ter-resolve status koneksi, koordinat, alamat, kabupaten,
	 * kontak PIC. Filter optional via ?kategori=awlr|arr.
	 */
	public function api_list()
	{
		$kategori = $this->input->get('kategori');
		$awal = date('Y-m-d H:i', mktime(date('H') - 1, 0, 0, date('m'), date('d'), date('Y')));

		$this->db
			->select('t_logger.*, t_lokasi.nama_lokasi, t_lokasi.latitude, t_lokasi.longitude, t_lokasi.alamat, t_lokasi.das, kategori_logger.tabel as icon, kategori_logger.nama_kategori, kategori_logger.id_katlogger, kategori_logger.temp_data, kategori_logger.controller, t_informasi.nama_pic, t_informasi.no_pic, t_informasi.nosell')
			->from('t_logger')
			->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
			->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
			->join('t_informasi', 't_informasi.logger_id = t_logger.id_logger', 'left')
			->where_in('t_logger.id_logger', $this->diy_loggers)
			->order_by('t_logger.id_logger', 'ASC');
		if ($kategori) {
			$this->db->where('kategori_logger.tabel', $kategori);
		}
		$rows = $this->db->get()->result();

		// Mapping kategori untuk DPUPESDM
		$kat_map = ['awlr' => 'Duga Air Sungai', 'arr' => 'Curah Hujan', 'awr' => 'Stasiun Cuaca'];

		$out = [];
		foreach ($rows as $r) {
			$id_logger = $r->id_logger;
			// kabupaten = parse alamat sederhana (cari "Kabupaten X" atau "Kulon Progo, DIY")
			$kabupaten = '';
			if (preg_match('/Kabupaten\s+([^,]+)/i', $r->alamat, $m)) {
				$kabupaten = trim($m[1]);
			} elseif (preg_match('/,\s*([^,]+)\s*,\s*(DIY|Daerah\s+Istimewa\s+Yogyakarta)/i', $r->alamat, $m)) {
				$kabupaten = trim($m[1]);
			}

			$dt = $this->db->where('code_logger', $id_logger)->get($r->temp_data)->row();
			$waktu = $dt ? $dt->waktu : null;
			$koneksi = ($waktu && $waktu >= $awal) ? 'Terhubung' : 'Terputus';

			$out[] = [
				'id_logger' => $id_logger,
				'id_logger_full' => $id_logger . '_bbws',
				'nama_lokasi' => $r->nama_lokasi,
				'latitude' => $r->latitude,
				'longitude' => $r->longitude,
				'alamat' => $r->alamat,
				'kabupaten' => $kabupaten,
				'das' => $r->das,
				'icon' => $r->icon,
				'kategori' => $kat_map[$r->icon] ?? $r->nama_kategori,
				'katlog_id' => $r->id_katlogger,
				'controller' => ($r->icon === 'arr') ? 'curah_hujan' : $r->controller,
				'nama_pic' => $r->nama_pic ?? '',
				'no_pic' => $r->no_pic ?? '',
				'nomor_seluler' => $r->nosell ?? '',
				'waktu' => $waktu,
				'koneksi' => $koneksi,
				'status_aset' => 'BBWS Serayu Opak',
			];
		}
		echo json_encode($out);
	}

	/**
	 * Mobile-shape chart data per pos BBWS DIY. Output mirror dari
	 * DPUPESDM Api::data_range: array of rows
	 * { Waktu, <Param1>: "value satuan", <Param2>: ..., Koneksi (last row) }.
	 *
	 * Parameter: id_logger, awal (Y-m-d), akhir (Y-m-d), interval (menit|jam|hari)
	 */
	public function api_data_range()
	{
		$id_logger = $this->input->get('id_logger');
		$awal = $this->input->get('awal');
		$akhir = $this->input->get('akhir');
		$interval = $this->input->get('interval') ?: 'jam';

		$this->_reject_non_diy($id_logger);
		if (!$awal || !$akhir) {
			echo json_encode(['status' => false, 'error' => 'Parameter awal/akhir wajib']);
			return;
		}

		$log = $this->db->select('t_logger.tabel_main, kategori_logger.temp_data')
			->from('t_logger')
			->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
			->where('t_logger.id_logger', $id_logger)
			->get()->row();
		if (!$log) {
			echo json_encode([]);
			return;
		}

		$params = $this->db->where('logger_id', $id_logger)
			->order_by('CAST(SUBSTR(kolom_sensor,7) AS UNSIGNED)', '', false)
			->get('parameter_sensor')->result();
		$selects = [];
		foreach ($params as $p) {
			$alias = $p->nama_parameter;
			$field = $p->kolom_sensor;
			$sat = $p->satuan;
			if ($interval === 'menit') {
				$selects[] = "$field as `$alias`";
			} else {
				if ($sat === 'mm') {
					$selects[] = "CONCAT(FORMAT(SUM($field),2), ' $sat') as `$alias`";
				} else {
					$selects[] = "CONCAT(FORMAT(AVG($field),3), ' $sat') as `$alias`";
				}
			}
		}
		if (!$selects) {
			echo json_encode([]);
			return;
		}

		if ($interval === 'menit') {
			$sql = "SELECT waktu as Waktu, " . implode(',', $selects)
				. " FROM {$log->tabel_main} WHERE code_logger=? AND waktu BETWEEN ? AND ?";
			$rows = $this->db->query($sql, [$id_logger, $awal . ' 00:00', $akhir . ' 23:59'])->result_array();
		} else {
			$interval_num = ($interval === 'hari') ? 1440 : 60;
			$waktu_select = ($interval === 'hari')
				? "DATE_FORMAT(FROM_UNIXTIME(ROUND(UNIX_TIMESTAMP(waktu)/(5*60))*(5*60)), '%Y-%m-%d')"
				: "FROM_UNIXTIME(ROUND(UNIX_TIMESTAMP(waktu)/(5*60))*(5*60))";
			$sql = "SELECT $waktu_select AS Waktu, " . implode(',', $selects)
				. " FROM {$log->tabel_main} WHERE code_logger=? AND waktu BETWEEN ? AND ?"
				. " GROUP BY TIMESTAMPDIFF(MINUTE,'1970-01-01 00:00:00',waktu) DIV $interval_num";
			$rows = $this->db->query($sql, [$id_logger, $awal . ' 00:00', $akhir . ' 23:59'])->result_array();
		}

		// Status koneksi (logger sehat?)
		$dt = $this->db->where('code_logger', $id_logger)->get($log->temp_data)->row();
		$awal_kon = date('Y-m-d H:i', mktime(date('H') - 1, 0, 0, date('m'), date('d'), date('Y')));
		$koneksi = ($dt && $dt->waktu >= $awal_kon) ? 'Terhubung' : 'Terputus';
		if (count($rows) > 0) {
			$rows[0]['Koneksi'] = $koneksi;
		} else {
			$rows[] = ['Koneksi' => $koneksi];
		}

		echo json_encode($rows);
	}

	/**
	 * Data terakhir per logger (mobile API mirror of DPUPESDM Api::dtakhir).
	 * Return semua parameter logger dengan nilai terbarunya.
	 */
	public function api_dtakhir()
	{
		$id_logger = $this->input->get('idlogger') ?: $this->input->get('id_logger');
		$this->_reject_non_diy($id_logger);

		$log = $this->db
			->select('t_logger.tabel_main, kategori_logger.temp_data, t_lokasi.nama_lokasi')
			->from('t_logger')
			->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
			->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
			->where('t_logger.id_logger', $id_logger)
			->get()->row();
		if (!$log) {
			echo json_encode(['data_terakhir' => []]);
			return;
		}

		$dt = $this->db->where('code_logger', $id_logger)
			->order_by('waktu', 'desc')->limit(1)
			->get($log->temp_data)->row();
		if (!$dt) {
			$dt = $this->db->where('code_logger', $id_logger)
				->order_by('waktu', 'desc')->limit(1)
				->get($log->tabel_main)->row();
		}

		$perbaikan = $this->db->where('id_logger', $id_logger)->get('t_perbaikan')->row();

		$params = $this->db->query(
			"SELECT * FROM parameter_sensor WHERE logger_id = ? ORDER BY CAST(SUBSTR(kolom_sensor,7) AS UNSIGNED)",
			[$id_logger]
		)->result();

		$data_terakhir = [];
		foreach ($params as $p) {
			$kolom = $p->kolom_sensor;
			$h = ($dt && isset($dt->$kolom)) ? $dt->$kolom : 0;
			// Rating curve untuk Debit
			if ($p->nama_parameter === 'Debit') {
				$rc = $this->_hitung_debit_rating_curve($id_logger, (float) $h);
				if ($rc !== null) $h = max(0, $rc);
			} elseif ($p->nama_parameter === 'Debit_Aliran_Sungai' && $dt) {
				$diff = (float) ($dt->sensor1 ?? 0) - (float) ($dt->sensor2 ?? 0);
				$h = (float) $this->_debit_interpolation($diff);
			}
			$data_terakhir[] = [
				'idsensor' => $p->id_param . '_bbws',
				'sensor' => $p->nama_parameter,
				'data' => number_format((float) $h, 2, '.', ''),
				'satuan' => $p->satuan,
				'icon' => $p->nama_parameter,
				'tipe_graf' => $p->tipe_graf ?: ($p->satuan === 'mm' ? 'column' : 'spline'),
			];
		}

		$out = [
			'nama_logger' => $log->nama_lokasi,
			'waktu' => $dt ? $dt->waktu : '',
			'tabel' => $log->tabel_main,
			'status_aset' => 'BBWS Serayu Opak',
			'data_terakhir' => $data_terakhir,
		];
		if ($perbaikan) {
			$out['status'] = 'perbaikan';
		}
		echo json_encode($out);
	}

	private function _marker_state($icon, $perb, $waktu, $awal, $data_p)
	{
		$base = base_url() . 'pin_marker/';
		if ($icon === 'awlr') {
			if ($perb)
				return [$base . 'awlr-iri-coklat.png', 'Perbaikan', 'Perbaikan', ''];
			if ($waktu >= $awal)
				return [$base . 'awlr-iri-hijau.png', 'Koneksi Terhubung', 'Koneksi Terhubung', ''];
			return [$base . 'awlr-iri-hitam.png', 'Koneksi Terputus', 'Koneksi Terputus', 'google.maps.Animation.BOUNCE'];
		}
		$pref = $icon === 'arr' ? 'arr' : 'awr';
		if ($perb)
			return [$base . $pref . '_coklat.png', 'Perbaikan', 'Perbaikan', ''];
		if (!$waktu || $waktu < $awal)
			return [$base . $pref . '_hitam.png', 'Koneksi Terputus', 'Koneksi Terputus', 'google.maps.Animation.BOUNCE'];
		if ($data_p <= 0)
			return [$base . $pref . '_hijau.png', 'Koneksi Terhubung', 'Tidak Hujan', ''];
		if ($data_p < 1)
			return [$base . $pref . '_biru.png', 'Koneksi Terhubung', 'Hujan Sangat Ringan', ''];
		if ($data_p < 5)
			return [$base . $pref . '_nila.png', 'Koneksi Terhubung', 'Hujan Ringan', ''];
		if ($data_p < 10)
			return [$base . $pref . '_kuning.png', 'Koneksi Terhubung', 'Hujan Sedang', ''];
		if ($data_p < 20)
			return [$base . $pref . '_oranye.png', 'Koneksi Terhubung', 'Hujan Lebat', 'google.maps.Animation.BOUNCE'];
		return [$base . $pref . '_merah.png', 'Koneksi Terhubung', 'Hujan Sangat Lebat', 'google.maps.Animation.BOUNCE'];
	}
}
