
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="<?= base_url() ?>code/bootstrap5-toggle.min.css" rel="stylesheet" />
<script src="<?= base_url() ?>code/bootstrap5-toggle.jquery.min.js"></script>
<div class="card mt-3 mt-md-0">
	<div class="card-header py-3">
		<h3 class="mb-0">Daftar Logger AWLR</h3>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th width="10px">No</th>
						<th>ID Logger</th>
						<th>Nama Pos</th>
						<th>Status Notifikasi</th>
						<th>Level Siaga</th>
						<th>Jeda Notif</th>
						<th>Edit</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					$i = 1;
					foreach($data_awlr as $key=>$vl){ ?>
					<tr>
						<td class="text-center"><?= $i++ ?></td>
						<td><?= $vl['id_logger'] ?></td>
						<td><?= $vl['nama_lokasi']?></td>
						<td><?= ($vl['status']) ? 'Aktif' : 'Tidak Aktif' ?></td>
						<td>
							<table class="table table-bordered mb-0 table-sm">
								<tbody>
									<?php 

						if($vl['list_notif']){
							foreach($vl['list_notif'] as $k=>$v) : ?>
									<tr>
										<td class="ps-2"><?= $v['nama']  ?></td>
										<td class="ps-2"><?= $v['nilai'] . ' m'  ?></td>
									</tr>

									<?php endforeach; } else { 
							echo '-';
						}?>
								</tbody>
							</table>
						</td>
						<td><?= ($vl['list_notif']) ? $vl['jeda_notif'] .' Menit' :'-'  ?></td>
						<td>
							<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#edit<?=$vl['id_logger'] ?>">Edit</button>
						</td>
					</tr>
					<?php } ?>
				</tbody>

			</table>
		</div>
	</div>
</div>
<?php 
foreach($data_awlr as $key=>$vl){ ?>
<div class="modal fade" id="edit<?= $vl['id_logger']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel"><?= $vl['nama_lokasi']?> - <?= $vl['id_logger']?></h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<form action="<?= base_url() ?>pengaturan/edit_notifikasi/<?=$vl['id_logger']?>" method="post" enctype="multipart/form-data">
				<div class="modal-body pt-3">
					<div class="d-flex align-items-center mb-3">
						<label class="me-3">Status Notifikasi</label>
						<input type="checkbox" name="status_notif" data-toggle="toggle" <?= ($vl['status']) ? 'checked' : '' ?> id="status_notif<?= $vl['id_logger'] ?>">	
					</div>

					<?php if($vl['status']){ ?>
					<div  id="notif<?= $vl['id_logger']?>">
						<div class="card">
							<div class="card-header py-2 px-3">
								Level Alert
							</div>
							<div class="card-body pt-1 px-3 pe-4" id="alert<?=$vl['id_logger']?>">
								<?php foreach($vl['list_notif'] as $k=>$v) : ?>
								<div class="row mt-2" id="list_<?= $vl['id_logger'] ?>_<?= $k ?>">
									<div class="col-md-6 ">
										<label class="form-label">Nama Alert</label>
										<div class="input-group ">
											<input type="text" value="<?= $v['nama']?>" name="nama_list[]" class="form-control" required/>
										</div>
									</div>
									<div class="col-10 col-md-5">
										<label class="form-label">Nilai</label>
										<div class="input-group">
											<input type="text" value="<?= $v['nilai']?>" name="nilai_list[]" class="form-control" required />
											<span class="input-group-text">m</span>
										</div>
									</div>
									<div class="col-2 col-md-1 d-flex align-items-end">
										<?php if($k == 0){?>
										<button class="btn btn-info" type="button" id="tambah<?= $vl['id_logger']?>_<?= $k?>">
											<i class="fa-solid fa-plus"></i>
										</button>
										<?php } else{?>
										<button class="btn btn-danger" type="button" id="hapus<?= $vl['id_logger']?>_<?= $k?>" onclick="remove_option(<?= $vl['id_logger']?> , <?= $k?>)">
											<i class="fa-solid fa-trash-can"></i>
										</button>
										<?php } ?>

									</div>
								</div>
								<?php endforeach; ?>
							</div>
						</div>
						<label class="form-label mt-3">Jeda Notifikasi</label>
						<div class=" input-group mb-3">
							<input type="number" name="jeda_notif" class="form-control" value="<?= $vl['jeda_notif'] ?>" required>
							<span class="input-group-text">Menit</span>
						</div>
					</div>
					<?php } else{?> 
					<div  id="notif<?= $vl['id_logger']?>"  class="d-none">
						<div class="card">
							<div class="card-header py-2 px-3">
								Level Alert
							</div>
							<div class="card-body pt-1 px-3 pe-4" id="alert<?=$vl['id_logger']?>">

								<div class="row mt-2" id="list_<?= $vl['id_logger'] ?>_0 ?>">
									<div class="col-md-6 ">
										<label class="form-label">Nama Alert</label>
										<div class="input-group ">
											<input type="text"  name="nama_list[]" class="form-control" placeholder="Nama Alert" required />
										</div>
									</div>
									<div class="col-10 col-md-5">
										<label class="form-label">Nilai</label>
										<div class="input-group">
											<input type="text"  name="nilai_list[]" class="form-control" placeholder="Nilai Alert" required/>
											<span class="input-group-text">m</span>
										</div>
									</div>
									<div class="col-2 col-md-1 d-flex align-items-end">

										<button class="btn btn-info"  type="button" id="tambah<?= $vl['id_logger']?>_0" onclick="add_option(<?= $vl['id_logger'] ?>,0)">
											<i class="fa-solid fa-plus"></i>
										</button>

									</div>
								</div>
							</div>
						</div>
						<label class="form-label mt-3">Jeda Notifikasi</label>
						<div class=" input-group mb-3">
							<input type="number" name="jeda_notif" class="form-control" required>
							<span class="input-group-text">Menit</span>
						</div>
					</div>
					<?php } ?>

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
					<button type="submit" class="btn btn-primary">Simpan</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?php } ?>
