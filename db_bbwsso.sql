-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 27, 2026 at 10:26 AM
-- Server version: 11.4.10-MariaDB-log
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_bbwsso`
--

-- --------------------------------------------------------

--
-- Table structure for table `afmr`
--

CREATE TABLE `afmr` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL,
  `sensor17` float NOT NULL,
  `sensor18` float NOT NULL,
  `sensor19` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `arr`
--

CREATE TABLE `arr` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL,
  `sensor17` float NOT NULL DEFAULT 0,
  `sensor18` float NOT NULL DEFAULT 0,
  `sensor19` float NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `arr2`
--

CREATE TABLE `arr2` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL,
  `sensor17` float NOT NULL DEFAULT 0,
  `sensor18` float NOT NULL DEFAULT 0,
  `sensor19` float NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `arr3`
--

CREATE TABLE `arr3` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL,
  `sensor17` float NOT NULL,
  `sensor18` float NOT NULL,
  `sensor19` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `awlr`
--

CREATE TABLE `awlr` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL,
  `sensor17` float DEFAULT 0,
  `sensor18` float DEFAULT 0,
  `sensor19` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `awlr2`
--

CREATE TABLE `awlr2` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `awlr3`
--

CREATE TABLE `awlr3` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `awlr4`
--

CREATE TABLE `awlr4` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL,
  `sensor17` float NOT NULL,
  `sensor18` float NOT NULL,
  `sensor19` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `awlr5`
--

CREATE TABLE `awlr5` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL,
  `sensor17` float NOT NULL,
  `sensor18` float NOT NULL,
  `sensor19` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `awr`
--

CREATE TABLE `awr` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

CREATE TABLE `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `filter`
--

