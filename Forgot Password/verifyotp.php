<?php
// Start session and clear any admin session if this is for users
session_start();

// Clear admin session variables if they exist
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_user_id']);
unset($_SESSION['admin_username']);

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
include '../PHP File/db_connection.php';

// Check if email exists in session
if (empty($_SESSION['reset_email'])) {
    die(json_encode(['status' => 'error', 'message' => 'Session expired. Please request OTP again.']));
}

$email = $_SESSION['reset_email'];

// Check if OTP was submitted via POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die(json_encode(['status' => 'error', 'message' => 'Invalid request method. Please submit the form.']));
}

if (empty($_POST['otp'])) {
    die(json_encode(['status' => 'error', 'message' => 'OTP is required']));
}

$otp = trim($_POST['otp']);

// Get OTP from database
$stmt = $conn->prepare("SELECT otp, otp_expiry FROM otp WHERE email = ?");
if (!$stmt) {
    die(json_encode(['status' => 'error', 'message' => 'Database error']));
}

$stmt->bind_param("s", $email);
if (!$stmt->execute()) {
    die(json_encode(['status' => 'error', 'message' => 'Database error']));
}

$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    die(json_encode(['status' => 'error', 'message' => 'No OTP found for this email. Please request a new OTP.']));
}

if ($row['otp'] !== $otp) {
    die(json_encode(['status' => 'error', 'message' => 'Invalid OTP!']));
}

if (strtotime($row['otp_expiry']) < time()) {
    die(json_encode(['status' => 'error', 'message' => 'OTP expired! Please request a new one.']));
}

// Mark OTP as verified
$_SESSION['otp_verified'] = true;

echo json_encode(['status' => 'success', 'message' => 'OTP verified! You can now reset your password.']);
?>