-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 23, 2025 at 07:49 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.5.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `training-system`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` tinyint(4) DEFAULT NULL,
  `status` enum('active','inactive','banned') NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `status`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'moha Admin', 'Moha@admins.com', NULL, '$2y$12$0u5.obglTXxC6IrKumFE9e//iz0HYLLmlExrIxApSJ/Rvi3RXN5DO', 1, 'active', NULL, '2025-12-23 04:50:38', '2025-12-23 04:50:38', NULL),
(2, 'System Admin', 'admin@test.com', NULL, '$2y$12$3Tb4SViabnQ30TWZOO7TeuK4oiBYpHXsXRraNWlEM7fkfrRGjHre.', 1, 'active', NULL, '2025-12-23 04:50:43', '2025-12-23 04:50:43', NULL),
(3, 'Department Head', 'dept@test.com', NULL, '$2y$12$3Tb4SViabnQ30TWZOO7TeuK4oiBYpHXsXRraNWlEM7fkfrRGjHre.', 2, 'active', NULL, '2025-12-23 04:50:43', '2025-12-23 04:50:43', NULL),
(4, 'Section Head', 'section@test.com', NULL, '$2y$12$3Tb4SViabnQ30TWZOO7TeuK4oiBYpHXsXRraNWlEM7fkfrRGjHre.', 3, 'active', NULL, '2025-12-23 04:50:43', '2025-12-23 04:50:43', NULL),
(5, 'Ministry User', 'moh@test.com', NULL, '$2y$12$3Tb4SViabnQ30TWZOO7TeuK4oiBYpHXsXRraNWlEM7fkfrRGjHre.', 4, 'active', NULL, '2025-12-23 04:50:43', '2025-12-23 04:50:43', NULL),
(6, 'College Supervisor', 'college@test.com', NULL, '$2y$12$3Tb4SViabnQ30TWZOO7TeuK4oiBYpHXsXRraNWlEM7fkfrRGjHre.', 5, 'active', NULL, '2025-12-23 04:50:43', '2025-12-23 04:50:43', NULL),
(7, 'Head Of Administration', 'hoa@test.com', NULL, '$2y$12$3Tb4SViabnQ30TWZOO7TeuK4oiBYpHXsXRraNWlEM7fkfrRGjHre.', 6, 'active', NULL, '2025-12-23 04:50:43', '2025-12-23 04:50:43', NULL),
(8, 'Head Of Medical', 'hom@test.com', NULL, '$2y$12$3Tb4SViabnQ30TWZOO7TeuK4oiBYpHXsXRraNWlEM7fkfrRGjHre.', 7, 'active', NULL, '2025-12-23 04:50:43', '2025-12-23 04:50:43', NULL),
(9, 'General Training Manager', 'gtm@test.com', NULL, '$2y$12$3Tb4SViabnQ30TWZOO7TeuK4oiBYpHXsXRraNWlEM7fkfrRGjHre.', 8, 'active', NULL, '2025-12-23 04:50:43', '2025-12-23 04:50:43', NULL),
(10, 'اثير المنيف', 'bilal.karam@example.net', '2025-12-23 04:50:44', '$2y$12$63NyQnkVoqyTVVZWaB5sN.un091po7OlsNRj4eRtaBHqwGkK9VKAC', 4, 'active', 'WZObexYcNF', '2025-12-23 04:50:45', '2025-12-23 04:50:45', NULL),
(11, 'الآنسة آلاء الشهيل', 'kanaan.mutaz@example.net', '2025-12-23 04:50:45', '$2y$12$IBnD35QjVU8Oao13ysvl7.WiCAUzvgHcXrCpr8j/U2QZ4m0yPTBme', 2, 'active', '5Ums44uZe4', '2025-12-23 04:50:45', '2025-12-23 04:50:45', NULL),
(12, 'نصار الجريد', 'abdullah.abbas@example.com', '2025-12-23 04:50:45', '$2y$12$0kkMpryUNkyWBIBPMit3G.84J7XJd./FM1fhPhZL0aGEr5VFzOct2', 4, 'active', '4Xh4xQTbhR', '2025-12-23 04:50:45', '2025-12-23 04:50:45', NULL),
(13, 'سيرين الفدا', 'hamad.fadi@example.org', '2025-12-23 04:50:45', '$2y$12$a68VZz.HMvyw4YwJE3m5tu2qaI4/nP6gKG71k6eXVnQM8DWdNVPvK', 4, 'active', 'Jn1EnzRoaq', '2025-12-23 04:50:45', '2025-12-23 04:50:45', NULL),
(14, 'همام الشيباني', 'xrabee@example.com', '2025-12-23 04:50:45', '$2y$12$YuYWwiJzzRKMtp7ngPZg0OHdnQpRQ9MyRDII8sgM7fKZgWwQ8EOlq', 4, 'active', '2lcdIclgXU', '2025-12-23 04:50:46', '2025-12-23 04:50:46', NULL),
(15, 'رحمه الفدا', 'bhadi@example.net', '2025-12-23 04:50:46', '$2y$12$uuP3G6jQvJqRjZVChb61Cu7sgpIWfNWGEaKk/R0ranfifBdP2uwz2', 2, 'active', '4n61fetCs5', '2025-12-23 04:50:46', '2025-12-23 04:50:46', NULL),
(16, 'الدكتورة راوية السماعيل', 'khaled39@example.net', '2025-12-23 04:50:46', '$2y$12$RNvceKH42dDSrXTD0sglSO/Hrm699USndzgj5kPG7L6KjgzS3IEt6', 4, 'active', 'IAS4tJEpjM', '2025-12-23 04:50:47', '2025-12-23 04:50:47', NULL),
(17, 'غدير الصقير', 'gkaram@example.net', '2025-12-23 04:50:47', '$2y$12$xaxaTg8Ur1YdhVEXunmftOrqMtv8lVoPAYks42M.C8zPqUJ8Dlv2W', 4, 'active', 'Ms7Dr4Jqyq', '2025-12-23 04:50:47', '2025-12-23 04:50:47', NULL),
(18, 'الدكتورة وسام السليم', 'omar.rabee@example.net', '2025-12-23 04:50:47', '$2y$12$BL5mYDrAKKXFmb0hZ9ZbuuC0EbyOKju0LAGqK1wCjWl2FIPGCG5u2', 4, 'active', 'pDMikI6scb', '2025-12-23 04:50:47', '2025-12-23 04:50:47', NULL),
(19, 'السيدة نادين برماوي', 'omar22@example.net', '2025-12-23 04:50:47', '$2y$12$4WtdXzbWDWI0NxoIWzVH4elzwNDhkH8/mrMONoXTxx6IE/El.QTT2', 4, 'active', 'c06PI0Bb6g', '2025-12-23 04:50:47', '2025-12-23 04:50:47', NULL),
(20, 'السيدة إسراء الشهيل', 'flefel.osama@example.org', '2025-12-23 04:50:47', '$2y$12$O5FQY10B.CIFL4pvIwI5g.o2P/Wvpthc9PpTDCpI9H5WDzIq541qO', 4, 'active', 'CTUrAXT6cY', '2025-12-23 04:50:48', '2025-12-23 04:50:48', NULL),
(21, 'ورود المنيف', 'ahmad90@example.com', '2025-12-23 04:50:48', '$2y$12$f5GVddIZBKL0W/TPuXmct.O/H2f1sVKbjxYkniVLNdroi6PzQG0va', 4, 'active', 'fuTLRVY4TR', '2025-12-23 04:50:48', '2025-12-23 04:50:48', NULL),
(22, 'الآنسة نسرين المشيقح', 'abd.abulebbeh@example.org', '2025-12-23 04:50:48', '$2y$12$CghWh/3/JzbNgV2OXI9sIeUkM/mMqbam1yu1PLx/Ga/icGfefuxru', 4, 'active', 'Qo3o70S5T5', '2025-12-23 04:50:48', '2025-12-23 04:50:48', NULL),
(23, 'حصة الفرحان', 'hamad.bilal@example.com', '2025-12-23 04:50:48', '$2y$12$YU/jYNZmZLntrtNFDatE..m7XF/B98WtLMFTOwvWCu9oKC0uJd8hG', 4, 'active', 'bAMmAr5iJd', '2025-12-23 04:50:49', '2025-12-23 04:50:49', NULL),
(24, 'مسعدة نزار نقولا الفيفي', 'abbas.bilal@example.net', '2025-12-23 04:50:49', '$2y$12$sMmDyJIzsRca/et0Mv5GyuZGyMFCYdysEnCi8jzzgoQkK5m6aP8hK', 4, 'active', 'jvt6Bn1GLa', '2025-12-23 04:50:49', '2025-12-23 04:50:49', NULL),
(25, 'روزانا السماري', 'gkanaan@example.org', '2025-12-23 04:50:49', '$2y$12$hoVYER4pRbvYJTWHwBFXmethIoSKjsjjwkKe7Sp3pfDw2UqyQ.SwG', 4, 'active', 'x2lWYt1pxl', '2025-12-23 04:50:49', '2025-12-23 04:50:49', NULL),
(26, 'عنان عبد السميع مالك الأسمري', 'gabbad@example.net', '2025-12-23 04:50:49', '$2y$12$PVnZaeyS.uMp..QAoM7Xj.g.y/yhF5oHHN4eMeALljinakwFQv7hW', 4, 'active', 'BmAQxLImYL', '2025-12-23 04:50:50', '2025-12-23 04:50:50', NULL),
(27, 'هنا هوساوي', 'nimry.rami@example.net', '2025-12-23 04:50:50', '$2y$12$FNdDPRBxRH4UEMZGmPZkrOqUwSb6PUlLugZ6wqVLfZAG7hpEG6BRC', 4, 'active', 'avkctZhNW2', '2025-12-23 04:50:50', '2025-12-23 04:50:50', NULL),
(28, 'أفنان السماعيل', 'hasan.yazan@example.net', '2025-12-23 04:50:50', '$2y$12$WQvm5/Cy4maGYjxPrZjBAOngkrZX7m0uuqLBSLxZI9CsF.Cptktvi', 4, 'active', 'WxnMGcmoQg', '2025-12-23 04:50:50', '2025-12-23 04:50:50', NULL),
(29, 'ميسم نايف منير الخالدي', 'abbas.akram@example.net', '2025-12-23 04:50:50', '$2y$12$XrKy4hzDQ09lVQrTq4nMB.aXqJXDSkPCLxOAWaZfx02xIsIVGOWty', 4, 'active', 'a90rQcVvjz', '2025-12-23 04:50:50', '2025-12-23 04:50:50', NULL),
(30, 'ايمن بسام الحصين', 'whadi@example.com', '2025-12-23 04:50:50', '$2y$12$oZs56CKY.8mNtSr7yZ.86Oav4NdBk/wp8xS17bFcFqs4C9hBxwAUG', 4, 'active', 'p2OEVYsFeJ', '2025-12-23 04:50:51', '2025-12-23 04:50:51', NULL),
(31, 'الدكتورة حبيبة الزامل', 'jabbad@example.net', '2025-12-23 04:50:51', '$2y$12$RBMHo8cJxi3hkEcsGJuZGeRgO5D9NAnEUSHtkJy3veEM5vumfsHUy', 4, 'active', 'kT87H50TqG', '2025-12-23 04:50:51', '2025-12-23 04:50:51', NULL),
(32, 'رامي وهيب المقبل', 'bashar.abbas@example.org', '2025-12-23 04:50:51', '$2y$12$XkOJDeusmjZ/tboQx41o2eMglXzDe/b6tej4aiUdBIkMLla1vdI8q', 4, 'active', 'FNquJI2Jgh', '2025-12-23 04:50:51', '2025-12-23 04:50:51', NULL),
(33, 'ميران الجهني', 'rami31@example.com', '2025-12-23 04:50:51', '$2y$12$gwOS7BbAb4hqj3KyBU.aluhKdpG1DhdUKcCZ5nxlmpj8p8oWnmd0a', 4, 'active', '5USSYEoTH5', '2025-12-23 04:50:52', '2025-12-23 04:50:52', NULL),
(34, 'المهندسة سماح الجهني', 'tzaloum@example.org', '2025-12-23 04:50:52', '$2y$12$pY.hrD6z6n4ktgyIe/6BQ.pXdyGdTRueKX6hbf13iFme6bG7wBwN.', 4, 'active', 'vv4KWVt6N0', '2025-12-23 04:50:52', '2025-12-23 04:50:52', NULL),
(35, 'إكرام جمزه مدني', 'nimry.akram@example.com', '2025-12-23 04:50:52', '$2y$12$Hh5M8QIfH6BoU2JG3W5cruQ/Q2hFJ.aHqeTBPtjIqEMUZUFg3l.GK', 4, 'active', 'zq5Xhuz1BK', '2025-12-23 04:50:52', '2025-12-23 04:50:52', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
