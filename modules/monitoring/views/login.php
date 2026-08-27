<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Monitoring System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 420px;
            width: 100%;
            animation: slideUp 0.5s ease;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h2 {
            color: #2c3e50;
            font-weight: 700;
        }
        .login-header p {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        .login-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 10px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        .alert {
            border-radius: 10px;
            display: none;
        }
        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
            font-size: 0.85rem;
        }
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .redirect-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: #6c757d;
            text-align: center;
        }
        .redirect-info i {
            margin-right: 6px;
        }
        .redirect-info .page-name {
            font-weight: 600;
            color: #2c3e50;
        }
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.2em;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h2>Monitoring System</h2>
            <p>Please login to continue</p>
        </div>
        
        <!-- Show redirect info -->
        <div class="redirect-info" id="redirectInfo">
            <i class="bi bi-arrow-right-circle"></i> 
            You'll be redirected to: <span class="page-name" id="redirectPageName">Loading...</span>
        </div>
        
        <div class="alert alert-danger" id="loginAlert" role="alert">
            <i class="bi bi-exclamation-triangle"></i>
            <span id="alertMessage">Invalid credentials</span>
        </div>
        
        <form id="loginForm" onsubmit="return handleLogin(event)">
            <div class="form-group">
                  <label for="employee_id"><i class="bi bi-person"></i> Employee ID</label>
                  <input type="text" class="form-control" id="employee_id" name="employee_id" 
                      placeholder="Enter employee ID" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password"><i class="bi bi-key"></i> Password</label>
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Enter password" required>
            </div>
            
            <!-- ⭐ Hidden redirect field -->
            <input type="hidden" id="redirectField" name="redirect" value="">
            
            <div class="form-group">
                <button type="submit" class="btn btn-login" id="loginBtn">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </div>
            
            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">
                        Remember me
                    </label>
                </div>
            </div>
        </form>
        
        <div class="footer-text">
            <i class="bi bi-info-circle"></i> Use your shared School Management employee ID and password
        </div>
    </div>
    
    <!-- Toast Container -->
    <div class="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ============================================
        // DETECT DEVICE TYPE
        // ============================================
        function isMobileDevice() {
            return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile/i.test(navigator.userAgent) 
                || window.innerWidth <= 768;
        }

        // Get redirect URL based on device
        function getRedirectUrl() {
            // Check if there's a redirect parameter in the URL
            const urlParams = new URLSearchParams(window.location.search);
            const redirectParam = urlParams.get('redirect');
            
            // If there's a redirect parameter, use it
            if (redirectParam) {
                return '/' + redirectParam;
            }
            
            // Otherwise, redirect based on device
            return isMobileDevice() ? '/mobile-attendance' : '/dashboard';
        }

        // Get device type label
        function getDeviceType() {
            return isMobileDevice() ? '📱 Mobile' : '💻 Desktop';
        }

        // ============================================
        // PAGE LOAD
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            const redirectUrl = getRedirectUrl();
            const pageName = redirectUrl === '/mobile-attendance' ? 'Mobile View' : 
                            redirectUrl === '/dashboard' ? 'Dashboard' : 
                            redirectUrl.replace('/', '');
            
            // Show where user will be redirected
            document.getElementById('redirectPageName').textContent = pageName;
            document.getElementById('redirectField').value = redirectUrl.replace('/', '');
            
            console.log('Redirect URL:', redirectUrl);
            console.log('Device:', getDeviceType());
        });

        // ============================================
        // TOAST NOTIFICATIONS
        // ============================================
        function showToast(message, type = 'success') {
            const colors = {
                success: '#28a745',
                error: '#dc3545',
                warning: '#ffc107',
                info: '#17a2b8'
            };

            const container = document.querySelector('.toast-container');
            const toastElement = document.createElement('div');
            toastElement.className = 'toast align-items-center text-white border-0';
            toastElement.setAttribute('role', 'alert');
            toastElement.setAttribute('aria-live', 'assertive');
            toastElement.setAttribute('aria-atomic', 'true');
            toastElement.style.backgroundColor = colors[type] || colors.info;
            toastElement.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;

            container.appendChild(toastElement);
            const toastInstance = new bootstrap.Toast(toastElement, {
                autohide: true,
                delay: 3000
            });
            toastInstance.show();
        }

        // ============================================
        // HANDLE LOGIN
        // ============================================
        async function handleLogin(event) {
            event.preventDefault();
            
            const employeeId = document.getElementById('employee_id').value;
            const password = document.getElementById('password').value;
            const redirect = document.getElementById('redirectField').value;
            const loginBtn = document.getElementById('loginBtn');
            const alertBox = document.getElementById('loginAlert');
            
            // Show loading state
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...';
            
            try {
                const formData = new URLSearchParams();
                formData.append('employee_id', employeeId);
                formData.append('password', password);
                formData.append('redirect', redirect);
                
                const response = await fetch('/modules/monitoring/public/index.php?api=auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showToast('Login successful! Redirecting...', 'success');
                    
                    // Use the redirect from the server response or fallback
                    const redirectUrl = data.redirect || '/' + redirect;
                    setTimeout(() => {
                        window.location.replace(redirectUrl);
                    }, 500);
                } else {
                    document.getElementById('alertMessage').textContent = data.message || 'Invalid credentials';
                    alertBox.style.display = 'block';
                    showToast(data.message || 'Login failed', 'error');
                }
            } catch (error) {
                console.error('Login error:', error);
                document.getElementById('alertMessage').textContent = 'An error occurred. Please try again.';
                alertBox.style.display = 'block';
                showToast('An error occurred. Please try again.', 'error');
            } finally {
                loginBtn.disabled = false;
                loginBtn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Sign In';
            }
            
            return false;
        }
        
        // ============================================
        // AUTO-HIDE ALERT
        // ============================================
        document.getElementById('loginAlert').addEventListener('click', function() {
            this.style.display = 'none';
        });
        
        // ============================================
        // CHECK AUTH - Redirect based on device
        // ============================================
        async function checkAuth() {
            try {
                const response = await fetch('/modules/monitoring/public/index.php?api=auth/check', {
                    cache: 'no-store'
                });
                const data = await response.json();
                if (data.authenticated === true) {
                    const redirectUrl = getRedirectUrl();
                    window.location.href = redirectUrl;
                }
            } catch (error) {
                console.error('Auth check error:', error);
            }
        }
        checkAuth();
    </script>
</body>
</html>