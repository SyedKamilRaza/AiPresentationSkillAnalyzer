<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validation
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required!";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email address is required!";
    }
    
    if (empty($password) || strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long!";
    }
    
    if (empty($errors)) {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            $errors[] = "Email already registered!";
        } else {
            // Create new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $verification_token = bin2hex(random_bytes(50));
            
            $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, verification_token) VALUES (?, ?, ?, ?)");
            
            if ($stmt->execute([$full_name, $email, $hashed_password, $verification_token])) {
                // Send verification email
                if (sendVerificationEmail($email, $full_name, $verification_token)) {
                    redirect('login.php', 'success', 'Registration successful! Please check your email to verify your account.');
                } else {
                    $errors[] = "Registration successful but failed to send verification email. Please contact support.";
                }
            } else {
                $errors[] = "Registration failed! Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up | AI Presentation Analyzer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

  <style>
    :root {
      --gold: #d4af37; /* softer metallic gold */
      --black: #000;
      --white: #fff;
    }

    body {
      background: linear-gradient(135deg, #000 0%, #111 100%);
      color: var(--white);
      font-family: 'Poppins', sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    /* Navbar */
    .navbar {
      background-color: #000;
      border-bottom: 1px solid var(--gold);
    }

    .navbar-brand {
      display: flex;
      align-items: center;
      font-weight: 700;
      color: var(--gold) !important;
    }

    .navbar-brand img {
      height: 70px; /* increased size */
      margin-right: 12px;
    }

    .navbar a {
      color: var(--white) !important;
      transition: color 0.3s ease;
    }

    .navbar a:hover {
      color: var(--gold) !important;
    }

    /* Signup Section */
    .signup-container {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 0;
    }

    .signup-card {
      max-width: 480px;
      width: 100%;
      background: #111;
      border: 1px solid rgba(212, 175, 55, 0.4);
      border-radius: 16px;
      padding: 50px;
      box-shadow: 0 0 25px rgba(212, 175, 55, 0.2);
    }

    .signup-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .signup-header img {
      height: 90px; /* increased logo size */
      margin-bottom: 15px;
    }

    .signup-header h3 {
      color: var(--gold);
      font-weight: 700;
    }

    .signup-header p {
      color: #ccc;
    }

    .form-control {
      background-color: transparent;
      border: 1px solid rgba(212, 175, 55, 0.5);
      color: var(--white);
      padding: 12px;
      border-radius: 10px;
    }

    .form-control:focus {
      background-color: transparent;
      border-color: var(--gold);
      box-shadow: 0 0 8px rgba(212, 175, 55, 0.4);
      color: var(--white);
    }

    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.85);
    }

    .btn-primary {
      background-color: var(--gold);
      border: none;
      color: var(--black);
      font-weight: 600;
      border-radius: 10px;
      padding: 12px;
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #e3c45b;
      box-shadow: 0 0 12px var(--gold);
    }

    .social-btn {
      border: 1px solid var(--gold);
      background: transparent;
      color: var(--gold);
      padding: 10px;
      border-radius: 10px;
      width: 100%;
      margin-bottom: 10px;
      transition: all 0.3s ease;
    }

    .social-btn:hover {
      background-color: var(--gold);
      color: var(--black);
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
      border-bottom: 1px solid rgba(212, 175, 55, 0.4);
    }

    .divider span {
      padding: 0 10px;
      color: var(--gold);
      font-size: 14px;
    }

    .signup-footer {
      text-align: center;
      margin-top: 20px;
      color: #ccc;
    }

    .signup-footer a {
      color: var(--gold);
      text-decoration: none;
      font-weight: 500;
    }

    .signup-footer a:hover {
      text-decoration: underline;
    }

    .benefits-box {
      background-color: rgba(255, 255, 255, 0.05);
      border-radius: 10px;
      padding: 15px;
      margin-top: 20px;
      text-align: left;
    }

    .benefits-box p {
      margin-bottom: 10px;
      color: #ddd;
    }

    .benefits-box i {
      color: var(--gold);
      margin-right: 8px;
    }

    .alert {
      border-radius: 10px;
    }

    /* Footer */
    footer {
      background: #000;
      border-top: 1px solid rgba(212, 175, 55, 0.4);
      padding: 40px 0;
      text-align: center;
      font-size: 1rem;
      color: #ddd;
    }

    footer a {
      color: var(--gold);
      text-decoration: none;
      font-weight: 500;
    }

    footer a:hover {
      text-decoration: underline;
      color: #e3c45b;
    }

    @media (max-width: 576px) {
      .signup-card {
        padding: 35px;
      }
      .signup-header img {
        height: 75px;
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
        <a href="login.php" class="btn btn-primary ms-2">Sign In</a>
      </div>
    </div>
  </nav>

  <!-- Signup Form -->
  <div class="signup-container">
    <div class="signup-card">
      <div class="signup-header">
        <img src="logo.png" alt="AI Presentation Analyzer Logo">
        <h3>Create Your Account</h3>
        <p>Sign up to get started with AI-powered presentation feedback</p>
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
          <input type="text" class="form-control" name="full_name" placeholder="Full Name" 
            value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
        </div>
        <div class="mb-3">
          <input type="email" class="form-control" name="email" placeholder="Email Address" 
            value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        </div>
        <div class="mb-3">
          <input type="password" class="form-control" name="password" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Create Account</button>
      </form>

      <div class="divider">
        <span>Or sign up with</span>
      </div>

      <button type="button" class="social-btn"><i class="bi bi-google"></i> Google</button>
      <button type="button" class="social-btn"><i class="bi bi-microsoft"></i> Microsoft</button>

      <div class="signup-footer">
        <p>Already have an account? <a href="login.php">Sign in</a></p>
      </div>

      <div class="benefits-box">
        <p><i class="bi bi-graph-up"></i> Get instant AI feedback on your presentations</p>
        <p><i class="bi bi-camera-video"></i> Analyze speaking pace, clarity, and body language</p>
        <p><i class="bi bi-clock"></i> Track your progress over time</p>
        <p><i class="bi bi-star"></i> Free trial – no credit card required</p>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <div class="container">
      <p>© 2025 AI Presentation Analyzer. All rights reserved.</p>
      <p>
        <a href="#">About</a> | 
        <a href="#">Support</a> | 
        <a href="#">Contact</a>
      </p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
