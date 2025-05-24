<?php
include_once 'user_auth_check.php'; // Include user authentication check
// view_ad.php
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

// Get ad images
$images = $pdo->prepare("SELECT * FROM ad_images WHERE ad_id = ?");
$images->execute([$ad_id]);
$images = $images->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Ad - OLX Clone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .ad-image {
            max-width: 100%;
            max-height: 400px;
            object-fit: contain;
        }
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid #dee2e6;
        }
        .thumbnail.active {
            border-color: #0d6efd;
        }
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
                    <h2 class="mb-0">View Ad</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="my_ads.php">My Ads</a></li>
                            <li class="breadcrumb-item active" aria-current="page">View Ad</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Ad Details</h6>
                    <div>
                        <a href="edit_ad.php?id=<?= $ad['id'] ?>" class="btn btn-sm btn-outline-warning me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="my_ads.php" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Back to My Ads
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h3><?= htmlspecialchars($ad['title']) ?></h3>
                            <div class="mb-4">
                                <?php if (!empty($images)): ?>
                                    <div class="text-center mb-3">
                                        <img id="mainImage" src="<?= htmlspecialchars($images[0]['image_path']) ?>" 
                                            class="ad-image img-fluid rounded" alt="Ad image">
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($images as $index => $image): ?>
                                            <img src="<?= htmlspecialchars($image['image_path']) ?>" 
                                                class="thumbnail <?= $index === 0 ? 'active' : '' ?>" 
                                                onclick="changeMainImage(this, '<?= htmlspecialchars($image['image_path']) ?>')"
                                                alt="Ad thumbnail">
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4 bg-light rounded">
                                        <i class="fas fa-image fa-4x text-muted mb-3"></i>
                                        <p class="text-muted">No images available</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-4">
                                <h5>Description</h5>
                                <p><?= nl2br(htmlspecialchars($ad['description'])) ?></p>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Details</h5>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Category:</span>
                                            <span><?= htmlspecialchars($ad['category']) ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Subcategory:</span>
                                            <span><?= htmlspecialchars($ad['subcategory']) ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Price:</span>
                                            <span class="fw-bold">$<?= number_format($ad['price'], 2) ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Status:</span>
                                            <span class="badge 
                                                <?= $ad['status'] === 'active' ? 'bg-success' : 
                                                   ($ad['status'] === 'pending' ? 'bg-warning' : 'bg-secondary') ?>">
                                                <?= ucfirst($ad['status']) ?>
                                            </span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Published:</span>
                                            <span><?= date('M j, Y', strtotime($ad['created_at'])) ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span>Last Updated:</span>
                                            <span><?= date('M j, Y', strtotime($ad['updated_at'])) ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function changeMainImage(thumbnail, imageSrc) {
            // Update main image
            document.getElementById('mainImage').src = imageSrc;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail').forEach(img => {
                img.classList.remove('active');
            });
            thumbnail.classList.add('active');
        }
    </script>
</body>
</html>