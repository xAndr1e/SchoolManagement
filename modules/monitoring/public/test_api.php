<?php
// Test script for API endpoints
echo "<h2>Monitoring System API Test</h2>";

function testEndpoint($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    
    if ($data && $method === 'POST') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$baseUrl = rtrim(getenv('MONITORING_BASE_URL') ?: '', '/');
if ($baseUrl === '') {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $baseUrl = $scheme . $host . ($basePath === '' ? '' : $basePath) . '/public';
}

echo "<h3>Testing Attendance Schedules</h3>";
$result = testEndpoint($baseUrl . '/attendance/schedules?date=2024-01-15');
echo "<pre>"; print_r($result); echo "</pre>";

echo "<h3>Testing Facility Reports</h3>";
$result = testEndpoint($baseUrl . '/facility/damaged');
echo "<pre>"; print_r($result); echo "</pre>";

echo "<h3>Testing Visitors Today</h3>";
$result = testEndpoint($baseUrl . '/visitor/today');
echo "<pre>"; print_r($result); echo "</pre>";

echo "<h3>Testing Comprehensive Report</h3>";
$result = testEndpoint($baseUrl . '/report/comprehensive?start=2024-01-01&end=2024-01-31');
echo "<pre>"; print_r($result); echo "</pre>";
?>
