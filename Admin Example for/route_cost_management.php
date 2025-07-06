<?php
session_start();
include 'db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM route_costs WHERE id = $id");
    header('Location: admin_dashboard.php');
    exit();
}

// Check for duplicate entry
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['source']) && isset($_POST['destination'])) {
    $source = $conn->real_escape_string($_POST['source']);
    $destination = $conn->real_escape_string($_POST['destination']);
    
    $checkSql = "SELECT * FROM route_costs WHERE source = '$source' AND destination = '$destination'";
    $result = $conn->query($checkSql);
    
    if ($result->num_rows > 0) {
        $duplicateError = "This route already exists (Source: $source, Destination: $destination)";
    } else {
        // Proceed with insertion if no duplicate
        $cost = $conn->real_escape_string($_POST['cost']);
        $insertSql = "INSERT INTO route_costs (source, destination, cost) VALUES ('$source', '$destination', '$cost')";
        
        if ($conn->query($insertSql)) {
            $successMsg = "Route added successfully!";
        } else {
            $errorMsg = "Error adding route: " . $conn->error;
        }
    }
}

// Fetch all locations
$locations = $conn->query("SELECT * FROM route_costs ORDER BY source, destination");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Management | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            padding: 20px;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 8px 15px;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-1px);
        }
        
        .dashboard-btn {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .dashboard-btn:hover {
            background-color: #1a252f;
            color: white;
            text-decoration: none;
            transform: translateY(-1px);
        }
        
        .dashboard-btn i {
            margin-right: 5px;
        }
        
        .btn-danger {
            background-color: var(--accent-color);
            border: none;
            padding: 8px 15px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .action-btn {
            padding: 5px 10px;
            margin: 0 3px;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .edit-btn {
            background-color: #2ecc71;
            color: white;
        }
        
        .delete-btn {
            background-color: var(--accent-color);
            color: white;
        }
        
        .search-box {
            max-width: 300px;
        }
        
        .form-control, .form-select {
            padding: 10px;
            border-radius: 5px;
        }
        
        .alert-message {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            animation: slideIn 0.5s, fadeOut 0.5s 2.5s forwards;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); }
            to { transform: translateX(0); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
</head>
<body>
    <!-- Success/Error Messages -->
    <?php if (isset($successMsg)): ?>
        <div class="alert alert-success alert-message">
            <i class="fas fa-check-circle me-2"></i><?php echo $successMsg; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($errorMsg)): ?>
        <div class="alert alert-danger alert-message">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMsg; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($duplicateError)): ?>
        <div class="alert alert-warning alert-message">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo $duplicateError; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="fas fa-route me-2"></i>Route Costs Management</h2>
            <a href="admin_dashboard.php" class="dashboard-btn">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </div>

        <!-- Add New Route Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Route</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="source" class="form-label">Source</label>
                            <input type="text" class="form-control" id="source" name="source" required>
                        </div>
                        <div class="col-md-4">
                            <label for="destination" class="form-label">Destination</label>
                            <input type="text" class="form-control" id="destination" name="destination" required>
                        </div>
                        <div class="col-md-3">
                            <label for="cost" class="form-label">Cost (₹)</label>
                            <input type="number" class="form-control" id="cost" name="cost" required>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-save me-1"></i>Add
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Routes Table Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Existing Routes</h5>
                <div class="search-box">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search routes..." id="searchInput">
                        <button class="btn btn-primary" type="button" id="searchBtn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="routesTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Source</th>
                                <th>Destination</th>
                                <th>Cost (₹)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $locations->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo ucfirst($row['source']); ?></td>
                                <td><?php echo ucfirst($row['destination']); ?></td>
                                <td>₹<?php echo number_format($row['cost'], 2); ?></td>
                                <td>
                                    <a href="edit_location.php?id=<?php echo $row['id']; ?>" class="btn btn-sm edit-btn">
                                        <i class="fas fa-edit me-1"></i>Edit
                                    </a>
                                    <a href="admin_dashboard.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm delete-btn" onclick="return confirm('Are you sure you want to delete this route?')">
                                        <i class="fas fa-trash-alt me-1"></i>Delete
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div>Showing <?php echo $locations->num_rows; ?> routes</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple search functionality
        document.getElementById('searchBtn').addEventListener('click', function() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('#routesTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        // Search as you type
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#routesTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
        
        // Auto-hide messages after 3 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('.alert-message');
            messages.forEach(msg => {
                msg.style.display = 'none';
            });
        }, 3000);
    </script>
</body>
</html>