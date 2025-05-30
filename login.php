<?php
require_once 'db.php'; // Include your database connection file

// Start the session
session_start();

// Check if the user is already logged in and is an admin, redirect to dashboard if so
// Now checking for 'user_role' in session
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin') {
    header('Location: admin/dashboard.php');
    exit();
}

// Initialize error message from session
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);

// Check if the form was submitted (Only process login on POST requests)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $error_message = 'Please enter both email and password.';
    } else {
        // Fetch user from the database, including the 'role' column
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password and check user's role
        if ($user && password_verify($password, $user['password'])) {
            // Authentication successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            // Store the user's role in the session
            $_SESSION['user_role'] = $user['role']; // Remove the default 'User' fallback
            
            // Debug information
            error_log("User role from database: " . $user['role']);
            error_log("Session role: " . $_SESSION['user_role']);
            
            // Check if the user is an Admin (case-insensitive comparison)
            if (strtolower($_SESSION['user_role']) === 'admin') {
                // Redirect to admin dashboard
                header('Location: admin/dashboard.php');
                exit();
            } else {
                // Authentication successful, but user is not an admin
                // Redirect regular users to a different page, e.g., homepage or user dashboard
                header('Location: index.php'); // Or your user dashboard page
                exit();
            }
        } else {
            // Authentication failed
            $error_message = 'Invalid email or password.';
        }
    }
    
    // If there is an error message from a failed POST, store it in session
    // This part is now handled by setting $error_message directly above
}

// If it's a GET request or POST failed, display the login form with potential errors
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GadgetHub - Login</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --primary-color: #002f34;
      --secondary-color: #23e5db;
      --accent-color: #ff6b00;
      --light-bg: #f2f4f5;
      --dark-text: #002f34;
      --light-text: #7f9799;
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--light-bg);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    
    .navbar {
      background-color: var(--primary-color);
      padding: 0.5rem 0;
    }
    
    .navbar-brand {
      font-weight: 700;
      color: white !important;
      font-size: 1.8rem;
    }
    
    .nav-link {
      color: white !important;
      font-weight: 500;
      padding: 0.5rem 1rem !important;
    }
    
    .nav-link:hover {
      color: var(--secondary-color) !important;
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }
    
    .btn-outline-primary {
      border-color: white;
      color: white;
    }
    
    .btn-outline-primary:hover {
      background-color: white;
      color: var(--primary-color);
    }
    
    .login-container {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 0;
    }
    
    .login-card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 400px;
      padding: 2rem;
    }
    
    .login-title {
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 1.5rem;
      text-align: center;
    }
    
    .form-label {
      font-weight: 500;
      color: var(--dark-text);
    }
    
    .form-control {
      padding: 0.75rem;
      border-radius: 6px;
      border: 1px solid #ddd;
    }
    
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(0, 47, 52, 0.1);
    }
    
    .forgot-link {
      color: var(--accent-color);
      text-decoration: none;
      font-size: 0.9rem;
    }
    
    .forgot-link:hover {
      text-decoration: underline;
    }
    
    .login-btn {
      background-color: var(--accent-color);
      border: none;
      padding: 0.75rem;
      font-weight: 600;
      width: 100%;
    }
    
    .login-btn:hover {
      background-color: #e05d00;
    }
    
    .signup-text {
      text-align: center;
      margin-top: 1.5rem;
      color: var(--light-text);
    }
    
    .signup-link {
      color: var(--accent-color);
      font-weight: 500;
      text-decoration: none;
    }
    
    .signup-link:hover {
      text-decoration: underline;
    }
    
    .footer {
      background-color: var(--primary-color);
      color: white;
      padding: 3rem 0 1rem;
    }
    
    .footer-links h5 {
      font-weight: 600;
      margin-bottom: 1.5rem;
    }
    
    .footer-links a {
      color: #bdc3c7;
      text-decoration: none;
      display: block;
      margin-bottom: 0.5rem;
    }
    
    .footer-links a:hover {
      color: white;
    }
    
    .social-icon {
      font-size: 1.5rem;
      color: white;
      margin-right: 1rem;
    }
    
    .theme-toggle {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 1000;
      background-color: var(--accent-color);
      color: white;
      border: none;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 10px rgba(0,0,0,0.2);
      cursor: pointer;
    }
    
    /* Dark mode styles */
    .dark-mode {
      background-color: #121212;
      color: #e0e0e0;
    }
    
    .dark-mode .login-card {
      background-color: #1e1e1e;
      color: #e0e0e0;
    }
    
    .dark-mode .form-control {
      background-color: #2d2d2d;
      border-color: #444;
      color: #e0e0e0;
    }
    
    .dark-mode .form-label {
      color: #e0e0e0;
    }
    
    .dark-mode .signup-text {
      color: #aaa;
    }

    /* Added for error messages */
    .alert {
        margin-bottom: 1.5rem;
    }
  </style>
</head>
<body>
  <?php include 'partials/header.php'; ?>

  <div class="login-container">
    <div class="login-card">
      <h2 class="login-title">Login to GadgetHub</h2>

      <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php echo $error_message; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <form action="login.php" method="POST">
        <div class="mb-3">
          <label for="email" class="form-label">Email address</label>
          <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="rememberMe">
          <label class="form-check-label" for="rememberMe">Remember me</label>
        </div>
        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-primary login-btn">Login</button>
        </div>
      </form>

      <p class="signup-text">Don't have an account? <a href="signup.php" class="signup-link">Sign Up</a></p>
    </div>
  </div>

  <?php include 'partials/footer.php'; ?>

  <!-- Theme Toggle Button -->
  <button id="theme-toggle" class="theme-toggle">
    <i class="fas fa-moon"></i>
  </button>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const themeToggle = document.getElementById('theme-toggle');
    const body = document.body;

    // Check for saved theme in localStorage
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
      body.classList.add(savedTheme);
      // Update icon based on saved theme
      if (savedTheme === 'dark-mode') {
        themeToggle.querySelector('i').classList.replace('fa-moon', 'fa-sun');
      } else {
        themeToggle.querySelector('i').classList.replace('fa-sun', 'fa-moon');
      }
    }

    themeToggle.addEventListener('click', () => {
      if (body.classList.contains('dark-mode')) {
        body.classList.remove('dark-mode');
        themeToggle.querySelector('i').classList.replace('fa-sun', 'fa-moon');
        localStorage.setItem('theme', 'light-mode');
      } else {
        body.classList.add('dark-mode');
        themeToggle.querySelector('i').classList.replace('fa-moon', 'fa-sun');
        localStorage.setItem('theme', 'dark-mode');
      }
    });
  </script>
</body>
</html>
