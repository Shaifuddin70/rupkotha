-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 05, 2025 at 04:04 PM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rpecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`) VALUES
(2, 'ROKIB', 'admin@gmail.com', '$2y$10$JLlfSjU8JiL//lrLBqSYVuoZPrL/Pv1CyIVKJBP7/ffin5SBP2XHm');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `created_at`, `updated_at`) VALUES
(8, 'HOME COOKED', '2025-06-27 20:53:06', '2025-06-27 20:53:06'),
(11, 'Furniture', '2025-06-27 20:53:06', '2025-06-27 20:53:06'),
(16, 'Shaifuddin Ahammed Rokib', '2025-06-27 21:31:29', '2025-06-27 21:31:29'),
(21, 'daw', '2025-07-05 06:53:52', '2025-07-05 06:53:52'),
(22, 'dawawd', '2025-07-05 06:54:52', '2025-07-05 06:54:52'),
(23, 'dawawdad', '2025-07-05 06:54:53', '2025-07-05 06:54:53');

-- --------------------------------------------------------

--
-- Table structure for table `hero_products`
--

CREATE TABLE `hero_products` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `hero_products`
--

INSERT INTO `hero_products` (`id`, `product_id`, `title`, `subtitle`, `image`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 6, 'Burgerwad', 'awdawd', '685fbf4df3faa_IMG-20250619-WA0049.jpg', 1, '2025-06-28 09:47:47', NULL),
(14, 6, 'dad', 'awdawd', '6868c325ee986bb4fbc0c26dac8a0.jpg', 1, '2025-07-05 06:16:05', NULL),
(15, 8, 'awd', 'awd', '6868cd3d91831ccb0793aabb3c5e5.jpg', 1, '2025-07-05 06:59:09', NULL),
(16, 8, 'awd', 'awd', '6868cd432d96c81789f6227b7ca53.jpg', 1, '2025-07-05 06:59:15', NULL),
(17, 8, 'awd', 'awd', '6868cd4969cbfe0842087b6c9d9a7.jpg', 1, '2025-07-05 06:59:21', NULL),
(18, 6, 'awd', 'awd', '6868cd51c6ca705fda83303f17ae5.jpg', 1, '2025-07-05 06:59:29', NULL),
(20, 10, 'awd', 'awd', 'bb8abef6a23147f83c90aba6.jpg', 1, '2025-07-05 07:40:15', NULL),
(21, 9, 'Update', 'Hello word', 'f754b4239485c68d6194ac29.jpg', 1, '2025-07-05 07:40:29', '2025-07-05 13:51:33');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `payment_method` varchar(50) NOT NULL DEFAULT 'Cash on Delivery',
  `payment_trx_id` varchar(255) DEFAULT NULL,
  `payment_sender_no` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `payment_method`, `payment_trx_id`, `payment_sender_no`, `created_at`) VALUES
(1, 1, '100050.00', 'Pending', 'Cash on Delivery', NULL, NULL, '2025-07-05 09:08:22'),
(2, 1, '402147.00', 'Pending', 'Cash on Delivery', NULL, NULL, '2025-07-05 09:10:21'),
(3, 1, '449984.00', 'Pending', 'Cash on Delivery', NULL, NULL, '2025-07-05 09:46:10');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 6, 1, '100000.00'),
(2, 2, 6, 4, '100000.00'),
(3, 2, 10, 3, '699.00'),
(4, 3, 6, 4, '100000.00'),
(5, 3, 9, 1, '49934.00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `stock` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `image`, `price`, `created_at`, `stock`, `updated_at`) VALUES
(6, 11, 'Shaifuddin Ahammed Rokib', 'test', '685f0916ad605_IMG-20250619-WA0049.jpg', '100000.00', '2025-07-05 09:46:10', 4, '2025-07-05 09:46:10'),
(8, 8, 'Chicken Burger', 'Salary', '6868c25dddead19fd9202d6f487d4.jpg', '1200.00', '2025-07-05 06:14:09', 55, '2025-07-05 06:14:09'),
(9, 8, 'HOME COOKED', 'Salary', '7adad8a6386ebc467132378d.jpg', '49934.00', '2025-07-05 09:46:10', 54, '2025-07-05 09:46:10'),
(10, 22, 'dawawdad', 'Google authenticator', 'b674c078f1354a8d83da3555.jpg', '699.00', '2025-07-05 09:10:21', 96, '2025-07-05 09:10:21');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text,
  `facebook` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `shipping_fee_dhaka` decimal(10,2) NOT NULL DEFAULT '60.00',
  `shipping_fee_outside` decimal(10,2) NOT NULL DEFAULT '120.00',
  `bkash_number` varchar(20) DEFAULT NULL,
  `nagad_number` varchar(20) DEFAULT NULL,
  `rocket_number` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `company_name`, `logo`, `phone`, `email`, `address`, `facebook`, `instagram`, `twitter`, `shipping_fee_dhaka`, `shipping_fee_outside`, `bkash_number`, `nagad_number`, `rocket_number`, `updated_at`) VALUES
(1, 'Rupkotha Properties Bangladesh', '6868c1785a7f3a3857e4927213ad2.jpg', '01234554', 'info@rpproperty.com', 'Dhaka, Bangladesh', 'https://www.facebook.com/', 'https://www.facebook.com/', 'https://www.facebook.com/', '60.00', '120.00', '01791912323', '01812345678', '01538347152', '2025-07-05 10:02:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `phone`, `address`, `created_at`) VALUES
(1, 'Shaifuddin Ahammed', 'shaifuddin70@gmail.com', '$2y$10$TU4FOibTfughwZ4EhnYs/uQi8VdBf9F8BtxNs2HPZWuwIyTWZz2ra', '01635485720', 'Army Society, Mazar road, Uttara, Dhaka-1230', '2025-07-05 08:49:22'),
(2, 'rokib', 'dardentimothy3@gmail.com', '$2y$10$/byoqCnj2gUim.hMXWklIOHu20uU7p9x33X9l7owJG5nybEgg429q', '18002122120', 'Bangalore, karnataka, india', '2025-07-05 08:53:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hero_products`
--
ALTER TABLE `hero_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

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
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `hero_products`
--
ALTER TABLE `hero_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `hero_products`
--
ALTER TABLE `hero_products`
  ADD CONSTRAINT `hero_products_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
