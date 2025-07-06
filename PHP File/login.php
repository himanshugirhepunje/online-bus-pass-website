<?php
session_start();
include 'db_connection.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emailOrPhone = $_POST['emailOrPhone'];
    $password = $_POST['password'];

    // Check if the user exists
    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ? OR phone = ?");
    $stmt->bind_param("ss", $emailOrPhone, $emailOrPhone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashedPassword);
        $stmt->fetch();

        if (password_verify($password, $hashedPassword)) {
            // Store user session
            $_SESSION['user'] = $name;
            $_SESSION['user_id'] = $id;

            echo "<script>alert('Login Successful!'); window.location.href='dashboard.php';</script>";
        } else {
            echo "<script>alert('Incorrect Password!'); window.location.href='../HTML File/login.html';</script>";
        }
    } else {
        echo "<script>alert('User not found!'); window.location.href='../HTML File/login.html';</script>";
    }

    $stmt->close();
    $conn->close();
}

if (password_verify($password, $hashedPassword)) {
    // Fetch user details
    $stmt2 = $conn->prepare("SELECT id, email, phone FROM users WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->bind_result($userId, $email, $phone);
    $stmt2->fetch();

    // Store user info in session
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_phone'] = $phone;

    echo "<script>alert('Login Successful!'); window.location.href='dashboard.php';</script>";
}
?>
