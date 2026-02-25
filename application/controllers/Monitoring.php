<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);
class Monitoring extends CI_Controller
{
	function __construct() {
		parent::__construct();
		if(!$this->session->userdata('logged_in'))
		{
			redirect('login');
		}
	}

	public function index () {
		$jenis = $this->input->get('format');
		if(!$jenis){
			$jenis = 'horizontal';
		}
		if($jenis == 'vertikal'){
			$this->vertikal();
		}else{
			$this->horizontal();
		}
	}

	public function horizontal (){
		if ($this->session->userdata('tanggal_rekap1') == '' or $this->session->userdata('tanggal_rekap2') == '') {
			$this->session->set_userdata('tanggal_rekap1', date('Y-m-d').' 00:00:00');
			$this->session->set_userdata('tanggal_rekap2', date('Y-m-d').' 23:00:00'); 
		};

		$id_kategori = $this->session->userdata('id_kategori_rekap');

		$tanggal_rekap = $this->session->userdata('tanggal_rekap1');
		$tanggal_rekap2 = $this->session->userdata('tanggal_rekap2');

		$data['kategori'] = $this->db->where('id_katlogger !=','8')->get('kategori_logger')->result_array();

		$data['data_rekap'] = array();
		$data['logger'] = $this->db->select('t_logger.id_logger, t_logger.nama_logger,t_logger.tabel_main, t_logger.kategori_log,kategori_logger.controller')->join('kategori_logger','kategori_logger.id_katlogger = t_logger.kategori_log')->where('t_logger.kategori_log', $id_kategori)->get('t_logger')->result_array();

		$start_timestamp = strtotime($tanggal_rekap);
		$end_timestamp = strtotime($tanggal_rekap2);
		$diff_in_seconds = $end_timestamp - $start_timestamp;
		$hours = $diff_in_seconds / (60 * 60);
		$day=[];
		$sr = [];
		foreach($data['logger'] as $key=>$val){
			$data_sensor = $this->db->get_where('parameter_sensor',array('logger_id'=>$val['id_logger'], 'parameter_utama'=>'1'))->row();
			if($val['kategori_log'] == '2'){
				$select = 'avg(' . $data_sensor->kolom_sensor . ') as ' . 'nilai';
			}else{
				$select = 'sum(' . $data_sensor->kolom_sensor . ') as ' . 'nilai';
			}

			$mulai_waktu = date('Y-m-d H',strtotime($tanggal_rekap)).':00';
			$selesai_waktu = date('Y-m-d H',strtotime($tanggal_rekap2)).':59';

			$query_data = $this->db->query("SELECT waktu,HOUR(waktu) as jam," . $select . " FROM " . $val['tabel_main'] . " where code_logger='" . $val['id_logger'] . "' and waktu >= '".$mulai_waktu."' and waktu <= '".$selesai_waktu."' group by HOUR(waktu),DAY(waktu),MONTH(waktu),YEAR(waktu) order by waktu asc;")->result_array();

			$result = [];
			$sr = [];
			for ($current_timestamp = $start_timestamp; $current_timestamp <= $end_timestamp; $current_timestamp += 3600) {
				$current_date = date('Y-m-d H:00:00', $current_timestamp);
				$found = false;
				foreach ($query_data as $item) {
					$wk = date('Y-m-d H:00:00', strtotime($item['waktu']));
					if ($wk === $current_date) {
						$result[] = $item;
						$found = true;
						break;
					}
				}

				if (!$found) {
					$result[] = ['waktu' => $current_date, 'nilai' => '-'];
				}

				$current_date = date('Y-m-d', $current_timestamp);
				$current_hour = date('H:00', $current_timestamp);

				if (!isset($sr[$current_date])) {
					$sr[$current_date] = [];
				}

				$sr[$current_date][] = $current_hour;
			}
			$data['data_rekap'][] = array(
				'id_logger' => $val['id_logger'],
				'nama_logger' => $val['nama_logger'],
				'controller' => $val['controller'],
				'data' => $result,
				'link'=> base_url() . 'analisa/set_sensordash?id_param='.$data_sensor->id_param.'_bbws',
				'id_param'=>$data_sensor->id_param
			);
		}
		$jumlah_jam = 0;
		if($id_kategori){
			foreach($sr as $s){
				$jumlah_jam += count($s);
			}
			$data['nama_logger'] = $this->db->get_where('kategori_logger',array('id_katlogger'=>$id_kategori))->row()->nama_kategori;
			if($data['nama_logger'] == 'ARR' or $data['nama_logger'] == 'AWS'){
				$param = $this->db->get_where('klasifikasi_hujan', array('waktuper'=>'perjam'))->row();
				$param2 = $this->db->get_where('klasifikasi_hujan', array('waktuper'=>'perhari'))->row();
				foreach($data['data_rekap'] as $key=> $dtr){
					$data['data_rekap'][$key]['tabel']= $dtr['controller'];
					$total_hujan = 0;
					foreach($dtr['data'] as $key2 => $dt_q){
						if($dt_q['nilai'] != '-'){
							$total_hujan += $dt_q['nilai'];
							if($dt_q['nilai'] <= $param->hijau) {
								$data['data_rekap'][$key]['data'][$key2]['warna'] = '#D5F0C1';
							}
							elseif($dt_q['nilai'] >= $param->biru && $dt_q['nilai'] < $param->biru_tua) {
								$data['data_rekap'][$key]['data'][$key2]['warna'] = '#70cddd';
							}
							elseif($dt_q['nilai'] >=  $param->biru_tua && $dt_q['nilai'] <  $param->kuning){
								$data['data_rekap'][$key]['data'][$key2]['warna'] = '#35549d';
							}
							elseif($dt_q['nilai'] >=  $param->kuning && $dt_q['nilai'] <  $param->oranye) {
								$data['data_rekap'][$key]['data'][$key2]['warna'] = '#fef216';
							}
							elseif($dt_q['nilai'] >=  $param->oranye && $dt_q['nilai'] <  $param->merah) {
								$data['data_rekap'][$key]['data'][$key2]['warna'] = '#f47e2c';
							}
							elseif($dt_q['nilai'] >=  $param->merah) {
								$data['data_rekap'][$key]['data'][$key2]['warna'] = '#ed1c24';
							}
						}else{
							$data['data_rekap'][$key]['data'][$key2]['warna'] = '#D5F0C1';
						}
					}
					if($total_hujan <= $param2->hijau) {
						$wrn = '#98bc85';
					}
					elseif($total_hujan >= $param2->biru && $total_hujan < $param2->biru_tua) {
						$wrn = '#70cddd';
					}
					elseif($total_hujan >=  $param2->biru_tua && $total_hujan <  $param2->kuning){
						$wrn = '#35549d';
					}
					elseif($total_hujan >=  $param2->kuning && $total_hujan <  $param2->oranye) {
						$wrn = '#fef216';
					}
					elseif($total_hujan >=  $param2->oranye && $total_hujan <  $param2->merah) {
						$wrn = '#f47e2c';
					}
					elseif($total_hujan >=  $param2->merah) {
						$wrn = '#ed1c24';
					}
					$data['data_rekap'][$key]['warna'] = $wrn;
					$data['data_rekap'][$key]['total'] = $total_hujan;
				}
			}else{
				foreach ($data['data_rekap'] as $key => $dtr) {

					$data['data_rekap'][$key]['tabel'] = 'awlr';

					$param = $this->db->order_by('nilai')
						->get_where('tingkat_siaga_awlr', ['id_logger' => $dtr['id_logger']])
						->result_array();

					foreach ($dtr['data'] as $key2 => $dt_q) {

						$warna = '#D5F0C1';

						if ($param && is_numeric($dt_q['nilai'])) {

							$nl = (float) $dt_q['nilai'];
	
							foreach ($param as $pr_siaga) {
								if ($nl >= $pr_siaga['nilai']) {
									$warna = $pr_siaga['warna'];
								} else {
									break;
								}
							}
						}

						$data['data_rekap'][$key]['data'][$key2]['warna'] = $warna;
					}
				}

			}
		}
		$data['hari'] = $sr;
		$data['jam'] = $jumlah_jam;
		
		$tanggal_rekap = urlencode($this->session->userdata('tanggal_rekap1'));
		$tanggal_rekap2 = urlencode($this->session->userdata('tanggal_rekap2'));
		$data_psda = ['data_rekap'=>[]];
		if($id_kategori == '2'){
			$data_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/horizontal?id_kategori=8&dari='.$tanggal_rekap.'&sampai='.$tanggal_rekap2));
		}elseif($id_kategori == '6'){
			$data_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/horizontal?id_kategori=awr&dari='.$tanggal_rekap.'&sampai='.$tanggal_rekap2));
		}elseif($id_kategori == '7'){
			$data_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/horizontal?id_kategori=arr&dari='.$tanggal_rekap.'&sampai='.$tanggal_rekap2));
		}
		$array  = json_decode(json_encode($data_psda), true);
		$merge = array_merge($data['data_rekap'],$array['data_rekap']);

		$data['data_rekap'] = $merge;

		$data['konten'] = 'konten/back/v_rekapitulasi2';
		$this->load->view('template_admin/site', $data);
	}

