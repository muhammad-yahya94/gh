<?php
session_start();
include_once 'user_auth_check.php';
require_once '../db.php';

$user_id = $_SESSION['user_id'] ?? null;
$user = null;

if ($user_id) {
    try {
        // Fetch user details
        $stmt = $pdo->prepare("SELECT name, email, profile_image FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching user: " . $e->getMessage());
    }
}

$ads = [];
if ($user_id) {
    try {
        $stmt = $pdo->prepare("
            SELECT ads.*, categories.name AS category_name, subcategories.name AS subcategory_name
            FROM ads
            LEFT JOIN categories ON ads.category_id = categories.id
            LEFT JOIN subcategories ON ads.subcategory_id = subcategories.id
            WHERE ads.user_id = ?
            ORDER BY ads.created_at DESC
        ");
        $stmt->execute([$user_id]);
        $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error fetching user ads: " . $e->getMessage());
        $error_message = "Error fetching ads: " . htmlspecialchars($e->getMessage());
    }
} else {
    $error_message = "User not authenticated. Please log in.";
}

// Handle modal form submission for editing ads
$edit_errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_ad'])) {
    $ad_id = $_POST['ad_id'] ?? 0;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');
    $subcategory_id = trim($_POST['subcategory_id'] ?? '');
    $existing_image_path = $_POST['existing_image_path'] ?? '';

    if (empty($title)) $edit_errors['title'] = 'Title is required.';
    if (empty($description)) $edit_errors['description'] = 'Description is required.';
    if (empty($price) || !is_numeric($price) || $price < 0) $edit_errors['price'] = 'Valid price is required.';
    if (empty($location)) $edit_errors['location'] = 'Location is required.';
    if (empty($category_id)) $edit_errors['category_id'] = 'Category is required.';
    if (empty($subcategory_id)) $edit_errors['subcategory_id'] = 'Subcategory is required.';

    $image_path = $existing_image_path;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
        $uploadFileDir = './Uploads/ads/';

        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }

        $dest_path = $uploadFileDir . $newFileName;
        $allowedfileExtensions = ['jpg', 'gif', 'png', 'jpeg'];

        if (in_array($fileExtension, $allowedfileExtensions)) {
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_path = 'Uploads/ads/' . $newFileName;
                if (!empty($existing_image_path) && file_exists('./' . $existing_image_path) && !preg_match('#^https?://#', $existing_image_path)) {
                    unlink('./' . $existing_image_path);
                }
            } else {
                $edit_errors['image'] = 'Error moving the uploaded file.';
            }
        } else {
            $edit_errors['image'] = 'Allowed file types: ' . implode(', ', $allowedfileExtensions);
        }
    }

    if (empty($edit_errors)) {
        try {
            $stmt = $pdo->prepare("
                UPDATE ads 
                SET title = ?, description = ?, price = ?, location = ?, category_id = ?, subcategory_id = ?, image_path = ?
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$title, $description, $price, $location, $category_id, $subcategory_id, $image_path, $ad_id, $user_id]);
            $success_message = 'Ad updated successfully!';
            // Refresh ads after update
            $stmt = $pdo->prepare("
                SELECT ads.*, categories.name AS category_name, subcategories.name AS subcategory_name
                FROM ads
                LEFT JOIN categories ON ads.category_id = categories.id
                LEFT JOIN subcategories ON ads.subcategory_id = subcategories.id
                WHERE ads.user_id = ?
                ORDER BY ads.created_at DESC
            ");
            $stmt->execute([$user_id]);
            $ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $edit_errors['db'] = 'Database error updating ad: ' . $e->getMessage();
        }
    }
}

// Fetch categories for edit modal dropdown
$categories = [];
try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $edit_errors['db'] = 'Error fetching categories: ' . $e->getMessage();
}

