<?php
$host = "localhost"; // Change if needed
$user = "root"; // Database username
$pass = ""; // Database password
$dbname = "online_bus_pass"; // Change to your database name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
