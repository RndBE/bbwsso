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
<?php

if ($data_sensor == null) {
	$namasensor = '';
} else {
	$namasensor = str_replace('_', ' ', $data_sensor->{'namaSensor'});
	$satuan = $data_sensor->{'satuan'};
	$tooltip = $data_sensor->{'tooltip'};
	$data = $data_sensor->{'data'};
	$nosensor = $data_sensor->{'nosensor'};
	$typegraf = $data_sensor->{'tipe_grafik'};
}

if ($data_sensor2 == null) {
} else {
	$namasensor2 = str_replace('_', ' ', $data_sensor2->{'namaSensor'});
	$satuan2 = $data_sensor2->{'satuan'};
	$tooltip2 = $data_sensor2->{'tooltip'};
	$data2 = $data_sensor2->{'data'};
	$nosensor2 = $data_sensor2->{'nosensor'};
	$typegraf2 = $data_sensor2->{'tipe_grafik'};
}

if ($data_sensor3 == null) {
} else {
	$namasensor3 = str_replace('_', ' ', $data_sensor3->{'namaSensor'});
	$satuan3 = $data_sensor3->{'satuan'};
	$tooltip3 = $data_sensor3->{'tooltip'};
	$data3 = $data_sensor3->{'data'};
	$nosensor3 = $data_sensor3->{'nosensor'};
	$typegraf3 = $data_sensor3->{'tipe_grafik'};
}
?>



