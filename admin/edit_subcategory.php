<?php
include_once 'auth_check.php'; // Include authentication check
require_once '../db.php';

if (!isset($_GET['id'])) {
    header("Location: categories.php");
    exit();
}

$id = $_GET['id'];
$subcategory = $pdo->prepare("
    SELECT s.*, c.name as parent_name 
    FROM subcategories s 
    JOIN categories c ON s.category_id = c.id 
    WHERE s.id = ?
");
$subcategory->execute([$id]);
$subcategory = $subcategory->fetch(PDO::FETCH_ASSOC);

if (!$subcategory) {
    header("Location: categories.php");
    exit();
}

// Get all categories for dropdown
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['subcategory_name']);
    $category_id = $_POST['parent_category'];
    
    // Validation
    if (empty($name)) {
        $errors['name'] = 'Subcategory name is required';
    } else {
        // Check if subcategory name already exists in the same category (excluding current subcategory)
        $stmt = $pdo->prepare("SELECT id FROM subcategories WHERE name = ? AND category_id = ? AND id != ?");
        $stmt->execute([$name, $category_id, $id]);
        if ($stmt->fetch()) {
            $errors['name'] = 'Subcategory name already exists in this category';
        }
    }

    if (empty($category_id)) {
        $errors['category'] = 'Parent category is required';
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE subcategories SET name = ?, category_id = ? WHERE id = ?");
        $stmt->execute([$name, $category_id, $id]);
        $success = true;
        $_SESSION['success_message'] = "Subcategory updated successfully!";
        header("Location: categories.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subcategory - Gadget Hub Admin</title>
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
        <nav class="header navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <button class="btn btn-link toggle-sidebar d-none d-lg-block">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-0">Edit Subcategory</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="categories.php">Categories</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Subcategory</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="table-card">
                        <div class="card-header py-3">
                            <h5 class="mb-0">Subcategory Details</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>Please fix the following errors:</strong>
                                    <ul class="mb-0">
                                        <?php foreach ($errors as $error): ?>
                                            <li><?= htmlspecialchars($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <form action="" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Subcategory Name</label>
                                    <input type="text" 
                                           class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                                           name="subcategory_name" 
                                           value="<?= htmlspecialchars($subcategory['name']) ?>" 
                                           required>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['name'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Parent Category</label>
                                    <select class="form-select <?= isset($errors['category']) ? 'is-invalid' : '' ?>" 
                                            name="parent_category" 
                                            required>
                                        <option value="">Select Parent Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?= $category['id'] ?>" 
                                                <?= $category['id'] == $subcategory['category_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php if (isset($errors['category'])): ?>
                                        <div class="invalid-feedback"><?= $errors['category'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                    <a href="categories.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="table-card">
                        <div class="card-header py-3">
                            <h5 class="mb-0">Quick Tips</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Important Notes</h6>
                                <ul class="mb-0">
                                    <li>Subcategory name must be unique within its parent category</li>
                                    <li>Parent category is required</li>
                                    <li>Changes will affect all ads in this subcategory</li>
                                </ul>
                            </div>
                            <div class="alert alert-warning">
                                <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Current Status</h6>
                                <p class="mb-0">
                                    Parent Category: <strong><?= htmlspecialchars($subcategory['parent_name']) ?></strong>
                                </p>
                            </div>
                        </div>
                    </div>
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
    </script>
</body>
</html>