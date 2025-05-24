<?php
include_once 'auth_check.php'; // Include authentication check
require_once '../db.php'; // Include database connection

// Get the logged-in admin's user ID from the session
$admin_user_id = $_SESSION['user_id'] ?? null; // Assuming user_id is stored in session

$admin_user = null;
$errors = [];
$success_message = '';

// Fetch current admin user data
if ($admin_user_id) {
    try {
        $stmt = $pdo->prepare("SELECT id, name, email, phone, profile_image FROM users WHERE id = ? AND role = 'Admin' LIMIT 1");
        $stmt->execute([$admin_user_id]);
        $admin_user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin_user) {
            header('Location: dashboard.php');
            exit();
        }

    } catch (PDOException $e) {
        $errors['db'] = 'Database error fetching user data: ' . $e->getMessage();
        error_log("Database error fetching admin user data: " . $e->getMessage());
    }
} else {
    header('Location: ../login.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle Update Profile form
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['profile_name'] ?? '');
        $email = trim($_POST['profile_email'] ?? '');
        $phone = trim($_POST['profile_phone'] ?? '');

        if (empty($name)) $errors['profile_name'] = 'Full Name is required.';
        if (empty($email)) $errors['profile_email'] = 'Email Address is required.';
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['profile_email'] = 'Invalid email format.';

        // Handle profile image upload
        $profile_image_path = $admin_user['profile_image'] ?? null;
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_image']['tmp_name'];
            $fileName = $_FILES['profile_image']['name'];
            $fileSize = $_FILES['profile_image']['size'];
            $fileType = $_FILES['profile_image']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = '../uploads/profiles/';

            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $dest_path = $uploadFileDir . $newFileName;
            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

            if (in_array($fileExtension, $allowedfileExtensions)) {
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    if (!empty($admin_user['profile_image']) && file_exists('../' . $admin_user['profile_image']) && strpos($admin_user['profile_image'], 'uploads/profiles/') === 0) {
                         unlink('../' . $admin_user['profile_image']);
                    }
                    $profile_image_path = 'uploads/profiles/' . $newFileName;
                } else {
                    $errors['profile_image'] = 'There was an error moving the uploaded file.';
                }
            } else {
                 $errors['profile_image'] = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
            }
        }

        // If no validation errors, update the database
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, profile_image = ? WHERE id = ?");
                $stmt->execute([$name, $email, $phone, $profile_image_path, $admin_user_id]);
                $success_message = 'Profile updated successfully!';
                $stmt = $pdo->prepare("SELECT id, name, email, phone, profile_image FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$admin_user_id]);
                $admin_user = $stmt->fetch(PDO::FETCH_ASSOC);

            } catch (PDOException $e) {
                $errors['db'] = 'Database error updating profile: ' . $e->getMessage();
                error_log("Database error updating admin profile: " . $e->getMessage());
            }
        }
    }

    // Handle Reset Password form
    if (isset($_POST['reset_password'])) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword)) $errors['current_password'] = 'Current Password is required.';
        if (empty($newPassword)) $errors['new_password'] = 'New Password is required.';
        if (empty($confirmPassword)) $errors['confirm_password'] = 'Confirm New Password is required.';

        if ($newPassword !== $confirmPassword) {
            $errors['confirm_password'] = 'New password and confirm password do not match.';
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$admin_user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($currentPassword, $user['password'])) {
                    $hashed_new_password = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed_new_password, $admin_user_id]);
                    $success_message = 'Password reset successfully!';
                } else {
                    $errors['current_password'] = 'Incorrect current password.';
                }

            } catch (PDOException $e) {
                $errors['db'] = 'Database error resetting password: ' . $e->getMessage();
                error_log("Database error resetting admin password: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Gadget Hub Admin</title>
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
                            <img src="<?= !empty($admin_user['profile_image']) ? '../' . $admin_user['profile_image'] : 'https://ui-avatars.com/api/?name=' . urlencode($admin_user['name']) . '&background=4361ee&color=fff' ?>" 
                                 class="rounded-circle me-2" 
                                 alt="<?= htmlspecialchars($admin_user['name']) ?>">
                            <span><?= htmlspecialchars($admin_user['name']) ?></span>
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
                    <h2 class="mb-0">Settings</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Settings</li>
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

            <?php if (isset($errors['db'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($errors['db']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Profile Settings -->
                <div class="col-lg-6 mb-4">
                    <div class="table-card">
                        <div class="card-header py-3">
                            <h5 class="mb-0">Profile Settings</h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST" enctype="multipart/form-data">
                                <div class="text-center mb-4">
                                    <img src="<?= !empty($admin_user['profile_image']) ? '../' . $admin_user['profile_image'] : 'https://ui-avatars.com/api/?name=' . urlencode($admin_user['name']) . '&background=4361ee&color=fff' ?>" 
                                         class="rounded-circle mb-3" 
                                         alt="<?= htmlspecialchars($admin_user['name']) ?>"
                                         style="width: 150px; height: 150px; object-fit: cover;">
                                    <div>
                                        <label class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-camera me-2"></i>Change Photo
                                            <input type="file" name="profile_image" class="d-none" accept="image/*">
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" 
                                           class="form-control <?= isset($errors['profile_name']) ? 'is-invalid' : '' ?>" 
                                           name="profile_name" 
                                           value="<?= htmlspecialchars($admin_user['name']) ?>" 
                                           required>
                                    <?php if (isset($errors['profile_name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['profile_name'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" 
                                           class="form-control <?= isset($errors['profile_email']) ? 'is-invalid' : '' ?>" 
                                           name="profile_email" 
                                           value="<?= htmlspecialchars($admin_user['email']) ?>" 
                                           required>
                                    <?php if (isset($errors['profile_email'])): ?>
                                        <div class="invalid-feedback"><?= $errors['profile_email'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" 
                                           class="form-control" 
                                           name="profile_phone" 
                                           value="<?= htmlspecialchars($admin_user['phone']) ?>">
                                </div>

                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Password Settings -->
                <div class="col-lg-6 mb-4">
                    <div class="table-card">
                        <div class="card-header py-3">
                            <h5 class="mb-0">Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form action="" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" 
                                               name="current_password" 
                                               required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <?php if (isset($errors['current_password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['current_password'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" 
                                               name="new_password" 
                                               required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <?php if (isset($errors['new_password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['new_password'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <input type="password" 
                                               class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                               name="confirm_password" 
                                               required>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <button type="submit" name="reset_password" class="btn btn-primary">
                                    <i class="fas fa-key me-2"></i>Update Password
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Security Tips -->
                    <div class="table-card mt-4">
                        <div class="card-header py-3">
                            <h5 class="mb-0">Security Tips</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <h6 class="alert-heading"><i class="fas fa-shield-alt me-2"></i>Password Security</h6>
                                <ul class="mb-0">
                                    <li>Use a strong, unique password</li>
                                    <li>Include numbers, symbols, and mixed case letters</li>
                                    <li>Never share your password with anyone</li>
                                    <li>Change your password regularly</li>
                                </ul>
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

        // Toggle Password Visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Profile Image Preview
        document.querySelector('input[name="profile_image"]').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.rounded-circle').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>