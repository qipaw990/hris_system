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

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'Invalid CSRF token';
    header('Location: /hrm/admin/leave/sick-permission/index.php');
    exit();
}

// Get form data
$requestId = $_POST['request_id'] ?? 0;
$rejectionReason = trim($_POST['rejection_reason'] ?? '');

if ($requestId <= 0) {
    $_SESSION['error'] = 'Invalid request ID';
    header('Location: /hrm/admin/leave/sick-permission/index.php');
    exit();
}

if (empty($rejectionReason)) {
    $_SESSION['error'] = 'Alasan penolakan harus diisi';
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
    
    // Update request status
    $updateStmt = $pdo->prepare("UPDATE sick_permission_requests SET 
                                 status = 'Rejected',
                                 approved_by = ?,
                                 approved_at = NOW(),
                                 rejection_reason = ?
                                 WHERE id = ?");
    $updateStmt->execute([$_SESSION['user_id'], $rejectionReason, $requestId]);
    
    $_SESSION['success'] = ucfirst($request['request_type']) . ' request berhasil ditolak';
    header('Location: /hrm/admin/leave/sick-permission/index.php');
    exit();
    
} catch (PDOException $e) {
    error_log("Error rejecting sick/permission request: " . $e->getMessage());
    $_SESSION['error'] = 'Gagal reject request: ' . $e->getMessage();
    header('Location: /hrm/admin/leave/sick-permission/index.php');
    exit();
}
