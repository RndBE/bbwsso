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

<!-- Modal Progress Download (inline-style, tidak bergantung pada Bootstrap JS) -->
<div id="downloadProgressOverlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:99998;"></div>
<div id="downloadProgressModal" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:8px;box-shadow:0 10px 40px rgba(0,0,0,0.3);z-index:99999;width:90%;max-width:480px;font-family:inherit;">
	<div style="padding:16px 20px;border-bottom:1px solid #e9ecef;">
		<h5 style="margin:0;font-size:1.1rem;font-weight:600;color:#1e293b;">Memproses Download Data</h5>
	</div>
	<div style="padding:20px;">
		<div style="margin-bottom:10px;font-size:0.95rem;color:#1e293b;" id="downloadProgressLabel">Mempersiapkan...</div>
		<div style="background:#e9ecef;border-radius:6px;overflow:hidden;height:22px;margin-bottom:10px;">
			<div id="downloadProgressBar" style="background:#2fb344;color:#fff;height:100%;width:0%;text-align:center;line-height:22px;font-size:0.85rem;font-weight:600;transition:width 0.3s ease;">0%</div>
		</div>
		<div style="font-size:0.85rem;color:#6c757d;" id="downloadProgressDetail">0 dari 0 bagian</div>
	</div>
	<div id="downloadProgressFooter" style="display:none;padding:12px 20px;border-top:1px solid #e9ecef;text-align:right;">
		<button type="button" id="downloadProgressClose" style="padding:6px 14px;background:#6c757d;color:#fff;border:none;border-radius:4px;cursor:pointer;">Tutup</button>
	</div>
</div>

<script>
(function(){
	function start(){
		var btn = document.getElementById('btnDownloadExcel');
		console.log('[DownloadExcel] init, btn=', btn);
		if (!btn) { console.warn('[DownloadExcel] tombol tidak ditemukan'); return; }

		var modalEl = document.getElementById('downloadProgressModal');
		var overlayEl = document.getElementById('downloadProgressOverlay');
		var bar = document.getElementById('downloadProgressBar');
		var label = document.getElementById('downloadProgressLabel');
		var detail = document.getElementById('downloadProgressDetail');
		var footer = document.getElementById('downloadProgressFooter');
		var closeBtn = document.getElementById('downloadProgressClose');

		function showModal(){
			console.log('[DownloadExcel] showModal');
			footer.style.display = 'none';
			overlayEl.style.display = 'block';
			modalEl.style.display = 'block';
		}
		function hideModal(){
			overlayEl.style.display = 'none';
			modalEl.style.display = 'none';
		}
		function showFooter(){ footer.style.display = 'block'; }
		if (closeBtn) closeBtn.addEventListener('click', hideModal);

		function setProgress(done, total, text){
			var pct = total ? Math.round((done/total)*100) : 0;
			bar.style.width = pct + '%';
			bar.textContent = pct + '%';
			detail.textContent = done + ' dari ' + total + ' bagian';
			if (text) label.textContent = text;
		}

		function parseDt(s){
			if (!s) return null;
			var parts = String(s).trim().split(/[\s\-:]/);
			var y = parseInt(parts[0],10), mo = parseInt(parts[1],10)-1, d = parseInt(parts[2],10);
			var h = parts[3] ? parseInt(parts[3],10) : 0;
			var mi = parts[4] ? parseInt(parts[4],10) : 0;
			if (isNaN(y) || isNaN(mo) || isNaN(d)) return null;
			return new Date(y, mo, d, h, mi, 0);
		}
		function pad(n){ return n<10 ? '0'+n : ''+n; }
		function fmtDt(dt){
			return dt.getFullYear()+'-'+pad(dt.getMonth()+1)+'-'+pad(dt.getDate())+' '+pad(dt.getHours())+':'+pad(dt.getMinutes());
		}
		function diffDays(a,b){
			return Math.ceil((b.getTime()-a.getTime()) / (1000*60*60*24));
		}

		function buildChunks(s, e){
			var totalDays = diffDays(s, e);
			var chunkDays;
			if (totalDays > 30) chunkDays = 7;
			else if (totalDays > 3) chunkDays = 3;
			else return [{ a: s, b: e }];

			var chunks = [];
			var cur = new Date(s.getTime());
			while (cur < e) {
				var lastDay = new Date(cur.getFullYear(), cur.getMonth(), cur.getDate() + chunkDays - 1, 23, 59, 59);
				if (lastDay >= e) lastDay = new Date(e.getTime());
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

		btn.addEventListener('click', function(ev){
			ev.preventDefault();
			console.log('[DownloadExcel] click fired');
			var tglAwal = btn.dataset.tglAwal;
			var tglAkhir = btn.dataset.tglAkhir;
			var idLogger = btn.dataset.idLogger;
			var sesi = btn.dataset.sesi || 'hari';
			console.log('[DownloadExcel] params', {tglAwal, tglAkhir, idLogger, sesi});

			var s = parseDt(tglAwal);
			var e = parseDt(tglAkhir);
			if (!s || !e || e <= s) {
				alert('Rentang tanggal tidak valid: ' + tglAwal + ' s/d ' + tglAkhir);
				return;
			}

			var chunks = buildChunks(s, e);
			console.log('[DownloadExcel] chunks=', chunks.length);
			showModal();
			setProgress(0, chunks.length, 'Memulai pengambilan data dalam ' + chunks.length + ' bagian...');

			var allData = [];
			var i = 0;
			function next(){
				if (i >= chunks.length) {
					setProgress(chunks.length, chunks.length, 'Menyiapkan file Excel...');
					document.getElementById('exportData').value = JSON.stringify(allData);
					setTimeout(function(){
						document.getElementById('formExportExcel').submit();
						setTimeout(hideModal, 1500);
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
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
})();
</script>