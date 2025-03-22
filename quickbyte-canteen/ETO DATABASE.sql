-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 22, 2025 at 04:02 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_storage`
--

CREATE TABLE `food_storage` (
  `storage_id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `expiration_day` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_storage`
--

INSERT INTO `food_storage` (`storage_id`, `stall_id`, `item_id`, `quantity`, `expiration_day`) VALUES
(3, 9, 12, 12, '2025-03-25'),
(4, 9, 13, 20, '2025-03-28'),
(5, 8, 14, 12, '2025-03-27');

-- --------------------------------------------------------

--
-- Table structure for table `gcash_payment_details`
--

CREATE TABLE `gcash_payment_details` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `gcash_reference` varchar(100) NOT NULL,
  `gcash_image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gcash_payment_details`
--

INSERT INTO `gcash_payment_details` (`id`, `order_id`, `gcash_reference`, `gcash_image_path`) VALUES
(1, 'ORDER_67dd2f93ab76a', '11231231', 'images/gcash/67dd2f93a76dc_476974947_598964489616296_4802757199467559226_n.png'),
(2, 'ORDER_67dd31cfe0570', '23232', 'images/gcash/67dd31cfde1ac_476974947_598964489616296_4802757199467559226_n.png');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventory_id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `expiry_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`inventory_id`, `stall_id`, `product_id`, `quantity`, `expiry_date`, `created_at`, `last_updated`) VALUES
(3, 9, 2, 27, '2025-03-23', '2025-03-22 08:35:15', '2025-03-22 08:36:09'),
(4, 9, 2, 12, '2025-03-25', '2025-03-22 08:38:36', '2025-03-22 08:39:02');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `item_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` enum('Snacks','Drinks','Meals') NOT NULL,
  `availability` enum('Available','Out Of Stock') DEFAULT 'Out Of Stock',
  `image_path` varchar(255) NOT NULL DEFAULT 'default.jpg',
  `stall_id` int(11) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`item_id`, `name`, `price`, `category`, `availability`, `image_path`, `stall_id`, `description`) VALUES
