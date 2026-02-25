<div class="container-xl">
	<!-- Page title -->
	<div class="page-header d-print-none">
		<div class="row g-2 align-items-center">
			<div class="col">
				<h2 class="page-title">
					<?php // echo ucfirst($this->uri->segment(1)) ?>
					Data Pos
				</h2>
			</div>
		</div>
	</div>
</div>
<a id="dlink" style="display:none;"></a>
<div class="page-body">
	<!-- Konten-->
	<div class="container-xl">
		<div class="row row-cards hide-scrollbar px-0 " >
			<div class="card">
				<div class="card-body pt-2 px-3">
					<div class="col-12 col-xl-8 row align-items-end">
						<div class="col-12 col-md-3">
							<div class="form-group">
								<label class="form-label mt-2">Lokasi Pos</label>
								<?php 
								$this->load->helper('logger');
								$cmblogger=loggercombo();
								echo form_open('datapos/set_lokasi');
								?>
								<select type="text" name="id_logger" class="form-select" placeholder="Cari Lokasi Pos" onchange="this.form.submit()" id="select-pos" value="">
									<option value="">Pilih Pos</option>
									<?php foreach ($pilih_pos as $mnpos) : ?>
									<option value="<?= $mnpos['id_logger'] .','.$mnpos['tabel'] ?>" <?= ($mnpos['id_logger'] == $this->session->userdata('data_idlogger')) ? 'selected' : '' ?>><?= str_replace('_', ' ',$mnpos['nama_lokasi']) ?></option>
									<?php endforeach ?>
								</select>
								<?php 
	echo form_close();
								?>
								<?php echo form_open('datapos/set_range'); ?>
							</div>
						</div>
						<div class="col-12 col-md-3">
							<div class="form-group">
								<label class="form-label mt-2">Dari</label>
								<input class="form-control" name="dari" placeholder="Dari" id="dpdari" value="<?= $this->session->userdata('data_tglawal') ?>" autocomplete="off" required/>
							</div>
						</div>
						<div class="col-12 col-md-3">
							<div class="form-group">
								<label class="form-label mt-2">Sampai</label>
								<input class="form-control" name="sampai" placeholder="Sampai" id="dpsampai" value="<?= $this->session->userdata('data_tglakhir') ?>" autocomplete="off" required/>
							</div>
						</div>
						<div class="col-6 col-md-auto d-flex align-items-end mt-3 mt-md-0">
							<input type="submit" class="btn btn-info w-100" value="Tampil"/>
						</div>
						<?php echo form_close() ?>
						<div class="col-6 col-md-auto d-flex align-items-end mt-3 mt-md-0">
							<?php
	if($datapos != "kosong"){ ?>
							<?php $judul = "Data ".$nama_lokasi. " pada ".  $this->session->userdata('data_tglawal') . " sampai ". $this->session->userdata('data_tglakhir') ?>
							
							<!--<input type="button" class="btn btn-success w-100"  onclick="tableToExcel('tabel', 'name', '<?= $judul ?>.xls')" value="Download" /> -->
							<form action="<?= base_url() ?>datapos/export_excel" method="post" enctype="multipart/formdata"> 
								<input type="text" name="title" value="<?= $judul?>"  class="d-none"/>
								<input type="text" name="parameter" value="<?= htmlspecialchars(json_encode($parameter->result_array())) ?>"  class="d-none"/>
								<input type="text" value="<?= htmlspecialchars(json_encode($datapos->result_array())) ?>" name="data" class="d-none"/>
								<input type="submit" class="btn btn-success w-100" value="Download">
							</form>
							<?php } ?>
						</div>
						
					</div>
				</div>
			</div>
			<?php	echo form_close();?>
			<div class="card <?= ($datapos == "kosong") ? '' : 'd-none' ?>">
				<div class="card-body">
					<h3>Data Tidak Ditemukan</h3>
				</div>
			</div>
			<div class="card <?= ($datapos != "kosong") ? '' : 'd-none' ?>">
				<div class="card-header pb-2 pt-3"><h3 class="mb-0">Data <?= $nama_lokasi ?> pada <?= $this->session->userdata('data_tglawal') ?> sampai <?= $this->session->userdata('data_tglakhir') ?></h3></div>
				<div class="card-body px-3">
					<div class="table-responsive">
						<table class="table table-bordered" id="tabel">
							<thead>
								<tr>
									<?php if($parameter != "kosong"){
									?>
									<td>Waktu</td>
									<?php
	foreach($parameter->result() as $kolom){
									?>
									<td><?php echo str_replace('_',' ',$kolom->nama_parameter) ?></td>

									<?php 
		} 
} ?>
								</tr>
							</thead>
							<?php
							if($datapos != "kosong"){
								foreach( $datapos->result() as $data )
								{ ?>
							<tr>
								<td><?php echo $data->waktu ?></td>

								<?php
									foreach($parameter->result() as $kolom){
										$sensor =$kolom->nama_parameter;
								?>

								<td><?php echo number_format($data->$sensor,3) ?>  </td>

								<?php } ?>

							</tr>
							<?php } }?>
						</table>
					</div>
				</div>
			</div>
		</div>

	</div>
	<!-- end Konten-->
</div>
<script>
	// @formatter:off
	document.addEventListener("DOMContentLoaded", function () {
		var el;
		window.TomSelect && (new TomSelect(el = document.getElementById('select-pos'), {
			copyClassesToDropdown: false,
			dropdownClass: 'dropdown-menu ts-dropdown',
			optionClass:'dropdown-item',
			controlInput: '<input>',
			render:{
				item: function(data,escape) {
					if( data.customProperties ){
						return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
					}
					return '<div>' + escape(data.text) + '</div>';
				},
				option: function(data,escape){
					if( data.customProperties ){
						return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
					}
					return '<div>' + escape(data.text) + '</div>';
				},
			},
		}));
	});
	// @formatter:on
</script>