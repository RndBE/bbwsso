<style>
	.hide-scrollbar::-webkit-scrollbar {
		display: none;
	}

	li {
		overflow: hidden
	}
</style>
<script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js"
	integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous"
	async></script>

<div class="container-xl">
	<!-- Page title -->
	<div class="page-header d-print-none">
		<div class="row g-2 align-items-center">
			<div class="col">
				<h2 class="page-title">
					Riwayat O&P
				</h2>
			</div>
			<div class="col-auto">
				<a href="<?= base_url('riwayat/tambah') ?>" class="btn btn-primary">
					<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus" width="24"
						height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
						stroke-linecap="round" stroke-linejoin="round">
						<path stroke="none" d="M0 0h24v24H0z" fill="none" />
						<path d="M12 5l0 14" />
						<path d="M5 12l14 0" />
					</svg>
					Tambah Riwayat
				</a>
			</div>
		</div>
	</div>
</div>

<?php if ($this->session->flashdata('success')): ?>
	<div class="container-xl">
		<div class="alert alert-success alert-dismissible fade show" role="alert">
			<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-check alert-icon" width="24"
				height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
				stroke-linejoin="round">
				<path stroke="none" d="M0 0h24v24H0z" fill="none" />
				<path d="M5 12l5 5l10 -10" />
			</svg>
			<?= $this->session->flashdata('success') ?>
			<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
		</div>
	</div>
<?php endif ?>

