<?php
include 'db_connection.php';

// Default to showing unread messages
$unread_only = !isset($_GET['show_all']);
$filter_sql = $unread_only ? " WHERE is_read = 0" : "";

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Delete message
    if (isset($_GET['delete_id'])) {
        $delete_id = intval($_GET['delete_id']);
        $delete_sql = "DELETE FROM contacts WHERE id = $delete_id";
        if ($conn->query($delete_sql)) {
            $success_msg = "Message deleted successfully!";
        } else {
            $error_msg = "Error deleting message: " . $conn->error;
        }
    }
    
    // Mark as read/unread
    if (isset($_GET['mark_id'])) {
        $mark_id = intval($_GET['mark_id']);
        $mark_status = intval($_GET['status']);
        $mark_sql = "UPDATE contacts SET is_read = $mark_status WHERE id = $mark_id";
        if ($conn->query($mark_sql)) {
            $success_msg = "Message status updated!";
        } else {
            $error_msg = "Error updating message status: " . $conn->error;
        }
    }
    
    // Mark all visible as read
    if (isset($_GET['mark_all']) && $_GET['mark_all'] == '1') {
        $base_sql = "UPDATE contacts SET is_read = 1";
        $sql = $unread_only ? $base_sql . " WHERE is_read = 0" : $base_sql;
        if ($conn->query($sql)) {
            $success_msg = "All messages marked as read!";
        } else {
            $error_msg = "Error updating messages: " . $conn->error;
        }
    }
}

// Fetch messages
$sql = "SELECT * FROM contacts" . $filter_sql . " ORDER BY created_at DESC";
$result = $conn->query($sql);

