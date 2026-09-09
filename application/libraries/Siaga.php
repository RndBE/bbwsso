<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Evaluasi tingkat siaga AWLR dari tabel `tingkat_siaga_awlr`.
 *
 * Logika ambang mengikuti halaman rekapitulasi (Monitoring.php):
 * ambang diurut naik, ambang TERTINGGI yang dilampaui nilai TMA yang dipakai.
 *
 * Kelas ini sengaja bebas DB/CI agar bisa diuji langsung:
 *   php tests/test_siaga.php
 */
class Siaga
{
    /**
     * @param mixed $nilai  TMA saat ini (meter)
     * @param array $ambang baris tingkat_siaga_awlr: [['nama','nilai','warna','id_status'], ...]
     * @return array
     */
    public function evaluasi($nilai, $ambang)
    {
        $hasil = [
            'level' => 'Tidak diatur',
            'nilai_ambang' => null,
            'warna' => null,
            'id_status' => null,
            'ambang_berikutnya' => null,
            'sisa_ke_ambang_berikutnya' => null,
            'is_bahaya' => false,
            'ambang_terpasang' => [],
        ];

        // Buang baris tanpa ambang numerik, lalu urut naik
        $rows = [];
        foreach ((array) $ambang as $r) {
            $r = (array) $r;
            if (!isset($r['nilai']) || !is_numeric($r['nilai'])) {
                continue;
            }
            $rows[] = [
                'nama' => isset($r['nama']) ? $r['nama'] : '-',
                'nilai' => (float) $r['nilai'],
                'warna' => isset($r['warna']) ? $r['warna'] : null,
                'id_status' => isset($r['id_status']) ? (int) $r['id_status'] : null,
            ];
        }
        if (!$rows) {
            return $hasil;
        }

        usort($rows, function ($a, $b) {
            return ($a['nilai'] == $b['nilai']) ? 0 : (($a['nilai'] < $b['nilai']) ? -1 : 1);
        });
        $hasil['ambang_terpasang'] = $rows;

        if (!is_numeric($nilai)) {
            $hasil['level'] = 'Data tidak tersedia';
            return $hasil;
        }
        $nilai = (float) $nilai;

        $cocok = null;
        $berikutnya = null;
        foreach ($rows as $r) {
            if ($nilai >= $r['nilai']) {
                $cocok = $r;
            } else {
                $berikutnya = $r;
                break;
            }
        }

        if ($cocok === null) {
            // Di bawah ambang terendah — aman
            $hasil['level'] = 'Aman';
            $hasil['is_bahaya'] = false;
        } else {
            $hasil['level'] = $cocok['nama'];
            $hasil['nilai_ambang'] = $cocok['nilai'];
            $hasil['warna'] = $cocok['warna'];
            $hasil['id_status'] = $cocok['id_status'];
            // "Aman" (id_status 0 / nilai 0) bukan kondisi bahaya
            $hasil['is_bahaya'] = ($cocok['id_status'] !== null)
                ? ($cocok['id_status'] > 0)
                : (strtolower($cocok['nama']) !== 'aman');
        }

        if ($berikutnya !== null) {
            $hasil['ambang_berikutnya'] = ['nama' => $berikutnya['nama'], 'nilai' => $berikutnya['nilai']];
            $hasil['sisa_ke_ambang_berikutnya'] = round($berikutnya['nilai'] - $nilai, 3);
        }

        return $hasil;
    }
}
