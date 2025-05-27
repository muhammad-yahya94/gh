<?php
session_start();
require_once 'db.php'; // PDO database connection

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    die("Invalid user ID.");
}

try {
    // Fetch user details
    $sql = "SELECT id, name, profile_image, created_at, email FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }

    // Calculate join time
    $join_date = new DateTime($user['created_at']);
    $now = new DateTime();
    $interval = $now->diff($join_date);
    $join_ago = '';
    if ($interval->y > 0) {
        $join_ago = $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
    } elseif ($interval->m > 0) {
        $join_ago = $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
    } else {
        $join_ago = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
    }

    // Fetch user's active ads with error handling
    $ads_sql = "SELECT id, title, price, location, image_path, created_at 
                FROM ads 
                WHERE user_id = ? AND status = 'active' 
                ORDER BY created_at DESC";
    $ads_stmt = $pdo->prepare($ads_sql);
    $ads_stmt->execute([$user_id]);
    $user_ads = $ads_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Debug: Log the number of ads fetched and dump data
    error_log("Number of ads for user_id $user_id: " . count($user_ads));
    // var_dump($user_ads); // Uncomment to debug data structure

    if (!$ads_stmt->rowCount() && $ads_stmt->errorCode() !== '00000') {
        error_log("Ads query error for user_id $user_id: " . print_r($ads_stmt->errorInfo(), true));
    }

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
    <title><?= htmlspecialchars($user['name']) ?> - GadgetHub Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="search-result.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
        .profile-container {
            padding: 60px 0;
        }
        .profile-card {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 800px;
            margin: 0 auto;
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
        }
        .profile-name {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
        }
        .profile-meta {
            color: #6c757d;
            font-size: 1rem;
            margin-bottom: 10px;
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
        .ads-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 30px 0 20px;
            color: #333;
            text-align: center;
        }
        .product-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            color: #333;
        }
        .card-text.product-price {
            font-size: 1rem;
            font-weight: 600;
            color: #28a745;
        }
        .card-text.text-muted {
            font-size: 0.9rem;
            color: #6c757d;
        }
        /* Footer styling */
        footer {
            background-color: #1a3c3c;
            color: #fff;
            padding: 20px 0;
            margin-top: 40px;
        }
        footer a {
            color: #20c997;
            text-decoration: none;
        }
        footer a:hover {
            color: #1ba87e;
        }
        footer .col {
            padding: 0 15px;
        }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <div class="profile-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12">
                    <div class="profile-card">
                        <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="<?= htmlspecialchars($user['name']) ?> Avatar" class="profile-avatar">
                        <h1 class="profile-name"><?= htmlspecialchars($user['name']) ?></h1>
                        <p class="profile-meta">Joined <?= $join_ago ?></p>
                        <?php if ($user['email']): ?>
                            <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="action-link"><i class="fas fa-envelope me-2"></i>Send Mail</a>
                        <?php endif; ?>
                        <div class="safety-notice">
                            <p><strong>Safety Tips:</strong> To avoid scams, meet the seller in a public place, verify the item in person before payment, and avoid sharing personal or financial information.</p>
                        </div>

                        <h2 class="ads-title">Active Ads</h2>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="adsGrid">
                            <?php if (empty($user_ads)): ?>
                                <div class="col-12">
                                    <p class="text-center">No active ads found.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($user_ads as $ad): ?>
                                    <div class="col">
                                        <div class="product-card">
                                            <img src="<?= htmlspecialchars($ad['image_path']) ?>" class="card-img-top product-card-img" alt="<?= htmlspecialchars($ad['title']) ?>">
                                            <div class="product-card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($ad['title']) ?></h5>
                                                <p class="card-text product-price">PKR <?= number_format($ad['price']) ?></p>
                                                <p class="card-text text-muted"><small><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($ad['location']) ?></small></p>
                                                <a href="ad-details.php?id=<?= $ad['id'] ?>"></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'partials/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Ensure DOM is fully loaded before running scripts
        document.addEventListener('DOMContentLoaded', () => {
            // Theme toggle functionality
            const themeToggle = document.getElementById('theme-toggle');
            const body = document.body;

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
        });
    </script>
</body>
</html>