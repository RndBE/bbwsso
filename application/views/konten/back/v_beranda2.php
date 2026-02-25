<style>
	.hide-scrollbar::-webkit-scrollbar {
		display: none;
	}
</style>
<script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>

<div class="container-xl">
	<!-- Page title -->
	<div class="page-header d-print-none">
		<div class="row g-2 align-items-center">
			<div class="col">
				<h2 class="page-title">
					<?php echo ucfirst($this->uri->segment(1))?>
				</h2>
			</div>
		</div>
	</div>
</div>
<div class="page-body" >
	<!-- Konten-->
	<div class="container-xl" >
		<div class="row row-cards hide-scrollbar px-0"  data-masonry='{"percentPosition": true }'>
			<?php foreach ($data_konten as $key => $kt){ ?>
			<div class="col-lg-12">
					<div class="card">
						<div class="card-header">
							<h3 class="card-title"><strong><?= $kt['nama_kategori'] ?></strong> (<?= $kt['kepanjangan'] ?>)</h3>
						</div>
						<div class="card-body">
							<div class="row row-cards">
								<?php foreach($kt['logger'] as $log){?>
								
								<div class="col-12 col-md-6 col-xl-4">
								<div class="card"> 
											<div class="card-status-top "  style="background:<?= $log['color'] ?>"></div>
											<div class="ribbon" style="background:<?= $log['color'] ?>"> <?=  $log['waktu'] ?>
												<div class="card-actions">
													<div class="dropdown">
														<a href="#" class="btn-icon" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
															<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-list" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="white" fill="none" stroke-linecap="round" stroke-linejoin="round">
																<path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
																<line x1="9" y1="6" x2="20" y2="6"></line>
																<line x1="9" y1="12" x2="20" y2="12"></line>
																<line x1="9" y1="18" x2="20" y2="18"></line>
																<line x1="5" y1="6" x2="5" y2="6.01"></line>
																<line x1="5" y1="12" x2="5" y2="12.01"></line>
																<line x1="5" y1="18" x2="5" y2="18.01"></line>
															</svg>

														</a>
														<div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
															<div class="dropdown-item">
																<strong><a href="#" class="text-reset">ID Logger</a></strong>
																<label class="form-check m-0 ms-auto">
																	<?= $log['id_logger'] ?>
																</label>
															</div>
															<div class="dropdown-item">
																<strong><a href="#" class="text-reset">Status Aset</a></strong>
																<label class="form-check m-0 ms-auto">
																	<?= isset($log['status_aset']) ? 'DPUPESDM DIY' :'BBWS SO' ?>
																</label>
															</div>
															<div class="dropdown-item">
																<strong><a href="#" class="text-reset">Status Logger</a></strong>
																<label class="form-check m-0 ms-auto">
																	<?= $log['status_logger'] ?>
																</label>
															</div>

															<div class="dropdown-item">
																<strong><a href="#" class="text-reset">Status SD Card</a></strong>
																<label class="form-check m-0 ms-auto">
																	<?= $log['status_sd'] ?>
																</label>
															</div>

														</div>
													</div>
												</div>
											</div>
											<div class="card-header pb-2 d-flex align-items-center">
												<div class="row">
													<div class="col-12">
														<h3 class="card-title mb-1"><?= $log['nama_lokasi'] ?></h3>
													</div>
												</div>
												
											</div>
											<div class="card-body p-0">
												<div class="table-responsive">
													<table class="table table-vcenter card-table">
														<thead>
															<tr>
																<th>Parameter</th>
																<th>Nilai Ukur</th>
															</tr>
														</thead>
														<tbody>
															<?php foreach($log['param'] as $val) { ?>
															<tr>
																<td>
																	<a href="<?= (isset($val['link'])) ? $val['link'] : '' ?>"><?= str_replace('_',' ',$val['nama_parameter']) ?></a></td>
																<td><?= $val['nilai'] ?> <?= ($val['nilai'] != '-') ? $val['satuan'] : '' ?></td>
															</tr>
															<?php } ?>
															<tr>
															</tr>
														</tbody>
													</table>
												</div>
											</div>
										</div>
									</div>
									<?php } ?>
								</div>
							</div>
						</div>
					</div>

					<?php } ?>

				</div>
			</div>
			<!-- end Konten-->
		</div>