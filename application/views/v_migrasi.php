<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" integrity="sha512-b2QcS5SsA8tZodcDtGRELiGv5SaKSk1vDHDaQRda0htPYWZ6046lr3kJ5bAAQdpV2mmA/4v0wQF9MyU6/pDIAg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.min.js" integrity="sha512-WW8/jxkELe2CAiE4LvQfwm1rajOS8PHasCCx+knHG0gBHt8EXxS6T6tJRTGuDQVnluuAvMxWF4j8SNFDKceLFg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<div class="container mt-5">
	<form action="<?= base_url() ?>terima/migrasi" method="post" >
		<div class="row"> 
			<div class="col-md-6">
				<div class="form-group mt-3">
					<label>Pilih Logger</label>
					<select class="form-select mt-2" name="id_logger">
						<?php foreach($logger as $key=> $vl){ ?>
						<option value="<?= $vl['id_logger'] ?>" <?= ($this->session->userdata('logger_m') == $vl['id_logger'] ) ? 'selected':'' ?>><?= $vl['id_logger'] . ' - ' . $vl['nama_logger'] ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="form-group mt-3">
					<label>Pilih Bulan</label>
					<input type="month" class="form-control mt-2" name="bulan" value="<?= $this->session->userdata('bln_m')?>"/>
				</div>
				<button type="submit" class="btn btn-primary mt-4 w-100">Mulai Migrasi</button>
			</div>
			<div class="col-md-6">
				<div class="accordion" id="accordionExample">
					<?php foreach($logger as $key => $val) : ?>
					<div class="accordion-item">
						<h2 class="accordion-header " id="headingOne">
							<button class="accordion-button  bg-white pb-0" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?= $val['id_logger'] ?>" aria-expanded="true" aria-controls="collapseOne">
								<table class="table">
									<tbody>
										
										<tr>
											<th><?= $val['id_logger']?></th>
											<th><?= (isset($val['data_last'])) ? $val['data_last']->bulan : '-' ?></th>
											<th><?= (isset($val['data_last'])) ? $val['data_last']->datetime : '-' ?></th>
										</tr>

									</tbody>
								</table>
							</button>
						</h2>
						<div id="collapse-<?= $val['id_logger'] ?>" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
							<div class="accordion-body">
								<?php if(isset($val['list_migrasi'])) { ?>
									
										<table class="table table-bordered">
											<tbody>
												<?php foreach($val['list_migrasi'] as $key => $vl) : ?>
												<tr>
													<th><?= $vl['id_logger']?></th>
													<th><?= $vl['bulan'] ?></th>
													<th><?= $vl['datetime']  ?></th>
												</tr>
												<?php endforeach; ?>
											</tbody>
										</table>
									
								<?php } ?>
								
							</div>
						</div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</form>
</div>