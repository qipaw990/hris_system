<?php
session_start();
require_once __DIR__ . '/../../../config/database.php';

// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    die("ERROR: Not logged in. user_id: " . ($_SESSION['user_id'] ?? 'not set') . ", role: " . ($_SESSION['role'] ?? 'not set'));
}

// Check if user is admin/hr
$userRole = strtolower($_SESSION['role']);
if (!in_array($userRole, ['admin', 'hr'])) {
    die("ERROR: Access denied. Your role: " . $_SESSION['role']);
}

// Get request ID
$requestId = $_GET['id'] ?? 0;

if ($requestId <= 0) {
    die("ERROR: Invalid request ID: $requestId");
}

echo "DEBUG: Request ID: $requestId<br>";
echo "DEBUG: User ID: {$_SESSION['user_id']}<br>";
echo "DEBUG: User Role: {$_SESSION['role']}<br>";

try {
    // Get request details
    $stmt = $pdo->prepare("SELECT * FROM attendance_correction_requests WHERE id = ?");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    
    if (!$request) {
        die("ERROR: Request not found with ID: $requestId");
    }
    
    echo "DEBUG: Request found - Employee ID: {$request['employee_id']}, Date: {$request['request_date']}, Status: {$request['status']}<br>";
    
    // Check if already processed
    if ($request['status'] !== 'Pending') {
        die("ERROR: Request already processed. Current status: {$request['status']}");
    }
    
    // Check if attendance already exists
    $attendanceCheck = $pdo->prepare("SELECT id, status FROM attendance 
                                      WHERE employee_id = ? AND attendance_date = ?");
    $attendanceCheck->execute([$request['employee_id'], $request['request_date']]);
    $existingAttendance = $attendanceCheck->fetch();
    
    if ($existingAttendance) {
        echo "DEBUG: Existing attendance found - ID: {$existingAttendance['id']}, Status: {$existingAttendance['status']}<br>";
        
        // If exists and not Alpha, reject
        if ($existingAttendance['status'] !== 'Alpha') {
            die("ERROR: Attendance already exists with status: {$existingAttendance['status']}");
        }
    } else {
        echo "DEBUG: No existing attendance record<br>";
    }
    
    // Start transaction
    echo "DEBUG: Starting transaction...<br>";
    $pdo->beginTransaction();
    
    try {
        if ($existingAttendance) {
            echo "DEBUG: Updating existing Alpha record...<br>";
            // Update existing Alpha record
            $updateAttendance = $pdo->prepare("UPDATE attendance SET 
                                              check_in = ?,
                                              check_out = ?,
                                              status = 'Hadir',
                                              notes = CONCAT(COALESCE(notes, ''), '\n[Corrected] Approved correction request'),
                                              updated_at = NOW()
                                              WHERE id = ?");
            $result = $updateAttendance->execute([
                $request['check_in_time'],
                $request['check_out_time'],
                $existingAttendance['id']
            ]);
            echo "DEBUG: Update result: " . ($result ? 'SUCCESS' : 'FAILED') . "<br>";
        } else {
            echo "DEBUG: Creating new attendance record...<br>";
            // Create new attendance record
            $insertStmt = $pdo->prepare("INSERT INTO attendance 
                                         (employee_id, attendance_date, check_in, check_out, status, notes, created_at) 
                                         VALUES (?, ?, ?, ?, 'Hadir', 'Approved correction request', NOW())");
            $result = $insertStmt->execute([
                $request['employee_id'],
                $request['request_date'],
                $request['check_in_time'],
                $request['check_out_time']
            ]);
            echo "DEBUG: Insert result: " . ($result ? 'SUCCESS' : 'FAILED') . "<br>";
            echo "DEBUG: Last insert ID: " . $pdo->lastInsertId() . "<br>";
        }
        
        echo "DEBUG: Updating request status...<br>";
        // Update request status
        $updateStmt = $pdo->prepare("UPDATE attendance_correction_requests SET 
                                     status = 'Approved',
                                     reviewed_by = ?,
                                     reviewed_at = NOW()
                                     WHERE id = ?");
        $result = $updateStmt->execute([$_SESSION['user_id'], $requestId]);
        echo "DEBUG: Update request result: " . ($result ? 'SUCCESS' : 'FAILED') . "<br>";
        echo "DEBUG: Rows affected: " . $updateStmt->rowCount() . "<br>";
        
        // Commit transaction first
        echo "DEBUG: Committing transaction...<br>";
        $pdo->commit();
        echo "DEBUG: Transaction committed successfully!<br>";
        
        // Log activity (optional - don't fail if table doesn't exist)
        try {
            $logStmt = $pdo->prepare("INSERT INTO activity_logs 
                                     (user_id, action, description, created_at) 
                                     VALUES (?, 'APPROVE_CORRECTION', ?, NOW())");
            $logStmt->execute([
                $_SESSION['user_id'],
                "Approved attendance correction request ID: $requestId for employee ID: {$request['employee_id']}, date: {$request['request_date']}"
            ]);
            echo "DEBUG: Activity logged<br>";
        } catch (PDOException $logError) {
            echo "DEBUG: Activity log failed (non-critical): " . $logError->getMessage() . "<br>";
        }
        
        $message = $existingAttendance 
            ? 'Request berhasil disetujui dan attendance record telah diupdate dari Alpha ke Hadir'
            : 'Request berhasil disetujui dan attendance record telah dibuat';
        
        echo "<br><strong style='color: green;'>SUCCESS: $message</strong><br>";
        echo "<a href='/hrm/admin/attendance/corrections/index.php'>Kembali ke daftar</a>";
        
        // Uncomment to enable auto-redirect
        // $_SESSION['success'] = $message;
        // header('Location: /hrm/admin/attendance/corrections/index.php');
        // exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "<br><strong style='color: red;'>TRANSACTION ERROR: " . $e->getMessage() . "</strong><br>";
        echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
        die();
    }
    
} catch (PDOException $e) {
    echo "<br><strong style='color: red;'>DATABASE ERROR: " . $e->getMessage() . "</strong><br>";
    echo "Stack trace:<br><pre>" . $e->getTraceAsString() . "</pre>";
    die();
}
