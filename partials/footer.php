  <!-- Footer -->
   <style>
    
   </style>
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
          <a href="search-result.html?category=Mobile+Phones">Mobile Phones</a>
          <a href="search-result.html?category=Cars">Cars</a>
          <a href="search-result.html?category=Electronics">Electronics</a>
          <a href="search-result.html?category=Property">Property</a>
          <a href="search-result.html">Furniture</a>
          <a href="search-result.html">Bikes</a>
        </div>
        <div class="col-6 col-md-3 col-lg-2 mb-4">
          <h5>Help & Support</h5>
          <a href="#">Help Center</a>
          <a href="#">Safety Tips</a>
          <a href="contact .html">Contact Us</a>
          <a href="#">FAQs</a>
          <a href="terms.html">Terms of Use</a>
          <a href="#">Privacy Policy</a>
        </div>
        <div class="col-6 col-md-3 col-lg-2 mb-4">
          <h5>About GadgetHub</h5>
          <a href="about.html">About Us</a>
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

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Simple theme toggle functionality
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
      
      // Add animation to cards when they come into view
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


