<html>
	<head>
		<title><?php echo $idlogger ?></title>
		<meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<style type="text/css">		
	a:hover,a:focus{
    text-decoration: none;
    outline: none;
}
.vertical-tab{
    font-family: 'Fira Sans', sans-serif;
    display: table;
}
.vertical-tab .nav-tabs{
    display: table-cell;
    width: 28%;
    min-width: 28%;
    vertical-align: top;
    border: none;
}
.vertical-tab .nav-tabs li{
   float: none;
   vertical-align: top;
}
.vertical-tab .nav-tabs li a{
    color: #333;
    background-color: #f5f5f5;
    font-size: 18px;
    font-weight: 600;
    letter-spacing: 1px;
    text-align: center;
    text-transform: uppercase;
    padding: 10px 15px;
    margin: 0 9px 5px 0;
    border-radius: 0;
    border: none;
    display: block;
    position: relative;
    overflow: hidden;
    z-index: 1;
    transition: all 0.3s ease 0s;
}
.vertical-tab .nav-tabs li a:hover,
.vertical-tab .nav-tabs li.active a,
.vertical-tab .nav-tabs li.active a:hover{
    color: #fff;
    background-color: transparent;
    border: none;
    text-shadow: 0 0 5px #555;
}
.vertical-tab .nav-tabs li a:before,
.vertical-tab .nav-tabs li a:after{
    content: "";
    background: #bb270fe6;
    width: 80%;
    height: 80%;
    transform: skewX(-15deg);
    position: absolute;
    top: 0;
    left: 150%;
    z-index: -1;
    transition: all 0.3s ease 0s;
}
.vertical-tab .nav-tabs li a:after{
    background: #ed2201;
    left: auto;
    right: 150%;
    top: auto;
    bottom: 0;
}
.vertical-tab .nav-tabs li a:hover:before,
.vertical-tab .nav-tabs li.active a:before{
    left: 7px;
}
.vertical-tab .nav-tabs li a:hover:after,
.vertical-tab .nav-tabs li.active a:after{
    right: 7px;
}
.vertical-tab .tab-content{
    color: #fff;
    background-color: #ed2201;
    font-size: 14px;
    line-height: 25px;
    padding: 20px 25px;
    margin-top: 10px;
    display: table-cell;
    position: relative;
}
.vertical-tab .tab-content h3{
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 0 0 7px 0;
}
@media only screen and (max-width: 479px){
    .vertical-tab .nav-tabs{
        width: 100%;
        display: block;
        border: none;
    }
    .vertical-tab .nav-tabs li a{ margin: 0 0 10px; }
    .vertical-tab .tab-content{
        padding: 20px 15px 5px;
        display: block;
    }
    .vertical-tab .tab-content h3{ font-size: 18px; }
}
	</style>
		
	</head>
	<body>
<div class="container">
	<div class="row text-center">
		<img src="<?php echo base_url()?>image/logo_be.png" class="rounded" alt="Cinque Terre">
              
            </div>
    <div class="row">
        <div class="col-md-12">
            <div class="vertical-tab" role="tabpanel">
                <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active" ><a href="#section1" aria-controls="home" role="tab" data-toggle="tab">Informasi</a></li>
        <li role="presentation" ><a href="#section2" aria-controls="home" role="tab" data-toggle="tab">Data Pengukuran</a></li>
		<li role="presentation" ><a href="#section3" aria-controls="home" role="tab" data-toggle="tab">Informasi User</a></li>
		<li role="presentation" ><a href="#section4" aria-controls="home" role="tab" data-toggle="tab">Aplikasi</a></li>
    </ul>
                <!-- Tab panes -->
                <div class="tab-content tabs">
                    <div role="tabpanel" class="tab-pane fade in active" id="section1">
                       <center> <h3>Informasi</h3></center>
                        <?php
 $q_informasi= $this->db->query("SELECT * FROM t_logger where id_logger ='".$idlogger."' ");
