<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/hrm/admin/payroll/index.php');
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    redirect('/hrm/admin/payroll/index.php', 'error', 'Token keamanan tidak valid');
}

// Get form data
$id = $_POST['id'] ?? 0;
$component_name = sanitize($_POST['component_name'] ?? '');
$calculation_type = $_POST['calculation_type'] ?? '';
$default_amount = $_POST['default_amount'] ?? 0;
$is_taxable = isset($_POST['is_taxable']) ? 1 : 0;
$is_active = isset($_POST['is_active']) ? 1 : 0;
$description = sanitize($_POST['description'] ?? '');

// Validate
if (empty($id) || empty($component_name) || empty($calculation_type)) {
    redirect('/hrm/admin/payroll/index.php', 'error', 'Data tidak lengkap');
}

try {
    // Update component
    $sql = "UPDATE payroll_components 
            SET component_name = ?, calculation_type = ?, default_amount = ?, is_taxable = ?, is_active = ?, description = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$component_name, $calculation_type, $default_amount, $is_taxable, $is_active, $description, $id]);
    
    redirect('/hrm/admin/payroll/index.php', 'success', 'Komponen gaji berhasil diupdate');
    
} catch (PDOException $e) {
    error_log("Error updating payroll component: " . $e->getMessage());
    redirect('/hrm/admin/payroll/index.php', 'error', 'Gagal mengupdate komponen gaji');
}
