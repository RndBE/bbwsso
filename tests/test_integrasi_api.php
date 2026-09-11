<?php
/**
 * Cek API Integrasi. Jalankan: php tests/test_integrasi_api.php
 *
 * Dua hal yang diperiksa:
 *  1. Parsing header HTTP Basic — jalur keamanan, murni, bisa diuji langsung.
 *  2. Dokumen OpenAPI tidak melenceng dari controller. Dokumen API tulisan
 *     tangan gampang basi; ini yang menangkapnya.
 */
define('BASEPATH', __DIR__);
require __DIR__ . '/../application/libraries/Basic_auth.php';

$ROOT = dirname(__DIR__);

// ── 1. Parsing Basic ────────────────────────────────────────────────────────
$b64 = base64_encode('pengguna:sandi');

assert(Basic_auth::parse_basic(['PHP_AUTH_USER' => 'a', 'PHP_AUTH_PW' => 'b']) === ['a', 'b'],
    'mod_php: PHP_AUTH_* dipakai langsung');

assert(Basic_auth::parse_basic(['HTTP_AUTHORIZATION' => 'Basic ' . $b64]) === ['pengguna', 'sandi'],
    'CGI/FastCGI: header Authorization mentah dibaca');

assert(Basic_auth::parse_basic(['REDIRECT_HTTP_AUTHORIZATION' => 'Basic ' . $b64]) === ['pengguna', 'sandi'],
    'varian REDIRECT_ (rewrite .htaccess) ikut dibaca');

assert(Basic_auth::parse_basic(['HTTP_AUTHORIZATION' => 'basic ' . $b64]) === ['pengguna', 'sandi'],
    'skema case-insensitive sesuai RFC 7617');

// Sandi boleh memuat titik dua — hanya pemisah pertama yang dipakai
assert(Basic_auth::parse_basic(['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('u:a:b:c')]) === ['u', 'a:b:c'],
    'sandi bertitik dua tidak terpotong');

// Yang tidak sah harus jadi [null, null], bukan lolos atau fatal
foreach ([
    'tanpa header'      => [],
    'skema Bearer'      => ['HTTP_AUTHORIZATION' => 'Bearer ' . $b64],
    'header kosong'     => ['HTTP_AUTHORIZATION' => ''],
    'base64 rusak'      => ['HTTP_AUTHORIZATION' => 'Basic @@bukan-base64@@'],
    'tanpa titik dua'   => ['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('tanpapemisah')],
] as $nama => $srv) {
    assert(Basic_auth::parse_basic($srv) === [null, null], "ditolak: {$nama}");
}

// ── 2. Dokumen OpenAPI vs controller ────────────────────────────────────────
$spec = json_decode(file_get_contents($ROOT . '/application/docs/bbwsso-openapi.json'), true);
assert(is_array($spec), 'bbwsso-openapi.json valid JSON');
assert($spec['openapi'] === '3.0.3', 'versi OpenAPI 3.0.3');
assert(isset($spec['components']['securitySchemes']['BasicAuth']), 'skema BasicAuth dideklarasikan');
assert($spec['security'] === [['BasicAuth' => []]], 'seluruh endpoint wajib BasicAuth');

$src = file_get_contents($ROOT . '/application/controllers/Integrasi_api.php');

// OpenAPI 3.0 tidak mengenal type berupa array — itu sintaks 3.1
assert(strpos(file_get_contents($ROOT . '/application/docs/bbwsso-openapi.json'), '"type": [') === false,
    'tidak ada type-array (sintaks 3.1) di dokumen 3.0');

// /integrasi_api → index(), /integrasi_api/all_logger → all_logger(), dst.
foreach (array_keys($spec['paths']) as $path) {
    $sisa = trim(substr($path, strlen('/integrasi_api')), '/');
    $method = ($sisa === '') ? 'index' : $sisa;
    assert(preg_match('/function\s+' . preg_quote($method, '/') . '\s*\(/', $src) === 1,
        "path {$path} punya method {$method}() di controller");
}

// Setiap parameter yang didokumentasikan benar-benar dibaca controller
foreach ($spec['paths'] as $path => $ops) {
    foreach ($ops as $op) {
        foreach ($op['parameters'] ?? [] as $p) {
            $nama = $p['name'] ?? null;
            if ($nama === null && isset($p['$ref'])) {
                $kunci = basename($p['$ref']);
                $nama = $spec['components']['parameters'][$kunci]['name'] ?? null;
            }
            assert($nama !== null, "parameter di {$path} punya nama");
            assert(strpos($src, "input->get('{$nama}')") !== false,
                "parameter {$nama} ({$path}) benar-benar dibaca controller");
        }
    }
}

// Batas yang ditulis di dokumen harus sama dengan konstanta di controller
preg_match('/MAKS_RIWAYAT\s*=\s*(\d+)/', $src, $m1);
preg_match('/MAKS_HARI\s*=\s*(\d+)/', $src, $m2);
preg_match('/MAKS_HARI_MENIT\s*=\s*(\d+)/', $src, $m3);
preg_match('/MAKS_BARIS_MENIT\s*=\s*(\d+)/', $src, $m4);
$teks = json_encode($spec, JSON_UNESCAPED_UNICODE);
assert(strpos($teks, $m1[1] . ' titik') !== false, "batas {$m1[1]} titik disebut di dokumen");
assert(strpos($teks, $m2[1] . ' hari') !== false, "batas {$m2[1]} hari disebut di dokumen");
assert(strpos($teks, $m3[1] . ' hari') !== false, "batas {$m3[1]} hari disebut di dokumen");
assert(strpos($teks, $m4[1] . ' baris') !== false, "batas {$m4[1]} baris disebut di dokumen");
assert(isset($spec['components']['schemas']['RangeLoggerResponse']['properties']['terpotong']),
    'field terpotong didokumentasikan');
assert(strpos($src, "'terpotong' => \$terpotong") !== false, 'controller benar-benar mengirim terpotong');

// Level akun: akun tamu/user tidak boleh bisa memakai API, sandinya publik
$lib = file_get_contents($ROOT . '/application/libraries/Basic_auth.php');
preg_match('/\$level_diizinkan\s*=\s*\[([^\]]*)\]/', $lib, $ml);
assert(!empty($ml[1]), 'daftar level_diizinkan ada');
$level = array_map(function ($x) { return trim($x, " '\""); }, explode(',', $ml[1]));
assert(!in_array('user', $level, true), "level 'user' TIDAK boleh diizinkan (sandi serayu_opak ada di source)");
assert(!in_array('tamu', $level, true), "level 'tamu' TIDAK boleh diizinkan");
assert(strpos($lib, 'level_diizinkan, true)') !== false, 'level benar-benar diperiksa saat autentikasi');

// Endpoint dokumentasi tidak boleh ikut terkunci auth
assert(preg_match('/function\s+docs\s*\(/', $src) === 1, 'ada method docs()');
assert(preg_match('/function\s+openapi\s*\(/', $src) === 1, 'ada method openapi()');
$blok_docs = substr($src, strpos($src, 'function docs('), 400);
assert(strpos($blok_docs, 'authenticate()') === false, 'halaman docs tetap publik');

echo "OK — semua cek API integrasi lulus\n";
