-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 15 Jul 2026 pada 03.41
-- Versi server: 10.6.23-MariaDB-0ubuntu0.22.04.1
-- Versi PHP: 8.2.32

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `motorcyclemanagement`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `trips`
--

CREATE TABLE `trips` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `device_id` varchar(100) DEFAULT NULL,
  `started_by` bigint(20) UNSIGNED NOT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `distance_meters` int(10) UNSIGNED DEFAULT NULL,
  `start_odometer` int(11) DEFAULT NULL,
  `end_odometer` int(11) DEFAULT NULL,
  `avg_speed_kph` decimal(8,2) DEFAULT NULL,
  `max_speed_kph` decimal(8,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `source` enum('gps','manual') NOT NULL DEFAULT 'gps' COMMENT 'Asal data perjalanan: GPS otomatis atau input manual',
  `kondisi_lalu_lintas` enum('macet','sedang','lancar') DEFAULT NULL COMMENT 'Kondisi lalu lintas: auto dari avg_speed, bisa di-override',
  `medan` enum('datar','berbukit','campuran') DEFAULT NULL COMMENT 'Medan jalan: auto dari elevation_gain, bisa di-override',
  `gaya_berkendara` enum('pelan','normal','agresif') DEFAULT NULL COMMENT 'Gaya berkendara: auto dari avg_speed, bisa di-override',
  `beban` enum('ringan','sedang','berat') DEFAULT NULL COMMENT 'Beban bawaan saat perjalanan (input user)',
  `ada_penumpang` tinyint(1) DEFAULT NULL COMMENT 'Apakah membawa penumpang saat perjalanan',
  `elevation_gain` int(10) UNSIGNED DEFAULT NULL COMMENT 'Total kenaikan elevasi dalam meter (dari GPS/barometer)',
  `idle_time_minutes` int(10) UNSIGNED DEFAULT NULL COMMENT 'Total waktu berhenti (speed=0) dalam menit',
  `rough_road_count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah deteksi jalan rusak/berlubang dari accelerometer',
  `hard_acceleration_count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah deteksi akselerasi kasar dari accelerometer',
  `hard_braking_count` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Jumlah deteksi pengereman keras dari accelerometer',
  `is_calibrated` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'True jika user sudah mengedit kondisi perjalanan secara manual',
  `service_score_factor` decimal(5,2) NOT NULL DEFAULT 1.00 COMMENT 'Bobot akhir perjalanan ini terhadap kalkulasi jadwal service',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `trips`
--

INSERT INTO `trips` (`id`, `vehicle_id`, `status`, `device_id`, `started_by`, `start_at`, `end_at`, `duration_minutes`, `distance_meters`, `start_odometer`, `end_odometer`, `avg_speed_kph`, `max_speed_kph`, `notes`, `source`, `kondisi_lalu_lintas`, `medan`, `gaya_berkendara`, `beban`, `ada_penumpang`, `elevation_gain`, `idle_time_minutes`, `rough_road_count`, `hard_acceleration_count`, `hard_braking_count`, `is_calibrated`, `service_score_factor`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'completed', NULL, 7, '2026-07-14 08:20:04', '2026-07-14 08:21:26', 1, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 08:20:04', '2026-07-14 08:21:26', NULL),
(2, 2, 'completed', NULL, 7, '2026-07-14 08:55:49', '2026-07-14 08:56:56', 1, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 08:55:49', '2026-07-14 08:56:56', NULL),
(3, 2, 'completed', NULL, 7, '2026-07-14 08:58:14', '2026-07-14 08:59:28', 1, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 08:58:14', '2026-07-14 08:59:28', NULL),
(4, 2, 'completed', NULL, 7, '2026-07-14 09:03:38', '2026-07-14 09:04:42', 1, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 09:03:38', '2026-07-14 09:04:42', NULL),
(5, 2, 'completed', NULL, 7, '2026-07-14 09:54:03', '2026-07-14 09:55:11', 1, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 09:54:03', '2026-07-14 09:55:11', NULL),
(6, 2, 'completed', NULL, 7, '2026-07-14 09:57:54', '2026-07-14 09:58:12', 0, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 09:57:54', '2026-07-14 09:58:12', NULL),
(7, 2, 'completed', NULL, 7, '2026-07-14 10:05:22', '2026-07-14 10:05:33', 0, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 10:05:22', '2026-07-14 10:05:33', NULL),
(8, 2, 'completed', NULL, 7, '2026-07-14 16:13:14', '2026-07-14 16:13:51', 1, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 16:13:14', '2026-07-14 16:13:51', NULL),
(9, 2, 'completed', NULL, 7, '2026-07-14 16:14:29', '2026-07-14 16:16:44', 2, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 16:14:29', '2026-07-14 16:16:44', NULL),
(10, 2, 'completed', NULL, 7, '2026-07-14 16:17:07', '2026-07-14 18:26:09', 129, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 16:17:07', '2026-07-14 18:26:09', NULL),
(11, 2, 'completed', NULL, 7, '2026-07-14 18:43:24', '2026-07-14 18:43:40', 0, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 18:43:24', '2026-07-14 18:43:40', NULL),
(12, 2, 'completed', NULL, 7, '2026-07-14 18:54:32', '2026-07-14 18:54:41', 0, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 18:54:32', '2026-07-14 18:54:41', NULL),
(13, 2, 'completed', NULL, 7, '2026-07-14 18:55:22', '2026-07-14 18:55:44', 0, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 18:55:22', '2026-07-14 18:55:44', NULL),
(14, 2, 'completed', NULL, 7, '2026-07-14 18:55:58', '2026-07-14 18:56:13', 0, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 18:55:58', '2026-07-14 18:56:13', NULL),
(15, 2, 'completed', NULL, 7, '2026-07-14 18:56:42', '2026-07-14 18:56:56', 0, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 18:56:42', '2026-07-14 18:56:56', NULL),
(16, 2, 'completed', NULL, 7, '2026-07-14 19:17:59', '2026-07-14 19:18:13', 0, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 19:17:59', '2026-07-14 19:18:13', NULL),
(17, 2, 'completed', NULL, 7, '2026-07-14 19:18:47', '2026-07-14 19:19:00', 0, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 19:18:47', '2026-07-14 19:19:00', NULL),
(18, 2, 'completed', NULL, 7, '2026-07-14 19:19:15', '2026-07-14 19:19:46', 1, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 19:19:15', '2026-07-14 19:19:46', NULL),
(19, 2, 'completed', NULL, 7, '2026-07-14 19:20:04', '2026-07-14 19:20:18', 0, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-14 19:20:04', '2026-07-14 19:20:18', NULL),
(20, 2, 'completed', NULL, 7, '2026-07-15 03:26:46', '2026-07-15 03:26:51', 0, 0, 1975, 1975, NULL, NULL, NULL, 'gps', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 0, 0, '1.00', '2026-07-15 03:26:46', '2026-07-15 03:26:51', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trips_vehicle_id_start_at_index` (`vehicle_id`,`start_at`),
  ADD KEY `trips_started_by_index` (`started_by`),
  ADD KEY `trips_device_id_index` (`device_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `trips`
--
ALTER TABLE `trips`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `trips_started_by_foreign` FOREIGN KEY (`started_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trips_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
