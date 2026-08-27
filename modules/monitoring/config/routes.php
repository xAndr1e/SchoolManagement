<?php
// API Routes — Updated router base path to match the Monitoring2 directory
$router = new Router('/Monitoring2/public');

// Auth Routes
$router->addRoute('POST', 'api/auth/login', 'AuthController', 'login');
$router->addRoute('POST', 'api/auth/logout', 'AuthController', 'logout');
$router->addRoute('GET', 'api/auth/check', 'AuthController', 'check');

// Dashboard Routes
$router->addRoute('GET', 'api/dashboard', 'DashboardController', 'getDashboard');

// Attendance Routes
$router->addRoute('GET', 'api/attendance/schedules', 'AttendanceController', 'getSchedulesByDay');
$router->addRoute('GET', 'api/attendance/records', 'AttendanceController', 'getRecords');
$router->addRoute('GET', 'api/attendance/online', 'AttendanceController', 'getOnlineClasses');
$router->addRoute('GET', 'api/attendance/archive', 'AttendanceController', 'getArchive');
$router->addRoute('GET', 'api/attendance/faculty/{faculty}', 'AttendanceController', 'getFacultySchedules');
$router->addRoute('POST', 'api/attendance/mark', 'AttendanceController', 'markAttendance');
$router->addRoute('POST', 'api/attendance/archive', 'AttendanceController', 'archiveRecords');
$router->addRoute('POST', 'api/attendance/schedule', 'AttendanceController', 'addSchedule');
$router->addRoute('PUT', 'api/attendance/schedule/{id}', 'AttendanceController', 'updateSchedule');
$router->addRoute('DELETE', 'api/attendance/record/{id}', 'AttendanceController', 'deleteRecord');

// Facility Routes
$router->addRoute('GET', 'api/facility/room/{room}', 'FacilityController', 'getByRoom');
$router->addRoute('GET', 'api/facility/damaged', 'FacilityController', 'getDamaged');
$router->addRoute('GET', 'api/facility/reports', 'FacilityController', 'getReports');
$router->addRoute('GET', 'api/facility/reports/{status}', 'FacilityController', 'getReportsByStatus');
$router->addRoute('GET', 'api/facility/all', 'FacilityController', 'getAll');
$router->addRoute('POST', 'api/facility/add', 'FacilityController', 'addFacility');
$router->addRoute('POST', 'api/facility/report', 'FacilityController', 'reportBrokenEquipment');
$router->addRoute('PUT', 'api/facility/update/{id}', 'FacilityController', 'updateFacility');
$router->addRoute('PUT', 'api/facility/report/{id}', 'FacilityController', 'updateReport');
$router->addRoute('DELETE', 'api/facility/{id}', 'FacilityController', 'deleteFacility');
$router->addRoute('GET', 'api/facility/archive', 'FacilityController', 'getArchive');

// Visitor Routes
$router->addRoute('GET', 'api/visitor/today', 'VisitorController', 'getTodayVisitors');
$router->addRoute('GET', 'api/visitor/inside', 'VisitorController', 'getVisitorsInside');
$router->addRoute('GET', 'api/visitor/history', 'VisitorController', 'getVisitorHistory');
$router->addRoute('GET', 'api/visitor/all', 'VisitorController', 'getAllVisitors');
$router->addRoute('GET', 'api/visitor/pending-approvals', 'VisitorController', 'getPendingApprovals');
$router->addRoute('GET', 'api/visitor/statistics', 'VisitorController', 'getStatistics');
$router->addRoute('GET', 'api/visitor/search', 'VisitorController', 'searchVisitors');
$router->addRoute('GET', 'api/visitor/{id}', 'VisitorController', 'getVisitor');
$router->addRoute('GET', 'api/visitor/archive', 'VisitorController', 'getArchive');

// Public Visitor Routes (no auth required)
$router->addRoute('POST', 'api/visitor/register', 'VisitorController', 'registerVisitor');
$router->addRoute('POST', 'api/visitor/checkout-by-identifier', 'VisitorController', 'checkoutByIdentifier');

// Protected Visitor Routes (auth required)
$router->addRoute('POST', 'api/visitor/entry', 'VisitorController', 'logEntry');
$router->addRoute('POST', 'api/visitor/exit/{id}', 'VisitorController', 'logExit');
$router->addRoute('POST', 'api/visitor/update/{id}', 'VisitorController', 'updateVisitor');
$router->addRoute('POST', 'api/visitor/approve/{id}', 'VisitorController', 'approveID');
$router->addRoute('PUT', 'api/visitor/{id}', 'VisitorController', 'updateVisitor');
$router->addRoute('DELETE', 'api/visitor/{id}', 'VisitorController', 'deleteVisitor');

// Report Routes
$router->addRoute('GET', 'api/report/attendance', 'ReportController', 'getAttendanceReport');
$router->addRoute('GET', 'api/report/facility', 'ReportController', 'getFacilityReport');
$router->addRoute('GET', 'api/report/visitor', 'ReportController', 'getVisitorReport');
$router->addRoute('GET', 'api/report/comprehensive', 'ReportController', 'getComprehensive');
$router->addRoute('GET', 'api/report/details', 'ReportController', 'getDetails');

// Schedule Routes - Mobile Monitoring
$router->addRoute('GET', 'api/schedule/today', 'ScheduleController', 'getTodaySchedules');
$router->addRoute('GET', 'api/schedule/room/{room}', 'ScheduleController', 'getSchedulesByRoom');
$router->addRoute('GET', 'api/schedule/all-rooms', 'ScheduleController', 'getAllRoomsWithSchedules');
$router->addRoute('GET', 'api/schedule/mobile-view', 'ScheduleController', 'getMobileView');

// Frontend Routes
$router->addRoute('GET', '', 'ViewController', 'dashboard');
$router->addRoute('GET', 'dashboard', 'ViewController', 'dashboard');
$router->addRoute('GET', 'attendance', 'ViewController', 'attendance');
$router->addRoute('GET', 'facilities', 'ViewController', 'facilities');
$router->addRoute('GET', 'visitors', 'ViewController', 'visitors');
$router->addRoute('GET', 'reports', 'ViewController', 'reports');
$router->addRoute('GET', 'online-classes', 'ViewController', 'onlineClasses');
$router->addRoute('GET', 'archive', 'ViewController', 'archive');
$router->addRoute('GET', 'register', 'ViewController', 'register');
$router->addRoute('GET', 'timeout', 'ViewController', 'timeout');
$router->addRoute('GET', 'qrcode', 'ViewController', 'qrcode');
$router->addRoute('GET', 'mobile-attendance', 'ViewController', 'mobileAttendance');
$router->addRoute('GET', 'mobile-facilities', 'ViewController', 'mobileFacilities');

$router->addRoute('POST', 'api/attendance/archive', 'AttendanceController', 'archiveRecords');
$router->addRoute('POST', 'api/facility/archive', 'FacilityController', 'archiveReports');
$router->addRoute('POST', 'api/visitor/archive', 'VisitorController', 'archiveVisitors');

return $router;
?>