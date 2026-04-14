<script src="<?php echo base_url(); ?>code/highcharts.js"></script>
<script src="<?php echo base_url(); ?>code/highcharts-more.js"></script>
<script src="<?php echo base_url(); ?>code/modules/series-label.js"></script>
<script src="<?php echo base_url(); ?>code/modules/exporting.js"></script>
<script src="<?php echo base_url(); ?>code/modules/export-data.js"></script>
<script src="<?php echo base_url(); ?>code/js/themes/grid.js"></script>
<style>
	/* Custom Picker */
	.custom-picker-wrap {
		position: relative;
	}

	.custom-picker-dropdown {
		display: none;
		position: absolute;
		top: 100%;
		left: 0;
		right: 0;
		z-index: 100;
		background: #fff;
		border: 1px solid #e0e0e0;
		border-radius: 8px;
		box-shadow: 0 4px 16px rgba(0, 0, 0, .12);
		margin-top: 4px;
		padding: 8px;
	}

	.custom-picker-dropdown.show {
		display: block;
	}

	.custom-picker-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 4px 0;
		margin-bottom: 8px;
	}

	.custom-picker-header .cp-nav {
		background: none;
		border: none;
		cursor: pointer;
		padding: 4px 10px;
		font-size: 18px;
		color: #666;
		border-radius: 4px;
		line-height: 1;
	}

	.custom-picker-header .cp-nav:hover {
		background: #f0f0f0;
	}

	.custom-picker-header .cp-label {
		font-weight: 600;
		font-size: 14px;
		user-select: none;
	}

	.custom-picker-grid {
		display: grid;
		grid-template-columns: repeat(3, 1fr);
		gap: 6px;
	}

	.custom-picker-grid .cp-item {
		padding: 8px 4px;
		text-align: center;
		border-radius: 6px;
		cursor: pointer;
		font-size: 13px;
		transition: background 0.15s, color 0.15s;
		border: none;
		background: none;
		width: 100%;
	}

	.custom-picker-grid .cp-item:hover {
		background: #e9ecef;
	}

	.custom-picker-grid .cp-item.active {
		background: #6c757d;
		color: #fff;
	}

	@media only screen and (max-width:576px) {
		#target {
			display: none
		}
	}

	.btn-info {
		background-color: #303481
	}

	.btn-info:hover {
		text-decoration: none;
		background-color: #000342;
		border-color: #000342
	}

	.circle {
		width: 12px;
		height: 12px;
		border-radius: 50%;
		box-shadow: 0 0 1px 1px #0000001a
	}

	.pulse-brown {
		background: #876a2f;
		animation: pulse-animation-brown 2s infinite
	}

	@keyframes pulse-animation-brown {
		0% {
			box-shadow: 0 0 0 0 #876a2f
		}

		100% {
			box-shadow: 0 0 0 15px rgba(0, 0, 0, 0)
		}
	}
