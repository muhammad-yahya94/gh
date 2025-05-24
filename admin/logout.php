<?php
// Check if session is not already started before starting
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the login page (relative path from admin directory)
header('Location: ../login.php');
exit();
?> 