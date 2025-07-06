<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Fetch location to edit
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM route_costs WHERE id = $id");
    $location = $result->fetch_assoc();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $source = $conn->real_escape_string($_POST['source']);
    $destination = $conn->real_escape_string($_POST['destination']);
    $cost = $conn->real_escape_string($_POST['cost']);
    
    $sql = "UPDATE route_costs SET source='$source', destination='$destination', cost='$cost' WHERE id=$id";
    
    if ($conn->query($sql)) {
        echo "<script>alert('Location updated successfully!'); window.location.href='route_cost_management.php';</script>";
    } else {
        echo "<script>alert('Error updating record: " . addslashes($conn->error) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Route | Admin Panel</title>
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
        
        .edit-container {
            max-width: 600px;
            margin: 30px auto;
            background: white;
            border-radius: 10px;
            box-shadow: var(--card-shadow);
            padding: 30px;
        }
        
        .page-header {
            color: var(--secondary-color);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--secondary-color);
        }
        
        .form-control {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 10px 20px;
            font-weight: 500;
        }
        
        .btn-outline-secondary {
            padding: 10px 20px;
            font-weight: 500;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 25px;
        }
        
        .back-link {
            color: var(--secondary-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .back-link i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="route_cost_management.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Routes
        </a>
        
        <div class="edit-container">
            <h2 class="page-header">
                <i class="fas fa-route me-2"></i>Edit Route
            </h2>
            
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo $location['id']; ?>">
                
                <div class="mb-4">
                    <label for="source" class="form-label">Source</label>
                    <input type="text" class="form-control" id="source" name="source" 
                           value="<?php echo htmlspecialchars($location['source']); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label for="destination" class="form-label">Destination</label>
                    <input type="text" class="form-control" id="destination" name="destination" 
                           value="<?php echo htmlspecialchars($location['destination']); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label for="cost" class="form-label">Cost (₹)</label>
                    <input type="number" class="form-control" id="cost" name="cost" 
                           value="<?php echo htmlspecialchars($location['cost']); ?>" required>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Update Route
                    </button>
                    <a href="route_cost_management.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>