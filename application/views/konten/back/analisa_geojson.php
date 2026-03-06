<!DOCTYPE html>
<html>

<head>
	<title>Peta Lokasi - BBWS SO</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
	<link rel="icon" href="<?php echo base_url(); ?>image/logopu 4.png">
	<link href="https://stesy.beacontelemetry.com/assets/code/tabler.min.css" rel="stylesheet" />
	<link href="https://stesy.beacontelemetry.com/assets/code/tabler-flags.min.css" rel="stylesheet" />
	<link href="https://stesy.beacontelemetry.com/assets/code/tabler-payments.min.css" rel="stylesheet" />
	<link href="https://stesy.beacontelemetry.com/assets/code/tabler-vendors.min.css" rel="stylesheet" />
	<link href="https://stesy.beacontelemetry.com/assets/code/demo.min.css" rel="stylesheet" />
	<script src="https://stesy.beacontelemetry.com/assets/code/tom-select.complete.min.js" defer></script>
	<script src="https://stesy.beacontelemetry.com/assets/code/tabler.min.js" defer></script>
	<script src="https://stesy.beacontelemetry.com/assets/code/demo.min.js" defer></script>
	<script src="<?php echo base_url(); ?>code/highcharts.js"></script>
	<script src="<?php echo base_url(); ?>code/highcharts-more.js"></script>
	<script src="<?php echo base_url(); ?>code/modules/series-label.js"></script>
	<script src="<?php echo base_url(); ?>code/modules/exporting.js"></script>
	<script src="<?php echo base_url(); ?>code/modules/export-data.js"></script>
	<script src="<?php echo base_url(); ?>code/js/themes/grid.js"></script>
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
		integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
	<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
		integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
	<script src="https://cdn.jsdelivr.net/gh/hosuaby/Leaflet.SmoothMarkerBouncing@v3.0.3/dist/bundle.js"
		crossorigin="anonymous"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
		integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
		crossorigin="anonymous" referrerpolicy="no-referrer" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/js/all.min.js"
		integrity="sha512-6sSYJqDreZRZGkJ3b+YfdhB3MzmuP9R7X1QZ6g5aIXhRvR1Y/N/P47jmnkENm7YL3oqsmI6AK+V6AD99uWDnIw=="
		crossorigin="anonymous" referrerpolicy="no-referrer"></script>
	<style>
		.accordion {
			border-radius: 5px;
			overflow: hidden;
			border: 1px solid white !important;
		}

		.accordion-item {
			border: 1px solid white !important;
		}

		.leaflet-popup-content {
			width: 350px !important;
		}

		.leaflet-popup-content-wrapper {
			overflow: visible;
		}

		.leaflet-popup-content td {
			padding: 6px 8px !important;
		}

		.leaflet-popup-content a {
			color: #303483;
		}

		.leaflet-control-container .leaflet-top,
		.leaflet-control-container .leaflet-bottom {
			z-index: 400;
		}

		*::-webkit-scrollbar {
			display: none;
			/* Chrome, Safari, and Opera */
		}

		#map {
			height: 100%;
			width: 100%;
		}

		html,
		body {
			height: 100%;
			margin: 0;
			padding: 0;
		}

		.legend-box {
			display: inline-block;
			width: 16px;
			height: 16px;
			margin-right: 6px;
			border-radius: 3px;
		}

		#tes {
			background: linear-gradient(to right, #303483, transparent, #303483);
			justify-content: space-between;
			display: flex;
			align-items: center;
			padding: 0px 20px;
			width: calc(100% - 40px);
			height: 75px;
			box-sizing: border-box;
			position: absolute;
			top: 0;
			left: 20px;
			border-radius: 5px;
			margin-top: 20px
		}

		.layer-control {
			background: #fff;
			padding: 0px 10px;
			border-radius: 10px;
			display: grid;
			gap: 6px;
			user-select: none;
		}

		.layer-control h3 {
			font-size: 14px;
		}

		.layer-control label {
			display: flex;
			align-items: center;
			gap: 8px;
			cursor: pointer
		}

		#left_map {
			top: 0;
			left: 0;
			margin-top: 110px;
			max-height: 80vh;
			overflow-y: scroll;
			scrollbar-width: none;
			overflow-x: hidden;

			position: absolute;
			margin-left: 20px;
			border-radius: 5px;
			background: linear-gradient(to right, #303483, transparent);
		}

		#filter_small {
			top: 90px;
			left: 0;
			position: absolute;
		}

		#right_map {
			right: 0;
			max-height: 70vh;
			position: absolute;
			scrollbar-width: none;
			margin-right: 20px;
			overflow-y: scroll;
			border-radius: 5px;
			background: linear-gradient(to right, transparent, #303483);
			flex-direction: column;
		}

		#filterlayer {
			position: absolute;
			top: 0;
			right: 0;
			border-radius: 5px;
			margin-top: 110px;
			margin-right: 20px;
			min-height: 50px;
			background: linear-gradient(to right, transparent, #303483);
		}

		#pilih_kat {
			font-size: 14px;
			font-weight: bold;
			color: white;
			background-color: #30348180;
			border: 2px solid #FFD61580;
			border-radius: 5px;
			padding: 10px 10px;
		}

		@keyframes pulseBorder {
			0% {
				box-shadow: 0 0 0px rgba(255, 214, 21, 0.8);
				border-color: rgba(255, 214, 21, 0.8);
			}

			50% {
				box-shadow: 0 0 10px rgba(255, 214, 21, 1);
				border-color: rgba(255, 214, 21, 1);
			}

			100% {
				box-shadow: 0 0 0px rgba(255, 214, 21, 0.8);
				border-color: rgba(255, 214, 21, 0.8);
			}
		}

		.border-pulse {
			animation: pulseBorder 2s cubic-bezier(0.4, 0, 0.2, 1);
		}

		/* ═══ Loading Overlay ═══ */
		#loading-overlay {
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			z-index: 99999;
			background: linear-gradient(135deg, #1a1d5e 0%, #303483 40%, #1a1d5e 100%);
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			transition: opacity 0.6s ease, visibility 0.6s ease;
		}

		#loading-overlay.fade-out {
			opacity: 0;
			visibility: hidden;
		}

		#loading-overlay .loader-logo {
			height: 60px;
			margin-bottom: 30px;
			filter: drop-shadow(0 4px 12px rgba(0, 0, 0, .3));
		}

		.spinner-ring {
			width: 56px;
			height: 56px;
			border: 4px solid rgba(255, 255, 255, 0.15);
			border-top-color: #FFD615;
			border-radius: 50%;
			animation: spin 0.9s linear infinite;
			margin-bottom: 24px;
		}

		@keyframes spin {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}

		#loading-status {
			color: rgba(255, 255, 255, .9);
			font: 600 15px/1.4 system-ui, -apple-system, sans-serif;
			text-align: center;
			letter-spacing: 0.3px;
		}

		#loading-progress {
			width: 200px;
			height: 4px;
			background: rgba(255, 255, 255, 0.15);
			border-radius: 4px;
			margin-top: 16px;
			overflow: hidden;
		}

		#loading-progress-bar {
			height: 100%;
			width: 0%;
			background: linear-gradient(90deg, #FFD615, #ff9800);
			border-radius: 4px;
			transition: width 0.4s ease;
		}

		/* Spinner kecil saat toggle layer GeoJSON */
		.layer-loading-spinner {
			display: inline-block;
			width: 14px;
			height: 14px;
			border: 2px solid rgba(0, 0, 0, 0.15);
			border-top-color: #303483;
			border-radius: 50%;
			animation: spin 0.7s linear infinite;
			margin-left: 6px;
			vertical-align: middle;
		}

		#small_inside {
			border-radius: 5px;
			background: linear-gradient(to right, #303483, transparent, #303483);
		}

		#logo_kiri {
			height: 55px
		}

		@media (max-width: 576px) {
			#logo_kiri {
				height: 40px;
			}

			#tes {
				height: 60px;
				padding: 0 10px;
			}

			#loading-overlay .loader-logo {
				height: 45px;
			}

			.spinner-ring {
				width: 44px;
				height: 44px;
			}
		}

		#searchbar {
			z-index: 5;
			background: white;
			padding: 8px 10px;
			border-radius: 8px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, .15);
			display: flex;
			gap: 8px;
			align-items: center;
			width: 100%;
		}

		#addr {
			font: 12px/1.4 system-ui, sans-serif;
			margin: 6px 0 0;
			color: #333;
		}

		/* ═══ Search Suggestions ═══ */
		#search-suggestions {
			list-style: none;
			padding: 0;
			margin: 4px 0 0;
			background: #fff;
			border-radius: 8px;
			box-shadow: 0 4px 16px rgba(0, 0, 0, .15);
			max-height: 260px;
			overflow-y: auto;
			display: none;
		}

		#search-suggestions.show {
			display: block;
		}

		#search-suggestions li {
			padding: 8px 12px;
			cursor: pointer;
			font-size: 13px;
			color: #333;
			border-bottom: 1px solid #f0f0f0;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		#search-suggestions li:last-child {
			border-bottom: none;
		}

		#search-suggestions li:hover,
		#search-suggestions li.active {
			background: #f0f2ff;
		}

		#search-suggestions .sg-icon {
			flex-shrink: 0;
			width: 20px;
			height: 20px;
			color: #303483;
			opacity: .7;
		}

		#search-suggestions .sg-label {
			font-weight: 600;
		}

		#search-suggestions .sg-sub {
			font-size: 11px;
			color: #888;
			margin-left: auto;
			white-space: nowrap;
		}

		@media (min-width: 768px) {}

		@media (min-width: 992px) {
			#logo_kiri {
				height: 45px;
			}
		}

		@media (min-width: 1200px) {
			#logo_kiri {
				height: 45px
			}
		}

		@media (min-width: 1400px) {
			#logo_kiri {
				height: 55px
			}
		}

		#filters {
			font: 14px/1.4 system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
		}

		.cat-row {
			display: flex;
			align-items: center;
			gap: 8px;
			margin-bottom: 6px;
		}

		.subs {
			margin-left: 24px;
		}

		.cb-row {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			margin: 0 10px 8px 0;
		}

		.count {
			opacity: .6;
		}

		.off {
			opacity: .55;
			filter: grayscale(1);
		}
	</style>