foreach($q_informasi->result() as $info)
{ 
$q_lokasi=$this->db->query("select * from t_lokasi where idlokasi='".$info->lokasi_logger."' ");
 		foreach($q_lokasi->result() as $lok)
		{
			$nama_lokasi= $lok->nama_lokasi;
		}
	
$q_info=$this->db->query("select * from t_informasi where logger_id='".$idlogger."' ");
	foreach($q_info->result() as $inf)
	{
		$ser = $inf->seri;
		$sensor=$inf->sensor;
		
		$nosell=$inf->nosell;
	}
?>
					
<table class="table" style="color:white">
	<tbody>
	<tr>
		<td>ID Logger</td><td>:</td><td><?php echo $info->id_logger ?></td>
	</tr>
	<tr>
		<td>Seri</td><td>:</td><td><?php echo $ser ?></td>
	</tr>
	<tr>
		<td>Sensor</td><td>:</td><td><?php echo str_replace('<br/>',',',$sensor) ?></td>
	</tr>
	<tr>
		<td>Lokasi</td><td>:</td><td><?php echo $nama_lokasi?></td>
	</tr>
	
	
	<tr>
		<td>No. Seluler </td><td>:</td><td><?php echo $nosell ?></td>
	</tr>
	<tr>
		<td>Nama PIC </td><td>:</td><td><?php //echo $info->nama_pic ?></td>
	</tr>
	<tr>
		<td>No. PIC </td><td>:</td><td><?php //echo $info->no_pic ?></td>
	</tr>
	<tr>
		<td>Kontraktor </td><td>:</td><td><?php //echo $info->kontraktor ?></td>
	</tr>
	<tr>
		<td>No. Kontrak </td><td>:</td><td><?php //echo $info->no_kontrak ?></td>
	</tr>
		</tbody>
</table>
<hr/>
						
						
<?php } ?>
						
						
                    </div>
				
                   <div role="tabpanel" class="tab-pane fade" id="section2">
					   <center> <h3>Data Terakhir</h3></center>
                        
<!-- ################################ Data Terakhir ########################################################### -->
	<?php
		$data_sensor=array();
		$q_logger=$this->db->query("select * from t_logger where id_logger='".$idlogger."' limit 1");
		foreach($q_logger->result() as $logger)
		{
			//$iduser=$logger->user_id;
			
			$q_katlogger=$this->db->query("select * from kategori_logger where id_katlogger='".$logger->kategori_log."' ");
			foreach($q_katlogger->result() as $kategori)
			{
				$tabel=$kategori->tabel;
			}
			
			$q_sensor=$this->db->query("select * from parameter_sensor where logger_id='".$idlogger."' ");
			foreach($q_sensor->result() as $sensor)
			{  
				$field_sensor=$sensor->kolom_sensor;
				$q_dtterakhir=$this->db->query("select * from ".$tabel." where code_logger='".$idlogger."' order by waktu desc limit 1");
				foreach($q_dtterakhir->result() as $dt_akhir)
				{
					$dt_sensor=$dt_akhir->$field_sensor;
					$waktu ='<tr><td>Waktu</td><td>:</td><td>'.$dt_akhir->waktu.'</td></tr>';
				}
				
				$data_sensor[]='<tr><td>'.$sensor->nama_parameter.'</td><td>:</td><td>'.$dt_sensor.' '.$sensor->satuan.'</td></tr>';
			}
		}
		
	?>
				<table class="table" style="color:white">
				<tbody>
					<?php 
					
					echo $waktu;
					echo join($data_sensor,' ');
					?>
					
					</tbody>
			</table>
					   <hr/>
<!-- ################################ End Data Terakhir ####################################################### -->					   
                    </div>
					 <div role="tabpanel" class="tab-pane fade" id="section3">
						 <center><h3>User</h3></center>
                        
<!-- ################################ Data User ########################################################### -->
	<?php
		$data_sensor=array();
		$q_user=$this->db->query("select * from t_user where id_user='1' ");
		foreach($q_user->result() as $user)
		{
			$infouser ='<tr><td>Nama</td><td>:</td><td>'.$user->nama.'</td></tr>
			<tr><td>Alamat</td><td>:</td><td>'.$user->alamat.'</td></tr>
			<tr><td>Telp. </td><td>:</td><td>'.$user->telp.'</td></tr>';
		}
		
	?>
				<table class="table" style="color:white">
				<tbody>
					<?php 
					echo $infouser;
					?>
					
					</tbody>
			</table>
						 <hr/>
<!-- ################################ End Informasi User ####################################################### -->	
	
                    </div>
					<!-- ################################ Aplikasi ########################################################### -->
	<div role="tabpanel" class="tab-pane fade" id="section4">
						 <center><h3>Aplikasi</h3></center>
				<table class="table" style="color:white">
				<tbody>
					<tr><td>Website</td><td>:</td><td><a href="<?php echo base_url()?>" class="btn btn-default" target="_blank">Klik untuk menuju ke aplikasi web.  </a></td></tr>
					<tr><td>Android App</td><td>:</td><td><a href="<?php echo base_url()?>/unduh/android"  class="btn btn-default" target="_blank"> Klik untuk download Aplikasi.</a></td></tr>
				
						</tbody>
				</table>
						 <hr/>
	</div>
<!-- ################################ End Informasi Aplikasi ####################################################### -->		
				</div>
                </div>
            </div>
        </div>
    </div>
		

	</body>
</html>
