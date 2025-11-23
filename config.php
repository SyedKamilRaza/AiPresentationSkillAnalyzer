<?php
// ====================
// CONFIG.PHP - AI Presentation Skill Analyzer
// ====================

// Load PHPMailer (via Composer)
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// ====================
// Database configuration
// ====================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'presentation_analyzer');

// ====================
// Email configuration
// ====================
// ⚠️ Fill these two values properly:
define('SMTP_USERNAME', 'kamilshah52645bps@gmail.com'); // your Gmail
define('SMTP_PASSWORD', 'ader luty vawq pcbh'); // your Google App Password

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('FROM_EMAIL', SMTP_USERNAME);
define('FROM_NAME', 'AI Presentation Skill Analyzer');

// ====================
// Website URL
// ====================
define('SITE_URL', 'http://localhost/AiPresentationSkillAnalyzer');

// ====================
// Database Connection
// ====================
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// ====================
// Redirect Function
// ====================
function redirect($url, $type = 'success', $message = null) {
    if ($message) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    header("Location: $url");
    exit();
}

// ====================
// Message Display Function
// ====================
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        $class = $type === 'error' ? 'alert-danger' : 'alert-success';
        echo '<div class="alert ' . $class . ' alert-dismissible fade show" role="alert">';
        echo $_SESSION['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['message'], $_SESSION['message_type']);
    }
}

// ====================
// PHPMailer - Send Verification Email
// ====================
function sendVerificationEmail($email, $full_name, $token) {
    $mail = new PHPMailer(true);

    try {
        // SMTP setup
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port = SMTP_PORT;

        // Sender info
        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($email, $full_name);

        // Email format
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - AI Presentation Skill Analyzer';

        // Verification link
        $verification_link = SITE_URL . "/verify.php?token=" . $token;

        // Email content
        $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 20px; }
                    .container { max-width: 600px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
                    .header { text-align: center; margin-bottom: 30px; }
                    .btn { display: inline-block; padding: 12px 30px; background-color: #212529; color: white; text-decoration: none; border-radius: 8px; margin: 20px 0; }
                    .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; color: #666; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>AI Presentation Skill Analyzer</h2>
                    </div>
                    <h3>Hello " . htmlspecialchars($full_name) . ",</h3>
                    <p>Welcome to <b>AI Presentation Skill Analyzer!</b> Please verify your email address to start using your account.</p>
                    <div style='text-align: center;'>
                        <a href='$verification_link' class='btn'>Verify Email Address</a>
                    </div>
                    <p>Or copy and paste this link in your browser:<br>
                    <a href='$verification_link'>$verification_link</a></p>
                    <div class='footer'>
                        <p><strong>This link will expire in 24 hours.</strong></p>
                        <p>If you didn't create an account, please ignore this email.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
     echo "Mailer Error: " . $mail->ErrorInfo;
exit();

    }
}
?>