</head>

<body>
	<!-- ═══ Loading Overlay ═══ -->
	<div id="loading-overlay">
		<img src="<?= base_url() ?>image/logo_bbwsso_white.svg" class="loader-logo" alt="BBWS SO">
		<div class="spinner-ring"></div>
		<div id="loading-status">Mempersiapkan halaman...</div>
		<div id="loading-progress">
			<div id="loading-progress-bar"></div>
		</div>
	</div>
	<div id="map"></div>
	<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
		<div class="offcanvas-header py-3">
			<h2 class="offcanvas-title" id="offcanvasEndLabel">Cari Lokasi</h2>
			<button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
		</div>
		<div class="offcanvas-body px-2 py-3">
			<div id="searchbar" class="px-3">
				<input id="q" class="form-control" placeholder="Cari nama/alamat… atau -7.8014,110.3649">
				<button id="go" class="btn btn-primary">Search</button>
			</div>
			<ul id="search-suggestions"></ul>
			<div class=" my-2 bg-white px-3 py-2"
				style=";border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.15);width:100%;"><span
					class="mb-0"><strong>Alamat</strong></span>
				<div id="addr" class="" style="font-size:12px;margin-top:6px;">Belum ada alamat yang dipilih</div>
			</div>

			<div id="nearby_panel" class="px-3 w-100 py-2"
				style="border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.15);">
				<div class="mb-3">
					<strong>Logger Terdekat</strong>
				</div>
				<div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
					<label for="radius_km" style="white-space:nowrap;">Radius</label>
					<input id="radius_km" type="number" min="1" value="10" class="form-control">
					<button id="btn_nearby_refresh" class="btn btn-primary">Cari</button>
				</div>
				<ol id="nearby_list" style="margin:0;padding-left:20px;overflow:auto;"></ol>
			</div>
		</div>
	</div>
	<div class="container-fluid">
		<div class="row justify-content-center">
			<div class="col-xl-3 col-xxl-2 pt-0 d-none d-xl-inline-block" id="left_map">
				<div class="ps-3 pe-2 d-flex justify-content-between align-items-center py-2" style="">
					<h3 class="text-white fw-bold mb-0">List Logger</h3>
					<button id="btn_hide" class="btn btn-outline-light btn-sm py-1 px-3 fw-bold"
						style="border:solid 2px white;border-radius:5px">Hide</button>
				</div>
				<div class="row gy-2 justify-content-center mt-0 mb-3" id="list_logger">
					<?php foreach ($data_konten as $key => $vl) { ?>
						<?php if ($vl['logger']) { ?>
							<div class="col-auto">
								<div class="py-2 text-white fw-bold text-center px-3"
									style="border:solid 2px white;font-size:14px;border-radius:5px">DAS <?= $vl['nama_das'] ?>
								</div>
							</div>

							<?php foreach ($vl['logger'] as $k => $v) { ?>

								<div class="col-12 px-3">
									<div class="card text-white" style="background:transparent;border:2px solid white;"
										id="sc_<?= $v['id_logger'] ?>">
										<div class="card-header px-3 py-2 d-flex justify-content-between "
											style="border-bottom:2px solid white;">
											<div class="d-flex align-items-center">
												<div class="me-2"
													style="width:10px;height:10px;border-radius:50%;background-color:<?= $v['color'] ?>;border:1px solid white">
												</div>
												<p class="mb-0 fw-bold"><?= $v['status_logger'] ?></p>
											</div>
											<p class="mb-0"><?= $v['waktu'] ?></p>
										</div>
										<div class="card-body px-3 py-2">
											<div class="d-flex justify-content-between align-items-center">
												<p class="fw-bold mb-0 h4"><?= $v['nama_lokasi'] ?></p>
												<div class="badge badge-outline text-white h-100 h6 mb-0 fw-bold">ID :
													<?= $v['id_logger'] ?>
												</div>
											</div>
											<div class="row justify-content-center mb-2 gy-2 mt-2 ">
												<?php foreach ($v['param'] as $y => $s) { ?>
													<?php if ($s['nama_parameter'] != 'Battery_Logger' and $s['nama_parameter'] != 'Humidity_Logger' and $s['nama_parameter'] != 'Temperature_Logger' and $s['nama_parameter'] != 'Baterai_Logger' and $s['nama_parameter'] != 'Kelembaban_Logger' and $s['nama_parameter'] != 'Temperatur_Logger') { ?>
														<div class="col-6 text-center">
															<h6 class="mb-0 fw-bold h3"><?= $s['nilai'] ?> 					<?= $s['satuan'] ?></h6>
															<p class="mb-0 h5 fw-normal">
																<a class="text-white"
																	href="<?= $s['link'] ?>"><?= str_replace('_', ' ', $s['nama_parameter']) ?></a>
															</p>
														</div>
													<?php } ?>
												<?php } ?>
											</div>

											<?php
											$param_bt = false;
											foreach ($v['param'] as $y => $s) {
												if ($s['parameter_utama'] == '0') {
													$param_bt = true;
												}
											}
											$found = false;

											foreach ($v['param'] as $item) {
												if (isset($item['nama_parameter']) && $item['nama_parameter'] === 'Humidity_Logger' or $item['nama_parameter'] == 'Kelembaban_Logger') {
													$found = true;
													break;
												}
											}
											?>
											<?php if ($param_bt) { ?>
												<div class="rounded py-0 mt-3" style="border:2px solid white">
													<div class="row gx-0 justify-content-center">
														<?php foreach ($v['param'] as $y => $s) { ?>
															<?php if ($s['nama_parameter'] == 'Battery_Logger' or $s['nama_parameter'] == 'Humidity_Logger' or $s['nama_parameter'] == 'Temperature_Logger' or $s['nama_parameter'] == 'Baterai_Logger' or $s['nama_parameter'] == 'Kelembaban_Logger' or $s['nama_parameter'] == 'Temperatur_Logger') { ?>
																<div class="<?= $found ? 'col-4' : 'col-6' ?>">
																	<div class=" d-flex justify-content-center align-items-center w-100 py-1"
																		style="<?= ($s['nama_parameter'] != 'Temperature_Logger' and $s['nama_parameter'] != 'Temperatur_Logger') ? 'border-right:solid 2px white' : '' ?>">
																		<img src="https://api.beacontelemetry.com/image/sensor/<?= $s['nama_parameter'] ?>.svg"
																			style="filter: brightness(0) invert(1);stroke:white;height:16px;color:white"
																			class="text-white me-2 mb-0" />
																		<span class="fw-bold mb-0"><?= $s['nilai'] ?>
																			<?= $s['satuan'] == 'Percent' ? '%' : $s['satuan'] ?></span>
																	</div>
																</div>
															<?php } ?>

														<?php } ?>

													</div>
												</div>
											<?php } ?>
										</div>
									</div>
								</div>
							<?php }
						} ?>
					<?php } ?>
				</div>
			</div>
		</div>
		<div class=" px-3 py-2 d-inline-block d-lg-none " id="filter_small">
			<div class="d-flex align-items-center py-2 px-2 " id="small_inside">
				<button class="btn btn-outline-light w-100 fw-bold py-1 pe-1 me-3" data-bs-toggle="offcanvas"
					href="#offcanvasEnd" style="border:solid 2px white;border-radius:5px"><svg
						xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
						stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
						class="icon icon-tabler icons-tabler-outline icon-tabler-map-search">
						<path stroke="none" d="M0 0h24v24H0z" fill="none" />
						<path d="M11 18l-2 -1l-6 3v-13l6 -3l6 3l6 -3v7.5" />
						<path d="M9 4v13" />
						<path d="M15 7v5" />
						<path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
						<path d="M20.2 20.2l1.8 1.8" />
					</svg></button>
				<button class="btn btn-outline-light w-100 fw-bold py-1 pe-1" data-bs-toggle="modal"
					data-bs-target="#setting_peta" style="border:solid 2px white;border-radius:5px"><svg
						xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
						stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
						class="icon icon-tabler icons-tabler-outline icon-tabler-settings">
						<path stroke="none" d="M0 0h24v24H0z" fill="none" />
						<path
							d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
						<path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
					</svg></button>
				<button class="d-lg-none btn btn-outline-light w-100 fw-bold py-1 mx-3 pe-1" id="sm_list"
					style="border:solid 2px white;border-radius:5px"><svg xmlns="http://www.w3.org/2000/svg" width="24"
						height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
						stroke-linecap="round" stroke-linejoin="round"
						class="icon icon-tabler icons-tabler-outline icon-tabler-layout-list">
						<path stroke="none" d="M0 0h24v24H0z" fill="none" />
						<path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
						<path d="M4 14m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
					</svg></button>
				<button class="d-lg-none btn btn-outline-light w-100 fw-bold py-1  pe-1" id="sm_das"
					style="border:solid 2px white;border-radius:5px"><svg xmlns="http://www.w3.org/2000/svg" width="24"
						height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
						stroke-linecap="round" stroke-linejoin="round"
						class="icon icon-tabler icons-tabler-outline icon-tabler-squares">
						<path stroke="none" d="M0 0h24v24H0z" fill="none" />
						<path d="M8 10a2 2 0 0 1 2 -2h9a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-9a2 2 0 0 1 -2 -2z" />
						<path d="M16 8v-3a2 2 0 0 0 -2 -2h-9a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h3" />
					</svg></button>
			</div>
		</div>
		<div class="col-xl-3 col-xxl-2 d-none d-lg-flex flex-column align-items-center px-2 py-2" id="filterlayer">
			<button class="btn btn-outline-light w-100 fw-bold py-1 mb-2" data-bs-toggle="offcanvas"
				href="#offcanvasEnd" style="border:solid 2px white;border-radius:5px"><svg
					xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
					stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
					class="icon icon-tabler icons-tabler-outline icon-tabler-map-search">
					<path stroke="none" d="M0 0h24v24H0z" fill="none" />
					<path d="M11 18l-2 -1l-6 3v-13l6 -3l6 3l6 -3v7.5" />
					<path d="M9 4v13" />
					<path d="M15 7v5" />
					<path d="M18 18m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
					<path d="M20.2 20.2l1.8 1.8" />
				</svg>Cari Lokasi</button>
			<button class="btn btn-outline-light w-100 fw-bold py-1" data-bs-toggle="modal"
				data-bs-target="#setting_peta" style="border:solid 2px white;border-radius:5px"><svg
					xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
					stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
					class="me-2 icon icon-tabler icons-tabler-outline icon-tabler-settings">
					<path stroke="none" d="M0 0h24v24H0z" fill="none" />
					<path
						d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
					<path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
				</svg>Pengaturan Peta</button>
			<button class="d-lg-none btn btn-outline-light w-100 fw-bold py-1 mt-2" data-bs-toggle="modal"
				data-bs-target="#setting_peta" style="border:solid 2px white;border-radius:5px"><svg
					xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
					stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
					class="me-2 icon icon-tabler icons-tabler-outline icon-tabler-settings">
					<path stroke="none" d="M0 0h24v24H0z" fill="none" />
					<path
						d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
					<path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
				</svg>List DAS</button>
			<button class="d-lg-none btn btn-outline-light w-100 fw-bold py-1 mt-2" data-bs-toggle="modal"
				data-bs-target="#setting_peta" style="border:solid 2px white;border-radius:5px"><svg
					xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
					stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
					class="me-2 icon icon-tabler icons-tabler-outline icon-tabler-settings">
					<path stroke="none" d="M0 0h24v24H0z" fill="none" />
					<path
						d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" />
					<path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" />
				</svg>List Logger</button>
		</div>
		<div class="col-xl-3 col-xxl-2 d-xl-inline-block d-none" id="right_map">

			<div class="px-3 pt-2 pb-2 d-flex justify-content-between align-items-center">
				<h3 class="mb-0 fw-bold text-white">Daftar DAS</h3><button id="btn_hide2"
					class="btn btn-outline-light btn-sm py-1 px-3 fw-bold"
					style="border:solid 2px white;border-radius:5px">Hide</button>
			</div>
			<div class="px-2 pb-2 text-white" style="overflow-y: scroll;scrollbar-width: none;" id="list_das">
				<div class="accordion mt-2" id="accordion-default">
					<?php foreach ($data_konten as $key => $vl) { ?>
						<div class="accordion-item">
							<div class="accordion-header">
								<button class="accordion-button collapsed text-white py-2" type="button"
									data-bs-toggle="collapse" data-bs-target="#collapse-<?= $key ?>" aria-expanded="false">
									<?= $vl['nama_das'] ?>
								</button>
							</div>
							<style>
								li {
									margin-bottom: 5px;
								}

								/* Optional: remove margin from last li */
								li:last-child {
									margin-bottom: 0;
								}
							</style>
							<div id="collapse-<?= $key ?>" class="accordion-collapse collapse text-white"
								data-bs-parent="#accordion-default" style="">
								<div class="accordion-body pt-2 pb-0">
									<?php if ($vl['logger']) { ?>
										<ul class="px-3">
											<?php foreach ($vl['logger'] as $k => $v) { ?>
												<li>
													<h4 class="mb-0"><?= $v['nama_lokasi'] ?></h4>
												</li>
											<?php } ?>
										</ul>
									<?php } else { ?>
										<h5 class="mb-2"> Tidak Ada Logger</h5>
									<?php } ?>
								</div>
							</div>
						</div>
					<?php } ?>

				</div>
			</div>

		</div>
	</div>
	<div style="" id="tes">
		<img src="<?= base_url() ?>image/logo_bbwsso_white.svg" id="logo_kiri" />
		<button class="btn bg-transparent px-2 text-white  d-lg-none " data-bs-toggle="modal"
			data-bs-target="#fullModal">
			<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
				stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
				class="icon icon-tabler icons-tabler-outline icon-tabler-menu-4 px-0 mx-0">
				<path stroke="none" d="M0 0h24v24H0z" fill="none" />
				<path d="M7 6h10" />
				<path d="M4 12h16" />
				<path d="M7 12h13" />
				<path d="M7 18h10" />
			</svg>
		</button>

		<div class="align-items-center d-none d-xl-flex">
			<div class="d-flex flex-column align-items-center">
				<a class="me-3 d-flex align-items-center py-1"
					style="background:transparent;border:none;color:white;font-weight:bold;font-size:16px"
					href="<?= base_url() ?>beranda">
					<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16" class="me-2">
						<path fill="currentColor"
							d="M6.906.664a1.749 1.749 0 0 1 2.187 0l5.25 4.2c.415.332.657.835.657 1.367v7.019A1.75 1.75 0 0 1 13.25 15h-3.5a.75.75 0 0 1-.75-.75V9H7v5.25a.75.75 0 0 1-.75.75h-3.5A1.75 1.75 0 0 1 1 13.25V6.23c0-.531.242-1.034.657-1.366l5.25-4.2Zm1.25 1.171a.25.25 0 0 0-.312 0l-5.25 4.2a.25.25 0 0 0-.094.196v7.019c0 .138.112.25.25.25H5.5V8.25a.75.75 0 0 1 .75-.75h3.5a.75.75 0 0 1 .75.75v5.25h2.75a.25.25 0 0 0 .25-.25V6.23a.25.25 0 0 0-.094-.195Z" />
					</svg>
					Dashboard
				</a>
			</div>
			<div class="d-flex flex-column align-items-center">
				<button class="me-3 d-flex align-items-center py-1"
					style="background:transparent;border:none;color:white;font-weight:bold;font-size:16px">
					<span class="nav-link-icon d-md-none d-lg-inline-block text-white">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
							stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
							class="icon icon-tabler icons-tabler-outline icon-tabler-map">
							<path stroke="none" d="M0 0h24v24H0z" fill="none" />
							<path d="M3 7l6 -3l6 3l6 -3v13l-6 3l-6 -3l-6 3v-13" />
							<path d="M9 4v13" />
							<path d="M15 7v13" />
						</svg>
					</span>
					Peta Lokasi
				</button>
				<div style="border-bottom:2px solid white;width:40px"></div>
			</div>
			<div class="d-flex flex-column align-items-center">
				<a class="me-3 d-flex align-items-center py-1"
					style="background:transparent;border:none;color:white;font-weight:bold;font-size:16px"
					href="<?= base_url() ?>komparasi">
					<span class="nav-link-icon d-md-none d-lg-inline-block text-white">
						<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chart-bar me-2"
							width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
							fill="none" stroke-linecap="round" stroke-linejoin="round">
							<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
							<rect x="3" y="12" width="6" height="8" rx="1"></rect>
							<rect x="9" y="8" width="6" height="12" rx="1"></rect>
							<rect x="15" y="4" width="6" height="16" rx="1"></rect>
							<line x1="4" y1="20" x2="18" y2="20"></line>
						</svg>
					</span>
					Komparasi
				</a>
			</div>
			<div class="d-flex flex-column align-items-center">
				<a class="me-3 d-flex align-items-center py-1"
					style="background:transparent;border:none;color:white;font-weight:bold;font-size:16px"
					href="<?= base_url() ?>monitoring">
					<span class="nav-link-icon d-md-none d-lg-inline-block text-white">
						<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-text"
							width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
							fill="none" stroke-linecap="round" stroke-linejoin="round">
							<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
							<path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
							<path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
							<path d="M9 9l1 0"></path>
							<path d="M9 13l6 0"></path>
							<path d="M9 17l6 0"></path>
						</svg>
					</span>
					Monitoring
				</a>
			</div>
			<div class="d-flex flex-column align-items-center">
				<a class="me-3 d-flex align-items-center py-1"
					style="background:transparent;border:none;color:white;font-weight:bold;font-size:16px"
					href="<?= base_url() ?>riwayat">
					<span class="text-white nav-link-icon d-md-none d-lg-inline-block">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
							stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
							class="icon icon-tabler icons-tabler-outline icon-tabler-tool me-2">
							<path stroke="none" d="M0 0h24v24H0z" fill="none" />
							<path
								d="M7 10h3v-3l-3.5 -3.5a6 6 0 0 1 8 8l6 6a2 2 0 0 1 -3 3l-6 -6a6 6 0 0 1 -8 -8l3.5 3.5" />
						</svg>
					</span>
					Riwayat O & P
				</a>
			</div>
			<div class="dropdown px-0 me-4">
				<button type="button" class="btn dropdown-toggle text-white bg-transparent border-0 fw-bold px-0"
					data-bs-toggle="dropdown">
					<!-- SVG icon from http://tabler-icons.io/i/calendar -->
					<svg xmlns="http://www.w3.org/2000/svg" width="22" class="me-2" height="22" viewBox="0 0 26 26">
						<g fill="none">
							<path
								d="M24 0v24H0V0h24ZM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035c-.01-.004-.019-.001-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427c-.002-.01-.009-.017-.017-.018Zm.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093c.012.004.023 0 .029-.008l.004-.014l-.034-.614c-.003-.012-.01-.02-.02-.022Zm-.715.002a.023.023 0 0 0-.027.006l-.006.014l-.034.614c0 .012.007.02.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01l-.184-.092Z" />
							<path fill="currentColor"
								d="M20 14.5a1.5 1.5 0 0 1 1.5 1.5v4a2.5 2.5 0 0 1-2.5 2.5H5A2.5 2.5 0 0 1 2.5 20v-4a1.5 1.5 0 0 1 3 0v3.5h13V16a1.5 1.5 0 0 1 1.5-1.5Zm-8-13A1.5 1.5 0 0 1 13.5 3v9.036l1.682-1.682a1.5 1.5 0 0 1 2.121 2.12l-4.066 4.067a1.75 1.75 0 0 1-2.474 0l-4.066-4.066a1.5 1.5 0 0 1 2.121-2.121l1.682 1.682V3A1.5 1.5 0 0 1 12 1.5Z" />
						</g>
					</svg>
					<h3 class="mb-0 fw-bold">Unduh</h3>
				</button>
				<div class="dropdown-menu fw-bold border-white">
					<a class="dropdown-item" href="<?= base_url() ?>datapos">
						Unduh Data
					</a>
					<a class="dropdown-item" href="<?= base_url() ?>unduh/bbws_so_1.3.2.apk" target="_blank">
						Android App
					</a>
					<a class="dropdown-item" href="https://apps.apple.com/id/app/bbws-so/id6480156441" target="_blank">
						iOS App
					</a>
				</div>
			</div>
			<a class="me-3 d-flex align-items-center fw-bold"
				style="background:transparent;border:none;color:white;font-size:16px"
				href="<?= base_url() ?>login/logout">
				<svg xmlns="http://www.w3.org/2000/svg" class="me-2" width="22" height="22" viewBox="0 0 24 24">
					<g fill="none">
						<path
							d="M24 0v24H0V0h24ZM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035c-.01-.004-.019-.001-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427c-.002-.01-.009-.017-.017-.018Zm.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093c.012.004.023 0 .029-.008l.004-.014l-.034-.614c-.003-.012-.01-.02-.02-.022Zm-.715.002a.023.023 0 0 0-.027.006l-.006.014l-.034.614c0 .012.007.02.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01l-.184-.092Z" />
						<path fill="currentColor"
							d="M12 2.5a1.5 1.5 0 0 1 0 3H7a.5.5 0 0 0-.5.5v12a.5.5 0 0 0 .5.5h4.5a1.5 1.5 0 0 1 0 3H7A3.5 3.5 0 0 1 3.5 18V6A3.5 3.5 0 0 1 7 2.5Zm6.06 5.61l2.829 2.83a1.5 1.5 0 0 1 0 2.12l-2.828 2.83a1.5 1.5 0 1 1-2.122-2.122l.268-.268H12a1.5 1.5 0 0 1 0-3h4.207l-.268-.268a1.5 1.5 0 1 1 2.122-2.121Z" />
					</g>
				</svg>
				Keluar
			</a>
		</div>
	</div>
	<div class="h-50 w-100 d-lg-none" id="bottom_small"
		style="display:none;border-radius:20px 20px 0px 0px;overflow-y:scroll;background:linear-gradient(to right,#303483,transparent, #303483);">
		<div id="lg_small">
			<div class="px-3 pt-2 pb-2 d-flex justify-content-between align-items-center text-white">
				<h3 class="mb-0 fw-bold">Daftar DAS</h3>
				<button class="btn_hidesmall btn btn-outline-light btn-sm py-1 px-3 fw-bold"
					style="border:solid 2px white;border-radius:5px">Tutup</button>
			</div>
			<div class="row gy-2 justify-content-center mt-0 mb-3" id="list_logger">
				<?php foreach ($data_konten as $key => $vl) { ?>
					<?php if ($vl['logger']) { ?>
						<div class="col-auto">
							<div class="py-2 fw-bold text-center text-white px-3"
								style="border:solid 2px white;font-size:14px;border-radius:5px">DAS <?= $vl['nama_das'] ?></div>
						</div>

						<?php foreach ($vl['logger'] as $k => $v) { ?>

							<div class="col-12 px-3">
								<div class="card text-white" style="background:transparent;border:2px solid white;">
									<div class="card-header px-3 py-2 d-flex justify-content-between "
										style="border-bottom:2px solid white;">
										<div class="d-flex align-items-center">
											<div class="me-2"
												style="width:10px;height:10px;border-radius:50%;background-color:<?= $v['color'] ?>;border:1px solid white">
											</div>
											<p class="mb-0 fw-bold"><?= $v['status_logger'] ?></p>
										</div>
										<p class="mb-0"><?= $v['waktu'] ?></p>
									</div>

									<div class="card-body px-3 py-2 text-white">
										<div class="d-flex justify-content-between align-items-center">
											<p class="fw-bold mb-0 h4"><?= $v['nama_lokasi'] ?></p>
											<div class="badge badge-outline h-100 h6 mb-0 fw-bold">ID : <?= $v['id_logger'] ?></div>
										</div>
										<div class="row justify-content-center mb-2 gy-2 mt-2 ">
											<?php foreach ($v['param'] as $y => $s) { ?>
												<?php if ($s['nama_parameter'] != 'Battery_Logger' and $s['nama_parameter'] != 'Humidity_Logger' and $s['nama_parameter'] != 'Temperature_Logger' and $s['nama_parameter'] != 'Baterai_Logger' and $s['nama_parameter'] != 'Kelembaban_Logger' and $s['nama_parameter'] != 'Temperatur_Logger') { ?>
													<div class="col-6 text-center">
														<h6 class="mb-0 fw-bold h3"><?= $s['nilai'] ?> 					<?= $s['satuan'] ?></h6>
														<p class="mb-0 h5 fw-normal">
															<a class="text-white"
																href="<?= $s['link'] ?>"><?= str_replace('_', ' ', $s['nama_parameter']) ?></a>
														</p>
													</div>
												<?php } ?>
											<?php } ?>
										</div>

										<?php
										$param_bt = false;
										foreach ($v['param'] as $y => $s) {
											if ($s['parameter_utama'] == '0') {
												$param_bt = true;
											}
										}
										$found = false;

										foreach ($v['param'] as $item) {
											if (isset($item['nama_parameter']) && $item['nama_parameter'] === 'Humidity_Logger' or $item['nama_parameter'] == 'Kelembaban_Logger') {
												$found = true;
												break;
											}
										}
										?>
										<?php if ($param_bt) { ?>
											<div class="rounded py-0 mt-3" style="border:2px solid white">
												<div class="row gx-0 justify-content-center">
													<?php foreach ($v['param'] as $y => $s) { ?>
														<?php if ($s['nama_parameter'] == 'Battery_Logger' or $s['nama_parameter'] == 'Humidity_Logger' or $s['nama_parameter'] == 'Temperature_Logger' or $s['nama_parameter'] == 'Baterai_Logger' or $s['nama_parameter'] == 'Kelembaban_Logger' or $s['nama_parameter'] == 'Temperatur_Logger') { ?>
															<div class="<?= $found ? 'col-4' : 'col-6' ?>">
																<div class=" d-flex justify-content-center align-items-center w-100 py-1"
																	style="<?= ($s['nama_parameter'] != 'Temperature_Logger' and $s['nama_parameter'] != 'Temperatur_Logger') ? 'border-right:solid 2px white' : '' ?>">
																	<img src="https://api.beacontelemetry.com/image/sensor/<?= $s['nama_parameter'] ?>.svg"
																		style="filter: brightness(0) invert(1);stroke:white;height:16px;color:white"
																		class="text-white me-2 mb-0" />
																	<span class="fw-bold mb-0"><?= $s['nilai'] ?>
																		<?= $s['satuan'] == 'Percent' ? '%' : $s['satuan'] ?></span>
																</div>
															</div>
														<?php } ?>

													<?php } ?>

												</div>
											</div>
										<?php } ?>
									</div>
								</div>
							</div>
						<?php }
					} ?>
				<?php } ?>
			</div>
		</div>
		<div id="das_small">
			<div class="px-3 pt-2 pb-2 d-flex justify-content-between align-items-center">
				<h3 class="mb-0 fw-bold text-white">Daftar DAS</h3>
				<button class="btn_hidesmall btn btn-outline-light btn-sm py-1 px-3 fw-bold"
					style="border:solid 2px white;border-radius:5px">Tutup</button>
			</div>
			<div class="px-2 pb-2 text-white" style="overflow-y: scroll;scrollbar-width: none;" id="list_das">
				<div class="accordion mt-2" id="accordion-default">
					<?php foreach ($data_konten as $key => $vl) { ?>
						<div class="accordion-item">
							<div class="accordion-header">
								<button class="accordion-button collapsed text-white py-2" type="button"
									data-bs-toggle="collapse" data-bs-target="#collapse-<?= $key ?>" aria-expanded="false">
									<?= $vl['nama_das'] ?>
								</button>
							</div>
							<style>
								li {
									margin-bottom: 5px;
								}

								/* Optional: remove margin from last li */
								li:last-child {
									margin-bottom: 0;
								}
							</style>
							<div id="collapse-<?= $key ?>" class="accordion-collapse collapse text-white"
								data-bs-parent="#accordion-default" style="">
								<div class="accordion-body pt-2 pb-0">
									<?php if ($vl['logger']) { ?>
										<ul class="px-3">
											<?php foreach ($vl['logger'] as $k => $v) { ?>
												<li>
													<h4 class="mb-0"><?= $v['nama_lokasi'] ?></h4>
												</li>
											<?php } ?>
										</ul>
									<?php } else { ?>
										<h5 class="mb-2"> Tidak Ada Logger</h5>
									<?php } ?>
								</div>
							</div>
						</div>
					<?php } ?>

				</div>
			</div>
		</div>

	</div>
	<div class="modal fade" id="setting_peta" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg">
			<div class="modal-content">
				<div class="modal-header py-2">
					<h5 class="modal-title">Pengaturan Peta</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-3 py-2">
					<div class="row gy-2">
						<div class="col-12 col-lg">
							<div class="card px-0">
								<div class="card-header py-1 fw-bold bg-light">
									Filter Peta
								</div>
								<div class="card-body pb-0 pt-0 px-2">
									<div id="filters" style="padding:8px; max-width:420px;">
										<label style="font-weight:600; display:block; margin-bottom:6px;">
											<input type="checkbox" id="filter-all" checked>
											Select All
										</label>
										<div id="filters-body"></div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-12 col-lg-auto">
							<div class="card px-0">
								<div class="card-header py-1 fw-bold bg-light">
									Layer Peta
								</div>
								<div class="card-body py-2 px-2">
									<div class="layer-control">
										<label><input type="checkbox" id="layer-das">Daerah Aliran Sungai</label>
										<label><input type="checkbox" id="layer-sungai1">Sungai Orde 1</label>
										<label><input type="checkbox" id="layer-sungai2">Sungai Orde 2</label>
										<label><input type="checkbox" id="layer-sungai3">Sungai Orde 3</label>
									</div>
								</div>
							</div>
							<div class="card px-0 mt-2">
								<div class="card-header py-1 fw-bold bg-light">
									Jenis Peta
								</div>
								<div class="card-body py-2 px-2">
									<div class="layer-control">
										<label><input type="radio" name="mapType" value="hybrid" checked>Hybrid</label>
										<label><input type="radio" name="mapType" value="roadmap">Normal</label>
										<label><input type="radio" name="mapType" value="satellite">Satellite</label>
										<label><input type="radio" name="mapType" value="terrain">Terrain</label>

									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
				<div class="modal-footer py-1">
					<button type="button" class="btn btn-secondary btm-sm px-2" data-bs-dismiss="modal">Tutup</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="fullModal" tabindex="-1" aria-labelledby="fullModalLabel" aria-hidden="true"
		data-bs-backdrop="static" data-bs-keyboard="false">
		<div class="modal-dialog modal-fullscreen">
			<div class="modal-content">

				<div class="modal-body" style="position:relative">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"
						style="position:absolute;right:10px; top:10px"></button>

					<a class="d-flex align-items-center py-1 text-secondary"
						style="background:transparent;border:none;font-weight:bold;font-size:16px"
						href="<?= base_url() ?>beranda">
						<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 16 16" class="me-2">
							<path fill="currentColor"
								d="M6.906.664a1.749 1.749 0 0 1 2.187 0l5.25 4.2c.415.332.657.835.657 1.367v7.019A1.75 1.75 0 0 1 13.25 15h-3.5a.75.75 0 0 1-.75-.75V9H7v5.25a.75.75 0 0 1-.75.75h-3.5A1.75 1.75 0 0 1 1 13.25V6.23c0-.531.242-1.034.657-1.366l5.25-4.2Zm1.25 1.171a.25.25 0 0 0-.312 0l-5.25 4.2a.25.25 0 0 0-.094.196v7.019c0 .138.112.25.25.25H5.5V8.25a.75.75 0 0 1 .75-.75h3.5a.75.75 0 0 1 .75.75v5.25h2.75a.25.25 0 0 0 .25-.25V6.23a.25.25 0 0 0-.094-.195Z" />
						</svg>
						Dashboard
					</a>
					<button class="d-flex align-items-center py-1 px-0 mt-3"
						style="background:transparent;border:none;font-weight:bold;font-size:16px">
						<span class="nav-link-icon d-md-none d-lg-inline-block text-black">
							<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
								fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
								stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-map">
								<path stroke="none" d="M0 0h24v24H0z" fill="none" />
								<path d="M3 7l6 -3l6 3l6 -3v13l-6 3l-6 -3l-6 3v-13" />
								<path d="M9 4v13" />
								<path d="M15 7v13" />
							</svg>
						</span>
						Peta Lokasi
					</button>
					<div style="border-bottom:2px solid black;width:120px"></div>
					<a class="d-flex align-items-center py-1 mt-3 text-secondary"
						style="background:transparent;border:none;font-weight:bold;font-size:16px"
						href="<?= base_url() ?>komparasi">
						<span class="nav-link-icon d-md-none d-lg-inline-block">
							<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chart-bar me-2"
								width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
								fill="none" stroke-linecap="round" stroke-linejoin="round">
								<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
								<rect x="3" y="12" width="6" height="8" rx="1"></rect>
								<rect x="9" y="8" width="6" height="12" rx="1"></rect>
								<rect x="15" y="4" width="6" height="16" rx="1"></rect>
								<line x1="4" y1="20" x2="18" y2="20"></line>
							</svg>
						</span>
						Komparasi
					</a>
					<a class="d-flex align-items-center py-1 mt-3 text-secondary"
						style="background:transparent;border:none;font-weight:bold;font-size:16px"
						href="<?= base_url() ?>monitoring">
						<span class="nav-link-icon d-md-none d-lg-inline-block ">
							<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-text"
								width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
								fill="none" stroke-linecap="round" stroke-linejoin="round">
								<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
								<path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
								<path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
								<path d="M9 9l1 0"></path>
								<path d="M9 13l6 0"></path>
								<path d="M9 17l6 0"></path>
							</svg>
						</span>
						Monitoring
					</a>
					<a class="d-flex align-items-center py-1 mt-3 text-secondary"
						style="background:transparent;border:none;font-weight:bold;font-size:16px"
						href="<?= base_url() ?>informasi">
						<span class="nav-link-icon d-md-none d-lg-inline-block">
							<svg xmlns="http://www.w3.org/2000/svg"
								class="icon icon-tabler icon-tabler-info-square-rounded" width="40" height="40"
								viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
								stroke-linecap="round" stroke-linejoin="round">
								<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
								<path d="M12 9h.01"></path>
								<path d="M11 12h1v4h1"></path>
								<path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z"></path>
							</svg>
						</span>
						Informasi
					</a>

					<a class="d-flex align-items-center fw-bold mt-3 text-secondary"
						style="background:transparent;border:none;font-size:16px" href="<?= base_url() ?>login/logout">
						<svg xmlns="http://www.w3.org/2000/svg" class="me-2" width="22" height="22" viewBox="0 0 24 24">
							<g fill="none">
								<path
									d="M24 0v24H0V0h24ZM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035c-.01-.004-.019-.001-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427c-.002-.01-.009-.017-.017-.018Zm.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093c.012.004.023 0 .029-.008l.004-.014l-.034-.614c-.003-.012-.01-.02-.02-.022Zm-.715.002a.023.023 0 0 0-.027.006l-.006.014l-.034.614c0 .012.007.02.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01l-.184-.092Z" />
								<path fill="currentColor"
									d="M12 2.5a1.5 1.5 0 0 1 0 3H7a.5.5 0 0 0-.5.5v12a.5.5 0 0 0 .5.5h4.5a1.5 1.5 0 0 1 0 3H7A3.5 3.5 0 0 1 3.5 18V6A3.5 3.5 0 0 1 7 2.5Zm6.06 5.61l2.829 2.83a1.5 1.5 0 0 1 0 2.12l-2.828 2.83a1.5 1.5 0 1 1-2.122-2.122l.268-.268H12a1.5 1.5 0 0 1 0-3h4.207l-.268-.268a1.5 1.5 0 1 1 2.122-2.121Z" />
							</g>
						</svg>
						Keluar
					</a>
				</div>

			</div>
		</div>
	</div>
	<script src="https://code.jquery.com/jquery-3.6.0.js"></script>
	<script>
		$(document).ready(function () {
			const ID_TO_MARKER = {};
			// ===== UI kecil =====
			$('.btn_hidesmall').on('click', () => $('#bottom_small').toggle());

			$('#sm_list').on('click', function () {
				if ($('#bottom_small').is(':hidden')) $('#bottom_small').toggle();
				$('#lg_small').show();
				$('#das_small').hide();
			});

			$('#sm_das').on('click', function () {
				if ($('#bottom_small').is(':hidden')) $('#bottom_small').toggle();
				$('#das_small').show();
				$('#lg_small').hide();
			});

			$('#btn_hide').on('click', function () {
				$('#list_logger').toggle();
				$(this).text($(this).text() === 'Show' ? 'Hide' : 'Show');
			});

			$('#btn_hide2').on('click', function () {
				$('#list_das').toggle();
				$(this).text($(this).text() === 'Show' ? 'Hide' : 'Show');
			});

			// ====== DATA dari server ======
			const location_new = <?php echo json_encode($marker) ?>;

			// ====== Layer katalog ======
			const LAYERS = {
				das: { nama: "DAS", url: "<?= base_url() ?>image/das_bbws_new2.geojson", layer: null, loaded: false },
				sungai_orde1: { nama: "Sungai Orde 1", url: "<?= base_url() ?>image/Sungai_Orde_1.geojson", layer: null, loaded: false },
				sungai_orde2: { nama: "Sungai Orde 2", url: "<?= base_url() ?>image/Sungai_Orde_2.geojson", layer: null, loaded: false },
				sungai_orde3: { nama: "Sungai Orde 3", url: "<?= base_url() ?>image/Sungai_Orde_3.geojson", layer: null, loaded: false }
			};

			// ====== Icon set ======
			const CAT_ICONS = {
				arr: "/pin_marker/arr.png",
				awlr: "/pin_marker/awlr.png",
				awr: "/pin_marker/arr.png",
				_default: "/pin_marker/arr.png",
			};
			const SUB_ICONS = {
				arr: {
					"tidak hujan": "/pin_marker/kotak-hijau.png",
					"hujan sangat ringan": "/pin_marker/kotak-cyan.png",
					"hujan ringan": "/pin_marker/kotak-nila.png",
					"hujan sedang": "/pin_marker/kotak-kuning.png",
					"hujan lebat": "/pin_marker/kotak-oranye.png",
					"hujan sangat lebat": "/pin_marker/kotak-merah.png",
					"perbaikan": "/pin_marker/kotak-coklat.png",
					"koneksi terputus": "/pin_marker/kotak-hitam.png",
					_default: "/pin_marker/kotak-hijau.png"
				},
				awlr: {
					"koneksi terhubung": "/pin_marker/kotak-hijau.png",
					"koneksi terputus": "/pin_marker/kotak-hitam.png",
					"perbaikan": "/pin_marker/kotak-coklat.png",
					_default: "/pin_marker/kotak-hijau.png"
				},
				awr: {
					"tidak hujan": "/pin_marker/kotak-hijau.png",
					"hujan sangat ringan": "/pin_marker/kotak-cyan.png",
					"hujan ringan": "/pin_marker/kotak-nila.png",
					"hujan sedang": "/pin_marker/kotak-kuning.png",
					"hujan lebat": "/pin_marker/kotak-oranye.png",
					"hujan sangat lebat": "/pin_marker/kotak-merah.png",
					"perbaikan": "/pin_marker/kotak-coklat.png",
					"koneksi terputus": "/pin_marker/kotak-hitam.png",
					_default: "/pin_marker/kotak-hijau.png"
				},
				_default: "/pin_marker/kotak-hijau.png"
			};

			// ====== Master sub agar selalu ada ======
			const ALL_SUBS = {
				arr: ["Tidak Hujan", "Hujan Sangat Ringan", "Hujan Ringan", "Hujan Sedang", "Hujan Lebat", "Hujan Sangat Lebat", "Perbaikan", "Koneksi Terputus"],
				awlr: ["Koneksi Terhubung", "Koneksi Terputus"],
				awr: ["Tidak Hujan", "Hujan Sangat Ringan", "Hujan Ringan", "Hujan Sedang", "Hujan Lebat", "Hujan Sangat Lebat", "Perbaikan", "Koneksi Terputus"]
			};

			// ====== State ======
			const MARKERS = {};
			const CATS = {};
			const LABEL = { cats: {}, subs: {} };

			// ====== Utils ======
			const norm = s => (s ?? "").toString().trim().toLowerCase();
			function setCatLabel(ncat, raw) { LABEL.cats[ncat] = LABEL.cats[ncat] || (raw?.toUpperCase() || ncat.toUpperCase()); }
			function setSubLabel(ncat, nsub, raw) { (LABEL.subs[ncat] ||= {})[nsub] = LABEL.subs[ncat][nsub] || (raw || nsub); }
			function getCatIconUrl(ncat) { return CAT_ICONS[ncat] || CAT_ICONS._default; }
			function getSubIconUrl(ncat, disp) {
				const byCat = SUB_ICONS[ncat] || {};
				const key = (disp ?? "").toString().trim().toLowerCase();
				return byCat[key] || byCat._default || SUB_ICONS._default || CAT_ICONS._default;
			}
			function ensureBucket(rawCat, rawSub) {
				const ncat = norm(rawCat || "unknown");
				const nsub = norm(rawSub || "no_group");
				(MARKERS[ncat] ||= {}); (MARKERS[ncat][nsub] ||= []);
				(CATS[ncat] ||= new Set()).add(nsub);
				setCatLabel(ncat, rawCat); setSubLabel(ncat, nsub, rawSub);
				return { ncat, nsub };
			}
			function preloadFromAllSubs() {
				for (const [rawCat, subs] of Object.entries(ALL_SUBS)) {
					const ncat = norm(rawCat);
					setCatLabel(ncat, rawCat);
					(CATS[ncat] ||= new Set());
					(MARKERS[ncat] ||= {});
					subs.forEach(rawSub => {
						const nsub = norm(rawSub);
						CATS[ncat].add(nsub);
						setSubLabel(ncat, nsub, rawSub);
						(MARKERS[ncat][nsub] ||= []);
					});
				}
			}

			// ====== Globals utk Maps ======
			let map, searchMarker;

			// ====== Loading Manager ======
			const LoadingMgr = {
				statusEl: document.getElementById('loading-status'),
				progressBar: document.getElementById('loading-progress-bar'),
				overlay: document.getElementById('loading-overlay'),
				set(msg, pct) {
					if (this.statusEl) this.statusEl.textContent = msg;
					if (this.progressBar) this.progressBar.style.width = pct + '%';
				},
				done() {
					this.set('Selesai!', 100);
					setTimeout(() => {
						if (this.overlay) this.overlay.classList.add('fade-out');
						setTimeout(() => { if (this.overlay) this.overlay.remove(); }, 700);
					}, 400);
				}
			};

			// ====== Tile Layers ======
			const TILE_LAYERS = {
				hybrid: L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
					maxZoom: 20,
					attribution: '&copy; Google'
				}),
				roadmap: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
					maxZoom: 19,
					attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
				}),
				satellite: L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
					maxZoom: 19,
					attribution: '&copy; Esri, Maxar, Earthstar Geographics'
				}),
				terrain: L.tileLayer('https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png', {
					maxZoom: 17,
					attribution: '&copy; OpenTopoMap'
				})
			};
			let currentTileLayer = null;

			// ====== INIT MAP ======
			LoadingMgr.set('Memuat peta...', 15);
			preloadFromAllSubs();

			map = L.map('map', {
				center: [-7.426223, 110.063884],
				zoom: 9,
				zoomControl: false
			});

			// Add zoom control to bottom-right
			L.control.zoom({ position: 'bottomright' }).addTo(map);

			// Set default tile layer
			currentTileLayer = TILE_LAYERS.hybrid;
			currentTileLayer.addTo(map);

			// ====== Move overlay controls into map container ======
			// (Google Maps did this via map.controls[].push(), Leaflet needs manual reparenting)
			const mapContainer = map.getContainer();

			// Append in correct visual order
			['tes', 'left_map', 'filter_small', 'filterlayer', 'right_map', 'bottom_small'].forEach(id => {
				const el = document.getElementById(id);
				if (el) {
					mapContainer.appendChild(el);
					el.style.zIndex = '1000';
					el.style.pointerEvents = 'auto';
				}
			});

			// Position #right_map dynamically below #filterlayer
			function positionRightMap() {
				const fl = document.getElementById('filterlayer');
				const rm = document.getElementById('right_map');
				if (fl && rm) {
					const flBottom = fl.offsetTop + fl.offsetHeight + 10;
					rm.style.top = flBottom + 'px';
				}
			}
			// Run after a short delay so elements have rendered
			setTimeout(positionRightMap, 100);
			// Also re-position when window resizes
			window.addEventListener('resize', positionRightMap);

			// Prevent scroll/click on overlays from propagating to the map
			['tes', 'left_map', 'right_map', 'filterlayer', 'filter_small', 'bottom_small'].forEach(id => {
				const el = document.getElementById(id);
				if (el) {
					L.DomEvent.disableScrollPropagation(el);
					L.DomEvent.disableClickPropagation(el);
				}
			});

			LoadingMgr.set('Memasang marker BBWS...', 30);

			// Search marker (circle)
			searchMarker = L.circleMarker([0, 0], {
				radius: 8,
				fillColor: '#ff0044',
				fillOpacity: 1,
				color: '#fff',
				weight: 2
			});

			// ====== GeoJSON layers setup ======
			function geojsonStyle(feature, isSungai) {
				return {
					color: feature.properties?.stroke || (isSungai ? '#00a' : '#0066ff'),
					weight: feature.properties?.['stroke-width'] || (isSungai ? 2.5 : 2),
					opacity: feature.properties?.['stroke-opacity'] ?? 0.95,
					fillColor: feature.properties?.fill || '#0088ff',
					fillOpacity: feature.properties?.['fill-opacity'] ?? (!isSungai ? 0.12 : 0)
				};
			}

			// ====== Checkbox handler ======
			document.getElementById("layer-das")?.addEventListener("change", e => toggleLayer("das", e.target.checked));
			document.getElementById("layer-sungai1")?.addEventListener("change", e => toggleLayer("sungai_orde1", e.target.checked));
			document.getElementById("layer-sungai2")?.addEventListener("change", e => toggleLayer("sungai_orde2", e.target.checked));
			document.getElementById("layer-sungai3")?.addEventListener("change", e => toggleLayer("sungai_orde3", e.target.checked));

			// ====== Marker lokasi perangkat ======
			let currentPopup = null;

			function addMarkerToMap(location) {
				const lat = parseFloat(location.latitude);
				const lng = parseFloat(location.longitude);
				const icon = L.icon({
					iconUrl: location.icon,
					iconSize: [30, 42],
					iconAnchor: [15, 42],
					popupAnchor: [0, -42]
				});
				const marker = L.marker([lat, lng], { icon: icon }).addTo(map);

				// Continuously bounce markers that have animation (disconnected / heavy rain)
				if (location.anim) {
					marker.setBouncingOptions({
						bounceHeight: 60,
						bounceSpeed: 54,
						elastic: true,
						shadowAngle: -Math.PI / 4
					}).bounce();
				}

				const { ncat, nsub } = ensureBucket(location.category, location.category_group);
				MARKERS[ncat][nsub].push(marker);

				const str_analisa = '<a class="d-flex align-items-center" href="' + location.link + '" target="_blank">Analisa Data</a>';
				const popupContent =
					'<div class="d-flex justify-content-start mt-2 w-100"><h3 class="pt-1 mb-0"><strong>' + location.nama_lokasi + '</strong></h3></div>'
					+ (location.foto_pos || '')
					+ '<div><table class="table table-bordered mt-3 rounded"><tbody>'
					+ '<tr><td>Status Aset</td><td>' + (location.status_aset || '-') + '</td></tr>'
					+ '<tr><td>Nama DAS</td><td>' + (location.nama_das || '-') + '</td></tr>'
					+ '<tr><td>Koordinat</td><td>' + parseFloat(location.latitude).toFixed(5) + ' , ' + parseFloat(location.longitude).toFixed(5) + '</td></tr>'
					+ '<tr><td>Status Koneksi</td><td>' + (location.koneksi || '-') + '</td></tr>'
					+ '<tr><td>Status SD Card</td><td>' + (location.status_sd || '-') + '</td></tr>'
					+ '<tr><td>Nama Penjaga</td><td>' + (location.nama_pic || '-') + '</td></tr>'
					+ '<tr><td>Nomor Penjaga</td><td>' + (location.no_pic || '-') + '</td></tr>'
					+ '</tbody></table></div>'
					+ '<div class="d-flex justify-content-center fw-bold">'
					+ '<a class="me-3 d-flex align-items-center" href="https://maps.google.com/?q=' + location.latitude + ',' + location.longitude + '" target="_blank">Menuju Lokasi</a> '
					+ str_analisa + '</div>';

				marker.bindPopup(popupContent, { maxWidth: 350, autoPan: true, autoPanPaddingTopLeft: [50, 120], autoPanPaddingBottomRight: [50, 50] });

				marker.on('click', function () {
					// Wait for popup to open, then pan so popup is centered in view
					setTimeout(function () {
						const popup = marker.getPopup();
						if (popup && popup.isOpen()) {
							const px = map.project(popup.getLatLng());
							// Offset upward by half the popup height so popup center is in viewport center
							const popupEl = popup.getElement();
							const popupH = popupEl ? popupEl.offsetHeight : 200;
							px.y -= popupH / 2;
							map.panTo(map.unproject(px), { animate: true });
						}
					}, 100);
					scrollToElement(location['id_logger']);
				});
				ID_TO_MARKER[location.id_logger] = marker;
				return marker;
			}

			if (Array.isArray(location_new)) {
				location_new.forEach(function (location) {
					addMarkerToMap(location);
				});
			}
			LoadingMgr.set('Marker BBWS terpasang, memuat data PSDA...', 55);

			let lastSearchPos = null;

			const DEVICES = (Array.isArray(location_new) ? location_new : []).map(d => ({
				...d,
				_lat: parseFloat(d.latitude),
				_lng: parseFloat(d.longitude)
			}));

			function updateNearby(center) {
				lastSearchPos = center;
				const radiusKm = parseFloat(document.getElementById('radius_km')?.value) || 10;

				const results = DEVICES.map(d => {
					const dist = haversineKm(center.lat, center.lng, d._lat, d._lng);
					return { ...d, distance_km: dist };
				})
					.filter(d => Number.isFinite(d.distance_km) && d.distance_km <= radiusKm)
					.sort((a, b) => a.distance_km - b.distance_km)
					.slice(0, 100);

				renderNearbyList(results, center);
			}

			function renderNearbyList(items, center) {
				const listEl = document.getElementById('nearby_list');
				if (!listEl) return;

				listEl.innerHTML = '';
				if (!items.length) {
					listEl.innerHTML = '<li>Tidak ada logger dalam radius tersebut.</li>';
					return;
				}

				items.forEach((loc, i) => {
					const li = document.createElement('li');
					const jarak = (Math.round(loc.distance_km * 100) / 100).toFixed(2);
					const id = loc.id_logger;
					li.style.marginBottom = '6px';
					li.innerHTML = `
	  <div style="display:flex;flex-direction:column;gap:2px;">
		<div><strong> ${loc.nama_lokasi || '(tanpa nama)'}</strong></div>
		<div style="font-size:12px;color:#444;">
		  ${jarak} km &nbsp;|&nbsp; ${loc.status_aset || '-'}
			</div>
		<div style="display:flex;gap:8px;margin-top:4px;">
		  <button class="btn_goto_marker" data-id="${id}" style="padding:2px 6px;text-decoration:none;border:1px solid #ccc;border-radius:4px;">Lihat</button>
		  <a href="https://maps.google.com/?saddr=${center.lat},${center.lng}&daddr=${loc.latitude},${loc.longitude}"
			 target="_blank" style="padding:2px 6px;text-decoration:none;border:1px solid #ccc;border-radius:4px;">
			Navigasi
			</a>
			</div>
			</div>
	`;
					listEl.appendChild(li);
				});

				listEl.querySelectorAll('.btn_goto_marker').forEach(btn => {
					btn.addEventListener('click', (e) => {
						const id = e.currentTarget.dataset.id;
						const m = ID_TO_MARKER[id];
						if (m) {
							map.setView(m.getLatLng(), 11);
							m.openPopup();
						}
					});
				});
			}
			// ====== Filters UI ======
			function renderFilters() {
				const container = document.getElementById('filters');
				if (!container) return;
				const body = document.createElement('div');
				body.id = 'filters-body';

				const selAll = document.createElement('label');
				selAll.innerHTML = `<input type="checkbox" id="filter-all" checked> Semua Perangkat`;
				selAll.style.display = 'block'; selAll.style.fontWeight = '600'; selAll.style.marginBottom = '8px';

				container.innerHTML = ''; container.appendChild(selAll); container.appendChild(body);

				const allCats = Object.keys(CATS).sort();
				allCats.forEach(ncat => {
					const catLabel = LABEL.cats[ncat] || ncat.toUpperCase();
					const catIcon = getCatIconUrl(ncat);
					const wrap = document.createElement('div');
					wrap.style.marginBottom = '8px';
					wrap.innerHTML = `
		  <div class="cat-row">
			<label class="cat-label" style="display:flex;align-items:center;gap:6px;">
			  <input type="checkbox" class="cb-cat" data-cat="${ncat}" checked>
			  <span style="width:18px;height:18px;display:inline-block;background-image:url('${catIcon}');background-size:contain;background-repeat:no-repeat;background-position:center;"></span>
			  <span>${catLabel}</span>
			</label>
			</div>
		  <div class="subs"></div>`;
					body.appendChild(wrap);

					const subsDiv = wrap.querySelector('.subs');
					[...CATS[ncat]].forEach(nsub => {
						const count = (MARKERS[ncat][nsub] || []).length;
						const disp = (LABEL.subs[ncat] && LABEL.subs[ncat][nsub]) ? LABEL.subs[ncat][nsub] : nsub;
						const subIcon = getSubIconUrl(ncat, disp);
						const lbl = document.createElement('label');
						lbl.className = 'cb-row';
						lbl.innerHTML = `
			<input type="checkbox" class="cb-sub" data-cat="${ncat}" data-sub="${nsub}" checked>
			<span style="width:16px;height:16px;display:inline-block;background-image:url('${subIcon}');background-size:contain;background-repeat:no-repeat;background-position:center;"></span>
			<span>${disp} <span class="count">(${count})</span></span>`;
						subsDiv.appendChild(lbl);
					});
				});
			}
			function updateVisibility() {
				Object.keys(CATS).forEach(ncat => {
					const catOn = document.querySelector(`.cb-cat[data-cat="${ncat}"]`)?.checked;
					[...CATS[ncat]].forEach(nsub => {
						const subOn = document.querySelector(`.cb-sub[data-cat="${ncat}"][data-sub="${nsub}"]`)?.checked;
						(MARKERS[ncat][nsub] || []).forEach(m => {
							if (catOn && subOn) {
								if (!map.hasLayer(m)) m.addTo(map);
							} else {
								if (map.hasLayer(m)) map.removeLayer(m);
							}
						});
					});
				});
			}
			function wireFilterEvents() {
				const selAll = document.getElementById('filter-all');
				selAll?.addEventListener('change', e => {
					const on = e.target.checked;
					document.querySelectorAll('#filters-body input[type="checkbox"]').forEach(cb => cb.checked = on);
					updateVisibility();
				});
				document.querySelectorAll('.cb-cat').forEach(cb => {
					cb.addEventListener('change', () => {
						const ncat = cb.dataset.cat, on = cb.checked;
						document.querySelectorAll(`.cb-sub[data-cat="${ncat}"]`).forEach(x => x.checked = on);
						updateVisibility(); syncGlobalAll();
					});
				});
				document.querySelectorAll('.cb-sub').forEach(cb => cb.addEventListener('change', () => { updateVisibility(); syncGlobalAll(); }));
				function syncGlobalAll() {
					const boxes = [...document.querySelectorAll('#filters-body input[type="checkbox"]')];
					const allOn = boxes.length ? boxes.every(x => x.checked) : true;
					const selAll = document.getElementById('filter-all');
					if (selAll) selAll.checked = allOn;
				}
			}
			renderFilters(); updateVisibility(); wireFilterEvents();

			// ====== Async fetch PSDA markers ======
			LoadingMgr.set('Mengambil data PSDA...', 65);
			$.ajax({
				url: '<?= base_url() ?>analisa/fetch_psda_markers',
				type: 'GET',
				dataType: 'json',
				timeout: 65000,
				success: function (resp) {
					if (resp && resp.status === 'ok') {
						LoadingMgr.set('Memasang marker PSDA...', 80);
						if (Array.isArray(resp.marker)) {
							resp.marker.forEach(function (loc) {
								addMarkerToMap(loc);
								DEVICES.push({
									...loc,
									_lat: parseFloat(loc.latitude),
									_lng: parseFloat(loc.longitude)
								});
							});
						}

						if (resp.data_konten) {
							const dc = resp.data_konten;
							const mapping = { 'Progo': 'PROGO', 'Opak': 'OPAK-OYO', 'Serang': 'SERANG' };
							for (const [dasName, psdaKey] of Object.entries(mapping)) {
								if (dc[psdaKey] && dc[psdaKey].logger) {
									dc[psdaKey].logger.forEach(function (logItem) {
										const card = buildLoggerCard(logItem);
										$('.das-section[data-das="' + dasName + '"] .das-loggers').append(card);
									});
								}
							}
						}

						renderFilters(); updateVisibility(); wireFilterEvents();
					}
					LoadingMgr.done();
				},
				error: function () {
					console.warn('Gagal mengambil data PSDA, melanjutkan tanpa data PSDA');
					LoadingMgr.done();
				}
			});

			function buildLoggerCard(v) {
				let paramHtml = '';
				if (v.param && Array.isArray(v.param)) {
					v.param.forEach(function (s) {
						if (s.nama_parameter !== 'Battery_Logger' && s.nama_parameter !== 'Humidity_Logger' && s.nama_parameter !== 'Temperature_Logger' && s.nama_parameter !== 'Baterai_Logger' && s.nama_parameter !== 'Kelembaban_Logger' && s.nama_parameter !== 'Temperatur_Logger') {
							paramHtml += '<div class="col-6 text-center"><h6 class="mb-0 fw-bold h3">' + (s.nilai || '-') + ' ' + (s.satuan || '') + '</h6><p class="mb-0 h5 fw-normal"><a href="' + (s.link || '#') + '">' + (s.nama_parameter || '').replace(/_/g, ' ') + '</a></p></div>';
						}
					});
				}
				return '<div class="col-12 px-3"><div class="card text-white" style="background:transparent;border:2px solid white;"><div class="card-header px-3 py-2 d-flex justify-content-between" style="border-bottom:2px solid white;"><div class="d-flex align-items-center"><div class="me-2" style="width:10px;height:10px;border-radius:50%;background-color:' + (v.color || '#888') + ';border:1px solid white"></div><p class="mb-0 fw-bold">' + (v.status_logger || '-') + '</p></div><p class="mb-0">' + (v.waktu || '') + '</p></div><div class="card-body px-3 py-2"><div class="d-flex justify-content-between align-items-center"><p class="fw-bold mb-0 h4">' + (v.nama_lokasi || '-') + '</p><div class="badge badge-outline text-white h-100 h6 mb-0 fw-bold">ID : ' + (v.id_logger || '') + '</div></div><div class="row justify-content-center mb-2 gy-2 mt-2">' + paramHtml + '</div></div></div></div>';
			}

			// ====== SEARCH (Nominatim + lat,lng) ======
			const input = document.getElementById("q");
			const goBtn = document.getElementById("go");
			const addrEl = document.getElementById("addr");

			if (input) {
				input.setAttribute('autocomplete', 'off');
				goBtn?.addEventListener("click", () => { closeSuggestions(); handleFreeText(input.value); });
				input.addEventListener("keydown", (e) => {
					if (e.key === "Enter") {
						const active = sugList.querySelector('li.active');
						if (active) { active.click(); }
						else { closeSuggestions(); handleFreeText(input.value); }
						e.preventDefault();
					} else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
						e.preventDefault();
						navSuggestion(e.key === 'ArrowDown' ? 1 : -1);
					} else if (e.key === 'Escape') {
						closeSuggestions();
					}
				});
				input.addEventListener('input', onSearchInput);
				document.addEventListener('click', (e) => {
					if (!e.target.closest('#searchbar') && !e.target.closest('#search-suggestions')) closeSuggestions();
				});
			}

			// ── Suggestion engine ──
			const sugList = document.getElementById('search-suggestions');
			let sugTimer = null;
			const PIN_SVG = '<svg class="sg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>';
			const GLOBE_SVG = '<svg class="sg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>';

			function onSearchInput() {
				const q = input.value.trim().toLowerCase();
				if (q.length < 2) { closeSuggestions(); return; }

				// 1. Local logger matches (instant)
				const localHits = DEVICES
					.filter(d => (d.nama_lokasi || '').toLowerCase().includes(q) || (d.id_logger || '').toString().includes(q) || (d.nama_das || '').toLowerCase().includes(q))
					.slice(0, 5);

				renderSuggestions(localHits, []);

				// 2. Nominatim (debounced 400ms)
				clearTimeout(sugTimer);
				sugTimer = setTimeout(() => {
					fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&countrycodes=id&limit=3`)
						.then(r => r.json())
						.then(places => {
							// Re-check: input might have changed
							const cur = input.value.trim().toLowerCase();
							if (cur.length < 2) return;
							const freshLocal = DEVICES
								.filter(d => (d.nama_lokasi || '').toLowerCase().includes(cur) || (d.id_logger || '').toString().includes(cur) || (d.nama_das || '').toLowerCase().includes(cur))
								.slice(0, 5);
							renderSuggestions(freshLocal, places || []);
						})
						.catch(() => { });
				}, 400);
			}

			function renderSuggestions(locals, places) {
				sugList.innerHTML = '';
				if (!locals.length && !places.length) { closeSuggestions(); return; }

				locals.forEach(d => {
					const li = document.createElement('li');
					li.innerHTML = PIN_SVG + '<span><span class="sg-label">' + (d.nama_lokasi || '-') + '</span></span><span class="sg-sub">' + (d.category || '').toUpperCase() + ' · ' + (d.nama_das || '') + '</span>';
					li.addEventListener('click', () => {
						input.value = d.nama_lokasi || '';
						closeSuggestions();
						const m = ID_TO_MARKER[d.id_logger];
						if (m) {
							map.setView(m.getLatLng(), 14);
							m.openPopup();
						} else {
							goToLocation(d._lat, d._lng, d.nama_lokasi);
						}
					});
					sugList.appendChild(li);
				});

				if (locals.length && places.length) {
					const sep = document.createElement('li');
					sep.style.cssText = 'padding:4px 12px;font-size:11px;color:#999;font-weight:600;cursor:default;background:#fafafa;';
					sep.textContent = 'Alamat / Lokasi';
					sugList.appendChild(sep);
				}

				places.forEach(p => {
					const li = document.createElement('li');
					li.innerHTML = GLOBE_SVG + '<span class="sg-label">' + p.display_name + '</span>';
					li.addEventListener('click', () => {
						input.value = p.display_name;
						closeSuggestions();
						goToLocation(parseFloat(p.lat), parseFloat(p.lon), p.display_name);
					});
					sugList.appendChild(li);
				});

				sugList.classList.add('show');
			}

			function closeSuggestions() { sugList.innerHTML = ''; sugList.classList.remove('show'); }

			function navSuggestion(dir) {
				const items = [...sugList.querySelectorAll('li[style=""], li:not([style])')].filter(li => li.style.cursor !== 'default');
				if (!items.length) return;
				let idx = items.findIndex(li => li.classList.contains('active'));
				items.forEach(li => li.classList.remove('active'));
				idx += dir;
				if (idx < 0) idx = items.length - 1;
				if (idx >= items.length) idx = 0;
				items[idx].classList.add('active');
				items[idx].scrollIntoView({ block: 'nearest' });
			}

			function handleFreeText(text) {
				const coords = parseLatLng(text);
				if (coords) {
					goToLocation(coords.lat, coords.lng);
					// Reverse geocode with Nominatim
					fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${coords.lat}&lon=${coords.lng}&zoom=18&addressdetails=1`)
						.then(r => r.json())
						.then(data => {
							if (data && data.display_name && addrEl) addrEl.textContent = data.display_name;
						})
						.catch(() => { });
				} else if (text && text.trim()) {
					// Forward geocode with Nominatim
					fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(text)}&countrycodes=id&limit=1`)
						.then(r => r.json())
						.then(data => {
							if (data && data.length > 0) {
								goToLocation(parseFloat(data[0].lat), parseFloat(data[0].lon), data[0].display_name);
							}
						})
						.catch(() => { });
				}
			}
			function goToLocation(lat, lng, label) {
				const pos = { lat: Number(lat), lng: Number(lng) };
				map.setView([pos.lat, pos.lng], 11);
				searchMarker.setLatLng([pos.lat, pos.lng]).addTo(map);
				if (addrEl) addrEl.textContent = label || `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
				updateNearby(pos);
			}
			function parseLatLng(str) {
				const m = String(str).trim().match(/^\s*(-?\d+(\.\d+)?)\s*,\s*(-?\d+(\.\d+)?)\s*$/);
				if (!m) return null;
				const lat = parseFloat(m[1]), lng = parseFloat(m[3]);
				if (isNaN(lat) || isNaN(lng)) return null;
				if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return null;
				return { lat, lng };
			}

			document.getElementById('btn_nearby_refresh')?.addEventListener('click', () => {
				if (lastSearchPos) updateNearby(lastSearchPos);
			});

			// ===== MapType switching =====
			$("input[name='mapType']").on("change", function () {
				const type = $(this).val();
				if (currentTileLayer) map.removeLayer(currentTileLayer);
				currentTileLayer = TILE_LAYERS[type] || TILE_LAYERS.hybrid;
				currentTileLayer.addTo(map);
				if (type === "roadmap" || type === "terrain") {
					$("#tes,#left_map,#right_map,#filterlayer").css("background", "#303481");
				} else {
					$("#tes").css("background", "linear-gradient(to right,#303481,transparent, #303481)");
					$("#left_map").css("background", "linear-gradient(to right,#303481,transparent)");
					$("#right_map").css("background", "linear-gradient(to right,transparent, #303481)");
					$("#filterlayer").css("background", "linear-gradient(to right,transparent, #303481)");
				}
			});

			// ===== Layer helpers =====
			function toggleLayer(key, visible) {
				const entry = LAYERS[key]; if (!entry) return;
				const isSungai = key.startsWith("sungai");

				const cbMap = { das: 'layer-das', sungai_orde1: 'layer-sungai1', sungai_orde2: 'layer-sungai2', sungai_orde3: 'layer-sungai3' };
				const cbEl = document.getElementById(cbMap[key]);
				const parentLabel = cbEl ? cbEl.closest('label') : null;

				if (visible) {
					const show = () => {
						entry.layer.addTo(map);
						// Bring sungai to front
						if (isSungai) entry.layer.bringToFront();
						if (key === "das") {
							["sungai_orde1", "sungai_orde2", "sungai_orde3"].forEach(k => {
								if (LAYERS[k].layer && map.hasLayer(LAYERS[k].layer)) LAYERS[k].layer.bringToFront();
							});
						}
						if (parentLabel) {
							const sp = parentLabel.querySelector('.layer-loading-spinner');
							if (sp) sp.remove();
						}
					};
					if (!entry.loaded) {
						if (parentLabel && !parentLabel.querySelector('.layer-loading-spinner')) {
							const spinner = document.createElement('span');
							spinner.className = 'layer-loading-spinner';
							parentLabel.appendChild(spinner);
						}
						fetch(entry.url)
							.then(r => r.json())
							.then(geojsonData => {
								entry.layer = L.geoJSON(geojsonData, {
									style: function (feature) { return geojsonStyle(feature, isSungai); },
									onEachFeature: function (feature, layer) {
										if (key === 'das') {
											const urutDas = feature.properties?.WS || "No name available";
											const namaDas = feature.properties?.NAMA_DAS || "No name available";
											const luasDas = feature.properties?.area || "No name available";
											layer.bindPopup(`
		<div class="d-flex justify-content-start mt-2 w-100"><h3 class="pt-1 mb-0"><strong>DAS ${namaDas}</strong></h3></div>
		<div><table class="table table-bordered mt-3 rounded"><tbody>
		  <tr><td>Wilayah Sungai</td><td>${urutDas}</td></tr>
		  <tr><td>Nama DAS</td><td>${namaDas}</td></tr>
		  <tr><td>Luas (m²)</td><td>${luasDas} m²</td></tr>
			</tbody></table></div>`, { maxWidth: 350 });
										}
									}
								});
								entry.loaded = true;
								show();
							})
							.catch(err => {
								console.warn('Gagal memuat GeoJSON:', key, err);
								if (parentLabel) {
									const sp = parentLabel.querySelector('.layer-loading-spinner');
									if (sp) sp.remove();
								}
							});
					} else { show(); }
				} else {
					if (entry.layer && map.hasLayer(entry.layer)) map.removeLayer(entry.layer);
				}
			}

			// ===== Scroll helper =====
			function scrollToElement(elementId) {
				const $element = $('#sc_' + elementId);
				if ($element.length) {
					$('#left_map').animate({ scrollTop: $element.offset().top - $('#left_map').offset().top + $('#left_map').scrollTop() - 20 }, 600);
					$element.addClass('border-pulse'); setTimeout(() => $element.removeClass('border-pulse'), 2000);
				}
			}
			function haversineKm(lat1, lon1, lat2, lon2) {
				const toRad = (d) => d * Math.PI / 180;
				const R = 6371;
				const dLat = toRad(lat2 - lat1);
				const dLon = toRad(lon2 - lon1);
				const a = Math.sin(dLat / 2) ** 2 +
					Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
					Math.sin(dLon / 2) ** 2;
				return 2 * R * Math.asin(Math.sqrt(a));
			}
		});

	</script>

</body>

</html>