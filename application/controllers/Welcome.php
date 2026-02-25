<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	function get_data(){
		$this->load->view('konten/back/fetch_data');
	}
	public function stream_test()
	{
		header('Content-Type: application/x-ndjson; charset=utf-8');
		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('Connection: keep-alive');
		header('X-Accel-Buffering: no'); // jika di belakang Nginx

		@ini_set('output_buffering', 'off');
		@ini_set('zlib.output_compression', '0');
		@ini_set('implicit_flush', '1');
		@ini_set('default_socket_timeout', '0');
		while (ob_get_level() > 0) { @ob_end_flush(); }
		ob_implicit_flush(true);
		set_time_limit(0);
		ignore_user_abort(false);

		// padding supaya proxy “buka” body
		echo str_repeat(" ", 2048) . "\n"; @ob_flush(); flush();

		for ($i=1; $i<=10; $i++) {
			echo json_encode(['_event'=>'tick','i'=>$i,'ts'=>gmdate('c')]) . "\n";
			@ob_flush(); flush();
			sleep(1);
		}
	}
	private $backend = 'http://31.58.158.182:2121/fetch_progress';

    public function fetch_progress()
    {
        // --- Param ---
        $id_logger = $this->input->get('id_logger', true);
        $awal      = $this->input->get('awal', true);
        $akhir     = $this->input->get('akhir', true);
        if (!$id_logger || !$awal || !$akhir) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error'=>'param wajib: id_logger, awal, akhir']);
            return;
        }

        // --- TUTUP SESSION LEBIH DULU (sangat penting) ---
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
        }

        // --- Header streaming ---
        header('Content-Type: application/x-ndjson; charset=utf-8');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');   // Nginx: matikan buffering
        // Jangan set Content-Length agar chunked auto-dipakai.

        // --- Matikan segala buffering & kompresi ---
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', '0');
        @ini_set('implicit_flush', '1');
        @ini_set('default_socket_timeout', '0');
        while (ob_get_level() > 0) { @ob_end_clean(); }  // bersihkan buffer, bukan flush
        ob_implicit_flush(true);
        set_time_limit(0);
        ignore_user_abort(false);
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }

        // --- Padding + start cepat supaya koneksi dianggap aktif ---
        echo str_repeat(" ", 2048) . "\n";  // padding membuka chunking di beberapa proxy
        echo json_encode([
            '_event'    => 'proxy_start',
            'id_logger' => $id_logger,
            'awal'      => $awal,
            'akhir'     => $akhir,
            'ts'        => gmdate('c'),
        ]) . "\n";
        @ob_flush(); flush();

        // --- Siapkan URL upstream ---
        $qs  = http_build_query(['id_logger'=>$id_logger,'awal'=>$awal,'akhir'=>$akhir]);
        $url = $this->backend.'?'.$qs;

        // --- cURL streaming ---
        $ch   = curl_init();
        $last = time();

        // OPTIONAL: log verbose ke /tmp untuk debug cepat
        $vlog = @fopen(sys_get_temp_dir().'/ci3_curl_verbose.log', 'a');

        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_HTTPGET        => true,
            CURLOPT_RETURNTRANSFER => false,           // stream, bukan buffer
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 0,               // no overall timeout
            CURLOPT_TCP_KEEPALIVE  => 1,
            CURLOPT_BUFFERSIZE     => 1024,            // kirim chunk kecil-kecil
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1, // paksa h2h chunked
            CURLOPT_HTTPHEADER     => [
                'Accept: application/x-ndjson',
                'User-Agent: ci3-revproxy/1.3',
                'Expect:'                                    // hindari 100-continue
            ],
            CURLOPT_VERBOSE        => (bool)$vlog,
            CURLOPT_STDERR         => $vlog ?: null,
            CURLOPT_WRITEFUNCTION  => function($ch, $data) use (&$last) {
                echo $data;                 // relay ke klien
                @ob_flush(); flush();       // flush paksa
                $last = time();
                if (connection_aborted()) { // klien tutup: hentikan upstream
                    return 0;
                }
                return strlen($data);
            },
        ]);

        // Multi-loop + heartbeat (keep-alive di jalur PHP/Nginx/fastcgi)
        $mh = curl_multi_init();
        curl_multi_add_handle($mh, $ch);
        do {
            $status = curl_multi_exec($mh, $active);
            if ($status > 0) break;

            // Heartbeat tiap 12 detik
            if (time() - $last >= 12) {
                echo json_encode(['_event'=>'proxy_heartbeat','ts'=>gmdate('c')]) . "\n";
                @ob_flush(); flush();
                $last = time();
            }

            if (connection_aborted()) {
                curl_multi_remove_handle($mh,$ch);
                curl_multi_close($mh);
                if ($vlog) fclose($vlog);
                return;
            }

            curl_multi_select($mh, 1.0); // tunggu IO s/d 1s
        } while ($active);

        // Finishing
        $err  = curl_error($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_multi_remove_handle($mh,$ch);
        curl_multi_close($mh);
        curl_close($ch);
        if ($vlog) fclose($vlog);

        if ($err) {
            echo json_encode(['_event'=>'proxy_error','error'=>$err,'ts'=>gmdate('c')]) . "\n";
        } elseif ($code >= 400) {
            echo json_encode(['_event'=>'proxy_upstream_http_error','code'=>$code,'ts'=>gmdate('c')]) . "\n";
        } else {
            echo json_encode(['_event'=>'proxy_end','ts'=>gmdate('c')]) . "\n";
        }
        @ob_flush(); flush();
        // Jangan pakai $this->output di bawah; akhiri saja.
    }


	public function index()
	{
		$data = $this->db->join('t_lokasi', 't_logger.lokasi_logger=t_lokasi.idlokasi')->join('kategori_logger', 't_logger.kategori_log=kategori_logger.id_katlogger')->where('t_logger.id_logger != ','10344')->get('t_logger')->result_array();
		$date_now = date('Y-m-d H:i:s');
		$date = date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($date_now)));
		foreach($data as $key => $val){
			$data[$key]['sumber'] = 'BBWS SO';
			$data[$key]['status'] = 'aktif';
			$waktu = $this->db->get_where($val['temp_data'], array('code_logger'=> $val['id_logger']))->row();
			$data[$key]['waktu'] = $waktu->waktu;
			if($waktu->waktu < $date ){
				$data[$key]['status'] = 'nonaktif';
			}

		}
		echo json_encode($data);
	}

	function tes_search(){

		$this->load->view('konten/back/tes_search');
	}
	public function tes_kirim () {

		$data = [
			"to" => '/topics/das_serang',
			"notification" => [
				"title" => 'AWGC Sedang Digunakan',
				"body" => "sedang beroperasi",
				'channelId' => 'bbws-so',
			],
		];

		$ch = curl_init();  // initialize curl handle
		$url = 'https://fcm.googleapis.com/fcm/send';
		curl_setopt($ch, CURLOPT_URL, $url); // set url to post to
		//curl_setopt($ch, CURLOPT_FAILonerror, 1); //Fail on error'=
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Authorization: key=AAAA-KIUWcg:APA91bE1gcaBhCHVo4xLTpJy6WIyZszoDtHH8lN7e8lICOubx6uNM2GdcVzNeBFB26FDBY174TR0W357ZaAynotHsQj3agxhx2j4D_wch7OuQIp4bqU2QAcD3uOGPz_8T4Ry7cGCwLMQ',
			'Content-Type: application/json'
		));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
		curl_setopt($ch, CURLOPT_POST, 1); // set POST method
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // add POST fields
		$result = curl_exec($ch); // run the whole process
		curl_close($ch);
	}

	public function coba() {
		$t_logger = array(
			array('seri' => 'WR-1000','nama_logger' => 'Klimatologi','id_logger' => '10007','nosell' => '+628112828306'),
			array('seri' => 'WLR-110','nama_logger' => 'AWLR Karang Talun','id_logger' => '10044','nosell' => '+628112828263'),
			array('seri' => 'RR-110','nama_logger' => 'ARR Soropadan','id_logger' => '10045','nosell' => '+628112828305'),
			array('seri' => '','nama_logger' => 'ARR Surotrunan','id_logger' => '10048','nosell' => '+628112863981'),
			array('seri' => '','nama_logger' => 'ARR Sindut','id_logger' => '10049','nosell' => '+628112863982'),
			array('seri' => '','nama_logger' => 'ARR Adimulyo','id_logger' => '10050','nosell' => '+628112863980'),
			array('seri' => '','nama_logger' => 'AWLR Butuh','id_logger' => '10051','nosell' => '+628112838193'),
			array('seri' => '','nama_logger' => 'AWLR Gombong','id_logger' => '10052','nosell' => '+628112863987'),
			array('seri' => '','nama_logger' => 'ARR Slinga','id_logger' => '10053','nosell' => '+628112863986'),
			array('seri' => '','nama_logger' => 'ARR Tajum','id_logger' => '10054','nosell' => '+628112863988'),
			array('seri' => '','nama_logger' => 'ARR Kaligending','id_logger' => '10055','nosell' => '+628112863985'),
			array('seri' => '','nama_logger' => 'ARR Bener','id_logger' => '10056','nosell' => '+628112863984'),
			array('seri' => '','nama_logger' => 'AWLR Rebug','id_logger' => '10062','nosell' => '+62112838195'),
			array('seri' => '','nama_logger' => 'AWLR Kali Meneng','id_logger' => '10063','nosell' => '+628112838194'),
			array('seri' => '','nama_logger' => 'AWLR Kedung Gupit','id_logger' => '10064','nosell' => '+628112849257'),
			array('seri' => '','nama_logger' => 'AWLR Merden','id_logger' => '10065','nosell' => '+628112849258'),
			array('seri' => '','nama_logger' => 'AWLR Pasucen','id_logger' => '10066','nosell' => '+628112849259'),
			array('seri' => '','nama_logger' => 'AWLR Rowokawuk','id_logger' => '10067','nosell' => '+628112838196'),
			array('seri' => '','nama_logger' => 'AWLR Pangenrejo','id_logger' => '10068','nosell' => '+628112838197'),
			array('seri' => '','nama_logger' => 'ARR Kedung Gupit','id_logger' => '10069','nosell' => '+628112849250'),
			array('seri' => '','nama_logger' => 'ARR Mangunranan','id_logger' => '10070','nosell' => '+628112849248'),
			array('seri' => '','nama_logger' => 'ARR Arjowinangun','id_logger' => '10071','nosell' => '+628112849249'),
			array('seri' => '','nama_logger' => 'ARR Taman Winangun','id_logger' => '10072','nosell' => '+628112849251'),
			array('seri' => '','nama_logger' => 'ARR Watu Barut','id_logger' => '10073','nosell' => '+628112849252'),
			array('seri' => '','nama_logger' => 'AWR Wadaslintang','id_logger' => '10074','nosell' => '+628112849253'),
			array('seri' => '','nama_logger' => 'ARR Pundong','id_logger' => '10093','nosell' => '+628112869539'),
			array('seri' => '','nama_logger' => 'AWLR Bendung Kediri','id_logger' => '10117','nosell' => '+628112842631'),
			array('seri' => '','nama_logger' => 'AWLR Bendung Slinga','id_logger' => '10118','nosell' => '+628112838204'),
			array('seri' => '','nama_logger' => 'AWLR Bulurejo','id_logger' => '10119','nosell' => '+628112944628'),
			array('seri' => '','nama_logger' => 'AWLR Kedungbenda','id_logger' => '10120','nosell' => '+628112981757'),
			array('seri' => '','nama_logger' => 'AWLR Kedungweru','id_logger' => '10121','nosell' => '+628112981754'),
			array('seri' => '','nama_logger' => 'AWLR Karangturi','id_logger' => '10122','nosell' => '+628112981755'),
			array('seri' => 'BL-110','nama_logger' => 'AWR Pejengkolan','id_logger' => '10151','nosell' => '+62811-294-9242'),
			array('seri' => 'BL-110','nama_logger' => 'ARR Sumingkir','id_logger' => '10152','nosell' => '+62811-294-9247'),
			array('seri' => 'BL-110','nama_logger' => 'AWLR Ketiwijayan','id_logger' => '10211','nosell' => '+628112847952')
		);

		foreach($t_logger as $k =>$v){
			$cek = $this->db->where('logger_id',$v['id_logger'])->get('t_informasi')->row();
			if($cek){
				$dt= [
					'nosell'=>$v['nosell']
				];
				$this->db->where('logger_id',$v['id_logger'])->update('t_informasi',$dt);
			}

		}
	}


	public function download_file()
	{
		$filename = $this->input->post('filename');

		// Load the download helper
		$this->load->helper('download');

		// Path to the file you want to download
		$filePath = 'https://bbwsso.monitoring4system.com/unduh/laporan_op/' . $filename;

		// Check if the file exists
		if (file_exists($filePath)) {
			// Force download the file
			force_download($filePath, NULL);
		} else {
			// Show an error if the file doesn't exist
			show_404();
		}
	}

}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */