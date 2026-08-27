<?php
// Start session for authentication
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// CORS HEADERS - Allow mobile access
// ============================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Simple router/autoloader
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/'
    ];
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Basic routing
$request = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Remove query string
$request = strtok($request, '?');
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if ($basePath !== '/' && $basePath !== '\\') {
    $request = preg_replace('#^' . preg_quote($basePath, '#') . '#', '', $request);
}
$request = trim($request, '/');

// Support API calls through a standard index.php query URL.
$apiQuery = isset($_GET['api']) ? trim($_GET['api'], '/') : '';
if ($apiQuery !== '') {
    $request = 'api/' . $apiQuery;
}

// Accept requests addressed through the front controller explicitly.
if (strpos($request, 'index.php/') === 0) {
    $request = substr($request, strlen('index.php/'));
}

// ============================================
// DETECT API REQUESTS
// ============================================
$isApiRequest = false;
$apiPath = '';

if (strpos($request, 'api/') === 0) {
    $isApiRequest = true;
    $apiPath = substr($request, 4);
} elseif ($request === 'api') {
    $isApiRequest = true;
    $apiPath = '';
}

if ($isApiRequest) {
    $request = $apiPath;
}

// Use page query parameter for frontend navigation
$viewPage = isset($_GET['page']) && !empty($_GET['page']) ? trim($_GET['page'], '/') : null;
if ($viewPage && !$isApiRequest) {
    $request = trim($viewPage, '/');
}

if (!$viewPage && ($request === 'index.php' || $request === 'index.php/')) {
    $request = '';
}

$parts = explode('/', $request);
$controller = isset($parts[0]) && !empty($parts[0]) ? $parts[0] : 'home';
$action = isset($parts[1]) && !empty($parts[1]) ? $parts[1] : 'index';
$id = isset($parts[2]) && !empty($parts[2]) ? $parts[2] : null;

// ============================================
// FIX: Parse URL correctly - handle numeric IDs
// ============================================
if (is_numeric($action)) {
    $id = $action;
    $action = 'index';
}

// Public frontend routes and auth routes
$publicRoutes = ['login', 'home', 'register', 'timeout'];
$isPublicRoute = in_array($controller, $publicRoutes) || ($controller === 'auth' && in_array($action, ['login', 'check']));

$hasValidSession = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// ============================================
// Handle root/home entry point
// ============================================
if (!$isApiRequest && $controller === 'home' && $action === 'index') {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    if ($hasValidSession) {
        header('Location: /dashboard');
        exit;
    }
    header('Location: /login');
    exit;
}

// ============================================
// Handle Login (HTML view) - ALLOW ACCESS
// ============================================
if (!$isApiRequest && $controller === 'login') {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    if ($hasValidSession) {
        header('Location: /dashboard');
        exit;
    }
    include '../views/login.php';
    exit;
}

// ============================================
// Handle Public Views (no auth required)
// ============================================
if (!$isApiRequest && in_array($controller, ['register', 'timeout'])) {
    $viewFile = "../views/{$controller}.php";
    if (file_exists($viewFile)) {
        header('Content-Type: text/html; charset=utf-8');
        include $viewFile;
        exit;
    }
}

// ============================================
// SENSITIVE PAGE AUTH - ONLY for HTML pages, NOT API
// ============================================
if (!$isApiRequest && $hasValidSession) {
    $sensitivePages = ['mobile-attendance', 'attendance', 'dashboard', 'reports', 'facilities', 'visitors'];
    $isSensitivePage = in_array($controller, $sensitivePages) || in_array($action, $sensitivePages);
    
    if ($isSensitivePage) {
        if (!isset($_SESSION['sensitive_auth']) || $_SESSION['sensitive_auth'] !== true) {
            $_SESSION = [];
            session_destroy();
            session_start();
            $redirect = $controller;
            if ($action !== 'index') {
                $redirect .= '/' . $action;
            }
            if ($id) {
                $redirect .= '/' . $id;
            }
            header('Location: /login?redirect=' . urlencode($redirect));
            exit;
        }

        $sensitiveTimeout = 900; // 15 minutes
        if (isset($_SESSION['sensitive_last_activity']) && 
            (time() - $_SESSION['sensitive_last_activity'] > $sensitiveTimeout)) {
            $_SESSION = [];
            session_destroy();
            session_start();
            $redirect = $controller;
            if ($action !== 'index') {
                $redirect .= '/' . $action;
            }
            if ($id) {
                $redirect .= '/' . $id;
            }
            header('Location: /login?redirect=' . urlencode($redirect));
            exit;
        }
        $_SESSION['sensitive_last_activity'] = time();
    }
}

