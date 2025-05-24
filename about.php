<?php
session_start();
?>
<?php include 'partials/header.php'; ?>

  <!-- Hero Section -->
  <section class="about-hero">
    <div class="container">
      <h1>About GadgetHub</h1>
      <p>Pakistan\'s trusted marketplace for buying and selling electronics, vehicles, property, and more</p>
    </div>
  </section>

  <!-- About Content -->
  <section class="about-section">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto">
          <div class="about-card">
            <h2 class="section-title">Our Story</h2>
            <p>Founded in 2020, GadgetHub began as a small project to help people buy and sell used electronics safely. Today, we\'ve grown into Pakistan\'s most trusted online classifieds platform with thousands of active users.</p>
            <p>Our mission is to empower local communities by connecting people through a safe and user-friendly online marketplace. We believe in making commerce accessible to everyone while maintaining the highest standards of security and reliability.</p>
          </div>
        </div>
      </div>

      <!-- Stats Section -->
      <div class="row mt-4">
        <div class="col-md-3">
          <div class="stats-item">
            <div class="stats-number">50K+</div>
            <div class="stats-label">Active Users</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-item">
            <div class="stats-number">100K+</div>
            <div class="stats-label">Listings</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-item">
            <div class="stats-number">1M+</div>
            <div class="stats-label">Monthly Visits</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stats-item">
            <div class="stats-number">5K+</div>
            <div class="stats-label">Categories</div>
          </div>
        </div>
      </div>

      <!-- Mission Section -->
      <div class="row mt-5">
        <div class="col-lg-8 mx-auto">
          <div class="about-card">
            <h2 class="section-title">Our Mission</h2>
            <p>To create a seamless and secure platform where every Pakistani can easily buy and sell anything, fostering economic growth and community connection.</p>
          </div>
        </div>
      </div>

      <!-- Values Section -->
      <div class="row mt-5">
        <div class="col-lg-10 mx-auto">
          <h2 class="section-title text-center">Our Values</h2>
          <div class="row">
            <div class="col-md-4">
              <div class="about-card text-center">
                <i class="fas fa-shield-alt feature-icon"></i>
                <h3>Trust & Safety</h3>
                <p>We prioritize the security and privacy of our users, building a platform you can rely on.</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="about-card text-center">
                <i class="fas fa-users feature-icon"></i>
                <h3>Community Focused</h3>
                <p>We aim to connect people and empower local trade, strengthening communities across Pakistan.</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="about-card text-center">
                <i class="fas fa-lightbulb feature-icon"></i>
                <h3>Innovation</h3>
                <p>We constantly strive to improve our platform and user experience with new features and technologies.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Team Section -->
      <div class="row mt-5">
        <div class="col-lg-10 mx-auto">
          <h2 class="section-title text-center">Meet the Team</h2>
          <div class="row">
            <div class="col-md-4">
              <div class="team-card">
                <img src="https://via.placeholder.com/150" alt="Team Member 1" class="team-img">
                <h4>Jane Doe</h4>
                <p>Founder & CEO</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="team-card">
                <img src="https://via.placeholder.com/150" alt="Team Member 2" class="team-img">
                <h4>John Smith</h4>
                <p>Head of Operations</p>
              </div>
            </div>
            <div class="col-md-4">
              <div class="team-card">
                <img src="https://via.placeholder.com/150" alt="Team Member 3" class="team-img">
                <h4>Emily Johnson</h4>
                <p>Lead Developer</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php include 'partials/footer.php'; ?>

  <button id="theme-toggle" class="theme-toggle">
    <i class="fas fa-moon"></i>
  </button>

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