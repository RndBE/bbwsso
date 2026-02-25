<style>

	.table td:first-child {
		position: -webkit-sticky;
		position: sticky;
		left: 0;
		background-color:#f8fafc;

	}

	.btn-info{
		background-color:#303481;
	}

	.btn-info:hover {
		text-decoration: none;
		background-color: #000342;
		border-color: #000342;
	}

	.sticky-col {
		position: sticky;
		left: 0;
		background-color: #f2f2f2;
		z-index: 1;
	}

	.sticky-overlay {
		position: absolute;
		top: 0;
		bottom: 0;
		left: 0;
		right: 100%;
		background-color: #f2f2f2;
		z-index: -1;
	}
</style>

<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/no-data-to-display.js"></script>
<?php
if ($this->input->get('theme') == 'dark') {
	echo '<script src="https://code.highcharts.com/themes/dark-unica.js"></script>';
} else {
	echo '<script src="https://code.highcharts.com/js/themes/grid.js"></script>';
}
?>


<div class="container-md">
	<div class="page-header d-print-none">
		<div class="row g-3 align-items-center">
			<div class="col-auto">

			</div>
			<div class="col">
				<h3 class="page-title">
					Monitoring
				</h3>

			</div>
		</div>
	</div>
</div>


<div class="page-body">
	<div class="container-xl">
		<div class="row row-cards">
			<div class="col-md-2">
				<div class="row row-cards">
					<div class="col-md-12">
						<div class="card">
							<div class="card-body px-3">
								<div class="d-flex justify-content-between align-items-center mb-2">
									<div class="subheader mb-0"><label class="form-label mb-0">Pilih Kategori</label> </div>

								</div>
								<div class="h3 m-0">
									<?php echo form_open('monitoring/set_kategori'); ?>
									<input value="<?= $this->input->get('format') ?> " name="format" class="d-none" />
									<select type="text" name="id_kategori" class="form-select" placeholder="Pilih Kategori" onchange="this.form.submit()" id="select-pos2" value=" ">
										<option disabled selected>Pilih Kategori</option>
										<?php foreach($kategori as $kt) {?>
										<option value="<?=$kt['id_katlogger']?>" <?= ($this->session->userdata('id_kategori_rekap')==$kt['id_katlogger']) ? 'selected' : '' ?>><?= $kt['nama_kategori']?></option>
										<?php } ?>
									</select>
									<?php echo form_close() ?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="card">
							<div class="card-body px-3">
								<div class="subheader"><label class="form-label">Pilih Tanggal</label></div>
								<div class="h3 m-0">
									<?php echo form_open('monitoring/set_tanggal'); ?>
									<input value="<?= $this->input->get('format') ?> " name="format" class="d-none" />
									<div class="row">
										<div class="col-12 col-md-12 col-sm-12">
											<label class="form-label mb-1">Dari</label>
											<div class="input-icon">

												<input class="form-control" name="tgl1" placeholder="Dari" id="dpdari" value="<?= $this->session->userdata('tanggal_rekap1') ?>" autocomplete="off" required/>
												<span class="input-icon-addon">
													<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
														<path stroke="none" d="M0 0h24v24H0z" fill="none" />
														<rect x="4" y="5" width="16" height="16" rx="2" />
														<line x1="16" y1="3" x2="16" y2="7" />
														<line x1="8" y1="3" x2="8" y2="7" />
														<line x1="4" y1="11" x2="20" y2="11" />
														<line x1="11" y1="15" x2="12" y2="15" />
														<line x1="12" y1="15" x2="12" y2="18" />
													</svg>
												</span>
											</div>
											<label class="form-label mt-2 mb-1">Sampai</label>
											<div class="input-icon mt-0">

												<input class="form-control" name="tgl2" placeholder="Dari" id="dpsampai" value="<?= $this->session->userdata('tanggal_rekap2') ?>" autocomplete="off" required/>
												<span class="input-icon-addon">
													<svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
														<path stroke="none" d="M0 0h24v24H0z" fill="none" />
														<rect x="4" y="5" width="16" height="16" rx="2" />
														<line x1="16" y1="3" x2="16" y2="7" />
														<line x1="8" y1="3" x2="8" y2="7" />
														<line x1="4" y1="11" x2="20" y2="11" />
														<line x1="11" y1="15" x2="12" y2="15" />
														<line x1="12" y1="15" x2="12" y2="18" />
													</svg>
												</span>
											</div>
											<div class="form-footer mt-4">
												<input type="submit" class="btn btn-info w-100" value="Tampil" />
											</div>
										</div>
									</div>
									<?php echo form_close() ?>
									<form action="<?= base_url() ?>monitoring/export_excel2" method="post" class="mt-3">
										<?php if($this->session->userdata('id_kategori_rekap') and $this->session->userdata('username') != 'serayu_opak') { ?>
										<input type="text" name="parameter" value="<?= htmlspecialchars(json_encode($new)) ?>"  class="d-none"/>
										<input type="text" name="judul" value="Monitoring <?= $nama_logger ?> <?= ($nama_logger == 'ARR' or $nama_logger == 'AWS') ? '(Curah Hujan)': '(Tinggi Muka Air)' ?> pada <?= $this->session->userdata('tanggal_rekap1') ?> sampai <?= $this->session->userdata('tanggal_rekap2') ?>"  class="d-none"/>
										<input type="text" value="<?= htmlspecialchars(json_encode($data_rekap)) ?>" name="hari" class="d-none"/>
										
										<button type="submit" class="btn btn-success w-100">
											Unduh
										</button>
										<?php } ?>
									</form>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
			<div class="col-md-10">
				<div class="card">
					<style>table {
						width: 100%;
						border-collapse: collapse; /* Merges borders of cells */
						border-spacing: 0;         /* Removes extra space between cells */
						border: 2px solid #dfe3e8;    /* Main border for the table */
						}

						table th, table td {
							border: 1px solid #dfe3e8;
							padding: 8px;              /* Adds padding inside cells */
							margin: 0;                 /* Removes any default cell margin */
						}
					</style>
					<div class="card-body">
						<?php if($this->session->userdata('id_kategori_rekap')) { ?>

						<div class="d-block d-xl-flex justify-content-between align-items-center mb-2">
							<h5 class="card-title mb-0">Monitoring <?= $nama_logger ?> <?= ($nama_logger == 'ARR' or $nama_logger == 'AWS') ? '(Curah Hujan)': '(Tinggi Muka Air)' ?> pada <?= $this->session->userdata('tanggal_rekap1') ?> sampai <?= $this->session->userdata('tanggal_rekap2') ?></h5>
							<div class="d-flex align-items-center mt-2 mt-xl-0">
								<h5 class="card-title mb-0" style="font-size:15px">Format Tanggal : </h5>
								<div class="row rounded border gx-0 ms-3" style="overflow:hidden">
									<div class="col-auto text-center">
										<a href="<?= base_url() ?>monitoring?format=horizontal" class=""><div class="border-end h-100 px-3 d-flex align-items-center py-2 text-dark" >Horizontal</div></a>
									</div>
									<div class="col-auto text-center">
										<a  href="<?= base_url() ?>monitoring?format=vertikal" style="white-space:nowrap"><div class="h-100 px-3 d-flex align-items-center py-2" style="font-size:13px;background:#14a2ba;color:white;font-weight:bold">Vertikal</div></a>
									</div>
								</div>
							</div>
						</div>
						<div class="table-responsive border">
							<table class="table table-vcenter table-bordered">
								<thead>
									<tr>
										<th class="sticky-col text-white" style="background-color:#99BC85;font-size:13px;font-weight:600;text-transform:capitalize">Nama Pos</th>
										<?php foreach($data_rekap as $key=>$lg){?>
										<th class=" text-white text-center" style="background-color:#99BC85;font-size:13px;font-weight:600;text-transform:capitalize; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; "><?= $lg['nama_logger'];?></th>
										<?php } ?>
									</tr>
								</thead>
								<tbody>
									<?php foreach($new as $k => $v) { ?>
									<tr>
										<td  class="sticky-col text-white fw-bold" style="background-color:#99BC85;white-space:nowrap;font-size:12px"><?= $k ?></td>
										<?php foreach($v as $s) { ?>
										<td class="<?= ($s['warna'] == '#fef216' or $s['warna'] == '#D5F0C1') ? 'text-dark':'text-light'?> fw-bold text-center"  style="background-color:<?= $s['warna']?>;font-size:13px;font-weight:600;white-space: nowrap">
											<?php if($s['nilai'] === '-'){ ?>-<?php } else{?>
											<?= number_format($s['nilai'], 2,'.','' ) ?> <?= $nama_logger == 'AWLR' ? 'm' : 'mm'?>
											<?php } ?> </td>
										<?php } ?>
									</tr>
									<?php } ?>
									<?php if($nama_logger == 'ARR' or $nama_logger == 'AWS') { ?>
									<tr>
										<td class=" text-white text-center" style="background-color:#99BC85;font-size:13px;font-weight:600;text-transform:capitalize; word-wrap: break-word; overflow-wrap: break-word; white-space: normal; ">Akumulasi</td>
										<?php foreach($data_rekap as $key=>$lg){?>
										<td class="<?= ($lg['warna'] == '#fef216' or $lg['warna'] == '#D5F0C1') ? 'text-dark':'text-light'?>  text-center" style="background-color:<?= $lg['warna'] ?>;font-size:13px;font-weight:600; white-space: nowrap; "><?= number_format( $lg['total'],2,'.','');?> mm</td>
										<?php } ?>
									</tr>
									<?php } ?>
								</tbody>
							</table>
						</div>

						<?php if($nama_logger == 'ARR' or $nama_logger == 'AWS'){?>
						<div class="rounded  border mt-3">
							<div class="border-bottom text-center fw-bold py-2">
								Intensitas Curah Hujan Per Jam
							</div>
							<div class="row justify-content-center px-3 py-3">
								<div class="col-xl-12">
									<div class="row gx-2 align-items-center justify-content-center">
										<div class="col-6 col-sm-auto">
											<div class="row gx-2 mt-2 mt-md-0">
												<div class="col-auto d-flex align-items-center">
													<div class="rounded border border-dark " style="background-color:#84c450;height:35px;width:35px"></div>
												</div>
												<div class="col-auto">
													<h5 class="mb-0">Tidak Hujan</h5>
													<h5 class="mb-0 text-muted">0 mm</h5>
												</div>

											</div>
										</div>
										<div class="col-6 col-sm-auto">
											<div class="row gx-2 mt-2 mt-md-0">
												<div class="col-auto d-flex align-items-center">
													<div class="rounded border border-dark" style="background-color:#70cddd;height:35px;width:35px"></div>
												</div>
												<div class="col-auto">
													<h5 class="mb-0">Hujan Sangat Ringan</h5>
													<h5 class="mb-0 text-muted">0.1 - 1 mm</h5>
												</div>

											</div>
										</div>
										<div class="col-6 col-sm-auto">
											<div class="row gx-2 mt-2 mt-md-0">

												<div class="col-auto d-flex align-items-center">
													<div class="rounded border border-dark " style="background-color:#35549d;height:35px;width:35px"></div>
												</div>
												<div class="col-auto">
													<h5 class="mb-0">Hujan Ringan</h5>
													<h5 class="mb-0 text-muted">1 - 5 mm</h5>
												</div>
											</div>
										</div>
										<div class="col-6 col-sm-auto">
											<div class="row gx-2  mt-2 mt-md-0">
												<div class="col-auto d-flex align-items-center">
													<div class="rounded border border-dark " style="background-color:#fef216;height:35px;width:35px"></div>
												</div>
												<div class="col-auto">
													<h5 class="mb-0">Hujan Sedang</h5>
													<h5 class="mb-0 text-muted">5 - 10 mm</h5>
												</div>

											</div>
										</div>
										<div class="col-6 col-sm-auto">
											<div class="row  gx-2 mt-2 mt-md-0">
												<div class="col-auto d-flex align-items-center">
													<div class="rounded border border-dark " style="background-color:#f47e2c;height:35px;width:35px"></div>
												</div>
												<div class="col-auto">
													<h5 class="mb-0">Hujan Lebat</h5>
													<h5 class="mb-0 text-muted">10 - 20 mm</h5>
												</div>

											</div>
										</div>
										<div class="col-6 col-sm-auto">
											<div class="row gx-2 mt-2 mt-md-0 mt-xl-2 mt-xxl-0">
												<div class="col-auto d-flex align-items-center">
													<div class="rounded border border-dark" style="background-color:#ed1c24;height:35px;width:35px"></div>
												</div>
												<div class="col-auto">
													<h5 class="mb-0">Hujan Sangat Lebat</h5>
													<h5 class="mb-0 text-muted"> ≥ 20 mm</h5>
												</div>

											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="rounded  border mt-3">
							<div class=" border-bottom text-center fw-bold py-2">
								Intensitas Curah Hujan Per Hari
							</div>
							<div class="row justify-content-center px-3 py-3">
								<div class="col-xl-12">
									<div class="row gx-2 align-items-center justify-content-center">
										<div class="col-6 col-sm-auto">
											<div class="row gx-2 mt-2 mt-md-0">
												<div class="col-auto d-flex align-items-center">
													<div class="rounded border border-dark " style="background-color:#84c450;height:35px;width:35px"></div>
												</div>
												<div class="col-auto">
													<h5 class="mb-0">Tidak Hujan</h5>
													<h5 class="mb-0 text-muted">0 mm</h5>
												</div>

											</div>
										</div>
										<div class="col-6 col-sm-auto">
											<div class="row gx-2 mt-2 mt-md-0">
												<div class="col-auto d-flex align-items-center">
													<div class="rounded border border-dark" style="background-color:#70cddd;height:35px;width:35px"></div>
												</div>
												<div class="col-auto">
													<h5 class="mb-0">Hujan Sangat Ringan</h5>
													<h5 class="mb-0 text-muted">0.1 - 5 mm</h5>
												</div>

											</div>
										</div>
										<div class="col-6 col-sm-auto">
											<div class="row gx-2 mt-2 mt-md-0">

												<div class="col-auto d-flex align-items-center">
													<div class="rounded border border-dark " style="background-color:#35549d;height:35px;width:35px"></div>
												</div>
												<div class="col-auto">
													<h5 class="mb-0">Hujan Ringan</h5>
													<h5 class="mb-0 text-muted">5 - 20 mm</h5>
												</div>
											</div>
										</div>
										<div class="col-6 col-sm-auto">
											<div class="row gx-2  mt-2 mt-md-0">
												<div class="col-auto d-flex align-items-center">
													<div class="rounded border border-dark " style="background-color:#fef216;height:35px;width:35px"></div>
												</div>
												<div class="col-auto">
													<h5 class="mb-0">Hujan Sedang</h5>
													<h5 class="mb-0 text-muted">20 - 50 mm</h5>
												</div>

											</div>
										</div>
										<div class="col-6 col-sm-auto">
											<div class="row  gx-2 mt-2 mt-md-0">
												<div class="col-auto d-flex align-items-center">
													<div class="rounded border border-dark " style="background-color:#f47e2c;height:35px;width:35px"></div>
												</div>
												<div class="col-auto">
													<h5 class="mb-0">Hujan Lebat</h5>
													<h5 class="mb-0 text-muted">50 - 100 mm</h5>
												</div>

											</div>
										</div>
										<div class="col-6 col-sm-auto">
											<div class="row gx-2 mt-2 mt-md-0 mt-xl-2 mt-xxl-0">
												<div class="col-auto d-flex align-items-center">
													<div class="rounded border border-dark" style="background-color:#ed1c24;height:35px;width:35px"></div>
												</div>
												<div class="col-auto">
													<h5 class="mb-0">Hujan Sangat Lebat</h5>
													<h5 class="mb-0 text-muted"> ≥ 100 mm</h5>
												</div>

											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<?php } else{?>
						<div class="row mt-3">
							<div class="col-xl-1 d-flex align-items-center justify-content-lg-between">
								<h4 class="mb-0">Keterangan</h4>
								<h4 class="mb-0">:</h4>
							</div>
							<div class="col-xl-9">
								<div class="row gx-2">
									<div class="col-xl-2 col-6">
										<div class="row h-100 gx-2 pt-2 pt-lg-0">
											<div class="col-3">
												<div class="rounded border border-dark" style="background-color:#fef216;height:35px;"></div>
											</div>
											<div class="col-9 d-flex align-items-center">
												<h5 class="mb-0">Waspada</h5>
											</div>

										</div>
									</div>
									<div class="col-xl-2 col-6">
										<div class="row h-100 gx-2 pt-2 pt-lg-0">
											<div class="col-3">
												<div class="rounded border border-dark" style="background-color:#ed1c24;height:35px;"></div>
											</div>
											<div class="col-9 d-flex align-items-center">
												<h5 class="mb-0">Siaga</h5>
											</div>

										</div>
									</div>
								</div>
							</div>
						</div>
						<?php }?>

						<?php } else{?> 
						<h5 class="fw-semibold">Pilih Kategori Terlebih Dahulu !</h5>
						<?php }?>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<script>
	// @formatter:off
	document.addEventListener("DOMContentLoaded", function() {
		var el;
		window.TomSelect && (new TomSelect(el = document.getElementById('select-pos'), {
			copyClassesToDropdown: false,
			dropdownClass: 'dropdown-menu ts-dropdown',
			optionClass: 'dropdown-item',
			controlInput: '<input>',
			render: {
				item: function(data, escape) {
					if (data.customProperties) {
						return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
					}
					return '<div>' + escape(data.text) + '</div>';
				},
				option: function(data, escape) {
					if (data.customProperties) {
						return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
					}
					return '<div>' + escape(data.text) + '</div>';
				},
			},
		}));
	});
	// @formatter:on
</script>

<script>
	// @formatter:off
	document.addEventListener("DOMContentLoaded", function() {
		var el;
		window.TomSelect && (new TomSelect(el = document.getElementById('select-pos2'), {
			controlInput: null,
		}));
	});
	// @formatter:on
</script>
<script>
	// @formatter:off
	document.addEventListener("DOMContentLoaded", function() {
		var el;
		window.TomSelect && (new TomSelect(el = document.getElementById('select-parameter'), {
			copyClassesToDropdown: false,
			dropdownClass: 'dropdown-menu ts-dropdown',
			optionClass: 'dropdown-item',
			controlInput: '<input>',
			render: {
				item: function(data, escape) {
					if (data.customProperties) {
						return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
					}
					return '<div>' + escape(data.text) + '</div>';
				},
				option: function(data, escape) {
					if (data.customProperties) {
						return '<div><span class="dropdown-item-indicator">' + data.customProperties + '</span>' + escape(data.text) + '</div>';
					}
					return '<div>' + escape(data.text) + '</div>';
				},
			},
		}));
	});
	// @formatter:on
</script>