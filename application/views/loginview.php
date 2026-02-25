
<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
		<meta http-equiv="X-UA-Compatible" content="ie=edge"/>
		<title>BBWS Serayu Opak - Login</title>
		<style>
			@import url('https://rsms.me/inter/inter.css');
			:root {
				--tblr-font-sans-serif: Inter,-apple-system,BlinkMacSystemFont,San Francisco,Segoe UI,Roboto,Helvetica Neue,sans-serif !important;
			}
		</style>
		<!-- CSS files -->
		<link rel="icon" href="<?php echo base_url()?>image/logopu 4.png">
		<link href="https://stesy.beacontelemetry.com/assets/code/tabler.min.css" rel="stylesheet"/>
		<link href="https://stesy.beacontelemetry.com/assets/code/tabler-flags.min.css" rel="stylesheet"/>
		<link href="https://stesy.beacontelemetry.com/assets/code/tabler-payments.min.css" rel="stylesheet"/>
		<link href="https://stesy.beacontelemetry.com/assets/code/tabler-vendors.min.css" rel="stylesheet"/>
		<link href="https://stesy.beacontelemetry.com/assets/code/demo.min.css" rel="stylesheet"/>
	</head>
	<body  class="border-top-wide border-primary d-flex flex-column">
		<div class="page page-center">
			<div class="container-tight py-4 my-auto">
				<div class="text-center mb-4">
					<a href="#" class="navbar-brand navbar-brand-autodark px-2" ><img src="<?php echo base_url()?>image/logo.png" alt="Logo BBWS SO"></a>
				</div>
				<?php echo form_open('login/validasi_login','id="loginform" autocomplete="off" class="card card-md"') ?>

				<div class="card-body">

					<div class="mb-3">
						<label class="form-label">Nama Pengguna</label>
						<input type="text" class="form-control" placeholder="Username" name="username" value="<?php echo set_value('username')?>" autocomplete="off">
					</div>
					<div class="mb-2">
						<label class="form-label">
							Kata Sandi
						</label>
						<div class="input-group input-group-flat">
							<input type="password" id="typepass" name="password"  class="form-control"  placeholder="Kata Sandi" value="<?php echo set_value('password')?>" autocomplete="off">
							<span class="input-group-text">
								<a href="#" id="btneye" class="link-secondary ps-2" onclick="show()" title="Tampilkan kata sandi" ><!-- Download SVG icon from http://tabler-icons.io/i/eye -->
									<img id="imgeye" src="<?php echo base_url()?>image/template/eye.svg" height="24" width="24" alt="" /> </a>
							</span>
						</div>
					</div>
					<div class="form-footer d-flex justify-content-between">
						<a href="<?= base_url() ?>login/login_tamu" class="btn btn-secondary w-100 me-3" style="background:#fac72a;color:black;">Masuk Sebagai Tamu</a>
						<button type="submit" class="btn btn-info w-100"  style="background:#303481;color:white;">Masuk</button>
					</div>
				</div>
				<?php echo form_close();?>

				<?php echo form_error('username');?>
				<?php echo form_error('password');?>
				<?php echo $this->session->flashdata('message');?>
			</div>
		</div>
		<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content p-0">

					<div class="alert alert-info mb-0" role="alert">
						<div class="d-flex py-2">
							<div>
								<!-- Download SVG icon from http://tabler-icons.io/i/info-circle -->
								<svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0"></path><path d="M12 9h.01"></path><path d="M11 12h1v4h1"></path></svg>
							</div>
							<div>
								<h4 class="alert-title mb-2" style="font-size:16px">Pemberitahuan</h4>
								<div class="text-secondary">Website Pemantauan Telemetry BBWS Serayu Opak yang semula di <a href="https://monitoring4system.com">https://monitoring4system.com</a> telah di pindah kan url nya ke <a href="https://bbws.monitoring4system.com">https://bbws.monitoring4system.com</a></div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- Libs JS -->
		<!-- Tabler Core -->
		<script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js" defer></script>
		<script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/demo.min.js" defer></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
		<script type="text/javascript">
			$(document).ready(function() {
				
				console.log(<?php echo json_encode($this->session->flashdata('asal'))?>);
				<?php if($this->session->flashdata('asal') == true){ 
	//echo 'console.log("awddwd");';
	echo "$('#exampleModal').modal('show');";
} ?>
			});
		</script>
		<script type="text/javascript">
			function show() {
				var temp = document.getElementById("typepass");
				var imgeye=document.getElementById("imgeye");
				var btneye=document.getElementById("btneye");
				if (temp.type === "password") {
					temp.type = "text";
					imgeye.src= "<?php echo base_url()?>image/template/eye-off.svg";
					btneye.title="Sembunyikan kata sandi";
				}
				else {
					temp.type = "password";
					imgeye.src= "<?php echo base_url()?>image/template/eye.svg";
					btneye.title="Tampilkan kata sandi";
				}
			}
		</script>
	</body>
</html>




