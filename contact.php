<?php
session_start();
?>
<?php include 'partials/header.php'; ?>

<style>
    /* Styles adapted from signup.php for form consistency */
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--light-bg);
      color: var(--dark-text);
      min-height: 100vh; /* Ensure body takes full height */
      display: flex; /* Use flexbox for layout */
      flex-direction: column; /* Stack children vertically */
    }

    .contact-container {
      flex: 1; /* Allow container to grow and take available space */
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 0;
    }
    
    .contact-card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.1);
      width: 100%;
      max-width: 450px; /* Slightly wider card like signup */
      padding: 2rem;
    }
    
    .section-title { /* Adapted from .signup-title */
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 1.5rem;
      text-align: center; /* Center the title */
       position: relative; /* Keep for consistency with other titles */
       padding-bottom: 0; /* Remove padding if any from original contact styles */
    }
     /* Remove the ::after pseudo-element from this specific title if it exists and is not wanted */
    .contact-card .section-title::after {
        content: none;
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

    .contact-btn {
       background-color: var(--accent-color);
      border: none;
      padding: 0.75rem;
      font-weight: 600;
      width: 100%;
    }
    
    .contact-btn:hover {
      background-color: #e05d00;
    }
    
    .contact-info {
      text-align: center;
      margin-top: 2rem;
      /* Add card-like styling if desired, similar to seller-card in ad-detail or adapt contact-card */
       background: white;
       border-radius: 8px;
       padding: 2rem;
       box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    }
     
    .contact-method {
       margin-bottom: 2rem; /* Keep spacing */
    }

    .contact-info .section-title::after {
         content: none; /* Remove line under this title too */
    }
    
     /* Dark mode styles adapted for contact page elements */
    .dark-mode .contact-card,
    .dark-mode .contact-info {
      background-color: #1e1e1e;
      color: #e0e0e0;
    }
     
    .dark-mode .section-title,
     .dark-mode .form-label {
       color: #e0e0e0;
    }

     .dark-mode .form-control {
      background-color: #2d2d2d;
      border-color: #444;
      color: #e0e0e0;
    }
     
     /* Original styles from contact.php - keep or adjust as needed */
     .contact-hero {
      background: linear-gradient(rgba(0, 47, 52, 0.8), rgba(0, 47, 52, 0.8)), 
                  url('https://images.unsplash.com/photo-1551434678-e076c223a692?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
      background-size: cover;
      background-position: center;
      color: white;
      padding: 5rem 0;
      text-align: center;
      margin-bottom: 2rem; /* Add some space below hero */
    }
    
     .contact-hero h1 {
       font-weight: 700;
       margin-bottom: 1rem;
     }
    
     .contact-hero p {
       font-size: 1.2rem;
       max-width: 700px;
       margin: 0 auto;
     }
    
     .contact-icon {
       font-size: 2rem;
       color: var(--accent-color);
       margin-bottom: 1rem;
    }
    
   </style>

  <section class="contact-hero">
    <div class="container">
      <h1>Contact GadgetHub</h1>
      <p>We\'re here to help with any questions about our marketplace</p>
    </div>
  </section>

  <!-- Contact Content -->
  <section class="contact-container">
    <div class="container">
      <div class="row">
        <div class="col-lg-8 mx-auto">
          <div class="contact-card">
            <h2 class="section-title">Send Us a Message</h2>
            <form action="submit-contact.php" method="POST">
              <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>
              <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject">
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
              </div>
              <button type="submit" class="btn btn-primary contact-btn">Send Message</button>
            </form>
          </div>
        </div>
      </div>

      <div class="row mt-4">
        <div class="col-lg-8 mx-auto text-center contact-info">
          <h2 class="section-title">Or Reach Us Directly</h2>
          <div class="row">
            <div class="col-md-4 contact-method">
              <i class="fas fa-map-marker-alt contact-icon"></i>
              <h5>Visit Us</h5>
              <p>123 Main Street, Lahore, Pakistan</p>
            </div>
            <div class="col-md-4 contact-method">
              <i class="fas fa-phone contact-icon"></i>
              <h5>Call Us</h5>
              <p>+92 300 1234567</p>
            </div>
            <div class="col-md-4 contact-method">
              <i class="fas fa-envelope contact-icon"></i>
              <h5>Email Us</h5>
              <p>info@gadgethub.com</p>
            </div>
          </div>
        </div>
      </div>
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