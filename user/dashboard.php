<?php
include_once 'user_auth_check.php';
require_once '../db.php';

$user_id = $_SESSION['user_id'] ?? null;

$stats = ['active' => 0, 'pending' => 0, 'rejected' => 0];
$user = null;
$ads = [];
$error_message = '';

if ($user_id) {
    try {
        // Fetch user details
        $stmt = $pdo->prepare("SELECT name, email, profile_image FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error_message = "User not found.";
        } else {
            // Fetch ad stats
            $stmt = $pdo->prepare("
                SELECT status, COUNT(*) as count
                FROM ads
                WHERE user_id = ?
                GROUP BY status
            ");
            $stmt->execute([$user_id]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($results as $row) {
                $stats[$row['status']] = $row['count'];
            }

            // Fetch recent ads
            $stmt = $pdo->prepare("
                SELECT ads.*, categories.name AS category_name
                FROM ads
                LEFT JOIN categories ON ads.category_id = categories.id
                WHERE ads.user_id = ?
                ORDER BY ads.created_at DESC
                LIMIT 5
            ");
            $stmt->execute([$user_id]);
            $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $error_message = "Error fetching data: " . htmlspecialchars($e->getMessage());
    }
} else {
    $error_message = "User not authenticated. Please log in.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - GadgetHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../admin/assets/css/admin-style.css">
    <!-- <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .header {
            background-color: #fff;
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .stat-card {
            border-radius: 10px;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .action-link {
            display: inline-block;
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
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .thumbnail-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style> -->
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <nav class="header navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-link toggle-sidebar d-none d-lg-block">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="d-flex align-items-center ms-auto">
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle d-flex align-items-center" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name=Admin+User&background=4361ee&color=fff" class="rounded-circle me-2" alt="User">
                            <span><?= htmlspecialchars($user['name'] ?? 'User Name') ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item action-link" href="settings.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item action-link" href="mailto:<?= htmlspecialchars($user['email'] ?? 'user@example.com') ?>"><i class="fas fa-envelope me-2"></i> Send Mail</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid py-4">
            <?php if ($error_message): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-0">Welcome, <?= htmlspecialchars($user['name'] ?? 'User') ?>!</h2>
                </div>
            </div>

            <!-- Stats and Quick Actions -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card stat-card shadow">
                        <div class="card-body">
                            <h5 class="card-title">My Ads</h5>
                            <p class="card-text">You have <strong><?= $stats['active'] ?></strong> active ads.</p>
                            <!-- <a href="my_ads.php" class="btn btn-outline-primary">View Ads</a> -->
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card stat-card shadow">
                        <div class="card-body">
                            <h5 class="card-title">Pending Ads</h5>
                            <p class="card-text">You have <strong><?= $stats['pending'] ?></strong> pending ads.</p>
                            <!-- <a href="my_ads.php?status=pending" class="btn btn-outline-primary">View Pending</a> -->
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card stat-card shadow">
                        <div class="card-body">
                            <h5 class="card-title">Rejected Ads</h5>
                            <p class="card-text">You have <strong><?= $stats['rejected'] ?></strong> rejected ads.</p>
                            <!-- <a href="my_ads.php?status=rejected" class="btn btn-outline-primary">View Rejected</a> -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-3">
                                <a href="publish_ad.php" class="btn btn-primary"><i class="fas fa-plus-circle me-2"></i> Publish New Ad</a>
                                <!-- <a href="categories.php" class="btn btn-outline-primary"><i class="fas fa-tags me-2"></i> Browse Categories</a> -->
                                <a href="settings.php" class="btn btn-outline-primary"><i class="fas fa-user me-2"></i> Update Profile</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Ads -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Ads</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive" id="recentAdsContainer">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Image</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <!-- <th>Actions</th> -->
                                        </tr>
                                    </thead>
                                    <tbody id="recentAdsTableBody">
                                        <?php if (empty($ads)): ?>
                                            <tr>
                                                <td colspan="5">
                                                    <div class="empty-state">
                                                        <h5>No Recent Ads</h5>
                                                        <p>You haven't posted any ads yet. Start by publishing a new ad!</p>
                                                        <a href="publish_ad.php" class="btn btn-primary">Publish Ad</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($ads as $ad): ?>
                                                <tr>
                                                    <td><img src="<?= htmlspecialchars($ad['image_path']) ?>" style="width:150px" class="thumbnail-img" alt="<?= htmlspecialchars($ad['title']) ?>"></td>
                                                    <td><?= htmlspecialchars($ad['title']) ?></td>
                                                    <td><?= htmlspecialchars($ad['category_name']) ?></td>
                                                    <td>PKR <?= number_format($ad['price']) ?></td>
                                                    <td>
                                                        <span class="badge <?= $ad['status'] === 'active' ? 'bg-success' : ($ad['status'] === 'pending' ? 'bg-warning' : 'bg-danger') ?>">
                                                            <?= ucfirst($ad['status']) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <!-- <a href="ad-details.php?id=<?= $ad['id'] ?>" class="btn btn-sm btn-outline-primary">View</a> -->
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Notifications -->
            <!-- <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Notifications</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group" id="notificationsList">
                                <li class="list-group-item">
                                    <span>New message received for iPhone 13 Pro Max</span>
                                    <small class="text-muted">2025-05-27</small>
                                </li>
                                <li class="list-group-item">
                                    <span>Your ad 'MacBook Pro 2023 M2' is pending approval</span>
                                    <small class="text-muted">2025-05-26</small>
                                </li>
                                <li class="list-group-item">
                                    <span>New inquiry for Samsung Galaxy S22 Ultra</span>
                                    <small class="text-muted">2025-05-25</small>
                                </li>
                            </ul>
                            <div class="empty-state d-none" id="notificationsEmptyState">
                                <h5>No Notifications</h5>
                                <p>You have no recent notifications.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.toggle-sidebar').click(function() {
                $('.sidebar').toggleClass('sidebar-collapsed');
                $('.main-content').toggleClass('content-expanded');
            });
            $('.sidebar-menu li a').click(function() {
                $('.sidebar-menu li a').removeClass('active');
                $(this).addClass('active');
            });
        });
    </script>
</body>
</html>