<?php
include_once 'auth_check.php'; // Include authentication check
require_once '../db.php'; // Include database connection

// Initialize variables for success/error messages
$success_message = '';
$error_message = '';

// Handle status updates and deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ad_id = $_POST['ad_id'] ?? 0;
    $action = $_POST['action'] ?? '';

    if (!$ad_id || !in_array($action, ['approve', 'reject', 'pending', 'delete'])) {
        $error_message = "Invalid request.";
    } else {
        try {
            if ($action === 'delete') {
                // Delete the ad
                $stmt = $pdo->prepare("DELETE FROM ads WHERE id = ?");
                $stmt->execute([$ad_id]);
                $success_message = "Ad deleted successfully.";
            } else {
                // Update ad status (approve = active, reject = rejected, pending = pending)
                $new_status = $action === 'approve' ? 'active' : ($action === 'reject' ? 'rejected' : 'pending');
                $stmt = $pdo->prepare("UPDATE ads SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $ad_id]);
                $success_message = "Ad status updated to " . ucfirst($new_status) . ".";
            }
        } catch (PDOException $e) {
            error_log("Error processing ad action: " . $e->getMessage());
            $error_message = "Error: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Fetch ad posts from the database
try {
    $stmt = $pdo->query("
        SELECT 
            a.*, 
            u.name AS user_name, 
            c.name AS category_name 
        FROM ads a
        JOIN users u ON a.user_id = u.id
        JOIN categories c ON a.category_id = c.id
        ORDER BY a.created_at DESC
    ");
    $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error fetching ads for admin: " . $e->getMessage());
    $error_message = "Error fetching ads: " . htmlspecialchars($e->getMessage());
    $ads = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Ad Posts - Gadget Hub Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
        .table thead th { background-color: #f1f3f5; }
        .ad-image-thumbnail { width: 50px; height: auto; }
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
                    <!-- User menu would go here -->
                </div>
            </div>
        </nav>

        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-0">Manage Ad Posts</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Ad Posts</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Ad Posts</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($ads)): ?>
                        <div class="alert alert-info">
                            No ad posts found.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>User</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Image</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ads as $ad): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($ad['id']) ?></td>
                                            <td><?= htmlspecialchars($ad['title']) ?></td>
                                            <td><?= htmlspecialchars($ad['user_name']) ?></td>
                                            <td><?= htmlspecialchars($ad['category_name']) ?></td>
                                            <td>$<?= number_format($ad['price'], 2) ?></td>
                                            <td>
                                                <?php if (!empty($ad['image_path'])): ?>
                                                    <?php $image_url = '/gadgethub/' . htmlspecialchars($ad['image_path']) . '?t=' . time(); ?>
                                                    <img src="<?= $image_url ?>" alt="Ad Image" class="ad-image-thumbnail" 
                                                         onload="console.log('Table image loaded: ' + this.src)" 
                                                         onerror="console.log('Table image failed: ' + this.src); this.src='/gadgethub/placeholder.jpg'; this.alt='Image failed to load';">
                                                <?php else: ?>
                                                    No Image
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge 
                                                    <?= $ad['status'] === 'active' ? 'bg-success' :
                                                       ($ad['status'] === 'pending' ? 'bg-warning' :
                                                        ($ad['status'] === 'rejected' ? 'bg-danger' : 'bg-secondary')) ?>">
                                                    <?= ucfirst($ad['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('M j, Y H:i', strtotime($ad['created_at'])) ?></td>
                                            <td>
                                                <!-- Status Update Buttons -->
                                                <?php if ($ad['status'] !== 'active'): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ($ad['status'] !== 'rejected'): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                                    </form>
                                                <?php endif; ?>
                                                <?php if ($ad['status'] !== 'pending'): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                                                        <button type="submit" name="action" value="pending" class="btn btn-sm btn-warning">Pending</button>
                                                    </form>
                                                <?php endif; ?>
                                                <!-- View Button -->
                                                <button type="button" class="btn btn-sm btn-outline-info view-ad-btn" 
                                                        data-bs-toggle="modal" data-bs-target="#adDetailsModal<?= $ad['id'] ?>">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <!-- Delete Button -->
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this ad?')">
                                                    <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                                                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Ad Details Modal for each ad -->
                                        <div class="modal fade" id="adDetailsModal<?= $ad['id'] ?>" tabindex="-1" aria-labelledby="adDetailsModalLabel<?= $ad['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="adDetailsModalLabel<?= $ad['id'] ?>">Ad Details: <?= htmlspecialchars($ad['title']) ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Title:</strong> <?= htmlspecialchars($ad['title']) ?></p>
                                                        <p><strong>User:</strong> <?= htmlspecialchars($ad['user_name']) ?></p>
                                                        <p><strong>Category:</strong> <?= htmlspecialchars($ad['category_name']) ?></p>
                                                        <p><strong>Price:</strong> $<?= number_format($ad['price'], 2) ?></p>
                                                        <p><strong>Location:</strong> <?= htmlspecialchars($ad['location']) ?></p>
                                                        <p><strong>Status:</strong> 
                                                            <span class="badge 
                                                                <?= $ad['status'] === 'active' ? 'bg-success' :
                                                                   ($ad['status'] === 'pending' ? 'bg-warning' :
                                                                    ($ad['status'] === 'rejected' ? 'bg-danger' : 'bg-secondary')) ?>">
                                                                <?= ucfirst($ad['status']) ?>
                                                            </span>
                                                        </p>
                                                        <p><strong>Created At:</strong> <?= date('M j, Y H:i', strtotime($ad['created_at'])) ?></p>
                                                        <p><strong>Image:</strong></p>
                                                        <?php if (!empty($ad['image_path'])): ?>
                                                            <?php 
                                                            $modal_image_url = '/gadgethub/' . htmlspecialchars($ad['image_path']) . '?t=' . time();
                                                            $modal_full_path = '../' . $ad['image_path'];
                                                            ?>
                                                            <p>Image Path: <?= htmlspecialchars($ad['image_path']) ?></p>
                                                            <p>Full Path: <?= htmlspecialchars($modal_full_path) ?></p>
                                                            <p>File Exists: <?= file_exists($modal_full_path) ? 'Yes' : 'No' ?></p>
                                                            <?php if (file_exists($modal_full_path)): ?>
                                                                <img id="modalImage<?= $ad['id'] ?>" src="<?= $modal_image_url ?>" alt="Ad Image" style="max-width: 100%; height: auto;" 
                                                                     onload="console.log('Modal image loaded: ' + this.src)" 
                                                                     onerror="console.log('Modal image failed: ' + this.src); this.src='/gadgethub/placeholder.jpg'; this.alt='Image failed to load';">
                                                            <?php else: ?>
                                                                <p>Image file not found.</p>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <p>No Image</p>
                                                        <?php endif; ?>
                                                        <p><strong>Description:</strong> <?= htmlspecialchars($ad['description']) ?></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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

            <?php foreach ($ads as $ad): ?>
                $('#adDetailsModal<?= $ad['id'] ?>').on('shown.bs.modal', function () {
                    console.log('Modal opened for ad ID <?= $ad['id'] ?>');
                    const img = document.getElementById('modalImage<?= $ad['id'] ?>');
                    if (img) {
                        console.log('Attempting to load modal image: ' + img.src);
                        img.src = img.src; // Force reload
                    }
                });
            <?php endforeach; ?>
        });
    </script>
</body>
</html>