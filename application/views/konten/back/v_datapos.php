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
							<form id="formExportExcel" action="<?= base_url() ?>datapos/export_excel" method="post" enctype="multipart/formdata">
								<input type="hidden" name="title" value="<?= $judul?>"/>
								<input type="hidden" name="parameter" id="exportParameter" value="<?= htmlspecialchars(json_encode($parameter->result_array())) ?>"/>
								<input type="hidden" name="data" id="exportData" value=""/>
								<button type="button" id="btnDownloadExcel" class="btn btn-success w-100"
									data-tgl-awal="<?= $this->session->userdata('data_tglawal') ?>"
									data-tgl-akhir="<?= $this->session->userdata('data_tglakhir') ?>"
									data-id-logger="<?= $this->session->userdata('data_idlogger') ?>"
									data-sesi="<?= $this->session->userdata('sesi_data') ?: 'hari' ?>">Download</button>
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

<!-- Modal Progress Download -->
<div class="modal modal-blur fade" id="downloadProgressModal" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Memproses Download Data</h5>
			</div>
			<div class="modal-body">
				<div class="mb-2" id="downloadProgressLabel">Mempersiapkan...</div>
				<div class="progress mb-2" style="height:20px">
					<div class="progress-bar progress-bar-striped progress-bar-animated bg-success" id="downloadProgressBar" role="progressbar" style="width: 0%" aria-valuemin="0" aria-valuemax="100">0%</div>
				</div>
				<div class="small text-muted" id="downloadProgressDetail">0 dari 0 bagian</div>
			</div>
			<div class="modal-footer d-none" id="downloadProgressFooter">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="downloadProgressClose">Tutup</button>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function(){
	var btn = document.getElementById('btnDownloadExcel');
	if (!btn) return;

	var modalEl = document.getElementById('downloadProgressModal');
	var bar = document.getElementById('downloadProgressBar');
	var label = document.getElementById('downloadProgressLabel');
	var detail = document.getElementById('downloadProgressDetail');
	var footer = document.getElementById('downloadProgressFooter');
	var modal = null;
	var backdropEl = null;

	function getModal(){
		if (modal) return modal;
		if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
			modal = new bootstrap.Modal(modalEl);
		}
		return modal;
	}
	function manualShow(){
		modalEl.classList.add('show');
		modalEl.style.display = 'block';
		modalEl.removeAttribute('aria-hidden');
		document.body.classList.add('modal-open');
		if (!backdropEl) {
			backdropEl = document.createElement('div');
			backdropEl.className = 'modal-backdrop fade show';
			document.body.appendChild(backdropEl);
		}
	}
	function manualHide(){
		modalEl.classList.remove('show');
		modalEl.style.display = 'none';
		modalEl.setAttribute('aria-hidden', 'true');
		document.body.classList.remove('modal-open');
		if (backdropEl) { backdropEl.parentNode.removeChild(backdropEl); backdropEl = null; }
	}
	function showModal(){ footer.classList.add('d-none'); var m = getModal(); if (m) m.show(); else manualShow(); }
	function hideModal(){ var m = getModal(); if (m) m.hide(); else manualHide(); }
	function showFooter(){ footer.classList.remove('d-none'); }
	function setProgress(done, total, text){
		var pct = total ? Math.round((done/total)*100) : 0;
		bar.style.width = pct + '%';
		bar.textContent = pct + '%';
		bar.setAttribute('aria-valuenow', pct);
		detail.textContent = done + ' dari ' + total + ' bagian';
		if (text) label.textContent = text;
	}

	function parseDt(s){
		if (!s) return null;
		var parts = s.trim().split(/[\s\-:]/);
		var y = parseInt(parts[0],10), mo = parseInt(parts[1],10)-1, d = parseInt(parts[2],10);
		var h = parts[3] ? parseInt(parts[3],10) : 0;
		var mi = parts[4] ? parseInt(parts[4],10) : 0;
		return new Date(y, mo, d, h, mi, 0);
	}
	function pad(n){ return n<10 ? '0'+n : ''+n; }
	function fmtDt(dt){
		return dt.getFullYear()+'-'+pad(dt.getMonth()+1)+'-'+pad(dt.getDate())+' '+pad(dt.getHours())+':'+pad(dt.getMinutes());
	}
	function diffDays(a,b){
		return Math.ceil((b.getTime()-a.getTime()) / (1000*60*60*24));
	}

	function buildChunks(start, end){
		var totalDays = diffDays(start, end);
		var chunkDays;
		if (totalDays > 30) chunkDays = 7;
		else if (totalDays > 3) chunkDays = 3;
		else return [{ a: start, b: end }];

		var chunks = [];
		var cur = new Date(start.getTime());
		while (cur < end) {
			var lastDay = new Date(cur.getFullYear(), cur.getMonth(), cur.getDate() + chunkDays - 1, 23, 59, 59);
			if (lastDay >= end) lastDay = new Date(end.getTime());
			chunks.push({ a: new Date(cur.getTime()), b: lastDay });
			cur = new Date(lastDay.getFullYear(), lastDay.getMonth(), lastDay.getDate() + 1, 0, 0, 0);
		}
		return chunks;
	}

	function fetchChunk(idLogger, sesi, a, b){
		var fd = new FormData();
		fd.append('id_logger', idLogger);
		fd.append('sesi', sesi);
		fd.append('tgl_awal', fmtDt(a));
		fd.append('tgl_akhir', fmtDt(b));
		return fetch('<?= base_url() ?>datapos/chunk_data', { method:'POST', body: fd, credentials:'same-origin' })
			.then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); });
	}

	btn.addEventListener('click', function(){
		var tglAwal = btn.dataset.tglAwal;
		var tglAkhir = btn.dataset.tglAkhir;
		var idLogger = btn.dataset.idLogger;
		var sesi = btn.dataset.sesi || 'hari';

		var start = parseDt(tglAwal);
		var end = parseDt(tglAkhir);
		if (!start || !end || end <= start) {
			alert('Rentang tanggal tidak valid');
			return;
		}

		var chunks = buildChunks(start, end);
		if (chunks.length <= 1) {
			var existingData = document.getElementById('exportData');
			showModal();
			setProgress(0, 1, 'Mengambil data...');
			fetchChunk(idLogger, sesi, start, end)
				.then(function(resp){
					setProgress(1, 1, 'Menyiapkan file Excel...');
					var data = (resp && resp.data) ? resp.data : [];
					existingData.value = JSON.stringify(data);
					setTimeout(function(){
						document.getElementById('formExportExcel').submit();
						hideModal();
					}, 300);
				})
				.catch(function(err){
					label.textContent = 'Gagal mengambil data: ' + err.message;
					showFooter();
				});
			return;
		}

		showModal();
		setProgress(0, chunks.length, 'Memulai pengambilan data dalam ' + chunks.length + ' bagian...');

		var allData = [];
		var i = 0;
		function next(){
			if (i >= chunks.length) {
				setProgress(chunks.length, chunks.length, 'Menyiapkan file Excel...');
				var dataField = document.getElementById('exportData');
				dataField.value = JSON.stringify(allData);
				setTimeout(function(){
					document.getElementById('formExportExcel').submit();
					setTimeout(hideModal, 800);
				}, 300);
				return;
			}
			var c = chunks[i];
			setProgress(i, chunks.length, 'Mengambil bagian ' + (i+1) + ' dari ' + chunks.length + ' (' + fmtDt(c.a) + ' s/d ' + fmtDt(c.b) + ')');
			fetchChunk(idLogger, sesi, c.a, c.b)
				.then(function(resp){
					if (resp && resp.data && resp.data.length) {
						allData = allData.concat(resp.data);
					}
					i++;
					next();
				})
				.catch(function(err){
					label.textContent = 'Gagal pada bagian ' + (i+1) + ': ' + err.message;
					showFooter();
				});
		}
		next();
	});
});
</script>