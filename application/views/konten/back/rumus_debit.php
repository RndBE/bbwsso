<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap5-toggle@5.0.4/css/bootstrap5-toggle.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap5-toggle@5.0.4/js/bootstrap5-toggle.jquery.min.js"></script>
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
						<th>Set Rumus</th>
						<th>Rumus Debit</th>
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
						<td><?= ($vl['set_rumus']) ? 'Aktif' : 'Tidak Aktif' ?></td>
						<td></td>
						<td>
							<button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#edit_rumus<?=$vl['id_logger'] ?>">Edit</button>
						</td>
					</tr>
					<?php } ?>
				</tbody>

			</table>
		</div>
	</div>
</div>
<?php foreach($data_awlr as $key=>$val) { ?>
<div class="modal fade" id="edit_rumus<?=$val['id_logger']?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel"><?= $val['nama_lokasi']?> </h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body py-3">
				<label></label>
				<input type="text" value="<?=$val['nama_lokasi']?>" class="form-control"/>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary">Save changes</button>
			</div>
		</div>
	</div>
</div>
<?php } ?>
<script type="text/javascript">

</script>