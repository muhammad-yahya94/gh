<?php
include_once 'auth_check.php'; // Include authentication check
// categories.php
require_once '../db.php';
require_once 'sidebar.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['category_name']);
        
        // Handle image upload - START
        $image_path = null; // Default to null
        if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['category_image']['tmp_name'];
            $fileName = $_FILES['category_image']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            // Sanitize file name
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

            // Directory relative to the webroot
            $uploadFileDir = '../uploads/categories/';

            // Ensure upload directory exists (create if not)
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            // Allowed file extensions
            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

            if (in_array($fileExtension, $allowedfileExtensions)) {
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $image_path = $dest_path; // Store path relative to webroot
                } else {
                    // Handle file upload error (optional)
                    // echo "Error moving uploaded file.";
                }
            } else {
                 // Handle invalid file type error (optional)
                 // echo "Invalid file type.";
            }
        }
        // Handle image upload - END
        
        if (!empty($name)) {
            $stmt = $pdo->prepare("INSERT INTO categories (name, image_path) VALUES (?, ?)");
            $stmt->execute([$name, $image_path]);
            header("Location: categories.php");
            exit();
        }
    }
    
    if (isset($_POST['add_subcategory'])) {
        $name = trim($_POST['subcategory_name']);
        $category_id = $_POST['parent_category'];
        
        if (!empty($name) && !empty($category_id)) {
            $stmt = $pdo->prepare("INSERT INTO subcategories (name, category_id) VALUES (?, ?)");
            $stmt->execute([$name, $category_id]);
            header("Location: categories.php");
            exit();
        }
    }
    
    if (isset($_POST['delete_category'])) {
        $id = $_POST['category_id'];
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: categories.php");
        exit();
    }
    
    if (isset($_POST['delete_subcategory'])) {
        $id = $_POST['subcategory_id'];
        $stmt = $pdo->prepare("DELETE FROM subcategories WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: categories.php");
        exit();
    }
}

// Get all categories
$categories = $pdo->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Get all subcategories with their parent category names
$subcategories = $pdo->query("
    SELECT s.*, c.name as parent_name 
    FROM subcategories s 
    JOIN categories c ON s.category_id = c.id
")->fetchAll(PDO::FETCH_ASSOC);

// Filter subcategories if category filter is set
$filtered_subcategories = $subcategories;
if (isset($_GET['filter_category']) && !empty($_GET['filter_category'])) {
    $category_id = $_GET['filter_category'];
    $filtered_subcategories = array_filter($subcategories, function($sub) use ($category_id) {
        return $sub['category_id'] == $category_id;
    });
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Gadget Hub Admin</title>
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
        .card-header { background-color: #f8f9fa; }
        .table thead th { background-color: #f1f3f5; }
        .form-label { font-weight: 500; }
        .btn-primary { background-color: #007bff; border-color: #007bff; }
        .btn-primary:hover { background-color: #0056b3; border-color: #0056b3; }
        .btn-outline-secondary { border-color: #6c757d; color: #6c757d; }
        .btn-outline-secondary:hover { background-color: #6c757d; color: white; }
        .btn-outline-warning { border-color: #ffc107; color: #ffc107; }
        .btn-outline-warning:hover { background-color: #ffc107; color: #212529; }
        .btn-outline-danger { border-color: #dc3545; color: #dc3545; }
        .btn-outline-danger:hover { background-color: #dc3545; color: white; }
        .empty-state { text-align: center; padding: 2rem; }
        .category-icon { width: 20px; height: 20px; margin-right: 8px; vertical-align: middle; }
        .list-group-item { display: flex; justify-content: space-between; align-items: center; }
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
                    <h2 class="mb-0">Manage Categories</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Categories</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Add Category Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Add Category</h6>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col">
                                <label for="categoryName" class="form-label">Category Name</label>
                                <input type="text" class="form-control" name="category_name" id="categoryName" placeholder="Enter category name" required>
                            </div>
                            <div class="col">
                                <label for="categoryImage" class="form-label">Category Image</label>
                                <input type="file" class="form-control" name="category_image" id="categoryImage" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="reset" class="btn btn-outline-secondary me-2">Clear</button>
                            <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Category Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">All Categories</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($categories)): ?>
                        <div class="empty-state">
                            <h5>No Categories Available</h5>
                            <p>Add a new category using the form above.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Image</th>
                                   
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($category['id']) ?></td>
                                            <td>
                                                <?= htmlspecialchars($category['name']) ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($category['image_path'])): ?>
                                                    <img src="<?= htmlspecialchars($category['image_path']) ?>" alt="<?= htmlspecialchars($category['name']) ?>" style="width: 50px; height: auto; margin-right: 8px; vertical-align: middle;">
                                                <?php else: ?>
                                                    No Image
                                                <?php endif; ?>
                                            </td>
                                         
                                            <td>
                                                <a href="edit_category.php?id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-warning me-1">Edit</a>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                                    <button type="submit" name="delete_category" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Add Subcategory Form -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Add Subcategory</h6>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="subcategoryName" class="form-label">Subcategory Name</label>
                                <input type="text" class="form-control" name="subcategory_name" id="subcategoryName" placeholder="Enter subcategory name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="parentCategory" class="form-label">Parent Category</label>
                                <select class="form-select" name="parent_category" id="parentCategory" required>
                                    <option value="">Select Parent Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="reset" class="btn btn-outline-secondary me-2">Clear</button>
                            <button type="submit" name="add_subcategory" class="btn btn-primary">Add Subcategory</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Subcategory List -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Subcategories</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label for="subcategoryFilter" class="form-label">Filter by Category</label>
                                <select class="form-select" name="filter_category" id="subcategoryFilter" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" <?= isset($_GET['filter_category']) && $_GET['filter_category'] == $category['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                    
                    <?php if (empty($filtered_subcategories)): ?>
                        <div class="empty-state">
                            <h5>No Subcategories Available</h5>
                            <p>Select a category or add a new subcategory using the form above.</p>
                        </div>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($filtered_subcategories as $subcategory): ?>
                                <li class="list-group-item">
                                    <span><?= htmlspecialchars($subcategory['name']) ?> (Parent: <?= htmlspecialchars($subcategory['parent_name']) ?></span>
                                    <div>
                                        <a href="edit_subcategory.php?id=<?= $subcategory['id'] ?>" class="btn btn-sm btn-outline-warning me-1">Edit</a>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="subcategory_id" value="<?= $subcategory['id'] ?>">
                                            <button type="submit" name="delete_subcategory" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this subcategory?')">Delete</button>
                                        </form>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar
        document.querySelectorAll('.toggle-sidebar').forEach(button => {
            button.addEventListener('click', () => {
                document.querySelector('.sidebar').classList.toggle('sidebar-collapsed');
                document.querySelector('.main-content').classList.toggle('content-expanded');
            });
        });
    </script>
</body>
</html>