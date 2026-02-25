<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Komparasi extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		$this->load->library('csvimport');
		$this->load->model('m_awlr');
	}

	function settgl2()
	{
		$tgl = str_replace('/', '-', $this->input->post('tgl'));
		$this->session->set_userdata('tanggal_komparasi', $tgl);
		$this->session->set_userdata('pada_komparasi', $tgl);
		redirect('komparasi');
	}


	public function hapus_awlr()
	{
		$this->session->unset_userdata('id_logger_komparasi_1');
		$this->session->unset_userdata('kolom_komparasi_1');
		redirect('komparasi');
	}
	public function hapus_awlr2()
	{
		$this->session->unset_userdata('id_logger_komparasi_3');
		$this->session->unset_userdata('kolom_komparasi_3');
		redirect('komparasi');
	}

	public function hapus_arr()
	{
		$this->session->unset_userdata('namalokasi_komparasi_2');
		$this->session->unset_userdata('id_logger_komparasi_2');
		$this->session->unset_userdata('kolom_komparasi_2');
		redirect('komparasi');
	}

	function ganti_logger(){
		$id_logger = $this->input->post('id_logger');
		$id_lama = $this->input->post('id_lama');
		$sess = $this->session->userdata('logger_komparasi'); 
		$key = array_search($id_lama, $sess);

		$sess[$key] = $id_logger; 

		$this->session->set_userdata('logger_komparasi',$sess);
		redirect('komparasi'); 
	}

	function tambah_logger(){
		$id_logger = $this->input->post('id_logger');		
		$sess = $this->session->userdata('logger_komparasi'); 

		array_push($sess,$id_logger);
		$this->session->set_userdata('logger_komparasi',$sess);
		redirect('komparasi'); 
	}

	function get_logger($id_kategori){
		$sess = [];
		$sess = $this->session->userdata('logger_komparasi'); 

		$data = $this->db->join('t_lokasi','t_lokasi.idlokasi = t_logger.lokasi_logger')->where('t_logger.kategori_log',$id_kategori)->get('t_logger')->result_array();
		$list= '';

		if ($id_kategori === '6') {
			$id_get = 'awr';
		} elseif ($id_kategori === '7') {
			$id_get = 'arr';
		} else {
			$id_get = 'awlr';
		}

		foreach($data as $key=>$val){
			if(!in_array($val["id_logger"].'_bbws', $sess) ){
				$list .= '<option value="'.$val["id_logger"].'_bbws">'.$val["nama_lokasi"].'</option>';
			}
		}
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://dpupesdm.monitoring4system.com/integrasi/get_logger',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => array('id_kategori' => $id_get,'logger_komparasi' => json_encode($sess)),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		echo $list.$response;
	}

	function index(){
		if (!$this->session->userdata('pada_komparasi')) {
			$this->session->set_userdata('pada_komparasi', date('Y-m-d'));
		}
		$tanggal = $this->session->userdata('pada_komparasi');

		$session_logger = $this->session->userdata('logger_komparasi'); 
		
		$selected       = [];
		$chart_name     = '';
		$chart_legend   = '1';

		if ($session_logger) {
			$is_single = count($session_logger) === 1;

			foreach ($session_logger as $logger_id) {
				$id_logger_real = explode('_',$logger_id)[0];
				$aset = explode('_',$logger_id)[1];
				if($aset == 'bbws'){
					$logger = $this->db
						->join('kategori_logger','kategori_logger.id_katlogger = t_logger.kategori_log')
						->join('t_lokasi','t_lokasi.idlokasi = t_logger.lokasi_logger')
						->where('id_logger', $id_logger_real)
						->get('t_logger')->row();

					$logger_category = $this->db
						->join('t_lokasi','t_lokasi.idlokasi = t_logger.lokasi_logger')
						->where('t_logger.kategori_log', $logger->kategori_log)
						->get('t_logger')->result_array();

					$parameter = $this->db
						->where('logger_id', $id_logger_real)
						->where('parameter_utama', '1')
						->get('parameter_sensor')->row_array();
					$list_category = array_map(function($row) use ($session_logger){
						return [
							'id_logger'    => $row['id_logger'].'_bbws',
							'kategori_log' => $row['kategori_log'],
							'nama_lokasi'  => $row['nama_lokasi'],
						];
					}, array_filter($logger_category, function($row) use ($session_logger){
						return !in_array($row['id_logger'].'_bbws', $session_logger);
					}));

					$list_category[] = [
						'id_logger'    => $id_logger_real.'_bbws',
						'kategori_log' => $logger->kategori_log,
						'nama_lokasi'  => $logger->nama_lokasi,
					];

					$is_spline  = $parameter['tipe_graf'] === 'spline';
					$agg_func   = $is_spline ? 'avg' : 'sum';
					$sensor_name = ($is_spline ? 'Rerata_' : 'Akumulasi_') . $parameter['nama_parameter'];

					$query = $this->db->query("
                SELECT 
                    waktu,
                    HOUR(waktu)  as jam,
                    DAY(waktu)   as hari,
                    MONTH(waktu) as bulan,
                    YEAR(waktu)  as tahun,
                    {$agg_func}({$parameter['kolom_sensor']}) as {$sensor_name}
                FROM {$logger->tabel_main}
                WHERE code_logger='{$id_logger_real}'
                  AND waktu BETWEEN '{$tanggal} 00:00' AND '{$tanggal} 23:59'
                GROUP BY YEAR(waktu), MONTH(waktu), DAY(waktu), HOUR(waktu)
                ORDER BY waktu ASC
            ");

					$data_chart = [];
					$data_value = [];
					foreach ($query->result() as $row) {
						$val = number_format($row->$sensor_name, 3, '.', '');
						$data_chart[] = "[ Date.UTC({$row->tahun}," . ($row->bulan-1) . ",{$row->hari},{$row->jam}),{$val} ]";
						$data_value[] = [
							'nilai' => $val,
							'waktu' => $row->waktu,
							'jam'   => $row->jam,
						];
					}
					for ($i = 0; $i < 24; $i++) {
						if (array_search($i, array_column($data_value, 'jam')) === false) {
							$jam = str_pad($i, 2, '0', STR_PAD_LEFT);
							$data_value[] = [
								'nilai' => '-',
								'jam'   => $jam,
								'waktu' => "{$tanggal} {$jam}:00:00"
							];
						}
					}
					array_multisort(array_column($data_value, 'waktu'), SORT_ASC, $data_value);

					$y_axis = ($is_single || $is_spline) ? 0 : 1;

					$selected[] = [
						'id_logger'     => $logger_id,
						'nama_kategori' => $logger->nama_kategori,
						'nama_lokasi'   => $logger->nama_lokasi,
						'list_kategori' => $list_category,
						'y_axis'        => $y_axis,
						'parameter'     => $parameter,
						'nama_chart'    => $sensor_name,
						'data'          => $data_chart,
						'data_nilai'    => $data_value,
						'tipe_graf'     => $parameter['tipe_graf'],
						'satuan'        => $parameter['satuan']
					];
					
				}else{
					$curl = curl_init();
					$kirim = array('id_logger' => $id_logger_real,'tanggal'=>$tanggal,'session_logger' => json_encode($session_logger));
					curl_setopt_array($curl, array(
						CURLOPT_URL => 'https://dpupesdm.monitoring4system.com/integrasi/komparasi',
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_ENCODING => '',
						CURLOPT_MAXREDIRS => 10,
						CURLOPT_TIMEOUT => 0,
						CURLOPT_FOLLOWLOCATION => true,
						CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
						CURLOPT_CUSTOMREQUEST => 'POST',
						CURLOPT_POSTFIELDS => $kirim,
					));

					$response = curl_exec($curl);
					$hasil = json_decode($response,true);
					$selected[] = $hasil;
					curl_close($curl);
				}
			}
			$kategori_list = array_column($selected, 'nama_kategori');
			if (in_array('AWS', $kategori_list) || in_array('ARR', $kategori_list)) {
				if (!in_array('AWLR', $kategori_list)) {
					$chart_legend = '1';
					$chart_name   = "Akumulasi Curah Hujan";
				} else {
					$chart_legend = '3';
					$chart_name   = "Akumulasi Curah Hujan dan Rerata Tinggi Muka Air";
				}
			} else {
				$chart_legend = '2';
				$chart_name   = "Rerata Tinggi Muka Air";
			}

			foreach ($selected as $key => $row) {
				if ($row['tipe_graf'] === 'column') {
					$selected[$key]['y_axis'] = ($chart_legend === '1') ? 0 : 1;
				}
			}
		}

		$kategori = $this->db
			->where('view','1')
			->where('nama_kategori !=','AFMR')
			->get('kategori_logger')->result_array();

		$data = [
			'chart_name'   => $chart_name,
			'chart_legend' => $chart_legend,
			'kategori'     => $kategori,
			'selected'     => $selected,
			'jumlah'       => count($selected),
			'konten'       => 'konten/back/komparasi',
		];

		$this->load->view('template_admin/site', $data);
	}

	function hapus_komparasi($id_logger) {
		
		$sess = $this->session->userdata('logger_komparasi');
		$a =array_values( array_diff($sess,[$id_logger]));
		
		$this->session->set_userdata('logger_komparasi',$a);
		redirect('komparasi'); 
	}

	function export_excel (){

		include APPPATH.'third_party/PHPExcel/PHPExcel.php';

		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		// Settingan awal fil excel
		$excel->getProperties()->setCreator('Beacon Engineering')
			->setTitle("Data")
			->setDescription("Data Komparasi pada - ". $this->session->userdata('pada_komparasi'));
		$excel->setActiveSheetIndex(0)->setCellValue('B2', "Data Komparasi pada - ". $this->session->userdata('pada_komparasi'));
		$data = json_decode(htmlspecialchars_decode($this->input->post('data')));

		$title = $this->input->post('title');
		$column = 'C';
		$row = '4';
		$excel->setActiveSheetIndex(0)->setCellValue('B3', 'Waktu');
		$excel->setActiveSheetIndex(0)->mergeCells('B3:B4');
		$excel->setActiveSheetIndex(0)->getStyle('B3')->getAlignment()->applyFromArray(
			array('vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,)
		);
		foreach($data as $key=>$v){
			$cl = $column ++;

			$excel->setActiveSheetIndex(0)->setCellValue($cl . '3', $v->nama_lokasi);
			$s = $row + 1;
			$excel->setActiveSheetIndex(0)->setCellValue($cl . '4','('. $v->nama_chart.')');

			foreach($v->data_nilai as $k =>$vl){
				$rows = $row + 1 + $k ;
				$excel->setActiveSheetIndex(0)->setCellValue('B' . $rows, $vl->waktu);
				$satuan = $vl->nilai != '-' ? $v->satuan:'';
				$excel->setActiveSheetIndex(0)->setCellValue($cl . $rows, $vl->nilai . ' ' .$satuan);
			}
		}
		foreach(range('B','L') as $columnID) {
			$excel->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="Data Komparasi - '.$this->session->userdata('pada_komparasi').'.xlsx"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
	}

}
