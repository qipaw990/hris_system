<?php
/**
 * Auto Mark Absent (Alpha) for Employees Who Didn't Check-In
 * Run this script via cron after work hours (e.g., 18:00 daily)
 * 
 * Cron example: 0 18 * * 1-5 /usr/bin/php /path/to/mark_absent.php
 */

require_once __DIR__ . '/../config/database.php';

// Set timezone
date_default_timezone_set('Asia/Jakarta');

$today = date('Y-m-d');
$currentDay = date('l'); // Monday, Tuesday, etc.
$logFile = __DIR__ . '/logs/auto_absent_' . date('Y-m') . '.log';

// Ensure log directory exists
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo "[$timestamp] $message\n";
}

logMessage("=== Starting Auto-Absent Process ===");
logMessage("Date: $today ($currentDay)");

try {
    // Check if today is weekend
    if (in_array($currentDay, ['Saturday', 'Sunday'])) {
        logMessage("Today is weekend. Skipping auto-absent.");
        exit(0);
    }
    
    // Check if today is a holiday (optional - skip if table doesn't exist)
    try {
        $holidayCheck = $pdo->prepare("SELECT * FROM holidays WHERE holiday_date = ? AND is_active = 1");
        $holidayCheck->execute([$today]);
        if ($holidayCheck->fetch()) {
            logMessage("Today is a holiday. Skipping auto-absent.");
            exit(0);
        }
    } catch (PDOException $e) {
        logMessage("Warning: Holidays table not found. Continuing without holiday check.");
        logMessage("Run: php database/holidays_schema.sql to create holidays table");
    }
    
    // Get all active employees
    $employeesStmt = $pdo->query("SELECT id, employee_code, CONCAT(first_name, ' ', last_name) as name 
                                  FROM employees 
                                  WHERE employment_status = 'Active'");
    $employees = $employeesStmt->fetchAll();
    
    logMessage("Found " . count($employees) . " active employees");
    
    $markedCount = 0;
    $skippedCount = 0;
    
    foreach ($employees as $employee) {
        $employeeId = $employee['id'];
        $employeeName = $employee['name'];
        $employeeCode = $employee['employee_code'];
        
        // Check if employee already has attendance record for today
        $attendanceCheck = $pdo->prepare("SELECT id, status FROM attendance 
                                          WHERE employee_id = ? AND attendance_date = ?");
        $attendanceCheck->execute([$employeeId, $today]);
        $attendance = $attendanceCheck->fetch();
        
        if ($attendance) {
            logMessage("[$employeeCode] $employeeName - Already has attendance record (Status: {$attendance['status']})");
            $skippedCount++;
            continue;
        }
        
        // Check if employee has approved leave for today
        $leaveCheck = $pdo->prepare("SELECT lr.id, lt.leave_name 
                                     FROM leave_requests lr
                                     JOIN leave_types lt ON lr.leave_type_id = lt.id
                                     WHERE lr.employee_id = ? 
                                     AND lr.status = 'Approved'
                                     AND ? BETWEEN lr.start_date AND lr.end_date");
        $leaveCheck->execute([$employeeId, $today]);
        $leave = $leaveCheck->fetch();
        
        if ($leave) {
            logMessage("[$employeeCode] $employeeName - On approved leave ({$leave['leave_name']})");
            $skippedCount++;
            continue;
        }
        
        // Mark as absent (Alpha)
        $insertStmt = $pdo->prepare("INSERT INTO attendance 
                                     (employee_id, attendance_date, status, notes, created_at) 
                                     VALUES (?, ?, 'Alpha', 'Auto-marked absent - No check-in', NOW())");
        $insertStmt->execute([$employeeId, $today]);
        
        logMessage("[$employeeCode] $employeeName - Marked as ALPHA (Auto-absent)");
        $markedCount++;
    }
    
    logMessage("=== Auto-Absent Process Completed ===");
    logMessage("Total Marked as Absent: $markedCount");
    logMessage("Total Skipped: $skippedCount");
    logMessage("Total Processed: " . count($employees));
    
} catch (PDOException $e) {
    logMessage("ERROR: Database error - " . $e->getMessage());
    exit(1);
} catch (Exception $e) {
    logMessage("ERROR: " . $e->getMessage());
    exit(1);
}
