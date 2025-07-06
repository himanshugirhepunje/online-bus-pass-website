<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $source = $conn->real_escape_string($_POST['source']);
    $destination = $conn->real_escape_string($_POST['destination']);
    $cost = $conn->real_escape_string($_POST['cost']);
    
    $sql = "INSERT INTO route_costs (source, destination, cost) VALUES ('$source', '$destination', '$cost')";
    
    if ($conn->query($sql) === TRUE) {
        // header('Location: route_cost_management.php');
        // exit();
        echo "<script>alert('Location Added successfully!'); window.location.href='route_cost_management.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>