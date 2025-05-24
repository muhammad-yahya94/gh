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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/admin-style.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <!-- Header -->
        <nav class="header navbar navbar-expand-lg navbar-light">
            <div class="container-fluid">
                <button class="btn btn-link toggle-sidebar d-none d-lg-block">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="d-flex align-items-center ms-auto">
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle d-flex align-items-center" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://ui-avatars.com/api/?name=Admin+User&background=4361ee&color=fff" class="rounded-circle me-2" alt="User">
                            <span>Admin User</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-0">Ad Posts</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Ad Posts</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($success_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card bg-primary text-white">
                        <div class="stat-card-info">
                            <h6 class="stat-card-title">Total Ads</h6>
                            <h3 class="stat-card-number"><?= count($ads) ?></h3>
                        </div>
                        <div class="stat-card-icon">
                            <i class="fas fa-ad"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-success text-white">
                        <div class="stat-card-info">
                            <h6 class="stat-card-title">Active Ads</h6>
                            <h3 class="stat-card-number">
                                <?= count(array_filter($ads, function($ad) { return $ad['status'] === 'active'; })) ?>
                            </h3>
                        </div>
                        <div class="stat-card-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-warning text-white">
                        <div class="stat-card-info">
                            <h6 class="stat-card-title">Pending Ads</h6>
                            <h3 class="stat-card-number">
                                <?= count(array_filter($ads, function($ad) { return $ad['status'] === 'pending'; })) ?>
                            </h3>
                        </div>
                        <div class="stat-card-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-danger text-white">
                        <div class="stat-card-info">
                            <h6 class="stat-card-title">Rejected Ads</h6>
                            <h3 class="stat-card-number">
                                <?= count(array_filter($ads, function($ad) { return $ad['status'] === 'rejected'; })) ?>
                            </h3>
                        </div>
                        <div class="stat-card-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ads Table -->
            <div class="table-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">All Ad Posts</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#filterModal">
                            <i class="fas fa-filter me-2"></i>Filter
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                            <i class="fas fa-download me-2"></i>Export
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>User</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Image</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ads as $ad): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-ad text-primary me-2"></i>
                                        <?= htmlspecialchars($ad['title']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name=<?= urlencode($ad['user_name']) ?>&background=4361ee&color=fff" 
                                             class="rounded-circle me-2" alt="User" style="width: 32px; height: 32px;">
                                        <?= htmlspecialchars($ad['user_name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= htmlspecialchars($ad['category_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold">$<?= number_format($ad['price'], 2) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($ad['image_path'])): ?>
                                        <img src="<?= htmlspecialchars($ad['image_path']) ?>" 
                                             alt="Ad Image" 
                                             class="img-thumbnail" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status_class = [
                                        'active' => 'success',
                                        'pending' => 'warning',
                                        'rejected' => 'danger'
                                    ][$ad['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $status_class ?>">
                                        <?= ucfirst($ad['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M d, Y', strtotime($ad['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#viewAdModal<?= $ad['id'] ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#approveAdModal<?= $ad['id'] ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAdModal<?= $ad['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- View Ad Modal -->
    <?php foreach ($ads as $ad): ?>
    <div class="modal fade" id="viewAdModal<?= $ad['id'] ?>" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View Ad Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?php if (!empty($ad['image_path'])): ?>
                                <img src="<?= htmlspecialchars($ad['image_path']) ?>" 
                                     alt="Ad Image" 
                                     class="img-fluid rounded mb-3">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h4><?= htmlspecialchars($ad['title']) ?></h4>
                            <p class="text-muted">Posted by <?= htmlspecialchars($ad['user_name']) ?></p>
                            <p class="fw-bold">$<?= number_format($ad['price'], 2) ?></p>
                            <p><?= nl2br(htmlspecialchars($ad['description'])) ?></p>
                            <div class="mt-3">
                                <span class="badge bg-<?= $status_class ?>">
                                    <?= ucfirst($ad['status']) ?>
                                </span>
                                <small class="text-muted ms-2">
                                    Posted on <?= date('M d, Y', strtotime($ad['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Approve Ad Modal -->
    <?php foreach ($ads as $ad): ?>
    <div class="modal fade" id="approveAdModal<?= $ad['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Ad Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Select the new status for this ad:</p>
                    <form action="" method="POST" class="d-inline">
                        <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                        <div class="d-grid gap-2">
                            <button type="submit" name="action" value="approve" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>Approve
                            </button>
                            <button type="submit" name="action" value="reject" class="btn btn-danger">
                                <i class="fas fa-times me-2"></i>Reject
                            </button>
                            <button type="submit" name="action" value="pending" class="btn btn-warning">
                                <i class="fas fa-clock me-2"></i>Set Pending
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Delete Ad Modal -->
    <?php foreach ($ads as $ad): ?>
    <div class="modal fade" id="deleteAdModal<?= $ad['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Ad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the ad "<?= htmlspecialchars($ad['title']) ?>"?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="" method="POST" class="d-inline">
                        <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                        <button type="submit" name="action" value="delete" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Filter Ads</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="filterForm">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="active" id="statusActive" checked>
                                <label class="form-check-label" for="statusActive">Active</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="pending" id="statusPending" checked>
                                <label class="form-check-label" for="statusPending">Pending</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="rejected" id="statusRejected" checked>
                                <label class="form-check-label" for="statusRejected">Rejected</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" id="categoryFilter">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date Range</label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="dateFrom">
                                <span class="input-group-text">to</span>
                                <input type="date" class="form-control" id="dateTo">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="applyFilters()">Apply Filters</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div class="modal fade" id="exportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Export Ads</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Export Format</label>
                        <select class="form-select" id="exportFormat">
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date Range</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="exportDateFrom">
                            <span class="input-group-text">to</span>
                            <input type="date" class="form-control" id="exportDateTo">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="exportAds()">Export</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Toggle Sidebar
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.querySelector('.main-content').classList.toggle('sidebar-collapsed');
        });

        // Filter functionality
        function applyFilters() {
            // Implement filter logic here
            $('#filterModal').modal('hide');
        }

        // Export functionality
        function exportAds() {
            // Implement export logic here
            $('#exportModal').modal('hide');
        }
    </script>
</body>
</html>