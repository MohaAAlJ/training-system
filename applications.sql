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
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
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

INSERT INTO `applications` (`id`, `trainee_id`, `administrative_id`, `department_id`, `section_id`, `training_type`, `duration`, `street`, `start_date`, `end_date`, `status`, `tags`, `application_letter`, `accepted_at`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 25, 5, 9, 13, 1, 3, 'شارع هيفاء السالم', '1991-01-08', '2012-07-26', 7, 'aut facilis ut', 'Dolor aut placeat consequatur libero vitae. Eum et est aut voluptatem omnis sed. Ullam et sit quia sed quis similique. Corporis et laboriosam sit neque eveniet ad.', '2025-12-23 04:50:53', '2025-12-23 04:50:53', '2025-12-23 04:50:53', NULL),
(2, 28, 2, 8, 20, 1, 2, 'طريق راغدة باشا', '1979-03-20', '2009-12-17', 7, 'repellendus molestias soluta', 'Autem sint similique dolores vel enim numquam. Vitae et accusamus unde quia. Debitis tempore perferendis et provident.', '2025-12-23 04:50:53', '2025-12-23 04:50:57', '2025-12-23 04:50:57', NULL),
(3, 23, 1, 2, 5, 2, 2, 'ممر كيان الفرحان', '1996-12-20', '1971-10-06', 4, 'et odio iste', 'Est ut earum eius. Excepturi quisquam a consectetur omnis velit cum autem. Corporis impedit rem nihil maiores.', NULL, '2025-12-23 04:50:57', '2025-12-23 04:50:57', NULL),
(4, 40, 4, 3, 15, 1, 8, 'طريق أفنان العقل', '1974-02-16', '1979-12-02', 2, 'et qui dolore', 'Est aut officiis sed. Temporibus harum debitis saepe debitis et ex. Eaque possimus nam aut voluptate voluptas.', NULL, '2025-12-23 04:50:57', '2025-12-23 04:50:57', NULL),
(5, 1, 1, 3, 19, 1, 7, 'طريق سيرينا الماجد', '1973-11-07', '1970-09-17', 9, 'et excepturi vitae', 'Modi et illum natus repellat deleniti autem. Ut ducimus quis minus sunt eum quod dolor. Velit vel a veritatis provident id quo. Quae porro et quibusdam sunt quidem.', '2025-12-23 04:50:53', '2025-12-23 04:50:57', '2025-12-23 04:50:57', NULL),
(6, 31, 3, 9, 5, 2, 4, 'طريق خميس العسكر', '1985-04-17', '2025-10-20', 2, 'fugit quas repellendus', 'Molestias minus amet reiciendis aut. Suscipit pariatur saepe enim dolorem voluptates. Totam ut optio omnis dolore harum molestiae aut.', NULL, '2025-12-23 04:50:57', '2025-12-23 04:50:57', NULL),
(7, 40, 3, 1, 16, 2, 8, 'شارع أميرة السليم', '2025-01-25', '2022-11-25', 3, 'veritatis sit a', 'Dolor culpa consequatur qui possimus molestiae architecto vero. Qui suscipit modi culpa pariatur eveniet. Sed aut laudantium dolore omnis consequatur aliquid. Porro eos et excepturi eos. Accusantium soluta ut iusto eos aut libero.', '2025-12-23 04:50:53', '2025-12-23 04:50:57', '2025-12-23 04:50:57', NULL),
(8, 38, 4, 7, 5, 1, 5, 'طريق عماد المشيقح', '2024-09-28', '2009-08-16', 2, 'vel cupiditate omnis', 'Ad eveniet aperiam et est sunt nesciunt quos dolores. Ut ut natus inventore sed voluptas ex rerum. Fuga dolor similique praesentium.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(9, 10, 1, 9, 1, 2, 5, 'طريق ريهام العقل', '2024-02-03', '1982-12-08', 9, 'totam facilis impedit', 'Cupiditate repellendus aut tenetur ut. Ad reiciendis adipisci illum aut quo. Culpa est voluptatem sit nihil iusto ad explicabo.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(10, 17, 5, 3, 9, 1, 5, 'طريق نظام السويلم', '1988-02-05', '1998-03-03', 9, 'expedita eum eaque', 'Excepturi eaque architecto eum praesentium incidunt sequi. Doloribus iure voluptatem et quia dolor rem sapiente eius. Autem culpa id velit molestiae minus et labore tempore. Numquam maiores ex minus impedit praesentium.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(11, 47, 4, 3, 19, 1, 4, 'طريق عبد الحافظ الفرحان', '1977-07-14', '1987-08-01', 7, 'adipisci est laudantium', 'Porro autem ipsum labore nihil quos. Ut suscipit quo deserunt in velit qui ad labore. Voluptas consequatur voluptate a aut voluptas ut quia. Perferendis delectus commodi at dolores.', '2025-12-23 04:50:53', '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(12, 50, 3, 7, 6, 1, 12, 'ممر سدين جواهرجي', '1987-06-24', '2025-01-04', 4, 'vero id aspernatur', 'Et sapiente sed nihil exercitationem explicabo sint fuga. Nostrum incidunt maxime qui ratione nisi. Delectus doloremque autem modi blanditiis eos sed.', '2025-12-23 04:50:53', '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(13, 4, 5, 3, 16, 1, 2, 'ممر نعمت القحطاني', '2005-08-16', '2002-05-12', 3, 'sequi quae odit', 'Est illum aut explicabo et consectetur unde qui. Quisquam ipsum blanditiis in quasi voluptatum soluta sequi.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(14, 25, 4, 8, 2, 1, 9, 'طريق مصعب المقبل', '2001-08-25', '1990-05-31', 3, 'voluptatem error non', 'Rerum porro occaecati suscipit atque dolor modi harum sunt. At adipisci est dolor officia est quae occaecati. Nemo deleniti amet esse perspiciatis facere nostrum aut. Nisi optio ipsam neque et suscipit autem. Autem molestiae voluptas ut ut.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(15, 14, 2, 2, 14, 1, 4, 'ممر جاسم الأسمري', '2002-01-27', '2017-03-09', 9, 'exercitationem labore facere', 'Dolores necessitatibus nesciunt aut. Dicta suscipit et laboriosam eaque enim placeat. Doloribus et ullam dolorum quos.', '2025-12-23 04:50:53', '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(16, 10, 2, 1, 4, 1, 1, 'شارع كايد الفرحان', '2009-12-22', '2010-03-02', 2, 'distinctio qui sequi', 'Blanditiis et dolore consectetur. Dolor et sed incidunt quis odio. Nobis nisi in rem quaerat. Molestiae voluptate molestias necessitatibus sed quis dignissimos rem.', '2025-12-23 04:50:53', '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(17, 21, 5, 2, 5, 1, 9, 'شارع باسمة الشهيل', '2025-03-08', '1996-12-01', 7, 'ipsum et voluptatem', 'Aliquid commodi ducimus explicabo. Quia debitis sed aut distinctio sed est ut. Expedita culpa inventore alias qui ex.', '2025-12-23 04:50:53', '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(18, 9, 4, 3, 15, 2, 2, 'ممر رياض الفرحان', '1982-08-28', '2019-02-17', 8, 'vitae amet in', 'Quibusdam totam alias et non at debitis dignissimos. Est fugit rerum quia sit inventore mollitia nemo. Quos omnis fuga sunt dolor in amet. Accusamus et fugit tenetur dolorem fugit et.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(19, 5, 5, 4, 4, 1, 3, 'شارع غازي العمرو', '1971-12-06', '1990-11-05', 1, 'saepe commodi ullam', 'Tempore quae iure ut. Sapiente eius nesciunt earum corrupti voluptate dolorem. Eos quisquam possimus et.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(20, 6, 2, 10, 11, 2, 10, 'طريق ليندا باشا', '2018-12-09', '1985-02-23', 5, 'magni culpa dolor', 'Laudantium omnis dolorem recusandae et quis. Fugit modi voluptas ipsa eum est facere est quas. Amet quibusdam ex nostrum ut quia omnis beatae. Nostrum voluptatum quae incidunt similique nobis necessitatibus quis.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(21, 19, 5, 4, 2, 2, 11, 'شارع أزهار المشيقح', '2006-09-15', '2021-12-01', 5, 'assumenda et illum', 'Et odio quia id eaque corporis necessitatibus. Et expedita aspernatur sunt fuga. Et et quia facere quod vel et. Maiores aut cupiditate est omnis ex tempora. Reprehenderit commodi nisi tenetur sequi sit iusto.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(22, 1, 5, 7, 20, 1, 6, 'طريق عزيز الماجد', '1977-03-04', '1997-12-30', 6, 'laudantium occaecati eos', 'Voluptatem nihil doloribus vitae at et eligendi est. Quam voluptas ad similique voluptate. Perspiciatis nam fugit autem nihil voluptas labore quos. Esse voluptatem est necessitatibus eaque quas ut laborum.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(23, 33, 1, 8, 7, 2, 8, 'طريق ميسون الفدا', '1996-08-04', '1978-04-23', 8, 'saepe libero tempora', 'Est aut rerum commodi illo porro. At iste sit fuga. Debitis veritatis aperiam ut.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(24, 44, 1, 4, 7, 1, 5, 'شارع أنوار العنزي', '1983-09-13', '1991-01-02', 9, 'rem dolores dolorem', 'Pariatur non consequatur asperiores animi. Soluta maxime dolorem facilis perspiciatis. Consequatur nostrum at similique optio. Fugit ipsam doloribus sed sit. Doloremque non laudantium cum velit.', '2025-12-23 04:50:53', '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(25, 23, 5, 6, 14, 2, 6, 'شارع محمد  السهلي', '2013-07-06', '1998-01-14', 5, 'ut dignissimos illum', 'Ducimus alias magnam fugit consectetur qui praesentium. At vel officia ad nam et delectus atque. Aut est molestiae sed adipisci commodi reiciendis est eum.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(26, 38, 4, 8, 11, 2, 5, 'ممر مسعد الزامل', '2024-05-12', '1982-06-28', 6, 'esse architecto nostrum', 'Qui eum voluptatem vitae nisi. Aut in ut quo dolor ut quam. Et ipsa minima sit dolore.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(27, 44, 3, 9, 7, 1, 6, 'شارع بشائر السمير', '2008-12-25', '2001-09-14', 1, 'et et cumque', 'Eos rerum autem minima corporis ut omnis praesentium. Et explicabo corporis adipisci similique. Nulla quas quaerat eum sed est. Debitis amet perspiciatis placeat nesciunt distinctio enim officia.', '2025-12-23 04:50:53', '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(28, 10, 1, 8, 20, 2, 9, 'ممر وفاء الجهني', '2020-07-14', '2004-06-18', 1, 'ex impedit rem', 'Quis quisquam est molestiae dolorem quos non omnis id. Maiores excepturi commodi praesentium soluta iusto nobis. Illo eligendi itaque officia numquam recusandae. Quia illum molestiae laudantium.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(29, 36, 5, 2, 7, 2, 12, 'ممر زين السماري', '1970-12-31', '1987-03-08', 4, 'incidunt fugit dolorum', 'Dolorum sapiente repudiandae omnis ut. Dolore omnis culpa alias et consectetur. Ea nam repudiandae itaque reprehenderit cupiditate voluptate veniam.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(30, 7, 5, 3, 1, 2, 12, 'ممر أفنان المقبل', '2003-07-12', '1980-03-28', 1, 'sunt enim voluptas', 'Delectus quo tenetur atque exercitationem eligendi qui. Dolorem eveniet rerum doloremque nesciunt adipisci. Est aut eum velit occaecati laboriosam. Quibusdam alias maxime sapiente et sed.', '2025-12-23 04:50:53', '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(31, 10, 4, 3, 17, 1, 7, 'ممر مدين الخالدي', '1979-09-22', '1971-11-14', 8, 'reiciendis eligendi quia', 'Inventore et sint voluptatem ea quaerat enim beatae. Veritatis id id provident quisquam cupiditate sit eum. Et soluta soluta autem dolor. Dolor quibusdam perferendis omnis explicabo cumque nihil.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(32, 7, 3, 8, 11, 1, 12, 'طريق واصف السويلم', '2016-03-29', '2021-01-29', 6, 'qui tenetur unde', 'Illo et excepturi unde omnis magnam. Qui et et maiores est. Maiores voluptatum porro et ea praesentium distinctio esse. Tenetur non dolorem soluta dolorum.', NULL, '2025-12-23 04:50:58', '2025-12-23 04:50:58', NULL),
(33, 39, 3, 4, 10, 2, 5, 'طريق رزان مدني', '2006-11-12', '1972-03-13', 7, 'qui ut ut', 'Quas corrupti quo in nam facilis vitae. Sed amet id magnam itaque molestiae. Distinctio ut repellendus nostrum.', NULL, '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(34, 37, 5, 1, 7, 1, 1, 'شارع نعمة الداوود', '2014-09-29', '2017-12-21', 7, 'iure maiores ullam', 'Eveniet ea quam ut est doloribus. Qui officia nihil at inventore aut quis sed.', NULL, '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(35, 36, 1, 5, 17, 1, 11, 'شارع سهام العسكر', '1986-01-29', '2019-02-05', 5, 'explicabo quis veniam', 'In error sed dicta enim. Qui voluptatem est necessitatibus doloremque laborum eaque. Eius qui repellat eos ipsa pariatur. Reprehenderit excepturi dolores laboriosam natus consequatur.', NULL, '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(36, 35, 3, 7, 15, 1, 4, 'طريق كريم الصامل', '2023-06-08', '2003-11-08', 2, 'et voluptate quae', 'Deleniti aut accusamus sit vel. Deleniti labore rerum non ipsum qui dignissimos illum. Laboriosam qui modi voluptas sapiente.', '2025-12-23 04:50:53', '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(37, 10, 1, 2, 20, 1, 11, 'طريق أمل الفدا', '2021-11-12', '2008-07-11', 8, 'culpa in consequatur', 'Ab sequi dolores aspernatur facilis magni voluptatum. Quo nesciunt odit ut omnis vitae molestiae sed quas. Dolores est aut placeat atque sunt dolor.', NULL, '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(38, 18, 3, 9, 7, 1, 2, 'طريق سماح الجريد', '2012-03-19', '1989-12-06', 8, 'facere quia id', 'Dolorem quis similique porro omnis molestiae. Illo nobis dolores sunt perspiciatis magnam. Et itaque similique sed explicabo ipsum nobis ea.', '2025-12-23 04:50:53', '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(39, 29, 3, 2, 12, 1, 6, 'شارع راشد مدني', '2010-10-11', '2018-04-04', 6, 'alias et earum', 'Nam saepe ea veniam recusandae perspiciatis voluptatibus impedit. Voluptas autem sequi quaerat cumque ipsam. Beatae dolorem qui facilis dolorem. Ducimus laborum dolor voluptas deleniti dolorem necessitatibus.', '2025-12-23 04:50:53', '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(40, 8, 4, 8, 2, 2, 2, 'طريق مظهر الفرحان', '1980-07-04', '2023-03-01', 6, 'dolores odit veniam', 'Aliquid voluptate ut distinctio soluta. Maxime iste dolorum ut iure saepe ea fuga. Commodi velit aut omnis sed sed accusamus. Quae sit et sequi vitae ab qui pariatur.', NULL, '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(41, 2, 1, 5, 3, 2, 1, 'ممر سلام العتيبي', '1999-01-17', '1996-11-03', 9, 'deleniti doloribus aliquam', 'Earum et iste voluptatem sunt odit consequatur nulla aut. Qui et beatae et non harum animi architecto optio. Voluptatem ea ab temporibus voluptatem ut labore.', '2025-12-23 04:50:53', '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(42, 29, 1, 4, 2, 1, 5, 'طريق يعرب العسكر', '1996-11-18', '1975-06-29', 5, 'perferendis nihil tempore', 'Porro eos eum harum et. Ut eveniet nam recusandae alias et quia iusto. Ut aliquam iusto et sint eum corporis.', '2025-12-23 04:50:53', '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(43, 3, 1, 2, 2, 1, 2, 'طريق نعمت العمرو', '1999-11-18', '1979-04-24', 4, 'quam minus facilis', 'Dolorum labore nostrum nihil ipsum culpa. At ab consectetur reiciendis magnam similique ea minima. Ullam tempore nesciunt ut culpa voluptas consequatur aut quia.', NULL, '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(44, 50, 3, 1, 3, 1, 2, 'شارع غسان الشهري', '1989-05-13', '1974-03-21', 6, 'cum rerum tempora', 'Ullam enim similique reprehenderit vitae accusantium. Quos odio eligendi accusantium ut temporibus. Ut odio non vero incidunt.', NULL, '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(45, 41, 5, 1, 9, 1, 3, 'ممر تسنيم السالم', '1998-11-09', '2017-12-09', 9, 'vero voluptatum culpa', 'Nihil aut qui maiores voluptas. Doloremque ipsum at labore voluptatem non.', '2025-12-23 04:50:53', '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(46, 5, 4, 2, 3, 2, 10, 'شارع منتصر سقا', '2003-05-27', '1977-07-03', 9, 'aliquam et exercitationem', 'Voluptates aspernatur nam aut aspernatur. Aliquam occaecati iure at aliquid aut. Debitis qui quia omnis sit aperiam qui. Fugiat repellendus maxime veritatis quidem ullam qui.', '2025-12-23 04:50:53', '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(47, 21, 5, 2, 18, 1, 7, 'شارع ريمان المقبل', '1991-10-25', '1988-09-02', 8, 'est perspiciatis quis', 'Reiciendis libero unde iusto autem illo quo. Eligendi velit voluptas dolor delectus. Est corrupti similique eius voluptatem. Voluptas sed minus sint dolor vero est eum.', '2025-12-23 04:50:53', '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(48, 17, 2, 8, 13, 1, 11, 'طريق عهد الجريد', '2005-08-02', '2008-06-09', 7, 'qui est magnam', 'Cupiditate deserunt quia dolor possimus. Totam et voluptate eum nihil eius. Natus illo est eaque est doloribus rem. Dicta illum ipsa incidunt veritatis est quaerat. Aperiam in neque qui exercitationem culpa voluptatem odio.', NULL, '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(49, 48, 1, 9, 9, 2, 3, 'طريق عدب السمير', '2011-01-11', '1995-11-03', 5, 'sed tempora et', 'Recusandae ullam in qui provident iusto vel voluptas voluptates. Non nihil non provident dolorum rerum et et.', NULL, '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(50, 14, 3, 9, 5, 1, 8, 'شارع ذياب الفرحان', '1989-03-01', '1993-06-01', 9, 'laborum omnis et', 'Commodi praesentium qui non. Eveniet rerum et id repellat aut ea. Eum dolor voluptatem rem magnam sunt sed eum. Nihil id debitis odit aliquam facilis rerum.', '2025-12-23 04:50:53', '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(51, 9, 2, 1, 16, 2, 4, 'شارع احمد الشهيل', '2018-05-22', '2009-04-11', 2, 'doloremque porro vel', 'Quis repudiandae ratione provident non. Voluptatum repellendus voluptas est tempora sequi eveniet. Quia omnis autem totam deserunt. Optio sit ipsam voluptate dolores ea.', NULL, '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(52, 35, 4, 8, 8, 2, 11, 'شارع الحارث الفرحان', '1986-06-19', '2009-06-06', 5, 'officiis est nisi', 'Eligendi aut earum ab laboriosam ipsum. Dolores repellendus nisi consequatur. Culpa consequatur rerum est expedita eligendi veniam.', NULL, '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(53, 30, 4, 6, 7, 2, 2, 'ممر عبادة الحصين', '1972-12-02', '2018-07-04', 1, 'et voluptatem alias', 'Unde alias ut et delectus et sed vitae. Recusandae facilis ducimus excepturi magnam voluptatem temporibus laborum. Quia aperiam quo omnis. Blanditiis voluptatem commodi ut et ut et quod. Earum perferendis omnis sed minima qui.', '2025-12-23 04:50:53', '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(54, 23, 2, 9, 8, 2, 4, 'طريق عبيدالله سقا', '2025-01-14', '2016-05-20', 5, 'quo accusantium animi', 'Inventore eum et sed magnam repellat molestiae rerum. Consequatur nihil voluptatem eum enim consequuntur. Mollitia saepe a deserunt aut est.', '2025-12-23 04:50:53', '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(55, 26, 4, 3, 17, 2, 3, 'ممر عليان الحصين', '1991-09-22', '1997-07-17', 2, 'voluptate modi enim', 'Blanditiis quaerat numquam velit assumenda eius id provident. Labore eum repellat aut ipsum vero. Labore iure atque minima nemo corrupti consequatur. Unde incidunt sunt eum quia esse.', NULL, '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(56, 16, 5, 5, 12, 2, 11, 'شارع ماهر برماوي', '2014-08-22', '2021-04-13', 7, 'ipsa cum odit', 'Mollitia velit fuga debitis numquam voluptatem aperiam enim. Et tempora quae aliquam cumque. Soluta odit repellendus voluptatibus placeat ullam qui. Provident occaecati dolor qui velit corrupti explicabo. Ullam similique necessitatibus et tempore.', NULL, '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(57, 13, 2, 1, 17, 1, 7, 'طريق معمر الشهري', '1973-04-28', '1975-04-21', 8, 'quod qui tempora', 'Est enim voluptas cum vel pariatur. Veritatis distinctio aut laborum dolore eos. Eaque nobis est repudiandae omnis suscipit veritatis nihil. Qui vitae praesentium magni velit voluptas facilis ut omnis.', '2025-12-23 04:50:53', '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(58, 50, 4, 4, 16, 1, 5, 'طريق مصعب الزامل', '1993-05-22', '1985-12-22', 5, 'aut pariatur rerum', 'Doloremque architecto sit vel odio facere. Ipsa id et omnis beatae fugiat laboriosam. Ea aut hic quo quas. Quisquam nemo quo enim soluta eligendi culpa qui qui.', '2025-12-23 04:50:53', '2025-12-23 04:50:59', '2025-12-23 04:50:59', NULL),
(59, 5, 3, 8, 20, 2, 9, 'ممر ضرار المشيقح', '2015-11-07', '1992-04-23', 1, 'temporibus porro perspiciatis', 'Est ipsa mollitia enim laudantium necessitatibus ea esse magni. Sint rerum fuga animi omnis optio. Et consequatur voluptatem eum ex mollitia tempora et. Quas vel aut sed.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(60, 26, 1, 9, 3, 2, 10, 'شارع أبرار العنزي', '2001-03-12', '1975-08-04', 5, 'et quos et', 'Et neque quasi quo dolor mollitia illo. Odio provident aut quia provident ducimus praesentium. Sint consequatur et sint veniam. Corporis aut consequatur repellat et. Eius quis molestiae velit ut necessitatibus enim.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(61, 28, 2, 7, 3, 1, 2, 'طريق أنعام العنزي', '2009-06-29', '2010-12-15', 6, 'aspernatur alias vel', 'Velit libero dolor voluptatem ab eligendi sit quas. Consequatur aut quam reprehenderit laudantium ea aut. Laboriosam sit voluptas sunt quis nemo quibusdam.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(62, 29, 2, 6, 20, 2, 9, 'ممر صلاح الفريدي', '2005-05-25', '2015-12-14', 5, 'ut accusamus ut', 'Itaque reiciendis omnis provident laboriosam rerum vel consequatur natus. Non eum quod eos qui esse ut molestiae. Non et voluptas excepturi ex eaque consequuntur. Exercitationem temporibus eos rerum omnis.', '2025-12-23 04:50:53', '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(63, 11, 3, 4, 7, 1, 1, 'طريق سوسن برماوي', '1984-07-10', '2019-12-23', 6, 'laudantium nulla quia', 'Laudantium saepe ea illum ad molestiae. Ea ex blanditiis qui cupiditate animi.', '2025-12-23 04:50:53', '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(64, 50, 2, 3, 18, 1, 3, 'ممر شيرين العسكر', '1982-10-19', '2001-09-11', 9, 'occaecati magnam facere', 'Et eligendi harum voluptas vitae tempore dolorem molestias et. Eum alias nobis earum nihil repellat minima suscipit vero.', '2025-12-23 04:50:53', '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(65, 50, 4, 6, 20, 1, 7, 'طريق ريان الراجحي', '1985-01-23', '2011-08-04', 4, 'consequatur soluta qui', 'Sed assumenda dolor libero quisquam dolores. Tenetur officia dolor voluptatem iure voluptatem culpa. Aut placeat voluptatem qui. Eos delectus illum iste similique odio nihil.', '2025-12-23 04:50:53', '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(66, 28, 4, 6, 20, 2, 5, 'ممر آية سقا', '1984-02-24', '1981-08-31', 5, 'dolorem veritatis quo', 'Corporis dolores et voluptate recusandae. Qui placeat corporis veniam tenetur repellendus magnam. Sit amet iusto illo nesciunt et vitae. Aliquid alias quae qui aliquid sed recusandae consequatur qui.', '2025-12-23 04:50:53', '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(67, 5, 2, 6, 9, 2, 7, 'ممر عيسى الخالدي', '1987-05-17', '1997-12-02', 8, 'et nesciunt dolor', 'Libero dolorum officiis enim. Illum rerum et a sequi molestiae rerum. Doloribus veritatis adipisci ab quia laudantium et magni. Blanditiis nostrum doloribus dolores at voluptatem.', '2025-12-23 04:50:53', '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(68, 10, 4, 4, 10, 1, 6, 'طريق محبوبة الماجد', '2001-11-10', '2016-08-31', 3, 'optio mollitia dolorem', 'Ipsum repellendus et cum est dolores. Voluptas voluptatem sint provident sequi aspernatur a. Temporibus expedita adipisci et optio voluptates repellat vel. Non qui nemo exercitationem cumque ipsa omnis. Officia aut sapiente illo cumque.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(69, 46, 2, 9, 14, 1, 10, 'طريق لورينا الفيفي', '2009-11-10', '1985-11-21', 1, 'sed at nisi', 'Quasi eius consequatur quis officia consequatur aut eius expedita. Blanditiis ut exercitationem maxime.', '2025-12-23 04:50:53', '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(70, 34, 1, 7, 5, 1, 12, 'طريق هنا الحميد', '2025-02-28', '1992-03-10', 6, 'laudantium sed placeat', 'Sed distinctio incidunt nobis doloremque. Consequatur et praesentium minus nemo quam. Quod hic repellendus ut nulla et corrupti architecto repellat.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(71, 46, 4, 4, 13, 1, 6, 'طريق عمران الشهيل', '1991-10-08', '2015-09-12', 9, 'vel consequatur esse', 'Laborum iste eligendi incidunt asperiores natus. Et nihil aut fuga quis quisquam accusamus.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(72, 6, 2, 1, 12, 2, 9, 'ممر بكر القحطاني', '1975-01-20', '2014-11-19', 9, 'dolores quibusdam debitis', 'Fugit fugiat consequatur odit eum in voluptas. Occaecati aut maxime quis est. Quaerat nemo omnis ad. Quia illum fuga cupiditate optio quia velit ex.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(73, 12, 2, 4, 1, 2, 6, 'ممر أسعد الأسمري', '2012-02-05', '2019-05-28', 4, 'voluptas velit laudantium', 'Qui molestias ullam atque dicta est nesciunt. Et alias voluptas ipsa aut neque. Modi blanditiis eum quod asperiores. Omnis placeat quis deserunt recusandae ipsam minus sequi.', '2025-12-23 04:50:53', '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(74, 20, 4, 7, 16, 1, 2, 'شارع آمال العسكر', '1987-03-15', '1998-11-04', 5, 'et vel nulla', 'Est odio quod modi. Accusamus voluptatem numquam omnis minima sunt doloremque fugiat. Minus suscipit illo sapiente saepe. Optio sapiente ducimus alias magnam. Esse corporis consectetur rerum et quia laborum numquam.', '2025-12-23 04:50:53', '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(75, 38, 3, 4, 16, 2, 9, 'طريق انور الزامل', '1973-09-24', '1979-01-30', 8, 'nisi maxime consequatur', 'Odio corrupti delectus iure ut non incidunt exercitationem. Illum possimus voluptatem et fuga voluptas debitis. Sit voluptatem earum occaecati eaque eos nesciunt. Molestiae eligendi consequatur illo eos et ratione quam.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(76, 15, 3, 8, 1, 1, 5, 'ممر علاء السماري', '1979-08-22', '2017-12-07', 5, 'id consequatur qui', 'Qui fuga iste maxime quo. Voluptas quas corporis beatae ut vel aspernatur a.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(77, 42, 5, 10, 6, 2, 6, 'ممر مدين القحطاني', '1982-05-30', '1996-05-28', 3, 'deleniti eligendi qui', 'Velit aliquid perspiciatis eum aspernatur neque perspiciatis inventore. Et quis cupiditate eum et et inventore blanditiis. Nesciunt aspernatur labore architecto eos dolor ullam sit.', '2025-12-23 04:50:53', '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(78, 16, 3, 1, 2, 2, 12, 'شارع دانة باشا', '2014-09-29', '2005-11-08', 2, 'ea est beatae', 'Quibusdam doloribus aspernatur sed magni veritatis. Ipsum qui non non repellendus tenetur. Praesentium voluptatum expedita amet voluptas a unde rerum.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(79, 2, 2, 6, 15, 1, 3, 'طريق سدير الحنتوشي', '2003-06-18', '1975-04-01', 3, 'est aut officia', 'Sit beatae facere iste nostrum mollitia. Quam eum illo ipsam ex et. Omnis labore ab aut facilis adipisci accusamus.', '2025-12-23 04:50:53', '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(80, 44, 4, 4, 6, 2, 11, 'شارع تولين السليم', '2006-04-06', '2003-01-19', 3, 'impedit tempora rerum', 'Velit cupiditate facere cupiditate adipisci ea deserunt inventore. Possimus omnis officiis voluptas id vel. Quam voluptatem quia molestias pariatur aut. Placeat alias reiciendis odit delectus.', '2025-12-23 04:50:53', '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(81, 50, 3, 8, 5, 2, 9, 'شارع بتول الحميد', '2011-10-22', '2010-07-02', 7, 'nesciunt qui ut', 'Eius suscipit qui aperiam qui quisquam sint qui mollitia. Deserunt totam omnis illo sint consequatur ipsam placeat quia. Dolorum aut consequuntur tempore quibusdam laboriosam iusto similique rerum.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(82, 42, 2, 8, 7, 2, 5, 'طريق إكرام الأحمري', '2020-05-17', '2016-09-23', 1, 'ab odit quisquam', 'Corporis eius et eum ea autem autem quo. Vel quia maxime repellendus iste est ratione. Temporibus doloribus laboriosam non nostrum cupiditate. Incidunt neque dolor voluptatem ab nemo ducimus reprehenderit deserunt.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(83, 23, 3, 9, 20, 1, 4, 'شارع خديجة الأحمري', '1971-09-06', '1995-04-23', 8, 'ab facere vitae', 'Beatae nisi sed aut animi ad molestiae. In architecto ipsum placeat quis est doloribus et. Similique illo commodi consequatur perspiciatis.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(84, 22, 5, 4, 13, 2, 8, 'شارع آيات الحسين', '1984-04-18', '1972-05-25', 8, 'ullam cum tempora', 'Veniam error facilis quae sit assumenda voluptatem. Nihil ut sapiente cum. Iusto reprehenderit omnis et harum. Quos dolorem eaque et. Possimus rem aut totam eaque asperiores et ipsam.', '2025-12-23 04:50:53', '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(85, 44, 4, 9, 10, 1, 12, 'طريق ادهم المشيقح', '1979-06-24', '1979-01-19', 2, 'et dolor eum', 'Vel repellat enim officia. Minus maiores culpa temporibus et. Et et pariatur nostrum necessitatibus. Fuga doloremque illum dolorum consequatur dignissimos est omnis. Perferendis et quisquam dicta quia.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(86, 48, 4, 6, 4, 1, 2, 'شارع راما الجريد', '2020-12-06', '2020-04-20', 8, 'eos omnis iure', 'Est illo aut doloribus dicta aut occaecati. Iste id laboriosam possimus adipisci qui pariatur molestias. Est officiis magnam natus est ut.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(87, 27, 4, 9, 10, 2, 6, 'شارع ساجدة الصقيه', '1973-03-22', '2008-11-30', 1, 'optio cumque eaque', 'Aut dignissimos repellendus laboriosam impedit sed maxime ea. Voluptas perspiciatis tempore quasi dolorum. Ipsam minima culpa adipisci illum incidunt corporis nam.', NULL, '2025-12-23 04:51:00', '2025-12-23 04:51:00', NULL),
(88, 12, 3, 7, 15, 2, 11, 'طريق يزيد الفرحان', '2006-10-25', '2014-12-31', 9, 'voluptas ut sed', 'Recusandae laboriosam est repellat. Velit eos praesentium libero aliquam in illum. Ab et maiores qui ab id explicabo cumque. Et officia adipisci reprehenderit aspernatur quis nisi.', NULL, '2025-12-23 04:51:01', '2025-12-23 04:51:01', NULL),
(89, 23, 4, 5, 19, 1, 3, 'ممر منير الحميد', '2019-01-30', '2003-07-16', 6, 'perspiciatis est recusandae', 'Et tenetur iusto eaque provident dolorem. Omnis aut vitae optio consequatur. Deleniti explicabo dolore adipisci quas fuga illo. Aut laudantium excepturi alias.', '2025-12-23 04:50:53', '2025-12-23 04:51:01', '2025-12-23 04:51:01', NULL),
(90, 7, 4, 3, 12, 1, 12, 'شارع تركي الجهني', '1994-07-12', '1978-09-12', 8, 'eius ea dicta', 'Aperiam laboriosam exercitationem est dolorem voluptas occaecati repudiandae non. Dolorem accusamus aut et in. Saepe debitis placeat et nisi necessitatibus ut. Hic dolores pariatur soluta natus.', NULL, '2025-12-23 04:51:01', '2025-12-23 04:51:01', NULL),
(91, 45, 4, 1, 10, 1, 5, 'طريق فوزية مكي', '2021-03-15', '2017-03-25', 3, 'facere maiores voluptas', 'Delectus odit magnam voluptas rem unde autem qui. Quos nam quaerat molestiae sint.', '2025-12-23 04:50:53', '2025-12-23 04:51:01', '2025-12-23 04:51:01', NULL),
(92, 15, 2, 5, 13, 2, 11, 'شارع جلال الفيفي', '1993-11-02', '1984-09-08', 4, 'amet occaecati nihil', 'Assumenda nobis quia ut iure officia. Optio soluta et dolorem consequatur aut illo. Voluptatem suscipit quidem numquam. Ea qui aliquid maiores alias sint pariatur autem.', '2025-12-23 04:50:53', '2025-12-23 04:51:01', '2025-12-23 04:51:01', NULL),
(93, 42, 5, 4, 4, 2, 5, 'ممر عبد اللطيف القحطاني', '2005-04-01', '1971-07-31', 5, 'velit iusto quia', 'Sequi libero expedita at consequuntur distinctio inventore placeat. Autem animi veniam labore. Inventore est adipisci debitis repellat qui sed dolorum.', '2025-12-23 04:50:53', '2025-12-23 04:51:01', '2025-12-23 04:51:01', NULL),
(94, 2, 5, 8, 13, 1, 4, 'ممر ملهم هوساوي', '1995-09-07', '2007-02-21', 3, 'temporibus aut qui', 'Repudiandae est laudantium molestias dolores ut sunt magni. Quam magni eum numquam quos eum sed aut. Sint eaque eum aperiam quis. Quo reiciendis ad quia accusantium temporibus velit impedit.', '2025-12-23 04:50:53', '2025-12-23 04:51:01', '2025-12-23 04:51:01', NULL),
(95, 19, 5, 3, 9, 2, 6, 'شارع دينا الجريد', '2004-09-23', '1999-02-11', 4, 'ipsa tempore quia', 'Laudantium rerum laudantium possimus fugit. Ullam ratione a adipisci maiores. Quae voluptatem inventore cum ea quia ea dolor.', NULL, '2025-12-23 04:51:01', '2025-12-23 04:51:01', NULL),
(96, 27, 5, 2, 15, 1, 8, 'شارع ريهام باشا', '1994-03-04', '2004-06-13', 8, 'mollitia cupiditate hic', 'Quas delectus laudantium quisquam debitis est laudantium laborum. Magnam quos harum facere est. Accusamus quia quia saepe hic.', NULL, '2025-12-23 04:51:01', '2025-12-23 04:51:01', NULL),
(97, 24, 1, 6, 14, 2, 6, 'ممر مريام الحميد', '1999-02-19', '2002-01-26', 1, 'deserunt rerum consequatur', 'Porro maxime veniam blanditiis. Aliquid cupiditate ullam eos sed. Accusantium ipsum voluptatibus beatae sit voluptates.', '2025-12-23 04:50:53', '2025-12-23 04:51:01', '2025-12-23 04:51:01', NULL),
(98, 25, 3, 7, 8, 1, 10, 'شارع ماجد الحنتوشي', '2008-12-01', '1979-06-13', 8, 'qui voluptas nihil', 'Quo aperiam optio hic vel. Amet sed enim animi unde voluptates. Et dolores et quia id nesciunt necessitatibus non voluptatem. Reiciendis illo laudantium id facere est.', NULL, '2025-12-23 04:51:01', '2025-12-23 04:51:01', NULL),
(99, 14, 2, 9, 6, 2, 2, 'ممر رحمه الشيباني', '2009-12-18', '2010-01-19', 5, 'et aut repellendus', 'Maiores eligendi quia odio nihil fugiat quos quas. Mollitia temporibus quis hic harum illo. Quia dolorem magni quaerat molestiae omnis eligendi. Atque ratione reprehenderit temporibus eligendi deserunt neque et.', '2025-12-23 04:50:53', '2025-12-23 04:51:01', '2025-12-23 04:51:01', NULL),
(100, 25, 3, 9, 20, 2, 11, 'طريق عادل السالم', '2019-04-09', '2012-01-16', 3, 'repudiandae corrupti et', 'Quos dolorem exercitationem dolores assumenda dolores excepturi. Consequatur adipisci perspiciatis qui error quis ab.', '2025-12-23 04:50:53', '2025-12-23 04:51:01', '2025-12-23 04:51:01', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `applications_trainee_id_foreign` (`trainee_id`),
  ADD KEY `applications_administrative_id_foreign` (`administrative_id`),
  ADD KEY `applications_department_id_foreign` (`department_id`),
  ADD KEY `applications_section_id_foreign` (`section_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

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
