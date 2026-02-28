<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * DateResolver Library
 * 
 * Menerjemahkan ekspresi tanggal natural language Bahasa Indonesia
 * menjadi structured date parameters untuk query database.
 * 
 * Usage:
 *   $this->load->library('DateResolver');
 *   $result = $this->dateresolver->resolve('7 hari terakhir');
 *   // => ['type'=>'range', 'granularity'=>'range', 'start'=>'2026-02-22', 'end'=>'2026-03-01', ...]
 */
class DateResolver
{
    // Mapping nama bulan Indonesia → angka
    private $bulan_map = [
        'januari' => 1,
        'jan' => 1,
        'februari' => 2,
        'feb' => 2,
        'maret' => 3,
        'mar' => 3,
        'april' => 4,
        'apr' => 4,
        'mei' => 5,
        'juni' => 6,
        'jun' => 6,
        'juli' => 7,
        'jul' => 7,
        'agustus' => 8,
        'ags' => 8,
        'agu' => 8,
        'september' => 9,
        'sep' => 9,
        'sept' => 9,
        'oktober' => 10,
        'okt' => 10,
        'november' => 11,
        'nov' => 11,
        'desember' => 12,
        'des' => 12,
    ];

    /**
     * Resolve natural language date text to structured parameters.
     *
     * @param string $text  Raw date expression from user
     * @return array        Structured date result
     */
    public function resolve($text)
    {
        $text = trim(strtolower($text));
        $today = date('Y-m-d');

        // ─── 1. Exact keywords ───
        $exact = $this->_match_exact($text, $today);
        if ($exact)
            return $exact;

        // ─── 2. "N hari/minggu/bulan/tahun terakhir/lalu/yang lalu" ───
        $relative_past = $this->_match_relative_past($text, $today);
        if ($relative_past)
            return $relative_past;

        // ─── 3. "N hari/minggu/bulan ke depan" ───
        $relative_future = $this->_match_relative_future($text, $today);
        if ($relative_future)
            return $relative_future;

        // ─── 4. Specific month: "januari 2026", "bulan maret", "maret 2026" ───
        $month = $this->_match_specific_month($text);
        if ($month)
            return $month;

        // ─── 5. Specific date: "tanggal 15 februari 2026", "15 maret", "1 jan 2026" ───
        $specific_date = $this->_match_specific_date($text);
        if ($specific_date)
            return $specific_date;

        // ─── 6. Date range: "1 - 15 maret 2026", "dari 1 feb sampai 15 mar" ───
        $range = $this->_match_date_range($text);
        if ($range)
            return $range;

        // ─── 7. ISO format fallback: "2026-03-01", "2026-03" ───
        $iso = $this->_match_iso_format($text);
        if ($iso)
            return $iso;

        // ─── 8. strtotime fallback ───
        $fallback = $this->_match_strtotime($text);
        if ($fallback)
            return $fallback;

        // Not recognized
        return [
            'status' => 'error',
            'message' => 'Tidak dapat mengenali format tanggal: "' . $text . '"',
            'hint' => 'Coba gunakan format: "hari ini", "kemarin", "7 hari terakhir", "bulan ini", "januari 2026", "1-15 maret 2026"'
        ];
    }

