<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <?= anchor('riwayat', '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-arrow-big-left-lines" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 15v3.586a1 1 0 0 1 -1.707 .707l-6.586 -6.586a1 1 0 0 1 0 -1.414l6.586 -6.586a1 1 0 0 1 1.707 .707v3.586h3v6h-3z"></path><path d="M21 15v-6"></path><path d="M18 15v-6"></path></svg>') ?>
            </div>
            <div class="col">
                <h2 class="page-title">
                    <?= $riwayat ? 'Edit Riwayat O&P' : 'Tambah Riwayat O&P' ?>
                </h2>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <?= $riwayat ? 'Edit Data' : 'Data Baru' ?>
                </h3>
            </div>
            <div class="card-body">
                <form action="<?= $form_action ?>" method="post" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Pos / Logger</label>
                                <select name="id_logger" id="select-logger" class="form-select" required>
                                    <option value="">-- Pilih Pos --</option>
                                    <?php foreach ($logger_list as $lg): ?>
                                        <option value="<?= $lg->id_logger ?>" <?= ($riwayat && $riwayat->id_logger == $lg->id_logger) ? 'selected' : '' ?>>
                                            <?= $lg->nama_lokasi ?> (
                                            <?= $lg->nama_logger ?>)
                                        </option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Tanggal Pelaksanaan</label>
                                <input type="date" name="tanggal" class="form-control"
                                    value="<?= $riwayat ? $riwayat->tanggal : '' ?>" required />
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Kendala & Masalah</label>
                                <input type="hidden" name="kendala" id="kendalaValue" required />
                                <div id="kendalaRows">
                                    <?php
                                    $kendala_items = ($riwayat && $riwayat->kendala) ? explode(';', $riwayat->kendala) : [''];
                                    foreach ($kendala_items as $i => $item): ?>
                                        <div class="input-group mb-2 kendala-row">
                                            <input type="text" class="form-control kendala-input"
                                                value="<?= htmlspecialchars(trim($item)) ?>"
                                                placeholder="Kendala <?= $i + 1 ?>" />
                                            <button type="button" class="btn btn-outline-danger btn-remove-row"
                                                title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-x" width="24" height="24"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M18 6l-12 12" />
                                                    <path d="M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success w-100" id="addKendala">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus"
                                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg>
                                    Tambah Kendala
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label required">Perbaikan</label>
                                <input type="hidden" name="perbaikan" id="perbaikanValue" required />
                                <div id="perbaikanRows">
                                    <?php
                                    $perbaikan_items = ($riwayat && $riwayat->perbaikan) ? explode(';', $riwayat->perbaikan) : [''];
                                    foreach ($perbaikan_items as $i => $item): ?>
                                        <div class="input-group mb-2 perbaikan-row">
                                            <input type="text" class="form-control perbaikan-input"
                                                value="<?= htmlspecialchars(trim($item)) ?>"
                                                placeholder="Perbaikan <?= $i + 1 ?>" />
                                            <button type="button" class="btn btn-outline-danger btn-remove-row"
                                                title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="icon icon-tabler icon-tabler-x" width="24" height="24"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                                    stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                                    <path d="M18 6l-12 12" />
                                                    <path d="M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-success w-100" id="addPerbaikan">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-plus"
                                        width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor" fill="none" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M12 5l0 14" />
                                        <path d="M5 12l14 0" />
                                    </svg>
                                    Tambah Perbaikan
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Upload Laporan (PDF)</label>
                                <input type="file" name="file_laporan" class="form-control" accept=".pdf" />
                                <?php if ($riwayat && $riwayat->file): ?>
                                    <small class="form-hint text-success">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-file-check" width="16" height="16"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                            <path
                                                d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                            <path d="M9 15l2 2l4 -4" />
                                        </svg>
                                        File saat ini:
                                        <?= $riwayat->file ?> (kosongkan jika tidak ingin mengganti)
                                    </small>
                                <?php endif ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Upload Foto Dokumentasi</label>
                                <input type="file" name="gambar[]" class="form-control" accept="image/*" multiple />
                                <?php if ($riwayat && $riwayat->gambar): ?>
                                    <small class="form-hint text-success">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="icon icon-tabler icon-tabler-photo-check" width="16" height="16"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M15 8h.01" />
                                            <path d="M11.5 21h-5.5a3 3 0 0 1 -3 -3v-12a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v7" />
                                            <path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l4 4" />
                                            <path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0" />
                                            <path d="M15 19l2 2l4 -4" />
                                        </svg>
                                        Foto sudah ada (kosongkan jika tidak ingin mengganti)
                                    </small>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <a href="<?= base_url('riwayat') ?>" class="btn">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-device-floppy"
                                width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M6 4h10l4 4v10a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2" />
                                <path d="M12 14m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                <path d="M14 4l0 4l-6 0l0 -4" />
                            </svg>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function createRow(type, placeholder) {
        var div = document.createElement('div');
        div.className = 'input-group mb-2 ' + type + '-row';
        div.innerHTML = '<input type="text" class="form-control ' + type + '-input" placeholder="' + placeholder + '" />' +
            '<button type="button" class="btn btn-outline-danger btn-remove-row" title="Hapus">' +
            '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-x" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M18 6l-12 12" /><path d="M6 6l12 12" /></svg>' +
            '</button>';
        return div;
    }

    function updateRemoveButtons(containerId) {
        var rows = document.querySelectorAll('#' + containerId + ' .btn-remove-row');
        rows.forEach(function (btn) {
            btn.style.display = rows.length <= 1 ? 'none' : '';
        });
    }

    document.getElementById('addKendala').addEventListener('click', function () {
        var container = document.getElementById('kendalaRows');
        var count = container.querySelectorAll('.kendala-row').length;
        container.appendChild(createRow('kendala', 'Kendala ' + (count + 1)));
        updateRemoveButtons('kendalaRows');
    });

    document.getElementById('addPerbaikan').addEventListener('click', function () {
        var container = document.getElementById('perbaikanRows');
        var count = container.querySelectorAll('.perbaikan-row').length;
        container.appendChild(createRow('perbaikan', 'Perbaikan ' + (count + 1)));
        updateRemoveButtons('perbaikanRows');
    });

    document.addEventListener('click', function (e) {
        var removeBtn = e.target.closest('.btn-remove-row');
        if (!removeBtn) return;
        var row = removeBtn.closest('.input-group');
        var container = row.parentElement;
        if (container.querySelectorAll('.input-group').length > 1) {
            row.remove();
            updateRemoveButtons(container.id);
        }
    });

    document.querySelector('form').addEventListener('submit', function (e) {
        var kendalaInputs = document.querySelectorAll('.kendala-input');
        var perbaikanInputs = document.querySelectorAll('.perbaikan-input');
        var kendalaVals = [];
        var perbaikanVals = [];
        kendalaInputs.forEach(function (inp) { if (inp.value.trim()) kendalaVals.push(inp.value.trim()); });
        perbaikanInputs.forEach(function (inp) { if (inp.value.trim()) perbaikanVals.push(inp.value.trim()); });
        document.getElementById('kendalaValue').value = kendalaVals.join(';');
        document.getElementById('perbaikanValue').value = perbaikanVals.join(';');
        if (!kendalaVals.length || !perbaikanVals.length) {
            e.preventDefault();
            alert('Kendala dan Perbaikan harus diisi minimal 1 item');
        }
    });

    updateRemoveButtons('kendalaRows');
    updateRemoveButtons('perbaikanRows');

    document.addEventListener('DOMContentLoaded', function () {
        if (window.TomSelect) {
            new TomSelect('#select-logger', { allowEmptyOption: true });
        }
    });
</script>