-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 16, 2026 at 03:07 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `e-commerce_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_lockouts`
--

CREATE TABLE `account_lockouts` (
  `id` int(11) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `locked_until` datetime NOT NULL,
  `attempt_count` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` varchar(50) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `backup_logs`
--

CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `created_by_username` varchar(100) NOT NULL,
  `created_by_role` enum('admin','staff','user') NOT NULL,
  `file_size` bigint(20) DEFAULT NULL COMMENT 'File size in bytes',
  `backup_path` varchar(500) DEFAULT NULL,
  `status` enum('success','failed','deleted') DEFAULT 'success',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `backup_logs`
--

INSERT INTO `backup_logs` (`id`, `filename`, `created_by_user_id`, `created_by_username`, `created_by_role`, `file_size`, `backup_path`, `status`, `ip_address`, `created_at`) VALUES
(1, 'backup_2026-02-03_12-44-18.sql', 30, 'Gab', 'admin', 37462, 'C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-03_12-44-18.sql', 'deleted', '::1', '2026-02-03 04:44:18'),
(2, 'backup_2026-02-03_17-25-56.sql', 30, 'Gab', 'admin', 38459, 'C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-03_17-25-56.sql', 'deleted', '::1', '2026-02-03 09:25:56'),
(3, 'backup_2026-02-03_17-27-28.sql', 137, 'mandy', 'admin', 38826, 'C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-03_17-27-28.sql', 'deleted', '::1', '2026-02-03 09:27:28'),
(4, 'backup_2026-02-03_17-27-34.sql', 137, 'mandy', 'admin', 39063, 'C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-03_17-27-34.sql', 'deleted', '::1', '2026-02-03 09:27:34'),
(5, 'backup_2026-02-03_17-27-35.sql', 137, 'mandy', 'admin', 39300, 'C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-03_17-27-35.sql', 'deleted', '::1', '2026-02-03 09:27:35'),
(6, 'backup_2026-02-03_17-27-42.sql', 137, 'mandy', 'admin', 39537, 'C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-03_17-27-42.sql', 'success', '::1', '2026-02-03 09:27:42'),
(7, 'backup_2026-02-03_17-27-42.sql', 137, 'mandy', 'admin', 39774, 'C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-03_17-27-42.sql', 'deleted', '::1', '2026-02-03 09:27:42'),
(8, 'backup_2026-02-03_17-29-42.sql', 137, 'mandy', 'admin', 40035, 'C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-03_17-29-42.sql', 'success', '::1', '2026-02-03 09:29:42'),
(9, 'E-commerce_2026-02-03_17-33-28.sql', 137, 'mandy', 'admin', 40272, 'C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/E-commerce_2026-02-03_17-33-28.sql', 'success', '::1', '2026-02-03 09:33:28'),
(10, 'E-commerce_2026-02-03_17-45-02.sql', 30, 'Gab', 'admin', 41084, 'C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/E-commerce_2026-02-03_17-45-02.sql', 'success', '::1', '2026-02-03 09:45:02'),
(11, 'backup_2026-02-06_18-01-30.sql', 30, 'Gab', 'admin', 41327, 'C:\\xampp\\htdocs\\E-commerce\\app\\views\\admin\\actions/backups/backup_2026-02-06_18-01-30.sql', 'success', '::1', '2026-02-06 10:01:30');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`, `description`, `status`) VALUES
(2, 'Bottoms', '2025-08-29 15:46:29', 'Sample', 'active'),
(3, 'Outerwear', '2025-08-29 15:46:29', 'Sample', 'active'),
(4, 'Footwear', '2025-08-29 15:46:29', 'Sample', 'active'),
(5, 'Accessories', '2025-08-29 15:46:29', 'Sample', 'active'),
(11, 'Tops', '2025-09-05 12:42:43', 'sample', 'active'),
(15, 'On Sale', '2025-09-16 18:29:26', 'samplea', 'active'),
(16, 'New Arrivals', '2025-09-16 18:29:36', 'sample', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `identifier` varchar(255) NOT NULL COMMENT 'Email or IP address',
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` datetime NOT NULL DEFAULT current_timestamp(),
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `identifier`, `ip_address`, `attempt_time`, `user_agent`) VALUES
(32, 'mlbbplays7@gmail.com', '::1', '2026-01-14 11:13:25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(33, 'mlbbplays7@gmail.com', '::1', '2026-01-14 11:32:43', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(41, 'admin123@example.com', '::1', '2026-01-14 11:58:00', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(42, 'admin123@example.com', '::1', '2026-01-14 11:58:37', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
(47, 'mandyadmin321@gmail.com', '::1', '2026-01-25 10:49:57', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(52, 'mandyfrancsico895@gmail.com', '::1', '2026-01-25 11:22:15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(53, 'mandyfrancsico895@gmail.com', '::1', '2026-01-25 11:23:04', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(54, 'mandyfrancsico895@gmail.com', '::1', '2026-01-25 11:23:10', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(55, 'mandyfrancsico895@gmail.com', '::1', '2026-01-25 11:24:34', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(59, 'mandyfrance895@gmail.com', '::1', '2026-01-28 21:59:41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(62, 'aiahdizon18@gmail.com', '::1', '2026-01-28 22:17:47', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(64, 'admin3321@gmail.com', '::1', '2026-01-29 17:21:07', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(65, 'admin76432321@gmail.com', '::1', '2026-01-29 17:31:46', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(69, 'admin1234@gmail.com', '::1', '2026-01-29 22:48:41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(70, 'admin123@gmail.com', '::1', '2026-01-29 22:50:03', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(71, 'staff321@gmd', '::1', '2026-01-31 01:33:20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(78, 'staffff4090@gmail.com', '::1', '2026-02-01 22:37:39', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(79, 'staffff4090@gmail.com', '::1', '2026-02-02 00:32:24', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(81, 'stafer@gmail.com', '::1', '2026-02-02 11:59:24', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(84, 'admin@gmail.com', '::1', '2026-02-02 15:14:06', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(86, 'try1@gmail.com', '::1', '2026-02-02 15:28:14', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(88, 'try1@gmail.com', '::1', '2026-02-02 16:00:46', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(95, 'admin321@gmail.comy', '::1', '2026-02-06 18:36:35', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
(96, 'mandyfrancisco895@gmai.com', '::1', '2026-02-14 00:40:11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0'),
(97, 'mandyfrance84@gmai.com', '::1', '2026-02-16 20:55:51', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_logs`
--

CREATE TABLE `maintenance_logs` (
  `id` int(11) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `action_performed` text NOT NULL,
  `status` varchar(50) DEFAULT 'Success',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `maintenance_logs`
--

INSERT INTO `maintenance_logs` (`id`, `admin_name`, `action_performed`, `status`, `created_at`) VALUES
(1, 'Gab', 'Created new admin account: mandy', 'Success', '2026-01-29 00:23:00'),
(2, 'Gab', 'Created new account: STAFFTRY (Role: Staff)', 'Success', '2026-02-02 15:19:37'),
(3, 'Gab', 'Created new account: try1 (Role: Staff)', 'Success', '2026-02-02 15:27:07'),
(4, 'Gab', 'Created new Staff account: ihaihafiaifhia', 'Success', '2026-02-02 22:38:01'),
(5, 'Gab', 'Created new Staff account: gab4', 'Success', '2026-02-02 22:38:33'),
(6, 'Gab', 'Created new Admin account: admin3214', 'Success', '2026-02-02 22:47:37'),
(7, 'Gab', 'Created new Staff account: TRYMANDY', 'Success', '2026-02-02 22:58:08'),
(8, 'Gab', 'Created new Staff account: try1111', 'Success', '2026-02-02 23:10:08'),
(9, 'Gab', 'Created new Staff account: trystaff', 'Success', '2026-02-02 23:11:01'),
(10, 'Gab', 'Created new Staff account: dwfsf', 'Success', '2026-02-03 00:43:10'),
(11, 'Gab', 'Created new Staff account: fsfs', 'Success', '2026-02-03 01:36:04'),
(12, 'Gab', 'Created new Staff account: dsdsds', 'Success', '2026-02-03 02:00:38'),
(13, 'Gab', 'Created new Staff account: dsds', 'Success', '2026-02-03 02:05:59'),
(14, 'Gab', 'Deleted Staff account: dsds (ID: 134)', 'Success', '2026-02-03 17:22:51'),
(15, 'Gab', 'Created new Staff account: osafmaomfoamfa', 'Success', '2026-02-03 17:23:10'),
(16, 'Gab', 'Deleted Staff account: trystaff (ID: 130)', 'Success', '2026-02-03 17:23:14'),
(17, 'Gab', 'Deleted Admin account: mandy (ID: 91)', 'Success', '2026-02-03 17:26:44'),
(18, 'Gab', 'Deleted Admin account: admin3214 (ID: 127)', 'Success', '2026-02-03 17:26:47'),
(19, 'Gab', 'Created new Admin account: mandy', 'Success', '2026-02-03 17:27:03');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
  `shipping_address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `paypal_order_id` varchar(100) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `status`, `shipping_address`, `payment_method`, `paypal_order_id`, `payment_status`, `created_at`, `updated_at`) VALUES
(68, 135, 'ORD-20260213-698F46890155C', 1000.00, 'cancelled', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 15:43:05', '2026-02-13 15:46:03'),
(69, 135, 'ORD-20260213-698F472DA0AEF', 700.00, 'cancelled', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 15:45:49', '2026-02-13 15:45:59'),
(70, 135, 'ORD-20260213-698F4842E7361', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 15:50:26', '2026-02-13 15:50:26'),
(71, 135, 'ORD-20260213-698F4879617F5', 600.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 15:51:21', '2026-02-13 15:51:21'),
(72, 135, 'ORD-20260213-698F48F6B529D', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 15:53:26', '2026-02-13 15:53:26'),
(73, 135, 'ORD-20260214-698F505B64A08', 600.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 16:24:59', '2026-02-13 16:24:59'),
(74, 135, 'ORD-20260214-698F50A88F9E2', 5000.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 16:26:16', '2026-02-13 16:26:16'),
(75, 135, 'ORD-20260214-698F531AEFC16', 799.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 16:36:42', '2026-02-13 16:36:42'),
(76, 135, 'ORD-20260214-698F533347065', 600.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 16:37:07', '2026-02-13 16:37:07'),
(77, 135, 'ORD-20260214-698F536F24F94', 600.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 16:38:07', '2026-02-13 16:38:07'),
(78, 135, 'ORD-20260214-698F539F12375', 700.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 16:38:55', '2026-02-13 16:38:55'),
(79, 135, 'ORD-20260214-698F540BEB66C', 7000.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 16:40:43', '2026-02-13 16:40:43'),
(80, 135, 'ORD-20260214-698F541FEEEA6', 600.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 16:41:03', '2026-02-13 16:41:03'),
(81, 135, 'ORD-20260214-698F55338F965', 600.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 16:45:39', '2026-02-13 16:45:39'),
(82, 138, 'ORD-20260214-698F59D5DA903', 1200.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:05:25', '2026-02-13 17:05:25'),
(83, 138, 'ORD-20260214-698F59E4D66EB', 700.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:05:40', '2026-02-13 17:05:40'),
(84, 138, 'ORD-20260214-698F5B143D5AE', 600.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:10:44', '2026-02-13 17:10:44'),
(85, 138, 'ORD-20260214-698F5B1440235', 600.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:10:44', '2026-02-13 17:10:44'),
(86, 138, 'ORD-20260214-698F5B2CE152D', 600.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:11:08', '2026-02-13 17:11:08'),
(87, 138, 'ORD-20260214-698F5B2CE38F8', 600.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:11:08', '2026-02-13 17:11:08'),
(88, 138, 'ORD-20260214-698F5B5AB69AB', 600.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:11:54', '2026-02-13 17:11:54'),
(89, 138, 'ORD-20260214-698F5B5AB9706', 600.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:11:54', '2026-02-13 17:11:54'),
(90, 138, 'ORD-20260214-698F5B91A972B', 600.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:12:49', '2026-02-13 17:12:49'),
(91, 138, 'ORD-20260214-698F5B91AAC9E', 600.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:12:49', '2026-02-13 17:12:49'),
(92, 138, 'ORD-20260214-698F5BD40C880', 600.00, 'pending', 'dsdsd', 'PayPal', '0BE86078GS158270N', 'paid', '2026-02-13 17:13:56', '2026-02-13 17:13:56'),
(93, 138, 'ORD-20260214-698F5D62EC95D', 500.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:20:34', '2026-02-13 17:20:34'),
(94, 138, 'ORD-20260214-698F5D62EE1D4', 500.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:20:34', '2026-02-13 17:20:34'),
(95, 138, 'ORD-20260214-698F5D7823548', 500.00, 'pending', 'dsdsd', 'PayPal', '1SC77355A8801852V', 'paid', '2026-02-13 17:20:56', '2026-02-13 17:20:56'),
(96, 135, 'ORD-20260214-698F5DD48B236', 1100.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:22:28', '2026-02-13 17:22:28'),
(97, 135, 'ORD-20260214-698F5DD48D9D0', 1100.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:22:28', '2026-02-13 17:22:28'),
(98, 135, 'ORD-20260214-698F5DE281103', 600.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:22:42', '2026-02-13 17:22:42'),
(99, 135, 'ORD-20260214-698F5DE283109', 600.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-13 17:22:42', '2026-02-13 17:22:42'),
(100, 135, 'ORD-20260214-698F5E4227BD1', 500.00, 'pending', 'bcbcbc', 'PayPal', '19R57248EX5446737', 'paid', '2026-02-13 17:24:18', '2026-02-13 17:24:18'),
(101, 135, 'ORD-20260214-698F5E8A24A58', 500.00, 'pending', 'bcbcbc', 'PayPal', '40L932867R284074L', 'paid', '2026-02-13 17:25:30', '2026-02-13 17:25:30'),
(102, 135, 'ORD-20260214-698F5EC48E18B', 1200.00, 'pending', 'bcbcbc', 'PayPal', '2TL483338T3119927', 'paid', '2026-02-13 17:26:28', '2026-02-13 17:26:28'),
(103, 135, 'ORD-20260214-698F5F25B4EE5', 1800.00, 'pending', 'bcbcbc', 'PayPal', '9SN801860F297970T', 'paid', '2026-02-13 17:28:05', '2026-02-13 17:28:05'),
(104, 135, 'ORD-20260214-69904E7BC27FA', 700.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-14 10:29:15', '2026-02-14 10:29:15'),
(105, 135, 'ORD-20260214-69904E7BCAD17', 700.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-14 10:29:15', '2026-02-14 10:29:15'),
(106, 135, 'ORD-20260214-69904EF83EDA5', 1200.00, 'pending', 'bcbcbc', 'PayPal', '5455233132284310M', 'paid', '2026-02-14 10:31:20', '2026-02-14 10:31:20'),
(107, 138, 'ORD-20260215-6991D95E257CA', 1200.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-15 14:34:06', '2026-02-15 14:34:06'),
(108, 138, 'ORD-20260215-6991D99F7C20F', 600.00, 'pending', 'dsdsd', 'PayPal', '03A63437XN769364J', 'paid', '2026-02-15 14:35:11', '2026-02-15 14:35:11'),
(109, 135, 'ORD-20260215-6991DBCEE3070', 600.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-15 14:44:30', '2026-02-15 14:44:30'),
(110, 135, 'ORD-20260215-6991DBD9A02C3', 600.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-15 14:44:41', '2026-02-15 14:44:41'),
(111, 135, 'ORD-20260215-6991DBF25D48D', 600.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-15 14:45:06', '2026-02-15 14:45:06'),
(112, 135, 'ORD-20260215-6991DD68368A1', 600.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-15 14:51:20', '2026-02-15 14:51:20'),
(113, 135, 'ORD-20260215-6991DDD41BE65', 1100.00, 'pending', 'bcbcbc', 'PayPal', '3B5490445F531301K', 'paid', '2026-02-15 14:53:08', '2026-02-15 14:53:08'),
(114, 135, 'ORD-20260215-6991DE876EB30', 500.00, 'pending', 'bcbcbc', 'PayPal', '0YV300184Y367715T', 'paid', '2026-02-15 14:56:07', '2026-02-15 14:56:07'),
(115, 135, 'ORD-20260215-6991DEAC48DCC', 700.00, 'pending', 'bcbcbc', 'PayPal', '58H131261H9430532', 'paid', '2026-02-15 14:56:44', '2026-02-15 14:56:44'),
(116, 135, 'ORD-20260215-6991DFB2DC8DB', 600.00, 'pending', 'bcbcbc', 'PayPal', '2VK502037W7435902', 'paid', '2026-02-15 15:01:06', '2026-02-15 15:01:06'),
(117, 135, 'ORD-20260215-6991DFF84175E', 500.00, 'pending', 'bcbcbc', 'PayPal', '6BL47115KD357404S', 'paid', '2026-02-15 15:02:16', '2026-02-15 15:02:16'),
(118, 135, 'ORD-20260215-6991E11D128AF', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-15 15:07:09', '2026-02-15 15:07:09'),
(119, 135, 'ORD-20260215-6991E14FB43D5', 600.00, 'pending', 'bcbcbc', 'PayPal', '72994656KN024453D', 'paid', '2026-02-15 15:07:59', '2026-02-15 15:07:59'),
(120, 135, 'ORD-20260215-6991E4BFC93AB', 600.00, 'pending', 'bcbcbc', 'PayPal', '37E390000A1658511', 'paid', '2026-02-15 15:22:39', '2026-02-15 15:22:39'),
(121, 135, 'ORD-20260215-6991E57A2B946', 600.00, 'pending', 'bcbcbc', 'PayPal', '11G86309R4951083K', 'paid', '2026-02-15 15:25:46', '2026-02-15 15:25:46'),
(122, 135, 'ORD-20260215-6991E862320B1', 5000.00, 'pending', 'bcbcbc', 'PayPal', '8Y645351NV9758412', 'paid', '2026-02-15 15:38:10', '2026-02-15 15:38:10'),
(123, 135, 'ORD-20260215-6991E89491A37', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-15 15:39:00', '2026-02-15 15:39:00'),
(124, 135, 'ORD-20260215-6991E8CD89C0F', 500.00, 'shipped', 'bcbcbc', 'PayPal', '5KM150131F683634C', 'paid', '2026-02-15 15:39:57', '2026-02-15 15:44:51'),
(125, 138, 'ORD-20260215-6991EC5A69606', 700.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-15 15:55:06', '2026-02-15 15:55:06'),
(126, 138, 'ORD-20260215-6991EC9BC9B8A', 2000.00, 'pending', 'dsdsd', 'PayPal', '28A03394V14880228', 'paid', '2026-02-15 15:56:11', '2026-02-15 15:56:11'),
(127, 138, 'ORD-20260215-6991ED0541CCB', 1500.00, 'pending', 'dsdsd', 'PayPal', '6G0542527Y636740N', 'paid', '2026-02-15 15:57:57', '2026-02-15 15:57:57'),
(128, 138, 'ORD-20260216-6991EE06BB66B', 4000.00, 'pending', 'dsdsd', 'PayPal', '8SW382329J5289705', 'paid', '2026-02-15 16:02:14', '2026-02-15 16:02:14'),
(129, 138, 'ORD-20260216-6991EE57CD39D', 1500.00, 'pending', 'dsdsd', 'PayPal', '2CH50074YA001471W', 'paid', '2026-02-15 16:03:35', '2026-02-15 16:03:35'),
(130, 138, 'ORD-20260216-6991EEA8BE6C8', 799.00, 'pending', 'dsdsd', 'PayPal', '3FR60833HY035920G', 'paid', '2026-02-15 16:04:56', '2026-02-15 16:04:56'),
(131, 138, 'ORD-20260216-6991EF3943413', 5000.00, 'pending', 'dsdsd', 'PayPal', '9NA16074NW569291E', 'paid', '2026-02-15 16:07:21', '2026-02-15 16:07:21'),
(132, 138, 'ORD-20260216-6991F08B79EB9', 5000.00, 'pending', 'dsdsd', 'PayPal', '38S47712NM090512H', 'paid', '2026-02-15 16:12:59', '2026-02-15 16:12:59'),
(133, 138, 'ORD-20260216-6991F16208539', 10000.00, 'pending', 'dsdsd', 'PayPal', '9XS14861RN4896529', 'paid', '2026-02-15 16:16:34', '2026-02-15 16:16:34'),
(134, 138, 'ORD-20260216-6991F277091AA', 799.00, 'pending', 'dsdsd', 'PayPal', '8SA125059J6435616', 'paid', '2026-02-15 16:21:11', '2026-02-15 16:21:11'),
(135, 138, 'ORD-20260216-6991F337B559B', 1200.00, 'pending', 'dsdsd', 'PayPal', '9KC32403TY118391T', 'paid', '2026-02-15 16:24:23', '2026-02-15 16:24:23'),
(136, 138, 'ORD-20260216-6991F46D73526', 600.00, 'pending', 'dsdsd', 'PayPal', '4S9530843S5548746', 'paid', '2026-02-15 16:29:33', '2026-02-15 16:29:33'),
(137, 138, 'ORD-20260216-6991F492D0AE8', 500.00, 'pending', 'dsdsd', 'PayPal', '9B6333447A8735915', 'paid', '2026-02-15 16:30:10', '2026-02-15 16:30:10'),
(138, 138, 'ORD-20260216-6991F5C3A1993', 500.00, 'pending', 'dsdsd', 'PayPal', '0B4606754Y6553459', 'paid', '2026-02-15 16:35:15', '2026-02-15 16:35:15'),
(139, 138, 'ORD-20260216-6991F8782EFF9', 1700.00, 'pending', 'dsdsd', 'PayPal', '1FU83190ML9640423', 'paid', '2026-02-15 16:46:48', '2026-02-15 16:46:48'),
(140, 138, 'ORD-20260216-6991F8AD85C98', 700.00, 'pending', 'dsdsd', 'PayPal', '3WT153954N9534732', 'paid', '2026-02-15 16:47:41', '2026-02-15 16:47:41'),
(141, 138, 'ORD-20260216-6991FB1444E43', 500.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-15 16:57:56', '2026-02-15 16:57:56'),
(142, 138, 'ORD-20260216-6991FB19D4CDA', 500.00, 'pending', 'dsdsd', 'Cash on Delivery (COD)', '', 'pending', '2026-02-15 16:58:01', '2026-02-15 16:58:01'),
(143, 138, 'ORD-20260216-6991FB30DF266', 600.00, 'pending', 'dsdsd', 'PayPal', '3FP06911YN698661A', 'paid', '2026-02-15 16:58:24', '2026-02-15 16:58:24'),
(144, 138, 'ORD-20260216-6991FC70D6E0D', 600.00, 'pending', 'dsdsd', 'PayPal', '1J652786S63152509', 'paid', '2026-02-15 17:03:44', '2026-02-15 17:03:44'),
(145, 138, 'ORD-20260216-6991FE512A460', 600.00, 'pending', 'dsdsd', 'PayPal', '6ND91442BV436540W', 'paid', '2026-02-15 17:11:45', '2026-02-15 17:11:45'),
(146, 135, 'ORD-20260216-6991FEC67F00B', 600.00, 'pending', 'bcbcbc', 'PayPal', '4GK28555UY692454H', 'paid', '2026-02-15 17:13:42', '2026-02-15 17:13:42'),
(147, 135, 'ORD-20260216-6991FF4C1F073', 500.00, 'pending', 'bcbcbc', 'PayPal', '4BS14907MH219390B', 'paid', '2026-02-15 17:15:56', '2026-02-15 17:15:56'),
(148, 135, 'ORD-20260216-699202111CF53', 5700.00, 'pending', 'bcbcbc', 'PayPal', '26V90990K1638541E', 'paid', '2026-02-15 17:27:45', '2026-02-15 17:27:45'),
(149, 135, 'ORD-20260216-699319AC030A1', 7200.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:20:44', '2026-02-16 13:20:44'),
(150, 135, 'ORD-20260216-699319B13E6FA', 7200.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:20:49', '2026-02-16 13:20:49'),
(151, 135, 'ORD-20260216-69931A350F60B', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:23:01', '2026-02-16 13:23:01'),
(152, 135, 'ORD-20260216-69931A392E17E', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:23:05', '2026-02-16 13:23:05'),
(153, 135, 'ORD-20260216-69931ABE23A86', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:25:18', '2026-02-16 13:25:18'),
(154, 135, 'ORD-20260216-69931AC2F15DF', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:25:22', '2026-02-16 13:25:22'),
(155, 135, 'ORD-20260216-69931B90C26FE', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:28:48', '2026-02-16 13:28:48'),
(156, 135, 'ORD-20260216-69931B95D292E', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:28:53', '2026-02-16 13:28:53'),
(157, 135, 'ORD-20260216-69931C657182C', 1000.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:32:21', '2026-02-16 13:32:21'),
(158, 135, 'ORD-20260216-69931D2FCF39E', 5000.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:35:43', '2026-02-16 13:35:43'),
(159, 135, 'ORD-20260216-69931D3551104', 5000.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:35:49', '2026-02-16 13:35:49'),
(160, 135, 'ORD-20260216-69931D655FD70', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:36:37', '2026-02-16 13:36:37'),
(161, 135, 'ORD-20260216-69931D6A6B5AC', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:36:42', '2026-02-16 13:36:42'),
(162, 135, 'ORD-20260216-69931D86AD658', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:37:10', '2026-02-16 13:37:10'),
(163, 135, 'ORD-20260216-69931D8BCC4D0', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:37:15', '2026-02-16 13:37:15'),
(164, 135, 'ORD-20260216-69931DEABB230', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:38:50', '2026-02-16 13:38:50'),
(165, 135, 'ORD-20260216-69931DEF9A789', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:38:55', '2026-02-16 13:38:55'),
(166, 135, 'ORD-20260216-69931E2BA8ACF', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:39:55', '2026-02-16 13:39:55'),
(167, 135, 'ORD-20260216-69931E31B81E7', 500.00, 'pending', 'bcbcbc', 'Cash on Delivery (COD)', '', 'pending', '2026-02-16 13:40:01', '2026-02-16 13:40:01'),
(168, 135, 'ORD-20260216-6993204BD078D', 1100.00, 'pending', 'bcbcbc', 'PayPal', '9YG45804KA581093W', 'paid', '2026-02-16 13:48:59', '2026-02-16 13:48:59');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_price`, `quantity`, `size`, `subtotal`, `created_at`) VALUES
(104, 68, 127, 'Graphical shirt', 500.00, 2, NULL, 1000.00, '2026-02-13 15:43:05'),
(105, 69, 125, 'Rich Boyz Shirt', 700.00, 1, NULL, 700.00, '2026-02-13 15:45:49'),
(106, 70, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-13 15:50:26'),
(107, 71, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 15:51:21'),
(108, 72, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-13 15:53:26'),
(109, 73, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 16:24:59'),
(110, 74, 124, 'Polo Jacket', 5000.00, 1, NULL, 5000.00, '2026-02-13 16:26:16'),
(111, 75, 123, 'Leather Jacket', 799.00, 1, NULL, 799.00, '2026-02-13 16:36:42'),
(112, 76, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 16:37:07'),
(113, 77, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 16:38:07'),
(114, 78, 125, 'Rich Boyz Shirt', 700.00, 1, NULL, 700.00, '2026-02-13 16:38:55'),
(115, 79, 122, 'Track Suit', 7000.00, 1, NULL, 7000.00, '2026-02-13 16:40:43'),
(116, 80, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 16:41:03'),
(117, 81, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 16:45:39'),
(118, 82, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-13 17:05:25'),
(119, 82, 125, 'Rich Boyz Shirt', 700.00, 1, NULL, 700.00, '2026-02-13 17:05:25'),
(120, 83, 125, 'Rich Boyz Shirt', 700.00, 1, NULL, 700.00, '2026-02-13 17:05:40'),
(121, 84, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 17:10:44'),
(122, 85, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 17:10:44'),
(123, 86, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 17:11:08'),
(124, 87, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 17:11:08'),
(125, 88, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 17:11:54'),
(126, 89, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 17:11:54'),
(127, 90, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 17:12:49'),
(128, 91, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 17:12:49'),
(129, 92, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 17:13:56'),
(130, 93, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-13 17:20:34'),
(131, 94, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-13 17:20:34'),
(132, 95, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-13 17:20:56'),
(133, 96, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-13 17:22:28'),
(134, 96, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 17:22:28'),
(135, 97, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-13 17:22:28'),
(136, 97, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 17:22:28'),
(137, 98, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 17:22:42'),
(138, 99, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 17:22:42'),
(139, 100, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-13 17:24:18'),
(140, 101, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-13 17:25:30'),
(141, 102, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-13 17:26:28'),
(142, 102, 125, 'Rich Boyz Shirt', 700.00, 1, NULL, 700.00, '2026-02-13 17:26:28'),
(143, 103, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-13 17:28:05'),
(144, 103, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-13 17:28:05'),
(145, 103, 125, 'Rich Boyz Shirt', 700.00, 1, NULL, 700.00, '2026-02-13 17:28:05'),
(146, 104, 125, 'Rich Boyz Shirt', 700.00, 1, NULL, 700.00, '2026-02-14 10:29:15'),
(147, 105, 125, 'Rich Boyz Shirt', 700.00, 1, NULL, 700.00, '2026-02-14 10:29:15'),
(148, 106, 125, 'Rich Boyz Shirt', 700.00, 1, NULL, 700.00, '2026-02-14 10:31:20'),
(149, 106, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-14 10:31:20'),
(150, 107, 125, 'Rich Boyz Shirt', 700.00, 1, NULL, 700.00, '2026-02-15 14:34:06'),
(151, 107, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-15 14:34:06'),
(152, 108, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-15 14:35:11'),
(153, 109, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-15 14:44:30'),
(154, 110, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-15 14:44:41'),
(155, 111, 126, 'Floral Shirt', 600.00, 1, 'M', 600.00, '2026-02-15 14:45:06'),
(156, 112, 126, 'Floral Shirt', 600.00, 1, 'M', 600.00, '2026-02-15 14:51:20'),
(157, 113, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-15 14:53:08'),
(158, 113, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-15 14:53:08'),
(159, 114, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-15 14:56:07'),
(160, 115, 125, 'Rich Boyz Shirt', 700.00, 1, NULL, 700.00, '2026-02-15 14:56:44'),
(161, 116, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-15 15:01:06'),
(162, 117, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-15 15:02:16'),
(163, 118, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-15 15:07:09'),
(164, 119, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-15 15:07:59'),
(165, 120, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-15 15:22:39'),
(166, 121, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-15 15:25:46'),
(167, 122, 124, 'Polo Jacket', 5000.00, 1, NULL, 5000.00, '2026-02-15 15:38:10'),
(168, 123, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-15 15:39:00'),
(169, 124, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-15 15:39:57'),
(170, 125, 125, 'Rich Boyz Shirt', 700.00, 1, NULL, 700.00, '2026-02-15 15:55:06'),
(171, 126, 121, 'UND Hoddie', 2000.00, 1, NULL, 2000.00, '2026-02-15 15:56:11'),
(172, 127, 119, 'Track Pants', 1500.00, 1, NULL, 1500.00, '2026-02-15 15:57:57'),
(173, 128, 121, 'UND Hoddie', 2000.00, 2, NULL, 4000.00, '2026-02-15 16:02:14'),
(174, 129, 120, 'Baggy Pants', 1500.00, 1, NULL, 1500.00, '2026-02-15 16:03:35'),
(175, 130, 123, 'Leather Jacket', 799.00, 1, NULL, 799.00, '2026-02-15 16:04:56'),
(176, 131, 124, 'Polo Jacket', 5000.00, 1, NULL, 5000.00, '2026-02-15 16:07:21'),
(177, 132, 124, 'Polo Jacket', 5000.00, 1, NULL, 5000.00, '2026-02-15 16:12:59'),
(178, 133, 124, 'Polo Jacket', 5000.00, 2, NULL, 10000.00, '2026-02-15 16:16:34'),
(179, 134, 123, 'Leather Jacket', 799.00, 1, NULL, 799.00, '2026-02-15 16:21:11'),
(180, 135, 126, 'Floral Shirt', 600.00, 2, NULL, 1200.00, '2026-02-15 16:24:23'),
(181, 136, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-15 16:29:33'),
(182, 137, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-15 16:30:10'),
(183, 138, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-15 16:35:15'),
(184, 139, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-15 16:46:48'),
(185, 139, 126, 'Floral Shirt', 600.00, 2, NULL, 1200.00, '2026-02-15 16:46:48'),
(186, 140, 125, 'Rich Boyz Shirt', 700.00, 1, NULL, 700.00, '2026-02-15 16:47:41'),
(187, 141, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-15 16:57:56'),
(188, 142, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-15 16:58:01'),
(189, 143, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-15 16:58:24'),
(190, 144, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-15 17:03:44'),
(191, 145, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-15 17:11:45'),
(192, 146, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-15 17:13:42'),
(193, 147, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-15 17:15:56'),
(194, 148, 125, 'Rich Boyz Shirt', 700.00, 1, NULL, 700.00, '2026-02-15 17:27:45'),
(195, 148, 124, 'Polo Jacket', 5000.00, 1, NULL, 5000.00, '2026-02-15 17:27:45'),
(196, 149, 126, 'Floral Shirt', 600.00, 2, NULL, 1200.00, '2026-02-16 13:20:44'),
(197, 149, 127, 'Graphical shirt', 500.00, 2, NULL, 1000.00, '2026-02-16 13:20:44'),
(198, 149, 124, 'Polo Jacket', 5000.00, 1, NULL, 5000.00, '2026-02-16 13:20:44'),
(199, 150, 126, 'Floral Shirt', 600.00, 2, NULL, 1200.00, '2026-02-16 13:20:49'),
(200, 150, 127, 'Graphical shirt', 500.00, 2, NULL, 1000.00, '2026-02-16 13:20:49'),
(201, 150, 124, 'Polo Jacket', 5000.00, 1, NULL, 5000.00, '2026-02-16 13:20:49'),
(202, 151, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:23:01'),
(203, 152, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:23:05'),
(204, 153, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:25:18'),
(205, 154, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:25:22'),
(206, 155, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:28:48'),
(207, 156, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:28:53'),
(208, 157, 127, 'Graphical shirt', 500.00, 2, NULL, 1000.00, '2026-02-16 13:32:21'),
(209, 158, 124, 'Polo Jacket', 5000.00, 1, NULL, 5000.00, '2026-02-16 13:35:43'),
(210, 159, 124, 'Polo Jacket', 5000.00, 1, NULL, 5000.00, '2026-02-16 13:35:49'),
(211, 160, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:36:37'),
(212, 161, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:36:42'),
(213, 162, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:37:10'),
(214, 163, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:37:15'),
(215, 164, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:38:50'),
(216, 165, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:38:55'),
(217, 166, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:39:55'),
(218, 167, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:40:01'),
(219, 168, 126, 'Floral Shirt', 600.00, 1, NULL, 600.00, '2026-02-16 13:48:59'),
(220, 168, 127, 'Graphical shirt', 500.00, 1, NULL, 500.00, '2026-02-16 13:48:59');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores password reset OTP codes';

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `otp_code`, `expires_at`, `used`, `used_at`, `created_at`, `ip_address`) VALUES
(1, 'gabrielvargas0423@gmail.com', '794700', '2025-11-30 22:47:43', 1, '2025-11-30 22:33:23', '2025-11-30 14:32:43', '::1'),
(2, 'gabrielvargas0423@gmail.com', '178531', '2025-11-30 22:50:49', 1, '2025-11-30 22:36:07', '2025-11-30 14:35:49', '::1'),
(3, 'gabrielvargas0423@gmail.com', '422666', '2025-11-30 22:51:36', 1, '2025-11-30 22:37:00', '2025-11-30 14:36:36', '::1'),
(4, 'gabrielvargas0423@gmail.com', '142193', '2025-11-30 22:56:34', 1, '2025-11-30 22:42:03', '2025-11-30 14:41:34', '::1'),
(5, 'gabrielvargas0423@gmail.com', '821272', '2025-12-01 18:27:46', 1, '2025-12-01 18:13:18', '2025-12-01 10:12:46', '::1'),
(6, 'gabrielvargas0423@gmail.com', '159095', '2025-12-01 21:29:59', 1, '2025-12-01 21:15:17', '2025-12-01 13:14:59', '::1'),
(7, 'Mandyfrancisco895@gmail.com', '610767', '2025-12-04 20:54:59', 1, NULL, '2025-12-04 12:39:59', '::1'),
(8, 'jamespeterduran826@gmail.com', '615979', '2025-12-04 20:55:29', 0, NULL, '2025-12-04 12:40:29', '::1'),
(9, 'gabrielvargas0423@gmail.com', '253520', '2025-12-04 21:21:42', 1, '2025-12-04 21:07:10', '2025-12-04 13:06:42', '::1'),
(10, 'Mandyfrancisco895@gmail.com', '109436', '2026-01-14 11:54:07', 1, '2026-01-14 11:39:42', '2026-01-14 03:39:07', '::1'),
(11, 'Mandyfrancisco895@gmail.com', '595745', '2026-01-14 12:04:24', 1, NULL, '2026-01-14 03:49:24', '::1'),
(12, 'Mandyfrancisco895@gmail.com', '637190', '2026-01-14 12:04:28', 1, NULL, '2026-01-14 03:49:28', '::1'),
(13, 'Mandyfrancisco895@gmail.com', '922981', '2026-01-14 12:05:41', 1, '2026-01-14 11:51:21', '2026-01-14 03:50:41', '::1'),
(14, 'Mandyfrancisco895@gmail.com', '334581', '2026-01-14 16:07:51', 1, NULL, '2026-01-14 07:52:51', '::1'),
(15, 'gabrielvargas0423@gmail.com', '950719', '2026-01-14 16:34:28', 1, NULL, '2026-01-14 08:19:28', '::1'),
(16, 'gabrielvargas0423@gmail.com', '186310', '2026-01-14 17:09:39', 0, NULL, '2026-01-14 08:54:39', '::1'),
(17, 'Mandyfrancisco895@gmail.com', '566354', '2026-01-25 10:47:26', 1, NULL, '2026-01-25 02:32:26', '::1'),
(18, 'mandyfrance84@gmail.com', '481425', '2026-01-25 11:07:02', 1, NULL, '2026-01-25 02:52:02', '::1'),
(19, 'Mandyfrancisco895@gmail.com', '115651', '2026-01-25 11:08:27', 1, '2026-01-25 10:55:29', '2026-01-25 02:53:27', '::1'),
(20, 'mandyfrance84@gmail.com', '763260', '2026-01-25 11:41:04', 1, '2026-01-25 11:28:30', '2026-01-25 03:26:04', '::1'),
(21, 'mandyfrancisco895@gmail.com', '685734', '2026-02-06 18:40:12', 0, NULL, '2026-02-06 10:25:12', '::1');

-- --------------------------------------------------------

--
-- Table structure for table `paypal_transactions`
--

CREATE TABLE `paypal_transactions` (
  `id` int(11) NOT NULL,
  `paypal_order_id` varchar(100) NOT NULL,
  `status` varchar(50) NOT NULL,
  `payer_email` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'PHP',
  `transaction_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `paypal_transactions`
--

INSERT INTO `paypal_transactions` (`id`, `paypal_order_id`, `status`, `payer_email`, `amount`, `currency`, `transaction_data`, `created_at`) VALUES
(1, '2GM46407E9880714T', 'COMPLETED', 'sb-j0kiz49407225@personal.example.com', 1600.00, 'PHP', '{\"success\":true,\"order_id\":\"2GM46407E9880714T\",\"status\":\"COMPLETED\",\"payer\":{\"name\":{\"given_name\":\"John\",\"surname\":\"Doe\"},\"email_address\":\"sb-j0kiz49407225@personal.example.com\",\"payer_id\":\"FS7DLNB2NXVAU\",\"address\":{\"country_code\":\"PH\"}},\"purchase_units\":[{\"reference_id\":\"ORDER-698f43c5de388\",\"shipping\":{\"name\":{\"full_name\":\"John Doe\"},\"address\":{\"address_line_1\":\"451 Juan Luna Street Binondo\",\"admin_area_2\":\"Manila\",\"admin_area_1\":\"Manila\",\"postal_code\":\"1006\",\"country_code\":\"PH\"}},\"payments\":{\"captures\":[{\"id\":\"98D93394U80659925\",\"status\":\"COMPLETED\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"1600.00\"},\"final_capture\":true,\"seller_protection\":{\"status\":\"ELIGIBLE\",\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"]},\"seller_receivable_breakdown\":{\"gross_amount\":{\"currency_code\":\"PHP\",\"value\":\"1600.00\"},\"paypal_fee\":{\"currency_code\":\"PHP\",\"value\":\"69.40\"},\"net_amount\":{\"currency_code\":\"PHP\",\"value\":\"1530.60\"}},\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/98D93394U80659925\",\"rel\":\"self\",\"method\":\"GET\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/payments\\/captures\\/98D93394U80659925\\/refund\",\"rel\":\"refund\",\"method\":\"POST\"},{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/2GM46407E9880714T\",\"rel\":\"up\",\"method\":\"GET\"}],\"create_time\":\"2026-02-13T15:31:16Z\",\"update_time\":\"2026-02-13T15:31:16Z\"}]}}]}', '2026-02-13 15:32:10'),
(2, '9YG45804KA581093W', 'COMPLETED', 'sb-j0kiz49407225@personal.example.com', 1100.00, 'PHP', '{\"id\":\"9YG45804KA581093W\",\"intent\":\"CAPTURE\",\"status\":\"COMPLETED\",\"purchase_units\":[{\"reference_id\":\"default\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"1100.00\",\"breakdown\":{\"item_total\":{\"currency_code\":\"PHP\",\"value\":\"1100.00\"},\"shipping\":{\"currency_code\":\"PHP\",\"value\":\"0.00\"},\"handling\":{\"currency_code\":\"PHP\",\"value\":\"0.00\"},\"insurance\":{\"currency_code\":\"PHP\",\"value\":\"0.00\"},\"shipping_discount\":{\"currency_code\":\"PHP\",\"value\":\"0.00\"}}},\"payee\":{\"email_address\":\"sb-um47lo49332495@business.example.com\",\"merchant_id\":\"CJZEM3M73HDEE\",\"display_data\":{\"brand_name\":\"EMPIRE STREETWEAR\"}},\"description\":\"Empire Streetwear Purchase\",\"items\":[{\"name\":\"Floral Shirt\",\"unit_amount\":{\"currency_code\":\"PHP\",\"value\":\"600.00\"},\"tax\":{\"currency_code\":\"PHP\",\"value\":\"0.00\"},\"quantity\":\"1\",\"description\":\"Size: N\\/A\",\"category\":\"PHYSICAL_GOODS\"},{\"name\":\"Graphical shirt\",\"unit_amount\":{\"currency_code\":\"PHP\",\"value\":\"500.00\"},\"tax\":{\"currency_code\":\"PHP\",\"value\":\"0.00\"},\"quantity\":\"1\",\"description\":\"Size: N\\/A\",\"category\":\"PHYSICAL_GOODS\"}],\"shipping\":{\"name\":{\"full_name\":\"mandy francisco\"},\"address\":{\"address_line_1\":\"451 Juan Luna Street Binondo\",\"admin_area_2\":\"Manila\",\"admin_area_1\":\"Manila\",\"postal_code\":\"1006\",\"country_code\":\"PH\"}},\"payments\":{\"captures\":[{\"id\":\"7AD1290637942104M\",\"status\":\"COMPLETED\",\"amount\":{\"currency_code\":\"PHP\",\"value\":\"1100.00\"},\"final_capture\":true,\"seller_protection\":{\"status\":\"ELIGIBLE\",\"dispute_categories\":[\"ITEM_NOT_RECEIVED\",\"UNAUTHORIZED_TRANSACTION\"]},\"create_time\":\"2026-02-16T13:48:04Z\",\"update_time\":\"2026-02-16T13:48:04Z\"}]}}],\"payer\":{\"name\":{\"given_name\":\"mandy\",\"surname\":\"francisco\"},\"email_address\":\"sb-j0kiz49407225@personal.example.com\",\"payer_id\":\"FS7DLNB2NXVAU\",\"address\":{\"country_code\":\"PH\"}},\"create_time\":\"2026-02-16T13:46:54Z\",\"update_time\":\"2026-02-16T13:48:04Z\",\"links\":[{\"href\":\"https:\\/\\/api.sandbox.paypal.com\\/v2\\/checkout\\/orders\\/9YG45804KA581093W\",\"rel\":\"self\",\"method\":\"GET\"}]}', '2026-02-16 13:48:59');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sizes` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `stock` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','pending') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `sizes`, `image`, `category_id`, `created_at`, `stock`, `status`) VALUES
(114, 'Round Bonet', 'A round bonnet is a circular head covering that fits neatly around the head, used for style or protection.', 400.00, 'S,M,L', 'acc-1.jpg', 5, '2025-10-11 12:47:27', 20, 'active'),
(115, 'Cap', 'A round cap is a simple, circular headwear that fits closely on the head for comfort and style.', 500.00, 'S,XL', 'acc-2.jpg', 5, '2025-10-11 12:48:29', 15, 'active'),
(116, 'Tote Bag', 'A tote bag is a large, sturdy, open-top bag with handles, used for carrying personal items or shopping.', 300.00, 'S', 'acc-3.jpg', 5, '2025-10-11 12:49:24', 9, 'active'),
(117, 'Shoulder Bag', 'A shoulder bag is a bag with a long strap designed to be worn over the shoulder for easy carrying.', 99.00, 'S,XL,XXL', 'acc-4.jpg', 5, '2025-10-11 12:50:22', 0, 'inactive'),
(118, 'UND Jorts', 'Jorts are denim shorts made from jeans, combining the look of jeans with the comfort of shorts.', 999.00, 'S,M,L', 'fit-outwear-1.jpg', 2, '2025-10-11 12:52:00', 15, 'active'),
(119, 'Track Pants', 'Track pants are comfortable, lightweight pants designed for sports or casual wear, often with an elastic waist and ankle cuffs.', 1500.00, 'S,M,XXL', 'fit-outwear-4.jpg', 2, '2025-10-11 12:56:03', 78, 'active'),
(120, 'Baggy Pants', 'Baggy pants are loose-fitting trousers with a relaxed, wide cut for comfort and a casual style.', 1500.00, 'XS,L', 'fit-outwear-8.jpg', 2, '2025-10-11 12:56:49', 18, 'active'),
(121, 'UND Hoddie', 'A hoodie is a sweatshirt with a hood, often featuring a front pocket and drawstrings for casual comfort.', 2000.00, 'S,L,XL', 'fit-outwear-3.jpg', 3, '2025-10-11 12:58:14', 28, 'active'),
(122, 'Track Suit', 'A tracksuit is a matching set of a jacket and pants made from lightweight fabric, worn for sports or casual wear.', 7000.00, 'XS,S,M,L,XL', 'fit-outwear-5.jpg', 3, '2025-10-11 12:59:28', 61, 'active'),
(123, 'Leather Jacket', 'A leather jacket is a stylish outerwear made from leather, known for its durability and edgy look.', 799.00, 'S,L', 'fit-outwear-11.jpg', 15, '2025-10-11 13:06:13', 18, 'active'),
(124, 'Polo Jacket', 'A polo jacket is a lightweight, collared jacket inspired by polo shirts, offering a neat and casual look.', 5000.00, 'S,L', 'fit-outwear-12(front).jpg', 16, '2025-10-11 13:07:22', 58, 'active'),
(125, 'Rich Boyz Shirt', 'A shirt is a garment worn on the upper body, usually with a collar, sleeves, and buttons on the front.', 700.00, 'S,M', 'fit-1.jpg', 11, '2025-10-11 13:09:02', 32, 'active'),
(126, 'Floral Shirt', 'A floral t-shirt is a casual shirt featuring flower patterns, adding a fresh and stylish look.', 600.00, 'S,M', 'fit-3.jpg', 11, '2025-10-11 13:10:05', 4458, 'active'),
(127, 'Graphical shirt', 'A graphical shirt is a t-shirt featuring printed designs, images, or text for a trendy, expressive style.', 500.00, 'S,L,XL', 'fit-outwear-6.jpg', 15, '2025-10-11 13:11:04', 2196, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `registration_otps`
--

CREATE TABLE `registration_otps` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `expiration` datetime NOT NULL,
  `used` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `registration_otps`
--

INSERT INTO `registration_otps` (`id`, `email`, `otp_code`, `expiration`, `used`, `created_at`) VALUES
(2, 'ecommercebsit2025@gmailcom', '534026', '2025-11-23 19:12:11', 0, '2025-11-23 11:02:11'),
(3, 'ecommercebsit2025@gmail.com', '166920', '2025-11-23 19:13:20', 0, '2025-11-23 11:03:20'),
(4, 'gabrielvargas0423@gmail.com', '438631', '2025-11-23 19:16:45', 1, '2025-11-23 11:06:45'),
(5, 'gabrielvargas0423@gmail.com', '815118', '2025-11-23 19:41:06', 1, '2025-11-23 11:31:06'),
(6, 'gecohaillie560@gmail.com', '959517', '2025-11-23 19:42:41', 1, '2025-11-23 11:32:41'),
(7, 'gabrielvargas0423@gmail.com', '269490', '2025-11-23 19:45:45', 1, '2025-11-23 11:35:45'),
(8, 'gabrielvargas0423@gmail.com', '398807', '2025-11-23 19:49:20', 1, '2025-11-23 11:39:20'),
(9, 'gabrielvargas0423@gmail.com', '886794', '2025-11-23 19:52:16', 1, '2025-11-23 11:42:16'),
(10, 'gabrielvargas0423@gmail.com', '992655', '2025-11-23 19:58:07', 1, '2025-11-23 11:48:07'),
(11, 'gabrielvargas0423@gmail.com', '858477', '2025-11-23 20:00:29', 1, '2025-11-23 11:50:29'),
(12, 'gabrielvargas0423@gmail.com', '249251', '2025-11-23 20:05:26', 1, '2025-11-23 11:55:26'),
(13, 'gabrielvargas0423@gmail.com', '211538', '2025-11-23 20:08:11', 1, '2025-11-23 11:58:11'),
(14, 'gabrielvargas0423@gmail.com', '253675', '2025-11-23 20:10:28', 1, '2025-11-23 12:00:28'),
(15, 'purplee.hazee12@gmail.com', '198141', '2025-11-23 21:03:36', 0, '2025-11-23 12:53:36'),
(16, 'jamespeterduran826@gmail.com', '878814', '2025-11-23 21:06:25', 1, '2025-11-23 12:56:25'),
(17, 'Mandyfrancisco895@gmail.com', '176660', '2025-11-23 21:13:35', 1, '2025-11-23 13:03:35'),
(18, 'laurencerafael8@gmail.com', '628614', '2025-11-23 21:18:54', 1, '2025-11-23 13:08:54'),
(19, 'laurencerafael8@gmail.com', '290711', '2025-11-23 21:20:33', 0, '2025-11-23 13:10:33'),
(20, 'aujscvargas@gmail.com', '236431', '2025-11-23 21:22:45', 1, '2025-11-23 13:12:45'),
(21, 'gecohaillie560@gmail.com', '640008', '2025-11-23 21:25:28', 1, '2025-11-23 13:15:28'),
(22, 'gabrielvargas0423@gmail.com', '143890', '2025-11-23 23:31:12', 1, '2025-11-23 15:21:12'),
(23, 'gabrielvargas0423@gmail.com', '320048', '2025-11-30 20:38:55', 1, '2025-11-30 12:28:55'),
(24, 'gabrielvargas0423@gmail.com', '997663', '2025-11-30 22:42:06', 1, '2025-11-30 14:32:06'),
(25, 'gabrielvargas0423@gmail.com', '337549', '2025-12-01 18:22:16', 1, '2025-12-01 10:12:16'),
(26, 'gabrielvargas0423@gmail.com', '410751', '2025-12-01 21:24:13', 1, '2025-12-01 13:14:13'),
(27, 'gabrielvargas0423@gmail.com', '206443', '2025-12-04 21:15:49', 1, '2025-12-04 13:05:49'),
(28, 'admin12345@gmail.com', '279042', '2025-12-10 14:48:49', 0, '2025-12-10 06:38:49'),
(29, 'renziealvarez18@gmail.com', '806693', '2025-12-10 14:50:24', 0, '2025-12-10 06:40:24'),
(30, 'mandyfrance84@gmail.com', '166701', '2025-12-10 14:52:32', 1, '2025-12-10 06:42:32'),
(31, 'gabrielvargas0423@gmail.com', '625943', '2025-12-10 14:53:51', 1, '2025-12-10 06:43:51'),
(32, 'gabrielvargas0423@gmail.com', '193573', '2026-01-14 12:03:48', 1, '2026-01-14 03:53:48'),
(33, 'mandyfrance84@gmail.com', '773558', '2026-01-25 10:43:24', 1, '2026-01-25 02:33:24'),
(34, 'mandyfrance84@gmail.com', '256053', '2026-01-25 10:53:48', 1, '2026-01-25 02:43:48'),
(35, 'mandyfrance84@gmail.com', '565115', '2026-01-25 11:27:17', 1, '2026-01-25 03:17:17'),
(36, 'mandyfrancisco895@gmail.com', '587513', '2026-02-03 13:14:45', 1, '2026-02-03 05:04:45'),
(37, 'mandyfrance84@gmail.com', '859394', '2026-02-14 00:56:26', 1, '2026-02-13 16:46:26'),
(38, 'mandyfrance84@gmail.com', '434684', '2026-02-14 00:56:31', 1, '2026-02-13 16:46:31');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `movement_type` enum('add','remove','set') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `product_id`, `movement_type`, `quantity`, `reason`, `notes`, `user_id`, `created_at`) VALUES
(14, 127, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(15, 126, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(16, 125, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(17, 124, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(18, 123, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(19, 122, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(20, 121, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(21, 120, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(24, 127, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(25, 126, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(26, 125, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(27, 124, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(28, 123, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(29, 122, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(30, 121, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(31, 120, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(34, 127, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(35, 126, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(36, 125, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(37, 124, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(38, 123, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(39, 122, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(40, 121, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(41, 120, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(44, 127, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(45, 126, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(46, 125, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(47, 124, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(48, 123, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(49, 122, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(50, 121, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(51, 120, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(54, 127, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(55, 126, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(56, 125, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(57, 124, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(58, 123, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(59, 122, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(60, 121, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(61, 120, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(67, 127, '', 20, 'sale', 'Bulk operation: add by 20', 30, '2025-10-16 12:48:34'),
(72, 127, 'add', 123, 'restock', '12', 30, '2026-02-02 08:41:12'),
(73, 126, 'add', 1, 'restock', '1', 30, '2026-02-02 08:41:52'),
(89, 127, 'set', 234, 'quick_adjustment', 'Quick adjustment via inventory table', 30, '2026-02-02 16:45:33'),
(90, 127, 'add', 2345, 'sale', 'f', 30, '2026-02-02 16:45:42'),
(91, 127, 'set', 244, 'quick_adjustment', 'Quick adjustment via inventory table', NULL, '2026-02-02 16:46:25'),
(92, 126, 'add', 234, 'sale', 'd', NULL, '2026-02-02 16:46:36'),
(93, 127, 'add', 1245, 'restock', 'rwrwr', NULL, '2026-02-02 16:46:51'),
(94, 127, 'set', 234, 'quick_adjustment', 'Quick adjustment via inventory table', NULL, '2026-02-02 17:00:48'),
(95, 126, 'add', 4244, 'restock', '', NULL, '2026-02-02 17:01:01'),
(96, 127, 'add', 2345, 'restock', '2', NULL, '2026-02-02 17:01:22'),
(97, 127, 'add', 23455, 'restock', '2', NULL, '2026-02-02 17:06:05'),
(98, 127, 'set', 13, 'quick_adjustment', 'Quick adjustment via inventory table', NULL, '2026-02-02 17:06:09'),
(99, 127, 'set', 134, 'quick_adjustment', 'Quick adjustment via inventory table', NULL, '2026-02-02 17:33:43'),
(100, 127, 'set', 356, 'quick_adjustment', 'Quick adjustment via inventory table', NULL, '2026-02-02 18:03:13'),
(101, 127, 'set', 123, 'quick_adjustment', 'Quick adjustment via inventory table', NULL, '2026-02-02 18:04:49'),
(102, 127, 'set', 1234, 'quick_adjustment', 'Quick adjustment via inventory table', 136, '2026-02-03 09:24:42'),
(103, 127, 'add', 1334, 'restock', '23', 136, '2026-02-03 09:24:53'),
(104, 127, 'set', 123, 'quick_adjustment', 'Quick adjustment via inventory table', 136, '2026-02-03 09:42:21'),
(105, 127, 'add', 2123, 'restock', '1', 136, '2026-02-03 09:42:38');

-- --------------------------------------------------------

--
-- Table structure for table `system_audit_logs`
--

CREATE TABLE `system_audit_logs` (
  `id` int(11) NOT NULL,
  `operation` varchar(255) NOT NULL,
  `admin_user` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Success',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_audit_logs`
--

INSERT INTO `system_audit_logs` (`id`, `operation`, `admin_user`, `details`, `message`, `status`, `created_at`) VALUES
(576, 'System Status Update', 'Gab', 'Maintenance: ON | Recovery: 2026-02-03T00:49', NULL, 'Completed', '2026-02-02 21:49:36'),
(577, 'System Status Update', 'Gab', 'Maintenance: OFF | Recovery: Not Specified', NULL, 'Completed', '2026-02-02 21:49:40'),
(578, 'System Status Update', 'Gab', 'Maintenance: ON | Recovery: 2026-02-03T01:01', NULL, 'Completed', '2026-02-02 22:01:12'),
(579, 'Test Operation', 'Admin User', 'This is a test log entry', NULL, 'Completed', '2026-02-02 22:02:38'),
(580, 'System Status Update', 'Gab', 'Maintenance: OFF | Recovery: Not Specified', NULL, 'Completed', '2026-02-02 22:04:18'),
(581, 'System Status Update', 'Gab', 'Maintenance: OFF | Recovery: 2026-02-03T01:46', NULL, 'Completed', '2026-02-02 22:46:34'),
(582, 'System Status Update', 'Gab', 'Maintenance: ON | Recovery: 2026-02-03T01:46', NULL, 'Completed', '2026-02-02 22:46:42'),
(583, 'System Status Update', 'Gab', 'Maintenance: OFF | Recovery: Not Specified', NULL, 'Completed', '2026-02-02 22:46:48'),
(584, 'System Status Update', 'Gab', 'Maintenance: ON | Recovery: 2026-02-03T20:42', NULL, 'Completed', '2026-02-03 17:42:50'),
(585, 'System Status Update', 'Gab', 'Maintenance: OFF | Recovery: Not Specified', NULL, 'Completed', '2026-02-03 17:44:29');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'store_name', 'Empire - Shop', '2026-01-25 08:59:25'),
(2, 'maintenance_mode', '0', '2026-02-03 09:44:29'),
(23, 'maint_message', '', '2026-02-03 09:44:29'),
(24, 'ip_whitelist', '', '2026-01-25 17:12:51'),
(25, 'recovery_time', '', '2026-02-03 09:44:29'),
(540, 'maint_start_time', '2026-01-28 00:13:07', '2026-01-27 16:13:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','staff') DEFAULT 'user',
  `status` enum('Active','Inactive','Blocked','Deactivated') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`, `created_at`, `phone`, `address`, `profile_pic`) VALUES
(30, 'Gab', 'admin321@gmail.com', '$2y$10$kLMBHM8ilzpdN4pSemAqruacxDdb9WtZTOEIL81n5cU2PETKAWngS', 'admin', 'Active', '2025-09-06 16:05:08', NULL, NULL, 'profile_30_1770044076.jpg'),
(54, 'Laurence Vargas', 'aujscvargas@gmail.com', '$2y$10$3TuAG5UQMYeRKvNYBcNRLOtbJKE1jaHB/hIBWeew2NaDarsg9mFE2', 'user', 'Active', '2025-11-23 13:13:09', '09938433971', '731 lansones st napico manggahan pasig city ', '1763903658_duds.jpg'),
(55, 'Haillie1 Geco', 'gecohaillie560@gmail.com', '$2y$10$NBW8JWWqHNtR6WsZ/n/A8eUkITpV0D84xL9zRtpXbPGJPG7velAzS', 'user', 'Active', '2025-11-23 13:16:05', '09938433890', 'blck 8. Kasigahan St. Pasig city', '1763903849_aa522e76-6c4c-4b74-a7d9-2afc6e4fa036.jpg'),
(135, 'ddaDAD', 'mandyfrancisco895@gmail.com', '$2y$10$0okeuJsFNMSlUQLwM50mIeK0wb.7sGp.aStyVsqL4YYo0L.jQEZcy', 'user', 'Active', '2026-02-03 05:05:13', '96494949494', 'bcbcbc', 'user'),
(136, 'osafmaomfoamfa', 'osafmaomfoamfa@gmail.com', '$2y$10$pgSQfb6YlVOe/OBek1nvsOaGT/Acioy1ZmyQ9uagJrokPTi3CoT0G', 'staff', 'Active', '2026-02-03 09:23:10', NULL, NULL, 'profile_136_1770110708.jpg'),
(137, 'mandy', 'admin4321@gmail.com', '$2y$10$GiGSfdcpEFx11rItfxzee.wW789y5aVFDRgEQZucU0OCQPAQBhwS6', 'admin', 'Active', '2026-02-03 09:27:03', NULL, NULL, 'profile_137_1770110887.jpg'),
(138, 'mandy1223', 'mandyfrance84@gmail.com', '$2y$10$F5jaPhgT3GGQtV7qbN2Va.JdgWNn4wsaeeeRupFNCXHaTQkYB9fF.', 'user', 'Active', '2026-02-13 16:47:54', '54494949494', 'dsdsd', '1771165636_1756209779_525978244_1076824247419053_4987873579582427024_n.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `user_otps`
--

CREATE TABLE `user_otps` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `otp_code` varchar(10) NOT NULL,
  `expiration` datetime NOT NULL,
  `used` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_lockouts`
--
ALTER TABLE `account_lockouts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identifier` (`identifier`),
  ADD KEY `idx_identifier` (`identifier`),
  ADD KEY `idx_locked_until` (`locked_until`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_created_by` (`created_by_user_id`),
  ADD KEY `idx_filename` (`filename`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_backup_logs_role` (`created_by_role`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_identifier` (`identifier`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_attempt_time` (`attempt_time`);

--
-- Indexes for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_paypal_order_id` (`paypal_order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_otp_code` (`otp_code`),
  ADD KEY `idx_used` (`used`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `paypal_transactions`
--
ALTER TABLE `paypal_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_paypal_order_id` (`paypal_order_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `registration_otps`
--
ALTER TABLE `registration_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_registration_email_otp` (`email`,`otp_code`,`used`),
  ADD KEY `idx_registration_expiration` (`expiration`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `stock_movements_ibfk_1` (`product_id`);

--
-- Indexes for table `system_audit_logs`
--
ALTER TABLE `system_audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_otps`
--
ALTER TABLE `user_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_lockouts`
--
ALTER TABLE `account_lockouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `backup_logs`
--
ALTER TABLE `backup_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `paypal_transactions`
--
ALTER TABLE `paypal_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT for table `registration_otps`
--
ALTER TABLE `registration_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `system_audit_logs`
--
ALTER TABLE `system_audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=586;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1940;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `user_otps`
--
ALTER TABLE `user_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD CONSTRAINT `fk_backup_user` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_otps`
--
ALTER TABLE `user_otps`
  ADD CONSTRAINT `user_otps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
