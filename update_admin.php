<?php
require_once 'db.php';

try {
    // Update the admin user's role to 'Admin'
    $stmt = $pdo->prepare("UPDATE users SET role = 'Admin' WHERE email = ?");
    $stmt->execute(['admin@gadgethub.com']);
    
    // Check if the update was successful
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
    $stmt->execute(['admin@gadgethub.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "Admin user updated successfully:<br>";
    echo "ID: " . $user['id'] . "<br>";
    echo "Name: " . $user['name'] . "<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Role: " . $user['role'] . "<br>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 