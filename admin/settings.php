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
            // If user not found or not an admin, redirect or show error
            // This case should ideally be caught by auth_check.php, but is a good fallback
            header('Location: index.php'); // Redirect to dashboard or error page
            exit();
        }

    } catch (PDOException $e) {
        $errors['db'] = 'Database error fetching user data: ' . $e->getMessage();
        error_log("Database error fetching admin user data: " . $e->getMessage());
    }
} else {
    // User ID not in session, should be caught by auth_check.php
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
        // Basic email format validation
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['profile_email'] = 'Invalid email format.';

        // Handle profile image upload
        $profile_image_path = $admin_user['profile_image'] ?? null; // Keep existing image by default
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_image']['tmp_name'];
            $fileName = $_FILES['profile_image']['name'];
            $fileSize = $_FILES['profile_image']['size'];
            $fileType = $_FILES['profile_image']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            // Sanitize file name
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

            // Directory relative to the webroot
            $uploadFileDir = '../uploads/profiles/';

            // Ensure upload directory exists (create if not)
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $dest_path = $uploadFileDir . $newFileName;

            // Allowed file extensions
            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');

            if (in_array($fileExtension, $allowedfileExtensions)) {
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // Delete old image if it exists and is not the default
                    if (!empty($admin_user['profile_image']) && file_exists('../' . $admin_user['profile_image']) && strpos($admin_user['profile_image'], 'uploads/profiles/') === 0) {
                         unlink('../' . $admin_user['profile_image']);
                    }
                    $profile_image_path = 'uploads/profiles/' . $newFileName; // Store path relative to webroot
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
                 // Refresh user data after update
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

        // If no validation errors so far, verify current password and update
        if (empty($errors)) {
            // Fetch the hashed password from the database
            try {
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
                $stmt->execute([$admin_user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($currentPassword, $user['password'])) {
                    // Current password is correct, proceed to update
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
    <title>Settings - OLX Clone Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
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
        .btn-primary { background-color: #007bff; border-color: #007bff; }
        .btn-primary:hover { background-color: #0056b3; border-color: #0056b3; }
        .btn-outline-secondary { border-color: #6c757d; color: #6c757d; }
        .btn-outline-secondary:hover { background-color: #6c757d; color: white; }
        .profile-img { width: 100px; height: 100px; object-fit: cover; }
    </style>
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
           
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="mb-0">Profile Settings</h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Profile Settings</li>
                        </ol>
                    </nav>
                </div>
            </div>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors['db'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errors['db']) ?>
                </div>
            <?php endif; ?>

            <!-- Update Profile -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Update Profile</h6>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="update_profile" value="1"> <!-- Hidden field to identify form -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="profileName" class="form-label">Full Name</label>
                                <input type="text" class="form-control <?= isset($errors['profile_name']) ? 'is-invalid' : '' ?>" 
                                    id="profileName" name="profile_name" placeholder="Enter full name" 
                                    value="<?= htmlspecialchars($admin_user['name'] ?? '') ?>" required>
                                <?php if (isset($errors['profile_name'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['profile_name']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="profileEmail" class="form-label">Email Address</label>
                                <input type="email" class="form-control <?= isset($errors['profile_email']) ? 'is-invalid' : '' ?>" 
                                    id="profileEmail" name="profile_email" placeholder="Enter email" 
                                    value="<?= htmlspecialchars($admin_user['email'] ?? '') ?>" required>
                                <?php if (isset($errors['profile_email'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['profile_email']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="profilePhone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control <?= isset($errors['profile_phone']) ? 'is-invalid' : '' ?>" 
                                    id="profilePhone" name="profile_phone" placeholder="Enter phone number"
                                    value="<?= htmlspecialchars($admin_user['phone'] ?? '') ?>">
                                 <?php if (isset($errors['profile_phone'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['profile_phone']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="profileImage" class="form-label">Profile Image</label>
                                <input type="file" class="form-control <?= isset($errors['profile_image']) ? 'is-invalid' : '' ?>" 
                                    id="profileImage" name="profile_image" accept="image/*">
                                <?php if (isset($errors['profile_image'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['profile_image']) ?></div>
                                <?php endif; ?>
                                <div class="mt-2">
                                    <?php if (!empty($admin_user['profile_image'])): ?>
                                        <img src="../<?= htmlspecialchars($admin_user['profile_image']) ?>" class="profile-img rounded-circle" alt="Profile Image Preview">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/100" class="profile-img rounded-circle" alt="Profile Image Preview">
                                    <?php endif; ?>
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
                    <form method="POST">
                        <input type="hidden" name="reset_password" value="1"> <!-- Hidden field to identify form -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="currentPassword" class="form-label">Current Password</label>
                                <input type="password" class="form-control <?= isset($errors['current_password']) ? 'is-invalid' : '' ?>" 
                                    id="currentPassword" name="current_password" placeholder="Enter current password" required>
                                <?php if (isset($errors['current_password'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['current_password']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="newPassword" class="form-label">New Password</label>
                                <input type="password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" 
                                    id="newPassword" name="new_password" placeholder="Enter new password" required>
                                <?php if (isset($errors['new_password'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['new_password']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                    id="confirmPassword" name="confirm_password" placeholder="Confirm new password" required>
                                <?php if (isset($errors['confirm_password'])): ?>
                                    <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                                <?php endif; ?>
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

        // Active menu item - keep for sidebar highlighting
        const currentPage = '<?= htmlspecialchars(basename($_SERVER['PHP_SELF'])) ?>';
        $('.sidebar-menu li a').each(function() {
            if ($(this).attr('href') === currentPage) {
                $(this).addClass('active');
                // Also open parent submenu if exists
                $(this).parents('.submenu').addClass('show');
                $(this).parents('li').children('.dropdown-toggle').attr('aria-expanded', 'true').addClass('active');
            }
        });

        // Profile Image Preview - keep this
        $('#profileImage').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('.profile-img').attr('src', e.target.result);
                };
                reader.readAsDataURL(file);
            } else {
                // If file input is cleared, revert to current saved image or placeholder
                const currentImage = "../<?php echo htmlspecialchars($admin_user['profile_image'] ?? 'https://via.placeholder.com/100'); ?>";
                $('.profile-img').attr('src', currentImage);
            }
        });
    });
</script>
</body>
</html>