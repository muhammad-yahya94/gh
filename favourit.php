<?php
session_start();
?>
<?php include 'partials/header.php'; ?>

<style>
    /* Page specific styles for Favorites */
    .page-header {
      padding: 3rem 0 1rem;
    }
    
    .page-title {
      font-weight: 600;
      color: var(--primary-color);
    }
    
    .page-subtitle {
      color: var(--light-text);
    }
    
    .favorites-container {
      padding: 2rem 0;
    }
    
    /* Card styles adapted from ad-detail.php .product-card */
    .favorite-card {
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      transition: transform 0.3s, box-shadow 0.3s;
      height: 100%;
      position: relative;
    }
    
    .favorite-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .favorite-img {
      height: 180px; /* Match product card image height */
      object-fit: cover;
      width: 100%;
    }
    
    .favorite-body {
      padding: 1rem; /* Match product card body padding */
    }
    
    .favorite-title {
      font-weight: 600; /* Match product card title weight */
      margin-bottom: 0.5rem;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .favorite-price {
      font-weight: 700; /* Match product card price weight */
      color: var(--accent-color); /* Use accent color for price */
      margin-bottom: 0.25rem;
    }
    
    .favorite-location {
      color: var(--light-text);
      font-size: 0.9rem;
      margin-bottom: 1rem;
    }
    
    .favorite-actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .view-btn {
      background-color: var(--primary-color);
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 5px;
      font-weight: 500;
      text-decoration: none;
    }
    
    .remove-btn {
      background: none;
      border: none;
      color: #e63946;
      cursor: pointer;
      font-weight: 500;
    }
    
    .empty-state {
      text-align: center;
      padding: 4rem 0;
    }
    
    .empty-icon {
      font-size: 4rem;
      color: var(--light-text);
      margin-bottom: 1.5rem;
    }
    
    .empty-title {
      font-weight: 600;
      margin-bottom: 1rem;
    }
    
    .empty-text {
      color: var(--light-text);
      max-width: 500px;
      margin: 0 auto 2rem;
    }
    
    .browse-btn {
      background-color: var(--accent-color);
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 5px;
      font-weight: 500;
      text-decoration: none;
    }

    /* Dark mode styles adapted for favorites page */
    .dark-mode .favorite-card {
      background-color: #1e1e1e;
      color: #e0e0e0;
    }
    
    .dark-mode .page-title {
      color: #e0e0e0;
    }
    
    .dark-mode .favorite-price {
      color: var(--secondary-color); /* Adjust price color for dark mode */
    }
    
    .dark-mode .empty-icon {
      color: #444;
    }

   </style>

  <!-- Page Header -->
  <section class="page-header">
    <div class="container">
      <h1 class="page-title">My Favorites</h1>
      <p class="page-subtitle">Ads you've saved for later</p>
    </div>
  </section>

  <!-- Favorites Container -->
  <section class="favorites-container">
    <div class="container">
      <?php
      // Placeholder for fetching and displaying favorite ads
      $favorite_ads = []; // Assume this will be populated from the database

      if (empty($favorite_ads)) {
          // Display empty state if no favorites
          echo '<div class="empty-state">';
          echo '<i class="fas fa-heart-broken empty-icon"></i>';
          echo '<h2 class="empty-title">No Favorites Yet</h2>';
          echo '<p class="empty-text">Looks like you haven\'t added any ads to your favorites. Start browsing to find ads you love!</p>';
          echo '<a href="index.php" class="btn browse-btn"><i class="fas fa-compass me-2"></i>Browse Ads</a>';
          echo '</div>';
      } else {
          // Display favorite ads (placeholder structure)
          echo '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">';
          foreach ($favorite_ads as $ad) {
              echo '<div class="col">';
              echo '<div class="favorite-card">';
              echo '<img src="https://via.placeholder.com/400x300" class="favorite-img" alt="Product Image">';
              echo '<div class="favorite-body">';
              echo '<h5 class="favorite-title">' . htmlspecialchars($ad['title']) . '</h5>';
              echo '<p class="favorite-price">PKR ' . number_format($ad['price']) . '</p>';
              echo '<p class="favorite-location"><i class="fas fa-map-marker-alt me-1"></i>' . htmlspecialchars($ad['location']) . '</p>';
              echo '<div class="favorite-actions">';
              echo '<a href="ad-detail.php?id=' . $ad['id'] . '" class="view-btn">View Ad</a>';
              echo '<button class="remove-btn"><i class="fas fa-trash-alt me-1"></i>Remove</button>';
              echo '</div>';
              echo '</div>';
              echo '</div>';
              echo '</div>';
          }
          echo '</div>';
      }
      ?>
    </div>
  </section>

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