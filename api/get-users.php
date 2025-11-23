<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
include_once 'config.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT id, full_name, email, is_verified, created_at FROM users ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $users = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $users[] = $row;
    }
    
    echo json_encode(['success' => true, 'users' => $users]);
    
} catch (PDOException $exception) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $exception->getMessage()]);
}
?>