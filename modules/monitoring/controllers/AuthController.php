<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }

    private function startSession() {
        if (headers_sent()) {
            return;
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // POST /api/auth/login - called from index.php
    public function login($employeeId, $password) {
        try {
            if (empty($employeeId) || empty($password)) {
                return ['success' => false, 'message' => 'Employee ID and password are required'];
            }
            
            $user = $this->userModel->authenticate($employeeId, $password);
            if ($user) {
                $this->startSession();
                session_regenerate_id(true);
                $_SESSION = [];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['fullname'] = $user['fullname'];
                
                // ⭐ Set sensitive auth flag for secure pages
                $_SESSION['sensitive_auth'] = true;
                $_SESSION['sensitive_last_activity'] = time();
                
                // ⭐ NEW: Remember Me functionality - 30 days
                if (isset($_POST['remember_me']) && $_POST['remember_me'] === 'on') {
                    // Set cookie to expire in 30 days
                    setcookie(session_name(), session_id(), time() + (86400 * 30), '/');
                    $_SESSION['remember_me'] = true;
                    // Extend sensitive timeout to 30 days
                    $_SESSION['sensitive_timeout'] = 2592000; // 30 days
                } else {
                    $_SESSION['sensitive_timeout'] = 900; // 15 minutes
                }
                
                $this->userModel->updateLastLogin($user['id']);
                
                // ⭐ FIX: Get redirect from POST
                $redirect = isset($_POST['redirect']) && !empty($_POST['redirect']) 
                    ? trim($_POST['redirect'], '/') 
                    : 'dashboard';
                
                unset($user['password']);
                return [
                    'success' => true, 
                    'user' => $user,
                    'redirect' => '/modules/monitoring/public/index.php?page=' . urlencode($redirect)
                ];
            }
            return ['success' => false, 'message' => 'Invalid username or password'];
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }
    
    // POST/GET /api/auth/logout - called from index.php
    public function logout() {
        try {
            $this->startSession();
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, 
                    $params['path'], $params['domain'], 
                    $params['secure'], $params['httponly']
                );
            }
            session_destroy();
            return ['success' => true];
        } catch (Exception $e) {
            error_log("Logout error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Logout failed'];
        }
    }
    
    // GET /api/auth/check - called from index.php
    public function checkAuth() {
        try {
            $this->startSession();
            return ['authenticated' => isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])];
        } catch (Exception $e) {
            error_log("Auth check error: " . $e->getMessage());
            return ['authenticated' => false];
        }
    }
    
    // ⭐ FIXED: Check if session is valid for sensitive pages
    public function checkSensitiveAuth() {
        try {
            $this->startSession();
            
            // Check if user is logged in
            if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
                return ['authenticated' => false, 'reason' => 'Not logged in'];
            }
            
            // Check sensitive auth flag
            if (!isset($_SESSION['sensitive_auth']) || $_SESSION['sensitive_auth'] !== true) {
                return ['authenticated' => false, 'reason' => 'Sensitive auth required'];
            }
            
            // ⭐ FIXED: Dynamic session timeout (30 days if remember me, else 15 min)
            $timeout = $_SESSION['sensitive_timeout'] ?? 900; // default 15 min
            if (isset($_SESSION['sensitive_last_activity']) && 
                (time() - $_SESSION['sensitive_last_activity'] > $timeout)) {
                return ['authenticated' => false, 'reason' => 'Session expired'];
            }
            
            // Update last activity
            $_SESSION['sensitive_last_activity'] = time();
            
            return ['authenticated' => true];
        } catch (Exception $e) {
            error_log("Auth check error: " . $e->getMessage());
            return ['authenticated' => false, 'reason' => 'Error checking auth'];
        }
    }
    
    // ⭐ NEW: Auto-login for local network access
    public function autoLoginLocal() {
        return false;
    }
    
    // ⭐ NEW: Force logout and clear session
    public function forceLogout() {
        try {
            $this->startSession();
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, 
                    $params['path'], $params['domain'], 
                    $params['secure'], $params['httponly']
                );
            }
            session_destroy();
            return true;
        } catch (Exception $e) {
            error_log("Force logout error: " . $e->getMessage());
            return false;
        }
    }
    
    public function getCurrentUser() {
        try {
            $this->startSession();
            if (isset($_SESSION['user_id'])) {
                return [
                    'id' => $_SESSION['user_id'],
                    'username' => $_SESSION['username'],
                    'role' => $_SESSION['role'],
                    'fullname' => $_SESSION['fullname']
                ];
            }
            return null;
        } catch (Exception $e) {
            error_log("Get current user error: " . $e->getMessage());
            return null;
        }
    }
    
    public function requireAuth() {
        if (!$this->checkAuth()) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized', 'message' => 'Please login first']);
            exit;
        }
    }
    
    // ⭐ Require sensitive auth (for secure pages)
    public function requireSensitiveAuth() {
        $result = $this->checkSensitiveAuth();
        if (!$result['authenticated']) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'error' => 'Unauthorized', 
                'message' => 'Please login again to access this page',
                'reason' => $result['reason'] ?? 'Authentication required'
            ]);
            exit;
        }
    }
}
?>