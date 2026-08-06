-- phpMyAdmin SQL Dump
-- Updated for Sugar Baby System — Full Orders & Users Structure
-- Host: 127.0.0.1:3307
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
-- UPDATED: Added all required columns + correct status list
--
CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `item_count` int(11) NOT NULL DEFAULT 1,
  `payment_method` varchar(50) NOT NULL DEFAULT 'Cash',
  `gcash_receipt` varchar(255) DEFAULT NULL,
  `status` enum('incoming','preparing','completed','cancelled','refunded') DEFAULT 'incoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `accepted_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` varchar(50) DEFAULT NULL,
  `cancel_reason` varchar(100) DEFAULT NULL,
  `refunded_at` timestamp NULL DEFAULT NULL,
  `refund_receipt` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
-- UPDATED: One order per status, matches all columns
--
-- --------------------------------------------------------

--
-- Table structure for table `users`
-- UPDATED: Added test customers with @clsu2.edu.ph emails
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
-- UPDATED: Added test customers | Password: sugarbaby123 (hashed)
--
INSERT INTO `users` (`id`, `full_name`, `phone_number`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin User', '09123456789', 'admin@sugarbaby.clsu.edu.ph', '$2y$10$Z.0Skxpqf25keDlC0InXFeRHV5BSpuxVDxyUCYKcQ8sUkenbJutnC', 'admin', '2026-07-29 12:50:42'),
(2, 'Staff Member', '09987654321', 'staff@sugarbaby.clsu.edu.ph', '$2y$10$YourHashedPasswordHere', 'employee', '2026-07-29 12:50:42');

-- sugarbaby123 | customer password
INSERT INTO `users` (`id`, `full_name`, `phone_number`, `email`, `password`, `role`) VALUES
(113, 'Rina Castillo', '09162223344', 'rina.castillo@clsu2.edu.ph', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'customer'),
(114, 'Kyle Ramos', '09165556677', 'kyle.ramos@clsu2.edu.ph', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'customer'),
(115, 'Tina Go', '09168889900', 'tina.go@clsu2.edu.ph', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'customer'),
(116, 'Mia Santos', '09161112233', 'mia.santos@clsu2.edu.ph', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'customer'),
(117, 'Jake Lim', '09164445566', 'jake.lim@clsu2.edu.ph', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'customer'),
(118, 'Liza Cruz', '09167778899', 'liza.cruz@clsu2.edu.ph', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'customer'),
(119, 'Mark Reyes', '09163334455', 'mark.reyes@clsu2.edu.ph', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'customer'),
(120, 'Sofia Reyes', '09179998877', 'sofia.reyes@clsu2.edu.ph', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'customer');

INSERT INTO `orders` (
  `id`, `user_id`, `customer_name`, `total_price`, `item_count`, `payment_method`,
  `gcash_receipt`, `status`, `created_at`, `accepted_at`, `completed_at`,
  `cancelled_at`, `cancelled_by`, `cancel_reason`, `refunded_at`, `refund_receipt`
) VALUES
(101, 113, 'Rina Castillo', 185.00, 2, 'GCash', 'receipt_rina_101.png', 'incoming', NOW(), NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(102, 114, 'Kyle Ramos', 230.00, 3, 'GCash', 'receipt_kyle_102.png', 'incoming', NOW(), NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(103, 115, 'Tina Go', 145.00, 2, 'GCash', 'receipt_tina_103.png', 'incoming', NOW(), NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(104, 116, 'Mia Santos', 210.00, 3, 'GCash', 'receipt_mia_104.png', 'incoming', NOW(), NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(105, 117, 'Jake Lim', 160.00, 2, 'GCash', 'receipt_jake_105.png', 'preparing', DATE_SUB(NOW(), INTERVAL 15 MINUTE), NOW(), NULL, NULL, NULL, NULL, NULL, NULL),
(106, 118, 'Liza Cruz', 195.00, 2, 'GCash', 'receipt_liza_106.png', 'completed', DATE_SUB(NOW(), INTERVAL 1 HOUR), DATE_SUB(NOW(), INTERVAL 55 MINUTE), DATE_SUB(NOW(), INTERVAL 10 MINUTE), NULL, NULL, NULL, NULL, NULL),
(107, 119, 'Mark Reyes', 125.00, 1, 'GCash', 'receipt_mark_107.png', 'cancelled', DATE_SUB(NOW(), INTERVAL 2 HOUR), NULL, NULL, DATE_SUB(NOW(), INTERVAL 1 HOUR), 'Admin', 'Outside working hours', NULL, NULL),
(108, 120, 'Sofia Reyes', 140.00, 2, 'GCash', 'receipt_sofia_108_original.png', 'refunded', DATE_SUB(NOW(), INTERVAL 3 HOUR), NULL, DATE_SUB(NOW(), INTERVAL 2 HOUR), NULL, NULL, NULL, DATE_SUB(NOW(), INTERVAL 30 MINUTE), 'receipt_sofia_108_refund.png');

INSERT INTO `order_items` (`order_id`, `product_name`, `flavor`, `size`, `sugar_level`, `add_ons`, `quantity`, `unit_price`, `subtotal`) VALUES
(101, 'Milk Tea', 'Wintermelon', 'Large', '50%', 'Pudding', 1, 90.00, 90.00),
(101, 'Fruit Tea', 'Lychee', 'Medium', '25%', 'Aloe Vera', 1, 95.00, 95.00),
(102, 'Cheese Cream Tea', 'Matcha', 'Large', '75%', 'Cheesecake Foam', 1, 100.00, 100.00),
(102, 'Milk Tea', 'Brown Sugar', 'Medium', '0%', 'Pearls', 2, 65.00, 130.00),
(103, 'Milk Tea', 'Chocolate', 'Medium', '50%', 'Coffee Jelly', 1, 75.00, 75.00),
(103, 'Yogurt Drink', 'Mango', 'Small', '0%', 'Popping Boba', 1, 70.00, 70.00),
(104, 'Fruit Tea', 'Strawberry', 'Large', '50%', 'Nata de Coco', 2, 60.00, 120.00),
(104, 'Milk Tea', 'Oreo', 'Medium', '25%', 'Pearls', 1, 90.00, 90.00),
(105, 'Milk Tea', 'Taro', 'Large', '75%', 'Pearls + Pudding', 2, 80.00, 160.00),
(106, 'Milk Tea', 'Hokkaido', 'Large', '50%', 'Red Bean', 1, 95.00, 95.00),
(106, 'Coffee', 'Vanilla Latte', 'Medium', '100%', 'Extra Shot', 1, 100.00, 100.00),
(107, 'Milk Tea', 'Caramel', 'Medium', '50%', '—', 1, 125.00, 125.00),
(108, 'Milk Tea', 'Cookies & Cream', 'Large', '50%', 'Oreo Crunch', 1, 75.00, 75.00),
(108, 'Fruit Tea', 'Peach', 'Medium', '25%', 'Nata de Coco', 1, 65.00, 65.00);


--
-- Indexes for dumped tables
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- Constraints for dumped tables
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;