// Count messages
$unread_count = $conn->query("SELECT COUNT(*) FROM contacts WHERE is_read = 0")->fetch_row()[0];
$total_count = $conn->query("SELECT COUNT(*) FROM contacts")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --light-bg: #f8f9fc;
            --dark-text: #5a5c69;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fc;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .page-header {
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.75rem;
            margin: 0;
        }
        
        .alert {
            border-radius: 0.35rem;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            vertical-align: middle;
            border-bottom: none;
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-top: 1px solid #e3e6f0;
        }
        
        .table tr:nth-child(even) {
            background-color: #f8f9fc;
        }
        
        .table tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        .unread-row {
            background-color: #f0f7ff !important;
            font-weight: 600;
        }
        
        .badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.35rem 0.65rem;
            border-radius: 0.25rem;
        }
        
        .badge-read {
            background-color: var(--success-color);
            color: white;
        }
        
        .badge-unread {
            background-color: var(--danger-color);
            color: white;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin: 0 3px;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            transform: scale(1.1);
        }
        
        .btn-read {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-unread {
            background-color: var(--warning-color);
            color: white;
        }
        
        .btn-delete {
            background-color: var(--danger-color);
            color: white;
        }
        
        .message-preview {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: pointer;
        }
        
        .message-modal .modal-content {
            border-radius: 0.35rem;
            border: none;
        }
        
        .message-modal .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-bottom: none;
            border-radius: 0.35rem 0.35rem 0 0;
        }
        
        .message-modal .modal-title {
            font-weight: 600;
        }
        
        .message-modal .close {
            color: white;
            opacity: 1;
        }
        
        .timestamp {
            font-size: 0.8rem;
            color: #858796;
        }
        
        .filter-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 15px;
        }
        
        .btn-group-toggle .btn {
            border-radius: 0.25rem !important;
        }
        
        .btn-mark-all-read {
            background-color: var(--info-color);
            color: white;
        }
        
        .btn-mark-all-read:hover {
            background-color: #2c9faf;
            color: white;
        }
        
        .btn-dashboard {
            background-color: var(--primary-color);
            color: white;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .header-row {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .filter-buttons {
                width: 100%;
                flex-wrap: wrap;
            }
            
            .table td, .table th {
                padding: 0.75rem;
            }
            
            .action-btns {
                display: flex;
                flex-direction: column;
                gap: 5px;
            }
            
            .action-btn {
                margin: 2px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <div class="header-row">
                <h1 class="page-title">
                    <i class="bi bi-envelope-fill me-2"></i>
                    <?php echo $unread_only ? 'Unread Messages' : 'All Messages'; ?>
                </h1>
                <a href="admin_dashboard.php" class="btn btn-dashboard">
                    <i class="bi bi-speedometer2 me-1"></i> Dashboard
                </a>
            </div>
            
            <div class="filter-buttons">
                <div class="btn-group btn-group-toggle" role="group">
                    <a href="?" class="btn <?php echo $unread_only ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="bi bi-envelope me-1"></i> Unread (<?php echo $unread_count; ?>)
                    </a>
                    <a href="?show_all=1" class="btn <?php echo !$unread_only ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        <i class="bi bi-envelope-open me-1"></i> All (<?php echo $total_count; ?>)
                    </a>
                </div>
                
                <?php if ($unread_count > 0 && $unread_only): ?>
                    <a href="?mark_all=1" class="btn btn-mark-all-read">
                        <i class="bi bi-check-all me-1"></i> Mark All as Read
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="<?php echo $row['is_read'] == 0 ? 'unread-row' : ''; ?>" data-message-id="<?php echo $row['id']; ?>">
                                <td>
                                    <span class="badge <?php echo $row['is_read'] == 1 ? 'badge-read' : 'badge-unread'; ?>">
                                        <?php echo $row['is_read'] == 1 ? 'Read' : 'Unread'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="message-preview" data-bs-toggle="modal" data-bs-target="#messageModal<?php echo $row['id']; ?>">
                                    <?php echo htmlspecialchars(substr($row['message'], 0, 50)); ?>
                                    <?php if (strlen($row['message']) > 50) echo '...'; ?>
                                </td>
                                <td class="timestamp">
                                    <?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php if ($row['is_read'] == 0): ?>
                                            <a href="?mark_id=<?php echo $row['id']; ?>&status=1<?php echo $unread_only ? '' : '&show_all=1'; ?>" class="action-btn btn-read" title="Mark as read">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="?mark_id=<?php echo $row['id']; ?>&status=0<?php echo $unread_only ? '' : '&show_all=1'; ?>" class="action-btn btn-unread" title="Mark as unread">
                                                <i class="bi bi-envelope"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="?delete_id=<?php echo $row['id']; ?><?php echo $unread_only ? '' : '&show_all=1'; ?>" class="action-btn btn-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this message?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Message Modal -->
                            <div class="modal fade message-modal" id="messageModal<?php echo $row['id']; ?>" tabindex="-1" aria-labelledby="messageModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="messageModalLabel<?php echo $row['id']; ?>">
                                                Message from <?php echo htmlspecialchars($row['name']); ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <strong>Email:</strong>
                                                <p><?php echo htmlspecialchars($row['email']); ?></p>
                                            </div>
                                            <div class="mb-3">
                                                <strong>Date:</strong>
                                                <p><?php echo date('M j, Y g:i A', strtotime($row['created_at'])); ?></p>
                                            </div>
                                            <div>
                                                <strong>Message:</strong>
                                                <p><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <?php if ($row['is_read'] == 0): ?>
                                                <a href="?mark_id=<?php echo $row['id']; ?>&status=1<?php echo $unread_only ? '' : '&show_all=1'; ?>" class="btn btn-success">
                                                    <i class="bi bi-check-lg me-1"></i> Mark as Read
                                                </a>
                                            <?php endif; ?>
                                            <a href="?delete_id=<?php echo $row['id']; ?><?php echo $unread_only ? '' : '&show_all=1'; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this message?');">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-envelope-open text-muted" style="font-size: 3rem;"></i>
                                    <h5 class="mt-3">No messages found</h5>
                                    <?php if ($unread_only): ?>
                                        <p class="text-muted">You have no unread messages</p>
                                        <a href="?show_all=1" class="btn btn-primary mt-2">
                                            <i class="bi bi-envelope-open me-1"></i> View All Messages
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-mark as read when modal is shown
        document.querySelectorAll('.message-preview').forEach(preview => {
            preview.addEventListener('click', function() {
                const messageId = this.getAttribute('data-bs-target').replace('#messageModal', '');
                const row = document.querySelector(`tr[data-message-id="${messageId}"]`);
                if (row && row.classList.contains('unread-row')) {
                    // You could add AJAX here to mark as read without page reload
                }
            });
        });
    </script>
</body>
</html>