

<style>
	.dropdown-item{
		color:black;
	}
	@media (max-width: 767.98px){
		.navbar-expand-md .navbar-collapse .dropdown-menu .dropdown-item {
			color: white;
		}
	}
</style>
<div class="collapse navbar-collapse bg-dark" id="navbar-menu">
	<div class="navbar" style="border-top: solid 3px #FFD615; background:#303481">
		<div class="container-xl">
			<ul class="navbar-nav">
				<li class="nav-item <?php if($this->uri->segment(1)=='beranda'){ echo 'active';} ?>">
					<?php echo anchor('beranda','<span class="nav-link-icon text-white d-md-none d-lg-inline-block">
                      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16"><path fill="currentColor" d="M6.906.664a1.749 1.749 0 0 1 2.187 0l5.25 4.2c.415.332.657.835.657 1.367v7.019A1.75 1.75 0 0 1 13.25 15h-3.5a.75.75 0 0 1-.75-.75V9H7v5.25a.75.75 0 0 1-.75.75h-3.5A1.75 1.75 0 0 1 1 13.25V6.23c0-.531.242-1.034.657-1.366l5.25-4.2Zm1.25 1.171a.25.25 0 0 0-.312 0l-5.25 4.2a.25.25 0 0 0-.094.196v7.019c0 .138.112.25.25.25H5.5V8.25a.75.75 0 0 1 .75-.75h3.5a.75.75 0 0 1 .75.75v5.25h2.75a.25.25 0 0 0 .25-.25V6.23a.25.25 0 0 0-.094-.195Z"/></svg>
                    </span>
                    <span class="nav-link-title text-white fw-semibold">
                      Beranda
                    </span>','class="nav-link"')?>

				</li>

				<li class="nav-item">
					<?php echo anchor('peta_lokasi','<span class="nav-link-icon d-md-none d-lg-inline-block text-white">
							<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-map"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 7l6 -3l6 3l6 -3v13l-6 3l-6 -3l-6 3v-13" /><path d="M9 4v13" /><path d="M15 7v13" /></svg>
						</span>
                    <span class="nav-link-title text-white fw-semibold">
					Peta Lokasi
                    </span>','class="nav-link"')?>

				</li>

				<?php if ($this->session->userdata('id_user') != '2') { ?>
				<li class="nav-item <?= ($this->uri->segment(1)=='komparasi') ? 'active' : ''?>">
					<?php echo anchor('komparasi','<span class=" text-white nav-link-icon d-md-none d-lg-inline-block">
						 <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-chart-bar me-2" width="16" height="16" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
             <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
             <rect x="3" y="12" width="6" height="8" rx="1"></rect>
             <rect x="9" y="8" width="6" height="12" rx="1"></rect>
             <rect x="15" y="4" width="6" height="16" rx="1"></rect>
             <line x1="4" y1="20" x2="18" y2="20"></line>
          </svg>
						</span>
						<span class="nav-link-title text-white fw-semibold">
							Komparasi
						</span>','class="nav-link"')?>
				</li>
				<?php } ?>

				<?php if ($this->session->userdata('id_user') != '2') { ?>
				<li class="nav-item <?= ($this->uri->segment(1)=='monitoring') ? 'active' : ''?>">
					<?php echo anchor('monitoring','<span class="text-white nav-link-icon d-md-none d-lg-inline-block">
						 <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-file-text" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
   <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
   <path d="M14 3v4a1 1 0 0 0 1 1h4"></path>
   <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"></path>
   <path d="M9 9l1 0"></path>
   <path d="M9 13l6 0"></path>
   <path d="M9 17l6 0"></path>
</svg>
						</span>
						<span class="nav-link-title text-white fw-semibold">
							Monitoring
						</span>','class="nav-link"')?>
				</li>

				<?php } ?>

				<?php if ($this->session->userdata('id_user') != '2' and $this->session->userdata('username') != 'serayu_opak') { ?>
				<li class="nav-item <?= ($this->uri->segment(1) == 'riwayat') ? 'active' : '' ?>">
					<?php echo anchor('riwayat','<span class="text-white nav-link-icon d-md-none d-lg-inline-block">
						<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-tool"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 10h3v-3l-3.5 -3.5a6 6 0 0 1 8 8l6 6a2 2 0 0 1 -3 3l-6 -6a6 6 0 0 1 -8 -8l3.5 3.5" /></svg>
						</span>
						<span class="nav-link-title text-white fw-semibold">
							Riwayat O&P
						</span>','class="nav-link"')?>
				</li>

				<?php } ?>
				<?php if ($this->session->userdata('username') != 'serayu_opak') { ?>
				<li class="nav-item dropdown">
					<a class="nav-link dropdown-toggle text-white" href="#navbar-help" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false" >
						<span class="nav-link-icon d-md-none d-lg-inline-block text-white">
							<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 26 26"><g fill="none"><path d="M24 0v24H0V0h24ZM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035c-.01-.004-.019-.001-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427c-.002-.01-.009-.017-.017-.018Zm.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093c.012.004.023 0 .029-.008l.004-.014l-.034-.614c-.003-.012-.01-.02-.02-.022Zm-.715.002a.023.023 0 0 0-.027.006l-.006.014l-.034.614c0 .012.007.02.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01l-.184-.092Z"/><path fill="currentColor" d="M20 14.5a1.5 1.5 0 0 1 1.5 1.5v4a2.5 2.5 0 0 1-2.5 2.5H5A2.5 2.5 0 0 1 2.5 20v-4a1.5 1.5 0 0 1 3 0v3.5h13V16a1.5 1.5 0 0 1 1.5-1.5Zm-8-13A1.5 1.5 0 0 1 13.5 3v9.036l1.682-1.682a1.5 1.5 0 0 1 2.121 2.12l-4.066 4.067a1.75 1.75 0 0 1-2.474 0l-4.066-4.066a1.5 1.5 0 0 1 2.121-2.121l1.682 1.682V3A1.5 1.5 0 0 1 12 1.5Z"/></g></svg>
						</span>
						<span class="nav-link-title fw-semibold text-white">
							Unduh
						</span>
					</a>
					<div class="dropdown-menu">
						<?php echo anchor('datapos','Unduh Data','class="dropdown-item"');?>
						<?php echo anchor('unduh/bbws_so_1.3.2.apk','Android App','class="dropdown-item"');?>
						<?php echo anchor('https://apps.apple.com/id/app/bbws-so/id6480156441','IOS App','class="dropdown-item ss" target="_blank"');?>		<?php echo anchor('unduh/manualbook_awlr.pdf','Manual Book AWLR','class="dropdown-item"');?>						

					</div>
				</li>
				<?php } ?>
				<?php if ($this->session->userdata('username') != 'serayu_opak') { ?>
				<li class="nav-item <?php if($this->uri->segment(1)=='pengaturan'){ echo 'active';} ?>">
					<?php echo anchor('pengaturan/tingkat_siaga_awlr','<span class="nav-link-icon d-md-none d-lg-inline-block text-white">
                      <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-settings" width="24" height="24" viewBox="0 0 24 24" stroke-width="225" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z" /><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0" /></svg>
                    </span>
                    <span class="nav-link-title  fw-semibold text-white">
                        Pengaturan
                    </span>','class="nav-link"')?>


				</li>

				<?php } ?>
				<li class="nav-item">
					<?php echo anchor('login/logout','<span class="nav-link-icon d-md-none d-lg-inline-block text-white">
                      <svg xmlns="http://www.w3.org/2000/svg" class="me-2" width="24" height="24" viewBox="0 0 24 24"><g fill="none"><path d="M24 0v24H0V0h24ZM12.593 23.258l-.011.002l-.071.035l-.02.004l-.014-.004l-.071-.035c-.01-.004-.019-.001-.024.005l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427c-.002-.01-.009-.017-.017-.018Zm.265-.113l-.013.002l-.185.093l-.01.01l-.003.011l.018.43l.005.012l.008.007l.201.093c.012.004.023 0 .029-.008l.004-.014l-.034-.614c-.003-.012-.01-.02-.02-.022Zm-.715.002a.023.023 0 0 0-.027.006l-.006.014l-.034.614c0 .012.007.02.017.024l.015-.002l.201-.093l.01-.008l.004-.011l.017-.43l-.003-.012l-.01-.01l-.184-.092Z"/><path fill="currentColor" d="M12 2.5a1.5 1.5 0 0 1 0 3H7a.5.5 0 0 0-.5.5v12a.5.5 0 0 0 .5.5h4.5a1.5 1.5 0 0 1 0 3H7A3.5 3.5 0 0 1 3.5 18V6A3.5 3.5 0 0 1 7 2.5Zm6.06 5.61l2.829 2.83a1.5 1.5 0 0 1 0 2.12l-2.828 2.83a1.5 1.5 0 1 1-2.122-2.122l.268-.268H12a1.5 1.5 0 0 1 0-3h4.207l-.268-.268a1.5 1.5 0 1 1 2.122-2.121Z"/></g></svg>
                    </span>
                    <span class="nav-link-title  fw-semibold text-white">
                        Keluar
                    </span>','class="nav-link"')?>


				</li>

			</ul>
		</div>
	</div>
</div>