<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';

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
    header('Location: /hrm/admin/leave/sick-permission/index.php');
    exit();
}

// Get request ID
$requestId = $_GET['id'] ?? 0;

if ($requestId <= 0) {
    $_SESSION['error'] = 'Invalid request ID';
    header('Location: /hrm/admin/leave/sick-permission/index.php');
    exit();
}

try {
    // Get request details
    $stmt = $pdo->prepare("SELECT * FROM sick_permission_requests WHERE id = ?");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    
    if (!$request) {
        $_SESSION['error'] = 'Request tidak ditemukan';
        header('Location: /hrm/admin/leave/sick-permission/index.php');
        exit();
    }
    
    // Check if already processed
    if ($request['status'] !== 'Pending') {
        $_SESSION['error'] = 'Request sudah diproses sebelumnya';
        header('Location: /hrm/admin/leave/sick-permission/index.php');
        exit();
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Update request status
        $updateStmt = $pdo->prepare("UPDATE sick_permission_requests SET 
                                     status = 'Approved',
                                     approved_by = ?,
                                     approved_at = NOW()
                                     WHERE id = ?");
        $updateStmt->execute([$_SESSION['user_id'], $requestId]);
        
        // Create attendance records for each day in the range
        $startDate = new DateTime($request['start_date']);
        $endDate = new DateTime($request['end_date']);
        $endDate->modify('+1 day'); // Include end date
        
        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($startDate, $interval, $endDate);
        
        $attendanceStatus = $request['request_type'] === 'Sakit' ? 'Sakit' : 'Izin';
        $notes = ucfirst($request['request_type']) . ' - Approved request';
        
        foreach ($dateRange as $date) {
            $currentDate = $date->format('Y-m-d');
            
            // Skip weekends
            $dayOfWeek = $date->format('N');
            if ($dayOfWeek >= 6) { // 6 = Saturday, 7 = Sunday
                continue;
            }
            
            // Check if attendance already exists
            $checkStmt = $pdo->prepare("SELECT id, status FROM attendance 
                                       WHERE employee_id = ? AND attendance_date = ?");
            $checkStmt->execute([$request['employee_id'], $currentDate]);
            $existingAttendance = $checkStmt->fetch();
            
            if ($existingAttendance) {
                // Update existing record (especially if it's Alpha)
                $updateAttendance = $pdo->prepare("UPDATE attendance SET 
                                                   status = ?,
                                                   notes = CONCAT(COALESCE(notes, ''), '\n', ?),
                                                   updated_at = NOW()
                                                   WHERE id = ?");
                $updateAttendance->execute([
                    $attendanceStatus,
                    $notes,
                    $existingAttendance['id']
                ]);
            } else {
                // Create new attendance record
                $insertAttendance = $pdo->prepare("INSERT INTO attendance 
                                                   (employee_id, attendance_date, status, notes, created_at)
                                                   VALUES (?, ?, ?, ?, NOW())");
                $insertAttendance->execute([
                    $request['employee_id'],
                    $currentDate,
                    $attendanceStatus,
                    $notes
                ]);
            }
        }
        
        // Commit transaction
        $pdo->commit();
        
        $message = ucfirst($request['request_type']) . ' request berhasil disetujui dan attendance record telah dibuat';
        $_SESSION['success'] = $message;
        header('Location: /hrm/admin/leave/sick-permission/index.php');
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
    
} catch (PDOException $e) {
    error_log("Error approving sick/permission request: " . $e->getMessage());
    $_SESSION['error'] = 'Gagal approve request: ' . $e->getMessage();
    header('Location: /hrm/admin/leave/sick-permission/index.php');
    exit();
}
