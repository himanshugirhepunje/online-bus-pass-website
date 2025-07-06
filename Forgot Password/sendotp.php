<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';

include '../PHP File/db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $_SESSION['reset_email'] = $email;

    // Check if email exists
    $checkUser = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkUser->bind_param("s", $email);
    $checkUser->execute();
    $checkUser->store_result();
    
    if ($checkUser->num_rows == 0) {
        die("Email not found in our system.");
    }

    // Generate OTP
    $otp = rand(100000, 999999);
    $expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

    // Store OTP
    $stmt = $conn->prepare("INSERT INTO otp (email, otp, otp_expiry) VALUES (?, ?, ?) 
                            ON DUPLICATE KEY UPDATE otp=?, otp_expiry=?");
    $stmt->bind_param("sssss", $email, $otp, $expiry, $otp, $expiry);
    
    if (!$stmt->execute()) {
        die("Error storing OTP: " . $conn->error);
    }

    // Send email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'gpshimanshugi@gmail.com';
        $mail->Password = 'drqp tryv ofij enfz';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPDebug = 2; // Enable verbose debug output

        $mail->setFrom('gpshimanshugi@gmail.com', 'Password Reset OTP');
        $mail->addAddress($email);
        $mail->Subject = 'Your Password Reset OTP';
        $mail->Body = "Your OTP code is: $otp. Valid for 5 minutes.";

        $mail->send();
        echo "OTP sent successfully!";
    } catch (Exception $e) {
        die("Error sending OTP: " . $e->getMessage());
    }
}
?>