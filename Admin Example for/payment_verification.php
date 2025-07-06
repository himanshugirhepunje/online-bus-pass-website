<?php
include 'db_connection.php';

// Handle status filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';
if (!in_array($status_filter, ['pending', 'success', 'failed'])) {
    $status_filter = 'pending';
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve'])) {
        $payment_id = intval($_POST['payment_id']);
        $update_sql = "UPDATE payments SET payment_status = 'success' WHERE id = $payment_id";
        if ($conn->query($update_sql)) {
            $success_msg = "Payment approved successfully!";
        } else {
            $error_msg = "Error approving payment: " . $conn->error;
        }
    } elseif (isset($_POST['reject'])) {
        $payment_id = intval($_POST['payment_id']);
        $reason = $conn->real_escape_string($_POST['rejection_reason']);
        $update_sql = "UPDATE payments SET payment_status = 'failed', rejection_reason = '$reason' WHERE id = $payment_id";
        if ($conn->query($update_sql)) {
            $success_msg = "Payment rejected successfully!";
        } else {
            $error_msg = "Error rejecting payment: " . $conn->error;
        }
    }
}

// Fetch payments with filter
$query = "SELECT p.id, p.user_id, s.name, p.source, p.destination, 
                 p.valid_until, p.cost, p.transaction_id, 
                 p.payment_status, p.rejection_reason, p.date 
          FROM payments p
          JOIN students s ON p.user_id = s.user_id
          WHERE p.payment_status = '$status_filter'
          ORDER BY p.date DESC";
$result = $conn->query($query);

// Count payments by status
$count_query = "SELECT payment_status, COUNT(*) as count FROM payments GROUP BY payment_status";
$count_result = $conn->query($count_query);
$status_counts = ['pending' => 0, 'success' => 0, 'failed' => 0];
while ($row = $count_result->fetch_assoc()) {
    $status_counts[$row['payment_status']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Verification</title>
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
        
        .payment-card {
            border-left: 4px solid;
            margin: 15px 20px;
            padding: 15px;
            border-radius: 5px;
            background: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        
        .payment-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .payment-card.pending {
            border-left-color: var(--warning);
        }
        
        .payment-card.success {
            border-left-color: var(--success);
        }
        
        .payment-card.failed {
            border-left-color: var(--danger);
        }
        
        .payment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .payment-title {
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .payment-status {
            font-size: 0.8rem;
            padding: 3px 10px;
            border-radius: 20px;
        }
        
        .status-pending {
            background-color: rgba(var(--warning), 0.1);
            color: #856404;
        }
        
        .status-success {
            background-color: rgba(var(--success), 0.1);
            color: #155724;
        }
        
        .status-failed {
            background-color: rgba(var(--danger), 0.1);
            color: #721c24;
        }
        
        .payment-details {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .detail-item {
            flex: 1;
            min-width: 150px;
        }
        
        .detail-label {
            font-size: 0.8rem;
            color: var(--dark);
            opacity: 0.7;
        }
        
        .detail-value {
            font-weight: 500;
        }
        
        .payment-actions {
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
        
        @media (max-width: 768px) {
            .payment-details {
                flex-direction: column;
                gap: 8px;
            }
            
            .payment-actions {
                flex-direction: column;
            }
            
            .payment-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="bi bi-credit-card"></i> Payment Verification</h1>
            <p>Review and manage student bus pass payments</p>
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
            <div class="filter-tab <?php echo $status_filter == 'success' ? 'active' : ''; ?>" onclick="window.location='?status=success'">
                Approved <span class="badge bg-light text-dark"><?php echo $status_counts['success']; ?></span>
            </div>
            <div class="filter-tab <?php echo $status_filter == 'failed' ? 'active' : ''; ?>" onclick="window.location='?status=failed'">
                Rejected <span class="badge bg-light text-dark"><?php echo $status_counts['failed']; ?></span>
            </div>
        </div>
        
        <div class="payment-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="payment-card <?php echo $row['payment_status']; ?>">
                        <div class="payment-header">
                            <div class="payment-title">
                                <?php echo htmlspecialchars($row['name']); ?>
                                <small class="text-muted">(ID: <?php echo $row['user_id']; ?>)</small>
                            </div>
                            <span class="payment-status status-<?php echo $row['payment_status']; ?>">
                                <?php 
                                    echo $row['payment_status'] == 'success' ? 'Approved' : 
                                         ($row['payment_status'] == 'failed' ? 'Rejected' : 'Pending'); 
                                ?>
                            </span>
                        </div>
                        
                        <div class="payment-details">
                            <div class="detail-item">
                                <div class="detail-label">Route</div>
                                <div class="detail-value">
                                    <?php echo htmlspecialchars($row['source']); ?> → <?php echo htmlspecialchars($row['destination']); ?>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Amount</div>
                                <div class="detail-value">₹<?php echo number_format($row['cost'], 2); ?></div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Transaction ID</div>
                                <div class="detail-value">
                                    <?php echo $row['transaction_id'] ?: '<span class="text-muted">N/A</span>'; ?>
                                </div>
                            </div>
                            
                            <div class="detail-item">
                                <div class="detail-label">Valid Until</div>
                                <div class="detail-value"><?php echo date('M j, Y', strtotime($row['valid_until'])); ?></div>
                            </div>
                        </div>
                        
                        <?php if ($row['payment_status'] == 'failed' && $row['rejection_reason']): ?>
                            <div class="rejection-reason">
                                <strong>Reason:</strong> <?php echo htmlspecialchars($row['rejection_reason']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($row['payment_status'] == 'pending'): ?>
                            <div class="payment-actions">
                                <form method="POST" style="flex:1;">
                                    <input type="hidden" name="payment_id" value="<?php echo $row['id']; ?>">
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
                                            <h5 class="modal-title">Reject Payment</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>You are about to reject this payment. Please provide a reason:</p>
                                            <form method="POST">
                                                <input type="hidden" name="payment_id" value="<?php echo $row['id']; ?>">
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
                    <i class="bi bi-credit-card"></i>
                    <h4>No <?php echo $status_filter; ?> payments found</h4>
                    <p>There are currently no <?php echo $status_filter; ?> payments to display.</p>
                    <?php if ($status_filter != 'pending'): ?>
                        <a href="?status=pending" class="btn btn-primary mt-2">
                            <i class="bi bi-hourglass"></i> View Pending Payments
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