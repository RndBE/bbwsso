<?php
class M_inputdata extends CI_Model{
	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('default', true);
	}

	function cek_sinkron($data)
	{
		$this->db->insert('cek_sinkron',$data);
		return;

	}

	function add_sinkron($data)
	{
		$this->db->insert('cek_sinkron',$data);
		return;

	}

	function update_sinkron($idlogger,$tanggal,$jam,$datasinkron)
	{
		$this->db->where('idlogger',$log_id);
		$this->db->where('tanggal',$tanggal);
		$this->db->where('jam',$jam);
		$this->db->update('cek_sinkron',$datasinkron);
		return;
	}
	########################## Buat ARR #####################

	function add_awr($data,$tabel)
	{
		$this->db->insert($tabel,$data);
		return;
	}
	function update_tempawr($idlogger,$data,$tabel)
	{
		$this->db->where('code_logger',$idlogger);
		$this->db->update($tabel,$data);
		return;

	}
	
	########################## Buat AFMR #####################

	function add_afmr($data,$tabel)
	{
		$this->db->insert($tabel,$data);
		return;
	}
	function update_tempafmr($idlogger,$data)
	{
		$this->db->where('code_logger',$idlogger);
		$this->db->update('temp_afmr',$data);
		return;

	}

	########################## Buat AWLR #####################
	function add_awlr($data)
	{
		$this->db->insert('awlr',$data);
		return;
	}
	
	function add_awlr2($data,$tabel)
	{
		$this->db->insert($tabel,$data);
		return;
	}
	function update_tempawlr($idlogger,$data)
	{
		$this->db->where('code_logger',$idlogger);
		$this->db->update('temp_awlr',$data);
		return;

	}

	########################## Buat ARR #####################

	function add_arr($data)
	{
		$this->db->insert('arr',$data);
		return;
	}

	function update_arr($data)
	{
		$this->db->where('code_logger',$this->input->post('id_alat'));
		$this->db->update('temp_arr',$data);		
	}
	
		/* function update_risetJWT($data)
	{
		$this->db->where('no',$this->input->post('id_alat'));
		$this->db->update('temp_arr',$data);		
	} */

	#################### CRUD #########################################
	function edit_data_awlr($where,$table){		
		return $this->db->get_where($table,$where);
	}

	function update_data_awlr_crud($where,$data,$table){
		//echo $data;
		$this->db->where($where);
		$this->db->update($table,$data);
	}	

	function hapus_awlr($where,$table){
		$this->db->where($where);
		$this->db->delete($table);
	}

	function edit_data_arr($where,$table){		
		return $this->db->get_where($table,$where);
	}

	function update_data_arr_crud($where,$data,$table){
		//echo $data;
		$this->db->where($where);
		$this->db->update($table,$data);
	}	

	function hapus_arr($where,$table){
		$this->db->where($where);
		$this->db->delete($table);
	}

	function update_sn($idlogger,$data)
	{
		$this->db->where('logger_id',$idlogger);
		$this->db->update('t_informasi',$data);
		return;

	}


	########### input SInkron  ###############
	function insert_ftp($data,$tabel) {
		$this->db->insert($tabel, $data);
		return;
	}

	function update_set($id,$data)
	{
		$this->db->where('idlogger', $id);
		$this->db->update('set_sinkronisasi',$data);
	}
	################################ END ###########################


	function add_baterai($data)
	{
		$this->db->insert('baterai',$data);
		return;
	}

	function update_baterai($data)
	{
		$this->db->where('code_logger',$this->input->post('id_alat'));
		$this->db->update('temp_baterai',$data);		
	}
	

	
}