	public function vertikal (){
		if ($this->session->userdata('tanggal_rekap1') == '' or $this->session->userdata('tanggal_rekap2') == '') {
			$this->session->set_userdata('tanggal_rekap1', date('Y-m-d').' 00:00:00');
			$this->session->set_userdata('tanggal_rekap2', date('Y-m-d').' 23:00:00'); 
		};

		$id_kategori = $this->session->userdata('id_kategori_rekap');

		$tanggal_rekap = $this->session->userdata('tanggal_rekap1');
		$tanggal_rekap2 = $this->session->userdata('tanggal_rekap2');

		$data['kategori'] = $this->db->where('id_katlogger !=','8')->get('kategori_logger')->result_array();

		$data['data_rekap'] = array();
		$data['logger'] = $this->db->select('t_logger.id_logger, t_logger.nama_logger,t_logger.tabel_main, t_logger.kategori_log,kategori_logger.controller')->join('kategori_logger','kategori_logger.id_katlogger = t_logger.kategori_log')->where('t_logger.kategori_log', $id_kategori)->get('t_logger')->result_array();
		$start_timestamp = strtotime($tanggal_rekap);
		$end_timestamp = strtotime($tanggal_rekap2);
		$diff_in_seconds = $end_timestamp - $start_timestamp;
		$hours = $diff_in_seconds / (60 * 60);
		$day=[];
		$sr = [];
		foreach($data['logger'] as $key=>$val){
			$data_sensor = $this->db->get_where('parameter_sensor',array('logger_id'=>$val['id_logger'], 'parameter_utama'=>'1'))->row();
			if($val['kategori_log'] == '2'){
				$select = 'avg(' . $data_sensor->kolom_sensor . ') as ' . 'nilai';
			}else{
				$select = 'sum(' . $data_sensor->kolom_sensor . ') as ' . 'nilai';
			}
			$mulai_waktu = date('Y-m-d H',strtotime($tanggal_rekap)).':00';
			$selesai_waktu = date('Y-m-d H',strtotime($tanggal_rekap2)).':59';
			$query_data = $this->db->query("SELECT waktu,HOUR(waktu) as jam," . $select . " FROM " . $val['tabel_main'] . " where code_logger='" . $val['id_logger'] . "' and waktu >= '".$mulai_waktu."' and waktu <= '".$selesai_waktu."' group by HOUR(waktu),DAY(waktu),MONTH(waktu),YEAR(waktu) order by waktu asc;")->result_array();

			$result = [];
			$sr = [];
			for ($current_timestamp = $start_timestamp; $current_timestamp <= $end_timestamp; $current_timestamp += 3600) {		
				$current_date = date('Y-m-d H:00:00', $current_timestamp);
				$found = false;
				foreach ($query_data as $item) {
					$wk = date('Y-m-d H:00:00', strtotime($item['waktu']));
					if ($wk === $current_date) {
						$result[] = [
							'waktu' =>$wk,
							'jam' =>$item['jam'],
							'nilai' =>$item['nilai'],
						];
						$found = true;
						break;
					}
				}

				if (!$found) {
					$result[] = ['waktu' => $current_date, 'nilai' => '-'];
				}

				$current_date = date('Y-m-d', $current_timestamp);
				$current_hour = date('Y-m-d H:00', $current_timestamp);

				if (!isset($sr[$current_date])) {
					$sr[$current_date] = [];
				}

				$sr[$current_date][] = $current_hour;
			}
			$data['data_rekap'][] = array(
				'id_logger' => $val['id_logger'],
				'nama_logger' => $val['nama_logger'],
				'controller' => $val['controller'],
				'data' => $result,
				'id_param'=>$data_sensor->id_param
			);
		}
		$jumlah_jam = 0;
		if($id_kategori){

			foreach($sr as $s){
				$jumlah_jam += count($s);
			}
			$data['nama_logger'] = $this->db->get_where('kategori_logger',array('id_katlogger'=>$id_kategori))->row()->nama_kategori;
			if($data['nama_logger'] == 'ARR' or $data['nama_logger'] == 'AWS'){
				$param = $this->db->get_where('klasifikasi_hujan', array('waktuper'=>'perjam'))->row();
				$param2 = $this->db->get_where('klasifikasi_hujan', array('waktuper'=>'perhari'))->row();
				foreach($data['data_rekap'] as $key=> $dtr){
					$total_hujan = 0;
					$data['data_rekap'][$key]['tabel']= $dtr['controller'];
					foreach($dtr['data'] as $key2 => $dt_q){
						if($dt_q['nilai'] != '-'){
							$total_hujan += $dt_q['nilai'];
							if($dt_q['nilai'] <= $param->hijau) {
								$data['data_rekap'][$key]['data'][$key2]['warna'] = '#D5F0C1';
							}
							elseif($dt_q['nilai'] >= $param->biru && $dt_q['nilai'] < $param->biru_tua) {
								$data['data_rekap'][$key]['data'][$key2]['warna'] = '#70cddd';
							}
							elseif($dt_q['nilai'] >=  $param->biru_tua && $dt_q['nilai'] <  $param->kuning){
								$data['data_rekap'][$key]['data'][$key2]['warna'] = '#35549d';
							}
							elseif($dt_q['nilai'] >=  $param->kuning && $dt_q['nilai'] <  $param->oranye) {
								$data['data_rekap'][$key]['data'][$key2]['warna'] = '#fef216';
							}
							elseif($dt_q['nilai'] >=  $param->oranye && $dt_q['nilai'] <  $param->merah) {
								$data['data_rekap'][$key]['data'][$key2]['warna'] = '#f47e2c';
							}
							elseif($dt_q['nilai'] >=  $param->merah) {
								$data['data_rekap'][$key]['data'][$key2]['warna'] = '#ed1c24';
							}
						}else{
							$data['data_rekap'][$key]['data'][$key2]['warna'] = '#D5F0C1';
						}
					}


					if($total_hujan <= $param2->hijau) {
						$wrn = '#98bc85';
					}
					elseif($total_hujan >= $param2->biru && $total_hujan < $param2->biru_tua) {
						$wrn = '#70cddd';
					}
					elseif($total_hujan >=  $param2->biru_tua && $total_hujan <  $param2->kuning){
						$wrn = '#35549d';
					}
					elseif($total_hujan >=  $param2->kuning && $total_hujan <  $param2->oranye) {
						$wrn = '#fef216';
					}
					elseif($total_hujan >=  $param2->oranye && $total_hujan <  $param2->merah) {
						$wrn = '#f47e2c';
					}
					elseif($total_hujan >=  $param2->merah) {
						$wrn = '#ed1c24';
					}
					$data['data_rekap'][$key]['warna'] = $wrn;
					$data['data_rekap'][$key]['total'] = $total_hujan;
				}
			}else{
				foreach($data['data_rekap'] as $key=> $dtr){
					$data['data_rekap'][$key]['tabel']= 'awlr';
					$param = $this->db->order_by('nilai')->get_where('tingkat_siaga_awlr', array('id_logger'=>$dtr['id_logger']))->result_array();

					if($param){
						foreach($dtr['data'] as $key2 => $dt_q){
							$data['data_rekap'][$key]['data'][$key2]['warna'] = '#D5F0C1';
							if($dt_q['nilai'] != '-'){
								foreach($param as $kyy =>$pr_siaga){
									$nl = number_format($dt_q['nilai'], 1);
									if ($pr_siaga['nilai'] <= $nl) {
										$data['data_rekap'][$key]['data'][$key2]['warna'] = $pr_siaga['warna'];
									} else {
										break; 
									}
								}
							}else{
								$data['data_rekap'][$key]['data'][$key2]['warna'] = '#D5F0C1';
							}
						}
					}else{
						foreach($dtr['data'] as $key2 => $dt_q){
							$data['data_rekap'][$key]['data'][$key2]['warna'] = '#D5F0C1';
						}
					}
					$data['data_rekap'][$key]['warna'] = "#98bc85";
					$data['data_rekap'][$key]['total'] = 0;
				}

			}
		}
		$result= [];
		foreach ($data['data_rekap'] as $logger) {
			foreach ($logger['data'] as $entry) {
				$timestamp = substr($entry['waktu'], 0, 16);
				$result[$timestamp][] = [
					"nilai" => $entry["nilai"],
					"warna" => $entry["warna"]
				];
			}
		}
		$tanggal_rekap = urlencode($tanggal_rekap);
		$tanggal_rekap2 = urlencode($tanggal_rekap2);
		if($id_kategori == '2'){
			$data_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/vertikal?id_kategori=8&dari='.$tanggal_rekap.'&sampai='.$tanggal_rekap2));
		}elseif($id_kategori == '6'){
			$data_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/vertikal?id_kategori=awr&dari='.$tanggal_rekap.'&sampai='.$tanggal_rekap2));
		}elseif($id_kategori == '7'){
			$data_psda = json_decode(file_get_contents('https://dpupesdm.monitoring4system.com/integrasi/vertikal?id_kategori=arr&dari='.$tanggal_rekap.'&sampai='.$tanggal_rekap2));
		}
		$array  = json_decode(json_encode($data_psda), true);
		$merge2 = array_merge($data['data_rekap'],$array['data_rekap']);
		foreach($result as $k => $x){
			$dt_new = array_merge($result[$k],$array['new'][$k]);
			$result[$k] = $dt_new;
		}

		$data['data_rekap'] = $merge2;
		$data['new'] = $result;
		$data['hari'] = $sr;
		$data['jam'] = $jumlah_jam;
		$data['konten'] = 'konten/back/v_rekapitulasi3';
		$this->load->view('template_admin/site', $data);
	}

