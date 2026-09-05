<?php
// Global error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error: [$errno] $errstr in $errfile on line $errline");
    
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'Internal Server Error',
            'message' => 'An unexpected error occurred'
        ]);
        exit;
    }
    return false;
});

set_exception_handler(function($exception) {
    error_log("Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'Internal Server Error',
            'message' => 'An unexpected error occurred: ' . $exception->getMessage()
        ]);
        exit;
    }
    
    // For non-API requests
    if (!headers_sent()) {
        http_response_code(500);
    }
    echo "<h1>500 Internal Server Error</h1>";
    echo "<p>An unexpected error occurred. Please try again later.</p>";
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === 'localhost' || $host === '127.0.0.1') {
        echo "<pre>" . htmlspecialchars($exception->getMessage()) . "</pre>";
        echo "<pre>" . htmlspecialchars($exception->getTraceAsString()) . "</pre>";
    }
    exit;
});

// Include this at the top of public/index.php
// require_once __DIR__ . '/../config/error_handler.php';
