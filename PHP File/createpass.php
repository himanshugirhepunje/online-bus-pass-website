<?php
session_start();
include 'db_connection.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Please log in first!'); window.location.href='../HTML File/login.html';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$source = $_POST['source'];
$destination = $_POST['destination'];
$valid_until = $_POST['valid_until'];
$cost = str_replace('₹', '', $_POST['cost']); // Remove ₹ symbol
$transaction_id = $_POST['transaction_id']; 
$payment_status = 'pending'; 

// Validate inputs
if (empty($source) || empty($destination) || empty($valid_until) || empty($cost) || empty($transaction_id)) {
    echo "<script>alert('All fields are required!'); window.location.href='bus_pass.php';</script>";
    exit();
}

// Check if user_id exists in students table
$check_student = $conn->prepare("SELECT user_id FROM students WHERE user_id = ?");
$check_student->bind_param("i", $user_id);
$check_student->execute();
$check_student->store_result();

if ($check_student->num_rows == 0) {
    echo "<script>alert('User is not a registered student!'); window.location.href='bus_pass.php';</script>";
    exit();
}

$check_student->close();

// Insert data into payments table
$stmt = $conn->prepare("INSERT INTO payments (user_id, source, destination, valid_until, cost, transaction_id, payment_status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssss", $user_id, $source, $destination, $valid_until, $cost, $transaction_id, $payment_status);

if ($stmt->execute()) {
    echo "<script>alert('Bus pass application submitted! Payment verification is pending.'); window.location.href='dashboard.php';</script>";
} else {
    echo "<script>alert('Error submitting bus pass application: " . $stmt->error . "'); window.location.href='bus_pass.php';</script>";
}

$stmt->close();
$conn->close();
?>
