<?php
// Start the session
session_start();

// Check if the user is logged in and is an administrator
// Checking for 'user_id' and 'user_role' in session

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    // User is not logged in or is not an admin, redirect to login page
    
    // Determine the correct path to the login page relative to the admin directory
    // Assuming login.html is in the parent directory (one level up)
    $login_page = '../login.php'; 
    
    // You might want to add a message to the session to inform the user why they were redirected
    // $_SESSION['error_message'] = 'Please log in as an administrator to access this page.';
    
    header('Location: ' . $login_page);
    exit();
}

// If the code reaches here, the user is a logged-in administrator
// Proceed with the rest of the page content
?> 