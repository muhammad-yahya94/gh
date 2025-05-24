<?php
include_once 'auth_check.php'; // Include authentication check
require_once '../db.php'; // Include database connection

header('Content-Type: application/json'); // Set header to indicate JSON response

$response = ['success' => false, 'message' => '', 'ad' => null];

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $adId = $_GET['id'];

    try {
        // Fetch ad details along with user and category names
        $stmt = $pdo->prepare("SELECT 
                                a.*, 
                                u.name as user_name, 
                                c.name as category_name 
                             FROM ads a
                             JOIN users u ON a.user_id = u.id
                             JOIN categories c ON a.category_id = c.id
                             WHERE a.id = ?");
        $stmt->execute([$adId]);
        $ad = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ad) {
            $response['success'] = true;
            $response['ad'] = $ad;
        } else {
            $response['message'] = 'Ad not found.';
        }

    } catch (PDOException $e) {
        // Log the error
        error_log("Database error fetching ad details: " . $e->getMessage());
        $response['message'] = 'Database error fetching ad details.';
    }
} else {
    $response['message'] = 'Invalid ad ID.';
}

echo json_encode($response);
?> 