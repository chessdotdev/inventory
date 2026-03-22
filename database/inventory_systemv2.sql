-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 22, 2026 at 01:36 PM
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
-- Database: `inventory_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `username`, `action`, `module`, `description`, `ip_address`, `created_at`) VALUES
(67, 1, 'admin', 'LOGOUT', 'auth', 'User logged out', '::1', '2026-03-22 10:25:23'),
(68, 1, 'admin', 'LOGIN', 'auth', 'User logged in', '::1', '2026-03-22 10:25:29'),
(69, 1, 'admin', 'DELETE_USER', 'users', 'Deleted user ID: 9', '::1', '2026-03-22 10:26:19'),
(70, 1, 'admin', 'DELETE_USER', 'users', 'Deleted user ID: 10', '::1', '2026-03-22 10:26:21'),
(71, 1, 'admin', 'LOGIN', 'auth', 'User logged in', '::1', '2026-03-22 10:33:54'),
(72, 1, 'admin', 'CREATE_USER', 'users', 'Created user: manager', '::1', '2026-03-22 10:39:24'),
(73, 1, 'admin', 'UPDATE_USER', 'users', 'Updated user: admin', '::1', '2026-03-22 10:39:31'),
(74, 1, 'admin', 'CREATE_USER', 'users', 'Created user: staff', '::1', '2026-03-22 10:39:52'),
(75, 1, 'admin', 'LOGOUT', 'auth', 'User logged out', '::1', '2026-03-22 10:39:58'),
(76, 12, 'staff', 'LOGIN', 'auth', 'User logged in', '::1', '2026-03-22 10:40:03'),
(77, 12, 'staff', 'LOGOUT', 'auth', 'User logged out', '::1', '2026-03-22 12:13:50'),
(78, 12, 'staff', 'LOGIN', 'auth', 'User logged in', '::1', '2026-03-22 12:13:57'),
(79, 1, 'admin', 'CREATE_USER', 'users', 'Created user: officer', '::1', '2026-03-22 12:17:22'),
(80, 13, 'officer', 'LOGIN', 'auth', 'User logged in', '::1', '2026-03-22 12:17:34'),
(81, 12, 'staff', 'CREATE_PRODUCT', 'products', 'Created: Laptop (SKU: SKU-8120102A-10)', '::1', '2026-03-22 12:18:03'),
(82, 13, 'officer', 'STOCK_IN', 'inventory', 'Product ID 10 | Qty: 5 | Ref: STO-19E055-20260322', '::1', '2026-03-22 12:19:13');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Electronics', 'Electronic devices and components', '2026-03-15 07:35:29'),
(2, 'Office Supplies', 'Stationery and office materials', '2026-03-15 07:35:29'),
(3, 'Furniture', 'Office and warehouse furniture', '2026-03-15 07:35:29'),
(4, 'Tools & Equipment', 'Hand tools and machinery', '2026-03-15 07:35:29'),
(5, 'Consumables', 'Items consumed in daily operations', '2026-03-15 07:35:29');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_id`, `quantity`, `last_updated`) VALUES
(8, 10, 5, '2026-03-22 12:19:13');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `unit` varchar(30) DEFAULT 'pcs',
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reorder_level` int(11) NOT NULL DEFAULT 10,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `sku`, `name`, `description`, `category_id`, `unit`, `price`, `reorder_level`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(10, 'SKU-8120102A-10', 'Laptop', 'Penge po', 1, 'pcs', 5000.00, 10, 1, 12, '2026-03-22 12:18:03', '2026-03-22 12:18:03');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `reference_no` varchar(50) NOT NULL,
  `type` enum('stock_in','stock_out','adjustment') NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `reference_no`, `type`, `product_id`, `quantity`, `unit_price`, `notes`, `performed_by`, `created_at`) VALUES
(16, 'STO-19E055-20260322', 'stock_in', 10, 5, 5000.00, '5 nalang meron yah', 13, '2026-03-22 12:19:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','inventory_officer','staff') NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$G/cZ50li8YAbkuq0PDBN7ux3Msy6ShomGy0sinHczmCGFZmNxZJca', 'admin', 1, '2026-03-15 07:35:29', '2026-03-22 10:39:31'),
(11, 'manager', 'manager@gmail.com', '$2y$10$o71gYlCA.dpyWApEwg2z.OKVRX6gTg8z4DJj1xMOOXa4hu375wh2m', 'manager', 1, '2026-03-22 10:39:24', '2026-03-22 10:39:24'),
(12, 'staff', 'staff@gmail.com', '$2y$10$TeyD0MSqGireRBkL6H0dj.KkwVHyjmrQ5FihQ0gr.JZKaW8g0v9bK', 'staff', 1, '2026-03-22 10:39:52', '2026-03-22 10:39:52'),
(13, 'officer', 'officer@gmail.com', '$2y$10$c6bx.3din8jVQJs02Uw1luOZwq0siM6Ma8NgexcU9re3a8bzbfCO2', 'inventory_officer', 1, '2026-03-22 12:17:22', '2026-03-22 12:17:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_no` (`reference_no`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
