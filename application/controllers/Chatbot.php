<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

class Chatbot extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('mlogin');
        $this->load->model('m_analisa');
    }

    // ─── Helper: read JSON body ───
    private function _json_input()
    {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    // ─── Helper: JSON response ───
    private function _json_response($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
    }

    // ─── Helper: cek koneksi (1 jam terakhir) ───
    private function _cek_koneksi($waktu)
    {
        $batas = date('Y-m-d H:i:s', strtotime('-1 hour'));
        return ($waktu >= $batas) ? 'On' : 'Off';
    }

    // ─── Helper: klasifikasi curah hujan PER JAM ───
    private function _klasifikasi_hujan_jam($mm)
    {
        if ($mm <= 0)
            return 'Berawan / Tidak Hujan';
        if ($mm < 1)
            return 'Hujan Sangat Ringan';
        if ($mm < 5)
            return 'Hujan Ringan';
        if ($mm < 10)
            return 'Hujan Sedang';
        if ($mm < 20)
            return 'Hujan Lebat';
        return 'Hujan Sangat Lebat';
    }

    // ─── Helper: klasifikasi curah hujan PER HARI ───
    private function _klasifikasi_hujan_harian($mm)
    {
        if ($mm <= 0)
            return 'Berawan / Tidak Hujan';
        if ($mm < 10)
            return 'Hujan Ringan';
        if ($mm < 20)
            return 'Hujan Ringan';
        if ($mm < 50)
            return 'Hujan Sedang';
        if ($mm < 100)
            return 'Hujan Lebat';
        return 'Hujan Sangat Lebat';
    }

    // ═══════════════════════════════════════════
    // 1) logger_list — daftar semua pos + status
    // ═══════════════════════════════════════════
    public function logger_list()
    {
        $input = $this->_json_input();
        $kategori = isset($input['kategori']) ? $input['kategori'] : 'all';

        $data = [];

        if ($kategori === 'all') {
            $query_kat = $this->db->query('SELECT * FROM kategori_logger WHERE view = 1');
        } else {
            $query_kat = $this->db->query("SELECT * FROM kategori_logger WHERE view = 1 AND id_katlogger = '$kategori'");
        }

        foreach ($query_kat->result() as $kat) {
            $tabel = $kat->tabel;
            $tabel_temp = $kat->temp_data;

            $query_logger = $this->db->query("
				SELECT * FROM t_logger 
				INNER JOIN t_lokasi ON t_logger.lokasi_logger = t_lokasi.idlokasi 
				WHERE kategori_log = '$kat->id_katlogger'
			");

            foreach ($query_logger->result() as $lok) {
                $id_logger = $lok->id_logger;
                $tabel_main = $lok->tabel_main;

                // cek perbaikan
                $is_perbaikan = $this->db->where('id_logger', $id_logger)->count_all_results('t_perbaikan') > 0;

                if ($is_perbaikan) {
                    $data[] = [
                        'id_logger' => $id_logger,
                        'nama_logger' => $lok->nama_logger,
                        'lokasi' => $lok->nama_lokasi,
                        'kategori' => $kat->nama_kategori,
                        'latitude' => $lok->latitude,
                        'longitude' => $lok->longitude,
                        'koneksi' => 'perbaikan',
                        'keterangan_koneksi' => 'Sedang Perbaikan',
                        'waktu_terakhir' => null
                    ];
                    continue;
                }

                // ambil data terakhir
                $temp = $this->db->where('code_logger', $id_logger)->get($tabel_temp)->row();
                $waktu = $temp ? $temp->waktu : null;

                $kn = $this->_cek_koneksi($waktu);
                $keter = ($kn === 'On') ? 'Koneksi Terhubung' : 'Koneksi Terputus';

                $item = [
                    'id_logger' => $id_logger,
                    'nama_logger' => $lok->nama_logger,
                    'lokasi' => $lok->nama_lokasi,
                    'kategori' => $kat->nama_kategori,
                    'latitude' => $lok->latitude,
                    'longitude' => $lok->longitude,
                    'koneksi' => $kn,
                    'keterangan_koneksi' => $keter,
                    'waktu_terakhir' => $waktu
                ];

                // untuk ARR / AWS, tambah klasifikasi hujan jam + harian
                if ($kn === 'On' && ($kat->controller == 'awr' || $kat->controller == 'arr')) {
                    $p_utama = $this->db->where('logger_id', $id_logger)->where('parameter_utama', '1')->get('parameter_sensor')->row();
                    if ($p_utama) {
                        $kolom = $p_utama->kolom_sensor;

                        // akumulasi per jam (jam ini)
                        $akum_jam = $this->db->query("SELECT SUM({$kolom}) as val FROM {$tabel_main} WHERE code_logger = '{$id_logger}' AND waktu >= '" . date('Y-m-d H') . ":00'")->row();
                        $val_jam = $akum_jam->val ?? 0;

                        // akumulasi per hari (hari ini)
                        $akum_hari = $this->db->query("SELECT SUM({$kolom}) as val FROM {$tabel_main} WHERE code_logger = '{$id_logger}' AND waktu >= '" . date('Y-m-d') . " 00:00'")->row();
                        $val_hari = $akum_hari->val ?? 0;

                        $item['curah_hujan_jam'] = [
                            'nilai' => number_format($val_jam, 2, '.', ''),
                            'satuan' => 'mm/jam',
                            'klasifikasi' => $this->_klasifikasi_hujan_jam($val_jam)
                        ];
                        $item['curah_hujan_harian'] = [
                            'nilai' => number_format($val_hari, 2, '.', ''),
                            'satuan' => 'mm/hari',
                            'klasifikasi' => $this->_klasifikasi_hujan_harian($val_hari)
                        ];

                        $keter = $this->_klasifikasi_hujan_jam($val_jam);
                    }
                }

                $item['keterangan_koneksi'] = $keter;
                $data[] = $item;
            }
        }

        // PSDA fallback — merge jika kategori tertentu
        if ($kategori === '2' || $kategori === 'all') {
            $psda = @json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/api/lokasi_baru?kategori_log=8&tabel=temp_awlr'), true);
            if (!empty($psda['lokasi'])) {
                foreach ($psda['lokasi'] as $p) {
                    $data[] = [
                        'id_logger' => $p['logger_id'] ?? $p['id_logger'] ?? '',
                        'nama_logger' => $p['nama_logger'] ?? '',
                        'lokasi' => $p['lokasi'] ?? $p['nama_lokasi'] ?? '',
                        'kategori' => 'AWLR (PSDA)',
                        'latitude' => $p['latitude'] ?? '',
                        'longitude' => $p['longitude'] ?? '',
                        'koneksi' => $p['status'] ?? $p['koneksi_log'] ?? '',
                        'keterangan_koneksi' => $p['koneksi'] ?? '',
                        'waktu_terakhir' => $p['waktu'] ?? null
                    ];
                }
            }
        }

        $this->_json_response([
            'status' => 'sukses',
            'total' => count($data),
            'data' => $data
        ]);
    }

    // ═══════════════════════════════════════════
    // 2) logger_detail — detail 1 logger
    // ═══════════════════════════════════════════
    public function logger_detail()
    {
        $input = $this->_json_input();
        $id_logger = isset($input['id_logger']) ? $input['id_logger'] : null;

        if (!$id_logger) {
            return $this->_json_response(['status' => 'error', 'message' => 'id_logger wajib diisi']);
        }

        $info = $this->db->where('logger_id', $id_logger)->get('t_informasi')->row();
        if (!$info) {
            return $this->_json_response(['status' => 'error', 'message' => 'Logger tidak ditemukan']);
        }

        // cek status SD & sensor
        $logger = $this->db->where('id_logger', $id_logger)->get('t_logger')->row();
        $kategori = $this->db->where('id_katlogger', $logger->kategori_log)->get('kategori_logger')->row();
        $status_sd = 'OK';
        $status_sensor = 'OK';

        if ($kategori) {
            $cek = $this->db->query("SELECT sensor13, sensor12 FROM {$kategori->temp_data} WHERE code_logger = '{$id_logger}' ORDER BY waktu DESC LIMIT 1")->row();
            if ($cek) {
                $status_sd = ($cek->sensor13 == '1') ? 'OK' : 'Terjadi Kesalahan';
                $status_sensor = ($cek->sensor12 == '1') ? 'OK' : 'Terjadi Kesalahan';
            }
        }

        $this->_json_response([
            'status' => 'sukses',
            'data' => [
                'id_logger' => $info->logger_id,
                'seri' => $info->seri_logger,
                'serial_number' => $info->serial_number,
                'sensor' => $info->sensor,
                'elevasi' => $info->elevasi ?? null,
                'awal_kontrak' => $info->awal_kontrak,
                'akhir_garansi' => $info->garansi,
                'logger_aktif' => $info->tanggal_pemasangan,
                'no_seluler' => $info->nosell,
                'imei' => $info->imei,
                'nama_pic' => $info->nama_pic,
                'no_pic' => $info->no_pic,
                'status_sd' => $status_sd,
                'status_sensor' => $status_sensor
            ]
        ]);
    }

    // ═══════════════════════════════════════════
    // 3) logger_parameter — parameter sensor 1 logger
    // ═══════════════════════════════════════════
    public function logger_parameter()
    {
        $input = $this->_json_input();
        $id_logger = isset($input['id_logger']) ? $input['id_logger'] : null;

        if (!$id_logger) {
            return $this->_json_response(['status' => 'error', 'message' => 'id_logger wajib diisi']);
        }

        $params = $this->db->where('logger_id', $id_logger)->get('parameter_sensor')->result();
        $data = [];
        foreach ($params as $p) {
            $data[] = [
                'id_param' => $p->id_param,
                'nama_parameter' => $p->nama_parameter,
                'kolom_sensor' => $p->kolom_sensor,
                'satuan' => $p->satuan,
                'tipe_graf' => $p->tipe_graf,
                'icon' => $p->icon_app
            ];
        }

        $this->_json_response([
            'status' => 'sukses',
            'data' => $data
        ]);
    }

    // ═══════════════════════════════════════════
    // 4) logger_koneksi — status online/offline
    // ═══════════════════════════════════════════
    public function logger_koneksi()
    {
        $input = $this->_json_input();
        $id_logger = isset($input['id_logger']) ? $input['id_logger'] : null;

        if (!$id_logger) {
            return $this->_json_response(['status' => 'error', 'message' => 'id_logger wajib diisi']);
        }

        $logger = $this->db
            ->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
            ->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
            ->where('t_logger.id_logger', $id_logger)
            ->get('t_logger')->row();

        if (!$logger) {
            return $this->_json_response(['status' => 'error', 'message' => 'Logger tidak ditemukan']);
        }

        $is_perbaikan = $this->db->where('id_logger', $id_logger)->count_all_results('t_perbaikan') > 0;

        if ($is_perbaikan) {
            return $this->_json_response([
                'status' => 'sukses',
                'data' => [
                    'id_logger' => $id_logger,
                    'nama_lokasi' => $logger->nama_lokasi,
                    'koneksi' => 'perbaikan',
                    'keterangan' => 'Sedang Perbaikan',
                    'waktu_terakhir' => null,
                    'is_perbaikan' => true
                ]
            ]);
        }

        $temp = $this->db->where('code_logger', $id_logger)->get($logger->temp_data)->row();
        $waktu = $temp ? $temp->waktu : null;
        $kn = $this->_cek_koneksi($waktu);

        $this->_json_response([
            'status' => 'sukses',
            'data' => [
                'id_logger' => $id_logger,
                'nama_lokasi' => $logger->nama_lokasi,
                'koneksi' => $kn,
                'keterangan' => ($kn === 'On') ? 'Koneksi Terhubung' : 'Koneksi Terputus',
                'waktu_terakhir' => $waktu,
                'is_perbaikan' => false
            ]
        ]);
    }

    // ═══════════════════════════════════════════
    // 5) data_realtime — data terbaru / live
    // ═══════════════════════════════════════════

    // --- helpers interpolasi (inlined dari Api.php) ---
    private function linear_interpolation($x)
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
        $x1 = $x2 = $y1 = $y2 = null;
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
        if (is_null($x1) || is_null($x2))
            return null;
        if ($x == $x1)
            return $y1;
        return $y1 + (($x - $x1) / ($x2 - $x1)) * ($y2 - $y1);
    }

    private function debit_interpolation($x)
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
        $x1 = $x2 = $y1 = $y2 = null;
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
        if (is_null($x1) || is_null($x2))
            return null;
        if ($x == $x1)
            return $y1;
        return $y1 + (($x - $x1) / ($x2 - $x1)) * ($y2 - $y1);
    }

    private function kalimeneng($tmaInput)
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
        if ($tmaInput < $data[0]['TMA'] || $tmaInput > $data[$n - 1]['TMA'])
            return 0;
        for ($i = 0; $i < $n - 1; $i++) {
            $tma1 = $data[$i]['TMA'];
            $tma2 = $data[$i + 1]['TMA'];
            if ($tmaInput >= $tma1 && $tmaInput <= $tma2) {
                $Cd = $data[$i]['Cd'] + (($tmaInput - $tma1) * ($data[$i + 1]['Cd'] - $data[$i]['Cd'])) / ($tma2 - $tma1);
                $Bef = $data[$i]['Bef'] + (($tmaInput - $tma1) * ($data[$i + 1]['Bef'] - $data[$i]['Bef'])) / ($tma2 - $tma1);
                return $K * $Cd * $Bef * pow($tmaInput, 1.5);
            }
        }
        return 0;
    }

    public function data_realtime()
    {
        $input = $this->_json_input();
        $id_logger = isset($input['id_logger']) ? $input['id_logger'] : null;
        $mode = isset($input['mode']) ? $input['mode'] : 'last';
        $id_sensor = isset($input['id_sensor']) ? $input['id_sensor'] : null;

        if (!$id_logger) {
            return $this->_json_response(['status' => 'error', 'message' => 'id_logger wajib diisi']);
        }

        $logger = $this->db
            ->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
            ->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
            ->where('t_logger.id_logger', $id_logger)
            ->get('t_logger')->row();

        // fallback PSDA jika logger tidak ada lokal
        if (!$logger) {
            $tabel_fallback = 'temp_awlr';
            $out = @json_decode(file_get_contents("https://dpupesdm.monitoring4system.com/api/dtakhir?idlogger={$id_logger}&tabel={$tabel_fallback}"), true);
            return $this->_json_response([
                'status' => $out ? 'sukses' : 'error',
                'data' => $out
            ]);
        }

        // === MODE: last ===
        if ($mode === 'last') {
            $is_perbaikan = $this->db->where('id_logger', $id_logger)->count_all_results('t_perbaikan') > 0;
            $qparam = $this->db->query("SELECT * FROM parameter_sensor WHERE logger_id = '{$id_logger}' ORDER BY CAST(SUBSTR(kolom_sensor,7) AS UNSIGNED)");
            $qdata = $this->db->query("SELECT * FROM {$logger->tabel_main} WHERE code_logger = '{$id_logger}' ORDER BY waktu DESC LIMIT 1")->row();
            $waktu = $qdata->waktu ?? null;

            $sensor_list = [];
            foreach ($qparam->result() as $s) {
                $h = $qdata->{$s->kolom_sensor} ?? null;

                if (!$is_perbaikan) {
                    if ($s->debit_awlr == '1' && $id_logger == '10063') {
                        $debit = $this->kalimeneng((float) $h);
                        $h = $h < 0 ? 0 : $debit;
                    } elseif ($s->nama_parameter == 'Debit' && $id_logger == '10249') {
                        $h = $this->linear_interpolation($qdata->sensor2 * 100) * $h;
                    } elseif ($s->nama_parameter == 'Luas_Penampang_Basah') {
                        $h = $this->linear_interpolation($qdata->sensor2 * 100);
                    } elseif ($s->nama_parameter == 'Debit_Aliran_Sungai') {
                        $h = $this->debit_interpolation(abs($qdata->sensor1 - $qdata->sensor2));
                    }
                }
                if ($s->nama_parameter != 'Wind_Direction')
                    $h = number_format($h, 2, '.', '');

                $sensor_list[] = [
                    'id_sensor' => $s->id_param,
                    'nama' => $s->nama_parameter,
                    'nilai' => $h,
                    'satuan' => $s->satuan,
                    'icon' => $s->icon_app,
                    'tipe_graf' => $s->tipe_graf
                ];
            }

            $koneksi = $this->_cek_koneksi($waktu);
            $out = [
                'nama_logger' => $logger->nama_lokasi,
                'waktu' => $waktu,
                'koneksi' => $koneksi,
                'sensor' => $sensor_list
            ];
            if ($is_perbaikan)
                $out['status_perangkat'] = 'perbaikan';

            return $this->_json_response(['status' => 'sukses', 'data' => $out]);
        }

        // === MODE: live ===
        if (!$id_sensor) {
            // ambil parameter utama sebagai default
            $p_utama = $this->db->where('logger_id', $id_logger)->where('parameter_utama', '1')->get('parameter_sensor')->row();
            $id_sensor = $p_utama ? $p_utama->id_param : null;
        }

        if (!$id_sensor) {
            return $this->_json_response(['status' => 'error', 'message' => 'id_sensor wajib diisi untuk mode live']);
        }

        $param1 = $this->db->where('logger_id', $id_logger)->where('id_param', $id_sensor)->get('parameter_sensor')->row();
        $kolom = $param1->kolom_sensor;

        $data_rows = $this->db->where('code_logger', $id_logger)->order_by('waktu', 'desc')->limit(25)->get($logger->tabel_main)->result_array();
        $temp_data = $this->db->where('code_logger', $id_logger)->get($logger->temp_data)->row();

        $data_tabel = [];
        foreach ($data_rows as $val) {
            $data_tabel[] = [
                'waktu' => $val['waktu'],
                'nilai' => $val[$kolom]
            ];
        }

        $waktu_terakhir = $temp_data->waktu ?? null;
        $koneksi = $this->_cek_koneksi($waktu_terakhir);

        $this->_json_response([
            'status' => 'sukses',
            'data' => [
                'nama_pos' => $logger->nama_lokasi,
                'nama_param' => $param1->nama_parameter,
                'koneksi' => $koneksi,
                'satuan' => $param1->satuan,
                'tipe_graf' => $param1->tipe_graf,
                'waktu_terakhir' => $waktu_terakhir,
                'data_tabel' => $data_tabel
            ]
        ]);
    }

    // ═══════════════════════════════════════════
    // 6) data_analisa — analisa historis
    // ═══════════════════════════════════════════
    public function data_analisa()
    {
        $input = $this->_json_input();
        $id_logger = isset($input['id_logger']) ? $input['id_logger'] : null;
        $id_sensor = isset($input['id_sensor']) ? $input['id_sensor'] : null;
        $granularity = isset($input['granularity']) ? $input['granularity'] : null;

        if (!$id_logger || !$id_sensor || !$granularity) {
            return $this->_json_response(['status' => 'error', 'message' => 'id_logger, id_sensor, dan granularity wajib diisi']);
        }

        $tb_main = $this->db->where('id_logger', $id_logger)->get('t_logger')->row();

        // ---------- fallback PSDA ----------
        if (!$tb_main) {
            return $this->_analisa_psda_fallback($id_sensor, $granularity, $input);
        }

        // ---------- parameter info ----------
        $param = $this->db->where('id_param', $id_sensor)->get('parameter_sensor')->row();
        if (!$param) {
            return $this->_json_response(['status' => 'error', 'message' => 'Sensor tidak ditemukan']);
        }

        $is_column = ($param->tipe_graf == 'column');
        $namaSensor = ($is_column ? 'Akumulasi_' : 'Rerata_') . $param->nama_parameter;
        $agg_func = $is_column ? 'sum' : 'avg';
        $select = "{$agg_func}({$param->kolom_sensor}) as {$namaSensor}";
        $sensor = $param->kolom_sensor;
        $satuan = $param->satuan;
        $namaparameter = $param->nama_parameter;
        $cek_rumus = ($param->debit_awlr == '1');

        // ---------- build query berdasarkan granularity ----------
        $where = "code_logger = '{$id_logger}'";
        $group = '';

        switch ($granularity) {
            case 'day':
                $tgl = $input['tanggal'] ?? date('Y-m-d');
                $where .= " AND waktu >= '{$tgl} 00:00' AND waktu <= '{$tgl} 23:59'";
                $group = 'GROUP BY HOUR(waktu), DAY(waktu), MONTH(waktu), YEAR(waktu)';
                $date_format = 'Y-m-d H';
                $date_suffix = ':00';
                break;
            case 'month':
                $bln = $input['bulan'] ?? date('Y-m');
                $where .= " AND waktu >= '{$bln}-01 00:00' AND waktu <= '{$bln}-31 23:59'";
                $group = 'GROUP BY DAY(waktu), MONTH(waktu), YEAR(waktu)';
                $date_format = 'Y-m-d';
                $date_suffix = '';
                break;
            case 'year':
                $thn = $input['tahun'] ?? date('Y');
                $where .= " AND waktu >= '{$thn}-01-01 00:00' AND waktu <= '{$thn}-12-31 23:59'";
                $group = 'GROUP BY MONTH(waktu), YEAR(waktu)';
                $date_format = 'Y-m';
                $date_suffix = '';
                break;
            case 'range':
                $start = $input['start'] ?? date('Y-m-d', strtotime('-7 days'));
                $end = $input['end'] ?? date('Y-m-d');
                $where .= " AND waktu >= '{$start}' AND waktu <= '{$end} 23:59:00'";
                $group = 'GROUP BY HOUR(waktu), DAY(waktu), MONTH(waktu), YEAR(waktu) ORDER BY waktu ASC';
                $date_format = 'Y-m-d H';
                $date_suffix = ':00';
                break;
            default:
                return $this->_json_response(['status' => 'error', 'message' => 'granularity harus day/month/year/range']);
        }

        $sql = "SELECT avg(sensor1 - sensor2) AS avg_diff, min(sensor1 - sensor2) AS min_diff, max(sensor1 - sensor2) AS max_diff, 
				avg(sensor2) as tma, min(sensor2) as tma_min, max(sensor2) as tma_max, 
				waktu, {$select}, min({$sensor}) as min, max({$sensor}) as max 
				FROM {$tb_main->tabel_main} WHERE {$where} {$group}";

        $query_data = $this->db->query($sql);
        $hsl = $query_data->result();

        $waktu_arr = [];
        $data_arr = [];
        $min_arr = [];
        $max_arr = [];

        foreach ($hsl as $datalog) {
            $h = $datalog->$namaSensor;
            $max_value = $datalog->max;
            $min_value = $datalog->min;

            // rumus debit
            if ($cek_rumus && $namaparameter == 'Debit') {
                $rumus = $this->db->where('id_logger', $id_logger)->get('rumus_debit')->row()->rumus;
                if ($h < 0) {
                    $h = 0;
                } else {
                    $debit_avg = eval ('return ' . $rumus . ';');
                    $h_bak = $h;
                    $h = $min_value;
                    $min_value = eval ('return ' . $rumus . ';');
                    $h = $max_value;
                    $max_value = eval ('return ' . $rumus . ';');
                    $h = $debit_avg;
                }
            }

            if ($namaparameter == 'Debit' && $id_logger == '10249') {
                if ($h >= 0) {
                    $n2 = $datalog->tma;
                    $h = number_format($this->linear_interpolation($n2 * 100) * $datalog->$namaSensor, 2, '.', '');
                    $max_value = number_format($this->linear_interpolation($datalog->tma_max * 100) * $datalog->max, 2, '.', '');
                    $min_value = number_format($this->linear_interpolation($datalog->tma_min * 100) * $datalog->min, 2, '.', '');
                } else {
                    $h = 0;
                }
            } elseif ($namaparameter == 'Luas_Penampang_Basah') {
                if ($h >= 0) {
                    $h = number_format($this->linear_interpolation($datalog->tma * 100), 2, '.', '');
                    $max_value = number_format($this->linear_interpolation($datalog->tma_max * 100), 2, '.', '');
                    $min_value = number_format($this->linear_interpolation($datalog->tma_min * 100), 2, '.', '');
                } else {
                    $h = 0;
                }
            } elseif ($namaparameter == 'Debit_Aliran_Sungai') {
                $h = $this->debit_interpolation(abs($datalog->avg_diff));
                $min_value = $this->debit_interpolation(abs($datalog->min_diff));
                $max_value = $this->debit_interpolation(abs($datalog->max_diff));
            }

            $waktu_arr[] = date($date_format, strtotime($datalog->waktu)) . $date_suffix;
            $data_arr[] = number_format($h, 2, '.', '');
            $min_arr[] = number_format($min_value, 2, '.', '');
            $max_arr[] = number_format($max_value, 2, '.', '');
        }

        if ($hsl) {
            $this->_json_response([
                'status' => 'sukses',
                'data' => [
                    'id_logger' => $id_logger,
                    'nama_sensor' => $namaSensor,
                    'satuan' => $satuan,
                    'tipe_graf' => $param->tipe_graf,
                    'waktu' => $waktu_arr,
                    'data' => $data_arr,
                    'data_min' => $min_arr,
                    'data_max' => $max_arr
                ]
            ]);
        } else {
            $this->_json_response(['status' => 'error', 'data' => null]);
        }
    }

    // helper PSDA fallback untuk analisa
    private function _analisa_psda_fallback($id_sensor, $granularity, $input)
    {
        $base = 'https://dpupesdm.monitoring4system.com/api/';
        switch ($granularity) {
            case 'day':
                $tgl = $input['tanggal'] ?? date('Y-m-d');
                $url = $base . "analisapertanggal2?idsensor={$id_sensor}&tanggal={$tgl}";
                break;
            case 'month':
                $bln = $input['bulan'] ?? date('Y-m');
                $url = $base . "analisaperbulan2?idsensor={$id_sensor}&tanggal={$bln}";
                break;
            case 'year':
                $thn = $input['tahun'] ?? date('Y');
                $url = $base . "analisapertahun2?idsensor={$id_sensor}&tahun={$thn}";
                break;
            case 'range':
                $start = $input['start'] ?? '';
                $end = $input['end'] ?? '';
                $url = $base . "analisaperrange2?idsensor={$id_sensor}&dari={$start}&sampai={$end}";
                break;
            default:
                return $this->_json_response(['status' => 'error', 'message' => 'granularity tidak valid']);
        }

        $data_psda = @json_decode(file_get_contents($url), true);
        if (!empty($data_psda['data'])) {
            $this->_json_response(['status' => 'sukses', 'data' => $data_psda]);
        } else {
            $this->_json_response(['status' => 'error', 'data' => null]);
        }
    }

    // ═══════════════════════════════════════════
    // 7) data_komparasi — bandingkan multi-logger
    // ═══════════════════════════════════════════
    public function data_komparasi()
    {
        $input = $this->_json_input();
        $loggers = isset($input['loggers']) ? $input['loggers'] : [];
        $tanggal = isset($input['tanggal']) ? $input['tanggal'] : date('Y-m-d');
        $export = isset($input['export']) ? $input['export'] : false;

        if (empty($loggers)) {
            return $this->_json_response(['status' => 'error', 'message' => 'loggers[] wajib diisi (minimal 1)']);
        }

        $selected = [];
        $is_single = count($loggers) === 1;

        foreach ($loggers as $logger_id) {
            $parts = explode('_', $logger_id);
            $id_logger_real = $parts[0];
            $aset = isset($parts[1]) ? $parts[1] : 'bbws';

            if ($aset === 'bbws') {
                $logger = $this->db
                    ->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
                    ->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
                    ->where('id_logger', $id_logger_real)
                    ->get('t_logger')->row();

                if (!$logger)
                    continue;

                $parameter = $this->db
                    ->where('logger_id', $id_logger_real)
                    ->where('parameter_utama', '1')
                    ->get('parameter_sensor')->row_array();

                if (!$parameter)
                    continue;

                $is_spline = $parameter['tipe_graf'] === 'spline';
                $agg_func = $is_spline ? 'avg' : 'sum';
                $sensor_name = ($is_spline ? 'Rerata_' : 'Akumulasi_') . $parameter['nama_parameter'];

                $query = $this->db->query("
					SELECT waktu, HOUR(waktu) as jam, DAY(waktu) as hari, MONTH(waktu) as bulan, YEAR(waktu) as tahun,
						{$agg_func}({$parameter['kolom_sensor']}) as {$sensor_name}
					FROM {$logger->tabel_main}
					WHERE code_logger = '{$id_logger_real}'
						AND waktu BETWEEN '{$tanggal} 00:00' AND '{$tanggal} 23:59'
					GROUP BY YEAR(waktu), MONTH(waktu), DAY(waktu), HOUR(waktu)
					ORDER BY waktu ASC
				");

                $data_nilai = [];
                foreach ($query->result() as $row) {
                    $val = number_format($row->$sensor_name, 3, '.', '');
                    $data_nilai[] = [
                        'nilai' => $val,
                        'waktu' => $row->waktu,
                        'jam' => (int) $row->jam
                    ];
                }

                // isi jam kosong
                for ($i = 0; $i < 24; $i++) {
                    if (array_search($i, array_column($data_nilai, 'jam')) === false) {
                        $jam = str_pad($i, 2, '0', STR_PAD_LEFT);
                        $data_nilai[] = [
                            'nilai' => '-',
                            'waktu' => "{$tanggal} {$jam}:00:00",
                            'jam' => $i
                        ];
                    }
                }
                array_multisort(array_column($data_nilai, 'waktu'), SORT_ASC, $data_nilai);

                $selected[] = [
                    'id_logger' => $logger_id,
                    'nama_lokasi' => $logger->nama_lokasi,
                    'nama_kategori' => $logger->nama_kategori,
                    'nama_chart' => $sensor_name,
                    'satuan' => $parameter['satuan'],
                    'tipe_graf' => $parameter['tipe_graf'],
                    'data_nilai' => $data_nilai
                ];

            } else {
                // PSDA via integrasi
                $curl = curl_init();
                $kirim = ['id_logger' => $id_logger_real, 'tanggal' => $tanggal, 'session_logger' => json_encode($loggers)];
                curl_setopt_array($curl, [
                    CURLOPT_URL => 'https://dpupesdm.monitoring4system.com/integrasi/komparasi',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $kirim,
                ]);
                $response = curl_exec($curl);
                curl_close($curl);
                $hasil = json_decode($response, true);
                if ($hasil)
                    $selected[] = $hasil;
            }
        }

        // chart name logic
        $kategori_list = array_column($selected, 'nama_kategori');
        $has_rain = in_array('AWS', $kategori_list) || in_array('ARR', $kategori_list);
        $has_awlr = in_array('AWLR', $kategori_list);

        if ($has_rain && !$has_awlr) {
            $chart_name = 'Akumulasi Curah Hujan';
        } elseif ($has_rain && $has_awlr) {
            $chart_name = 'Akumulasi Curah Hujan dan Rerata Tinggi Muka Air';
        } else {
            $chart_name = 'Rerata Tinggi Muka Air';
        }

        $this->_json_response([
            'status' => 'sukses',
            'chart_name' => $chart_name,
            'tanggal' => $tanggal,
            'data' => $selected
        ]);
    }
}