CREATE TABLE `filter` (
  `id` int(11) NOT NULL,
  `id_kategori` int(11) NOT NULL,
  `nama_filter` varchar(225) NOT NULL,
  `icon` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `foto_pos`
--

CREATE TABLE `foto_pos` (
  `id` int(11) NOT NULL,
  `id_logger` varchar(20) NOT NULL,
  `url_foto` text NOT NULL,
  `foto_utama` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ipcam`
--

CREATE TABLE `ipcam` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kategori_logger`
--

CREATE TABLE `kategori_logger` (
  `id_katlogger` int(5) NOT NULL,
  `nama_kategori` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `controller` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `tabel` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kepanjangan` text NOT NULL,
  `temp_data` varchar(20) NOT NULL,
  `icon_app` varchar(25) NOT NULL,
  `view` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klasifikasi_hujan`
--

CREATE TABLE `klasifikasi_hujan` (
  `id_klasifikasi` int(11) NOT NULL,
  `waktuper` varchar(10) NOT NULL,
  `hijau` int(11) NOT NULL,
  `biru` varchar(5) NOT NULL,
  `biru_tua` int(11) NOT NULL,
  `kuning` int(11) NOT NULL,
  `oranye` int(11) NOT NULL,
  `merah` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `list_das`
--

CREATE TABLE `list_das` (
  `id` int(11) NOT NULL,
  `nama_das` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrasi`
--

CREATE TABLE `migrasi` (
  `id` int(11) NOT NULL,
  `id_logger` varchar(15) NOT NULL,
  `bulan` varchar(20) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int(11) NOT NULL,
  `id_logger` int(11) NOT NULL,
  `id_tingkat_siaga` int(11) NOT NULL,
  `tma` double NOT NULL,
  `datetime` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parameter_sensor`
--

CREATE TABLE `parameter_sensor` (
  `id_param` int(10) NOT NULL,
  `logger_id` varchar(15) NOT NULL,
  `nama_parameter` varchar(25) NOT NULL,
  `kolom_sensor` varchar(20) NOT NULL,
  `satuan` varchar(15) NOT NULL,
  `tipe_graf` varchar(20) NOT NULL,
  `icon_app` varchar(20) NOT NULL,
  `debit_awlr` varchar(15) NOT NULL,
  `parameter_utama` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `riset`
--

CREATE TABLE `riset` (
  `no` int(11) NOT NULL,
  `jwt` text NOT NULL,
  `waktu` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rumus_debit`
--

CREATE TABLE `rumus_debit` (
  `id` int(11) NOT NULL,
  `id_logger` varchar(11) NOT NULL,
  `rumus` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `set_sinkronisasi`
--

CREATE TABLE `set_sinkronisasi` (
  `id` int(10) NOT NULL,
  `idlogger` varchar(15) NOT NULL,
  `tanggal` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `temp_afmr`
--

CREATE TABLE `temp_afmr` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL,
  `sensor17` float NOT NULL,
  `sensor18` float NOT NULL,
  `sensor19` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `temp_arr`
--

CREATE TABLE `temp_arr` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL,
  `sensor17` float NOT NULL,
  `sensor18` float NOT NULL,
  `sensor19` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `temp_awlr`
--

CREATE TABLE `temp_awlr` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL,
  `sensor17` float DEFAULT 0,
  `sensor18` float DEFAULT 0,
  `sensor19` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `temp_awr`
--

CREATE TABLE `temp_awr` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tes_data`
--

CREATE TABLE `tes_data` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL,
  `status_anomali` varchar(11) NOT NULL,
  `last_send` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tes_notif`
--

CREATE TABLE `tes_notif` (
  `id` int(11) NOT NULL,
  `status` varchar(225) NOT NULL,
  `waktu` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tingkat_siaga_awlr`
--

CREATE TABLE `tingkat_siaga_awlr` (
  `id` int(11) NOT NULL,
  `id_logger` varchar(15) NOT NULL,
  `id_status` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `nilai` float NOT NULL,
  `status` int(11) NOT NULL,
  `warna` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_demo`
--

CREATE TABLE `t_demo` (
  `id` int(15) NOT NULL,
  `code_logger` varchar(15) NOT NULL,
  `waktu` datetime NOT NULL,
  `sensor1` float NOT NULL,
  `sensor2` float NOT NULL,
  `sensor3` float NOT NULL,
  `sensor4` float NOT NULL,
  `sensor5` float NOT NULL,
  `sensor6` float NOT NULL,
  `sensor7` float NOT NULL,
  `sensor8` float NOT NULL,
  `sensor9` float NOT NULL,
  `sensor10` float NOT NULL,
  `sensor11` float NOT NULL,
  `sensor12` float NOT NULL,
  `sensor13` float NOT NULL,
  `sensor14` float NOT NULL,
  `sensor15` float NOT NULL,
  `sensor16` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_informasi`
--

CREATE TABLE `t_informasi` (
  `id_inf` int(5) NOT NULL,
  `logger_id` varchar(10) NOT NULL,
  `seri_logger` text NOT NULL,
  `sensor` text NOT NULL,
  `serial_number` varchar(25) NOT NULL,
  `elevasi` varchar(10) NOT NULL,
  `nosell` varchar(15) NOT NULL,
  `nama_pic` varchar(100) NOT NULL,
  `no_pic` varchar(100) NOT NULL,
  `tanggal_pemasangan` varchar(10) NOT NULL,
  `garansi` varchar(10) NOT NULL,
  `awal_kontrak` varchar(10) NOT NULL,
  `imei` varchar(20) NOT NULL,
  `gps1` varchar(30) NOT NULL,
  `gps2` varchar(30) NOT NULL,
  `gps3` varchar(30) NOT NULL,
  `ad` varchar(10) NOT NULL,
  `kd` varchar(10) NOT NULL,
  `mr` varchar(10) NOT NULL,
  `wdt` varchar(30) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_logger`
--

CREATE TABLE `t_logger` (
  `id` int(11) NOT NULL,
  `id_logger` varchar(10) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_logger` varchar(30) NOT NULL,
  `lokasi_logger` varchar(30) NOT NULL,
  `kategori_log` varchar(10) NOT NULL,
  `tabel_main` varchar(10) NOT NULL,
  `jeda_notif` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_lokasi`
--

CREATE TABLE `t_lokasi` (
  `idlokasi` int(5) NOT NULL,
  `nama_lokasi` varchar(40) NOT NULL,
  `latitude` varchar(20) NOT NULL,
  `longitude` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `das` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_manual`
--

CREATE TABLE `t_manual` (
  `id` int(11) NOT NULL,
  `id_logger` varchar(15) NOT NULL,
  `sensor1` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_perbaikan`
--

CREATE TABLE `t_perbaikan` (
  `id_perbaikan` int(11) NOT NULL,
  `id_logger` varchar(15) NOT NULL,
  `data_terakhir` text NOT NULL,
  `tabel` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_riwayat`
--

CREATE TABLE `t_riwayat` (
  `id_riwayat` int(11) NOT NULL,
  `id_logger` varchar(20) NOT NULL,
  `tanggal` date NOT NULL,
  `kendala` text NOT NULL,
  `perbaikan` text NOT NULL,
  `gambar` text NOT NULL,
  `file` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_user`
--

CREATE TABLE `t_user` (
  `id_user` int(5) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(32) NOT NULL,
  `level_user` varchar(20) NOT NULL,
  `alamat` text NOT NULL,
  `telp` varchar(25) NOT NULL,
  `instansi` varchar(50) DEFAULT NULL,
  `latitude` varchar(50) NOT NULL,
  `longitude` varchar(50) NOT NULL,
  `zoom` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `afmr`
--
ALTER TABLE `afmr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_logger` (`code_logger`),
  ADD KEY `waktu` (`waktu`);

--
-- Indexes for table `arr`
--
ALTER TABLE `arr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_logger` (`code_logger`),
  ADD KEY `waktu` (`waktu`);

--
-- Indexes for table `arr2`
--
ALTER TABLE `arr2`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_logger` (`code_logger`),
  ADD KEY `waktu` (`waktu`);

--
-- Indexes for table `arr3`
--
ALTER TABLE `arr3`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_logger` (`code_logger`),
  ADD KEY `waktu` (`waktu`);

--
-- Indexes for table `awlr`
--
ALTER TABLE `awlr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_logger` (`code_logger`),
  ADD KEY `waktu` (`waktu`);

--
-- Indexes for table `awlr2`
--
ALTER TABLE `awlr2`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_logger` (`code_logger`),
  ADD KEY `waktu` (`waktu`);

--
-- Indexes for table `awlr3`
--
ALTER TABLE `awlr3`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_logger` (`code_logger`),
  ADD KEY `waktu` (`waktu`);

--
-- Indexes for table `awlr4`
--
ALTER TABLE `awlr4`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_logger` (`code_logger`),
  ADD KEY `waktu` (`waktu`);

--
-- Indexes for table `awlr5`
--
ALTER TABLE `awlr5`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_logger` (`code_logger`),
  ADD KEY `waktu` (`waktu`);

--
-- Indexes for table `awr`
--
ALTER TABLE `awr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_logger` (`code_logger`),
  ADD KEY `waktu` (`waktu`);

--
-- Indexes for table `ci_sessions`
--
ALTER TABLE `ci_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ci_sessions_timestamp` (`timestamp`),
  ADD KEY `timestamp` (`timestamp`);

--
-- Indexes for table `filter`
--
ALTER TABLE `filter`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `foto_pos`
--
ALTER TABLE `foto_pos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ipcam`
--
ALTER TABLE `ipcam`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_logger` (`code_logger`),
  ADD KEY `waktu` (`waktu`);

--
-- Indexes for table `kategori_logger`
--
ALTER TABLE `kategori_logger`
  ADD PRIMARY KEY (`id_katlogger`);

--
-- Indexes for table `klasifikasi_hujan`
--
ALTER TABLE `klasifikasi_hujan`
  ADD PRIMARY KEY (`id_klasifikasi`);

--
-- Indexes for table `list_das`
--
ALTER TABLE `list_das`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrasi`
--
ALTER TABLE `migrasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `parameter_sensor`
--
ALTER TABLE `parameter_sensor`
  ADD PRIMARY KEY (`id_param`);

--
-- Indexes for table `riset`
--
ALTER TABLE `riset`
  ADD PRIMARY KEY (`no`);

--
-- Indexes for table `rumus_debit`
--
ALTER TABLE `rumus_debit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `set_sinkronisasi`
--
ALTER TABLE `set_sinkronisasi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp_afmr`
--
ALTER TABLE `temp_afmr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_logger` (`code_logger`),
  ADD KEY `waktu` (`waktu`);

--
-- Indexes for table `temp_arr`
--
ALTER TABLE `temp_arr`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `temp_awlr`
--
ALTER TABLE `temp_awlr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_logger` (`code_logger`),
  ADD KEY `waktu` (`waktu`);

--
-- Indexes for table `temp_awr`
--
ALTER TABLE `temp_awr`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tes_data`
--
ALTER TABLE `tes_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code_logger` (`code_logger`),
  ADD KEY `waktu` (`waktu`);

--
-- Indexes for table `tes_notif`
--
ALTER TABLE `tes_notif`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tingkat_siaga_awlr`
--
ALTER TABLE `tingkat_siaga_awlr`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_demo`
--
ALTER TABLE `t_demo`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_informasi`
--
ALTER TABLE `t_informasi`
  ADD PRIMARY KEY (`id_inf`),
  ADD KEY `id_inf` (`id_inf`);

--
-- Indexes for table `t_logger`
--
ALTER TABLE `t_logger`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_lokasi`
--
ALTER TABLE `t_lokasi`
  ADD PRIMARY KEY (`idlokasi`);

--
-- Indexes for table `t_manual`
--
ALTER TABLE `t_manual`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_perbaikan`
--
ALTER TABLE `t_perbaikan`
  ADD PRIMARY KEY (`id_perbaikan`);

--
-- Indexes for table `t_riwayat`
--
ALTER TABLE `t_riwayat`
  ADD PRIMARY KEY (`id_riwayat`);

--
-- Indexes for table `t_user`
--
ALTER TABLE `t_user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `afmr`
--
ALTER TABLE `afmr`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `arr`
--
ALTER TABLE `arr`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `arr2`
--
ALTER TABLE `arr2`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `arr3`
--
ALTER TABLE `arr3`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `awlr`
--
ALTER TABLE `awlr`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `awlr2`
--
ALTER TABLE `awlr2`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `awlr3`
--
ALTER TABLE `awlr3`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `awlr4`
--
ALTER TABLE `awlr4`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `awlr5`
--
ALTER TABLE `awlr5`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `awr`
--
ALTER TABLE `awr`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `filter`
--
ALTER TABLE `filter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `foto_pos`
--
ALTER TABLE `foto_pos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ipcam`
--
ALTER TABLE `ipcam`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori_logger`
--
ALTER TABLE `kategori_logger`
  MODIFY `id_katlogger` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `klasifikasi_hujan`
--
ALTER TABLE `klasifikasi_hujan`
  MODIFY `id_klasifikasi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `list_das`
--
ALTER TABLE `list_das`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrasi`
--
ALTER TABLE `migrasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `parameter_sensor`
--
ALTER TABLE `parameter_sensor`
  MODIFY `id_param` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `riset`
--
ALTER TABLE `riset`
  MODIFY `no` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rumus_debit`
--
ALTER TABLE `rumus_debit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `set_sinkronisasi`
--
ALTER TABLE `set_sinkronisasi`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temp_afmr`
--
ALTER TABLE `temp_afmr`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temp_arr`
--
ALTER TABLE `temp_arr`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temp_awlr`
--
ALTER TABLE `temp_awlr`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temp_awr`
--
ALTER TABLE `temp_awr`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tes_data`
--
ALTER TABLE `tes_data`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tes_notif`
--
ALTER TABLE `tes_notif`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tingkat_siaga_awlr`
--
ALTER TABLE `tingkat_siaga_awlr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_demo`
--
ALTER TABLE `t_demo`
  MODIFY `id` int(15) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_informasi`
--
ALTER TABLE `t_informasi`
  MODIFY `id_inf` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_logger`
--
ALTER TABLE `t_logger`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_lokasi`
--
ALTER TABLE `t_lokasi`
  MODIFY `idlokasi` int(5) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_manual`
--
ALTER TABLE `t_manual`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_perbaikan`
--
ALTER TABLE `t_perbaikan`
  MODIFY `id_perbaikan` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_riwayat`
--
ALTER TABLE `t_riwayat`
  MODIFY `id_riwayat` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
