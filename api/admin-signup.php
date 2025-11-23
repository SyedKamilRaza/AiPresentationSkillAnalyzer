<?php
// api/admin-signup.php
include_once 'config.php';
setupCORS();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method');
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    sendResponse(false, 'Invalid JSON input');
}

// Validate required fields
$required_fields = ['firstName', 'lastName', 'email', 'password', 'organization', 'adminCode'];
foreach ($required_fields as $field) {
    if (!isset($input[$field]) || empty(trim($input[$field]))) {
        sendResponse(false, "Missing required field: $field");
    }
}

$firstName = trim($input['firstName']);
$lastName = trim($input['lastName']);
$email = trim($input['email']);
$password = $input['password'];
$organization = trim($input['organization']);
$adminCode = trim($input['adminCode']);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        sendResponse(false, 'Database connection failed');
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendResponse(false, 'Invalid email format');
    }

    // Validate password strength
    if (strlen($password) < 8) {
        sendResponse(false, 'Password must be at least 8 characters long');
    }

    // Check if email already exists
    $check_email_query = "SELECT id FROM admin_users WHERE email = :email";
    $check_email_stmt = $db->prepare($check_email_query);
    $check_email_stmt->bindParam(':email', $email);
    $check_email_stmt->execute();

    if ($check_email_stmt->rowCount() > 0) {
        sendResponse(false, 'Email already registered');
    }

    // Validate admin code
    $valid_admin_codes = ['ADMIN2024', 'PRESENTATIONMASTER', 'ANALYZERACCESS'];
    if (!in_array($adminCode, $valid_admin_codes)) {
        sendResponse(false, 'Invalid admin access code');
    }

    // Hash password
    $hashed_password = hashPassword($password);

    // Insert new admin user
    $insert_query = "INSERT INTO admin_users 
                    (first_name, last_name, email, password_hash, organization, admin_code) 
                    VALUES 
                    (:first_name, :last_name, :email, :password_hash, :organization, :admin_code)";

    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':first_name', $firstName);
    $insert_stmt->bindParam(':last_name', $lastName);
    $insert_stmt->bindParam(':email', $email);
    $insert_stmt->bindParam(':password_hash', $hashed_password);
    $insert_stmt->bindParam(':organization', $organization);
    $insert_stmt->bindParam(':admin_code', $adminCode);

    if ($insert_stmt->execute()) {
        // Get the newly created user ID
        $user_id = $db->lastInsertId();
        
        // Start session and store user data
        startSecureSession();
        $_SESSION['admin_id'] = $user_id;
        $_SESSION['admin_email'] = $email;
        $_SESSION['admin_name'] = $firstName . ' ' . $lastName;
        $_SESSION['admin_organization'] = $organization;
        $_SESSION['logged_in'] = true;
        
        sendResponse(true, 'Admin account created successfully', [
            'userId' => $user_id,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $email,
            'organization' => $organization
        ]);
    } else {
        sendResponse(false, 'Failed to create admin account');
    }

} catch (PDOException $exception) {
    error_log("Database error in admin signup: " . $exception->getMessage());
    sendResponse(false, 'Database error occurred');
} catch (Exception $exception) {
    error_log("Error in admin signup: " . $exception->getMessage());
    sendResponse(false, 'An error occurred');
}
?>