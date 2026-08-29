<?php
// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /');
    exit;
}

$registrationUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/register';
$timeoutUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/timeout';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor QR Codes - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: #f8f9fa;
        }
        
        .qr-container {
            padding: 40px 20px;
        }
        
        .qr-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }
        
        .qr-card:hover {
            transform: translateY(-5px);
        }
        
        .qr-code-wrapper {
            background: white;
            padding: 20px;
            border-radius: 16px;
            border: 2px dashed #dee2e6;
            display: inline-block;
        }
        
        .qr-code-wrapper img {
            max-width: 250px;
            height: auto;
        }
        
        .qr-label {
            font-weight: 600;
            color: #1a1a2e;
            margin-top: 15px;
            font-size: 18px;
        }
        
        .qr-url {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            word-break: break-all;
            font-size: 14px;
            color: #6c757d;
            margin-top: 10px;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .instructions {
            background: #e3f2fd;
            border-radius: 12px;
            padding: 20px;
        }
        
        .instructions li {
            margin-bottom: 8px;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            .qr-card {
                box-shadow: none;
                border: 1px solid #dee2e6;
            }
            body {
                background: white;
            }
        }
    </style>
</head>
<body>
    <div class="container qr-container">
        <div class="d-flex justify-content-between align-items-center mb-4 no-print">
            <h2><i class="bi bi-qr-code"></i> Visitor QR Codes</h2>
            <div>
                <button class="btn btn-print me-2" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print QR Codes
                </button>
                <a href="/dashboard" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
        
        <div class="row">
            <!-- Registration QR Code -->
            <div class="col-md-6">
                <div class="qr-card text-center">
                    <h4 class="mb-3"><i class="bi bi-person-plus" style="color: #28a745;"></i> Visitor Registration</h4>
                    <div class="qr-code-wrapper">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?php echo urlencode($registrationUrl); ?>" 
                             alt="Registration QR Code">
                    </div>
                    <div class="qr-label">Scan to Register</div>
                    <div class="qr-url">
                        <i class="bi bi-link-45deg"></i> <?php echo $registrationUrl; ?>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-success">Active</span>
                        <span class="badge bg-info">Public</span>
                    </div>
                </div>
            </div>
            
            <!-- Time Out QR Code -->
            <div class="col-md-6">
                <div class="qr-card text-center">
                    <h4 class="mb-3"><i class="bi bi-door-open" style="color: #dc3545;"></i> Visitor Time Out</h4>
                    <div class="qr-code-wrapper">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=<?php echo urlencode($timeoutUrl); ?>" 
                             alt="Time Out QR Code">
                    </div>
                    <div class="qr-label">Scan to Check Out</div>
                    <div class="qr-url">
                        <i class="bi bi-link-45deg"></i> <?php echo $timeoutUrl; ?>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-warning text-dark">Active</span>
                        <span class="badge bg-info">Public</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="row no-print">
            <div class="col-12">
                <div class="instructions">
                    <h5><i class="bi bi-info-circle"></i> How to Use</h5>
                    <ol>
                        <li><strong>Print</strong> these QR codes and display them at the school entrance.</li>
                        <li><strong>Registration QR Code</strong> - Visitors scan this to register their visit.</li>
                        <li><strong>Time Out QR Code</strong> - Visitors scan this when leaving to record their departure.</li>
                        <li>Both QR codes are <strong>permanent</strong> and can be used indefinitely.</li>
                        <li>Visitors will be directed to mobile-friendly pages.</li>
                    </ol>
                    <div class="alert alert-warning mt-2">
                        <i class="bi bi-exclamation-triangle"></i> Make sure to display these QR codes in a visible location at the entrance.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>