$success_message = $success_message ?? ($_SESSION['success_message'] ?? '');
if (isset($_SESSION['success_message'])) {
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Ads - GadgetHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../admin/assets/css/admin-style.css">
    <style>
        .thumbnail-img, .image-preview {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
        .modal-body img {
            max-width: 300px;
            height: auto;
            border-radius: 5px;
        }
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
                    <!-- <div class="dropdown me-3">
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
                    </div> -->
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

        <div class="container-fluid py-4">
            <div class="row mb-2">
                <div class="col-12">
                    <h2 class="mb-0">My Ads</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">My Ads</li>
                        </ol>
                    </nav>
                </div>
            </div>



            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($edit_errors)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($edit_errors as $error): ?>   
                        <p><?= htmlspecialchars($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">My Advertisements</h6>
                    <a href="publish_ad.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> New Ad
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($ads)): ?>
                        <div class="alert alert-info">
                            You haven't published any ads yet. <a href="publish_ad.php" class="alert-link">Create your first ad</a>.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ads as $ad): ?>
                                        <?php
                                        $image_path = $ad['image_path'] ?? '';
                                        $is_url = preg_match('#^https?://#', $image_path);
                                        $thumbnail_url = $image_path ? ($is_url ? $image_path : '/gadgethub/' . $image_path . '?t=' . time()) : '/gadgethub/placeholder.jpg';
                                        ?>
                                        <tr>
                                            <td>
                                                <img src="<?= htmlspecialchars($thumbnail_url) ?>" class="thumbnail-img" alt="<?= htmlspecialchars($ad['title'] ?? 'Ad Image') ?>" onerror="this.src='/gadgethub/placeholder.jpg';">
                                            </td>
                                            <td><?= htmlspecialchars($ad['title'] ?? 'N/A') ?></td>
                                            <td><?= htmlspecialchars($ad['category_name'] ?? 'N/A') ?> / <?= htmlspecialchars($ad['subcategory_name'] ?? 'N/A') ?></td>
                                            <td>PKR <?= number_format($ad['price'] ?? 0, 2) ?></td>
                                            <td>
                                                <span class="badge 
                                                    <?= ($ad['status'] ?? '') === 'active' ? 'bg-success' : 
                                                       (($ad['status'] ?? '') === 'pending' ? 'bg-warning' : 'bg-secondary') ?>">
                                                    <?= ucfirst($ad['status'] ?? 'N/A') ?>
                                                </span>
                                            </td>
                                            <td><?= date('M j, Y', strtotime($ad['created_at'] ?? 'now')) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#editAdModal<?= $ad['id'] ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewAdModal<?= $ad['id'] ?>">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                                <form method="POST" action="delete_ad.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this ad?')">
                                                    <input type="hidden" name="id" value="<?= $ad['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- View Modal -->
                                        <div class="modal fade" id="viewAdModal<?= $ad['id'] ?>" tabindex="-1" aria-labelledby="viewAdModalLabel<?= $ad['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="viewAdModalLabel<?= $ad['id'] ?>">
                                                            <?= htmlspecialchars($ad['title'] ?? 'Ad Title Missing') ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p><strong>Category:</strong> <?= htmlspecialchars($ad['category_name'] ?? 'N/A') ?> / <?= htmlspecialchars($ad['subcategory_name'] ?? 'N/A') ?></p>
                                                        <p><strong>Price:</strong> PKR <?= number_format($ad['price'] ?? 0, 2) ?></p>
                                                        <p><strong>Location:</strong> <?= htmlspecialchars($ad['location'] ?? 'N/A') ?></p>
                                                        <p><strong>Description:</strong> <?= htmlspecialchars($ad['description'] ?? 'N/A') ?></p>
                                                        <p><strong>Status:</strong> <?= ucfirst($ad['status'] ?? 'N/A') ?></p>
                                                        <p><strong>Created:</strong> <?= date('M j, Y', strtotime($ad['created_at'] ?? 'now')) ?></p>
                                                        <p><strong>Image:</strong></p>
                                                        <?php
                                                        $display_image = $image_path ? ($is_url ? $image_path : '/gadgethub/' . $image_path . '?t=' . time()) : '/gadgethub/placeholder.jpg';
                                                        ?>
                                                        <img id="adImageAbs<?= $ad['id'] ?>" src="<?= htmlspecialchars($display_image) ?>" alt="<?= htmlspecialchars($ad['title'] ?? 'Ad Image') ?>" class="img-fluid" onerror="this.src='/gadgethub/placeholder.jpg'; this.alt='Image failed to load';">
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Edit Modal -->
                                        <div class="modal fade" id="editAdModal<?= $ad['id'] ?>" tabindex="-1" aria-labelledby="editAdModalLabel<?= $ad['id'] ?>" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editAdModalLabel<?= $ad['id'] ?>">Edit Ad: <?= htmlspecialchars($ad['title'] ?? 'N/A') ?></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST" enctype="multipart/form-data">
                                                            <input type="hidden" name="edit_ad" value="1">
                                                            <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                                                            <input type="hidden" name="existing_image_path" value="<?= htmlspecialchars($ad['image_path'] ?? '') ?>">
                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="title<?= $ad['id'] ?>" class="form-label">Title</label>
                                                                    <input type="text" class="form-control <?= isset($edit_errors['title']) ? 'is-invalid' : '' ?>" 
                                                                           id="title<?= $ad['id'] ?>" name="title" value="<?= htmlspecialchars($ad['title'] ?? '') ?>" required>
                                                                    <?php if (isset($edit_errors['title'])): ?>
                                                                        <div class="invalid-feedback"><?= htmlspecialchars($edit_errors['title']) ?></div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="category<?= $ad['id'] ?>" class="form-label">Category</label>
                                                                    <select class="form-select <?= isset($edit_errors['category_id']) ? 'is-invalid' : '' ?>" 
                                                                            id="category<?= $ad['id'] ?>" name="category_id" required>
                                                                        <option value="" disabled>Select Category</option>
                                                                        <?php foreach ($categories as $cat): ?>
                                                                            <option value="<?= $cat['id'] ?>" <?= ($ad['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                                                                <?= htmlspecialchars($cat['name']) ?>
                                                                            </option>
                                                                        <?php endforeach; ?>
                                                                    </select>
                                                                    <?php if (isset($edit_errors['category_id'])): ?>
                                                                        <div class="invalid-feedback"><?= htmlspecialchars($edit_errors['category_id']) ?></div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="subcategory<?= $ad['id'] ?>" class="form-label">Subcategory</label>
                                                                    <select class="form-select <?= isset($edit_errors['subcategory_id']) ? 'is-invalid' : '' ?>" 
                                                                            id="subcategory<?= $ad['id'] ?>" name="subcategory_id" required>
                                                                        <option value="" disabled>Select Subcategory</option>
                                                                    </select>
                                                                    <?php if (isset($edit_errors['subcategory_id'])): ?>
                                                                        <div class="invalid-feedback"><?= htmlspecialchars($edit_errors['subcategory_id']) ?></div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="price<?= $ad['id'] ?>" class="form-label">Price</label>
                                                                    <input type="number" class="form-control <?= isset($edit_errors['price']) ? 'is-invalid' : '' ?>" 
                                                                           id="price<?= $ad['id'] ?>" name="price" value="<?= htmlspecialchars($ad['price'] ?? '') ?>" required>
                                                                    <?php if (isset($edit_errors['price'])): ?>
                                                                        <div class="invalid-feedback"><?= htmlspecialchars($edit_errors['price']) ?></div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="location<?= $ad['id'] ?>" class="form-label">Location</label>
                                                                    <input type="text" class="form-control <?= isset($edit_errors['location']) ? 'is-invalid' : '' ?>" 
                                                                           id="location<?= $ad['id'] ?>" name="location" value="<?= htmlspecialchars($ad['location'] ?? '') ?>" required>
                                                                    <?php if (isset($edit_errors['location'])): ?>
                                                                        <div class="invalid-feedback"><?= htmlspecialchars($edit_errors['location']) ?></div>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label for="image<?= $ad['id'] ?>" class="form-label">Image</label>
                                                                    <input type="file" class="form-control <?= isset($edit_errors['image']) ? 'is-invalid' : '' ?>" 
                                                                           id="image<?= $ad['id'] ?>" name="image" accept="image/*">
                                                                    <?php if (isset($edit_errors['image'])): ?>
                                                                        <div class="invalid-feedback"><?= htmlspecialchars($edit_errors['image']) ?></div>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($ad['image_path'])): ?>
                                                                        <?php
                                                                        $is_edit_url = preg_match('#^https?://#', $ad['image_path']);
                                                                        $edit_image_url = $is_edit_url ? $ad['image_path'] : '/gadgethub/' . htmlspecialchars($ad['image_path']) . '?t=' . time();
                                                                        ?>
                                                                        <p>Current Image:</p>
                                                                        <img id="editImage<?= $ad['id'] ?>" src="<?= htmlspecialchars($edit_image_url) ?>" alt="Current Image" class="image-preview" 
                                                                             onerror="this.src='/gadgethub/placeholder.jpg'; this.alt='Image failed to load';">
                                                                    <?php endif; ?>
                                                                    <img id="imagePreview<?= $ad['id'] ?>" class="image-preview" style="display: none;">
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="description<?= $ad['id'] ?>" class="form-label">Description</label>
                                                                <textarea class="form-control <?= isset($edit_errors['description']) ? 'is-invalid' : '' ?>" 
                                                                          id="description<?= $ad['id'] ?>" name="description" rows="5" required><?= htmlspecialchars($ad['description'] ?? '') ?></textarea>
                                                                <?php if (isset($edit_errors['description'])): ?>
                                                                    <div class="invalid-feedback"><?= htmlspecialchars($edit_errors['description']) ?></div>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                                            </div>
                                                        </form>
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
                $('#category<?= $ad['id'] ?>').change(function() {
                    const categoryId = $(this).val();
                    const subcategoryDropdown = $('#subcategory<?= $ad['id'] ?>');
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
                                        const selected = subcategory.id == <?= $ad['subcategory_id'] ?? 0 ?> ? 'selected' : '';
                                        subcategoryDropdown.append($('<option>').val(subcategory.id).text(subcategory.name).prop('selected', selected));
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

                if ($('#category<?= $ad['id'] ?>').val()) {
                    $('#category<?= $ad['id'] ?>').trigger('change');
                }

                $('#image<?= $ad['id'] ?>').change(function() {
                    const file = this.files[0];
                    const preview = $('#imagePreview<?= $ad['id'] ?>');
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.attr('src', e.target.result).show();
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.hide();
                    }
                });
            <?php endforeach; ?>
        });
    </script>
</body>
</html>