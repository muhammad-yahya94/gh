<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once '../db.php';
require_once 'user_auth_check.php';

$title = '';
$description = '';
$price = '';
$location = '';
$category_id = '';
$subcategory_id = '';
$errors = [];
$success = false;

$categories = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors['db'] = 'Error fetching categories: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');
    $subcategory_id = trim($_POST['subcategory_id'] ?? '');
    $user_id = $_SESSION['user_id'] ?? null;

    if (empty($title)) $errors['title'] = 'Title is required.';
    if (empty($description)) $errors['description'] = 'Description is required.';
    if (empty($price) || !is_numeric($price) || $price < 0) $errors['price'] = 'Valid price is required.';
    if (empty($location)) $errors['location'] = 'Location is required.';
    if (empty($category_id)) $errors['category_id'] = 'Category is required.';
    if (empty($subcategory_id)) $errors['subcategory_id'] = 'Subcategory is required.';
    if (empty($user_id)) $errors['user'] = 'User not logged in.';

    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileSize = $_FILES['image']['size'];
        $fileType = $_FILES['image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = '../Uploads/ads/';

        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }

        $dest_path = $uploadFileDir . $newFileName;
        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

        if (in_array($fileExtension, $allowedfileExtensions)) {
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_path = 'Uploads/ads/' . $newFileName; // Store relative path
            } else {
                $errors['image'] = 'There was an error moving the uploaded file.';
            }
        } else {
            $errors['image'] = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
        }
    }

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO ads (user_id, title, description, price, location, category_id, subcategory_id, image_path, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $title, $description, $price, $location, $category_id, $subcategory_id, $image_path]);
            $_SESSION['success_message'] = 'Ad published successfully!';
            header('Location: my_ads.php');
            exit();
        } catch (PDOException $e) {
            $errors['db'] = 'Database error: ' . $e->getMessage();
            echo 'Database error: ' . $e->getMessage(); // Temporary debug
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Publish Ad - OLX Clone</title>
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
        .badge

-sm { font-size: 0.65em; padding: 0.25em 0.4em; }
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
                    <div class="dropdown me-3">
                        <a href="#" class="dropdown-toggle" id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">3</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsDropdown">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            <li><a class="dropdown-item" href="#"><small>New message received</small></a></li>
                            <li><a class="dropdown-item" href="#"><small>Ad approved</small></a></li>
                            <li><a class="dropdown-item" href="#"><small>Ad inquiry</small></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-center" href="#">View all</a></li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="dropdown-toggle d-flex align-items-center" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="https://via.placeholder.com/40" class="rounded-circle me-2" alt="User">
                            <span>User Name</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class=" ABOVE CODE IS TRUNCATED, BUT THIS IS THE FULL FILE BASED ON YOUR INPUT
                            <a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-0">Publish New Ad</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Publish Ad</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($errors as $key => $error): ?>
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ad Details</h6>
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
                                <select class="form-select <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>" 
                                    id="adCategory" name="category_id" required>
                                    <option value="" disabled selected>Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= htmlspecialchars($cat['id']) ?>" 
                                            <?= $category_id === $cat['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (isset($errors['category_id'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['category_id']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="adSubCategory" class="form-label">Subcategory</label>
                                <select class="form-select <?= isset($errors['subcategory_id']) ? 'is-invalid' : '' ?>" 
                                    id="adSubCategory" name="subcategory_id" required>
                                    <option value="" disabled selected>Select Subcategory</option>
                                </select>
                                <?php if (isset($errors['subcategory_id'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['subcategory_id']) ?></div>
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
                            <div class="col-md-6 mb-3">
                                <label for="adLocation" class="form-label">Location</label>
                                <input type="text" class="form-control <?= isset($errors['location']) ? 'is-invalid' : '' ?>" 
                                    id="adLocation" name="location" placeholder="Enter location" 
                                    value="<?= htmlspecialchars($location) ?>" required>
                                <?php if (isset($errors['location'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['location']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="adImages" class="form-label">Images (Max 5)</label>
                                <input type="file" class="form-control <?= isset($errors['image']) ? 'is-invalid' : '' ?>" 
                                    id="adImages" name="image" accept="image/*">
                                <?php if (isset($errors['image'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['image']) ?></div>
                                <?php endif; ?>
                            </div>
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
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="window.history.back()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Publish Ad</button>
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
            $('.toggle-sidebar').click(function() {
                $('.sidebar').toggleClass('sidebar-collapsed');
                $('.main-content').toggleClass('content-expanded');
            });

            $('#adCategory').change(function() {
                const categoryId = $(this).val();
                const subcategoryDropdown = $('#adSubCategory');
                subcategoryDropdown.empty();
                subcategoryDropdown.append($('<option>').val('').text('Select Subcategory').prop('disabled', true).prop('selected', true));

                if (categoryId) {
                    $.ajax({
                        url: 'get_subcategories.php',
                        type: 'GET',
                        data: { category_id: categoryId },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success && response.subcategories.length > 0) {
                                $.each(response.subcategories, function(index, subcategory) {
                                    subcategoryDropdown.append($('<option>').val(subcategory.id).text(subcategory.name));
                                });
                            } else {
                                subcategoryDropdown.append($('<option>').val('').text('No subcategories found').prop('disabled', true));
                            }
                        },
                        error: function() {
                            subcategoryDropdown.append($('<option>').val('').text('Error loading subcategories').prop('disabled', true));
                            console.error('Error fetching subcategories.');
                        }
                    });
                }
            });

            const initialCategoryId = $('#adCategory').val();
            if (initialCategoryId) {
                $('#adCategory').trigger('change');
            }
        });
    </script>
</body>
</html>