<script src="<?php echo base_url();?>code/highcharts.js"></script>
<script src="<?php echo base_url();?>code/highcharts-more.js"></script>
<script src="<?php echo base_url();?>code/modules/series-label.js"></script>
<script src="<?php echo base_url();?>code/modules/exporting.js"></script>
<script src="<?php echo base_url();?>code/modules/export-data.js"></script>
<script src="<?php echo base_url();?>code/js/themes/grid.js"></script>
<style>
	.btn-info{
		background-color:#303481;
	}
	.btn-info:hover {
		text-decoration: none;
		background-color: #000342;
		border-color: #000342;
	}
</style>
<div class="container-md">
	<div class="page-header d-print-none">
		<h3 class="page-title">
			Komparasi
		</h3>
	</div>
</div>


<div class="page-body">
	<div class="container-xl">
		<div class="row row-cards">
			<div class="col-md-2">
				<div class="row row-cards">
					<?php if($selected){ ?>
					<?php foreach($selected as $k=>$vl){ ?>
					<div class="col-md-12">
						<div class="card">
							<div class="card-body px-3">

								<div class="d-flex justify-content-between align-items-center mb-2">
									<div class="subheader">
										<label class="form-label mb-0">Pilih Logger <?= $vl['nama_kategori'] ?> </label>
									</div>
									<a href="<?= base_url() ?>komparasi/hapus_komparasi/<?= $vl['id_logger']?>" class="text-danger mb-0"><small>Hapus</small></a>
								</div>
								<div class="h3 m-0">
									<form action="<?= base_url() ?>komparasi/ganti_logger" method="POST">
										<input value="<?= $vl['id_logger'] ?>" name="id_lama" class="d-none"/>
										<select type="text" name="id_logger" class="form-select" placeholder="Pilih Pos AWLR" onchange="this.form.submit()" id="select-pos<?= $vl['id_logger'] ?>" value=" ">
											<option disabled selected>Pilih Pos</option>
											<?php foreach($vl['list_kategori'] as $key=>$v) { ?> 
											<option value="<?= $v['id_logger'] ?>" <?= ($v['id_logger'] == $vl['id_logger']) ? 'selected' : '' ?>><?= str_replace('_', ' ', $v['nama_lokasi']) ?></option>
											<?php } ?>
										</select>
									</form>
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
					<?php }?>
					<div class="col-md-12">
						<div class="card">
							<div class="card-body d-xxl-flex justify-content-center py-3">
								<?php if(count($selected) < 5){ ?> 
								<button class="btn btn-primary py-2 px-3 me-2 w-100"  data-bs-target="#tambah_komparasi" data-bs-toggle='modal'><svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-plus me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
									Tambah
								</button>
								<?php } ?>
								
								<?php if($selected and $this->session->userdata('username') != 'serayu_opak'){ ?>
								<form action="<?= base_url() ?>komparasi/export_excel" method="post">
									<input type="text" name='title' value="awd" class="d-none"/>
									<input type="text" name="data" value="<?= htmlspecialchars(json_encode($selected)) ?>" class="d-none">   
									<button type="submit" class="btn btn-success py-2 px-3 mt-2 mt-xxl-0 w-100" ><svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-download"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
										Unduh
									</button>
								</form>
								<?php } ?>
							</div>
						</div>
					</div>

					<div class="col-md-12">
						<div class="card">
							<div class="card-body px-3">
								<div class="subheader"><label class="form-label">Pilih Tanggal</label></div>
								<div class="h3 m-0">
									<?php echo form_open('komparasi/settgl2'); ?>
									<div class="row">
										<div class="col-12 col-md-12 col-sm-12">
											<div class="input-icon">
												<input class="form-control " name="tgl" placeholder="Pilih Tanggal" id="dptanggal" value="<?= $this->session->userdata('pada_komparasi') ?>" autocomplete="off" required />
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
											<div class="form-footer">
												<input type="submit" class="btn btn-info w-100" value="Tampil" />
											</div>
										</div>
									</div>
									<?php echo form_close() ?>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
			<div class="col-md-10">
				<div class="card">
					<div class="card-body">
						<?php if(!$selected){ ?>
						<h4>Pilih Pos Terlebih Dahulu ! </h4>
						<?php }else{?>
						<div id="analisa"></div>
						<?php } ?>


					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="tambah_komparasi" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Pilih Logger</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form action="<?= base_url() ?>komparasi/tambah_logger" method="post">
				<div class="modal-body pt-2 pb-3 d-flex flex-column align-items-center justify-content-center">
					<div class="w-100">
						<label>Pilih Kategori</label>

						<div class="row mt-2 ">
							<?php foreach($kategori as $key=>$val) { ?>
							<div class="col-xl-3">
								<button type="button" class="bg-white border-0 w-100 px-0 mx-0" onclick="pilih_kategori(<?=$val['id_katlogger'] ?>)">
									<div class="card" id="kt_<?= $val['id_katlogger']?>">
										<div class="card-body d-flex flex-column align-items-center py-3">
											<img src="<?= base_url()?>pin_marker/<?= $val['controller']?>.png" height="40"/>
											<h3 class="mb-0 mt-2"><?= $val['nama_kategori']?></h3>
										</div>
									</div>
								</button>
							</div>
							<?php } ?>
						</div>
						<div class="form mt-3">
							<label>Pilih Logger</label>
							<select class="form-select mt-2" id="opsi_logger" name="id_logger" required>
								<option value='' selected disabled>Pilih Kategori Terlebih Dahulu</option>
							</select>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-primary">Pilih</button>
				</div>
			</form>
		</div>
	</div>
</div>

<script>

	document.addEventListener("DOMContentLoaded", function() {
		<?php foreach($selected as $vl) { ?> 
		window.TomSelect && (new TomSelect(el = document.getElementById('select-pos<?= $vl['id_logger'] ?>'), {

		}));
		<?php } ?>
	});
	function pilih_kategori(ktg) {
		var list = <?php echo json_encode($kategori); ?>;
		updateElements(list, ktg);
		console.log(ktg);
		$('#opsi_logger')
					.empty();
		$.ajax({
			url: '<?= base_url() ?>'+'komparasi/get_logger/'+ktg,
			method: 'GET',
			success: function(response) {
				console.log(response);
				$('#opsi_logger')
					.empty()
					.append(response);

			},
			error: function(xhr, status, error) {
				console.error('Error loading HTML:', error);
			}
		});
	}

	function updateElements(arr, val) {
		console.log(val);
		arr.forEach(item => {
			const elementId = '#kt_' + item.id_katlogger;
			if ($(elementId).length) {
				if (item.id_katlogger == val) {
					$(elementId).addClass('bg-dark-lt');
				} else {
					$(elementId).removeClass('bg-dark-lt');
				}
			}
		});
	}
</script>
<script>
	Highcharts.chart('analisa', {
		chart: {
			zoomType: 'xy',
			backgroundColor:'#FEFEFE',
			borderRadius:3,
			borderColor:'#304C81'
		},

		title: {
			text: '<?= $chart_name ?>'
		},

		subtitle: {
			text: 'Tanggal <?= $this->session->userdata('pada_komparasi') ?>'
		},
		xAxis: [{

			type: 'datetime',
			dateTimeLabelFormats: { // don't display the dummy year
				millisecond: '%H:%M',
				second: '%H:%M',
				minute: '%H:%M',
				hour: '%H:%M',
				day: '%e. %b %y',
				week: '%e. %b %y',
				month: '%b \'%y',
				year: '%Y'

			},
			crosshair: true
		}],
		yAxis: [
			<?php if($chart_legend == '3' or $chart_legend == '2'){ ?>
			{ 
				gridLineWidth: 0,
				title: {
					text: 'Tinggi Muka Air',
					style: {
						color: Highcharts.getOptions().colors[0]
					}
				},
				labels: {
					format: '{value} m',
					style: {
						color: Highcharts.getOptions().colors[0]
					}
				}

			},
			<?php } ?>	
			<?php if($chart_legend == '3' or $chart_legend == '1'){ ?> 
			{ 
				gridLineWidth: 0,
				title: {
					text: 'Akumulasi Curah Hujan',
					style: {
						color: '#000000'
					}
				},
				labels: {
					format: '{value} mm',
					style: {
						color: '#000000'
					}
				},
				opposite: true
			},
			<?php } ?>	
		],
		tooltip: {
			shared: true
		},
		credits: {
			enabled: false
		},
		series: [
			<?php

			foreach ($selected as $ky => $vl) {

			?> 
			{
				name: '<?= str_replace('_',' ',$vl['nama_chart']) ?> (<?= $vl['nama_lokasi'] ?>)',
				type: '<?php echo $vl['tipe_graf']; ?>',
				data: <?php echo str_replace('"', '', json_encode($vl['data'])); ?>,
				
				yAxis: <?= $vl['y_axis'] ?> ,
				zIndex: <?= $ky ?>,
				marker: {
				lineWidth: 2,
			},
			tooltip: {
			valueSuffix: ' <?php echo $vl['parameter']['satuan']; ?>',
			valueDecimals: 2,
			}
					 },
					 <?php } ?>

					 ],
		exporting: {
			buttons: {
				contextButton: {
					menuItems: ['printChart', 'separator', 'downloadPNG', 'downloadJPEG', 'downloadXLS']
				}
			},
				showTable: true, 
					<?php if($this->session->userdata('username') == 'serayu_opak'){ 
	echo 'enabled:false';
} ?>
		},
			responsive: {
				rules: [{
					condition: {
						maxWidth: 500
					},
					chartOptions: {
						legend: {
							floating: false,
							layout: 'horizontal',
							align: 'center',
							verticalAlign: 'bottom',
							x: 0,
							y: 0
						},
						yAxis: [
							<?php if($chart_legend == '3' or $chart_legend == '1'){ ?> 
							{
								labels: {
									align: 'right',
									x: 0,
									y: -6
								},
								showLastLabel: false
							},
							<?php } ?>
							<?php if($chart_legend == '3' or $chart_legend == '2'){ ?> 
							{
								labels: {
									align: 'left',
									x: 0,
									y: -6
								},
								showLastLabel: false
							}, 
							{
								visible: false
							}
							<?php } ?>
						]
					}
				}]
			}
	});
</script>