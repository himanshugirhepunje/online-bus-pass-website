<?php
session_start();
require 'db_connection.php';

// Validate session
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Regenerate session ID to prevent fixation
if (!isset($_SESSION['initiated'])) {
    session_regenerate_id();
    $_SESSION['initiated'] = true;
}

// Sanitize session data
$user_id = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
$user_name = htmlspecialchars($_SESSION['user'], ENT_QUOTES, 'UTF-8');

if (!$user_id) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Get registration status
$registration = null;
$stmt = $conn->prepare("SELECT status, rejection_reason FROM students WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $registration = $result->fetch_assoc();
    $stmt->close();
}

// Get latest payment and pass info
$payment = null;
$stmt = $conn->prepare("SELECT id, payment_status, valid_until, transaction_id FROM payments WHERE user_id = ? ORDER BY id DESC LIMIT 1");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --warning: #f6c23e;
            --danger: #e74a3b;
        }
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', sans-serif;
        }
        .dashboard-card {
            border: 0;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            margin-bottom: 1.5rem;
        }
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            font-weight: 700;
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 0.35rem 0.65rem;
        }
        .btn-download {
            background-color: var(--primary);
            color: white;
        }
        .btn-download:hover {
            background-color: #2e59d9;
            color: white;
        }
        .pass-expiry {
            background-color: #fff8e1;
            border-left: 4px solid var(--warning);
        }
        .processing-spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            vertical-align: middle;
            border: 0.2em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner 0.75s linear infinite;
        }
        @keyframes spinner {
            to { transform: rotate(360deg); }
        }
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Student Dashboard</h1>
            <div>
                <span class="me-3">Welcome, <strong><?= $user_name ?></strong></span>
                <a href="logout.php" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <!-- Registration Status Card -->
        <div class="card dashboard-card mb-4">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-graduate me-2"></i>Registration Status
                </h6>
                <?php if ($registration): ?>
                    <span class="badge rounded-pill 
                        <?= $registration['status'] == 'approved' ? 'bg-success' : 
                           ($registration['status'] == 'pending' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                        <?= ucfirst($registration['status']) ?>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!$registration): ?>
                    <p>You haven't registered yet. Complete registration to get your student pass.</p>
                    <a href="../HTML File/registration.html" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Register Now
                    </a>
                <?php else: ?>
                    <?php if ($registration['status'] == 'rejected'): ?>
                        <div class="alert alert-danger">
                            <strong>Rejection Reason:</strong> 
                            <?= htmlspecialchars($registration['rejection_reason'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <a href="re-apply-registration.php" class="btn btn-warning">
                            <i class="fas fa-redo me-2"></i>Re-apply
                        </a>
                    <?php elseif ($registration['status'] == 'approved'): ?>
                        <p>Your registration has been approved. You can now apply for your student pass.</p>
                    <?php else: ?>
                        <p>Your registration is being processed. Please check back later.</p>
                        <div class="mt-2">
                            <span class="processing-spinner" aria-hidden="true"></span>
                            <span class="sr-only">Processing...</span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($registration && $registration['status'] == 'approved'): ?>
            <!-- Payment & Pass Status Card -->
            <div class="card dashboard-card mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-credit-card me-2"></i>Pass Status
                    </h6>
                    <?php if ($payment): ?>
                        <span class="badge rounded-pill 
                            <?= $payment['payment_status'] == 'success' ? 'bg-success' : 
                               ($payment['payment_status'] == 'pending' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                            <?= ucfirst($payment['payment_status']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if (!$payment): ?>
                        <p>No pass application found. Apply now to get your student pass.</p>
                        <a href="../HTML File/createpass.html" class="btn btn-primary">
                            <i class="fas fa-ticket-alt me-2"></i>Apply for Pass
                        </a>
                    <?php else: ?>
                        <?php if ($payment['payment_status'] == 'success'): ?>
                            <?php 
                                $valid_until = new DateTime($payment['valid_until']);
                                $now = new DateTime();
                                $pass_status = ($valid_until < $now) ? 'expired' : 'active';
                            ?>

                            <?php if ($pass_status == 'expired'): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Your pass has expired!</strong> Please renew it now.
                                </div>
                            <?php elseif ($now->diff($valid_until)->days < 5): ?>
                                <div class="alert pass-expiry mb-4 p-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Your pass expires soon!</strong> Renew now to avoid interruption.
                                </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Pass Details</h5>
                                    <p><strong>Status:</strong> 
                                        <?php if ($pass_status == 'active'): ?>
                                            <span class="text-success">Active</span>
                                        <?php else: ?>
                                            <span class="text-danger">Expired</span>
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>Valid Until:</strong> <?= $valid_until->format('F j, Y') ?></p>
                                </div>
                                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                                    <?php if (!empty($payment['transaction_id'])): ?>
                                        <a href="printpass.php?txn=<?= urlencode($payment['transaction_id']) ?>" class="btn btn-secondary">
                                            <i class="fas fa-print me-2"></i>Print Pass
                                        </a>
                                        <br class="d-md-none"><br class="d-md-none">
                                        <a href="renew_pass.php" class="btn btn-warning mt-2 mt-md-0">
                                            <i class="fas fa-sync-alt me-2"></i>Renew Pass
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php elseif ($payment['payment_status'] == 'pending'): ?>
                            <p>Your payment is being processed. This may take up to 24 hours.</p>
                            <div class="mt-2">
                                <span class="processing-spinner" aria-hidden="true"></span>
                                <span class="sr-only">Processing...</span>
                            </div>
                            <script>
                                setTimeout(() => location.reload(), 30000);
                            </script>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <strong>Payment Failed:</strong> Please try again or contact support.
                            </div>
                            <a href="re-apply-payment.php?txn=<?= urlencode($payment['transaction_id']) ?>" class="btn btn-danger">
                                <i class="fas fa-credit-card me-2"></i>Retry Payment
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
