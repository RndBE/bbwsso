<html>

<head>
   <title>Data Masuk</title>
   <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
   	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-aFq/bzH65dt+w6FI2ooMVUpc+21e0SRygnTpmBvdBgSdnuTN7QbdgL+OapgHtvPp" crossorigin="anonymous">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

   <style>

   </style>
</head>

<body>
	<?php $info=$this->db->where('logger_id',$this->session->userdata('log_id'))->get('t_informasi')->row(); ?>
	<main>
		<h3 class="fw-bold mb-3 mt-3 text-center">Data Masuk Serayu Opak</h3> 
				<hr/>
		<div class="container-fluid px-3">
			<section class="mb-3"> 
				
				<div class="row gx-md-3 justify-content-center "> 
					<div class="col-lg-3"> 
						<ul class="list-group list-group-item-primary p-0">
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<span class="fw-bold">ID Logger | Tanggal</span>
								<span><?= $this->session->userdata('log_id') .' | ' .$this->session->userdata('tgl_search')?></span>
							</li>
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<span class="fw-bold">GPS</span>
								<span><?php if($info){ echo $info->gps1 . ', '. $info->gps2; } ?></span>
							</li>
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<span class="fw-bold">Serial Number</span>
								<span><?= ($info) ? $info->serial_number : ''?></span>
							</li>
							<li class="list-group-item d-flex align-items-center justify-content-between">
								<span class="fw-bold">Kelengkapan Data</span>
								<div class="d-flex align-items-center ">
								<span class="badge bg-primary me-2 pb-1"><?= ($data_count == 0) ? '0 %' :  number_format($data_count/$total_minutes*100,2,'.','') . ' %'   ?></span><span><?=  $data_count .' / ' . $total_minutes ?></span>
								</div>
							</li>
						</ul>
					</div> 
					<div class="col-lg-5 mt-2 mt-md-0"> 
						<div class="card mb-2" >
							<div class="card-body pb-0">
								<!--<form class="row g-3">-->
								<?php echo form_open('datamasuk/sesi_logger','class="row gx-3 align-items-center "');?>
								<div class="col-3 ">
									<label class="text-md-start">ID Logger </label>
								</div>
								<div class="col-6">
										<select class="form-select" name="logger_id" id="">
											<option value="" selected disabled>Pilih ID Logger</option>
											<?php foreach ($list_logger as $ls) { ?>

											<option value="<?= $ls['id_logger'] ?>" <?= ($this->session->userdata('log_id') == $ls['id_logger']) ? 'selected' : '' ?>><?= $ls['nama_logger'] . ' - ' . $ls['id_logger']  ?></option>
											<?php } ?>
										</select>
								</div>
								<div class="col-3">
									
										<input class="btn btn-primary w-100" name="btnlog" value="Cari"  type="submit"/>
									
								</div>
								<!--</form>-->
								<?php echo form_close();?>
							</div>
						</div>	
						<div class="card mb-2">
							<div class="card-body pb-0">
								<!--<form class="row g-3">-->
								<?php echo form_open('datamasuk/tgl_search','class="row g-3 align-items-center "');?>
								<div class="col-3">
									<label class="text-md-start">Tanggal</label>
								</div>
									<div class="col-6">
										<input type="date" id="tgl" name="tgl" class="form-control" value="<?= $this->session->userdata('tgl_search') ?>">
									</div>
									<div class="col-3">
										
										<input class="btn btn-primary w-100" name="btntgl" value="Cari" type="submit"/>
										
									</div>
								<!--</form>-->
								<?php echo form_close();?>
							</div>
						</div>
					
					</div> 
					
					<div class="col-lg-3 mt-2 mt-md-0"> 
					
						<ul class="list-group list-group-item-primary p-0">
							
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<span class="fw-bold">Ambil Data</span>
								<span><?= ($info) ? $info->ad : '' ?></span>
							</li>
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<span class="fw-bold">Kirim Data</span>
								<span><?= ($info) ? $info->kd  : '' ?></span>
							</li>
							<li class="list-group-item d-flex align-items-center justify-content-between">
								<span class="fw-bold">Max Reset</span>
								<span><?= ($info) ? $info->mr : '' ?></span>
							</li>
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<span class="fw-bold">WDT</span>
								<span><?= ($info) ? $info->wdt : ''?></span>
							</li>
						</ul>
						<!--
						<?php echo form_open('datamasuk');?>
						
							<button class="btn btn-primary" name="btnrefresh" type="submit"><div class="d-flex justify-content-center"><span class="material-symbols-outlined mx-2" >refresh</span> Refresh</div></button>

						
						<?php echo form_close();?>-->
					</div> 
			
				</div> 
			</section>
	
		</div>
		
	</main>
      <hr />
      <?php if ($data) { ?>
         <div class="table-responsive px-3">
            <table border="1" cellspacing="1" cellpadding="1" class="table table-bordered table-sm">
				 <tr class="fw-bold">
				   <?php
               foreach ($key as $r) {
               ?>
				   <td style="font-size:11px" class=" text-center bg-light">&nbsp;<?= str_replace('_',' ',$r['nama']) ?>&nbsp;</td>
				   <?php
               }
               ?>
               </tr>
               <tr style="font-size:13px" class="fw-bold text-center">
				   <?php
               foreach ($key as $r) {
               ?>
				   <td class=" bg-light">&nbsp;<?= $r['key'] ?>&nbsp;</td>
				   <?php
               }
               ?>
               </tr><?php
					foreach ($data as $row) {
					?>
						<tr>
							<?php foreach ($row as $ky => $val) { ?>
								<td  style="font-size:14px;text-align:center" class="text-nowrap"><?= $val ?></td>
							<?php } ?>
						</tr>

					<?php
					}
					?>
            </table>
         </div>
      <?php } else { ?>
        <center> Data Tidak Ditemukan</center>
      <?php } ?>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha2/dist/js/bootstrap.bundle.min.js" integrity="sha384-qKXV1j0HvMUeCBQ+QVp7JcfGl760yU08IQ+GpUo5hlbpg51QRiuqHAJz8+BrxE/N" crossorigin="anonymous"></script>
 
</html>