    // ─────────────────────────────────────────
    // Pattern 1: Exact keywords
    // ─────────────────────────────────────────
    private function _match_exact($text, $today)
    {
        $map = [
            // Hari ini
            'hari ini' => ['type' => 'day', 'granularity' => 'day', 'tanggal' => $today],
            'sekarang' => ['type' => 'day', 'granularity' => 'day', 'tanggal' => $today],
            'saat ini' => ['type' => 'day', 'granularity' => 'day', 'tanggal' => $today],

            // Kemarin
            'kemarin' => ['type' => 'day', 'granularity' => 'day', 'tanggal' => date('Y-m-d', strtotime('-1 day'))],
            'kemaren' => ['type' => 'day', 'granularity' => 'day', 'tanggal' => date('Y-m-d', strtotime('-1 day'))],

            // Besok / lusa
            'besok' => ['type' => 'day', 'granularity' => 'day', 'tanggal' => date('Y-m-d', strtotime('+1 day'))],
            'besuk' => ['type' => 'day', 'granularity' => 'day', 'tanggal' => date('Y-m-d', strtotime('+1 day'))],
            'lusa' => ['type' => 'day', 'granularity' => 'day', 'tanggal' => date('Y-m-d', strtotime('+2 days'))],

            // Minggu ini/lalu
            'minggu ini' => [
                'type' => 'range',
                'granularity' => 'range',
                'start' => date('Y-m-d', strtotime('monday this week')),
                'end' => $today
            ],
            'minggu lalu' => [
                'type' => 'range',
                'granularity' => 'range',
                'start' => date('Y-m-d', strtotime('monday last week')),
                'end' => date('Y-m-d', strtotime('sunday last week'))
            ],
            'pekan ini' => [
                'type' => 'range',
                'granularity' => 'range',
                'start' => date('Y-m-d', strtotime('monday this week')),
                'end' => $today
            ],
            'pekan lalu' => [
                'type' => 'range',
                'granularity' => 'range',
                'start' => date('Y-m-d', strtotime('monday last week')),
                'end' => date('Y-m-d', strtotime('sunday last week'))
            ],

            // Bulan ini/lalu
            'bulan ini' => ['type' => 'month', 'granularity' => 'month', 'bulan' => date('Y-m')],
            'bulan lalu' => ['type' => 'month', 'granularity' => 'month', 'bulan' => date('Y-m', strtotime('first day of last month'))],
            'bulan kemarin' => ['type' => 'month', 'granularity' => 'month', 'bulan' => date('Y-m', strtotime('first day of last month'))],

            // Tahun ini/lalu
            'tahun ini' => ['type' => 'year', 'granularity' => 'year', 'tahun' => date('Y')],
            'tahun lalu' => ['type' => 'year', 'granularity' => 'year', 'tahun' => (string) (date('Y') - 1)],
            'tahun kemarin' => ['type' => 'year', 'granularity' => 'year', 'tahun' => (string) (date('Y') - 1)],
        ];

        foreach ($map as $keyword => $result) {
            if ($text === $keyword) {
                $result['label'] = $keyword;
                $result['status'] = 'sukses';
                return $result;
            }
        }

        return null;
    }

    // ─────────────────────────────────────────
    // Pattern 2: "N hari/minggu/bulan/tahun terakhir/lalu/yang lalu/kebelakang"
    // ─────────────────────────────────────────
    private function _match_relative_past($text, $today)
    {
        // Match: "7 hari terakhir", "2 minggu lalu", "3 bulan yang lalu", "1 tahun kebelakang"
        $pattern = '/(\d+)\s*(hari|minggu|pekan|bulan|tahun)\s*(terakhir|lalu|yang\s+lalu|kebelakang|ke\s+belakang)/';

        if (preg_match($pattern, $text, $m)) {
            $n = (int) $m[1];
            $unit = $m[2];

            switch ($unit) {
                case 'hari':
                    return [
                        'status' => 'sukses',
                        'type' => 'range',
                        'granularity' => 'range',
                        'start' => date('Y-m-d', strtotime("-{$n} days")),
                        'end' => $today,
                        'label' => "{$n} hari terakhir"
                    ];

                case 'minggu':
                case 'pekan':
                    $days = $n * 7;
                    return [
                        'status' => 'sukses',
                        'type' => 'range',
                        'granularity' => 'range',
                        'start' => date('Y-m-d', strtotime("-{$days} days")),
                        'end' => $today,
                        'label' => "{$n} minggu terakhir"
                    ];

                case 'bulan':
                    if ($n == 1) {
                        return [
                            'status' => 'sukses',
                            'type' => 'month',
                            'granularity' => 'month',
                            'bulan' => date('Y-m', strtotime('first day of last month')),
                            'label' => '1 bulan lalu'
                        ];
                    }
                    return [
                        'status' => 'sukses',
                        'type' => 'range',
                        'granularity' => 'range',
                        'start' => date('Y-m-d', strtotime("-{$n} months")),
                        'end' => $today,
                        'label' => "{$n} bulan terakhir"
                    ];

                case 'tahun':
                    if ($n == 1) {
                        return [
                            'status' => 'sukses',
                            'type' => 'year',
                            'granularity' => 'year',
                            'tahun' => (string) (date('Y') - 1),
                            'label' => '1 tahun lalu'
                        ];
                    }
                    return [
                        'status' => 'sukses',
                        'type' => 'range',
                        'granularity' => 'range',
                        'start' => date('Y-m-d', strtotime("-{$n} years")),
                        'end' => $today,
                        'label' => "{$n} tahun terakhir"
                    ];
            }
        }

        return null;
    }

