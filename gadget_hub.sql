-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 10:04 PM
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
-- Database: `gadget_hub`
--

-- --------------------------------------------------------

--
-- Table structure for table `ads`
--

CREATE TABLE `ads` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `status` enum('active','rejected','pending') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `subcategory_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------


-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `ad_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('User','Admin') DEFAULT 'User',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `phone` varchar(50) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image_path`) VALUES
(1, 'Smartphones', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500'),
(2, 'Laptops', 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500'),
(3, 'Gaming', 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=500'),
(4, 'Audio', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500'),
(5, 'Accessories', 'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=500');

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`id`, `name`, `category_id`, `ad_count`) VALUES
(1, 'iPhone', 1, 0),
(2, 'Samsung', 1, 0),
(3, 'Xiaomi', 1, 0),
(4, 'MacBook', 2, 0),
(5, 'Windows Laptops', 2, 0),
(6, 'Gaming Consoles', 3, 0),
(7, 'Gaming Accessories', 3, 0),
(8, 'Headphones', 4, 0),
(9, 'Speakers', 4, 0),
(10, 'Phone Cases', 5, 0);

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `profile_image`) VALUES
(1, 'Admin User', 'admin@gadgethub.com', '$2y$10$8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM', 'Admin', '1234567890', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=500'),
(2, 'John Doe', 'john@example.com', '$2y$10$8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM', 'User', '2345678901', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=500'),
(3, 'Jane Smith', 'jane@example.com', '$2y$10$8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM', 'User', '3456789012', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=500');

--
-- Dumping data for table `ads`
--

INSERT INTO `ads` (`id`, `user_id`, `title`, `price`, `description`, `status`, `category_id`, `subcategory_id`, `location`, `image_path`) VALUES
(1, 2, 'iPhone 13 Pro Max - Like New', 899.99, 'iPhone 13 Pro Max in excellent condition. 256GB storage, Pacific Blue color. Includes original box and accessories.', 'active', 1, 1, 'New York', 'https://images.unsplash.com/photo-1632661674596-79bd3e16c2bd?w=500'),
(2, 2, 'MacBook Pro 2023 M2', 1299.99, 'MacBook Pro with M2 chip, 16GB RAM, 512GB SSD. Perfect condition, barely used.', 'active', 2, 4, 'Los Angeles', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500'),
(3, 3, 'Sony WH-1000XM4 Headphones', 249.99, 'Sony WH-1000XM4 wireless noise-cancelling headphones. Includes carrying case and all accessories.', 'active', 4, 8, 'Chicago', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500'),
(4, 3, 'PlayStation 5 Bundle', 499.99, 'PS5 Digital Edition with 2 controllers and 3 games. Like new condition.', 'active', 3, 6, 'Miami', 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?w=500'),
(5, 2, 'Samsung Galaxy S22 Ultra', 799.99, 'Samsung Galaxy S22 Ultra 256GB, Phantom Black. Includes S Pen and original accessories.', 'active', 1, 2, 'Boston', 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=500');

--
-- Indexes for dumped tables
--

ALTER TABLE `ads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `subcategory_id` (`subcategory_id`);

ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

ALTER TABLE `ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

ALTER TABLE `ads`
  ADD CONSTRAINT `ads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ads_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `ads_ibfk_3` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`);

ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

COMMIT;