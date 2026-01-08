<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/departments/index.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/departments/index.php', 'error', 'Token keamanan tidak valid');
}

// Get form data
$id = $_POST['id'] ?? 0;
$department_name = sanitize($_POST['department_name'] ?? '');
$description = sanitize($_POST['description'] ?? '');

// Validate
if (empty($id) || empty($department_name)) {
    redirect('/hrm/admin/departments/index.php', 'error', 'Data tidak lengkap');
}

try {
    // Check if department exists
    $checkStmt = $pdo->prepare("SELECT id FROM departments WHERE id = ?");
    $checkStmt->execute([$id]);
    if (!$checkStmt->fetch()) {
        redirect('/hrm/admin/departments/index.php', 'error', 'Departemen tidak ditemukan');
    }
    
    // Check if new name already exists (excluding current department)
    $checkNameStmt = $pdo->prepare("SELECT id FROM departments WHERE department_name = ? AND id != ?");
    $checkNameStmt->execute([$department_name, $id]);
    if ($checkNameStmt->fetch()) {
        redirect('/hrm/admin/departments/index.php', 'error', 'Nama departemen sudah digunakan');
    }
    
    // Update department
    $sql = "UPDATE departments SET department_name = ?, description = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$department_name, $description, $id]);
    
    redirect('/hrm/admin/departments/index.php', 'success', 'Departemen berhasil diupdate');
    
} catch (PDOException $e) {
    error_log("Error updating department: " . $e->getMessage());
    redirect('/hrm/admin/departments/index.php', 'error', 'Gagal mengupdate departemen');
}
