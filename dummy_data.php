<?php
require_once 'db.php';

// Pakistani cities
$cities = [
    'Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Faisalabad',
    'Multan', 'Peshawar', 'Quetta', 'Sialkot', 'Gujranwala'
];

// Pakistani names
$firstNames = [
    'Muhammad', 'Ali', 'Ahmed', 'Hassan', 'Usman', 'Hamza', 'Bilal', 'Zain',
    'Fatima', 'Ayesha', 'Sana', 'Hina', 'Sadia', 'Nida', 'Amina', 'Zara'
];
$lastNames = [
    'Khan', 'Ali', 'Ahmed', 'Hussain', 'Raza', 'Malik', 'Butt', 'Chaudhry',
    'Sheikh', 'Qureshi', 'Hashmi', 'Zaidi', 'Rizvi', 'Jafri', 'Naqvi', 'Shah'
];

// Categories with realistic images
$categories = [
    ['Smartphones', 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500'],
    ['Laptops', 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500'],
    ['Gaming', 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=500'],
    ['Audio', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500'],
    ['Accessories', 'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=500']
];

// Subcategories with realistic mappings
$subcategories = [
    ['iPhone', 1],
    ['Samsung', 1],
    ['Xiaomi', 1],
    ['MacBook', 2],
    ['Windows Laptops', 2],
    ['Gaming Consoles', 3],
    ['Gaming Accessories', 3],
    ['Headphones', 4],
    ['Speakers', 4],
    ['Phone Cases', 5]
];

// Product conditions
$conditions = ['New', 'Like New', 'Good', 'Fair'];

// Generate random Pakistani name
function generatePakistaniName() {
    global $firstNames, $lastNames;
    return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
}

// Generate random Pakistani email
function generatePakistaniEmail($name) {
    $domains = ['gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com'];
    $name = strtolower(str_replace(' ', '', $name));
    return $name . rand(1, 999) . '@' . $domains[array_rand($domains)];
}

// Generate random Pakistani phone number
function generatePakistaniPhone() {
    $prefixes = ['0300', '0301', '0302', '0303', '0304', '0305', '0306', '0307', '0308', '0309',
                 '0310', '0311', '0312', '0313', '0314', '0315', '0316', '0317', '0318', '0319',
                 '0320', '0321', '0322', '0323', '0324', '0325', '0326', '0327', '0328', '0329',
                 '0330', '0331', '0332', '0333', '0334', '0335', '0336', '0337', '0338', '0339',
                 '0340', '0341', '0342', '0343', '0344', '0345', '0346', '0347', '0348', '0349'];
    return $prefixes[array_rand($prefixes)] . rand(1000000, 9999999);
}

try {
    // Delete existing data
    echo "Deleting existing data...\n";
    
    // Disable foreign key checks temporarily
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Delete data from all tables
    $tables = ['ads', 'subcategories', 'categories', 'users'];
    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE $table");
        echo "Cleared table: $table\n";
    }
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Insert Categories
    echo "Inserting categories...\n";
    foreach ($categories as $category) {
        $stmt = $pdo->prepare("INSERT INTO categories (name, image_path) VALUES (?, ?)");
        $stmt->execute($category);
    }

    // Insert Subcategories
    echo "Inserting subcategories...\n";
    foreach ($subcategories as $subcategory) {
        $stmt = $pdo->prepare("INSERT INTO subcategories (name, category_id, ad_count) VALUES (?, ?, 0)");
        $stmt->execute($subcategory);
    }

    // Insert Users
    echo "Inserting users...\n";
    $users = [];
    
    // Regular users
    for ($i = 0; $i < 20; $i++) {
        $name = generatePakistaniName();
        $email = generatePakistaniEmail($name);
        $password = password_hash('user123', PASSWORD_DEFAULT);
        $role = 'User';
        $phone = generatePakistaniPhone();
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role, $phone]);
        $users[] = $pdo->lastInsertId();
    }

    // Admin users
    $adminEmails = ['admin@gadgethub.com', 'superadmin@gadgethub.com'];
    foreach ($adminEmails as $email) {
        $name = generatePakistaniName();
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $role = 'Admin';
        $phone = generatePakistaniPhone();
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $password, $role, $phone]);
        $users[] = $pdo->lastInsertId();
    }

    // Insert ads
    echo "Inserting ads...\n";
    $statuses = ['active', 'rejected', 'pending'];
    
    // Sample product titles for each category
    $productTitles = [
        1 => ['iPhone 13 Pro Max', 'Samsung Galaxy S21', 'Xiaomi Mi 11', 'OnePlus 9 Pro', 'Google Pixel 6'],
        2 => ['MacBook Pro M1', 'Dell XPS 13', 'HP Spectre x360', 'Lenovo ThinkPad', 'Asus ROG'],
        3 => ['PlayStation 5', 'Xbox Series X', 'Nintendo Switch', 'Gaming PC RTX 3080', 'Gaming Laptop'],
        4 => ['AirPods Pro', 'Sony WH-1000XM4', 'Samsung Galaxy Buds', 'JBL Flip 5', 'Bose QuietComfort'],
        5 => ['iPhone Case', 'Samsung Case', 'Laptop Bag', 'Wireless Charger', 'Screen Protector']
    ];

    // Sample images for ads
    $adImages = [
        1 => 'https://images.unsplash.com/photo-1632661674596-79bd3e16c2bd?w=500',
        2 => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500',
        3 => 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?w=500',
        4 => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500',
        5 => 'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=500'
    ];

    for ($i = 0; $i < 50; $i++) {
        $category_id = rand(1, count($categories));
        $subcategory_id = rand(1, count($subcategories));
        $title = $productTitles[$category_id][array_rand($productTitles[$category_id])] . ' ' . rand(1, 1000);
        
        // Generate realistic Pakistani prices
        $basePrice = 0;
        switch ($category_id) {
            case 1: // Smartphones
                $basePrice = rand(50000, 200000);
                break;
            case 2: // Laptops
                $basePrice = rand(80000, 300000);
                break;
            case 3: // Gaming
                $basePrice = rand(50000, 250000);
                break;
            case 4: // Audio
                $basePrice = rand(5000, 50000);
                break;
            case 5: // Accessories
                $basePrice = rand(1000, 20000);
                break;
        }

        $description = "Brand new " . $title . " for sale. " . 
                      "Condition: " . $conditions[array_rand($conditions)] . ". " .
                      "Location: " . $cities[array_rand($cities)] . ". " .
                      "Contact: " . generatePakistaniPhone();
        
        $stmt = $pdo->prepare("
            INSERT INTO ads (
                title, description, price, location,
                user_id, category_id, subcategory_id, status, image_path
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $title,
            $description,
            $basePrice,
            $cities[array_rand($cities)],
            $users[array_rand($users)],
            $category_id,
            $subcategory_id,
            $statuses[array_rand($statuses)],
            $adImages[$category_id]
        ]);
    }

    echo "Dummy data generation completed successfully!\n";
    echo "Admin credentials:\n";
    echo "Email: admin@gadgethub.com\n";
    echo "Password: admin123\n";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 