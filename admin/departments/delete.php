<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;

if (empty($id)) {
    redirect('/hrm/admin/departments/index.php', 'error', 'ID departemen tidak valid');
}

try {
    // Check if department has employees
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE department_id = ?");
    $checkStmt->execute([$id]);
    $employeeCount = $checkStmt->fetchColumn();
    
    if ($employeeCount > 0) {
        redirect('/hrm/admin/departments/index.php', 'error', 'Tidak dapat menghapus departemen yang masih memiliki karyawan');
    }
    
    // Delete department
    $deleteStmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
    $deleteStmt->execute([$id]);
    
    redirect('/hrm/admin/departments/index.php', 'success', 'Departemen berhasil dihapus');
    
} catch (PDOException $e) {
    error_log("Error deleting department: " . $e->getMessage());
    redirect('/hrm/admin/departments/index.php', 'error', 'Gagal menghapus departemen');
}
