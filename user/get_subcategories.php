<?php
include_once 'user_auth_check.php'; // Include user authentication check
require_once '../db.php'; // Include database connection

header('Content-Type: application/json'); // Set header to indicate JSON response

$response = ['success' => false, 'message' => '', 'subcategories' => []];

if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $categoryId = $_GET['category_id'];

    try {
        // Fetch subcategories for the given category ID
        $stmt = $pdo->prepare("SELECT id, name FROM subcategories WHERE category_id = ? ORDER BY name");
        $stmt->execute([$categoryId]);
        $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['subcategories'] = $subcategories;

    } catch (PDOException $e) {
        // Log the error
        error_log("Database error fetching subcategories: " . $e->getMessage());
        $response['message'] = 'Database error fetching subcategories.';
    }
} else {
    $response['message'] = 'Invalid category ID.';
}

echo json_encode($response);
?>