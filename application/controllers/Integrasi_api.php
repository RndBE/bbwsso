<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * API Integrasi BBWS Serayu Opak.
 *
 * Tiga endpoint untuk pihak luar menarik data logger, bentuknya mengikuti
 * API Sisda di be_jastir2 supaya konsumen yang sudah terintegrasi ke sana
 * tidak perlu menulis parser baru.
 *
 * Sengaja TERPISAH dari controller Integrasi (/integrasi/*). Yang itu
 * melayani DPUPESDM DIY tanpa autentikasi dan dipanggil terus-menerus dari
 * sisi mereka; menambahkan auth ke sana akan memutus integrasi berjalan.
 *
 * Dokumentasi: /integrasi_api/docs (Swagger UI) · /integrasi_api/openapi (JSON)
 */
class Integrasi_api extends CI_Controller
{
	/** Maksimal baris riwayat pada endpoint satu logger. */
	const MAKS_RIWAYAT = 300;

	/** Batas rentang tanggal, dalam hari. */
	const MAKS_HARI = 31;

	/** Batas rentang untuk interval mentah per-menit. */
	const MAKS_HARI_MENIT = 2;

	/**
	 * Batas keras baris untuk interval mentah. Logger merekam tiap 10–30
	 * detik, jadi dua hari saja bisa belasan ribu baris dikali jumlah
	 * parameter — cukup untuk menghabiskan memori PHP dalam satu permintaan.
	 */
	const MAKS_BARIS_MENIT = 20000;

	/** Basis URL foto pos. */
	const BASIS_FOTO = 'https://bbws.beacontelemetry.com/image/foto_pos/';

	public function __construct()
	{
		parent::__construct();
		$this->load->library('basic_auth');
		header('Access-Control-Allow-Origin: *');
	}

	// ═══════════════════════════════════════════════════════════
	//  ENDPOINT
	// ═══════════════════════════════════════════════════════════

	/**
	 * GET /integrasi_api?id_logger=10048
	 * Riwayat terbaru satu logger (maksimal 300 titik).
	 */
	public function index()
	{
		if (!$this->basic_auth->authenticate()) {
			return;
		}

		$id_logger = $this->input->get('id_logger');
		if (!$id_logger) {
			return $this->_json(['status' => false, 'pesan' => 'Parameter id_logger wajib diisi'], 400);
		}

		$logger = $this->_logger($id_logger);
		if (!$logger) {
			return $this->_json(['status' => false, 'pesan' => 'Logger tidak terdaftar']);
		}

		$params = $this->_parameter($id_logger);
		if (!$params) {
			return $this->_json(['status' => false, 'pesan' => 'Logger belum punya parameter sensor']);
		}

		$kolom = [];
		foreach ($params as $p) {
			$kolom[] = "`{$p->kolom_sensor}`";
		}
		$sql = 'SELECT waktu, ' . implode(', ', $kolom)
			. " FROM `{$logger->tabel_main}` WHERE code_logger = ?"
			. ' ORDER BY waktu DESC LIMIT ' . self::MAKS_RIWAYAT;
		$rows = $this->db->query($sql, [$id_logger])->result();

		$this->_json([
			'status' => true,
			'foto' => $this->_foto($id_logger),
			'nama_lokasi' => $logger->nama_lokasi,
			'koneksi_logger' => $this->_koneksi($logger),
			'latitude' => $logger->latitude,
			'longitude' => $logger->longitude,
			'jenis' => $logger->nama_kategori,
			'jumlah_data' => count($rows),
			'data' => $this->_titik_riwayat($rows, $params),
		]);
	}

	/**
	 * GET /integrasi_api/all_logger
	 * Snapshot terbaru seluruh logger pada kategori yang aktif.
	 */
	public function all_logger()
	{
		if (!$this->basic_auth->authenticate()) {
			return;
		}

		$loggers = $this->db
			->select('t_logger.id_logger, t_logger.tabel_main, t_lokasi.nama_lokasi, t_lokasi.latitude,
			          t_lokasi.longitude, t_lokasi.das, kategori_logger.nama_kategori, kategori_logger.temp_data')
			->from('t_logger')
			->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
			->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
			->where('kategori_logger.view', 1)
			->order_by('t_lokasi.nama_lokasi', 'ASC')
			->get()->result();

		// Satu query per tabel temp, bukan per logger.
		$temp = [];
		foreach ($loggers as $l) {
			if ($this->_nama_aman($l->temp_data)) {
				$temp[$l->temp_data] = [];
			}
		}
		foreach (array_keys($temp) as $t) {
			foreach ($this->db->get($t)->result() as $r) {
				$temp[$t][$r->code_logger] = $r;
			}
		}

		$ids = [];
		foreach ($loggers as $l) {
			$ids[] = $l->id_logger;
		}
		$param_map = [];
		if ($ids) {
			$q = $this->db->where_in('logger_id', $ids)
				->order_by('CAST(SUBSTR(kolom_sensor,7) AS UNSIGNED)', '', false)
				->get('parameter_sensor')->result();
			foreach ($q as $p) {
				$param_map[$p->logger_id][] = $p;
			}
		}
		$foto_map = $this->_foto_semua();

		$out = [];
		foreach ($loggers as $l) {
			$baris = isset($temp[$l->temp_data][$l->id_logger]) ? $temp[$l->temp_data][$l->id_logger] : null;
			$waktu = $baris ? $baris->waktu : null;

			$nilai = [];
			foreach (isset($param_map[$l->id_logger]) ? $param_map[$l->id_logger] : [] as $p) {
				$k = $p->kolom_sensor;
				$nilai[] = [
					'nama_parameter' => $p->nama_parameter,
					'satuan' => $p->satuan,
					'nilai' => ($baris && isset($baris->$k)) ? $baris->$k : null,
				];
			}

			$out[] = [
				'id_logger' => $l->id_logger,
				'foto' => isset($foto_map[$l->id_logger]) ? $foto_map[$l->id_logger] : null,
				'nama_lokasi' => $l->nama_lokasi,
				'das' => $l->das,
				'waktu' => $waktu,
				'koneksi_logger' => $this->_koneksi_dari_waktu($waktu),
				'latitude' => $l->latitude,
				'longitude' => $l->longitude,
				'jenis' => $l->nama_kategori,
				'data' => $nilai,
			];
		}

		$this->_json([
			'status' => true,
			'jumlah_logger' => count($out),
			'data' => $out,
		]);
	}

	/**
	 * GET /integrasi_api/range_tanggal?id_logger=&awal=&akhir=&interval=
	 *
	 * Logger di sistem ini merekam tiap 10–30 detik, jadi sehari saja sudah
	 * ribuan baris. Karena itu keluaran diringkas per jam secara bawaan;
	 * interval `menit` mengembalikan data mentah dan dibatasi rentang pendek.
	 */
	public function range_tanggal()
	{
		if (!$this->basic_auth->authenticate()) {
			return;
		}

		$id_logger = $this->input->get('id_logger');
		$awal = $this->input->get('awal');
		$akhir = $this->input->get('akhir');
		$interval = strtolower(trim((string) $this->input->get('interval'))) ?: 'jam';

		if (!$id_logger || !$awal || !$akhir) {
			return $this->_json(['status' => false, 'pesan' => 'Parameter id_logger, awal, dan akhir wajib diisi'], 400);
		}
		if (!in_array($interval, ['menit', 'jam', 'hari'], true)) {
			return $this->_json(['status' => false, 'pesan' => 'interval harus menit, jam, atau hari'], 400);
		}
		if (!$this->_tanggal_sah($awal) || !$this->_tanggal_sah($akhir)) {
			return $this->_json(['status' => false, 'pesan' => 'Format tanggal harus YYYY-MM-DD'], 400);
		}
		if (strtotime($awal) > strtotime($akhir)) {
			list($awal, $akhir) = [$akhir, $awal];
		}

		$hari = (strtotime($akhir) - strtotime($awal)) / 86400 + 1;
		$batas = ($interval === 'menit') ? self::MAKS_HARI_MENIT : self::MAKS_HARI;
		if ($hari > $batas) {
			return $this->_json([
				'status' => false,
				'pesan' => "Rentang maksimal {$batas} hari untuk interval {$interval}",
			], 400);
		}

		$logger = $this->_logger($id_logger);
		if (!$logger) {
			return $this->_json(['status' => false, 'pesan' => 'Logger tidak terdaftar']);
		}
		$params = $this->_parameter($id_logger);
		if (!$params) {
			return $this->_json(['status' => false, 'pesan' => 'Logger belum punya parameter sensor']);
		}

		$rows = $this->_data_rentang($logger, $params, $id_logger, $awal, $akhir, $interval);
		$terpotong = ($interval === 'menit' && count($rows) >= self::MAKS_BARIS_MENIT);

		$this->_json([
			'status' => true,
			'nama_lokasi' => $logger->nama_lokasi,
			'koneksi_logger' => $this->_koneksi($logger),
			'latitude' => $logger->latitude,
			'longitude' => $logger->longitude,
			'jenis' => $logger->nama_kategori,
			'interval' => $interval,
			'awal' => $awal,
			'akhir' => $akhir,
			'jumlah_data' => count($rows),
			'terpotong' => $terpotong,
			'data' => $rows,
		]);
	}

	// ═══════════════════════════════════════════════════════════
	//  DOKUMENTASI
	// ═══════════════════════════════════════════════════════════

	/** Halaman Swagger UI. Publik — yang butuh kredensial hanya endpointnya. */
	public function docs()
	{
		$this->load->view('integrasi_api/docs');
	}

	/** Berkas OpenAPI mentah. */
	public function openapi()
	{
		$path = APPPATH . 'docs/bbwsso-openapi.json';
		if (!is_file($path)) {
			return $this->_json(['status' => false, 'pesan' => 'Berkas OpenAPI tidak ditemukan'], 404);
		}
		$this->output
			->set_content_type('application/json')
			->set_output(file_get_contents($path));
	}

	// ═══════════════════════════════════════════════════════════
	//  PEMBANTU
	// ═══════════════════════════════════════════════════════════

	private function _json($data, $kode = 200)
	{
		$this->output
			->set_status_header($kode)
			->set_content_type('application/json')
			->set_output(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
	}

	/** Nama tabel/kolom dari DB tetap divalidasi sebelum masuk SQL mentah. */
	private function _nama_aman($nama)
	{
		return (is_string($nama) && preg_match('/^[A-Za-z0-9_]{1,64}$/', $nama)) ? $nama : null;
	}

	private function _tanggal_sah($t)
	{
		$d = DateTime::createFromFormat('Y-m-d', (string) $t);
		return $d && $d->format('Y-m-d') === $t;
	}

	private function _logger($id_logger)
	{
		$row = $this->db
			->select('t_logger.id_logger, t_logger.tabel_main, t_lokasi.nama_lokasi, t_lokasi.latitude,
			          t_lokasi.longitude, t_lokasi.das, kategori_logger.nama_kategori,
			          kategori_logger.temp_data, kategori_logger.controller')
			->from('t_logger')
			->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
			->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
			->where('t_logger.id_logger', $id_logger)
			->where('kategori_logger.view', 1)
			->get()->row();

		return ($row && $this->_nama_aman($row->tabel_main) && $this->_nama_aman($row->temp_data)) ? $row : null;
	}

	private function _parameter($id_logger)
	{
		$rows = $this->db->where('logger_id', $id_logger)
			->order_by('CAST(SUBSTR(kolom_sensor,7) AS UNSIGNED)', '', false)
			->get('parameter_sensor')->result();

		$out = [];
		foreach ($rows as $r) {
			if ($this->_nama_aman($r->kolom_sensor)) {
				$out[] = $r;
			}
		}
		return $out;
	}

	/** Aturan koneksi sama dengan seluruh sistem: data terakhir < 1 jam. */
	private function _koneksi_dari_waktu($waktu)
	{
		return ($waktu && $waktu >= date('Y-m-d H:i:s', strtotime('-1 hour'))) ? 'Terhubung' : 'Terputus';
	}

	private function _koneksi($logger)
	{
		$row = $this->db->where('code_logger', $logger->id_logger)->get($logger->temp_data)->row();
		return $this->_koneksi_dari_waktu($row ? $row->waktu : null);
	}

	private function _foto($id_logger)
	{
		$row = $this->db->where('id_logger', $id_logger)->get('foto_pos')->row();
		return ($row && !empty($row->url_foto)) ? self::BASIS_FOTO . $row->url_foto : null;
	}

	private function _foto_semua()
	{
		$out = [];
		foreach ($this->db->get('foto_pos')->result() as $r) {
			if (!isset($out[$r->id_logger]) && !empty($r->url_foto)) {
				$out[$r->id_logger] = self::BASIS_FOTO . $r->url_foto;
			}
		}
		return $out;
	}

	/** Satu titik riwayat = waktu + nilai seluruh parameter. */
	private function _titik_riwayat($rows, $params)
	{
		$out = [];
		foreach ($rows as $r) {
			$nilai = [];
			foreach ($params as $p) {
				$k = $p->kolom_sensor;
				$nilai[] = [
					'nama_parameter' => $p->nama_parameter,
					'satuan' => $p->satuan,
					'nilai' => isset($r->$k) ? $r->$k : null,
				];
			}
			$out[] = ['waktu' => $r->waktu, 'data' => $nilai];
		}
		return $out;
	}

	/**
	 * Parameter kumulatif (tipe_graf = column, mis. curah hujan) diakumulasi;
	 * sisanya dirata-rata. Aturan yang sama dipakai halaman analisa.
	 */
	private function _data_rentang($logger, $params, $id_logger, $awal, $akhir, $interval)
	{
		$select = [];
		foreach ($params as $p) {
			$k = $p->kolom_sensor;
			$alias = 'p_' . $k;
			if ($interval === 'menit') {
				$select[] = "`{$k}` AS `{$alias}`";
			} else {
				$fn = ($p->tipe_graf === 'column') ? 'SUM' : 'AVG';
				$select[] = "{$fn}(`{$k}`) AS `{$alias}`";
			}
		}

		if ($interval === 'menit') {
			$sql = 'SELECT waktu, ' . implode(', ', $select)
				. " FROM `{$logger->tabel_main}` WHERE code_logger = ? AND waktu BETWEEN ? AND ?"
				. ' ORDER BY waktu ASC LIMIT ' . self::MAKS_BARIS_MENIT;
		} else {
			$fmt = ($interval === 'hari') ? "'%Y-%m-%d 00:00:00'" : "'%Y-%m-%d %H:00:00'";
			$sql = "SELECT DATE_FORMAT(waktu, {$fmt}) AS waktu, " . implode(', ', $select)
				. " FROM `{$logger->tabel_main}` WHERE code_logger = ? AND waktu BETWEEN ? AND ?"
				. " GROUP BY DATE_FORMAT(waktu, {$fmt}) ORDER BY waktu ASC";
		}

		$rows = $this->db->query($sql, [$id_logger, $awal . ' 00:00:00', $akhir . ' 23:59:59'])->result();

		$out = [];
		foreach ($rows as $r) {
			$nilai = [];
			foreach ($params as $p) {
				$alias = 'p_' . $p->kolom_sensor;
				$v = isset($r->$alias) ? $r->$alias : null;
				$nilai[] = [
					'nama_parameter' => $p->nama_parameter,
					'satuan' => $p->satuan,
					'nilai' => is_numeric($v) ? round((float) $v, 3) : $v,
				];
			}
			$out[] = ['waktu' => $r->waktu, 'data' => $nilai];
		}
		return $out;
	}
}
