<?php
session_start();
require_once 'db.php'; // PDO database connection

// Get ad ID from URL
$ad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($ad_id <= 0) {
    die("Invalid ad ID.");
}

try {
    // Fetch ad details with user, category, and subcategory information
    $sql = "SELECT a.id, a.title, a.price, a.description, a.location, a.image_path, a.created_at, 
                   a.status, a.category_id, a.subcategory_id, 
                   c.name AS category_name, s.name AS subcategory_name,
                   u.id AS user_id, u.name AS seller_name, u.profile_image, u.created_at AS user_created_at
            FROM ads a
            JOIN categories c ON a.category_id = c.id
            JOIN subcategories s ON a.subcategory_id = s.id
            JOIN users u ON a.user_id = u.id
            WHERE a.id = ? AND a.status = 'active'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ad_id]);
    $ad = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ad) {
        die("Ad not found or is not active.");
    }

    // Calculate time since ad was posted
    $created_at = new DateTime($ad['created_at']);
    $now = new DateTime();
    $interval = $now->diff($created_at);
    $time_ago = '';
    if ($interval->y > 0) {
        $time_ago = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
    } elseif ($interval->m > 0) {
        $time_ago = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
    } elseif ($interval->d > 0) {
        $time_ago = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
    } elseif ($interval->h > 0) {
        $time_ago = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
    } else {
        $time_ago = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
    }

    // Determine condition
    $condition = (stripos($ad['title'], 'new') !== false || stripos($ad['description'], 'new') !== false) ? 'New' : 'Used';

    // Fetch similar ads (same category, exclude current ad)
    $similar_sql = "SELECT a.id, a.title, a.price, a.location, a.image_path
                   FROM ads a
                   WHERE a.category_id = ? AND a.id != ? AND a.status = 'active'
                   ORDER BY a.created_at DESC
                   LIMIT 4";
    $similar_stmt = $pdo->prepare($similar_sql);
    $similar_stmt->execute([$ad['category_id'], $ad_id]);
    $similar_ads = $similar_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Query error: " . $e->getMessage());
    die("Query failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($ad['title']) ?> - GadgetHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="search-result.css">
    <style>
        /* Custom styles to match screenshot */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .product-container {
            padding: 20px 0;
        }
        .product-gallery {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .product-gallery img {
            width: 100%;
            max-width: 500px;
            max-height: 400px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .product-details {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .product-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .product-price {
            font-size: 1.5rem;
            font-weight: 600;
            color: #28a745;
            margin-bottom: 15px;
        }
        .product-meta {
            display: flex;
            gap: 20px;
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        .action-link {
            display: inline-block;
            margin-bottom: 10px;
            color: #20c997;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s;
        }
        .action-link:hover {
            color: #1ba87e;
            animation: vibrate 0.3s ease-in-out;
        }
        @keyframes vibrate {
            0% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            80% { transform: translate(2px, -2px); }
            100% { transform: translate(0); }
        }
        .safety-notice {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            color: #856404;
            font-size: 0.9rem;
        }
        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin-top: 20px;
            margin-bottom: 15px;
        }
        .product-description p {
            color: #333;
            line-height: 1.6;
        }
        .specs-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .specs-table tr td:first-child {
            font-weight: 500;
            color: #6c757d;
            padding: 5px 0;
            width: 40%;
        }
        .specs-table tr td:last-child {
            color: #333;
            padding: 5px 0;
        }
        .seller-card {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        .seller-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        .seller-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
        }
        .seller-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }
        .seller-meta {
            font-size: 0.9rem;
            color: #6c757d;
            margin: 0;
        }
        .similar-products {
            padding: 20px 0;
        }
        .product-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-card-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .product-card-body {
            padding: 15px;
        }
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .card-text.product-price {
            font-size: 1rem;
            font-weight: 600;
            color: #28a745;
        }
        .card-text.text-muted {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <div class="product-container">
        <div class="container">
            <div class="row">
                <!-- Product Gallery -->
                <div class="col-md-7">
                    <div class="product-gallery">
                        <!-- Display only one image -->
                        <img src="<?= htmlspecialchars($ad['image_path']) ?>" alt="<?= htmlspecialchars($ad['title']) ?>">
                    </div>
                </div>

                <!-- Product Details -->
                <div class="col-md-5">
                    <div class="product-details">
                        <h1 class="product-title"><?= htmlspecialchars($ad['title']) ?></h1>
                        <p class="product-price">PKR <?= number_format($ad['price']) ?></p>

                        <div class="product-meta">
                            <div class="meta-item"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($ad['location']) ?></div>
                            <div class="meta-item"><i class="fas fa-clock"></i> <?= $time_ago ?></div>
                        </div>

                        <a href="mailto:<?= htmlspecialchars($ad['seller_name']) ?>@example.com" class="action-link"><i class="fas fa-envelope me-2"></i>Send Mail</a>

                        <h2 class="section-title">Description</h2>
                        <div class="product-description">
                            <p><?= htmlspecialchars($ad['description']) ?></p>
                        </div>

                        <h2 class="section-title">Specifications</h2>
                        <table class="specs-table">
                            <tr>
                                <td>Condition</td>
                                <td><?= $condition ?></td>
                            </tr>
                            <tr>
                                <td>Category</td>
                                <td><?= htmlspecialchars($ad['category_name']) ?></td>
                            </tr>
                            <tr>
                                <td>Subcategory</td>
                                <td><?= htmlspecialchars($ad['subcategory_name']) ?></td>
                            </tr>
                        </table>

                        <!-- Safety Notice -->
                        <div class="safety-notice">
                            <p><strong>Safety Tips:</strong> To avoid scams, meet the seller in a public place, verify the item in person before payment, and avoid sharing personal or financial information.</p>
                        </div>

                        <!-- Seller Info -->
                        <div class="seller-card">
                            <div class="seller-header">
                                <img src="<?= htmlspecialchars($ad['profile_image']) ?>" alt="Seller Avatar" class="seller-avatar">
                                <div>
                                    <h5 class="seller-name"><?= htmlspecialchars($ad['seller_name']) ?></h5>
                                    <p class="seller-meta">Member since <?= (new DateTime($ad['user_created_at']))->format('M Y') ?></p>
                                </div>
                            </div>
                            <a href="user_profile.php?id=<?= $ad['user_id'] ?>" class="btn btn-outline-secondary w-100"><i class="fas fa-user me-2"></i>View Profile</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Similar Products Section -->
    <section class="similar-products">
        <div class="container">     
            <h2 class="section-title">Similar Ads</h2>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                <?php if (empty($similar_ads)): ?>
                    <div class="col-12">
                        <p class="text-center">No similar ads found.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($similar_ads as $similar_ad): ?>
                        <div class="col">
                            <div class="product-card">
                                <img src="<?= htmlspecialchars($similar_ad['image_path']) ?>" class="card-img-top product-card-img" alt="<?= htmlspecialchars($similar_ad['title']) ?>">
                                <div class="product-card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($similar_ad['title']) ?></h5>
                                    <p class="card-text product-price">PKR <?= number_format($similar_ad['price']) ?></p>
                                    <p class="card-text text-muted"><small><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($similar_ad['location']) ?></small></p>
                                    <a href="ad-details.php?id=<?= $similar_ad['id'] ?>" class="stretched-link"></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include 'partials/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Theme toggle functionality
        const themeToggle = document.getElementById('theme-toggle');
        const body = document.body;

        // Check for saved theme in localStorage
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            body.classList.add(savedTheme);
            if (savedTheme === 'dark-mode') {
                themeToggle.querySelector('i').classList.replace('fa-moon', 'fa-sun');
            } else {
                themeToggle.querySelector('i').classList.replace('fa-sun', 'fa-moon');
            }
        }

        themeToggle.addEventListener('click', () => {
            if (body.classList.contains('dark-mode')) {
                body.classList.remove('dark-mode');
                themeToggle.querySelector('i').classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('theme', 'light-mode');
            } else {
                body.classList.add('dark-mode');
                themeToggle.querySelector('i').classList.replace('fa-moon', 'fa-sun');
                localStorage.setItem('theme', 'dark-mode');
            }
        });
    </script>
</body>
</html>