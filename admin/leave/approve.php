<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;

if (empty($id)) {
    redirect('/hrm/admin/leave/index.php', 'error', 'ID tidak valid');
}

try {
    // Update leave request status
    $sql = "UPDATE leave_requests 
            SET status = 'Approved', 
                approved_by = ?, 
                approved_at = NOW() 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $id]);
    
    redirect('/hrm/admin/leave/index.php', 'success', 'Pengajuan cuti berhasil disetujui');
    
} catch (PDOException $e) {
    error_log("Error approving leave: " . $e->getMessage());
    redirect('/hrm/admin/leave/index.php', 'error', 'Gagal menyetujui cuti');
}