<div class="page-body">
	<!-- Konten-->
	<div class="container-xl">
		<div class="row">
			<div class="col-xl-3 col-xxl-3">
				<div class="card">
					<div class="card-header py-3">
						<h4 class="mb-0">Tanggal Perawatan</h4>
					</div>
					<div class="card-body">
						<div class="accordion" id="accordionExample">
							<?php foreach ($data_op as $k => $v) { ?>
								<div class="accordion-item">
									<h2 class="accordion-header border-bottom">
										<button class="accordion-button py-2" type="button" data-bs-toggle="collapse"
											data-bs-target="#collapse<?= $k ?>" aria-expanded="false"
											aria-controls="collapse<?= $k ?>">
											<?= $v['nama_lokasi'] ?>
										</button>
									</h2>
									<div id="collapse<?= $k ?>" class="accordion-collapse collapse show "
										data-bs-parent="#accordionExample">
										<div class="accordion-body py-2 px-2">
											<ul class="list-group">
												<?php foreach ($v['riwayat'] as $key => $vl) { ?>

													<li class="list-group-item py-0 px-0" aria-current="true">
														<form method='post'
															action="<?= base_url() ?>riwayat/pilih_riwayat?id_riwayat=<?= $vl['id_riwayat'] ?>">
															<button type="submit"
																class="w-100 py-2 border-0 <?= ($this->session->userdata('id_riwayat') == $vl['id_riwayat']) ? 'bg-muted text-muted-fg fw-bold' : '' ?>  text-start px-3"><?= $vl['tanggal'] ?></button>
														</form>
													</li>
												<?php } ?>

											</ul>
										</div>
									</div>
								</div>
							<?php } ?>


						</div>
					</div>
				</div>

			</div>
			<div class="col-xl-9 col-xxl-9">
				<div class="card">
					<div class="card-header py-3 d-flex align-items-center justify-content-between">
						<h4 class="mb-0">Laporan Hasil Perawatan</h4>
						<?php if ($selected): ?>
							<div class="d-flex gap-2">
								<a href="<?= base_url('riwayat/edit/' . $selected->id_riwayat) ?>"
									class="btn btn-sm btn-outline-primary">
									<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-edit"
										width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
										fill="none" stroke-linecap="round" stroke-linejoin="round">
										<path stroke="none" d="M0 0h24v24H0z" fill="none" />
										<path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
										<path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
									</svg>
									Edit
								</a>
								<button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
									data-bs-target="#modalHapus">
									<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-trash"
										width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
										fill="none" stroke-linecap="round" stroke-linejoin="round">
										<path stroke="none" d="M0 0h24v24H0z" fill="none" />
										<path d="M4 7l16 0" />
										<path d="M10 11l0 6" />
										<path d="M14 11l0 6" />
										<path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
										<path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
									</svg>
									Hapus
								</button>
							</div>
						<?php endif ?>
					</div>
					<div class="card-body">
						<?php if ($selected): ?>
							<div class="">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th class='text-center fw-bold'>
												Kendala dan masalah yang terjadi
											</th>
											<th class="text-center">
												Perbaikan
											</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$kendala = explode(';', $selected->kendala);
										$perbaikan = explode(';', $selected->perbaikan);
										?>

										<tr>
											<td>
												<div class="list-group px-0">
													<?php foreach ($kendala as $k => $vk) { ?>
														<div class="list-group-item py-2">
															<div class="row align-items-center">
																<div class="col-auto"><span class="badge bg-dark"></span></div>
																<div class="col mb-0 fw-normal text-start">
																	<?= $vk ?>
																</div>
															</div>
														</div>
													<?php } ?>


												</div>
											</td>
											<td class="">
												<div class="list-group px-0">
													<?php foreach ($perbaikan as $k => $vk) { ?>
														<div class="list-group-item py-2">
															<div class="row align-items-center">
																<div class="col-auto"><span class="badge bg-dark"></span></div>
																<div class="col mb-0 fw-normal text-start">
																	<?= $vk ?>
																</div>
															</div>
														</div>
													<?php } ?>

												</div>
											</td>
										</tr>
									</tbody>
								</table>
								<h4 class="mb-0">Laporan Kegiatan</h4>
								<?php if ($selected->file): ?>
									<a href="<?= base_url() ?>unduh/laporan_op/<?= $selected->file ?>"
										class="btn btn-primary mt-2 mb-3" target="_blank"> <svg
											xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
											fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
											stroke-linejoin="round"
											class="icon icon-tabler icons-tabler-outline icon-tabler-download">
											<path stroke="none" d="M0 0h24v24H0z" fill="none" />
											<path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" />
											<path d="M7 11l5 5l5 -5" />
											<path d="M12 4l0 12" />
										</svg> Download Laporan</a>
								<?php else: ?>
									<p class="text-muted mt-2 mb-3">Tidak ada file laporan</p>
								<?php endif ?>
								<h4 class="mb-0">Dokumentasi Kegiatan</h4>
								<div class="row gy-2 mt-2">
									<?php if (!empty($gambar)): ?>
										<?php foreach ($gambar as $k => $vl) { ?>
											<div class="col-xl-3 col-xxl-2">
												<div class="rounded">
													<img src="<?= base_url() ?>image/riwayat_op/<?= $vl ?>"
														class="img-fluid rounded" />
												</div>
											</div>
										<?php } ?>
									<?php else: ?>
										<div class="col-12">
											<p class="text-muted">Tidak ada dokumentasi foto</p>
										</div>
									<?php endif ?>

								</div>
							</div>
						<?php else: ?>
							<div class="text-center py-5 text-muted">
								<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-off mb-2"
									width="48" height="48" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
									fill="none" stroke-linecap="round" stroke-linejoin="round">
									<path stroke="none" d="M0 0h24v24H0z" fill="none" />
									<path d="M3 3l18 18" />
									<path d="M7 3h7l5 5v7m0 4a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2v-14" />
								</svg>
								<p>Pilih riwayat dari daftar di sebelah kiri, atau tambah riwayat baru.</p>
							</div>
						<?php endif ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end Konten-->
</div>

<?php if ($selected): ?>
	<!-- Modal Hapus -->
	<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
		<div class="modal-dialog modal-sm modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-body text-center py-4">
					<svg xmlns="http://www.w3.org/2000/svg"
						class="icon icon-tabler icon-tabler-alert-triangle text-danger mb-2" width="48" height="48"
						viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round"
						stroke-linejoin="round">
						<path stroke="none" d="M0 0h24v24H0z" fill="none" />
						<path d="M12 9v4" />
						<path
							d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
						<path d="M12 16h.01" />
					</svg>
					<h3>Hapus Riwayat?</h3>
					<p class="text-muted">Data riwayat tanggal <strong><?= $selected->tanggal ?></strong> beserta file dan
						foto akan dihapus secara permanen.</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn w-100" data-bs-dismiss="modal">Batal</button>
					<a href="<?= base_url('riwayat/hapus/' . $selected->id_riwayat) ?>" class="btn btn-danger w-100">Ya,
						Hapus</a>
				</div>
			</div>
		</div>
	</div>
<?php endif ?>