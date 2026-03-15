-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 15, 2026 at 01:58 PM
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
(39, 1, 'admin', 'LOGOUT', 'auth', 'User logged out', '::1', '2026-03-15 12:29:59'),
(40, 1, 'admin', 'LOGIN', 'auth', 'User logged in', '::1', '2026-03-15 12:30:22'),
(41, 1, 'admin', 'DELETE_USER', 'users', 'Deleted user ID: 6', '::1', '2026-03-15 12:30:28'),
(42, 1, 'admin', 'DELETE_USER', 'users', 'Deleted user ID: 7', '::1', '2026-03-15 12:30:30'),
(43, 1, 'admin', 'LOGOUT', 'auth', 'User logged out', '::1', '2026-03-15 12:30:33'),
(46, 1, 'admin', 'LOGIN', 'auth', 'User logged in', '::1', '2026-03-15 12:38:09'),
(47, 1, 'admin', 'CREATE_USER', 'users', 'Created user: admin1', '::1', '2026-03-15 12:38:36'),
(48, 1, 'admin', 'DELETE_USER', 'users', 'Deleted user ID: 8', '::1', '2026-03-15 12:38:57'),
(49, 1, 'admin', 'CREATE_USER', 'users', 'Created user: admin1', '::1', '2026-03-15 12:39:06'),
(50, 9, 'admin1', 'LOGIN', 'auth', 'User logged in', '::1', '2026-03-15 12:39:12'),
(51, 1, 'admin', 'CREATE_USER', 'users', 'Created user: admin2', '::1', '2026-03-15 12:39:33'),
(52, 1, 'admin', 'UPDATE_USER', 'users', 'Updated user: admin2', '::1', '2026-03-15 12:39:38'),
(53, 10, 'admin2', 'LOGIN', 'auth', 'User logged in', '::1', '2026-03-15 12:39:55'),
(54, 10, 'admin2', 'CREATE_PRODUCT', 'products', 'Created: Laptop (SKU: 123-321)', '::1', '2026-03-15 12:40:36'),
(55, 1, 'admin', 'STOCK_IN', 'inventory', 'Product ID 7 | Qty: 5 | Ref: STO-68E373-20260315', '::1', '2026-03-15 12:40:54'),
(56, 10, 'admin2', 'CREATE_PRODUCT', 'products', 'Created: Rice (SKU: 123-2313-3123)', '::1', '2026-03-15 12:41:39'),
(57, 1, 'admin', 'STOCK_IN', 'inventory', 'Product ID 8 | Qty: 12 | Ref: STO-D732B7-20260315', '::1', '2026-03-15 12:42:05'),
(58, 1, 'admin', 'UPDATE_PRODUCT', 'products', 'Updated: Rice', '::1', '2026-03-15 12:42:26'),
(59, 1, 'admin', 'LOGOUT', 'auth', 'User logged out', '::1', '2026-03-15 12:42:33');

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
(5, 7, 5, '2026-03-15 12:40:54'),
(6, 8, 12, '2026-03-15 12:42:05');

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
(7, '123-321', 'Laptop', 'Laptoppppppppppppppp', 1, 'pcs', 5000.00, 5, 1, 10, '2026-03-15 12:40:36', '2026-03-15 12:40:36'),
(8, '123-2313-3123', 'Rice', 'Riceeeeeeeeeeeeeeeeee', 5, 'kg', 800.00, 15, 1, 10, '2026-03-15 12:41:39', '2026-03-15 12:42:26');

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
(13, 'STO-68E373-20260315', 'stock_in', 7, 5, 5000.00, 'eto na', 1, '2026-03-15 12:40:54'),
(14, 'STO-D732B7-20260315', 'stock_in', 8, 12, 800.00, '12 lang meron yah', 1, '2026-03-15 12:42:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','staff') NOT NULL DEFAULT 'staff',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@inventory.com', '$2y$10$G/cZ50li8YAbkuq0PDBN7ux3Msy6ShomGy0sinHczmCGFZmNxZJca', 'admin', 1, '2026-03-15 07:35:29', '2026-03-15 07:37:34'),
(9, 'admin1', 'admin12312@gmail.com', '$2y$10$q3HVym6IOigGxeofH6SkkOVmE70TBomoFeJbPI.DqYPwakp8GiLOC', 'staff', 1, '2026-03-15 12:39:06', '2026-03-15 12:39:06'),
(10, 'admin2', 'admin32131@gmail.com', '$2y$10$wl0i.iQJjz/aFnBMe87m7.GDcR884.80phNHVEhHuMj9ODQPhFTcK', 'manager', 1, '2026-03-15 12:39:33', '2026-03-15 12:39:38');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
