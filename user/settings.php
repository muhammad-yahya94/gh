<?php
session_start();
include_once 'user_auth_check.php'; // Include authentication check
require_once '../db.php'; // Include database connection

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Fetch current user data
try {
    $stmt = $pdo->prepare("SELECT name, email, phone, profile_image FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $error_message = "User not found.";
        $user = ['name' => '', 'email' => '', 'phone' => '', 'profile_image' => ''];
    }
} catch (PDOException $e) {
    error_log("Error fetching user data: " . $e->getMessage());
    $error_message = "Error fetching user data.";
    $user = ['name' => '', 'email' => '', 'phone' => '', 'profile_image' => ''];
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['profileName'] ?? '');
    $email = trim($_POST['profileEmail'] ?? '');
    $phone = trim($_POST['profilePhone'] ?? '');

    // Validate inputs
    if (empty($name) || empty($email)) {
        $error_message = "Name and Email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        try {
            // Handle profile image upload
            $profile_image = $user['profile_image'];
            if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../Uploads/users/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_tmp = $_FILES['profileImage']['tmp_name'];
                $file_name = $_FILES['profileImage']['name'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($file_ext, $allowed_exts)) {
                    $error_message = "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.";
                } else {
                    $new_file_name = uniqid() . '.' . $file_ext;
                    $dest_path = $upload_dir . $new_file_name;

                    if (move_uploaded_file($file_tmp, $dest_path)) {
                        // Delete old image if it exists
                        if ($profile_image && file_exists('../' . $profile_image)) {
                            unlink('../' . $profile_image);
                        }
                        $profile_image = 'Uploads/users/' . $new_file_name;
                    } else {
                        $error_message = "Error uploading image.";
                    }
                }
            }

            if (!$error_message) {
                // Update user data in the database
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone = ?, profile_image = ? WHERE id = ?");
                $stmt->execute([$name, $email, $phone, $profile_image, $user_id]);
                $success_message = "Profile updated successfully!";
            }
        } catch (PDOException $e) {
            error_log("Error updating profile: " . $e->getMessage());
            $error_message = "Error updating profile: " . htmlspecialchars($e->getMessage());
        }
    }
}

// Handle password reset
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password'])) {
    $current_password = $_POST['currentPassword'] ?? '';
    $new_password = $_POST['newPassword'] ?? '';
    $confirm_password = $_POST['confirmPassword'] ?? '';

    // Validate inputs
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New password and confirm password do not match.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "New password must be at least 6 characters long.";
    } else {
        try {
            // Verify current password
            $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $stored_password = $stmt->fetchColumn();

            if (!password_verify($current_password, $stored_password)) {
                $error_message = "Current password is incorrect.";
            } else {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
                $success_message = "Password reset successfully!";
            }
        } catch (PDOException $e) {
            error_log("Error resetting password: " . $e->getMessage());
            $error_message = "Error resetting password: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - GadgetHub User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../admin/assets/css/admin-style.css">
    <!-- <style>
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
        .btn-primary { background-color: #007bff; border-color: #007bff; }
        .btn-primary:hover { background-color: #0056b3; border-color: #0056b3; }
        .btn-outline-secondary { border-color: #6c757d; color: #6c757d; }
        .btn-outline-secondary:hover { background-color: #6c757d; color: white; }
        .profile-img { width: 100px; height: 100px; object-fit: cover; }
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
                    <div class="dropdown me-3">
                 
                    </div>
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
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-0">Profile Settings</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Profile Settings</li>
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

            <!-- Update Profile -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update Profile</h6>
                </div>
                <div class="card-body">
                    <form id="updateProfileForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="update_profile" value="1">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="profileName" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="profileName" name="profileName" 
                                       value="<?= htmlspecialchars($user['name']) ?>" placeholder="Enter full name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="profileEmail" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="profileEmail" name="profileEmail" 
                                       value="<?= htmlspecialchars($user['email']) ?>" placeholder="Enter email" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="profilePhone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="profilePhone" name="profilePhone" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="Enter phone number">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="profileImage" class="form-label">Profile Image</label>
                                <input type="file" class="form-control" id="profileImage" name="profileImage" accept="image/*">
                                <div class="mt-2">
                                    <img src="<?= !empty($user['profile_image']) ? '/gadgethub/' . htmlspecialchars($user['profile_image']) . '?t=' . time() : 'https://via.placeholder.com/100' ?>" 
                                         class="profile-img rounded-circle" alt="Profile Image">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="window.location.reload()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reset Password -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Reset Password</h6>
                </div>
                <div class="card-body">
                    <form id="resetPasswordForm" method="POST">
                        <input type="hidden" name="reset_password" value="1">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="currentPassword" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="currentPassword" name="currentPassword" 
                                       placeholder="Enter current password" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="newPassword" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="newPassword" name="newPassword" 
                                       placeholder="Enter new password" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" 
                                       placeholder="Confirm new password" required>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="button" class="btn btn-outline-secondary me-2" onclick="window.location.reload()">Cancel</button>
                            <button type="submit" class="btn btn-primary">Reset Password</button>
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
            // Sidebar toggle
            $('.toggle-sidebar').click(function() {
                $('.sidebar').toggleClass('sidebar-collapsed');
                $('.main-content').toggleClass('content-expanded');
            });

            // Active menu item
            $('.sidebar-menu li a').click(function() {
                $('.sidebar-menu li a').removeClass('active');
                $(this).addClass('active');
            });

            // Preview profile image
            $('#profileImage').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('.profile-img').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>
</html>