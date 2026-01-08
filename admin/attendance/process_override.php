<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    $_SESSION['error'] = 'Silakan login terlebih dahulu';
    header('Location: /hrm/admin/login.php');
    exit();
}

// Check if user is admin/hr
$userRole = strtolower($_SESSION['role']);
if (!in_array($userRole, ['admin', 'hr'])) {
    $_SESSION['error'] = 'Akses ditolak';
    header('Location: /hrm/admin/attendance/auto_absent.php');
    exit();
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error'] = 'Invalid CSRF token';
    header('Location: /hrm/admin/attendance/auto_absent.php');
    exit();
}

// Get input
$attendanceId = intval($_POST['attendance_id'] ?? 0);
$newStatus = $_POST['status'] ?? '';
$overrideReason = trim($_POST['override_reason'] ?? '');

// Validate
if ($attendanceId <= 0 || empty($newStatus) || empty($overrideReason)) {
    $_SESSION['error'] = 'Semua field wajib diisi';
    header('Location: /hrm/admin/attendance/auto_absent.php');
    exit();
}

// Validate status
$validStatuses = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha', 'Cuti'];
if (!in_array($newStatus, $validStatuses)) {
    $_SESSION['error'] = 'Status tidak valid';
    header('Location: /hrm/admin/attendance/auto_absent.php');
    exit();
}

try {
    // Get current attendance record
    $checkStmt = $pdo->prepare("SELECT * FROM attendance WHERE id = ?");
    $checkStmt->execute([$attendanceId]);
    $attendance = $checkStmt->fetch();
    
    if (!$attendance) {
        $_SESSION['error'] = 'Record kehadiran tidak ditemukan';
        header('Location: /hrm/admin/attendance/auto_absent.php');
        exit();
    }
    
    // Update status
    $updateStmt = $pdo->prepare("UPDATE attendance SET 
                                 status = ?,
                                 notes = CONCAT(notes, '\n\n[Override by Admin] ', ?, ' - Changed to: ', ?, ' at ', NOW()),
                                 updated_at = NOW()
                                 WHERE id = ?");
    $updateStmt->execute([$newStatus, $overrideReason, $newStatus, $attendanceId]);
    
    // Log the override action
    $logStmt = $pdo->prepare("INSERT INTO activity_logs 
                             (user_id, action, description, created_at) 
                             VALUES (?, 'OVERRIDE_ATTENDANCE', ?, NOW())");
    $logStmt->execute([
        $_SESSION['user_id'],
        "Override auto-absent for attendance ID: $attendanceId. Changed from Alpha to $newStatus. Reason: $overrideReason"
    ]);
    
    $_SESSION['success'] = 'Status kehadiran berhasil diubah';
    header('Location: /hrm/admin/attendance/auto_absent.php?date=' . $attendance['attendance_date']);
    exit();
    
} catch (PDOException $e) {
    error_log("Error overriding attendance: " . $e->getMessage());
    $_SESSION['error'] = 'Gagal mengubah status: ' . $e->getMessage();
    header('Location: /hrm/admin/attendance/auto_absent.php');
    exit();
}
