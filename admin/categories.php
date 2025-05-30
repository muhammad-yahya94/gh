<?php
include_once 'auth_check.php'; // Include authentication check
require_once '../db.php';
require_once 'sidebar.php';

// Initialize error message variable
$error_message = '';
$success_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = trim($_POST['category_name']);
        
        // Handle image upload
        $image_path = null;
        if (isset($_FILES['category_image']) && $_FILES['category_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['category_image']['tmp_name'];
            $fileName = $_FILES['category_image']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = '../uploads/categories/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $dest_path = $uploadFileDir . $newFileName;
            $allowedfileExtensions = ['jpg', 'gif', 'png', 'jpeg'];

            if (in_array($fileExtension, $allowedfileExtensions)) {
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $image_path = $dest_path;
                } else {
                    $error_message = "Error moving uploaded file.";
                }
            } else {
                $error_message = "Invalid file type. Allowed types: " . implode(', ', $allowedfileExtensions);
            }
        }
        
        if (empty($error_message) && !empty($name)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO categories (name, image_path) VALUES (?, ?)");
                $stmt->execute([$name, $image_path]);
                $success_message = "Category added successfully!";
                header("Location: categories.php");
                exit();
            } catch (PDOException $e) {
                $error_message = "Error adding category: " . $e->getMessage();
            }
        }
    }
    
    if (isset($_POST['add_subcategory'])) {
        $name = trim($_POST['subcategory_name']);
        $category_id = $_POST['parent_category'];
        
        if (!empty($name) && !empty($category_id)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO subcategories (name, category_id) VALUES (?, ?)");
                $stmt->execute([$name, $category_id]);
                $success_message = "Subcategory added successfully!";
                header("Location: categories.php");
                exit();
            } catch (PDOException $e) {
                $error_message = "Error adding subcategory: " . $e->getMessage();
            }
        } else {
            $error_message = "Subcategory name and parent category are required.";
        }
    }
    
    if (isset($_POST['delete_category'])) {
        $id = $_POST['category_id'];
        
        // Check for dependent subcategories
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM subcategories WHERE category_id = ?");
        $stmt->execute([$id]);
        $subcategory_count = $stmt->fetchColumn();

        if ($subcategory_count > 0) {
            $error_message = "Cannot delete category because it has $subcategory_count associated subcategories. Delete or reassign the subcategories first.";
        } else {
            try {
                $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                $stmt->execute([$id]);
                $success_message = "Category deleted successfully!";
                header("Location: categories.php");
                exit();
            } catch (PDOException $e) {
                $error_message = "Error deleting category: " . $e->getMessage();
            }
        }
    }
    
    if (isset($_POST['delete_subcategory'])) {
        $id = $_POST['subcategory_id'];
        try {
            $stmt = $pdo->prepare("DELETE FROM subcategories WHERE id = ?");
            $stmt->execute([$id]);
            $success_message = "Subcategory deleted successfully!";
            header("Location: categories.php");
            exit();
        } catch (PDOException $e) {
            $error_message = "Error deleting subcategory: " . $e->getMessage();
        }
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/admin-style.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
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

        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-0">Categories</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Categories</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Display Error or Success Messages -->
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($error_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($success_message) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Categories Section -->
            <div class="row">
                <div class="col-lg-8">
                    <div class="table-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">All Categories</h5>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="fas fa-plus me-2"></i>Add Category
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Image</th>
                                        <th>Subcategories</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-folder text-primary me-2"></i>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($category['image_path']): ?>
                                                <img src="<?php echo htmlspecialchars($category['image_path']); ?>" alt="Category Image" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $subcategory_count = count(array_filter($subcategories, function($sub) use ($category) {
                                                return $sub['category_id'] == $category['id'];
                                            }));
                                            ?>
                                            <span class="badge bg-info"><?php echo $subcategory_count; ?> subcategories</span>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="edit_category.php?id=<?php echo $category['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal<?php echo $category['id']; ?>">
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

                <div class="col-lg-4">
                    <div class="table-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSubcategoryModal">
                                <i class="fas fa-plus me-2"></i>Add Subcategory
                            </button>
                        </div>
                        <div class="list-group list-group-flush">
                            <?php foreach ($filtered_subcategories as $subcategory): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-tag text-primary me-2"></i>
                                    <?php echo htmlspecialchars($subcategory['name']); ?>
                                    <small class="text-muted d-block"><?php echo htmlspecialchars($subcategory['parent_name']); ?></small>
                                </div>
                                <div class="btn-group">
                                    <a href="edit_subcategory.php?id=<?php echo $subcategory['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteSubcategoryModal<?php echo $subcategory['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="category_name" class="form-label">Category Name</label>
                            <input type="text" class="form-control" id="category_name" name="category_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="category_image" class="form-label">Category Image</label>
                            <input type="file" class="form-control" id="category_image" name="category_image" accept="image/*">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Subcategory Modal -->
    <div class="modal fade" id="addSubcategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Subcategory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="subcategory_name" class="form-label">Subcategory Name</label>
                            <input type="text" class="form-control" id="subcategory_name" name="subcategory_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="parent_category" class="form-label">Parent Category</label>
                            <select class="form-select" id="parent_category" name="parent_category" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_subcategory" class="btn btn-primary">Add Subcategory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Category Modals -->
    <?php foreach ($categories as $category): ?>
    <div class="modal fade" id="deleteCategoryModal<?php echo $category['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the category "<?php echo htmlspecialchars($category['name']); ?>"?</p>
                    <p class="text-danger">This action cannot be undone. Ensure there are no associated subcategories.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="" method="POST" class="d-inline">
                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                        <button type="submit" name="delete_category" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Delete Subcategory Modals -->
    <?php foreach ($subcategories as $subcategory): ?>
    <div class="modal fade" id="deleteSubcategoryModal<?php echo $subcategory['id']; ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Subcategory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the subcategory "<?php echo htmlspecialchars($subcategory['name']); ?>"?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="" method="POST" class="d-inline">
                        <input type="hidden" name="subcategory_id" value="<?php echo $subcategory['id']; ?>">
                        <button type="submit" name="delete_subcategory" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelector('.toggle-sidebar').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('show');
            document.querySelector('.main-content').classList.toggle('sidebar-collapsed');
        });
    </script>
</body>
</html>