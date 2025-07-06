<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    // Password Validation
    if ($password != $confirmPassword) {
        echo "<script>alert('Passwords do not match!'); window.location.href='../HTML File/signup.html';</script>";
        exit();
    }

    // Hash the Password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the user already exists
    $checkUser = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
    $checkUser->bind_param("ss", $email, $phone);
    $checkUser->execute();
    $checkUser->store_result();

    if ($checkUser->num_rows > 0) {
        echo "<script>alert('Email or Phone already exists!'); window.location.href='../HTML File/signup.html';</script>";
        exit();
    }

    // Insert User Data
    $stmt = $conn->prepare("INSERT INTO users (name, phone, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $phone, $email, $hashedPassword);

    if ($stmt->execute()) {
        // Store user info in session
        $_SESSION['user'] = $email;
        echo "<script>alert('Signup Successful!'); window.location.href='../HTML File/login.html';</script>";
    } else {
        echo "<script>alert('Something went wrong!'); window.location.href='../HTML File/signup.html';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
