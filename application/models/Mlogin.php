<?php
class Mlogin extends CI_Model{
	

public  function  ambilPengguna($username,$password)
{
        $data = $this->db
                ->where(array('username' => $username, 'password' => $password))
                ->get('t_user');
        //dicek
        if ($data->num_rows() > 0)
        {
            $user = $data->row();
            //data hasil seleksi dimasukkan ke dalam $session
            $session = array(
                'logged_in' => true,			
				'id_user' => $user->id_user,
                'username' => $user->username,
            	'nama' => $user->nama,
				'leveluser'=>$user->level_user,
				'bidang'=>$user->bidang,
            	'logger_komparasi'=>[]
            );
            //data dari $session akhirnya dimasukkan ke dalam session
            $this->session->set_userdata($session);
            return true;
			
        }
        else
        {
        	$this->session->set_flashdata('message', '<div class="alert alert-danger" role="alert"> Kombinasi Username dan Password tidak cocok.</div>');
          redirect('login');
            return false;
        }
    }
	
	public  function  apiambilPengguna($username,$password)
{
        $data = $this->db
                ->where(array('username' => $username, 'password' => $password))
                ->get('t_user');
        //dicek
        if ($data->num_rows() > 0)
        {
            $user = $data->row();
            //data hasil seleksi dimasukkan ke dalam $session
            $session = array(
               // 'logged_in' => true,
				//'level_user' => $user->level_user,
                'username' => $user->username,
            	'nama' => $user->nama,
            	//'telp' => $user->telp,
            	//'alamat' => $user->alamat,
            	//'id_user'=>$user->id_user,
               // 'foto'=>'https://beacontelemetry.com/image/user/'.$user->foto,
				//'center_map'=>$user->center_map,
				//'zoom_map'=>$user->zoom_map,
				//'kode_instansi'=>$user->kode_instansi,
                
            );
            
           echo json_encode($session);
            
        }
        else
        {
        	echo 'Username dan Password tidak cocok';
        }
    }
	
	public  function  apiambilPengguna2($username,$password)
{
        $data = $this->db
                ->where(array('username' => $username, 'password' => $password))
                ->get('t_user');
        //dicek
        if ($data->num_rows() > 0)
        {
            $user = $data->row();
            //data hasil seleksi dimasukkan ke dalam $session
            $session = array(
               // 'logged_in' => true,
				'level' => $user->level_user,
                'username' => $user->username,
            	'nama' => $user->nama,
            	'telp' => $user->telp,
            	'alamat' => $user->alamat,
            	'instansi'=>$user->instansi,
				'latitude'=>$user->latitude,
				'longitude'=>$user->longitude,
               // 'foto'=>'https://beacontelemetry.com/image/user/'.$user->foto,
				//'center_map'=>$user->center_map,
				//'zoom_map'=>$user->zoom_map,
				//'kode_instansi'=>$user->kode_instansi,
                
            );
            
           echo json_encode($session);
            
        }
        else
        {
        	echo json_encode(array('nama'=>'error'));
        }
    }
}