<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * Kabupaten/kota dan kecamatan satu pos dari teks alamatnya
 * (t_lokasi.alamat; untuk pos PSDA: api/list_logger).
 *
 * Alamat diketik manual dengan format campur:
 *   "Desa, Kec. X"                              → kecamatan tertulis
 *   "Dusun, Desa, Kecamatan, Kab, DIY"          → kecamatan = segmen polos sebelum kabupaten
 *   "Desa, Kabupaten X, Daerah Istimewa ..."    → kecamatan TIDAK tertulis
 * Hanya yang tertulis yang diambil; kosong = tidak diketahui, bukan tebakan.
 *
 * Kelas ini sengaja bebas DB/CI agar bisa diuji langsung:
 *   php tests/test_wilayah.php
 */
class Wilayah
{
    // Penulisan yang tidak konsisten di data (PSDA "Gunung Kidul" vs BBWS "Gunungkidul")
    private static $kanonik = ['gunungkidul' => 'Gunungkidul', 'kulonprogo' => 'Kulon Progo'];

    /**
     * @param string $alamat   teks alamat pos
     * @param string $kab_hint kabupaten dari sumber terstruktur bila ada (kolom `kabupaten` PSDA)
     * @return array ['kabupaten' => string, 'kecamatan' => string]  ('' bila tidak diketahui)
     */
    public function parse($alamat, $kab_hint = '')
    {
        $kab_hint = trim((string) $kab_hint);
        $kab = $this->_kanonik($kab_hint);
        $kec = '';
        $berawalan = false;   // "Kabupaten X"/"Kota X" → gaya panjang: kecamatan hanya bila berawalan Kec.
        $provinsi = false;
        $sisa = [];

        foreach (explode(',', preg_replace('/\s+/', ' ', (string) $alamat)) as $s) {
            $s = trim($s);
            if ($s === '') {
                continue;
            }
            if (preg_match('/^(?:Kec(?:amatan)?\.?|Kapanewon)\s+(.+)$/i', $s, $m)) {
                $kec = $m[1];
            } elseif (preg_match('/^(Kabupaten|Kab\.?|Kota)\s+(.+)$/i', $s, $m)) {
                $kab = $this->_kanonik((stripos($m[1], 'Kota') === 0 ? 'Kota ' : '') . $m[2]);
                $berawalan = true;
            } elseif (preg_match('/^(?:DIY|Daerah Istimewa Yogyakarta|Jawa Tengah|Jateng|Indonesia)\b/i', $s)) {
                $provinsi = true;
            } else {
                $sisa[] = $s;
            }
        }

        // Gaya "Desa, Kec, Kab[, DIY]": kabupaten polos di akhir, dikenali lewat hint
        // atau lewat provinsi yang menyusul di belakangnya.
        if (!$berawalan && $sisa) {
            $akhir = end($sisa);
            $cocok = $kab_hint !== '' ? $this->_kunci($akhir) === $this->_kunci($kab_hint) : $provinsi;
            if ($cocok) {
                $kab = $this->_kanonik($akhir);
                array_pop($sisa);
            }
        }

        // Kecamatan polos = segmen terakhir yang tersisa, asal masih ada desa di depannya
        // dan kabupaten sudah pasti. "Desa, Temon" (tanpa kabupaten) ambigu → dibiarkan kosong.
        if ($kec === '' && !$berawalan && $kab !== '' && count($sisa) >= 2) {
            $kec = end($sisa);
        }

        return ['kabupaten' => $kab, 'kecamatan' => $kec];
    }

    private function _kanonik($nama)
    {
        $k = $this->_kunci($nama);
        return isset(self::$kanonik[$k]) ? self::$kanonik[$k] : trim($nama);
    }

    private function _kunci($s)
    {
        return strtolower(preg_replace('/\s+/', '', (string) $s));
    }
}
