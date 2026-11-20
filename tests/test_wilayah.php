<?php
/**
 * Cek parser alamat → kabupaten/kecamatan. Jalankan: php tests/test_wilayah.php
 * Tanpa framework — library Wilayah sengaja bebas DB/CI.
 *
 * Kasus nyata diambil dari api/list_logger PSDA dan integrasi/api_list BBWS (2026-11-20).
 */
define('BASEPATH', __DIR__);
require __DIR__ . '/../application/libraries/Wilayah.php';

$w = new Wilayah();
$gagal = 0;

// [alamat, hint kabupaten (kolom `kabupaten` PSDA), kabupaten diharapkan, kecamatan diharapkan]
$kasus = [
    // ── PSDA: kabupaten datang sebagai hint ──
    ['Kaliurang, Hargobinangun, Pakem, Sleman', 'Sleman', 'Sleman', 'Pakem'],            // gaya polos
    ['Randusari, Argomulyo, Kec. Cangkringan', 'Sleman', 'Sleman', 'Cangkringan'],       // Kec. tertulis
    ['Candibinangun, Kec. Pakem, Kabupaten Sleman', 'Sleman', 'Sleman', 'Pakem'],
    ['Papringan, Caturtunggal, Depok', 'Sleman', 'Sleman', 'Depok'],                      // tanpa Kec. & tanpa kab
    ['Nyemengan, Tirtonirmolo, Kasihan', 'Bantul', 'Bantul', 'Kasihan'],
    ['Besi, Sidoarum, Godean, Sleman ', 'Sleman', 'Sleman', 'Godean'],                    // spasi sisa
    ['Bunder, Kec. Patuk', 'Gunung Kidul', 'Gunungkidul', 'Patuk'],                       // ejaan dikanonkan
    ['Terong - Dlingo No.2, RT.06/RW.13, Terong II, Terong, Kec. Dlingo', 'Bantul', 'Bantul', 'Dlingo'],
    ['Jl. Wates - Kedundang, Area Sawah, Wates, Kec. Wates, Kabupaten Kulon Progo', 'Kulon Progo', 'Kulon Progo', 'Wates'],
    // ── BBWS (t_lokasi.alamat): tanpa hint ──
    ['Banjaroyo, Kalibawang, Kulon Progo, DIY', '', 'Kulon Progo', 'Kalibawang'],
    ['Seloharjo, Pundong, Bantul, DIY', '', 'Bantul', 'Pundong'],
    ['Karangwuni, Kabupaten Kulon Progo, Daerah Istimewa Yogyakarta', '', 'Kulon Progo', ''],  // kecamatan tak tertulis
    ['Jl. Ngapak - Kentheng, Sendangmulyo, Kec. Minggir, Kabupaten Sleman, Daerah Istimewa Yogyakarta', '', 'Sleman', 'Minggir'],
    ['Logandeng, Kabupaten Gunungkidul, Daerah Istimewa Yogyakarta', '', 'Gunungkidul', ''],
    // ── tepi ──
    ['Jl. Raya Wates, Sindutan, Kabupaten Kulon Progo, Daerah Istimewa Yogyakarta 55654', '', 'Kulon Progo', ''], // desa ≠ kecamatan
    ['Sindutan, Temon', '', '', ''],                                                      // ambigu → kosong
    ['Jl. Magelang, Kec. Mertoyudan, Kabupaten Magelang, Jawa Tengah 56172', '', 'Magelang', 'Mertoyudan'],
    ['Kec. Tegalrejo, Kota Yogyakarta, DIY', '', 'Kota Yogyakarta', 'Tegalrejo'],          // kota tetap berawalan
    ['Gembongan, Kapanewon Sentolo, Kabupaten Kulon Progo', '', 'Kulon Progo', 'Sentolo'],
    ['', '', '', ''],
    [null, null, '', ''],
];

foreach ($kasus as $k) {
    list($alamat, $hint, $kab, $kec) = $k;
    $r = $w->parse($alamat, $hint);
    if ($r['kabupaten'] !== $kab || $r['kecamatan'] !== $kec) {
        $gagal++;
        echo "GAGAL: \"$alamat\" [hint=$hint]\n   dapat: {$r['kabupaten']} / {$r['kecamatan']}\n   harap: $kab / $kec\n";
    }
}

if ($gagal) {
    echo "$gagal dari " . count($kasus) . " kasus gagal\n";
    exit(1);
}
echo "OK — " . count($kasus) . " kasus wilayah lulus\n";