<script type="text/javascript">

	var total_pos = <?php echo json_encode($data_awlr) ?>;
	total_pos.forEach(function(item) {
		var ck = $('#status_notif'+item['id_logger']);
		var ls = $('#notif'+item['id_logger']);
		ck.bootstrapToggle();
		$(ck).change(function() {
			if(this.checked) {
				ls.removeClass('d-none');
			}else{
				ls.addClass('d-none');
			}
		});
		var list = item['list_notif'];
		var c = list.length;
		list.forEach(function(i,key){
			var btn = $('#tambah'+item['id_logger']+'_'+key);	

			btn.click(function(){  
				var cs = c++;
				$('#alert'+item['id_logger']).append('<div class="row mt-2" id="list_'+item['id_logger']+'_'+ cs +'"><div class="col-md-6 "><label class="form-label">Nama Alert</label><div class="input-group"><input type="text" class="form-control" name="nama_list[]" placeholder="Nama Alert"/></div></div><div class="col-10 col-md-5"><label class="form-label">Nilai</label><div class="input-group"><input type="text" class="form-control" name="nilai_list[]" placeholder="Nilai Alert" /><span class="input-group-text">m</span></div></div><div class="col-2 col-md-1 d-flex align-items-end"><button class="btn btn-danger" type="button" id="hapus'+item["id_logger"]+'_'+ cs +'" onclick="remove_option('+item['id_logger']+','+cs+')"><i class="fa-solid fa-trash-can"></i></button></div></div>');
			});
		});

	});
	function remove_option(id_logger, id_list){
		console.log('#list_'+id_logger+'_'+id_list);
		$('#list_'+id_logger+'_'+id_list).remove();
	}
	
	function add_option(id_logger, id_list){
		
		var cs = id_list++;
		$('#alert'+id_logger).append('<div class="row mt-2" id="list_'+id_logger+'_'+ cs +'"><div class="col-md-6 "><label class="form-label">Nama Alert</label><div class="input-group"><input type="text" class="form-control" name="nama_list[]" placeholder="Nama Alert" required/></div></div><div class="col-10 col-md-5"><label class="form-label">Nilai</label><div class="input-group"><input type="text" class="form-control" name="nilai_list[]" placeholder="Nilai Alert" required/><span class="input-group-text">m</span></div></div><div class="col-2 col-md-1 d-flex align-items-end"><button class="btn btn-danger" type="button" id="hapus'+id_logger+'_'+ cs +'" onclick="remove_option('+id_logger+','+cs+')"><i class="fa-solid fa-trash-can"></i></button></div></div>');
	}
</script>