(12, 'egs', 150.00, 'Snacks', 'Available', '../../images/287722010_2617601478373713_8808211517651136_n.png', 9, NULL),
(13, 'French Fries', 40.00, 'Snacks', 'Available', '../../images/39fec7a8a5f87a0f95f2863a5854f8c2.jpg_720x720q80.jpg', 9, 'Masarap'),
(14, 'Cheeseburger', 349.99, 'Snacks', 'Out Of Stock', '../../images/39fec7a8a5f87a0f95f2863a5854f8c2.jpg_720x720q80.jpg', 8, 'Masarap, parang si Levi');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_status` enum('Pending','Completed','Cancelled','Ready for Pickup','Partially Completed','Preparing') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `order_detail_id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `status` enum('Preparing','Ready','Cancelled','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `category` enum('Condiment','Beverage','Eating Essential','') NOT NULL,
  `unit` enum('Can','Bottle','Pack','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category`, `unit`) VALUES
(1, 'Ketchup', 'Condiment', 'Bottle'),
(2, 'Cheese', 'Condiment', 'Pack');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `receipt_id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `receipt_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('in-store','gcash') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receipts`
--

INSERT INTO `receipts` (`receipt_id`, `order_id`, `user_id`, `receipt_date`, `total_amount`, `payment_method`) VALUES
(1, 'ORDER_67d5205a8d1e8', 17, '2025-03-15 06:38:18', 5.00, 'in-store'),
(2, 'ORDER_67d5342343b5b', 17, '2025-03-15 08:02:43', 14.00, 'in-store'),
(3, 'ORDER_67d5342345ce7', 17, '2025-03-15 08:02:43', 1.00, 'in-store'),
(4, 'ORDER_67dd2f93ab76a', 17, '2025-03-21 09:21:23', 1.00, 'gcash'),
(5, 'ORDER_67dd31cfe0570', 7, '2025-03-21 09:30:55', 8.00, 'gcash'),
(6, 'ORDER_67dd6f8b7bdf6', 6, '2025-03-21 13:54:19', 1.00, 'in-store'),
(7, 'ORDER_67dd71a325fca', 14, '2025-03-21 14:03:15', 1.00, 'in-store');

-- --------------------------------------------------------

--
-- Table structure for table `retailers`
--

CREATE TABLE `retailers` (
  `retailer_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stall_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `retailers`
--

INSERT INTO `retailers` (`retailer_id`, `user_id`, `stall_id`) VALUES
(1, 14, 9),
(2, 15, 8),
(3, 16, 13);

-- --------------------------------------------------------

--
-- Table structure for table `stalls`
--

CREATE TABLE `stalls` (
  `stall_id` int(11) NOT NULL,
  `stall_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL DEFAULT 'default_stall.jpg',
  `status` enum('Open','Closed','','') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stalls`
--

INSERT INTO `stalls` (`stall_id`, `stall_name`, `description`, `image_path`, `status`, `created_at`) VALUES
(1, 'Bonapetite', 'Delicious Burgers and Fries', 'images/store1.jpg', 'Open', '2025-03-22 07:56:07'),
(2, 'Kael', 'Fresh Drinks and Juices', 'images/store2.jpg', 'Open', '2025-03-22 07:56:07'),
(3, 'asfdfg', 'Tasty Snacks and Desserts', 'images/store3.jpg', 'Open', '2025-03-22 07:56:07'),
(4, 'The Hungry Hippo', 'Sells snacks and beverages', 'images/stall1.jpg', 'Open', '2025-03-22 07:56:07'),
(5, 'Rice & Shine', 'Offers rice meals and soups', 'images/stall2.jpg', 'Open', '2025-03-22 07:56:07'),
(6, 'Juice Junction', 'Specializes in fresh fruit juices', 'images/stall3.jpg', 'Open', '2025-03-22 07:56:07'),
(7, 'Grillzilla', 'Fast food and grilled items', 'images/stall4.jpg', 'Open', '2025-03-22 07:56:07'),
(8, 'Sweet Tooth Haven', 'Desserts and sweets', 'images/stall5.jpg', 'Open', '2025-03-22 07:56:07'),
(9, 'Fried House', 'Authentic Filipino cuisine', '../../images/GTA BACK.jpg', 'Open', '2025-03-22 07:56:07'),
(10, 'Green Bites', 'Vegetarian and healthy options', 'images/stall7.jpg', 'Open', '2025-03-22 07:56:07'),
(11, 'Bean & Brew', 'Coffee, tea, and pastries', 'images/stall8.jpg', 'Open', '2025-03-22 07:56:07'),
(12, 'Taste of Asia', 'International dishes', 'images/stall9.jpg', 'Open', '2025-03-22 07:56:07'),
(13, 'Street Feast', 'Street food and local delicacies', 'images/stall10.jpg', 'Open', '2025-03-22 07:56:07');

-- --------------------------------------------------------

--
-- Table structure for table `stall_application`
--

CREATE TABLE `stall_application` (
  `application_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `stall_name` varchar(100) NOT NULL,
  `stall_description` text DEFAULT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Approved','Denied') DEFAULT 'Pending',
  `verification_date` timestamp NULL DEFAULT NULL,
  `birth_certificate` varchar(255) DEFAULT NULL,
  `tin_number` varchar(255) DEFAULT NULL,
  `business_permit` varchar(255) DEFAULT NULL,
  `valid_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stall_application`
--

INSERT INTO `stall_application` (`application_id`, `user_id`, `stall_name`, `stall_description`, `application_date`, `status`, `verification_date`, `birth_certificate`, `tin_number`, `business_permit`, `valid_id`) VALUES
(3, 17, 'Melvin Eatery', 'asasdfge', '2025-03-22 14:59:53', 'Pending', NULL, 'images/application/birth/67ded069b5a2c.jpg', 'images/application/tin/67ded069b5b33.jpg', 'images/application/permit/67ded069b5c15.jpg', 'images/application/id/67ded069b5c9f.jpg'),
(4, 17, 'Melvin Eatery', 'asasdfge', '2025-03-22 15:00:15', 'Pending', NULL, 'images/application/birth/67ded07fe2501.jpg', 'images/application/tin/67ded07fe25f1.jpg', 'images/application/permit/67ded07fe2687.jpg', 'images/application/id/67ded07fe2717.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Student','Retailer','Admin') DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `phone` varchar(15) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `image_path` varchar(255) DEFAULT 'images/default-profile.jpg',
  `email_unique` varchar(255) GENERATED ALWAYS AS (case when `email` = 'ad123min@gmail.com' then NULL else `email` end) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(15, 'Melvs', 'w@gmail.com', '$2y$10$OdI59Cgo1bxHCpANUlNYBOgjZ/EpR40uSaQNXmGfPX.IMSnT0RZBK', 'Retailer', 0.00, NULL, NULL, 1, 'images/default-profile.jpg'),
(16, 'Allan', 'allanmonforte1@gmail.com', '$2y$10$KV6xmya9.Vfw1pcUd0xnZ.6RMmrN76krd/6PUZX48uH.fF.pbSsnS', 'Retailer', 0.00, NULL, NULL, 1, 'images/default-profile.jpg'),
(17, 'melvs', 'catueramelvin08@gmail.com', '$2y$10$OPA6FtH6PQS/fEETzyFoh.Lkh6cHbau8bN858BgfssD0ya2wXPpjy', 'Retailer', 0.00, NULL, NULL, 1, 'images/default-profile.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_feedback_stalls` (`stall_id`);

--
-- Indexes for table `food_storage`
--
ALTER TABLE `food_storage`
  ADD PRIMARY KEY (`storage_id`),
  ADD KEY `stall_id` (`stall_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `gcash_payment_details`
--
ALTER TABLE `gcash_payment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventory_id`),
  ADD KEY `item_id` (`product_id`),
  ADD KEY `connect_stall` (`stall_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `stall_id` (`stall_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `connect_stall` (`stall_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`order_detail_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `fk_orderdetails_menuitems` (`item_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`receipt_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `retailers`
--
ALTER TABLE `retailers`
  ADD PRIMARY KEY (`retailer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `stalls`
--
ALTER TABLE `stalls`
  ADD PRIMARY KEY (`stall_id`);

--
-- Indexes for table `stall_application`
--
ALTER TABLE `stall_application`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `email_unique` (`email_unique`),
  ADD UNIQUE KEY `unique_email_constraint` (`email`,`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `food_storage`
--
ALTER TABLE `food_storage`
  MODIFY `storage_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `gcash_payment_details`
--
ALTER TABLE `gcash_payment_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `order_detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `receipt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `retailers`
--
ALTER TABLE `retailers`
  MODIFY `retailer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `stalls`
--
ALTER TABLE `stalls`
  MODIFY `stall_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `stall_application`
--
ALTER TABLE `stall_application`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
  ADD CONSTRAINT `fk_feedback_stalls` FOREIGN KEY (`stall_id`) REFERENCES `stalls` (`stall_id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `fk_orderdetails_menuitems` FOREIGN KEY (`item_id`) REFERENCES `menu_items` (`item_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
