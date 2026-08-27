<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Time Out</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .timeout-card {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 550px;
            width: 100%;
            animation: slideUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .logo-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-header .icon {
            font-size: 64px;
            color: #f5576c;
        }
        
        .logo-header h1 {
            color: #1a1a2e;
            font-weight: 700;
            margin-top: 10px;
            font-size: 28px;
        }
        
        .logo-header p {
            color: #6c757d;
        }
        
        .form-control {
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            padding: 12px 16px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #f5576c;
            box-shadow: 0 0 0 3px rgba(245, 87, 108, 0.1);
        }
        
        .btn-timeout {
            background: var(--primary-gradient);
            border: none;
            padding: 14px 40px;
            font-weight: 600;
            letter-spacing: 0.5px;
            border-radius: 12px;
            color: white;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 16px;
        }
        
        .btn-timeout:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(245, 87, 108, 0.4);
            color: white;
        }
        
        .btn-timeout:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .result-container {
            margin-top: 20px;
            display: none;
        }
        
        .result-container .alert {
            border-radius: 12px;
            animation: slideUp 0.4s ease;
        }
        
        .visitor-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            margin: 10px 0;
        }
        
        .visitor-info .label {
            color: #6c757d;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .visitor-info .value {
            font-weight: 600;
            font-size: 16px;
            color: #1a1a2e;
        }
        
        .badge-timeout {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="timeout-card">
        <div class="logo-header">
            <div class="icon"><i class="bi bi-door-open"></i></div>
            <h1>Visitor Time Out</h1>
            <p>Enter your Visitor ID or Contact Number to record your departure</p>
        </div>
        
        <form id="timeoutForm">
            <div class="mb-4">
                <label class="form-label" style="font-weight: 600;">Visitor Identifier</label>
                <input type="text" class="form-control" name="identifier" 
                       placeholder="Enter QR Code or Contact Number" required>
                <small class="text-muted">You can find your QR Code on the registration confirmation</small>
            </div>
            
            <button type="submit" class="btn-timeout">
                <span class="spinner-border spinner-border-sm d-none" role="status" id="loadingSpinner"></span>
                <i class="bi bi-door-open"></i> Record Time Out
            </button>
        </form>
        
        <div class="result-container" id="resultContainer">
            <div class="alert" id="resultMessage" role="alert"></div>
            <div id="visitorDetails"></div>
        </div>
        
        <div class="text-center mt-4">
            <a href="/" class="text-decoration-none">
                <i class="bi bi-house"></i> Back to Home
            </a>
        </div>
    </div>

    <script>
        document.getElementById('timeoutForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const identifier = this.querySelector('input[name="identifier"]').value.trim();
            if (!identifier) {
                showResult('Please enter your identifier', 'warning');
                return;
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const spinner = document.getElementById('loadingSpinner');
            
            // Show loading
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            submitBtn.innerHTML = 'Processing...';
            
            try {
                const response = await fetch('/api/visitor/checkout-by-identifier', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ identifier })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Find visitor details
                    const visitor = result.visitor || {};
                    const duration = result.duration || 'N/A';
                    
                    let visitorHtml = `
                        <div class="visitor-info">
                            <div class="row">
                                <div class="col-6">
                                    <div class="label">Visitor Name</div>
                                    <div class="value">${visitor.visitor_name || 'N/A'}</div>
                                </div>
                                <div class="col-6">
                                    <div class="label">Status</div>
                                    <div class="value"><span class="badge badge-timeout">Checked Out</span></div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <div class="label">Time In</div>
                                    <div class="value">${formatDate(visitor.time_in)}</div>
                                </div>
                                <div class="col-6">
                                    <div class="label">Visit Duration</div>
                                    <div class="value">${duration}</div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    showResult('Time out recorded successfully!', 'success', visitorHtml);
                    
                    // Clear form
                    this.reset();
                } else {
                    showResult(result.error || 'Failed to record time out', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                showResult('An error occurred. Please try again.', 'danger');
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('d-none');
                submitBtn.innerHTML = '<i class="bi bi-door-open"></i> Record Time Out';
            }
        });
        
        function showResult(message, type, details = '') {
            const container = document.getElementById('resultContainer');
            const alert = document.getElementById('resultMessage');
            const detailsDiv = document.getElementById('visitorDetails');
            
            container.style.display = 'block';
            alert.className = `alert alert-${type}`;
            alert.textContent = message;
            
            if (details) {
                detailsDiv.innerHTML = details;
            } else {
                detailsDiv.innerHTML = '';
            }
            
            // Auto scroll to result
            container.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleString('en-PH', {
                year: 'numeric',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });
        }
        
        // Auto-focus
        document.querySelector('input[name="identifier"]').focus();
    </script>
</body>
</html>