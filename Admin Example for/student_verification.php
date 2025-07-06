<?php
include 'db_connection.php';

// Handle status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';
if (!in_array($status_filter, ['pending', 'approved', 'rejected'])) {
    $status_filter = 'pending';
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve'])) {
        $student_id = intval($_POST['student_id']);
        $update_sql = "UPDATE students SET status = 'approved' WHERE id = $student_id";
        if ($conn->query($update_sql)) {
            $success_msg = "Student application approved successfully!";
        } else {
            $error_msg = "Error approving application: " . $conn->error;
        }
    } elseif (isset($_POST['reject'])) {
        $student_id = intval($_POST['student_id']);
        $reason = $conn->real_escape_string($_POST['rejection_reason']);
        $update_sql = "UPDATE students SET status = 'rejected', rejection_reason = '$reason' WHERE id = $student_id";
        if ($conn->query($update_sql)) {
            $success_msg = "Student application rejected successfully!";
        } else {
            $error_msg = "Error rejecting application: " . $conn->error;
        }
    }
}

// Fetch students with filter
$query = "SELECT * FROM students WHERE status = '$status_filter' ORDER BY created_at DESC";
$result = $conn->query($query);

// Count students by status
$count_query = "SELECT status, COUNT(*) as count FROM students GROUP BY status";
$count_result = $conn->query($count_query);
$status_counts = ['pending' => 0, 'approved' => 0, 'rejected' => 0];
while ($row = $count_result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Verification</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --danger: #e74a3b;
            --warning: #f6c23e;
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
        
        .filter-tabs {
            display: flex;
            border-bottom: 1px solid #dee2e6;
            padding: 0 20px;
        }
        
        .filter-tab {
            padding: 12px 20px;
            cursor: pointer;
            font-weight: 500;
            position: relative;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .filter-tab:hover {
            background-color: rgba(0,0,0,0.03);
        }
        
        .filter-tab.active {
            border-bottom-color: var(--primary);
            color: var(--primary);
        }
        
        .filter-tab .badge {
            font-size: 0.7rem;
            margin-left: 5px;
        }
        
        .student-card {
            border-left: 4px solid;
            margin: 15px 20px;
            padding: 15px;
            border-radius: 5px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .student-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .student-card.pending {
            border-left-color: var(--warning);
        }
        
        .student-card.approved {
            border-left-color: var(--success);
        }
        
        .student-card.rejected {
            border-left-color: var(--danger);
        }
        
        .student-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .student-title {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .student-status {
            font-size: 0.8rem;
            padding: 3px 10px;
            border-radius: 20px;
        }
        
        .status-pending {
            background-color: rgba(var(--warning), 0.1);
            color: #856404;
        }
        
        .status-approved {
            background-color: rgba(var(--success), 0.1);
            color: #155724;
        }
        
        .status-rejected {
            background-color: rgba(var(--danger), 0.1);
            color: #721c24;
        }
        
        .student-details {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .detail-item {
            margin-bottom: 5px;
        }
        
        .detail-label {
            font-size: 0.8rem;
            color: var(--dark);
            opacity: 0.7;
        }
        
        .detail-value {
            font-weight: 500;
        }
        
        .document-link {
            color: var(--primary);
            text-decoration: none;
        }
        
        .document-link:hover {
            text-decoration: underline;
        }
        
        .student-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .rejection-reason {
            background-color: #f8d7da;
            color: #721c24;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 0.85rem;
            margin-top: 10px;
            grid-column: 1 / -1;
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
        
        .document-preview {
            max-width: 100px;
            max-height: 100px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .student-details {
                grid-template-columns: 1fr;
            }
            
            .student-actions {
                flex-direction: column;
            }
            
            .student-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="bi bi-people-fill"></i> Student Verification</h1>
            <p>Review and manage student bus pass applications</p>
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
        
        <div class="filter-tabs">
            <div class="filter-tab <?php echo $status_filter == 'pending' ? 'active' : ''; ?>" onclick="window.location='?status=pending'">
                Pending <span class="badge bg-light text-dark"><?php echo $status_counts['pending']; ?></span>
            </div>
            <div class="filter-tab <?php echo $status_filter == 'approved' ? 'active' : ''; ?>" onclick="window.location='?status=approved'">
                Approved <span class="badge bg-light text-dark"><?php echo $status_counts['approved']; ?></span>
            </div>
            <div class="filter-tab <?php echo $status_filter == 'rejected' ? 'active' : ''; ?>" onclick="window.location='?status=rejected'">
                Rejected <span class="badge bg-light text-dark"><?php echo $status_counts['rejected']; ?></span>
            </div>
        </div>
        
        <div class="student-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="student-card <?php echo $row['status']; ?>">
                        <div class="student-header">
                            <div class="student-title">
                                <?php echo htmlspecialchars($row['name']); ?>
                                <small class="text-muted">(ID: <?php echo $row['id']; ?>)</small>
                            </div>
                            <span class="student-status status-<?php echo $row['status']; ?>">
                                <?php echo ucfirst($row['status']); ?>
                            </span>
                        </div>
                        
                        <div class="student-details">
                            <div class="detail-item">
                                <div class="detail-label">Email</div>
                                <div class="detail-value"><?php echo htmlspecialchars($row['email']); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Phone</div>
                                <div class="detail-value"><?php echo htmlspecialchars($row['phone']); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Date of Birth</div>
                                <div class="detail-value"><?php echo date('M j, Y', strtotime($row['dob'])); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Age</div>
                                <div class="detail-value"><?php echo $row['age']; ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Gender</div>
                                <div class="detail-value"><?php echo htmlspecialchars($row['gender']); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">College</div>
                                <div class="detail-value"><?php echo htmlspecialchars($row['collage_name']); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Passport Photo</div>
                                <div class="detail-value">
                                    <a href="<?php echo htmlspecialchars($row['passport_photo']); ?>" class="document-link" target="_blank">
                                        View Photo
                                    </a>
                                    <img src="<?php echo htmlspecialchars($row['passport_photo']); ?>" class="document-preview" alt="Passport Photo">
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">ID Card</div>
                                <div class="detail-value">
                                    <a href="<?php echo htmlspecialchars($row['id_card']); ?>" class="document-link" target="_blank">
                                        View ID Card
                                    </a>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Bonafide Certificate</div>
                                <div class="detail-value">
                                    <a href="<?php echo htmlspecialchars($row['bonafide_certificate']); ?>" class="document-link" target="_blank">
                                        View Certificate
                                    </a>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Application Date</div>
                                <div class="detail-value"><?php echo date('M j, Y H:i', strtotime($row['created_at'])); ?></div>
                            </div>
                            
                            <?php if ($row['status'] == 'rejected' && $row['rejection_reason']): ?>
                                <div class="rejection-reason">
                                    <strong>Reason:</strong> <?php echo htmlspecialchars($row['rejection_reason']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($row['status'] == 'pending'): ?>
                            <div class="student-actions">
                                <form method="POST" style="flex:1;">
                                    <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="approve" class="btn btn-success w-100">
                                        <i class="bi bi-check-circle"></i> Approve
                                    </button>
                                </form>
                                
                                <button class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal<?php echo $row['id']; ?>">
                                    <i class="bi bi-x-circle"></i> Reject
                                </button>
                            </div>
                            
                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal<?php echo $row['id']; ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title">Reject Application</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>You are about to reject this student's application. Please provide a reason:</p>
                                            <form method="POST">
                                                <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                                                <div class="mb-3">
                                                    <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Enter rejection reason..."></textarea>
                                                </div>
                                                <div class="d-flex justify-content-end gap-2">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="reject" class="btn btn-danger">Confirm Rejection</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="bi bi-people"></i>
                    <h4>No <?php echo $status_filter; ?> applications found</h4>
                    <p>There are currently no <?php echo $status_filter; ?> student applications to display.</p>
                    <?php if ($status_filter != 'pending'): ?>
                        <a href="?status=pending" class="btn btn-primary mt-2">
                            <i class="bi bi-hourglass"></i> View Pending Applications
                        </a>
                    <?php endif; ?>
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
</body>
</html>