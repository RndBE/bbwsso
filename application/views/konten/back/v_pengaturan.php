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
<div class="page-body ">
	<div class="container-xl ">
		<div class="row">
			<div class="col-md-2">
				<div class="card border-0">
					<div class="card-body p-0">
						<ul class="list-group w-100">
							<li class="list-group-item py-0 px-0 bg-light bg-<?= ($this->uri->segment(2) == 'tingkat_siaga_awlr') ? 'dark-lt' : '' ?>">
								<a href="<?= base_url()  ?>pengaturan/tingkat_siaga_awlr" class="w-100 text-dark d-flex justify-content-center py-3"><!-- Download SVG icon from http://tabler-icons.io/i/home -->
									<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-ripple me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7c3 -2 6 -2 9 0s6 2 9 0" /><path d="M3 17c3 -2 6 -2 9 0s6 2 9 0" /><path d="M3 12c3 -2 6 -2 9 0s6 2 9 0" /></svg>
									Tingkat Siaga AWLR</a></li>
							<li class="list-group-item py-0 px-0 bg-light bg-<?= ($this->uri->segment(2) == 'indikator_curah_hujan') ? 'dark-lt' : '' ?>">
								<a href="<?= base_url()  ?>pengaturan/indikator_curah_hujan" class="w-100 text-dark d-flex justify-content-center py-3"><!-- Download SVG icon from http://tabler-icons.io/i/home -->
									<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-cloud-rain me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 18a4.6 4.4 0 0 1 0 -9a5 4.5 0 0 1 11 2h1a3.5 3.5 0 0 1 0 7" /><path d="M11 13v2m0 3v2m4 -5v2m0 3v2" /></svg>
									Indikator Curah Hujan</a>
							</li>
							<li class="list-group-item py-0 px-0 bg-light bg-<?= ($this->uri->segment(2) == 'unduh_aplikasi') ? 'dark-lt' : '' ?>">
								<a href="<?= base_url()  ?>pengaturan/unduh_aplikasi" class="w-100 text-dark d-flex justify-content-center py-3"><!-- Download SVG icon from http://tabler-icons.io/i/home -->
									<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-download me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
									Unduh Aplikasi</a>
							</li>
							<li class="list-group-item py-0 px-0 bg-light bg-<?= ($this->uri->segment(2) == 'rating_curve') ? 'dark-lt' : '' ?>">
								<a href="<?= base_url() ?>pengaturan/rating_curve" class="w-100 text-dark d-flex justify-content-center py-3">
									<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-chart-line me-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 19l16 0" /><path d="M4 15l4 -6l4 2l4 -5l4 4" /></svg>
									Rumus Rating Curve</a>
							</li>
							<!--
	   <li class="list-group-item py-0 px-0 bg-light" >
		<a href="<?= base_url()  ?>pengaturan/tingkat_status_awlr" class="w-100 text-dark d-flex justify-content-center py-3"><!-- Download SVG icon from http://tabler-icons.io/i/home 
		 <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-notification me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 6h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" /><path d="M17 7m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /></svg>
		 Jeda Notifikasi AWLR</a>
	   </li>-->
						</ul>
					</div>

				</div>
			</div>
			<div class="col-md-10">
				<?php $this->load->view($setting) ?>
			</div>
		</div>
	</div>