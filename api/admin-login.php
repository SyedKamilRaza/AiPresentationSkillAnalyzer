<?php
// api/admin-login.php
include_once 'config.php';
setupCORS();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method');
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['email']) || !isset($input['password'])) {
    sendResponse(false, 'Email and password are required');
}

$email = trim($input['email']);
$password = $input['password'];

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        sendResponse(false, 'Database connection failed');
    }

    // Find user by email
    $query = "SELECT id, first_name, last_name, email, password_hash, organization, is_active 
              FROM admin_users 
              WHERE email = :email";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        sendResponse(false, 'Invalid email or password');
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if account is active
    if (!$user['is_active']) {
        sendResponse(false, 'Account is deactivated. Please contact administrator.');
    }

    // Verify password
    if (!verifyPassword($password, $user['password_hash'])) {
        sendResponse(false, 'Invalid email or password');
    }

    // Update last login time
    $update_query = "UPDATE admin_users SET last_login = CURRENT_TIMESTAMP WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':id', $user['id']);
    $update_stmt->execute();

    // Start secure session
    startSecureSession();
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_email'] = $user['email'];
    $_SESSION['admin_name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['admin_organization'] = $user['organization'];
    $_SESSION['logged_in'] = true;

    sendResponse(true, 'Login successful', [
        'userId' => $user['id'],
        'firstName' => $user['first_name'],
        'lastName' => $user['last_name'],
        'email' => $user['email'],
        'organization' => $user['organization']
    ]);

} catch (PDOException $exception) {
    error_log("Database error in admin login: " . $exception->getMessage());
    sendResponse(false, 'Database error occurred');
}
?>