<div class="container-md">
	<div class="page-header d-print-none">
		<div class="row g-3 align-items-center">
			<div class="col-auto">

			</div>
			<div class="col">
				<h3 class="page-title">
					Komparasi
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
									<div class="subheader mb-0"><label class="form-label mb-0">Pilih Pos AWLR 1</label> </div>
									<?php if ($data_sensor) { ?>
										<a href="<?= base_url() ?>komparasi/hapus_awlr"><small class="text-danger">Hapus</small></a>
									<?php } ?>

								</div>
								<div class="h3 m-0">
									<?php
									echo form_open('komparasi/set_pos2'); ?>
									<select type="text" name="pilihpos" class="form-select" placeholder="Pilih Pos AWLR" onchange="this.form.submit()" id="select-pos" value=" ">
										<option disabled selected>Pilih Pos</option>

										<?php foreach ($pilih_pos as $mnpos) : ?>
											<option value="<?= $mnpos->idLogger ?>" <?= ($mnpos->idLogger == $this->session->userdata('id_logger_komparasi_1')) ? 'selected' : '' ?>><?= str_replace('_', ' ', $mnpos->namaPos) ?></option>
										<?php endforeach ?>
									</select>
									<?php echo form_close() ?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="card">
							<div class="card-body px-3">
								<div class="d-flex justify-content-between align-items-center mb-2">
									<div class="subheader mb-0"><label class="form-label mb-0">Pilih Pos AWLR 2</label> </div>
									<?php if ($data_sensor3) { ?>
										<a href="<?= base_url() ?>komparasi/hapus_awlr2"><small class="text-danger">Hapus</small></a>
									<?php } ?>

								</div>
								<div class="h3 m-0">
									<?php
									echo form_open('komparasi/set_pos4'); ?>
									<select type="text" name="pilihpos" class="form-select" placeholder="Pilih Pos AWLR" onchange="this.form.submit()" id="select-pos2" value=" ">
										<option disabled selected>Pilih Pos</option>

										<?php foreach ($pilih_pos2 as $mnpos) : ?>
											<option value="<?= $mnpos->idLogger ?>" <?= ($mnpos->idLogger == $this->session->userdata('id_logger_komparasi_3')) ? 'selected' : '' ?>><?= str_replace('_', ' ', $mnpos->namaPos) ?></option>
										<?php endforeach ?>
									</select>
									<?php echo form_close() ?>
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-12">
						<div class="card">
							<div class="card-body px-3">
								<div class="d-flex justify-content-between align-items-center mb-2">
									<div class="subheader mb-0"><label class="form-label mb-0">Pilih Pos ARR / AWR</label> </div>
									<?php if ($data_sensor2) { ?>
										<a href="<?= base_url() ?>komparasi/hapus_arr"><small class="text-danger">Hapus</small></a>
									<?php } ?>
								</div>
								<div class="m-0">

									<?php
									echo form_open('komparasi/set_pos3'); ?>
									<select type="text" name="pilihpos2" class="form-select" placeholder="Pilih Pos" onchange="this.form.submit()" id="select-parameter">
										<option disabled selected>Pilih Pos</option>
										<?php foreach ($pilih_pos_arr as $mnpos) : ?>
											<option value="<?= $mnpos->idLogger ?>" <?= ($mnpos->idLogger == $this->session->userdata('id_logger_komparasi_2')) ? 'selected' : '' ?>><?= str_replace('_', ' ', $mnpos->namaPos) ?></option>
										<?php endforeach ?>
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

					<!-- IMPORT DATA --------------------------------     
  <div class="col-md-12">
 <div class="card">
   <div class="card-body">
   <div class="subheader"><label class="form-label">Import Data</label></div>
  <div class="h3 m-0">

 <input type="file" class="form-control" />

  </div>
   </div>
 </div>
  </div>

   End Import Data ---------------------------->
				</div>
			</div>
			<div class="col-md-10">
				<div class="card">
					<div class="card-body">
						<?php
						if (!$data_sensor and !$data_sensor2 and !$data_sensor3) { ?>

							<h4>Pilih Pos Terlebih Dahulu ! </h4>

						<?php } else { ?>

							<div id="analisa"></div>
						<?php } ?>
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
<script type="text/javascript">
	<?php
	if ($data_sensor or $data_sensor2 or $data_sensor3) {
		$title = " pada " . $this->session->userdata('pada_komparasi');
	} else {
		$title = "";
	}
	$index = 0;
	$judul = '';
	if ($data_sensor or $data_sensor3) {
		$judul = 'Rerata Tinggi Muka Air';
		$index = 1;
	}
	$judul2 = '';
	if ($data_sensor2) {
		$judul2 = 'Akumulasi Curah Hujan';
	}

	$dan = '';
	if ($data_sensor or $data_sensor3) {
		if ($data_sensor2) {
			$dan = 'dan';
		}
	}
	$warna = 0;
	if ($data_sensor) {
		$warna = 1;
	}
	?>

	Highcharts.chart('analisa', {
		chart: {
			zoomType: 'xy'
		},

		title: {
			text: "<?= $judul . ' ' . $dan . ' ' . $judul2 ?>" + " <?php echo $title ?>"
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
		yAxis: [<?php if ($data_sensor or $data_sensor3) { ?> { // Secondary yAxis
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

				}, <?php } ?>
			<?php if ($data_sensor2) { ?> { // Tertiary yAxis
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
			<?php if ($data_sensor) { ?> {
					name: 'Rerata TMA (<?= $this->session->userdata('namalokasi_komparasi_1') ?>)',
					type: '<?php echo $typegraf; ?>',
					data: <?php echo str_replace('"', '', json_encode($data)); ?>,
					yAxis: 0,
					zIndex: 2,
					marker: {
						fillColor: 'white',
						lineWidth: 2,
						lineColor: Highcharts.getOptions().colors[0]
					},
					tooltip: {
						valueSuffix: ' <?php echo $satuan; ?>',
						valueDecimals: 2,
					}
				},
			<?php } ?>
			<?php if ($data_sensor3) { ?> {
					name: 'Rerata TMA (<?= $this->session->userdata('namalokasi_komparasi_3') ?>)',
					type: '<?php echo $typegraf3; ?>',
					data: <?php echo str_replace('"', '', json_encode($data3)); ?>,
					yAxis: 0,
					zIndex: 2,
					marker: {
						fillColor: 'white',
						lineWidth: 2,

						lineColor: Highcharts.getOptions().colors[<?= $warna ?>]
					},
					tooltip: {
						valueSuffix: ' <?php echo $satuan3; ?>',
						valueDecimals: 2,
					}
				},
			<?php } ?>
			<?php if ($data_sensor2) { ?> {
					name: 'Akumulasi Curah Hujan (<?= $this->session->userdata('namalokasi_komparasi_2') ?>)',
					type: 'column',
					color: '#000000',
					data: <?php echo str_replace('"', '', json_encode($data2)); ?>,
					yAxis: <?= $index; ?>,
					zIndex: 1,
					marker: {

						enabled: false
					},
					dashStyle: 'shortdot',
					tooltip: {
						valueSuffix: ' <?php echo $satuan2; ?>'
					}
				},
			<?php }  ?>

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
						<?php if ($data_sensor) { ?> {
								labels: {
									align: 'right',
									x: 0,
									y: -6
								},
								showLastLabel: false
							},
						<?php } ?>
						<?php if ($data_sensor2) { ?> {
								labels: {
									align: 'left',
									x: 0,
									y: -6
								},
								showLastLabel: false
							}, {
								visible: false
							}
						<?php } ?>
					]
				}
			}]
		}
	});
	/*
	Highcharts.chart('analisa', {
		chart: {
			zoomType: 'xy'
		},

		title: {
			text: "<?php echo $namasensor ?> <?php echo $title ?>"
		},
		subtitle: {
			text: '<?php echo $this->session->userdata('namalokasi') ?> '
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
		 yAxis: [{ // Primary yAxis
        labels: {
            format: '{value}°C',
            style: {
                color: Highcharts.getOptions().colors[2]
            }
        },
        title: {
            text: '',
            style: {
                color: Highcharts.getOptions().colors[2]
            }
        },
        opposite: true

    }, { // Secondary yAxis
        gridLineWidth: 0,
        title: {
            text: 'Rainfall',
            style: {
                color: Highcharts.getOptions().colors[0]
            }
        },
        labels: {
            format: '{value} mm',
            style: {
                color: Highcharts.getOptions().colors[0]
            }
        }

    }, { // Tertiary yAxis
        gridLineWidth: 0,
        title: {
            text: 'Sea-Level Pressure',
            style: {
                color: Highcharts.getOptions().colors[1]
            }
        },
        labels: {
            format: '{value} mb',
            style: {
                color: Highcharts.getOptions().colors[1]
            }
        },
        opposite: true
    }],
		}],
		tooltip: {
			xDateFormat: '<?php echo $tooltip ?>',
			shared: true
		},
		/*s  legend: {
            layout: 'vertical',
            align: 'left',
            x: 10,
            verticalAlign: 'top',
            y: 30,
            floating: true,
            backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'
        },
		credits: {
			enabled: false
		},
		exporting: {
			buttons: {
				contextButton: {
					menuItems: ['printChart','separator','downloadPNG', 'downloadJPEG','downloadXLS']
				}
			},
			showTable:false
		},
		<?php if ($this->session->userdata('leveluser') == 'user') { ?>
		navigation: {
			buttonOptions: {
				enabled: false
			}
		},
		<?php } ?>
		series: [{
			name: 'Rainfall',
			type: 'column',
			yAxis: 1,
			data: [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
			tooltip: {
				valueSuffix: ' mm'
			}

		},{
			name: 'Rainfall',
			type: 'column',
			yAxis: 2,
			data: [49.9, 71.5, 106.4, 129.2, 144.0, 176.0, 135.6, 148.5, 216.4, 194.1, 95.6, 54.4],
			tooltip: {
				valueSuffix: ' mm'
			}

		}, ],

		responsive: {
			rules: [{
				condition: {
					maxWidth: 500
				},
				chartOptions: {
					legend: {
						layout: 'horizontal',
						align: 'center',
						verticalAlign: 'bottom'
					}
				}
			}]
		}

	});
	*/
</script>