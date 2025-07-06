<?php
include 'db_connection.php'; // Include database connection

// Fetch unique sources
$query = "SELECT DISTINCT source FROM route_costs";
$result = $conn->query($query);
$sources = [];
while ($row = $result->fetch_assoc()) {
    $sources[] = $row['source'];
}

// Fetch unique destinations
$query = "SELECT DISTINCT destination FROM route_costs";
$result = $conn->query($query);
$destinations = [];
while ($row = $result->fetch_assoc()) {
    $destinations[] = $row['destination'];
}

echo json_encode(["sources" => $sources, "destinations" => $destinations]);
?>