// ============================================
// Protect ALL frontend routes if not logged in
// ============================================
if (!$isApiRequest && !$hasValidSession) {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    $redirect = $controller;
    if ($action !== 'index') {
        $redirect .= '/' . $action;
    }
    if ($id) {
        $redirect .= '/' . $id;
    }
    header('Location: /login?redirect=' . urlencode($redirect));
    exit;
}

// Handle API auth check
if ($isApiRequest && $controller === 'auth' && $action === 'check') {
    require_once '../controllers/AuthController.php';
    $authController = new AuthController();
    $result = $authController->checkAuth();
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// ============================================
// PUBLIC API ROUTES - NO AUTH REQUIRED
// ============================================
$publicApiRoutes = [
    'visitor/register',
    'visitor/checkout-by-identifier',
    'auth/login',
    'auth/check'
];

$isPublicApiRoute = false;

if ($isApiRequest && $controller === 'visitor' && !empty($id) && is_numeric($id)) {
    $isPublicApiRoute = true;
} else {
    $currentRoute = $controller . '/' . $action;
    $isPublicApiRoute = in_array($currentRoute, $publicApiRoutes);
}

// Protect API routes (except public ones)
if ($isApiRequest && !$hasValidSession && !$isPublicApiRoute) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized', 'message' => 'Please login first']);
    exit;
}

