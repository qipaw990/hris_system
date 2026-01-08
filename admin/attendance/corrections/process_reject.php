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
    header('Location: /hrm/admin/attendance/corrections/index.php');
    exit();
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'Invalid CSRF token';
    header('Location: /hrm/admin/attendance/corrections/index.php');
    exit();
}

// Get input
$requestId = intval($_POST['request_id'] ?? 0);
$rejectionReason = trim($_POST['rejection_reason'] ?? '');

// Validate
if ($requestId <= 0 || empty($rejectionReason)) {
    $_SESSION['error'] = 'Request ID dan alasan penolakan wajib diisi';
    header('Location: /hrm/admin/attendance/corrections/index.php');
    exit();
}

try {
    // Get request details
    $stmt = $pdo->prepare("SELECT * FROM attendance_correction_requests WHERE id = ?");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch();
    
    if (!$request) {
        $_SESSION['error'] = 'Request tidak ditemukan';
        header('Location: /hrm/admin/attendance/corrections/index.php');
        exit();
    }
    
    // Check if already processed
    if ($request['status'] !== 'Pending') {
        $_SESSION['error'] = 'Request sudah diproses sebelumnya';
        header('Location: /hrm/admin/attendance/corrections/index.php');
        exit();
    }
    
    // Update request status
    $updateStmt = $pdo->prepare("UPDATE attendance_correction_requests SET 
                                 status = 'Rejected',
                                 reviewed_by = ?,
                                 reviewed_at = NOW(),
                                 rejection_reason = ?
                                 WHERE id = ?");
    $updateStmt->execute([$_SESSION['user_id'], $rejectionReason, $requestId]);
    
    // Log activity
    $logStmt = $pdo->prepare("INSERT INTO activity_logs 
                             (user_id, action, description, created_at) 
                             VALUES (?, 'REJECT_CORRECTION', ?, NOW())");
    $logStmt->execute([
        $_SESSION['user_id'],
        "Rejected attendance correction request ID: $requestId. Reason: $rejectionReason"
    ]);
    
    $_SESSION['success'] = 'Request berhasil ditolak';
    header('Location: /hrm/admin/attendance/corrections/index.php');
    exit();
    
} catch (PDOException $e) {
    error_log("Error rejecting correction request: " . $e->getMessage());
    $_SESSION['error'] = 'Gagal reject request: ' . $e->getMessage();
    header('Location: /hrm/admin/attendance/corrections/index.php');
    exit();
}
