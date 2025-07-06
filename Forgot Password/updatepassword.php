<?php
// Start output buffering to catch any stray output
ob_start();

// Set headers first
header('Content-Type: application/json');

// Start session and enable error reporting
session_start();
error_reporting(0); // Disable error display in production

// Validate session
if (!isset($_SESSION['otp_verified']) || !$_SESSION['otp_verified'] || !isset($_SESSION['reset_email'])) {
    http_response_code(401);
    die(json_encode(['status' => 'error', 'message' => 'Session expired or invalid']));
}

// Database connection
include '../PHP File/db_connection.php';

// Process request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate input
        if (empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
            throw new Exception('Both password fields are required');
        }
        
        if ($_POST['new_password'] !== $_POST['confirm_password']) {
            throw new Exception('Passwords do not match');
        }
        
        $password = $_POST['new_password'];
        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters');
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        if ($hashedPassword === false) {
            throw new Exception('Failed to hash password');
        }

        // Update database
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        if (!$stmt || !$stmt->bind_param("ss", $hashedPassword, $_SESSION['reset_email']) || !$stmt->execute()) {
            throw new Exception('Failed to update password');
        }

        // Clean up
        unset($_SESSION['reset_email'], $_SESSION['otp_verified']);
        $conn->query("DELETE FROM otp WHERE email = '".$conn->real_escape_string($_SESSION['reset_email'])."'");

        // Successful response
        echo json_encode([
            'status' => 'success',
            'message' => 'Password updated successfully!'
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Method not allowed'
    ]);
}

// Clean any output buffer and exit
ob_end_flush();
exit;
?>