// ============================================
// HANDLE API ROUTES
// ============================================
if ($isApiRequest) {
    header('Content-Type: application/json');
    
    $postData = [];
    $uploadedFiles = [];
    
    if ($method === 'POST' || $method === 'PUT') {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        if (strpos($contentType, 'multipart/form-data') !== false) {
            $postData = $_POST;
            $uploadedFiles = $_FILES;
        } elseif (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $postData = json_decode($input, true) ?: [];
        } else {
            $postData = $_POST;
        }
    }
    
    switch ($controller) {
        case 'auth':
            require_once '../controllers/AuthController.php';
            $authController = new AuthController();
            if ($action === 'login' && $method === 'POST') {
                $employeeId = $_POST['employee_id'] ?? '';
                $password = $_POST['password'] ?? '';
                $result = $authController->login($employeeId, $password);
                echo json_encode($result);
            } elseif ($action === 'logout' && ($method === 'GET' || $method === 'POST')) {
                $authController->logout();
                if ($method === 'GET') {
                    header('Location: /login');
                    exit;
                }
                echo json_encode(['success' => true]);
            }
            break;
            
        case 'dashboard':
            require_once '../controllers/DashboardController.php';
            $dashboardController = new DashboardController();
            if ($method === 'GET') {
                $data = $dashboardController->getDashboardData();
                echo json_encode($data);
            }
            break;
            
        case 'attendance':
            require_once '../controllers/AttendanceController.php';
            $attendanceController = new AttendanceController();
            
            if ($method === 'GET') {
                $date = $_GET['date'] ?? null;
                $day = $_GET['day'] ?? null;
                $faculty = $_GET['faculty'] ?? null;
                
                if ($action === 'schedules' || $action === '') {
                    if ($date) {
                        $result = $attendanceController->getSchedules($date);
                    } elseif ($day) {
                        $result = $attendanceController->getSchedulesByDay($day);
                    } elseif ($faculty) {
                        $result = $attendanceController->getFacultySchedules($faculty);
                    } else {
                        $result = $attendanceController->getSchedules(date('Y-m-d'));
                    }
                    echo json_encode($result);
                    
                } elseif ($action === 'records') {
                    if ($date) {
                        $result = $attendanceController->getAttendanceRecords($date);
                    } elseif ($day) {
                        $result = $attendanceController->getAttendanceRecordsByDay($day);
                    } else {
                        $result = $attendanceController->getAttendanceRecords(date('Y-m-d'));
                    }
                    echo json_encode($result);
                    
                } elseif ($action === 'online') {
                    $result = $attendanceController->getOnlineClasses($date);
                    echo json_encode($result);
                    
                } elseif ($action === 'archive') {
                    $faculty = $_GET['faculty'] ?? null;
                    $result = $attendanceController->getArchivedRecords($faculty);
                    echo json_encode($result);
                    
                } elseif ($action === 'delete' && !empty($id)) {
                    $result = $attendanceController->deleteRecord($id);
                    echo json_encode($result);
                    
                } else {
                    echo json_encode(['error' => 'Invalid attendance endpoint', 'action' => $action]);
                }
                
            } elseif ($method === 'POST') {
                if ($action === 'mark') {
                    $result = $attendanceController->markAttendance($postData, $uploadedFiles);
                    echo json_encode($result);
                } elseif ($action === 'archive') {
                    $result = $attendanceController->archiveRecords($postData);
                    echo json_encode($result);
                } elseif ($action === 'restore') {
                    $result = $attendanceController->restoreRecords($postData);
                    echo json_encode($result);
                } else {
                    echo json_encode(['error' => 'Invalid attendance POST endpoint']);
                }
            } elseif ($method === 'DELETE') {
                if ($action === 'record' && !empty($id)) {
                    $result = $attendanceController->deleteRecord($id);
                    echo json_encode($result);
                } else {
                    echo json_encode(['error' => 'Invalid attendance DELETE endpoint']);
                }
            }
            break;

        case 'schedule':
            require_once '../controllers/ScheduleController.php';
            $scheduleController = new ScheduleController();
            
            if ($method === 'GET') {
                if ($action === 'today' || $action === '') {
                    $result = $scheduleController->getTodaySchedules();
                    echo json_encode($result);
                } elseif ($action === 'mobile-view') {
                    $result = $scheduleController->getMobileView();
                    echo json_encode($result);
                } elseif ($action === 'all-rooms') {
                    $result = $scheduleController->getAllRoomsWithSchedules();
                    echo json_encode($result);
                } elseif ($action === 'room' && !empty($id)) {
                    $result = $scheduleController->getSchedulesByRoom(['room' => $id]);
                    echo json_encode($result);
                } else {
                    echo json_encode(['error' => 'Invalid schedule endpoint']);
                }
            }
            break;
            
        case 'facility':
            require_once '../controllers/FacilityController.php';
            $facilityController = new FacilityController();
            
            if ($method === 'GET') {
                if ($action === 'all' || $action === '') {
                    $result = $facilityController->getAllFacilities();
                    echo json_encode($result);
                } elseif ($action === 'damaged') {
                    $result = $facilityController->getDamagedEquipment();
                    echo json_encode($result);
                } elseif ($action === 'reports') {
                    $status = $_GET['status'] ?? null;
                    $result = $facilityController->getFacilityReports($status);
                    echo json_encode($result);
                } elseif ($action === 'archive') {
                    $result = $facilityController->getArchive();
                    echo json_encode($result);
                } elseif ($action === 'room' && !empty($id)) {
                    $methodName = 'getFacilitiesByRoom';
                    if (method_exists($facilityController, $methodName)) {
                        $result = $facilityController->$methodName($id);
                    } else {
                        $result = ['error' => 'Facility room lookup is not available', 'room_id' => $id];
                    }
                    echo json_encode($result);
                } else {
                    echo json_encode(['error' => 'Invalid facility endpoint', 'action' => $action]);
                }
            } elseif ($method === 'POST') {
                if ($action === 'add') {
                    $result = $facilityController->addFacility($postData);
                    echo json_encode($result);
                } elseif ($action === 'report') {
                    $result = $facilityController->reportBrokenEquipment($postData);
                    echo json_encode($result);
                } elseif ($action === 'archive') {
                    $result = $facilityController->archiveReports($postData);
                    echo json_encode($result);
                } elseif ($action === 'restore') {
                    $result = $facilityController->restoreReports($postData);
                    echo json_encode($result);
                } elseif ($action === 'update' && !empty($id)) {
                    $result = $facilityController->updateFacility($id, $postData);
                    echo json_encode($result);
                } elseif ($action === 'update-report' && !empty($id)) {
                    $status = $postData['status'] ?? null;
                    $resolvedDate = $postData['resolved_date'] ?? null;
                    $result = $facilityController->updateReportStatus($id, $status, $resolvedDate);
                    echo json_encode($result);
                } else {
                    echo json_encode(['error' => 'Invalid facility POST endpoint']);
                }
                
            } elseif ($method === 'PUT') {
                if ($action === 'update' && !empty($id)) {
                    $result = $facilityController->updateFacility($id, $postData);
                    echo json_encode($result);
                } else {
                    echo json_encode(['error' => 'Invalid facility PUT endpoint']);
                }
            } elseif ($method === 'DELETE') {
                if ($action === 'delete' && !empty($id)) {
                    $result = $facilityController->deleteFacility($id);
                    echo json_encode($result);
                } else {
                    echo json_encode(['error' => 'Invalid facility DELETE endpoint']);
                }
            }
            break;
            
        case 'visitor':
            require_once '../controllers/VisitorController.php';
            $visitorController = new VisitorController();
            
            if ($method === 'GET') {
                if (!empty($id) && is_numeric($id)) {
                    $result = $visitorController->getVisitor($id);
                    echo json_encode($result);
                    break;
                }
                
                if ($action === 'today' || $action === '') {
                    $result = $visitorController->getTodayVisitors();
                    echo json_encode($result);
                } elseif ($action === 'inside') {
                    $result = $visitorController->getVisitorsInside();
                    echo json_encode($result);
                } elseif ($action === 'history') {
                    $startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
                    $endDate = $_GET['end'] ?? date('Y-m-d');
                    $result = $visitorController->getVisitorHistory($startDate, $endDate);
                    echo json_encode($result);
                } elseif ($action === 'all') {
                    $result = $visitorController->getAllVisitors();
                    echo json_encode($result);
                } elseif ($action === 'pending-approvals') {
                    $result = $visitorController->getPendingApprovals();
                    echo json_encode($result);
                } elseif ($action === 'statistics') {
                    $result = $visitorController->getStatistics();
                    echo json_encode($result);
                } elseif ($action === 'search') {
                    $keyword = $_GET['keyword'] ?? '';
                    $result = $visitorController->searchVisitors($keyword);
                    echo json_encode($result);
                } elseif ($action === 'archive') {
                    $result = $visitorController->getArchive();
                    echo json_encode($result);
                } else {
                    echo json_encode(['error' => 'Invalid visitor endpoint']);
                }
            } elseif ($method === 'POST') {
                if ($action === 'register') {
                    $result = $visitorController->registerVisitor($postData, $uploadedFiles);
                    echo json_encode($result);
                    break;
                }
                if ($action === 'checkout-by-identifier') {
                    $result = $visitorController->checkoutByIdentifier($postData);
                    echo json_encode($result);
                    break;
                }
                if ($action === 'entry') {
                    $data = $postData ?: $_POST;
                    $result = $visitorController->logEntry($data);
                    echo json_encode($result);
                } elseif ($action === 'archive') {
                    $result = $visitorController->archiveVisitors($postData);
                    echo json_encode($result);
                } elseif ($action === 'restore') {
                    $result = $visitorController->restoreVisitors($postData);
                    echo json_encode($result);
                } elseif ($action === 'exit' && !empty($id)) {
                    $result = $visitorController->logExit($id);
                    echo json_encode($result);
                } elseif ($action === 'update' && !empty($id)) {
                    $data = $postData ?: $_POST;
                    $result = $visitorController->updateVisitor($id, $data);
                    echo json_encode($result);
                } elseif ($action === 'approve' && !empty($id)) {
                    $adminId = $_SESSION['user_id'] ?? null;
                    $result = $visitorController->approveID($id, $adminId);
                    echo json_encode($result);
                } else {
                    echo json_encode(['error' => 'Invalid visitor POST endpoint']);
                }
            } elseif ($method === 'DELETE') {
                if ($action === 'delete' && !empty($id)) {
                    $result = $visitorController->deleteVisitor($id);
                    echo json_encode($result);
                } else {
                    echo json_encode(['error' => 'Invalid visitor DELETE endpoint']);
                }
            } elseif ($method === 'PUT') {
                if ($action === 'visitor' && !empty($id)) {
                    $data = $postData ?: $_POST;
                    $result = $visitorController->updateVisitor($id, $data);
                    echo json_encode($result);
                } else {
                    echo json_encode(['error' => 'Invalid visitor PUT endpoint']);
                }
            }
            break;
            
        case 'report':
            require_once '../controllers/ReportController.php';
            $reportController = new ReportController();
            
            if ($method === 'GET') {
                $startDate = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
                $endDate = $_GET['end'] ?? date('Y-m-d');
                $type = $_GET['type'] ?? null;
                
                if ($action === 'attendance') {
                    $result = $reportController->getAttendanceReport($startDate, $endDate);
                    echo json_encode($result);
                } elseif ($action === 'facility') {
                    $result = $reportController->getFacilityReport($startDate, $endDate);
                    echo json_encode($result);
                } elseif ($action === 'visitor') {
                    $result = $reportController->getVisitorReport($startDate, $endDate);
                    echo json_encode($result);
                } elseif ($action === 'comprehensive') {
                    $result = $reportController->getComprehensive($startDate, $endDate);
                    echo json_encode($result);
                } elseif ($action === 'details') {
                    $result = $reportController->getDetails($type, $startDate, $endDate);
                    echo json_encode($result);
                } else {
                    echo json_encode(['error' => 'Invalid report endpoint', 'action' => $action]);
                }
            }
            break;

        default:
            echo json_encode([
                'status' => 'success',
                'message' => 'Monitoring System API v1.0',
                'endpoints' => [
                    'auth' => ['POST /api/auth/login', 'POST /api/auth/logout', 'GET /api/auth/check'],
                    'attendance' => ['GET /api/attendance/schedules', 'GET /api/attendance/records', 'POST /api/attendance/mark', 'POST /api/attendance/archive', 'POST /api/attendance/restore'],
                    'schedule' => ['GET /api/schedule/mobile-view', 'GET /api/schedule/today'],
                    'facility' => ['GET /api/facility/all', 'POST /api/facility/add', 'POST /api/facility/report', 'POST /api/facility/archive', 'POST /api/facility/restore'],
                    'visitor' => ['POST /api/visitor/register (no auth required)', 'GET /api/visitor/today', 'POST /api/visitor/entry', 'POST /api/visitor/archive', 'POST /api/visitor/restore'],
                    'report' => ['GET /api/report/attendance', 'GET /api/report/facility', 'GET /api/report/visitor']
                ]
            ]);
            break;
    }
    exit;
}

// ============================================
// HANDLE FRONTEND VIEWS (HTML)
// ============================================
$allowedViews = ['dashboard', 'attendance', 'facilities', 'visitors', 'reports', 'online-classes', 'archive', 'qrcode', 'mobile-attendance', 'mobile-facilities'];
$viewFile = null;

if ($controller === 'home' || $controller === '') {
    if (isset($_SESSION['user_id'])) {
        $viewFile = '../views/dashboard.php';
    } else {
        include '../views/login.php';
        exit;
    }
} elseif (in_array($controller, $allowedViews)) {
    $viewFile = "../views/{$controller}.php";
} elseif (in_array($action, $allowedViews)) {
    $viewFile = "../views/{$action}.php";
} else {
    if (isset($_SESSION['user_id'])) {
        $viewFile = '../views/dashboard.php';
    } else {
        include '../views/login.php';
        exit;
    }
}

if ($viewFile && file_exists($viewFile)) {
    header_remove('Content-Type');
    header('Content-Type: text/html; charset=utf-8');
    include $viewFile;
} else {
    header('HTTP/1.0 404 Not Found');
    echo '<h1>404 - Page Not Found</h1>';
    echo '<p>The requested page could not be found.</p>';
}
?>