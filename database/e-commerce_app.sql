-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2025 at 02:15 PM
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
-- Database: `e-commerce app`
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
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(119, 'Track Pants', 'Track pants are comfortable, lightweight pants designed for sports or casual wear, often with an elastic waist and ankle cuffs.', 1500.00, 'S,M,XXL', 'fit-outwear-4.jpg', 2, '2025-10-11 12:56:03', 79, 'active'),
(120, 'Baggy Pants', 'Baggy pants are loose-fitting trousers with a relaxed, wide cut for comfort and a casual style.', 1500.00, 'XS,L', 'fit-outwear-8.jpg', 2, '2025-10-11 12:56:49', 19, 'active'),
(121, 'UND Hoddie', 'A hoodie is a sweatshirt with a hood, often featuring a front pocket and drawstrings for casual comfort.', 2000.00, 'S,L,XL', 'fit-outwear-3.jpg', 3, '2025-10-11 12:58:14', 31, 'active'),
(122, 'Track Suit', 'A tracksuit is a matching set of a jacket and pants made from lightweight fabric, worn for sports or casual wear.', 7000.00, 'XS,S,M,L,XL', 'fit-outwear-5.jpg', 3, '2025-10-11 12:59:28', 62, 'active'),
(123, 'Leather Jacket', 'A leather jacket is a stylish outerwear made from leather, known for its durability and edgy look.', 799.00, 'S,L', 'fit-outwear-11.jpg', 15, '2025-10-11 13:06:13', 21, 'active'),
(124, 'Polo Jacket', 'A polo jacket is a lightweight, collared jacket inspired by polo shirts, offering a neat and casual look.', 5000.00, 'S,L', 'fit-outwear-12(front).jpg', 16, '2025-10-11 13:07:22', 70, 'active'),
(125, 'Rich Boyz Shirt', 'A shirt is a garment worn on the upper body, usually with a collar, sleeves, and buttons on the front.', 700.00, 'S,M', 'fit-1.jpg', 11, '2025-10-11 13:09:02', 50, 'active'),
(126, 'Floral Shirt', 'A floral t-shirt is a casual shirt featuring flower patterns, adding a fresh and stylish look.', 600.00, 'S,M', 'fit-3.jpg', 11, '2025-10-11 13:10:05', 28, 'active'),
(127, 'Graphical shirt', 'A graphical shirt is a t-shirt featuring printed designs, images, or text for a trendy, expressive style.', 500.00, 'S,L,XL', 'fit-outwear-6.jpg', 15, '2025-10-11 13:11:04', 49, 'active'),
(128, 'Black Shirt', 'versatile top in black color, suitable for both casual and formal wear.', 700.00, 'S,L', 'fit-outwear-9.jpg', 11, '2025-10-11 13:12:44', 43, 'active'),
(129, 'Jersey Shirt', 'A jersey shirt is a lightweight, stretchy top made from jersey fabric, often used for sports or casual wear.', 700.00, 'S,L', 'fit-outwear-7.jpg', 16, '2025-10-11 13:13:40', 40, 'active');

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
(22, 'gabrielvargas0423@gmail.com', '143890', '2025-11-23 23:31:12', 0, '2025-11-23 15:21:12');

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
(11, 129, 'set', 50, 'quick_adjustment', 'Quick adjustment via inventory table', 30, '2025-10-11 14:39:18'),
(12, 129, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(13, 128, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(14, 127, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(15, 126, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(16, 125, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(17, 124, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(18, 123, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(19, 122, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(20, 121, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(21, 120, '', 10, 'sample', 'Bulk operation: add by 10', 30, '2025-10-11 15:15:36'),
(22, 129, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(23, 128, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(24, 127, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(25, 126, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(26, 125, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(27, 124, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(28, 123, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(29, 122, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(30, 121, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(31, 120, '', 20, 'restock', 'Bulk operation: add by 20', 30, '2025-10-11 15:22:56'),
(32, 129, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(33, 128, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(34, 127, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(35, 126, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(36, 125, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(37, 124, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(38, 123, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(39, 122, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(40, 121, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(41, 120, '', 20, 'sale', 'Bulk operation: subtract by 20', 30, '2025-10-11 15:23:14'),
(42, 129, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(43, 128, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(44, 127, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(45, 126, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(46, 125, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(47, 124, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(48, 123, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(49, 122, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(50, 121, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(51, 120, '', 1, 'restock', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:11'),
(52, 129, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(53, 128, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(54, 127, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(55, 126, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(56, 125, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(57, 124, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(58, 123, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(59, 122, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(60, 121, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(61, 120, '', 1, 'sale', 'Bulk operation: add by 1', 30, '2025-10-12 09:55:39'),
(62, 129, 'set', 1, 'quick_adjustment', 'Quick adjustment via inventory table', 30, '2025-10-12 09:55:54'),
(63, 129, 'set', 60, 'quick_adjustment', 'Quick adjustment via inventory table', 30, '2025-10-12 09:56:01'),
(64, 129, 'add', 10, 'restock', 'sample', 30, '2025-10-16 12:47:58'),
(65, 129, '', 20, 'sale', 'Bulk operation: add by 20', 30, '2025-10-16 12:48:34'),
(66, 128, '', 20, 'sale', 'Bulk operation: add by 20', 30, '2025-10-16 12:48:34'),
(67, 127, '', 20, 'sale', 'Bulk operation: add by 20', 30, '2025-10-16 12:48:34'),
(68, 129, 'set', 50, 'quick_adjustment', 'Quick adjustment via inventory table', 30, '2025-10-16 12:48:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
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
(30, 'Gab', 'admin321@gmail.com', '$2y$10$kLMBHM8ilzpdN4pSemAqruacxDdb9WtZTOEIL81n5cU2PETKAWngS', 'admin', 'Active', '2025-09-06 16:05:08', '09938433973', '1103. ALAM MO KUNG sandok', 'profile_30_1757345933.jpg'),
(52, 'James Duran', 'jamespeterduran826@gmail.com', '$2y$10$7MB2VXQBKhwvEcjgT9InzuoxkYrUKdsFxchwPJgUR3qtfzqXcQoFe', 'user', 'Active', '2025-11-23 12:57:42', '09938433973', '731 MANILA CITY', '1763902914_james.jpg'),
(53, 'Mandy Francisco', 'Mandyfrancisco895@gmail.com', '$2y$10$NvfZItjEpzfYjurnMGnQZOS83xXjGcyHu2ABNj5NbDZ7NLEhwTXiy', 'user', 'Active', '2025-11-23 13:06:06', '09212312122', '62134 makati mercedes city', '1763903226_mandy.jpg'),
(54, 'Laurence Vargas', 'aujscvargas@gmail.com', '$2y$10$3TuAG5UQMYeRKvNYBcNRLOtbJKE1jaHB/hIBWeew2NaDarsg9mFE2', 'user', 'Active', '2025-11-23 13:13:09', '09938433971', '731 lansones st napico manggahan pasig city ', '1763903658_duds.jpg'),
(55, 'Haillie Geco', 'gecohaillie560@gmail.com', '$2y$10$NBW8JWWqHNtR6WsZ/n/A8eUkITpV0D84xL9zRtpXbPGJPG7velAzS', 'user', 'Active', '2025-11-23 13:16:05', '09938433890', 'blck 8. Kasigahan St. Pasig city', '1763903849_aa522e76-6c4c-4b74-a7d9-2afc6e4fa036.jpg');

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
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `registration_otps`
--
ALTER TABLE `registration_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `user_otps`
--
ALTER TABLE `user_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

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
