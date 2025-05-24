<?php
// Check if session is not already started before starting
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in AND is NOT an Admin
// Assuming 'user_id' and 'user_role' are set in the session upon successful login

// Determine the correct path to the login page relative to the user directory
$login_page = '../login.php'; 

// Check if user is NOT logged in OR if they ARE logged in but ARE an Admin
if (!isset($_SESSION['user_id']) || (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin')) {
    // User is not logged in OR is an Admin, redirect.
    
    // You might want to add a message to the session to inform the user why they were redirected
    // $_SESSION['error_message'] = 'Please log in to access this page.';
    
    // If they are an Admin, perhaps redirect to the admin dashboard instead of login?
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin') {
         header('Location: ../admin/dashboard.php'); // Redirect Admins to admin dashboard
    } else {
         header('Location: ' . $login_page); // Redirect non-logged-in to login
    }

    exit();
}

// If the code reaches here, the user is logged in and is NOT an Admin.
// Proceed with the rest of the page content
?> 