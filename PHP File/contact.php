<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';



include 'db_connection.php';

// Form handling
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    // Insert into database
    $sql = "INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $message);
    
    if ($stmt->execute()) {
        // Send email
        $mail = new PHPMailer(true);
        try {
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Use your mail server (Gmail, Outlook, etc.)
            $mail->SMTPAuth = true;
            $mail->Username = 'gpshimanshugi@gmail.com'; // Your email
            $mail->Password = 'drqp tryv ofij enfz'; // Your email password (or App Password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email settings
            $mail->setFrom('gpshimanshugi@gmail.com', 'MSRTC Bus Pass SAkoli');
            $mail->addAddress('gpshimanshugi@gmail.com', 'Admin'); // send message copy to admin
            $mail->addAddress($email, $name); // Send confirmation to user

            // Email content
            $mail->isHTML(true);
            $mail->Subject = "Thank you for contacting with us.";
            $mail->Body = "
                <p>Hello <b> $name:</b> </p>
                <p>Thank You for contacting with Us!:</p>
                <blockquote>$message</blockquote>
                <p>We will get back you soon.</p>
                <p>Best regards ,<br>
                MSRTC bus Pass Team</p>
            ";

            // Send email
            $mail->send();

            echo "<script>alert('Message sent successfully!'); window.location.href='../HTML file/contact.html';</script>";
        } catch (Exception $e) {
            echo "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
