<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
	.badge-segmen { font-size: 0.7rem; padding: 0.25em 0.6em; }
	.rumus-preview { font-family: 'Courier New', monospace; font-size: 0.85rem; color: #1e293b; }
	.domain-label { font-size: 0.78rem; color: #64748b; }
	.table-rating td, .table-rating th { vertical-align: middle; }
	.card-stasiun { border-left: 3px solid #0ea5e9; }
	.card-stasiun .card-header { background: #f8fafc; }
	.btn-add-segmen { border-style: dashed; }
</style>

<div class="card mt-3 mt-md-0">
	<div class="card-header py-3 d-flex justify-content-between align-items-center">
		<h3 class="mb-0">Rumus Rating Curve (Q = a &times; (MA + b)<sup>c</sup>)</h3>
		<button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahStasiun">
			<i class="fa-solid fa-plus me-1"></i> Tambah Stasiun
		</button>
	</div>
	<div class="card-body">
		<?php if (empty($data_stasiun)) : ?>
			<div class="text-center py-5">
				<i class="fa-solid fa-chart-line fa-3x text-muted mb-3 d-block"></i>
				<p class="text-muted">Belum ada rumus rating curve yang terdaftar.</p>
				<button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalTambahStasiun">
					Tambah Stasiun Pertama
				</button>
			</div>
		<?php else : ?>
			<?php foreach ($data_stasiun as $idLogger => $stasiun) :
				$first = $stasiun[0];
			?>
				<div class="card card-stasiun mb-3">
					<div class="card-header py-2 d-flex justify-content-between align-items-center">
						<div>
							<strong><?= $first->nama_stasiun ?></strong>
							<span class="text-muted ms-2">ID: <?= $idLogger ?></span>
							<span class="domain-label ms-3">
								Domain MA: <?= $first->domain_min ?> m s/d <?= $first->domain_max ?> m
							</span>
							<span class="domain-label ms-2">|</span>
							<span class="domain-label ms-2">Kalibrasi: <?= $first->periode_kalibrasi ?></span>
						</div>
						<div>
							<button class="btn btn-sm btn-outline-primary btn-add-segmen"
								data-bs-toggle="modal"
								data-bs-target="#modalTambahSegmen"
								data-logger="<?= $idLogger ?>"
								data-nama="<?= $first->nama_stasiun ?>"
								data-dmin="<?= $first->domain_min ?>"
								data-dmax="<?= $first->domain_max ?>">
								<i class="fa-solid fa-plus me-1"></i> Segmen
							</button>
						</div>
					</div>
					<div class="card-body p-0">
						<div class="table-responsive">
							<table class="table table-bordered table-rating mb-0">
								<thead>
									<tr class="bg-light">
										<th width="60" class="text-center">Seg</th>
										<th>Rentang MA (m)</th>
										<th>Rumus Q (m<sup>3</sup>/det)</th>
										<th width="120">a</th>
										<th width="100">b</th>
										<th width="100">c</th>
										<th width="130">Sumber</th>
										<th width="90" class="text-center">Aksi</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($stasiun as $seg) : ?>
										<tr>
											<td class="text-center">
												<span class="badge bg-azure badge-segmen"><?= $seg->segmen ?></span>
											</td>
											<td>
												<code><?= $seg->ma_min ?> &le; MA &le; <?= $seg->ma_max ?></code>
											</td>
											<td class="rumus-preview">
												Q = <?= $seg->koef_a ?> &times; (MA <?= ($seg->koef_b >= 0) ? '+ ' . $seg->koef_b : '- ' . abs($seg->koef_b) ?>)<sup><?= $seg->koef_c ?></sup>
											</td>
											<td><?= $seg->koef_a ?></td>
											<td><?= $seg->koef_b ?></td>
											<td><?= $seg->koef_c ?></td>
											<td><small><?= $seg->sumber_penurunan ?></small></td>
											<td class="text-center">
												<button class="btn btn-sm btn-outline-warning btn-edit-seg"
													data-id="<?= $seg->id ?>"
													data-logger="<?= $idLogger ?>"
													data-nama="<?= $seg->nama_stasiun ?>"
													data-dmin="<?= $seg->domain_min ?>"
													data-dmax="<?= $seg->domain_max ?>"
													data-segmen="<?= $seg->segmen ?>"
													data-mamin="<?= $seg->ma_min ?>"
													data-mamax="<?= $seg->ma_max ?>"
													data-a="<?= $seg->koef_a ?>"
													data-b="<?= $seg->koef_b ?>"
													data-c="<?= $seg->koef_c ?>"
													data-sumber="<?= $seg->sumber_penurunan ?>"
													data-periode="<?= $seg->periode_kalibrasi ?>"
													data-bs-toggle="modal"
													data-bs-target="#modalEditSegmen">
													<i class="fa-solid fa-pen-to-square"></i>
												</button>
												<button class="btn btn-sm btn-outline-danger btn-hapus-seg"
													data-id="<?= $seg->id ?>"
													data-nama="<?= $seg->nama_stasiun ?> Segmen <?= $seg->segmen ?>">
													<i class="fa-solid fa-trash-can"></i>
												</button>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>

<!-- Modal Tambah Stasiun Baru -->
<div class="modal fade" id="modalTambahStasiun" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<form action="<?= base_url() ?>pengaturan/simpan_rating_curve" method="post">
			<input type="hidden" name="mode" value="stasiun_baru">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Tambah Stasiun AWLR Baru</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<div class="row mb-3">
						<div class="col-md-4">
							<label class="form-label">ID Logger</label>
							<input type="text" name="id_logger" class="form-control" placeholder="contoh: 10358" required>
						</div>
						<div class="col-md-8">
							<label class="form-label">Nama Stasiun</label>
							<input type="text" name="nama_stasiun" class="form-control" placeholder="contoh: AWLR Ngrancah" required>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-3">
							<label class="form-label">Domain MA Min (m)</label>
							<input type="number" step="0.001" name="domain_min" class="form-control" required>
						</div>
						<div class="col-md-3">
							<label class="form-label">Domain MA Max (m)</label>
							<input type="number" step="0.001" name="domain_max" class="form-control" required>
						</div>
						<div class="col-md-3">
							<label class="form-label">Periode Kalibrasi</label>
							<input type="text" name="periode_kalibrasi" class="form-control" value="2023-2025" required>
						</div>
						<div class="col-md-3">
							<label class="form-label">Sumber Penurunan</label>
							<input type="text" name="sumber_penurunan" class="form-control" value="Grafis-analitis" required>
						</div>
					</div>
					<hr>
					<h6>Segmen 1</h6>
					<div class="row mb-2">
						<div class="col-md-2">
							<label class="form-label">MA Min</label>
							<input type="number" step="0.001" name="seg_ma_min[]" class="form-control" required>
						</div>
						<div class="col-md-2">
							<label class="form-label">MA Max</label>
							<input type="number" step="0.001" name="seg_ma_max[]" class="form-control" required>
						</div>
						<div class="col-md-3">
							<label class="form-label">Koefisien a</label>
							<input type="number" step="0.000001" name="seg_a[]" class="form-control" required>
						</div>
						<div class="col-md-2">
							<label class="form-label">Koefisien b</label>
							<input type="number" step="0.000001" name="seg_b[]" class="form-control" required>
						</div>
						<div class="col-md-2">
							<label class="form-label">Koefisien c</label>
							<input type="number" step="0.000001" name="seg_c[]" class="form-control" required>
						</div>
						<div class="col-md-1 d-flex align-items-end">
							<!-- placeholder for alignment -->
						</div>
					</div>
					<div id="segmenTambahan"></div>
					<button type="button" class="btn btn-sm btn-outline-info mt-2" id="btnTambahSegBaru">
						<i class="fa-solid fa-plus me-1"></i> Tambah Segmen
					</button>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-primary">Simpan</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Modal Tambah Segmen ke Stasiun Existing -->
<div class="modal fade" id="modalTambahSegmen" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<form action="<?= base_url() ?>pengaturan/simpan_rating_curve" method="post">
			<input type="hidden" name="mode" value="segmen_baru">
			<input type="hidden" name="id_logger" id="tsIdLogger">
			<input type="hidden" name="nama_stasiun" id="tsNama">
			<input type="hidden" name="domain_min" id="tsDmin">
			<input type="hidden" name="domain_max" id="tsDmax">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Tambah Segmen - <span id="tsTitle"></span></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<div class="row mb-3">
						<div class="col-md-6">
							<label class="form-label">MA Min (m)</label>
							<input type="number" step="0.001" name="seg_ma_min[]" class="form-control" required>
						</div>
						<div class="col-md-6">
							<label class="form-label">MA Max (m)</label>
							<input type="number" step="0.001" name="seg_ma_max[]" class="form-control" required>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-4">
							<label class="form-label">Koefisien a</label>
							<input type="number" step="0.000001" name="seg_a[]" class="form-control" required>
						</div>
						<div class="col-md-4">
							<label class="form-label">Koefisien b</label>
							<input type="number" step="0.000001" name="seg_b[]" class="form-control" required>
						</div>
						<div class="col-md-4">
							<label class="form-label">Koefisien c</label>
							<input type="number" step="0.000001" name="seg_c[]" class="form-control" required>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-6">
							<label class="form-label">Sumber Penurunan</label>
							<input type="text" name="sumber_penurunan" class="form-control" value="Grafis-analitis">
						</div>
						<div class="col-md-6">
							<label class="form-label">Periode Kalibrasi</label>
							<input type="text" name="periode_kalibrasi" class="form-control" value="2023-2025">
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-primary">Simpan Segmen</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Modal Edit Segmen -->
<div class="modal fade" id="modalEditSegmen" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<form action="<?= base_url() ?>pengaturan/update_rating_curve" method="post">
			<input type="hidden" name="id" id="editId">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Edit Segmen - <span id="editTitle"></span></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<div class="row mb-3">
						<div class="col-md-4">
							<label class="form-label">Domain Min (m)</label>
							<input type="number" step="0.001" name="domain_min" id="editDmin" class="form-control" required>
						</div>
						<div class="col-md-4">
							<label class="form-label">Domain Max (m)</label>
							<input type="number" step="0.001" name="domain_max" id="editDmax" class="form-control" required>
						</div>
						<div class="col-md-4">
							<label class="form-label">Segmen</label>
							<input type="number" name="segmen" id="editSegmen" class="form-control" required>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-6">
							<label class="form-label">MA Min (m)</label>
							<input type="number" step="0.001" name="ma_min" id="editMaMin" class="form-control" required>
						</div>
						<div class="col-md-6">
							<label class="form-label">MA Max (m)</label>
							<input type="number" step="0.001" name="ma_max" id="editMaMax" class="form-control" required>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-4">
							<label class="form-label">Koefisien a</label>
							<input type="number" step="0.000001" name="koef_a" id="editA" class="form-control" required>
						</div>
						<div class="col-md-4">
							<label class="form-label">Koefisien b</label>
							<input type="number" step="0.000001" name="koef_b" id="editB" class="form-control" required>
						</div>
						<div class="col-md-4">
							<label class="form-label">Koefisien c</label>
							<input type="number" step="0.000001" name="koef_c" id="editC" class="form-control" required>
						</div>
					</div>
					<div class="row">
						<div class="col-md-6">
							<label class="form-label">Sumber Penurunan</label>
							<input type="text" name="sumber_penurunan" id="editSumber" class="form-control">
						</div>
						<div class="col-md-6">
							<label class="form-label">Periode Kalibrasi</label>
							<input type="text" name="periode_kalibrasi" id="editPeriode" class="form-control">
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="submit" class="btn btn-primary">Simpan Perubahan</button>
				</div>
			</div>
		</form>
	</div>
</div>

<!-- Form Hapus Hidden -->
<form id="formHapusSegmen" action="<?= base_url() ?>pengaturan/hapus_rating_curve" method="post" style="display:none;">
	<input type="hidden" name="id" id="hapusId">
</form>

<script>
	// Tambah Segmen di Modal Stasiun Baru
	var segCount = 1;
	$('#btnTambahSegBaru').click(function () {
		segCount++;
		var html = '<hr><h6>Segmen ' + segCount + '</h6>' +
			'<div class="row mb-2 seg-row">' +
			'<div class="col-md-2"><label class="form-label">MA Min</label><input type="number" step="0.001" name="seg_ma_min[]" class="form-control" required></div>' +
			'<div class="col-md-2"><label class="form-label">MA Max</label><input type="number" step="0.001" name="seg_ma_max[]" class="form-control" required></div>' +
			'<div class="col-md-3"><label class="form-label">Koef a</label><input type="number" step="0.000001" name="seg_a[]" class="form-control" required></div>' +
			'<div class="col-md-2"><label class="form-label">Koef b</label><input type="number" step="0.000001" name="seg_b[]" class="form-control" required></div>' +
			'<div class="col-md-2"><label class="form-label">Koef c</label><input type="number" step="0.000001" name="seg_c[]" class="form-control" required></div>' +
			'<div class="col-md-1 d-flex align-items-end"><button type="button" class="btn btn-sm btn-danger btn-remove-seg"><i class="fa-solid fa-trash-can"></i></button></div>' +
			'</div>';
		$('#segmenTambahan').append(html);
	});

	$(document).on('click', '.btn-remove-seg', function () {
		$(this).closest('.seg-row').prev('hr').remove();
		$(this).closest('.seg-row').prev('h6').remove();
		$(this).closest('.seg-row').remove();
	});

	// Populate modal Tambah Segmen
	$('#modalTambahSegmen').on('show.bs.modal', function (e) {
		var btn = $(e.relatedTarget);
		$('#tsIdLogger').val(btn.data('logger'));
		$('#tsNama').val(btn.data('nama'));
		$('#tsDmin').val(btn.data('dmin'));
		$('#tsDmax').val(btn.data('dmax'));
		$('#tsTitle').text(btn.data('nama') + ' (' + btn.data('logger') + ')');
	});

	// Populate modal Edit Segmen
	$('#modalEditSegmen').on('show.bs.modal', function (e) {
		var btn = $(e.relatedTarget);
		$('#editId').val(btn.data('id'));
		$('#editTitle').text(btn.data('nama') + ' Seg ' + btn.data('segmen'));
		$('#editDmin').val(btn.data('dmin'));
		$('#editDmax').val(btn.data('dmax'));
		$('#editSegmen').val(btn.data('segmen'));
		$('#editMaMin').val(btn.data('mamin'));
		$('#editMaMax').val(btn.data('mamax'));
		$('#editA').val(btn.data('a'));
		$('#editB').val(btn.data('b'));
		$('#editC').val(btn.data('c'));
		$('#editSumber').val(btn.data('sumber'));
		$('#editPeriode').val(btn.data('periode'));
	});

	// Hapus Segmen
	$(document).on('click', '.btn-hapus-seg', function () {
		var nama = $(this).data('nama');
		if (confirm('Hapus ' + nama + '?\nData yang sudah dihapus tidak dapat dikembalikan.')) {
			$('#hapusId').val($(this).data('id'));
			$('#formHapusSegmen').submit();
		}
	});
</script>
