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
$component_name = sanitize($_POST['component_name'] ?? '');
$component_type = $_POST['component_type'] ?? '';
$calculation_type = $_POST['calculation_type'] ?? '';
$default_amount = $_POST['default_amount'] ?? 0;
$is_taxable = isset($_POST['is_taxable']) ? 1 : 0;
$description = sanitize($_POST['description'] ?? '');

// Validate
if (empty($component_name) || empty($component_type) || empty($calculation_type)) {
    redirect('/hrm/admin/payroll/index.php', 'error', 'Data tidak lengkap');
}

try {
    // Insert component
    $sql = "INSERT INTO payroll_components (component_name, component_type, calculation_type, default_amount, is_taxable, description) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$component_name, $component_type, $calculation_type, $default_amount, $is_taxable, $description]);
    
    redirect('/hrm/admin/payroll/index.php', 'success', 'Komponen gaji berhasil ditambahkan');
    
} catch (PDOException $e) {
    error_log("Error adding payroll component: " . $e->getMessage());
    redirect('/hrm/admin/payroll/index.php', 'error', 'Gagal menambahkan komponen gaji');
}
