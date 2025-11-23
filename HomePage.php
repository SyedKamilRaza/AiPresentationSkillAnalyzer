<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect('login.php', 'error', 'Please login to access this page!');
}

// Get user information
$stmt = $pdo->prepare("SELECT full_name, email, created_at FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Sample stats
$total_presentations = 12;
$best_score = 89;
$average_wpm = 145;
$last_session_score = 74;
$monthly_progress = 78;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>AI Presentation Analyzer - Dashboard</title>
  <style>
    :root {
      --gold: #d4af37;
      --gold-dark: #b8860b;
      --black: #0c0c0c;
      --white: #ffffff;
    }

    body {
      margin: 0;
      font-family: "Segoe UI", Arial, sans-serif;
      background: linear-gradient(135deg, var(--black) 0%, #1b1b1b 100%);
      color: var(--white);
      min-height: 100vh;
    }

    header {
      background: var(--black);
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      padding: 15px 25px;
      box-shadow: 0 2px 15px rgba(212,175,55,0.2);
      border-bottom: 1px solid rgba(212,175,55,0.3);
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 22px;
      font-weight: 700;
      color: var(--gold);
    }

    .logo img {
      height: 80px;
      width: auto;
    }

    .user-controls {
      display: flex;
      align-items: center;
      gap: 10px;
      flex-wrap: wrap;
      color: var(--white);
    }

    .btn {
      display: inline-block;
      padding: 8px 16px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      font-size: 14px;
      text-decoration: none;
      font-weight: 600;
      transition: 0.3s;
      text-align: center;
    }

    .btn-dark {
      background: var(--gold);
      color: var(--black);
    }

    .btn-dark:hover {
      background: var(--gold-dark);
      color: var(--white);
    }

    .btn-light {
      background: transparent;
      border: 1px solid var(--gold);
      color: var(--gold);
    }

    .btn-light:hover {
      background: var(--gold);
      color: var(--black);
    }

    .container {
      max-width: 1200px;
      margin: 30px auto;
      padding: 0 20px;
    }

    .welcome {
      background: #1b1b1b;
      border-radius: 12px;
      padding: 25px;
      display: flex;
      align-items: center;
      gap: 20px;
      flex-wrap: wrap;
      box-shadow: 0 2px 15px rgba(212,175,55,0.15);
      border: 1px solid rgba(212,175,55,0.3);
    }

    .welcome h2 {
      color: var(--gold);
      margin: 0;
    }

    .welcome p, .welcome small {
      color: #ccc;
    }

    .main-layout {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 25px;
      margin-top: 25px;
    }

    .stat-card, .recent, .sidebar-card {
      background: #141414;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 2px 10px rgba(212,175,55,0.1);
      border: 1px solid rgba(212,175,55,0.2);
    }

    .stat-card h3 {
      color: var(--gold);
      font-size: 28px;
      margin: 0;
    }

    .stat-card p {
      color: #ccc;
    }

    .recent-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid rgba(255,255,255,0.1);
    }

    .recent-item strong {
      color: var(--white);
    }

    .recent-item span {
      color: #ccc;
      font-size: 13px;
    }

    .progress-bar {
      height: 8px;
      background: #2b2b2b;
      border-radius: 6px;
      margin: 8px 0;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      width: <?php echo $monthly_progress; ?>%;
      background: var(--gold);
    }

    .dashboard-logo {
      display: flex;
      justify-content: center;
      margin: 30px 0;
    }

    .dashboard-logo img {
      height: 130px;
      width: auto;
    }

    .alert-success {
      background: rgba(212,175,55,0.1);
      border: 1px solid rgba(212,175,55,0.3);
      color: var(--gold);
      padding: 12px;
      border-radius: 10px;
      margin-bottom: 20px;
    }

    .footer {
      background: var(--black);
      padding: 30px 0;
      text-align: center;
      color: #ccc;
      border-top: 1px solid rgba(212,175,55,0.3);
      margin-top: 40px;
    }

    .footer span {
      color: var(--gold);
    }

    /* Quick Actions layout fix */
    .quick-actions {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    @media(max-width: 900px) {
      .main-layout {
        grid-template-columns: 1fr;
      }

      header {
        flex-direction: column;
        align-items: flex-start;
      }

      .user-controls {
        justify-content: flex-start;
      }

      .quick-actions {
        flex-direction: column;
        align-items: stretch;
      }

      .logo img {
        height: 70px;
      }
    }

    @media(max-width: 500px) {
      .logo {
        font-size: 18px;
      }
      .logo img {
        height: 60px;
      }
      .btn {
        font-size: 13px;
        padding: 6px 12px;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="logo.png" alt="AI Presentation Analyzer Logo">
      <span>AI Presentation Analyzer</span>
    </div>
    <div class="user-controls">
      <span>👤 <?php echo htmlspecialchars($user['full_name']); ?></span>
      <a href="settings.php" class="btn btn-light">Settings</a>
      <a href="logout.php" class="btn btn-dark">Logout</a>
    </div>
  </header>

  <div class="container">
    <?php 
    if (isset($_SESSION['message'])) {
        echo '<div class="alert-success">' . $_SESSION['message'] . '</div>';
        unset($_SESSION['message']);
    }
    ?>

    <div class="dashboard-logo">
      <img src="logo.png" alt="Logo">
    </div>

    <div class="welcome">
      <div style="width: 70px; height: 70px; border-radius: 50%; background: var(--gold); color: var(--black); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold;">
        <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
      </div>
      <div>
        <h2>Welcome back, <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>!</h2>
        <p>Ready to improve your presentation skills? Let’s analyze your next performance.</p>
        <small>📅 Member since <?php echo date('M Y', strtotime($user['created_at'])); ?> | 
          📊 <?php echo $total_presentations; ?> presentations | 
          ⭐ Avg: <?php echo $monthly_progress; ?>%</small>
      </div>
    </div>

    <div class="main-layout">
      <div>
        <div class="stat-card">
          <h3><?php echo $best_score; ?>%</h3>
          <p>🏆 Best Score</p>
        </div>
        <div class="stat-card">
          <h3><?php echo $average_wpm; ?></h3>
          <p>📊 Average WPM</p>
        </div>
        <div class="stat-card">
          <h3><?php echo $last_session_score; ?>%</h3>
          <p>🎤 Last Session</p>
        </div>

        <div class="recent">
          <h3 style="color: var(--gold);">Recent Presentations</h3>
          <div class="recent-item">
            <div>
              <strong>AI Business Impact</strong>
              <span>15:42 • 74%</span>
            </div>
            <button class="btn btn-light">View Report</button>
          </div>
          <div class="recent-item">
            <div>
              <strong>Q4 Sales Review</strong>
              <span>22:18 • 82%</span>
            </div>
            <button class="btn btn-light">View Report</button>
          </div>
          <div class="recent-item">
            <div>
              <strong>Product Launch Strategy</strong>
              <span>18:55 • 89%</span>
            </div>
            <button class="btn btn-light">View Report</button>
          </div>
        </div>
      </div>

      <div>
        <div class="sidebar-card">
          <h3 style="color: var(--gold);">Quick Actions</h3>
          <div class="quick-actions">
            <a href="LivePresentationPage.html" class="btn btn-dark">Start Live</a>
            <a href="upload-recording.php" class="btn btn-light">Upload Recording</a>
            <a href="reports.php" class="btn btn-light">View Reports</a>
          </div>
        </div>

        <div class="sidebar-card">
          <h3 style="color: var(--gold);">Monthly Progress</h3>
          <p>Presentations: 4/5</p>
          <div class="progress-bar">
            <div class="progress-fill"></div>
          </div>
          <small style="color:#ccc;">✔ Almost at your monthly goal!</small>
        </div>

        <div class="sidebar-card">
          <h3 style="color: var(--gold);">Today’s Tip</h3>
          <p>Practice the “Rule of Three” — organize main points in groups of three for better retention.</p>
        </div>
      </div>
    </div>
  </div>

  <footer class="footer">
    <p>© <?php echo date('Y'); ?> <span>AI Presentation Analyzer</span>. All rights reserved.</p>
  </footer>
</body>
</html>
