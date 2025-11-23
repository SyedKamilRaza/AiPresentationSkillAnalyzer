<?php
// api/check-auth.php
include_once 'config.php';
setupCORS();

startSecureSession();

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    sendResponse(true, 'User is authenticated', [
        'userId' => $_SESSION['admin_id'],
        'email' => $_SESSION['admin_email'],
        'name' => $_SESSION['admin_name'],
        'organization' => $_SESSION['admin_organization']
    ]);
} else {
    sendResponse(false, 'User not authenticated');
}
?>