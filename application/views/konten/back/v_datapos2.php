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
				<div class="card-header pb-2 pt-3 d-flex w-100 justify-content-between align-items-center flex-wrap">
					<h3 class="mb-0" id="head-title">Pilih pos & rentang tanggal, lalu klik Tampil</h3>
					<div class="d-flex align-items-center" id="intervalSwitcher" style="display:none !important">
						<h4 class="mb-0 me-2 fw-normal">Data dalam :</h4>
						<div class="d-flex rounded border" style="width:max-content;overflow:hidden">
							<a href="#" data-interval="jam" class="intervalBtn px-3 py-2 text-white fw-bold" style="background:#303481;text-decoration:none">Jam</a>
							<a href="#" data-interval="hari" class="intervalBtn px-3 py-2 text-dark border-start" style="text-decoration:none">Hari</a>
							<a href="#" data-interval="bulan" class="intervalBtn px-3 py-2 text-dark border-start" style="text-decoration:none">Bulan</a>
						</div>
					</div>
				</div>
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
		var PARAM_URL = '<?= site_url('datapos/get_parameter') ?>';

		var $form    = $('#fetchForm');
		var $status  = $('#statusText');
		var $bar     = $('#progressBar');
		var $modal   = $('#exampleModal');
		var exportBtn = document.getElementById('btn-export');

		var rawRows = [];          // Data mentah per jam (cache satu kali fetch)
		var displayRows = [];      // Hasil aggregasi sesuai interval saat ini
		var parameters = [];
		var currentInterval = 'jam';
		var lastTitle = '';
		var lastNamaPos = '';
		var lastAwal = '';
		var lastAkhir = '';

		function setStatus(msg) { $status.text(msg); }
		function setPct(pct) { $bar.css('width', pct + '%').text(Math.round(pct) + '%'); }

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

		// === Aggregasi client-side dari rawRows (per jam) ke hari/bulan ===
		function aggregate(raw, interval, params) {
			if (interval === 'jam') return raw.slice();

			var keyOf = interval === 'hari'
				? function (w) { return String(w).slice(0, 10); }   // YYYY-MM-DD
				: function (w) { return String(w).slice(0, 7); };   // YYYY-MM

			var groups = {};
			var order = [];
			raw.forEach(function (row) {
				var k = keyOf(row.waktu);
				if (!groups[k]) { groups[k] = []; order.push(k); }
				groups[k].push(row);
			});

			return order.map(function (k) {
				var items = groups[k];
				var out = { waktu: k };
				params.forEach(function (p) {
					var sum = 0, cnt = 0;
					items.forEach(function (it) {
						var v = Number(it[p.nama_parameter]);
						if (isFinite(v)) { sum += v; cnt++; }
					});
					if (cnt === 0) { out[p.nama_parameter] = ''; return; }
					var val = (p.satuan === 'mm') ? sum : (sum / cnt);
					out[p.nama_parameter] = val.toFixed(2);
				});
				return out;
			});
		}

		// === Render tabel dari displayRows ===
		function renderTable() {
			var sel = '#tabel';
			if (!displayRows.length) {
				$(sel + ' thead').html('<tr><th>Tidak ada data</th></tr>');
				$(sel + ' tbody').empty();
				return;
			}
			var esc = function (s) {
				return String(s).replace(/[&<>"']/g, function (m) {
					return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
				});
			};

			var thead = '<tr><th>Waktu</th>';
			parameters.forEach(function (p) {
				thead += '<th>' + esc(String(p.nama_parameter).replace(/_/g, ' ')) + (p.satuan ? ' (' + esc(p.satuan) + ')' : '') + '</th>';
			});
			thead += '</tr>';
			$(sel + ' thead').html(thead);

			var tbody = displayRows.map(function (row) {
				var html = '<tr><td>' + esc(row.waktu) + '</td>';
				parameters.forEach(function (p) {
					var v = row[p.nama_parameter];
					if (v === undefined || v === null || v === '') { html += '<td></td>'; return; }
					var n = Number(v);
					html += '<td>' + (isFinite(n) ? n.toFixed(3) : esc(v)) + (p.satuan ? ' ' + esc(p.satuan) : '') + '</td>';
				});
				html += '</tr>';
				return html;
			}).join('');
			$(sel + ' tbody').html(tbody);
		}

		// === Switch interval (tanpa fetch ulang) ===
		function switchInterval(interval) {
			currentInterval = interval;
			$('.intervalBtn').each(function () {
				var $a = $(this);
				if ($a.data('interval') === interval) {
					$a.removeClass('text-dark').addClass('text-white fw-bold').css('background', '#303481');
				} else {
					$a.removeClass('text-white fw-bold').addClass('text-dark').css('background', '');
				}
			});
			displayRows = aggregate(rawRows, interval, parameters);
			renderTable();
		}

		$(document).on('click', '.intervalBtn', function (e) {
			e.preventDefault();
			switchInterval($(this).data('interval'));
		});

		// === Fetch parameter (header) ===
		function fetchParameters(id_logger) {
			return fetch(PARAM_URL + '?id_logger=' + encodeURIComponent(id_logger), { credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (j) { return j.parameters || []; });
		}

		// === Fetch semua chunks per jam (sesi=hari di backend) ===
		function fetchAllChunks(id_logger, awal, akhir) {
			var chunks = generateChunks(awal, akhir);
			var total = chunks.length;
			var allRows = [];

			function nextChunk(i) {
				if (i >= total) return Promise.resolve(allRows);
				var c = chunks[i];
				var pct = Math.round((i / total) * 100);
				setStatus('Memuat bagian ' + (i + 1) + ' dari ' + total + ' (' + c.start + ' s/d ' + c.end + ')');
				setPct(pct);

				var params = $.param({ id_logger: id_logger, awal: c.start, akhir: c.end, sesi: 'hari' });
				return fetch(WEEK_URL + '?' + params, { credentials: 'same-origin' })
					.then(function (r) { return r.ok ? r.json() : { status: 'error', rows: [] }; })
					.then(function (data) {
						if (data.status === 'ok' && Array.isArray(data.rows)) {
							allRows = allRows.concat(data.rows);
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

			if (!id_logger || !awal || !akhir) {
				alert('Pilih pos dan isi rentang tanggal terlebih dahulu.');
				return;
			}
			if (new Date(akhir + 'T00:00:00') < new Date(awal + 'T00:00:00')) {
				alert('Tanggal Sampai tidak boleh sebelum Dari.');
				return;
			}

			exportBtn.disabled = true;
			$('#intervalSwitcher').hide();
			rawRows = []; displayRows = []; parameters = [];
			showModal();
			setStatus('Memulai...');
			setPct(0);

			fetchParameters(id_logger)
				.then(function (params) {
					parameters = params;
					return fetchAllChunks(id_logger, awal, akhir);
				})
				.then(function (rows) {
					rawRows = rows;
					lastNamaPos = namaPos;
					lastAwal = awal;
					lastAkhir = akhir;
					lastTitle = 'Data ' + namaPos + ' pada ' + awal + ' sampai ' + akhir;
					setPct(100);
					setStatus('Selesai — ' + rows.length + ' baris');
					setTimeout(function () {
						hideModal();
						$('#head-title').text(lastTitle);
						$('#intervalSwitcher').css('display', 'flex');
						exportBtn.disabled = rows.length === 0;
						switchInterval('jam');   // default tampilan per jam
					}, 400);
				})
				.catch(function (err) {
					console.error(err);
					setStatus('Error: ' + err.message);
					setTimeout(hideModal, 1500);
				});
		});

		// === Download Excel (sesuai interval yang sedang ditampilkan) ===
		exportBtn.addEventListener('click', function () {
			if (!displayRows.length) {
				alert('Belum ada data. Klik Tampil dulu.');
				return;
			}
			var $spinner = $('.spinner-border', exportBtn);
			$spinner.show();
			exportBtn.disabled = true;

			var labelInterval = currentInterval === 'jam' ? 'per jam' : (currentInterval === 'hari' ? 'per hari' : 'per bulan');
			var titleForExport = lastTitle + ' (' + labelInterval + ')';

			var fd = new FormData();
			fd.append('title', titleForExport);
			fd.append('data', JSON.stringify(displayRows));
			fd.append('parameter', JSON.stringify(parameters));

			fetch(EXPORT_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
				.then(function (resp) {
					if (!resp.ok) throw new Error('HTTP ' + resp.status);
					return resp.blob();
				})
				.then(function (blob) {
					var filename = titleForExport.replace(/[^\w\- ]/g, '_') + '.xlsx';
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
