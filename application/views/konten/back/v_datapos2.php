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
							
							<input type="submit" class="btn btn-primary" value="Tampil">
								
						</div>
						<?php echo form_close() ?>
						<div class="col-6 col-md-auto d-flex align-items-end mt-3 mt-md-0">
							<?php
	if($datapos != "kosong"){ ?>
							<?php $judul = "Data ".$nama_lokasi. " pada ".  $this->session->userdata('data_tglawal') . " sampai ". $this->session->userdata('data_tglakhir') ?>

							<form action="<?= base_url() ?>datapos/export_excel" method="post" enctype="multipart/formdata"> 
								<input type="text" name="title" value="<?= $judul?>"  class="d-none"/>
								<input type="text" name="parameter" value="<?= htmlspecialchars(json_encode($parameter->result_array())) ?>"  class="d-none"/>
								<input type="text" value="<?= htmlspecialchars(json_encode($datapos)) ?>" name="data" class="d-none"/>
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
			<div class="card <?= ($datapos != "kosong") ? '' : 'd-none' ?>" id="data_fetch">
				<div class="card-header pb-2 pt-3 d-flex w-100 justify-content-between"><h3 class="mb-0">Data <?= $nama_lokasi ?> pada <?= $this->session->userdata('data_tglawal') ?> sampai <?= $this->session->userdata('data_tglakhir') ?></h3>
					<div class="d-flex align-items-center">
						<h4 class="mb-0 me-2 fw-normal">Data dalam :</h4>
						<div class="d-flex rounded border" style="width:max-content;overflow:hidden">

							<a href="<?= base_url() ?>datapos/ubah_session?sesi=hari">
								<div class="border-end px-3 py-2 <?= ($this->session->userdata('sesi_data') == 'hari') ? 'text-white fw-bold': 'text-dark'?> " style="background:<?= ($this->session->userdata('sesi_data') == 'hari') ? '#303481': ''?>">
									Hari
								</div>
							</a>
							<a href="<?= base_url() ?>datapos/ubah_session?sesi=bulan">
								<div class="border-end px-3 py-2 <?= ($this->session->userdata('sesi_data') == 'bulan') ? 'text-white fw-bold': 'text-dark'?> " style="background:<?= ($this->session->userdata('sesi_data') == 'bulan') ? '#303481': ''?>">
									Bulan
								</div>
							</a>
							<a href="<?= base_url() ?>datapos/ubah_session?sesi=tahun">
								<div class="px-3 py-2 <?= ($this->session->userdata('sesi_data') == 'tahun') ? 'text-white fw-bold': 'text-dark'?> " style="background:<?= ($this->session->userdata('sesi_data') == 'tahun') ? '#303481': ''?>">
									Tahun
								</div>
							</a>
						</div>
					</div>
				</div>
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
								foreach( $datapos as $data )
								{ ?>
							<tr>
								<td><?php echo $data['waktu'] ?></td>

								<?php
									foreach($parameter->result() as $kolom){
										$sensor =$kolom->nama_parameter;
								?>

								<td><?php echo number_format($data[$sensor],3) ?> <?= $kolom->satuan?>  </td>

								<?php } ?>

							</tr>
							<?php } }?>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	var tmp;
	function strip(html) {
		tmp = document.createElement("DIV");
		tmp.innerHTML = html;
		console.log(tmp.innerText);
		console.log(tmp.textContent);

		return tmp.textContent || tmp.innerText || "";
	}
	var tableToExcel = (function() {
		var uri = 'data:application/vnd.ms-excel;base64,',
			template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>',
			base64 = function(s) {
				return window.btoa(unescape(encodeURIComponent(s)))
			},
			format = function(s, c) {
				return s.replace(/{(\w+)}/g, function(m, p) {
					return c[p];
				})
			}
		return function(table, name, filename) {
			if (!table.nodeType) 
				table = $('#'+table).clone();

			var hyperLinks = table.find('a');
			for (i = 0; i < hyperLinks.length; i++) {

				var sp1 = document.createElement("span");
				var sp1_content = document.createTextNode($(hyperLinks[i]).text());
				sp1.appendChild(sp1_content);
				var sp2 = hyperLinks[i];
				var parentDiv = sp2.parentNode;
				parentDiv.replaceChild(sp1, sp2);
			}

			var ctx = {
				worksheet: name || 'Worksheet',
				table: table[0].innerHTML
			}


			document.getElementById("dlink").href = uri + base64(format(template, ctx));
			document.getElementById("dlink").download = filename;
			document.getElementById("dlink").click();

		}
	})()
</script>
<script>
	// @formatter:off
	document.addEventListener("DOMContentLoaded", function () {
		var el;
		window.TomSelect && (new TomSelect(el = document.getElementById('select-pos'), {
		}));
	});
	// @formatter:on
</script>