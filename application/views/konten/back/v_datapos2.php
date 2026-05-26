<div class="container-xl">
	<!-- Page title -->
	<div class="page-header d-print-none">
		<div class="row g-2 align-items-center">
			<div class="col">
				<h2 class="page-title">Data Pos</h2>
			</div>
		</div>
	</div>
</div>
<a id="dlink" style="display:none;"></a>
<div class="page-body">
	<div class="container-xl">
		<div class="row row-cards hide-scrollbar px-0">
			<div class="card">
				<div class="card-body pt-2 px-3">
					<form id="fetchForm">
						<div class="col-12 col-xl-10 row align-items-end">
							<div class="col-12 col-md-3">
								<div class="form-group">
									<label class="form-label mt-2">Lokasi Pos</label>
									<select name="id_logger" class="form-select" id="select-pos">
										<option value="">Pilih Pos</option>
										<?php foreach ($pilih_pos as $mnpos) : ?>
											<option value="<?= $mnpos['id_logger'] ?>|<?= htmlspecialchars($mnpos['nama_lokasi'], ENT_QUOTES) ?>"
												<?= ($mnpos['id_logger'] == $this->session->userdata('data_idlogger')) ? 'selected' : '' ?>>
												<?= str_replace('_', ' ', $mnpos['nama_lokasi']) ?>
											</option>
										<?php endforeach ?>
									</select>
								</div>
							</div>
							<div class="col-12 col-md-3">
								<div class="form-group">
									<label class="form-label mt-2">Dari</label>
									<input class="form-control" name="awal" id="awal_new" placeholder="Dari" autocomplete="off" required
										value="<?= $this->session->userdata('data_tglawal') ? substr($this->session->userdata('data_tglawal'), 0, 10) : '' ?>"/>
								</div>
							</div>
							<div class="col-12 col-md-3">
								<div class="form-group">
									<label class="form-label mt-2">Sampai</label>
									<input class="form-control" name="akhir" id="akhir_new" placeholder="Sampai" autocomplete="off" required
										value="<?= $this->session->userdata('data_tglakhir') ? substr($this->session->userdata('data_tglakhir'), 0, 10) : '' ?>"/>
								</div>
							</div>
							<div class="col-12 col-md-2">
								<div class="form-group">
									<label class="form-label mt-2">Interval</label>
									<select name="sesi" class="form-select" id="select-sesi">
										<option value="hari" <?= ($this->session->userdata('sesi_data') == 'hari' || !$this->session->userdata('sesi_data')) ? 'selected' : '' ?>>Hari (per jam)</option>
										<option value="bulan" <?= ($this->session->userdata('sesi_data') == 'bulan') ? 'selected' : '' ?>>Bulan (per hari)</option>
										<option value="tahun" <?= ($this->session->userdata('sesi_data') == 'tahun') ? 'selected' : '' ?>>Tahun (per bulan)</option>
									</select>
								</div>
							</div>
							<div class="col-6 col-md-auto d-flex align-items-end mt-3 mt-md-0">
								<button type="submit" class="btn btn-primary" id="btnTampil">Tampil</button>
							</div>
							<div class="col-6 col-md-auto d-flex align-items-end mt-3 mt-md-0">
								<button type="button" id="btn-export" class="btn btn-success w-100" disabled>
									Download
									<span class="spinner-border spinner-border-sm ms-2" style="display:none" role="status"></span>
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>

			<div class="card">
				<div class="card-header pb-2 pt-3"><h3 class="mb-0" id="head-title">Pilih pos & rentang tanggal, lalu klik Tampil</h3></div>
				<div class="card-body px-3">
					<div class="table-responsive">
						<table class="table table-bordered" id="tabel">
							<thead></thead>
							<tbody></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal Progress -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-sm modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-body px-3 py-3">
				<div class="mb-2 text-center">
					<span class="badge bg-blue-lt me-2">Status: <span id="statusText">idle</span></span>
				</div>
				<div class="progress mt-3" role="progressbar" aria-label="Progress" style="height:30px">
					<div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" style="width:0%">0%</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	$(function () {
		$('#awal_new').datetimepicker({ timepicker: false, format: 'Y-m-d' });
		$('#akhir_new').datetimepicker({ timepicker: false, format: 'Y-m-d' });
	});
</script>

