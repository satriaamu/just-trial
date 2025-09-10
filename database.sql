-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 19, 2025 at 10:30 PM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u258463114_mokobang`
--

-- --------------------------------------------------------

--
-- Table structure for table `katalog`
--

CREATE TABLE `katalog` (
  `id` int(11) NOT NULL,
  `tipe` varchar(255) DEFAULT NULL,
  `harga` decimal(15,2) DEFAULT NULL,
  `bahan_utama` varchar(255) DEFAULT NULL,
  `struktur` varchar(255) DEFAULT NULL,
  `konstruksi` varchar(255) DEFAULT NULL,
  `rangka_atap` varchar(255) DEFAULT NULL,
  `lantai_dinding` varchar(255) DEFAULT NULL,
  `jumlah_kamar` varchar(50) DEFAULT NULL,
  `teras_depan` varchar(255) DEFAULT NULL,
  `ventilasi_jendela` varchar(255) DEFAULT NULL,
  `pengerjaan` varchar(255) DEFAULT NULL,
  `nomor_kontak` varchar(50) DEFAULT NULL,
  `gambar` text DEFAULT NULL,
  `fitur_tambahan` text DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `katalog`
--

INSERT INTO `katalog` (`id`, `tipe`, `harga`, `bahan_utama`, `struktur`, `konstruksi`, `rangka_atap`, `lantai_dinding`, `jumlah_kamar`, `teras_depan`, `ventilasi_jendela`, `pengerjaan`, `nomor_kontak`, `gambar`, `fitur_tambahan`, `deskripsi`, `created_at`, `updated_at`) VALUES
(2, 'B (Ukuran 6 x 9 meter)', 60000000.00, 'Kayu kelapa/nantu pilihan', 'Rumah panggung dengan pondasi kayu', 'Bongkar-pasang (knock down)', 'Kayu, atap seng gelombang', 'Kayu papan tebal', '2 kamar tidur + 1 ruang utama', 'Ya', 'Lengkap, kayu & kaca', '±14–21 hari sejak pemesanan', '+6285299718294', '[\"uploads\\/WhatsApp Image 2025-06-16 at 23.28.38 (1).jpeg\",\"uploads\\/WhatsApp Image 2025-06-16 at 23.28.38.jpeg\",\"uploads\\/WhatsApp Image 2025-06-16 at 23.28.40.jpeg\"]', '• Atap sirap kayu (tambahan biaya)\r\n• Finishing cat kayu (custom)\r\n• Pengiriman dan instalasi ke luar Sulawesi\r\n', 'Rumah panggung kayu khas Minahasa dengan desain tradisional, cocok untuk rumah tinggal, vila, homestay, maupun bangunan pendukung wisata. Dibuat oleh pengrajin profesional dari Desa Mokobang, rumah ini dirancang kokoh, bernilai estetika tinggi, dan siap bongkar pasang (sistem knock down).', '2025-06-19 08:24:32', '2025-06-19 22:07:18'),
(3, 'A (Ukuran 7 x 9 meter)', 80000000.00, 'Kayu cempaka/nantu pilihan', 'Rumah panggung dengan pondasi kayu', 'Bongkar-pasang (knock down)', 'Kayu, atap seng gelombang', 'Kayu papan tebal', '3 kamar tidur + 1 ruang utama', 'Ya', 'Lengkap, kayu & kaca', '±14–21 hari sejak pemesanan', '+6281234567890', '[\"uploads\\/WhatsApp Image 2025-06-16 at 23.21.10 (1).jpeg\",\"uploads\\/WhatsApp Image 2025-06-16 at 23.21.10.jpeg\",\"uploads\\/WhatsApp Image 2025-06-16 at 23.21.14.jpeg\"]', '• Atap sirap kayu (tambahan biaya)\r\n• Finishing cat kayu (custom)\r\n• Pengiriman dan instalasi ke luar Sulawesi\r\n', 'Rumah panggung ini merupakan hasil karya pengrajin lokal Desa Mokobang, dirancang dengan perpaduan antara kekuatan konstruksi kayu dan nilai estetika budaya Minahasa. Dibuat dari kayu kelapa/nantu pilihan yang tahan lama dan sudah melalui proses pengeringan alami.', '2025-06-19 18:59:27', '2025-06-19 18:59:27'),
(4, '±6 x 9 meter', 65000000.00, 'Kayu cempaka/nantu pilihan', 'Rumah panggung bertingkat, atap melengkung khas', 'Bongkar-pasang (knock down)', 'Seng gelombang dengan rangka kayu', 'Papan kayu finishing halus', '2 kamar tidur', 'Luas dengan pagar kayu ukiran tradisional', 'Jendela kayu dan kisi angin di sekeliling rumah', '?', '?', '[\"uploads\\/WhatsApp Image 2025-06-16 at 23.30.54.jpeg\",\"uploads\\/WhatsApp Image 2025-06-16 at 23.30.55 (1).jpeg\",\"uploads\\/WhatsApp Image 2025-06-16 at 23.30.55.jpeg\"]', '• Desain menarik dengan perpaduan tradisi dan modern\r\n• Sistem bongkar pasang (knock down), praktis untuk pengiriman luar daerah\r\n• Dikerjakan oleh pengrajin profesional dari Desa Mokobang\r\n• Nyaman, sejuk, dan ideal untuk berbagai kebutuhan hunian\r\n', 'Rumah panggung ini mengusung desain tradisional Minahasa yang dipadukan dengan sentuhan modern. Dibuat langsung oleh pengrajin Desa Mokobang, rumah ini cocok sebagai hunian utama maupun vila keluarga. Dengan dua kamar tidur, rumah ini dirancang untuk kenyamanan dan efisiensi ruang.', '2025-06-19 20:55:17', '2025-06-19 20:55:17');

-- --------------------------------------------------------

--
-- Table structure for table `pesan`
--

CREATE TABLE `pesan` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `pesan` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pesan`
--

INSERT INTO `pesan` (`id`, `nama`, `email`, `pesan`, `created_at`, `updated_at`) VALUES
(9, 'dummy1', 'dummy1@gmail.com', 'Oke', '2025-06-17 14:31:55', '2025-06-19 19:07:35'),
(10, 'dummy2', 'dummy2@gmail.com', 'Iya', '2025-06-17 14:32:08', '2025-06-19 19:07:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(2, 'ivena', 'ivenamanembu317@gmail.com', '$2y$10$7d/fbkEQkLnA3kgLZn.uduAsNCoHLUk4d1r4THKH.S8thdQ.Arwvi', '2025-06-04 09:47:24'),
(3, 'Revier', 'revier1234567@gmail.com', '$2y$10$P4theM4CwjkXJGRkiKGuouwEbtsq3lZt3jVKL9U/hWvhgt/YstQ.K', '2025-06-12 23:29:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `katalog`
--
ALTER TABLE `katalog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pesan`
--
ALTER TABLE `pesan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `katalog`
--
ALTER TABLE `katalog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pesan`
--
ALTER TABLE `pesan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
