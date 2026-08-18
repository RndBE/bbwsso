<?php
// Rekonsiliasi data logger — peta kelengkapan per menit dalam satu hari.
// Menit kosong diambil dari database, lalu diminta ulang ke logger
// dengan REQ_DATA (lihat dokumen komunikasi MQTT JSON BL1100/BL110).
// Command lain (REQ_PENDING_CLEAR, REQ_RANGE, RTC, FTP) belum dipakai.
$idlogger    = $this->session->userdata('idlogger');
$namalokasi  = $this->session->userdata('namalokasi');
$mqtt_broker = 'mqtt.beacontelemetry.com';
$topik_cmd   = 'sub_' . $idlogger;
$topik_resp  = 'pub_' . $idlogger;
$hari_ini    = date('Y-m-d');
?>

<style>
	.rekon-grid {
		display: grid;
		grid-template-columns: repeat(60, 1fr);
		gap: 2px;
	}

	.rekon-sel {
		aspect-ratio: 1 / 1;
		border-radius: 2px;
		background: #e9ecef;
		cursor: default;
	}

	.rekon-sel.hilang {
		background: #d63939;
		cursor: pointer;
	}

	.rekon-sel.bisa {
		background: #4299e1;
		cursor: pointer;
	}

	.rekon-sel.antre {
		background: #f59f00;
		cursor: pointer;
	}

	.rekon-sel.menunggu {
		background: #ae3ec9;
		animation: rekon-kedip 1s ease-in-out infinite;
	}

	.rekon-sel.terkirim {
		background: #2fb344;
	}

	.rekon-sel.gagal {
		background: #7f1d1d;
		cursor: pointer;
	}

	.rekon-sel.luar {
		background: #f8f9fa;
	}

	@keyframes rekon-kedip {

		0%,
		100% {
			opacity: 1;
		}

		50% {
			opacity: .35;
		}
	}

	.rekon-baris-jam {
		display: grid;
		grid-template-columns: 34px 1fr;
		gap: 8px;
		align-items: center;
		margin-bottom: 2px;
	}

	.rekon-label-jam {
		font-size: 11px;
		color: #667382;
		text-align: right;
		font-variant-numeric: tabular-nums;
	}

	/* btn-sm bawaan terlalu rapat untuk tombol yang berisi ikon + label +
	   badge, jadi paddingnya dilebarkan sendiri. */
	.rekon-aksi .btn {
		padding: .5rem 1rem;
		font-size: .8125rem;
		line-height: 1.45;
	}

	.rekon-aksi .btn .icon {
		margin-right: .5rem;
	}

	.rekon-aksi .btn .badge {
		padding: .2rem .45rem;
		font-size: .75rem;
	}

	/* Warna teks disetel eksplisit — bawaan pre di Tabler terlalu terang
	   di atas latar terang sehingga isinya nyaris tidak terbaca. */
	#rekon-log {
		max-height: 220px;
		overflow: auto;
		margin: 0;
		padding: 12px;
		border-radius: 6px;
		border: 1px solid #e6e8eb;
		background: #f8f9fa;
		color: #354052;
		font-size: 12px;
		line-height: 1.7;
	}

	.rekon-legenda span {
		display: inline-block;
		width: 10px;
		height: 10px;
		border-radius: 2px;
		margin-right: 4px;
		vertical-align: -1px;
	}
</style>

