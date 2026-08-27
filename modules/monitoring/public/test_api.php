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

$baseUrl = 'http://localhost/Monitoring3/public';

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