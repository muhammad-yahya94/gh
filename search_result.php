<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Results - GadgetHub</title>
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
    
    .search-container {
      padding: 2rem 0;
    }
    
    .filter-card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      padding: 1.5rem;
      height: 100%;
    }
    
    .filter-title {
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 1.5rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid var(--light-bg);
    }
    
    .filter-section {
      margin-bottom: 1.5rem;
    }
    
    .filter-label {
      font-weight: 500;
      margin-bottom: 0.5rem;
      display: block;
    }
    
    .filter-btn {
      background-color: var(--accent-color);
      border: none;
      width: 100%;
      padding: 0.75rem;
      font-weight: 500;
    }
    
    .search-header {
      background: white;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .search-input {
      border: 2px solid #eee;
      border-radius: 6px;
      padding: 0.75rem;
    }
    
    .search-input:focus {
      border-color: var(--primary-color);
      box-shadow: none;
    }
    
    .sort-select {
      border: 2px solid #eee;
      border-radius: 6px;
      padding: 0.75rem;
    }
    
    .product-card {
      background: white;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      transition: transform 0.3s, box-shadow 0.3s;
      height: 100%;
    }
    
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .product-img {
      height: 180px;
      object-fit: cover;
      width: 100%;
    }
    
    .product-body {
      padding: 1rem;
    }
    
    .product-title {
      font-weight: 600;
      margin-bottom: 0.5rem;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .product-price {
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 0.25rem;
    }
    
    .product-meta {
      color: var(--light-text);
      font-size: 0.9rem;
      margin-bottom: 0.75rem;
    }
    
    .product-link {
      color: var(--accent-color);
      font-weight: 500;
      text-decoration: none;
    }
    
    .product-link:hover {
      text-decoration: underline;
    }
    
    .pagination {
      justify-content: center;
      margin-top: 2rem;
    }
    
    .page-item.active .page-link {
      background-color: var(--accent-color);
      border-color: var(--accent-color);
    }
    
    .page-link {
      color: var(--primary-color);
    }
    
    .footer {
      background-color: var(--primary-color);
      color: white;
      padding: 2rem 0;
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
    
    .dark-mode .filter-card,
    .dark-mode .search-header,
    .dark-mode .product-card {
      background-color: #1e1e1e;
      color: #e0e0e0;
    }
    
    .dark-mode .filter-title {
      color: #e0e0e0;
    }
    
    .dark-mode .search-input,
    .dark-mode .sort-select {
      background-color: #2d2d2d;
      border-color: #444;
      color: #e0e0e0;
    }
    
    .dark-mode .product-price {
      color: var(--secondary-color);
    }
    
    .dark-mode .product-meta {
      color: #aaa;
    }
  </style>
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="index.html">GadgetHub</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.html"><i class="fas fa-home me-1"></i> Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="post-ad.html"><i class="fas fa-plus-circle me-1"></i> Post Ad</a>
          </li>
        </ul>
        <div class="d-flex">
          <a href="login.php" class="btn btn-outline-primary"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- Search Results -->
  <section class="search-container">
    <div class="container">
      <div class="row">
        <!-- Filters Column -->
        <div class="col-lg-3 mb-4">
          <div class="filter-card">
            <h4 class="filter-title">Filters</h4>
            
            <div class="filter-section">
              <label class="filter-label">Category</label>
              <select class="form-select">
                <option>All Categories</option>
                <option>Mobile Phones</option>
                <option>Cars</option>
                <option>Electronics</option>
                <option>Property</option>
              </select>
            </div>
            
            <div class="filter-section">
              <label class="filter-label">Price Range</label>
              <div class="row g-2 mb-2">
                <div class="col">
                  <input type="number" class="form-control" placeholder="Min">
                </div>
                <div class="col">
                  <input type="number" class="form-control" placeholder="Max">
                </div>
              </div>
            </div>
            
            <div class="filter-section">
              <label class="filter-label">Location</label>
              <input type="text" class="form-control" placeholder="City or Area">
            </div>
            
            <div class="filter-section">
              <label class="filter-label">Condition</label>
              <select class="form-select">
                <option>Any</option>
                <option>New</option>
                <option>Used</option>
              </select>
            </div>
            
            <button class="btn filter-btn mt-3">Apply Filters</button>
          </div>
        </div>
        
        <!-- Results Column -->
        <div class="col-lg-9">
          <div class="search-header">
            <div class="row g-2">
              <div class="col-md-8">
                <input type="text" class="form-control search-input" placeholder="Search for ads...">
              </div>
              <div class="col-md-4">
                <select class="form-select sort-select">
                  <option>Sort by: Newest</option>
                  <option>Price: Low to High</option>
                  <option>Price: High to Low</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <!-- Product Card 1 -->
            <div class="col">
              <div class="product-card">
                <img src="https://images.unsplash.com/photo-1601784551446-20c9e07cdbdb?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" class="product-img" alt="iPhone 14 Pro">
                <div class="product-body">
                  <h5 class="product-title">iPhone 14 Pro 128GB</h5>
                  <p class="product-price">₹95,000</p>
                  <p class="product-meta"><i class="fas fa-map-marker-alt"></i> Delhi • Used</p>
                  <a href="product-detail.html" class="product-link">View Details <i class="fas fa-arrow-right"></i></a>
                </div>
              </div>
            </div>
            
            <!-- Product Card 2 -->
            <div class="col">
              <div class="product-card">
                <img src="https://images.unsplash.com/photo-1494972308805-463bc619d34e?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" class="product-img" alt="Honda City">
                <div class="product-body">
                  <h5 class="product-title">Honda City 2020 VX</h5>
                  <p class="product-price">₹7,80,000</p>
                  <p class="product-meta"><i class="fas fa-map-marker-alt"></i> Mumbai • Used</p>
                  <a href="product-detail.html" class="product-link">View Details <i class="fas fa-arrow-right"></i></a>
                </div>
              </div>
            </div>
            
            <!-- Product Card 3 -->
            <div class="col">
              <div class="product-card">
                <img src="https://images.unsplash.com/photo-1547721064-da6cfb341d50?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" class="product-img" alt="Samsung TV">
                <div class="product-body">
                  <h5 class="product-title">Samsung 55" Smart TV</h5>
                  <p class="product-price">₹45,000</p>
                  <p class="product-meta"><i class="fas fa-map-marker-alt"></i> Bangalore • New</p>
                  <a href="product-detail.html" class="product-link">View Details <i class="fas fa-arrow-right"></i></a>
                </div>
              </div>
            </div>
            
            <!-- Product Card 4 -->
            <div class="col">
              <div class="product-card">
                <img src="https://images.unsplash.com/photo-1584622650111-993a426fbf0a?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" class="product-img" alt="House">
                <div class="product-body">
                  <h5 class="product-title">3 BHK Apartment</h5>
                  <p class="product-price">₹85,00,000</p>
                  <p class="product-meta"><i class="fas fa-map-marker-alt"></i> Hyderabad • New</p>
                  <a href="product-detail.html" class="product-link">View Details <i class="fas fa-arrow-right"></i></a>
                </div>
              </div>
            </div>
            
            <!-- Product Card 5 -->
            <div class="col">
              <div class="product-card">
                <img src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" class="product-img" alt="Toyota Corolla">
                <div class="product-body">
                  <h5 class="product-title">Toyota Corolla 2019</h5>
                  <p class="product-price">₹9,50,000</p>
                  <p class="product-meta"><i class="fas fa-map-marker-alt"></i> Chennai • Used</p>
                  <a href="product-detail.html" class="product-link">View Details <i class="fas fa-arrow-right"></i></a>
                </div>
              </div>
            </div>
            
            <!-- Product Card 6 -->
            <div class="col">
              <div class="product-card">
                <img src="https://images.unsplash.com/photo-1593642632823-8f785ba67e45?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60" class="product-img" alt="Laptop">
                <div class="product-body">
                  <h5 class="product-title">Dell XPS 15 Laptop</h5>
                  <p class="product-price">₹1,25,000</p>
                  <p class="product-meta"><i class="fas fa-map-marker-alt"></i> Pune • New</p>
                  <a href="product-detail.html" class="product-link">View Details <i class="fas fa-arrow-right"></i></a>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Pagination -->
          <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination">
              <li class="page-item"><a class="page-link" href="#">Previous</a></li>
              <li class="page-item active"><a class="page-link" href="#">1</a></li>
              <li class="page-item"><a class="page-link" href="#">2</a></li>
              <li class="page-item"><a class="page-link" href="#">3</a></li>
              <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="col-lg-3 mb-4">
          <h5 class="mb-3">GadgetHub</h5>
          <p>Pakistan's largest marketplace to buy and sell anything from cars to mobile phones.</p>
          <div class="mt-3">
            <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
            <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
          </div>
        </div>
        <div class="col-6 col-md-3 col-lg-2 mb-4">
          <h5>Buy & Sell</h5>
          <a href="#">Mobile Phones</a>
          <a href="#">Cars</a>
          <a href="#">Electronics</a>
          <a href="#">Property</a>
          <a href="#">Furniture</a>
          <a href="#">Bikes</a>
        </div>
        <div class="col-6 col-md-3 col-lg-2 mb-4">
          <h5>Help & Support</h5>
          <a href="#">Help Center</a>
          <a href="#">Safety Tips</a>
          <a href="#">Contact Us</a>
          <a href="#">FAQs</a>
          <a href="#">Terms of Use</a>
          <a href="#">Privacy Policy</a>
        </div>
        <div class="col-6 col-md-3 col-lg-2 mb-4">
          <h5>About GadgetHub</h5>
          <a href="#">About Us</a>
          <a href="#">Careers</a>
          <a href="#">Blog</a>
          <a href="#">Press</a>
          <a href="#">Sitemap</a>
        </div>
        <div class="col-6 col-md-3 col-lg-3 mb-4">
          <h5>Contact Info</h5>
          <p><i class="fas fa-map-marker-alt me-2"></i> 123 Main Street, Lahore</p>
          <p><i class="fas fa-phone me-2"></i> +92 300 1234567</p>
          <p><i class="fas fa-envelope me-2"></i> info@gadgethub.com</p>
        </div>
      </div>
      <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
      <div class="text-center">
        <p class="mb-0">© 2025 GadgetHub. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <!-- Theme Toggle Button -->
  <button class="theme-toggle" onclick="toggleTheme()">
    <i class="fas fa-moon"></i>
  </button>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Theme toggle functionality
    function toggleTheme() {
      document.body.classList.toggle('dark-mode');
      const icon = document.querySelector('.theme-toggle i');
      if (document.body.classList.contains('dark-mode')) {
        icon.classList.remove('fa-moon');
        icon.classList.add('fa-sun');
      } else {
        icon.classList.remove('fa-sun');
        icon.classList.add('fa-moon');
      }
    }
  </script>
</body>
</html>