<?php
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize pass data
$passData = null;
$error = "";

// Automatically fetch pass for logged-in user
$user_id = $_SESSION['user_id'];

// SQL query to fetch pass details for current user
$query = "SELECT 
            p.user_id,
            p.payment_status,
            p.transaction_id,
            p.cost,
            p.date,
            p.valid_until,
           
            s.id as student_id,
            s.name,
            s.phone,
            s.collage_name,
            s.passport_photo,
            p.source,
            p.destination
          FROM payments p
          JOIN students s ON p.user_id = s.user_id
          WHERE s.user_id = ?
          ORDER BY p.id DESC
          LIMIT 1";

// Using prepared statement for security
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $passData = $result->fetch_assoc();
    $_SESSION['pass_data'] = $passData;
} else {
    $error = "No pass found for your account! Please complete your payment first.";
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MSRTC Bus Pass Card</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --msrtc-red: #a40000;
            --msrtc-dark-red: #8a0000;
            --msrtc-light: #f9f3f3;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .card-container {
            width: 85mm;
            height: 54mm;
            margin: 20px auto;
            border: 2px solid var(--msrtc-red);
            border-radius: 8px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            background: white;
            position: relative;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--msrtc-red), var(--msrtc-dark-red));
            color: white;
            padding: 6px;
            text-align: center;
            font-size: 10px;
            position: relative;
        }
        
        .card-header::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #f8d100, #f8d100 33%, #fff 33%, #fff 66%, #138808 66%);
        }
        
        .card-body {
            padding: 8px;
            display: flex;
            height: calc(100% - 40px);
        }
        
        .card-photo {
            width: 30mm;
            height: 35mm;
            border: 1px solid #ddd;
            border-radius: 3px;
            object-fit: cover;
            margin-right: 8px;
        }
        
        .card-details {
            flex: 1;
            font-size: 9px;
            line-height: 1.3;
        }
        
        .card-details strong {
            font-weight: 600;
        }
        
        .card-number {
            font-size: 10px;
            font-weight: bold;
            color: var(--msrtc-red);
            margin-bottom: 3px;
        }
        
        .card-qr {
            width: 15mm;
            height: 15mm;
            border: 1px solid #eee;
            margin-top: 3px;
        }
        
        .card-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 7px;
            text-align: center;
            padding: 3px;
            background-color: var(--msrtc-light);
            border-top: 1px dashed #ddd;
        }
        
        .status-badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            display: inline-block;
            margin-left: 5px;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-expired {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .print-only {
            display: none;
        }
        
        @media print {
            body {
                background: none;
                margin: 0;
                padding: 0;
            }
            
            .no-print {
                display: none;
            }
            
            .print-only {
                display: block;
            }
            
            .card-container {
                box-shadow: none;
                margin: 0;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="container py-3">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h4 class="mb-0 text-primary">
                <i class="bi bi-card-checklist"></i> Your Bus Pass Card
            </h4>
            <div>
                <a href="dashboard.php" class="btn btn-sm btn-outline-secondary me-2">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
                <button onclick="window.print()" class="btn btn-sm btn-primary">
                    <i class="bi bi-printer"></i> Print
                </button>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center py-2">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div class="small"><?php echo htmlspecialchars($error); ?></div>
            </div>
            <div class="text-center no-print mt-3">
                <a href="apply_pass.php" class="btn btn-sm btn-primary">
                    <i class="bi bi-credit-card"></i> Apply for Bus Pass
                </a>
            </div>
        <?php endif; ?>

        <?php if ($passData): 
            $validUntil = date('d M Y', strtotime($passData['valid_until']));
            $isExpired = strtotime($passData['valid_until']) < time();
            $isPending = $passData['payment_status'] === 'pending';
        ?>
        <div class="card-container" id="passCard">
            <div class="card-header">
                <strong>MAHARASHTRA STATE ROAD TRANSPORT CORPORATION</strong><br>
                <small>Student Concessional Bus Pass</small>
            </div>
            
            <div class="card-body">
                <?php if (!empty($passData['passport_photo'])): ?>
                    <img src="<?php echo htmlspecialchars($passData['passport_photo']); ?>" class="card-photo" alt="Passport Photo">
                <?php else: ?>
                    <div class="card-photo d-flex align-items-center justify-content-center bg-light">
                        <span class="text-muted small">No Photo</span>
                    </div>
                <?php endif; ?>
                
                <div class="card-details">
                    <div class="card-number">
                        MSRTC/<?php echo date('Y'); ?>/<?php echo str_pad($passData['user_id'], 5, '0', STR_PAD_LEFT); ?>
                    </div>
                    
                    <div><strong>Name:</strong> <?php echo htmlspecialchars($passData['name']); ?></div>
                    <div><strong>College:</strong> <?php echo substr(htmlspecialchars($passData['collage_name']), 0, 20); ?></div>
                    <div><strong>Route:</strong> <?php echo substr(htmlspecialchars($passData['source']), 0, 10); ?>-<?php echo substr(htmlspecialchars($passData['destination']), 0, 10); ?></div>
                    <div><strong>Valid Until:</strong> <?php echo $validUntil; ?>
                        <?php if ($isPending): ?>
                            <span class="status-badge status-pending">PENDING</span>
                        <?php elseif ($isExpired): ?>
                            <span class="status-badge status-expired">EXPIRED</span>
                        <?php else: ?>
                            <span class="status-badge status-active">ACTIVE</span>
                        <?php endif; ?>
                    </div>
                    
                  
                        <img src="../Images/qr-code.png" class="card-qr" alt="QR Code">
                  
                </div>
            </div>
            
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <div>ID: <?php echo str_pad($passData['user_id'], 5, '0', STR_PAD_LEFT); ?></div>
                    <div>MSRTC © <?php echo date('Y'); ?></div>
                    <div class="print-only">Computer Generated</div>
                </div>
            </div>
        </div>

        <div class="text-center mt-3 no-print">
            <button onclick="window.print()" class="btn btn-sm btn-primary me-2">
                <i class="bi bi-printer"></i> Print Card
            </button>
            
            <?php if ($isExpired): ?>
                <a href="renew_pass.php" class="btn btn-sm btn-warning">
                    <i class="bi bi-arrow-repeat"></i> Renew
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-print if coming from payment success page
        if (window.location.search.includes('print=true')) {
            window.print();
        }
        
        // Set proper print size
        function beforePrint() {
            document.body.style.width = '85mm';
            document.body.style.height = '54mm';
        }
        
        function afterPrint() {
            document.body.style.width = '';
            document.body.style.height = '';
        }
        
        if (window.matchMedia) {
            const mediaQueryList = window.matchMedia('print');
            mediaQueryList.addListener((mql) => {
                if (mql.matches) {
                    beforePrint();
                } else {
                    afterPrint();
                }
            });
        }
        
        window.onbeforeprint = beforePrint;
        window.onafterprint = afterPrint;
    </script>
</body>
</html>