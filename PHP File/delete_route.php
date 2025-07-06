<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['admin_logged_in'])) {
    echo "Unauthorized access!";
    exit();
}

if (isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $stmt = $conn->prepare("DELETE FROM route_costs WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "success"; // Success response
    } else {
        echo "error"; // Error response
    }
    
    $stmt->close();
    $conn->close();
}
?>
