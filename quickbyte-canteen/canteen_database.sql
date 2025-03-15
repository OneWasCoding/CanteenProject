-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 15, 2025 at 06:30 AM
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
-- Database: `canteen_database`
--
CREATE DATABASE IF NOT EXISTS `canteen_database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `canteen_database`;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE IF NOT EXISTS `cart` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`cart_id`),
  KEY `user_id` (`user_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `user_id`, `item_id`, `quantity`) VALUES
(37, 2, 5, 1),
(38, 14, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE IF NOT EXISTS `feedback` (
  `feedback_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`feedback_id`),
  KEY `user_id` (`user_id`),
  KEY `stall_id` (`stall_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_storage`
--

CREATE TABLE IF NOT EXISTS `food_storage` (
  `storage_id` int(11) NOT NULL AUTO_INCREMENT,
  `stall_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `food_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `expiration_day` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`storage_id`),
  KEY `stall_id` (`stall_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE IF NOT EXISTS `inventory` (
  `inventory_id` int(11) NOT NULL AUTO_INCREMENT,
  `stall_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `expiry_date` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_updated` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`inventory_id`),
  KEY `item_id` (`product_id`),
  KEY `connect_stall` (`stall_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `stall_id`, `product_id`, `quantity`, `expiry_date`, `last_updated`, `created_at`) VALUES
(1, 0, 1, 43, '2025-03-12 00:42:29', '2025-03-02 11:01:20', '2025-03-15 03:48:05'),
(2, 0, 2, 89, '2025-03-12 00:42:29', '2025-02-28 04:01:46', '2025-03-15 03:48:05'),
(3, 0, 3, 177, '2025-03-12 00:42:29', '2025-03-02 09:54:55', '2025-03-15 03:48:05'),
(4, 0, 4, 29, '2025-03-12 00:42:29', '2025-02-20 13:10:40', '2025-03-15 03:48:05'),
(5, 0, 5, 66, '2025-03-12 00:42:29', '2025-02-21 01:22:01', '2025-03-15 03:48:05');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE IF NOT EXISTS `menu_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` enum('Snacks','Drinks','Meals') NOT NULL,
  `availability` tinyint(1) DEFAULT 1,
  `image_path` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `stall_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`item_id`),
  KEY `stall_id` (`stall_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`item_id`, `name`, `price`, `category`, `availability`, `image_path`, `stall_id`, `description`) VALUES
(1, 'Cheeseburger', 5.99, 'Meals', 1, 'images/cheeseburger.jpg', 1, 'A delicious cheeseburger with fresh ingredients.'),
(2, 'French Fries', 2.99, 'Snacks', 1, 'images/french_fries.jpg', 1, 'Crispy and golden French fries, perfect as a snack.'),
(3, 'Coke', 1.50, 'Drinks', 1, 'images/coke.jpg', 2, 'Refreshing Coca-Cola drink, served cold.'),
(4, 'Pizza Slice', 3.99, 'Meals', 1, 'images/pizza_slice.jpg', 1, 'A slice of cheesy pizza with a crispy crust.'),
(5, 'Ice Cream', 2.49, 'Snacks', 1, 'images/ice_cream.jpg', 3, 'Creamy and sweet ice cream, available in multiple flavors.');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_status` enum('Pending','Completed','Cancelled','Ready-for-Pickup','Partially Completed','Preparing') DEFAULT 'Pending',
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`),
  KEY `connect_stall` (`stall_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `stall_id`, `total_price`, `order_date`, `order_status`) VALUES
('ORDER_67b729d0000a00.79837591', 1, 9, 9.98, '2025-02-20 13:10:40', 'Completed'),
('ORDER_67b729da8ec268.39575638', 1, 0, 4.50, '2025-02-20 13:10:50', ''),
('ORDER_67b732cdde3a30.48020126', 1, 0, 1.50, '2025-02-20 13:49:01', ''),
('ORDER_67b7d49e4e5f71.05682525', 1, 0, 7.49, '2025-02-21 01:19:26', ''),
('ORDER_67b7d5396f1d13.39117098', 1, 9, 22.41, '2025-02-21 01:22:01', 'Completed'),
('ORDER_67b7d6337e1f40.69251557', 1, 8, 29.93, '2025-02-21 01:26:11', 'Cancelled'),
('ORDER_67b7dae517d179.85585574', 2, 9, 7.50, '2025-02-21 01:46:13', 'Completed'),
('ORDER_67b7ec30719de5.72521993', 2, 0, 5.99, '2025-02-21 03:00:00', 'Pending'),
('ORDER_67c1352adf30c4.98216851', 6, 0, 11.96, '2025-02-28 04:01:46', 'Pending'),
('ORDER_67c42aefd3ff28.57286805', 1, 0, 1.50, '2025-03-02 09:54:55', 'Pending'),
('ORDER_67c43802b22438.44873278', 1, 0, 5.99, '2025-03-02 10:50:42', ''),
('ORDER_67c43a8026f036.63167700', 1, 0, 5.99, '2025-03-02 11:01:20', ''),
('ORDER_67ca47aa5c042', 1, 0, 19.92, '2025-03-07 01:11:06', 'Pending'),
('ORDER_67ca47dfa2a11', 1, 0, 20.93, '2025-03-07 01:11:59', ''),
('ORDER_67ca8a9a1f1cb', 1, 0, 2.99, '2025-03-07 05:56:42', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE IF NOT EXISTS `order_details` (
  `order_detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `status` enum('Preparing','Ready','Cancelled','') NOT NULL,
  PRIMARY KEY (`order_detail_id`),
  KEY `order_id` (`order_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`order_detail_id`, `order_id`, `item_id`, `quantity`, `subtotal`, `unit_price`, `status`) VALUES
(1, 'ORDER_67b729d0000a00.79837591', 1, 2, 11.98, 5.99, 'Preparing'),
(2, 'ORDER_67b729d0000a00.79837591', 2, 1, 2.99, 2.99, 'Preparing'),
(3, 'ORDER_67b729da8ec268.39575638', 3, 3, 4.50, 1.50, 'Preparing'),
(4, 'ORDER_67b732cdde3a30.48020126', 3, 1, 1.50, 1.50, 'Preparing'),
(5, 'ORDER_67b7d49e4e5f71.05682525', 1, 1, 5.99, 5.99, 'Preparing'),
(6, 'ORDER_67b7d49e4e5f71.05682525', 5, 1, 1.50, 1.50, 'Preparing'),
(7, 'ORDER_67ca47aa5c042', 5, 8, 19.92, 2.49, 'Preparing'),
(8, 'ORDER_67ca47dfa2a11', 2, 7, 20.93, 2.99, 'Preparing'),
(9, 'ORDER_67ca8a9a1f1cb', 2, 1, 2.99, 2.99, 'Preparing');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('gcash','balance') DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`payment_id`),
  KEY `order_id` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `order_id`, `user_id`, `amount`, `payment_method`, `status`, `payment_date`) VALUES
(6, 'ORDER_67b729d0000a00.79837591', 1, 9.98, 'balance', 'completed', '2025-02-20 13:10:40'),
(7, 'ORDER_67b729da8ec268.39575638', 1, 4.50, '', 'completed', '2025-02-20 13:10:50'),
(8, 'ORDER_67b732cdde3a30.48020126', 1, 1.50, 'gcash', 'completed', '2025-02-20 13:49:01'),
(9, 'ORDER_67b7d49e4e5f71.05682525', 1, 7.49, 'balance', 'completed', '2025-02-21 01:19:26'),
(10, 'ORDER_67b7d5396f1d13.39117098', 1, 22.41, '', 'completed', '2025-02-21 01:22:01'),
(11, 'ORDER_67b7d6337e1f40.69251557', 1, 29.93, 'balance', 'completed', '2025-02-21 01:26:11'),
(12, 'ORDER_67b7dae517d179.85585574', 2, 7.50, 'balance', 'completed', '2025-02-21 01:46:13'),
(13, 'ORDER_67b7ec30719de5.72521993', 2, 5.99, 'balance', 'completed', '2025-02-21 03:00:00'),
(15, 'ORDER_67c1352adf30c4.98216851', 6, 11.96, '', 'completed', '2025-02-28 04:01:46'),
(16, 'ORDER_67c42aefd3ff28.57286805', 1, 1.50, 'gcash', 'completed', '2025-03-02 09:54:55'),
(17, 'ORDER_67c43802b22438.44873278', 1, 5.99, 'balance', 'completed', '2025-03-02 10:50:42'),
(18, 'ORDER_67c43a8026f036.63167700', 1, 5.99, 'gcash', 'completed', '2025-03-02 11:01:20');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE IF NOT EXISTS `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_name` varchar(100) NOT NULL,
  `category` enum('Condiment','Beverage','Eating Essential','') NOT NULL,
  `unit` enum('Can','Bottle','Pack','') NOT NULL,
  PRIMARY KEY (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `retailers`
--

CREATE TABLE IF NOT EXISTS `retailers` (
  `retailer_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  PRIMARY KEY (`retailer_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `retailers`
--

INSERT INTO `retailers` (`retailer_id`, `user_id`, `stall_id`) VALUES
(1, 14, 9),
(2, 15, 8);

-- --------------------------------------------------------

--
-- Table structure for table `stalls`
--

CREATE TABLE IF NOT EXISTS `stalls` (
  `stall_id` int(11) NOT NULL AUTO_INCREMENT,
  `stall_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL DEFAULT 'default_stall.jpg',
  PRIMARY KEY (`stall_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stalls`
--

INSERT INTO `stalls` (`stall_id`, `stall_name`, `description`, `image_path`) VALUES
(1, 'Bonapetite', 'Delicious Burgers and Fries', 'images/store1.jpg'),
(2, 'Kael', 'Fresh Drinks and Juices', 'images/store2.jpg'),
(3, 'asfdfg', 'Tasty Snacks and Desserts', 'images/store3.jpg'),
(4, 'The Hungry Hippo', 'Sells snacks and beverages', 'images/stall1.jpg'),
(5, 'Rice & Shine', 'Offers rice meals and soups', 'images/stall2.jpg'),
(6, 'Juice Junction', 'Specializes in fresh fruit juices', 'images/stall3.jpg'),
(7, 'Grillzilla', 'Fast food and grilled items', 'images/stall4.jpg'),
(8, 'Sweet Tooth Haven', 'Desserts and sweets', 'images/stall5.jpg'),
(9, 'Lutong Bahay', 'Authentic Filipino cuisine', 'images/stall6.jpg'),
(10, 'Green Bites', 'Vegetarian and healthy options', 'images/stall7.jpg'),
(11, 'Bean & Brew', 'Coffee, tea, and pastries', 'images/stall8.jpg'),
(12, 'Taste of Asia', 'International dishes', 'images/stall9.jpg'),
(13, 'Street Feast', 'Street food and local delicacies', 'images/stall10.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Student','Retailer','Admin') DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `phone` varchar(15) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `image_path` varchar(255) DEFAULT 'images/default-profile.jpg',
  `email_unique` varchar(255) GENERATED ALWAYS AS (case when `email` = 'ad123min@gmail.com' then NULL else `email` end) STORED,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `email_unique` (`email_unique`),
  UNIQUE KEY `unique_email_constraint` (`email`,`role`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `balance`, `phone`, `address`, `status`, `image_path`) VALUES
(1, 'Allan Monforte', 'allanmonforte@gmail.com', '$2y$10$dc.DM7LykXx8iyqgPZK4U.7epAUrDJqVSFLGUWbYNu6Ai1zG0j2ou', 'Admin', 13497.19, '09686827403', '285 PNR Site, Western Bicutan Taguig City, Western Bicutan', 0, 'images/profiles/cute-cat-eyes-profile-picture-uq3edzmg1guze2hh.jpg'),
(2, 'eggs', 'ego123123@gmail.net', '$2y$10$jTeazZ6pzHdI2d5dSxkIdu65pLFLM7xpyR6mmExVIcrb1vSdoxgVq', 'Student', 99999999.99, '6969', 'gayland', 1, 'images/default-profile.jpg'),
(3, 'Melvin', 'melvin1234@gmail.com', '$2y$10$3sdO.wGghgce/3n6jipIh.syBETL7VagujIPjIKXiUFh7inGO6DpK', 'Student', 0.00, NULL, NULL, 1, 'images/default-profile.jpg'),
(4, 'zcdasad@gasfsas.coaj', '123123@gmail.com', '$2y$10$.7F2Ud.lXqib.uNErYlc1uVy162S.7KKxeB64PyORXj8sHhBx5aTq', 'Student', 0.00, NULL, NULL, 1, 'images/default-profile.jpg'),
(6, 'egoian', 'ego123@gmail.com', '$2y$10$v7CN02fZjTAVgoheVDzBWOiHtbnsTRbxZGJHyp/v7mf4seTHKKWSy', 'Student', 0.00, NULL, NULL, 1, 'images/profiles/Ego.jpeg'),
(7, 'egoego123', 'ego123123123@gmail.com', '$2y$10$hhh3B7FizI7.VqRDV4vgcuraBJF/8XuR88mo.qe0kkFhcvEXRYxIy', 'Admin', 0.00, NULL, NULL, 1, 'images/default-profile.jpg'),
(8, 'Egos', 'ad123min@gmail.com', '$2y$10$aJ5tAaRF477X6kvm.kjFkuy8VKeNpGdBHKAq2JTWkU05BwKVv2J0i', 'Student', 0.00, NULL, NULL, 1, 'images/default-profile.jpg'),
(9, 'Ianzae Ryan P. Ego', 'tolitsjacks@gmail.com', '$2y$10$TJZaVULCZEB6ho7ZGW65JuKVXllthAocnuNxVlIrdzlUgtKLbwJOe', 'Student', 0.00, NULL, NULL, 1, 'images/default-profile.jpg'),
(11, 'Meinfried', '123@gmail.com', '$2y$10$dA6o1HwMunCiebYLSZo5Qe.1WF6SavcA0xaDDjXUxdYL9ETd1u/Ey', 'Student', 0.00, NULL, NULL, 1, 'images/default-profile.jpg'),
(12, 'Allan', 'a@gmail.com', '$2y$10$jS3SFUAO0CMTvBPaAO0hveeKYWM7K3EmGcNZBxW9v/FEd3dewbAQS', 'Student', 0.00, NULL, NULL, 1, 'images/default-profile.jpg'),
(13, 'Levi', 'l@gmail.com', '$2y$10$HlFlEWdBU7GbnX2k.70u7ujLB1hpJ8T.mRNuDd3MS2VKcNKQglxaa', 'Student', 0.00, NULL, NULL, 1, 'images/default-profile.jpg'),
(14, 'bor', 'borjabisaya@gmail.com', '$2y$10$CSxsLT/j4yOHoyTD8vBuKO0QvYbOhPVPq90aIHs6jHiLIQ4b8aeMK', 'Retailer', 0.00, NULL, NULL, 1, 'images/default-profile.jpg'),
(15, 'Melvs', 'w@gmail.com', '$2y$10$OdI59Cgo1bxHCpANUlNYBOgjZ/EpR40uSaQNXmGfPX.IMSnT0RZBK', 'Retailer', 0.00, NULL, NULL, 1, 'images/default-profile.jpg');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`stall_id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `fk_stall` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`stall_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `retailers`
--
ALTER TABLE `retailers`
  ADD CONSTRAINT `retailers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
