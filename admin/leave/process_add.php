<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/leave/index.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/leave/index.php', 'error', 'Token keamanan tidak valid');
}

// Get form data
$employee_id = $_POST['employee_id'] ?? 0;
$leave_type_id = $_POST['leave_type_id'] ?? 0;
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$total_days = $_POST['total_days'] ?? 0;
$reason = sanitize($_POST['reason'] ?? '');

// Validate
if (empty($employee_id) || empty($leave_type_id) || empty($start_date) || empty($end_date) || empty($reason)) {
    redirect('/hrm/admin/leave/index.php', 'error', 'Semua field harus diisi');
}

try {
    // Insert leave request
    $sql = "INSERT INTO leave_requests (employee_id, leave_type_id, start_date, end_date, total_days, reason, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$employee_id, $leave_type_id, $start_date, $end_date, $total_days, $reason]);
    
    redirect('/hrm/admin/leave/index.php', 'success', 'Pengajuan cuti berhasil diajukan');
    
} catch (PDOException $e) {
    error_log("Error adding leave request: " . $e->getMessage());
    redirect('/hrm/admin/leave/index.php', 'error', 'Gagal mengajukan cuti');
}
