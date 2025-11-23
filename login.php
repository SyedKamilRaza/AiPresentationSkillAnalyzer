<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']) ? 1 : 0;
    
    $errors = [];
    
    if (empty($email) || empty($password)) {
        $errors[] = "Both email and password are required!";
    } else {
        $stmt = $pdo->prepare("SELECT id, full_name, email, password, is_verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            if (password_verify($password, $user['password'])) {
                if ($user['is_verified'] == 1) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['full_name'];
                    
                    if ($remember_me) {
                        setcookie('user_email', $email, time() + (7 * 24 * 60 * 60), "/");
                    }
                    
                    redirect('HomePage.php', 'success', 'Login successful! Welcome back, ' . htmlspecialchars($user['full_name']) . '!');
                } else {
                    $errors[] = "Please verify your email address before logging in! <a href='resend-verification.php?email=" . urlencode($email) . "' class='alert-link'>Resend verification email</a>";
                }
            } else {
                $errors[] = "Invalid password!";
            }
        } else {
            $errors[] = "No account found with this email!";
        }
    }
}

$remembered_email = '';
if (isset($_COOKIE['user_email'])) {
    $remembered_email = $_COOKIE['user_email'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | AI Presentation Analyzer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <style>
    :root {
      --gold: #d4af37;
      --gold-dark: #b8860b;
      --black: #121212;
      --white: #ffffff;
    }

    body {
      background: linear-gradient(135deg, var(--black) 0%, #1f1f1f 100%);
      color: var(--white);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .navbar {
      background: var(--black);
      box-shadow: 0 2px 15px rgba(212,175,55,0.2);
    }

    .navbar-brand {
      display: flex;
      align-items: center;
      font-weight: 700;
      color: var(--gold) !important;
    }

    .navbar-brand img {
      height: 60px; /* Increased from 42px */
      margin-right: 10px;
    }

    .navbar a {
      color: var(--white) !important;
      transition: color 0.3s;
    }

    .navbar a:hover {
      color: var(--gold) !important;
    }

    .btn-dark {
      background-color: var(--gold);
      border: none;
      color: var(--black);
      font-weight: 600;
    }

    .btn-dark:hover {
      background-color: var(--gold-dark);
      color: var(--white);
    }

    .login-container {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 0;
    }

    .login-card {
      max-width: 480px; /* Slightly increased to accommodate larger logo */
      width: 100%;
      margin: 20px auto;
      background: #1b1b1b;
      color: var(--white);
      border-radius: 16px;
      padding: 40px;
      box-shadow: 0 0 25px rgba(212,175,55,0.2);
      border: 1px solid rgba(212,175,55,0.3);
    }

    .login-header img {
      height: 100px; /* Increased from 70px */
      margin-bottom: 15px;
    }

    .login-header h3 {
      font-weight: 700;
      color: var(--gold);
    }

  .form-control {
  background: #2b2b2b;
  border: 1px solid #3a3a3a;
  color: #ffffff !important; /* ✅ ensures typed text is white */
  border-radius: 10px;
  padding: 12px 15px;
}

.form-control:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 0.25rem rgba(212,175,55,0.25);
  background-color: #222;
  color: #ffffff !important; /* ✅ white text while focused */
}

/* White placeholder text */
.form-control::placeholder {
  color: #e0e0e0 !important;
  opacity: 1;
}


    .btn-primary {
      background: var(--gold);
      border: none;
      padding: 12px;
      font-weight: 600;
      border-radius: 10px;
      color: var(--black);
      transition: all 0.3s;
    }

    .btn-primary:hover {
      background: var(--gold-dark);
      color: var(--white);
    }

    .social-btn {
      border: 1px solid rgba(212,175,55,0.4);
      background: transparent;
      color: var(--gold);
      padding: 12px;
      border-radius: 10px;
      width: 100%;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      font-weight: 500;
      transition: 0.3s;
    }

    .social-btn:hover {
      background-color: var(--gold);
      color: var(--black);
      transform: translateY(-2px);
    }

    .alert {
      border-radius: 10px;
    }

    .form-check-label {
      color: var(--white);
    }

    .login-footer {
      text-align: center;
      margin-top: 25px;
      color: #ccc;
      font-size: 14px;
    }

    .login-footer a {
      color: var(--gold);
      text-decoration: none;
      font-weight: 500;
    }

    .login-footer a:hover {
      text-decoration: underline;
    }

    .benefits-box {
      background: #111;
      border: 1px solid rgba(212,175,55,0.4);
      border-radius: 12px;
      padding: 20px;
      margin-top: 25px;
    }

    .benefits-box i {
      color: var(--gold);
      margin-right: 10px;
    }

    .divider {
      display: flex;
      align-items: center;
      margin: 20px 0;
    }

    .divider::before,
    .divider::after {
      content: "";
      flex: 1;
      border-bottom: 1px solid rgba(255,255,255,0.2);
    }

    .divider span {
      padding: 0 15px;
      color: var(--gold);
      font-size: 14px;
    }

    /* Footer Styles */
    .main-footer {
      background: #0a0a0a;
      color: var(--white);
      padding: 40px 0 20px;
      border-top: 1px solid rgba(212,175,55,0.3);
    }

    .footer-logo {
      height: 50px;
      margin-bottom: 15px;
    }

    .footer-heading {
      color: var(--gold);
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    .footer-links {
      list-style: none;
      padding: 0;
    }

    .footer-links li {
      margin-bottom: 10px;
    }

    .footer-links a {
      color: #ccc;
      text-decoration: none;
      transition: color 0.3s;
    }

    .footer-links a:hover {
      color: var(--gold);
    }

    .social-icons {
      display: flex;
      gap: 15px;
      margin-top: 15px;
    }

    .social-icons a {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background: rgba(212,175,55,0.1);
      color: var(--gold);
      border-radius: 50%;
      transition: all 0.3s;
    }

    .social-icons a:hover {
      background: var(--gold);
      color: var(--black);
      transform: translateY(-3px);
    }

    .footer-bottom {
      border-top: 1px solid rgba(255,255,255,0.1);
      padding-top: 20px;
      margin-top: 30px;
      text-align: center;
      color: #888;
      font-size: 14px;
    }

    /* Mobile responsiveness */
    @media (max-width: 576px) {
      .navbar-brand img {
        height: 50px;
      }
      
      .login-header img {
        height: 80px;
      }
      
      .login-card {
        padding: 25px;
        margin: 10px;
      }
      
      .footer-logo {
        height: 40px;
      }
    }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <img src="logo.png" alt="AI Presentation Analyzer Logo">
        <span>AI Presentation Analyzer</span>
      </a>
      <div class="d-none d-md-block">
        <a href="#" class="text-decoration-none me-3">Features</a>
        <a href="#" class="text-decoration-none me-3">Pricing</a>
        <a href="#" class="text-decoration-none me-3">About</a>
        <a href="signup.php" class="btn btn-dark ms-2">Sign Up</a>
      </div>
      <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMobile">
        <span class="navbar-toggler-icon"></span>
      </button>
     
    </div>
  </nav>

  <!-- Login Container -->
  <div class="login-container">
    <div class="login-card">
      <div class="login-header text-center">
        <img src="logo.png" alt="AI Presentation Analyzer Logo">
        <h3>Welcome Back</h3>
        <p class="text-muted">Sign in to your account to continue</p>
      </div>

      <?php 
      if (!empty($errors)) {
          foreach ($errors as $error) {
              echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
              echo $error;
              echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
              echo '</div>';
          }
      }
      displayMessage();
      ?>

      <form method="POST" action="">
        <div class="mb-3">
          <input type="email" class="form-control" name="email" placeholder="Enter your email" 
                 value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : htmlspecialchars($remembered_email); ?>" required>
        </div>
        <div class="mb-3">
          <input type="password" class="form-control" name="password" placeholder="Enter your password" required>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="remember_me" id="remember_me" 
                   <?php echo (isset($_POST['remember_me']) || !empty($remembered_email)) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="remember_me">Remember me</label>
          </div>
          <a href="forgot-password.php" class="text-decoration-none text-warning">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2">Sign In</button>
      </form>

      <div class="divider">
        <span>Or continue with</span>
      </div>

      <button type="button" class="social-btn">
        <i class="bi bi-google"></i> Google
      </button>
      <button type="button" class="social-btn">
        <i class="bi bi-microsoft"></i> Microsoft
      </button>

      <div class="login-footer">
        <p>Don't have an account? <a href="signup.php">Sign up for free</a></p>
      </div>

      <div class="benefits-box">
        <p><i class="bi bi-check-circle-fill"></i> Free trial available</p>
        <p><i class="bi bi-check-circle-fill"></i> No credit card required</p>
        <p><i class="bi bi-check-circle-fill"></i> Instant AI feedback</p>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="main-footer">
    <div class="container">
      <div class="row">
        <div class="col-lg-4 mb-4">
          <img src="logo.png" alt="AI Presentation Analyzer Logo" class="footer-logo">
          <p class="text-muted">Transform your presentation skills with AI-powered feedback and analytics.</p>
          <div class="social-icons">
            <a href="#"><i class="bi bi-facebook"></i></a>
            <a href="#"><i class="bi bi-twitter"></i></a>
            <a href="#"><i class="bi bi-linkedin"></i></a>
            <a href="#"><i class="bi bi-instagram"></i></a>
          </div>
        </div>
        <div class="col-lg-2 col-md-4 mb-4">
          <h5 class="footer-heading">Product</h5>
          <ul class="footer-links">
            <li><a href="#">Features</a></li>
            <li><a href="#">Pricing</a></li>
            <li><a href="#">Use Cases</a></li>
            <li><a href="#">Updates</a></li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-4 mb-4">
          <h5 class="footer-heading">Resources</h5>
          <ul class="footer-links">
            <li><a href="#">Documentation</a></li>
            <li><a href="#">Tutorials</a></li>
            <li><a href="#">Blog</a></li>
            <li><a href="#">Support</a></li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-4 mb-4">
          <h5 class="footer-heading">Company</h5>
          <ul class="footer-links">
            <li><a href="#">About Us</a></li>
            <li><a href="#">Careers</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="#">Partners</a></li>
          </ul>
        </div>
        <div class="col-lg-2 col-md-12 mb-4">
          <h5 class="footer-heading">Legal</h5>
          <ul class="footer-links">
            <li><a href="#">Privacy Policy</a></li>
            <li><a href="#">Terms of Service</a></li>
            <li><a href="#">Cookie Policy</a></li>
            <li><a href="#">GDPR</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2025 AI Presentation Analyzer. All rights reserved.</p>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>