<?php
session_start();
include 'db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Check for duplicate entry
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['source']) && isset($_POST['destination'])) {
    $source = $conn->real_escape_string($_POST['source']);
    $destination = $conn->real_escape_string($_POST['destination']);
    $cost = $conn->real_escape_string($_POST['cost']);
    
    // Check if the route already exists
    $checkSql = "SELECT * FROM route_costs WHERE source = '$source' AND destination = '$destination'";
    $result = $conn->query($checkSql);
    
    if ($result->num_rows > 0) {
        $duplicateError = "This route already exists!";
    } else {
        // Insert new route
        $insertSql = "INSERT INTO route_costs (source, destination, cost) VALUES ('$source', '$destination', '$cost')";
        if ($conn->query($insertSql)) {
            $successMsg = "Route added successfully!";
        } else {
            $errorMsg = "Error adding route: " . $conn->error;
        }
    }
}

// Fetch all routes
$routes = $conn->query("SELECT * FROM route_costs ORDER BY source, destination");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Management | Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
            --success-color: #4cc9f0;
            --danger-color: #f72585;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table thead {
            background-color: var(--primary-color);
            color: white;
        }
        
        .table th {
            border: none;
            font-weight: 500;
        }
        
        .table td {
            vertical-align: middle;
        }
        
        .alert {
            border-radius: 8px;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #ddd;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }
        
        .badge {
            padding: 6px 10px;
            font-weight: 500;
            border-radius: 8px;
        }
        
        .action-btns .btn {
            margin-right: 5px;
        }
        
        .dashboard-header {
            background-color: white;
            padding: 15px 0;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Dashboard Header with Back Button -->
        <div class="dashboard-header">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0"><i class="fas fa-route text-primary me-2"></i>Route Management</h1>
                    <a href="admin_dashboard.php" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Success/Error Messages -->
            <?php if (isset($successMsg)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo $successMsg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($errorMsg)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $errorMsg; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($duplicateError)): ?>
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $duplicateError; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Add New Route Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-plus-circle me-2"></i>Add New Route
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Source City</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" class="form-control" name="source" placeholder="Enter source city" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Destination City</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" class="form-control" name="destination" placeholder="Enter destination city" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Cost (₹)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" name="cost" placeholder="0.00" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-plus me-1"></i> Add
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Routes Table Card -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <i class="fas fa-list me-2"></i>Existing Routes
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Source</th>
                                    <th>Destination</th>
                                    <th>Cost</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $routes->fetch_assoc()): ?>
                                    <tr id="route-<?php echo $row['id']; ?>">
                                        <td><?php echo $row['id']; ?></td>
                                        <td>
                                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                            <?php echo ucfirst($row['source']); ?>
                                        </td>
                                        <td>
                                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                            <?php echo ucfirst($row['destination']); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">
                                                <i class="fas fa-rupee-sign me-1"></i>
                                                <?php echo number_format($row['cost'], 2); ?>
                                            </span>
                                        </td>
                                        <td class="action-btns">
                                            <a href="edit_location.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?php echo $row['id']; ?>">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this route? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            let routeIdToDelete = null;
            
            // Delete button click handler
            $(".delete-btn").click(function() {
                routeIdToDelete = $(this).data("id");
                $('#deleteModal').modal('show');
            });
            
            // Confirm delete
            $("#confirmDelete").click(function() {
                if (routeIdToDelete) {
                    $.ajax({
                        url: "delete_route.php",
                        type: "POST",
                        data: { id: routeIdToDelete },
                        success: function(response) {
                            if (response === "success") {
                                $("#route-" + routeIdToDelete).fadeOut(500, function() { 
                                    $(this).remove(); 
                                    $('#deleteModal').modal('hide');
                                    
                                    // Show success toast
                                    showToast('Route deleted successfully!', 'success');
                                });
                            } else {
                                $('#deleteModal').modal('hide');
                                showToast('Error deleting route. Please try again.', 'danger');
                            }
                        },
                        error: function() {
                            $('#deleteModal').modal('hide');
                            showToast('Error deleting route. Please try again.', 'danger');
                        }
                    });
                }
            });
            
            // Toast notification function
            function showToast(message, type) {
                const toast = $(`
                    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                        <div class="toast show align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
                                    ${message}
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    </div>
                `);
                
                $('body').append(toast);
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    toast.remove();
                }, 5000);
            }
        });
    </script>
</body>
</html>