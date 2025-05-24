<?php
include_once 'user_auth_check.php'; // Include user authentication check
// edit_ad.php
require_once '../db.php';
require_once 'sidebar.php';

if (!isset($_GET['id'])) {
    header("Location: my_ads.php");
    exit();
}

$ad_id = $_GET['id'];
$user_id = 1; // In real app, get from session

// Get ad details
$stmt = $pdo->prepare("SELECT * FROM ads WHERE id = ? AND user_id = ?");
$stmt->execute([$ad_id, $user_id]);
$ad = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ad) {
    header("Location: my_ads.php");
    exit();
}

// Get categories and subcategories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$subcategories = $pdo->prepare("SELECT s.* FROM subcategories s 
                               JOIN categories c ON s.category_id = c.id 
                               WHERE c.name = ?");
$subcategories->execute([$ad['category']]);
$subcategories = $subcategories->fetchAll(PDO::FETCH_ASSOC);

// Get ad images
$images = $pdo->prepare("SELECT * FROM ad_images WHERE ad_id = ?");
$images->execute([$ad_id]);
$images = $images->fetchAll(PDO::FETCH_ASSOC);

// Initialize variables
$errors = [];
$title = $ad['title'];
$category = $ad['category'];
$subcategory = $ad['subcategory'];
$price = $ad['price'];
$description = $ad['description'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize inputs
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $subcategory = $_POST['subcategory'];
    $price = trim($_POST['price']);
    $description = trim($_POST['description']);
    
    // Validation
    if (empty($title)) {
        $errors['title'] = 'Ad title is required';
    }
    
    if (empty($category)) {
        $errors['category'] = 'Category is required';
    }
    
    if (empty($subcategory)) {
        $errors['subcategory'] = 'Subcategory is required';
    }
    
    if (empty($price)) {
        $errors['price'] = 'Price is required';
    } elseif (!is_numeric($price) || $price <= 0) {
        $errors['price'] = 'Please enter a valid price';
    }
    
    if (empty($description)) {
        $errors['description'] = 'Description is required';
    }
    
    // Handle image deletion
    if (isset($_POST['delete_images'])) {
        foreach ($_POST['delete_images'] as $image_id) {
            $stmt = $pdo->prepare("SELECT image_path FROM ad_images WHERE id = ? AND ad_id = ?");
            $stmt->execute([$image_id, $ad_id]);
            $image = $stmt->fetch();
            
            if ($image) {
                // Delete file from server
                if (file_exists($image['image_path'])) {
                    unlink($image['image_path']);
                }
                // Delete record from database
                $stmt = $pdo->prepare("DELETE FROM ad_images WHERE id = ?");
                $stmt->execute([$image_id]);
            }
        }
    }
    
    // Handle new image uploads
    if (!empty($_FILES['images']['name'][0])) {
        $upload_dir = 'uploads/ads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            $file_name = basename($_FILES['images']['name'][$key]);
            $file_path = $upload_dir . uniqid() . '_' . $file_name;
            
            if (move_uploaded_file($tmp_name, $file_path)) {
                $stmt = $pdo->prepare("INSERT INTO ad_images (ad_id, image_path) VALUES (?, ?)");
                $stmt->execute([$ad_id, $file_path]);
            }
        }
    }
    
    // If no errors, update ad
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE ads SET title = ?, category = ?, subcategory = ?, 
                                  price = ?, description = ?, updated_at = NOW() 
                                  WHERE id = ? AND user_id = ?");
            $stmt->execute([$title, $category, $subcategory, $price, $description, $ad_id, $user_id]);
            
            $_SESSION['success_message'] = "Ad updated successfully!";
            header("Location: my_ads.php");
            exit();
        } catch (Exception $e) {
            $errors['general'] = "Error updating ad: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ad - OLX Clone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .image-thumbnail {
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin: 5px;
        }
        .image-container {
            position: relative;
            display: inline-block;
        }
        .delete-checkbox {
            position: absolute;
            top: 5px;
            right: 5px;
        }
        .is-invalid { border-color: #dc3545; }
        .invalid-feedback { color: #dc3545; }
        :root {
            --sidebar-width: 250px;
            --sidebar-bg: #343a40;
            --sidebar-color: #e9ecef;
            --sidebar-active-bg: #007bff;
            --header-height: 56px;
        }
        body { overflow-x: hidden; }
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--sidebar-bg);
            color: var(--sidebar-color);
            transition: all 0.3s;
            z-index: 1000;
        }
        .sidebar-header {
            padding: 1rem;
            background: rgba(0, 0, 0, 0.2);
        }
        .sidebar-menu { padding: 0; list-style: none; }
        .sidebar-menu li { position: relative; }
        .sidebar-menu li a {
            display: block;
            padding: 0.75rem 1rem;
            color: var(--sidebar-color);
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar-menu li a:hover,
        .sidebar-menu li a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }
        .sidebar-menu li a.active { background: var(--sidebar-active-bg); }
        .sidebar-menu li a i { margin-right: 10px; width: 20px; text-align: center; }
        .sidebar-menu .submenu { padding-left: 20px; list-style: none; display: none; }
        .sidebar-menu .submenu.show { display: block; }
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s;
        }
        .header {
            height: var(--header-height);
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar-collapsed { margin-left: -250px; }
        .content-expanded { margin-left: 0; }
        .badge-sm { font-size: 0.65em; padding: 0.25em 0.4em; }
        .form-label { font-weight: 500; }
        .card-header { background-color: #f8f9fa; }
        .btn-primary { background-color: #007bff; border-color: #007bff; }
        .btn-primary:hover { background-color: #0056b3; border-color: #0056b3; }
        .btn-outline-secondary { border-color: #6c757d; color: #6c757d; }
        .btn-outline-secondary:hover { background-color: #6c757d; color: white; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <nav class="header navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-link toggle-sidebar d-none d-lg-block">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="d-flex align-items-center ms-auto">
                    <!-- User menu and notifications -->
                </div>
            </div>
        </nav>

        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-0">Edit Ad</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="my_ads.php">My Ads</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Ad</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errors['general']) ?>
                </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Ad Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="adTitle" class="form-label">Ad Title</label>
                                <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" 
                                    id="adTitle" name="title" placeholder="Enter ad title" 
                                    value="<?= htmlspecialchars($title) ?>" required>
                                <?php if (isset($errors['title'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['title']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="adCategory" class="form-label">Category</label>
                                <select class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>" 
                                    id="adCategory" name="category" required>
                                    <option value="" disabled>Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['name']) ?>" 
                                            <?= $category === $cat['name'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['category'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['category']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="adSubCategory" class="form-label">Sub Category</label>
                                <select class="form-select <?= isset($errors['subcategory']) ? 'is-invalid' : '' ?>" 
                                    id="adSubCategory" name="subcategory" required>
                                    <option value="" disabled>Select Sub Category</option>
                                    <?php foreach ($subcategories as $sub): ?>
                                        <option value="<?= htmlspecialchars($sub['name']) ?>" 
                                            <?= $subcategory === $sub['name'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sub['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['subcategory'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['subcategory']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="adPrice" class="form-label">Price</label>
                                <input type="number" class="form-control <?= isset($errors['price']) ? 'is-invalid' : '' ?>" 
                                    id="adPrice" name="price" placeholder="Enter price" 
                                    value="<?= htmlspecialchars($price) ?>" required>
                                <?php if (isset($errors['price'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['price']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Current Images</label>
                            <div>
                                <?php if (empty($images)): ?>
                                    <p class="text-muted">No images uploaded</p>
                                <?php else: ?>
                                    <?php foreach ($images as $image): ?>
                                        <div class="image-container">
                                            <img src="<?= htmlspecialchars($image['image_path']) ?>" 
                                                class="image-thumbnail" alt="Ad image">
                                            <div class="form-check delete-checkbox">
                                                <input class="form-check-input" type="checkbox" 
                                                    name="delete_images[]" value="<?= $image['id'] ?>" 
                                                    id="deleteImage<?= $image['id'] ?>">
                                                <label class="form-check-label" for="deleteImage<?= $image['id'] ?>">
                                                    Delete
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="adImages" class="form-label">Add More Images (Max 5)</label>
                            <input type="file" class="form-control" id="adImages" name="images[]" 
                                accept="image/*" multiple>
                            <small class="text-muted">Upload up to 5 additional images</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="adDescription" class="form-label">Description</label>
                            <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" 
                                id="adDescription" name="description" rows="5" 
                                placeholder="Enter ad description" required><?= htmlspecialchars($description) ?></textarea>
                            <?php if (isset($errors['description'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['description']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-end mt-4">
                            <a href="my_ads.php" class="btn btn-outline-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Ad</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load subcategories when category changes
            $('#adCategory').change(function() {
                const category = $(this).val();
                if (category) {
                    $.ajax({
                        url: 'get_subcategories.php',
                        type: 'GET',
                        data: { category: category },
                        success: function(response) {
                            $('#adSubCategory').html('<option value="" disabled>Select Sub Category</option>' + response);
                        }
                    });
                } else {
                    $('#adSubCategory').html('<option value="" disabled>Select Sub Category</option>');
                }
            });
            
            // Limit file uploads to 5
            $('#adImages').on('change', function() {
                if (this.files.length > 5) {
                    alert('You can upload a maximum of 5 images');
                    $(this).val('');
                }
            });
        });
    </script>
</body>
</html>