<?php if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Riwayat extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('m_dashboard');
		$this->load->library('upload');
	}

	function pilih_riwayat()
	{
		$this->session->set_userdata('id_riwayat', $this->input->get('id_riwayat'));
		redirect('riwayat');
	}

	public function index()
	{
		$dt = [];
		$data_op = $this->db->join('t_lokasi', 't_lokasi.idlokasi=lokasi_logger')->get('t_logger')->result_array();
		$key = 0;
		foreach ($data_op as $k => $v) {
			$riwayat = $this->db->where('id_logger', $v['id_logger'])->order_by('tanggal', 'DESC')->get('t_riwayat')->result_array();
			$data_op[$k]['riwayat'] = [];
			if ($riwayat) {
				$ind = $key;
				$dt[$ind] = $v;
				$dt[$ind]['riwayat'] = $riwayat;
				$key += 1;
			}
		}
		if (!$this->session->userdata('id_riwayat')) {
			if (!empty($dt)) {
				$this->session->set_userdata('id_riwayat', $dt[0]['riwayat'][0]['id_riwayat']);
			}
		}
		$selected = $this->session->userdata('id_riwayat');
		$data_riwayat = $this->db->join('t_logger', 't_logger.id_logger = t_riwayat.id_logger')->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')->where('t_riwayat.id_riwayat', $selected)->get('t_riwayat')->row();

		$data['data_op'] = $dt;
		$data['selected'] = $this->db->where('id_riwayat', $selected)->get('t_riwayat')->row();

		if ($data['selected']) {
			$data_gambar = json_decode(file_get_contents(base_url() . 'image/tes.php?folder=riwayat_op&word=' . $data['selected']->gambar));
			$data['gambar'] = $data_gambar ? $data_gambar : [];
		} else {
			$data['gambar'] = [];
		}

		$data['konten'] = 'konten/back/riwayat_op';
		$this->load->view('template_admin/site', $data);
	}

	public function tambah()
	{
		$data['logger_list'] = $this->db->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')->get('t_logger')->result();
		$data['riwayat'] = null;
		$data['form_action'] = base_url('riwayat/simpan');
		$data['konten'] = 'konten/back/riwayat_form';
		$this->load->view('template_admin/site', $data);
	}

	public function simpan()
	{
		$id_logger = $this->input->post('id_logger');
		$tanggal = $this->input->post('tanggal');
		$kendala = $this->input->post('kendala');
		$perbaikan = $this->input->post('perbaikan');

		// Upload file laporan PDF
		$file_name = '';
		if (!empty($_FILES['file_laporan']['name'])) {
			$config_file = [
				'upload_path' => './unduh/laporan_op/',
				'allowed_types' => 'pdf',
				'max_size' => 10240,
				'file_name' => 'laporan_' . date('YmdHis')
			];
			$this->upload->initialize($config_file);
			if ($this->upload->do_upload('file_laporan')) {
				$file_name = $this->upload->data('file_name');
			}
		}

		// Upload gambar multiple
		$gambar_prefix = '';
		if (!empty($_FILES['gambar']['name'][0])) {
			$gambar_prefix = 'riwayat_' . date('YmdHis');
			$files = $_FILES['gambar'];
			for ($i = 0; $i < count($files['name']); $i++) {
				$_FILES['gambar_file'] = [
					'name' => $files['name'][$i],
					'type' => $files['type'][$i],
					'tmp_name' => $files['tmp_name'][$i],
					'error' => $files['error'][$i],
					'size' => $files['size'][$i],
				];
				$ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
				$config_img = [
					'upload_path' => './image/riwayat_op/',
					'allowed_types' => 'jpg|jpeg|png|gif',
					'max_size' => 5120,
					'file_name' => $gambar_prefix . '_' . ($i + 1) . '.' . $ext
				];
				$this->upload->initialize($config_img);
				$this->upload->do_upload('gambar_file');
			}
		}

		$insert = [
			'id_logger' => $id_logger,
			'tanggal' => $tanggal,
			'kendala' => $kendala,
			'perbaikan' => $perbaikan,
			'gambar' => $gambar_prefix,
			'file' => $file_name
		];
		$this->db->insert('t_riwayat', $insert);
		$new_id = $this->db->insert_id();

		$this->session->set_userdata('id_riwayat', $new_id);
		$this->session->set_flashdata('success', 'Riwayat berhasil ditambahkan');
		redirect('riwayat');
	}

	public function edit($id)
	{
		$data['logger_list'] = $this->db->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')->get('t_logger')->result();
		$data['riwayat'] = $this->db->where('id_riwayat', $id)->get('t_riwayat')->row();

		if (!$data['riwayat']) {
			redirect('riwayat');
		}

		$data['form_action'] = base_url('riwayat/update/' . $id);
		$data['konten'] = 'konten/back/riwayat_form';
		$this->load->view('template_admin/site', $data);
	}

	public function update($id)
	{
		$riwayat = $this->db->where('id_riwayat', $id)->get('t_riwayat')->row();
		if (!$riwayat) {
			redirect('riwayat');
		}

		$id_logger = $this->input->post('id_logger');
		$tanggal = $this->input->post('tanggal');
		$kendala = $this->input->post('kendala');
		$perbaikan = $this->input->post('perbaikan');

		$update = [
			'id_logger' => $id_logger,
			'tanggal' => $tanggal,
			'kendala' => $kendala,
			'perbaikan' => $perbaikan,
		];

		// Upload file laporan baru (opsional)
		if (!empty($_FILES['file_laporan']['name'])) {
			// Hapus file lama
			if ($riwayat->file && file_exists('./unduh/laporan_op/' . $riwayat->file)) {
				unlink('./unduh/laporan_op/' . $riwayat->file);
			}
			$config_file = [
				'upload_path' => './unduh/laporan_op/',
				'allowed_types' => 'pdf',
				'max_size' => 10240,
				'file_name' => 'laporan_' . date('YmdHis')
			];
			$this->upload->initialize($config_file);
			if ($this->upload->do_upload('file_laporan')) {
				$update['file'] = $this->upload->data('file_name');
			}
		}

		// Upload gambar baru (opsional, menggantikan semua gambar lama)
		if (!empty($_FILES['gambar']['name'][0])) {
			// Hapus gambar lama
			if ($riwayat->gambar) {
				$old_images = glob('./image/riwayat_op/' . $riwayat->gambar . '*');
				if ($old_images) {
					foreach ($old_images as $img) {
						unlink($img);
					}
				}
			}

			$gambar_prefix = 'riwayat_' . date('YmdHis');
			$files = $_FILES['gambar'];
			for ($i = 0; $i < count($files['name']); $i++) {
				$_FILES['gambar_file'] = [
					'name' => $files['name'][$i],
					'type' => $files['type'][$i],
					'tmp_name' => $files['tmp_name'][$i],
					'error' => $files['error'][$i],
					'size' => $files['size'][$i],
				];
				$ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
				$config_img = [
					'upload_path' => './image/riwayat_op/',
					'allowed_types' => 'jpg|jpeg|png|gif',
					'max_size' => 5120,
					'file_name' => $gambar_prefix . '_' . ($i + 1) . '.' . $ext
				];
				$this->upload->initialize($config_img);
				$this->upload->do_upload('gambar_file');
			}
			$update['gambar'] = $gambar_prefix;
		}

		$this->db->where('id_riwayat', $id)->update('t_riwayat', $update);

		$this->session->set_userdata('id_riwayat', $id);
		$this->session->set_flashdata('success', 'Riwayat berhasil diperbarui');
		redirect('riwayat');
	}

	public function hapus($id)
	{
		$riwayat = $this->db->where('id_riwayat', $id)->get('t_riwayat')->row();
		if (!$riwayat) {
			redirect('riwayat');
		}

		// Hapus file laporan
		if ($riwayat->file && file_exists('./unduh/laporan_op/' . $riwayat->file)) {
			unlink('./unduh/laporan_op/' . $riwayat->file);
		}

		// Hapus gambar terkait
		if ($riwayat->gambar) {
			$images = glob('./image/riwayat_op/' . $riwayat->gambar . '*');
			if ($images) {
				foreach ($images as $img) {
					unlink($img);
				}
			}
		}

		$this->db->where('id_riwayat', $id)->delete('t_riwayat');
		$this->session->unset_userdata('id_riwayat');
		$this->session->set_flashdata('success', 'Riwayat berhasil dihapus');
		redirect('riwayat');
	}
}