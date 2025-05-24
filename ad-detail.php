<?php
session_start();
?>

<?php include 'partials/header.php'; ?>

  <div class="product-container">
    <div class="container">
      <div class="row">
        <!-- Product Gallery -->
        <div class="col-md-7">
          <div class="product-gallery">
            <img src="https://via.placeholder.com/600x400" alt="Main Product Image" class="main-image" id="mainImage">
            <div class="thumbnail-container">
              <img src="https://via.placeholder.com/80" alt="Thumbnail 1" class="thumbnail active" onclick="changeImage('https://via.placeholder.com/600x400')">
              <img src="https://via.placeholder.com/80" alt="Thumbnail 2" class="thumbnail" onclick="changeImage('https://via.placeholder.com/600x401')">
              <img src="https://via.placeholder.com/80" alt="Thumbnail 3" class="thumbnail" onclick="changeImage('https://via.placeholder.com/600x402')">
              <img src="https://via.placeholder.com/80" alt="Thumbnail 4" class="thumbnail" onclick="changeImage('https://via.placeholder.com/600x403')">
              <img src="https://via.placeholder.com/80" alt="Thumbnail 5" class="thumbnail" onclick="changeImage('https://via.placeholder.com/600x404')">
            </div>
          </div>
        </div>

        <!-- Product Details -->
        <div class="col-md-5">
          <div class="product-details">
            <h1 class="product-title">iPhone 14 Pro</h1>
            <p class="product-price">PKR 250,000</p>

            <div class="product-meta">
              <div class="meta-item"><i class="fas fa-map-marker-alt"></i> Lahore, Punjab</div>
              <div class="meta-item"><i class="fas fa-clock"></i> 2 hours ago</div>
            </div>

            <button class="btn btn-primary action-btn"><i class="fas fa-comment-alt me-2"></i>Chat with Seller</button>
            <button class="btn btn-outline-primary action-btn"><i class="fas fa-phone-alt me-2"></i>Show Phone Number</button>

            <hr>

            <h2 class="section-title">Description</h2>
            <div class="product-description">
              <p>Selling my iPhone 14 Pro, 128GB, Deep Purple. It's in excellent condition, used for only 3 months. No scratches or dents. Comes with original box and cable. Battery health is 99%. Reason for selling: Upgrading to the new model.</p>
              <p>Features:</p>
              <ul>
                <li>128GB Storage</li>
                <li>Deep Purple Color</li>
                <li>Excellent Condition (99% Battery Health)</li>
                <li>Comes with Box and Cable</li>
                <li>Purchased in Pakistan</li>
              </ul>
            </div>

            <hr>

            <h2 class="section-title">Specifications</h2>
            <table class="specs-table">
              <tr>
                <td>Condition</td>
                <td>Used</td>
              </tr>
              <tr>
                <td>Brand</td>
                <td>Apple</td>
              </tr>
              <tr>
                <td>Model</td>
                <td>iPhone 14 Pro</td>
              </tr>
              <tr>
                <td>Storage</td>
                <td>128GB</td>
              </tr>
              <tr>
                <td>Color</td>
                <td>Deep Purple</td>
              </tr>
            </table>

            <!-- Seller Info -->
            <div class="seller-card">
              <div class="seller-header">
                <img src="https://via.placeholder.com/50" alt="Seller Avatar" class="seller-avatar">
                <div>
                  <h5 class="seller-name">Ali Hassan</h5>
                  <p class="seller-meta">Member since Jan 2022</p>
                </div>
              </div>
              <button class="btn btn-outline-secondary w-100"><i class="fas fa-user me-2"></i>View Profile</button>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Similar Products Section -->
  <section class="similar-products">
    <div class="container">
      <h2 class="section-title">Similar Ads</h2>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
        <!-- Similar Product Card 1 -->
        <div class="col">
          <div class="product-card">
            <img src="https://via.placeholder.com/400x300" class="card-img-top product-card-img" alt="Product Image">
            <div class="product-card-body">
              <h5 class="card-title">Samsung Galaxy S21 Ultra</h5>
              <p class="card-text product-price">PKR 180,000</p>
              <p class="card-text text-muted"><small><i class="fas fa-map-marker-alt me-1"></i>Karachi, Sindh</small></p>
              <a href="#" class="stretched-link"></a>
            </div>
          </div>
        </div>

        <!-- Similar Product Card 2 -->
        <div class="col">
          <div class="product-card">
            <img src="https://via.placeholder.com/400x300" class="card-img-top product-card-img" alt="Product Image">
            <div class="product-card-body">
              <h5 class="card-title">Google Pixel 7 Pro</h5>
              <p class="card-text product-price">PKR 160,000</p>
              <p class="card-text text-muted"><small><i class="fas fa-map-marker-alt me-1"></i>Islamabad, ICT</small></p>
              <a href="#" class="stretched-link"></a>
            </div>
          </div>
        </div>

        <!-- Similar Product Card 3 -->
        <div class="col">
          <div class="product-card">
            <img src="https://via.placeholder.com/400x300" class="card-img-top product-card-img" alt="Product Image">
            <div class="product-card-body">
              <h5 class="card-title">OnePlus 10 Pro</h5>
              <p class="card-text product-price">PKR 120,000</p>
              <p class="card-text text-muted"><small><i class="fas fa-map-marker-alt me-1"></i>Lahore, Punjab</small></p>
              <a href="#" class="stretched-link"></a>
            </div>
          </div>
        </div>

        <!-- Similar Product Card 4 -->
        <div class="col">
          <div class="product-card">
            <img src="https://via.placeholder.com/400x300" class="card-img-top product-card-img" alt="Product Image">
            <div class="product-card-body">
              <h5 class="card-title">Xiaomi 13 Pro</h5>
              <p class="card-text product-price">PKR 130,000</p>
              <p class="card-text text-muted"><small><i class="fas fa-map-marker-alt me-1"></i>Karachi, Sindh</small></p>
              <a href="#" class="stretched-link"></a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include 'partials/footer.php'; ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function changeImage(imageSrc) {
      document.getElementById('mainImage').src = imageSrc;
      // Optional: update active class on thumbnails
      const thumbnails = document.querySelectorAll('.thumbnail');
      thumbnails.forEach(thumb => thumb.classList.remove('active'));
      event.target.classList.add('active');
    }

    // Theme toggle functionality
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