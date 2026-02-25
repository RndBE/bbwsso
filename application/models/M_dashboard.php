<?php
class M_dashboard extends CI_Model {

	function __construct(){
		parent::__construct();
		
	}

	 function kategori_logger()        
	 {
		 $this->db->select('*')->from('kategori_logger');
		 $query=$this->db->get();
		 if($query->num_rows() > 0)
		 {
			 return $query->result();
		 }
		 else
		 {
			 echo "Tidak Ada Kategori Logger";
			 return array();
		 }

	 }

    function get_logger($idkat)        
	
	{
	
		$this->db->select('*');
		$this->db->from('t_logger');
		$this->db->join('t_lokasi', 't_logger.lokasi_logger = t_lokasi.idlokasi');
		$this->db->where('kategori_log',$idkat);
		$query=$this->db->get();
		if($query->num_rows() > 0)
		{
			return $query->result();
		}
		else
		{
			echo "Tidak Ada Logger";
			return array();
		}

	}
	function get_logger2($idkat)        
	{
	
		$this->db->select('*');
		$this->db->from('t_logger');
		$this->db->join('t_lokasi', 't_logger.lokasi_logger = t_lokasi.idlokasi');
		$this->db->where('kategori_log',$idkat);
		return $this->db->get()->result();

	}

/*	function sensor($id_logger)
	{
		$this->db->select('*');
		$this->db->from('sensor_logger');
		$this->db->where('logger_id',$id_logger);
		$query=$this->db->get();
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
		//	echo "Tidak ada Sensor";
			return array();
		}

	}
*/
	function parameter($id_logger)
	{
		$this->db->select('*');
		$this->db->from('parameter_sensor');
		$this->db->where('logger_id',$id_logger);
		$query=$this->db->get();
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
		//	echo "Tidak ada Sensor";
			return array();
		}

	}

	function dataset($id_sensor)
	{
		$this->db->where('sensorid',$id_sensor);
		$query=$this->db->get('t_dataset');
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
		//	echo "Tidak ada Sensor";
			return array();
		}


	}

	function data($id_logger,$tabel)
	{

		$this->db->where('code_logger',$id_logger);
		$this->db->limit('1');
		$this->db->order_by('waktu','desc');
		
		$query=$this->db->get($tabel);
		if($query->num_rows()>0)
		{
			return $query->result();
		}
		else
		{
			echo "Tidak Ada data";
			return array();
		}

	}
			
}

	

