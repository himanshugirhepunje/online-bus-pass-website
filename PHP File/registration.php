<?php
session_start();
include 'db_connection.php';

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must log in first!'); window.location.href='../HTML File/login.html';</script>";
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);  // Ensure email is assigned correctly
    $dob = $conn->real_escape_string($_POST['dob']);
    $age = (int) $_POST['age'];
    $gender = $conn->real_escape_string($_POST['gender']);
    $collage_name = $conn->real_escape_string($_POST['collage_name']);

    // File Upload Handling
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    function uploadFile($file_input_name, $upload_dir) {
        $file_tmp_name = $_FILES[$file_input_name]["tmp_name"];
        $file_name = basename($_FILES[$file_input_name]["name"]);
        $target_file = $upload_dir . time() . "_" . $file_name; // Add timestamp to avoid filename conflicts

        if (move_uploaded_file($file_tmp_name, $target_file)) {
            return $target_file; // Return file path if successful
        }
        return false;
    }

    $passport_photo = uploadFile("passport_photo", $upload_dir);
    $id_card = uploadFile("id_card", $upload_dir);
    $bonafide_certificate = uploadFile("bonafide_certificate", $upload_dir);

    if ($passport_photo && $id_card && $bonafide_certificate) {
        // Insert into database
        $sql = "INSERT INTO students (user_id, name, phone, email, dob, age, gender,collage_name, passport_photo, id_card, bonafide_certificate) 
                VALUES ('$user_id', '$name', '$phone', '$email', '$dob', '$age', '$gender','$collage_name', '$passport_photo', '$id_card', '$bonafide_certificate')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Registration Successful!'); window.location.href='dashboard.php';</script>";
        } else {
            die("Database Error: " . $conn->error); // Show exact SQL error
        }
    } else {
        echo "<script>alert('Error uploading files!'); window.history.back();</script>";
    }
}

$conn->close();
?>
