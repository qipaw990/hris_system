<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;

if (empty($id)) {
    redirect('/hrm/admin/positions/index.php', 'error', 'ID jabatan tidak valid');
}

try {
    // Check if position has employees
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE position_id = ?");
    $checkStmt->execute([$id]);
    $employeeCount = $checkStmt->fetchColumn();
    
    if ($employeeCount > 0) {
        redirect('/hrm/admin/positions/index.php', 'error', 'Tidak dapat menghapus jabatan yang masih memiliki karyawan');
    }
    
    // Delete position
    $deleteStmt = $pdo->prepare("DELETE FROM positions WHERE id = ?");
    $deleteStmt->execute([$id]);
    
    redirect('/hrm/admin/positions/index.php', 'success', 'Jabatan berhasil dihapus');
    
} catch (PDOException $e) {
    error_log("Error deleting position: " . $e->getMessage());
    redirect('/hrm/admin/positions/index.php', 'error', 'Gagal menghapus jabatan');
}
