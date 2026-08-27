<?php
require_once '../models/Schedule.php';
require_once '../controllers/ScheduleController.php';

// Test 1: Direct database query
echo "=== TEST 1: Direct Database Query ===\n";
try {
    $schedule = new Schedule();
    $db = $schedule->getDb();
    
    $today = date('Y-m-d');
    $day = date('l');
    
    echo "Today: $today, Day: $day\n\n";
    
    // Check if table exists and has data
    $stmt = $db->query("SHOW TABLES LIKE 'cc_schedule'");
    if ($stmt->rowCount() > 0) {
        echo "✓ cc_schedule table exists\n";
        
        // Count total records
        $stmt = $db->query("SELECT COUNT(*) as total FROM cc_schedule");
        $result = $stmt->fetch();
        echo "Total records: " . $result['total'] . "\n";
        
        // Get all records
        $stmt = $db->query("SELECT * FROM cc_schedule LIMIT 5");
        $records = $stmt->fetchAll();
        echo "\nSample records:\n";
        print_r($records);
        
        // Check specific date
        $stmt = $db->prepare("SELECT * FROM cc_schedule WHERE schedule_date = ?");
        $stmt->execute([$today]);
        $todayRecords = $stmt->fetchAll();
        echo "\nRecords for today ($today): " . count($todayRecords) . "\n";
        print_r($todayRecords);
        
    } else {
        echo "✗ cc_schedule table does NOT exist!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n\n=== TEST 2: Schedule Controller ===\n";
try {
    $controller = new ScheduleController();
    $schedules = $controller->getTodaySchedules();
    
    echo "Schedules found: " . count($schedules) . "\n";
    echo "Raw schedule data:\n";
    print_r($schedules);
    
    // Check time fields specifically
    if (!empty($schedules)) {
        foreach ($schedules as $index => $schedule) {
            echo "\nSchedule #" . ($index + 1) . ":\n";
            echo "  start_time: " . (isset($schedule['start_time']) ? $schedule['start_time'] : 'NOT SET') . "\n";
            echo "  end_time: " . (isset($schedule['end_time']) ? $schedule['end_time'] : 'NOT SET') . "\n";
            echo "  schedule_date: " . (isset($schedule['schedule_date']) ? $schedule['schedule_date'] : 'NOT SET') . "\n";
            echo "  day: " . (isset($schedule['day']) ? $schedule['day'] : 'NOT SET') . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>