<?php
// api/config.php

class Database {
    private $host = "localhost";
    private $db_name = "presentation_analyzer";
    private $username = "root";  // Change to your MySQL username
    private $password = "";      // Change to your MySQL password
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            return false;
        }
        return $this->conn;
    }
}

// Password hashing functions
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hashedPassword) {
    return password_verify($password, $hashedPassword);
}

// Response helper function
function sendResponse($success, $message, $data = []) {
    header('Content-Type: application/json');
    http_response_code($success ? 200 : 400);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// CORS headers
function setupCORS() {
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');
    
    // Handle preflight OPTIONS request
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        exit(0);
    }
}

// Start session securely
function startSecureSession() {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}
?>