    // ─────────────────────────────────────────
    // Pattern 3: "N hari/minggu/bulan ke depan"
    // ─────────────────────────────────────────
    private function _match_relative_future($text, $today)
    {
        $pattern = '/(\d+)\s*(hari|minggu|pekan|bulan|tahun)\s*(ke\s*depan|mendatang)/';

        if (preg_match($pattern, $text, $m)) {
            $n = (int) $m[1];
            $unit = $m[2];

            switch ($unit) {
                case 'hari':
                    return [
                        'status' => 'sukses',
                        'type' => 'range',
                        'granularity' => 'range',
                        'start' => $today,
                        'end' => date('Y-m-d', strtotime("+{$n} days")),
                        'label' => "{$n} hari ke depan"
                    ];
                case 'minggu':
                case 'pekan':
                    $days = $n * 7;
                    return [
                        'status' => 'sukses',
                        'type' => 'range',
                        'granularity' => 'range',
                        'start' => $today,
                        'end' => date('Y-m-d', strtotime("+{$days} days")),
                        'label' => "{$n} minggu ke depan"
                    ];
                case 'bulan':
                    return [
                        'status' => 'sukses',
                        'type' => 'range',
                        'granularity' => 'range',
                        'start' => $today,
                        'end' => date('Y-m-d', strtotime("+{$n} months")),
                        'label' => "{$n} bulan ke depan"
                    ];
                case 'tahun':
                    return [
                        'status' => 'sukses',
                        'type' => 'range',
                        'granularity' => 'range',
                        'start' => $today,
                        'end' => date('Y-m-d', strtotime("+{$n} years")),
                        'label' => "{$n} tahun ke depan"
                    ];
            }
        }

        return null;
    }

    // ─────────────────────────────────────────
    // Pattern 4: Specific month — "januari 2026", "bulan maret", "maret 2026"
    // ─────────────────────────────────────────
    private function _match_specific_month($text)
    {
        $bulan_names = implode('|', array_keys($this->bulan_map));

        // "januari 2026", "maret 2026", "feb 2025"
        if (preg_match('/(?:bulan\s+)?(' . $bulan_names . ')\s+(\d{4})/', $text, $m)) {
            $bln = $this->bulan_map[$m[1]];
            $thn = $m[2];
            return [
                'status' => 'sukses',
                'type' => 'month',
                'granularity' => 'month',
                'bulan' => sprintf('%s-%02d', $thn, $bln),
                'label' => ucfirst($m[1]) . ' ' . $thn
            ];
        }

        // "bulan januari" (tanpa tahun, asumsi tahun ini)
        if (preg_match('/(?:bulan\s+)(' . $bulan_names . ')$/', $text, $m)) {
            $bln = $this->bulan_map[$m[1]];
            return [
                'status' => 'sukses',
                'type' => 'month',
                'granularity' => 'month',
                'bulan' => sprintf('%s-%02d', date('Y'), $bln),
                'label' => ucfirst($m[1]) . ' ' . date('Y')
            ];
        }

        // Just month name alone: "januari", "februari"
        if (preg_match('/^(' . $bulan_names . ')$/', $text, $m)) {
            $bln = $this->bulan_map[$m[1]];
            return [
                'status' => 'sukses',
                'type' => 'month',
                'granularity' => 'month',
                'bulan' => sprintf('%s-%02d', date('Y'), $bln),
                'label' => ucfirst($m[1]) . ' ' . date('Y')
            ];
        }

        return null;
    }

    // ─────────────────────────────────────────
    // Pattern 5: Specific date — "tanggal 15 februari 2026", "15 maret", "1 jan 2026"
    // ─────────────────────────────────────────
    private function _match_specific_date($text)
    {
        $bulan_names = implode('|', array_keys($this->bulan_map));

        // "tanggal 15 februari 2026", "15 februari 2026", "1 jan 2026"
        if (preg_match('/(?:tanggal\s+)?(\d{1,2})\s+(' . $bulan_names . ')\s+(\d{4})/', $text, $m)) {
            $day = (int) $m[1];
            $bln = $this->bulan_map[$m[2]];
            $thn = $m[3];
            $date = sprintf('%s-%02d-%02d', $thn, $bln, $day);
            return [
                'status' => 'sukses',
                'type' => 'day',
                'granularity' => 'day',
                'tanggal' => $date,
                'label' => $day . ' ' . ucfirst($m[2]) . ' ' . $thn
            ];
        }

        // "tanggal 15 februari", "15 maret" (tanpa tahun)
        if (preg_match('/(?:tanggal\s+)?(\d{1,2})\s+(' . $bulan_names . ')/', $text, $m)) {
            $day = (int) $m[1];
            $bln = $this->bulan_map[$m[2]];
            $date = sprintf('%s-%02d-%02d', date('Y'), $bln, $day);
            return [
                'status' => 'sukses',
                'type' => 'day',
                'granularity' => 'day',
                'tanggal' => $date,
                'label' => $day . ' ' . ucfirst($m[2]) . ' ' . date('Y')
            ];
        }

        return null;
    }

