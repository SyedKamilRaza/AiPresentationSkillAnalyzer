<?php
require_once 'config.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Check if token exists and is valid
    $stmt = $pdo->prepare("SELECT id, email, full_name FROM users WHERE verification_token = ? AND is_verified = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Mark user as verified
        $update_stmt = $pdo->prepare("UPDATE users SET is_verified = 1, verification_token = '' WHERE id = ?");
        
        if ($update_stmt->execute([$user['id']])) {
            redirect('login.php', 'success', 'Email verified successfully! You can now login.');
        } else {
            redirect('signup.php', 'error', 'Verification failed! Please try again.');
        }
    } else {
        redirect('signup.php', 'error', 'Invalid or expired verification token!');
    }
} else {
    redirect('signup.php', 'error', 'Invalid verification link!');
}
?>