<div class="page-header d-print-none">
	<div class="container-xl">
		<div class="row g-3 align-items-center">
			<div class="col-auto">
				<?= anchor('awlr/analisa', '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-big-left-lines" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 15v3.586a1 1 0 0 1 -1.707 .707l-6.586 -6.586a1 1 0 0 1 0 -1.414l6.586 -6.586a1 1 0 0 1 1.707 .707v3.586h3v6h-3z"></path><path d="M21 15v-6"></path><path d="M18 15v-6"></path></svg>') ?>
			</div>
			<div class="col-auto">
				<span class="status-indicator status-secondary status-indicator-animated" id="rekon-indicator">
					<span class="status-indicator-circle"></span>
					<span class="status-indicator-circle"></span>
					<span class="status-indicator-circle"></span>
				</span>
			</div>
			<div class="col">
				<div class="page-pretitle">Rekonsiliasi Data</div>
				<h2 class="page-title"><?= $namalokasi ?></h2>
				<div class="text-muted">
					<ul class="list-inline list-inline-dots mb-0">
						<li class="list-inline-item"><?= $idlogger ?></li>
						<li class="list-inline-item"><span id="rekon-status-broker">Menghubungkan ke logger…</span></li>
					</ul>
				</div>
			</div>
			<div class="col-auto">
				<div class="btn-list">
					<button class="btn btn-icon" id="btn-hari-mundur" title="Hari sebelumnya">&lsaquo;</button>
					<input type="date" class="form-control" id="rekon-tanggal" style="width:170px"
						value="<?= $hari_ini ?>" max="<?= $hari_ini ?>">
					<button class="btn btn-icon" id="btn-hari-maju" title="Hari berikutnya">&rsaquo;</button>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="page-body">
	<div class="container-xl">
		<div class="row row-cards">

			<div class="col-12">
				<div class="card">
					<div class="card-body">
						<div class="row align-items-end">
							<div class="col-auto">
								<div class="text-muted text-uppercase" style="font-size:11px;letter-spacing:.04em">
									Kelengkapan</div>
								<div class="h1 m-0 text-green" id="rekon-persen">—</div>
							</div>
							<div class="col">
								<div><strong id="rekon-ada">0</strong> <span class="text-muted">/ <span
											id="rekon-harapan">0</span> menit</span></div>
								<div class="text-muted"><span id="rekon-hilang">0</span> hilang</div>
							</div>
						</div>
						<div class="progress mt-3" style="height:8px">
							<div class="progress-bar bg-green" id="rekon-bar" style="width:0%"></div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<div>
							<h3 class="card-title">Peta Menit</h3>
							<div class="text-muted" style="font-size:12px">1.440 sel — satu per menit. Klik sel merah
								untuk minta ulang data menit itu.</div>
						</div>
						<div class="card-actions btn-list flex-wrap rekon-aksi">
							<button class="btn btn-sm text-nowrap" id="btn-req-pending" disabled>
								<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="16" height="16"
									viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
									stroke-linecap="round" stroke-linejoin="round">
									<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
									<path d="M12 6m-8 0a8 3 0 1 0 16 0a8 3 0 1 0 -16 0"></path>
									<path d="M4 6v6a8 3 0 0 0 16 0v-6"></path>
									<path d="M4 12v6a8 3 0 0 0 16 0v-6"></path>
								</svg>
								Cek Data di Logger
							</button>
							<button class="btn btn-sm btn-azure text-nowrap d-none" id="btn-minta-bisa">
								<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="16" height="16"
									viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
									stroke-linecap="round" stroke-linejoin="round">
									<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
									<path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2"></path>
									<path d="M7 11l5 5l5 -5"></path>
									<path d="M12 4l0 12"></path>
								</svg>
								Minta yang Ada di Logger
								<span class="badge bg-white text-azure ms-2" id="rekon-jml-bisa">0</span>
							</button>
							<button class="btn btn-sm btn-primary text-nowrap" id="btn-minta-hilang" disabled>
								<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="16" height="16"
									viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
									stroke-linecap="round" stroke-linejoin="round">
									<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
									<path d="M19 18a3.5 3.5 0 0 0 0 -7h-1a5 4.5 0 0 0 -11 -2a4.6 4.4 0 0 0 -2.1 8.4">
									</path>
									<path d="M12 13l0 9"></path>
									<path d="M9 19l3 3l3 -3"></path>
								</svg>
								Minta Data Hilang
								<span class="badge bg-white text-primary ms-2" id="rekon-jml-hilang">0</span>
							</button>
							<button class="btn btn-sm btn-outline-warning text-nowrap d-none" id="btn-ulangi">
								<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="16" height="16"
									viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
									stroke-linecap="round" stroke-linejoin="round">
									<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
									<path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4"></path>
									<path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4"></path>
								</svg>
								Ulangi Gagal
								<span class="badge bg-warning-lt ms-2" id="rekon-jml-gagal">0</span>
							</button>
							<button class="btn btn-sm btn-outline-danger text-nowrap d-none" id="btn-berhenti">
								<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-sm" width="16" height="16"
									viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
									stroke-linecap="round" stroke-linejoin="round">
									<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
									<path d="M5 5m0 2a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v10a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z">
									</path>
								</svg>
								Berhenti
							</button>
						</div>
					</div>
					<div class="card-body">
						<div id="rekon-grid"></div>
						<div class="rekon-legenda text-muted mt-3" style="font-size:12px">
							<span style="background:#e9ecef"></span>Ada
							<span style="background:#d63939;margin-left:14px"></span>Hilang
							<span style="background:#4299e1;margin-left:14px"></span>Hilang, masih ada di logger
							<span style="background:#f59f00;margin-left:14px"></span>Antre
							<span style="background:#ae3ec9;margin-left:14px"></span>Menunggu logger
							<span style="background:#2fb344;margin-left:14px"></span>Terkirim
							<span style="background:#7f1d1d;margin-left:14px"></span>Gagal
						</div>
						<div class="text-muted mt-2" id="rekon-status-antrian" style="font-size:12px"></div>
					</div>
				</div>
			</div>

			<div class="col-12">
				<div class="card">
					<div class="card-header">
						<h3 class="card-title">Riwayat</h3>
						<div class="card-actions">
							<button class="btn btn-sm" id="btn-bersih-log">Bersihkan</button>
						</div>
					</div>
					<div class="card-body">
						<pre id="rekon-log">Menunggu…</pre>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

