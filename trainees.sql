-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 14, 2026 at 10:45 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.3.29

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
-- Table structure for table `trainees`
--

CREATE TABLE `trainees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `national_id` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `phone_number` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `governorate_id` bigint(20) UNSIGNED DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `institution_id` bigint(20) UNSIGNED DEFAULT NULL,
  `college_id` bigint(20) UNSIGNED DEFAULT NULL,
  `major_id` bigint(20) UNSIGNED DEFAULT NULL,
  `training_hours` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `trainees`
--

INSERT INTO `trainees` (`id`, `national_id`, `full_name`, `phone_number`, `dob`, `governorate_id`, `street`, `institution_id`, `college_id`, `major_id`, `training_hours`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '400100100', 'أحمد محمد خليل', '970599100100', '2002-05-15', 1, 'الرمال، غزة', 1, 3, 128, 100, '2026-01-12 07:12:59', '2026-01-12 07:12:59', NULL),
(2, '400200200', 'سارة علي حسن', '970599200200', '2000-03-20', 3, 'البلد، خانيونس', 2, 16, 63, 100, '2026-01-12 07:12:59', '2026-01-12 07:12:59', NULL),
(3, '400300300', 'محمود خالد يوسف', '970599300300', '2003-01-01', NULL, 'المعسكر، دير البلح', 3, 26, 60, 100, '2026-01-12 07:12:59', '2026-01-12 07:12:59', NULL),
(4, '400400400', 'يوسف حسن علي', '970599400400', '1999-12-12', 1, 'الشيخ رضوان', 2, 19, 30, 100, '2026-01-12 07:12:59', '2026-01-12 07:12:59', NULL),
(5, '400500500', 'منى سمير محمود', '970599500500', '2004-07-07', 1, 'تل الهوا', 1, 7, 4, 100, '2026-01-12 07:12:59', '2026-01-12 07:12:59', NULL),
(6, '132316878', 'Test Trainee 1 (Random 69675b0dcee8f)', '970598090454', '2000-01-14', 1, 'Test Street 1', 1, 1, 1, NULL, '2026-01-14 06:59:57', '2026-01-14 06:59:57', NULL),
(7, '506971373', 'Test Trainee 2 (Random 69675b1134325)', '970591651528', '2002-01-14', 1, 'Test Street 2', 1, 1, 1, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(8, '969622919', 'Test Trainee 3 (Random 69675b113b740)', '970595139728', '1993-01-14', 1, 'Test Street 3', 1, 1, 1, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(9, '604738398', 'Test Trainee 4 (Random 69675b113e27c)', '970593895750', '2001-01-14', 1, 'Test Street 4', 1, 1, 1, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(10, '295700770', 'Test Trainee 5 (Random 69675b114086a)', '970598756011', '2001-01-14', 1, 'Test Street 5', 1, 1, 1, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(11, '643895003', 'Test Trainee 6 (Random 69675b1144481)', '970594394141', '2000-01-14', 1, 'Test Street 6', 1, 1, 1, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(12, '550644240', 'Test Trainee 7 (Random 69675b1147ce3)', '970590556323', '1993-01-14', 1, 'Test Street 7', 1, 1, 1, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(13, '541692699', 'Test Trainee 8 (Random 69675b114ac8e)', '970597415928', '1997-01-14', 1, 'Test Street 8', 1, 1, 1, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(14, '338812722', 'Test Trainee 9 (Random 69675b114eb05)', '970590430156', '1991-01-14', 1, 'Test Street 9', 1, 1, 1, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(15, '448695458', 'Test Trainee 10 (Random 69675b1152e25)', '970595543147', '1991-01-14', 1, 'Test Street 10', 1, 1, 1, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(16, '408989168', 'عادل شراب', '970567371501', '2003-01-24', 3, 'البلد', 9, 63, 123, 85, '2026-01-14 07:33:00', '2026-01-14 07:33:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `trainees`
--
ALTER TABLE `trainees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `trainees_national_id_unique` (`national_id`),
  ADD KEY `trainees_governorate_id_foreign` (`governorate_id`),
  ADD KEY `trainees_institution_id_foreign` (`institution_id`),
  ADD KEY `trainees_college_id_foreign` (`college_id`),
  ADD KEY `trainees_major_id_foreign` (`major_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `trainees`
--
ALTER TABLE `trainees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `trainees`
--
ALTER TABLE `trainees`
  ADD CONSTRAINT `trainees_college_id_foreign` FOREIGN KEY (`college_id`) REFERENCES `colleges` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `trainees_governorate_id_foreign` FOREIGN KEY (`governorate_id`) REFERENCES `governorates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `trainees_institution_id_foreign` FOREIGN KEY (`institution_id`) REFERENCES `institutions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `trainees_major_id_foreign` FOREIGN KEY (`major_id`) REFERENCES `majors` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
