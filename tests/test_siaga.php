<?php
/**
 * Cek logika ambang siaga. Jalankan: php tests/test_siaga.php
 * Tanpa framework — library Siaga sengaja bebas DB/CI.
 */
define('BASEPATH', __DIR__);
require __DIR__ . '/../application/libraries/Siaga.php';

$s = new Siaga();

// Skema nyata dari Pengaturan::edit_notifikasi(): baris "Aman" nilai 0 id_status 0,
// lalu ambang naik dengan id_status 1..n
$ambang = [
    ['nama' => 'Aman',     'nilai' => '0',   'warna' => '#D5F0C1', 'id_status' => '0'],
    ['nama' => 'Awas',     'nilai' => '3.0', 'warna' => '#ed1c24', 'id_status' => '3'],
    ['nama' => 'Waspada',  'nilai' => '1.5', 'warna' => '#fef216', 'id_status' => '1'],
    ['nama' => 'Siaga',    'nilai' => '2.2', 'warna' => '#f47e2c', 'id_status' => '2'],
];

// Urutan input acak harus tetap benar (library mengurutkan sendiri)
$r = $s->evaluasi(0.8, $ambang);
assert($r['level'] === 'Aman', 'di bawah ambang pertama → Aman');
assert($r['is_bahaya'] === false, 'Aman bukan bahaya');
assert($r['ambang_berikutnya']['nama'] === 'Waspada', 'ambang berikutnya Waspada');
assert(abs($r['sisa_ke_ambang_berikutnya'] - 0.7) < 1e-9, 'sisa 0.7 m ke Waspada');

// Tepat di ambang harus dihitung sudah melampaui (>=), bukan belum
$r = $s->evaluasi(1.5, $ambang);
assert($r['level'] === 'Waspada', 'tepat di ambang = sudah Waspada');
assert($r['is_bahaya'] === true, 'Waspada = bahaya');

// Ambang TERTINGGI yang dilampaui yang menang, bukan yang pertama cocok
$r = $s->evaluasi(2.5, $ambang);
assert($r['level'] === 'Siaga', 'ambang tertinggi yang dilampaui menang');
assert($r['nilai_ambang'] === 2.2, 'nilai ambang Siaga 2.2');
assert($r['ambang_berikutnya']['nama'] === 'Awas', 'berikutnya Awas');

// Di atas ambang teratas: tidak ada ambang berikutnya
$r = $s->evaluasi(4.1, $ambang);
assert($r['level'] === 'Awas', 'lewat ambang teratas → Awas');
assert($r['ambang_berikutnya'] === null, 'tidak ada ambang di atas Awas');
assert($r['sisa_ke_ambang_berikutnya'] === null, 'sisa null di ambang teratas');

// Pos tanpa ambang terpasang tidak boleh dilaporkan sebagai Aman
$r = $s->evaluasi(2.5, []);
assert($r['level'] === 'Tidak diatur', 'tanpa ambang → Tidak diatur, bukan Aman');
assert($r['is_bahaya'] === false, 'tanpa ambang bukan bahaya');

// Pos offline (TMA null) juga tidak boleh dilaporkan Aman
$r = $s->evaluasi(null, $ambang);
assert($r['level'] === 'Data tidak tersedia', 'TMA null → Data tidak tersedia');
assert($r['is_bahaya'] === false, 'data kosong bukan bahaya');
assert(count($r['ambang_terpasang']) === 4, 'ambang tetap dilaporkan walau data kosong');

// Baris ambang rusak (nilai non-numerik) diabaikan, tidak bikin fatal
$r = $s->evaluasi(2.5, [['nama' => 'Rusak', 'nilai' => '-'], ['nama' => 'Siaga', 'nilai' => '2.0', 'id_status' => '1']]);
assert($r['level'] === 'Siaga', 'baris ambang rusak diabaikan');

echo "OK — semua cek siaga lulus\n";
