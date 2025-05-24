<?php
ob_start(); // Start output buffering
session_start();
require_once 'db.php'; // Include your database connection file

// Initialize form data and errors arrays from session
$errors = $_SESSION['errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? ['name' => '', 'email' => '', 'password' => '', 'confirm_password' => '', 'terms' => ''];

// Clear session data after reading it
unset($_SESSION['errors']);
unset($_SESSION['form_data']);

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? ''; // Password will be hashed, no need to trim
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = $_POST['terms'] ?? '';
    
    $errors = []; // Reset errors for current submission
    
    // Basic validation
    if (empty($name)) {
        $errors['name'] = 'Full name is required.';
    }
    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email address format.';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) { // Example minimum length
        $errors['password'] = 'Password must be at least 6 characters long.';
    }
    if (empty($confirm_password)) {
         $errors['confirm_password'] = 'Please confirm your password.';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match.';
    }
    // Check if terms checkbox is checked
     if (empty($terms)) {
        $errors['terms'] = 'You must agree to the terms and conditions.';
    }
    
    // Check if email already exists (only if there are no other validation errors for email)
    if (empty($errors['email'])) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors['email'] = 'This email address is already registered.';
        }
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Set the default role for a new user
        $role = 'User'; // Assuming new users have 'User' role by default
        
        // Insert user into the database
        // Use the 'role' column
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        
        if ($stmt->execute([$name, $email, $hashed_password, $role])) {
            // Registration successful
            $_SESSION['success_message'] = 'Registration successful. Please log in.';
            // Use a root-relative path for redirect
            ob_clean(); // Clean output buffer before redirect
            session_write_close(); // Save session and close
            header('Location: /gadgethub/login.php'); // Correct path based on your workspace root
            exit();
        } else {
            // Database error
            $error_info = $stmt->errorInfo();
            $errors['general'] = 'An error occurred during registration: ' . $error_info[2];
            // Consider logging $error_info[2] to a file for debugging as well
        }
    }
    
    // If there are errors, store them in session and display the form
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = ['name' => $name, 'email' => $email, 'terms' => $terms]; // Preserve form data and terms checkbox state
        // No redirect here, let the script continue to display the form with errors
    }
}
// No else block for GET requests, let the script continue to display the empty form
?>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
:root {
  --primary-color: #1a3c34; /* Dark teal for a modern, techy vibe */
  --secondary-color: #00d4b4; /* Bright teal for accents */
  --accent-color: #ff5733; /* Vibrant orange for buttons and links */
  --light-bg: #f5f7fa; /* Light gray-blue for background */
  --dark-text: #1a1a1a; /* Near-black for text */
  --light-text: #6b7280; /* Muted gray for secondary text */
  --error-color: #dc3545; /* Bootstrap-compatible red for errors */
}

body {
  font-family: 'Inter', sans-serif;
  background-color: var(--light-bg);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.signup-container {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
}

.signup-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
  width: 100%;
  max-width: 420px;
  padding: 2.5rem;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.signup-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.12);
}

.signup-title {
  font-weight: 700;
  color: var(--primary-color);
  margin-bottom: 2rem;
  text-align: center;
  font-size: 1.8rem;
}

.form-label {
  font-weight: 500;
  color: var(--dark-text);
  font-size: 0.95rem;
  margin-bottom: 0.5rem;
}

.form-control {
  padding: 0.85rem;
  border-radius: 8px;
  border: 1px solid #d1d5db;
  font-size: 0.95rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.form-control:focus {
  border-color: var(--secondary-color);
  box-shadow: 0 0 0 0.2rem rgba(0, 212, 180, 0.2);
  outline: none;
}

.form-control.is-invalid {
  border-color: var(--error-color);
  background-color: #fff5f5;
}

.invalid-feedback {
  font-size: 0.85rem;
  color: var(--error-color);
  margin-top: 0.25rem;
}

.form-check {
  margin-bottom: 1.5rem;
}

.form-check-input {
  width: 1.2rem;
  height: 1.2rem;
  margin-right: 0.5rem;
  border: 2px solid #d1d5db;
  cursor: pointer;
}

.form-check-input:checked {
  background-color: var(--secondary-color);
  border-color: var(--secondary-color);
}

.form-check-input.is-invalid {
  border-color: var(--error-color);
}

.form-check-label {
  font-size: 0.9rem;
  color: var(--dark-text);
}

.terms-link {
  color: var(--accent-color);
  text-decoration: none;
}

.terms-link:hover {
  text-decoration: underline;
}

.signup-btn {
  background-color: var(--accent-color);
  border: none;
  padding: 0.85rem;
  font-weight: 600;
  font-size: 1rem;
  border-radius: 8px;
  width: 100%;
  transition: background-color 0.2s ease;
}

.signup-btn:hover {
  background-color: #e04b2c;
}

.login-text {
  text-align: center;
  margin-top: 1.5rem;
  font-size: 0.9rem;
  color: var(--light-text);
}

.login-link {
  color: var(--accent-color);
  font-weight: 500;
  text-decoration: none;
}

.login-link:hover {
  text-decoration: underline;
}

.alert {
  font-size: 0.9rem;
  padding: 0.75rem;
  border-radius: 6px;
  margin-bottom: 1.5rem;
}

.alert-danger {
  background-color: #f8d7da;
  border-color: #f5c6cb;
  color: var(--error-color);
}

/* Dark mode styles */
.dark-mode {
  background-color: #111827;
  color: #d1d5db;
}

.dark-mode .signup-card {
  background-color: #1f2937;
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.3);
}

