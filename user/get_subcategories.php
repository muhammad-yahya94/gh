<?php
include_once 'user_auth_check.php';
require_once '../db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'subcategories' => []];

if (isset($_GET['category_id']) && !empty($_GET['category_id'])) {
    $categoryId = $_GET['category_id'];

    try {
        $stmt = $pdo->prepare("SELECT id, name FROM subcategories WHERE category_id = ? ORDER BY name");
        $stmt->execute([$categoryId]);
        $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Subcategories found for category $categoryId: " . count($subcategories));
        
        $response['success'] = true;
        $response['subcategories'] = $subcategories;
    } catch (PDOException $e) {
        error_log("Database error fetching subcategories: " . $e->getMessage());
        $response['message'] = 'Database error fetching subcategories.';
    }
} else {
    $response['message'] = 'Invalid category ID.';
}

echo json_encode($response);
?>