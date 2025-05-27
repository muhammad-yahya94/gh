<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GadgetHub - Buy & Sell Everything</title>
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
      color: var(--dark-text);
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

  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="index.php">GadgetHub</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="search-result.php"><i class="fas fa-list me-1"></i> Categories</a>
          </li>
          <!-- <li class="nav-item">
            <a class="nav-link" href="#"><i class="fas fa-plus-circle me-1"></i> Sell</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="contact.php"><i class="fas fa-envelope me-1"></i> Contact Us</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="favourit.php"><i class="fas fa-heart me-1"></i> Favorites</a>
          </li> -->
        </ul>
        <div class="d-flex">
          <?php if (isset($_SESSION['user_id'])): ?>
              <!-- User is logged in -->
              <?php
                  $dashboard_link = 'user/dashboard.php'; // Default link for regular users
                  if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'Admin') {
                      $dashboard_link = 'admin/dashboard.php'; // Link for admin users
                  }
              ?>
              <a href="<?= $dashboard_link ?>" class="btn btn-outline-primary me-2"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a>
              <a href="logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
          <?php else: ?>
              <!-- User is not logged in -->
              <a href="login.php" class="btn btn-outline-primary me-2"><i class="fas fa-user me-1"></i> Login</a>
              <a href="signup.php" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i> Register</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>
</body>
</html> 