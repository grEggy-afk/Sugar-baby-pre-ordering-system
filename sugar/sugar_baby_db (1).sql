-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Aug 12, 2026 at 03:50 PM
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
-- Database: `sugar_baby_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `item_count` int(11) NOT NULL DEFAULT 1,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT 'GCash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `total_price`, `item_count`, `status`, `created_at`, `payment_method`) VALUES
(1, 1, 'Juan Dela Cruz', 150.00, 1, 'completed', '2026-07-29 22:02:52', 'GCash'),
(2, 1, 'Maria Santos', 230.00, 1, 'pending', '2026-07-29 22:02:52', 'GCash'),
(3, 1, 'Pedro Penduko', 98.00, 1, 'completed', '2026-07-29 22:02:52', 'GCash'),
(4, 4, 'rg', 25.00, 1, 'pending', '2026-08-12 13:34:47', 'GCash'),
(5, 4, 'rg', 25.00, 1, 'pending', '2026-08-12 13:42:51', 'GCash');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `flavor` varchar(50) DEFAULT NULL,
  `size` varchar(20) DEFAULT 'Large',
  `sugar_level` varchar(20) DEFAULT '100%',
  `add_ons` varchar(255) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','employee','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `phone_number`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin User', '09123456789', 'admin@sugarbaby.clsu.edu.ph', '$2y$10$Z.0Skxpqf25keDlC0InXFeRHV5BSpuxVDxyUCYKcQ8sUkenbJutnC', 'admin', '2026-07-29 12:50:42'),
(2, 'Staff Member', '09987654321', 'staff@sugarbaby.clsu.edu.ph', '$2y$10$YourHashedPasswordHere', 'employee', '2026-07-29 12:50:42'),
(3, 'greg', '12345', 'emmanuelfronda5@gmail.com', '$2y$10$ebXb6AyH0EbtkB8BEicXXeBBUDXXTRhhvSLWcPukvjrr7A1Gy4NAC', 'customer', '2026-08-05 14:54:32'),
(4, 'rg', '111', 'gregjamesfronda441@gmail.com', '$2y$10$yydJJQW1t2YwGLOckNBjgegm6brgwrGEGYJaeemaUxYKIZ3icVX2G', 'customer', '2026-08-08 19:17:03'),
(5, 'juan', '111', 'test@gmail.com', '$2y$10$4uDI0BCho2QYV1WiqMl3YeXaq38AmzsBsVKdcYjSqq2.zMSzoUyDC', 'customer', '2026-08-08 19:35:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
