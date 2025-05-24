<?php
session_start();
require_once '../db.php';
include_once 'user_auth_check.php';

$user_id = $_SESSION['user_id'] ?? null;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user_id) {
    $ad_id = $_POST['id'] ?? 0;

    if (empty($ad_id)) {
        $errors['id'] = 'Invalid ad ID.';
    } else {
        try {
            // Verify the ad belongs to the user
            $stmt = $pdo->prepare("SELECT image_path FROM ads WHERE id = ? AND user_id = ?");
            $stmt->execute([$ad_id, $user_id]);
            $ad = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($ad) {
                // Delete the image file if it exists
                if (!empty($ad['image_path']) && file_exists('../' . $ad['image_path'])) {
                    unlink('../' . $ad['image_path']);
                }

                // Delete the ad
                $stmt = $pdo->prepare("DELETE FROM ads WHERE id = ? AND user_id = ?");
                $stmt->execute([$ad_id, $user_id]);

                $_SESSION['success_message'] = 'Ad deleted successfully!';
            } else {
                $errors['id'] = 'Ad not found or you do not have permission to delete it.';
            }
        } catch (PDOException $e) {
            $errors['db'] = 'Database error: ' . $e->getMessage();
        }
    }
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
}

header('Location: my_ads.php');
exit();
?>