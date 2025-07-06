<?php
include 'db_connection.php';

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $delete_sql = "DELETE FROM users WHERE id = $delete_id";
    if ($conn->query($delete_sql)) {
        $success_msg = "User deleted successfully!";
    } else {
        $error_msg = "Error deleting user: " . $conn->error;
    }
}

// Fetch all users
$query = "SELECT id, name, phone, email, created_at FROM users ORDER BY created_at DESC";
$result = $conn->query($query);

// Count total users
$count_query = "SELECT COUNT(*) as total FROM users";
$count_result = $conn->query($count_query);
$total_users = $count_result->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #4e73df;
            --danger: #e74a3b;
            --light: #f8f9fc;
            --dark: #5a5c69;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }
        
        .container {
            max-width: 1200px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            padding: 0;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            font-weight: 600;
            margin: 0;
        }
        
        .header p {
            opacity: 0.9;
            margin: 5px 0 0;
        }
        
        .user-card {
            display: grid;
            grid-template-columns: 50px 80px 1fr 1fr 1fr 1fr auto;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            transition: all 0.3s;
        }
        
        .user-card:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        .user-id {
            font-family: monospace;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        .user-name {
            font-weight: 500;
        }
        
        .user-email {
            color: var(--dark);
            font-size: 0.9rem;
        }
        
        .user-phone {
            font-family: monospace;
        }
        
        .user-date {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .delete-btn {
            color: var(--danger);
            background: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            padding: 5px 10px;
            border-radius: 4px;
            background-color: rgba(231, 74, 59, 0.1);
        }
        
        .delete-btn:hover {
            background-color: rgba(231, 74, 59, 0.2);
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            color: #dee2e6;
            margin-bottom: 15px;
        }
        
        .user-count {
            padding: 10px 20px;
            border-bottom: 1px solid #eee;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        @media (max-width: 992px) {
            .user-card {
                grid-template-columns: 50px 80px 1fr 1fr auto;
            }
            .user-phone {
                display: none;
            }
        }
        
        @media (max-width: 768px) {
            .user-card {
                grid-template-columns: 1fr;
                gap: 10px;
                padding: 15px;
                border-bottom: 1px solid #eee;
            }
            
            .user-details {
                display: grid;
                grid-template-columns: 80px 1fr;
                gap: 10px;
                align-items: center;
            }
            
            .user-actions {
                justify-self: start;
                margin-top: 10px;
            }
            
            .user-phone {
                display: block;
                grid-column: span 2;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="bi bi-people-fill"></i> User Management</h1>
            <p>Manage all registered users in the system</p>
        </div>
        
        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
                <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
                <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="user-count">
            Total Users: <?php echo $total_users; ?>
        </div>
        
        <div class="user-list">
            <?php if ($result->num_rows > 0): ?>
                <!-- Header Row -->
                <div class="user-card" style="background-color: var(--light); font-weight: 500;">
                    <div>#</div>
                    <div>ID</div>
                    <div>Name</div>
                    <div>Email</div>
                    <div>Phone</div>
                    <div>Registered</div>
                    <div>Actions</div>
                </div>
                
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="user-card">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                        </div>
                        <div class="user-id">
                            <?php echo $row['id']; ?>
                        </div>
                        <div class="user-name">
                            <?php echo htmlspecialchars($row['name']); ?>
                        </div>
                        <div class="user-email">
                            <?php echo htmlspecialchars($row['email']); ?>
                        </div>
                        <div class="user-phone">
                            <?php echo htmlspecialchars($row['phone']); ?>
                        </div>
                        <div class="user-date">
                            <?php echo date('M j, Y', strtotime($row['created_at'])); ?>
                        </div>
                        <div class="user-actions">
                            <button class="delete-btn" onclick="confirmDelete(<?php echo $row['id']; ?>)">
                                <i class="bi bi-trash-fill"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-people"></i>
                    <h4>No Users Found</h4>
                    <p>There are currently no registered users in the system.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="p-3 text-center border-top">
            <a href="admin_dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(userId) {
            if (confirm('Are you sure you want to delete user #' + userId + '? This action cannot be undone.')) {
                window.location.href = '?delete_id=' + userId;
            }
        }
    </script>
</body>
</html>