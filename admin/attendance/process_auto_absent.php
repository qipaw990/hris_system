<?php
/**
 * Auto Absent Process
 * Mark all employees without attendance today as Alpha
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['error'] = 'Unauthorized access';
    header('Location: /hrm/admin/attendance/index.php');
    exit();
}

$today = date('Y-m-d');

try {
    // Get all active employees
    $employeesStmt = $pdo->query("SELECT id, CONCAT(first_name, ' ', last_name) as name 
                                  FROM employees 
                                  WHERE employment_status = 'Active'");
    $allEmployees = $employeesStmt->fetchAll();
    
    // Get employees who already have attendance today
    $attendedStmt = $pdo->prepare("SELECT DISTINCT employee_id 
                                   FROM attendance 
                                   WHERE attendance_date = ?");
    $attendedStmt->execute([$today]);
    $attendedEmployees = $attendedStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Find employees without attendance
    $absentCount = 0;
    $insertStmt = $pdo->prepare("INSERT INTO attendance 
                                (employee_id, attendance_date, status, notes) 
                                VALUES (?, ?, 'Alpha', 'Auto-marked by system')");
    
    foreach ($allEmployees as $employee) {
        if (!in_array($employee['id'], $attendedEmployees)) {
            $insertStmt->execute([$employee['id'], $today]);
            $absentCount++;
        }
    }
    
    if ($absentCount > 0) {
        $_SESSION['success'] = "Auto absent berhasil! $absentCount karyawan ditandai Alpha.";
    } else {
        $_SESSION['info'] = 'Semua karyawan sudah memiliki record kehadiran hari ini.';
    }
    
} catch (PDOException $e) {
    error_log("Auto absent error: " . $e->getMessage());
    $_SESSION['error'] = 'Terjadi kesalahan saat memproses auto absent';
}

header('Location: /hrm/admin/attendance/index.php');
exit();
