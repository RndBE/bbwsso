-- =========================================================
-- Migrasi: Tabel rumus_rating_curve
-- Menyimpan rumus debit rating curve (Q = a * (MA + b)^c)
-- untuk 5 stasiun AWLR BBWS Serayu Opak
-- Periode kalibrasi: 2023–2025
-- Tanggal: 2026-04-30
-- =========================================================

-- --------------------------------------------------------
-- 1. Buat tabel baru rumus_rating_curve
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `rumus_rating_curve` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_logger` varchar(11) NOT NULL COMMENT 'ID logger stasiun AWLR',
  `nama_stasiun` varchar(100) NOT NULL COMMENT 'Nama pos AWLR',
  `domain_min` double NOT NULL COMMENT 'Batas bawah domain MA valid (m)',
  `domain_max` double NOT NULL COMMENT 'Batas atas domain MA valid (m)',
  `segmen` int(11) NOT NULL COMMENT 'Nomor urut segmen (1,2,3,...)',
  `ma_min` double NOT NULL COMMENT 'Batas bawah MA segmen (m)',
  `ma_max` double NOT NULL COMMENT 'Batas atas MA segmen (m)',
  `koef_a` double NOT NULL COMMENT 'Koefisien a pada Q = a*(MA+b)^c',
  `koef_b` double NOT NULL COMMENT 'Koefisien b pada Q = a*(MA+b)^c',
  `koef_c` double NOT NULL COMMENT 'Koefisien c (eksponen) pada Q = a*(MA+b)^c',
  `sumber_penurunan` varchar(50) NOT NULL DEFAULT 'Grafis-analitis' COMMENT 'Metode penurunan rumus',
  `periode_kalibrasi` varchar(20) NOT NULL DEFAULT '2023-2025' COMMENT 'Periode data kalibrasi',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_id_logger` (`id_logger`),
  KEY `idx_logger_segmen` (`id_logger`, `segmen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci
  COMMENT='Rumus debit rating curve Q=a*(MA+b)^c per segmen AWLR';

-- --------------------------------------------------------
-- 2. Insert data rumus 5 stasiun AWLR
-- --------------------------------------------------------

-- -------------------------------------------------------
-- 2.1 AWLR Ngrancah — ID Logger: 10358
--     Domain MA valid: -0.5 m ≤ MA ≤ 3 m
--     3 segmen
-- -------------------------------------------------------
INSERT INTO `rumus_rating_curve`
  (`id_logger`, `nama_stasiun`, `domain_min`, `domain_max`, `segmen`, `ma_min`, `ma_max`, `koef_a`, `koef_b`, `koef_c`, `sumber_penurunan`, `periode_kalibrasi`)
VALUES
  ('10358', 'AWLR Ngrancah',   -0.5, 3.0, 1, -0.5, 0.1, 0.336,   0.564,  2.6,   'Grafis-analitis', '2023-2025'),
  ('10358', 'AWLR Ngrancah',   -0.5, 3.0, 2,  0.1, 0.6, 6.183,   0.072,  2.212, 'Grafis-analitis', '2023-2025'),
  ('10358', 'AWLR Ngrancah',   -0.5, 3.0, 3,  0.6, 3.0, 9.586,  -0.141,  1.754, 'Grafis-analitis', '2023-2025');

-- -------------------------------------------------------
-- 2.2 AWLR Madurejo — ID Logger: 10346
--     Domain MA valid: -0.7 m ≤ MA ≤ 5 m
--     3 segmen
-- -------------------------------------------------------
INSERT INTO `rumus_rating_curve`
  (`id_logger`, `nama_stasiun`, `domain_min`, `domain_max`, `segmen`, `ma_min`, `ma_max`, `koef_a`, `koef_b`, `koef_c`, `sumber_penurunan`, `periode_kalibrasi`)
VALUES
  ('10346', 'AWLR Madurejo',   -0.7, 5.0, 1, -0.7, 0.0, 6.38,    1.032,  2.591, 'Grafis-analitis', '2023-2025'),
  ('10346', 'AWLR Madurejo',   -0.7, 5.0, 2,  0.0, 3.2, 10.983,  0.759,  1.768, 'Grafis-analitis', '2023-2025'),
  ('10346', 'AWLR Madurejo',   -0.7, 5.0, 3,  3.2, 5.0, 1.698,   2.377,  2.5,   'Grafis-analitis', '2023-2025');

-- -------------------------------------------------------
-- 2.3 AWLR Karangtalun — ID Logger: 10044
--     Domain MA valid: -0.2 m ≤ MA ≤ 2.2 m
--     1 segmen
-- -------------------------------------------------------
INSERT INTO `rumus_rating_curve`
  (`id_logger`, `nama_stasiun`, `domain_min`, `domain_max`, `segmen`, `ma_min`, `ma_max`, `koef_a`, `koef_b`, `koef_c`, `sumber_penurunan`, `periode_kalibrasi`)
VALUES
  ('10044', 'AWLR Karangtalun', -0.2, 2.2, 1, -0.2, 2.2, 113.822, 0.274,  1.822, 'Grafis-analitis', '2023-2025');

-- -------------------------------------------------------
-- 2.4 AWLR Slinga — ID Logger: 10118
--     Domain MA valid: 0 m ≤ MA ≤ 5 m
--     1 segmen
-- -------------------------------------------------------
INSERT INTO `rumus_rating_curve`
  (`id_logger`, `nama_stasiun`, `domain_min`, `domain_max`, `segmen`, `ma_min`, `ma_max`, `koef_a`, `koef_b`, `koef_c`, `sumber_penurunan`, `periode_kalibrasi`)
VALUES
  ('10118', 'AWLR Slinga',      0.0, 5.0, 1,  0.0, 5.0, 181.751, 0.009,  1.508, 'Grafis-analitis', '2023-2025');

-- -------------------------------------------------------
-- 2.5 AWLR Gombong — ID Logger: 10052
--     Domain MA valid: 0.3 m ≤ MA ≤ 3.2 m
--     2 segmen
-- -------------------------------------------------------
INSERT INTO `rumus_rating_curve`
  (`id_logger`, `nama_stasiun`, `domain_min`, `domain_max`, `segmen`, `ma_min`, `ma_max`, `koef_a`, `koef_b`, `koef_c`, `sumber_penurunan`, `periode_kalibrasi`)
VALUES
  ('10052', 'AWLR Gombong',     0.3, 3.2, 1,  0.3, 1.4, 3.791,  -0.23,   2.264, 'Grafis-analitis', '2023-2025'),
  ('10052', 'AWLR Gombong',     0.3, 3.2, 2,  1.4, 3.2, 7.668,  -0.575,  1.834, 'Grafis-analitis', '2023-2025');

-- =========================================================
-- 3. Update / Insert parameter_sensor: flag debit_awlr = '1'
--    untuk 5 stasiun AWLR agar controller menghitung debit
--    dari nilai MA (kolom sensor1)
-- =========================================================

-- 3a. Jika logger sudah punya parameter "Debit", pastikan debit_awlr = 1
UPDATE `parameter_sensor`
  SET `debit_awlr` = '1'
  WHERE `logger_id` IN ('10358', '10346', '10044', '10118', '10052')
    AND `nama_parameter` = 'Debit';

-- 3b. Jika logger BELUM punya parameter "Debit", tambahkan
--     Gunakan INSERT ... SELECT ... WHERE NOT EXISTS agar aman dijalankan berulang
--     kolom_sensor = 'sensor1' karena AWLR menyimpan TMA di sensor1

-- AWLR Ngrancah (10358)
INSERT INTO `parameter_sensor` (`logger_id`, `nama_parameter`, `kolom_sensor`, `satuan`, `tipe_graf`, `icon_app`, `debit_awlr`, `parameter_utama`)
SELECT '10358', 'Debit', 'sensor1', 'm3/det', 'spline', 'debit', '1', '0'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `parameter_sensor` WHERE `logger_id` = '10358' AND `nama_parameter` = 'Debit'
);

-- AWLR Madurejo (10346)
INSERT INTO `parameter_sensor` (`logger_id`, `nama_parameter`, `kolom_sensor`, `satuan`, `tipe_graf`, `icon_app`, `debit_awlr`, `parameter_utama`)
SELECT '10346', 'Debit', 'sensor1', 'm3/det', 'spline', 'debit', '1', '0'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `parameter_sensor` WHERE `logger_id` = '10346' AND `nama_parameter` = 'Debit'
);

-- AWLR Karangtalun (10044)
INSERT INTO `parameter_sensor` (`logger_id`, `nama_parameter`, `kolom_sensor`, `satuan`, `tipe_graf`, `icon_app`, `debit_awlr`, `parameter_utama`)
SELECT '10044', 'Debit', 'sensor1', 'm3/det', 'spline', 'debit', '1', '0'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `parameter_sensor` WHERE `logger_id` = '10044' AND `nama_parameter` = 'Debit'
);

-- AWLR Slinga (10118)
INSERT INTO `parameter_sensor` (`logger_id`, `nama_parameter`, `kolom_sensor`, `satuan`, `tipe_graf`, `icon_app`, `debit_awlr`, `parameter_utama`)
SELECT '10118', 'Debit', 'sensor1', 'm3/det', 'spline', 'debit', '1', '0'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `parameter_sensor` WHERE `logger_id` = '10118' AND `nama_parameter` = 'Debit'
);

-- AWLR Gombong (10052)
INSERT INTO `parameter_sensor` (`logger_id`, `nama_parameter`, `kolom_sensor`, `satuan`, `tipe_graf`, `icon_app`, `debit_awlr`, `parameter_utama`)
SELECT '10052', 'Debit', 'sensor1', 'm3/det', 'spline', 'debit', '1', '0'
FROM DUAL
WHERE NOT EXISTS (
  SELECT 1 FROM `parameter_sensor` WHERE `logger_id` = '10052' AND `nama_parameter` = 'Debit'
);

-- =========================================================
-- Verifikasi: cek data yang sudah masuk
-- =========================================================
-- SELECT id_logger, nama_stasiun, segmen,
--        CONCAT('Q = ', koef_a, ' * (MA + ', koef_b, ')^', koef_c) AS rumus,
--        CONCAT(ma_min, ' <= MA <= ', ma_max) AS rentang
-- FROM rumus_rating_curve
-- ORDER BY id_logger, segmen;

-- Cek parameter debit yang sudah terdaftar:
-- SELECT id_param, logger_id, nama_parameter, kolom_sensor, satuan, debit_awlr
-- FROM parameter_sensor
-- WHERE logger_id IN ('10358', '10346', '10044', '10118', '10052')
--   AND debit_awlr = '1';
