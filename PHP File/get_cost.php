<?php
include 'db_connection.php'; // Include database connection

if (isset($_POST['source']) && isset($_POST['destination'])) {
    $source = $_POST['source'];
    $destination = $_POST['destination'];

    $query = "SELECT cost FROM route_costs WHERE source=? AND destination=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $source, $destination);
    $stmt->execute();
    $stmt->bind_result($cost);
    $stmt->fetch();
    
    echo ($cost) ? $cost : "Not Available";
    $stmt->close();
}
?>