    // ─────────────────────────────────────────
    // Pattern 6: Date range — "1 - 15 maret 2026", "dari 1 feb sampai 15 mar 2026"
    // ─────────────────────────────────────────
    private function _match_date_range($text)
    {
        $bulan_names = implode('|', array_keys($this->bulan_map));

        // "1 - 15 maret 2026", "1-15 maret 2026"
        if (preg_match('/(\d{1,2})\s*[-–—]\s*(\d{1,2})\s+(' . $bulan_names . ')\s*(\d{4})?/', $text, $m)) {
            $day1 = (int) $m[1];
            $day2 = (int) $m[2];
            $bln = $this->bulan_map[$m[3]];
            $thn = isset($m[4]) && $m[4] ? $m[4] : date('Y');

            return [
                'status' => 'sukses',
                'type' => 'range',
                'granularity' => 'range',
                'start' => sprintf('%s-%02d-%02d', $thn, $bln, $day1),
                'end' => sprintf('%s-%02d-%02d', $thn, $bln, $day2),
                'label' => "{$day1}-{$day2} " . ucfirst($m[3]) . " {$thn}"
            ];
        }

        // "dari 1 feb sampai 15 mar 2026"
        if (preg_match('/(?:dari|mulai)\s+(\d{1,2})\s+(' . $bulan_names . ')\s*(\d{4})?\s*(?:sampai|hingga|s\.?d\.?|ke)\s*(\d{1,2})\s+(' . $bulan_names . ')\s*(\d{4})?/', $text, $m)) {
            $day1 = (int) $m[1];
            $bln1 = $this->bulan_map[$m[2]];
            $thn1 = ($m[3] ?? '') ?: date('Y');
            $day2 = (int) $m[4];
            $bln2 = $this->bulan_map[$m[5]];
            $thn2 = ($m[6] ?? '') ?: $thn1;

            return [
                'status' => 'sukses',
                'type' => 'range',
                'granularity' => 'range',
                'start' => sprintf('%s-%02d-%02d', $thn1, $bln1, $day1),
                'end' => sprintf('%s-%02d-%02d', $thn2, $bln2, $day2),
                'label' => "{$day1} " . ucfirst($m[2]) . " - {$day2} " . ucfirst($m[5]) . " {$thn2}"
            ];
        }

        return null;
    }

    // ─────────────────────────────────────────
    // Pattern 7: ISO format — "2026-03-01", "2026-03"
    // ─────────────────────────────────────────
    private function _match_iso_format($text)
    {
        // Full date: 2026-03-01
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $text, $m)) {
            return [
                'status' => 'sukses',
                'type' => 'day',
                'granularity' => 'day',
                'tanggal' => $text,
                'label' => $text
            ];
        }

        // Year-month: 2026-03
        if (preg_match('/^(\d{4})-(\d{2})$/', $text, $m)) {
            return [
                'status' => 'sukses',
                'type' => 'month',
                'granularity' => 'month',
                'bulan' => $text,
                'label' => $text
            ];
        }

        // Year only: 2026
        if (preg_match('/^(\d{4})$/', $text, $m)) {
            return [
                'status' => 'sukses',
                'type' => 'year',
                'granularity' => 'year',
                'tahun' => $text,
                'label' => $text
            ];
        }

        return null;
    }

    // ─────────────────────────────────────────
    // Pattern 8: strtotime fallback (English expressions)
    // ─────────────────────────────────────────
    private function _match_strtotime($text)
    {
        $ts = @strtotime($text);
        if ($ts !== false && $ts > 0) {
            return [
                'status' => 'sukses',
                'type' => 'day',
                'granularity' => 'day',
                'tanggal' => date('Y-m-d', $ts),
                'label' => $text . ' → ' . date('Y-m-d', $ts)
            ];
        }

        return null;
    }
}
