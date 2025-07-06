<?php
session_start();
include 'db_connection.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

// Rest of your dashboard code...



// Get counts for dashboard cards
$users_count = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$pending_payments = $conn->query("SELECT COUNT(*) FROM payments WHERE payment_status = 'pending'")->fetch_row()[0];
$pending_students = $conn->query("SELECT COUNT(*) FROM students WHERE status = 'pending'")->fetch_row()[0];
$messages_count = $conn->query("SELECT COUNT(*) FROM contacts")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --danger: #e74a3b;
            --warning: #f6c23e;
            --info: #36b9cc;
            --light: #f8f9fc;
            --dark: #5a5c69;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: linear-gradient(180deg, #4e73df 0%, #224abe 100%);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .sidebar-header h3 {
            color: white;
            font-weight: 600;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .menu-item {
            margin-bottom: 5px;
        }
        
        .menu-link {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 12px 20px;
            transition: all 0.3s;
        }
        
        .menu-link:hover, .menu-link.active {
            color: white;
            background-color: rgba(255,255,255,0.1);
        }
        
        .menu-link i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }
        
        .content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        
        .header {
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: var(--dark);
            font-weight: 600;
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: all 0.3s;
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .card-icon {
            font-size: 2rem;
            opacity: 0.3;
            position: absolute;
            right: 20px;
            top: 20px;
        }
        
        .card-primary {
            border-left: 4px solid var(--primary);
        }
        
        .card-success {
            border-left: 4px solid var(--success);
        }
        
        .card-warning {
            border-left: 4px solid var(--warning);
        }
        
        .card-danger {
            border-left: 4px solid var(--danger);
        }
        
        .card-title {
            font-size: 0.9rem;
            text-transform: uppercase;
            color: var(--dark);
            font-weight: 600;
        }
        
        .card-value {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        .card-link {
            text-decoration: none;
        }
        
        .card-link:hover .card {
            transform: translateY(-5px);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 80px;
                overflow: hidden;
            }
            
            .sidebar-header h3, .menu-link span {
                display: none;
            }
            
            .menu-link {
                justify-content: center;
                padding: 12px 0;
            }
            
            .menu-link i {
                margin-right: 0;
                font-size: 1.3rem;
            }
            
            .content {
                margin-left: 80px;
                width: calc(100% - 80px);
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="bi bi-speedometer2"></i> <span>Admin Panel</span></h3>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item">
                <a href="admin_dashboard.php" class="menu-link active">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="user_management.php" class="menu-link">
                    <i class="bi bi-people-fill"></i>
                    <span>User Management</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="student_verification.php" class="menu-link">
                    <i class="bi bi-person-vcard"></i>
                    <span>Student Verification</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="payment_verification.php" class="menu-link">
                    <i class="bi bi-credit-card"></i>
                    <span>Payment Verification</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="route_cost_management.php" class="menu-link">
                    <i class="bi bi-credit-card"></i>
                    <span> Route Costs Management</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="message_management.php" class="menu-link">
                    <i class="bi bi-envelope-fill"></i>
                    <span>Messages</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="admin_logout.php" class="menu-link">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="header">
            <h1>Dashboard Overview</h1>
            <p class="text-muted">Welcome back, Admin. Here's what's happening with your system today.</p>
        </div>
        
        <div class="row">
            <div class="col-md-6 col-lg-3 mb-4">
                <a href="user_management.php" class="card-link">
                    <div class="card card-primary">
                        <div class="card-body">
                            <div class="card-title">Total Users</div>
                            <div class="card-value"><?php echo $users_count; ?></div>
                            <i class="bi bi-people-fill card-icon text-primary"></i>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <a href="student_verification.php?status=pending" class="card-link">
                    <div class="card card-warning">
                        <div class="card-body">
                            <div class="card-title">Pending Students</div>
                            <div class="card-value"><?php echo $pending_students; ?></div>
                            <i class="bi bi-person-vcard card-icon text-warning"></i>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <a href="payment_verification.php?status=pending" class="card-link">
                    <div class="card card-danger">
                        <div class="card-body">
                            <div class="card-title">Pending Payments</div>
                            <div class="card-value"><?php echo $pending_payments; ?></div>
                            <i class="bi bi-credit-card card-icon text-danger"></i>
                        </div>
                    </div>
                </a>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <a href="message_management.php" class="card-link">
                    <div class="card card-success">
                        <div class="card-body">
                            <div class="card-title">Messages</div>
                            <div class="card-value"><?php echo $messages_count; ?></div>
                            <i class="bi bi-envelope-fill card-icon text-success"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Activities</h5>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <small class="text-muted">10 minutes ago</small>
                                </div>
                                <p class="mb-1">5 new student applications received</p>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <small class="text-muted">1 hour ago</small>
                                </div>
                                <p class="mb-1">3 payments approved</p>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <small class="text-muted">3 hours ago</small>
                                </div>
                                <p class="mb-1">System maintenance completed</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <div class="d-grid gap-2">
                            <a href="student_verification.php?status=pending" class="btn btn-outline-primary">
                                <i class="bi bi-person-vcard me-2"></i> Review Pending Students
                            </a>
                            <a href="payment_verification.php?status=pending" class="btn btn-outline-warning">
                                <i class="bi bi-credit-card me-2"></i> Approve Pending Payments
                            </a>
                            <a href="message_management.php" class="btn btn-outline-success">
                                <i class="bi bi-envelope me-2"></i> Check New Messages
                            </a>
                            <a href="user_management.php" class="btn btn-outline-danger">
                                <i class="bi bi-people me-2"></i> Manage Users
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>