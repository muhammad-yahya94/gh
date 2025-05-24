<?php
// Database connection parameters
$host = 'localhost';
$dbname = 'gadget_hub';
$username = 'root';
$password = '';

try {
    // Create connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Start transaction
    $conn->beginTransaction();

    // Disable foreign key checks temporarily
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Clear existing data
    $tables = ['ads', 'subcategories', 'categories', 'users'];
    foreach ($tables as $table) {
        $conn->exec("TRUNCATE TABLE `$table`");
    }

    // Re-enable foreign key checks
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Insert Categories
    $categories = [
        ['Smartphones', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500'],
        ['Laptops', 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500'],
        ['Gaming', 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=500'],
        ['Audio', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500'],
        ['Accessories', 'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=500']
    ];

    $stmt = $conn->prepare("INSERT INTO categories (name, image_path) VALUES (?, ?)");
    foreach ($categories as $category) {
        $stmt->execute($category);
    }

    // Insert Subcategories
    $subcategories = [
        ['iPhone', 1, 0],
        ['Samsung', 1, 0],
        ['Xiaomi', 1, 0],
        ['MacBook', 2, 0],
        ['Windows Laptops', 2, 0],
        ['Gaming Consoles', 3, 0],
        ['Gaming Accessories', 3, 0],
        ['Headphones', 4, 0],
        ['Speakers', 4, 0],
        ['Phone Cases', 5, 0]
    ];

    $stmt = $conn->prepare("INSERT INTO subcategories (name, category_id, ad_count) VALUES (?, ?, ?)");
    foreach ($subcategories as $subcategory) {
        $stmt->execute($subcategory);
    }

    // Insert Users
    $users = [
        ['Admin User', 'admin@gadgethub.com', '$2y$10$8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM', 'Admin', '1234567890', 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=500'],
        ['John Doe', 'john@example.com', '$2y$10$8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM', 'User', '2345678901', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=500'],
        ['Jane Smith', 'jane@example.com', '$2y$10$8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM8K1p/a0dR1xqM', 'User', '3456789012', 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=500']
    ];

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($users as $user) {
        $stmt->execute($user);
    }

    // Insert Ads
    $ads = [
        [2, 'iPhone 13 Pro Max - Like New', 899.99, 'iPhone 13 Pro Max in excellent condition. 256GB storage, Pacific Blue color. Includes original box and accessories.', 'active', 1, 1, 'New York', 'https://images.unsplash.com/photo-1632661674596-79bd3e16c2bd?w=500'],
        [2, 'MacBook Pro 2023 M2', 1299.99, 'MacBook Pro with M2 chip, 16GB RAM, 512GB SSD. Perfect condition, barely used.', 'active', 2, 4, 'Los Angeles', 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500'],
        [3, 'Sony WH-1000XM4 Headphones', 249.99, 'Sony WH-1000XM4 wireless noise-cancelling headphones. Includes carrying case and all accessories.', 'active', 4, 8, 'Chicago', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500'],
        [3, 'PlayStation 5 Bundle', 499.99, 'PS5 Digital Edition with 2 controllers and 3 games. Like new condition.', 'active', 3, 6, 'Miami', 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?w=500'],
        [2, 'Samsung Galaxy S22 Ultra', 799.99, 'Samsung Galaxy S22 Ultra 256GB, Phantom Black. Includes S Pen and original accessories.', 'active', 1, 2, 'Boston', 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?w=500']
    ];

    $stmt = $conn->prepare("INSERT INTO ads (user_id, title, price, description, status, category_id, subcategory_id, location, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($ads as $ad) {
        $stmt->execute($ad);
    }

    // Commit transaction
    $conn->commit();

    echo "Database initialized successfully!";
} catch(PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}

$conn = null;
?> 