	function nextExcelColumn($currentColumn, $increment = 1) {
		$columnNumber = 0;

		// Convert column letters to a number
		for ($i = 0; $i < strlen($currentColumn); $i++) {
			$columnNumber = $columnNumber * 26 + (ord($currentColumn[$i]) - ord('A') + 1);
		}

		// Increment the number
		$columnNumber += $increment;

		// Convert the number back to column letters
		$newColumn = '';
		while ($columnNumber > 0) {
			$mod = ($columnNumber - 1) % 26;
			$newColumn = chr($mod + ord('A')) . $newColumn;
			$columnNumber = (int)(($columnNumber - 1) / 26);
		}

		return $newColumn;
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

		$data = json_decode(htmlspecialchars_decode($this->input->post('hari')));
		$parameter = json_decode(htmlspecialchars_decode($this->input->post('parameter')));

		$row = '2';
		$row_hour = '3';
		$columns = 'B';
		$column_hour = 'B';
		foreach($data as $key=>$v){
			$new_column = $column_hour; // Start from the last used column
			$first_column = $new_column; // Store the first column for merging later

			foreach ($v as $vl) {
				// Write the data value in the current $new_column and $row_hour
				$excel->setActiveSheetIndex(0)->setCellValue($new_column . $row_hour, $vl);
				$excel->setActiveSheetIndex(0)->getStyle($new_column . $row_hour)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$excel->setActiveSheetIndex(0)->getStyle($new_column . $row_hour)->getFont()->getColor()->setRGB('FFFFFF');
				$excel->setActiveSheetIndex(0)->getStyle($new_column . $row_hour)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$excel->setActiveSheetIndex(0)->getStyle($new_column . $row_hour)->getFill()->getStartColor()->setRGB('99bd86');
				$excel->setActiveSheetIndex(0)->getStyle($new_column . $row_hour)->getFont()->setBold(true);
				$excel->setActiveSheetIndex(0)->getStyle($new_column . $row_hour)->getFont()->setSize(12);
				$new_column = $this->nextExcelColumn($new_column, 1);
			}

			$excel->setActiveSheetIndex(0)->mergeCells($first_column . $row . ':' .  $this->nextExcelColumn($new_column, -1) . $row);
			$excel->setActiveSheetIndex(0)->getStyle($first_column.'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			// Write the key (header) in the starting column of this loop
			$excel->setActiveSheetIndex(0)->getStyle($column_hour . $row)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$excel->setActiveSheetIndex(0)->getStyle($column_hour . $row)->getFill()->getStartColor()->setRGB('99bd86');
			$excel->setActiveSheetIndex(0)->getStyle($column_hour . $row)->getFont()->getColor()->setRGB('FFFFFF');
			$excel->setActiveSheetIndex(0)->setCellValue($column_hour . $row, $key);
			$excel->setActiveSheetIndex(0)->getStyle($column_hour . $row)->getFont()->setBold(true);
			$excel->setActiveSheetIndex(0)->getStyle($column_hour . $row)->getFont()->setSize(12);
			$column_hour = $new_column;
		}

		$excel->setActiveSheetIndex(0)->mergeCells('A2:A3');
		$excel->setActiveSheetIndex(0)->getStyle('A2:A3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		$excel->setActiveSheetIndex(0)->setCellValue('A2', 'NAMA POS/TANGGAL');
		$excel->setActiveSheetIndex(0)->getStyle('A2')->getFont()->setBold(true);
		$excel->setActiveSheetIndex(0)->getStyle('A2:A3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$excel->setActiveSheetIndex(0)->getStyle('A2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$excel->setActiveSheetIndex(0)->getStyle('A2')->getFill()->getStartColor()->setRGB('99bd86');
		$excel->setActiveSheetIndex(0)->getStyle('A2')->getFont()->getColor()->setRGB('FFFFFF');
		$excel->setActiveSheetIndex(0)->getStyle('A2')->getFont()->setSize(13);
		$row2 = '4';
		$row_hour2 = '4';
		$columns2 = 'A';

		foreach($parameter as $ky=>$v){

			$rw2 = $row2 ++;
			$excel->setActiveSheetIndex(0)->getStyle($columns2 . $rw2)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$excel->setActiveSheetIndex(0)->getStyle($columns2 . $rw2)->getFill()->getStartColor()->setRGB('99bd86');
			$excel->setActiveSheetIndex(0)->getStyle($columns2 . $rw2)->getFont()->getColor()->setRGB('FFFFFF');
			$excel->setActiveSheetIndex(0)->getStyle($columns2 . $rw2)->getFont()->setBold(true);

			$excel->setActiveSheetIndex(0)->getStyle($columns2 . $rw2)->getFont()->setSize(12);
			$excel->setActiveSheetIndex(0)->setCellValue($columns2 . $rw2, $v->nama_logger);
			$column_data = 'B';
			$akumulasi = 0;
			foreach($v->data as $ke=>$vle){
				$cl2 = $column_data ++;
				if($this->session->userdata('id_kategori_rekap') == '7' or $this->session->userdata('id_kategori_rekap') == '6'){
					$excel->setActiveSheetIndex(0)->setCellValue($cl2 . $rw2, number_format((float)$vle->nilai,2,'.',''));
				}else{
					$excel->setActiveSheetIndex(0)->setCellValue($cl2 . $rw2, number_format((float)$vle->nilai,2,'.',''));
				}
				$excel->setActiveSheetIndex(0)->getStyle($cl2 . $rw2)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$excel->setActiveSheetIndex(0)->getStyle($cl2 . $rw2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$excel->setActiveSheetIndex(0)->getStyle($cl2 . $rw2)->getFill()->getStartColor()->setRGB(str_replace('#','',$vle->warna));
				$excel->setActiveSheetIndex(0)->getStyle($cl2 . $rw2)->getFont()->setBold(true);
				$excel->setActiveSheetIndex(0)->getStyle($cl2 . $rw2)->getFont()->setSize(10);
				if($vle->warna == 'white' or $vle->warna == '#fef216' or $vle->warna == '#D5F0C1'){
					$excel->setActiveSheetIndex(0)->getStyle($cl2 . $rw2)->getFont()->getColor()->setRGB('000000');
				}else{
					$excel->setActiveSheetIndex(0)->getStyle($cl2 . $rw2)->getFont()->getColor()->setRGB('FFFFFF');
				}
			}
		}
		$param2 = $this->db->get_where('klasifikasi_hujan', array('waktuper'=>'perhari'))->row();
		if($this->session->userdata('id_kategori_rekap') == '7' or $this->session->userdata('id_kategori_rekap') == '6'){
			$excel->setActiveSheetIndex(0)->setCellValue($column_data . '2','Akumulasi');
			$excel->setActiveSheetIndex(0)->mergeCells($column_data . '2:'.$column_data.'3');
			$excel->setActiveSheetIndex(0)->getStyle($column_data . '2')->getFont()->setBold(true);
			$excel->setActiveSheetIndex(0)->getStyle($column_data . '2:'.$column_data.'3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
			$excel->setActiveSheetIndex(0)->getStyle($column_data . '2:'.$column_data.'3')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$excel->setActiveSheetIndex(0)->getStyle($column_data . '2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$excel->setActiveSheetIndex(0)->getStyle($column_data . '2')->getFill()->getStartColor()->setRGB('99bd86');
			$excel->setActiveSheetIndex(0)->getStyle($column_data . '2')->getFont()->getColor()->setRGB('FFFFFF');
			$excel->setActiveSheetIndex(0)->getStyle($column_data . '2')->getFont()->setSize(13);
		}
		$row2 = '4';
		if($this->session->userdata('id_kategori_rekap') == '7' or $this->session->userdata('id_kategori_rekap') == '6'){
			foreach($parameter as $ky=>$v){
				$rw2 = $row2 ++;
				$total_hujan = 0;
				foreach($v->data as $ke=>$vle){
					if($vle->nilai != '-'){
						$total_hujan += (float)$vle->nilai;
					}
				}
				$excel->setActiveSheetIndex(0)->getStyle($column_data . $rw2)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);

				$excel->setActiveSheetIndex(0)->getStyle($column_data . $rw2)->getFont()->setBold(true);

				$excel->setActiveSheetIndex(0)->getStyle($column_data . $rw2)->getFont()->setSize(12);
				if($total_hujan <= $param2->hijau) {
					$wrn = '98bc85';
				}
				elseif($total_hujan >= $param2->biru && $total_hujan < $param2->biru_tua) {
					$wrn = '70cddd';
				}
				elseif($total_hujan >=  $param2->biru_tua && $total_hujan <  $param2->kuning){
					$wrn = '35549d';
				}
				elseif($total_hujan >=  $param2->kuning && $total_hujan <  $param2->oranye) {
					$wrn = 'fef216';
				}
				elseif($total_hujan >=  $param2->oranye && $total_hujan <  $param2->merah) {
					$wrn = 'f47e2c';
				}
				elseif($total_hujan >=  $param2->merah) {
					$wrn = 'ed1c24';
				}
				if($wrn == 'fef216' or $wrn == 'ed1c24'){
					$excel->setActiveSheetIndex(0)->getStyle($column_data . $rw2)->getFont()->getColor()->setRGB('000000 ');
				}else{
					$excel->setActiveSheetIndex(0)->getStyle($column_data . $rw2)->getFont()->getColor()->setRGB('FFFFFF');
				}

				$excel->setActiveSheetIndex(0)->getStyle($column_data . $rw2)->getFill()->getStartColor()->setRGB($wrn);
				$excel->setActiveSheetIndex(0)->setCellValue($column_data . $rw2, number_format($total_hujan,2,'.','') . ' mm');
			}
		}


		$this->autoSizeColumns($excel,0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$this->input->post("judul").'.xlsx"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
	}


	function export_excel2 (){

		include APPPATH.'third_party/PHPExcel/PHPExcel.php';

		// Panggil class PHPExcel nya
		$excel = new PHPExcel();
		// Settingan awal fil excel
		$title = $this->input->post('title');
		$excel->getProperties()->setCreator('Beacon Engineering')
			->setTitle("Data")
			->setDescription("Data Semua Parameter");

		$data = json_decode(htmlspecialchars_decode($this->input->post('hari')));
		$parameter = json_decode(htmlspecialchars_decode($this->input->post('parameter')));

		$row = '2';
		$row_hour = '3';
		$columns = 'B';
		$column_hour = 'B';
		foreach($data as $key=>$v){

			$excel->setActiveSheetIndex(0)->getStyle($column_hour.'2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			// Write the key (header) in the starting column of this loop
			$excel->setActiveSheetIndex(0)->getStyle($column_hour . '2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$excel->setActiveSheetIndex(0)->getStyle($column_hour . '2')->getFill()->getStartColor()->setRGB('99bd86');
			$excel->setActiveSheetIndex(0)->getStyle($column_hour . '2')->getFont()->getColor()->setRGB('FFFFFF');
			$excel->setActiveSheetIndex(0)->setCellValue($column_hour . '2', $v->nama_logger);
			$excel->setActiveSheetIndex(0)->getStyle($column_hour . '2')->getFont()->setBold(true);
			$excel->setActiveSheetIndex(0)->getStyle($column_hour . '2')->getFont()->setSize(12);
			$column_hour++;
		}

		$excel->setActiveSheetIndex(0)->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		$excel->setActiveSheetIndex(0)->setCellValue('A2', 'TANGGAL/NAMA POS');
		$excel->setActiveSheetIndex(0)->getStyle('A2')->getFont()->setBold(true);
		$excel->setActiveSheetIndex(0)->getStyle('A2:A3')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$excel->setActiveSheetIndex(0)->getStyle('A2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$excel->setActiveSheetIndex(0)->getStyle('A2')->getFill()->getStartColor()->setRGB('99bd86');
		$excel->setActiveSheetIndex(0)->getStyle('A2')->getFont()->getColor()->setRGB('FFFFFF');
		$excel->setActiveSheetIndex(0)->getStyle('A2')->getFont()->setSize(12);

		$row2 = '3';
		$row_hour2 = '3';
		$columns2 = 'A';

		foreach($parameter as $ky=>$v){

			$rw2 = $row2 ++;
			$excel->setActiveSheetIndex(0)->getStyle($columns2 . $rw2)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$excel->setActiveSheetIndex(0)->getStyle($columns2 . $rw2)->getFill()->getStartColor()->setRGB('99bd86');
			$excel->setActiveSheetIndex(0)->getStyle($columns2 . $rw2)->getFont()->getColor()->setRGB('FFFFFF');
			$excel->setActiveSheetIndex(0)->getStyle($columns2 . $rw2)->getFont()->setBold(true);

			$excel->setActiveSheetIndex(0)->getStyle($columns2 . $rw2)->getFont()->setSize(12);
			$excel->setActiveSheetIndex(0)->setCellValue($columns2 . $rw2, $ky);
			$column_data = 'B';

			foreach($v as $ke=>$vle){
				$cl2 = $column_data ++;
				if($this->session->userdata('id_kategori_rekap') == '7' or $this->session->userdata('id_kategori_rekap') == '6'){

					$excel->setActiveSheetIndex(0)->setCellValue($cl2 . $rw2, number_format((float)$vle->nilai,2,'.',''));
				}else{
					$excel->setActiveSheetIndex(0)->setCellValue($cl2 . $rw2, number_format((float)$vle->nilai,2,'.',''));
				}
				$excel->setActiveSheetIndex(0)->getStyle($cl2 . $rw2)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$excel->setActiveSheetIndex(0)->getStyle($cl2 . $rw2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				$excel->setActiveSheetIndex(0)->getStyle($cl2 . $rw2)->getFill()->getStartColor()->setRGB(str_replace('#','',$vle->warna));
				$excel->setActiveSheetIndex(0)->getStyle($cl2 . $rw2)->getFont()->setBold(true);
				$excel->setActiveSheetIndex(0)->getStyle($cl2 . $rw2)->getFont()->setSize(10);
				if($vle->warna == 'white' or $vle->warna == '#fef216' or $vle->warna == '#D5F0C1'){
					$excel->setActiveSheetIndex(0)->getStyle($cl2 . $rw2)->getFont()->getColor()->setRGB('000000');
				}else{
					$excel->setActiveSheetIndex(0)->getStyle($cl2 . $rw2)->getFont()->getColor()->setRGB('FFFFFF');
				}

			}
		}


		$param2 = $this->db->get_where('klasifikasi_hujan', array('waktuper'=>'perhari'))->row();
		if($this->session->userdata('id_kategori_rekap') == '7' or $this->session->userdata('id_kategori_rekap') == '6'){
			$excel->setActiveSheetIndex(0)->setCellValue('A' . $row2,'Akumulasi');
			$excel->setActiveSheetIndex(0)->getStyle('A' . $row2)->getFont()->setBold(true);
			$excel->setActiveSheetIndex(0)->getStyle('A' . $row2)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$excel->setActiveSheetIndex(0)->getStyle('A' . $row2)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$excel->setActiveSheetIndex(0)->getStyle('A' . $row2)->getFill()->getStartColor()->setRGB('99bd86');
			$excel->setActiveSheetIndex(0)->getStyle('A' . $row2)->getFont()->getColor()->setRGB('FFFFFF');
			$excel->setActiveSheetIndex(0)->getStyle('A' . $row2)->getFont()->setSize(13);
		}
		/*
		$row2 = '4';
		foreach($parameter as $ky=>$v){
			$rw2 = $row2 ++;
			$total_hujan = 0;
			foreach($v->data as $ke=>$vle){
				if($vle->nilai != '-'){
					$total_hujan += (float)$vle->nilai;
				}
			}
			$excel->setActiveSheetIndex(0)->getStyle($column_data . $rw2)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);

			$excel->setActiveSheetIndex(0)->getStyle($column_data . $rw2)->getFont()->setBold(true);

			$excel->setActiveSheetIndex(0)->getStyle($column_data . $rw2)->getFont()->setSize(12);
			if($total_hujan <= $param2->hijau) {
				$wrn = '98bc85';
			}
			elseif($total_hujan >= $param2->biru && $total_hujan < $param2->biru_tua) {
				$wrn = '70cddd';
			}
			elseif($total_hujan >=  $param2->biru_tua && $total_hujan <  $param2->kuning){
				$wrn = '35549d';
			}
			elseif($total_hujan >=  $param2->kuning && $total_hujan <  $param2->oranye) {
				$wrn = 'fef216';
			}
			elseif($total_hujan >=  $param2->oranye && $total_hujan <  $param2->merah) {
				$wrn = 'f47e2c';
			}
			elseif($total_hujan >=  $param2->merah) {
				$wrn = 'ed1c24';
			}
			if($wrn == 'fef216' or $wrn == 'ed1c24'){
				$excel->setActiveSheetIndex(0)->getStyle($column_data . $rw2)->getFont()->getColor()->setRGB('000000 ');
			}else{
				$excel->setActiveSheetIndex(0)->getStyle($column_data . $rw2)->getFont()->getColor()->setRGB('FFFFFF');
			}

			$excel->setActiveSheetIndex(0)->getStyle($column_data . $rw2)->getFill()->getStartColor()->setRGB($wrn);
			$excel->setActiveSheetIndex(0)->setCellValue($column_data . $rw2, number_format($total_hujan,2,'.','') . ' mm');
		}
		*/
		$this->autoSizeColumns($excel,0);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="'.$this->input->post("judul").'.xlsx"'); // Set nama file excel nya
		header('Cache-Control: max-age=0');
		$write = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$write->save('php://output');
	}

	function autoSizeColumns($excel, $sheetIndex = 0) {
		// Set the active sheet by index
		$sheet = $excel->setActiveSheetIndex($sheetIndex);

		// Get the highest column (e.g., "ZZ", "AAA")
		$highestColumn = $sheet->getHighestColumn();

		// Loop through each column from "A" to the highest column
		$currentColumn = 'A';
		while ($currentColumn !== $highestColumn) {
			$sheet->getColumnDimension($currentColumn)->setAutoSize(true);
			$currentColumn++;
		}
		// Also include the highest column
		$sheet->getColumnDimension($highestColumn)->setAutoSize(true);
	}


	function set_kategori(){
		$id_kategori = $this->input->post('id_kategori');
		$this->session->set_userdata('id_kategori_rekap', $id_kategori);
		$jenis = $this->input->post('format');
		redirect('monitoring?format='.$jenis);

	}

	function set_tanggal(){
		$tanggal = $this->input->post('tgl1');
		$tanggal2 = $this->input->post('tgl2');
		$this->session->set_userdata('tanggal_rekap1', $tanggal);
		$this->session->set_userdata('tanggal_rekap2', $tanggal2);
		$jenis = $this->input->post('format');
		redirect('monitoring?format='.$jenis);
	}

	function set_tanggal2(){
		$tanggal = $this->input->post('tgl1');
		$tanggal2 = $this->input->post('tgl2');
		$this->session->set_userdata('tanggal_rekap1', $tanggal);
		$this->session->set_userdata('tanggal_rekap2', $tanggal2);
		$jenis = $this->input->post('format');
		redirect('monitoring?format='.$jenis);
	}
}