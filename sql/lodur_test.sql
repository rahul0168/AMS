-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 27, 2025 at 05:18 AM
-- Server version: 8.0.18
-- PHP Version: 8.1.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lodur_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `anwesenheits_kontrolle`
--

DROP TABLE IF EXISTS `anwesenheits_kontrolle`;
CREATE TABLE IF NOT EXISTS `anwesenheits_kontrolle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('present','absent','excused') DEFAULT 'present',
  `recorded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ak_event_user` (`event_id`,`user_id`),
  KEY `idx_ak_user` (`user_id`),
  KEY `idx_ak_event` (`event_id`),
  KEY `idx_ak_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `anwesenheits_kontrolle`
--

INSERT INTO `anwesenheits_kontrolle` (`id`, `event_id`, `user_id`, `status`, `recorded_at`) VALUES
(1, 2, 2, 'present', '2025-10-25 16:18:26'),
(2, 2, 2, 'present', '2025-10-25 13:27:09'),
(15, 4, 3, 'present', '2025-10-26 12:42:40'),
(16, 4, 1, 'present', '2025-10-26 12:57:31'),
(17, 4, 6, 'present', '2025-10-26 12:58:36'),
(18, 4, 4, 'present', '2025-10-26 13:45:30'),
(19, 4, 5, 'present', '2025-10-26 13:46:41');

-- --------------------------------------------------------

--
-- Table structure for table `nutzer`
--

DROP TABLE IF EXISTS `nutzer`;
CREATE TABLE IF NOT EXISTS `nutzer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `nutzer`
--

INSERT INTO `nutzer` (`id`, `name`, `role`, `department_id`) VALUES
(1, 'Alice', 'admin', 1),
(2, 'Bob', 'manager', 2),
(3, 'Charlie', 'viewer', 3),
(4, 'David', 'viewer', 3);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `data` mediumtext,
  `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `data`, `last_activity`, `created_at`) VALUES
('55sq7u2ppqujn8nrjoqsarsct0', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '', '2025-10-24 12:27:22', '2025-10-24 12:27:22'),
('7uvbj2rfv80k7u3ftv3f9qqr7f', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '', '2025-10-24 11:59:01', '2025-10-24 11:59:01'),
('9knl6a8s3ohchvruhte30kfaru', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '', '2025-10-24 11:42:43', '2025-10-24 11:42:43'),
('gstfsniurji0qo6rofmq5k7o8e', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '', '2025-10-24 11:51:55', '2025-10-24 11:51:55'),
('j88apmgarudofcm75krn0q8ldq', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '', '2025-10-25 12:47:25', '2025-10-25 12:47:25');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','manager','viewer') NOT NULL DEFAULT 'viewer',
  `department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `department_id`, `created_at`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$10$V6IQJAQ60OE.JT1xypWaoO8CbH4/gzQmUGJmaLnMNCsIg9C8KWgKu', 'admin', NULL, '2025-10-24 11:30:53'),
(2, 'Manager 1', 'manager1@example.com', '$2y$10$V6IQJAQ60OE.JT1xypWaoO8CbH4/gzQmUGJmaLnMNCsIg9C8KWgKu', 'manager', 1, '2025-10-24 11:30:53'),
(3, 'Manager 2', 'manager2@example.com', '$2y$10$V6IQJAQ60OE.JT1xypWaoO8CbH4/gzQmUGJmaLnMNCsIg9C8KWgKu', 'manager', 2, '2025-10-24 11:30:53'),
(4, 'Viewer 1', 'viewer1@example.com', '$2y$10$V6IQJAQ60OE.JT1xypWaoO8CbH4/gzQmUGJmaLnMNCsIg9C8KWgKu', 'viewer', 1, '2025-10-24 11:30:53'),
(5, 'Viewer 21', 'viewer2@example.com', '$2y$10$Y0u7QKZKqPu3F7Hr2n3OHe4H4zM3lO9BV1Zf6xEdnM1HfyEXNqek6', 'viewer', 2, '2025-10-24 11:30:53'),
(6, 'Admin1', 'admin1@example.com', '$2y$10$V6IQJAQ60OE.JT1xypWaoO8CbH4/gzQmUGJmaLnMNCsIg9C8KWgKu', 'admin', 0, '2025-10-24 11:42:21');

-- --------------------------------------------------------

--
-- Table structure for table `veranstaltung`
--

DROP TABLE IF EXISTS `veranstaltung`;
CREATE TABLE IF NOT EXISTS `veranstaltung` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `event_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_events_date` (`event_date`),
  KEY `idx_events_type_date` (`event_type`,`event_date`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `veranstaltung`
--

INSERT INTO `veranstaltung` (`id`, `name`, `event_type`, `event_date`, `created_at`) VALUES
(2, 'test11', 'test2', '2025-10-26', '2025-10-25 12:18:38'),
(4, 'test', 'newevents', '2025-10-27', '2025-10-26 15:06:43');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anwesenheits_kontrolle`
--
ALTER TABLE `anwesenheits_kontrolle`
  ADD CONSTRAINT `anwesenheits_kontrolle_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `veranstaltung` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `anwesenheits_kontrolle_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
