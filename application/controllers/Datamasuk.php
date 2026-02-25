<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Datamasuk extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->model('m_inputdata');
		$this->load->library('PhpMQTT');
		$this->load->library('Csvimport');
		$this->load->helper('jwt');
	}

	public function index()
	{
		if (empty($this->session->userdata('tgl_search'))) {
			$tgl = date('Y-m-d');
			$this->session->set_userdata('tgl_search', $tgl);
		}
		$id_logger = $this->session->userdata('log_id');
		$data['list_logger'] = $this->db->get('t_logger')->result_array();
		$tabel = new stdClass();
		$ky = [];
		$tabel = $this->db->where('t_logger.id_logger', $id_logger)->get('t_logger')->row();

		if ($tabel) {
			$data['data'] = $this->db->query('SELECT * FROM ' . $tabel->tabel_main . ' where code_logger="' . $id_logger . '" and waktu >= "' . $this->session->userdata('tgl_search') . ' 00:00" and waktu <= "' . $this->session->userdata('tgl_search') . ' 23:59" ORDER BY waktu desc')->result_array();
			$data['tabel'] = $tabel->tabel_main;
			if($data['data']){
				foreach ($data['data'][0] as $key => $vl) {
					$ky[] = ['key'=>$key];
				}
				$data['key'] = $ky;
			}else{
				$data['key'] = $ky;
			}

			$data20 =  $this->db->query('select count(DISTINCT waktu) as waktu from '.$tabel->tabel_main.' where code_logger="'.$this->session->userdata('log_id').'" and waktu >= "'.  $this->session->userdata('tgl_search').'  00:00" and  waktu <= "'.  $this->session->userdata('tgl_search').'  23:59" ')->row();
			$current_time = time();
			$current_minute = date('i', $current_time);
			$total_minutes = ((int)date('H', $current_time) * 60) + (int)$current_minute;
			$data_count = $data20->waktu;
			if ($this->session->userdata('tgl_search') == date('Y-m-d')) {

				$tgl = date('Y-m-d H:i');

				if ($data_count > $total_minutes) {
					$data_count = $total_minutes;
				}
				$res = number_format(($data_count / $total_minutes * 100), 2);
				$res2 = $res . ' %';
			} else {
				$tgl = $this->session->userdata('tgl_awr');
				$total_minutes = 1440;
				$res = number_format(($data_count / 1440 * 100), 2);
				$res2 = $res . ' %';
			}
			$data['data_count'] = $data_count;
			$data['total_minutes'] = $total_minutes;
		} else {
			$data['data'] = array();
			$data['tabel'] = null;
			$data['data_count'] = 0;
			$data['total_minutes'] = 0;
		}
		if($ky){
			foreach($data['key'] as $k=> $vl){
				$param = $this->db->where('kolom_sensor',$vl['key'])->where('logger_id',$this->session->userdata('log_id'))->get('parameter_sensor')->row();
				if($param){
					$data['key'][$k]['nama'] = $param->nama_parameter;
				}else{
					$data['key'][$k]['nama'] = '';	
				}
			}
		}else{
			$data['key'] = $ky;
		}
		$this->load->view('konten/inputdata/view_alldata', $data);
	}

	public function sesi_logger()
	{
		$this->session->set_userdata('log_id', $this->input->post('logger_id'));
		redirect('datamasuk');
	}

	function tgl_search()
	{
		$date = date_create($this->input->post('tgl'));
		$tgl = date_format($date, "Y-m-d");
		$this->session->set_userdata('tgl_search', $tgl);
		redirect('datamasuk');
	}

	function tes_kirim () {
		$message = 'Anomali Terdeteksi !\n'
			. 'Pos \n'
			. 'Waktu : \n'
			. 'Tinggi Muka Air : ';
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => '31.58.158.182:3000/client/sendMessage/beacon',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => '{
  "chatId": "6289514761334@c.us",
  "contentType": "string",
  "content": "'.$message.'"
}',
			CURLOPT_HTTPHEADER => array(
				'x-api-key: ',
				'Content-Type: application/json'
			),
		));
		$response = curl_exec($curl);
		echo json_encode($response);
		curl_close($curl);
	}

	public function tes_anomali()
	{
		$data = array (
			'code_logger'=>'10223',
			'waktu'=>$this->input->post('waktu'),
			'sensor1'=>$this->input->post('sensor1'),
			'sensor2'=>0,
			'sensor3'=>0,
			'sensor4'=>0,
			'sensor5'=>0,
			'sensor6'=>0,
			'sensor7'=>0,
			'sensor8'=>0,
			'sensor9'=>0,
			'sensor10'=>0,
			'sensor11'=>0,
			'sensor12'=>0,
			'sensor13'=>0,
			'sensor14'=>0,
			'sensor15'=>0,
			'sensor16'=>0,
			'status_anomali'=>$this->input->post('status_anomali'),
		);
		$insert = $this->db->insert('tes_data',$data);
		if($insert){
			$db_time = $this->db->order_by('id','desc')->limit(1)->get('tes_notif')->row()->waktu;

			if (strtotime($this->input->post('waktu')) - strtotime($db_time) >= 3600) {
				$send_db = [
					'status'=>$this->input->post('status_anomali'),
					'waktu'=>$this->input->post('waktu'),
				];

				$pos    = "Pos AWLR Kolam Retensi Karangwuni";
				$tanggal= $this->input->post('waktu');
				$tma    = number_format($this->input->post('sensor1'),2,'.',''). " m";

				if($this->input->post('status_anomali') != 'Normal'){
					$message = 'Anomali Terdeteksi !\n'
						. 'Pos :'.$pos.'\n'
						. 'Waktu : '.$tanggal.'\n'
						. 'Tinggi Muka Air : '.$tma;
					$curl = curl_init();
					curl_setopt_array($curl, array(
						CURLOPT_URL => '31.58.158.182:3000/client/sendMessage/beacon',
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => '',
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 0,
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => 'POST',
						CURLOPT_POSTFIELDS => '{
  "chatId": "120363042735897956@g.us",
  "contentType": "string",
  "content": "'.$message.'"
}',
						CURLOPT_HTTPHEADER => array(
							'x-api-key: ',
							'Content-Type: application/json'
						),
					));
					$response = curl_exec($curl);
					curl_close($curl);
					$this->db->insert('tes_notif',$send_db);
				}
			} else {
				echo "⏳ Belum 1 jam";
			}
			echo json_encode([
				'status'=>true
			]);
		}
	}

	public function add_awlr(){
		$tgl=GETDATE();
		$tanggal = $this->input->post('tanggal');
		$jam = $this->input->post('jam');
		$waktu = $tanggal.' '.$jam;

		$data = array (
			'code_logger'=>$this->input->post('id_alat'),
			//'user_id'=>$this->input->post('user_id'),
			'waktu'=>$waktu,

			'sensor1'=>$this->input->post('sensor1'),
			'sensor2'=>$this->input->post('sensor2'),
			'sensor3'=>$this->input->post('sensor3'),
			'sensor4'=>$this->input->post('sensor4'),
			'sensor5'=>$this->input->post('sensor5'),
			'sensor6'=>$this->input->post('sensor6'),
			'sensor7'=>$this->input->post('sensor7'),
			'sensor8'=>$this->input->post('sensor8'),
			'sensor9'=>$this->input->post('sensor9'),
			'sensor10'=>$this->input->post('sensor10'),
			'sensor11'=>$this->input->post('sensor11'),
			'sensor12'=>$this->input->post('sensor12'),
			'sensor13'=>$this->input->post('sensor13'),
			'sensor14'=>$this->input->post('sensor14'),
			'sensor15'=>$this->input->post('sensor15'),
			'sensor16'=>$this->input->post('sensor16'),

		);
		$databbws = array (
			'id_alat'=>$this->input->post('id_alat'),
			//'user_id'=>$this->input->post('user_id'),
			'waktu'=>$waktu,

			'sensor1'=>$this->input->post('sensor1'),
			'sensor2'=>0,
			'sensor3'=>0,
			'sensor4'=>0,
			'sensor5'=>0,
			'sensor6'=>0,
			'sensor7'=>0,
			'sensor8'=>$this->input->post('sensor8'),
			'sensor9'=>0,
			'sensor10'=>0,
			'sensor11'=>0,
			'sensor12'=>0,
			'sensor13'=>0,
			'sensor14'=>$this->input->post('sensor14'),
			'sensor15'=>$this->input->post('sensor15'),
			'sensor16'=>$this->input->post('sensor16'),

		);

		$tabel = $this->db->where('id_logger',$this->input->post('id_alat'))->get('t_logger')->row();
		$this->m_inputdata->add_awlr2($data,$tabel->tabel_main);
		$this->m_inputdata->update_tempawlr($this->input->post('id_alat'),$data);

		//echo json_encode($data);
		/*
		if($this->input->post('sensor12') == '1')
		{
			$this->sinkrondatabyrequest($this->input->post('id_alat'));
		}
		*/
		#################### Update Serial Number ############################
		if(!empty($this->input->post('sn')))
		{
			$query_inf=$this->db->query('select serial_number from t_informasi where logger_id = "'.$this->input->post('id_alat').'"');
			foreach($query_inf->result() as $inf)
			{

				if($inf->serial_number != $this->input->post('sn'))
				{
					$updata_inf = array(
						'serial_number'=>$this->input->post('sn'),

					);
					$this->db->where('logger_id', $this->input->post('id_alat'));
					$this->db->update('t_informasi', $updata_inf);
				}
			}
		}
		###### BBWS #######
		if($this->input->post('id_alat') == '10223'){
			$dt_new = $this->db->select('waktu,sensor1 as tma')->distinct('waktu')->where('code_logger','10223')->order_by('waktu','desc')->limit(30)->get($tabel->tabel_main)->result_array();
			$timestamps = [];
			$sensor1 = [];
			foreach($dt_new as $k =>$vp){
				array_push($timestamps, $vp['waktu']);
				array_push($sensor1, $vp['tma']);
			}
			$dt_ar = [
				'timestamps'=>$timestamps,
				'sensor1'=>$sensor1
			];

			$dt_send =$dt_ar; 
			// URL API tujuan
			$url = "http://31.58.158.182/dpupesdm/datamasuk/receive_dt";
			$ch = curl_init($url);

			curl_setopt_array($ch, [
				CURLOPT_POST           => true,
				CURLOPT_POSTFIELDS     => json_encode($dt_send), // kirim sebagai JSON mentah
				CURLOPT_HTTPHEADER     => [
					'Content-Type: application/json',
					'Content-Length: ' . strlen(json_encode($dt_send))
				],
				CURLOPT_RETURNTRANSFER => true
			]);

			// Eksekusi request
			$response = curl_exec($ch);

			// Cek error
			if ($response === false) {
				echo "cURL Error: " . curl_error($ch);
			} else {
				echo $response;
			}
			//$this->db->insert('tes_data',$data);
		}
		/*
		$urlbws = "http://202.157.187.148/api/put?key=BBWSSOjogja@p!";
		$ch1 = curl_init();  // initialize curl handle
		curl_setopt($ch1, CURLOPT_URL, $urlbws); // set url to post to
		curl_setopt($ch1, CURLOPT_FAILONERROR, 1); //Fail on error
		curl_setopt($ch1, CURLOPT_RETURNTRANSFER,1); // return into a variable
		curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, TRUE);
		curl_setopt($ch1, CURLOPT_POST, 1); // set POST method
		curl_setopt($ch1, CURLOPT_POSTFIELDS, $databbws); // add POST fields
		//curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "POST");
		//curl_setopt($ch1, CURLOPT_POSTREDIR, 3);
		if(curl_exec($ch1) === false)
		{
			echo 'Curl error: ' . curl_error($ch1);
		}
		else
		{
			echo 'Operation completed without any errors';
		}
		curl_close($ch1);

		$urlserver = "http://202.169.239.11:8000/datamasuk/add_awlr2";
		$ch2 = curl_init();  // initialize curl handle
		curl_setopt($ch2, CURLOPT_URL, $urlserver); // set url to post to
		curl_setopt($ch2, CURLOPT_FAILONERROR, 1); //Fail on error
		curl_setopt($ch2, CURLOPT_RETURNTRANSFER,1); // return into a variable
		curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, TRUE);
		curl_setopt($ch2, CURLOPT_POST, 1); // set POST method
		curl_setopt($ch2, CURLOPT_POSTFIELDS, $data); // add POST fields
		//curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "POST");
		//curl_setopt($ch1, CURLOPT_POSTREDIR, 3);
		if(curl_exec($ch2) === false)
		{
			echo 'Curl error: ' . curl_error($ch2);
		}
		else
		{
			echo 'Operation completed without any errors';
		}
		curl_close($ch2);
*/
		################## MQTT ############
		/*
		$server = 'mqtt.beacontelemetry.com';    
		$port = 8883;                  
		$username = 'userlog';               
		$password = 'b34c0n';                 
		$client_id = 'bemqtt-'.$this->input->post('id_alat');
		$ca="/etc/ssl/certs/ca-bundle.crt";

		$mqtt = new phpMQTT($server, $port, $client_id,$ca);
		// $mqtt = new phpMQTT($server, $port, $client_id);
		if ($mqtt->connect(true, NULL, $username, $password)) {
			$mqtt->publish($this->input->post('id_alat'), json_encode($data), 0, false);
			$mqtt->close();
			echo 'data AWLR dikirim dengan mqtt';
		} else {
			echo "Time out!\n";
		}*/
		//$this->tingkat_status($this->input->post('id_alat'),number_format($this->input->post('sensor1'),1,'.',''),$waktu);
	}

	public function add_awlr2()
	{
		$tgl=GETDATE();
		$tanggal = $this->input->post('tanggal');
		$jam = $this->input->post('jam');
		$waktu = $this->input->post('waktu');

		$data = array (
			'code_logger'=>$this->input->post('id_alat'),
			//'user_id'=>$this->input->post('user_id'),
			'waktu'=>$waktu,
			'sensor1'=>$this->input->post('sensor1'),
			'sensor2'=>$this->input->post('sensor2'),
			'sensor3'=>$this->input->post('sensor3'),
			'sensor4'=>$this->input->post('sensor4'),
			'sensor5'=>$this->input->post('sensor5'),
			'sensor6'=>$this->input->post('sensor6'),
			'sensor7'=>$this->input->post('sensor7'),
			'sensor8'=>$this->input->post('sensor8'),
			'sensor9'=>$this->input->post('sensor9'),
			'sensor10'=>$this->input->post('sensor10'),
			'sensor11'=>$this->input->post('sensor11'),
			'sensor12'=>$this->input->post('sensor12'),
			'sensor13'=>$this->input->post('sensor13'),
			'sensor14'=>$this->input->post('sensor14'),
			'sensor15'=>$this->input->post('sensor15'),
			'sensor16'=>$this->input->post('sensor16'),
		);
		$tabel = $this->db->where('id_logger',$this->input->post('id_alat'))->get('t_logger')->row();
		if($tabel){
			$this->m_inputdata->add_awlr2($data,$tabel->tabel_main);
			$this->m_inputdata->update_tempawlr($this->input->post('id_alat'),$data);

			//$this->tingkat_status($this->input->post('id_alat'),number_format($this->input->post('sensor1'),1,'.',''),$waktu);
			/*
			$urlserver = "http://202.169.239.11:8000/datamasuk/add_awlr2";
			$ch2 = curl_init();  // initialize curl handle
			curl_setopt($ch2, CURLOPT_URL, $urlserver); // set url to post to
			curl_setopt($ch2, CURLOPT_FAILONERROR, 1); //Fail on error
			curl_setopt($ch2, CURLOPT_RETURNTRANSFER,1); // return into a variable
			curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, TRUE);
			curl_setopt($ch2, CURLOPT_POST, 1); // set POST method
			curl_setopt($ch2, CURLOPT_POSTFIELDS, $data); // add POST fields
			//curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "POST");
			//curl_setopt($ch1, CURLOPT_POSTREDIR, 3);
			if(curl_exec($ch2) === false)
			{
				echo 'Curl error: ' . curl_error($ch2);
			}
			else
			{
				echo 'Operation completed without any errors';
			}
			curl_close($ch2);
			*/
		}else{
			echo 'gagal mengirim data';
		}
		################## MQTT ############
		/*
		$server = 'mqtt.beacontelemetry.com';    
		$port = 8883;                  
		$username = 'userlog';               
		$password = 'b34c0n';                 
		$client_id = 'bemqtt-'.$this->input->post('id_alat');
		$ca="/etc/ssl/certs/ca-bundle.crt";

		$mqtt = new phpMQTT($server, $port, $client_id,$ca);
		// $mqtt = new phpMQTT($server, $port, $client_id);
		if ($mqtt->connect(true, NULL, $username, $password)) {
			$mqtt->publish($this->input->post('id_alat'), json_encode($data), 0, false);
			$mqtt->close();
			echo 'data AWLR dikirim dengan mqtt';
		} else {
			echo "Time out!\n";
		}*/
	}

	public function add_afmr()
	{
		$tgl=GETDATE();
		$tanggal = $this->input->post('tanggal');
		$jam = $this->input->post('jam');

		$waktu = $tanggal.' '.$jam;
		$data = array (
			'code_logger'=>$this->input->post('id_alat'),
			//'user_id'=>$this->input->post('user_id'),
			'waktu'=>date('Y-m-d H:i'),
			'sensor1'=>$this->input->post('sensor1'),
			'sensor2'=>$this->input->post('sensor2')+0.579,
			'sensor3'=>$this->input->post('sensor3'),
			'sensor4'=>12.579,
			'sensor5'=>$this->input->post('sensor5'),
			'sensor6'=>$this->input->post('sensor6'),
			'sensor7'=>$this->input->post('sensor7'),
			'sensor8'=>$this->input->post('sensor8'),
			'sensor9'=>$this->input->post('sensor9'),
			'sensor10'=>$this->input->post('sensor10'),
			'sensor11'=>$this->input->post('sensor11'),
			'sensor12'=>$this->input->post('sensor12'),
			'sensor13'=>$this->input->post('sensor13'),
			'sensor14'=>$this->input->post('sensor14'),
			'sensor15'=>$this->input->post('sensor15'),
			'sensor16'=>$this->input->post('sensor16'),
			'sensor17'=>$this->input->post('sensor17'),
			'sensor18'=>$this->input->post('sensor18'),
			'sensor19'=>$this->input->post('sensor19'),
		);
		$tabel = $this->db->where('id_logger',$this->input->post('id_alat'))->get('t_logger')->row();
		if($tabel){
			$this->m_inputdata->add_afmr($data,$tabel->tabel_main);
			$this->m_inputdata->update_tempafmr($this->input->post('id_alat'),$data);

			//$this->tingkat_status($this->input->post('id_alat'),number_format($this->input->post('sensor1'),1,'.',''),$waktu);
			$data_set= [
				'gps1'=>$this->input->post('gps1'),
				'gps2'=>$this->input->post('gps2'),
				'gps3'=>$this->input->post('gps3'),
				'ad'=>$this->input->post('ad'),
				'kd'=>$this->input->post('kd'),
				'mr'=>$this->input->post('mr'),
				'wdt'=>$this->input->post('wdt'),
			];
			$this->db->where('logger_id',$this->input->post('id_alat'))->update('t_informasi',$data_set);
		}else{
			echo 'gagal mengirim data';
			echo $this->input->post('id_alat');
		}
		################## MQTT ############
			/*
		$server = 'mqtt.beacontelemetry.com';    
		$port = 8883;                  
		$username = 'userlog';               
		$password = 'b34c0n';                 
		$client_id = 'bemqtt-'.$this->input->post('id_alat');
		$ca="/etc/ssl/certs/ca-bundle.crt";

		$mqtt = new phpMQTT($server, $port, $client_id,$ca);
		// $mqtt = new phpMQTT($server, $port, $client_id);
		if ($mqtt->connect(true, NULL, $username, $password)) {
			$mqtt->publish($this->input->post('id_alat'), json_encode($data), 0, false);
			$mqtt->close();
			echo 'data AWLR dikirim dengan mqtt';
		} else {
			echo "Time out!\n";
		}
		*/
	}

	public function int_sih($key){
		//$data = $this->input->post();
		$date = $this->input->post('tanggal'). ' ' .$this->input->post('jam');

		$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date);
		$url;
		//$atomDate;
		if ($dateTime !== false) {
			$atomDate = $dateTime->format(DateTime::ATOM);
		}
		//if ($this->input->post('id_alat')=="10248") $uid = "BL-1100-32X-10248";
		if ($this->input->post('id_alat')=="10248"){
			$uid = "06.14.02170010046"; //11111111   06.14.02170010043
			$nama_pos = "POS_DAS_BOGOWONTO_2";
			$sensor  	= array( 
				array(
					'nama'=>"ch",
					'nilai'=>$this->input->post('sensor9'),
					'satuan'=>"mm"
				),
			);
			$url = 'https://bbwsso.monitoring4system.com/datamasuk/add_arr19';
		} 
		else if ($this->input->post('id_alat')=="10247") {
			$uid = "06.14.02170010044|06.13.02170010045";
			$nama_pos = "POS_DAS_BOGOWONTO_1";
			$sensor  	= array( 
				array(
					'nama'=>"tma1",
					'nilai'=>$this->input->post('sensor1'),
					'satuan'=>"m",
				),array(
					'nama'=>"tma2",
					'nilai'=>$this->input->post('sensor2'),
					'satuan'=>"m"
				), array(
					'nama'=>"ch",
					'nilai'=>$this->input->post('sensor9'),
					'satuan'=>"mm"
				),
			);
		} 
		else if ($this->input->post('id_alat')=="10249") {
			$uid = "06.13.02170010047";
			$nama_pos = "POS_DAS_BOGOWONTO_3";
			$sensor  	= array( 
				array(
					'nama'=>"tma",
					'nilai'=>$this->input->post('sensor1'),
					'satuan'=>"m",
				),
				array(
					'nama'=>"debit",
					'nilai'=>$this->input->post('sensor2'),
					'satuan'=>"m",
				),
				array(
					'nama'=>"kecepatan_arus",
					'nilai'=>$this->input->post('sensor8'),
					'satuan'=>"mm"
				), array(
					'nama'=>"ch",
					'nilai'=>$this->input->post('sensor9'),
					'satuan'=>"mm"
				),
			);

		} 
		$data = array(
			'id_logger'=>$this->input->post('id_alat'),
			'uid'=>$uid,
			'nama_lokasi'=>$nama_pos,
			'tanggal'=>$this->input->post('tanggal'),
			'jam'=>$this->input->post('jam'),
			'tzone'=>$atomDate,
			'latitude'=>$this->input->post('gps1'),
			'longitude'=>$this->input->post('gps2'),
			"sensor"=>$sensor,

		);
		$jwt = $this->kirim_balai($data,$key);
		echo $jwt;

		echo $date;

		$postData = array(
			'id_alat'=>$this->input->post('id_alat'),
			'waktu'=>$date,
			'sensor1'=>$this->input->post('sensor1'),
			'sensor2'=>$this->input->post('sensor2'),
			'sensor3'=>$this->input->post('sensor3'),
			'sensor4'=>$this->input->post('sensor4'),
			'sensor5'=>$this->input->post('sensor5'),
			'sensor6'=>$this->input->post('sensor6'),
			'sensor7'=>$this->input->post('sensor7'),
			'sensor8'=>$this->input->post('sensor8'),
			'sensor9'=>$this->input->post('sensor9'),
			'sensor10'=>$this->input->post('sensor10'),
			'sensor11'=>$this->input->post('sensor11'),
			'sensor12'=>$this->input->post('sensor12'),
			'sensor13'=>$this->input->post('sensor13'),
			'sensor14'=>$this->input->post('sensor14'),
			'sensor15'=>$this->input->post('sensor15'),
			'sensor16'=>$this->input->post('sensor16'),
			'sensor17'=>$this->input->post('sensor17'),
			'sensor18'=>$this->input->post('sensor18'),
			'sensor19'=>$this->input->post('sensor19'),
		);

		$postDataString = http_build_query($postData);

		$options = array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $postDataString,
				'timeout' => 10
			)
		);
		/*
		if ($this->input->post('id_alat')=="10248"){
			$url = 'https://bbwsso.monitoring4system.com/datamasuk/add_arr19';
			$context = stream_context_create($options);
			$response = json_decode(file_get_contents($url, false, $context));
			echo "\nterkirim";
		} else if ($this->input->post('id_alat')=="10247") {
			$url = 'https://bbwsso.monitoring4system.com/datamasuk/add_awlr2';
			$context = stream_context_create($options);
			$response = json_decode(file_get_contents($url, false, $context));
		} else if ($this->input->post('id_alat')=="10249") {
			$url = 'https://bbwsso.monitoring4system.com/datamasuk/add_afmr';
			$context = stream_context_create($options);
			$response = json_decode(file_get_contents($url, false, $context));
		} */
		//$context = stream_context_create($options);
		//$response = json_decode(file_get_contents($url, false, $context));
		$url = 'https://bbwsso.monitoring4system.com/datamasuk/add_awlr2';
		$context = stream_context_create($options);
		$response = json_decode(file_get_contents($url, false, $context));
		//print_r($data);
	}

	public function int_sihCoba($key){
		//$data = $this->input->post();
		$date = $this->input->post('tanggal'). ' ' .$this->input->post('jam');

		$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date);

		if ($dateTime !== false) {
			$atomDate = $dateTime->format(DateTime::ATOM);
		}
		if ($this->input->post('id_alat')=="10248"){
			$uid = "06.14.02170010046"; //11111111   06.14.02170010043
			$nama_pos = "POS_DAS_BOGOWONTO_2";
			$sensor  	= array( 
				array(
					"nama"=>"ch2",
					"nilai"=>"2",
					"satuan"=>"mm"
				)
			);
		} 
		else if ($this->input->post('id_alat')=="10247") {
			$uid = "06.14.02170010044|06.13.02170010045";
			$nama_pos 	= "POS_DAS_BOGOWONTO_1";
			$sensor  	= array( 
				array(
					'nama'=>"tma1",
					'nilai'=>"20",
					'satuan'=>"m",
				), array(
					'nama'=>"ch1",
					'nilai'=>"2",
					'satuan'=>"mm"
				), array(
					'nama'=>"tma2",
					'nilai'=>"20",
					'satuan'=>"m",
				)
			);	
		} 
		else if ($this->input->post('id_alat')=="10249") {
			$uid = "06.13.02170010047";
			$nama_pos = "POS_DAS_BOGOWONTO_3";
			$sensor  	= array( 
				array(
					'nama'=>"tma",
					'nilai'=>"20",
					'satuan'=>"m",
				),array(
					'nama'=>"kecepatan_arus",
					'nilai'=>"2",
					'satuan'=>"mm"
				), array(
					'nama'=>"ch",
					'nilai'=>"2",
					'satuan'=>"mm"
				),
			);
		} 


		$data = array(
			'id_logger'=>$this->input->post('id_alat'),
			'uid'=>$uid,
			'nama_lokasi'=>$nama_pos,
			'tanggal'=>$this->input->post('tanggal'),
			'jam'=>$this->input->post('jam'),
			'tzone'=>$atomDate,
			'latitude'=>$this->input->post('gps1'),
			'longitude'=>$this->input->post('gps2'),
			"sensor"=>$sensor,
		);
		$jwt = $this->kirim_balai($data,$key);
		echo $jwt;

		/*
		$postData = array(
			'id_alat'=>$this->input->post('id_alat'),
			'waktu'=>$date,
			'sensor1'=>$this->input->post('sensor1'),
			'sensor2'=>$this->input->post('sensor2'),
			'sensor3'=>$this->input->post('sensor3'),
			'sensor4'=>$this->input->post('sensor4'),
			'sensor5'=>$this->input->post('sensor5'),
			'sensor6'=>$this->input->post('sensor6'),
			'sensor7'=>$this->input->post('sensor7'),
			'sensor8'=>$this->input->post('sensor8'),
			'sensor9'=>$this->input->post('sensor9'),
			'sensor10'=>$this->input->post('sensor10'),
			'sensor11'=>$this->input->post('sensor11'),
			'sensor12'=>$this->input->post('sensor12'),
			'sensor13'=>$this->input->post('sensor13'),
			'sensor14'=>$this->input->post('sensor14'),
			'sensor15'=>$this->input->post('sensor15'),
			'sensor16'=>$this->input->post('sensor16'),
		);

		$url = 'https://bbwsso.monitoring4system.com/datamasuk/add_awlr2';
		$postDataString = http_build_query($postData);

		$options = array(
			'http' => array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $postDataString,
				'timeout' => 10
			)
		);
		$context = stream_context_create($options);
		$response = json_decode(file_get_contents($url, false, $context));

		//print_r($data); */
	}

	public function jwt_key ($key) {
		//$token = file_get_contents('https://sihka.dev-tunnels.id/key/beacon');
		return $key;
	}

	public function kirim_balai($data,$key)
	{
		$encode_data= jwt_encode($data, $key,'HS256');
		$token=array('token'=>$encode_data);
		return json_encode($token);
	}

	public function get_jwt()
	{
		$tgl=GETDATE();
		$tanggal = $this->input->post('tanggal');
		$jam = $this->input->post('jam');
		$waktu = $tanggal.' '.$jam;
		$data = array (
			'code_logger'=>$this->input->post('id_alat'),
			//'user_id'=>$this->input->post('user_id'),
			'waktu'=>$waktu,
			'sensor1'=>$this->input->post('sensor1'),
			'sensor2'=>$this->input->post('sensor2'),
			'sensor3'=>$this->input->post('sensor3'),
			'sensor4'=>$this->input->post('sensor4'),
			'sensor5'=>$this->input->post('sensor5'),
			'sensor6'=>$this->input->post('sensor6'),
			'sensor7'=>$this->input->post('sensor7'),
			'sensor8'=>$this->input->post('sensor8'),
			'sensor9'=>$this->input->post('sensor9'),
			'sensor10'=>$this->input->post('sensor10'),
			'sensor11'=>$this->input->post('sensor11'),
			'sensor12'=>$this->input->post('sensor12'),
			'sensor13'=>$this->input->post('sensor13'),
			'sensor14'=>$this->input->post('sensor14'),
			'sensor15'=>$this->input->post('sensor15'),
			'sensor16'=>$this->input->post('sensor16'),

		);

		$encode_data= jwt_encode($data,'BzJEfNhxs0XvhBf6JTx8sjFv','HS256');
		$token=array('token'=>$encode_data);
		echo json_encode($token);
	}

	public function add_demo_logger()
	{
		$data = json_decode(file_get_contents('php://input'));
		$encode_data= jwt_decode($data->token,'BzJEfNhxs0XvhBf6JTx8sjFv','HS256');

		$data = array (
			'code_logger'=> $encode_data->code_logger,
			//'user_id'=>$this->input->post('user_id'),
			'waktu'=> $encode_data->waktu,
			'sensor1'=>$encode_data->sensor1,
			'sensor2'=>$encode_data->sensor2,
			'sensor3'=>$encode_data->sensor3,
			'sensor4'=>$encode_data->sensor4,
			'sensor5'=>$encode_data->sensor5,
			'sensor6'=>$encode_data->sensor6,
			'sensor7'=>$encode_data->sensor7,
			'sensor8'=>$encode_data->sensor8,
			'sensor9'=>$encode_data->sensor9,
			'sensor10'=>$encode_data->sensor10,
			'sensor11'=>$encode_data->sensor11,
			'sensor12'=>$encode_data->sensor12,
			'sensor13'=>$encode_data->sensor13,
			'sensor14'=>$encode_data->sensor14,
			'sensor15'=>$encode_data->sensor15,
			'sensor16'=>$encode_data->sensor16,

		);

		$tabel = $this->db->where('id_logger',$encode_data->code_logger)->get('t_logger')->row();
		if($tabel){
			$this->m_inputdata->add_awlr2($data,$tabel->tabel_main);
		}

	}

	public function add_awr()
	{
		$tgl=GETDATE();
		$tanggal = $this->input->post('tanggal');
		$jam = $this->input->post('jam');
		$waktu = $tanggal.' '.$jam;

		$data = array (
			'code_logger'=>$this->input->post('id_alat'),
			//'user_id'=>$this->input->post('user_id'),
			'waktu'=>$waktu,
			'sensor1'=>$this->input->post('sensor1'),
			'sensor2'=>$this->input->post('sensor2'),
			'sensor3'=>$this->input->post('sensor3'),
			'sensor4'=>$this->input->post('sensor4'),
			'sensor5'=>$this->input->post('sensor5'),
			'sensor6'=>$this->input->post('sensor6'),
			'sensor7'=>$this->input->post('sensor7'),
			'sensor8'=>$this->input->post('sensor8'),
			'sensor9'=>$this->input->post('sensor9'),
			'sensor10'=>$this->input->post('sensor10'),
			'sensor11'=>$this->input->post('sensor11'),
			'sensor12'=>$this->input->post('sensor12'),
			'sensor13'=>$this->input->post('sensor13'),
			'sensor14'=>$this->input->post('sensor14'),
			'sensor15'=>$this->input->post('sensor15'),
			'sensor16'=>$this->input->post('sensor16'),

		);
		$tabel = $this->db->where('id_logger',$this->input->post('id_alat'))->get('t_logger')->row();
		if($tabel){
			$this->m_inputdata->add_awr($data,$tabel->tabel_main);
			$temp = $this->db->where('id_katlogger',$tabel->kategori_log)->get('kategori_logger')->row();
			$this->m_inputdata->update_tempawr($this->input->post('id_alat'),$data,$temp->temp_data);
			/*
			$urlserver = "http://202.169.239.11:8000/datamasuk/add_awr";
			$ch2 = curl_init();  // initialize curl handle
			curl_setopt($ch2, CURLOPT_URL, $urlserver); // set url to post to
			curl_setopt($ch2, CURLOPT_FAILONERROR, 1); //Fail on error
			curl_setopt($ch2, CURLOPT_RETURNTRANSFER,1); // return into a variable
			curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, TRUE);
			curl_setopt($ch2, CURLOPT_POST, 1); // set POST method
			curl_setopt($ch2, CURLOPT_POSTFIELDS, $data); // add POST fields
			//curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "POST");
			//curl_setopt($ch1, CURLOPT_POSTREDIR, 3);
			if(curl_exec($ch2) === false)
			{
				echo 'Curl error: ' . curl_error($ch2);
			}
			else
			{
				echo 'Operation completed without any errors';
			}
			curl_close($ch2);
			*/
		}else{
			echo 'failed';
		}
		################## MQTT ############
		/*
		$server = 'mqtt.beacontelemetry.com';    
		$port = 8883;                  
		$username = 'userlog';               
		$password = 'b34c0n';                 
		$client_id = 'bemqtt-'.$this->input->post('id_alat');
		$ca="/etc/ssl/certs/ca-bundle.crt";

		$mqtt = new phpMQTT($server, $port, $client_id,$ca);
		// $mqtt = new phpMQTT($server, $port, $client_id);
		if ($mqtt->connect(true, NULL, $username, $password)) {
			$mqtt->publish($this->input->post('id_alat'), json_encode($data), 0, false);
			$mqtt->close();
			echo 'data AWLR dikirim dengan mqtt';
		} else {
			echo "Time out!\n";
		}
		*/
		echo $this->input->post('id_alat');
	}

	public function add_arr()
	{
		$tgl=GETDATE();
		$tanggal = $this->input->post('tanggal');
		$jam = $this->input->post('jam');
		$waktu = $tanggal.' '.$jam;

		$data = array (
			'code_logger'=>$this->input->post('id_alat'),
			//'user_id'=>$this->input->post('user_id'),
			'waktu'=>$waktu,
			'sensor1'=>$this->input->post('sensor1'),
			'sensor2'=>$this->input->post('sensor2'),
			'sensor3'=>$this->input->post('sensor3'),
			'sensor4'=>$this->input->post('sensor4'),
			'sensor5'=>$this->input->post('sensor5'),
			'sensor6'=>$this->input->post('sensor6'),
			'sensor7'=>$this->input->post('sensor7'),
			'sensor8'=>$this->input->post('sensor8'),
			'sensor9'=>$this->input->post('sensor9'),
			'sensor10'=>$this->input->post('sensor10'),
			'sensor11'=>$this->input->post('sensor11'),
			'sensor12'=>$this->input->post('sensor12'),
			'sensor13'=>$this->input->post('sensor13'),
			'sensor14'=>$this->input->post('sensor14'),
			'sensor15'=>$this->input->post('sensor15'),
			'sensor16'=>$this->input->post('sensor16'),
			'sensor17'=>$this->input->post('sensor17') ?? 0,
			'sensor18'=>$this->input->post('sensor18') ?? 0,
			'sensor19'=>$this->input->post('sensor19') ?? 0,


		);
		$tabel = $this->db->where('id_logger',$this->input->post('id_alat'))->get('t_logger')->row();
		if($tabel){
			$this->m_inputdata->add_awr($data,$tabel->tabel_main);
			$this->m_inputdata->update_tempawr($this->input->post('id_alat'),$data,'temp_arr');
			/*
			$urlserver = "http://202.169.239.11:8000/datamasuk/add_arr";
			$ch2 = curl_init();  // initialize curl handle
			curl_setopt($ch2, CURLOPT_URL, $urlserver); // set url to post to
			curl_setopt($ch2, CURLOPT_FAILONERROR, 1); //Fail on error
			curl_setopt($ch2, CURLOPT_RETURNTRANSFER,1); // return into a variable
			curl_setopt($ch2, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, TRUE);
			curl_setopt($ch2, CURLOPT_POST, 1); // set POST method
			curl_setopt($ch2, CURLOPT_POSTFIELDS, $data); // add POST fields
			//curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, "POST");
			//curl_setopt($ch1, CURLOPT_POSTREDIR, 3);
			if(curl_exec($ch2) === false)
			{
				echo 'Curl error: ' . curl_error($ch2);
			}
			else
			{
				echo 'Operation completed without any errors';
			}
			curl_close($ch2);
*/
		}else{
			echo 'failed';
		}
		
		################## FTP #############
		/*if($this->input->post('sensor12') == '1')
			{
				$this->sinkrondatabyrequest($this->input->post('id_alat'));
			}
			*/
		
		################## MQTT ############
		/*
		$server = 'mqtt.beacontelemetry.com';    
		$port = 8883;                  
		$username = 'userlog';               
		$password = 'b34c0n';                 
		$client_id = 'bemqtt-'.$this->input->post('id_alat');
		$ca="/etc/ssl/certs/ca-bundle.crt";

		$mqtt = new phpMQTT($server, $port, $client_id,$ca);
		// $mqtt = new phpMQTT($server, $port, $client_id);
		if ($mqtt->connect(true, NULL, $username, $password)) {
			$mqtt->publish($this->input->post('id_alat'), json_encode($data), 0, false);
			$mqtt->close();
			echo 'data AWLR dikirim dengan mqtt';
		} else {
			echo "Time out!\n";
		}
		*/
		echo $this->input->post('id_alat');
	}

	public function add_arr19_2()
	{
		//echo "id_alat: ".$this->input->post('id_alat')."\n";
		$date = $this->input->post('tanggal'). ' ' .$this->input->post('jam');

		/*	$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date);

		if ($dateTime !== false) {
			$atomDate = $dateTime->format(DateTime::ATOM);
		}
		if ($this->input->post('id_alat')=="10248"){
			$uid = "06.14.02170010046"; //11111111   06.14.02170010043
			$nama_pos = "POS_DAS_BOGOWONTO_2";
			$sensor  	= array( 
				array(
					"nama"=>"ch",
					"nilai"=>$this->input->post('sensor9'),
					"satuan"=>"mm"
				),
				array(
					"nama"=>"ch_akumulasi_24jam",
					"nilai"=>$this->input->post('sensor10'),
					"satuan"=>"mm"
				),
			);
		} 

			$data = array(
			'id_logger'=>$this->input->post('id_alat'),
			'uid'=>$uid,
			'nama_lokasi'=>$nama_pos,
			'tanggal'=>$this->input->post('tanggal'),
			'jam'=>$this->input->post('jam'),
			'tzone'=>$atomDate,
			'latitude'=>$this->input->post('gps1'),
			'longitude'=>$this->input->post('gps2'),
			"sensor"=>$sensor,

		); */
		//$jwt = $this->kirim_balai($data,$key);
		//echo $jwt;
		$idalat;
		if($this->input->post('id_alat') == '10000') $idalat = "10248";
		else $idalat = $this->input->post('id_alat');
		if($idalat == '10248'){
			$mm_ch = $this->input->post('sensor9') * 0.05;
		}else{
			$mm_ch = $this->input->post('sensor13');
		}
		echo $mm_ch;
		$data = array (
			'code_logger'=>$idalat,
			//'code_logger'=>$this->input->post('id_alat'),
			//'user_id'=>$this->input->post('user_id'),
			'waktu'=>$date,
			'sensor1'=>$this->input->post('sensor1'),
			'sensor2'=>$this->input->post('sensor2'),
			'sensor3'=>$this->input->post('sensor3'),
			'sensor4'=>$this->input->post('sensor4'),
			'sensor5'=>$this->input->post('sensor5'),
			'sensor6'=>$this->input->post('sensor6'),
			'sensor7'=>$this->input->post('sensor7'),
			'sensor8'=>$this->input->post('sensor8'),
			'sensor9'=>$this->input->post('sensor9'),
			'sensor10'=>$this->input->post('sensor10'),
			'sensor11'=>$this->input->post('sensor11'),
			'sensor12'=>$this->input->post('sensor12'),
			'sensor13'=>$mm_ch,
			'sensor14'=>$this->input->post('sensor14'),
			'sensor15'=>$this->input->post('sensor15'),
			'sensor16'=>$this->input->post('sensor16'),
			'sensor17'=>$this->input->post('sensor17'),
			'sensor18'=>$this->input->post('sensor18'),
			'sensor19'=>$this->input->post('sensor19'),
		);
		//$this->m_inputdata->add_awr($data,'arr3');
		//$this->m_inputdata->update_tempawr($this->input->post('id_alat'),$data,'temp_arr');
		echo json_encode($data);

		///*
		$tabel = $this->db->where('id_logger',$idalat)->get('t_logger')->row();
		//$tabel = $this->db->where('id_logger',$this->input->post('id_alat'))->get('t_logger')->row();
		if($tabel){
			$this->m_inputdata->add_awr($data,$tabel->tabel_main);
			//$this->m_inputdata->update_tempawr($this->input->post('id_alat'),$data,'temp_arr');
			$this->m_inputdata->update_tempawr($idalat,$data,'temp_arr');

			$data_set= [
				'gps1'=>$this->input->post('gps1'),
				'gps2'=>$this->input->post('gps2'),
				'gps3'=>$this->input->post('gps3'),
				'ad'=>$this->input->post('ad'),
				'kd'=>$this->input->post('kd'),
				'mr'=>$this->input->post('mr'),
				'wdt'=>$this->input->post('wdt'),
				'serial_number'=>$this->input->post('sn'),
			];
			$this->db->where('logger_id',$this->input->post('id_alat'))->update('t_informasi',$data_set);
			echo json_encode($data_set);

		}else{
			echo 'failed';
		}  
		################## MQTT ############
		/*
		$server = 'mqtt.beacontelemetry.com';    
		$port = 8883;                  
		$username = 'userlog';               
		$password = 'b34c0n';                 
		$client_id = 'bemqtt-'.$this->input->post('id_alat');
		$ca="/etc/ssl/certs/ca-bundle.crt";

		$mqtt = new phpMQTT($server, $port, $client_id,$ca);
		// $mqtt = new phpMQTT($server, $port, $client_id);
		if ($mqtt->connect(true, NULL, $username, $password)) {
			$mqtt->publish($this->input->post('id_alat'), json_encode($data), 0, false);
			$mqtt->close();
			echo 'data AWLR dikirim dengan mqtt';
		} else {
			echo "Time out!\n";
		}
		*/
	}

	public function add_arr19()
	{
		$tgl=GETDATE();
		$tanggal = $this->input->post('tanggal');
		$jam = $this->input->post('jam');
		$waktu = $tanggal.' '.$jam;

		$data = array (
			//'code_logger'=>$this->input->post('id_alat'),
			'code_logger'=>"10248",
			//'user_id'=>$this->input->post('user_id'),
			'waktu'=>$waktu,
			'sensor1'=>$this->input->post('sensor1'),
			'sensor2'=>$this->input->post('sensor2'),
			'sensor3'=>$this->input->post('sensor3'),
			'sensor4'=>$this->input->post('sensor4'),
			'sensor5'=>$this->input->post('sensor5'),
			'sensor6'=>$this->input->post('sensor6'),
			'sensor7'=>$this->input->post('sensor7'),
			'sensor8'=>$this->input->post('sensor8'),
			'sensor9'=>$this->input->post('sensor9'),
			'sensor10'=>$this->input->post('sensor10'),
			'sensor11'=>$this->input->post('sensor11'),
			'sensor12'=>$this->input->post('sensor12'),
			'sensor13'=>$this->input->post('sensor13'),
			'sensor14'=>$this->input->post('sensor14'),
			'sensor15'=>$this->input->post('sensor15'),
			'sensor16'=>$this->input->post('sensor16'),
			'sensor17'=>$this->input->post('sensor17'),
			'sensor18'=>$this->input->post('sensor18'),
			'sensor19'=>$this->input->post('sensor19'),
		);
		//$tabel = $this->db->where('id_logger',$this->input->post('id_alat'))->get('t_logger')->row();
		$tabel = $this->db->where('id_logger',"10248")->get('t_logger')->row();
		if($tabel){
			$this->m_inputdata->add_awr($data,$tabel->tabel_main);
			//$this->m_inputdata->update_tempawr($this->input->post('id_alat'),$data,'temp_arr');
			$this->m_inputdata->update_tempawr("10248",$data,'temp_arr');
			$data_set= [
				'gps1'=>$this->input->post('gps1'),
				'gps2'=>$this->input->post('gps2'),
				'gps3'=>$this->input->post('gps3'),
				'ad'=>$this->input->post('ad'),
				'kd'=>$this->input->post('kd'),
				'mr'=>$this->input->post('mr'),
				'wdt'=>$this->input->post('wdt'),
			];
			$this->db->where('logger_id',$this->input->post('id_alat'))->update('t_informasi',$data_set);
		}else{
			echo 'failed';
		}
		################## MQTT ############
		/*
		$server = 'mqtt.beacontelemetry.com';    
		$port = 8883;                  
		$username = 'userlog';               
		$password = 'b34c0n';                 
		$client_id = 'bemqtt-'.$this->input->post('id_alat');
		$ca="/etc/ssl/certs/ca-bundle.crt";

		$mqtt = new phpMQTT($server, $port, $client_id,$ca);
		// $mqtt = new phpMQTT($server, $port, $client_id);
		if ($mqtt->connect(true, NULL, $username, $password)) {
			$mqtt->publish($this->input->post('id_alat'), json_encode($data), 0, false);
			$mqtt->close();
			echo 'data AWLR dikirim dengan mqtt';
		} else {
			echo "Time out!\n";
		}
		*/
		echo $this->input->post('id_alat');
	}

	public function add_awlr19()
	{
		$tgl=GETDATE();
		$tanggal = $this->input->post('tanggal');
		$jam = $this->input->post('jam');
		$waktu = $tanggal . ' '. $jam;

		$data = array (
			'code_logger'=>$this->input->post('id_alat'),
			//'user_id'=>$this->input->post('user_id'),
			'waktu'=>date('Y-m-d H:i'),
			'sensor1'=>$this->input->post('sensor1'),
			'sensor2'=>$this->input->post('sensor2'),
			'sensor3'=>$this->input->post('sensor3'),
			'sensor4'=>$this->input->post('sensor4'),
			'sensor5'=>$this->input->post('sensor5'),
			'sensor6'=>$this->input->post('sensor6'),
			'sensor7'=>$this->input->post('sensor7'),
			'sensor8'=>$this->input->post('sensor8'),
			'sensor9'=>$this->input->post('sensor9'),
			'sensor10'=>$this->input->post('sensor10'),
			'sensor11'=>$this->input->post('sensor11'),
			'sensor12'=>$this->input->post('sensor12'),
			'sensor13'=>$this->input->post('sensor13'),
			'sensor14'=>$this->input->post('sensor14'),
			'sensor15'=>$this->input->post('sensor15'),
			'sensor16'=>$this->input->post('sensor16'),
			'sensor17'=>$this->input->post('sensor17'),
			'sensor18'=>$this->input->post('sensor18'),
			'sensor19'=>$this->input->post('sensor19'),
		);
		echo json_encode($data);
		//$this->m_inputdata->add_awlr2($data,'awlr4');

		$tabel = $this->db->where('id_logger',$this->input->post('id_alat'))->get('t_logger')->row();
		if($tabel){
			$insert = $this->m_inputdata->add_awlr2($data,$tabel->tabel_main);
			//$this->m_inputdata->add_awlr2($data,'awlr4');
			$this->m_inputdata->update_tempawlr($this->input->post('id_alat'),$data);	
			$data_set= [
				'gps1'=>$this->input->post('gps1'),
				'gps2'=>$this->input->post('gps2'),
				'gps3'=>$this->input->post('gps3'),
				'ad'=>$this->input->post('ad'),
				'kd'=>$this->input->post('kd'),
				'mr'=>$this->input->post('mr'),
				'wdt'=>$this->input->post('wdt'),
				'serial_number'=>$this->input->post('sn'),
			];
			$this->db->where('logger_id',$this->input->post('id_alat'))->update('t_informasi',$data_set);
			echo json_encode($data_set);

		}else{
			echo 'failed';
		}
		################## MQTT ############
		/*
		$server = 'mqtt.beacontelemetry.com';    
		$port = 8883;                  
		$username = 'userlog';               
		$password = 'b34c0n';                 
		$client_id = 'bemqtt-'.$this->input->post('id_alat');
		$ca="/etc/ssl/certs/ca-bundle.crt";

		$mqtt = new phpMQTT($server, $port, $client_id,$ca);
		// $mqtt = new phpMQTT($server, $port, $client_id);
		if ($mqtt->connect(true, NULL, $username, $password)) {
			$mqtt->publish($this->input->post('id_alat'), json_encode($data), 0, false);
			$mqtt->close();
			echo 'data AWLR dikirim dengan mqtt';
		} else {
			echo "Time out!\n";
		}*/
		//echo $this->input->post('id_alat');
	}


	public function add_ipcam()
	{
		$tgl=GETDATE();
		$tanggal = $this->input->post('tanggal');
		$jam = $this->input->post('jam');
		$waktu = $tanggal.' '.$jam;

		$data = array (
			'code_logger'=>$this->input->post('id_alat'),
			//'user_id'=>$this->input->post('user_id'),
			'waktu'=>$waktu,
			'sensor1'=>$this->input->post('sensor1'),
			'sensor2'=>$this->input->post('sensor2'),
			'sensor3'=>$this->input->post('sensor3'),
			'sensor4'=>$this->input->post('sensor4'),
			'sensor5'=>$this->input->post('sensor5'),
			'sensor6'=>$this->input->post('sensor6'),
			'sensor7'=>$this->input->post('sensor7'),
			'sensor8'=>$this->input->post('sensor8'),
			'sensor9'=>$this->input->post('sensor9'),
			'sensor10'=>$this->input->post('sensor10'),
			'sensor11'=>$this->input->post('sensor11'),
			'sensor12'=>$this->input->post('sensor12'),
			'sensor13'=>$this->input->post('sensor13'),
			'sensor14'=>$this->input->post('sensor14'),
			'sensor15'=>$this->input->post('sensor15'),
			'sensor16'=>$this->input->post('sensor16'),

		);
		echo '{Berhasil}';
		$tabel = $this->db->where('id_logger',$this->input->post('id_alat'))->get('t_logger')->row();
		if($tabel){
			$this->m_inputdata->add_awr($data,$tabel->tabel_main);
			if(!empty($this->input->post('sn')))
			{
				$query_inf=$this->db->query('select serial_number from t_informasi where logger_id = "'.$this->input->post('id_alat').'"')->row();
				if($query_inf->serial_number != $this->input->post('sn'))
				{
					$updata_inf = array(
						'serial_number'=>$this->input->post('sn'),
					);
					$this->db->where('logger_id', $this->input->post('id_alat'));
					$this->db->update('t_informasi', $updata_inf);
				}
			}
		}else{
			echo 'failed';
		}
	}

	function tes_sihka (){
		$date = $this->input->post('tanggal'). ' ' .$this->input->post('jam');
		$dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $date);
		if ($dateTime !== false) {
			$atomDate = $dateTime->format(DateTime::ATOM);
		}

		$data = array(
			'id_logger'=>$this->input->post('id_alat'),
			'uid'=>"06.13.02170010042",
			'nama_lokasi'=>"POS_ARR_SENDANGTIRTO",
			'tanggal'=>$this->input->post('tanggal'),
			'jam'=>$this->input->post('jam'),
			'tzone'=>$atomDate,
			'latitude'=>'-7.805063',
			'longitude'=>'110.433239',
			'ch'=>$this->input->post('sensor9'),
		);
		$rawData = $this->kirim_balai($data,'M2Q63TialJQtrT8');


		$contextOptions = array(
			'http' => array(
				'method' => 'POST',
				'header' => 'Content-type: text/plain',
				'content' => $rawData
			)
		);
		$context = stream_context_create($contextOptions);

		$url = 'https://sihka.dev-tunnels.id/send/beacon';

		$response = file_get_contents($url, false, $context);

		echo $response;
	}

	function tingkat_status($id_logger,$tma,$waktu2) {
		$tingkat_status = $this->db->where('id_logger',$id_logger)->where('status','1')->get('tingkat_siaga_awlr')->result_array();
		$status = [];
		if($tingkat_status){
			foreach($tingkat_status as $key=>$vl){
				if($tma >= $vl['nilai']){
					$status = $vl;
				}
			}
		}

		if($status){
			$now =date('Y-m-d H:i:s');
			$data = [
				'id_logger'=>$status['id_logger'],
				'id_tingkat_siaga'=>$status['id_status'],
				'tma'=>$tma,
				'datetime'=>$waktu2
			];
			$wk = $this->db->join('t_logger','t_logger.id_logger = notifikasi.id_logger')->where('notifikasi.id_logger',$id_logger)->order_by('datetime','desc')->limit(1)->get('notifikasi')->row();
			if($wk){
				$waktu = date('Y-m-d H:i:s', strtotime('+'.$wk->jeda_notif. ' minute', strtotime($wk->datetime)));

				if($wk->id_tingkat_siaga != $status['id_status']){
					$this->db->insert('notifikasi',$data);
					$this->kirim_fcm($id_logger, $tma,$status['nama']);
				}elseif($now >= $waktu){
					$this->db->insert('notifikasi',$data);
					$this->kirim_fcm($id_logger, $tma,$status['nama']);
				}
				echo json_encode($data);	
			}else{
				$this->db->insert('notifikasi',$data);
				$this->kirim_fcm($id_logger, $tma,$status['nama']);
			}
		}else{
			$id = $this->db->where('id_logger',$id_logger)->where('status','0')->get('tingkat_siaga_awlr')->row();
			$wk = $this->db->where('id_logger',$id_logger)->order_by('datetime','desc')->limit(1)->get('notifikasi')->row();
			if($id){
				$data = [
					'id_logger'=>$id_logger,
					'id_tingkat_siaga'=>$id->id_status,
					'tma'=>$tma,
					'datetime'=>$waktu2
				];
				if($wk){
					if($wk->id_tingkat_siaga != $id->id_status){
						$this->db->insert('notifikasi',$data);
						$this->kirim_fcm($id_logger, $tma,'Aman');
					}
					echo $wk->id_tingkat_siaga . ' - '.$id->id;
				} 
			}
		}
	}

	function tingkat_status2() {
		$id_logger = $this->input->get('id_logger');
		$tma = $this->input->get('tma');
		$waktu2= $this->input->get('waktu');
		$tingkat_status = $this->db->where('id_logger',$id_logger)->where('status','1')->get('tingkat_siaga_awlr')->result_array();

		$status = [];
		if($tingkat_status){
			foreach($tingkat_status as $key=>$vl){
				if($tma >= $vl['nilai']){
					$status = $vl;
				}
			}
		}


		if($status){
			$now =date('Y-m-d H:i:s');
			$data = [
				'id_logger'=>$status['id_logger'],
				'id_tingkat_siaga'=>$status['id_status'],
				'tma'=>$tma,
				'datetime'=>$waktu2
			];
			$wk = $this->db->join('t_logger','t_logger.id_logger = notifikasi.id_logger')->where('notifikasi.id_logger',$id_logger)->order_by('datetime','desc')->limit(1)->get('notifikasi')->row();
			echo json_encode($wk);
			if($wk){
				$waktu = date('Y-m-d H:i:s', strtotime('+'.$wk->jeda_notif. ' minute', strtotime($wk->datetime)));
				//$this->kirim_fcm($id_logger, $tma,$status['nama']);
				if($wk->id_tingkat_siaga != $status['id_status']){
					//$this->db->insert('notifikasi',$data);

					$this->kirim_fcm($id_logger, $tma,$status['nama']);
				}elseif($now >= $waktu){
					//					$this->db->insert('notifikasi',$data);

					$this->kirim_fcm($id_logger, $tma,$status['nama']);
				}
				//echo json_encode($data);	
			}else{
				$this->db->insert('notifikasi',$data);
				$this->kirim_fcm($id_logger, $tma,$status['nama']);
			}
		}else{
			$id = $this->db->where('id_logger',$id_logger)->where('status','0')->get('tingkat_siaga_awlr')->row();
			$wk = $this->db->where('id_logger',$id_logger)->order_by('datetime','desc')->limit(1)->get('notifikasi')->row();
			if($id){
				$data = [
					'id_logger'=>$id_logger,
					'id_tingkat_siaga'=>$id->id_status,
					'tma'=>$tma,
					'datetime'=>$waktu2
				];
				if($wk){
					if($wk->id_tingkat_siaga != $id->id_status){
						$this->db->insert('notifikasi',$data);
						$this->kirim_fcm($id_logger, $tma,'Aman');
					}
					echo $wk->id_tingkat_siaga . ' - '.$id->id;
				} 
			}
		}
	}

	public function kirim_fcm ($id_logger,$tma,$nama_status) {
		//$id_logger = $this->input->get('id_logger');
		//$tma = $this->input->get('tma');
		//$nama_status= $this->input->get('status');
		$nama_lokasi = $this->db->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')->where('id_logger',$id_logger)->get('t_logger')->row();
		$serverKey = 'AAAA-KIUWcg:APA91bE1gcaBhCHVo4xLTpJy6WIyZszoDtHH8lN7e8lICOubx6uNM2GdcVzNeBFB26FDBY174TR0W357ZaAynotHsQj3agxhx2j4D_wch7OuQIp4bqU2QAcD3uOGPz_8T4Ry7cGCwLMQ';
		$deviceToken = '/topics/bbws-so';

		$message = [
			'to' => $deviceToken,
			"notification" => [
				"android_channel_id" => 'bbws-so',
				"title" => $nama_lokasi->nama_lokasi,
				"body" => $nama_status. " - TMA : " . $tma .' m',
				"priority" => 'high',
			],
		];

		$options = [
			'http' => [
				'header' => [
					'Authorization: key=' . $serverKey,
					'Content-Type: application/json',
				],
				'method' => 'POST',
				'content' => json_encode($message),
			],
		];

		$context = stream_context_create($options);
		$result = file_get_contents('https://fcm.googleapis.com/fcm/send', false, $context);

		if ($result === false) {
			echo 'Error sending FCM notification';
		} else {
			echo 'FCM notification sent successfully';
		}
	}


	public function kirim_fcm2 () {
		$id_logger = $this->input->get('id_logger');
		$tma = $this->input->get('tma');
		$nama_status= $this->input->get('status');
		$nama_lokasi = $this->db->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')->where('id_logger',$id_logger)->get('t_logger')->row();
		$serverKey = 'AAAA-KIUWcg:APA91bE1gcaBhCHVo4xLTpJy6WIyZszoDtHH8lN7e8lICOubx6uNM2GdcVzNeBFB26FDBY174TR0W357ZaAynotHsQj3agxhx2j4D_wch7OuQIp4bqU2QAcD3uOGPz_8T4Ry7cGCwLMQ';
		$deviceToken = '/topics/bbws-so';

		$message = [
			'to' => $deviceToken,
			"notification" => [
				"android_channel_id" => 'bbws-so',
				"title" => $nama_lokasi->nama_lokasi,
				"body" => $nama_status. " - TMA : " . $tma .' m',
				"priority" => 'high',
			],
		];

		$options = [
			'http' => [
				'header' => [
					'Authorization: key=' . $serverKey,
					'Content-Type: application/json',
				],
				'method' => 'POST',
				'content' => json_encode($message),
			],
		];

		$context = stream_context_create($options);
		$result = file_get_contents('https://fcm.googleapis.com/fcm/send', false, $context);

		if ($result === false) {
			echo 'Error sending FCM notification';
		} else {
			echo 'FCM notification sent successfully';
		}
	}

	############################################################################
	function cek_sinkron()
	{
		$tanggal=$this->input->post('tanggal');
		$jam=$this->input->post('jam');
		$koded=$this->input->post('kode_database');
		$nilai=$this->input->post('nilai');
		$kodep=$this->input->post('kode_penyedia');
		$model=$this->input->post('model');

		$datakirim=array(
			'tanggal' => $this->input->post('tanggal'),
			'jam' => $this->input->post('jam'),
			'idlogger' => $this->input->post('kode_database'),
			'nilai' =>$this->input->post('nilai')

		);
		$data=array( 'data' =>json_encode($datakirim));
		$this->m_inputdata->add_sinkron($datakirim);

	}
	######################### FTP ####################
	function sinkrondatabyrequest($idlogger) {
		//$idlogger = $this->input->get('idlogger');
		$cek_sinkron = $this->db->query('select * from set_sinkronisasi where idlogger = "'.$idlogger.'"');
		$set = $cek_sinkron->row();
		$date=date_create($set->tanggal);
		$tanggal = date_format($date,'Ymd');
		$file_name = $set->idlogger.'-'.$tanggal.'.csv';

		//$tabel = $this->input->get('tabel');
		$file_path =  './filelogger/'.$file_name;
		$idlogger = substr( $file_name, 0, 5 );

		if(file_exists($file_path))
		{
			$ceklogger=$this->db->query('select * from t_logger INNER JOIN kategori_logger ON t_logger.kategori_log=kategori_logger.id_katlogger where id_logger = "'.$idlogger.'"');

			if($ceklogger->num_rows() == 0)
			{
				//$cek = $ceklogger->row();
				//$tabel = $cek->tabel;
				$tabel = 't_demo';
			}
			else{

				$cek = $ceklogger->row();
				$tabel = $cek->tabel;
			}

			if ($this->csvimport->parse_file($file_path)) {
				$csv_array = $this->csvimport->parse_file($file_path);

				foreach ($csv_array as $row) {

					$cekdata=$this->db->query('select waktu,code_logger from '.$tabel.'  where code_logger="'.$row['id_alat'].'" and waktu = "'.$row['tanggal'].' '.$row['jam'].'"');
					//$cekdata=$this->db->query('select * from '.$tabel.'  where code_logger="'.$idlogger.'" and waktu = "'.$row['tanggal'].' '.$row['jam'].'"');
					if($cekdata->num_rows() == 0)
					{
						$insert_data = array(

							'code_logger'=>$row['id_alat'],
							'waktu'=>$row['tanggal'].' '.$row['jam'],
							'sensor1'=>$row['sensor1'],
							'sensor2'=>$row['sensor2'],
							'sensor3'=>$row['sensor3'],
							'sensor4'=>$row['sensor4'],
							'sensor5'=>$row['sensor5'],
							'sensor6'=>$row['sensor6'],
							'sensor7'=>$row['sensor7'],
							'sensor8'=>$row['sensor8'],
							'sensor9'=>$row['sensor9'],
							'sensor10'=>$row['sensor10'],
							'sensor11'=>$row['sensor11'],
							'sensor12'=>$row['sensor12'],
							'sensor13'=>$row['sensor13'],
							'sensor14'=>$row['sensor14'],
							'sensor15'=>$row['sensor15'],
							'sensor16'=>$row['sensor16'],
						);
						//
						$this->m_inputdata->insert_ftp($insert_data,$tabel);

					}
					else{
						echo 'Data sudah ada';
					}

				}


				//echo 'Berhasil sinkron data';

			} else {
				echo 'gagal parsing';
			}
			$data_update = array(
				'tanggal' => '0',
			);
			$this->m_inputdata->update_set($idlogger,$data_update);
			unlink($file_path);


		}
		else {
			echo 'File tidak ditemukan';
		}
	}

}
