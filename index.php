<?php
session_start();
include 'partials/header.php';
require_once 'db.php'; // Include database connection

// Fetch categories for the "Popular Categories" section
try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");   
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching categories: " . $e->getMessage());
    $categories = [];
}

// Fetch unique locations for the search dropdown (active ads only)
try {
    $stmt = $pdo->query("
        SELECT DISTINCT location 
        FROM ads 
        WHERE status = 'active' AND location IS NOT NULL AND location != ''
        ORDER BY location
    ");
    $locations = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Error fetching locations: " . $e->getMessage());
    $locations = [];
}

// Fetch recently added ads (4 most recent, approved)
try {
    $stmt = $pdo->query("
        SELECT a.*, c.name AS category_name 
        FROM ads a
        JOIN categories c ON a.category_id = c.id
        WHERE a.status = 'active'
        ORDER BY a.created_at DESC
        LIMIT 4
    ");
    $recent_ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching recent ads: " . $e->getMessage());
    $recent_ads = [];
}

// Fetch all approved ads for the "Featured Ads" section
try {
    $stmt = $pdo->query("
        SELECT a.*, c.name AS category_name 
        FROM ads a
        JOIN categories c ON a.category_id = c.id
        WHERE a.status = 'active'
        ORDER BY a.created_at DESC
    ");
    $featured_ads = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching featured ads: " . $e->getMessage());
    $featured_ads = [];
}

// Helper function to calculate time ago
function timeAgo($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $interval = $now->diff($past);

    if ($interval->y > 0) {
        return $interval->y . " year" . ($interval->y > 1 ? "s" : "") . " ago";
    } elseif ($interval->m > 0) {
        return $interval->m . " month" . ($interval->m > 1 ? "s" : "") . " ago";
    } elseif ($interval->d > 0) {
        return $interval->d . " day" . ($interval->d > 1 ? "s" : "") . " ago";
    } elseif ($interval->h > 0) {
        return $interval->h . " hour" . ($interval->h > 1 ? "s" : "") . " ago";
    } elseif ($interval->i > 0) {
        return $interval->i . " minute" . ($interval->i > 1 ? "s" : "") . " ago";
    } else {
        return "Just now";
    }
}

// Helper function to format location
function formatLocation($location) {
    if (empty($location)) {
        return "Location not specified";
    }
    // Capitalize each word in the location
    return ucwords(strtolower(htmlspecialchars(trim($location))));
}
?>

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
  <link rel="stylesheet" href="index.css">
  <style>
    .safety-notice {
        background-color: #fff3cd;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        color: #856404;
        font-size: 0.9rem;
    }
    .product-card {
        position: relative;
        transition: transform 0.2s;
    }
    .product-card:hover {
        transform: translateY(-5px);
    }
    .product-img {
        height: 180px;
        object-fit: cover;
        border-radius: 8px 8px 0 0;
    }
    .category-img {
        height: 120px;
        object-fit: cover;
        border-radius: 0 0 8px 8px;
    }
    .location-text {
        font-size: 0.9rem;
        color: #6c757d;
    }
    .badge-featured {
        background-color: #ffeb3b;
        color: #333;
        font-weight: 500;
    }
  </style>
</head>
<body>
  <!-- Hero Section with Search -->
  <section class="hero-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8 text-center mb-4">
          <h1 class="mb-3">Buy and Sell Anything in Pakistan</h1>
          <p class="lead">Find great deals on new and used items near you</p>
        </div>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <div class="search-card">
            <form method="GET" action="search-result.php">
              <div class="row g-2">
                <div class="col-md-5">
                  <input type="text" name="query" class="form-control form-control-lg" placeholder="What are you looking for?">
                </div>
                <div class="col-md-3">
                  <select name="category" class="form-select form-select-lg">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['id']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-3">
                  <select name="location" class="form-select form-select-lg">
                    <option value="">All Locations</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?= htmlspecialchars($location) ?>"><?= formatLocation($location) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="col-md-1">
                  <button type="submit" class="btn btn-primary btn-lg w-100"><i class="fas fa-search"></i></button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Safety Notice -->
  <section class="container mb-5">
    <div class="safety-notice">
      <p><strong>Safety Tips:</strong> To avoid scams, meet buyers/sellers in a public place, verify the item in person before payment, and avoid sharing personal or financial information.</p>
    </div>
  </section>

  <!-- Categories Section -->
  <section class="container mb-5">
    <h2 class="section-title">Popular Categories</h2>
    <?php if (empty($categories)): ?>
        <div class="alert alert-info">No categories found.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php
            $category_icons = [
                'Mobile Phones' => 'fa-mobile-alt',    
                'Cars' => 'fa-car',
                'Electronics' => 'fa-laptop',
                'Property' => 'fa-home',
            ];
            $category_images = [
                'Mobile Phones' => 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60',
                'Cars' => 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60',
                'Electronics' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60',
                'Property' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60',
            ];
            foreach ($categories as $category):
                $ad_count = rand(5000, 20000); // Replace with real count if needed
                $icon_class = $category_icons[$category['name']] ?? 'fa-tag';
                $category_image = $category_images[$category['name']] ?? 'https://via.placeholder.com/500x120?text=' . urlencode($category['name']);
            ?>
                <div class="col-6 col-md-3">
                    <a href="search-result.php?category=<?= urlencode($category['id']) ?>" class="category-card text-decoration-none text-dark">
                        <div class="p-2 text-center">
                            <div class="category-icon">
                                <i class="fas <?= htmlspecialchars($icon_class) ?>"></i>
                            </div>
                            <h5><?= htmlspecialchars($category['name']) ?></h5>
                            <p class="text-muted"><?= number_format($ad_count) ?> ads</p>
                        </div>
                        <img src="<?= htmlspecialchars($category_image) ?>" class="category-img" alt="<?= htmlspecialchars($category['name']) ?>">
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </section>

  <!-- Recently Added Section -->
  <section class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="section-title">Recently Added</h2>
      <a href="search-result.php" class="btn btn-primary">View All</a>
    </div>
    <?php if (empty($recent_ads)): ?>
        <div class="alert alert-info">No recent ads found.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($recent_ads as $ad): ?>
                <?php
                $is_url = preg_match('#^https?://#', $ad['image_path'] ?? '');
                $image_url = !empty($ad['image_path']) ? ($is_url ? $ad['image_path'] : '/gadgethub/' . htmlspecialchars($ad['image_path']) . '?t=' . time()) : 'https://via.placeholder.com/500x180?text=No+Image';
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <a href="ad-details.php?id=<?= htmlspecialchars($ad['id']) ?>" class="text-decoration-none text-dark">
                            <img src="<?= htmlspecialchars($image_url) ?>" 
                                 class="product-img w-100" alt="<?= htmlspecialchars($ad['title']) ?>">
                            <div class="p-3">
                                <h5 class="mb-1"><?= htmlspecialchars($ad['title']) ?></h5>
                                <p class="price-tag mb-1">Rs <?= number_format($ad['price'], 0) ?></p>
                                <p class="location-text mb-2"><i class="fas fa-map-marker-alt"></i> <?= formatLocation($ad['location']) ?></p>
                                <small class="text-muted"><?= timeAgo($ad['created_at']) ?></small>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </section>

  <!-- Featured Ads Section -->
  <section class="container mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="section-title">Featured Ads</h2>
      <a href="search-result.php" class="btn btn-primary">View All</a>
    </div>
    <?php if (empty($featured_ads)): ?>
        <div class="alert alert-info">No featured ads found.</div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($featured_ads as $index => $ad): ?>
                <?php
                $is_url = preg_match('#^https?://#', $ad['image_path'] ?? '');
                $image_url = !empty($ad['image_path']) ? ($is_url ? $ad['image_path'] : '/gadgethub/' . htmlspecialchars($ad['image_path']) . '?t=' . time()) : 'https://via.placeholder.com/500x180?text=No+Image';
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <a href="ad-details.php?id=<?= htmlspecialchars($ad['id']) ?>" class="text-decoration-none text-dark">
                            <?php if ($index < 2): ?>
                                <span class="badge badge-featured position-absolute m-2">Featured</span>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($image_url) ?>" 
                                 class="product-img w-100" alt="<?= htmlspecialchars($ad['title']) ?>">
                            <div class="p-3">
                                <h5 class="mb-1"><?= htmlspecialchars($ad['title']) ?></h5>
                                <p class="price-tag mb-1">Rs <?= number_format($ad['price'], 0) ?></p>
                                <p class="location-text mb-2"><i class="fas fa-map-marker-alt"></i> <?= formatLocation($ad['location']) ?></p>
                                <small class="text-muted"><?= timeAgo($ad['created_at']) ?></small>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </section>

  <?php include 'partials/footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Theme toggle and card animation
    document.addEventListener('DOMContentLoaded', function() {
      const themeToggle = document.createElement('button');
      themeToggle.className = 'btn btn-sm btn-outline-light position-fixed bottom-0 end-0 m-3';
      themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
      themeToggle.onclick = function() {
        document.body.classList.toggle('dark-theme');
        this.innerHTML = document.body.classList.contains('dark-theme') ? 
          '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
      };
      document.body.appendChild(themeToggle);
      
      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animate__animated', 'animate__fadeInUp');
          }
        });
      }, { threshold: 0.1 });
      
      document.querySelectorAll('.product-card, .category-card').forEach(card => {
        observer.observe(card);
      });
    });
  </script>
</body>
</html>