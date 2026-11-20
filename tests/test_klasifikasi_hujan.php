<?php
/**
 * Cek batas klasifikasi hujan chatbot = menu Analisa. Jalankan: php tests/test_klasifikasi_hujan.php
 *
 * Controller dibuat tanpa konstruktor (tanpa DB/CI); helper private dipanggil lewat reflection.
 * Acuan: Analisa.php / Api.php (per jam) dan analisa_all.php / analisa_arr.php / analisa_awr.php (per hari).
 */
define('BASEPATH', __DIR__);
class CI_Controller
{
}
require __DIR__ . '/../application/controllers/Chatbot.php';

$bot = (new ReflectionClass('Chatbot'))->newInstanceWithoutConstructor();
$gagal = 0;

$TH = 'Berawan / Tidak Hujan';
$SR = 'Hujan Sangat Ringan';
$R = 'Hujan Ringan';
$S = 'Hujan Sedang';
$L = 'Hujan Lebat';
$SL = 'Hujan Sangat Lebat';

// [helper, mm, kelas diharapkan]. Angka tepat di batas masuk kelas di ATASNYA.
$kasus = [
    // ── PER JAM: 0 | 0.1–<1 | 1–<5 | 5–<10 | 10–<20 | ≥20 ──
    ['_klasifikasi_hujan_jam', 0, $TH],
    ['_klasifikasi_hujan_jam', '0.00', $TH],           // SUM dari DB berupa string
    ['_klasifikasi_hujan_jam', 0.2, $SR],
    ['_klasifikasi_hujan_jam', 0.99, $SR],
    ['_klasifikasi_hujan_jam', 1, $R],
    ['_klasifikasi_hujan_jam', 4.99, $R],
    ['_klasifikasi_hujan_jam', 5, $S],
    ['_klasifikasi_hujan_jam', 9.99, $S],
    ['_klasifikasi_hujan_jam', 10, $L],
    ['_klasifikasi_hujan_jam', 19.99, $L],
    ['_klasifikasi_hujan_jam', 20, $SL],
    ['_klasifikasi_hujan_jam', 37.4, $SL],
    ['_klasifikasi_hujan_jam', 4.9999999, $S],         // noise float: tampil "5.00" → Sedang
    // ── PER HARI: 0 | 0.1–<5 | 5–<20 | 20–<50 | 50–<100 | ≥100 ──
    ['_klasifikasi_hujan_harian', 0, $TH],
    ['_klasifikasi_hujan_harian', 3.4, $SR],
    ['_klasifikasi_hujan_harian', 4.99, $SR],
    ['_klasifikasi_hujan_harian', 5, $R],
    ['_klasifikasi_hujan_harian', 7, $R],              // dulu Sangat Ringan (batas lama 10)
    ['_klasifikasi_hujan_harian', 19.99, $R],
    ['_klasifikasi_hujan_harian', 20, $S],
    ['_klasifikasi_hujan_harian', 49.99, $S],
    ['_klasifikasi_hujan_harian', 50, $L],
    ['_klasifikasi_hujan_harian', 72.8, $L],
    ['_klasifikasi_hujan_harian', 99.99, $L],
    ['_klasifikasi_hujan_harian', 100, $SL],
    ['_klasifikasi_hujan_harian', 120.5, $SL],
];

foreach ($kasus as $k) {
    list($metode, $mm, $harap) = $k;
    $m = new ReflectionMethod($bot, $metode);
    $m->setAccessible(true);
    $dapat = $m->invoke($bot, $mm);
    if ($dapat !== $harap) {
        $gagal++;
        echo "GAGAL: $metode($mm)\n   dapat: $dapat\n   harap: $harap\n";
    }
}

if ($gagal) {
    echo "$gagal dari " . count($kasus) . " kasus gagal\n";
    exit(1);
}
echo "OK — " . count($kasus) . " kasus batas klasifikasi hujan lulus\n";
