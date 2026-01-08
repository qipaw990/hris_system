<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
$reason = $_GET['reason'] ?? '';

if (empty($id) || empty($reason)) {
    redirect('/hrm/admin/leave/index.php', 'error', 'ID atau alasan tidak valid');
}

try {
    // Update leave request status
    $sql = "UPDATE leave_requests 
            SET status = 'Rejected', 
                approved_by = ?, 
                approved_at = NOW(),
                rejection_reason = ?
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $reason, $id]);
    
    redirect('/hrm/admin/leave/index.php', 'success', 'Pengajuan cuti berhasil ditolak');
    
} catch (PDOException $e) {
    error_log("Error rejecting leave: " . $e->getMessage());
    redirect('/hrm/admin/leave/index.php', 'error', 'Gagal menolak cuti');
}
