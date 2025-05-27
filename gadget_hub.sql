-- Database: `gadget_hub`

-- Table structure for table `ads`
CREATE TABLE `ads` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `status` enum('active','rejected','pending') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `subcategory_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `location` varchar(100) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `categories`
CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `subcategories`
CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_id` int(11) NOT NULL,
  `ad_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('User','Admin') DEFAULT 'User',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `phone` varchar(50) NOT NULL,
  `profile_image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `categories`
INSERT INTO `categories` (`id`, `name`, `image_path`) VALUES
(1, 'Smartphones', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500'),
(2, 'Laptops', 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500'),
(3, 'Gaming', 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=500'),
(4, 'Audio', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500'),
(5, 'Accessories', 'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=500');

-- Dumping data for table `subcategories`
INSERT INTO `subcategories` (`id`, `name`, `category_id`, `ad_count`, `created_at`) VALUES
(1, 'iPhone', 1, 0, '2025-05-14 22:04:00'),
(2, 'Samsung', 1, 0, '2025-05-14 22:04:00'),
(3, 'Xiaomi', 1, 0, '2025-05-14 22:04:00'),
(4, 'MacBook', 2, 0, '2025-05-14 22:04:00'),
(5, 'Windows Laptops', 2, 0, '2025-05-14 22:04:00'),
(6, 'Gaming Consoles', 3, 0, '2025-05-14 22:04:00'),
(7, 'Gaming Accessories', 3, 0, '2025-05-14 22:04:00'),
(8, 'Headphones', 4, 0, '2025-05-14 22:04:00'),
(9, 'Speakers', 4, 0, '2025-05-14 22:04:00'),
(10, 'Phone Cases', 5, 0, '2025-05-14 22:04:00');

-- Dumping data for table `users`
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `profile_image`) VALUES
(1, 'Admin User', 'admin@gadgethub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', '1234567890', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=500'),
(2, 'John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'User', '2345678901', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=500'),
(3, 'Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'User', '3456789012', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=500');

-- Dumping data for table `ads`
INSERT INTO `ads` (`id`, `user_id`, `title`, `price`, `description`, `status`, `category_id`, `subcategory_id`, `location`, `image_path`) VALUES
(1, 2, 'iPhone 13 Pro Max - Like New', 899.99, 'iPhone 13 Pro Max in excellent condition. 256GB storage, Pacific Blue color. Includes original box and accessories.', 'active', 1, 1, 'New York', 'https://images.unsplash.com/photo-1632661674596-79bd3e16c2bd?w=500'),
(2, 2, 'MacBook Pro 2023 M2', 1299.99, 'MacBook Pro with M2 chip, 16GB RAM, 512GB SSD. Perfect condition, barely used.', 'active', 2, 4, 'Los Angeles', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500'),
(3, 3, 'Sony WH-1000XM4 Headphones', 249.99, 'Sony WH-1000XM4 wireless noise-cancelling headphones. Includes carrying case and all accessories.', 'active', 4, 8, 'Chicago', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500'),
(4, 3, 'PlayStation 5 Bundle', 499.99, 'PS5 Digital Edition with 2 controllers and 3 games. Like new condition.', 'active', 3, 6, 'Miami', 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?w=500'),
(5, 2, 'Samsung Galaxy S22 Ultra', 799.99, 'Samsung Galaxy S22 Ultra 256GB, Phantom Black. Includes S Pen and original accessories.', 'active', 1, 2, 'Boston', 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=500');