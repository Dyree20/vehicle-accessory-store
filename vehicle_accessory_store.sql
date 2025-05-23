-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2025 at 07:33 PM
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
-- Database: `vehicle_accessory_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Exterior Accessories', 'Parts and accessories for vehicle exterior', '2025-05-19 14:01:03', '2025-05-19 14:01:03'),
(2, 'Interior Accessories', 'Parts and accessories for vehicle interior', '2025-05-19 14:01:03', '2025-05-19 14:01:03'),
(3, 'Performance Parts', 'Parts to enhance vehicle performance', '2025-05-19 14:01:03', '2025-05-19 14:01:03'),
(4, 'Electronics', 'Electronic accessories and gadgets', '2025-05-19 14:01:03', '2025-05-19 14:01:03'),
(5, 'Maintenance', 'Maintenance and care products', '2025-05-19 14:01:03', '2025-05-19 14:01:03'),
(6, 'Lighting', 'Vehicle lighting and LED accessories', '2025-05-19 14:01:03', '2025-05-19 14:01:03');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `delivery_address` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'COD',
  `delivery_date` date DEFAULT NULL,
  `courier` varchar(100) DEFAULT NULL,
  `status` enum('pending','processing','order placed','on delivery','delivered','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `delivery_address`, `total_amount`, `payment_method`, `delivery_date`, `courier`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 299.99, 'COD', NULL, NULL, 'cancelled', '2025-05-19 16:26:35', '2025-05-19 16:43:22'),
(2, 1, 'San Fernando, Cebu, Philippines', 299.99, 'COD', NULL, NULL, 'cancelled', '2025-05-19 16:44:26', '2025-05-19 16:44:31'),
(3, 2, 'Minglanilla, Cebu', 899.97, 'COD', NULL, NULL, 'cancelled', '2025-05-21 14:35:48', '2025-05-21 15:17:48'),
(4, 2, 'Minglanilla, Cebu', 299.99, 'COD', NULL, NULL, 'cancelled', '2025-05-21 15:18:49', '2025-05-21 15:25:57'),
(5, 2, 'Minglanilla, Cebu', 899.97, 'COD', '2025-05-30', NULL, 'cancelled', '2025-05-21 15:26:28', '2025-05-21 16:20:06'),
(6, 1, 'San Fernando, Cebu, Philippines', 199.99, 'COD', '2025-05-29', NULL, 'cancelled', '2025-05-21 15:47:59', '2025-05-21 16:02:17'),
(7, 2, 'Minglanilla, Cebu', 359.99, 'COD', '2025-05-30', 'FLASH EXPRESS', 'completed', '2025-05-21 16:21:55', '2025-05-21 16:29:27');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`) VALUES
(1, 1, 2, 1, 299.99, '2025-05-19 16:26:35'),
(2, 2, 2, 1, 299.99, '2025-05-19 16:44:26'),
(3, 3, 2, 3, 299.99, '2025-05-21 14:35:48'),
(4, 4, 2, 1, 299.99, '2025-05-21 15:18:49'),
(5, 5, 2, 3, 299.99, '2025-05-21 15:26:28'),
(6, 6, 3, 1, 199.99, '2025-05-21 15:47:59'),
(7, 7, 2, 1, 299.99, '2025-05-21 16:21:55');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `stock` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category_id`, `image_path`, `user_id`, `created_at`, `updated_at`, `stock`) VALUES
(2, 'LED HEADLIGHTS', 'High-performance LED headlight', 299.99, 6, 'uploads/1747837207_led-headlights.jpg', 1, '2025-05-19 16:25:04', '2025-05-21 17:03:13', 100),
(3, 'Premium DIY Leather Steering Wheel Cover', 'Upgrade your driving experience with this high-quality, non-slip leather steering wheel cover. Designed for comfort, durability, and style, this DIY wrap-around cover offers a custom-fit solution for car enthusiasts who value both aesthetics and functionality.\r\n\r\nFeatures:\r\n\r\nPremium Material: Made from durable, breathable microfiber leather with an anti-slip texture for improved grip and control.\r\n\r\nStylish Stitching: Available in multiple thread colors (red, blue, black) to match your interior and add a personal touch.\r\n\r\nEnhanced Comfort: Soft padding and textured surface reduce hand fatigue during long drives.\r\n\r\nDIY Friendly: Comes with needle and thread for easy hand-stitching—perfect for those who enjoy a personalized project.\r\n\r\nUniversal Fit: Suitable for most standard-size steering wheels (14.5\"–15\" diameter).\r\n\r\nIdeal For:\r\n\r\nDaily commuters\r\n\r\nCar mod enthusiasts\r\n\r\nAnyone looking to protect their original steering wheel from wear and tear\r\n\r\nElevate your car’s interior with a steering wheel cover that combines elegance, comfort, and a touch of DIY flair!', 199.99, 2, 'uploads/1747842420_steering-cover.jpg', 2, '2025-05-21 15:47:00', '2025-05-21 15:47:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `security_questions`
--

CREATE TABLE `security_questions` (
  `id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `security_questions`
--

INSERT INTO `security_questions` (`id`, `question`) VALUES
(1, 'What is your mother\'s maiden name?'),
(2, 'What was the name of your first pet?'),
(3, 'What is your favorite color?'),
(4, 'What city were you born in?'),
(5, 'What is your favorite food?');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `role` enum('customer','seller','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `security_question_id` int(11) DEFAULT NULL,
  `security_answer` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `address`, `phone`, `role`, `created_at`, `updated_at`, `security_question_id`, `security_answer`) VALUES
(1, 'dree', 'dyree@gmail.com', '$2y$10$nt3WPfvbkMFQPuRNFO.vh.mDrVe2aDkFE6OuqYXuvDZgm6OwVLIwG', 'Dyree Orase', 'San Fernando, Cebu, Philippines', '09912345678', 'seller', '2025-05-19 16:16:14', '2025-05-21 17:18:50', 1, 'Rox'),
(2, 'injel', 'angel@gmail.com', '$2y$10$h/2ITsTvcTLQzow9juSy9u8jSdl6Sq36em/W.fISzQVFRiKped38a', 'Angel Amaro', 'Minglanilla, Cebu', '0990987654', 'seller', '2025-05-21 13:24:08', '2025-05-21 13:24:08', 1, 'Clydee');

-- --------------------------------------------------------

--
-- Table structure for table `user_phones`
--

CREATE TABLE `user_phones` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

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
  ADD KEY `category_id` (`category_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `security_questions`
--
ALTER TABLE `security_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_phones`
--
ALTER TABLE `user_phones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `security_questions`
--
ALTER TABLE `security_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_phones`
--
ALTER TABLE `user_phones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_phones`
--
ALTER TABLE `user_phones`
  ADD CONSTRAINT `user_phones_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
