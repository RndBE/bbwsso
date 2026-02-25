<style>
	.hide-scrollbar::-webkit-scrollbar {
		display: none;
	}

	li{
		overflow:hidden
	}
</style>
<script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>

<div class="container-xl">
	<!-- Page title -->
	<div class="page-header d-print-none">
		<div class="row g-2 align-items-center">
			<div class="col">
				<h2 class="page-title">
					Riwayat O&P
				</h2>
			</div>
		</div>
	</div>
</div>
<div class="page-body" >
	<!-- Konten-->
	<div class="container-xl" >
		<div class="row">
			<div class="col-xl-3 col-xxl-3">
				<div class="card">
					<div class="card-header py-3">
						<h4 class="mb-0">Tanggal Perawatan</h4>
					</div>
					<div class="card-body">
						<div class="accordion" id="accordionExample">
							<?php foreach($data_op as $k=>$v){ ?>
							<div class="accordion-item">
								<h2 class="accordion-header border-bottom">
									<button class="accordion-button py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $k?>" aria-expanded="false" aria-controls="collapse<?= $k?>">
										<?= $v['nama_lokasi'] ?>
									</button>
								</h2>
								<div id="collapse<?= $k?>" class="accordion-collapse collapse show " data-bs-parent="#accordionExample">
									<div class="accordion-body py-2 px-2">
										<ul class="list-group">
											<?php foreach($v['riwayat'] as $key => $vl) { ?>

											<li class="list-group-item py-0 px-0" aria-current="true">
												<form method='post' action="<?= base_url() ?>riwayat/pilih_riwayat?id_riwayat=<?= $vl['id_riwayat'] ?>">
													<button type="submit" class="w-100 py-2 border-0 <?= ($this->session->userdata('id_riwayat') == $vl['id_riwayat']) ? 'bg-muted text-muted-fg fw-bold' : '' ?>  text-start px-3"><?=  $vl['tanggal'] ?></button>
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
					<div class="card-header py-3">
						<h4 class="mb-0">Laporan Hasil Perawatan</h4>
					</div>
					<div class="card-body">
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
									<?php if($selected) { 
	$kendala= explode(';',$selected->kendala);
	$perbaikan= explode(';',$selected->perbaikan);
									?>
									<?php } ?>
									
									<tr>
										<td>
											<div class="list-group px-0">
												<?php foreach($kendala as $k => $vk) { ?>
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
												<?php foreach($perbaikan as $k => $vk) { ?>
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
							<a href="<?= base_url() ?>unduh/laporan_op/<?= $selected->file ?>" class="btn btn-primary mt-2 mb-3" target="_blank"> <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-download"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg> Download Laporan</a>
							<h4 class="mb-0">Dokumentasi Kegiatan</h4>
							<div class="row gy-2 mt-2">
								<?php foreach($gambar as $k => $vl) { ?>
								<div class="col-xl-3 col-xxl-2">
									<div class="rounded">
									<img src="<?= base_url() ?>image/riwayat_op/<?= $vl ?>" class="img-fluid rounded" />
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
	<!-- end Konten-->
</div>