.dark-mode .form-control {
  background-color: #374151;
  border-color: #4b5563;
  color: #d1d5db;
}

.dark-mode .form-control:focus {
  border-color: var(--secondary-color);
  box-shadow: 0 0 0 0.2rem rgba(0, 212, 180, 0.3);
}

.dark-mode .form-label {
  color: #d1d5db;
}

.dark-mode .form-check-input {
  border-color: #4b5563;
  background-color: #374151;
}

.dark-mode .form-check-label {
  color: #d1d5db;
}

.dark-mode .login-text {
  color: #9ca3af;
}

.dark-mode .alert-danger {
  background-color: #7c2d2d;
  border-color: #991b1b;
  color: #f3e8e8;
}

/* Responsive adjustments */
@media (max-width: 576px) {
  .signup-card {
    padding: 1.5rem;
    max-width: 90%;
  }

  .signup-title {
    font-size: 1.5rem;
  }

  .form-control {
    padding: 0.75rem;
  }

  .signup-btn {
    padding: 0.75rem;
  }
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
    .footer {
      background-color: var(--primary-color);
      color: white;
      padding: 3rem 0 1rem;
    }
    /* Added for error messages */
    .alert {
        margin-bottom: 1.5rem;
    }
  </style>
</head>
<body>
<?php include 'partials/header.php'; ?>

  <div class="signup-container">
    <div class="signup-card">
      <h2 class="signup-title">Create a GadgetHub Account</h2>

      <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php echo htmlspecialchars($errors['general']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <form action="signup.php" method="POST">
        <div class="mb-3">
          <label for="name" class="form-label">Full Name</label>
          <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" required value="<?php echo htmlspecialchars($form_data['name']); ?>">
          <?php if (isset($errors['name'])): ?>
            <div class="invalid-feedback">
              <?php echo htmlspecialchars($errors['name']); ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email address</label>
          <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" required value="<?php echo htmlspecialchars($form_data['email']); ?>">
           <?php if (isset($errors['email'])): ?>
            <div class="invalid-feedback">
              <?php echo htmlspecialchars($errors['email']); ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
           <?php if (isset($errors['password'])): ?>
            <div class="invalid-feedback">
              <?php echo htmlspecialchars($errors['password']); ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="mb-3">
          <label for="confirm_password" class="form-label">Confirm Password</label>
          <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" required>
           <?php if (isset($errors['confirm_password'])): ?>
            <div class="invalid-feedback">
              <?php echo htmlspecialchars($errors['confirm_password']); ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="mb-3 form-check <?php echo isset($errors['terms']) ? 'is-invalid' : ''; ?>">
          <input type="checkbox" class="form-check-input <?php echo isset($errors['terms']) ? 'is-invalid' : ''; ?>" id="terms" name="terms" value="agree" <?php echo isset($form_data['terms']) && $form_data['terms'] === 'agree' ? 'checked' : ''; ?> required>
          <label class="form-check-label" for="terms">I agree to the <a href="terms.html" class="terms-link">Terms of Service</a></label>
           <?php if (isset($errors['terms'])): ?>
            <div class="invalid-feedback">
              <?php echo htmlspecialchars($errors['terms']); ?>
            </div>
          <?php endif; ?>
        </div>
        <div class="d-grid mb-3">
          <button type="submit" class="btn btn-primary signup-btn">Sign Up</button>
        </div>
      </form>

      <p class="login-text">Already have an account? <a href="login.php" class="login-link">Login</a></p>
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