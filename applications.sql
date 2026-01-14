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
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` char(36) NOT NULL,
  `trainee_id` bigint(20) UNSIGNED NOT NULL,
  `administrative_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `section_id` bigint(20) UNSIGNED NOT NULL,
  `training_type` tinyint(4) NOT NULL DEFAULT 1,
  `duration` int(11) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `tags` text DEFAULT NULL,
  `application_letter` text DEFAULT NULL,
  `accepted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `uuid`, `trainee_id`, `administrative_id`, `department_id`, `section_id`, `training_type`, `duration`, `street`, `start_date`, `end_date`, `status`, `tags`, `application_letter`, `accepted_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'd60d14b8-f7c6-442f-a832-dda1f5448de2', 3, 2, 13, 20, 1, 100, NULL, '2026-02-12', '2026-03-12', 4, 'انتظار الموافقة النهائية', NULL, '2026-01-12 07:12:59', '2026-01-12 07:12:59', '2026-01-12 07:12:59', NULL),
(2, '4ad2a906-1fc0-40e5-ab7f-f52ab926eb58', 4, 1, 3, 3, 2, 100, NULL, '2026-01-13', '2026-07-12', 2, 'بانتظار تأكيد الوزارة', NULL, NULL, '2026-01-12 07:13:00', '2026-01-12 07:13:00', NULL),
(3, 'b003f5a5-ed3c-4717-8770-622000148913', 5, 1, 10, 10, 1, 100, NULL, '2025-10-12', '2026-01-11', 6, 'تم الانتهاء بنجاح', NULL, '2026-01-12 07:13:00', '2026-01-12 07:13:00', '2026-01-12 07:13:00', NULL),
(4, '32088b9b-fff6-43f6-b4ce-270c6e8c74ca', 6, 1, 1, 1, 1, NULL, NULL, '2026-01-14', '2026-04-14', 5, NULL, NULL, NULL, '2026-01-14 06:59:59', '2026-01-14 06:59:59', NULL),
(5, '7c16561e-c16b-4547-9a91-4b5cf8069841', 7, 1, 1, 1, 1, NULL, NULL, '2026-01-14', '2026-04-14', 5, NULL, NULL, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(6, 'c1d444e7-607a-40c7-95a4-7a51bea6febd', 8, 1, 1, 1, 1, NULL, NULL, '2026-01-14', '2026-04-14', 5, NULL, NULL, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(7, '73c66c20-0979-401b-9058-682f645cd510', 9, 1, 1, 1, 1, NULL, NULL, '2026-01-14', '2026-04-14', 5, NULL, NULL, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(8, '877f1fd1-e19d-4372-b97a-d6a7a03e074d', 10, 1, 1, 1, 1, NULL, NULL, '2026-01-14', '2026-04-14', 5, NULL, NULL, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(9, '8efa92bf-83e6-4071-b764-b1e3bcee179a', 11, 1, 1, 1, 1, NULL, NULL, '2026-01-14', '2026-04-14', 5, NULL, NULL, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(10, '780bdd4c-c8ed-4470-804e-651f1fefcb56', 12, 1, 1, 1, 1, NULL, NULL, '2026-01-14', '2026-04-14', 5, NULL, NULL, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(11, '9c6715b1-981a-45ac-a6a8-f9faa26eb53a', 13, 1, 1, 1, 1, NULL, NULL, '2026-01-14', '2026-04-14', 5, NULL, NULL, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(12, '623bdb88-42fb-4837-b918-fe0947ea8b24', 14, 1, 1, 1, 1, NULL, NULL, '2026-01-14', '2026-04-14', 5, NULL, NULL, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(13, '4b7ab8d8-cdf2-437f-999f-56a4d237e6e8', 15, 1, 1, 1, 1, NULL, NULL, '2026-01-14', '2026-04-14', 5, NULL, NULL, NULL, '2026-01-14 07:00:01', '2026-01-14 07:00:01', NULL),
(14, '380fc4bf-118b-4181-8906-813dcd80ab02', 16, 1, 12, 12, 1, NULL, 'البلد', NULL, NULL, 6, NULL, NULL, NULL, '2026-01-14 07:33:00', '2026-01-14 07:33:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `applications_uuid_unique` (`uuid`),
  ADD KEY `applications_administrative_id_foreign` (`administrative_id`),
  ADD KEY `applications_department_id_foreign` (`department_id`),
  ADD KEY `applications_section_id_foreign` (`section_id`),
  ADD KEY `idx_applications_trainee_type_status` (`trainee_id`,`training_type`,`status`),
  ADD KEY `idx_applications_trainee_id` (`trainee_id`),
  ADD KEY `idx_applications_training_type` (`training_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_administrative_id_foreign` FOREIGN KEY (`administrative_id`) REFERENCES `administratives` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `sections` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_trainee_id_foreign` FOREIGN KEY (`trainee_id`) REFERENCES `trainees` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