</style>
<?php
$namasensor = str_replace('_', ' ', $data_sensor->namaSensor);
$satuan = $data_sensor->satuan;
$tooltip = $data_sensor->tooltip;
$data = $data_sensor->data;
$range = $data_sensor->range;
$typegraf = $data_sensor->tipe_grafik;
$mode = $data_sensor->mode_data ?? 'hari';
$idLogger = $data_sensor->idLogger;
$tglPada = $data_sensor->pada;
$tglDari = $data_sensor->dari;
$tglSampai = $data_sensor->sampai;
$isPerbaikan = ($temp_data['stts'] === '1');
$labels = ['hari' => 'Pilih Tanggal', 'bulan' => 'Pilih Bulan', 'tahun' => 'Pilih Tahun', 'range' => 'Pilih Rentang Waktu'];
$actions = ['hari' => 'analisa/set_token', 'bulan' => 'analisa/set_token', 'tahun' => 'analisa/set_token', 'range' => 'analisa/set_token'];
$title = ($mode === 'range') ? ' dari ' . $tglDari . ' sampai ' . $tglSampai : ' pada ' . $tglPada;
if ($mode === 'hari' && $typegraf === 'column') {
	$akumRaw = (float) ($data_sensor->akumulasi_hujan ?? 0);
	$akumStr = number_format($akumRaw, 1, '.', '');
	$legend = [['#84c450', 'Tidak Hujan', '0 mm'], ['#70cddd', 'Hujan Sangat Ringan', '0.1 - 1 mm'], ['#35549d', 'Hujan Ringan', '1 - 5 mm'], ['#fef216', 'Hujan Sedang', '5 - 10 mm'], ['#f47e2c', 'Hujan Lebat', '10 - 20 mm'], ['#ed1c24', 'Hujan Sangat Lebat', '≥ 20 mm']];
	$zones = [['value' => 0.1, 'color' => '#78c145'], ['value' => 1, 'color' => '#70cddd'], ['value' => 5, 'color' => '#35549d'], ['value' => 10, 'color' => '#fef216'], ['value' => 20, 'color' => '#f47e2c'], ['color' => '#ed1c24']];
	$img = 'kotak-hijau.png';
	$txtCls = 'text-white';
	$txtAkum = 'Tidak Hujan';
	if ($akumRaw <= 0) {
		$img = 'kotak-hijau.png';
		$txtCls = 'text-white';
		$txtAkum = 'Tidak Hujan';
	} elseif ($akumRaw < 5 && $akumRaw >= 0.1) {
		$img = 'kotak-cyan.png';
		$txtCls = 'text-white';
		$txtAkum = 'Hujan Sangat Ringan';
	} elseif ($akumRaw < 20) {
		$img = 'kotak-nila.png';
		$txtCls = 'text-white';
		$txtAkum = 'Hujan Ringan';
	} elseif ($akumRaw < 50) {
		$img = 'kotak-kuning.png';
		$txtCls = '';
		$txtAkum = 'Hujan Sedang';
	} elseif ($akumRaw < 100) {
		$img = 'kotak-oranye.png';
		$txtCls = 'text-white';
		$txtAkum = 'Hujan Lebat';
	} else {
		$img = 'kotak-merah.png';
		$txtCls = 'text-white';
		$txtAkum = 'Hujan Sangat Lebat';
	}
} elseif ($mode === 'range') {
	$days = ($tglDari && $tglSampai) ? max(1, (strtotime($tglSampai) - strtotime($tglDari)) / 86400) : 1;
	$avgPerDay = (float) ($data_sensor->akumulasi_hujan ?? 0) / $days;
	$akumRaw = $avgPerDay;
	$akumStr = number_format((float) ($data_sensor->akumulasi_hujan ?? 0), 1, '.', '');
	$legend = [['#84c450', 'Tidak Hujan', '0 mm'], ['#70cddd', 'Hujan Sangat Ringan', '0.1 - 5 mm'], ['#35549d', 'Hujan Ringan', '5 - 20 mm'], ['#fef216', 'Hujan Sedang', '20 - 50 mm'], ['#f47e2c', 'Hujan Lebat', '50 - 100 mm'], ['#ed1c24', 'Hujan Sangat Lebat', '≥ 100 mm']];
	$zones = [['value' => 0.1, 'color' => '#78c145'], ['value' => 5, 'color' => '#70cddd'], ['value' => 20, 'color' => '#35549d'], ['value' => 50, 'color' => '#fef216'], ['value' => 100, 'color' => '#f47e2c'], ['color' => '#ed1c24']];
	$img = 'kotak-hijau.png';
	$txtCls = 'text-white';
	if ($akumRaw <= 0) {
		$img = 'kotak-hijau.png';
		$txtCls = 'text-white';
	} elseif ($akumRaw < 5 && $akumRaw >= 0.1) {
		$img = 'kotak-cyan.png';
		$txtCls = 'text-white';
	} elseif ($akumRaw < 20) {
		$img = 'kotak-nila.png';
		$txtCls = 'text-white';
	} elseif ($akumRaw < 50) {
		$img = 'kotak-kuning.png';
		$txtCls = '';
	} elseif ($akumRaw < 100) {
		$img = 'kotak-oranye.png';
		$txtCls = 'text-white';
	} else {
		$img = 'kotak-merah.png';
		$txtCls = 'text-white';
	}
} elseif ($typegraf === 'column' && $mode === 'bulan') {
	$legend = [['#84c450', 'Tidak Hujan', '0 mm'], ['#70cddd', 'Hujan Sangat Ringan', '0.1 - 5 mm'], ['#35549d', 'Hujan Ringan', '5 - 20 mm'], ['#fef216', 'Hujan Sedang', '20 - 50 mm'], ['#f47e2c', 'Hujan Lebat', '50 - 100 mm'], ['#ed1c24', 'Hujan Sangat Lebat', '≥ 100 mm']];
	$zones = [['value' => 0.1, 'color' => '#78c145'], ['value' => 5, 'color' => '#70cddd'], ['value' => 20, 'color' => '#35549d'], ['value' => 50, 'color' => '#fef216'], ['value' => 100, 'color' => '#f47e2c'], ['color' => '#ed1c24']];
}
$namafile = ($data_sensor->mode_data === 'range') ? ($temp_data['nama_lokasi'] . ' - ' . str_replace('_', ' ', $data_sensor->namaSensor) . ' - ' . $data_sensor->dari . ' - ' . $data_sensor->sampai) : ($temp_data['nama_lokasi'] . ' - ' . $data_sensor->namaSensor . ' - ' . $data_sensor->pada);
?>
<div class="container-md">
	<div class="page-header d-print-none">
		<div class="row g-3 align-items-center">
			<div class="col-auto">
				<?= anchor('analisa', '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-big-left-lines" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 15v3.586a1 1 0 0 1 -1.707 .707l-6.586 -6.586a1 1 0 0 1 0 -1.414l6.586 -6.586a1 1 0 0 1 1.707 .707v3.586h3v6h-3z"></path><path d="M21 15v-6"></path><path d="M18 15v-6"></path></svg>') ?>
			</div>
			<div class="col-auto">
				<?php if ($isPerbaikan) { ?>
					<div class="circle pulse-brown mx-3"></div><?php } else { ?>
					<span
						class="status-indicator status-<?= htmlspecialchars($temp_data['color'], ENT_QUOTES) ?> status-indicator-animated"><span
							class="status-indicator-circle"></span><span class="status-indicator-circle"></span><span
							class="status-indicator-circle"></span></span>
				<?php } ?>
			</div>
			<div class="col col-md-auto">
				<h2 class="page-title mb-1"><?= htmlspecialchars($temp_data['nama_lokasi'], ENT_QUOTES) ?></h2>
				<div class="text-muted">
					<ul class="list-inline list-inline-dots mb-0">
						<li class="list-inline-item">
							<?php if ($isPerbaikan) { ?>
								<span
									style="color:#876a2f"><?= htmlspecialchars($temp_data['status_logger'], ENT_QUOTES) ?></span>
							<?php } else { ?>
								<span
									class="text-<?= htmlspecialchars($temp_data['color'], ENT_QUOTES) ?>"><?= htmlspecialchars($temp_data['status_logger'], ENT_QUOTES) ?></span>
							<?php } ?>
						</li>
					</ul>
				</div>
			</div>
			<div class="col-12 col-md">
				<div class="row g-2 align-items-center justify-content-end">
					<div class="col-4 d-md-none">
						<button type="button" class="btn w-100 toggle">
							<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-layout-list"
								width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
								fill="none" stroke-linecap="round" stroke-linejoin="round">
								<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
								<path
									d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z">
								</path>
								<path
									d="M4 14m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z">
								</path>
							</svg>
							Opsi
						</button>
					</div>
					<div class="col-8 col-md-auto d-flex">
						<?php if ($idLogger === '10247' || $idLogger === '10249') { ?>
							<button type="button" class="btn btn-primary me-2" data-bs-toggle="modal"
								data-bs-target="#skema_perangkat">
								<svg xmlns="http://www.w3.org/2000/svg"
									class="icon icon-tabler icons-tabler-outline icon-tabler-schema" width="24" height="24"
									viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
									stroke-linecap="round" stroke-linejoin="round">
									<path stroke="none" d="M0 0h24v24H0z" fill="none" />
									<path d="M5 2h5v4h-5z" />
									<path d="M15 10h5v4h-5z" />
									<path d="M5 18h5v4h-5z" />
									<path d="M5 10h5v4h-5z" />
									<path d="M10 12h5" />
									<path d="M7.5 6v4" />
									<path d="M7.5 14v4" />
								</svg>
								Skema Perangkat
							</button>
						<?php } ?>
						<a class="btn w-100" data-bs-toggle="offcanvas" href="#offcanvasEnd" role="button"
							aria-controls="offcanvasEnd">
							<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-info"
								width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
								fill="none" stroke-linecap="round" stroke-linejoin="round">
								<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
								<path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
								<path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
								<path d="M11 14h1v4h1"></path>
								<path d="M12 11h.01"></path>
							</svg>
							Informasi
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="page-body">
	<div class="container-xl">
		<div class="row row-cards">
			<div class="col-md-3 col-xxl-2" id="target">
				<div class="row row-cards">
					<div class="col-md-12">
						<div class="card">
							<div class="card-body">
								<div class="subheader"><label class="form-label">Pilih Pos AWLR</label></div>
								<div class="h3 m-0">
									<form action="<?= base_url() ?>analisa/set_token" method="post" id="form-pos">
										<input value="<?= $token ?>" name="token" class="d-none" />
										<select name="id_logger" class="form-select" onchange="validate_form()"
											id="select-pos">
											<option value="">Pilih Pos</option>
											<?php foreach ($pilih_pos as $mnpos):
												$merge = explode('_', $mnpos->idLogger);
												$log_select = $merge[0];
												?>

												<option value="<?= $mnpos->idLogger ?>" <?= ($idLogger == $log_select) ? 'selected' : '' ?>><?= str_replace('_', ' ', $mnpos->namaPos) ?>
												</option>
											<?php endforeach ?>
										</select>
									</form>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="card">
							<div class="card-body">
								<div class="subheader"><label class="form-label">Pilih Parameter</label></div>
								<div class="h3 m-0">

									<?= form_open('analisa/set_token'); ?>
									<input value="<?= $token ?>" name="token" class="d-none" />
									<select name="id_param" class="form-select" onchange="this.form.submit()"
										id="select-parameter">
										<option value="">Pilih Parameter</option>
										<?php foreach ($pilih_parameter as $mnparameter): ?>
											<option value="<?= $mnparameter->idParameter ?>"
												<?= ($data_sensor->idParam == $mnparameter->idParameter) ? 'selected' : '' ?>>
												<?= str_replace('_', ' ', $mnparameter->namaParameter) ?>
											</option>
										<?php endforeach ?>
									</select>
									<?= form_close(); ?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="card">
							<div class="card-body">
								<div class="subheader"><label class="form-label"><?= $labels[$mode] ?></label></div>
								<div class="h3 m-0">
									<?= form_open($actions[$mode]) ?>
									<input value="<?= $token ?>" name="token" class="d-none" />
									<div class="row">
										<div class="col-12">
											<?php if ($mode === 'hari') { ?>
												<div class="input-icon">
													<input class="form-control" name="pada" id="dptanggal"
														placeholder="Pilih Tanggal" value="<?= $tglPada ?>"
														autocomplete="off" required />
													<span class="input-icon-addon"><svg xmlns="http://www.w3.org/2000/svg"
															class="icon" width="24" height="24" viewBox="0 0 24 24"
															stroke-width="2" stroke="currentColor" fill="none"
															stroke-linecap="round" stroke-linejoin="round">
															<path stroke="none" d="M0 0h24v24H0z" fill="none" />
															<rect x="4" y="5" width="16" height="16" rx="2" />
															<line x1="16" y1="3" x2="16" y2="7" />
															<line x1="8" y1="3" x2="8" y2="7" />
															<line x1="4" y1="11" x2="20" y2="11" />
															<line x1="11" y1="15" x2="12" y2="15" />
															<line x1="12" y1="15" x2="12" y2="18" />
														</svg></span>
												</div>
											<?php } elseif ($mode === 'bulan') { ?>
												<input type="hidden" name="pada" id="monthPickerValue"
													value="<?= $tglPada ?>" required />
												<div class="custom-picker-wrap">
													<div class="input-icon">
														<input class="form-control" id="monthPickerInput"
															placeholder="Pilih Bulan" value="<?= $tglPada ?>"
															autocomplete="off" readonly
															style="cursor:pointer;background:#fff" />
														<span class="input-icon-addon"><svg
																xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
																height="24" viewBox="0 0 24 24" stroke-width="2"
																stroke="currentColor" fill="none" stroke-linecap="round"
																stroke-linejoin="round">
																<path stroke="none" d="M0 0h24v24H0z" fill="none" />
																<rect x="4" y="5" width="16" height="16" rx="2" />
																<line x1="16" y1="3" x2="16" y2="7" />
																<line x1="8" y1="3" x2="8" y2="7" />
																<line x1="4" y1="11" x2="20" y2="11" />
																<line x1="11" y1="15" x2="12" y2="15" />
																<line x1="12" y1="15" x2="12" y2="18" />
															</svg></span>
													</div>
													<div class="custom-picker-dropdown" id="monthPickerDropdown">
														<div class="custom-picker-header">
															<button type="button" class="cp-nav"
																id="mpPrev">&#8249;</button>
															<span class="cp-label" id="mpYearLabel"></span>
															<button type="button" class="cp-nav"
																id="mpNext">&#8250;</button>
														</div>
														<div class="custom-picker-grid" id="mpGrid"></div>
													</div>
												</div>
											<?php } elseif ($mode === 'tahun') { ?>
												<input type="hidden" name="pada" id="yearPickerValue"
													value="<?= $tglPada ?>" required />
												<div class="custom-picker-wrap">
													<div class="input-icon">
														<input class="form-control" id="yearPickerInput"
															placeholder="Pilih Tahun" value="<?= $tglPada ?>"
															autocomplete="off" readonly
															style="cursor:pointer;background:#fff" />
														<span class="input-icon-addon"><svg
																xmlns="http://www.w3.org/2000/svg" class="icon" width="24"
																height="24" viewBox="0 0 24 24" stroke-width="2"
																stroke="currentColor" fill="none" stroke-linecap="round"
																stroke-linejoin="round">
																<path stroke="none" d="M0 0h24v24H0z" fill="none" />
																<rect x="4" y="5" width="16" height="16" rx="2" />
																<line x1="16" y1="3" x2="16" y2="7" />
																<line x1="8" y1="3" x2="8" y2="7" />
																<line x1="4" y1="11" x2="20" y2="11" />
																<line x1="11" y1="15" x2="12" y2="15" />
																<line x1="12" y1="15" x2="12" y2="18" />
															</svg></span>
													</div>
													<div class="custom-picker-dropdown" id="yearPickerDropdown">
														<div class="custom-picker-header">
															<button type="button" class="cp-nav"
																id="ypPrev">&#8249;</button>
															<span class="cp-label" id="ypRangeLabel"></span>
															<button type="button" class="cp-nav"
																id="ypNext">&#8250;</button>
														</div>
														<div class="custom-picker-grid" id="ypGrid"></div>
													</div>
												</div>
											<?php } else { ?>
												<div class="row">
													<div class="col-12">
														<label class="form-label">Dari</label>
														<div class="input-icon">
															<input class="form-control" name="dari" id="dpdari"
																placeholder="Dari" value="<?= $tglDari ?>"
																autocomplete="off" required />
															<span class="input-icon-addon"><svg
																	xmlns="http://www.w3.org/2000/svg" class="icon"
																	width="24" height="24" viewBox="0 0 24 24"
																	stroke-width="2" stroke="currentColor" fill="none"
																	stroke-linecap="round" stroke-linejoin="round">
																	<path stroke="none" d="M0 0h24v24H0z" fill="none" />
																	<rect x="4" y="5" width="16" height="16" rx="2" />
																	<line x1="16" y1="3" x2="16" y2="7" />
																	<line x1="8" y1="3" x2="8" y2="7" />
																	<line x1="4" y1="11" x2="20" y2="11" />
																	<line x1="11" y1="15" x2="12" y2="15" />
																	<line x1="12" y1="15" x2="12" y2="18" />
																</svg></span>
														</div>
													</div>
													<div class="col-12">
														<label class="form-label mt-2">Sampai</label>
														<div class="input-icon">
															<input class="form-control" name="sampai" id="dpsampai"
																placeholder="Sampai" value="<?= $tglSampai ?>"
																autocomplete="off" required />
															<span class="input-icon-addon"><svg
																	xmlns="http://www.w3.org/2000/svg" class="icon"
																	width="24" height="24" viewBox="0 0 24 24"
																	stroke-width="2" stroke="currentColor" fill="none"
																	stroke-linecap="round" stroke-linejoin="round">
																	<path stroke="none" d="M0 0h24v24H0z" fill="none" />
																	<rect x="4" y="5" width="16" height="16" rx="2" />
																	<line x1="16" y1="3" x2="16" y2="7" />
																	<line x1="8" y1="3" x2="8" y2="7" />
																	<line x1="4" y1="11" x2="20" y2="11" />
																	<line x1="11" y1="15" x2="12" y2="15" />
																	<line x1="12" y1="15" x2="12" y2="18" />
																</svg></span>
														</div>
													</div>
												</div>
											<?php } ?>
											<div class="form-footer mt-3"><input type="submit"
													class="btn btn-info w-100" value="Tampil" /></div>
										</div>
									</div>
									<?= form_close() ?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="card">
							<div class="card-body pt-3">
								<div class="subheader mb-3"><label class="form-label">Analisa dalam</label></div>
								<?= form_open('analisa/set_token') ?>
								<input value="<?= $token ?>" name="token" class="d-none" />
								<label class="form-check"><input class="form-check-input" type="radio" name="mode"
										value="hari" onclick="this.form.submit()" <?= $mode === 'hari' ? 'checked' : '' ?> /><span class="form-check-label">Hari</span></label>
								<label class="form-check"><input class="form-check-input" type="radio" name="mode"
										value="bulan" onclick="this.form.submit()" <?= $mode === 'bulan' ? 'checked' : '' ?> /><span class="form-check-label">Bulan</span></label>
								<label class="form-check"><input class="form-check-input" type="radio" name="mode"
										value="tahun" onclick="this.form.submit()" <?= $mode === 'tahun' ? 'checked' : '' ?> /><span class="form-check-label">Tahun</span></label>
								<label class="form-check mb-0"><input class="form-check-input" type="radio" name="mode"
										value="range" onclick="this.form.submit()" <?= $mode === 'range' ? 'checked' : '' ?> /><span class="form-check-label">Rentang Waktu</span></label>
								<?= form_close() ?>
							</div>
						</div>
					</div>
					<?php if ($this->session->userdata('username') != 'serayu_opak') { ?>
						<div class="col-md-12">
							<div class="card">
								<div class="card-body">
									<button onclick="ExportToExcel('xlsx')" class="btn btn-outline-success w-100"><svg
											xmlns="http://www.w3.org/2000/svg"
											class="icon icon-tabler icon-tabler-file-spreadsheet" width="40" height="40"
											viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
											stroke-linecap="round" stroke-linejoin="round">
											<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
											<path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
											<path
												d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z">
											</path>
											<path d="M8 11h8v7h-8z"></path>
											<path d="M8 15h8"></path>
											<path d="M11 11v7"></path>
										</svg>Download Excel</button>
									<?php if ($data_op) { ?>
										<button data-bs-toggle="modal" data-bs-target="#exampleModal"
											class="btn btn-outline-primary w-100 mt-3"><svg xmlns="http://www.w3.org/2000/svg"
												width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
												stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
												class="icon icon-tabler icons-tabler-outline icon-tabler-history">
												<path stroke="none" d="M0 0h24v24H0z" fill="none" />
												<path d="M12 8l0 4l2 2" />
												<path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" />
											</svg>Riwayat O & P</button>
									<?php } ?>
								</div>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
			<div class="col-md-9 col-xxl-10">
				<?php if ($mode === 'hari' && $typegraf === 'column') { ?>
					<div class="row row-cards">
						<div class="col-sm-6 col-lg-3 mb-lg-3">
							<div class="card card-sm h-100">
								<div class="card-body d-flex align-items-center">
									<div class="row align-items-center">
										<div class="col-auto">
											<span class="form-control w-100 rounded <?= $txtCls ?>"
												style="background-image:url(<?= base_url() ?>pin_marker/<?= $img ?>);background-repeat:no-repeat;background-position:center center;">
												<svg xmlns="http://www.w3.org/2000/svg"
													class="icon icon-tabler icon-tabler-cloud-rain" width="50" height="50"
													viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
													stroke-linecap="round" stroke-linejoin="round">
													<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
													<path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7">
													</path>
													<path d="M11 13v2m0 3v2m4 -5v2m0 3v2"></path>
												</svg>
											</span>
										</div>
										<div class="col">
											<div class="font-weight-medium">
												<div class="subheader">Akumulasi CH Harian <?= $tglPada ?></div>
											</div>
											<div class="h1 mb-0 me-2 mt-0"><?= $akumStr ?> mm</div>
											<div class="h5 fw-normal mb-0 me-2 mt-0">
												<?= $txtAkum ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-9 col-lg-9 mb-3">
							<div class="card card-sm h-100">
								<div class="card-body d-flex align-items-center">
									<div class="row w-100 gy-2">
										<div class="col-auto d-flex align-items-center justify-content-xl-between">
											<h4 class="mb-0 mt-0">Keterangan Intensitas per Jam</h4>
											<h4 class="mb-0 mt-0">:</h4>
										</div>
										<div class="col-xl-auto mt-2 ">
											<div class="row gx-2 align-items-center">
												<?php foreach ($legend as $it) { ?>
													<div class="col-6 col-sm-auto">
														<div class="row gx-2 mt-2 mt-md-0">
															<div class="col-auto d-flex align-items-center">
																<div class="rounded border border-dark"
																	style="background-color:<?= $it[0] ?>;height:35px;width:35px">
																</div>
															</div>
															<div class="col-auto">
																<h5 class="mb-0"><?= $it[1] ?></h5>
																<h5 class="mb-0 text-muted"><?= $it[2] ?></h5>
															</div>
														</div>
													</div>
												<?php } ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php } elseif ($mode === 'bulan' && $typegraf === 'column') { ?>
					<div class="row row-cards">

						<div class="col-sm-12 col-lg-12 mb-3">
							<div class="card card-sm h-100">
								<div class="card-body d-flex align-items-center">
									<div class="row w-100">
										<div class="col-auto d-flex align-items-center justify-content-xl-between">
											<h4 class="mb-0 mt-0">Keterangan Intensitas per Hari</h4>
											<h4 class="mb-0 mt-0">:</h4>
										</div>
										<div class="col-xl-auto">
											<div class="row gx-2 align-items-center">
												<?php foreach ($legend as $it) { ?>
													<div class="col-6 col-sm-auto">
														<div class="row gx-2 mt-2 mt-md-0">
															<div class="col-auto d-flex align-items-center">
																<div class="rounded border border-dark"
																	style="background-color:<?= $it[0] ?>;height:35px;width:35px">
																</div>
															</div>
															<div class="col-auto">
																<h5 class="mb-0"><?= $it[1] ?></h5>
																<h5 class="mb-0 text-muted"><?= $it[2] ?></h5>
															</div>
														</div>
													</div>
												<?php } ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php } elseif ($mode === 'range' && $typegraf === 'column') { ?>
					<div class="row row-cards">
						<div class="col-sm-6 col-lg-3 mb-lg-3">
							<div class="card card-sm h-100">
								<div class="card-body d-flex align-items-center py-2">
									<div class="row align-items-center">
										<div class="col-auto">
											<span class="form-control w-100 rounded <?= $txtCls ?>"
												style="background-image:url(<?= base_url() ?>pin_marker/<?= $img ?>);background-repeat:no-repeat;background-position:center center;">
												<svg xmlns="http://www.w3.org/2000/svg"
													class="icon icon-tabler icon-tabler-cloud-rain" width="50" height="50"
													viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
													stroke-linecap="round" stroke-linejoin="round">
													<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
													<path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7">
													</path>
													<path d="M11 13v2m0 3v2m4 -5v2m0 3v2"></path>
												</svg>
											</span>
										</div>
										<div class="col">
											<div class="font-weight-medium">
												<div class="subheader">Akumulasi Curah Hujan <br> <?= $tglDari ?> -
													<?= $tglSampai ?>
												</div>
											</div>
											<div class="h1 mb-0 me-2 mt-0"><?= $akumStr ?> mm</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-12 col-lg-9 mb-3">
							<div class="card card-sm h-100">
								<div class="card-body d-flex align-items-center">
									<div class="row w-100">
										<div class="col-auto d-flex align-items-center justify-content-xl-between">
											<h4 class="mb-0 mt-0">Keterangan</h4>
											<h4 class="mb-0 mt-0">:</h4>
										</div>
										<div class="col-xl-auto">
											<div class="row gx-2 align-items-center">
												<?php foreach ($legend as $it) { ?>
													<div class="col-6 col-sm-auto">
														<div class="row gx-2 mt-2 mt-md-0">
															<div class="col-auto d-flex align-items-center">
																<div class="rounded border border-dark"
																	style="background-color:<?= $it[0] ?>;height:35px;width:35px">
																</div>
															</div>
															<div class="col-auto">
																<h5 class="mb-0"><?= $it[1] ?></h5>
																<h5 class="mb-0 text-muted"><?= $it[2] ?></h5>
															</div>
														</div>
													</div>
												<?php } ?>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
				<div class="row row-cards">
					<div class="col-md-12">
						<div class="card">
							<div class="card-body">
								<div id="analisa"></div>
								<div class="w-100 mt-3 card">
									<?php
									$titleTable = ($mode === 'range') ? ' dari ' . $tglDari . ' sampai ' . $tglSampai : ' pada ' . $tglPada;

									?>
									<div class="table-responsive">
										<table class="table mb-0 table-bordered table-sm" id="tbl_exporttable_to_xls">
											<thead>
												<tr>
													<th colspan="4">
														<h5 class="mb-0 fw-bold">
															<?= str_replace('_', ' ', $data_sensor->namaSensor) ?>
															<?= $titleTable ?>
														</h5>
													</th>
												</tr>
												<tr>
													<th class="d-none">
														<h5 class="mb-0 fw-bold"><?= $titleTable ?></h5>
													</th>
												</tr>
												<tr>
													<th>Waktu</th>
													<th><?= str_replace('_', ' ', $data_sensor->namaSensor) ?></th>
													<?php if ($typegraf != 'column') { ?>
														<th>Minimal</th>
														<th>Maksimal</th><?php } ?>
												</tr>
											</thead>
											<tbody>
												<?php foreach ($data_sensor->data_tabel as $dt): ?>
													<tr>
														<td><?= $dt->waktu ?></td>
														<td><?= $dt->dta . ' ' . $satuan ?></td>
														<?php if ($typegraf != 'column') { ?>
															<td><?= $dt->min . ' ' . $satuan ?></td>
															<td><?= $dt->max . ' ' . $satuan ?></td><?php } ?>
													</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								</div>
								<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd"
									aria-labelledby="offcanvasEndLabel">
									<div class="offcanvas-header">
										<h2 class="offcanvas-title" id="offcanvasEndLabel">Informasi Logger</h2>
										<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
											aria-label="Close"></button>
									</div>
									<div class="offcanvas-body">
										<div>
											<table class="table table-sm table-borderless">
												<tbody>
													<tr>
														<td class="fw-bold">ID Logger</td>
														<td class="text-end"><?= $informasi->logger_id ?></td>
													</tr>
													<tr>
														<td class="fw-bold">Seri Logger</td>
														<td class="text-end">
															<?= isset($informasi->seri_logger) ? $informasi->seri_logger : $informasi->seri ?>
														</td>
													</tr>
													<tr>
														<td class="fw-bold">Sensor</td>
														<td class="text-end"><?= $informasi->sensor ?></td>
													</tr>
													<tr>
														<td class="fw-bold">Serial Number</td>
														<td class="text-end"><?= $informasi->serial_number ?></td>
													</tr>
													<tr>
														<td class="fw-bold">Nomor Seluler</td>
														<td class="text-end"><?= $informasi->nosell ?></td>
													</tr>
													<?php if ($this->uri->segment(1) == 'awlr') { ?>
														<tr>
															<td class="fw-bold">Elevasi</td>
															<td class="text-end"><?= $informasi->sensor ?></td>
														</tr><?php } ?>
													<tr>
														<td class="fw-bold">Nama Penjaga</td>
														<td class="text-end">
															<?= $informasi->nama_pic == '' ? '-' : $informasi->nama_pic ?>
														</td>
													</tr>
													<tr>
														<td class="fw-bold">Nomor Penjaga</td>
														<td class="text-end">
															<?= $informasi->no_pic == '' ? '-' : $informasi->no_pic ?>
														</td>
													</tr>
												</tbody>
											</table>
											<?php if ($foto_pos) { ?>
												<h3 class="text-center">Foto Pos</h3>
												<?php foreach ($foto_pos as $vl) { ?><img
														src="https://bbws.beacontelemetry.com/image/foto_pos/<?= $vl['url_foto'] ?>"
														class="img-fluid mb-2 rounded" /><?php }
											} ?>
											<?php if ($idLogger === '10247') { ?>
												<h3 class="text-center mb-1">Titik BM Pos Sungai Bogowonto</h3><img
													src="https://bbwsso.monitoring4system.com/image/SB.jpeg"
													class="img-fluid mb-2 rounded" />
												<h3 class="text-center mt-3 mb-1">Titik BM Pos Carik Barat</h3><img
													src="https://bbwsso.monitoring4system.com/image/CB.jpeg"
													class="img-fluid mb-2 rounded" />
											<?php } ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="skema_perangkat" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Skema Perangkat</h5><button type="button"
					class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body p-3 text-center d-flex justify-content-center">
				<?php if ($idLogger === '10249') { ?><img src="<?= base_url() ?>image/afmr.jpg"
						class="img-fluid w-100" /><?php } else { ?><img src="<?= base_url() ?>image/awlr_bogowonto.png"
						class="img-fluid w-100" /><?php } ?>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Riwayat Operasional dan Perawatan</h5><button
					type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<h4 class="fw-normal">Tanggal Pelaksanaan O & P</h4>
				<div class="accordion" id="accordionExample">
					<?php foreach ($data_op as $k => $dp) { ?>
						<div class="accordion-item">
							<h2 class="accordion-header py-0">
								<button class="accordion-button py-2 border-bottom" type="button" data-bs-toggle="collapse"
									data-bs-target="#collapse<?= $k ?>" aria-expanded="false"
									aria-controls="collapse<?= $k ?>"><?= $dp['tanggal'] ?></button>
							</h2>
							<div id="collapse<?= $k ?>" class="accordion-collapse" data-bs-parent="#accordionExample">
								<div class="accordion-body py-2 text-end">
									<a class="btn btn-success"
										href="https://bbwsso.monitoring4system.com/unduh/laporan_op/<?= $dp['file'] ?>"
										target="_blank">
										<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
											fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
											stroke-linejoin="round"
											class="icon icon-tabler icons-tabler-outline icon-tabler-download">
											<path stroke="none" d="M0 0h24v24H0z" fill="none" />
											<path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
											<path d="M7 11l5 5l5 -5" />
											<path d="M12 4l0 12" />
										</svg>
										Unduh Laporan
									</a>
								</div>
							</div>
						</div>
					<?php } ?>
				</div>
			</div>
			<div class="modal-footer"><button type="button" class="btn btn-secondary"
					data-bs-dismiss="modal">Tutup</button></div>
		</div>
	</div>
</div>
<script src="https://unpkg.com/xlsx@0.15.1/dist/xlsx.full.min.js"></script>
<script>
	// === Custom Month Picker ===
	(function () {
		var input = document.getElementById('monthPickerInput');
		var dropdown = document.getElementById('monthPickerDropdown');
		if (!input || !dropdown) return;
		var grid = document.getElementById('mpGrid');
		var label = document.getElementById('mpYearLabel');
		var hidden = document.getElementById('monthPickerValue');
		var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
		var cur = hidden.value;
		var selYear = cur ? parseInt(cur.split('-')[0]) : new Date().getFullYear();
		var selMonth = cur ? parseInt(cur.split('-')[1]) : (new Date().getMonth() + 1);
		var dispYear = selYear;

		function render() {
			label.textContent = dispYear;
			grid.innerHTML = '';
			for (var i = 0; i < 12; i++) {
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'cp-item';
				btn.textContent = months[i];
				if (dispYear === selYear && (i + 1) === selMonth) btn.classList.add('active');
				btn.dataset.month = i + 1;
				btn.addEventListener('click', function () {
					selYear = dispYear;
					selMonth = parseInt(this.dataset.month);
					hidden.value = selYear + '-' + String(selMonth).padStart(2, '0');
					input.value = hidden.value;
					dropdown.classList.remove('show');
					render();
				});
				grid.appendChild(btn);
			}
		}
		input.addEventListener('click', function (e) {
			e.stopPropagation();
			dropdown.classList.toggle('show');
			// close year picker if open
			var yd = document.getElementById('yearPickerDropdown');
			if (yd) yd.classList.remove('show');
		});
		dropdown.addEventListener('click', function (e) { e.stopPropagation(); });
		document.getElementById('mpPrev').addEventListener('click', function () { dispYear--; render(); });
		document.getElementById('mpNext').addEventListener('click', function () { dispYear++; render(); });
		render();
	})();

	// === Custom Year Picker ===
	(function () {
		var input = document.getElementById('yearPickerInput');
		var dropdown = document.getElementById('yearPickerDropdown');
		if (!input || !dropdown) return;
		var grid = document.getElementById('ypGrid');
		var label = document.getElementById('ypRangeLabel');
		var hidden = document.getElementById('yearPickerValue');
		var selYear = hidden.value ? parseInt(hidden.value) : new Date().getFullYear();
		var startYear = selYear - (selYear % 12);

		function render() {
			var endYear = startYear + 11;
			label.textContent = startYear + ' \u2013 ' + endYear;
			grid.innerHTML = '';
			for (var y = startYear; y <= endYear; y++) {
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'cp-item';
				btn.textContent = y;
				if (y === selYear) btn.classList.add('active');
				btn.dataset.year = y;
				btn.addEventListener('click', function () {
					selYear = parseInt(this.dataset.year);
					hidden.value = selYear;
					input.value = selYear;
					dropdown.classList.remove('show');
					render();
				});
				grid.appendChild(btn);
			}
		}
		input.addEventListener('click', function (e) {
			e.stopPropagation();
			dropdown.classList.toggle('show');
			// close month picker if open
			var md = document.getElementById('monthPickerDropdown');
			if (md) md.classList.remove('show');
		});
		dropdown.addEventListener('click', function (e) { e.stopPropagation(); });
		document.getElementById('ypPrev').addEventListener('click', function () { startYear -= 12; render(); });
		document.getElementById('ypNext').addEventListener('click', function () { startYear += 12; render(); });
		render();
	})();

	// Close all custom pickers on outside click
	document.addEventListener('click', function () {
		var md = document.getElementById('monthPickerDropdown');
		var yd = document.getElementById('yearPickerDropdown');
		if (md) md.classList.remove('show');
		if (yd) yd.classList.remove('show');
	});
	$('.toggle').click(function () { $('#target').toggle('fast') });
	document.addEventListener("DOMContentLoaded", function () { var el; window.TomSelect && (new TomSelect(el = document.getElementById('select-pos')), new TomSelect(el = document.getElementById('select-parameter'))) });
	function ExportToExcel(type, fn, dl) { var elt = document.getElementById('tbl_exporttable_to_xls'); var wb = XLSX.utils.table_to_book(elt, { sheet: "sheet1" }); return dl ? XLSX.write(wb, { bookType: type, bookSST: true, type: 'base64' }) : XLSX.writeFile(wb, fn || ('<?= $namafile ?>.' + (type || 'xlsx'))) }
	function validate_form() { var v = $('#select-pos').val(); if (v != '') { $('#form-pos').submit() } }
	Highcharts.chart('analisa', {
		chart: {
			<?php if ($idLogger == '10249' and $data_sensor->namaSensor == 'Rerata_Elevasi_Muka_Air') { ?>
																									events: {
					load: function () {
						let c = this;

						// Gambar sungai sebagai background plot area
						c.renderer.image(
							'<?php echo base_url("image/gambar_sungai.svg"); ?>',   // URL gambar sungai
							c.plotLeft,             // posisi X mengikuti area plot
							c.plotTop,              // posisi Y mengikuti area plot
							c.plotWidth,            // lebar dibentangkan sesuai plot
							c.plotHeight            // tinggi dibentangkan sesuai plot
						)
							.css({ opacity: .65 })    // transparansi agar chart tetap jelas
							.add();
					}
				},
			<?php } ?>
			zoomType: 'xy',
			borderWidth: 1.5,
			backgroundColor: '#FEFEFE',
			borderRadius: 3,
			borderColor: '#304C81'
		},
		title: { text: "<?= $namasensor . ' ' . $title ?>" },
		subtitle: { text: '<?= $temp_data['nama_lokasi'] ?>' },
		xAxis: [
			{
				<?php if ($idLogger == '10249' and $data_sensor->namaSensor == 'Rerata_Elevasi_Muka_Air') { ?>
																										gridLineWidth: 0,          // Hapus grid vertikal
					minorGridLineWidth: 0,     // Hapus grid minor
					tickLength: 0,             // Opsional: hapus tick kecil
				<?php } ?>
				type: 'datetime',
				dateTimeLabelFormats: {
					millisecond: '%H:%M',
					second: '%H:%M',
					minute: '%H:%M',
					hour: '%H:%M',
					day: '%e. %b %y',
					week: '%e. %b %y',
					month: "%b '%y",
					year: '%Y'
				},
				crosshair: true
			}
		],
		yAxis: [
			{
				<?php if ($idLogger == '10249' and $data_sensor->namaSensor == 'Rerata_Elevasi_Muka_Air') { ?>
																										min: 0,
					max: 8,
					gridLineWidth: 0,          // Hapus grid vertikal
					minorGridLineWidth: 0,     // Hapus grid minor
					tickLength: 0,             // Opsional: hapus tick kecil
					lineWidth: 0,              // Opsional: hapus garis axis
				<?php } ?>
				tickAmount: 5,
				title: {
					text: "<?= $namasensor ?>",
					style: {
						color: Highcharts.getOptions().colors[1]
					}
				},
				labels: {
					format: "{value} <?= $satuan ?>",
					style: {
						color: Highcharts.getOptions().colors[1]
					}
				}
			}
		],
		tooltip: { xDateFormat: '<?= $tooltip ?>', shared: true },
		credits: { enabled: false },
		exporting: {
			buttons: { contextButton: { menuItems: ['printChart', 'separator', 'downloadPNG', 'downloadJPEG', 'downloadXLS'] } },
			showTable: false,
			<?php if ($this->session->userdata('username') == 'serayu_opak') {
				echo 'enabled:false';
			} ?>
		},
		<?php if ($this->session->userdata('leveluser') == 'user') { ?>navigation: { buttonOptions: { enabled: false } }, <?php } ?>
		series: [{
			name: '<?= $namasensor ?>',
			type: '<?= $typegraf ?>',
			data: <?= str_replace('"', '', json_encode($data)) ?>,
			zIndex: 1,
			marker: { fillColor: 'white', lineWidth: 2, lineColor: Highcharts.getOptions().colors[0] },
			tooltip: { valueSuffix: ' <?= $satuan ?>', valueDecimals: 2 }
		}
		<?php if ($typegraf != 'column') { ?>, {
				name: 'Range',
				data: <?= str_replace('"', '', json_encode($range)) ?>,
				type: 'areasplinerange',
				lineWidth: 0,
				linkedTo: ':previous',
				color: Highcharts.getOptions().colors[0],
				fillOpacity: 0.3,
				zIndex: 0,
				marker: { enabled: false },
				tooltip: { valueSuffix: ' <?= $satuan ?>', valueDecimals: 3 }
			}<?php } ?>],
		<?php if ($typegraf == 'column' and $mode != 'tahun') { ?>plotOptions: { column: { zones: <?= json_encode($zones) ?> } }, <?php } ?>
				responsive: { rules: [{ condition: { maxWidth: 500 }, chartOptions: { legend: { layout: 'horizontal', align: 'center', verticalAlign: 'bottom' } } }] }
	});
</script>