<script>
(function () {
	function start() {
		var WEEK_URL = '<?= site_url('datapos/fetch_week') ?>';
		var EXPORT_URL = '<?= site_url('datapos/excel_export') ?>';

		var $form    = $('#fetchForm');
		var $status  = $('#statusText');
		var $bar     = $('#progressBar');
		var $modal   = $('#exampleModal');
		var exportBtn = document.getElementById('btn-export');

		var lastRowsForExport = [];
		var lastTitle = '';

		function setStatus(msg) { $status.text(msg); }
		function setPct(pct) { $bar.css('width', pct + '%').text(Math.round(pct) + '%'); }

		// === Fallback show/hide modal kalau bootstrap.Modal tidak tersedia ===
		function showModal() {
			try {
				if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
					bootstrap.Modal.getOrCreateInstance($modal[0]).show();
					return;
				}
			} catch (e) {}
			$modal[0].classList.add('show');
			$modal[0].style.display = 'block';
			$modal[0].removeAttribute('aria-hidden');
			document.body.classList.add('modal-open');
			if (!document.getElementById('__bbwsso_backdrop')) {
				var bd = document.createElement('div');
				bd.id = '__bbwsso_backdrop';
				bd.className = 'modal-backdrop fade show';
				document.body.appendChild(bd);
			}
		}
		function hideModal() {
			try {
				if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
					bootstrap.Modal.getOrCreateInstance($modal[0]).hide();
					return;
				}
			} catch (e) {}
			$modal[0].classList.remove('show');
			$modal[0].style.display = 'none';
			$modal[0].setAttribute('aria-hidden', 'true');
			document.body.classList.remove('modal-open');
			var bd = document.getElementById('__bbwsso_backdrop');
			if (bd) bd.parentNode.removeChild(bd);
		}

		// === Generate chunks (per minggu kalau >30hr, per 3hr kalau >3hr, 1 chunk kalau ≤3hr) ===
		function generateChunks(awal, akhir) {
			var endDt = new Date(akhir + 'T00:00:00');
			var startDt = new Date(awal + 'T00:00:00');
			var totalDays = Math.floor((endDt - startDt) / (1000 * 60 * 60 * 24)) + 1;
			var chunkSize;
			if (totalDays > 30) chunkSize = 7;
			else if (totalDays > 3) chunkSize = 3;
			else chunkSize = totalDays;

			var chunks = [];
			var cursor = new Date(startDt);
			function fmt(d) {
				var y = d.getFullYear(), m = d.getMonth() + 1, dd = d.getDate();
				return y + '-' + (m < 10 ? '0' + m : m) + '-' + (dd < 10 ? '0' + dd : dd);
			}
			while (cursor <= endDt) {
				var wStart = new Date(cursor);
				var wEnd = new Date(cursor);
				wEnd.setDate(wEnd.getDate() + (chunkSize - 1));
				if (wEnd > endDt) wEnd.setTime(endDt.getTime());
				chunks.push({ start: fmt(wStart), end: fmt(wEnd) });
				cursor.setDate(cursor.getDate() + chunkSize);
			}
			return chunks;
		}

		// === Render tabel ===
		function renderTable(rows, parameters) {
			var sel = '#tabel';
			if (!rows || rows.length === 0) {
				$(sel + ' thead').html('<tr><th>Tidak ada data</th></tr>');
				$(sel + ' tbody').empty();
				return;
			}
			var esc = function (s) {
				return String(s).replace(/[&<>"']/g, function (m) {
					return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
				});
			};
			var keys = ['waktu'];
			parameters.forEach(function (p) { keys.push(p.nama_parameter); });

			var thead = '<tr>' + keys.map(function (k) {
				if (k === 'waktu') return '<th>Waktu</th>';
				var unit = '';
				for (var i = 0; i < parameters.length; i++) {
					if (parameters[i].nama_parameter === k) { unit = parameters[i].satuan; break; }
				}
				return '<th>' + esc(String(k).replace(/_/g, ' ')) + (unit ? ' (' + esc(unit) + ')' : '') + '</th>';
			}).join('') + '</tr>';
			$(sel + ' thead').html(thead);

			var tbody = rows.map(function (row) {
				return '<tr>' + keys.map(function (k) {
					var v = row[k];
					if (v === undefined || v === null || v === '') return '<td></td>';
					if (k === 'waktu') return '<td>' + esc(v) + '</td>';
					var n = Number(v);
					return '<td>' + (isFinite(n) ? n.toFixed(3) : esc(v)) + '</td>';
				}).join('') + '</tr>';
			}).join('');
			$(sel + ' tbody').html(tbody);
		}

		// === Fetch parameter sekali untuk header tabel ===
		function fetchParameters(id_logger) {
			return $.getJSON('<?= site_url('datapos/get_parameter') ?>', { id_logger: id_logger })
				.then(function (resp) { return resp.parameters || []; });
		}

		// === Fetch semua chunks berurutan ===
		function fetchAllChunks(id_logger, awal, akhir, sesi) {
			var chunks = generateChunks(awal, akhir);
			var total = chunks.length;
			var allRows = [];

			console.log('[fetch] ' + total + ' chunks, id_logger=' + id_logger + ', sesi=' + sesi);

			function nextChunk(i) {
				if (i >= total) return Promise.resolve(allRows);
				var c = chunks[i];
				var pct = Math.round((i / total) * 100);
				setStatus('Memuat bagian ' + (i + 1) + ' dari ' + total + ' (' + c.start + ' s/d ' + c.end + ')');
				setPct(pct);

				var params = $.param({ id_logger: id_logger, awal: c.start, akhir: c.end, sesi: sesi });
				return fetch(WEEK_URL + '?' + params, { credentials: 'same-origin' })
					.then(function (r) { return r.ok ? r.json() : { status: 'error', rows: [] }; })
					.then(function (data) {
						if (data.status === 'ok' && Array.isArray(data.rows)) {
							allRows = allRows.concat(data.rows);
							console.log('[chunk ' + (i + 1) + '/' + total + '] +' + data.rows.length + ' rows (total: ' + allRows.length + ')');
						} else if (data.status === 'error') {
							console.warn('[chunk ' + (i + 1) + '] ' + data.message);
						}
						return nextChunk(i + 1);
					})
					.catch(function (err) {
						console.warn('[chunk ' + (i + 1) + '] fetch error: ' + err.message);
						return nextChunk(i + 1);
					});
			}
			return nextChunk(0);
		}

		// === Form submit (Tampil) ===
		$form.on('submit', function (e) {
			e.preventDefault();
			var raw       = $('#select-pos').val() || '';
			var id_logger = raw.split('|')[0].trim();
			var namaPos   = (raw.split('|')[1] || '').trim();
			var awal      = $('#awal_new').val();
			var akhir     = $('#akhir_new').val();
			var sesi      = $('#select-sesi').val() || 'hari';

			if (!id_logger || !awal || !akhir) {
				alert('Pilih pos dan isi rentang tanggal terlebih dahulu.');
				return;
			}
			if (new Date(akhir + 'T00:00:00') < new Date(awal + 'T00:00:00')) {
				alert('Tanggal Sampai tidak boleh sebelum Dari.');
				return;
			}

			exportBtn.disabled = true;
			lastRowsForExport = [];
			showModal();
			setStatus('Memulai...');
			setPct(0);

			// Ambil parameter (header) sekali, lalu fetch data per chunk
			fetchParameters(id_logger)
				.then(function (parameters) {
					return fetchAllChunks(id_logger, awal, akhir, sesi)
						.then(function (rows) {
							setPct(100);
							setStatus('Selesai — ' + rows.length + ' baris');
							lastRowsForExport = rows;
							lastTitle = 'Data ' + namaPos + ' pada ' + awal + ' sampai ' + akhir;
							setTimeout(function () {
								hideModal();
								renderTable(rows, parameters);
								$('#head-title').text(lastTitle);
								exportBtn.disabled = rows.length === 0;
								// Simpan parameter di tombol download
								exportBtn.dataset.parameter = JSON.stringify(parameters);
							}, 400);
						});
				})
				.catch(function (err) {
					console.error(err);
					setStatus('Error: ' + err.message);
					setTimeout(hideModal, 1500);
				});
		});

		// === Download Excel ===
		exportBtn.addEventListener('click', function () {
			if (!lastRowsForExport.length) {
				alert('Belum ada data. Klik Tampil dulu.');
				return;
			}
			var $spinner = $('.spinner-border', exportBtn);
			$spinner.show();
			exportBtn.disabled = true;

			var fd = new FormData();
			fd.append('title', lastTitle);
			fd.append('data', JSON.stringify(lastRowsForExport));
			fd.append('parameter', exportBtn.dataset.parameter || '[]');

			fetch(EXPORT_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
				.then(function (resp) {
					if (!resp.ok) throw new Error('HTTP ' + resp.status);
					return resp.blob();
				})
				.then(function (blob) {
					var filename = lastTitle.replace(/[^\w\- ]/g, '_') + '.xlsx';
					var href = URL.createObjectURL(blob);
					var a = Object.assign(document.createElement('a'), { href: href, download: filename });
					document.body.appendChild(a); a.click(); a.remove();
					setTimeout(function () { URL.revokeObjectURL(href); }, 2000);
				})
				.catch(function (err) {
					console.error('Export error:', err);
					alert('Gagal export Excel: ' + err.message);
				})
				.finally(function () {
					$spinner.hide();
					exportBtn.disabled = false;
				});
		});

	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
})();
</script>