<script type="text/javascript">
	(function () {
		var MQTTbroker = <?= json_encode($mqtt_broker) ?>;
		var MQTTport = 8083;
		var topikCmd = <?= json_encode($topik_cmd) ?>;
		var topikResp = <?= json_encode($topik_resp) ?>;
		var urlMenit = <?= json_encode(base_url('awlr/rekonsiliasi_menit')) ?>;
		var hariIni = <?= json_encode($hari_ini) ?>;

		var BATAS_DATA = 25000;   // ms menunggu satu REQ_DATA
		var BATAS_PENDING = 30000; // ms menunggu daftar pending
		var JEDA_MUAT_ULANG = 6000; // ms sebelum grid dimuat ulang setelah antrian habis
		var AMBANG_KONFIRMASI = 100; // jumlah request yang dianggap banyak

		var tanggal = hariIni;
		var menit = [];        // status per menit: ada | hilang | antre | menunggu | terkirim | gagal | luar
		var harapan = 0;
		var pendingLogger = {}; // menit yang dilaporkan logger masih tersimpan
		var antrian = [];
		var aktif = null;
		var timerData = null;
		var timerPending = null;
		var timerMuat = null;

		var $tanggal = $('#rekon-tanggal');
		var $grid = $('#rekon-grid');
		var $log = $('#rekon-log');
		var $btnPending = $('#btn-req-pending');
		var $btnHilang = $('#btn-minta-hilang');
		var $btnBisa = $('#btn-minta-bisa');
		var $btnUlangi = $('#btn-ulangi');
		var $btnBerhenti = $('#btn-berhenti');
		var $statusAntrian = $('#rekon-status-antrian');
		var $statusBroker = $('#rekon-status-broker');
		var $indicator = $('#rekon-indicator');

		// Riwayat dipakai user biasa — tulis kejadiannya saja, tanpa
		// istilah teknis jalur komunikasi maupun isi perintah mentah.
		function tulisLog(pesan) {
			var d = new Date();
			var jam = ('0' + d.getHours()).slice(-2) + ':' + ('0' + d.getMinutes()).slice(-2) + ':' + ('0' + d.getSeconds()).slice(-2);
			if ($log.text() === 'Menunggu…') {
				$log.text('');
			}
			$log.append('[' + jam + '] ' + pesan + '\n');
			$log.scrollTop($log[0].scrollHeight);
		}

		function setIndikator(warna) {
			$indicator.attr('class', 'status-indicator status-' + warna + ' status-indicator-animated');
		}

		function jamMenit(i) {
			return ('0' + Math.floor(i / 60)).slice(-2) + ':' + ('0' + (i % 60)).slice(-2);
		}

		function kelasSel(i) {
			var status = menit[i];
			if (status === 'hilang' && pendingLogger[i]) {
				return 'rekon-sel bisa';
			}
			return 'rekon-sel ' + (status || 'luar');
		}

		function bangunGrid() {
			var html = [];
			for (var jam = 0; jam < 24; jam++) {
				html.push('<div class="rekon-baris-jam"><div class="rekon-label-jam">' + ('0' + jam).slice(-2) + '</div><div class="rekon-grid">');
				for (var m = 0; m < 60; m++) {
					var i = jam * 60 + m;
					html.push('<div class="rekon-sel luar" id="sel-' + i + '" data-menit="' + i + '" title="' + jamMenit(i) + '"></div>');
				}
				html.push('</div></div>');
			}
			$grid.html(html.join(''));
		}

		function gambarSel(i) {
			var el = document.getElementById('sel-' + i);
			if (el) {
				el.className = kelasSel(i);
			}
		}

		function gambarSemuaSel() {
			for (var i = 0; i < 1440; i++) {
				gambarSel(i);
			}
		}

		function daftarStatus(status) {
			var hasil = [];
			for (var i = 0; i < 1440; i++) {
				if (menit[i] === status) {
					hasil.push(i);
				}
			}
			return hasil;
		}

		// Menit yang hilang di database tapi masih dipegang logger —
		// ini yang benar-benar bisa ditarik, sisanya kemungkinan besar
		// sudah tidak ada dan cuma akan dijawab tanpa data.
		function daftarBisa() {
			return daftarStatus('hilang').filter(function (i) {
				return pendingLogger[i];
			});
		}

		function ringkas() {
			var ada = 0, hilang = 0;
			for (var i = 0; i < harapan; i++) {
				if (menit[i] === 'ada' || menit[i] === 'terkirim') {
					ada++;
				} else if (menit[i] === 'luar') {
					// belum dimuat
				} else {
					hilang++;
				}
			}
			var persen = harapan ? (ada / harapan * 100) : 0;

			$('#rekon-persen').text(persen.toFixed(2) + '%');
			$('#rekon-ada').text(ada);
			$('#rekon-harapan').text(harapan);
			$('#rekon-hilang').text(hilang);
			$('#rekon-bar').css('width', persen.toFixed(2) + '%');

			var sisaHilang = daftarStatus('hilang').length;
			var gagal = daftarStatus('gagal').length;
			var sibuk = aktif !== null;

			var bisa = daftarBisa().length;

			$('#rekon-jml-hilang').text(sisaHilang);
			$('#rekon-jml-gagal').text(gagal);
			$('#rekon-jml-bisa').text(bisa);
			$btnBisa.toggleClass('d-none', bisa === 0).prop('disabled', !siapKirim() || sibuk);
			// btn-loading membuat teks transparan, tapi badge punya latar sendiri
			// jadi tetap kelihatan — tombol ini cukup dinonaktifkan saja.
			$btnHilang.prop('disabled', !siapKirim() || sisaHilang === 0 || sibuk);
			$btnUlangi.toggleClass('d-none', gagal === 0 || sibuk);
			$btnBerhenti.toggleClass('d-none', antrian.length === 0);

			if (aktif) {
				$statusAntrian.text('Meminta menit ' + jamMenit(aktif) + ' — sisa antrian ' + antrian.length);
			} else if (antrian.length) {
				$statusAntrian.text('Antrian ' + antrian.length + ' menit');
			} else {
				$statusAntrian.text('');
			}
		}

		function muatHari(tgl) {
			batalkanAntrian();
			tanggal = tgl;
			pendingLogger = {};
			menit = [];
			for (var i = 0; i < 1440; i++) {
				menit[i] = 'luar';
			}
			harapan = 0;
			gambarSemuaSel();
			$('#rekon-persen').text('…');

			$.ajax({
				url: urlMenit,
				type: 'GET',
				dataType: 'json',
				data: { tanggal: tgl },
				success: function (res) {
					if (!res || res.status !== 'ok') {
						tulisLog('Gagal memuat data ' + tgl);
						return;
					}
					harapan = res.harapan;
					for (var i = 0; i < harapan; i++) {
						menit[i] = 'hilang';
					}
					(res.ada || []).forEach(function (i) {
						menit[i] = 'ada';
					});
					gambarSemuaSel();
					ringkas();
					tulisLog(tgl + ' — ' + (res.ada || []).length + ' dari ' + harapan + ' menit terisi');
				},
				error: function () {
					tulisLog('Gagal memuat data ' + tgl);
				}
			});
		}

		function kirimPerintah(payload) {
			var message = new Paho.MQTT.Message(payload);
			message.destinationName = topikCmd;
			message.qos = 1;
			message.retained = false;   // perintah retained akan diulang tiap logger reconnect

			try {
				client.send(message);
			} catch (e) {
				tulisLog('Gagal mengirim permintaan ke logger');
				return false;
			}
			return true;
		}

		function siapKirim() {
			return client && client.isConnected();
		}

		// REQ_DATA dikirim satu per satu — perintah berikutnya menunggu
		// COMPLETE atau timeout, biar logger tidak dibanjiri.
		function prosesAntrian() {
			if (aktif !== null || !antrian.length) {
				if (aktif === null && !antrian.length) {
					ringkas();
					jadwalkanMuatUlang();
				}
				return;
			}

			var i = antrian.shift();
			if (menit[i] === 'ada' || menit[i] === 'terkirim') {
				prosesAntrian();
				return;
			}

			aktif = i;
			menit[i] = 'menunggu';
			gambarSel(i);
			ringkas();

			var payload = JSON.stringify({
				DATA: { cmd: 'REQ_DATA', date: tanggal, time: jamMenit(i) }
			});
			if (!kirimPerintah(payload)) {
				menit[i] = 'gagal';
				gambarSel(i);
				aktif = null;
				ringkas();
				return;
			}

			pasangTimerData(i);
		}

		function pasangTimerData(i) {
			if (timerData) {
				clearTimeout(timerData);
			}
			timerData = setTimeout(function () {
				if (aktif === i) {
					menit[i] = 'gagal';
					gambarSel(i);
					tulisLog('Tidak ada respon dari logger untuk menit ' + jamMenit(i));
					selesaikanAktif();
				}
			}, BATAS_DATA);
		}

		function selesaikanAktif() {
			if (timerData) {
				clearTimeout(timerData);
				timerData = null;
			}
			aktif = null;
			ringkas();
			prosesAntrian();
		}

		function antre(daftar) {
			daftar.forEach(function (i) {
				if (antrian.indexOf(i) >= 0 || aktif === i) {
					return;
				}
				if (menit[i] === 'ada' || menit[i] === 'terkirim') {
					return;
				}
				menit[i] = 'antre';
				gambarSel(i);
				antrian.push(i);
			});
			ringkas();
			prosesAntrian();
		}

		function batalkanAntrian() {
			antrian.forEach(function (i) {
				if (menit[i] === 'antre') {
					menit[i] = 'hilang';
					gambarSel(i);
				}
			});
			antrian = [];
			if (timerMuat) {
				clearTimeout(timerMuat);
				timerMuat = null;
			}
			ringkas();
		}

		// Data hasil REQ_DATA masuk database lewat subscriber yang sudah jalan,
		// jadi grid dimuat ulang sebentar setelah antrian habis.
		function jadwalkanMuatUlang() {
			if (timerMuat || !daftarStatus('terkirim').length) {
				return;
			}
			timerMuat = setTimeout(function () {
				timerMuat = null;
				tulisLog('Memuat ulang ' + tanggal);
				muatHari(tanggal);
			}, JEDA_MUAT_ULANG);
		}

		function keMenit(tgl8, jam5) {
			if (!/^\d{8}$/.test(tgl8) || !/^\d{2}:\d{2}$/.test(jam5)) {
				return null;
			}
			var iso = tgl8.slice(0, 4) + '-' + tgl8.slice(4, 6) + '-' + tgl8.slice(6, 8);
			if (iso !== tanggal) {
				return null;   // respons untuk tanggal lain, abaikan
			}
			return parseInt(jam5.slice(0, 2), 10) * 60 + parseInt(jam5.slice(3, 5), 10);
		}

		// "ACCEPTED,20260805,23:50" / "COMPLETE,…" / "NO_DATA,…"
		function tanganiReqData(resp) {
			var bagian = String(resp || '').split(',');
			var status = (bagian[0] || '').trim().toUpperCase();
			var i = keMenit((bagian[1] || '').trim(), (bagian[2] || '').trim());
			if (i === null) {
				return;
			}

			if (status === 'ACCEPTED') {
				if (aktif === i) {
					pasangTimerData(i);   // data menyusul, timer diperpanjang
				}
				return;
			}

			menit[i] = (status === 'COMPLETE') ? 'terkirim' : 'gagal';
			gambarSel(i);

			if (aktif === i) {
				selesaikanAktif();
			} else {
				ringkas();
			}
		}

		// "20260805,23:32|20260805,23:33,COMPLETE" atau "EMPTY"
		function tanganiPending(resp) {
			resp = String(resp || '').trim();
			if (timerPending) {
				clearTimeout(timerPending);
				timerPending = null;
			}
			$btnPending.prop('disabled', !siapKirim()).removeClass('btn-loading');

			if (resp.toUpperCase() === 'EMPTY') {
				tulisLog('Logger tidak menyimpan data cadangan');
				return;
			}

			var jumlah = 0;
			resp.split('|').forEach(function (token) {
				var bagian = token.trim().split(',');
				var i = keMenit((bagian[0] || '').trim(), (bagian[1] || '').trim());
				if (i === null) {
					return;
				}
				pendingLogger[i] = true;
				jumlah++;
				gambarSel(i);
			});
			tulisLog(jumlah + ' menit pada tanggal ini masih tersimpan di logger');
			ringkas();
		}

		var client = new Paho.MQTT.Client(MQTTbroker, MQTTport, 'rekon_' + parseInt(Math.random() * 100000, 10));

		client.onConnectionLost = function (res) {
			setIndikator('secondary');
			$statusBroker.text('Koneksi ke logger terputus');
			$btnPending.prop('disabled', true).removeClass('btn-loading');
			$btnHilang.prop('disabled', true);
			$btnBisa.prop('disabled', true);
			tulisLog('Koneksi ke logger terputus');
		};

		client.onMessageArrived = function (message) {
			if (message.destinationName !== topikResp) {
				return;
			}
			var obj;
			try {
				obj = JSON.parse(message.payloadString);
			} catch (e) {
				return;
			}
			if (!obj || !obj.DATA) {
				return;
			}
			if (obj.DATA.cmd === 'REQDATA') {
				tanganiReqData(obj.DATA.response);
			} else if (obj.DATA.cmd === 'REQPENDING') {
				tanganiPending(obj.DATA.response);
			}
			// command lain diabaikan
		};

		client.connect({
			timeout: 3,
			useSSL: true,
			userName: 'userlog',
			password: 'b34c0n',
			onSuccess: function () {
				setIndikator('green');
				$statusBroker.text('Terhubung');
				$btnPending.prop('disabled', false);
				client.subscribe(topikResp, { qos: 1 });
				tulisLog('Terhubung ke logger');
				ringkas();
			},
			onFailure: function (res) {
				setIndikator('red');
				$statusBroker.text('Gagal terhubung ke logger');
				tulisLog('Gagal terhubung ke logger');
			}
		});

		$grid.on('click', '.rekon-sel', function () {
			if (!siapKirim()) {
				return;
			}
			var i = parseInt($(this).data('menit'), 10);
			if (menit[i] === 'hilang' || menit[i] === 'gagal') {
				antre([i]);
			}
		});

		$btnBisa.on('click', function () {
			var bisa = daftarBisa();
			if (!bisa.length) {
				return;
			}
			tulisLog('Meminta ' + bisa.length + ' menit yang masih tersimpan di logger');
			antre(bisa);
		});

		$btnHilang.on('click', function () {
			var hilang = daftarStatus('hilang');
			if (!hilang.length) {
				return;
			}
			if (hilang.length > AMBANG_KONFIRMASI &&
				!confirm(hilang.length + ' menit akan diminta satu per satu ke logger. Lanjutkan?')) {
				return;
			}
			tulisLog('Meminta ' + hilang.length + ' menit yang hilang pada ' + tanggal);
			antre(hilang);
		});

		$btnUlangi.on('click', function () {
			var gagal = daftarStatus('gagal');
			if (!gagal.length) {
				return;
			}
			tulisLog('Mengulang ' + gagal.length + ' menit yang gagal pada ' + tanggal);
			antre(gagal);
		});

		$btnBerhenti.on('click', function () {
			batalkanAntrian();
			tulisLog('Permintaan dibatalkan');
		});

		$btnPending.on('click', function () {
			if (!kirimPerintah(JSON.stringify({ DATA: { cmd: 'REQ_PENDING' } }))) {
				return;
			}
			$btnPending.prop('disabled', true).addClass('btn-loading');
			timerPending = setTimeout(function () {
				timerPending = null;
				$btnPending.prop('disabled', !siapKirim()).removeClass('btn-loading');
				tulisLog('Tidak ada respon dari logger');
			}, BATAS_PENDING);
		});

		$tanggal.on('change', function () {
			var nilai = $(this).val();
			if (nilai && nilai <= hariIni) {
				muatHari(nilai);
			}
		});

		function geserHari(selisih) {
			var d = new Date(tanggal + 'T00:00:00');
			d.setDate(d.getDate() + selisih);
			var baru = d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2);
			if (baru > hariIni) {
				return;
			}
			$tanggal.val(baru);
			muatHari(baru);
		}

		$('#btn-hari-mundur').on('click', function () { geserHari(-1); });
		$('#btn-hari-maju').on('click', function () { geserHari(1); });

		$('#btn-bersih-log').on('click', function () {
			$log.text('Menunggu…');
		});

		$(window).on('beforeunload', function () {
			try {
				client.disconnect();
			} catch (e) { }
		});

		bangunGrid();
		muatHari(hariIni);
	})();
</script>
